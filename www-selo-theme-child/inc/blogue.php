<?php
/**
 * Página agregadora do blogue (#4), com a estrutura do blogue do acessibilidade.gov.pt:
 *
 * 1. Destaque principal: o artigo fixado ("Fixar no topo do blogue") mais recente
 *    ou, se não houver nenhum, o artigo mais recente.
 * 2. Outros destaques: os 3 artigos seguintes, em cartões.
 * 3. Lista com os restantes artigos (paginada) e, ao lado, "Mais lidos".
 *
 * Sem Pods: o destaque usa os artigos fixados do WordPress e as visualizações
 * ficam no meta "visualizacoes" (o mesmo nome do acessibilidade.gov.pt).
 */

defined( 'ABSPATH' ) || exit;

/** Artigos por página na lista (sem contar com os destaques). */
const AMA_BLOGUE_POR_PAGINA = 8;

/** Número de "Outros destaques" (cartões). */
const AMA_BLOGUE_OUTROS_DESTAQUES = 3;

/**
 * IDs dos destaques: [ principal, outro 1, outro 2, outro 3 ].
 *
 * @return int[]
 */
function ama_blogue_destaques(): array
{
    static $ids = null;

    if ( null !== $ids ) {
        return $ids;
    }

    $base = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'fields'              => 'ids',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'suppress_filters'    => true,
    ];

    $principal = [];
    $fixados   = array_filter( array_map( 'intval', (array) get_option( 'sticky_posts', [] ) ) );

    if ( $fixados ) {
        $principal = get_posts( $base + [ 'post__in' => $fixados, 'posts_per_page' => 1 ] );
    }

    $outros = get_posts( $base + [
        'posts_per_page' => AMA_BLOGUE_OUTROS_DESTAQUES + ( $principal ? 0 : 1 ),
        'post__not_in'   => $principal,
    ] );

    $ids = array_map( 'intval', array_merge( $principal, $outros ) );

    return $ids;
}

/**
 * Na página do blogue, a consulta principal passa a ser a lista: sem os destaques
 * e com AMA_BLOGUE_POR_PAGINA artigos por página (as páginas /page/N/ seguem esta lista).
 *
 * @param WP_Query $query
 * @return void
 */
function ama_blogue_consulta_lista( WP_Query $query ): void
{
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
        return;
    }

    $query->set( 'posts_per_page', AMA_BLOGUE_POR_PAGINA );
    $query->set( 'ignore_sticky_posts', true );
    $query->set( 'post__not_in', ama_blogue_destaques() );
}
add_action( 'pre_get_posts', 'ama_blogue_consulta_lista' );

/**
 * Conta as visualizações de cada artigo (meta "visualizacoes"), para os "Mais lidos".
 *
 * Não conta pré-visualizações, utilizadores com sessão iniciada nem robôs conhecidos.
 *
 * @return void
 */
function ama_blogue_contar_visualizacao(): void
{
    if ( ! is_singular( 'post' ) || is_preview() || is_user_logged_in() ) {
        return;
    }

    $agente = strtolower( (string) ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );

    if ( '' === $agente || preg_match( '/bot|crawl|spider|slurp|preview|monitor|headless/', $agente ) ) {
        return;
    }

    $post_id = get_queried_object_id();
    update_post_meta( $post_id, 'visualizacoes', (int) get_post_meta( $post_id, 'visualizacoes', true ) + 1 );
}
add_action( 'template_redirect', 'ama_blogue_contar_visualizacao' );

/**
 * Artigos mais lidos (por visualizações; sem visualizações, os mais recentes).
 *
 * @param int $total
 * @return WP_Post[]
 */
function ama_blogue_mais_lidos( int $total = 4 ): array
{
    $mais_lidos = get_posts( [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $total,
        'ignore_sticky_posts' => true,
        'meta_key'            => 'visualizacoes',
        'orderby'             => [ 'meta_value_num' => 'DESC', 'date' => 'DESC' ],
    ] );

    if ( count( $mais_lidos ) < $total ) {
        $mais_lidos = array_merge( $mais_lidos, get_posts( [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $total - count( $mais_lidos ),
            'ignore_sticky_posts' => true,
            'post__not_in'        => wp_list_pluck( $mais_lidos, 'ID' ),
        ] ) );
    }

    return $mais_lidos;
}

/**
 * Resumo de um artigo para o blogue (sem HTML, com N palavras).
 *
 * @param int|WP_Post $post
 * @param int         $palavras
 * @return string
 */
function ama_blogue_resumo( $post, int $palavras = 25 ): string
{
    return wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), $palavras );
}
