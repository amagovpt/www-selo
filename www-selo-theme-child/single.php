<?php
/**
 * Artigo do blogue (#5): mesmo template dos artigos do acessibilidade.gov.pt.
 *
 * Cabeçalho branco (sem a faixa azul das páginas), etiqueta "Blogue", título,
 * data, imagem destacada, conteúdo, "Relacionados" ao lado, categorias e
 * etiquetas, partilha e navegação para o artigo anterior/seguinte.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Só para artigos; outros tipos de conteúdo sem template próprio usam o do tema pai.
if ( 'post' !== get_post_type() ) {
    require get_template_directory() . '/single.php';
    return;
}

wp_enqueue_style(
    'ama-artigo',
    get_stylesheet_directory_uri() . '/assets/css/artigo.css',
    [],
    filemtime( get_stylesheet_directory() . '/assets/css/artigo.css' )
);

get_header();

while ( have_posts() ) :
    the_post();

    $blog_page_id = (int) get_option( 'page_for_posts' );
    $blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/blogue/' );
    $blog_titulo  = $blog_page_id ? get_the_title( $blog_page_id ) : 'Blogue';

    $categorias = get_the_category();
    $etiquetas  = get_the_tags() ?: [];
    $relacionados = ama_artigo_relacionados( get_the_ID(), wp_list_pluck( $categorias, 'term_id' ) );
    ?>
    <main class="site-main" id="main">
        <div class="wrapper artigo" id="single-wrapper">
            <div class="container" id="content" tabindex="-1">
                <div class="page-header">
                    <nav aria-label="Você está aqui">
                        <ol class="breadcrumb mb-5">
                            <li class="breadcrumb-item">
                                <a href="<?= esc_url( home_url( '/' ) ); ?>">Início</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?= esc_url( $blog_url ); ?>"><?= esc_html( $blog_titulo ); ?></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= esc_html( get_the_title() ); ?>
                            </li>
                        </ol>
                    </nav>
                </div>
                <article class="page-content">
                    <div class="row gx-4 gx-lg-5">
                        <div class="col-12 col-md-8">
                            <a href="<?= esc_url( $blog_url ); ?>" class="version">Blogue<span class="visually-hidden">: consultar todos os artigos</span></a>
                            <h1 class="mb-2"><?php the_title(); ?></h1>
                            <p class="last-update py-2 mb-0">
                                <time datetime="<?= esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?= esc_html( get_the_date( 'j \d\e F, Y' ) ); ?></time>
                            </p>
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="post featured-image">
                                    <?php the_post_thumbnail( 'large', [ 'class' => 'img-fluid' ] ); ?>
                                </div>
                            <?php endif; ?>
                            <hr class="my-3 my-lg-5">
                            <?php the_content(); ?>
                        </div>
                        <aside class="col-12 col-md-4" aria-labelledby="artigo-relacionados">
                            <div class="sidebar p-4 p-lg-5">
                                <h2 id="artigo-relacionados">Relacionados</h2>
                                <ul class="sidebar-list">
                                    <?php foreach ( $relacionados as $relacionado ) : ?>
                                        <li class="item">
                                            <h3><a href="<?= esc_url( get_permalink( $relacionado ) ); ?>"><?= esc_html( get_the_title( $relacionado ) ); ?></a></h3>
                                            <div class="post-date"><time datetime="<?= esc_attr( get_the_date( 'Y-m-d', $relacionado ) ); ?>"><?= esc_html( get_the_date( 'j \d\e F, Y', $relacionado ) ); ?></time></div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </aside>
                    </div>

                    <?php if ( $categorias || $etiquetas ) : ?>
                        <div class="row mt-3 mt-lg-5 artigo-termos">
                            <?php if ( $categorias ) : ?>
                                <div class="col-12">
                                    <h2 class="h5">Categorias</h2>
                                    <ul class="list-unstyled list-inline">
                                        <?php foreach ( $categorias as $categoria ) : ?>
                                            <li class="list-inline-item"><a href="<?= esc_url( get_category_link( $categoria ) ); ?>"><?= esc_html( $categoria->name ); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ( $etiquetas ) : ?>
                                <div class="col-12 mt-3">
                                    <h2 class="h5">Etiquetas</h2>
                                    <ul class="list-unstyled list-inline">
                                        <?php foreach ( $etiquetas as $etiqueta ) : ?>
                                            <li class="list-inline-item"><a href="<?= esc_url( get_tag_link( $etiqueta ) ); ?>"><?= esc_html( $etiqueta->name ); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </article>
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
                            <p class="mb-0">Última atualização a <?= esc_html( get_the_modified_date( 'j \d\e F, Y' ) ); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $anterior = get_previous_post();
            $seguinte = get_next_post();
            ?>
            <?php if ( $anterior || $seguinte ) : ?>
                <nav class="container" aria-label="Outros artigos">
                    <hr>
                    <div class="post-navigation">
                        <?php if ( $anterior ) : ?>
                            <div class="artigo-anterior">
                                <div>Artigo anterior</div>
                                <a href="<?= esc_url( get_permalink( $anterior ) ); ?>" rel="prev"><?= esc_html( get_the_title( $anterior ) ); ?></a>
                            </div>
                        <?php endif; ?>
                        <?php if ( $seguinte ) : ?>
                            <div class="artigo-seguinte">
                                <div>Artigo seguinte</div>
                                <a href="<?= esc_url( get_permalink( $seguinte ) ); ?>" rel="next"><?= esc_html( get_the_title( $seguinte ) ); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif; ?>
        </div>
    </main>
    <?php
endwhile;

get_footer();
