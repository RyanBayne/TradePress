/**
 * TradePress - StockTwits Social Platform Scripts
 *
 * @package  TradePress/assets/js
 * @since    1.0.0
 */

jQuery(document).ready(function($) {
    // Update the slider value display when the slider changes
    $('#stocktwits_sentiment_importance').on('input', function() {
        $('.slider-value').text($(this).val() + '%');
    });
});