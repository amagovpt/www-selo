<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<form role="search" class="search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Motor de pesquisa">
	<label for="site-search">Pesquisar por:</label>
	<div class="input-group">
		<input type="search" class="search-field" id="site-search" name="s" value="<?php the_search_query(); ?>" placeholder="Pesquisa...">
		<button type="submit" class="search-submit">
			<span class="icon-search icon" aria-label="Pesquisar" role="img"></span>
		</button>
	</div>
</form>
<?php
