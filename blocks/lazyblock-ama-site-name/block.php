<?php
/**
 * Example Block Template.
 *
 * @var  array  $attributes Block attributes.
 * @var  array  $block Block data.
 * @var  string $context Preview context [editor,frontend].
 */
if(is_front_page()) {
	echo '<h1 class="site-name mb-0">'.$attributes['site-name'].'</h1>';
} else {
	echo '<span class="site-name mb-0">'.$attributes['site-name'].'</span>';
}


