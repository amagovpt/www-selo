<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Shortcode para mostrar o número de selos de um tipo específico.
 *
 * @param array $attrs Atributos passados para o shortcode.
 * @return string|null
 */
function indicador_selo_cb($attrs): ?string
{
    $attrs = shortcode_atts( array(
        'selo' => '%',
    ), $attrs );

    if ($attrs['selo'] === '0') {
        return null;
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'sitios_web_com_selo';

    // Query a base de dados para obter o número de selos
    $result = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT COUNT(*) AS selos FROM $table_name WHERE LOWER(`selo`) LIKE %s",
            strtolower($attrs['selo'])
        )
    );

    if ($result) {
        // Process the result if needed
        // For example, you can access the count of selos: $result[0]->selos
        // Then return the indicator number as a string
        return $result[0]->selos;
    }

    return null;
}

add_shortcode('indicador', 'indicador_selo_cb');
