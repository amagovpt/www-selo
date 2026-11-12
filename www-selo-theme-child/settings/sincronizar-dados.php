<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Enqueue JavaScript for handling AJAX action
function enqueue_custom_scripts(): void
{
    global $pagenow;
    if ($pagenow == 'options-general.php' && $_GET['page'] == 'ama-definicoes') {
        wp_enqueue_script(
            'ama-settings-script',
            get_stylesheet_directory_uri() . '/assets/js/admin.js',
            ["jquery"],
            '1.0',
            true
        );

        ama_scripts();
    }

}
add_action('admin_enqueue_scripts', 'enqueue_custom_scripts');

/**
 * Callback para renderizar a nova seção de configuração.
 *
 * Nesta secção vamos adicionar um botão para executar a ação AJAX,
 * para forçar a sincronização de dados entre o sítio ‘web’ do Selo e o Observatório.
 *
 * @return void
 */
function ama_ajax_action_section_callback() {
    // Output the HTML content for the new section
    echo '<p>Esta ação força a execução da tarefa de sincronização de dados entre o Observatório<br>
 e o site do Selo para as fichas dos sítios web com Selo de Usabilidade e Acessibilidade.</p>';
    echo '<button type="button" id="ama-sync-button" class="button">Sincronizar Dados</button>';
    echo '<div id="ama-sync-response"></div>';
}
add_action('admin_init', 'ama_register_ajax_action_section');

/**
 * Regista uma nova secção para a configuração da ação AJAX.
 *
 * No 4º parametro da função add_setting_section, inserimos o nome da página onde a secção será mostrada.
 * O nome de página tem de ser diferente das outras secções, mesmo que se mostrem todas na mesma página.
 * É confuso... mas o Wordpress funciona assim.
 *
 * @return void
 */
function ama_register_ajax_action_section() {
    add_settings_section(
        'ama_ajax_action_section',
        'Executar sincronização de dados',
        'ama_ajax_action_section_callback',
        'ama_ajax_action_section'
    );
}
