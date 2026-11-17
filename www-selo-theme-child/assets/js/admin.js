jQuery(document).ready(function($) {
    $('#ama-sync-button').on('click', function() {
        const $button = $(this);
        const settings = window.amaSelo || {};

        $button.prop('disabled', true);
        $('#ama-sync-response').html(dialogBSAlert('info', settings.aguarde || '…'));

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'AMA_sync_dados_sitios_selo',
                nonce: settings.nonce
            },
            success: function(response) {
                let message = response.data
                let alert = response.success ? 'success' : 'error';
                $('#ama-sync-response').html(dialogBSAlert(alert, message));
            },
            error: function(xhr) {
                let message = (xhr.responseJSON && xhr.responseJSON.data) || settings.erroRede;
                $('#ama-sync-response').html(dialogBSAlert('error', message));
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    function dialogBSAlert(alertType, message) {
        let HTMLOutput = '';
        switch (alertType) {
            case 'error':
                HTMLOutput = '<div class="ama alert alert-danger" role="alert">' + message + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                break;

            case 'success':
                HTMLOutput = '<div class="ama alert alert-success" role="alert">' + message + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                break;

            default:
                HTMLOutput = '<div class="ama alert alert-info" role="alert">' + message + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                break;
        }

        return HTMLOutput;
    }
});