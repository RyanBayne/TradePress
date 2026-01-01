/**
 * TradePress - Discord Stock VIP Integration Scripts
 *
 * JavaScript functionality for the Discord Stock VIP tab
 *
 * @author   AI Assistant
 * @category Admin
 * @package  TradePress/admin/page/SocialPlatforms
 * @since    1.0.0
 * @created  April 23, 2025
 */

jQuery(document).ready(function($) {
    // Initialize variables
    const alertsContainer = $('.stock-alerts-container');
    const alertsGrid = $('.stock-alerts-grid');
    const loadingOverlay = $('.loading-overlay');
    const filterSelect = $('#alert-type-filter');
    const refreshButton = $('#refresh-alerts');
    
    // Hide loading overlay initially
    loadingOverlay.hide();
    
    /**
     * Filter alerts based on selected type
     */
    filterSelect.on('change', function() {
        const selectedType = $(this).val();
        
        if (selectedType === 'all') {
            // Show all alerts
            $('.stock-alert-card').show();
        } else {
            // Hide all alerts first
            $('.stock-alert-card').hide();
            // Show only alerts with the selected type
            $(`.stock-alert-card.${selectedType}`).show();
        }
    });
    
    /**
     * Refresh alerts
     */
    refreshButton.on('click', function() {
        // Show loading overlay
        loadingOverlay.show();
        
        // Simulate an AJAX call to get fresh data
        setTimeout(function() {
            // This would be replaced with a real AJAX call to fetch discord messages
            simulateDataRefresh();
            
            // Hide loading overlay
            loadingOverlay.hide();
            
            // Show success notification
            showNotification('Alerts refreshed successfully!', 'success');
        }, 1500);
    });
    
    /**
     * Handle pagination
     */
    $('.next-page').on('click', function() {
        // Simulate pagination - would be replaced with real pagination
        $('.current-page').text('2');
        showNotification('Next page of alerts loaded', 'info');
    });
    
    $('.prev-page').on('click', function() {
        // Simulate pagination - would be replaced with real pagination
        $('.current-page').text('1');
        showNotification('Previous page of alerts loaded', 'info');
    });
    
    /**
     * View alert details
     */
    $(document).on('click', '.view-details-link', function(e) {
        e.preventDefault();
        const ticker = $(this).data('ticker');
        
        // Create and show a dialog with more details
        showAlertDetailsDialog(ticker);
    });
    
    /**
     * Simulate refreshing the data
     * This would be replaced with actual AJAX call to the backend
     */
    function simulateDataRefresh() {
        // In a real implementation, this would fetch data from the server
        // For now, we'll just indicate the refresh was successful
        
        // Apply a brief highlight effect to show the refresh happened
        alertsGrid.fadeOut(200).fadeIn(200);
    }
    
    /**
     * Show notification
     * 
     * @param {string} message The message to display
     * @param {string} type The notification type (success, error, info)
     */
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = $(`
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss"></button>
            </div>
        `);
        
        // Add to the top of the page
        $('.wrap').prepend(notification);
        
        // Set up dismiss functionality
        notification.find('.notice-dismiss').on('click', function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Auto dismiss after 3 seconds
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    /**
     * Show alert details dialog
     * 
     * @param {string} ticker The stock ticker symbol
     */
    function showAlertDetailsDialog(ticker) {
        // Find the alert data (in a real scenario, this would be from an API)
        const alertCard = $(`.stock-alert-card .ticker:contains('${ticker}')`).closest('.stock-alert-card');
        const alertType = alertCard.find('.alert-type').text();
        const company = alertCard.find('.company-name').text();
        const price = alertCard.find('.detail-value.price').text();
        
        // Create dialog content
        const dialogContent = $(`
            <div class="alert-details-dialog">
                <h2>${ticker} - ${company}</h2>
                <div class="alert-dialog-content">
                    <div class="alert-dialog-section">
                        <h3>Alert Details</h3>
                        <div class="dialog-detail-row">
                            <span class="dialog-label">Alert Type:</span>
                            <span class="dialog-value">${alertType}</span>
                        </div>
                        <div class="dialog-detail-row">
                            <span class="dialog-label">Current Price:</span>
                            <span class="dialog-value">${price}</span>
                        </div>
                    </div>
                    
                    <div class="alert-dialog-section">
                        <h3>Market Data</h3>
                        <div class="dialog-detail-row">
                            <span class="dialog-label">Market Cap:</span>
                            <span class="dialog-value">$42.8B</span>
                        </div>
                        <div class="dialog-detail-row">
                            <span class="dialog-label">52-Week Range:</span>
                            <span class="dialog-value">$124.17 - $187.42</span>
                        </div>
                        <div class="dialog-detail-row">
                            <span class="dialog-label">Average Volume:</span>
                            <span class="dialog-value">3.2M</span>
                        </div>
                    </div>
                    
                    <div class="alert-dialog-section">
                        <h3>Trading Strategy</h3>
                        <p>Based on the Discord alert and current market conditions, this security appears to have 
                           momentum potential with the following recommended parameters:</p>
                        <div class="dialog-detail-row">
                            <span class="dialog-label">Entry Strategy:</span>
                            <span class="dialog-value">Buy on breakout above resistance with volume confirmation</span>
                        </div>
                        <div class="dialog-detail-row">
                            <span class="dialog-label">Position Size:</span>
                            <span class="dialog-value">2% of trading capital</span>
                        </div>
                        <div class="dialog-detail-row">
                            <span class="dialog-label">Stop Loss:</span>
                            <span class="dialog-value">3-5% below entry</span>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Style the dialog content
        dialogContent.css({
            'max-width': '800px',
            'padding': '10px'
        });
        
        dialogContent.find('.alert-dialog-section').css({
            'margin-bottom': '20px',
            'padding-bottom': '15px',
            'border-bottom': '1px solid #eee'
        });
        
        dialogContent.find('h3').css({
            'margin-top': '0',
            'margin-bottom': '15px',
            'color': '#23282d'
        });
        
        dialogContent.find('.dialog-detail-row').css({
            'margin-bottom': '10px',
            'display': 'flex'
        });
        
        dialogContent.find('.dialog-label').css({
            'font-weight': 'bold',
            'width': '130px',
            'display': 'inline-block'
        });
        
        // Create and show the dialog
        dialogContent.dialog({
            title: `Alert Details: ${ticker}`,
            dialogClass: 'wp-dialog',
            autoOpen: true,
            draggable: true,
            width: 'auto',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            position: {
                my: "center",
                at: "center",
                of: window
            },
            buttons: [
                {
                    text: "Add to Watchlist",
                    class: "button-primary",
                    click: function() {
                        showNotification(`${ticker} added to watchlist`, 'success');
                    }
                },
                {
                    text: "Close",
                    click: function() {
                        $(this).dialog("close");
                    }
                }
            ],
            open: function() {
                // Add close button styling
                $(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').addClass('ui-button');
            },
            close: function() {
                $(this).dialog("destroy").remove();
            }
        });
    }
    
    /**
     * Settings form handling
     */
    $('form[name="save_stock_vip_settings"]').on('submit', function(e) {
        // In a real implementation, this would be handled by the server
        // For now, we'll just show a success message
        showNotification('Settings saved successfully!', 'success');
    });
});