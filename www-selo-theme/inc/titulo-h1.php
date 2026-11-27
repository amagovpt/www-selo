<?php
/**
 * Título da página (H1) com HTML, separado do título do documento.
 *
 * O WordPress usa o mesmo título para o <title>, o breadcrumb, as listagens e o <h1>.
 * Como o <title> não aceita HTML, não era possível marcar no H1 abreviaturas ou
 * mudanças de língua, por exemplo:
 *
 *   <title>ARTE e W3C lutam contra a arte de phishing!</title>
 *   <h1><abbr title="Agência para a Reforma Tecnológica do Estado">ARTE</abbr> e
 *       <abbr title="World Wide Web Consortium" lang="en">W3C</abbr> lutam contra a arte de
 *       <em lang="en">phishing</em>!</h1>
 *
 * Este módulo acrescenta ao editor um campo opcional «Título da página (H1)», que aceita
 * HTML de texto (abbr, em, span lang…). Se estiver preenchido, é usado só no <h1>; o título
 * normal continua a ser usado no <title>, no breadcrumb e nas listagens. Se estiver vazio,
 * o <h1> mostra o título normal, como antes.
 *
 * Nos modelos: <h1><?= ama_titulo_h1(); ?></h1>
 *
 * @package www-selo-theme
 */

defined( 'ABSPATH' ) || exit;

const AMA_TITULO_H1_META = '_ama_titulo_h1';

/**
 * Elementos e atributos permitidos no H1: só marcação de texto, sem links nem blocos.
 */
function ama_titulo_h1_tags_permitidas(): array {
	$comuns = [ 'lang' => true, 'dir' => true, 'title' => true ];
	$tags   = [];
	foreach ( [ 'abbr', 'span', 'em', 'strong', 'i', 'b', 'q', 'cite', 'dfn', 'code', 'small', 'sup', 'sub', 'bdi', 'mark' ] as $tag ) {
		$tags[ $tag ] = $comuns;
	}
	$tags['br']   = [];
	$tags['time'] = $comuns + [ 'datetime' => true ];

	return (array) apply_filters( 'ama_titulo_h1_tags_permitidas', $tags );
}

/**
 * Limpa o HTML do título: só os elementos permitidos, sem quebras de linha nem espaços a mais.
 */
function ama_titulo_h1_limpar( string $html ): string {
	$html = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $html );
	$html = wp_kses( (string) $html, ama_titulo_h1_tags_permitidas() );
	$html = preg_replace( '/\s+/u', ' ', $html );

	return trim( (string) $html );
}

/**
 * Conteúdo do H1 de um conteúdo: o título com HTML, se existir, ou o título normal.
 *
 * @param int|WP_Post|null $post Conteúdo (por omissão, o atual).
 * @return string HTML seguro para pôr dentro do <h1>.
 */
function ama_titulo_h1( $post = null ): string {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	$html = ama_titulo_h1_limpar( (string) get_post_meta( $post->ID, AMA_TITULO_H1_META, true ) );
	if ( '' !== wp_strip_all_tags( $html ) ) {
		return $html;
	}

	return wp_kses( get_the_title( $post ), ama_titulo_h1_tags_permitidas() );
}

/**
 * Tipos de conteúdo com o campo: todos os públicos com editor (páginas, artigos, facilitadores…).
 */
function ama_titulo_h1_tipos(): array {
	$tipos = array_filter(
		get_post_types( [ 'public' => true, 'show_ui' => true ] ),
		static fn( $tipo ) => 'attachment' !== $tipo && post_type_supports( $tipo, 'title' )
	);

	return (array) apply_filters( 'ama_titulo_h1_tipos', array_values( $tipos ) );
}

/**
 * Regista o campo para que a API REST (editor de blocos, revisões) o conheça.
 */
function ama_titulo_h1_registar_meta(): void {
	foreach ( ama_titulo_h1_tipos() as $tipo ) {
		register_post_meta(
			$tipo,
			AMA_TITULO_H1_META,
			[
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'sanitize_callback' => static fn( $valor ) => ama_titulo_h1_limpar( (string) $valor ),
				'auth_callback'     => static fn( $permitido, $meta, $post_id ) => current_user_can( 'edit_post', $post_id ),
				'show_in_rest'      => false,
			]
		);
	}
}
add_action( 'init', 'ama_titulo_h1_registar_meta', 20 );

/**
 * Caixa no editor, logo abaixo do conteúdo.
 */
function ama_titulo_h1_caixa(): void {
	foreach ( ama_titulo_h1_tipos() as $tipo ) {
		add_meta_box( 'ama-titulo-h1', 'Título da página (H1) com HTML', 'ama_titulo_h1_caixa_html', $tipo, 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes', 'ama_titulo_h1_caixa' );

function ama_titulo_h1_caixa_html( WP_Post $post ): void {
	$valor = (string) get_post_meta( $post->ID, AMA_TITULO_H1_META, true );
	wp_nonce_field( 'ama_titulo_h1_guardar', 'ama_titulo_h1_nonce' );
	?>
	<p>
		<label for="ama-titulo-h1-campo"><strong>Título visível no H1</strong> (opcional)</label>
	</p>
	<textarea id="ama-titulo-h1-campo" name="ama_titulo_h1" rows="2" class="large-text code" aria-describedby="ama-titulo-h1-ajuda"><?= esc_textarea( $valor ); ?></textarea>
	<div id="ama-titulo-h1-ajuda">
		<p class="description">
			Preencha só se o título precisar de HTML, por exemplo abreviaturas ou palavras noutra língua.
			O título normal (acima) continua a ser usado no separador do navegador (&lt;title&gt;), no caminho de navegação e nas listagens; deixe este campo vazio para usar o mesmo título no H1.
		</p>
		<p class="description">
			Exemplo: <code>&lt;abbr title="World Wide Web Consortium" lang="en"&gt;W3C&lt;/abbr&gt; contra o &lt;em lang="en"&gt;phishing&lt;/em&gt;</code>
		</p>
		<p class="description">
			Elementos permitidos: <?= esc_html( implode( ', ', array_keys( ama_titulo_h1_tags_permitidas() ) ) ); ?>, com os atributos lang, dir e title. Links e outros elementos são removidos.
		</p>
	</div>
	<?php
}

function ama_titulo_h1_guardar( int $post_id ): void {
	if ( ! isset( $_POST['ama_titulo_h1_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ama_titulo_h1_nonce'] ) ), 'ama_titulo_h1_guardar' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$valor = ama_titulo_h1_limpar( (string) wp_unslash( $_POST['ama_titulo_h1'] ?? '' ) );
	if ( '' === wp_strip_all_tags( $valor ) ) {
		delete_post_meta( $post_id, AMA_TITULO_H1_META );
	} else {
		update_post_meta( $post_id, AMA_TITULO_H1_META, $valor );
	}
}
add_action( 'save_post', 'ama_titulo_h1_guardar' );
