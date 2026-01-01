/**
 * TradePress Settings JavaScript
 *
 * @package TradePress/Admin/JS
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    // Database settings functionality
    $('#tradepress-install-tables').on('click', function() {
        var $button = $(this);
        var $spinner = $button.next('.spinner');
        var $result = $('#tradepress-install-result');
        
        $button.prop('disabled', true);
        $spinner.css('visibility', 'visible');
        
        $result.removeClass('success error').hide().html('');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_install_tables',
                security: $('#tradepress-install-tables').data('nonce')
            },
            success: function(response) {
                if (response.success) {
                    $result.addClass('success').html(response.data.message).show();
                } else {
                    $result.addClass('error').html(response.data && response.data.message ? response.data.message : 'Unknown error occurred').show();
                }
            },
            error: function(xhr, status, error) {
                $result.addClass('error').html('Error installing tables. Please check error logs.').show();
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.css('visibility', 'hidden');
            }
        });
    });
    
    // Shortcodes settings functionality
    $('#shortcode-status-filter').on('change', function() {
        var status = $(this).val();
        
        if (status === 'all') {
            $('.shortcode-card').show();
        } else {
            $('.shortcode-card').hide();
            $('.shortcode-card.' + status + '-status').show();
        }
    });
    
    $('.copy-shortcode').on('click', function() {
        var shortcode = $(this).data('shortcode');
        var tempTextarea = $('<textarea>');
        $('body').append(tempTextarea);
        tempTextarea.val(shortcode).select();
        document.execCommand('copy');
        tempTextarea.remove();
        
        var $button = $(this);
        var $originalIcon = $button.find('.dashicons');
        $originalIcon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
        setTimeout(function() {
            $originalIcon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
        }, 1500);
    });
});