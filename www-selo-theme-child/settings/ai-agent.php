<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;// Adiciona uma página de configurações ao menu Opções

// Adiciona campo de configuração para o script AI
function adicionar_opcao_ai_agent() {
    // Recupera o valor atual da opção ai_agent
    $ai_agent_script = get_option('ai_agent');

    // Renderiza o campo de textarea para o script AI
    echo '<textarea id="ai_agent" name="ai_agent" rows="8" cols="50">' . esc_textarea($ai_agent_script) . '</textarea>';
}

// Registra o campo de configuração para o script AI
function registrar_campos_ai_agent() {
    add_settings_section(
        'ai_agent_section',
        'Configurações AI Agent',
        'adicionar_opcao_ai_agent',
        'ai_agent_options'
    );

    // Registra a opção no banco de dados
    register_setting(
        'ai_agent_options',
        'ai_agent'
    );
}

// Adiciona os campos de configuração à nova seção AI Agent na página de opções
add_action('admin_init', 'registrar_campos_ai_agent');
