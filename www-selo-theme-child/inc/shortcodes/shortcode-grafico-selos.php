<?php

if(!defined('ABSPATH')) { exit; }

function grafico_selos_cb( $attrs ): ?string {

    $attrs = shortcode_atts(
        [
            "ouro"          => '',
            "prata"         => '',
            "bronze"        => '',
            "aria-label"    => '',
        ],
        $attrs
    );

    $output = '';

    $aria_label = '';
    $ouro = "";
    $prata = "";
    $bronze = "";

    
    if (!empty($attrs['ouro'])) {
        $ouro = $attrs['ouro'];
    } else {
        $ouro = numero_sitios_web_com_selo("ouro");
    }

    if (!empty($attrs['prata'])) {
        $prata = $attrs['prata'];
    } else {
        $prata = numero_sitios_web_com_selo("prata");
    }

    if (!empty($attrs['bronze'])) {
        $bronze = $attrs['bronze'];
    } else {
        $bronze = numero_sitios_web_com_selo("bronze");
    }

    if (empty($attrs['aria-label'])) {
        $aria_label = "%s sítios web com Selo de Usabilidade e Acessibilidade.";
    }

    wp_enqueue_script(
        'chartJS', 
        'https://cdn.jsdelivr.net/npm/chart.js', 
        [], 
        null, 
        true
    );

    wp_enqueue_script(
        'grafico-selos',
        get_stylesheet_directory_uri() . '/assets/js/grafico-selos.js',
        ['jquery', 'chartJS'],
        "1.1",
        true
    );

    $chart_data = [
        'selosOuro'     => $ouro,
        'selosPrata'    => $prata, 
        'selosBronze'   => $bronze, 
        'totalSelos'    => $ouro + $prata + $bronze,
    ];

    $aria_label = sprintf($aria_label, $chart_data['totalSelos']);
    
    wp_localize_script('grafico-selos', 'chartData', $chart_data);

    $output .= '<div class="grafico-selos-container">';
    
    // Accessible Tabs
    $output .= '<div class="grafico-selos-tabs">';
    $output .= '<div role="tablist" aria-label="Visualização de dados dos selos" class="tabs-list">';
    $output .= '<button role="tab" id="tab-grafico" aria-selected="true" aria-controls="panel-grafico" tabindex="0" class="tab-button active">Gráfico</button>';
    $output .= '<button role="tab" id="tab-tabela" aria-selected="false" aria-controls="panel-tabela" tabindex="-1" class="tab-button">Tabela</button>';
    $output .= '</div>';
    
    // Chart Panel (Visible by default)
    $output .= '<div role="tabpanel" id="panel-grafico" aria-labelledby="tab-grafico" tabindex="0" class="tab-panel">';
    $output .= '<div id="canva-wrapper" style="width: 100%; max-width: 540px;">';
    $output .= '<canvas id="graficoSelos" width="540" height="540" role="img" aria-label="Gráfico do número de sítios web com selo. Para mais detalhes selecionar o separador tabela."></canvas>';
    $output .= '</div>';
    $output .= '</div>';
    
    // Table Panel (Hidden by default)
    $output .= '<div role="tabpanel" id="panel-tabela" aria-labelledby="tab-tabela" tabindex="0" class="tab-panel" hidden>';
    $output .= '<table class="ama table">';
    $output .= '<caption>Número de sítios web com selo</caption>';
    $output .= '<thead><tr><th scope="col">Tipo de Selo</th><th scope="col">Quantidade</th></tr></thead>';
    $output .= '<tbody>';
    $output .= '<tr><td>Ouro</td><td>' . $ouro . '</td></tr>';
    $output .= '<tr><td>Prata</td><td>' . $prata . '</td></tr>';
    $output .= '<tr><td>Bronze</td><td>' . $bronze . '</td></tr>';
    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '</div>';
    
    $output .= '</div>'; // Close tabs
    $output .= '</div>'; // Close container

    return $output;
}
add_shortcode('grafico-selos', 'grafico_selos_cb');