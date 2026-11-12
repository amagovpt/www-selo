<?php

// Exit se o ficheiro for acedido diretamente
defined('ABSPATH') || exit;


// Função para fazer a solicitação à API e armazenar os resultados no banco de dados

/**
 * Atualiza os dados dos indicadores.
 *
 * Esta função faz um pedido via REST API ao OBservatório e atualiza os dados dos indicadores na base de dados.
 *
 * @return void
 */
function atualizar_dados_indicadores() {
    // URL da API que você deseja acessar
    $api_url = 'URL_DA_API_ESPECIFICA';

    // Faz a solicitação à API
    $response = wp_remote_get($api_url);

    // Verifica se a solicitação foi bem-sucedida
    if (is_wp_error($response)) {
        // Erro ao fazer a solicitação à API
        error_log('Erro ao fazer a solicitação à API: ' . $response->get_error_message());
        return;
    }

    // Obtém o corpo da resposta
    $body = wp_remote_retrieve_body($response);

    // Decodifica o JSON
    $data = json_decode($body);

    // Verifica se os dados foram decodificados com sucesso
    if ($data === null) {
        // Erro ao decodificar JSON
        error_log('Erro ao decodificar JSON da resposta da API.');
        return;
    }

    // Salva os resultados no banco de dados
    update_option('api_results', $data);
}

// Agendar a execução da função uma vez por dia
// Adiciona a função ao cron job do WordPress
add_action('wp', 'atualizar_dados_indicadores');

function agendar_tarefa_diaria() {
    if (!wp_next_scheduled('atualizar_dados_indicadores')) {
        // Agenda a execução da função para 00:00 todos os dias
        wp_schedule_event(strtotime('midnight'), 'daily', 'atualizar_dados_indicadores');
    }
}


