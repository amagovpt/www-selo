<?php
/**
 * Example Block Template.
 *
 * @var  array  $attributes Block attributes.
 * @var  array  $block Block data.
 * @var  string $context Preview context [editor,frontend].
 */
wp_enqueue_style(
	'block-ama-site-name',
	get_template_directory_uri() . '/blocks/lazyblock-ama-site-name/css/ama-site-name.css',
	[],
	filemtime(get_template_directory() . '/blocks/lazyblock-ama-site-name/css/ama-site-name.css')
);
if(is_front_page()) {
	echo '<h1 class="site-name">'.$attributes['site-name'].'</h1>';
} else {
	echo '<span class="site-name">'.$attributes['site-name'].'</span>';
}


