/**
 * TradePress - Portfolio Tab Scripts
 *
 * @package TradePress/Admin/JS
 */

jQuery(document).ready(function($) {
    $('#refresh-portfolio').on('click', function() {
        $(this).find('.dashicons').addClass('dashicons-update-spin');
        
        // Simulate refresh with a timeout
        setTimeout(function() {
            location.reload();
        }, 1000);
    });
    
    $('#export-portfolio').on('click', function() {
        alert('Portfolio data export functionality will be implemented here.');
    });
});