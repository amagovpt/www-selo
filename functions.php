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