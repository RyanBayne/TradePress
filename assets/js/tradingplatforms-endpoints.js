/**
 * TradePress - Endpoints Tab Scripts
 *
 * @package TradePress/Admin/JS
 */

jQuery(document).ready(function($) {
    $('.test-button').on('click', function() {
        const button = $(this);
        const endpointId = button.data('endpoint');
        const platformId = tradepress_endpoints_params.current_platform;
        
        // Disable button and show loading state
        button.prop('disabled', true).text(tradepress_endpoints_params.testing_text);
        
        // Show results container
        $('#endpoint-test-results').show().html('<div class="loading-indicator"><span class="spinner is-active"></span> ' + tradepress_endpoints_params.testing_endpoint_text + '</div>');
        
        // Make AJAX call to test endpoint
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'tradepress_test_endpoint',
                endpoint: endpointId,
                platform: platformId,
                nonce: tradepress_endpoints_params.nonce
            },
            success: function(response) {
                // Reset button state
                button.prop('disabled', false).text(tradepress_endpoints_params.test_text);
                
                // Format and display test results
                let resultsHtml = '<div class="endpoint-test-header">';
                resultsHtml += '<h3>' + tradepress_endpoints_params.test_results_text + ': <span class="endpoint-name">' + endpointId + '</span></h3>';
                resultsHtml += '<button type="button" class="close-results button"><span class="dashicons dashicons-no-alt"></span></button>';
                resultsHtml += '</div>';
                
                if (response.success) {
                    // Success response
                    resultsHtml += '<div class="endpoint-test-success">';
                    resultsHtml += '<div class="status-indicator success"><span class="dashicons dashicons-yes-alt"></span> ' + tradepress_endpoints_params.test_successful_text + '</div>';
                    resultsHtml += '<div class="test-metadata">';
                    resultsHtml += '<div class="platform-name"><strong>' + tradepress_endpoints_params.platform_text + ':</strong> ' + (response.data.platform || platformId) + '</div>';
                    resultsHtml += '<div class="test-time"><strong>' + tradepress_endpoints_params.time_text + ':</strong> ' + new Date().toLocaleString() + '</div>';
                    resultsHtml += '</div>';
                    
                    // Add formatted data response
                    resultsHtml += '<div class="response-data">';
                    resultsHtml += '<h4>' + tradepress_endpoints_params.response_data_text + '</h4>';
                    
                    // Pretty format data
                    if (typeof response.data.data === 'object') {
                        resultsHtml += '<pre class="api-json-data">' + JSON.stringify(response.data.data, null, 2) + '</pre>';
                    } else {
                        resultsHtml += '<pre class="api-text-data">' + response.data.data + '</pre>';
                    }
                    
                    resultsHtml += '</div>'; // End response-data
                    resultsHtml += '</div>'; // End endpoint-test-success
                } else {
                    // Error response
                    resultsHtml += '<div class="endpoint-test-error">';
                    resultsHtml += '<div class="status-indicator error"><span class="dashicons dashicons-warning"></span> ' + tradepress_endpoints_params.test_failed_text + '</div>';
                    
                    // Format error message
                    if (typeof response.data.message === 'string' && response.data.message.includes('API Test Error Report')) {
                        // Display formatted error report
                        resultsHtml += '<pre class="api-error-report">' + response.data.message + '</pre>';
                    } else {
                        // Simple error display
                        resultsHtml += '<div class="error-message">' + response.data.message + '</div>';
                    }
                    
                    // Add troubleshooting tips
                    resultsHtml += '<div class="troubleshooting-tips">';
                    resultsHtml += '<h4>' + tradepress_endpoints_params.troubleshooting_tips_text + '</h4>';
                    resultsHtml += '<ul>';
                    resultsHtml += '<li>' + tradepress_endpoints_params.tip_api_key_text + '</li>';
                    resultsHtml += '<li>' + tradepress_endpoints_params.tip_rate_limits_text + '</li>';
                    resultsHtml += '<li>' + tradepress_endpoints_params.tip_subscription_text + '</li>';
                    resultsHtml += '<li>' + tradepress_endpoints_params.tip_network_text + '</li>';
                    resultsHtml += '</ul>';
                    resultsHtml += '</div>'; // End troubleshooting tips
                    
                    resultsHtml += '</div>'; // End endpoint-test-error
                }
                
                // Display the results
                $('#endpoint-test-results').html(resultsHtml);
                
                // Scroll to results
                $('html, body').animate({
                    scrollTop: $('#endpoint-test-results').offset().top - 50
                }, 500);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Reset button state
                button.prop('disabled', false).text(tradepress_endpoints_params.test_text);
                
                // Show ajax error
                $('#endpoint-test-results').html(
                    '<div class="endpoint-test-error">' +
                    '<div class="status-indicator error"><span class="dashicons dashicons-warning"></span> ' + tradepress_endpoints_params.ajax_error_text + '</div>' +
                    '<div class="error-message">' + textStatus + ': ' + errorThrown + '</div>' +
                    '</div>'
                );
            }
        });
    });
    
    // Close results when clicking the close button
    $(document).on('click', '.close-results', function() {
        $('#endpoint-test-results').hide();
    });
});