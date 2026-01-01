/**
 * TradePress Features Management JavaScript
 */

// Set flag to indicate script has loaded
window.featuresScriptLoaded = true;

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('TradePress features.js loaded successfully');
    
    // Hide all content sections initially
    $('.feature-page-content').hide();
    
    // Toggle section when header is clicked
    $('.feature-page-header').on('click', function() {
        $(this).next('.feature-page-content').slideToggle();
        $(this).find('.toggle-section').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
    });
    
    // Expand all sections
    $('.expand-all').on('click', function(e) {
        e.preventDefault();
        $('.feature-page-content').slideDown();
        $('.toggle-section').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
        console.log('Expand all clicked');
    });
    
    // Collapse all sections
    $('.collapse-all').on('click', function(e) {
        e.preventDefault();
        $('.feature-page-content').slideUp();
        $('.toggle-section').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
        console.log('Collapse all clicked');
    });
    
    // Filter by status
    $('#feature-status-filter').on('change', function() {
        var status = $(this).val();
        if (status === 'all') {
            $('.feature-row').show();
        } else {
            $('.feature-row').hide();
            $('.feature-row.' + status + '-status').show();
        }
        console.log('Status filter changed to: ' + status);
    });
    
    // Update stats
    function updateStats() {
        var totalFeatures = $('.feature-row').length;
        var liveFeatures = $('.feature-row.live-status').length;
        var demoFeatures = $('.feature-row.demo-status').length;
        var completionRate = Math.round((liveFeatures / totalFeatures) * 100);
        
        $('#total-features').text(totalFeatures);
        $('#live-features').text(liveFeatures);
        $('#demo-features').text(demoFeatures);
        $('#completion-rate').text(completionRate + '%');
        
        console.log('Stats updated: ' + totalFeatures + ' total, ' + liveFeatures + ' live, ' + demoFeatures + ' demo');
    }
    
    // Initialize stats
    updateStats();
    
    // Update stats when mode changes
    $('.feature-mode select').on('change', function() {
        var row = $(this).closest('tr');
        row.removeClass('live-status demo-status');
        row.addClass($(this).val() + '-status');
        updateStats();
    });
});
