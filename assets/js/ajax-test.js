/**
 * TradePress Ajax Test Script
 * 
 * Simple script to test WordPress ajax functionality.
 */
jQuery(document).ready(function($) {
    // Handle the ajax test button click
    $('#simple-ajax-test').on('click', function() {
        var $button = $(this);
        var $spinner = $button.next('.spinner');
        var $results = $('#ajax-test-results');
        var $content = $('#ajax-result-content');
        
        // Clear previous results
        $content.empty();
        
        // Disable button and show spinner
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        
        console.log('AJAX Test Config:', tradepressAjaxTest);
        
        // Make the simplest possible ajax request
        $.ajax({
            url: tradepressAjaxTest.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tradepress_simple_ajax_test',
                nonce: tradepressAjaxTest.nonce
            },
            success: function(response) {
                console.log('Ajax Response:', response);
                
                if (response.success) {
                    // Display the success message
                    $content.html(
                        '<div style="color: #46b450;">✓ ' + response.data.message + '</div>' +
                        '<p>Server Time: ' + response.data.time + '</p>' +
                        '<p>Test ID: ' + response.data.test_id + '</p>'
                    );
                } else {
                    // Display the error message
                    $content.html(
                        '<div style="color: #dc3232;">✗ Ajax error: ' + 
                        (response.data ? response.data.message : 'Unknown error') + 
                        '</div>'
                    );
                }
                
                // Show the results
                $results.show();
            },
            error: function(xhr, status, error) {
                console.error('Ajax Error:', {xhr: xhr, status: status, error: error});
                
                // Try to parse the response
                var errorMessage = 'Ajax request failed: ' + status;
                if (xhr.responseText) {
                    errorMessage += '<br>Response: ' + xhr.responseText.substring(0, 200);
                    if (xhr.responseText.length > 200) {
                        errorMessage += '...';
                    }
                }
                
                // Display the error
                $content.html('<div style="color: #dc3232;">✗ ' + errorMessage + '</div>');
                $results.show();
            },
            complete: function() {
                // Re-enable button and hide spinner
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
});
