<?php
/**
 * Template name: Full width Cinza
 */
defined('ABSPATH') || exit;

// Flag: está embebido no doc-spa?
$__embed = defined('DOC_SPA_EMBED') && DOC_SPA_EMBED === true;

// CSS sempre, mesmo em embed
wp_enqueue_style(
  'full-wdith-cinza',
  get_stylesheet_directory_uri() . '/assets/css/full-width-cinza.css',
  [],
  '1.0'
);

if (!$__embed) {
  get_header(); ?>
  <main class="site-main" id="main">
    <div class="wrapper full-width-cinza" id="page-wrapper">
      <div class="container-fluid px-0" id="content" tabindex="-1">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
          <?php get_template_part( 'template-parts/cabecalho', 'pagina' ); ?>
        <div class="container">
          <div class="row">
            <div class="col">
                <div class="page-content">
                  <?php the_content(); ?>
                </div>

                <div class="page-footer">
                  <div class="container d-flex justify-content-between py-5">
                    <div class="share-post">
                      <h2>Partilhar</h2>
                      <ul class="d-flex list-inline mb-0">
                        <li><button id="fb-icon" type="button" aria-label="Partilhar no Facebook"><span class="icon-share-facebook" aria-hidden="true"></span></button></li>
                        <li><button id="tw-icon" type="button" aria-label="Partilhar no Twitter"><span class="icon-share-twitter" aria-hidden="true"></span></button></li>
                        <li><button id="ld-icon" type="button" aria-label="Partilhar no Linkedin"><span class="icon-share-linkedin" aria-hidden="true"></span></button></li>
                      </ul>
                    </div>
                    <div class="last-update py-2">
                      <p class="mb-0">Última atualização: <?php echo esc_html(get_the_modified_date('j \d\e F, Y')); ?></p>
                    </div>
                  </div>
                </div>
<?php
            // fecha wrappers
            echo '</div></div></div>';
          endwhile; endif;
            echo '</div></div></main>';
            get_footer();
            return;
          }
          // ====== MODO EMBED (chamado dentro do doc-spa) — sem Loop ======
?>
<div class="page-header">
  <div class="row">
    <div class="col-12 col-lg-8<?php echo has_excerpt() ? '' : ' align-self-end'; ?>">
      <h1><?= ama_titulo_h1(); ?></h1>
      <?php if (has_excerpt()) : ?>
        <div class="excerpt"><?php the_excerpt(); ?></div>
      <?php endif; ?>
    </div>
    <?php if (has_post_thumbnail()) : ?>
      <div class="d-none d-lg-flex col-lg-4 d-flex justify-content-center featured-image">
        <div class="featured-image"><?php the_post_thumbnail('full', ['loading' => 'lazy', 'decoding' => 'async']); ?></div>
      </div>
    <?php endif; ?>
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
        <li><button id="fb-icon" type="button" aria-label="Partilhar no Facebook"><span class="icon-share-facebook" aria-hidden="true"></span></button></li>
        <li><button id="tw-icon" type="button" aria-label="Partilhar no Twitter"><span class="icon-share-twitter" aria-hidden="true"></span></button></li>
        <li><button id="ld-icon" type="button" aria-label="Partilhar no Linkedin"><span class="icon-share-linkedin" aria-hidden="true"></span></button></li>
      </ul>
    </div>
    <div class="last-update py-2">
      <p class="mb-0">Última atualização: <?php echo esc_html(get_the_modified_date('j \d\e F, Y')); ?></p>
    </div>
  </div>
</div>
