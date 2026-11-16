<?php

if(!defined('ABSPATH')) { exit; }

/**
 * Gráfico (e tabela equivalente) do número de selos de um tipo de plataforma.
 *
 * [grafico-selos]                         sítios web
 * [grafico-selos tipo="aplicacao_movel"]  aplicações móveis
 *
 * Os números vêm da tabela sincronizada (#6); os atributos ouro/prata/bronze
 * continuam a permitir valores fixos. Pode haver vários gráficos na mesma página.
 *
 * @param array|string $attrs
 * @return string|null
 */
function grafico_selos_cb( $attrs ): ?string {

    static $instancia = 0;
    $instancia++;

    $attrs = shortcode_atts(
        [
            "tipo"          => AMA_SELO_TIPO_SITIO_WEB,
            "ouro"          => '',
            "prata"         => '',
            "bronze"        => '',
            "aria-label"    => '',
        ],
        $attrs
    );

    $tipo = ama_selo_normalizar_tipo( $attrs['tipo'] );
    $app  = AMA_SELO_TIPO_APP === $tipo;

    $numeros = [];
    foreach ( [ 'ouro', 'prata', 'bronze' ] as $selo ) {
        $numeros[ $selo ] = '' !== $attrs[ $selo ]
            ? absint( $attrs[ $selo ] )
            : (int) numero_sitios_web_com_selo( $selo, $tipo );
    }
    $total = array_sum( $numeros );

    // Ex.: "1 aplicação móvel com Selo", "151 sítios web com Selo".
    if ( $app ) {
        $nome_plataforma = 1 === $total ? 'aplicação móvel' : 'aplicações móveis';
    } else {
        $nome_plataforma = 1 === $total ? 'sítio web' : 'sítios web';
    }
    $titulo  = sprintf( '%d %s com Selo', $total, $nome_plataforma );
    $eixo    = $app ? 'Número de aplicações móveis com Selo' : 'Número de sítios web com Selo';
    $legenda = $app ? 'Número de aplicações móveis com selo, por tipo de selo' : 'Número de sítios web com selo, por tipo de selo';

    $aria_label = '' !== $attrs['aria-label']
        ? $attrs['aria-label']
        : sprintf(
            'Gráfico: %s. Ouro: %d, Prata: %d, Bronze: %d.',
            $titulo,
            $numeros['ouro'],
            $numeros['prata'],
            $numeros['bronze']
        );

    wp_enqueue_script(
        'chartJS',
        'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
        [],
        '4.4.1',
        true
    );

    wp_enqueue_script(
        'grafico-selos',
        get_stylesheet_directory_uri() . '/assets/js/grafico-selos.js',
        ['chartJS'],
        filemtime( get_stylesheet_directory() . '/assets/js/grafico-selos.js' ),
        true
    );

    $id = 'grafico-selos-' . $instancia;

    $output  = '<div class="grafico-selos-container grafico-selos--' . esc_attr( $tipo ) . '">';

    // Separadores acessíveis (comportamento em assets/js/tabs.js).
    $output .= '<div class="grafico-selos-tabs">';
    $output .= '<div role="tablist" aria-label="' . esc_attr( 'Visualização de dados: ' . $titulo ) . '" class="tabs-list">';
    $output .= '<button type="button" role="tab" id="' . $id . '-tab-grafico" aria-selected="true" aria-controls="' . $id . '-grafico" tabindex="0" class="tab-button active">Gráfico</button>';
    $output .= '<button type="button" role="tab" id="' . $id . '-tab-tabela" aria-selected="false" aria-controls="' . $id . '-tabela" tabindex="-1" class="tab-button">Tabela</button>';
    $output .= '</div>';

    // Gráfico (visível por omissão).
    $output .= '<div role="tabpanel" id="' . $id . '-grafico" aria-labelledby="' . $id . '-tab-grafico" tabindex="0" class="tab-panel">';
    $output .= '<div class="grafico-selos-canvas">';
    $output .= sprintf(
        '<canvas id="%s-canvas" role="img" aria-label="%s" data-titulo="%s" data-eixo="%s" data-ouro="%d" data-prata="%d" data-bronze="%d"></canvas>',
        esc_attr( $id ),
        esc_attr( $aria_label ),
        esc_attr( $titulo ),
        esc_attr( $eixo ),
        $numeros['ouro'],
        $numeros['prata'],
        $numeros['bronze']
    );
    $output .= '</div>';
    $output .= '</div>';

    // Tabela equivalente.
    $output .= '<div role="tabpanel" id="' . $id . '-tabela" aria-labelledby="' . $id . '-tab-tabela" tabindex="0" class="tab-panel" hidden>';
    $output .= '<table class="ama table">';
    $output .= '<caption>' . esc_html( $legenda ) . '</caption>';
    $output .= '<thead><tr><th scope="col">Tipo de Selo</th><th scope="col">Quantidade</th></tr></thead>';
    $output .= '<tbody>';
    $output .= '<tr><th scope="row">Ouro</th><td>' . $numeros['ouro'] . '</td></tr>';
    $output .= '<tr><th scope="row">Prata</th><td>' . $numeros['prata'] . '</td></tr>';
    $output .= '<tr><th scope="row">Bronze</th><td>' . $numeros['bronze'] . '</td></tr>';
    $output .= '</tbody>';
    $output .= '<tfoot><tr><th scope="row">Total</th><td>' . $total . '</td></tr></tfoot>';
    $output .= '</table>';
    $output .= '</div>';

    $output .= '</div>'; // .grafico-selos-tabs
    $output .= '</div>'; // .grafico-selos-container

    return $output;
}
add_shortcode('grafico-selos', 'grafico_selos_cb');
