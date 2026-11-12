<?php

// Exit se o ficheiro for acedido diretamente
defined('ABSPATH') || exit;


// Função para fazer a solicitação à API e armazenar os resultados no banco de dados

/**
 * Atualiza os dados dos indicadores.
 *
 * Esta função faz um pedido via REST API ao Observatório e atualiza os dados dos indicadores na base de dados.
 *
 * @return void
 */
function atualizar_dados_sites_selo_cb(): void
{

    // Atualiza os dados dos sítios ‘web’ com selo.
    $atualizacao = atualizar_tabela_com_dados_observatorio();

    // Se houver um erro, envia um e/mail com o erro e termina a execução do script
    if(is_wp_error($atualizacao)) {
        /*
        wp_mail(
            get_option('ama_email_feedback_sincronizacao'),
            'Erro na sincronização dos dados dos sítios Web com Selo',
            $atualizacao->get_error_message()
        );
        */
        wp_die($atualizacao->get_error_message());
    }
}
// Adiciona a função ao cron job do WordPress
add_action('atualizar_dados_sites_selo', 'atualizar_dados_sites_selo_cb');


/**
 * Agendar a execução da função uma vez por dia.
 *
 * Esta função verifica se a função de atualização dos dados dos sítios com selo já está agendada e, caso não esteja, agenda a execução da função para 00:00 todos os dias.
 *
 * @return void
 */
function agendar_tarefa_diaria_sites_selo(): void {
        // Agenda a execução da função para 00:00 todos os dias
        wp_schedule_event(
            strtotime(get_option('ama_cron_job_date')),
            get_option('ama_cron_job_frequency'),
            'atualizar_dados_sites_selo');
}
// Agendar a execução da função uma vez por dia e adiciona a função ao cron job do WordPress
add_action('wp', 'agendar_tarefa_diaria_sites_selo');