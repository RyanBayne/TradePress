/**
 * TradePress Directive Test JavaScript
 * 
 * Handles the interaction with the directive test functionality.
 */
(function($) {
    'use strict';
    
    // Debug flag for logging info
    var debug = true;
    
    if (debug) {
        console.log('TradePress Directive Test script loaded');
    }
    
    // Make sure we run initialization directly, don't just wait for document.ready
    initDirectiveTests();
    
    // Also add the standard jQuery ready method as a backup
    $(document).ready(function() {
        console.log('Document ready - initializing directive tests');
        initDirectiveTests();
    });
    
    function initDirectiveTests() {
        if (debug) {
            console.log('Initializing directive tests');
        }
        
        // Cache DOM elements
        var $modal = $('#directive-test-modal');
        var $modalTitle = $('#directive-test-modal-title');
        var $modalLoading = $('#directive-test-modal-loading');
        var $modalResults = $('#directive-test-modal-results');
        var $modalScore = $('#directive-test-score-value');
        var $modalMessage = $('#directive-test-message');
        var $modalDataTable = $('#directive-test-data tbody');
        var $closeBtn = $('.directive-test-close, #directive-test-close-btn');
        var $testButtons = $('.test-directive');
        var $manualTestButton = $('#manual-test-button');
        
        if (debug) {
            console.log('DOM elements:', {
                'Modal found': $modal.length > 0,
                'Test buttons found': $testButtons.length,
                'Manual test button found': $manualTestButton.length > 0
            });
        }
        
        // Fix: Explicitly unbind and rebind event handlers to ensure they're attached
        if ($testButtons.length > 0) {
            console.log('Attaching click handlers to', $testButtons.length, 'test buttons');
            
            // Use direct function binding for event handlers instead of .on()
            $testButtons.each(function() {
                $(this).unbind('click').bind('click', handleTestDirective);
            });
            
            // Double-check binding success
            setTimeout(function() {
                try {
                    var firstButton = $testButtons.first()[0];
                    var events = $._data(firstButton, "events");
                    console.log('Event binding check:', events ? 'Success' : 'Failed');
                    if (!events || !events.click) {
                        console.error('Warning: Events not attached, trying alternative binding');
                        // Try alternative binding method if jQuery data method fails
                        $testButtons.each(function() {
                            $(this).click(handleTestDirective);
                        });
                    }
                } catch(e) {
                    console.error('Event binding check failed:', e);
                }
            }, 100);
        }
        
        // Also use direct function binding for other elements
        if ($closeBtn.length > 0) {
            $closeBtn.unbind('click').bind('click', closeModal);
        }
        
        if ($manualTestButton.length > 0) {
            $manualTestButton.unbind('click').bind('click', handleManualTest);
        }
        
        // Close modal if user clicks outside of it
        $(window).unbind('click.directive-modal').bind('click.directive-modal', function(event) {
            if ($(event.target).is($modal)) {
                closeModal();
            }
        });
        
        /**
         * Handle test directive button click
         */
        function handleTestDirective(e) {
            e.preventDefault();
            
            console.log('Test button clicked');
            
            // Get directive slug
            var directiveSlug = $(this).data('directive');
            var directiveName = $(this).closest('tr').find('td:first-child').text().trim();
            
            console.log('Testing directive: ' + directiveSlug);
            
            // Show modal with loading state
            $modalTitle.text('Testing: ' + directiveName + ' on NVDA');
            $modalLoading.show();
            $modalResults.hide();
            $modal.css('display', 'block');
            
            // Send AJAX request to test directive
            $.ajax({
                url: tradepressDirectiveTest.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'test_scoring_directive',
                    directive: directiveSlug,
                    nonce: tradepressDirectiveTest.nonce
                },
                success: function(response) {
                    console.log('AJAX success, full response:', response);
                    
                    if (response.success && response.data) {
                        displayTestResults(response.data);
                    } else {
                        console.error('Invalid response format:', response);
                        alert('Invalid response format. Check console for details.');
                        closeModal();
                    }
                },
                error: function(xhr, status, error) { 
                    console.error('AJAX request error:', status, error, xhr.responseText);
                    alert(tradepressDirectiveTest.error_message + ' Check console for details.');
                    closeModal();
                }
            });
        }
        
        /**
         * Handle manual test button click
         */
        function handleManualTest() {
            console.log('Manual test button clicked');
            
            // Show exactly what we're sending in the request
            var ajaxParams = {
                url: tradepressDirectiveTest.ajax_url,
                data: {
                    action: 'test_scoring_directive',
                    directive: 'price_above_sma_50',
                    nonce: tradepressDirectiveTest.nonce
                }
            };
            
            console.log('AJAX request parameters:', ajaxParams);
            
            // Show modal with loading state
            $modalTitle.text('Testing: Price Above SMA 50 on NVDA (Manual)');
            $modalLoading.show();
            $modalResults.hide();
            $modal.css('display', 'block');
            
            $.ajax({
                url: tradepressDirectiveTest.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'test_scoring_directive',
                    directive: 'price_above_sma_50',
                    nonce: tradepressDirectiveTest.nonce
                },
                success: function(response) {
                    console.log('Manual test success, full response:', response);
                    
                    if (response.success && response.data) {
                        displayTestResults(response.data);
                    } else {
                        console.error('Invalid response format:', response);
                        alert('Invalid response format. Check console for details.');
                        closeModal();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Manual test error:', status, error);
                    console.log('Response text:', xhr.responseText);
                    alert('AJAX test failed. Check the console for details.');
                    closeModal();
                }
            });
        }
        
        /**
         * Display test results in modal
         */
        function displayTestResults(data) {
            // Update score display
            $modalScore.text(data.score);
            
            // Color the score based on value
            var scoreColor = getScoreColor(data.score);
            $modalScore.css('color', scoreColor);
            
            // Update message
            $modalMessage.text(data.message);
            
            // Clear and populate data table
            $modalDataTable.empty();
            $.each(data.data, function(key, value) {
                $modalDataTable.append(
                    $('<tr>').append(
                        $('<td>').text(key),
                        $('<td>').text(value)
                    )
                );
            });
            
            // Hide loading, show results
            $modalLoading.hide();
            $modalResults.show();
        }
        
        /**
         * Get color for score display
         */
        function getScoreColor(score) {
            if (score >= 80) {
                return '#2e7d32'; // Green for strong positive
            } else if (score >= 60) {
                return '#689f38'; // Light green for moderate positive
            } else if (score >= 40) {
                return '#ff9800'; // Orange for neutral
            } else if (score >= 20) {
                return '#f57c00'; // Dark orange for moderate negative
            } else {
                return '#c62828'; // Red for strong negative
            }
        }
        
        /**
         * Close the modal
         */
        function closeModal() {
            $modal.css('display', 'none');
        }
    }
})(jQuery);
