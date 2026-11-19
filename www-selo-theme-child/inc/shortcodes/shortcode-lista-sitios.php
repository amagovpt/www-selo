<?php

// Exit if accessed directly.
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Colunas que podem ser ordenadas na lista (parâmetro "ordenar" no endereço).
 */
const AMA_SELO_LISTA_ORDENAVEIS = [ 'nome', 'tipo', 'selo', 'data_declaracao' ];

/**
 * Ícone e texto da declaração de acessibilidade.
 *
 * @param string $declaracao Valor da fonte (ex.: Conforme, Plenamente conforme, Parcialmente conforme).
 * @return string HTML da célula.
 */
function ama_selo_lista_declaracao( string $declaracao ): string
{
    $valor = strtolower( remove_accents( trim( $declaracao ) ) );
    $img   = get_stylesheet_directory_uri() . '/assets/img/';

    if ( in_array( $valor, [ 'conforme', 'plenamente conforme' ], true ) ) {
        $ficheiro = 'declaracao_plenamente_conforme.svg';
        $texto    = 'Declaração plenamente conforme';
    } elseif ( 'parcialmente conforme' === $valor ) {
        $ficheiro = 'declaracao_parcialmente_conforme.svg';
        $texto    = 'Declaração parcialmente conforme';
    } elseif ( in_array( $valor, [ 'nao', 'nao conforme' ], true ) ) {
        $ficheiro = 'declaracao_nao_conforme.svg';
        $texto    = 'Declaração não conforme';
    } else {
        return '<abbr title="não disponível">n.d.</abbr>';
    }

    return sprintf( '<img width="30" height="30" src="%s" alt="%s">', esc_url( $img . $ficheiro ), esc_attr( $texto ) );
}

/**
 * Cabeçalho de coluna ordenável: ligação que alterna ASC/DESC e aria-sort na coluna ativa.
 *
 * @param string $coluna  Chave da coluna (AMA_SELO_LISTA_ORDENAVEIS).
 * @param string $rotulo  Texto do cabeçalho.
 * @param string $ordenar Coluna ordenada atualmente.
 * @param string $sentido asc ou desc.
 * @return string
 */
function ama_selo_lista_cabecalho( string $coluna, string $rotulo, string $ordenar, string $sentido ): string
{
    $ativa   = $coluna === $ordenar;
    $proximo = ( $ativa && 'asc' === $sentido ) ? 'desc' : 'asc';

    // A ordenação recomeça na página 1.
    $url = add_query_arg( [ 'ordenar' => $coluna, 'sentido' => $proximo ], get_permalink() );

    $aria_sort = $ativa ? sprintf( ' aria-sort="%s"', 'asc' === $sentido ? 'ascending' : 'descending' ) : '';
    $icone     = $ativa ? ( 'asc' === $sentido ? '▲' : '▼' ) : '↕';
    $acao      = 'asc' === $proximo ? 'ordenar por ordem ascendente' : 'ordenar por ordem descendente';

    return sprintf(
        '<th scope="col"%s><a class="ordenar%s" href="%s">%s<span class="visually-hidden">, %s</span> <span class="ordenar-icone" aria-hidden="true">%s</span></a></th>',
        $aria_sort,
        $ativa ? ' ativa' : '',
        esc_url( $url ),
        esc_html( $rotulo ),
        esc_html( $acao ),
        $icone
    );
}

/**
 * Lista de sítios web e aplicações móveis com selo em formato tabela.
 *
 * Colunas: Nome (ligação para a ficha), Tipo, Declaração, Selo e Data da declaração.
 * Ordenação no servidor (?ordenar=nome|tipo|selo|data_declaracao&sentido=asc|desc),
 * que funciona sem JavaScript e com a paginação. Com JavaScript (assets/js/lista-selos.js)
 * a tabela é atualizada sem recarregar a página, para o foco ficar no título da coluna.
 *
 * @param array|string $atts
 * @return string|null
 */
function lista_sitios_web_selo_cb($atts) : ?string
{
    $atts = shortcode_atts(
        [
            'resultados_pagina' => 50,
            'contacto'          => 'selo@arte.gov.pt',
        ],
        $atts
    );

    $resultados_por_pagina = max( 1, absint( $atts['resultados_pagina'] ) );
    $total_paginas         = (int) numero_total_paginas_sitios_web( $resultados_por_pagina );
    $pagina_atual          = max( 1, (int) get_query_var( 'paged' ) );

    // Só valores conhecidos (a ordenação é montada a partir de uma lista fixa).
    $ordenar = sanitize_key( $_GET['ordenar'] ?? '' );
    $ordenar = in_array( $ordenar, AMA_SELO_LISTA_ORDENAVEIS, true ) ? $ordenar : '';
    $sentido = 'desc' === sanitize_key( $_GET['sentido'] ?? '' ) ? 'desc' : 'asc';

    $sitios = dados_sitios_web([
        'resultados' => $resultados_por_pagina,
        'pagina'     => $pagina_atual,
        'ordenar'    => $ordenar,
        'sentido'    => $sentido,
    ]);

    if ( is_wp_error( $sitios ) ) {
        return '<p>' . esc_html( $sitios->get_error_message() ) . '</p>';
    }

    wp_enqueue_script(
        'ama-lista-selos',
        get_stylesheet_directory_uri() . '/assets/js/lista-selos.js',
        [],
        filemtime( get_stylesheet_directory() . '/assets/js/lista-selos.js' ),
        true
    );

    $img    = get_stylesheet_directory_uri() . '/assets/img/';
    $nomes  = [ 'nome' => 'nome', 'tipo' => 'tipo', 'selo' => 'selo', 'data_declaracao' => 'data da declaração' ];
    $ordem  = 'asc' === $sentido ? 'ascendente' : 'descendente';

    // Frase anunciada pelos leitores de ecrã depois de ordenar sem recarregar a página.
    $anuncio = $ordenar ? sprintf( 'Lista ordenada por %s, ordem %s.', $nomes[ $ordenar ], $ordem ) : '';

    $output  = '<div class="lista-selos-contentor" data-lista-selos>';
    $output .= sprintf( '<p class="visually-hidden lista-selos-estado" aria-live="polite" data-anuncio="%s"></p>', esc_attr( $anuncio ) );
    $output .= '<div class="table-responsive lista-selos">';
    $output .= '<table class="ama table">';
    $output .= '<caption>Lista de sítios web e aplicações móveis com Selo de Usabilidade e Acessibilidade';
    if ( $ordenar ) {
        $output .= sprintf( '<span class="visually-hidden">, ordenada por %s, ordem %s</span>', esc_html( $nomes[ $ordenar ] ), $ordem );
    }
    $output .= '</caption>';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= ama_selo_lista_cabecalho( 'nome', 'Nome', $ordenar, $sentido );
    $output .= ama_selo_lista_cabecalho( 'tipo', 'Tipo', $ordenar, $sentido );
    $output .= '<th scope="col">Declaração</th>';
    $output .= ama_selo_lista_cabecalho( 'selo', 'Selo', $ordenar, $sentido );
    $output .= ama_selo_lista_cabecalho( 'data_declaracao', 'Data da declaração', $ordenar, $sentido );
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    foreach ( $sitios as $item ) {
        $data = ama_selo_normalizar_data( $item['data_declaracao'] ?? '' );
        $selo = ucfirst( strtolower( $item['selo'] ) );

        $output .= '<tr>';
        $output .= sprintf( '<th scope="row"><a class="bold" href="%s">%s</a></th>', esc_url( ama_selo_url_ficha( $item ) ), esc_html( $item['name'] ) );
        $output .= '<td>' . esc_html( ama_selo_nome_tipo( $item['tipo'] ?? AMA_SELO_TIPO_SITIO_WEB ) ) . '</td>';
        $output .= '<td>' . ama_selo_lista_declaracao( (string) $item['declaracao'] ) . '</td>';
        $output .= sprintf(
            '<td><img width="30" height="30" src="%s" alt="%s"></td>',
            esc_url( $img . 'selo_' . strtolower( remove_accents( $selo ) ) . '.svg' ),
            esc_attr( 'Selo ' . $selo )
        );
        $output .= $data
            ? sprintf( '<td><time datetime="%s">%s</time></td>', esc_attr( substr( $data, 0, 10 ) ), esc_html( mysql2date( 'd/m/Y', $data ) ) )
            : '<td><abbr title="não disponível">n.d.</abbr></td>';
        $output .= '</tr>';
    }

    $output .= '</tbody>';
    $output .= '</table></div>';

    // Contentor sempre presente, mesmo vazio, para o JavaScript poder atualizar a paginação.
    $output .= '<div class="lista-selos-paginacao">';
    $output .= ama_selo_lista_paginacao( $total_paginas, $pagina_atual, $ordenar ? [ 'ordenar' => $ordenar, 'sentido' => $sentido ] : [] );
    $output .= '</div>';
    $output .= '</div>';

    if ( '' !== $atts['contacto'] ) {
        $email   = sanitize_email( $atts['contacto'] );
        $output .= sprintf(
            '<p class="lista-selos-nota mt-4"><strong>Nota:</strong> se encontrou uma plataforma digital chancelada com o <em>Selo de Usabilidade e Acessibilidade</em> e ela não consta desta lista, por favor, envie uma mensagem para <a href="mailto:%1$s">%1$s</a>.</p>',
            esc_html( $email )
        );
    }

    return $output;
}
add_shortcode( 'lista-sitios-selo', 'lista_sitios_web_selo_cb' );

/**
 * Paginação acessível da lista, mantendo a ordenação escolhida.
 *
 * @param int   $total_paginas
 * @param int   $pagina_atual
 * @param array $args_extra Parâmetros a manter nas ligações (ordenar, sentido).
 * @return string
 */
function ama_selo_lista_paginacao( int $total_paginas, int $pagina_atual, array $args_extra ): string
{
    if ( $total_paginas < 2 ) {
        return '';
    }

    $base = trailingslashit( get_permalink( get_queried_object_id() ) );
    $url  = function ( int $n ) use ( $base, $args_extra ) {
        return add_query_arg( $args_extra, 1 === $n ? $base : $base . 'page/' . $n . '/' );
    };

    $page_links = paginate_links(
        [
            'base'      => $base . '%_%',
            'format'    => 'page/%#%/',
            'current'   => $pagina_atual,
            'total'     => $total_paginas,
            'prev_text' => '',
            'next_text' => '',
            'type'      => 'array',
            'add_args'  => $args_extra,
        ]
    );

    if ( ! is_array( $page_links ) || ! $page_links ) {
        return '';
    }

    $pages = '';

    if ( $pagina_atual > 1 ) {
        $pages .= sprintf( '<li class="page-item first-page"><a href="%s" aria-label="Primeira página">&nbsp;</a></li>', esc_url( $url( 1 ) ) );
    }

    foreach ( $page_links as $link ) {
        $aria_attrs = '';

        // O paginate_links copia os parâmetros do endereço atual, mesmo inválidos: ficam só os válidos.
        $link = preg_replace_callback( '/href="([^"]*)"/', function ( $m ) use ( $args_extra ) {
            $limpo = remove_query_arg( [ 'ordenar', 'sentido' ], html_entity_decode( $m[1] ) );
            return 'href="' . esc_url( add_query_arg( $args_extra, $limpo ) ) . '"';
        }, $link );

        if ( false !== strpos( $link, 'current' ) ) {
            $aria_attrs .= ' aria-current="page"';
            $link        = strip_tags( $link );
        } elseif ( false !== strpos( $link, 'next' ) ) {
            $link = str_replace( '<a ', '<a aria-label="Próxima página" ', $link );
        } elseif ( false !== strpos( $link, 'prev' ) ) {
            $link = str_replace( '<a ', '<a aria-label="Página anterior" ', $link );
        } elseif ( false !== strpos( $link, 'dots' ) ) {
            $aria_attrs .= ' aria-hidden="true"';
        }

        $pages .= sprintf( '<li class="page-item"%s>%s</li>', $aria_attrs, $link );
    }

    if ( $pagina_atual < $total_paginas ) {
        $pages .= sprintf( '<li class="page-item last-page"><a href="%s" aria-label="Última página">&nbsp;</a></li>', esc_url( $url( $total_paginas ) ) );
    }

    return sprintf( '<nav aria-label="Seletor de páginas da lista de sítios web e aplicações móveis com Selo"><ul class="ama paginacao mt-5">%s</ul></nav>', $pages );
}
