<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Enqueue JavaScript for handling AJAX action
function enqueue_custom_scripts(): void
{
    global $pagenow;
    if ($pagenow == 'options-general.php' && ($_GET['page'] ?? '') == 'ama-definicoes') {
        wp_enqueue_script(
            'ama-settings-script',
            get_stylesheet_directory_uri() . '/assets/js/admin.js',
            ["jquery"],
            filemtime(get_stylesheet_directory() . '/assets/js/admin.js'),
            true
        );

        wp_localize_script('ama-settings-script', 'amaSelo', [
            'nonce'    => wp_create_nonce('ama_selo_sincronizar'),
            'aguarde'  => 'A sincronizar…',
            'erroRede' => 'Não foi possível contactar o servidor. Tente de novo.',
        ]);

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
    echo '<div id="ama-sync-response" aria-live="polite"></div>';

    ama_selo_estado_sincronizacao();
}

/**
 * Mostra o estado da sincronização: próxima execução, último sucesso e último erro.
 *
 * @return void
 */
function ama_selo_estado_sincronizacao(): void
{
    $formato  = 'j \d\e F \d\e Y, H:i';
    $proxima  = wp_next_scheduled(AMA_SELO_CRON_HOOK);
    $sucesso  = (int) get_option('ama_selo_sync_ultimo_sucesso');
    $erro     = get_option('ama_selo_sync_ultimo_erro');
    $resumo   = get_option('ama_selo_sync_resumo', []);

    echo '<h3>Estado</h3><ul>';

    echo '<li><strong>Próxima execução:</strong> ' . ($proxima ? esc_html(wp_date($formato, $proxima)) : 'não agendada') . '</li>';

    echo '<li><strong>Última sincronização com sucesso:</strong> ' . ($sucesso ? esc_html(wp_date($formato, $sucesso)) : 'sem registo');
    if ($sucesso && !empty($resumo['registos'])) {
        echo esc_html(' (' . ama_selo_texto_resumo() . ')');
    }
    echo '</li>';

    if (is_array($erro) && !empty($erro['mensagem'])) {
        echo '<li><strong>Último erro:</strong> ' . esc_html(wp_date($formato, (int) $erro['data']) . ' — ' . $erro['mensagem']) . '</li>';
    }

    if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
        echo '<li>O WP-Cron está a ser disparado pelo cron do sistema (DISABLE_WP_CRON).</li>';
    }

    echo '</ul>';
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
