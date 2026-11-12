<?php


/**
 * Shortcode para exibir os facilitadores.
 *
 * Shortcode: [facilitadores] que mostra todos os facilitadores registados no site.
 * Apresenta uma lista de cartões,em formato de grelha com duas colunas, e inclui a foto do faciitador,
 * nome, breve descrição, um botão para visitar o Linkedin do facilitador e um botão para ver a biografia completa.
 *
 * @return string
 */
function facilitadores_shortcode_cb( array $atts ) : ?string
{
    // Atributos do shortcode
    $atts = shortcode_atts(
        [
            'resultados_pagina' => 6,
        ],
        $atts
    );

    // Página atual
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    ob_start();

    // Query para obter todos os facilitadores
    $post = new WP_Query([
        "post_type"         => "facilitador",
        "post_status"       => "publish",
        "orderby"           => "title",
        "order"             => "ASC",
        "posts_per_page"    => $atts["resultados_pagina"],
        "paged"             => $paged,
    ]);

    // Apresenta todos os resultados com o template de cartão de facilitador
    if($post->have_posts()) {
        while($post->have_posts()) {
            $post->the_post();
            get_template_part("template-parts/facilitador", "card");
        }
    }

    // HTML para exibir os facilitadores
    $output = ob_get_clean();

    //reset post data
    wp_reset_postdata();

    return sprintf('<ul class="facilitadores-grid">%s</ul>', $output)
        . ama_paginate_links($post->max_num_pages, "Seletor de páginas da lista de facilitadores");
}
add_shortcode("facilitadores", "facilitadores_shortcode_cb");
