<?php
/* Template Name: Template Base com Menu */
wp_enqueue_style(
  "documentacao_page",
  get_stylesheet_directory_uri() . "/assets/css/doc-page.css",
  [],
  ""
);
get_header();
?>

<div class="wrapper">
<div class="container mt-5 mb-5 doc-spa-container">
  <div class="row content_rows">
    <!-- Sidebar -->
    <div id="sidebarMenu" class="col-md-4 col-lg-3 d-md-block py-4 menu-lateral doc-sp-sidebar">
      <h6 class="sidebar-heading text-muted text-uppercase">Guia para obtenção do Selo</h6>

      <?php
      $menu_slug = get_field('selo_indice_de_conteudo_documentacao');
      $menu = wp_get_nav_menu_object($menu_slug);

      if ($menu) {
        echo render_bootstrap_menu($menu->term_id, false); 
      } else {
        echo '<p><em>Menu não encontrado. Verifique o slug no campo ACF.</em></p>';
      }
      ?>
    </div>

    <!-- Conteúdo principal -->
    <main class="col-md-8 ms-sm-auto col-lg-9 px-md-4">
      <div id="doc-content" class="container">
        <?php
        // Verificar se há uma página específica na URL (último segmento do pathname)
        $current_url = $_SERVER['REQUEST_URI'];
        $path_segments = explode('/', trim($current_url, '/'));
        $page_slug = end($path_segments);
        
        // Se o slug não for vazio e não for o slug da página atual
        if ($page_slug && $page_slug !== '' && $page_slug !== get_post_field('post_name', get_the_ID())) {
          // Buscar a página pelo slug
          $page = get_page_by_path($page_slug);
          if ($page) {
            echo '<div class="entry-content">';
            echo apply_filters('the_content', $page->post_content);
            echo '</div>';
          } else {
            echo '<p><em>Página não encontrada: ' . esc_html($page_slug) . '</em></p>';
          }
        } else {
          // Mostrar conteúdo da página atual se não houver slug específico
          while (have_posts()) : the_post();
            echo '<div class="entry-content">';
            the_content();
            echo '</div>';
          endwhile;
        }
        ?>
      </div>
    </main>
  </div>
</div>
</div>

<script>
  window.onload = function() {
    const $ = jQuery;

    // Função para extrair slug da URL
    function extractSlugFromUrl(url) {
      let cleanUrl = url.replace(/^\/+|\/+$/g, '');
      cleanUrl = cleanUrl.split('#')[0];
      
      if (cleanUrl.includes('http')) {
        const urlParts = cleanUrl.split('/');
        for (let i = urlParts.length - 1; i >= 0; i--) {
          if (urlParts[i] && urlParts[i] !== '') {
            return urlParts[i];
          }
        }
        return urlParts[urlParts.length - 2] || cleanUrl;
      }
      
      return cleanUrl;
    }

    // Função para encontrar link por slug
    function findLinkBySlug(slug) {
      return $('#sidebarMenu a').filter(function() {
        const href = $(this).attr('href');
        let hrefSlug = extractSlugFromUrl(href);
        
        if (!hrefSlug || hrefSlug === '' || href.startsWith('#')) {
          hrefSlug = $(this).text().trim().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        }
        
        return hrefSlug === slug;
      });
    }

    // Função para marcar link ativo
    function markActiveLink() {
      $('#sidebarMenu a').removeClass('active');
      
      // Extrair slug da URL atual (último segmento do pathname)
      const pathSegments = window.location.pathname.split('/').filter(segment => segment !== '');
      const currentSlug = pathSegments[pathSegments.length - 1];
      
      if (currentSlug && currentSlug !== '') {
        const $targetLink = findLinkBySlug(currentSlug);
        if ($targetLink.length) {
          $targetLink.addClass('active');
          
          // Expandir accordion se necessário
          const $accordionItem = $targetLink.closest('.accordion-collapse');
          if ($targetLink.closest('.accordion-collapse').length) {
            $accordionItem.addClass('show');
            $accordionItem.prev().find('.accordion-button').removeClass('collapsed');
          }
        }
      } else {
        // Marcar primeiro link como ativo se não houver slug na URL
        const $firstLink = $('#sidebarMenu a').first();
        if ($firstLink.length) {
          $firstLink.addClass('active');
        }
      }
    }

    // Marcar link ativo ao carregar a página
    markActiveLink();

    // Listener para cliques no menu
    $('#sidebarMenu').on('click', 'a', function(e) {
      e.preventDefault();
      
      const href = $(this).attr('href');
      const slug = extractSlugFromUrl(href);
      
      // Redirecionar diretamente para a página em vez de usar AJAX
      window.location.href = href;
    });



    // Garantir que o menu seja sempre visível
    if (window.innerWidth <= 767.98) {
      $('#sidebarMenu').show();
      $('.menu-lateral').show();
    }

    // Listener para redimensionamento da janela
    $(window).on('resize', function() {
      if (window.innerWidth <= 767.98) {
        $('#sidebarMenu').show();
        $('.menu-lateral').show();
      }
    });
  };
</script>

<?php get_footer(); ?>
