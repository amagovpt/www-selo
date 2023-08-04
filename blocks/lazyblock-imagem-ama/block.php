<?php
/**
 * Lazyblock para Gutenberg para imagens.
 *
 * @var  array  $attributes Block attributes.
 * @var  array  $block Block data.
 * @var  string $context Preview context [editor,frontend].
 */
?>

<img alt="<?= ( ( ! empty( $attributes['imagem-alt'] ) ) ?? '' ) ?>" src="<?= $attributes['imagem-ama']['url'] ?>" class="img-fluid">
<span>Custom image block</span>