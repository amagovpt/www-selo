<?php

// Exit se o ficheiro for acedido diretamente
defined('ABSPATH') || exit;

/**
 * Sincronização dos sítios web/aplicações com selo (WP-Cron).
 *
 * - Agendada UMA vez (não em cada visita), à hora definida em Definições > Definições | AMA
 *   (por omissão 02:30, hora de Lisboa), com a frequência aí escolhida.
 * - Reagendada sozinha quando a hora ou a frequência mudam.
 * - Em produção, o WP-Cron deve ser disparado pelo cron do sistema (ver docs/sincronizacao-selos.md).
 */

const AMA_SELO_CRON_HOOK        = 'atualizar_dados_sites_selo';
const AMA_SELO_CRON_HORA_OMISSAO = '02:30';

/**
 * Hora do dia (HH:MM) a que a sincronização corre.
 *
 * Aceita o valor antigo do campo (datetime-local, ex.: "2024-02-27T00:30") e o novo ("02:30").
 *
 * @return string
 */
function ama_selo_cron_hora(): string
{
    $valor = (string) get_option( 'ama_cron_job_date', '' );

    if ( preg_match( '/(\d{1,2}):(\d{2})/', $valor, $m ) && (int) $m[1] < 24 && (int) $m[2] < 60 ) {
        return sprintf( '%02d:%02d', $m[1], $m[2] );
    }

    return AMA_SELO_CRON_HORA_OMISSAO;
}

/**
 * Frequência (recorrência do WP-Cron) da sincronização; "daily" se o valor guardado não existir.
 *
 * @return string
 */
function ama_selo_cron_frequencia(): string
{
    $frequencia = (string) get_option( 'ama_cron_job_frequency', 'daily' );

    return array_key_exists( $frequencia, wp_get_schedules() ) ? $frequencia : 'daily';
}

/**
 * Próxima ocorrência da hora configurada, no fuso horário do site (timestamp UTC).
 *
 * @return int
 */
function ama_selo_cron_proxima_execucao(): int
{
    [ $hora, $minuto ] = array_map( 'intval', explode( ':', ama_selo_cron_hora() ) );

    $agora   = new DateTimeImmutable( 'now', wp_timezone() );
    $proxima = $agora->setTime( $hora, $minuto );

    if ( $proxima <= $agora ) {
        $proxima = $proxima->modify( '+1 day' );
    }

    return $proxima->getTimestamp();
}

/**
 * Agenda a sincronização se ainda não estiver agendada.
 *
 * Corre em "init", mas só faz uma leitura ao cron em memória: não há pedidos à fonte nas visitas.
 *
 * @return void
 */
function agendar_tarefa_diaria_sites_selo(): void
{
    if ( wp_installing() ) {
        return;
    }

    // Agendamentos feitos pela versão anterior (hora em UTC, eventos repetidos): refaz uma vez.
    if ( '2' !== get_option( 'ama_selo_cron_versao' ) ) {
        update_option( 'ama_selo_cron_versao', '2' );
        wp_clear_scheduled_hook( AMA_SELO_CRON_HOOK );
    }

    if ( wp_next_scheduled( AMA_SELO_CRON_HOOK ) ) {
        return;
    }

    wp_schedule_event( ama_selo_cron_proxima_execucao(), ama_selo_cron_frequencia(), AMA_SELO_CRON_HOOK );
}
add_action( 'init', 'agendar_tarefa_diaria_sites_selo' );

/**
 * Volta a agendar a sincronização (ex.: quando a hora ou a frequência mudam).
 *
 * @return void
 */
function ama_selo_cron_reagendar(): void
{
    wp_clear_scheduled_hook( AMA_SELO_CRON_HOOK );
    agendar_tarefa_diaria_sites_selo();
}
add_action( 'update_option_ama_cron_job_date', 'ama_selo_cron_reagendar' );
add_action( 'update_option_ama_cron_job_frequency', 'ama_selo_cron_reagendar' );
add_action( 'add_option_ama_cron_job_date', 'ama_selo_cron_reagendar' );
add_action( 'add_option_ama_cron_job_frequency', 'ama_selo_cron_reagendar' );
add_action( 'switch_theme', function () {
    wp_clear_scheduled_hook( AMA_SELO_CRON_HOOK );
} );

/**
 * Executa a sincronização e regista o resultado.
 *
 * Usado pelo WP-Cron, pelo botão "Sincronizar Dados" e pelo comando "wp selo sincronizar".
 * Um bloqueio impede duas sincronizações em simultâneo.
 *
 * @param string $origem cron, manual ou wp-cli (só para o registo).
 * @return true|WP_Error
 */
function ama_selo_sincronizar( string $origem = 'cron' )
{
    // add_option é atómico: se a opção já existir, outra sincronização está a decorrer.
    $bloqueio = 'ama_selo_sync_bloqueio';
    $agora    = time();

    if ( ! add_option( $bloqueio, $agora, '', false ) ) {
        $inicio = (int) get_option( $bloqueio );

        if ( $inicio && $agora - $inicio < 10 * MINUTE_IN_SECONDS ) {
            return new WP_Error( 'selo_sync_em_curso', 'Já está a decorrer uma sincronização dos dados dos selos.' );
        }

        // Bloqueio antigo (processo interrompido): assume-o.
        update_option( $bloqueio, $agora, false );
    }

    try {
        $resultado = atualizar_tabela_com_dados_observatorio();
    } catch ( Throwable $e ) {
        $resultado = new WP_Error( 'selo_sync_excecao', $e->getMessage() );
    } finally {
        delete_option( $bloqueio );
    }

    // Pelo menos uma fonte foi atualizada: conta como carregamento com sucesso (data mostrada no site).
    if ( true === $resultado || ( is_wp_error( $resultado ) && 'selo_parcial' === $resultado->get_error_code() ) ) {
        update_option( 'ama_selo_sync_ultimo_sucesso', $agora );
    }

    if ( is_wp_error( $resultado ) ) {
        update_option( 'ama_selo_sync_ultimo_erro', [
            'data'     => $agora,
            'origem'   => $origem,
            'mensagem' => $resultado->get_error_message(),
        ], false );

        error_log( '[Selo] Sincronização (' . $origem . ') falhou: ' . $resultado->get_error_message() );
        ama_selo_notificar_erro( $resultado );

        return $resultado;
    }

    delete_option( 'ama_selo_sync_ultimo_erro' );

    return true;
}

/**
 * Envia um e-mail com o erro aos endereços definidos (no máximo um por dia para o mesmo erro).
 *
 * @param WP_Error $erro
 * @return void
 */
function ama_selo_notificar_erro( WP_Error $erro ): void
{
    $destinatarios = array_filter( array_map( 'sanitize_email', explode( ',', (string) get_option( 'ama_email_feedback_sincronizacao', '' ) ) ) );

    if ( ! $destinatarios ) {
        return;
    }

    $chave = 'ama_selo_sync_aviso_' . md5( $erro->get_error_message() );

    if ( get_transient( $chave ) ) {
        return;
    }

    set_transient( $chave, 1, DAY_IN_SECONDS );

    wp_mail(
        $destinatarios,
        '[' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . '] Erro na sincronização dos dados dos selos',
        "A sincronização dos dados dos sítios web e aplicações com selo falhou.\n\n"
        . 'Erro: ' . $erro->get_error_message() . "\n\n"
        . "Os dados publicados no site não foram alterados.\n"
        . 'Definições: ' . admin_url( 'options-general.php?page=ama-definicoes' )
    );
}

/**
 * Resumo da última sincronização, ex.: "134 sítios web, 16 aplicações móveis (1 ignorado)".
 *
 * @return string
 */
function ama_selo_texto_resumo(): string
{
    $resumo = get_option( 'ama_selo_sync_resumo', [] );
    $partes = [];

    foreach ( $resumo['fontes'] ?? [] as $tipo => $f ) {
        $partes[] = sprintf( '%d %s', $f['registos'], AMA_SELO_TIPO_APP === $tipo ? 'aplicações móveis' : 'sítios web' );
    }

    if ( ! $partes && isset( $resumo['registos'] ) ) {
        $partes[] = sprintf( '%d registos', $resumo['registos'] );
    }

    $texto = implode( ', ', $partes );

    if ( ! empty( $resumo['invalidos'] ) ) {
        $texto .= sprintf( ' (%d ignorados por serem inválidos)', $resumo['invalidos'] );
    }

    return $texto;
}

/**
 * Callback do WP-Cron.
 *
 * @return void
 */
function atualizar_dados_sites_selo_cb(): void
{
    ama_selo_sincronizar( 'cron' );
}
add_action( AMA_SELO_CRON_HOOK, 'atualizar_dados_sites_selo_cb' );

/**
 * Remove o agendamento antigo do cron de indicadores (ficheiro removido), se existir.
 */
add_action( 'admin_init', function () {
    if ( wp_next_scheduled( 'atualizar_dados_indicadores' ) ) {
        wp_clear_scheduled_hook( 'atualizar_dados_indicadores' );
    }
} );

/**
 * Comando WP-CLI: wp selo sincronizar
 *
 * Útil para testar e para disparar a sincronização a partir do cron do sistema.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'selo sincronizar', function () {
        $resultado = ama_selo_sincronizar( 'wp-cli' );

        if ( is_wp_error( $resultado ) ) {
            WP_CLI::error( $resultado->get_error_message() );
        }

        WP_CLI::success( 'Dados sincronizados: ' . ama_selo_texto_resumo() . '.' );
    } );
}
