<?php
/**
 * Accordion Template.
 *
 * @var  array  $attributes Block attributes.
 * @var  array  $block Block data.
 * @var  string $context Preview context [editor,frontend].
 */

//var_dump($attributes);
?>
<div class="accordion">
	<div class="accordion-item">
		<h2 class="accordion-header">
			<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $attributes["blockId"]?>-panel" aria-expanded="false">
				<?=$attributes["titulo"]?>
			</button>
		</h2>
		<div id="<?= $attributes["blockId"]?>-panel" class="accordion-collapse collapse">
			<div class="accordion-body">
				<?=$attributes["conteudo"]?>
			</div>
		</div>
	</div>
</div>
