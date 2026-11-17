<?php
/**
 * Homepage — secção "O Selo de Usabilidade e Acessibilidade em números" (#7).
 *
 * Substitui as 2 colunas (texto + gráfico dos sítios web) por:
 *   título a toda a largura
 *   [descrição] [gráfico dos sítios web] [gráfico das aplicações móveis]
 * e troca a data fixa do parágrafo pelo shortcode [data-atualizacao-sitios-selo]
 * (data do último carregamento com sucesso dos dados).
 *
 * Uso (na raiz do WordPress):
 *   php caminho/para/homepage-selo-numeros.php            # simulação: mostra o que mudaria
 *   php caminho/para/homepage-selo-numeros.php --aplicar  # grava
 *
 * Ou com WP-CLI: wp eval-file caminho/para/homepage-selo-numeros.php [--aplicar]
 *
 * O conteúdo anterior fica guardado no meta "_ama_conteudo_antigo_selo_numeros" da página.
 */

if ( ! defined( 'ABSPATH' ) ) {
	$wp_load = getcwd() . '/wp-load.php';
	if ( ! file_exists( $wp_load ) ) {
		fwrite( STDERR, "Corra este script na raiz do WordPress (onde está o wp-load.php).\n" );
		exit( 1 );
	}
	require $wp_load;
}

$argumentos = $GLOBALS['argv'] ?? [];
if ( isset( $args ) && is_array( $args ) ) { // wp eval-file
	$argumentos = array_merge( $argumentos, $args );
}
$aplicar = in_array( '--aplicar', $argumentos, true );

$pagina_id = (int) get_option( 'page_on_front' );
$pagina    = $pagina_id ? get_post( $pagina_id ) : null;

if ( ! $pagina ) {
	echo "Não há página inicial definida (Definições > Leitura).\n";
	exit( 1 );
}

$conteudo = $pagina->post_content;

if ( false !== strpos( $conteudo, 'selo-numeros' ) ) {
	echo "A secção já foi alterada (encontrei a classe selo-numeros). Nada a fazer.\n";
	exit( 0 );
}

// As colunas que têm o título "... em números" e o [grafico-selos].
$padrao = '#<!-- wp:columns -->\s*<div class="wp-block-columns">(?:(?!<!-- wp:columns).)*?em números.*?\[grafico-selos[^\]]*\].*?<!-- /wp:columns -->#s';

if ( ! preg_match( $padrao, $conteudo, $m ) ) {
	echo "Não encontrei a secção \"em números\" com o [grafico-selos] na página {$pagina_id}. Nada foi alterado.\n";
	exit( 1 );
}

$antigo = $m[0];

// Título (mantém o texto e as classes atuais).
if ( ! preg_match( '#<!-- wp:heading.*?<!-- /wp:heading -->#s', $antigo, $titulo ) ) {
	echo "Não encontrei o título da secção. Nada foi alterado.\n";
	exit( 1 );
}

// Parágrafos da descrição; a data fixa passa a ser o shortcode.
preg_match_all( '#<!-- wp:paragraph.*?<!-- /wp:paragraph -->#s', $antigo, $paragrafos );

$descricao = [];
foreach ( $paragrafos[0] as $p ) {
	$p = preg_replace(
		'#(no passado dia\s+)(?:<em>)?\[?\d{1,2} de [a-zç]+ de \d{4}\]?(?:</em>)?#u',
		'$1[data-atualizacao-sitios-selo]',
		$p
	);
	// Na coluna estreita não faz sentido limitar a largura (w-55).
	$p = str_replace( [ ',"className":"mb-3 w-55"', '"className":"mb-3 w-55"', ' w-55' ], [ ',"className":"mb-3"', '"className":"mb-3"', '' ], $p );
	$descricao[] = $p;
}

if ( false === strpos( implode( '', $descricao ), '[data-atualizacao-sitios-selo]' ) ) {
	echo "Aviso: não encontrei a frase \"no passado dia <data>\"; a data não foi trocada pelo shortcode.\n";
}

$novo = $titulo[0] . "\n\n"
	. '<!-- wp:columns {"className":"selo-numeros"} -->' . "\n"
	. '<div class="wp-block-columns selo-numeros"><!-- wp:column {"width":"40%"} -->' . "\n"
	. '<div class="wp-block-column" style="flex-basis:40%">' . implode( "\n\n", $descricao ) . '</div>' . "\n"
	. '<!-- /wp:column -->' . "\n\n"
	. '<!-- wp:column {"width":"30%"} -->' . "\n"
	. '<div class="wp-block-column" style="flex-basis:30%"><!-- wp:shortcode -->' . "\n"
	. '[grafico-selos]' . "\n"
	. '<!-- /wp:shortcode --></div>' . "\n"
	. '<!-- /wp:column -->' . "\n\n"
	. '<!-- wp:column {"width":"30%"} -->' . "\n"
	. '<div class="wp-block-column" style="flex-basis:30%"><!-- wp:shortcode -->' . "\n"
	. '[grafico-selos tipo="aplicacao_movel"]' . "\n"
	. '<!-- /wp:shortcode --></div>' . "\n"
	. '<!-- /wp:column --></div>' . "\n"
	. '<!-- /wp:columns -->';

echo "Página {$pagina_id} ({$pagina->post_title})\n\n--- ANTES ---\n{$antigo}\n\n--- DEPOIS ---\n{$novo}\n\n";

if ( ! $aplicar ) {
	echo "Simulação: nada foi gravado. Para aplicar, junte --aplicar.\n";
	exit( 0 );
}

update_post_meta( $pagina_id, '_ama_conteudo_antigo_selo_numeros', wp_slash( $conteudo ) );

$resultado = wp_update_post( [
	'ID'           => $pagina_id,
	'post_content' => wp_slash( str_replace( $antigo, $novo, $conteudo ) ),
], true );

if ( is_wp_error( $resultado ) ) {
	echo 'Erro: ' . $resultado->get_error_message() . "\n";
	exit( 1 );
}

echo "Gravado. Conteúdo anterior guardado no meta _ama_conteudo_antigo_selo_numeros.\n";
