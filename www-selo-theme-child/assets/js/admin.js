jQuery(document).ready(function($) {
    $('#ama-sync-button').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'AMA_sync_dados_sitios_selo' // Updated AJAX action name
            },
            success: function(response) {
                let message = response.data
                let alert = response.success ? 'success' : 'error';
                $('#ama-sync-response').html(dialogBSAlert(alert, message));
            },
            error: function(xhr, status, error) {
                let message = response.data
                let alert = response.success ? 'success' : 'error';
                $('#ama-sync-response').html(dialogBSAlert(alert, message));
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