/**
 * TradePress - Endpoints Table Partial Scripts
 *
 * @package TradePress/Admin/JS
 */

jQuery(document).ready(function($) {
    // Add endpoint filtering functionality
    $('#endpoints-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        filterEndpoints(searchTerm, $('#endpoints-method-filter').val());
    });
    
    $('#endpoints-method-filter').on('change', function() {
        filterEndpoints($('#endpoints-search').val().toLowerCase(), $(this).val());
    });
    
    function filterEndpoints(searchTerm, methodFilter) {
        $('.endpoints-table tbody tr').each(function() {
            var endpointName = $(this).find('.endpoint-name').text().toLowerCase();
            var endpointPath = $(this).find('.endpoint-path').text().toLowerCase();
            var endpointDesc = $(this).find('td:nth-child(2)').text().toLowerCase();
            var endpointMethod = $(this).find('.method-badge').text().toLowerCase();
            
            var matchesSearch = endpointName.includes(searchTerm) || 
                               endpointPath.includes(searchTerm) || 
                               endpointDesc.includes(searchTerm);
            
            var matchesMethod = !methodFilter || endpointMethod === methodFilter;
            
            if (matchesSearch && matchesMethod) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
    
    // Make the API test results notice dismissible using standard approach - NO AJAX
    $('.api-test-dismiss').on('click', function() {
        $(this).closest('.api-test-results-notice').fadeOut();
    });
});