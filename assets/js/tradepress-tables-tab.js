/**
 * TradePress Tables Tab JavaScript
 *
 * Handles all interactive functionality for the database tables tab
 *
 * @package TradePress
 * @subpackage Admin/JS
 * @version 1.0.2
 * @since 1.0.0
 * @created 2025-05-12 14:30:00
 */

(function($) {
    'use strict';

    // Tables Tab functionality
    var TradePressTables = {
        // Store localized data for access throughout the object
        data: window.tradePressTables || {},

        /**
         * Initialize the tables tab functionality
         */
        init: function() {
            // Store reference to this object
            var self = this;
            
            // Log initialization for debugging
            console.log('TradePress Tables Tab initialized', self.data);
            
            // Debug the table data structure to help diagnose lookup issues
            if (self.data && self.data.tableData) {
                console.log('Table Data Structure:', self.data.tableData);
            } else {
                console.warn('No table data available in the localized script data');
            }
            
            // Set up event listeners
            self.initEventListeners();
            
            // Add rotating animation class for loading indicators
            $('<style>')
                .prop('type', 'text/css')
                .html('.rotating { animation: spin 1.5s linear infinite; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }')
                .appendTo('head');
        },
        
        /**
         * Initialize all event listeners
         */
        initEventListeners: function() {
            var self = this;
            
            // View processes button click
            $('.view-processes').on('click', function() {
                var tableName = $(this).closest('.table-card').data('table');
                console.log('View processes clicked for table:', tableName);
                self.showProcessDetails(tableName);
            });
            
            // View data button click
            $('.view-data').on('click', function() {
                var tableName = $(this).closest('.table-card').data('table');
                console.log('View data clicked for table:', tableName);
                self.showTableDataPreview(tableName);
            });
            
            // Optimize table button click
            $('.table-optimize').on('click', function() {
                var tableName = $(this).closest('.table-card').data('table');
                self.optimizeTable(tableName, $(this));
            });
            
            // Close modal when clicking the close button or outside the modal
            $('.modal-close, .modal-close-button').on('click', function() {
                $('.tradepress-modal').hide();
            });
            
            $(window).on('click', function(event) {
                if ($(event.target).hasClass('tradepress-modal')) {
                    $('.tradepress-modal').hide();
                }
            });
            
            // Global action buttons
            $('.action-refresh').on('click', function() {
                self.refreshData($(this));
            });
            
            $('.action-optimize').on('click', function() {
                self.optimizeAllTables($(this));
            });
            
            $('.action-add-process').on('click', function() {
                self.addProcess();
            });
            
            // Process action button clicks (in modal)
            $(document).on('click', '.process-pause-button', function() {
                alert('Pausing process...\n\nIn a full implementation, this would pause the selected data process.');
            });
            
            $(document).on('click', '.process-resume-button', function() {
                alert('Resuming process...\n\nIn a full implementation, this would resume the selected data process.');
            });
            
            $(document).on('click', '.process-run-now-button', function() {
                alert('Running process now...\n\nIn a full implementation, this would trigger an immediate run of the selected data process.');
            });
            
            $(document).on('click', '.process-edit-button', function() {
                alert('Edit Process functionality would open a form to edit the selected data process.');
            });
            
            $(document).on('click', '.process-delete-button', function() {
                alert('Are you sure you want to delete this process?\n\nIn a full implementation, this would delete the selected data process after confirmation.');
            });
            
            // Add process button click
            $('.add-process-button').on('click', function() {
                self.addProcess();
            });
            
            // Table data modal actions
            $('.filter-apply').on('click', function() {
                alert('Applying filters...\n\nIn a full implementation, this would filter the table data based on search terms and other criteria.');
            });
            
            $('.pagination-prev').on('click', function() {
                alert('Previous page...\n\nIn a full implementation, this would load the previous page of table data.');
            });
            
            $('.pagination-next').on('click', function() {
                alert('Next page...\n\nIn a full implementation, this would load the next page of table data.');
            });
            
            $('.export-csv-button').on('click', function() {
                alert('Exporting CSV...\n\nIn a full implementation, this would export the current table data to a CSV file.');
            });
        },
        
        /**
         * Show process details modal
         * 
         * @param {string} tableName The table name
         */
        showProcessDetails: function(tableName) {
            // Use this.data instead of global tradePressTables
            var tableData = this.data.tableData || [];
            
            // Debug the lookup
            console.log('Looking for table:', tableName);
            console.log('Available tables:', tableData.map(function(t) { return t.name; }));
            
            // Check if tableData is empty and create a fallback object if needed
            if (!tableData || tableData.length === 0) {
                console.warn('No table data available. Creating fallback data for display purposes.');
                
                // Create a fallback table object with minimal data for display
                var fallbackTable = {
                    name: tableName,
                    description: 'Table data not available - please check database connection',
                    rows: 0,
                    size: 'Unknown',
                    last_updated: 'N/A',
                    status: 'Unknown',
                    processes: [{
                        name: 'Data Loading',
                        type: 'System',
                        frequency: 'N/A',
                        last_run: 'N/A',
                        status: 'Error',
                        description: 'Unable to load data from server. This could be due to the table not existing or a data loading issue.'
                    }]
                };
                
                // Use the fallback table
                var table = fallbackTable;
                console.log('Using fallback table data:', table);
            } else {
                // Try to find the table in the data
                var table = tableData.find(function(t) { return t.name === tableName; });
                
                if (!table) {
                    console.error('Table data not found for:', tableName);
                    
                    // Try a more flexible search as a fallback
                    var tableNameWithoutPrefix = tableName.replace(/^wp_/, '');
                    console.log('Trying without prefix:', tableNameWithoutPrefix);
                    
                    table = tableData.find(function(t) { 
                        return t.name === tableNameWithoutPrefix || 
                               t.name.endsWith(tableNameWithoutPrefix);
                    });
                    
                    if (table) {
                        console.log('Found table with flexible search:', table.name);
                    } else {
                        // Still not found - create a fallback object for this specific table
                        table = {
                            name: tableName,
                            description: 'Table not found - may not exist in database',
                            rows: 0,
                            size: 'Unknown',
                            last_updated: 'N/A',
                            status: 'Missing',
                            processes: [{
                                name: 'Table Creation',
                                type: 'System',
                                frequency: 'One-time',
                                last_run: 'Never',
                                status: 'Error',
                                description: 'This table does not appear to exist in the database or could not be found.'
                            }]
                        };
                        console.log('Created fallback data for missing table:', tableName);
                    }
                }
            }
            
            // Continue with rendering the modal
            $('#modal-table-name').text(tableName.replace(this.data.wpdbPrefix || '', ''));
            
            var processesHtml = '';
            if (table.processes && table.processes.length > 0) {
                table.processes.forEach(function(process) {
                    var statusClass = 'status-badge-active';
                    if (process.status === 'Warning') {
                        statusClass = 'status-badge-warning';
                    } else if (process.status === 'Error' || process.status === 'Inactive') {
                        statusClass = 'status-badge-error';
                    } else if (process.status === 'Not Implemented') {
                        statusClass = 'status-badge-not-implemented';
                    }
                    
                    processesHtml += '<div class="process-list-item">';
                    processesHtml += '<div class="process-header">';
                    processesHtml += '<span class="process-name">' + process.name + '</span>';
                    processesHtml += '<span class="process-status-badge ' + statusClass + '">' + process.status + '</span>';
                    processesHtml += '</div>';
                    
                    processesHtml += '<div class="process-details">';
                    processesHtml += '<div class="process-detail-item"><span class="process-detail-label">Type</span><span class="process-detail-value">' + process.type + '</span></div>';
                    processesHtml += '<div class="process-detail-item"><span class="process-detail-label">Frequency</span><span class="process-detail-value">' + process.frequency + '</span></div>';
                    processesHtml += '<div class="process-detail-item"><span class="process-detail-label">Last Run</span><span class="process-detail-value">' + process.last_run + '</span></div>';
                    processesHtml += '</div>';
                    
                    processesHtml += '<div class="process-description">' + process.description + '</div>';
                    
                    if (process.status !== 'Not Implemented') {
                        processesHtml += '<div class="process-actions">';
                        if (process.status === 'Active') {
                            processesHtml += '<button type="button" class="button button-small process-pause-button"><span class="dashicons dashicons-controls-pause"></span> Pause</button>';
                        } else {
                            processesHtml += '<button type="button" class="button button-small process-resume-button"><span class="dashicons dashicons-controls-play"></span> Resume</button>';
                        }
                        processesHtml += '<button type="button" class="button button-small process-run-now-button"><span class="dashicons dashicons-update"></span> Run Now</button>';
                        processesHtml += '<button type="button" class="button button-small process-edit-button"><span class="dashicons dashicons-edit"></span> Edit</button>';
                        processesHtml += '<button type="button" class="button button-small process-delete-button"><span class="dashicons dashicons-trash"></span> Delete</button>';
                        processesHtml += '</div>';
                    }
                    
                    processesHtml += '</div>';
                });
            } else {
                processesHtml = '<div class="no-processes-message"><p>No data processes found for this table. Add a process to keep this table updated with fresh data.</p></div>';
            }
            
            $('#process-list').html(processesHtml);
            $('#process-details-modal').show();
        },
        
        /**
         * Show table data preview modal
         * 
         * @param {string} tableName The table name
         */
        showTableDataPreview: function(tableName) {
            // Use this.data instead of global tradePressTables
            var tableData = this.data.tableData || [];
            
            // Debug the lookup
            console.log('Looking for table data preview:', tableName);
            console.log('Available tables:', tableData.map(function(t) { return t.name; }));
            
            // Check if tableData is empty and create a fallback object if needed
            if (!tableData || tableData.length === 0) {
                console.warn('No table data available. Creating fallback data for display purposes.');
                
                // Create a fallback table object with minimal data for display
                var fallbackTable = {
                    name: tableName,
                    description: 'Table data not available - please check database connection',
                    rows: 0,
                    size: 'Unknown',
                    last_updated: 'N/A',
                    status: 'Unknown'
                };
                
                // Use the fallback table
                var table = fallbackTable;
                console.log('Using fallback table data:', table);
            } else {
                // Try to find the table in the data
                var table = tableData.find(function(t) { return t.name === tableName; });
                
                if (!table) {
                    console.error('Table data not found for:', tableName);
                    
                    // Try a more flexible search as a fallback
                    var tableNameWithoutPrefix = tableName.replace(/^wp_/, '');
                    console.log('Trying without prefix:', tableNameWithoutPrefix);
                    
                    table = tableData.find(function(t) { 
                        return t.name === tableNameWithoutPrefix || 
                               t.name.endsWith(tableNameWithoutPrefix);
                    });
                    
                    if (table) {
                        console.log('Found table with flexible search:', table.name);
                    } else {
                        // Still not found - create a fallback object for this specific table
                        table = {
                            name: tableName,
                            description: 'Table not found - may not exist in database',
                            rows: 0,
                            size: 'Unknown',
                            last_updated: 'N/A',
                            status: 'Missing'
                        };
                        console.log('Created fallback data for missing table:', tableName);
                    }
                }
            }
            
            $('#data-modal-table-name').text(tableName.replace(this.data.wpdbPrefix || '', ''));
            
            // Display a message that this feature is being implemented
            var tableHtml = '<div class="feature-not-implemented">';
            tableHtml += '<h3>Feature Not Fully Implemented</h3>';
            tableHtml += '<p>The table data preview functionality is currently under development.</p>';
            tableHtml += '<p>When implemented, this feature will allow you to view and filter actual data from the "' + tableName.replace(this.data.wpdbPrefix || '', '') + '" table.</p>';
            tableHtml += '</div>';
            
            // Add a loading message to indicate we're trying to fetch data
            tableHtml += '<div class="loading-data">Attempting to fetch data from ' + tableName + '...</div>';
            
            $('#table-data-preview').html(tableHtml);
            
            // Make an AJAX call to get actual table data
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tradepress_get_table_data',
                    security: this.data.nonces.dataOperations,
                    table: tableName,
                    limit: 10,
                    offset: 0
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Build table from actual data
                        var tableHtml = '<table>';
                        
                        // Add column headers
                        if (response.data.columns && response.data.columns.length > 0) {
                            tableHtml += '<thead><tr>';
                            response.data.columns.forEach(function(column) {
                                tableHtml += '<th>' + column + '</th>';
                            });
                            tableHtml += '</tr></thead>';
                        }
                        
                        // Add rows
                        if (response.data.rows && response.data.rows.length > 0) {
                            tableHtml += '<tbody>';
                            response.data.rows.forEach(function(row) {
                                tableHtml += '<tr>';
                                response.data.columns.forEach(function(column) {
                                    tableHtml += '<td>' + (row[column] !== undefined ? row[column] : '') + '</td>';
                                });
                                tableHtml += '</tr>';
                            });
                            tableHtml += '</tbody>';
                        } else {
                            tableHtml += '<tbody><tr><td colspan="' + (response.data.columns ? response.data.columns.length : 1) + '">No data found in table.</td></tr></tbody>';
                        }
                        
                        tableHtml += '</table>';
                        
                        // Update with actual data
                        $('.loading-data').remove();
                        $('#table-data-preview').append(tableHtml);
                        
                        // Update pagination info
                        $('.current-page').text('1');
                        $('.total-pages').text(Math.ceil(response.data.total / 10) || 1);
                        
                        // Enable/disable pagination buttons
                        if (response.data.total > 10) {
                            $('.pagination-next').prop('disabled', false);
                        } else {
                            $('.pagination-next').prop('disabled', true);
                        }
                    } else {
                        // Error fetching data
                        $('.loading-data').html('<div class="notice notice-error"><p>Error fetching table data: ' + (response.data || 'Unknown error') + '</p></div>');
                    }
                },
                error: function() {
                    // Error making AJAX request
                    $('.loading-data').html('<div class="notice notice-error"><p>Error connecting to server. Please try again.</p></div>');
                }
            });
            
            $('#table-data-modal').show();
        },
        
        /**
         * Optimize a specific table
         * 
         * @param {string} tableName The table name
         * @param {object} $button jQuery button object
         */
        optimizeTable: function(tableName, $button) {
            var self = this;
            
            // Show loading state
            var originalText = $button.html();
            $button.html('<span class="dashicons dashicons-update rotating"></span> Optimizing...');
            $button.prop('disabled', true);
            
            // Make AJAX call to optimize table
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tradepress_optimize_table',
                    security: self.data.nonces.dataOperations,
                    table: tableName
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        alert('Table optimized successfully.');
                    } else {
                        // Show error message
                        alert('Error optimizing table: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function() {
                    // Show error message
                    alert('Error connecting to server. Please try again.');
                },
                complete: function() {
                    // Restore button state
                    $button.html(originalText);
                    $button.prop('disabled', false);
                }
            });
        },
        
        /**
         * Refresh the data on the page
         * 
         * @param {object} $button jQuery button object
         */
        refreshData: function($button) {
            // Show loading state
            var originalText = $button.html();
            $button.html('<span class="dashicons dashicons-update rotating"></span> Refreshing...');
            $button.prop('disabled', true);
            
            // Reload the page
            location.reload();
        },
        
        /**
         * Optimize all tables
         * 
         * @param {object} $button jQuery button object
         */
        optimizeAllTables: function($button) {
            var self = this;
            
            if (confirm('Optimize all TradePress tables? This may take a while for large tables.')) {
                // Show loading state
                var originalText = $button.html();
                $button.html('<span class="dashicons dashicons-update rotating"></span> Optimizing...');
                $button.prop('disabled', true);
                
                // Make AJAX call to optimize all tables
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tradepress_optimize_all_tables',
                        security: self.data.nonces.dataOperations
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            alert('All tables optimized successfully.');
                            // Reload the page to show updated table information
                            location.reload();
                        } else {
                            // Show error message
                            alert('Error optimizing tables: ' + (response.data || 'Unknown error'));
                            // Restore button state
                            $button.html(originalText);
                            $button.prop('disabled', false);
                        }
                    },
                    error: function() {
                        // Show error message
                        alert('Error connecting to server. Please try again.');
                        // Restore button state
                        $button.html(originalText);
                        $button.prop('disabled', false);
                    }
                });
            }
        },
        
        /**
         * Add a new process
         */
        addProcess: function() {
            // Show a notification that this feature is not implemented yet
            alert('This feature is not fully implemented yet.\n\nIn the future, this will allow you to add new data processes to keep tables updated with fresh data.');
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        // Only initialize if we're on the tables tab
        if ($('.tradepress-data-tables-container').length) {
            TradePressTables.init();
        }
    });
    
})(jQuery);
