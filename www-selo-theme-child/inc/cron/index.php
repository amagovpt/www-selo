<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Path to the shortcodes directory
$shortcode_dir = get_stylesheet_directory() . '/inc/cron/';

// Get all PHP files in the shortcodes directory
$shortcode_files = glob( $shortcode_dir . 'cron-*.php' );

// Include each shortcode file
foreach ( $shortcode_files as $file ) {
    require_once $file;
}
