<?php

/**
 * Template name: Lista de Sítios Web com Selo
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

wp_enqueue_style(
    "full-wdith-cinza",
    get_stylesheet_directory_uri()."/assets/css/full-width-cinza.css",
    "",
    "1.0"
);

get_header(); ?>
    <main class="site-main" id="main">
        <div class="wrapper full-width-cinza" id="page-wrapper">
            <div class="container-fluid px-0" id="content" tabindex="-1">
                <div class="container">
                    <div class="row">
                        <div class="page-header col">
                            <nav aria-label="Você está aqui">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="<?php echo site_url(); ?>">Início</a>
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
                                <div class="col-12 col-lg-8<?php echo has_excerpt() ? '' : ' align-self-end'; ?>">
                                    <h1><?php the_title(); ?></h1>
                                    <?php
                                    if ( has_excerpt() ) : ?>
                                        <div class="excerpt">
                                            <?php the_excerpt(); ?>
                                        </div>
                                    <?php endif; ?>
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
                </div>

                <div class="page-content">
                    <?php the_content(); ?>
                </div>
                <div class="page-footer">
                    <div class="container d-flex justify-content-between py-5">
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
        </div>
    </main>
<?php
get_footer();