<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Adiciona um shortcode para renderizar o AI Agent.
 * @return void
 */
function add_chatbot_ai_agent_cb(): void
{
    // Renderiza o campo de textarea para o script AI
    echo '<iframe src="https://chatbot.getvirtualbrain.com/11ed509b-18a5-4f31-a2df-bc6b5b5031ca" width="100%" style="height: 100%; min-height: 700px;" frameBorder="0"></iframe>';
}

add_shortcode('ai-agent', 'add_chatbot_ai_agent_cb');
