/**
 * TradePress Research JavaScript
 * 
 * @package TradePress
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    // Symbol selection functionality
    $('#symbol-select').on('change', function() {
        if ($(this).val() !== '') {
            $(this).closest('form').submit();
        }
    });
    
    // Time horizon tab functionality
    $('.time-horizon-tab').on('click', function() {
        var horizon = $(this).data('horizon');
        
        // Update active tab
        $('.time-horizon-tab').removeClass('active');
        $(this).addClass('active');
        
        // Update active content
        $('.time-horizon-content').removeClass('active');
        $('#horizon-' + horizon).addClass('active');
    });
    
    // News Feed functionality
    $('#load-more-feed').on('click', function() {
        alert('In a real implementation, this would load more feed items via AJAX.');
    });
    
    $('.save-feed-item').on('click', function() {
        var itemId = $(this).data('id');
        alert('Item ' + itemId + ' saved!');
    });
    
    // Research Overview functionality
    $('.tab-button').on('click', function() {
        const tabId = $(this).data('tab');
        
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        
        $('.tab-pane').removeClass('active');
        $('#' + tabId + '-tab').addClass('active');
    });
    
    $('.refresh-data').on('click', function() {
        const $card = $(this).closest('.overview-card');
        
        $(this).addClass('dashicons-update-spin');
        
        setTimeout(() => {
            $(this).removeClass('dashicons-update-spin');
            
            const $content = $card.find('.card-content');
            const originalContent = $content.html();
            
            $content.html('<div class="refresh-message">' + 
                          '<span class="dashicons dashicons-yes"></span> ' +
                          'Data refreshed successfully</div>');
                          
            setTimeout(() => {
                $content.html(originalContent);
            }, 1500);
        }, 800);
    });
    
    // Sector Rotation functionality
    $('.sector-name').hover(function() {
        // Show sector info tooltip functionality would go here
    }, function() {
        // Hide sector info tooltip functionality would go here
    });
    
    // Technical Indicators functionality
    // JavaScript functionality for technical indicators will be added here
});

// News Feed custom date range toggle
function toggleCustomDateRange(value) {
    const customDateContainer = document.getElementById('custom-date-container');
    if (value === 'custom') {
        customDateContainer.style.display = 'block';
    } else {
        customDateContainer.style.display = 'none';
    }
}