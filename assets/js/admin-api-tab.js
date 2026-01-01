/**
 * TradePress Admin API Tab JavaScript
 * 
 * Handles functionality for the API tabs in the admin area
 * 
 * @package TradePress
 * @since 1.0.2
 */

jQuery(document).ready(function($) {
    console.log('API tab script initializing'); // Debug
    
    // Handle quick action buttons
    $('.quick-action-button').on('click', function(e) {
        e.preventDefault();
        console.log('Quick action button clicked: ' + $(this).data('target')); // Debug
        
        var target = $(this).data('target');
        
        // Hide all content sections
        $('.content-section').hide();
        
        // Show the target section
        $('#' + target).show();
        
        // Update active button
        $('.quick-action-button').removeClass('active');
        $(this).addClass('active');
        
        // Scroll to the section if needed
        if (target && $('#' + target).length > 0) {
            if (!isElementInViewport($('#' + target)[0])) {
                $('html, body').animate({
                    scrollTop: $('#' + target).offset().top - 50
                }, 500);
            }
        }
    });
    
    // Check if element is in viewport
    function isElementInViewport(el) {
        if (!el) {
            return false;
        }
        
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // By default, show the API status section
    $('#api-status-view').show();
    $('.quick-action-button[data-target="api-status-view"]').addClass('active');
    
    // Handle refresh button click
    $('.refresh-debug-info').on('click', function() {
        var button = $(this);
        var apiId = button.data('api');
        var nonce = button.data('nonce');
        var container = $('#api-call-info-container');
        
        // Add loading state
        button.addClass('loading');
        
        // Send Ajax request to refresh the debug info
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'refresh_debug_info',
                api_id: apiId,
                security: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update the content with the new HTML
                    container.html(response.data.html);
                } else {
                    // Show error message
                    container.html('<div class="api-info-message"><p>Error refreshing data: ' + 
                        (response.data ? response.data.message : 'Unknown error') + '</p></div>');
                }
            },
            error: function() {
                // Show error message for AJAX failure
                container.html('<div class="api-info-message"><p>Error refreshing data. Please try again.</p></div>');
            },
            complete: function() {
                // Remove loading state
                button.removeClass('loading');
            }
        });
    });
    
    // IMPORTANT: DO NOT ADD AJAX FUNCTIONALITY FOR ENDPOINT TESTING
    // The form submission process for endpoint testing must use standard POST
    // to ensure proper processing and display of test results.
    
    // Log that script has fully initialized
    console.log('API tab script fully initialized');
});