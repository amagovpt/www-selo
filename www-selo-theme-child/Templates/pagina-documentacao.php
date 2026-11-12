<?php
/* Template Name: paginas-de-documentacao */
defined('ABSPATH') || exit;

// CSSs (ajuste os caminhos se necessário)
wp_enqueue_style(
  'documentacao_page',
  get_stylesheet_directory_uri() . '/assets/css/doc-page.css',
  [],
  null
);
wp_enqueue_style(
  'full-width-cinza', // corrigido o handle
  get_stylesheet_directory_uri() . '/assets/css/full-width-cinza.css',
  [],
  '1.0'
);

wp_enqueue_script(
  'page-documentations-js',
  get_stylesheet_directory_uri().'/assets/js/page-documentations.js',
  ['jquery'],
  '1.0',
  true
);

get_header();
?>
<div class="wrapper">
  <div class="container mt-5 mb-5 doc-spa-container">
    <div class="row content_rows">

      <!-- Sidebar -->
      <aside id="sidebarMenu" class="col-md-4 col-lg-3 d-md-block py-4 menu-lateral doc-sp-sidebar">
        <h2 class="sidebar-heading text-muted text-uppercase">Guia para obtenção do Selo</h2>
        <?php
        $menu_slug = function_exists('get_field') ? get_field('selo_indice_de_conteudo_documentacao') : '';
        $menu      = $menu_slug ? wp_get_nav_menu_object($menu_slug) : null;

        if ($menu && function_exists('render_bootstrap_menu')) {
          echo render_bootstrap_menu($menu->term_id, false);
        } else {
          echo '<p><em>Menu não encontrado. Verifique o ACF e a função render_bootstrap_menu().</em></p>';
        }
        ?>
      </aside>

      <!-- Conteúdo principal -->
    <main class="col-md-8 col-lg-9 px-md-9 doc-sp-content">
        <div id="doc-content">
          <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

            <?php
            // 1) tenta no child depois no parent automaticamente
            $full_cinza_path = get_theme_file_path('/templates/full-width-cinza.php');

            // 2) se não existir, tenta variações comuns
            if ( ! file_exists($full_cinza_path) ) {
              $candidates = [
                '/Templates/full-width-cinza.php',
               
              ];
              $full_cinza_path = '';
              foreach ($candidates as $rel) {
                $try = get_theme_file_path($rel);
                if (file_exists($try)) { $full_cinza_path = $try; break; }
              }
            }

            // 3) inclui em modo EMBED ou faz fallback pro conteúdo
            if ($full_cinza_path && file_exists($full_cinza_path)) {
              if (!defined('DOC_SPA_EMBED')) define('DOC_SPA_EMBED', true);
              include $full_cinza_path;
            } else {
              // Fallback: garante que o conteúdo aparece, sem <article>/<h1>
              echo '<div class="entry-content">';
              the_content();
              echo '</div>';
            }
            ?>

          <?php endwhile; else : ?>
            <div class="alert alert-warning">
              <h2>Sem conteúdo</h2>
              <p>Não foram encontrados posts para exibir.</p>
              <p><strong>URL atual:</strong> <?php echo esc_html($_SERVER['REQUEST_URI'] ?? ''); ?></p>
            </div>
          <?php endif; ?>
        </div>
    </main>

    </div>
  </div>
</div>


<?php get_footer(); ?>
