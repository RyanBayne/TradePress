/**
 * TradePress Shortcodes JavaScript
 */
(function($) {
    'use strict';
    
    var TradePressShortcodes = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Filter by status
            $('#shortcode-status-filter').on('change', function() {
                var status = $(this).val();
                
                if (status === 'all') {
                    $('.shortcode-card').show();
                } else {
                    $('.shortcode-card').hide();
                    $('.shortcode-card.' + status + '-status').show();
                }
            });
            
            // Copy shortcode to clipboard
            $('.copy-shortcode').on('click', function() {
                var shortcode = $(this).data('shortcode');
                
                // Use Clipboard API if available
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(shortcode).then(function() {
                        TradePressShortcodes.showCopySuccess($(this));
                    }.bind(this));
                } else {
                    // Fallback for older browsers
                    var tempTextarea = $('<textarea>');
                    $('body').append(tempTextarea);
                    tempTextarea.val(shortcode).select();
                    document.execCommand('copy');
                    tempTextarea.remove();
                    
                    TradePressShortcodes.showCopySuccess($(this));
                }
            });
        },
        
        showCopySuccess: function($button) {
            var $icon = $button.find('.dashicons');
            
            // Change icon to show success
            $icon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
            
            // Reset after 1.5 seconds
            setTimeout(function() {
                $icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
            }, 1500);
        }
    };
    
    $(document).ready(function() {
        TradePressShortcodes.init();
    });
    
})(jQuery);
