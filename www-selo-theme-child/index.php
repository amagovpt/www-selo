<?php

/*
 * Template para exibir a página agregadora de artigos do site.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
$page_id = get_queried_object_id();
?>
<main class="site-main" id="main">
    <div class="wrapper" id="page-wrapper">
        <div class="container" id="content" tabindex="-1">
            <div class="page-header">
                <nav aria-label="Você está aqui">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo site_url(); ?>">Início</a>
                        </li>
                        <?php if ( has_post_parent($page_id) ) : ?>
                            <?php $post_parent_id = wp_get_post_parent_id($page_id); ?>
                            <li class="breadcrumb-item">
                                <a href="<?php echo get_the_permalink( $post_parent_id ); ?>"><?php echo get_the_title( $post_parent_id ); ?></a>
                            </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php echo get_the_title($page_id); ?>
                        </li>
                    </ol>
                </nav>
                <div class="row">
                    <div class="col-12 col-lg-8<?php echo has_excerpt($page_id) ? '' : ' align-self-end'; ?>">
                        <h1><?= get_the_title($page_id); ?></h1>
                        <?php
                        if ( has_excerpt($page_id) ) : ?>
                            <div class="excerpt">
                                <?= get_the_excerpt($page_id); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    if ( has_post_thumbnail($page_id) ) : ?>
                        <div class="d-none d-lg-flex col-lg-4 d-flex justify-content-center featured-image">
                            <div class="featured-image">
                                <?= get_the_post_thumbnail($page_id); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="page-content">
                <?php
                $args = array(
                    "post_type"         => "post",
                    "post_status"       => "publish",
                    "posts_per_page"    => 10,
                    "orderby"           => "date",
                    "order"             => "DESC",
                    "paged"             => get_query_var("paged") ? get_query_var("paged") : 1,
                );

                $posts = new WP_Query($args);

                if ( $posts->have_posts()) {

                    ob_start();
                    $counter = 0;
                    while ( $posts->have_posts()) {
                        $posts->the_post();
                        // check if it's first post and get a different template
                        if($counter == 0) {
                            get_template_part('template-parts/destaque', 'post');
                        } else {
                            get_template_part('template-parts/list-item', 'post');
                        }
                        $counter++;
                    }

                    echo sprintf('<ul class="list-unstyled blogue">%s</ul>', ob_get_clean());
                } else {
                    echo '<p>Não foram encontrados artigos.</p>';
                }

                ?>
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
                        <p class="mb-0">Última atualização: <?php echo get_the_modified_date('j \d\e F, Y'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
get_footer();


