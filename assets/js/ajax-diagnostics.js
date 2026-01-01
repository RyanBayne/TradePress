/**
 * TradePress Ajax Diagnostics Scripts
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        console.log('Ajax diagnostics loaded. Ajax URL:', tpAjaxDiagnostics.ajax_url);
        
        // Basic Ajax Test
        $('#tp-test-ajax-basic').on('click', function() {
            const $button = $(this);
            const $result = $('#tp-test-ajax-basic-result');
            
            $button.prop('disabled', true);
            $result.html('<p>Testing Ajax connectivity...</p>');
            
            // Simple Ajax request with minimal parameters
            $.post(tpAjaxDiagnostics.ajax_url, {
                action: 'tradepress_test_ajax_basic'
            })
            .done(function(response) {
                console.log('Basic test response:', response);
                if (response.success) {
                    $result.html('<p class="tp-success">' + response.data.message + '</p>');
                } else {
                    $result.html('<p class="tp-error">Error: ' + response.data.message + '</p>');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Basic test error:', xhr.responseText, status, error);
                $result.html('<p class="tp-error">Ajax Error: ' + error + '</p>' +
                            '<p class="tp-error">Status: ' + status + '</p>' +
                            '<p class="tp-error">Response: ' + xhr.responseText + '</p>');
            })
            .always(function() {
                $button.prop('disabled', false);
            });
        });
        
        // Data Transfer Test
        $('#tp-test-ajax-data').on('click', function() {
            const $button = $(this);
            const $result = $('#tp-test-ajax-data-result');
            
            $button.prop('disabled', true);
            $result.html('<p>Testing data transfer...</p>');
            
            const testData = {
                timestamp: new Date().toISOString(),
                browser: navigator.userAgent,
                screen: window.innerWidth + 'x' + window.innerHeight
            };
            
            $.ajax({
                url: tpAjaxDiagnostics.ajax_url,
                type: 'POST',
                data: {
                    action: 'tradepress_test_ajax_data',
                    nonce: tpAjaxDiagnostics.nonce,
                    test_data: JSON.stringify(testData)
                },
                success: function(response) {
                    console.log('Data test response:', response);
                    if (response.success) {
                        let html = '<p class="tp-success">' + response.data.message + '</p>';
                        html += '<div class="tp-data-results">';
                        html += '<h4>Client Data Sent:</h4>';
                        html += '<pre>' + JSON.stringify(testData, null, 2) + '</pre>';
                        html += '<h4>Server Data Received:</h4>';
                        html += '<pre>' + JSON.stringify(response.data.server_data, null, 2) + '</pre>';
                        html += '</div>';
                        $result.html(html);
                    } else {
                        $result.html('<p class="tp-error">Error: ' + response.data.message + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Data test error:', xhr.responseText, status, error);
                    $result.html('<p class="tp-error">Ajax Error: ' + error + '</p>' +
                                '<p class="tp-error">Status: ' + status + '</p>' +
                                '<p class="tp-error">Response: ' + xhr.responseText + '</p>');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });
        
        // Timing Test
        $('#tp-test-ajax-timing').on('click', function() {
            const $button = $(this);
            const $result = $('#tp-test-ajax-timing-result');
            
            $button.prop('disabled', true);
            $result.html('<p>Testing Ajax response time...</p>');
            
            const startTime = new Date().getTime();
            
            $.ajax({
                url: tpAjaxDiagnostics.ajax_url,
                type: 'POST',
                data: {
                    action: 'tradepress_test_ajax_timing',
                    nonce: tpAjaxDiagnostics.nonce
                },
                success: function(response) {
                    console.log('Timing test response:', response);
                    const endTime = new Date().getTime();
                    const totalTime = endTime - startTime;
                    
                    if (response.success) {
                        let html = '<p class="tp-success">' + response.data.message + '</p>';
                        html += '<div class="tp-timing-results">';
                        html += '<p><strong>Total round-trip time:</strong> ' + totalTime + 'ms</p>';
                        html += '<p><strong>Server processing time:</strong> ' + response.data.server_processing_time + '</p>';
                        html += '<p><strong>Network latency (estimated):</strong> ' + (totalTime - 1000) + 'ms</p>';
                        html += '</div>';
                        $result.html(html);
                    } else {
                        $result.html('<p class="tp-error">Error: ' + response.data.message + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Timing test error:', xhr.responseText, status, error);
                    const endTime = new Date().getTime();
                    const totalTime = endTime - startTime;
                    
                    let html = '<p class="tp-error">Ajax Error: ' + error + '</p>';
                    html += '<p class="tp-error">Status: ' + status + '</p>';
                    html += '<p class="tp-error">Response: ' + xhr.responseText + '</p>';
                    html += '<p><strong>Failed after:</strong> ' + totalTime + 'ms</p>';
                    $result.html(html);
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });
    });
    
})(jQuery);
