jQuery(document).ready(function($) {
    // Run specific test
    $('.run-test').click(function() {
        var button = $(this);
        var testId = button.data('test-id');
        var row = button.closest('tr');
        
        button.prop('disabled', true).text(tradePressTests.i18n.running);
        
        $.ajax({
            url: tradePressTests.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_run_tests',
                test_id: testId,
                nonce: tradePressTests.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayTestResult(row, response.data);
                } else {
                    alert(response.data || tradePressTests.i18n.error);
                }
            },
            error: function() {
                alert(tradePressTests.i18n.error);
            },
            complete: function() {
                button.prop('disabled', false).text(tradePressTests.i18n.run);
            }
        });
    });

    // Phase 3 specific handlers
    $('#run-phase3-tests').click(function() {
        var button = $(this);
        button.prop('disabled', true).text(tradePressTests.i18n.running);
        
        $.ajax({
            url: tradePressTests.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_run_tests',
                test_suite: 'phase3',
                nonce: tradePressTests.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayPhase3Results(response.data);
                } else {
                    $('#test-results').html(
                        '<div class="notice notice-error"><p>' + 
                        tradePressTests.i18n.error + ': ' + response.data + 
                        '</p></div>'
                    );
                }
            },
            error: function() {
                $('#test-results').html(
                    '<div class="notice notice-error"><p>' + 
                    tradePressTests.i18n.ajaxError + 
                    '</p></div>'
                );
            },
            complete: function() {
                button.prop('disabled', false).text(tradePressTests.i18n.runPhase3);
            }
        });
    });
    
    $('#clear-test-results').click(function() {
        $('#test-results').html(
            '<p><em>' + tradePressTests.i18n.resultsCleared + '</em></p>'
        );
    });
    
    function displayTestResult(row, result) {
        row.find('.test-status').html(getStatusBadge(result.status));
        row.find('.test-last-run').text('just now');
        row.find('.test-success-rate').text(
            result.success_rate ? Math.round(result.success_rate * 10) / 10 + '%' : '-'
        );
    }
    
    function getStatusBadge(status) {
        var statusClasses = {
            active: 'status-active',
            passed: 'status-passed',
            failed: 'status-failed',
            error: 'status-error',
            pending: 'status-pending'
        };
        
        var className = statusClasses[status] || 'status-unknown';
        return '<span class="status-badge ' + className + '">' + status + '</span>';
    }
    
    function displayPhase3Results(results) {
        var html = '<h2>' + tradePressTests.i18n.testResults + '</h2>';
        
        // Overall status
        html += '<div class="test-result ' + results.overall_status + '">';
        html += '<h3>' + tradePressTests.i18n.overallStatus + ': ' + results.overall_status.toUpperCase() + '</h3>';
        html += '<p>' + tradePressTests.i18n.executionTime + ': ' + results.execution_time + '</p>';
        html += '<p>' + tradePressTests.i18n.requirementsSatisfied + ': ' + results.requirements_satisfied.length + '</p>';
        html += '</div>';
        
        // Individual tests
        html += '<h3>' + tradePressTests.i18n.individualResults + '</h3>';
        results.tests.forEach(function(test) {
            html += '<div class="test-result ' + test.status + '">';
            html += '<h4>' + test.name + ' (' + test.status.toUpperCase() + ')</h4>';
            html += '<p><strong>' + tradePressTests.i18n.requirement + ':</strong> ' + test.requirement + '</p>';
            html += '<p><strong>' + tradePressTests.i18n.executionTime + ':</strong> ' + test.execution_time_ms + 'ms</p>';
            
            if (test.details && test.details.length > 0) {
                html += '<div class="test-details">';
                test.details.forEach(function(detail) {
                    html += detail + '<br>';
                });
                html += '</div>';
            }
            html += '</div>';
        });
        
        // Performance summary
        if (results.performance_summary) {
            html += '<div class="performance-summary">';
            html += '<h3>' + tradePressTests.i18n.performanceSummary + '</h3>';
            html += '<p><strong>' + tradePressTests.i18n.apiCallsSaved + ':</strong> ' + results.performance_summary.api_calls_saved + '</p>';
            html += '<p><strong>' + tradePressTests.i18n.cacheHitRate + ':</strong> ' + (results.performance_summary.cache_hit_rate * 100) + '%</p>';
            html += '<p><strong>' + tradePressTests.i18n.memoryUsage + ':</strong> ' + results.performance_summary.memory_usage_mb + 'MB</p>';
            html += '</div>';
        }
        
        // Recommendations
        if (results.recommendations && results.recommendations.length > 0) {
            html += '<div class="recommendations">';
            html += '<h3>' + tradePressTests.i18n.recommendations + '</h3>';
            html += '<ul>';
            results.recommendations.forEach(function(rec) {
                html += '<li>' + rec + '</li>';
            });
            html += '</ul>';
            html += '</div>';
        }
        
        $('#test-results').html(html);
    }
});