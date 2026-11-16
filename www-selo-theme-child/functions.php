<?php
// require_once get_template_directory() . '/sidebar/function.php';
/**
 * Carrega a folha de estilos do tema filho.
 *
 * Prioridade definida a 11, para garantir que o estilo do tema filho sobrescreve o estilo do tema pai.
 * Principalmente, porque o Bootstrap é carregado com prioridade 10, e sobrescrevia alguns estilos do tema AMA.
 *
 * @return void
 */

function child_theme_enqueue_stylesheet(): void
{
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        '',
        '1.0.1',
    );

    wp_enqueue_style(
        'www-a11y-theme-child-style',
        get_stylesheet_uri(),
        ['parent-style'],
        '1.0.1',
    );

    wp_enqueue_script(
        'grafico-selos-js',
        get_stylesheet_directory_uri() . '/assets/js/grafico-selos.js',
        ['jquery'],
        "1.1",
        true
    );

    wp_enqueue_script(
        'tabs-js',
        get_stylesheet_directory_uri() . '/assets/js/tabs.js',
        [],
        "1.0.1",
        true
    );
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_stylesheet', 11);

// Carrega os ficheiros com shortcodes
include_once  get_stylesheet_directory() . '/inc/shortcodes/index.php';

// Carrega os ficheiros com funções auxiliares
require_once get_stylesheet_directory() . '/inc/helper-functions.php';

// Carrega os ficheiros com funções de administração
require_once get_stylesheet_directory() . '/settings/index.php';

/**
 * Adiciona a variável de query 'idsitio' para permitir a
 * @param $vars
 * @return array
 */
function add_query_vars($vars): array
{
    $vars[] = 'idsitio';
    return $vars;
}
add_filter('query_vars', 'add_query_vars');

require_once get_stylesheet_directory() . '/inc/base-dados.php';


/**
 * Habilita o cabeçalho HTTP Strict Transport Security (HSTS) no WordPress.
 *
 * Includes preloading with subdomain support.
 *
 * @return void
 */
function ama_habilita_header_HSTS_cb(): void
{
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
add_action('send_headers', 'ama_habilita_header_HSTS_cb');

/**
 * Inclui o ficheiro com os cron jobs
 */
require_once get_stylesheet_directory() . '/inc/cron/index.php';

/**
 * Torna a paginação do WordPress acessível.
 *
 * @param int $max_pages
 * @return string|null
 */
function ama_paginate_links(int $max_pages, string $nav_label = ""): ?string
{
    $output = '';

    if ($nav_label !== "") {
        $nav_label = 'aria-label="' . $nav_label . '"';
    }

    $page_links = paginate_links(
        [
            "total"                 => $max_pages,
            "prev_text"             => "",
            "next_text"             => "",
            "type"                  => "array",
        ]
    );

    if (is_array($page_links) && count($page_links) > 0) {
        $pages = '';

        foreach ($page_links as $link) {
            $aria_attrs = '';

            $dom = new DOMDocument();
            $dom->loadHTML(
                mb_convert_encoding(
                    $link,
                    'HTML-ENTITIES',
                    'UTF-8'
                ),
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $a_tags = $dom->getElementsByTagName('a');


            if (strpos($link, 'current') !== false) {
                $aria_attrs .= ' aria-current="page"';
            }

            if (strpos($link, 'next') !== false) {
                $aria_attrs .= ' aria-label="Próxima página"';
            }

            if (strpos($link, 'prev') !== false) {
                $aria_attrs .= ' aria-label="Página anterior"';
            }

            if (strpos($link, 'dots') !== false) {
                $aria_attrs .= ' aria-hidden="true"';
            }

            foreach ($a_tags as $a_tag) {
                $a_tag->setAttribute('title', 'Página ' . $a_tag->nodeValue);
            }

            $link = $dom->saveHTML();

            $pages .= sprintf('<li class="page-item"%s>%s</li>', $aria_attrs, $link);
        }

        $output .= sprintf('<nav ' . $nav_label . '><ul class="ama paginacao mt-5">%s</ul></nav>', $pages);
    }

    return $output;
}

// Lê todos os menus criados no WordPress e retorna um array com os IDs dos menus.
function get_all_menus(): array
{
    $menus = wp_get_nav_menus();
    $menu_ids = [];

    foreach ($menus as $menu) {
        $menu_ids[] = $menu->term_id;
    }

    return $menu_ids;
}

// Mostra uma selecção de menus na página de edição de página para o ACF "selo_indice_de_conteudo_documentacao"
function ama_acf_select_menus($field)
{
    $menus = get_all_menus();

    if (!empty($menus)) {
        foreach ($menus as $menu_id) {
            $menu = wp_get_nav_menu_object($menu_id);
            $field['choices'][$menu_id] = $menu->name;
        }
    } else {
        $field['choices'] = [];
    }

    return $field;
}

// Gerar Accordion

function render_bootstrap_menu($menu_id, $wrap_in_hidden_div = false)
{
    $menu_items = wp_get_nav_menu_items($menu_id);
    $parent_items = [];

    foreach ($menu_items as $item) {
        $parent_id = (int) $item->menu_item_parent;
        if (!isset($parent_items[$parent_id])) {
            $parent_items[$parent_id] = [];
        }
        $parent_items[$parent_id][] = $item;
    }

    $output = '';
    if ($wrap_in_hidden_div) {
        $output .= '<div id="rendered-menu" style="display: none;">';
    }

    $output .= '<div class="accordion" id="sidebarAccordion">';
    $output .= render_accordion_items($parent_items, 0, 'sidebarAccordion');
    $output .= '</div>';

    if ($wrap_in_hidden_div) {
        $output .= '</div>';
    }

    return $output;
}



// Função recursiva para montar os itens
function render_accordion_items($items_by_parent, $parent_id, $accordion_id_prefix, $depth = 0)
{
    $output = '';
    if (!isset($items_by_parent[$parent_id])) return $output;

    foreach ($items_by_parent[$parent_id] as $index => $item) {
        $has_children = isset($items_by_parent[$item->ID]);
        $collapse_id = $accordion_id_prefix . '-collapse-' . $item->ID;
        $heading_id = $accordion_id_prefix . '-heading-' . $item->ID;

        $output .= '<div class="accordion-item">';
        $output .= '<h2 class="accordion-header" id="' . $heading_id . '">';

        if ($has_children) {
            $output .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#' . $collapse_id . '" aria-expanded="false" aria-controls="' . $collapse_id . '">';
            $output .= esc_html($item->title);
            $output .= '</button>';
        } else {
            $output .= '<div class="accordion-button no-toggle" style="cursor: default;">';
            // Garantir que a URL seja sempre completa com o caminho correto
            $menu_url = $item->url;
            if (strpos($menu_url, 'http') !== 0) {
                // Se não for uma URL completa, construir com home_url
                $menu_url = home_url($menu_url);
            }
            $output .= '<a href="' . esc_url($menu_url) . '" class="nav-link p-0 w-100 d-block">' . esc_html($item->title) . '</a>';
            $output .= '</div>';
        }

        $output .= '</h2>';

        if ($has_children) {
            $output .= '<div id="' . $collapse_id . '" class="accordion-collapse collapse" aria-labelledby="' . $heading_id . '">';
            $output .= '<div class="accordion-body">';

            // Aqui está o truque: chamada recursiva
            $output .= '<div class="accordion" id="' . $collapse_id . '-inner">';
            $output .= render_accordion_items($items_by_parent, $item->ID, $collapse_id . '-inner', $depth + 1);
            $output .= '</div>';

            $output .= '</div></div>';
        }

        $output .= '</div>';
    }

    return $output;
}


add_action('wp_enqueue_scripts', function () {
  wp_enqueue_script(
    'iframe-a11y',
    get_stylesheet_directory_uri() . '/assets/js/iframe-a11y.js',
    [], // dependências
    '1.0.0',
    true // no footer
  );
});

/**
 * Defer parsing of JavaScript files.
 * Adds 'defer' attribute to custom scripts only on frontend, excluding WordPress core scripts.
 * Does NOT apply defer in admin area to prevent breaking Gutenberg editor.
 */
function add_defer_attribute($tag, $handle) {
    // Don't apply defer in admin area (Gutenberg, etc.)
    if (is_admin()) {
        return $tag;
    }
    
    // Scripts to exclude from deferral (WordPress core scripts and dependencies)
    $exclude_handles = [
        // jQuery and jQuery UI
        'jquery', 
        'jquery-core', 
        'jquery-migrate',
        'jquery-ui-core',
        'jquery-ui-widget',
        'jquery-ui-mouse',
        'jquery-ui-sortable',
        'jquery-ui-draggable',
        'jquery-ui-droppable',
        'jquery-ui-resizable',
        'jquery-ui-selectable',
        'jquery-ui-position',
        'jquery-ui-datepicker',
        'jquery-ui-tooltip',
        'jquery-ui-accordion',
        'jquery-ui-autocomplete',
        'jquery-ui-button',
        'jquery-ui-dialog',
        'jquery-ui-slider',
        'jquery-ui-tabs',
        'jquery-ui-menu',
        'jquery-ui-spinner',
        'jquery-effects-core',
        // JavaScript libraries
        'underscore',
        'backbone',
        'react',
        'react-dom',
        // WordPress core
        'wp-polyfill',
        'wp-i18n',
        'wp-api-fetch',
        'wp-media-utils',
        'wp-a11y',
        'wp-element',
        'wp-hooks',
        'wp-data',
        'wp-components',
        'wp-blocks',
        'wp-editor',
        'wp-edit-post',
        'wp-util',
        'wp-backbone',
        'media-models',
        'media-views',
        'media-editor',
        'wp-pointer'
    ];
    
    // Don't defer any script that starts with 'wp-', 'media-', or 'jquery-'
    if (in_array($handle, $exclude_handles) || 
        strpos($handle, 'wp-') === 0 || 
        strpos($handle, 'media-') === 0 ||
        strpos($handle, 'jquery-') === 0) {
        return $tag;
    }
    
    // Only defer if script has src attribute (not inline)
    if (strpos($tag, ' src=') !== false) {
        return str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);

/**
 * Artigos relacionados (#5): da mesma categoria, mais recentes primeiro, sem o próprio;
 * completa com os mais recentes se não houver suficientes.
 *
 * @param int   $post_id
 * @param int[] $categorias
 * @param int   $total
 * @return WP_Post[]
 */
function ama_artigo_relacionados( int $post_id, array $categorias, int $total = 4 ): array
{
    $base = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ];

    $relacionados = $categorias ? get_posts( $base + [
        'posts_per_page' => $total,
        'category__in'   => array_map( 'intval', $categorias ),
        'post__not_in'   => [ $post_id ],
    ] ) : [];

    if ( count( $relacionados ) < $total ) {
        $relacionados = array_merge( $relacionados, get_posts( $base + [
            'posts_per_page' => $total - count( $relacionados ),
            'post__not_in'   => array_merge( [ $post_id ], wp_list_pluck( $relacionados, 'ID' ) ),
        ] ) );
    }

    return $relacionados;
}
