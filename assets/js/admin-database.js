/**
 * TradePress Database Settings JavaScript
 *
 * @package    TradePress
 * @subpackage Admin
 * @version    1.0.0
 * @created    2025-04-20
 */

jQuery(document).ready(function($) {
    
    // Handle the install tables button
    $('#tradepress-install-tables').on('click', function() {
        var $button = $(this);
        var $spinner = $button.next('.spinner');
        var $result = $('#tradepress-install-result');
        
        // Disable button and show spinner
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        
        // Clear previous results
        $result.removeClass('success error').hide().html('');
        
        // Send AJAX request to install tables
        $.ajax({
            url: tradepressDatabase.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tradepress_install_tables',
                nonce: tradepressDatabase.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.addClass('success').html(response.data.message).show();
                } else {
                    $result.addClass('error').html(response.data.message).show();
                }
            },
            error: function() {
                $result.addClass('error').html(tradepressDatabase.installError).show();
            },
            complete: function() {
                // Re-enable button and hide spinner
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
    
});
