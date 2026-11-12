<?php

function posts_cards_cb( $attrs ): ?string {
    $html = '';

    $attrs = shortcode_atts(
        [
            "total" => 4,
        ],
        $attrs
    );

    //
    $posts = new WP_Query([
        "post_type"         => "post",
        "post_status"       => "publish",
        "orderby"           => "date",
        "order"             => "DESC",
        "posts_per_page"    => $attrs["total"],
    ]);

    // Mostra os resultados
    if(!is_wp_error($posts)) {
        if(is_countable($posts->posts) && count($posts->posts)){
            ob_start();
            // Loop through all the posts
            if ($posts->have_posts()) {
                for($i = 0; $i < $attrs["total"]; $i++) {
                    $posts->the_post();
                    get_template_part('template-parts/post', 'card');
                }
            }

            $html .= ob_get_clean();
        }
    }
    wp_reset_postdata();

    return sprintf('<div class="container p-0"><div class="row"><div class="col"><div class="grid-4 separador">%s</div></div> </div> </div>', $html);
}

add_shortcode("posts-cards", "posts_cards_cb");