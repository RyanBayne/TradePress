<?php
/**
 * TradePress - API Efficiency Management Tab
 * 
 * @package TradePress/Admin/TradingPlatforms
 * @version 1.0.0
 * @created 2024-12-16
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load API Directory
if (!class_exists('TradePress_API_Directory')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . '/api/api-directory.php';
}

// Get all API providers
$all_providers = TradePress_API_Directory::get_all_providers();

// Handle form submissions for priority settings
if (isset($_POST['action']) && $_POST['action'] === 'save_api_priorities') {
    if (wp_verify_nonce($_POST['priority_nonce'], 'tradepress_api_priorities') && current_user_can('manage_options')) {
        
        // Save primary API selections
        $primary_apis = [
            'primary_live_trading' => sanitize_text_field($_POST['primary_live_trading'] ?? ''),
            'primary_paper_trading' => sanitize_text_field($_POST['primary_paper_trading'] ?? ''),
            'primary_data_only' => sanitize_text_field($_POST['primary_data_only'] ?? '')
        ];
        
        // Save secondary API selections
        $secondary_apis = [
            'secondary_live_trading' => sanitize_text_field($_POST['secondary_live_trading'] ?? ''),
            'secondary_paper_trading' => sanitize_text_field($_POST['secondary_paper_trading'] ?? ''),
            'secondary_data_only' => sanitize_text_field($_POST['secondary_data_only'] ?? '')
        ];
        
        // Update options
        update_option('tradepress_primary_apis', $primary_apis);
        update_option('tradepress_secondary_apis', $secondary_apis);
        
        add_settings_error('tradepress_api_priority', 'priorities_saved', 
                          __('API priorities saved successfully.', 'tradepress'), 'updated');
    }
}

// Get current priority settings
$primary_apis = get_option('tradepress_primary_apis', [
    'primary_live_trading' => '',
    'primary_paper_trading' => '',
    'primary_data_only' => ''
]);

$secondary_apis = get_option('tradepress_secondary_apis', [
    'secondary_live_trading' => '',
    'secondary_paper_trading' => '',
    'secondary_data_only' => ''
]);

// Filter APIs by type
$trading_apis = [];
$data_only_apis = [];

foreach ($all_providers as $api_id => $provider) {
    $is_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no') === 'yes';
    if (!$is_enabled) continue;
    
    if (isset($provider['api_type']) && $provider['api_type'] === 'data_only') {
        $data_only_apis[$api_id] = $provider;
    } else {
        $trading_apis[$api_id] = $provider;
    }
}

?>

<div class="configure-directives-container">
    <?php settings_errors('tradepress_api_priority'); ?>
    
    <div class="directives-layout">
        <!-- Left Column: Rate Limiting Visualization -->
        <div class="directives-table-container">
            <h3><?php esc_html_e('Rate Limiting Status', 'tradepress'); ?></h3>
            
            <div class="rate-limit-dashboard">
                <?php foreach ($all_providers as $api_id => $provider): 
                    $is_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no') === 'yes';
                    if (!$is_enabled) continue;
                    
                    // Get rate limit data (placeholder for now)
                    $calls_today = get_option('tradepress_' . $api_id . '_rate_limit_count', 0);
                    $daily_limit = 25; // This would come from API provider settings
                    $usage_percent = ($calls_today / $daily_limit) * 100;
                    $status_class = $usage_percent > 80 ? 'critical' : ($usage_percent > 60 ? 'warning' : 'normal');
                ?>
                    <div class="rate-limit-card">
                        <div class="card-header">
                            <h4><?php echo esc_html($provider['name']); ?></h4>
                            <span class="status-indicator status-<?php echo esc_attr($status_class); ?>"></span>
                        </div>
                        <div class="card-content">
                            <div class="usage-bar">
                                <div class="usage-fill" style="width: <?php echo min(100, $usage_percent); ?>%"></div>
                            </div>
                            <div class="usage-stats">
                                <span><?php echo esc_html($calls_today); ?> / <?php echo esc_html($daily_limit); ?> calls</span>
                                <span class="percentage"><?php echo round($usage_percent, 1); ?>%</span>
                            </div>
                            <div class="last-call">
                                Last call: <?php echo date('H:i:s'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h3><?php esc_html_e('Cache Efficiency Status', 'tradepress'); ?></h3>
            <div class="cache-efficiency-dashboard">
                <?php 
                // Check cache status for common symbols
                $common_symbols = ['NVDA', 'AAPL', 'TSLA', 'MSFT'];
                foreach ($all_providers as $api_id => $provider):
                    $is_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no') === 'yes';
                    if (!$is_enabled) continue;
                    
                    $cache_hits = 0;
                    $total_checks = count($common_symbols);
                    
                    foreach ($common_symbols as $symbol) {
                        $cache_key = 'tradepress_' . $api_id . '_' . $symbol . '_bars';
                        if (get_transient($cache_key) !== false) {
                            $cache_hits++;
                        }
                    }
                    
                    $cache_efficiency = $total_checks > 0 ? ($cache_hits / $total_checks) * 100 : 0;
                ?>
                    <div class="cache-efficiency-item">
                        <div class="cache-header">
                            <strong><?php echo esc_html($provider['name']); ?></strong>
                            <span class="cache-percentage"><?php echo round($cache_efficiency); ?>% cached</span>
                        </div>
                        <div class="cache-details">
                            <small><?php echo $cache_hits; ?>/<?php echo $total_checks; ?> symbols cached</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h3><?php esc_html_e('Recent API Activity', 'tradepress'); ?></h3>
            <div class="api-activity-log">
                <div class="activity-item">
                    <span class="timestamp"><?php echo date('H:i:s'); ?></span>
                    <span class="api-name">Alpaca</span>
                    <span class="endpoint">bars/NVDA</span>
                    <span class="status success">Success</span>
                </div>
                <div class="activity-item">
                    <span class="timestamp"><?php echo date('H:i:s', time() - 120); ?></span>
                    <span class="api-name">Alpha Vantage</span>
                    <span class="endpoint">TIME_SERIES_INTRADAY</span>
                    <span class="status success">Success</span>
                </div>
                <div class="activity-item">
                    <span class="timestamp"><?php echo date('H:i:s', time() - 300); ?></span>
                    <span class="api-name">Polygon</span>
                    <span class="endpoint">aggregates/AAPL</span>
                    <span class="status error">Rate Limited</span>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Priority Management -->
        <div class="directive-right-column">
            <div class="directive-details-container">
                <div class="directive-section">
                    <div class="section-header">
                        <h3><?php esc_html_e('API Priority Configuration', 'tradepress'); ?></h3>
                    </div>
                    <div class="section-content">
                        <form method="post">
                            <input type="hidden" name="priority_nonce" value="<?php echo wp_create_nonce('tradepress_api_priorities'); ?>">
                            <input type="hidden" name="action" value="save_api_priorities">
                            
                            <h4><?php esc_html_e('Primary APIs', 'tradepress'); ?></h4>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="primary_live_trading">
                                            <?php esc_html_e('Live Trading API', 'tradepress'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="primary_live_trading" name="primary_live_trading">
                                            <option value=""><?php esc_html_e('Select API...', 'tradepress'); ?></option>
                                            <?php foreach ($trading_apis as $api_id => $provider): ?>
                                                <option value="<?php echo esc_attr($api_id); ?>" <?php selected($primary_apis['primary_live_trading'], $api_id); ?>>
                                                    <?php echo esc_html($provider['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="primary_paper_trading">
                                            <?php esc_html_e('Paper Trading API', 'tradepress'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="primary_paper_trading" name="primary_paper_trading">
                                            <option value=""><?php esc_html_e('Select API...', 'tradepress'); ?></option>
                                            <?php foreach ($trading_apis as $api_id => $provider): ?>
                                                <option value="<?php echo esc_attr($api_id); ?>" <?php selected($primary_apis['primary_paper_trading'], $api_id); ?>>
                                                    <?php echo esc_html($provider['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="primary_data_only">
                                            <?php esc_html_e('Data Only API', 'tradepress'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="primary_data_only" name="primary_data_only">
                                            <option value=""><?php esc_html_e('Select API...', 'tradepress'); ?></option>
                                            <?php foreach ($data_only_apis as $api_id => $provider): ?>
                                                <option value="<?php echo esc_attr($api_id); ?>" <?php selected($primary_apis['primary_data_only'], $api_id); ?>>
                                                    <?php echo esc_html($provider['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            
                            <h4><?php esc_html_e('Secondary APIs (Fallback)', 'tradepress'); ?></h4>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="secondary_live_trading">
                                            <?php esc_html_e('Live Trading API', 'tradepress'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="secondary_live_trading" name="secondary_live_trading">
                                            <option value=""><?php esc_html_e('Select API...', 'tradepress'); ?></option>
                                            <?php foreach ($trading_apis as $api_id => $provider): ?>
                                                <option value="<?php echo esc_attr($api_id); ?>" <?php selected($secondary_apis['secondary_live_trading'], $api_id); ?>>
                                                    <?php echo esc_html($provider['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="secondary_paper_trading">
                                            <?php esc_html_e('Paper Trading API', 'tradepress'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="secondary_paper_trading" name="secondary_paper_trading">
                                            <option value=""><?php esc_html_e('Select API...', 'tradepress'); ?></option>
                                            <?php foreach ($trading_apis as $api_id => $provider): ?>
                                                <option value="<?php echo esc_attr($api_id); ?>" <?php selected($secondary_apis['secondary_paper_trading'], $api_id); ?>>
                                                    <?php echo esc_html($provider['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="secondary_data_only">
                                            <?php esc_html_e('Data Only API', 'tradepress'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="secondary_data_only" name="secondary_data_only">
                                            <option value=""><?php esc_html_e('Select API...', 'tradepress'); ?></option>
                                            <?php foreach ($data_only_apis as $api_id => $provider): ?>
                                                <option value="<?php echo esc_attr($api_id); ?>" <?php selected($secondary_apis['secondary_data_only'], $api_id); ?>>
                                                    <?php echo esc_html($provider['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            
                            <div class="api-settings-actions">
                                <button type="submit" class="button button-primary">
                                    <?php esc_html_e('Save Priority Settings', 'tradepress'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="directive-details-container">
                <div class="directive-section">
                    <div class="section-header">
                        <h3><?php esc_html_e('Fallback Strategy', 'tradepress'); ?></h3>
                    </div>
                    <div class="section-content">
                        <p><?php esc_html_e('When an API call fails or rate limits are exceeded:', 'tradepress'); ?></p>
                        <ol>
                            <li><?php esc_html_e('Try Primary API first', 'tradepress'); ?></li>
                            <li><?php esc_html_e('If Primary fails, try Secondary API', 'tradepress'); ?></li>
                            <li><?php esc_html_e('If Secondary fails, select randomly from remaining enabled APIs', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Log all fallback attempts for monitoring', 'tradepress'); ?></li>
                        </ol>
                        
                        <h4><?php esc_html_e('Current Priority Order', 'tradepress'); ?></h4>
                        <div class="priority-display">
                            <div class="priority-group">
                                <strong><?php esc_html_e('Live Trading:', 'tradepress'); ?></strong>
                                <span><?php echo esc_html($primary_apis['primary_live_trading'] ? $all_providers[$primary_apis['primary_live_trading']]['name'] ?? 'Unknown' : 'Not set'); ?></span>
                                →
                                <span><?php echo esc_html($secondary_apis['secondary_live_trading'] ? $all_providers[$secondary_apis['secondary_live_trading']]['name'] ?? 'Unknown' : 'Not set'); ?></span>
                            </div>
                            <div class="priority-group">
                                <strong><?php esc_html_e('Paper Trading:', 'tradepress'); ?></strong>
                                <span><?php echo esc_html($primary_apis['primary_paper_trading'] ? $all_providers[$primary_apis['primary_paper_trading']]['name'] ?? 'Unknown' : 'Not set'); ?></span>
                                →
                                <span><?php echo esc_html($secondary_apis['secondary_paper_trading'] ? $all_providers[$secondary_apis['secondary_paper_trading']]['name'] ?? 'Unknown' : 'Not set'); ?></span>
                            </div>
                            <div class="priority-group">
                                <strong><?php esc_html_e('Data Only:', 'tradepress'); ?></strong>
                                <span><?php echo esc_html($primary_apis['primary_data_only'] ? $all_providers[$primary_apis['primary_data_only']]['name'] ?? 'Unknown' : 'Not set'); ?></span>
                                →
                                <span><?php echo esc_html($secondary_apis['secondary_data_only'] ? $all_providers[$secondary_apis['secondary_data_only']]['name'] ?? 'Unknown' : 'Not set'); ?></span>
                            </div>
                        </div>
                        
                        <h4><?php esc_html_e('Next API Call Prediction', 'tradepress'); ?></h4>
                        <div class="next-call-prediction">
                            <?php 
                            // Determine which API would be used for next data call
                            $next_data_api = 'None available';
                            $prediction_reason = 'No data APIs configured';
                            
                            if (!empty($primary_apis['primary_data_only'])) {
                                $primary_id = $primary_apis['primary_data_only'];
                                $primary_calls = get_option('tradepress_' . $primary_id . '_rate_limit_count', 0);
                                $primary_limit = 25; // This would come from API settings
                                
                                if ($primary_calls < $primary_limit) {
                                    $next_data_api = $all_providers[$primary_id]['name'] ?? 'Unknown';
                                    $prediction_reason = 'Primary API available (' . $primary_calls . '/' . $primary_limit . ' calls used)';
                                } elseif (!empty($secondary_apis['secondary_data_only'])) {
                                    $secondary_id = $secondary_apis['secondary_data_only'];
                                    $secondary_calls = get_option('tradepress_' . $secondary_id . '_rate_limit_count', 0);
                                    $secondary_limit = 25;
                                    
                                    if ($secondary_calls < $secondary_limit) {
                                        $next_data_api = $all_providers[$secondary_id]['name'] ?? 'Unknown';
                                        $prediction_reason = 'Primary exhausted, using secondary (' . $secondary_calls . '/' . $secondary_limit . ' calls used)';
                                    } else {
                                        $next_data_api = 'Random fallback';
                                        $prediction_reason = 'Both primary and secondary APIs exhausted';
                                    }
                                } else {
                                    $next_data_api = 'Rate limited';
                                    $prediction_reason = 'Primary API exhausted, no secondary configured';
                                }
                            }
                            ?>
                            <div class="prediction-item">
                                <strong><?php esc_html_e('Data Only Call:', 'tradepress'); ?></strong>
                                <span class="next-api"><?php echo esc_html($next_data_api); ?></span>
                                <small class="prediction-reason"><?php echo esc_html($prediction_reason); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rate-limit-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.rate-limit-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.card-header h4 {
    margin: 0;
    font-size: 14px;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-normal { background: #28a745; }
.status-warning { background: #ffc107; }
.status-critical { background: #dc3545; }

.usage-bar {
    background: #f1f1f1;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.usage-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745 0%, #ffc107 60%, #dc3545 80%);
    transition: width 0.3s ease;
}

.usage-stats {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    margin-bottom: 5px;
}

.percentage {
    font-weight: bold;
}

.last-call {
    font-size: 11px;
    color: #666;
}

.api-activity-log {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
}

.activity-item {
    display: grid;
    grid-template-columns: 80px 100px 1fr 80px;
    gap: 10px;
    padding: 8px 12px;
    border-bottom: 1px solid #e9ecef;
    font-size: 12px;
    align-items: center;
}

.activity-item:last-child {
    border-bottom: none;
}

.timestamp {
    color: #666;
}

.api-name {
    font-weight: bold;
}

.status.success {
    color: #28a745;
}

.status.error {
    color: #dc3545;
}

.priority-display {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin-top: 10px;
}

.priority-group {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.priority-group:last-child {
    margin-bottom: 0;
}

.priority-group strong {
    min-width: 120px;
}

.priority-group span {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.next-call-prediction {
    background: #e8f4fd;
    border: 1px solid #bee5eb;
    border-radius: 4px;
    padding: 15px;
    margin-top: 15px;
}

.prediction-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.next-api {
    background: #007cba;
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: bold;
    display: inline-block;
    width: fit-content;
}

.prediction-reason {
    color: #666;
    font-style: italic;
}

.cache-efficiency-dashboard {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.cache-efficiency-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.cache-efficiency-item:last-child {
    border-bottom: none;
}

.cache-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cache-percentage {
    background: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
}

.cache-details {
    color: #666;
    font-size: 12px;
}
</style>