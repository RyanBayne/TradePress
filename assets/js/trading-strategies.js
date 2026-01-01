/**
 * TradePress Trading Strategies JavaScript
 *
 * @package TradePress/Admin/JS
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    // Toggle the Quick View row
    $('.quick-view-strategy').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $('#quick-view-row-' + id).toggle();
    });
    
    // Close the Quick View row
    $('.close-quick-view').on('click', function() {
        var id = $(this).data('id');
        $('#quick-view-row-' + id).hide();
    });
    
    // Toggle strategy status
    $('.strategy-toggle').on('change', function() {
        var id = $(this).data('id');
        var isActive = $(this).prop('checked');
        
        console.log('Strategy ' + id + ' status changed to: ' + (isActive ? 'active' : 'inactive'));
        
        if (isActive) {
            $('#strategy-row-' + id + ' .strategy-status').removeClass('status-inactive').addClass('status-active').text('Active');
        } else {
            $('#strategy-row-' + id + ' .strategy-status').removeClass('status-active').addClass('status-inactive').text('Inactive');
        }
    });
    
    // Open the Create Strategy modal
    $('.add-new-strategy').on('click', function(e) {
        e.preventDefault();
        $('#strategy-modal-title').text('Create New Strategy');
        $('#save-strategy').text('Create Strategy');
        $('#strategy-id').val('');
        $('#strategy-is-copy').val('0');
        $('#strategy-form')[0].reset();
        $('#strategy-modal').show();
    });
    
    // Open the Copy Strategy modal
    $('.copy-strategy').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        var name = $('#strategy-row-' + id + ' .column-name a').first().text() + ' (Copy)';
        var description = $('#quick-view-row-' + id + ' .quick-view-section p').first().text();
        
        $('#strategy-modal-title').text('Copy Strategy');
        $('#save-strategy').text('Save Copy');
        $('#strategy-id').val(id);
        $('#strategy-is-copy').val('1');
        $('#strategy-name').val(name);
        $('#strategy-description').val(description);
        
        $('#strategy-modal').show();
    });
    
    // Close the modals
    $('.close-strategy-modal, #cancel-strategy').on('click', function() {
        $('#strategy-modal').hide();
    });
    
    $('.close-strategy-details').on('click', function() {
        $('#strategy-details-modal').hide();
    });
    
    // Toggle margin settings based on the "Allow Margin" checkbox
    $('#strategy-allow-margin').on('change', function() {
        if ($(this).is(':checked')) {
            $('.margin-settings').show();
        } else {
            $('.margin-settings').hide();
        }
    });
    
    // Save strategy
    $('#save-strategy').on('click', function() {
        var form = $('#strategy-form')[0];
        if (form.checkValidity()) {
            alert('Strategy saved successfully! (Demo only - no actual save occurred)');
            $('#strategy-modal').hide();
        } else {
            $('<input type="submit">').hide().appendTo(form).click().remove();
        }
    });
    
    // View Strategy
    $('.view-strategy').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        $('#strategy-details-title').text('Strategy Details');
        $('.strategy-details-loading').show();
        $('.strategy-details-content').hide();
        $('#strategy-details-modal').show();
        
        setTimeout(function() {
            var name = $('#strategy-row-' + id + ' .column-name a').first().text();
            var description = $('#quick-view-row-' + id + ' .quick-view-section p').first().text();
            var successRate = $('#strategy-row-' + id + ' .progress span').text();
            var profitLoss = $('#strategy-row-' + id + ' .column-profit').text();
            
            var content = '<div class="strategy-full-details">' +
                '<h2>' + name + '</h2>' +
                '<div class="strategy-tabs">' +
                '<div class="nav-tab-wrapper">' +
                '<a href="#" class="nav-tab nav-tab-active">Overview</a>' +
                '<a href="#" class="nav-tab">Trade History</a>' +
                '<a href="#" class="nav-tab">Performance</a>' +
                '<a href="#" class="nav-tab">Configuration</a>' +
                '</div>' +
                '<div class="strategy-tab-content">' +
                '<div class="strategy-overview">' +
                '<h3>Description</h3>' +
                '<p>' + description + '</p>' +
                '<h3>Performance Summary</h3>' +
                '<div class="metrics-grid">' +
                '<div class="metric-card">' +
                '<div class="metric-value">' + successRate + '</div>' +
                '<div class="metric-label">Success Rate</div>' +
                '</div>' +
                '<div class="metric-card">' +
                '<div class="metric-value">' + profitLoss + '</div>' +
                '<div class="metric-label">Total P/L</div>' +
                '</div>' +
                '<div class="metric-card">' +
                '<div class="metric-value">24 days</div>' +
                '<div class="metric-label">Avg Hold Time</div>' +
                '</div>' +
                '<div class="metric-card">' +
                '<div class="metric-value">2.8:1</div>' +
                '<div class="metric-label">Risk/Reward</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            $('.strategy-details-content').html(content).show();
            $('.strategy-details-loading').hide();
            
        }, 1000);
    });
    
    // Archive/Unarchive strategy
    $('.archive-strategy, .unarchive-strategy').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var isArchive = $(this).hasClass('archive-strategy');
        
        var message = isArchive ? 
            'Strategy has been archived. It will not appear in the main list unless "Show Archived" is selected.' : 
            'Strategy has been restored from archive.';
            
        alert(message + ' (Demo only - no actual change occurred)');
    });
    
    // Built-in strategies table row expansion
    $('.strategy-row').on('click', function() {
        var strategyName = $(this).data('strategy');
        var detailsRow = $('#details-' + strategyName);
        
        // Toggle the details row
        detailsRow.toggle();
        
        // Toggle active state on the clicked row
        $(this).toggleClass('expanded');
    });
    
    // Prevent details row from closing when clicking inside it
    $('.strategy-details').on('click', function(e) {
        e.stopPropagation();
    });
});