<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

wp_enqueue_style(
    "full-wdith-cinza",
    get_stylesheet_directory_uri() . "/assets/css/full-width-cinza.css",
    "",
    "1.0"
);

wp_enqueue_style(
    "facilitador",
    get_stylesheet_directory_uri() . "/assets/css/facilitador.css",
    "",
    "1.0"
);

get_header();
?>
    <main class="site-main" id="main">
        <div class="wrapper full-width-cinza" id="page-wrapper">
            <div class="container-fluid px-0" id="content" tabindex="-1">
                <div class="container">
                    <div class="row">
                        <div class="page-header col">
                            <nav aria-label="Você está aqui">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="<?= site_url(); ?>">Início</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="<?= site_url("facilitadores"); ?>">Facilitadores</a>
                                    </li>
                                    <?php if ( has_post_parent() ) : ?>
                                        <?php $post_parent_id = wp_get_post_parent_id(); ?>
                                        <li class="breadcrumb-item">
                                            <a href="<?php echo get_the_permalink( $post_parent_id ); ?>"><?php echo get_the_title( $post_parent_id ); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        <?php echo the_title(); ?>
                                    </li>
                                </ol>
                            </nav>
                            <div class="row">
                                <div class="col-12 col-lg-8 d-flex flex-column justify-content-center <?php echo has_excerpt() ? '' : ' align-self-center'; ?>">
                                    <h1><?= ama_titulo_h1(); ?></h1>
                                    <?php
                                    if ( has_excerpt() ) : ?>
                                        <div class="excerpt">
                                            <?php the_excerpt(); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex flex-row align-items-center justify-content-start mt-4">
                                        <h2 class="h5 me-2">Seguir em:</h2>
                                        <?php
                                        $linkedin = get_field("facilitador_linkedin");
                                        if($linkedin && !empty($linkedin)) {
                                            ?>
                                            <ul class="d-flex list-inline mb-0">
                                                <li>
                                                    <a class="facilitador-rs-link" href="<?= $linkedin ?>" target="_blank" title="Ir para o Linkedin do facilitador">
                                                        <span class="icon-share-linkedin" aria-hidden="true"></span>
                                                    </a>
                                                </li>
                                            </ul>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                                if ( has_post_thumbnail() ) : ?>
                                    <div class="d-none d-lg-flex col-lg-4 d-flex justify-content-center featured-image">
                                        <div class="featured-image">
                                            <?php the_post_thumbnail("medium"); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="page-content my-5 mb-0">
                    <div class="biografia container p-5">
                        <h2 class="mb-4">Biografia</h2>
                        <div class="container px-0">
                            <div class="row">
                                <div class="col-lg-8">
                                    <?php the_content(); ?>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <h3 class="h5">
                                        Facilitador certificado em:
                                    </h3>
                                    <?php
                                    $certificacao = get_field("facilitador_certificacao_tipo_selo");
                                    if($certificacao && !empty($certificacao)) {
                                        switch ($certificacao) {
                                            case "bronze_prata":
                                                $certificacao = "prata";
                                                $alt = " níveis Bronze e Prata";
                                                break;

                                            case "ouro":
                                                $certificacao = "ouro";
                                                $alt = " nível ouro";
                                                break;

                                            default:
                                                $certificacao = "prata";
                                                $alt = " níveis Bronze e Prata";
                                                break;
                                        }

                                        echo '<img width="150" src="' . get_stylesheet_directory_uri() . '/assets/img/selo_' . $certificacao . '.png" alt="Selo ' . $alt . '" class="img-fluid" />';
                                    }?>
                                </div>
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
                            <p class="mb-0">Última atualização: <?php echo get_the_modified_date('j \d\e F, Y'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
get_footer();