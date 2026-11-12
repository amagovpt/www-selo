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
        <div class="container">
          <div class="row">
            <div class="page-header col">
              <nav aria-label="Você está aqui">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="<?php echo esc_url(site_url()); ?>">Início</a>
                  </li>
                  <?php
                  $parent_id = wp_get_post_parent_id(get_the_ID());
                  if ($parent_id) : ?>
                    <li class="breadcrumb-item">
                      <a href="<?php echo esc_url(get_the_permalink($parent_id)); ?>">
                        <?php echo esc_html(get_the_title($parent_id)); ?>
                      </a>
                    </li>
                  <?php endif; ?>
                  <li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
                </ol>
              </nav>

              <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <div class="row">
                  <div class="col-12 col-lg-8<?php echo has_excerpt() ? '' : ' align-self-end'; ?>">
                    <h1><?php the_title(); ?></h1>
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
              <?php endwhile; endif; ?>
<?php
            // fecha wrappers
            echo '</div></div></div></div></div></main>';
            get_footer();
            return;
          }
          // ====== MODO EMBED (chamado dentro do doc-spa) — sem Loop ======
?>
<div class="page-header">
  <div class="row">
    <div class="col-12 col-lg-8<?php echo has_excerpt() ? '' : ' align-self-end'; ?>">
      <h1><?php the_title(); ?></h1>
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
