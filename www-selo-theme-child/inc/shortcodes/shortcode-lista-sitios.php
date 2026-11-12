<?php

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Lista de sítios ‘web’ com selo em formato tabela
 *
 * @return string|null
 */
function lista_sitios_web_selo_cb($atts) : ?string
{
    // Dados do sítio ‘web’
    // Resultados por página
    $resultados_por_pagina = 50;

    // Nº total de páginas
    $total_paginas = numero_total_paginas_sitios_web($resultados_por_pagina);

    // Página atual
    $pagina_atual = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Devolve a lista dos sítios ‘web’
    $sitio = dados_sitios_web([
        'resultados'    => $resultados_por_pagina,
        'pagina'        => $pagina_atual,
    ]);

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

    // Insere o sumário d tabela caso o atributo não esteja vazio
    if ($atts['sumario'] !== '' ) {
        $output .= '<table class="ama table" summary="'.$atts['sumario'].'">';
    } else {
        $output .= '<div class="table-responsive"><table class="ama table">';
    }

    $output .= '<caption>Lista de sítios web com Selo de Usabilidade e Acessibilidade.</caption>';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>Sítio Web</th>';
    $output .= '<th>Declaração</th>';
    $output .= '<th>Selo</th>';
    $output .= '<th>Ficha do Sítio Web</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    // Gera a lista de sítios ‘web’ com Selo na tabela
    foreach ($sitio as $item) {

        /*
        if($item["id"] == 1) {
            var_dump($item);
        }
        */

        $output .= '<tr>';
        $output .= '<td><a class="bold" href="'.trailingslashit(get_permalink(1062)).'?idsitio='.$item["id"].'">'.$item['name'].'</a></td>';

        // Adiciona a imagem conforme o estado da declaração
        switch (strtolower($item["declaracao"])) {
            case "conforme":
                $output .= '<td><img width="30" src="'.get_stylesheet_directory_uri().'/assets/img/declaracao_plenamente_conforme.svg" alt="Declaração plenamente conforme"></td>';
                break;
            case "parcialmente conforme":
                $output .= '<td><img width="30" src="'.get_stylesheet_directory_uri().'/assets/img/declaracao_parcialmente_conforme.svg" alt="Declaração parcialmente conforme"></td>';
                break;
            case "nao":
                $output .= '<td><img width="30" src="'.get_stylesheet_directory_uri().'/assets/img/declaracao_nao_conforme.svg" alt="Declaração não conforme"></td>';
                break;
            default:
                $output .= '<td></td>';
        }

        $output .= '<td><img width="30" src="'.get_stylesheet_directory_uri().'/assets/img/selo_'.strtolower($item["selo"]).'.svg" alt="Selo '.$item["selo"].' de Usabilidade e Acessibilidade"></td>';
        $output .= '<td><a class="ama btn btn-primary" href="'.trailingslashit(get_permalink(1062)).'?idsitio='.$item["id"].'">Consultar<span class="visually-hidden"> ficha do sítio web</span></a></td>';
        $output .= '</tr>';
    }

    // Fecha a tabela
    $output .= '</tbody>';
    $output .= '</table></div>';

    // Obtém as ligações de paginação
    $page_links = paginate_links(
        [
            'total'     => $total_paginas,
            'prev_text' => __(''),
            'next_text' => __(''),
            'type'      => 'array',
        ]
    );

    if( is_array( $page_links ) && count($page_links) > 0 ) {
        $pages = '';

        // Ligação para a 1ª página
        if (get_query_var('paged') < $total_paginas) {
            $first_page_link = paginate_links([
                'total'     => $total_paginas,
                'prev_text' => '',
                'type'      => '',
            ]);

            $pages .= sprintf('<li class="page-item first-page" aria-label="Primeira página">%s</li>', '<a href="'.get_permalink(get_queried_object_id()).'page/1">&nbsp;</a>');
        }

        foreach ( $page_links as $link )
        {
            $aria_attrs = '';
            if (strpos($link, 'current') !== false) {
                $aria_attrs .= ' aria-current="page"';
                $link = strip_tags($link);
            }

            if(strpos($link, 'next') !== false) {
                $link = str_replace('next page-numbers', 'next page-numbers', $link);
                $aria_attrs .= ' aria-label="Próxima página"';
            }

            if(strpos($link, 'prev') !== false) {
                $link = str_replace('next page-numbers', 'next page-numbers', $link);
                $aria_attrs .= ' aria-label="Página anterior"';
            }

            if(strpos($link, 'dots') !== false) {
                $aria_attrs .= ' aria-hidden="true"';
            }

            $pages .= sprintf('<li class="page-item"%s>%s</li>', $aria_attrs, $link);
        }

        // Ligação para a última página
        if (get_query_var('paged') < $total_paginas) {
            $last_page_link = paginate_links([
                'total'     => $total_paginas,
                'next_text' => '',
                'type'      => '',
            ]);

            // Adiciona a ligação à paginação
            $pages .= '<li class="page-item last-page" aria-label="Última página"><a href="'.get_permalink(get_queried_object_id()).'page/'.$total_paginas.'">&nbsp;</a></li>';
        }

        // Output HTML
        $output .= sprintf('<nav aria-label="Seletor de páginas da lista de sítios web com Selo"><ul class="ama paginacao mt-5">%s</ul></nav>', $pages );
    }

    return $output;
}
add_shortcode( 'lista-sitios-selo', 'lista_sitios_web_selo_cb' );