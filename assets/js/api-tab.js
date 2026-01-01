/**
 * TradePress API Tab JavaScript
 *
 * Handles API tab interactions including testing credentials and refreshing debug info
 *
 * @package TradePress
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    // Show/hide API settings section
    $('.show-api-settings').on('click', function() {
        $('#data-explorer-section').hide();
        $('#api-settings-section').show();
    });

    $('.back-to-explorer').on('click', function(e) {
        e.preventDefault();
        $('#api-settings-section').hide();
        $('#data-explorer-section').show();
    });

    // Data Explorer button active state
    $('.quick-action-button').on('click', function() {
        $('.quick-action-button').removeClass('active');
        $(this).addClass('active');
    });

    // Handle Test Live Trading button click
    $('.test-api-credentials').on('click', function() {
        var button = $(this);
        var apiId = button.data('api');
        var mode = button.data('mode');
        var nonce = button.data('nonce');
        var resultsContainer = $('#api-test-results-' + apiId);
        
        // Show loading state
        button.prop('disabled', true);
        button.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-update loading-spinner');
        
        // Clear previous results
        resultsContainer.empty();
        
        // Send Ajax request to test API credentials
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'test_api_credentials',
                api_id: apiId,
                mode: mode,
                security: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    resultsContainer.html(
                        '<div class="test-result success">' +
                        '<span class="dashicons dashicons-yes-alt"></span> ' +
                        response.data.message +
                        (response.data.details ? '<span class="details">' + response.data.details + '</span>' : '') +
                        '</div>'
                    );
                } else {
                    // Show error message
                    resultsContainer.html(
                        '<div class="test-result error">' +
                        '<span class="dashicons dashicons-warning"></span> ' +
                        (response.data ? response.data.message : 'Unknown error') +
                        (response.data && response.data.details ? '<span class="details">' + response.data.details + '</span>' : '') +
                        '</div>'
                    );
                }
            },
            error: function() {
                // Show error message for AJAX failure
                resultsContainer.html(
                    '<div class="test-result error">' +
                    '<span class="dashicons dashicons-warning"></span> ' +
                    'Connection error. Please try again.' +
                    '</div>'
                );
            },
            complete: function() {
                // Reset button state
                button.prop('disabled', false);
                button.find('.dashicons').removeClass('dashicons-update loading-spinner').addClass('dashicons-yes');
            }
        });
    });

    // Handle refresh debug info button click
    $('.refresh-debug-info').on('click', function() {
        var button = $(this);
        var apiId = button.data('api');
        var nonce = button.data('nonce');
        var container = $('#api-call-info-container');
        
        // Add loading state
        button.addClass('loading');
        button.find('.dashicons').addClass('loading-spinner');
        
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
                button.find('.dashicons').removeClass('loading-spinner');
            }
        });
    });

    // Handle button to refresh API status
    $('.button-refresh').on('click', function(e) {
        e.preventDefault();
        
        // Reload the current page to refresh all API status information
        location.reload();
    });

    // Handle clear cache button
    $('.clear-cache').on('click', function() {
        var button = $(this);
        var apiId = button.data('api');
        
        // Add loading state
        button.find('.dashicons').addClass('loading-spinner');
        
        // Send Ajax request to clear the API cache
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'clear_api_cache',
                api_id: apiId,
                security: wp_nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message as notification
                    alert(response.data.message || 'Cache cleared successfully.');
                } else {
                    // Show error message
                    alert(response.data.message || 'Error clearing cache.');
                }
            },
            error: function() {
                // Show error message for AJAX failure
                alert('Connection error. Please try again.');
            },
            complete: function() {
                // Remove loading state
                button.find('.dashicons').removeClass('loading-spinner');
            }
        });
    });
});