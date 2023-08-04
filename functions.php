<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function ama_scripts() {
    $the_theme = wp_get_theme();
    $theme_version = $the_theme->get( 'Version' );

    wp_enqueue_style( 'bootstrap-5-css', '//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css', array(), '5.0.2' );
    wp_enqueue_style( 'ama-style', get_stylesheet_uri(), array(), $theme_version );

    wp_enqueue_script( 'bootstrap-5-js-bundle', '//cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', array(), '5.0.2' );
    wp_enqueue_script( 'ama-scripts', get_template_directory_uri() . '/js/scripts.js', array('jquery'), $theme_version );
}
add_action( 'wp_enqueue_scripts', 'ama_scripts' );

add_post_type_support( 'page', 'excerpt' );

function ama_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );

    register_nav_menus(
        array(
            'primary' => 'Menu principal',
            'footer' => 'Menu de rodapé',
        ),
    );
}
add_action( 'after_setup_theme', 'ama_theme_setup' );

function ama_widgets() {
    register_sidebar( array(
        'name'          => 'Title',
        'id'            => 'title',
        'before_widget' => '<div class="site-title">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Description',
        'id'            => 'description',
        'before_widget' => '<div class="site-description">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Title Ecossistema AMA',
        'id'            => 'title-ecossistema-ama',
        'before_widget' => '<div class="title-ecosssistema-ama">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Description Ecossistema AMA',
        'id'            => 'description-ecossistema-ama',
        'before_widget' => '<div class="description-ecossistema-ama">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Left Column Ecossistema AMA',
        'id'            => 'left-column-ecossistema-ama',
        'before_widget' => '<div class="left-column-ecossistema-ama">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Center Column Ecossistema AMA',
        'id'            => 'center-column-ecossistema-ama',
        'before_widget' => '<div class="center-column-ecossistema-ama">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Right Column Ecossistema AMA',
        'id'            => 'right-column-ecossistema-ama',
        'before_widget' => '<div class="right-column-ecossistema-ama">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Footer',
        'id'            => 'footer',
        'before_widget' => '<div class="footer-logo">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

    register_sidebar( array(
        'name'          => 'Copyright',
        'id'            => 'copyright',
        'before_widget' => '<div class="copyright">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );
}
add_action( 'widgets_init', 'ama_widgets' );

function ama_index_menu( $atts = array() ) {
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );

    $shortcode_atts = shortcode_atts(
        array(
            'esconder_numeracao' => false,
        ), $atts
    );

    $content = get_the_content();
    $count = substr_count( $content, '<h2 id="');

    $menu = '';

    if ( $count > 0 ) {
        $menu .= '<div class="sidebar-menu">';
        $menu .= '<h2>Índice de conteúdos</h2>';

        preg_match_all( '@<h2.id=.*?>(.*?)<\/h2>@', $content, $matches );
        $headings = $matches[1];

        preg_match_all( '@<h2.id="?(.*?)">.*?<\/h2>@', $content, $matches );
        $hrefs = $matches[1];

        $class = $shortcode_atts['esconder_numeracao'] ? ' class="hide-bullets"' : '';

        $menu .= '<ol' . $class . '>';

        for ( $i = 0; $i < count( $headings ); $i++ ) {
            $menu .= '<li><a href="#' . $hrefs[$i] . '">' . $headings[$i] . '</a></li>';
        }

        $menu .= '</ol>';
        $menu .= '</div>';

    }

    return $menu;
}
add_shortcode( 'indice', 'ama_index_menu' );

add_action( 'wp_footer', function () {
    wp_dequeue_style( 'core-block-supports' );
});

include 'inc/indicadores.php';

function ama_pagination( $args = [], $class = 'pagination' ) {

    if ( $GLOBALS['wp_query']->max_num_pages <= 1 ) return;

    $args = wp_parse_args( $args, [
        'mid_size'           => 2,
        'prev_next'          => true,
        'prev_text'          => '<span class="icon-arrow-drop arrow-left" aria-hidden="true"></span>',
        'next_text'          => '<span class="icon-arrow-drop arrow-right" aria-hidden="true"></span>',
        'screen_reader_text' => 'resultados pesquisa',
        'type'               => 'array',
        'current'            => max( 1, get_query_var('paged') ),
    ]);

    $links = paginate_links($args);

    ?>

    <nav class="mt-5" aria-label="Paginação <?php echo $args['screen_reader_text']; ?>">

        <ul class="pagination">

            <?php

            foreach ( $links as $key => $link ) { ?>

                <li class="page-item <?php echo strpos( $link, 'current' ) ? 'active' : '' ?>">

                    <?php echo str_replace( 'page-numbers', 'page-link', $link ); ?>

                </li>

            <?php } ?>

        </ul>

    </nav>

    <?php
}

/**
 * Adiciona suporte a cores personalizadas no editor de blocos.
 *
 * @return void
 */
function your_theme_color_support(): void {
    $custom_color_palette = array(
        [
            'name'  => __( 'Primaria', 'ama' ),
            'slug'  => 'primaria',
            'color' => '#333399',
        ],
        [
            'name'  => __( 'Secundaria', 'ama' ),
            'slug'  => 'secundaria',
            'color' => '#b1e4b2',
        ],
        [
            'name'  => __( 'Terciaria', 'ama' ),
            'slug'  => 'terciaria',
            'color' => '#edeceb',
        ],
        [
            'name'  => __( 'Quaternaria', 'ama' ),
            'slug'  => 'quaternaria',
            'color' => '#f0a502',
        ],
        [
            'name'  => __( 'preto', 'ama' ),
            'slug'  => 'preto',
            'color' => '#000000',
        ],
        [
            'name'  => __( 'branco', 'ama' ),
            'slug'  => 'branco',
            'color' => '#ffffff',
        ],
    );

    add_theme_support(
        'editor-color-palette',
        $custom_color_palette
    );
}
add_action( 'after_setup_theme', 'your_theme_color_support' );

/**
 * Remove o cardinal das classes dos blocos, para não cortar o nome duma classe a meio e crie um ID inválido.
 *
 * @param array $classes
 * @return array|null
 */
function modify_block_classes( $classes, $block ): ?array {
    foreach ( $classes as &$class ) {
        $class = str_replace( 'has-#', 'has-', $class );
    }
    return $classes;
}
add_filter( 'block_class', 'modify_block_classes', 10, 2 );


//TODO: On theme activation, verificar se existem os posts to tipo lazy block, se não existirem, criar através do import do ficheiro JSON que esta na pasta do tema > blocks > imports
function check_and_create_post_on_theme_activation() {
	// Replace 'YOUR_POST_TITLE' with the title of the post you want to check/create
	$post_title = 'YOUR_POST_TITLE';

	// Check if the post exists
	$post = get_page_by_title($post_title, OBJECT, 'post');

	// If the post doesn't exist, create it
	if (!$post) {
		// Replace 'YOUR_POST_CONTENT' with the content of the post
		$post_content = 'YOUR_POST_CONTENT';

		$new_post = array(
			'post_title'   => $post_title,
			'post_content' => $post_content,
			'post_status'  => 'publish',
			'post_author'  => 1, // Replace with the author ID if needed
			'post_type'    => 'post',
		);

		// Insert the post into the database
		$post_id = wp_insert_post($new_post);

		if ($post_id) {
			// The post was created successfully
			// You can perform additional actions here if needed
		} else {
			// There was an error creating the post
			// You can handle the error here if needed
		}
	}
}
add_action('after_switch_theme', 'check_and_create_post_on_theme_activation');


