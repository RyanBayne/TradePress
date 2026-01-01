/**
 * Sandbox Database Explorer JS
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Handle explore table button
    $('.explore-table-button').on('click', function() {
        var table = $('#table-select').val();
        var $tableInfo = $('#table-info');
        
        if (!table) {
            alert('Please select a table');
            return;
        }
        
        // Show loading state
        $tableInfo.show().html('Loading table information...');
        
        // In a real implementation, this would make an AJAX call to get the table info
        // For now, just show some example output
        setTimeout(function() {
            $tableInfo.html(
                '<h3>Table: ' + table + '</h3>' +
                '<p>This is a placeholder for table information that would come from an AJAX call.</p>' +
                '<table>' +
                '<thead><tr><th>Column</th><th>Type</th><th>Key</th></tr></thead>' +
                '<tbody>' +
                '<tr><td>id</td><td>int(11)</td><td>PRI</td></tr>' +
                '<tr><td>title</td><td>varchar(255)</td><td></td></tr>' +
                '<tr><td>content</td><td>longtext</td><td></td></tr>' +
                '</tbody>' +
                '</table>'
            );
        }, 500);
    });
});
