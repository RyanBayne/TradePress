<?php
/**
 * TradePress - API Management Tab
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

// CSS is enqueued by the main Trading Platforms page

// Get all API providers
$all_providers = TradePress_API_Directory::get_all_providers();

// Handle form submissions
if (isset($_POST['action']) && $_POST['action'] === 'toggle_api_status') {
    if (wp_verify_nonce($_POST['toggle_nonce'], 'tradepress_toggle_api') && current_user_can('manage_options')) {
        $api_id = sanitize_text_field($_POST['api_id']);
        $new_state = (bool)intval($_POST['new_state']);
        
        $option_name = 'TradePress_switch_' . $api_id . '_api_services';
        update_option($option_name, $new_state ? 'yes' : 'no');
        
        $message = $new_state 
            ? sprintf(__('%s API has been enabled.', 'tradepress'), ucfirst($api_id))
            : sprintf(__('%s API has been disabled.', 'tradepress'), ucfirst($api_id));
        
        add_settings_error('tradepress_api_management', 'api_toggled', $message, 'updated');
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'toggle_trading_mode') {
    if (wp_verify_nonce($_POST['trading_mode_nonce'], 'tradepress_toggle_trading_mode') && current_user_can('manage_options')) {
        $api_id = sanitize_text_field($_POST['api_id']);
        $new_mode = sanitize_text_field($_POST['new_mode']);
        
        $option_name = 'TradePress_api_' . $api_id . '_trading_mode';
        update_option($option_name, $new_mode);
        
        $message = sprintf(__('%s trading mode switched to %s.', 'tradepress'), ucfirst($api_id), ucfirst($new_mode));
        add_settings_error('tradepress_api_management', 'trading_mode_toggled', $message, 'updated');
    }
}

// Handle API settings form submissions
if (isset($_POST['action']) && strpos($_POST['action'], 'tradepress_save_') === 0 && strpos($_POST['action'], '_settings') !== false) {
    $api_id = sanitize_text_field($_POST['api_id']);
    $nonce_field = 'tradepress_' . $api_id . '_nonce';
    $nonce_action = 'tradepress_save_' . $api_id . '_settings';
    
    if (wp_verify_nonce($_POST[$nonce_field], $nonce_action) && current_user_can('manage_options')) {
        // Handle checkboxes first
        $checkbox_fields = [
            $api_id . '_enable_api' => 'TradePress_switch_' . $api_id . '_api_services',
            $api_id . '_api_logging' => 'tradepress_' . $api_id . '_api_logging',
            $api_id . '_premium_endpoints' => 'tradepress_' . $api_id . '_premium_endpoints'
        ];
        
        foreach ($checkbox_fields as $post_key => $option_key) {
            $value = isset($_POST[$post_key]) ? 'yes' : 'no';
            update_option($option_key, $value);
        }
        
        // Handle regular form fields with proper option name mapping
        $field_mappings = [
            // Trading API fields
            $api_id . '_api_key' => 'tradepress_' . $api_id . '_api_key',
            $api_id . '_api_secret' => 'tradepress_' . $api_id . '_api_secret',
            $api_id . '_paper_api_key' => 'tradepress_' . $api_id . '_paper_api_key',
            $api_id . '_paper_api_secret' => 'tradepress_' . $api_id . '_paper_api_secret',
            $api_id . '_max_position_size' => 'tradepress_' . $api_id . '_max_position_size',
            $api_id . '_stop_loss_percent' => 'tradepress_' . $api_id . '_stop_loss_percent',
            $api_id . '_take_profit_percent' => 'tradepress_' . $api_id . '_take_profit_percent',
            // Data settings
            $api_id . '_update_frequency' => 'tradepress_' . $api_id . '_update_frequency',
            $api_id . '_data_retention' => 'tradepress_' . $api_id . '_data_retention',
            $api_id . '_data_priority' => 'tradepress_' . $api_id . '_data_priority',
            // Data-only API fields
            'TradePress_api_' . $api_id . '_key' => 'TradePress_api_' . $api_id . '_key',
            'tradepress_api_alphavantage_key' => 'tradepress_api_alphavantage_key'
        ];
        
        foreach ($field_mappings as $post_key => $option_key) {
            if (isset($_POST[$post_key])) {
                $sanitized_value = sanitize_text_field($_POST[$post_key]);
                update_option($option_key, $sanitized_value);
            }
        }
        
        $message = sprintf(__('%s settings saved successfully.', 'tradepress'), ucfirst($api_id));
        add_settings_error('tradepress_api_management', 'settings_saved', $message, 'updated');
    }
}

// Handle API test requests
if (isset($_POST['action']) && ($_POST['action'] === 'test_api' || $_POST['action'] === 'query_test')) {
    $is_query_test = $_POST['action'] === 'query_test';
    if (wp_verify_nonce($_POST['test_api_nonce'], 'tradepress_test_api') && current_user_can('manage_options')) {
        $api_id = sanitize_text_field($_POST['api_id']);
        $default_symbol = get_option('tradepress_default_symbol', 'NVDA');
        
        // Enhanced logging for both test types
        $test_type = $is_query_test ? 'Query Test' : 'Call Test';
        error_log("[" . date('Y-m-d H:i:s') . "] {$test_type} initiated for {$api_id} API, symbol: {$default_symbol}\n", 3, ABSPATH . 'calls.log');
        
        // Check for cached data if this is a Query Test
        if ($is_query_test) {
            // Multiple cache key patterns to check
            $cache_patterns = [
                'tradepress_' . $api_id . '_' . $default_symbol . '_bars',
                'tradepress_' . $api_id . '_bars_' . $default_symbol,
                $api_id . '_' . $default_symbol . '_bars',
                'api_cache_' . $api_id . '_' . $default_symbol
            ];
            
            $cache_found = false;
            $cache_key_used = '';
            $cache_expiry = 0;
            
            foreach ($cache_patterns as $cache_key) {
                $cached_data = get_transient($cache_key);
                error_log("[" . date('Y-m-d H:i:s') . "] Checking cache key: {$cache_key} - " . ($cached_data !== false ? 'FOUND' : 'NOT FOUND') . "\n", 3, ABSPATH . 'calls.log');
                
                if ($cached_data !== false) {
                    $cache_found = true;
                    $cache_key_used = $cache_key;
                    $cache_expiry = get_option('_transient_timeout_' . $cache_key, time());
                    break;
                }
            }
            
            if ($cache_found) {
                error_log("[" . date('Y-m-d H:i:s') . "] Query Test SUCCESS - Cache hit for {$default_symbol}, key: {$cache_key_used}\n", 3, ABSPATH . 'calls.log');
                
                $notice_content = "<div class='tradepress-test-results'>";
                $notice_content .= "<div class='test-section background-activity'>";
                $notice_content .= "<h4>Background Activity</h4>";
                $notice_content .= "<div class='activity-item'>Cache Check: {$cache_key_used} - FOUND</div>";
                $notice_content .= "<div class='activity-item'>API Call: SKIPPED (cache hit)</div>";
                $notice_content .= "<div class='activity-item'>Rate Limit: PRESERVED</div>";
                $notice_content .= "</div>";
                $notice_content .= "<div class='test-section test-summary'>";
                $notice_content .= "<h4>Query Test Results - Cache Hit</h4>";
                $notice_content .= "<div class='test-grid'>";
                $notice_content .= "<div class='test-row'><span>Platform:</span><span>" . ucfirst($api_id) . "</span></div>";
                $notice_content .= "<div class='test-row'><span>Symbol:</span><span>{$default_symbol}</span></div>";
                $notice_content .= "<div class='test-row'><span>Status:</span><span class='status-success'>✓ Cache Hit</span></div>";
                $notice_content .= "<div class='test-row'><span>Cache Key:</span><span>{$cache_key_used}</span></div>";
                $notice_content .= "<div class='test-row'><span>Expires:</span><span>" . date('Y-m-d H:i:s', $cache_expiry) . "</span></div>";
                $notice_content .= "<div class='test-row'><span>API Call Made:</span><span class='status-success'>NO</span></div>";
                $notice_content .= "</div>";
                $notice_content .= "</div></div>";
                
                add_settings_error('tradepress_api_management', 'query_test_cached', $notice_content, 'updated');
                return;
            } else {
                error_log("[" . date('Y-m-d H:i:s') . "] Query Test - No cache found for {$default_symbol}, proceeding with API call\n", 3, ABSPATH . 'calls.log');
            }
        }
        
        // Get rate limit before test
        $rate_limit_before = get_option('tradepress_' . $api_id . '_rate_limit_count', 0);
        
        // Determine test endpoint based on API
        $test_endpoints = [
            'alphavantage' => 'TIME_SERIES_INTRADAY',
            'alpaca' => 'bars',
            'polygon' => 'aggregates'
        ];
        
        $endpoint = $test_endpoints[$api_id] ?? 'default';
        
        // Perform actual API test
        $test_start_time = microtime(true);
        $test_success = false;
        $response_data = [];
        $import_notices = [];
        
        // Initialize variables outside try block
        $trading_mode = 'unknown';
        $base_url = 'unknown';
        
        try {
            // Load appropriate API class and make actual call
            if ($api_id === 'alpaca') {
                // Get trading mode to determine which credentials and endpoint to use
                $trading_mode = get_option('TradePress_api_alpaca_trading_mode', 'paper');
                
                if ($trading_mode === 'live') {
                    $api_key = get_option('tradepress_alpaca_api_key', '');
                    $api_secret = get_option('tradepress_alpaca_api_secret', '');
                    $base_url = 'https://data.alpaca.markets';
                } else {
                    $api_key = get_option('tradepress_alpaca_paper_api_key', '');
                    $api_secret = get_option('tradepress_alpaca_paper_api_secret', '');
                    $base_url = 'https://data.alpaca.markets';
                }
                
                if (empty($api_key) || empty($api_secret)) {
                    throw new Exception('API credentials not configured for ' . $trading_mode . ' mode');
                }
                
                // Actual Alpaca bars API call using appropriate endpoint
                $url = "{$base_url}/v2/stocks/{$default_symbol}/bars?timeframe=1Day&limit=1";
                $headers = [
                    'APCA-API-KEY-ID: ' . $api_key,
                    'APCA-API-SECRET-KEY: ' . $api_secret
                ];
                
                // Log to alpaca.log if enabled
                if (get_option('bugnet_output_alpaca', 'no') === 'yes') {
                    $developer_mode = get_option('tradepress_developer_mode', 'no') === 'yes';
                    $log_msg = "[" . date('Y-m-d H:i:s') . "] API Test Started - Mode: {$trading_mode}, Symbol: {$default_symbol}, Endpoint: {$endpoint}";
                    if ($developer_mode) {
                        $log_msg .= ", URL: {$url}, Headers: " . json_encode($headers);
                    }
                    error_log($log_msg . "\n", 3, WP_CONTENT_DIR . '/alpaca.log');
                }
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'TradePress/1.0');
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_error = curl_error($ch);
                $curl_errno = curl_errno($ch);
                curl_close($ch);
                
                // Log summary to calls.log
                $status = ($curl_errno === 0 && $http_code === 200) ? 'SUCCESS' : 'FAILED';
                error_log("[" . date('Y-m-d H:i:s') . "] Alpaca: {$default_symbol}/{$endpoint} - {$status}\n", 3, ABSPATH . 'calls.log');
                
                // Log details to alpaca.log if enabled
                if (get_option('bugnet_output_alpaca', 'no') === 'yes') {
                    $developer_mode = get_option('tradepress_developer_mode', 'no') === 'yes';
                    $log_msg = "[" . date('Y-m-d H:i:s') . "] API Response - HTTP: {$http_code}, Status: {$status}";
                    if ($curl_errno !== 0) {
                        $log_msg .= ", cURL Error: {$curl_errno} - {$curl_error}";
                    }
                    if ($developer_mode && $response) {
                        $log_msg .= ", Response Size: " . strlen($response) . " bytes, Content: " . $response;
                    } elseif ($response && $http_code !== 200) {
                        $log_msg .= ", Response: " . $response;
                    }
                    error_log($log_msg . "\n", 3, WP_CONTENT_DIR . '/alpaca.log');
                }
                
                if ($curl_errno !== 0) {
                    throw new Exception("cURL Error {$curl_errno}: {$curl_error}");
                } elseif ($http_code === 200 && $response) {
                    $data = json_decode($response, true);
                    $test_success = true;
                    
                    $stored_count = count($data['bars'] ?? []);
                    
                    // Store in cache for future Query Tests (15 minutes)
                    $cache_key = 'tradepress_' . $api_id . '_' . $default_symbol . '_bars';
                    set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);
                    error_log("[" . date('Y-m-d H:i:s') . "] Cached data stored: {$cache_key} (expires in 15 minutes)\n", 3, ABSPATH . 'calls.log');
                    
                    $response_data = [
                        'symbol' => $default_symbol,
                        'endpoint' => $endpoint,
                        'trading_mode' => $trading_mode,
                        'api_url' => $base_url,
                        'status' => 'success',
                        'data_points' => count($data['bars'] ?? []),
                        'raw_response_size' => strlen($response)
                    ];
                    
                    // Add developer notice for data import analysis
                    if ($developer_mode) {
                        $import_notices[] = "✓ Data Structure Analysis: Alpaca bars endpoint returns array of OHLCV data";
                        $import_notices[] = "✓ API Response: Received {$stored_count} price data records";
                        if (isset($data['bars'][0])) {
                            $sample_bar = $data['bars'][0];
                            $import_notices[] = "🔍 Sample Data Fields: " . implode(', ', array_keys($sample_bar));
                        }
                    }
                } else {
                    $error_msg = "API call failed with HTTP {$http_code}";
                    if ($response) {
                        $error_msg .= ". Response: " . substr($response, 0, 200);
                    }
                    throw new Exception($error_msg);
                }
            } elseif ($api_id === 'finnhub') {
                // Get Finnhub API key
                $api_key = get_option('TradePress_api_finnhub_key', '');
                $base_url = 'https://finnhub.io/api/v1';
                $trading_mode = 'data_only';
                
                if (empty($api_key)) {
                    throw new Exception('Finnhub API key not configured');
                }
                
                // Finnhub quote API call
                $url = "{$base_url}/quote?symbol={$default_symbol}&token={$api_key}";
                
                $response = wp_remote_get($url, array(
                    'timeout' => 30,
                    'headers' => array(
                        'Accept' => 'application/json',
                    ),
                ));
                
                if (is_wp_error($response)) {
                    throw new Exception('HTTP Error: ' . $response->get_error_message());
                }
                
                $http_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                
                if ($http_code === 200 && $body) {
                    $data = json_decode($body, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($data['c'])) {
                        $test_success = true;
                        
                        $response_data = [
                            'symbol' => $default_symbol,
                            'endpoint' => 'quote',
                            'trading_mode' => $trading_mode,
                            'api_url' => $base_url,
                            'status' => 'success',
                            'current_price' => $data['c'],
                            'raw_response_size' => strlen($body)
                        ];
                    } else {
                        throw new Exception('Invalid JSON response or missing price data');
                    }
                } else {
                    $error_msg = "API call failed with HTTP {$http_code}";
                    if ($body) {
                        $error_msg .= ". Response: " . substr($body, 0, 200);
                    }
                    throw new Exception($error_msg);
                }
            } else {
                // For other APIs, show configuration needed message
                throw new Exception("Live API testing not yet implemented for {$api_id}");
            }
        } catch (Exception $e) {
            $test_success = false;
            $response_data = [
                'symbol' => $default_symbol,
                'endpoint' => $endpoint,
                'trading_mode' => $trading_mode,
                'api_url' => $base_url,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
        
        $test_end_time = microtime(true);
        $execution_time = round(($test_end_time - $test_start_time) * 1000, 2);
        
        // Update rate limit counter
        $rate_limit_after = $rate_limit_before + 1;
        update_option('tradepress_' . $api_id . '_rate_limit_count', $rate_limit_after);
        
        // Check if developer mode is enabled
        $developer_mode = get_option('tradepress_developer_mode', 'no') === 'yes';
        
        if ($developer_mode) {
            // Developer mode output
            $dev_output = "<div class='developer-output'>";
            $dev_output .= "<h4>Developer Information:</h4>";
            $dev_output .= "<p><strong>API:</strong> " . ucfirst($api_id) . "</p>";
            $dev_output .= "<p><strong>Trading Mode:</strong> " . ($response_data['trading_mode'] ?? 'unknown') . "</p>";
            $dev_output .= "<p><strong>API URL:</strong> " . ($response_data['api_url'] ?? 'unknown') . "</p>";
            $dev_output .= "<p><strong>Endpoint:</strong> {$endpoint}</p>";
            $dev_output .= "<p><strong>Symbol:</strong> {$default_symbol}</p>";
            $dev_output .= "<p><strong>Execution Time:</strong> {$execution_time}ms</p>";
            $dev_output .= "<p><strong>Rate Limit Before:</strong> {$rate_limit_before}</p>";
            $dev_output .= "<p><strong>Rate Limit After:</strong> {$rate_limit_after}</p>";
            $dev_output .= "<p><strong>Response Data:</strong> " . json_encode($response_data) . "</p>";
            
            // Add data import notices
            if (!empty($import_notices)) {
                $dev_output .= "<h4>Data Import Analysis:</h4>";
                foreach ($import_notices as $notice) {
                    $dev_output .= "<p>{$notice}</p>";
                }
            }
            
            $dev_output .= "</div>";
        }
        
        // Standardized test output format
        $notice_content = "<div class='tradepress-test-results'>";
        
        // 1. Background Activity (Developer Mode Only)
        if ($developer_mode) {
            $notice_content .= "<div class='test-section background-activity'>";
            $notice_content .= "<h4>Background Activity</h4>";
            $notice_content .= "<div class='activity-item'>API Call: {$api_id} → {$endpoint}({$default_symbol}) - " . ($test_success ? 'Success' : 'Failed') . "</div>";
            $notice_content .= "<div class='activity-item'>DB SELECT: wp_options - Success (rate_limit_count)</div>";
            $notice_content .= "<div class='activity-item'>DB UPDATE: wp_options - Success (rate_limit_count)</div>";
            if (!empty($import_notices)) {
                foreach ($import_notices as $notice) {
                    $notice_content .= "<div class='activity-item'>{$notice}</div>";
                }
            }
            $notice_content .= "</div>";
        }
        
        // 2. Test Results Summary
        $notice_content .= "<div class='test-section test-summary'>";
        $notice_content .= "<h4>" . ucfirst($api_id) . " {$test_type} Results</h4>";
        $notice_content .= "<div class='test-grid'>";
        $notice_content .= "<div class='test-row'><span>Test Type:</span><span>{$test_type}</span></div>";
        $notice_content .= "<div class='test-row'><span>Platform:</span><span>" . ucfirst($api_id) . "</span></div>";
        $notice_content .= "<div class='test-row'><span>Endpoint:</span><span>{$endpoint}</span></div>";
        $notice_content .= "<div class='test-row'><span>Status:</span><span class='" . ($test_success ? 'status-success' : 'status-error') . "'>" . ($test_success ? '✓ Success' : '✗ Failed') . "</span></div>";
        $notice_content .= "<div class='test-row'><span>Environment:</span><span>" . ($trading_mode === 'live' ? 'Live' : 'Paper') . "</span></div>";
        $notice_content .= "<div class='test-row'><span>Trading Mode:</span><span>{$trading_mode}</span></div>";
        $notice_content .= "<div class='test-row'><span>Cache Status:</span><span>" . ($is_query_test ? 'Cache Miss - API Called' : 'Bypassed') . "</span></div>";
        $notice_content .= "<div class='test-row'><span>API Version:</span><span>v2</span></div>";
        $notice_content .= "<div class='test-row'><span>Date/Time:</span><span>" . date('d/m/Y, H:i:s') . "</span></div>";
        $notice_content .= "</div>";
        $notice_content .= "</div>";
        
        // 3. Developer Information
        if ($developer_mode) {
            $notice_content .= "<div class='test-section developer-info'>";
            $notice_content .= "<h4>Developer Information</h4>";
            $notice_content .= "<div class='dev-grid'>";
            $notice_content .= "<div class='dev-row'><span>API:</span><span>" . ucfirst($api_id) . "</span></div>";
            $notice_content .= "<div class='dev-row'><span>Trading Mode:</span><span>{$trading_mode}</span></div>";
            $notice_content .= "<div class='dev-row'><span>API URL:</span><span>" . ($response_data['api_url'] ?? 'unknown') . "</span></div>";
            $notice_content .= "<div class='dev-row'><span>Endpoint:</span><span>{$endpoint}</span></div>";
            $notice_content .= "<div class='dev-row'><span>Symbol:</span><span>{$default_symbol}</span></div>";
            $notice_content .= "<div class='dev-row'><span>Execution Time:</span><span>{$execution_time}ms</span></div>";
            $notice_content .= "<div class='dev-row'><span>Rate Limit Before:</span><span>{$rate_limit_before}</span></div>";
            $notice_content .= "<div class='dev-row'><span>Rate Limit After:</span><span>{$rate_limit_after}</span></div>";
            $notice_content .= "<div class='dev-row'><span>Response Data:</span><span>" . json_encode($response_data) . "</span></div>";
            $notice_content .= "</div>";
            $notice_content .= "</div>";
        }
        
        // 4. Error Message (if failed)
        if (!$test_success) {
            $notice_content .= "<div class='test-section error-message'>";
            $notice_content .= "<h4>Error Message</h4>";
            $notice_content .= "<div class='error-content'>" . ($response_data['error'] ?? 'Unknown error') . " [DEBUG: " . time() . "]</div>";
            $notice_content .= "</div>";
        }
        
        // 5. AI Troubleshooting Report
        $ai_report = "TradePress API Test Report\n";
        $ai_report .= "========================\n\n";
        $ai_report .= "Platform: " . ucfirst($api_id) . "\n";
        $ai_report .= "Endpoint: {$endpoint}\n";
        $ai_report .= "Symbol: {$default_symbol}\n";
        $ai_report .= "Status: " . ($test_success ? 'SUCCESS' : 'FAILED') . "\n";
        $ai_report .= "Trading Mode: {$trading_mode}\n";
        $ai_report .= "API URL: " . ($response_data['api_url'] ?? 'unknown') . "\n";
        $ai_report .= "Execution Time: {$execution_time}ms\n";
        $ai_report .= "Rate Limit: {$rate_limit_before} → {$rate_limit_after}\n";
        if (!$test_success) {
            $ai_report .= "Error: " . ($response_data['error'] ?? 'Unknown error') . "\n";
        }
        $ai_report .= "\nTechnical Details:\n";
        $ai_report .= json_encode($response_data, JSON_PRETTY_PRINT);
        
        $notice_content .= "<div class='test-section ai-report'>";
        $notice_content .= "<h4>AI Troubleshooting Report</h4>";
        $notice_content .= "<textarea readonly class='ai-report-textarea'>" . esc_textarea($ai_report) . "</textarea>";
        $notice_content .= "</div>";
        
        $notice_content .= "</div>";
        
        $notice_type = $test_success ? 'updated' : 'error';
        add_settings_error('tradepress_api_management', 'api_test_result', $notice_content, $notice_type);
    }
}

?>

<div class="configure-directives-container">
    <?php settings_errors('tradepress_api_management'); ?>
    
    <div class="directives-layout">
        <!-- Left Column: API Providers Table -->
        <div class="directives-table-container">
            <div class="tablenav top">
                <div class="alignleft actions">
                    <input type="search" id="api-search-input" name="s" value="<?php echo esc_attr(isset($_GET['s']) ? $_GET['s'] : ''); ?>" placeholder="<?php esc_attr_e('Search APIs...', 'tradepress'); ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search APIs', 'tradepress'); ?>">
                </div>
            </div>
            
            <div class="wp-list-table widefat fixed striped">
                <div class="table-header" style="display: flex; background: #f1f1f1; padding: 12px 15px; font-weight: 600; border-bottom: 1px solid #c3c4c7;">
                    <div style="flex: 2;"><?php _e('API Provider', 'tradepress'); ?></div>
                    <div style="flex: 1;"><?php _e('Status', 'tradepress'); ?></div>
                    <div style="flex: 1;"><?php _e('Type', 'tradepress'); ?></div>
                    <div style="flex: 1;"><?php _e('Trading Mode', 'tradepress'); ?></div>
                    <div style="flex: 1;"><?php _e('Rate Limit', 'tradepress'); ?></div>
                </div>
            </div>

            <div class="tradepress-compact-table">
                <?php 
                // Handle search filtering
                $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
                $filtered_providers = $all_providers;
                
                if (!empty($search_term)) {
                    $filtered_providers = array_filter($all_providers, function($provider, $id) use ($search_term) {
                        return stripos($provider['name'], $search_term) !== false || 
                               stripos($id, $search_term) !== false;
                    }, ARRAY_FILTER_USE_BOTH);
                }
                
                // Sort by status - OPERATIONAL first
                uksort($filtered_providers, function($a, $b) {
                    $status_a = get_option('TradePress_switch_' . $a . '_api_services', 'no');
                    $status_b = get_option('TradePress_switch_' . $b . '_api_services', 'no');
                    return ($status_b === 'yes') - ($status_a === 'yes');
                });
                
                foreach ($filtered_providers as $api_id => $provider): 
                    // Get API status
                    $api_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no');
                    $trading_mode = get_option('TradePress_api_' . $api_id . '_trading_mode', 'paper');
                    $is_data_only = isset($provider['api_type']) && $provider['api_type'] === 'data_only';
                    
                    // Get rate limit info
                    $rate_limit_count = get_option('tradepress_' . $api_id . '_rate_limit_count', 0);
                    $rate_limit_max = ($api_id === 'alphavantage') ? 25 : 60;
                    $rate_limit_status = ($rate_limit_count >= $rate_limit_max) ? 'Exceeded' : 'Normal';
                ?>
                    <div class="accordion-row">
                        <div class="accordion-header">
                            <div style="flex: 2;">
                                <strong><?php echo esc_html($provider['name']); ?></strong>
                            </div>
                            <div style="flex: 1;">
                                <span class="status-badge <?php echo $api_enabled === 'yes' ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $api_enabled === 'yes' ? 'Operational' : 'Disabled'; ?>
                                </span>
                            </div>
                            <div style="flex: 1;">
                                <span class="type-badge <?php echo $is_data_only ? 'type-data' : 'type-trading'; ?>">
                                    <?php echo $is_data_only ? 'Data Only' : 'Trading'; ?>
                                </span>
                            </div>
                            <div style="flex: 1;">
                                <?php if (!$is_data_only): ?>
                                    <span class="mode-badge mode-<?php echo esc_attr($trading_mode); ?>">
                                        <?php echo $trading_mode === 'live' ? 'Live' : 'Paper'; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #666;">N/A</span>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <span class="rate-limit-badge rate-normal">
                                    <?php echo esc_html($rate_limit_status); ?>
                                </span>
                            </div>
                        </div>
                        <div class="accordion-content">
                            <div class="api-meta">
                                <div>
                                    <strong>Description:</strong><br>
                                    <?php echo esc_html($provider['description'] ?? 'No description available'); ?>
                                </div>
                                <div>
                                    <strong>Website:</strong><br>
                                    <a href="<?php echo esc_url($provider['website'] ?? '#'); ?>" target="_blank">
                                        <?php echo esc_html($provider['website'] ?? 'Not available'); ?>
                                    </a>
                                </div>

                                <div>
                                    <strong>Rate Limiting:</strong><br>
                                    <span class="rate-limit-badge rate-<?php echo strtolower($rate_limit_status); ?>"><?php echo esc_html($rate_limit_status); ?></span> - <?php echo esc_html($rate_limit_count); ?>/<?php echo esc_html($rate_limit_max); ?> calls today
                                </div>
                            </div>
                            
                            <div class="api-actions">
                                <a href="<?php echo esc_url(add_query_arg('configure', $api_id)); ?>" class="button button-primary">
                                    <?php esc_html_e('Configure', 'tradepress'); ?>
                                </a>
                                <button type="button" class="button view-api-status" data-api="<?php echo esc_attr($api_id); ?>">
                                    <?php esc_html_e('Status Details', 'tradepress'); ?>
                                </button>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="test_api_nonce" value="<?php echo wp_create_nonce('tradepress_test_api'); ?>">
                                    <input type="hidden" name="action" value="test_api">
                                    <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                    <button type="submit" class="button">
                                        <?php esc_html_e('Call Test', 'tradepress'); ?>
                                    </button>
                                </form>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="test_api_nonce" value="<?php echo wp_create_nonce('tradepress_test_api'); ?>">
                                    <input type="hidden" name="action" value="query_test">
                                    <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                    <button type="submit" class="button">
                                        <?php esc_html_e('Query Test', 'tradepress'); ?>
                                    </button>
                                </form>
                                <?php if (!$is_data_only): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="trading_mode_nonce" value="<?php echo wp_create_nonce('tradepress_toggle_trading_mode'); ?>">
                                    <input type="hidden" name="action" value="toggle_trading_mode">
                                    <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                    <input type="hidden" name="new_mode" value="<?php echo $trading_mode === 'live' ? 'paper' : 'live'; ?>">
                                    <button type="submit" class="button">
                                        <?php echo $trading_mode === 'live' ? esc_html__('Switch to Paper', 'tradepress') : esc_html__('Switch to Live', 'tradepress'); ?>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="toggle_nonce" value="<?php echo wp_create_nonce('tradepress_toggle_api'); ?>">
                                    <input type="hidden" name="action" value="toggle_api_status">
                                    <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                    <input type="hidden" name="new_state" value="<?php echo $api_enabled === 'yes' ? '0' : '1'; ?>">
                                    <button type="submit" class="button">
                                        <?php echo $api_enabled === 'yes' ? esc_html__('Disable', 'tradepress') : esc_html__('Enable', 'tradepress'); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Column: API Information Panel -->
        <div class="directive-right-column">

            
            <!-- API Configuration Containers -->
            <?php 
            // Find first operational API as default
            $default_api = null;
            foreach ($all_providers as $api_id => $provider) {
                if (get_option('TradePress_switch_' . $api_id . '_api_services', 'no') === 'yes') {
                    $default_api = $api_id;
                    break;
                }
            }
            
            $configure_api = isset($_GET['configure']) ? sanitize_text_field($_GET['configure']) : $default_api;
            if (isset($all_providers[$configure_api])): 
                $api_provider = $all_providers[$configure_api];
                $is_data_only = isset($api_provider['api_type']) && $api_provider['api_type'] === 'data_only';
            ?>
                <?php 
                $api_id = $configure_api;
                
                // Get data settings variables for all APIs
                $update_frequency = get_option('tradepress_' . $api_id . '_update_frequency', 'daily');
                $data_retention = get_option('tradepress_' . $api_id . '_data_retention', '30');
                $data_priority = get_option('tradepress_' . $api_id . '_data_priority', 'normal');
                
                if ($is_data_only): 
                    // Data-only API configuration containers
                    $api_key_option_name = 'TradePress_api_' . $api_id . '_key';
                    if ($api_id === 'alphavantage') {
                        $api_key_option_name = 'tradepress_api_alphavantage_key';
                    }
                    $api_key = get_option($api_key_option_name, '');
                    $api_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no') === 'yes';
                ?>
                
                <!-- API Configuration Container -->
                <div class="directive-details-container">
                    <div class="directive-section">
                        <div class="section-header">
                            <h3><?php esc_html_e('API Configuration', 'tradepress'); ?></h3>
                        </div>
                        <div class="section-content">
                            <form method="post">
                                <?php wp_nonce_field('tradepress_save_' . $api_id . '_settings', 'tradepress_' . $api_id . '_nonce'); ?>
                                <input type="hidden" name="action" value="tradepress_save_<?php echo esc_attr($api_id); ?>_settings">
                                <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_enable_api">
                                                <?php esc_html_e('Enable API', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="checkbox" id="<?php echo esc_attr($api_id); ?>_enable_api" 
                                                   name="<?php echo esc_attr($api_id); ?>_enable_api" 
                                                   value="1" <?php checked(get_option('TradePress_switch_' . $api_id . '_api_services', 'no'), 'yes'); ?>>
                                            <label for="<?php echo esc_attr($api_id); ?>_enable_api"><?php esc_html_e('Enable this API for use.', 'tradepress'); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_api_logging">
                                                <?php esc_html_e('API Logging', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="checkbox" id="<?php echo esc_attr($api_id); ?>_api_logging" 
                                                   name="<?php echo esc_attr($api_id); ?>_api_logging" 
                                                   value="1" <?php checked(get_option('tradepress_' . $api_id . '_api_logging', 'no'), 'yes'); ?>>
                                            <label for="<?php echo esc_attr($api_id); ?>_api_logging"><?php esc_html_e('Log API Activity', 'tradepress'); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_premium_endpoints">
                                                <?php esc_html_e('Premium Endpoints', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="checkbox" id="<?php echo esc_attr($api_id); ?>_premium_endpoints" 
                                                   name="<?php echo esc_attr($api_id); ?>_premium_endpoints" 
                                                   value="1" <?php checked(get_option('tradepress_' . $api_id . '_premium_endpoints', 'no'), 'yes'); ?>>
                                            <label for="<?php echo esc_attr($api_id); ?>_premium_endpoints"><?php esc_html_e('Allow Premium Endpoints', 'tradepress'); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_key_option_name); ?>">
                                                <?php esc_html_e('API Key', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="password" id="<?php echo esc_attr($api_key_option_name); ?>" 
                                                   name="<?php echo esc_attr($api_key_option_name); ?>" 
                                                   value="<?php echo esc_attr($api_key); ?>" 
                                                   class="regular-text">
                                            <p class="description">
                                                <?php esc_html_e('Enter your API key for data access', 'tradepress'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="api-settings-actions">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e('Save Configuration', 'tradepress'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                

                
                <?php endif; ?>
                
                <!-- Data Settings Container - Standard for All APIs -->
                <div class="directive-details-container">
                    <div class="directive-section">
                        <div class="section-header">
                            <h3><?php esc_html_e('Data Settings', 'tradepress'); ?></h3>
                        </div>
                        <div class="section-content">
                            <form method="post">
                                <input type="hidden" name="<?php echo 'tradepress_' . $api_id . '_nonce'; ?>" value="<?php echo wp_create_nonce('tradepress_save_' . $api_id . '_settings'); ?>">
                                <input type="hidden" name="action" value="tradepress_save_<?php echo esc_attr($api_id); ?>_settings">
                                <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_update_frequency">
                                                <?php esc_html_e('Update Frequency', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <select id="<?php echo esc_attr($api_id); ?>_update_frequency" 
                                                    name="<?php echo esc_attr($api_id); ?>_update_frequency">
                                                <option value="hourly" <?php selected($update_frequency, 'hourly'); ?>>
                                                    <?php esc_html_e('Hourly', 'tradepress'); ?>
                                                </option>
                                                <option value="daily" <?php selected($update_frequency, 'daily'); ?>>
                                                    <?php esc_html_e('Daily', 'tradepress'); ?>
                                                </option>
                                                <option value="weekly" <?php selected($update_frequency, 'weekly'); ?>>
                                                    <?php esc_html_e('Weekly', 'tradepress'); ?>
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_data_retention">
                                                <?php esc_html_e('Data Retention (days)', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="number" id="<?php echo esc_attr($api_id); ?>_data_retention" 
                                                   name="<?php echo esc_attr($api_id); ?>_data_retention" 
                                                   value="<?php echo esc_attr($data_retention); ?>" 
                                                   class="small-text" min="1" max="365">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_data_priority">
                                                <?php esc_html_e('Data Priority', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <select id="<?php echo esc_attr($api_id); ?>_data_priority" 
                                                    name="<?php echo esc_attr($api_id); ?>_data_priority">
                                                <option value="high" <?php selected($data_priority, 'high'); ?>>
                                                    <?php esc_html_e('High - Preferred Source', 'tradepress'); ?>
                                                </option>
                                                <option value="normal" <?php selected($data_priority, 'normal'); ?>>
                                                    <?php esc_html_e('Normal', 'tradepress'); ?>
                                                </option>
                                                <option value="low" <?php selected($data_priority, 'low'); ?>>
                                                    <?php esc_html_e('Low - Fallback Only', 'tradepress'); ?>
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="api-settings-actions">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e('Save Settings', 'tradepress'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php if (!$is_data_only): 
                    // Trading API configuration containers
                    $api_key = get_option('tradepress_' . $api_id . '_api_key', '');
                    $api_secret = get_option('tradepress_' . $api_id . '_api_secret', '');
                    $paper_api_key = get_option('tradepress_' . $api_id . '_paper_api_key', '');
                    $paper_api_secret = get_option('tradepress_' . $api_id . '_paper_api_secret', '');
                    $trading_mode = get_option('tradepress_' . $api_id . '_trading_mode', 'paper');
                    $max_position_size = get_option('tradepress_' . $api_id . '_max_position_size', '5');
                    $stop_loss_percent = get_option('tradepress_' . $api_id . '_stop_loss_percent', '5');
                    $take_profit_percent = get_option('tradepress_' . $api_id . '_take_profit_percent', '10');
                ?>
                
                <!-- Live Trading Container -->
                <div class="directive-details-container">
                    <div class="directive-section">
                        <div class="section-header">
                            <h3><?php esc_html_e('Live Trading', 'tradepress'); ?></h3>
                        </div>
                        <div class="section-content">
                            <form method="post">
                                <input type="hidden" name="<?php echo 'tradepress_' . $api_id . '_nonce'; ?>" value="<?php echo wp_create_nonce('tradepress_save_' . $api_id . '_settings'); ?>">
                                <input type="hidden" name="action" value="tradepress_save_<?php echo esc_attr($api_id); ?>_settings">
                                <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_api_key">
                                                <?php esc_html_e('Real-Money API Key ID', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="password" id="<?php echo esc_attr($api_id); ?>_api_key" 
                                                   name="<?php echo esc_attr($api_id); ?>_api_key" 
                                                   value="<?php echo esc_attr($api_key); ?>" 
                                                   class="regular-text">
                                            <p class="description">
                                                <?php esc_html_e('Your API key ID for real money trading on ' . ucfirst($api_id) . '.', 'tradepress'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_api_secret">
                                                <?php esc_html_e('Real-Money API Secret Key', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="password" id="<?php echo esc_attr($api_id); ?>_api_secret" 
                                                   name="<?php echo esc_attr($api_id); ?>_api_secret" 
                                                   value="<?php echo esc_attr($api_secret); ?>" 
                                                   class="regular-text">
                                            <p class="description">
                                                <?php esc_html_e('Your API secret key for real money trading on ' . ucfirst($api_id) . '.', 'tradepress'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="api-settings-actions">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e('Save Live Credentials', 'tradepress'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Paper Trading Container -->
                <div class="directive-details-container">
                    <div class="directive-section">
                        <div class="section-header">
                            <h3><?php esc_html_e('Paper Trading', 'tradepress'); ?></h3>
                        </div>
                        <div class="section-content">
                            <form method="post">
                                <input type="hidden" name="<?php echo 'tradepress_' . $api_id . '_nonce'; ?>" value="<?php echo wp_create_nonce('tradepress_save_' . $api_id . '_settings'); ?>">
                                <input type="hidden" name="action" value="tradepress_save_<?php echo esc_attr($api_id); ?>_settings">
                                <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_paper_api_key">
                                                <?php esc_html_e('Paper-Money API Key ID', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="password" id="<?php echo esc_attr($api_id); ?>_paper_api_key" 
                                                   name="<?php echo esc_attr($api_id); ?>_paper_api_key" 
                                                   value="<?php echo esc_attr($paper_api_key); ?>" 
                                                   class="regular-text">
                                            <p class="description">
                                                <?php esc_html_e('Your API key ID for paper trading on ' . ucfirst($api_id) . '.', 'tradepress'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_paper_api_secret">
                                                <?php esc_html_e('Paper-Money API Secret Key', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="password" id="<?php echo esc_attr($api_id); ?>_paper_api_secret" 
                                                   name="<?php echo esc_attr($api_id); ?>_paper_api_secret" 
                                                   value="<?php echo esc_attr($paper_api_secret); ?>" 
                                                   class="regular-text">
                                            <p class="description">
                                                <?php esc_html_e('Your API secret key for paper trading on ' . ucfirst($api_id) . '.', 'tradepress'); ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="api-settings-actions">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e('Save Paper Credentials', 'tradepress'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Trading Rules Container -->
                <div class="directive-details-container">
                    <div class="directive-section">
                        <div class="section-header">
                            <h3><?php esc_html_e('Trading Rules', 'tradepress'); ?></h3>
                        </div>
                        <div class="section-content">
                            <form method="post">
                                <input type="hidden" name="<?php echo 'tradepress_' . $api_id . '_nonce'; ?>" value="<?php echo wp_create_nonce('tradepress_save_' . $api_id . '_settings'); ?>">
                                <input type="hidden" name="action" value="tradepress_save_<?php echo esc_attr($api_id); ?>_settings">
                                <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_max_position_size">
                                                <?php esc_html_e('Maximum Position Size (%)', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="number" id="<?php echo esc_attr($api_id); ?>_max_position_size" 
                                                   name="<?php echo esc_attr($api_id); ?>_max_position_size" 
                                                   value="<?php echo esc_attr($max_position_size); ?>" 
                                                   class="small-text" min="1" max="100" step="0.1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_stop_loss_percent">
                                                <?php esc_html_e('Default Stop Loss (%)', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="number" id="<?php echo esc_attr($api_id); ?>_stop_loss_percent" 
                                                   name="<?php echo esc_attr($api_id); ?>_stop_loss_percent" 
                                                   value="<?php echo esc_attr($stop_loss_percent); ?>" 
                                                   class="small-text" min="0.1" max="50" step="0.1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($api_id); ?>_take_profit_percent">
                                                <?php esc_html_e('Default Take Profit (%)', 'tradepress'); ?>
                                            </label>
                                        </th>
                                        <td>
                                            <input type="number" id="<?php echo esc_attr($api_id); ?>_take_profit_percent" 
                                                   name="<?php echo esc_attr($api_id); ?>_take_profit_percent" 
                                                   value="<?php echo esc_attr($take_profit_percent); ?>" 
                                                   class="small-text" min="0.1" max="100" step="0.1">
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="api-settings-actions">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e('Save Trading Rules', 'tradepress'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.tradepress-test-results {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 20px;
    margin: 15px 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.test-section {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}
.test-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}
.test-section h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 14px;
    font-weight: 600;
}
.background-activity .activity-item {
    background: #e3f2fd;
    padding: 6px 12px;
    margin: 4px 0;
    border-radius: 4px;
    font-size: 12px;
    border-left: 3px solid #2196f3;
}
.test-grid, .dev-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 8px;
    font-size: 13px;
}
.test-row, .dev-row {
    display: contents;
}
.test-row span:first-child, .dev-row span:first-child {
    font-weight: 600;
    color: #555;
}
.test-row span:last-child, .dev-row span:last-child {
    color: #333;
    word-break: break-all;
}
.status-success {
    color: #28a745;
    font-weight: bold;
}
.status-error {
    color: #dc3545;
    font-weight: bold;
}
.error-message {
    background: #fff5f5;
    border: 1px solid #fed7d7;
    border-radius: 4px;
    padding: 12px;
}
.error-content {
    color: #c53030;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.4;
}
.ai-report-textarea {
    width: 100%;
    height: 200px;
    font-family: monospace;
    font-size: 12px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f8f9fa;
    resize: vertical;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Search functionality
    $('#api-search-input').on('keyup', function(e) {
        if (e.keyCode === 13) { // Enter key
            performSearch();
        }
    });
    
    $('#search-submit').on('click', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    function performSearch() {
        var searchTerm = $('#api-search-input').val();
        var url = new URL(window.location);
        if (searchTerm) {
            url.searchParams.set('s', searchTerm);
        } else {
            url.searchParams.delete('s');
        }
        window.location.href = url.toString();
    }
    
    // Accordion functionality with URL update
    $('.accordion-header').on('click', function() {
        var $content = $(this).next('.accordion-content');
        var isActive = $content.hasClass('active');
        
        // Extract API key from configure link
        var configureLink = $(this).closest('.accordion-row').find('a[href*="configure="]');
        var apiKey = '';
        if (configureLink.length > 0) {
            var href = configureLink.attr('href');
            var match = href.match(/configure=([^&]+)/);
            if (match) {
                apiKey = match[1];
            }
        }
        
        // Close all accordions
        $('.accordion-content').removeClass('active').slideUp();
        $('.accordion-header').removeClass('active');
        
        // Open clicked accordion if it wasn't active and update URL
        if (!isActive && apiKey) {
            $content.addClass('active').slideDown();
            $(this).addClass('active');
            
            // Update URL with configure parameter
            var url = new URL(window.location);
            url.searchParams.set('configure', apiKey);
            window.location.href = url.toString();
        }
    });
    
    // Status details button
    $('.view-api-status').on('click', function() {
        var apiId = $(this).data('api');
        alert('Status details for ' + apiId + ' - This will be enhanced in Phase 2');
    });
    
    // Check for selected API from URL and open accordion
    var urlParams = new URLSearchParams(window.location.search);
    var selectedApi = urlParams.get('configure');
    
    if (selectedApi) {
        $('.accordion-row').each(function() {
            var configureLink = $(this).find('a[href*="configure=' + selectedApi + '"]');
            if (configureLink.length > 0) {
                $(this).find('.accordion-content').addClass('active').show();
                $(this).find('.accordion-header').addClass('active');
            }
        });
    }
});
</script>