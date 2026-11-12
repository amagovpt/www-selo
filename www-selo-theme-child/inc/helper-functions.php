<?php
/**
 * Devolve a data por extenso e traduz os meses para português.
 *
 * @param $data
 * @param string $formato
 * @return string|null
 */
function data_extenso($data, string $formato = 'd \d\e F, Y'): ?string
{
    // Se a data estiver vazia, devolve uma string vazia
    if (empty($data)) {
        return '';
    }

    // Converte a data para o formato desejado
    $data_output = date($formato, strtotime($data));

    return str_replace(
        ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"],
        ["janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"],
        strtolower($data_output)
    );
}

/**
 * Callback function para a ação AJAX 'AMA_sync_dados_sitios_selo'.
 *
 * Esta ação é usada para sincronizar os dados dos sítios 'Web' com o que está presente
 * Observatório da Acessibilidade. Vamos ignorar o agendamento da terfa de sincronia de dados e vamos fazê-la
 * manualmente
 *
 * @return void
 */
function ama_sync_dados_sitios_selo_callback(): void
{
    // Verificar se o utilizador tem permissões para executar esta ação
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Não tem permissões para executar esta ação.');
        wp_die();
    }

    // Return a response (optional)
    $atualizacao = atualizar_tabela_com_dados_observatorio();
    if(is_wp_error($atualizacao)) {
        wp_send_json_error($atualizacao->get_error_message());
        wp_die();
    } elseif ($atualizacao) {
        wp_send_json_success('Os dados foram sincronizados com sucesso.');
        wp_die();
    }

    // Ocorreu um erro ao sincronizar os dados
    wp_send_json_error('Ocorreu ao sincronizar os dados.');
    wp_die();
}

// Hook the function to the updated 'wp_ajax_' action for logged-in users
add_action('wp_ajax_AMA_sync_dados_sitios_selo', 'ama_sync_dados_sitios_selo_callback');