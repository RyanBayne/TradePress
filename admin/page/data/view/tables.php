<?php
/**
 * TradePress Data Tables Tab
 *
 * Displays database tables with their update processes
 *
 * @package TradePress
 * @subpackage admin/page/DataTabs
 * @version 1.0.5
 * @since 1.0.0
 * @created 2025-04-26 21:30:00
 * 
 * Required CSS: admin-database.css
 * Required JS: js/tradepress-tables-tab.js
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue the configure-directives CSS for consistent layout
wp_enqueue_style('tradepress-configure-directives', TRADEPRESS_PLUGIN_URL . 'assets/css/pages/configure-directives.css', array(), TRADEPRESS_VERSION);

/**
 * Get the latest record from a table
 */
function tradepress_get_latest_table_record($table_name) {
    global $wpdb;
    
    // Get the latest record (assuming most tables have an ID or timestamp column)
    $record = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM `%s` ORDER BY id DESC LIMIT 1",
            $table_name
        ),
        ARRAY_A
    );
    
    // If no ID column, try common timestamp columns
    if (!$record) {
        $timestamp_columns = ['created_at', 'updated_at', 'date_created', 'timestamp'];
        foreach ($timestamp_columns as $col) {
            $record = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM `%s` ORDER BY `%s` DESC LIMIT 1",
                    $table_name,
                    $col
                ),
                ARRAY_A
            );
            if ($record) break;
        }
    }
    
    return $record;
}

/**
 * Display record fields in a readable format
 */
function tradepress_display_record_fields($record) {
    if (!$record) return;
    
    echo '<div class="record-fields">';
    foreach ($record as $field => $value) {
        // Skip empty values
        if ($value === null || $value === '') continue;
        
        // Truncate long values
        $display_value = strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
        
        // Format field name
        $field_label = ucwords(str_replace(['_', '-'], ' ', $field));
        
        echo '<div class="record-field">';
        echo '<strong>' . esc_html($field_label) . ':</strong> ';
        echo '<span>' . esc_html($display_value) . '</span>';
        echo '</div>';
    }
    echo '</div>';
}

/**
 * Display the Data Tables tab content
 */
function tradepress_data_tables_tab_content() {
    global $wpdb;
    
    // Get TradePress tables
    $tables_result = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}tradepress%'");
    $tables = array();
    
    // Check if we have any tables
    if (!empty($tables_result)) {
        foreach ($tables_result as $table_row) {
            foreach ($table_row as $table_name) {
                $tables[] = $table_name;
            }
        }
    }
    
    // Get table data from actual database
    $table_data = array();
    
    if (!empty($tables)) {
        foreach ($tables as $table_name) {
            // Get table information
            $rows = tradepress_get_table_row_count($table_name);
            $size = tradepress_get_table_size($table_name);
            $last_updated = tradepress_get_table_last_updated($table_name);
            $status = tradepress_get_table_status($table_name);
            $description = tradepress_get_table_description($table_name);
            
            // Get processes associated with this table
            $processes = tradepress_get_table_processes($table_name);
            
            $table_data[] = array(
                'name' => $table_name,
                'description' => $description,
                'rows' => $rows,
                'size' => $size,
                'last_updated' => $last_updated,
                'status' => $status,
                'processes' => $processes
            );
        }
        
        // Handle sorting
        $sort_by = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'last_updated';
        $sort_order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'desc';
        
        usort($table_data, function($a, $b) use ($sort_by, $sort_order) {
            $aVal = $bVal = '';
            
            switch ($sort_by) {
                case 'name':
                    $aVal = str_replace($GLOBALS['wpdb']->prefix . 'tradepress_', '', $a['name']);
                    $bVal = str_replace($GLOBALS['wpdb']->prefix . 'tradepress_', '', $b['name']);
                    break;
                case 'status':
                    $aVal = $a['status'];
                    $bVal = $b['status'];
                    break;
                case 'last_updated':
                default:
                    if ($a['last_updated'] === 'N/A' && $b['last_updated'] === 'N/A') return 0;
                    if ($a['last_updated'] === 'N/A') return 1;
                    if ($b['last_updated'] === 'N/A') return -1;
                    return $sort_order === 'desc' ? 
                        strtotime($b['last_updated']) - strtotime($a['last_updated']) :
                        strtotime($a['last_updated']) - strtotime($b['last_updated']);
            }
            
            $result = strcasecmp($aVal, $bVal);
            return $sort_order === 'desc' ? -$result : $result;
        });
    }
    
    // Store table data in a global variable for use in the JS file
    $GLOBALS['tradepress_table_data'] = $table_data;
    
    ?>
    <div class="configure-directives-container">
        <div class="directives-layout">
            <!-- Left Column: Tables List -->
            <div class="directives-table-container">
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <input type="search" id="table-search-input" name="s" value="<?php echo esc_attr(isset($_GET['s']) ? $_GET['s'] : ''); ?>" placeholder="<?php esc_attr_e('Search tables...', 'tradepress'); ?>">
                        <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Tables', 'tradepress'); ?>">
                        <button type="button" class="button action-refresh"><span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh', 'tradepress'); ?></button>
                    </div>
                </div>
                
                <?php if (empty($tables)): ?>
                    <div class="tradepress-notice notice-error">
                        <p><?php esc_html_e('No TradePress tables found in the database. The plugin may not be properly installed or initialized.', 'tradepress'); ?></p>
                        <p><?php esc_html_e('To install missing tables, visit the Database tab in the TradePress Settings.', 'tradepress'); ?></p>
                    </div>
                <?php elseif (empty($table_data)): ?>
                    <div class="tradepress-notice notice-warning">
                        <p><?php esc_html_e('Could not retrieve table information. Please refresh or check database permissions.', 'tradepress'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="wp-list-table widefat fixed striped">
                        <?php 
                        $current_orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'last_updated';
                        $current_order = isset($_GET['order']) ? $_GET['order'] : 'desc';
                        ?>
                        <div class="table-header" style="display: flex; background: #f1f1f1; padding: 12px 15px; font-weight: 600; border-bottom: 1px solid #c3c4c7;">
                            <div style="flex: 2;" class="sortable-column">
                                <a href="<?php echo esc_url(add_query_arg(array('orderby' => 'name', 'order' => ($current_orderby === 'name' && $current_order === 'asc') ? 'desc' : 'asc'))); ?>" class="<?php echo $current_orderby === 'name' ? 'sorted' : 'sortable'; ?> <?php echo $current_orderby === 'name' ? $current_order : ''; ?>">
                                    <?php _e('Table Name', 'tradepress'); ?>
                                </a>
                            </div>
                            <div style="flex: 1;" class="sortable-column">
                                <a href="<?php echo esc_url(add_query_arg(array('orderby' => 'status', 'order' => ($current_orderby === 'status' && $current_order === 'asc') ? 'desc' : 'asc'))); ?>" class="<?php echo $current_orderby === 'status' ? 'sorted' : 'sortable'; ?> <?php echo $current_orderby === 'status' ? $current_order : ''; ?>">
                                    <?php _e('Status', 'tradepress'); ?>
                                </a>
                            </div>
                            <div style="flex: 1;"><?php _e('Rows', 'tradepress'); ?></div>
                            <div style="flex: 1;"><?php _e('Size', 'tradepress'); ?></div>
                            <div style="flex: 1;"><?php _e('Processes', 'tradepress'); ?></div>
                            <div style="flex: 1;">
                                <a href="<?php echo esc_url(add_query_arg(array('orderby' => 'last_updated', 'order' => ($current_orderby === 'last_updated' && $current_order === 'desc') ? 'asc' : 'desc'))); ?>" class="<?php echo $current_orderby === 'last_updated' ? 'sorted' : 'sortable'; ?> <?php echo $current_orderby === 'last_updated' ? $current_order : ''; ?>">
                                    <?php _e('Last Updated', 'tradepress'); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="tradepress-compact-table">
                        <?php foreach ($table_data as $table): 
                            $status_class = 'status-good';
                            if ($table['status'] === 'Empty') {
                                $status_class = 'status-warning';
                            } elseif ($table['status'] === 'Missing' || $table['status'] === 'Error') {
                                $status_class = 'status-error';
                            }
                            
                            // Count processes by status
                            $active_processes = 0;
                            $warning_processes = 0;
                            $error_processes = 0;
                            $not_implemented_processes = 0;
                            
                            foreach ($table['processes'] as $process) {
                                if ($process['status'] === 'Active') {
                                    $active_processes++;
                                } elseif ($process['status'] === 'Warning') {
                                    $warning_processes++;
                                } elseif ($process['status'] === 'Error' || $process['status'] === 'Inactive') {
                                    $error_processes++;
                                } elseif ($process['status'] === 'Not Implemented') {
                                    $not_implemented_processes++;
                                }
                            }
                            
                            $process_status_class = 'status-active';
                            if ($not_implemented_processes > 0) {
                                $process_status_class = 'status-inactive';
                            } elseif ($warning_processes > 0 || $error_processes > 0) {
                                $process_status_class = 'status-warning';
                            }
                        ?>
                            <div class="accordion-row">
                                <div class="accordion-header">
                                    <div style="flex: 2;">
                                        <strong><?php echo esc_html(str_replace($wpdb->prefix . 'tradepress_', '', $table['name'])); ?></strong>
                                    </div>
                                    <div style="flex: 1;">
                                        <span class="status-badge <?php echo $status_class === 'status-good' ? 'status-active' : $status_class; ?>">
                                            <?php echo esc_html($table['status']); ?>
                                        </span>
                                    </div>
                                    <div style="flex: 1;"><?php echo number_format($table['rows']); ?></div>
                                    <div style="flex: 1;"><?php echo esc_html($table['size']); ?></div>
                                    <div style="flex: 1;">
                                        <span class="status-badge <?php echo $process_status_class; ?>">
                                            <?php echo count($table['processes']); ?>
                                        </span>
                                    </div>
                                    <div style="flex: 1;">
                                        <?php 
                                        if ($table['last_updated'] === 'N/A') {
                                            echo '<span style="color: #666;">' . esc_html__('Never', 'tradepress') . '</span>';
                                        } else {
                                            echo esc_html(human_time_diff(strtotime($table['last_updated']), current_time('timestamp')) . ' ago');
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="accordion-content">
                                    <div class="directive-meta">
                                        <div>
                                            <strong>Description:</strong><br>
                                            <?php echo esc_html($table['description']); ?>
                                        </div>
                                        <div>
                                            <strong>Full Name:</strong><br>
                                            <?php echo esc_html($table['name']); ?>
                                        </div>
                                        <div>
                                            <strong>Process Status:</strong><br>
                                            <?php if ($not_implemented_processes > 0): ?>
                                                <?php echo $not_implemented_processes; ?> not implemented
                                            <?php elseif ($warning_processes > 0 || $error_processes > 0): ?>
                                                <?php if ($warning_processes > 0): ?>
                                                    <?php echo $warning_processes; ?> warnings
                                                <?php endif; ?>
                                                <?php if ($error_processes > 0): ?>
                                                    <?php echo $error_processes; ?> errors
                                                <?php endif; ?>
                                            <?php else: ?>
                                                All processes active
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="directive-actions">
                                        <a href="<?php echo esc_url(add_query_arg('manage', str_replace($wpdb->prefix . 'tradepress_', '', $table['name']))); ?>" class="button button-primary">
                                            <?php esc_html_e('Manage', 'tradepress'); ?>
                                        </a>
                                        <button type="button" class="button view-processes" data-table="<?php echo esc_attr($table['name']); ?>">
                                            <?php esc_html_e('View Processes', 'tradepress'); ?>
                                        </button>
                                        <button type="button" class="button view-data" data-table="<?php echo esc_attr($table['name']); ?>">
                                            <?php esc_html_e('View Data', 'tradepress'); ?>
                                        </button>
                                        <button type="button" class="button table-optimize" data-table="<?php echo esc_attr($table['name']); ?>">
                                            <?php esc_html_e('Optimize', 'tradepress'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column: Table Management -->
            <div class="directive-right-column">
                <!-- Table Details Container -->
                <div class="directive-details-container">
                <?php 
                $manage_table = isset($_GET['manage']) ? sanitize_text_field($_GET['manage']) : '';
                $selected_table_data = null;
                
                if ($manage_table && !empty($table_data)) {
                    foreach ($table_data as $table) {
                        if (str_replace($wpdb->prefix . 'tradepress_', '', $table['name']) === $manage_table) {
                            $selected_table_data = $table;
                            break;
                        }
                    }
                }
                
                if (!$selected_table_data && !empty($table_data)) {
                    $selected_table_data = $table_data[0];
                    $manage_table = str_replace($wpdb->prefix . 'tradepress_', '', $selected_table_data['name']);
                }
                ?>
                
                <?php if ($selected_table_data): ?>
                    <div class="directive-section">
                        <div class="section-header">
                            <h3><?php esc_html_e('Latest Record', 'tradepress'); ?></h3>
                        </div>
                        
                        <div class="section-content">
                            <div class="directive-description">
                                <p><?php esc_html_e('Most recent record from this table:', 'tradepress'); ?></p>
                            </div>
                            
                            <?php 
                            // Get latest record from the selected table
                            $latest_record = tradepress_get_latest_table_record($selected_table_data['name']);
                            if ($latest_record): ?>
                                <div class="latest-record-display">
                                    <?php tradepress_display_record_fields($latest_record); ?>
                                </div>
                            <?php else: ?>
                                <div class="no-data-message">
                                    <p style="color: #666; font-style: italic;"><?php esc_html_e('No records found in this table.', 'tradepress'); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="directive-actions">
                                <button type="button" class="button button-primary view-data" data-table="<?php echo esc_attr($selected_table_data['name']); ?>">
                                    <?php esc_html_e('View All Records', 'tradepress'); ?>
                                </button>
                                <button type="button" class="button table-optimize" data-table="<?php echo esc_attr($selected_table_data['name']); ?>">
                                    <?php esc_html_e('Optimize Table', 'tradepress'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="directive-section">
                        <div class="section-header">
                            <h3><?php esc_html_e('No Table Selected', 'tradepress'); ?></h3>
                        </div>
                        <div class="section-content">
                            <p><?php esc_html_e('Select a table from the list to manage its settings and view details.', 'tradepress'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                </div>
                
                <!-- Processes Container -->
                <?php if ($selected_table_data && !empty($selected_table_data['processes'])): ?>
                <div class="directive-details-container">
                    <div class="directive-section">
                        <div class="section-header">
                            <h3><?php esc_html_e('Data Processes', 'tradepress'); ?></h3>
                        </div>
                        
                        <div class="section-content">
                            <div class="directive-description">
                                <p><?php esc_html_e('Automated processes that populate and maintain this table.', 'tradepress'); ?></p>
                            </div>
                            
                            <?php foreach ($selected_table_data['processes'] as $process): ?>
                                <div class="setting-group">
                                    <label><?php echo esc_html($process['name']); ?>:</label>
                                    <div class="setting-control">
                                        <span class="status-badge <?php echo $process['status'] === 'Active' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo esc_html($process['status']); ?>
                                        </span>
                                        <?php if (!empty($process['description'])): ?>
                                            <p class="setting-description"><?php echo esc_html($process['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="directive-actions">
                                <button type="button" class="button view-processes" data-table="<?php echo esc_attr($selected_table_data['name']); ?>">
                                    <?php esc_html_e('Manage Processes', 'tradepress'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
        
        <!-- Process Details Modal -->
        <div id="process-details-modal" class="tradepress-modal" style="display: none;">
            <div class="tradepress-modal-content">
                <div class="modal-header">
                    <h2 id="modal-table-name"></h2>
                    <span class="modal-close">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="process-list"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button button-primary add-process-button"><?php esc_html_e('Add Process', 'tradepress'); ?></button>
                    <button type="button" class="button modal-close-button"><?php esc_html_e('Close', 'tradepress'); ?></button>
                </div>
            </div>
        </div>
        
        <!-- Table Data Modal -->
        <div id="table-data-modal" class="tradepress-modal" style="display: none;">
            <div class="tradepress-modal-content">
                <div class="modal-header">
                    <h2 id="data-modal-table-name"></h2>
                    <span class="modal-close">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="data-filter-controls">
                        <div class="filter-row">
                            <input type="text" id="data-search" placeholder="<?php esc_attr_e('Search...', 'tradepress'); ?>" class="regular-text">
                            <select id="data-limit">
                                <option value="10">10 <?php esc_html_e('records', 'tradepress'); ?></option>
                                <option value="25">25 <?php esc_html_e('records', 'tradepress'); ?></option>
                                <option value="50">50 <?php esc_html_e('records', 'tradepress'); ?></option>
                                <option value="100">100 <?php esc_html_e('records', 'tradepress'); ?></option>
                            </select>
                            <button type="button" class="button filter-apply"><?php esc_html_e('Apply', 'tradepress'); ?></button>
                        </div>
                    </div>
                    <div id="table-data-preview"></div>
                    <div class="data-pagination">
                        <button type="button" class="button button-small pagination-prev" disabled><?php esc_html_e('Previous', 'tradepress'); ?></button>
                        <span class="pagination-info"><?php esc_html_e('Page', 'tradepress'); ?> <span class="current-page">1</span> <?php esc_html_e('of', 'tradepress'); ?> <span class="total-pages">1</span></span>
                        <button type="button" class="button button-small pagination-next" disabled><?php esc_html_e('Next', 'tradepress'); ?></button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button export-csv-button"><?php esc_html_e('Export CSV', 'tradepress'); ?></button>
                    <button type="button" class="button modal-close-button"><?php esc_html_e('Close', 'tradepress'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .latest-record-display {
        background: #f9f9f9;
        border: 1px solid #e1e1e1;
        border-radius: 4px;
        padding: 15px;
        margin: 15px 0;
    }
    
    .record-fields {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .record-field {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 4px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .record-field:last-child {
        border-bottom: none;
    }
    
    .record-field strong {
        min-width: 120px;
        color: #0073aa;
        font-size: 13px;
    }
    
    .record-field span {
        flex: 1;
        font-size: 13px;
        word-break: break-word;
    }
    
    .no-data-message {
        text-align: center;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 4px;
        margin: 15px 0;
    }
    
    .sortable-column a {
        text-decoration: none;
        color: inherit;
        display: block;
        padding: 4px 8px;
        border-radius: 3px;
    }
    
    .sortable-column a:hover {
        background: rgba(0,0,0,0.05);
    }
    
    .sortable-column a.sortable:after {
        content: '\f142';
        font-family: dashicons;
        opacity: 0.3;
        margin-left: 5px;
    }
    
    .sortable-column a.sorted.asc:after {
        content: '\f142';
        font-family: dashicons;
        opacity: 1;
    }
    
    .sortable-column a.sorted.desc:after {
        content: '\f140';
        font-family: dashicons;
        opacity: 1;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Accordion functionality with URL update
        $('.accordion-header').on('click', function() {
            var $content = $(this).next('.accordion-content');
            var isActive = $content.hasClass('active');
            
            // Extract table key from manage link
            var manageLink = $(this).closest('.accordion-row').find('a[href*="manage="]');
            var tableKey = '';
            if (manageLink.length > 0) {
                var href = manageLink.attr('href');
                var match = href.match(/manage=([^&]+)/);
                if (match) {
                    tableKey = match[1];
                }
            }
            
            // Close all accordions
            $('.accordion-content').removeClass('active').slideUp();
            $('.accordion-header').removeClass('active');
            
            // Open clicked accordion if it wasn't active and update URL
            if (!isActive && tableKey) {
                $content.addClass('active').slideDown();
                $(this).addClass('active');
                
                // Update URL with manage parameter
                var url = new URL(window.location);
                url.searchParams.set('manage', tableKey);
                window.location.href = url.toString();
            }
        });
        
        // Check for selected table from URL and open accordion
        var urlParams = new URLSearchParams(window.location.search);
        var selectedTable = urlParams.get('manage');
        
        if (selectedTable) {
            $('.accordion-row').each(function() {
                var manageLink = $(this).find('a[href*="manage=' + selectedTable + '"]');
                if (manageLink.length > 0) {
                    $(this).find('.accordion-content').addClass('active').show();
                    $(this).find('.accordion-header').addClass('active');
                }
            });
        }
        
        // Search functionality
        $('#table-search-input').on('keyup', function(e) {
            if (e.keyCode === 13) { // Enter key
                performSearch();
            }
        });
        
        $('#search-submit').on('click', function(e) {
            e.preventDefault();
            performSearch();
        });
        
        function performSearch() {
            var searchTerm = $('#table-search-input').val();
            var url = new URL(window.location);
            if (searchTerm) {
                url.searchParams.set('s', searchTerm);
            } else {
                url.searchParams.delete('s');
            }
            window.location.href = url.toString();
        }
        
        // Refresh button
        $('.action-refresh').on('click', function() {
            window.location.reload();
        });
        
        // View Data button functionality (existing)
        $('.view-data').on('click', function() {
            var tableName = $(this).data('table');
            // Trigger existing modal functionality
            // This will use the existing modal code
        });
        
        // View Processes button functionality (existing)
        $('.view-processes').on('click', function() {
            var tableName = $(this).data('table');
            // Trigger existing modal functionality
        });
        
        // Optimize button functionality (existing)
        $('.table-optimize').on('click', function() {
            var tableName = $(this).data('table');
            // Trigger existing optimization functionality
        });
        

    });
    </script>
    
    <?php
}
