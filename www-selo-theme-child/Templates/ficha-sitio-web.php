<?php
/**
 * Template name: Ficha de sítio web
 *
 * Ficha de identificação de um sítio web ou aplicação móvel com selo,
 * com o aspeto de um cartão de identificação (#9).
 *
 * Endereço: ?idsitio=<id> (sítios web) ou ?idsitio=<id>&tipo=app (aplicações móveis).
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$site_id = absint( get_query_var( 'idsitio' ) );

// As apps levam "tipo=app" no endereço (os ids repetem-se entre sítios web e apps).
$tipo_plataforma = ( 'app' === sanitize_key( $_GET['tipo'] ?? '' ) ) ? AMA_SELO_TIPO_APP : AMA_SELO_TIPO_SITIO_WEB;
$app             = AMA_SELO_TIPO_APP === $tipo_plataforma;

$sitio = $site_id ? dados_site_por_id( $site_id, $tipo_plataforma ) : new WP_Error( 'site_not_found', 'Sem identificador.' );

// Só as plataformas publicadas na lista (as que saíram da fonte deixam de ter ficha).
if ( ! is_wp_error( $sitio ) && empty( $sitio['mostrar'] ) ) {
    $sitio = new WP_Error( 'site_not_found', 'Plataforma não publicada.' );
}

$encontrado = ! is_wp_error( $sitio );

if ( ! $encontrado ) {
    status_header( 404 );
    nocache_headers();
}

// Título da página no separador do navegador.
add_filter( 'document_title_parts', function ( $partes ) use ( $encontrado, $sitio ) {
    $partes['title'] = $encontrado ? $sitio['name'] : 'Plataforma não encontrada';
    return $partes;
} );

/**
 * Data por extenso ("29 de setembro de 2020") ou null se não houver.
 *
 * @param string|null $data Data da tabela (Y-m-d H:i:s).
 * @return string|null
 */
$ficha_data = function ( $data ) {
    $data = ama_selo_normalizar_data( $data );

    return $data ? ama_selo_data_extenso( strtotime( $data . ' UTC' ) ) : null;
};

$nao_disponivel = '<abbr title="não disponível">n.d.</abbr>';

if ( $encontrado ) {
    $selo     = ucfirst( strtolower( $sitio['selo'] ) );
    $url      = esc_url( $sitio['url'] );
    $url_mostrar = $url ? untrailingslashit( preg_replace( '#^https?://#', '', $sitio['url'] ) ) : '';

    $data_selo       = ama_selo_normalizar_data( $sitio['data_selo'] );
    $certificacao    = null;
    if ( $data_selo ) {
        // A certificação é válida por um ano a partir da data do selo.
        $inicio       = strtotime( $data_selo . ' UTC' );
        $fim          = strtotime( '+1 year', $inicio );
        $certificacao = sprintf(
            '<time datetime="%s">%s</time> a <time datetime="%s">%s</time>',
            gmdate( 'Y-m-d', $inicio ),
            esc_html( ama_selo_data_extenso( $inicio ) ),
            gmdate( 'Y-m-d', $fim ),
            esc_html( ama_selo_data_extenso( $fim ) )
        );
    }

    $data_declaracao = $ficha_data( $sitio['data_declaracao'] );

    // Registo no Observatório: as apps não têm (N/A).
    if ( $app ) {
        $observatorio = '<abbr title="não aplicável">N/A</abbr>';
    } elseif ( '' !== trim( (string) $sitio['url_observatorio'] ) ) {
        /**
         * Endereço base do Observatório (o campo url_observatorio traz só o caminho).
         *
         * @param string $base
         */
        $base_observatorio = apply_filters( 'ama_selo_observatorio_url', 'https://observatorio.acessibilidade.gov.pt' );
        $url_observatorio  = preg_match( '#^https?://#', $sitio['url_observatorio'] )
            ? $sitio['url_observatorio']
            : untrailingslashit( $base_observatorio ) . '/' . ltrim( $sitio['url_observatorio'], '/' );
        $observatorio = sprintf( '<a href="%s">%s<span class="visually-hidden"> (no Observatório Português da Acessibilidade Web)</span></a>', esc_url( $url_observatorio ), esc_html( $sitio['name'] ) );
    } else {
        $observatorio = $nao_disponivel;
    }
}

// Folha de estilos
wp_enqueue_style(
    'ficha-sitio-web-style',
    get_stylesheet_directory_uri() . '/assets/css/ficha-sitio-web.css',
    [],
    filemtime( get_stylesheet_directory() . '/assets/css/ficha-sitio-web.css' )
);

get_header();
?>
    <main class="site-main" id="main">
        <div class="wrapper" id="page-wrapper">
            <div class="container-fluid p-0" id="content" tabindex="-1">
            <?php
            // Título, excerto e imagem destacada vêm da página no backoffice (como na Lista de Selos);
            // no breadcrumb, a página atual é a plataforma.
            $cabecalho = [ 'atual' => $encontrado ? $sitio['name'] : 'Plataforma não encontrada' ];
            if ( '' === trim( get_the_title() ) ) {
                $cabecalho['titulo'] = 'Ficha de identificação da plataforma com Selo de Usabilidade e Acessibilidade';
            }
            get_template_part( 'template-parts/cabecalho', 'pagina', $cabecalho );
            ?>
            <div class="page-content overflow-x-hidden">
                <div class="container ficha mb-5">
                    <?php if ( $encontrado ) : ?>
                        <article class="cartao-selo cartao-selo--<?= esc_attr( strtolower( $selo ) ); ?>" aria-labelledby="cartao-selo-nome">
                            <div class="cartao-selo__selo">
                                <img src="<?= esc_url( get_stylesheet_directory_uri() . '/assets/img/selo_' . strtolower( $selo ) . '.svg' ); ?>"
                                     alt="<?= esc_attr( 'Selo ' . $selo . ' de Usabilidade e Acessibilidade' ); ?>"
                                     width="160" height="160">
                                <p class="cartao-selo__nivel" aria-hidden="true">Selo <?= esc_html( $selo ); ?></p>
                            </div>
                            <dl class="cartao-selo__dados">
                                <div>
                                    <dt><?= $app ? 'Aplicação móvel' : 'Sítio Web'; ?>:</dt>
                                    <dd id="cartao-selo-nome"><?= esc_html( $sitio['name'] ); ?></dd>
                                </div>
                                <div>
                                    <dt>URL:</dt>
                                    <dd><?= $url ? sprintf( '<a href="%s">%s</a>', $url, esc_html( $url_mostrar ) ) : $nao_disponivel; ?></dd>
                                </div>
                                <div>
                                    <dt>Proprietário:</dt>
                                    <dd><?= '' !== trim( $sitio['entidade'] ) ? esc_html( $sitio['entidade'] ) : $nao_disponivel; ?></dd>
                                </div>
                                <div>
                                    <dt>Data da declaração de acessibilidade:</dt>
                                    <dd><?= $data_declaracao
                                        ? sprintf( '<time datetime="%s">%s</time>', esc_attr( substr( ama_selo_normalizar_data( $sitio['data_declaracao'] ), 0, 10 ) ), esc_html( $data_declaracao ) )
                                        : $nao_disponivel; ?></dd>
                                </div>
                                <div>
                                    <dt>Certificação Selo:</dt>
                                    <dd><?= $certificacao ?: $nao_disponivel; ?></dd>
                                </div>
                                <div>
                                    <dt>Registo no Observatório:</dt>
                                    <dd><?= $observatorio; ?></dd>
                                </div>
                            </dl>
                        </article>
                    <?php else : ?>
                        <h2>Plataforma não encontrada</h2>
                        <p>Não encontrámos a plataforma pedida na lista de sítios web e aplicações móveis com Selo de Usabilidade e Acessibilidade.</p>
                        <?php if ( has_post_parent() ) : ?>
                            <p><a href="<?= esc_url( get_the_permalink( wp_get_post_parent_id() ) ); ?>">Consultar a lista completa</a></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="page-footer">
                <div class="container d-flex justify-content-between pb-5">
                    <div class="share-post">
                        <h2>Partilhar</h2>
                        <ul class="d-flex list-inline mb-0">
                            <li>
                                <button id="fb-icon" type="button" aria-label="Partilhar no Facebook" >
                                    <span class="icon-share-facebook" aria-hidden="true"></span>
                                </button>
                            </li>
                            <li>
                                <button id="tw-icon" type="button" aria-label="Partilhar no Twitter">
                                    <span class="icon-share-twitter" aria-hidden="true"></span>
                                </button>
                            </li>
                            <li>
                                <button id="ld-icon" type="button" aria-label="Partilhar no Linkedin">
                                    <span class="icon-share-linkedin" aria-hidden="true"></span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="last-update py-2">
                            <p class="mb-0">Última atualização: <?= esc_html( data_de_atualizacao_sitios_web_com_selo() ); ?></p>
                        </div>
                </div>
            </div>
        </div>
    </main>
<?php
get_footer();
