<?php

// Exit se o ficheiro for acedido diretamente
defined('ABSPATH') || exit;

// Função para criar a tabela no banco de dados
function criar_tabela_sitios_web_com_selo()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';
    $charset_collate = $wpdb->get_charset_collate();

    // Query para criar a tabela
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` mediumint(9) NOT NULL,
        `name` varchar(255) NOT NULL,
        `entidade` varchar(255) NOT NULL,
        `selo` varchar(50) NOT NULL,
        `data_selo` datetime NOT NULL,
        `declaracao` varchar(50) NOT NULL,
        `data_declaracao` datetime NOT NULL,
        `url_observatorio` varchar(255) NOT NULL,
        `url` varchar(255) NOT NULL,
        `data_atualizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql );
}
add_action( 'after_switch_theme', 'criar_tabela_sitios_web_com_selo' );

/**
 * Verifica se a coluna "data_atualizacao" existe na tabela "sitios_web_com_selo".
 *
 * Se a coluna não existir, ela será criada.
 *
 * @return void
 */
function verificar_coluna_data_atualizacao_sitios_web(): void {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';
    $column_name = 'data_atualizacao';
    $column_exists = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE '$column_name'");

    // Se a coluna não existir, vamos alterar a tabela e adicionar a coluna.
    if ( ! $column_exists ) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
}
add_action('after_switch_theme', 'verificar_coluna_data_atualizacao_sitios_web');


/**
 * Garante que a coluna "mostrar" existe na tabela "sitios_web_com_selo".
 *
 * @author RCP
 * @return void
 */
function verifica_coluna_mostrar_sitios_web(): void {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';
    $column_name = 'mostrar';
    $column_exists = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE '$column_name'");

    if ( ! $column_exists ) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN $column_name TINYINT(1) NOT NULL DEFAULT 0");
    }
}
add_action('after_switch_theme', 'verifica_coluna_mostrar_sitios_web');


/**
 * Atualiza a tabela com os dados do arquivo JSON.
 *
 * Esta função lê o conteúdo do arquivo JSON e atualiza a tabela com os dados.
 *
 * @return bool|WP_Error Devolve true se os dados foram atualizados com sucesso, caso contrário devolve um WP_Error.
 */
function atualizar_tabela_com_dados_observatorio()
{
    $endpoint = get_option('indicadores_endpoint_url');
    $response = wp_remote_get( $endpoint );

    if ( is_wp_error( $response ) ) {
        return new WP_Error('api_error', __('Não foi possível comunicar com o endpoint da API:'.' '.$endpoint.'.'));
    }

    $response_data = wp_remote_retrieve_body( $response );

    if( is_wp_error( $response_data ) || empty( $response_data ) ) {
        return new WP_Error('api_error', __('Erro ao obter os dados da API.Erro:'.$response_data.'.'));
    }

    $data = json_decode( $response_data, true );

    if ( ! empty( $data ) ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sitios_web_com_selo';
        // marca todos os registros como não mostrados
        $wpdb->query("UPDATE $table_name SET mostrar = 0");

        foreach ( $data as $item ) {
            $existing_item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $item['id'] ) );

            if ( $existing_item ) {
                $wpdb->update(
                    $table_name,
                    [
                        'name'              => $item['Name'],
                        'entidade'          => $item['entidade'],
                        'selo'              => $item['selo'],
                        'data_selo'         => date( 'Y-m-d H:i:s', strtotime( $item['data_selo'] ) ),
                        'declaracao'        => $item['declaracao'],
                        'data_declaracao'   => date( 'Y-m-d H:i:s', strtotime( $item['data_declaracao'] ) ),
                        'url_observatorio'  => $item['url_observatorio'],
                        'url'               => $item['url'],
                        'mostrar'           => 1, 
                    ],
                    array( 'id' => $item['id'] )
                );
            } else {
                $wpdb->insert(
                    $table_name,
                    [
                        'id'                => $item['id'],
                        'name'              => $item['Name'],
                        'entidade'          => $item['entidade'],
                        'selo'              => $item['selo'],
                        'data_selo'         => date( 'Y-m-d H:i:s', strtotime( $item['data_selo'] ) ),
                        'declaracao'        => $item['declaracao'],
                        'data_declaracao'   => date( 'Y-m-d H:i:s', strtotime( $item['data_declaracao'] ) ),
                        'url_observatorio'  => $item['url_observatorio'],
                        'url'               => $item['url'],
                        'mostrar'           => 1,
                    ]
                );
            }
        }
    }

    return true;
}

/**
 * Devolve todos os dados da tabela "sitios_web_com_selo".
 *
 * @return array|WP_Error Um array que contém os dados da tabela, ou um WP_Error em caso de erro.
 */
function dados_sitios_web( array $args = [])
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'sitios_web_com_selo';

    $query = "SELECT * FROM $table_name WHERE `mostrar` = 1 ORDER BY FIELD(selo, 'Ouro', 'Prata', 'Bronze'), name ASC ";

    if ( ! empty( $args ) ) {
       if(array_key_exists("resultados", $args) && array_key_exists("pagina", $args)) {
           $query .= "LIMIT " . $args['resultados'] . " OFFSET " . ($args['pagina'] - 1) * $args['resultados'];
       }
    }

    $results = $wpdb->get_results($query, ARRAY_A);

    if ($wpdb->last_error) {
        return new WP_Error('db_error', __('Erro ao obter os dados da tabela.'));
    }

    return $results;
}

/**
 * Devolve os dados de um único sítio ‘web’ com base no ‘ID’.
 *
 * @param $site_id  O ID do site a ser recuperado.
 * @return array|WP_Error Um array contendo os dados do sítio ‘web’, ou um WP_Error em caso de erro ou se o sítio ‘web’ não for encontrado.
 */
function dados_site_por_id($site_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';
    $query      = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $site_id);
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
 * @param string $tipo_selo
 * @return string|null
 */
function numero_sitios_web_com_selo($tipo_selo): ?string
{
    global $wpdb;
    $table_name         = $wpdb->prefix . 'sitios_web_com_selo';
    $query              = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE selo = LOWER(%s) AND `mostrar` = 1", strtolower($tipo_selo));
    $total_sitios_web   = $wpdb->get_var($query);

    return $total_sitios_web;
}

/**
 * Summary of data_de_atualizacao_sitios_web_com_selo
 * 
 * @return string|null
 */
function data_de_atualizacao_sitios_web_com_selo(): ?string
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sitios_web_com_selo';

    $data_raw = $wpdb->get_var("SELECT `data_atualizacao` FROM $table_name WHERE `mostrar` = 1 ORDER BY `data_atualizacao` DESC LIMIT 1;");

    if (!$data_raw) {
        return null;
    }

    return date_i18n('d \d\e F \d\e Y', strtotime($data_raw));
}
