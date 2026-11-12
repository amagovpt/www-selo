<?php
/**
 * Template Name: Documentação V2
 */
defined('ABSPATH') || exit;

wp_enqueue_style(
    'doc-v2-style',
    get_stylesheet_directory_uri() . '/assets/css/doc-v2.css',
    [],
    '1.1'
);

wp_enqueue_script(
    'doc-v2-script',
    get_stylesheet_directory_uri() . '/assets/js/doc-v2.js',
    [],
    '1.0',
    true // Load in footer
);

get_header();
?>

<div class="wrapper">
    <div class="container mt-5 mb-5">
        
        <!-- Breadcrumb Navigation -->
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
        
        <div class="container-fluid doc-v2-container">
            <div class="row pt-4">

                <!-- Sidebar Navigation -->
                <nav id="docSidebarNav" class="col-md-4 col-lg-3 pb-4 doc-sidebar-wrapper" aria-label="Índice de conteúdo">
                    
                    <!-- Filter Checkboxes -->
                    <fieldset class="doc-filter-fieldset">
                        <legend class="doc-filter-legend">Tipo de candidatura</legend>
                        <div class="doc-filter-checkboxes">
                            <label class="doc-filter-label">
                                <input type="checkbox" id="filter-sitios-web" class="doc-filter-checkbox" data-filter-type="sitios-web" checked>
                                <span>Sítios Web</span>
                            </label>
                            <label class="doc-filter-label">
                                <input type="checkbox" id="filter-aplicacoes-moveis" class="doc-filter-checkbox" data-filter-type="aplicacoes-moveis" checked>
                                <span>Aplicações Móveis</span>
                            </label>
                        </div>
                    </fieldset>
                    
                    <div class="doc-sidebar-inner">
                    <?php
                    // Tenta obter o menu definido no ACF
                    $menu_slug = function_exists('get_field') ? get_field('selo_indice_de_conteudo_documentacao') : '';
                
                // 1. Tenta obter o menu definido no ACF da página atual
                $menu_slug = function_exists('get_field') ? get_field('selo_indice_de_conteudo_documentacao') : '';
                
                // 2. Se não houver na página atual, tenta obter dos pais (herança)
                if (!$menu_slug) {
                    $current_post_id = get_the_ID();
                    while ($current_post_id) {
                        $parent_id = wp_get_post_parent_id($current_post_id);
                        if ($parent_id) {
                            $parent_menu_slug = function_exists('get_field') ? get_field('selo_indice_de_conteudo_documentacao', $parent_id) : '';
                            if ($parent_menu_slug) {
                                $menu_slug = $parent_menu_slug;
                                break;
                            }
                            $current_post_id = $parent_id;
                        } else {
                            break;
                        }
                    }
                }



                $menu = $menu_slug ? wp_get_nav_menu_object($menu_slug) : null;

                if ($menu) {
                    
                    // Renderização manual com hierarquia semântica (acessibilidade)
                    $menu_items = wp_get_nav_menu_items($menu->term_id);
                    
                    if ($menu_items) {
                        // 1. Pré-processamento: Correção de URLs e Mapa de Filhos
                        $children = [];
                        $root_items = [];
                        
                        foreach ($menu_items as $key => $item) {
                            // Lógica de correção de URL
                            $url = $item->url;
                            $parsed_url = parse_url($url);
                            $home_parsed = parse_url(home_url());
                            $home_host = isset($home_parsed['host']) ? $home_parsed['host'] : '';

                            if (isset($parsed_url['host'])) {
                                $url_host = $parsed_url['host'];
                                if ($url_host !== $home_host) {
                                    if (strpos($url_host, '.') === false) {
                                        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
                                        $url = home_url('/' . $url_host . $path);
                                    }
                                }
                            } else {
                                if (strpos($url, '#') !== 0) {
                                    $url = home_url($url);
                                }
                            }
                            
                            // Atualiza a URL no objeto para uso posterior
                            $menu_items[$key]->url = $url;
                            
                            // Organiza hierarquia
                            if ($item->menu_item_parent == 0) {
                                $root_items[] = $item;
                            } else {
                                $children[$item->menu_item_parent][] = $item;
                            }
                        }
                            
                        // 2. Determina o Item Ativo
                        $active_item_id = null;
                        $current_id = get_the_ID();
                        $current_url = get_permalink();
                        
                        // Encontra todos os itens que correspondem à página atual
                        $matching_ids = [];
                        
                        foreach ($menu_items as $item) {
                            $is_match = false;
                            
                            // Check by object_id
                            if ($item->object_id == $current_id) {
                                $is_match = true;
                            } 
                            // Check by URL if not already matched
                            else {
                                $normalized_item_url = trailingslashit(strtolower($item->url));
                                $normalized_current_url = trailingslashit(strtolower($current_url));
                                if ($normalized_item_url === $normalized_current_url) {
                                    $is_match = true;
                                }
                            }
                            
                            if ($is_match) {
                                $matching_ids[] = $item->ID;
                            }
                        }
                        
                        // Decide qual usar
                        if (!empty($matching_ids)) {
                            // Se tiver cookie e ele estiver na lista de matches válidos para esta página, usa ele
                            if (isset($_COOKIE['doc_v2_active_id'])) {
                                $cookie_id = intval($_COOKIE['doc_v2_active_id']);
                                if (in_array($cookie_id, $matching_ids)) {
                                    $active_item_id = $cookie_id;
                                }
                            }
                            
                            // Se não definiu pelo cookie (ou cookie inválido para esta página), usa o primeiro match
                            if (!$active_item_id) {
                                $active_item_id = $matching_ids[0];
                            }
                        }

                        // Função para determinar o tipo de conteúdo baseado no título
                        function get_content_type($title) {
                            $title_lower = mb_strtolower($title, 'UTF-8');
                            
                            // Verifica se é relacionado a sítios web
                            if (stripos($title_lower, 'sítios web') !== false || 
                                stripos($title_lower, 'sitios web') !== false ||
                                stripos($title_lower, 'sítio web') !== false ||
                                stripos($title_lower, 'sitio web') !== false) {
                                return 'sitios-web';
                            }
                            
                            // Verifica se é relacionado a aplicações móveis
                            if (stripos($title_lower, 'aplicações móveis') !== false || 
                                stripos($title_lower, 'aplicacoes moveis') !== false ||
                                stripos($title_lower, 'aplicação móvel') !== false ||
                                stripos($title_lower, 'aplicacao movel') !== false ||
                                stripos($title_lower, 'app móvel') !== false ||
                                stripos($title_lower, 'app movel') !== false) {
                                return 'aplicacoes-moveis';
                            }
                            
                            return '';
                        }

                        // 3. Função Recursiva de Renderização
                        // Usamos uma closure para renderizar a árvore
                        $render_tree = function($items, $depth = 0, $parent_type = '') use (&$render_tree, $children, $active_item_id) {
                            // Classe da lista: root sem estilo, filhos com sub-menu e padding
                            $ul_class = ($depth === 0) ? 'mb-0 pt-4' : 'sub-menu';
                            
                            echo '<ul class="' . esc_attr($ul_class) . '">';
                            
                            foreach ($items as $item) {
                                $is_active = ($item->ID === $active_item_id);
                                $has_children = isset($children[$item->ID]);
                                
                                // Determina o tipo de conteúdo
                                $content_type = get_content_type($item->title);
                                // Se não encontrou tipo no item atual, usa o tipo do pai
                                if (empty($content_type) && !empty($parent_type)) {
                                    $content_type = $parent_type;
                                }
                                
                                $active_class = $is_active ? 'current-menu-item' : '';
                                $item_id_class = 'menu-item-' . $item->ID;
                                $depth_class = 'menu-depth-' . $depth;
                                $children_class = $has_children ? 'has-children' : '';
                                $type_class = !empty($content_type) ? 'doc-type-' . $content_type : '';
                                $aria_current = $is_active ? ' aria-current="page"' : '';
                                
                                $all_classes = trim($active_class . ' ' . $depth_class . ' ' . $children_class . ' ' . $item_id_class . ' ' . $type_class);
                                
                                echo '<li class="' . esc_attr($all_classes) . '"';
                                if (!empty($content_type)) {
                                    echo ' data-content-type="' . esc_attr($content_type) . '"';
                                }
                                echo '>';
                                echo '<a href="' . esc_url($item->url) . '"' . $aria_current . '>';
                                echo '<span class="menu-text">' . esc_html($item->title) . '</span>';
                                echo '</a>';
                                
                                // Recursão para filhos (passa o tipo para os filhos)
                                if ($has_children) {
                                    $render_tree($children[$item->ID], $depth + 1, $content_type ?: $parent_type);
                                }
                                
                                echo '</li>';
                            }
                            
                            echo '</ul>';
                        };

                        // 4. Inicia Renderização
                        echo '<div class="doc-sidebar-nav">';
                        $render_tree($root_items);
                        echo '</div>';
                    }

                } else {
                    echo '<p class="text-muted ps-3">Menu não selecionado (verifique o campo ACF na página atual ou ascendentes).</p>';
                }
                ?>
                </div>
            </nav>

            <!-- Conteúdo Principal -->
            <main class="col-md-8 col-lg-9 px-md-5 doc-sp-content">
                
                <!-- Botão Collapse do Menu -->
                <button 
                    id="toggleMenuBtn"
                    class="btn btn-primary mb-3 toggle-menu-btn" 
                    type="button" 
                    aria-expanded="true" 
                    aria-controls="docSidebarNav"
                    aria-label="Ocultar menu de navegação lateral">
                    <i class="AMA-SetacurtaoficialEsq-Line" aria-hidden="true"></i>
                    <span class="btn-text ms-2" aria-hidden="true">Menu</span>
                </button>
                
                <div id="doc-content">
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <?php
                        // 1) tenta no child depois no parent automaticamente
                        $full_cinza_path = get_theme_file_path('/Templates/full-width-cinza.php');

                        // 2) se não existir, tenta variações comuns
                        if (!file_exists($full_cinza_path)) {
                            $candidates = [
                                '/templates/full-width-cinza.php',
                                '/full-width-cinza.php',
                            ];
                            $full_cinza_path = '';
                            foreach ($candidates as $rel) {
                                $try = get_theme_file_path($rel);
                                if (file_exists($try)) {
                                    $full_cinza_path = $try;
                                    break;
                                }
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
