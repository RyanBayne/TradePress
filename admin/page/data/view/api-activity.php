<?php
/**
 * TradePress API Activity Debug Tab
 *
 * Displays API calls from the centralized logging system
 *
 * @package TradePress
 * @subpackage admin/page/debug
 * @version 1.0.0
 * @since 1.0.0
 * @created 2025-05-21
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the filter parameters
$service = isset($_GET['service']) ? sanitize_text_field($_GET['service']) : '';
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
$page = isset($_GET['api_page']) ? intval($_GET['api_page']) : 1;

// Calculate the offset
$offset = ($page - 1) * $limit;

// Get API call logs
$logs = TradePress_API_Logging::get_api_calls(array(
    'service' => $service,
    'status' => $status,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'limit' => $limit,
    'offset' => $offset,
));

// Get the total count for pagination
$total_count = TradePress_API_Logging::get_api_call_count(array(
    'service' => $service,
    'status' => $status,
    'date_from' => $date_from,
    'date_to' => $date_to,
));

// Calculate pagination
$total_pages = ceil($total_count / $limit);

// Get service statistics
$service_stats = TradePress_API_Logging::get_service_stats($date_from, $date_to);

// Get available services for the filter
global $wpdb;
$available_services = $wpdb->get_col("SELECT DISTINCT service FROM {$wpdb->prefix}tradepress_calls ORDER BY service");
?>

<div class="tradepress-debug-section">
    <!-- API Statistics Cards -->
    <div class="tradepress-stats-cards">
        <?php foreach ($service_stats as $stat): ?>
            <div class="stat-card">
                <h3><?php echo esc_html($stat['service']); ?></h3>
                <div class="stat-numbers">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo tradepress_number_format(isset($stat['total_calls']) ? $stat['total_calls'] : 0); ?></span>
                        <span class="stat-label"><?php _e('Calls', 'tradepress'); ?></span>
                    </div>
                    <div class="stat-item success">
                        <span class="stat-value"><?php echo tradepress_number_format(isset($stat['successful_calls']) ? $stat['successful_calls'] : 0); ?></span>
                        <span class="stat-label"><?php _e('Success', 'tradepress'); ?></span>
                    </div>
                    <div class="stat-item error">
                        <span class="stat-value"><?php echo tradepress_number_format(isset($stat['error_calls']) ? $stat['error_calls'] : 0); ?></span>
                        <span class="stat-label"><?php _e('Errors', 'tradepress'); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Filter Form -->
    <div class="tradepress-filter-form">
        <form method="get" action="">
            <input type="hidden" name="page" value="<?php echo isset($_GET['page']) ? esc_attr($_GET['page']) : ''; ?>">
            <input type="hidden" name="tab" value="<?php echo isset($_GET['tab']) ? esc_attr($_GET['tab']) : ''; ?>">
            
            <div class="filter-controls">
                <div class="filter-row">
                    <div class="filter-item">
                        <label for="service"><?php _e('Service:', 'tradepress'); ?></label>
                        <select name="service" id="service">
                            <option value=""><?php _e('All Services', 'tradepress'); ?></option>
                            <?php foreach ($available_services as $available_service): ?>
                                <option value="<?php echo esc_attr($available_service); ?>" <?php selected($service, $available_service); ?>>
                                    <?php echo esc_html($available_service); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label for="status"><?php _e('Status:', 'tradepress'); ?></label>
                        <select name="status" id="status">
                            <option value=""><?php _e('All Statuses', 'tradepress'); ?></option>
                            <option value="success" <?php selected($status, 'success'); ?>><?php _e('Success', 'tradepress'); ?></option>
                            <option value="error" <?php selected($status, 'error'); ?>><?php _e('Error', 'tradepress'); ?></option>
                            <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'tradepress'); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label for="date_from"><?php _e('From:', 'tradepress'); ?></label>
                        <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($date_from); ?>">
                    </div>
                    
                    <div class="filter-item">
                        <label for="date_to"><?php _e('To:', 'tradepress'); ?></label>
                        <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($date_to); ?>">
                    </div>
                    
                    <div class="filter-item">
                        <label for="limit"><?php _e('Show:', 'tradepress'); ?></label>
                        <select name="limit" id="limit">
                            <option value="25" <?php selected($limit, 25); ?>>25</option>
                            <option value="50" <?php selected($limit, 50); ?>>50</option>
                            <option value="100" <?php selected($limit, 100); ?>>100</option>
                            <option value="200" <?php selected($limit, 200); ?>>200</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="button"><?php _e('Apply Filters', 'tradepress'); ?></button>
                        <a href="?page=<?php echo isset($_GET['page']) ? esc_attr($_GET['page']) : ''; ?>&tab=<?php echo isset($_GET['tab']) ? esc_attr($_GET['tab']) : ''; ?>" class="button"><?php _e('Reset', 'tradepress'); ?></a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- API Calls Table -->
    <?php if (!empty($logs)): ?>
        <div class="tradepress-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'tradepress'); ?></th>
                        <th><?php _e('Service', 'tradepress'); ?></th>
                        <th><?php _e('Function/Endpoint', 'tradepress'); ?></th>
                        <th><?php _e('Type', 'tradepress'); ?></th>
                        <th><?php _e('Status', 'tradepress'); ?></th>
                        <th><?php _e('Time', 'tradepress'); ?></th>
                        <th><?php _e('Outcome', 'tradepress'); ?></th>
                        <th><?php _e('Actions', 'tradepress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr id="log-<?php echo esc_attr($log['entryid']); ?>">
                            <td><?php echo esc_html($log['entryid']); ?></td>
                            <td><?php echo esc_html($log['service']); ?></td>
                            <td><?php echo esc_html($log['function']); ?></td>
                            <td><?php echo esc_html($log['type']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr(strtolower($log['status'])); ?>">
                                    <?php echo esc_html($log['status']); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html(human_time_diff(strtotime($log['timestamp']), current_time('timestamp'))); ?> <?php _e('ago', 'tradepress'); ?></td>
                            <td><?php echo esc_html($log['outcome']); ?></td>
                            <td>
                                <button type="button" class="button button-small view-details" data-id="<?php echo esc_attr($log['entryid']); ?>">
                                    <?php _e('Details', 'tradepress'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="tradepress-pagination">
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf(_n('%s item', '%s items', $total_count, 'tradepress'), number_format_i18n($total_count)); ?>
                    </span>
                    
                    <span class="pagination-links">
                        <?php if ($page > 1): ?>
                            <a class="first-page button" href="<?php echo esc_url(add_query_arg('api_page', 1)); ?>">
                                <span class="screen-reader-text"><?php _e('First page', 'tradepress'); ?></span>
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                            <a class="prev-page button" href="<?php echo esc_url(add_query_arg('api_page', $page - 1)); ?>">
                                <span class="screen-reader-text"><?php _e('Previous page', 'tradepress'); ?></span>
                                <span aria-hidden="true">&lsaquo;</span>
                            </a>
                        <?php else: ?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                        <?php endif; ?>
                        
                        <span class="paging-input">
                            <span class="tablenav-paging-text">
                                <?php printf(__('Page %1$s of %2$s', 'tradepress'), $page, $total_pages); ?>
                            </span>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a class="next-page button" href="<?php echo esc_url(add_query_arg('api_page', $page + 1)); ?>">
                                <span class="screen-reader-text"><?php _e('Next page', 'tradepress'); ?></span>
                                <span aria-hidden="true">&rsaquo;</span>
                            </a>
                            <a class="last-page button" href="<?php echo esc_url(add_query_arg('api_page', $total_pages)); ?>">
                                <span class="screen-reader-text"><?php _e('Last page', 'tradepress'); ?></span>
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        <?php else: ?>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e('No API calls found matching your criteria.', 'tradepress'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Details Modal -->
    <div id="api-details-modal" class="tradepress-modal" style="display:none;">
        <div class="tradepress-modal-content">
            <div class="modal-header">
                <h2><?php _e('API Call Details', 'tradepress'); ?></h2>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="api-details-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button modal-close-button"><?php _e('Close', 'tradepress'); ?></button>
            </div>
        </div>
    </div>
</div>
