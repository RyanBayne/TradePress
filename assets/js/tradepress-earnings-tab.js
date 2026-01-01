/**
 * TradePress Earnings Tab JavaScript
 *
 * JavaScript functionality for the earnings calendar in the research page
 *
 * @package TradePress
 * @subpackage Admin/JS
 * @version 1.0.0
 * @since 1.0.1
 * @created 2023-10-24
 */

jQuery(document).ready(function($) {
    // Show/hide date fields based on view mode
    $('#view').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.date-filter').addClass('visible');
        } else {
            $('.date-filter').removeClass('visible');
        }
    });
    
    // Add to watchlist button functionality
    $('.add-to-watchlist').on('click', function() {
        var symbol = $(this).data('symbol');
        // Show notification
        alert('Added ' + symbol + ' to watchlist (Demo functionality)');
    });
    
    // View details button functionality
    $('.view-details').on('click', function() {
        var symbol = $(this).data('symbol');
        // Show notification
        alert('Viewing details for ' + symbol + ' (Demo functionality)');
    });
});
