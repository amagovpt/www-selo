<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="site-main" id="main">
    <div class="wrapper" id="page-wrapper">
        <div id="content" tabindex="-1">
            <?php get_template_part( 'template-parts/cabecalho', 'pagina' ); ?>
            <div class="container">
            <div class="page-content">
                <?php the_content(); ?>
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
    </div>
</main>
<?php
get_footer();