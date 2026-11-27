<?php
/**
 * Cabeçalho uniforme das páginas (#3).
 *
 * Faixa azul a toda a largura (a mesma cor e altura da faixa da página inicial) com:
 * - H1 com o título;
 * - descrição: o excerto da página (aceita HTML);
 * - à direita, a imagem destacada (ícone ou imagem decorativa).
 * O breadcrumb fica por baixo da faixa.
 *
 * Uso: get_template_part( 'template-parts/cabecalho', 'pagina', $args ), com $args opcionais:
 *   'post_id'   => ID da página de onde vêm título, excerto e imagem (por omissão, a atual);
 *   'titulo'    => HTML do H1 (por omissão, ama_titulo_h1(): o título com HTML, se existir);
 *   'descricao' => HTML da descrição (null = excerto da página; '' = sem descrição);
 *   'imagem'    => HTML da imagem (null = imagem destacada; '' = sem imagem);
 *   'migalhas'  => [ [ 'url' => …, 'titulo' => … ], … ] entre "Início" e a página atual
 *                  (null = página mãe, se existir);
 *   'atual'     => texto da página atual no breadcrumb (por omissão, o título).
 *
 * @package www-selo-theme
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : [];
$post_id = (int) ( $args['post_id'] ?? get_the_ID() );

$titulo = $args['titulo'] ?? ( $post_id ? ama_titulo_h1( $post_id ) : '' );

// Descrição: o excerto escrito na página (com HTML), nunca o resumo automático do conteúdo.
if ( array_key_exists( 'descricao', $args ) && null !== $args['descricao'] ) {
	$descricao = (string) $args['descricao'];
} else {
	$excerto   = $post_id ? (string) get_post_field( 'post_excerpt', $post_id ) : '';
	$descricao = '' !== trim( $excerto ) ? wpautop( $excerto ) : '';
}

if ( array_key_exists( 'imagem', $args ) && null !== $args['imagem'] ) {
	$imagem = (string) $args['imagem'];
} else {
	$imagem = ( $post_id && has_post_thumbnail( $post_id ) )
		? get_the_post_thumbnail( $post_id, 'medium_large', [ 'class' => 'cabecalho-pagina__img', 'loading' => 'eager' ] )
		: '';
}

if ( array_key_exists( 'migalhas', $args ) && null !== $args['migalhas'] ) {
	$migalhas = (array) $args['migalhas'];
} else {
	$migalhas = [];
	if ( $post_id ) {
		foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $ancestral ) {
			$migalhas[] = [ 'url' => get_permalink( $ancestral ), 'titulo' => get_the_title( $ancestral ) ];
		}
	}
}

// O breadcrumb usa o título normal, sem o HTML do H1 (ver inc/titulo-h1.php).
$atual = $args['atual'] ?? ( $args['titulo'] ?? ( $post_id ? get_the_title( $post_id ) : '' ) );
?>
<div class="cabecalho-pagina">
	<div class="container">
		<div class="cabecalho-pagina__grelha<?= '' !== $imagem ? ' cabecalho-pagina__grelha--com-imagem' : ''; ?>">
			<div class="cabecalho-pagina__texto">
				<h1><?= wp_kses_post( $titulo ); ?></h1>
				<?php if ( '' !== trim( wp_strip_all_tags( $descricao ) ) ) : ?>
					<div class="cabecalho-pagina__descricao">
						<?= wp_kses_post( $descricao ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $imagem ) : ?>
				<div class="cabecalho-pagina__imagem">
					<?= wp_kses_post( $imagem ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="container">
	<nav aria-label="Você está aqui" class="cabecalho-pagina__migalhas">
		<ol class="breadcrumb">
			<li class="breadcrumb-item">
				<a href="<?= esc_url( home_url( '/' ) ); ?>">Início</a>
			</li>
			<?php foreach ( $migalhas as $migalha ) : ?>
				<li class="breadcrumb-item">
					<a href="<?= esc_url( $migalha['url'] ); ?>"><?= esc_html( wp_strip_all_tags( $migalha['titulo'] ) ); ?></a>
				</li>
			<?php endforeach; ?>
			<li class="breadcrumb-item active" aria-current="page">
				<?= esc_html( wp_strip_all_tags( $atual ) ); ?>
			</li>
		</ol>
	</nav>
</div>
