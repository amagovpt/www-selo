<?php
/**
 * Lista de auditores e facilitadores em formato tabela
 *
 * @return string|null
 */
function lista_facilitadores_cb($atts) : ?string
{
    // Atributos do shortcode
    $atts = shortcode_atts(
        [
            'resultados_pagina' => 10,
            'sumario'           => '',
        ],
        $atts
    );

    // Página atual
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Output em HTMl
    $output = '';

    // Parâmetros da query
    $query_params =  [
        'post_type'         => 'facilitador',
        'posts_per_page'    => $atts['resultados_pagina'],
        'orderby'           => 'title',
        'order'             => 'ASC',
        'paged'             => $paged, // Add pagination parameter
    ];

    $query = new WP_Query($query_params);

    if ( $query->have_posts() ) {
        // Insere o sumário d tabela caso o atributo não esteja vazio
        if ($atts['sumario'] !== '' ) {
            $output .= '<table class="ama table" summary="'.$atts['sumario'].'">'; //class="table table-striped table-hover"
        } else {
            $output .= '<table class="ama table">'; //class="table table-striped table-hover"
        }

        $output .= '<caption align="top">Lista de facilitadores do Selo de Usabilidade e Acessibilidade.</caption>';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<th>Nome</th>';
        $output .= '<th>Email</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        while ( $query->have_posts() ) {
            $query->the_post();
            $output .= '<tr>';
            $output .= '<td>'.get_the_title().'</td>';
            $output .= '<td>'.get_field("auditor_facilitador_email").'</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody>';
        $output .= '</table>';

        // Obtém as ligações de paginação
        $page_links = paginate_links(
            [
                'total'     => $query->max_num_pages,
                'prev_text' => __('&lt;'),
                'next_text' => __('&gt;'),
                'type'      => 'array',
            ]
        );

        if( is_array( $page_links ) && count($page_links) > 0 ) {
            $pages = '';

            // Ligação para a 1ª página
            if (get_query_var('paged') > 1) {
                $first_page_link = paginate_links([
                    'total'     => $query->max_num_pages,
                    'prev_text' => '&laquo;',
                    'type'      => 'array',
                ]);

                $pages .= sprintf('<li class="page-item first-page" aria-label="Primeira página">%s</li>', $first_page_link[0]);
            }

            // Add pagination links
            foreach ( $page_links as $link )
            {
                // Verifica se a ligação é para a página atual e adiciona o atributo aria-current
                $aria_attrs = '';
                if (strpos($link, 'current') !== false) {
                    $aria_attrs .= ' aria-current="page"';
                    $link = strip_tags($link);
                }

                // Check if it's the next page
                if(strpos($link, 'next') !== false) {
                    $link = str_replace('next page-numbers', 'next page-numbers', $link);
                    $aria_attrs .= ' aria-label="Próxima página"';
                }

                // Check if it's the previous page
                if(strpos($link, 'prev') !== false) {
                    $link = str_replace('next page-numbers', 'next page-numbers', $link);
                    $aria_attrs .= ' aria-label="Página anterior"';
                }

                // Verifica se a ligação tem a classe dots
                if(strpos($link, 'dots') !== false) {
                    $aria_attrs .= ' aria-hidden="true"';
                }

                // Adiciona a ligação à paginação
                $pages .= sprintf('<li class="page-item"%s>%s</li>', $aria_attrs, $link);
            }

            // Ligação para a última página
            if (get_query_var('paged') < $query->max_num_pages) {
                $last_page_link = paginate_links([
                    'total'     => $query->max_num_pages,
                    'next_text' => "&raquo;", // Alterando para 'next_text' para a seta da direita
                    'type'      => 'array',
                ]);

                // Adiciona a ligação à paginação
                $pages .= '<li class="page-item last-page" aria-label="Última página"><a href="'.get_permalink(get_queried_object_id()).'page/'.$query->max_num_pages.'">&raquo;</a></li>';
            }

            // Output HTML
            $output .= sprintf('<nav aria-label="Seletor de páginas da lista de facilitadores"><ul class="ama paginacao mt-5">%s</ul></nav>', $pages );
        }

        wp_reset_postdata();
    } else {
        $output .= 'Não foram encontrados facilitadores.';
    }

    return $output;
}
add_shortcode( 'lista-facilitadores', 'lista_facilitadores_cb' );
