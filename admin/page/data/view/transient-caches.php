<?php
/**
 * TradePress Transient Caches Tab
 *
 * Displays all transient caches used by TradePress with their details
 *
 * @package TradePress
 * @subpackage admin/page/DataTabs
 * @version 1.0.0
 * @since 1.0.0
 * @created 2025-05-10 14:30:00
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all TradePress transient caches
 * 
 * @return array List of transient caches with their details
 */
function tradepress_get_transient_caches() {
    global $wpdb;
    
    // Hardcoded list of TradePress transient caches with their information
    $transient_info = array(
        'bugnet_wpaction' => array(
            'name' => 'Action Hooks Log',
            'purpose' => 'Logs WordPress action hook executions for debugging',
            'functionality' => 'Hook Monitoring',
            'added_in' => '1.0.0',
            'normal_expiry' => '1 hour'
        ),
        '_TradePress_activation_redirect' => array(
            'name' => 'Activation Redirect',
            'purpose' => 'Controls redirection after plugin activation',
            'functionality' => 'Plugin Activation',
            'added_in' => '1.0.0',
            'normal_expiry' => '30 seconds'
        ),
        'TradePresshelptabappstatus' => array(
            'name' => 'Help Tab App Status',
            'purpose' => 'Stores application status for the help tab',
            'functionality' => 'Admin Help',
            'added_in' => '1.0.0',
            'normal_expiry' => '1 hour'
        ),
        'TradePress_api_endpoints' => array(
            'name' => 'API Endpoints',
            'purpose' => 'Caches API endpoint details',
            'functionality' => 'API Management',
            'added_in' => '1.0.0',
            'normal_expiry' => '12 hours'
        ),
        'TradePress_market_data' => array(
            'name' => 'Market Data',
            'purpose' => 'Caches financial market data to reduce API calls',
            'functionality' => 'Financial Data',
            'added_in' => '1.0.0',
            'normal_expiry' => '15 minutes'
        ),
        'TradePress_quote_data' => array(
            'name' => 'Quote Data',
            'purpose' => 'Stores stock quote information',
            'functionality' => 'Stock Data',
            'added_in' => '1.0.0',
            'normal_expiry' => '1 minute'
        ),
        'TradePress_historical_data' => array(
            'name' => 'Historical Data',
            'purpose' => 'Caches historical price data',
            'functionality' => 'Chart Data',
            'added_in' => '1.0.0',
            'normal_expiry' => '6 hours'
        )
    );
    
    // Get actual transient data from the database
    $transient_data = array();
    
    // Query for all transients that match our keys
    $like_patterns = array();
    foreach ($transient_info as $key => $info) {
        $like_patterns[] = "option_name LIKE '_transient_$key' OR option_name LIKE '_transient_timeout_$key'";
    }
    
    $query = "SELECT option_name, option_value FROM $wpdb->options WHERE " . implode(' OR ', $like_patterns);
    $results = $wpdb->get_results($query);

    // Process results
    $transients = array();
    $timeouts = array();
    
    if (!empty($results)) {
        foreach ($results as $result) {
            if (strpos($result->option_name, '_transient_timeout_') === 0) {
                // This is a timeout entry
                $key = str_replace('_transient_timeout_', '', $result->option_name);
                $timeouts[$key] = $result->option_value;
            } else {
                // This is a data entry
                $key = str_replace('_transient_', '', $result->option_name);
                $transients[$key] = $result->option_value;
            }
        }
    }
    
    // Combine data
    foreach ($transient_info as $key => $info) {
        $value = isset($transients[$key]) ? $transients[$key] : null;
        $timeout = isset($timeouts[$key]) ? $timeouts[$key] : null;
        
        $transient_data[] = array(
            'key' => $key,
            'name' => $info['name'],
            'purpose' => $info['purpose'],
            'functionality' => $info['functionality'],
            'added_in' => $info['added_in'],
            'normal_expiry' => $info['normal_expiry'],
            'exists' => ($value !== null),
            'timeout' => $timeout ? date('Y-m-d H:i:s', $timeout) : 'N/A',
            'expires_in' => $timeout ? human_time_diff(time(), $timeout) : 'N/A',
            'size' => $value ? strlen(maybe_serialize($value)) : 0
        );
    }
    
    return $transient_data;
}

/**
 * Display the Transient Caches tab content
 */
function tradepress_transient_caches_tab_content() {
    // Get all transient caches
    $transients = tradepress_get_transient_caches();
    
    ?>
    <div class="tradepress-transient-caches-container">
        <div class="tradepress-data-section">
            <div class="section-header">
                <div class="section-actions">
                    <button type="button" class="button action-refresh"><span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh Data', 'tradepress'); ?></button>
                    <button type="button" class="button action-purge-all"><span class="dashicons dashicons-trash"></span> <?php esc_html_e('Purge All Caches', 'tradepress'); ?></button>
                </div>
            </div>
            
            <p class="section-description"><?php esc_html_e('Review and manage transient caches used by TradePress to optimize performance and reduce API calls.', 'tradepress'); ?></p>
            
            <div class="transients-table-container">
                <table class="widefat striped transients-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Cache Key', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Name', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Functionality', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Status', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Expiry', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Size', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Actions', 'tradepress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transients as $transient): ?>
                            <tr data-transient-key="<?php echo esc_attr($transient['key']); ?>">
                                <td><?php echo esc_html($transient['key']); ?></td>
                                <td>
                                    <strong><?php echo esc_html($transient['name']); ?></strong>
                                    <div class="row-actions">
                                        <span class="purpose"><?php echo esc_html($transient['purpose']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo esc_html($transient['functionality']); ?></td>
                                <td>
                                    <?php if ($transient['exists']): ?>
                                        <span class="status-active"><?php esc_html_e('Active', 'tradepress'); ?></span>
                                    <?php else: ?>
                                        <span class="status-inactive"><?php esc_html_e('Not Set', 'tradepress'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($transient['exists']): ?>
                                        <?php echo esc_html($transient['expires_in']); ?>
                                        <div class="row-actions">
                                            <span class="expiry-time"><?php echo esc_html($transient['timeout']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <?php esc_html_e('N/A', 'tradepress'); ?>
                                        <div class="row-actions">
                                            <span class="normal-expiry"><?php esc_html_e('Normal: ', 'tradepress'); ?><?php echo esc_html($transient['normal_expiry']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($transient['exists']) {
                                        echo esc_html(size_format($transient['size']));
                                    } else {
                                        esc_html_e('0 B', 'tradepress');
                                    }
                                    ?>
                                </td>
                                <td class="actions">
                                    <?php if ($transient['exists']): ?>
                                        <button type="button" class="button button-small view-cache" data-key="<?php echo esc_attr($transient['key']); ?>">
                                            <?php esc_html_e('View', 'tradepress'); ?>
                                        </button>
                                        <button type="button" class="button button-small purge-cache" data-key="<?php echo esc_attr($transient['key']); ?>">
                                            <?php esc_html_e('Purge', 'tradepress'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="button button-small view-cache" data-key="<?php echo esc_attr($transient['key']); ?>" disabled>
                                            <?php esc_html_e('View', 'tradepress'); ?>
                                        </button>
                                        <button type="button" class="button button-small purge-cache" data-key="<?php echo esc_attr($transient['key']); ?>" disabled>
                                            <?php esc_html_e('Purge', 'tradepress'); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Cache Details Modal -->
        <div id="cache-details-modal" class="tradepress-modal" style="display: none;">
            <div class="tradepress-modal-content">
                <div class="modal-header">
                    <h2><?php esc_html_e('Cache Contents', 'tradepress'); ?>: <span id="modal-cache-name"></span></h2>
                    <span class="modal-close">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="cache-details-loader" style="text-align: center; padding: 20px;">
                        <span class="spinner is-active" style="float: none; margin: 0;"></span>
                        <p><?php esc_html_e('Loading cache contents...', 'tradepress'); ?></p>
                    </div>
                    <div id="cache-contents-container"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button purge-cache-modal" data-key=""><?php esc_html_e('Purge This Cache', 'tradepress'); ?></button>
                    <button type="button" class="button modal-close-button"><?php esc_html_e('Close', 'tradepress'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Call the main function to display the tab content
tradepress_transient_caches_tab_content();