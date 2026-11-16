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
        'Fonte dos sítios web (endpoint do Observatório ou JSON)',
        'callback_campo_indicadores_endpoint_url',
        'ama_definicoes',
        'ama_seccao_indicadores_sitios_web'
    );

    // Registra as opções no base de dados do endereço do endpoint de indicadores
    register_setting(
        'ama_definicoes',
        'indicadores_endpoint_url',
        ['sanitize_callback' => 'esc_url_raw']
    );

    // Fonte das aplicações móveis (JSON com a mesma estrutura). Opcional.
    add_settings_field(
        'ama_selo_fonte_apps_url',
        'Fonte das aplicações móveis (JSON)',
        'ama_selo_campo_fonte_apps_url',
        'ama_definicoes',
        'ama_seccao_indicadores_sitios_web'
    );

    register_setting(
        'ama_definicoes',
        'ama_selo_fonte_apps_url',
        ['sanitize_callback' => 'esc_url_raw']
    );

    // Adiciona o campo para a data do WP-Cron da atualização de dados dos Sítios ‘Web’.
    add_settings_field(
        'ama_cron_job_date',
        'Hora da execução da tarefa',
        'callback_campo_cron_job_date',
        'ama_definicoes',
        'ama_seccao_indicadores_sitios_web'
    );

    // Registra a opção de data para o WP-Cron na base de dados
    register_setting(
        'ama_definicoes',
        'ama_cron_job_date',
        ['sanitize_callback' => 'ama_selo_sanitizar_hora']
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
        'ama_cron_job_frequency',
        ['sanitize_callback' => 'ama_selo_sanitizar_frequencia']
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
        'ama_email_feedback_sincronizacao',
        ['sanitize_callback' => 'ama_selo_sanitizar_emails']
    );
}

/**
 * Guarda a hora no formato HH:MM (aceita também o valor antigo "AAAA-MM-DDTHH:MM").
 *
 * @param mixed $valor
 * @return string
 */
function ama_selo_sanitizar_hora($valor): string
{
    if (preg_match('/(\d{1,2}):(\d{2})/', (string) $valor, $m) && (int) $m[1] < 24 && (int) $m[2] < 60) {
        return sprintf('%02d:%02d', $m[1], $m[2]);
    }

    return AMA_SELO_CRON_HORA_OMISSAO;
}

/**
 * Só aceita frequências que existam no WP-Cron.
 *
 * @param mixed $valor
 * @return string
 */
function ama_selo_sanitizar_frequencia($valor): string
{
    return array_key_exists((string) $valor, wp_get_schedules()) ? (string) $valor : 'daily';
}

/**
 * Lista de e-mails válidos separados por vírgulas.
 *
 * @param mixed $valor
 * @return string
 */
function ama_selo_sanitizar_emails($valor): string
{
    $emails = array_filter(array_map('sanitize_email', explode(',', (string) $valor)), 'is_email');

    return implode(', ', $emails);
}

// Callback para renderizar a seção
function meu_callback_secao() {
    echo '<p>As seguintes opções servem o propósito de agendar a tarefa de sincronia de dados de sítios web entre o Observatório e o sítio do Selo.</p>';
    echo '<p>A tarefa corre uma vez por dia à hora indicada (hora de Lisboa). Os dados publicados só mudam se a fonte responder com uma lista válida.</p>';
}

// Callback para renderizar o campo de URL do endpoint de indicadores
function callback_campo_indicadores_endpoint_url() {
    $url_endpoint = get_option('indicadores_endpoint_url');

    if (defined('AMA_SELO_FONTE_URL') && AMA_SELO_FONTE_URL) {
        echo '<p>Definido no <code>wp-config.php</code> (AMA_SELO_FONTE_URL); este campo é ignorado.</p>';
    }

    echo '<input type="url" class="regular-text" id="indicadores_endpoint_url" name="indicadores_endpoint_url" value="' . esc_attr($url_endpoint) . '" />';

    $protecoes = [];
    if (defined('AMA_SELO_FONTE_TOKEN') && AMA_SELO_FONTE_TOKEN) {
        $protecoes[] = 'token de acesso';
    }
    if (defined('AMA_SELO_FONTE_SEGREDO') && AMA_SELO_FONTE_SEGREDO) {
        $protecoes[] = 'assinatura HMAC';
    }
    if (defined('AMA_SELO_FONTE_HOSTS') && AMA_SELO_FONTE_HOSTS) {
        $protecoes[] = 'anfitriões autorizados';
    }
    echo '<p class="description">Só HTTPS. Proteções ativas (as mesmas para as duas fontes): ' . esc_html($protecoes ? implode(', ', $protecoes) : 'nenhuma (ver docs/sincronizacao-selos.md)') . '.</p>';
}

/**
 * Campo do endereço da fonte das aplicações móveis.
 *
 * @return void
 */
function ama_selo_campo_fonte_apps_url(): void
{
    if (defined('AMA_SELO_FONTE_APPS_URL') && AMA_SELO_FONTE_APPS_URL) {
        echo '<p>Definido no <code>wp-config.php</code> (AMA_SELO_FONTE_APPS_URL); este campo é ignorado.</p>';
    }

    echo '<input type="url" class="regular-text" id="ama_selo_fonte_apps_url" name="ama_selo_fonte_apps_url" value="' . esc_attr(get_option('ama_selo_fonte_apps_url')) . '" />';
    echo '<p class="description">Mesma estrutura do JSON dos sítios web. Vazio = as aplicações publicadas não são alteradas.</p>';
}

/**
 * Callback para renderizar o campo da data do WP-Cron para a atualização de dados dos Sítios ‘Web’.
 *
 * @return void
 */
function callback_campo_cron_job_date() {
    echo '<input type="time" id="ama_cron_job_date" name="ama_cron_job_date" value="' . esc_attr(ama_selo_cron_hora()) . '">';
}

/**
 * Callback para renderizar o campo da frequência do WP-Cron para a atualização de dados dos Sítios ‘Web’.
 *
 * @return void
 */
function callback_campo_cron_job_frequency() {
    $cron_job_frequency = get_option('ama_cron_job_frequency');
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