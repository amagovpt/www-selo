<?php

/*
 * Template para exibir a página agregadora de artigos do site (Blogue, #4).
 *
 * Mesma estrutura do blogue do acessibilidade.gov.pt: destaque principal,
 * outros destaques em cartões, lista de artigos paginada e "Mais lidos".
 * A lógica (destaques, paginação, visualizações) está em inc/blogue.php.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

wp_enqueue_style(
    'ama-blogue',
    get_stylesheet_directory_uri() . '/assets/css/blogue.css',
    [],
    filemtime( get_stylesheet_directory() . '/assets/css/blogue.css' )
);

get_header();
$e_blogue     = is_home();
$page_id      = $e_blogue ? (int) get_option( 'page_for_posts' ) : 0;
$titulo       = $page_id ? get_the_title( $page_id ) : wp_strip_all_tags( get_the_archive_title() );
$pagina_atual = max( 1, (int) get_query_var( 'paged' ) );
// Destaques só na página do blogue (este template também serve categorias e etiquetas).
$destaques    = $e_blogue ? ama_blogue_destaques() : [];
?>
<main class="site-main" id="main">
    <div class="wrapper" id="page-wrapper">
        <div class="container" id="content" tabindex="-1">
            <div class="page-header">
                <nav aria-label="Você está aqui">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= esc_url( home_url( '/' ) ); ?>">Início</a>
                        </li>
                        <?php if ( $page_id && has_post_parent( $page_id ) ) : ?>
                            <?php $post_parent_id = wp_get_post_parent_id( $page_id ); ?>
                            <li class="breadcrumb-item">
                                <a href="<?= esc_url( get_the_permalink( $post_parent_id ) ); ?>"><?= esc_html( get_the_title( $post_parent_id ) ); ?></a>
                            </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= esc_html( $titulo ); ?>
                        </li>
                    </ol>
                </nav>
                <div class="row">
                    <div class="col-12 col-lg-8<?= $page_id && has_excerpt( $page_id ) ? '' : ' align-self-end'; ?>">
                        <h1><?= esc_html( $titulo ); ?><?= $pagina_atual > 1 ? '<span class="visually-hidden">, página ' . $pagina_atual . '</span>' : ''; ?></h1>
                        <?php if ( $page_id && has_excerpt( $page_id ) ) : ?>
                            <div class="excerpt">
                                <?= wp_kses_post( get_the_excerpt( $page_id ) ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ( $page_id && has_post_thumbnail( $page_id ) ) : ?>
                        <div class="d-none d-lg-flex col-lg-4 d-flex justify-content-center featured-image">
                            <div class="featured-image">
                                <?= get_the_post_thumbnail( $page_id ); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="page-content blogue-agregador">
                <?php if ( 1 === $pagina_atual && $destaques ) : ?>
                    <?php $principal = get_post( $destaques[0] ); ?>
                    <section class="blogue-destaque" aria-labelledby="blogue-destaque-titulo">
                        <h2 class="visually-hidden" id="blogue-destaque-titulo">Destaque principal</h2>
                        <div class="blogue-destaque__grelha">
                            <div class="blogue-destaque__texto">
                                <h3 class="h2 mb-2"><a href="<?= esc_url( get_permalink( $principal ) ); ?>"><?= esc_html( get_the_title( $principal ) ); ?></a></h3>
                                <p class="blogue-data"><time datetime="<?= esc_attr( get_the_date( 'Y-m-d', $principal ) ); ?>"><?= esc_html( get_the_date( 'j \d\e F, Y', $principal ) ); ?></time></p>
                                <p><?= esc_html( ama_blogue_resumo( $principal, 25 ) ); ?></p>
                                <a class="ama btn btn-secondary" href="<?= esc_url( get_permalink( $principal ) ); ?>">Ler mais<span class="visually-hidden">: <?= esc_html( get_the_title( $principal ) ); ?></span></a>
                            </div>
                            <?php if ( has_post_thumbnail( $principal ) ) : ?>
                                <div class="blogue-destaque__imagem">
                                    <?= get_the_post_thumbnail( $principal, 'large', [ 'class' => 'img-fluid', 'alt' => '' ] ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <?php $outros = array_slice( $destaques, 1 ); ?>
                    <?php if ( $outros ) : ?>
                        <section class="blogue-outros" aria-labelledby="blogue-outros-titulo">
                            <h2 class="visually-hidden" id="blogue-outros-titulo">Outros destaques</h2>
                            <div class="blogue-outros__grelha">
                                <?php
                                global $post;
                                foreach ( $outros as $outro_id ) {
                                    $post = get_post( $outro_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
                                    setup_postdata( $post );
                                    get_template_part( 'template-parts/post', 'card' );
                                }
                                wp_reset_postdata();
                                ?>
                            </div>
                        </section>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="row blogue-conteudo">
                    <section class="col-12 col-lg-8 blogue-lista" aria-labelledby="blogue-lista-titulo">
                        <h2 class="visually-hidden" id="blogue-lista-titulo">Lista de artigos</h2>
                        <?php if ( have_posts() ) : ?>
                            <ul class="list-unstyled blogue-lista__itens">
                                <?php while ( have_posts() ) : the_post(); ?>
                                    <li class="blogue-lista__item">
                                        <h3 class="h5"><a href="<?= esc_url( get_permalink() ); ?>"><?= esc_html( get_the_title() ); ?></a></h3>
                                        <p class="blogue-data"><time datetime="<?= esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?= esc_html( get_the_date( 'j \d\e F, Y' ) ); ?></time></p>
                                        <p><?= esc_html( ama_blogue_resumo( get_post(), 30 ) ); ?></p>
                                        <a class="ama btn btn-secondary" href="<?= esc_url( get_permalink() ); ?>">Ler mais<span class="visually-hidden">: <?= esc_html( get_the_title() ); ?></span></a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                            <?= ama_paginate_links( (int) $GLOBALS['wp_query']->max_num_pages, 'Seletor de páginas do blogue' ); ?>
                        <?php elseif ( 1 === $pagina_atual && $destaques ) : ?>
                            <p>Não há mais artigos.</p>
                        <?php else : ?>
                            <p>Não foram encontrados artigos.</p>
                        <?php endif; ?>
                    </section>

                    <aside class="col-12 col-lg-4 blogue-lateral" aria-labelledby="blogue-mais-lidos-titulo">
                        <div class="blogue-mais-lidos">
                            <h2 class="h4" id="blogue-mais-lidos-titulo">Mais lidos</h2>
                            <ul class="most-read">
                                <?php foreach ( ama_blogue_mais_lidos( 4 ) as $lido ) : ?>
                                    <li class="item">
                                        <h3 class="h5"><a href="<?= esc_url( get_permalink( $lido ) ); ?>"><?= esc_html( get_the_title( $lido ) ); ?></a></h3>
                                        <p class="blogue-data mb-0"><time datetime="<?= esc_attr( get_the_date( 'Y-m-d', $lido ) ); ?>"><?= esc_html( get_the_date( 'j \d\e F, Y', $lido ) ); ?></time></p>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </aside>
                </div>
            </div>
            <div class="page-footer">
                <div class="d-flex justify-content-between">
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
                        <?php if ( $page_id ) : ?>
                            <p class="mb-0">Última atualização: <?= esc_html( get_the_modified_date( 'j \d\e F, Y', $page_id ) ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
get_footer();
