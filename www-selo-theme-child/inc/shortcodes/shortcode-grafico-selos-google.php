<?php

function grafico_selos_google_cb( $attrs )
{
    $attrs = shortcode_atts(
        [
            "aria-label"    => '',
            "ouro"          => "",
            "prata"         => "",
            "bronze"        => ""
        ], $attrs
    );


    $aria_label = "";
    if($attrs["aria-label"] !== '') {
        $aria_label  = sprintf('aria-label="%s"', $attrs["aria-label"]);
    }

    // Enqueue Google Charts JS
    wp_enqueue_script(
        "Google-Charts-js",
        'https://www.gstatic.com/charts/loader.js',
        "",
        "1.0.0",
         true// Load in the footer
    );

    // Enqueue Piechart (doughnut)
    wp_enqueue_script(
        "Selos-chart-js",
        get_stylesheet_directory_uri()."/assets/js/selos-chart.js",
        ["jquery", "Google-Charts-js"],
        "1.0.0",
         true// Load in the footer
    );

    /**
     * Se o número de Selos não é passado como parametro neste shortcode,
     * vamos buscá-los a outro shortcode, que lê diretamente da nossa base de dados
     * quantos Selos temos
     */
    if($attrs["ouro"] !== ""){
       $selos_ouro = intval($attrs["ouro"]);
    } else {
        $selos_ouro   = do_shortcode('[indicador selo="ouro"]');
    }

    if($attrs["prata"] !== ""){
        $selos_prata = intval($attrs["prata"]);
    } else {
        $selos_prata   = do_shortcode('[indicador selo="prata"]');
    }

    if($attrs["bronze"] !== ""){
        $selos_bronze = intval($attrs["bronze"]);
    } else {
        $selos_bronze   = do_shortcode('[indicador selo="bronze"]');
    }

    wp_localize_script(
        "Selos-chart-js",
        "chartData",
        [
            "selosOuro"     => intval($selos_ouro),
            "selosPrata"    => intval($selos_prata),
            "selosBronze"   => intval($selos_bronze)
        ]
    );

    ob_start(); ?>
    <div id="barchart" role="img" style="width: 400px; height: 400px;" <?= $aria_label  ?>></div>
<?php return ob_get_clean();

}
add_shortcode("grafico-selos-google", "grafico_selos_google_cb");
