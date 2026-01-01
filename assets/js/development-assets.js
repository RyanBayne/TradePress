/**
 * Development Assets Tab JavaScript
 */

jQuery(document).ready(function($) {
    
    // Handle assets tab switching
    $('.assets-tab').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        
        // Update tab navigation
        $('.assets-tab').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');
        
        // Update tab content
        $('.assets-tab-content').removeClass('active');
        $('#' + targetTab).addClass('active');
    });
    
    // Handle refresh buttons
    $('#refresh-css-scan, #refresh-js-scan').on('click', function() {
        location.reload();
    });
    
    // Handle filter toggles
    $('#toggle-css-issues, #toggle-js-issues').on('click', function() {
        var isCSS = $(this).attr('id').includes('css');
        var tableId = isCSS ? '#assets-css-table' : '#assets-js-table';
        var $table = $(tableId);
        
        if ($(this).hasClass('showing-issues-only')) {
            // Show all rows
            $table.find('tr').show();
            $(this).removeClass('showing-issues-only').text($(this).text().replace('Show All', 'Show Issues Only'));
        } else {
            // Show only error rows
            $table.find('tr').hide();
            $table.find('tr[data-status="error"], thead tr').show();
            $(this).addClass('showing-issues-only').text($(this).text().replace('Show Issues Only', 'Show All'));
        }
    });
    
    // Handle filter checkboxes
    $('[id^="filter-"]').on('change', function() {
        var filterType = $(this).attr('id');
        var isChecked = $(this).is(':checked');
        var tableType = filterType.includes('css') ? 'css' : 'js';
        var $table = $('#assets-' + tableType + '-table');
        
        if (filterType.includes('available')) {
            $table.find('tr[data-status="success"]').toggle(isChecked);
        } else if (filterType.includes('missing')) {
            $table.find('tr[data-status="error"]').toggle(isChecked);
        } else if (filterType.includes('issues-only')) {
            if (isChecked) {
                $table.find('tr[data-status="success"]').hide();
            } else {
                $table.find('tr[data-status="success"]').show();
            }
        }
    });
    
});