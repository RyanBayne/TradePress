/**
 * TradePress - Event Debugger Partial Scripts
 *
 * @package TradePress/Admin/JS
 */

jQuery(document).ready(function($) {
    // Event debugger toggle
    $('.event-debug-toggle-container').on('click', function() {
        var $container = $('.event-debugger-container');
        if ($container.is(':visible')) {
            $(this).text('Show Debugger');
            $container.slideUp();
        } else {
            $(this).text('Hide Debugger');
            $container.slideDown();
        }
    });
    
    // Create fixed debugger button at bottom right
    $('body').append('<div class="floating-debugger-toggle">Debug</div>');
    
    $('.floating-debugger-toggle').on('click', function() {
        $('.event-debug-toggle-container').click();
    });
    
    // Clear log
    $('.event-debug-clear').on('click', function() {
        $('.event-log-entries').empty();
        logEvent('system', 'Log cleared', 'user action');
    });
    
    // Start event monitoring
    $('.event-debug-start').on('click', function() {
        logEvent('system', 'Event monitoring started', 'user action');
        
        // Monitor quick action button clicks
        $(document).on('click', '.quick-action-button', function(e) {
            logEvent('click', 
                    'Quick action button: ' + $(this).text().trim(), 
                    'Target: ' + $(this).data('target'));
        });
        
        // Monitor content section visibility
        setInterval(function() {
            $('.content-section').each(function() {
                var $section = $(this);
                var isVisible = $section.is(':visible');
                var id = $section.attr('id');
                logEvent('visibility', 
                        'Content section: ' + id, 
                        'Visible: ' + isVisible);
            });
        }, 2000); // Check every 2 seconds
        
        $(this).prop('disabled', true).text('Monitoring Active');
    });
    
    // Helper function to log events
    function logEvent(type, element, details) {
        var now = new Date();
        var timestamp = now.getHours() + ':' + 
                        ('0' + now.getMinutes()).slice(-2) + ':' + 
                        ('0' + now.getSeconds()).slice(-2) + '.' +
                        ('00' + now.getMilliseconds()).slice(-3);
        
        var $entry = $('<div class="event-log-entry"></div>');
        $entry.append('<span class="event-timestamp">' + timestamp + '</span>');
        $entry.append('<span class="event-type">' + type + '</span>');
        $entry.append('<span class="event-element">' + element + '</span>');
        $entry.append('<span class="event-details">' + details + '</span>');
        
        $('.event-log-entries').prepend($entry);
        
        // Limit to 50 entries
        if ($('.event-log-entry').length > 50) {
            $('.event-log-entry').last().remove();
        }
    }
    
    // Log initial state
    logEvent('system', 'Event debugger initialized', 'waiting for start');
});