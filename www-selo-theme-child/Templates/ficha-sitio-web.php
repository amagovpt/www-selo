<?php
/**
 * Template name: Ficha de sítio web
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Lê o ‘ID’ do sítio ‘web’ da URL. Por defeito mostra o 1.
$site_id = get_query_var('idsitio');

if(!is_numeric($site_id) || $site_id === '') {
    $site_id = "1";
}

$sitio = dados_site_por_id($site_id);

// Folha de estilos
wp_enqueue_style(
    'ficha-sitio-web-style',
    get_stylesheet_directory_uri() . '/assets/css/ficha-sitio-web.css',
    '',
    '1.0.0',
);

get_header();
?>
    <main class="site-main" id="main">
        <div class="wrapper" id="page-wrapper">
            <div class="container-fluid p-0" id="content" tabindex="-1">
            <div class="page-header">
                <div class="container">
                    <nav aria-label="Você está aqui">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="<?= site_url(); ?>">Início</a>
                            </li>
                            <?php if ( has_post_parent() ) : ?>
                                <?php $post_parent_id = wp_get_post_parent_id(); ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= get_the_permalink( $post_parent_id ); ?>">
                                        <?= get_the_title( $post_parent_id ); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= $sitio["name"] ?>
                            </li>
                        </ol>
                    </nav>
                    <div class="row">
                        <div class="col-12 col-lg-8<?= has_excerpt() ? '' : ' align-self-end'; ?>">
                            <h1><?php
                                if ($site_id) {
                                    echo $sitio["name"];
                                }
                                ?></h1>
                            <p class="h3 bold">
                                <a href="<?= $sitio["url"]?>" title="URL do site: <?= $sitio["url"]?>"><?= $sitio["url"]?></a>
                            </p>
                        </div>
                        <?php
                        if ( has_post_thumbnail() ) : ?>
                            <div class="d-none d-lg-flex col-lg-4 d-flex justify-content-center featured-image">
                                <div class="featured-image">
                                    <?php the_post_thumbnail(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="page-content overflow-x-hidden">
                <div class="container ficha mb-5">
                    <div class="row g-5">
                        <div class="col-md-9 order-lg-2">
                            <div class="detalhes-wrapper">
                                <ul class="item detalhes-list">
                                    <li class="detalhes-list-item">
                                        <h3 class="h5">Entidade:</h3>
                                        <div><?= $sitio["entidade"] ?></div>
                                    </li>
                                    <li class="detalhes-list-item">
                                        <h3 class="h5">Observatório:</h3>
                                        <p class="mb-0">Ver mais informação no sítio web do <a href="https://observatorio.acessibilidade.gov.pt<?= $sitio["url_observatorio"]; ?>">Observátorio</a></p>
                                    </li>
                                </ul>
                                <ul class="item detalhes-list">
                                    <li class="detalhes-list-item">
                                        <h3 class="h5">Certificação efetuada em:</h3>
                                        <div><?= data_extenso($sitio["data_selo"]); ?></div>
                                    </li>
                                    <li class="detalhes-list-item">
                                        <h3 class="h5">Data da declaração:</h3>
                                        <div><?= data_extenso($sitio["data_declaracao"]); ?></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3 order-lg-1">
                            <h2 class="h5 mb-3">Certificação com:</h2>
                            <?php
                            // Mostra o selo conforme a classificação do sítio web
                            switch(strtolower($sitio["selo"])){
                                case "ouro":
                                    echo '<img src="' . get_stylesheet_directory_uri() . '/assets/img/selo_ouro.png" alt="Selo Ouro de Usabilidade e Acessibilidade" class="img-fluid" width="150">';
                                    break;

                                case "prata":
                                    echo '<img src="' . get_stylesheet_directory_uri() . '/assets/img/selo_prata.png" alt="Selo Prata de Usabilidade e Acessibilidade" class="img-fluid" width="150">';
                                    break;

                                case "bronze":
                                    echo '<img src="' . get_stylesheet_directory_uri() . '/assets/img/selo_bronze.png" alt="Selo Bronze de Usabilidade e Acessibilidade" class="img-fluid" width="150">';
                                    break;

                                default:
                                    echo '';
                                    break;
                            }
                            ?>
                        </div>
                    </div>
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
                            <p class="mb-0">Última atualização: <?= data_de_atualizacao_sitios_web_com_selo(); ?></p>
                        </div>
                </div>
            </div>
        </div>
    </main>
<?php
get_footer();