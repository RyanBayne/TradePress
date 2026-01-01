/**
 * TradePress Directive Testing JavaScript
 *
 * @package TradePress/Assets/JS
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    
    // Show ISA Reset directive by default on page load
    $('.directive-section').hide();
    $('#directive-isa_reset').show();
    $('.directive-row[data-directive="isa_reset"]').addClass('selected');
    
    // Handle Test button clicks
    $('.test-directive').on('click', function(e) {
        e.preventDefault();
        
        const directiveId = $(this).data('directive');
        const button = $(this);
        
        // Show modal
        showTestModal(directiveId);
        
        // Start test
        runDirectiveTest(directiveId, button);
    });
    
    /**
     * Show test modal
     */
    function showTestModal(directiveId) {
        const modal = $('#directive-test-modal');
        const title = $('#directive-test-modal-title');
        const loading = $('#directive-test-modal-loading');
        const results = $('#directive-test-modal-results');
        
        title.text('Testing ' + directiveId.replace('_', ' ').toUpperCase());
        loading.show();
        results.hide();
        modal.show();
    }
    
    /**
     * Run directive test
     */
    function runDirectiveTest(directiveId, button) {
        const originalText = button.text();
        button.text('Testing...').prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_test_directive',
                directive_id: directiveId,
                symbol: 'NVDA',
                force_fresh: true,
                nonce: tradepressDirectiveTest.nonce
            },
            success: function(response) {
                if (response.success) {
                    showTestResults(response);
                } else {
                    showTestError(response.error || 'Test failed');
                }
            },
            error: function() {
                showTestError('Network error occurred');
            },
            complete: function() {
                button.text(originalText).prop('disabled', false);
            }
        });
    }
    
    /**
     * Show test results
     */
    function showTestResults(data) {
        const loading = $('#directive-test-modal-loading');
        const results = $('#directive-test-modal-results');
        const scoreValue = $('#directive-test-score-value');
        const message = $('#directive-test-message');
        const dataTable = $('#directive-test-data tbody');
        
        // Hide loading, show results
        loading.hide();
        results.show();
        
        // Update score
        scoreValue.text(data.score);
        scoreValue.removeClass('score-low score-medium score-high');
        if (data.score > 15) {
            scoreValue.addClass('score-high');
        } else if (data.score > 5) {
            scoreValue.addClass('score-medium');
        } else {
            scoreValue.addClass('score-low');
        }
        
        // Update message
        message.text(data.message);
        
        // Update data table
        dataTable.empty();
        if (data.data_analysis) {
            $.each(data.data_analysis, function(key, value) {
                dataTable.append(
                    '<tr><td>' + key + '</td><td>' + value + '</td></tr>'
                );
            });
        }
        
        // Add execution time
        dataTable.append(
            '<tr><td>Execution Time</td><td>' + data.execution_time + '</td></tr>'
        );
    }
    
    /**
     * Show test error
     */
    function showTestError(error) {
        const loading = $('#directive-test-modal-loading');
        const results = $('#directive-test-modal-results');
        const message = $('#directive-test-message');
        
        loading.hide();
        results.show();
        
        $('#directive-test-score-value').text('0').removeClass().addClass('score-error');
        message.text('Error: ' + error);
        $('#directive-test-data tbody').html('<tr><td colspan="2">Test failed</td></tr>');
    }
    
    // Close modal handlers
    $('.directive-test-close, #directive-test-close-btn').on('click', function() {
        $('#directive-test-modal').hide();
    });
    
    // Close modal on outside click
    $('#directive-test-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Handle Configure/View button clicks - simple show/hide
    $('.view-directive').on('click', function(e) {
        e.preventDefault();
        
        const directiveId = $(this).data('directive');
        
        // Hide all directive sections
        $('.directive-section').hide();
        
        // Show the selected directive section
        $('#directive-' + directiveId).show();
        
        // Highlight selected row
        $('.directive-row').removeClass('selected');
        $(this).closest('.directive-row').addClass('selected');
    });
    
    // Show ISA Reset directive by default on page load
    $(document).ready(function() {
        $('.directive-section').hide();
        $('#directive-isa_reset').show();
        $('.directive-row[data-directive="isa_reset"]').addClass('selected');
    });
    
    /**
     * Show dynamic directive configuration
     */
    function showDynamicDirectiveConfig(directiveId) {
        // Check if tradepressDirectives is available
        if (typeof tradepressDirectives === 'undefined' || !tradepressDirectives.directives) {
            console.log('tradepressDirectives not available');
            $('#directive-placeholder').show();
            return;
        }
        
        const directive = tradepressDirectives.directives[directiveId];
        if (!directive) {
            console.log('Directive not found:', directiveId);
            console.log('Available directives:', Object.keys(tradepressDirectives.directives));
            $('#directive-placeholder').show();
            return;
        }
        

        
        const dynamicSection = $('#directive-dynamic');
        
        // Update title and content
        $('.dynamic-title').text(directive.name || 'Unknown Directive');
        $('.dynamic-description').text(directive.description || 'No description available');
        $('.dynamic-weight').val(directive.weight || 10);
        $('.dynamic-bullish').text(directive.bullish || 'N/A');
        $('.dynamic-bearish').text(directive.bearish || 'N/A');
        $('.dynamic-active').prop('checked', directive.active || false);
        
        // Update tip content

        if (directive.tip) {
            $('.directive-tip-content').text(directive.tip);
            $('.directive-tip-box').show();
        } else {
            $('.directive-tip-box').hide();
        }
        
        // Store directive ID for saving
        dynamicSection.data('directive-id', directiveId);
        
        // Show the dynamic section
        dynamicSection.show();
    }
    
    // Handle Enable/Disable button clicks (use event delegation)
    $(document).on('click', '.toggle-directive', function(e) {
        e.preventDefault();
        
        const directiveId = $(this).data('directive');
        const currentActive = $(this).data('active') === '1';
        const newActive = !currentActive;
        const button = $(this);
        
        // ONLY update button text and data - absolutely no other changes
        button.data('active', newActive ? '1' : '0');
        button.text(newActive ? 'Disable' : 'Enable');
        
        // Save state to WordPress options
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_toggle_directive',
                directive_id: directiveId,
                active: newActive,
                nonce: tradepressDirectiveTest.toggleNonce
            }
        });
        
        // Prevent any other event handlers from running
        e.stopPropagation();
        e.stopImmediatePropagation();
        return false;
    });
});