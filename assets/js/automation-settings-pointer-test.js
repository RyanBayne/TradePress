/**
 * Automation Settings Pointer Test
 * 
 * @package TradePress/Assets/JS
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    $('#test-api-pointer').on('click', function() {
        var target = $('select[name="api_provider"]');
        if (target.length) {
            // Create focus overlay
            $('body').append('<div class="tradepress-focus-overlay"></div>');
            target.addClass('tradepress-focus-target');
            
            // Create pointer
            target.pointer({
                content: '<h3>API Selection</h3><p>Choose your preferred API provider for market data. This setting affects all automation processes.</p>',
                position: {
                    edge: 'left',
                    align: 'center'
                },
                close: function() {
                    $('.tradepress-focus-overlay').remove();
                    target.removeClass('tradepress-focus-target');
                }
            }).pointer('open');
        }
    });
});