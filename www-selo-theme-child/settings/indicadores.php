<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Adicione um ‘item’ de menu no painel de administração
add_action('admin_menu', 'ama_menu_de_opcoes');

function ama_menu_de_opcoes()
{
    // Adiciona uma página de configurações ao menu Opções
    $hook_suffix = add_options_page(
        'Definições | Site Selo',
        'Definições | AMA',
        'manage_options',
        'ama-definicoes',
        'render_pagina_opcoes_cb'
    );

    // Adiciona a folha de estilos do thema e do pai.
    wp_enqueue_style(
        'ama-a11y-theme-parent',
        get_template_directory_uri() . '/style.css',
        [],
        '1.0'
    );


    wp_enqueue_style(
        'ama-a11y-theme-child',
        get_stylesheet_directory_uri() . '/style.css',
        ["ama-a11y-theme-parent"],
        '1.0'
    );


    // Enqueue scripts and styles only on this specific admin page
    add_action('load-' . $hook_suffix, 'enqueue_specific_ama_style_in_admin_page');

}

function enqueue_specific_ama_style_in_admin_page() {
    // Adiciona a folha de estilos do thema e do pai.
    wp_enqueue_style(
        'ama-a11y-theme-parent',
        get_template_directory_uri() . '/style.css',
        [],
        '1.0'
    );


    wp_enqueue_style(
        'ama-a11y-theme-child',
        get_stylesheet_directory_uri() . '/style.css',
        ["ama-a11y-theme-parent"],
        '1.0'
    );
}

/**
 * Renderiza a página de configurações.
 *
 * @return void
 */
function render_pagina_opcoes_cb() {
    ?>
    <div class="wrap">
        <div class="container ms-0">
            <div class="row">
                <div class="col">
                    <h1>Definições adicionais | AMA</h1>
                    <form method="post" action="options.php">
                        <?php
                        // Adiciona campos de configuração
                        settings_fields('ama_definicoes');
                        do_settings_sections('ama_definicoes');
                        submit_button();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <hr style="margin: 2rem 0;">
        <div class="container ms-0">
            <div class="row">
                <div class="col-md-6">
                    <!-- New form for the AJAX action button -->
                    <form id="ama-ajax-form" method="post">
                        <!-- New section for the AJAX action button -->
                        <?php do_settings_sections('ama_ajax_action_section'); ?>
                    </form>
                </div>
            </div>
        </div>
        <hr style="margin: 2rem 0;">
        <div class="container ms-0">
            <div class="row">
                <div class="col-md-6">
                    <form method="post" action="options.php">
                        <?php
                        // Adiciona campos de configuração
                        settings_fields('ai_agent_options');
                        do_settings_sections('ai_agent_options');
                        submit_button();
                        ?>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <?php
}

// Função de inicialização das opções
add_action('admin_init', 'inicializar_opcoes');

function inicializar_opcoes() {
    // Registra uma seção de configuração e campos de configuração
    add_settings_section(
        'ama_seccao_indicadores_sitios_web',
        'Dados de sítios web com Selo de Usabilidade e Acessibilidade',
        'meu_callback_secao',
        'ama_definicoes'
    );

    // Adiciona campo de configuração para a URL do endpoint de indicadores
    add_settings_field(
        'ama_indicadores_endpoint_url',
        'Endpoint da REST API do Observatório',
        'callback_campo_indicadores_endpoint_url',
        'ama_definicoes',
        'ama_seccao_indicadores_sitios_web'
    );

    // Registra as opções no base de dados do endereço do endpoint de indicadores
    register_setting(
        'ama_definicoes',
        'indicadores_endpoint_url'
    );

    // Adiciona o campo para a data do WP-Cron da atualização de dados dos Sítios ‘Web’.
    add_settings_field(
        'ama_cron_job_date',
        'Data e hora da execução da tarefa',
        'callback_campo_cron_job_date',
        'ama_definicoes',
        'ama_seccao_indicadores_sitios_web'
    );

    // Registra a opção de data para o WP-Cron na base de dados
    register_setting(
        'ama_definicoes',
        'ama_cron_job_date'
    );

    // Adiciona o campo para a frequência do WP-Cron da atualização de dados dos Sítios ‘Web’.
    add_settings_field(
        'ama_cron_job_frequency',
        'Frequência de execução da tarefa.',
        'callback_campo_cron_job_frequency',
        'ama_definicoes',
        'ama_seccao_indicadores_sitios_web'
    );

    // Registra a opção de frequencia para o WP-Cron na base de dados
    register_setting(
        'ama_definicoes',
        'ama_cron_job_frequency'
    );

    // Adiciona o campo para guardar o e-mail que ira receber notificacoes de erro na sincronização
    add_settings_field(
        'ama_email_feedback_sincronizacao',
        'Email para notificações de erro',
        'callback_campo_feedback_agent_email',
        'ama_definicoes',
        'ama_seccao_indicadores_sitios_web');

    // Registra a opção de email para notificações de erro na sincronização
    register_setting(
        'ama_definicoes',
        'ama_email_feedback_sincronizacao'
    );
}

// Callback para renderizar a seção
function meu_callback_secao() {
    echo '<p>As seguintes opções servem o propósito de agendar a tarefa de sincronia de dados de sítios web entre o Observatório e o sítio do Selo.</p>';
}

// Callback para renderizar o campo de URL do endpoint de indicadores
function callback_campo_indicadores_endpoint_url() {
    $url_endpoint = get_option('indicadores_endpoint_url');
    echo '<input type="text" id="indicadores_endpoint_url" name="indicadores_endpoint_url" value="' . esc_attr($url_endpoint) . '" />';
}

/**
 * Callback para renderizar o campo da data do WP-Cron para a atualização de dados dos Sítios ‘Web’.
 *
 * @return void
 */
function callback_campo_cron_job_date() {
    $cron_job_date = get_option('ama_cron_job_date');
    // Convert the date format from WordPress format to datetime-local format
    $datetime_local_date = date('Y-m-d\TH:i', strtotime($cron_job_date));
    echo '<input type="datetime-local" id="ama_cron_job_date" name="ama_cron_job_date" value="' . esc_attr($datetime_local_date) . '">';
}

/**
 * Callback para renderizar o campo da frequência do WP-Cron para a atualização de dados dos Sítios ‘Web’.
 *
 * @return void
 */
function callback_campo_cron_job_frequency() {
    $cron_job_frequency = get_option('ama_cron_job_frequency');
    echo '<p><'.$cron_job_frequency.'/p>';
    echo '<select id="ama_cron_job_frequency" name="ama_cron_job_frequency">
            <option value="hourly"' . selected( $cron_job_frequency, 'hourly', false ) . '>A cada hora</option>
            <option value="twicedaily"' . selected( $cron_job_frequency, 'twicedaily', false ) . '>Duas vezes por dia</option>
            <option value="daily"' . selected( $cron_job_frequency, 'daily', false ) . '>Diariamente</option>
          </select>';
}

/**
 * Callback para renderizar o campo do e-mail para notificações de erro
 *
 * @return void
 */
function callback_campo_feedback_agent_email(): void
{
    $feedback_email = get_option('ama_email_feedback_sincronizacao');
    echo '<input type="email" id="ama_email_feedback_sincronizacao" name="ama_email_feedback_sincronizacao" value="' . esc_attr($feedback_email) . '">';
    echo '<p>Insira uma ou várias contas de e-mail separadas por uma vírgula.</p>';
}