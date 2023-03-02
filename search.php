<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="wrapper" id="search-wrapper">

    <div class="container" id="content" tabindex="-1">

        <main class="site-main" id="main">

            <div class="page-header">
                <nav aria-label="Você está aqui">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?php echo site_url(); ?>">Início</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"> 
                            Pesquisa
                        </li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-12 col-lg-8 align-self-end">
                        <h1 class="mb-3">Pesquisou por: <?php the_search_query(); ?></h1>
                        <?php if ( get_search_query() !== '' ) : ?>
                            <p style="font-size: 1.125rem;"><?php echo $wp_query->found_posts; ?> <?php echo $wp_query->found_posts == 1 ? 'resultado encontrado' : 'resultados encontrados'; ?></p>
                        <?php else : ?>
                            <p style="font-size: 1.125rem;">0 resultados encontrados</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-none d-lg-flex col-lg-4 d-flex justify-content-center featured-image"> 
                        <div class="featured-image">
                            <span class="icon-xl-search-results icon" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-content">
                <?php if ( have_posts() && get_search_query() !== '' ) : ?>
                    <ul style="padding: 0; margin: 0; list-style: none;">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <li style="padding: 40px 0; border-bottom: 1px solid #333333;">
                            <a href="<?php the_permalink(); ?>">
                                <h3><?php the_title(); ?></h3>
                            </a>
                            <div class="mb-3">
                                <?php echo get_the_modified_date('j \d\e F, Y'); ?>
                            </div>
                            <?php if ( has_excerpt() ) : ?>
                            <div class="excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                    <ul>
                <?php else : ?>
                    <p>Não foram encontrados resultados para esta pesquisa.</p>
                <?php endif; ?>
            </div>

        </main>

    </div>

</div>

<?php
get_footer();