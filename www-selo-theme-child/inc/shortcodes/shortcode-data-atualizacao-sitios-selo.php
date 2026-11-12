<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode para exibir a data de atualização dos sítios com selo.
 *
 * @return string
 */
function shortcode_data_atualizacao_sitios_selo_cb() : ?string 
{
    $output = '';
    $output = data_de_atualizacao_sitios_web_com_selo();
    return $output;
}
add_shortcode('data-atualizacao-sitios-selo', 'shortcode_data_atualizacao_sitios_selo_cb');