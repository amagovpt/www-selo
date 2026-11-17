<?php

// Exit se o ficheiro for acedido diretamente
defined('ABSPATH') || exit;

/**
 * Versão do esquema da tabela "sitios_web_com_selo".
 *
 * Sempre que a estrutura da tabela mudar, incrementar este número:
 * a migração corre sozinha (ama_selo_migrar_tabela), sem ser preciso reativar o tema.
 */
const AMA_SELO_DB_VERSAO = '3';

/** Tipos de plataforma aceites na coluna "tipo". */
const AMA_SELO_TIPO_SITIO_WEB = 'sitio_web';
const AMA_SELO_TIPO_APP       = 'aplicacao_movel';

/** Tamanho máximo aceite para a resposta da fonte de dados (bytes). */
const AMA_SELO_FONTE_TAMANHO_MAXIMO = 5 * MB_IN_BYTES;

// Função para criar a tabela no banco de dados
function criar_tabela_sitios_web_com_selo()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';
    $charset_collate = $wpdb->get_charset_collate();

    // dbDelta cria a tabela ou acrescenta as colunas que faltarem.
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL,
        name varchar(255) NOT NULL,
        entidade varchar(255) NOT NULL,
        selo varchar(50) NOT NULL,
        data_selo datetime NOT NULL,
        declaracao varchar(50) NOT NULL,
        data_declaracao datetime NOT NULL,
        url_observatorio varchar(255) NOT NULL,
        url varchar(255) NOT NULL,
        tipo varchar(20) NOT NULL DEFAULT 'sitio_web',
        data_atualizacao timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        mostrar tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY  (tipo,id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql );
}
add_action( 'after_switch_theme', 'criar_tabela_sitios_web_com_selo' );

/**
 * Cria/atualiza a tabela quando a versão do esquema guardada for antiga.
 *
 * Antes, as colunas novas só eram criadas ao reativar o tema (after_switch_theme).
 *
 * @return void
 */
function ama_selo_migrar_tabela(): void
{
    if ( get_option( 'ama_selo_db_versao' ) === AMA_SELO_DB_VERSAO ) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';

    // Os ids de sítios web e de apps vêm de fontes diferentes e repetem-se:
    // a chave primária passa a ser (tipo, id). O dbDelta não muda chaves primárias.
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) {
        if ( ! $wpdb->get_var( "SHOW COLUMNS FROM $table_name LIKE 'tipo'" ) ) {
            $wpdb->query( "ALTER TABLE $table_name ADD COLUMN tipo varchar(20) NOT NULL DEFAULT 'sitio_web' AFTER url" );
        }

        $chave = $wpdb->get_col( "SHOW KEYS FROM $table_name WHERE Key_name = 'PRIMARY'", 4 );

        if ( [ 'tipo', 'id' ] !== $chave ) {
            $wpdb->query( "ALTER TABLE $table_name DROP PRIMARY KEY, ADD PRIMARY KEY (tipo, id)" );
        }
    }

    criar_tabela_sitios_web_com_selo();
    update_option( 'ama_selo_db_versao', AMA_SELO_DB_VERSAO );
}
add_action( 'admin_init', 'ama_selo_migrar_tabela' );
add_action( 'ama_selo_antes_sincronizar', 'ama_selo_migrar_tabela' );

/**
 * Endereço da fonte de dados de um tipo de plataforma.
 *
 * Duas fontes com a mesma estrutura: sítios web (endpoint do Observatório ou JSON)
 * e aplicações móveis (JSON). As constantes AMA_SELO_FONTE_URL e AMA_SELO_FONTE_APPS_URL
 * (wp-config.php) têm prioridade sobre os campos em Definições > Definições | AMA.
 *
 * @param string $tipo AMA_SELO_TIPO_SITIO_WEB ou AMA_SELO_TIPO_APP.
 * @return string
 */
function ama_selo_fonte_url( string $tipo = AMA_SELO_TIPO_SITIO_WEB ): string
{
    $app       = AMA_SELO_TIPO_APP === $tipo;
    $constante = $app ? 'AMA_SELO_FONTE_APPS_URL' : 'AMA_SELO_FONTE_URL';

    if ( defined( $constante ) && constant( $constante ) ) {
        return trim( (string) constant( $constante ) );
    }

    return trim( (string) get_option( $app ? 'ama_selo_fonte_apps_url' : 'indicadores_endpoint_url', '' ) );
}

/**
 * Fontes configuradas, por tipo. A fonte das apps é opcional; a dos sítios web não.
 *
 * @return array<string,string> tipo => URL
 */
function ama_selo_fontes(): array
{
    $fontes = [ AMA_SELO_TIPO_SITIO_WEB => ama_selo_fonte_url( AMA_SELO_TIPO_SITIO_WEB ) ];
    $apps   = ama_selo_fonte_url( AMA_SELO_TIPO_APP );

    if ( '' !== $apps ) {
        $fontes[ AMA_SELO_TIPO_APP ] = $apps;
    }

    return $fontes;
}

/**
 * Nome legível do tipo de plataforma.
 *
 * @param string $tipo
 * @return string
 */
function ama_selo_nome_tipo( string $tipo ): string
{
    return AMA_SELO_TIPO_APP === $tipo ? 'Aplicação móvel' : 'Sítio web';
}

/**
 * Valida a origem da fonte de dados: só HTTPS e, se definido, só os anfitriões autorizados.
 *
 * AMA_SELO_FONTE_HOSTS (wp-config.php): lista de anfitriões separados por vírgulas.
 *
 * @param string $url
 * @return true|WP_Error
 */
function ama_selo_validar_origem( string $url )
{
    if ( '' === $url ) {
        return new WP_Error( 'selo_sem_fonte', 'Não está definido o endereço da fonte de dados dos selos.' );
    }

    $partes = wp_parse_url( $url );

    if ( empty( $partes['scheme'] ) || empty( $partes['host'] ) ) {
        return new WP_Error( 'selo_fonte_invalida', 'O endereço da fonte de dados dos selos não é válido.' );
    }

    /**
     * Permite HTTP (sem TLS) só em ambientes de teste.
     *
     * @param bool $permitir
     */
    $permitir_http = apply_filters( 'ama_selo_permitir_http', 'local' === wp_get_environment_type() );
    $esquema       = strtolower( $partes['scheme'] );

    if ( 'https' !== $esquema && ! ( $permitir_http && 'http' === $esquema ) ) {
        return new WP_Error( 'selo_fonte_sem_https', 'A fonte de dados dos selos tem de usar HTTPS.' );
    }

    if ( defined( 'AMA_SELO_FONTE_HOSTS' ) && AMA_SELO_FONTE_HOSTS ) {
        $hosts = array_filter( array_map( 'strtolower', array_map( 'trim', explode( ',', (string) AMA_SELO_FONTE_HOSTS ) ) ) );

        if ( ! in_array( strtolower( $partes['host'] ), $hosts, true ) ) {
            return new WP_Error( 'selo_fonte_nao_autorizada', 'O anfitrião da fonte de dados dos selos não está autorizado (AMA_SELO_FONTE_HOSTS).' );
        }
    }

    return true;
}

/**
 * Faz o pedido HTTP à fonte de dados.
 *
 * AMA_SELO_FONTE_TOKEN (wp-config.php): enviado como "Authorization: Bearer <token>".
 *
 * @param string $url
 * @return array|WP_Error Resposta do wp_remote_get.
 */
function ama_selo_pedido_fonte( string $url )
{
    $cabecalhos = [ 'Accept' => 'application/json' ];

    if ( defined( 'AMA_SELO_FONTE_TOKEN' ) && AMA_SELO_FONTE_TOKEN ) {
        $cabecalhos['Authorization'] = 'Bearer ' . AMA_SELO_FONTE_TOKEN;
    }

    return wp_safe_remote_get( $url, [
        'timeout'             => 20,
        'redirection'         => 2,
        'headers'             => $cabecalhos,
        'limit_response_size' => AMA_SELO_FONTE_TAMANHO_MAXIMO,
        'user-agent'          => 'Selo-Usabilidade-Acessibilidade; ' . home_url( '/' ),
    ] );
}

/**
 * Certifica que o conteúdo veio de quem tem o segredo partilhado (HMAC-SHA256).
 *
 * Só é exigido quando AMA_SELO_FONTE_SEGREDO está definido no wp-config.php.
 * A assinatura (hex, com ou sem prefixo "sha256=") é lida do cabeçalho "X-Selo-Assinatura"
 * ou, se a fonte for um ficheiro estático, do ficheiro "<url>.sig".
 *
 * @param string $corpo    Corpo da resposta, tal como recebido.
 * @param array  $resposta Resposta do wp_remote_get.
 * @param string $url      Endereço da fonte.
 * @return true|WP_Error
 */
function ama_selo_verificar_assinatura( string $corpo, $resposta, string $url )
{
    if ( ! defined( 'AMA_SELO_FONTE_SEGREDO' ) || ! AMA_SELO_FONTE_SEGREDO ) {
        return true;
    }

    $assinatura = (string) wp_remote_retrieve_header( $resposta, 'x-selo-assinatura' );

    if ( '' === $assinatura ) {
        $resposta_sig = ama_selo_pedido_fonte( $url . '.sig' );

        if ( ! is_wp_error( $resposta_sig ) && 200 === (int) wp_remote_retrieve_response_code( $resposta_sig ) ) {
            $assinatura = (string) wp_remote_retrieve_body( $resposta_sig );
        }
    }

    $assinatura = strtolower( trim( preg_replace( '/^\s*sha256=/i', '', $assinatura ) ) );

    if ( '' === $assinatura ) {
        return new WP_Error( 'selo_sem_assinatura', 'A fonte de dados dos selos não enviou assinatura (X-Selo-Assinatura ou ficheiro .sig).' );
    }

    $esperada = hash_hmac( 'sha256', $corpo, (string) AMA_SELO_FONTE_SEGREDO );

    if ( ! hash_equals( $esperada, $assinatura ) ) {
        return new WP_Error( 'selo_assinatura_invalida', 'A assinatura da fonte de dados dos selos não é válida: os dados foram ignorados.' );
    }

    return true;
}

/**
 * Normaliza o tipo de plataforma vindo da fonte.
 *
 * Campo opcional "tipo" no JSON; sem ele, o registo é tratado como sítio web.
 *
 * @param mixed $tipo
 * @return string AMA_SELO_TIPO_SITIO_WEB ou AMA_SELO_TIPO_APP.
 */
function ama_selo_normalizar_tipo( $tipo ): string
{
    $tipo = str_replace( '-', '_', sanitize_title( (string) $tipo ) );

    if ( in_array( $tipo, [ 'aplicacao_movel', 'aplicacoes_moveis', 'aplicacao', 'app', 'apps', 'mobile', 'mobile_app' ], true ) ) {
        return AMA_SELO_TIPO_APP;
    }

    return AMA_SELO_TIPO_SITIO_WEB;
}

/**
 * Converte uma data da fonte em "Y-m-d H:i:s" (ou null se não for válida, ex.: "nd").
 *
 * Aceita AAAA-MM-DD (Observatório) e DD/MM/AAAA ou DD-MM-AAAA (JSON do Git).
 * Não usa strtotime: "02/10/2026" seria lido como 10 de fevereiro.
 *
 * @param mixed $data
 * @return string|null
 */
function ama_selo_normalizar_data( $data ): ?string
{
    $data = trim( (string) $data );

    foreach ( [ 'Y-m-d H:i:s', 'Y-m-d', 'd/m/Y', 'd-m-Y' ] as $formato ) {
        $d = DateTimeImmutable::createFromFormat( '!' . $formato, $data, new DateTimeZone( 'UTC' ) );

        if ( $d && $d->format( $formato ) === $data ) {
            return $d->format( 'Y-m-d H:i:s' );
        }
    }

    return null;
}

/**
 * Valida e limpa um registo da fonte.
 *
 * @param mixed  $item
 * @param string $tipo Tipo da fonte de onde veio o registo.
 * @return array|null Linha pronta para a tabela, ou null se o registo for inválido.
 */
function ama_selo_normalizar_registo( $item, string $tipo = AMA_SELO_TIPO_SITIO_WEB ): ?array
{
    if ( ! is_array( $item ) ) {
        return null;
    }

    $id   = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
    $nome = sanitize_text_field( (string) ( $item['Name'] ?? $item['name'] ?? '' ) );
    $selo = ucfirst( strtolower( sanitize_text_field( (string) ( $item['selo'] ?? '' ) ) ) );

    if ( ! $id || '' === $nome || ! in_array( $selo, [ 'Ouro', 'Prata', 'Bronze' ], true ) ) {
        return null;
    }

    return [
        'id'               => $id,
        'name'             => $nome,
        'entidade'         => sanitize_text_field( (string) ( $item['entidade'] ?? '' ) ),
        'selo'             => $selo,
        'data_selo'        => ama_selo_normalizar_data( $item['data_selo'] ?? '' ) ?? '0000-00-00 00:00:00',
        'declaracao'       => sanitize_text_field( (string) ( $item['declaracao'] ?? '' ) ),
        'data_declaracao'  => ama_selo_normalizar_data( $item['data_declaracao'] ?? '' ) ?? '0000-00-00 00:00:00',
        'url_observatorio' => esc_url_raw( (string) ( $item['url_observatorio'] ?? '' ), [ 'http', 'https' ] ),
        'url'              => esc_url_raw( (string) ( $item['url'] ?? '' ), [ 'http', 'https' ] ),
        'tipo'             => ama_selo_normalizar_tipo( $tipo ),
        'mostrar'          => 1,
    ];
}

/**
 * Obtém e valida os registos de uma fonte.
 *
 * @param string $endpoint
 * @param string $tipo
 * @return array|WP_Error [ 'linhas' => [...], 'invalidos' => int ]
 */
function ama_selo_obter_registos( string $endpoint, string $tipo )
{
    $origem = ama_selo_validar_origem( $endpoint );

    if ( is_wp_error( $origem ) ) {
        return $origem;
    }

    $response = ama_selo_pedido_fonte( $endpoint );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'api_error', 'Não foi possível comunicar com a fonte: ' . $response->get_error_message() );
    }

    $codigo = (int) wp_remote_retrieve_response_code( $response );

    if ( 200 !== $codigo ) {
        return new WP_Error( 'api_error', sprintf( 'A fonte respondeu com o código HTTP %d.', $codigo ) );
    }

    $response_data = (string) wp_remote_retrieve_body( $response );

    if ( '' === trim( $response_data ) ) {
        return new WP_Error( 'api_error', 'A fonte devolveu uma resposta vazia.' );
    }

    $assinatura = ama_selo_verificar_assinatura( $response_data, $response, $endpoint );

    if ( is_wp_error( $assinatura ) ) {
        return $assinatura;
    }

    // Aceita JSON com BOM (ficheiros gravados em alguns editores).
    $data = json_decode( preg_replace( '/^\xEF\xBB\xBF/', '', $response_data ), true );

    if ( ! is_array( $data ) || ! array_is_list( $data ) ) {
        return new WP_Error( 'api_error', 'A fonte não devolveu uma lista JSON válida.' );
    }

    $linhas    = [];
    $invalidos = 0;

    foreach ( $data as $item ) {
        $linha = ama_selo_normalizar_registo( $item, $tipo );

        if ( null === $linha ) {
            $invalidos++;
            continue;
        }

        $linhas[ $linha['id'] ] = $linha;
    }

    if ( ! $linhas ) {
        return new WP_Error( 'api_error', 'A fonte não tem nenhum registo válido.' );
    }

    return [ 'linhas' => $linhas, 'invalidos' => $invalidos ];
}

/**
 * Substitui os registos publicados de um tipo, numa transação (tudo ou nada).
 *
 * @param string $tipo
 * @param array  $linhas
 * @return true|WP_Error
 */
function ama_selo_gravar_registos( string $tipo, array $linhas )
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';

    $wpdb->query( 'START TRANSACTION' );

    $ok = false !== $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET mostrar = 0 WHERE tipo = %s", $tipo ) );

    foreach ( $linhas as $linha ) {
        if ( ! $ok ) {
            break;
        }

        $ok = false !== $wpdb->replace( $table_name, $linha );
    }

    if ( ! $ok ) {
        $erro = $wpdb->last_error;
        $wpdb->query( 'ROLLBACK' );

        return new WP_Error( 'db_error', 'Erro ao gravar na base de dados: ' . $erro );
    }

    $wpdb->query( 'COMMIT' );

    return true;
}

/**
 * Atualiza a tabela com os dados das fontes (sítios web e, se configurada, apps).
 *
 * Cada fonte é tratada à parte: se uma falhar (indisponível, vazia, JSON inválido,
 * assinatura errada), os registos publicados desse tipo ficam como estavam e os da
 * outra fonte são atualizados na mesma.
 *
 * @return bool|WP_Error true se todas as fontes foram atualizadas; WP_Error com os erros
 *                       (código "selo_parcial" se pelo menos uma fonte foi atualizada).
 */
function atualizar_tabela_com_dados_observatorio()
{
    do_action( 'ama_selo_antes_sincronizar' );

    $resumo = [ 'data' => time(), 'registos' => 0, 'invalidos' => 0, 'fontes' => [] ];
    $erros  = [];

    foreach ( ama_selo_fontes() as $tipo => $endpoint ) {
        $nome = AMA_SELO_TIPO_APP === $tipo ? 'aplicações móveis' : 'sítios web';

        if ( '' === $endpoint ) {
            $erros[] = sprintf( 'Fonte de %s: não está definido o endereço.', $nome );
            continue;
        }

        $registos = ama_selo_obter_registos( $endpoint, $tipo );

        if ( ! is_wp_error( $registos ) ) {
            $gravado = ama_selo_gravar_registos( $tipo, $registos['linhas'] );
            $registos = is_wp_error( $gravado ) ? $gravado : $registos;
        }

        if ( is_wp_error( $registos ) ) {
            $erros[] = sprintf( 'Fonte de %s: %s Os dados anteriores foram mantidos.', $nome, $registos->get_error_message() );
            continue;
        }

        $resumo['fontes'][ $tipo ] = [ 'registos' => count( $registos['linhas'] ), 'invalidos' => $registos['invalidos'] ];
        $resumo['registos']       += count( $registos['linhas'] );
        $resumo['invalidos']      += $registos['invalidos'];
    }

    if ( $resumo['fontes'] ) {
        update_option( 'ama_selo_sync_resumo', $resumo, false );
    }

    if ( $erros ) {
        return new WP_Error( $resumo['fontes'] ? 'selo_parcial' : 'selo_erro', implode( ' ', $erros ) );
    }

    return true;
}

/**
 * Devolve todos os dados da tabela "sitios_web_com_selo".
 *
 * @param array $args resultados + pagina (paginação), tipo (AMA_SELO_TIPO_*),
 *                    ordenar (nome, tipo, selo, data_declaracao) e sentido (asc, desc).
 * @return array|WP_Error Um array que contém os dados da tabela, ou um WP_Error em caso de erro.
 */
function dados_sitios_web( array $args = [])
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'sitios_web_com_selo';

    $query = "SELECT * FROM $table_name WHERE `mostrar` = 1";

    if ( ! empty( $args['tipo'] ) ) {
        $query .= $wpdb->prepare( ' AND `tipo` = %s', ama_selo_normalizar_tipo( $args['tipo'] ) );
    }

    $query .= ' ORDER BY ' . ama_selo_ordem_sql( $args['ordenar'] ?? '', $args['sentido'] ?? '' );

    if ( isset( $args['resultados'], $args['pagina'] ) ) {
        $resultados = max( 1, absint( $args['resultados'] ) );
        $query     .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $resultados, ( max( 1, absint( $args['pagina'] ) ) - 1 ) * $resultados );
    }

    $results = $wpdb->get_results($query, ARRAY_A);

    if ($wpdb->last_error) {
        return new WP_Error('db_error', __('Erro ao obter os dados da tabela.'));
    }

    return $results;
}

/**
 * Cláusula ORDER BY para a lista (só colunas e sentidos conhecidos: nada vem do pedido sem filtro).
 *
 * Sem ordenação pedida: Ouro, Prata, Bronze e depois o nome.
 *
 * @param string $ordenar nome, tipo, selo ou data_declaracao.
 * @param string $sentido asc ou desc.
 * @return string
 */
function ama_selo_ordem_sql( string $ordenar = '', string $sentido = '' ): string
{
    $desc = 'desc' === strtolower( $sentido ) ? 'DESC' : 'ASC';

    switch ( $ordenar ) {
        case 'nome':
            return "name $desc";
        case 'tipo':
            return "tipo $desc, name ASC";
        case 'selo':
            // Ascendente = do selo mais alto para o mais baixo (Ouro, Prata, Bronze).
            return "FIELD(selo, 'Ouro', 'Prata', 'Bronze') $desc, name ASC";
        case 'data_declaracao':
            // Sem data ("nd") fica sempre no fim.
            return "data_declaracao = '0000-00-00 00:00:00' ASC, data_declaracao $desc, name ASC";
        default:
            return "FIELD(selo, 'Ouro', 'Prata', 'Bronze'), name ASC";
    }
}

/**
 * Devolve os dados de um único sítio ‘web’ com base no ‘ID’.
 *
 * @param $site_id  O ID do site a ser recuperado.
 * @param string $tipo Tipo de plataforma (os ids repetem-se entre sítios web e apps).
 * @return array|WP_Error Um array contendo os dados do sítio ‘web’, ou um WP_Error em caso de erro ou se o sítio ‘web’ não for encontrado.
 */
function dados_site_por_id($site_id, string $tipo = AMA_SELO_TIPO_SITIO_WEB)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';
    $query      = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d AND tipo = %s", $site_id, ama_selo_normalizar_tipo($tipo));
    $result     = $wpdb->get_row($query, ARRAY_A);

    if ($wpdb->last_error) {
        return new WP_Error('db_error', __('Erro ao obter os dados do site.'));
    }

    if (!$result) {
        return new WP_Error('site_not_found', __('Site não encontrado.'));
    }

    return $result;
}


/**
 * Devolve o número total de páginas de sítios web com selo.
 *
 * @param int $resultados_por_pagina O número de resultados por página.
 * @return int O número total de páginas.
 */
function numero_total_paginas_sitios_web($resultados_por_pagina)
{
    global $wpdb;
    $table_name         = $wpdb->prefix . 'sitios_web_com_selo';
    $query              = "SELECT COUNT(*) FROM $table_name WHERE `mostrar` = 1";
    $total_sitios_web   = $wpdb->get_var($query);

    return ceil($total_sitios_web / $resultados_por_pagina);
}

/**
 * Devolve o nº de sítios web com selo especifico.
 *
 * @author RCP
 * @param string      $tipo_selo Ouro, Prata ou Bronze.
 * @param string|null $tipo      Tipo de plataforma (AMA_SELO_TIPO_*); null = todos.
 * @return string|null
 */
function numero_sitios_web_com_selo($tipo_selo, ?string $tipo = null): ?string
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';
    $query      = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE LOWER(selo) = %s AND `mostrar` = 1", strtolower($tipo_selo));

    if ( $tipo ) {
        $query .= $wpdb->prepare( ' AND `tipo` = %s', ama_selo_normalizar_tipo( $tipo ) );
    }

    return $wpdb->get_var($query);
}

/**
 * Data da última sincronização com sucesso (por extenso).
 *
 * Usa o registo da última sincronização; a coluna data_atualizacao só serve de recurso,
 * porque só muda nas linhas cujos valores mudaram.
 *
 * @return string|null
 */
function data_de_atualizacao_sitios_web_com_selo(): ?string
{
    $ultimo_sucesso = (int) get_option( 'ama_selo_sync_ultimo_sucesso' );

    if ( $ultimo_sucesso ) {
        return ama_selo_data_extenso( $ultimo_sucesso );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';

    $data_raw = $wpdb->get_var("SELECT `data_atualizacao` FROM $table_name WHERE `mostrar` = 1 ORDER BY `data_atualizacao` DESC LIMIT 1;");

    if (!$data_raw) {
        return null;
    }

    return ama_selo_data_extenso( strtotime( $data_raw ) );
}

/**
 * Data por extenso com o mês em minúsculas (ex.: "9 de outubro de 2026"), no fuso horário do site.
 *
 * @param int $timestamp
 * @return string
 */
function ama_selo_data_extenso( int $timestamp ): string
{
    return sprintf(
        '%s de %s de %s',
        wp_date( 'j', $timestamp ),
        mb_strtolower( wp_date( 'F', $timestamp ) ),
        wp_date( 'Y', $timestamp )
    );
}

/**
 * Endereço da ficha de um sítio web ou aplicação móvel.
 *
 * As apps levam "tipo=app" porque os ids repetem-se entre sítios web e apps.
 *
 * @param array $item Linha da tabela (id, tipo).
 * @return string
 */
function ama_selo_url_ficha( array $item ): string
{
    /**
     * ID da página com o template "Ficha do sítio web".
     *
     * @param int $pagina_id
     */
    $pagina_id = (int) apply_filters( 'ama_selo_pagina_ficha', 1062 );

    $args = [ 'idsitio' => (int) $item['id'] ];

    if ( AMA_SELO_TIPO_APP === ( $item['tipo'] ?? '' ) ) {
        $args['tipo'] = 'app';
    }

    return add_query_arg( $args, trailingslashit( get_permalink( $pagina_id ) ) );
}
