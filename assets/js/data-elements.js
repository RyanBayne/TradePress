/**
 * TradePress Data Elements Tab JavaScript
 */

jQuery(document).ready(function($) {
    
    // Manual import functionality
    $('.manual-import').on('click', function() {
        const button = $(this);
        const elementId = button.data('element');
        
        if (button.hasClass('importing')) {
            return; // Already importing
        }
        
        button.addClass('importing').prop('disabled', true).text('Importing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_manual_import',
                element_id: elementId,
                nonce: tradepress_data_elements.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update last import time
                    $(`[data-element="${elementId}"] .import-time`).text(response.data.import_time);
                    
                    // Update status
                    $(`[data-element="${elementId}"] .status-indicator`).html(response.data.status);
                    
                    // Show success message
                    showNotice('Import completed successfully', 'success');
                } else {
                    showNotice('Import failed: ' + response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('Import request failed', 'error');
            },
            complete: function() {
                button.removeClass('importing').prop('disabled', false).text('Import Now');
            }
        });
    });
    
    // Refresh status functionality
    $('#refresh-status').on('click', function() {
        const button = $(this);
        button.prop('disabled', true).text('Refreshing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_refresh_data_status',
                nonce: tradepress_data_elements.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update all status indicators and import times
                    Object.keys(response.data).forEach(function(elementId) {
                        const data = response.data[elementId];
                        $(`[data-element="${elementId}"] .import-time`).text(data.import_time);
                        $(`[data-element="${elementId}"] .status-indicator`).html(data.status);
                    });
                    
                    showNotice('Status refreshed', 'success');
                } else {
                    showNotice('Failed to refresh status', 'error');
                }
            },
            error: function() {
                showNotice('Refresh request failed', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Refresh Status');
            }
        });
    });
    
    // Run all imports functionality
    $('#run-all-imports').on('click', function() {
        if (!confirm('This will run imports for all data elements. This may take several minutes. Continue?')) {
            return;
        }
        
        const button = $(this);
        button.prop('disabled', true).text('Running All Imports...');
        
        // Disable all manual import buttons
        $('.manual-import').prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_run_all_imports',
                nonce: tradepress_data_elements.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update all status indicators and import times
                    Object.keys(response.data).forEach(function(elementId) {
                        const data = response.data[elementId];
                        $(`[data-element="${elementId}"] .import-time`).text(data.import_time);
                        $(`[data-element="${elementId}"] .status-indicator`).html(data.status);
                    });
                    
                    showNotice('All imports completed', 'success');
                } else {
                    showNotice('Some imports failed: ' + response.data.message, 'warning');
                }
            },
            error: function() {
                showNotice('Import request failed', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Run All Imports');
                $('.manual-import').prop('disabled', false);
            }
        });
    });
    
    // Show notice function
    function showNotice(message, type) {
        const noticeClass = type === 'success' ? 'notice-success' : 
                           type === 'warning' ? 'notice-warning' : 'notice-error';
        
        const notice = $(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);
        $('.tradepress-data-elements-container').prepend(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(function() {
                notice.remove();
            });
        }, 5000);
    }
    
    // Auto-refresh status every 5 minutes
    setInterval(function() {
        $('#refresh-status').trigger('click');
    }, 300000); // 5 minutes
    
});