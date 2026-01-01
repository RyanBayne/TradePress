jQuery(document).ready(function($) {
    // Initialize - show the ISA Reset directive by default
    showDirectiveDetails('isa_reset');
    $('.directive-row[data-directive="isa_reset"]').addClass('selected');
    
    // Handle directive row clicks
    $('.directive-row').on('click', function() {
        var directiveId = $(this).data('directive');
        
        // Update selected row
        $('.directive-row').removeClass('selected');
        $(this).addClass('selected');
        
        // Show the corresponding directive details
        showDirectiveDetails(directiveId);
    });
    
    // View directive button click
    $('.view-directive').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var directiveId = $(this).data('directive');
        
        // Update selected row
        $('.directive-row').removeClass('selected');
        $('.directive-row[data-directive="' + directiveId + '"]').addClass('selected');
        
        // Show the corresponding directive details
        showDirectiveDetails(directiveId);
    });
    
    // Test directive button click
    $('.test-directive').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var directiveId = $(this).data('directive');
        
        $('#directive-test-modal').show();
        $('#directive-test-modal-title').text('Testing ' + directiveId + ' Directive');
        $('#directive-test-modal-loading').show();
        $('#directive-test-modal-results').hide();
        
        setTimeout(function() {
            $('#directive-test-modal-loading').hide();
            $('#directive-test-modal-results').show();
            $('#directive-test-score-value').text('85');
            $('#directive-test-message').text('Test completed successfully on NVDA.');
            
            var testData = '<tr><td>Current Price</td><td>$954.73</td></tr>';
            testData += '<tr><td>SMA 50</td><td>$877.25</td></tr>';
            testData += '<tr><td>Above SMA 50</td><td>Yes</td></tr>';
            $('#directive-test-data tbody').html(testData);
        }, 2000);
    });
    
    // Close modal events
    $('.directive-test-close, #directive-test-close-btn').on('click', function() {
        $('#directive-test-modal').hide();
    });
    
    // Directives Status functionality
    $('#directive-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.tradepress-directives-status-table tbody tr.directive-row').each(function() {
            var directiveName = $(this).data('name');
            if (directiveName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#directive-filter-status').on('change', function() {
        var filterStatus = $(this).val();
        $('.tradepress-directives-status-table tbody tr.directive-row').each(function() {
            if (filterStatus === 'all' || $(this).data('status') === filterStatus) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $(document).on('click', '.toggle-description', function(e) {
        e.preventDefault();
        $(this).closest('td').find('.directive-description').slideToggle('fast');
        var text = $(this).text();
        $(this).text(text == 'View Details' ? 'Hide Details' : 'View Details');
    });
    
    $('#toggle-all-descriptions').on('click', function(e) {
        e.preventDefault();
        var descriptions = $('.directive-description');
        var anyHidden = descriptions.filter(':hidden').length > 0;
        if (anyHidden) {
            descriptions.slideDown('fast');
            $('.toggle-description').text('Hide Details');
        } else {
            descriptions.slideUp('fast');
            $('.toggle-description').text('View Details');
        }
    });
    
    $(document).on('click', '.favorite-directive', function(e) {
        e.preventDefault();
        var $button = $(this);
        var directiveId = $button.data('directive-id');
        
        $button.toggleClass('is-favorite');
        $button.find('.dashicons').toggleClass('dashicons-star-empty dashicons-star-filled');
        if ($button.hasClass('is-favorite')) {
            $button.attr('title', 'Remove from favorites');
        } else {
            $button.attr('title', 'Add to favorites');
        }
    });
    
    $(document).on('click', '.hide-directive', function(e) {
        e.preventDefault();
        var $button = $(this);
        var directiveId = $button.data('directive-id');
        var $row = $('#directive-' + directiveId);
        
        $row.toggleClass('is-hidden');
        $button.find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
        if ($row.hasClass('is-hidden')) {
            $button.attr('title', 'Show directive');
        } else {
            $button.attr('title', 'Hide directive');
        }
    });
    
    // Toggle directive active status
    $('.toggle-directive').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $this = $(this);
        var directiveId = $this.data('directive');
        var isActive = $this.data('active') === 1;
        
        // Toggle the status
        isActive = !isActive;
        
        // Update button text and data attribute
        $this.text(isActive ? 'Disable' : 'Enable');
        $this.data('active', isActive ? 1 : 0);
        
        // Update the row status
        var $row = $('.directive-row[data-directive="' + directiveId + '"]');
        if (isActive) {
            $row.removeClass('status-inactive').addClass('status-active');
            $row.find('.status-badge').removeClass('status-inactive').addClass('status-active').text('Active');
        } else {
            $row.removeClass('status-active').addClass('status-inactive');
            $row.find('.status-badge').removeClass('status-active').addClass('status-inactive').text('Inactive');
        }
        
        // Update the directive toggle switch if it's currently visible
        $('#directive-' + directiveId + ' .switch input').prop('checked', isActive);
        
        // In a real implementation, you would send an AJAX request here
        console.log('Toggling directive: ' + directiveId + ' to ' + (isActive ? 'active' : 'inactive'));
    });
    
    // Save ISA directive settings
    $('#save-isa-directive').on('click', function() {
        var $status = $('#isa-status');
        var isActive = $('#isa-reset-active').is(':checked');
        var daysBefore = $('#isa-days-before').val();
        var daysAfter = $('#isa-days-after').val();
        var scoreImpact = $('#isa-score-impact').val();
        
        $status.html('<span style="color: #0073aa;">Saving...</span>');
        
        // AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_update_isa_reset_directive',
                nonce: tradepressScoringDirectives.nonce,
                is_active: isActive ? 1 : 0,
                days_before: daysBefore,
                days_after: daysAfter,
                score_impact: scoreImpact
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<span style="color: #00a32a;">✓ Saved successfully</span>');
                } else {
                    $status.html('<span style="color: #d63638;">✗ Save failed</span>');
                }
                
                setTimeout(function() {
                    $status.html('');
                }, 3000);
            },
            error: function() {
                $status.html('<span style="color: #d63638;">✗ Save failed</span>');
                setTimeout(function() {
                    $status.html('');
                }, 3000);
            }
        });
    });
    
    // Logs functionality
    setInterval(function() {
        console.log('Auto-refreshing logs...');
    }, 30000);
    
    $('.refresh-logs-btn').on('click', function() {
        console.log('Refreshing logs manually...');
    });
    
    $('.clear-logs-btn').on('click', function() {
        if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
            console.log('Clearing logs...');
        }
    });
    
    $('#log-level, #log-source').on('change', function() {
        console.log('Applying filters...');
    });
    
    // Testing functionality
    $('.run-test-btn').on('click', function() {
        $('#results-status').text('Running test...');
        $('#results-content').hide();
        
        setTimeout(function() {
            $('#results-status').text('Test completed successfully');
            $('#overall-score').text(Math.floor(Math.random() * 40 + 60));
            $('#execution-time').text((Math.random() * 2 + 0.5).toFixed(2) + 's');
            $('#api-calls').text(Math.floor(Math.random() * 10 + 5));
            $('#confidence-level').text(Math.floor(Math.random() * 20 + 75) + '%');
            $('#results-content').show();
        }, 2000);
    });
    
    $('.clear-results-btn').on('click', function() {
        $('#results-content').hide();
        $('#results-status').text('No test results yet. Configure a test and click "Run Test" to begin.');
    });
    
    // Function to show the selected directive details
    function showDirectiveDetails(directiveId) {
        // Hide all directive sections
        $('.directive-section').hide();
        
        // Show the selected directive section
        $('#directive-' + directiveId).show();
        
        // If the directive doesn't exist, show placeholder
        if ($('#directive-' + directiveId).length === 0) {
            $('#directive-placeholder').show();
            
            // Update placeholder title
            var directiveName = $('.directive-row[data-directive="' + directiveId + '"] .directive-name').text();
            $('.placeholder-title').text(directiveName);
        }
    }
});
