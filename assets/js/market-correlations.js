/**
 * TradePress Market Correlations JavaScript
 *
 * JavaScript functionality for the Market Correlations tab in the Research page
 *
 * @package TradePress
 * @version 1.0.0
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    // Accordion functionality - reused from other tabs
    $('.tradepress-accordion-header').on('click', function() {
        $(this).next('.tradepress-accordion-content').slideToggle();
        $(this).find('.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
    });
    
    // Initially hide all accordion content except the first one
    $('.tradepress-accordion-content').not(':first').hide();
    $('.tradepress-accordion-header').first().find('.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
});