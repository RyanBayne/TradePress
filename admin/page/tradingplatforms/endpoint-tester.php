<?php
/**
 * TradePress Endpoint Tester
 * 
 * Handles testing of API endpoints for different trading platforms.
 * 
 * @package TradePress
 * @subpackage admin/page/TradingPlatforms
 * @version 1.1.4
 * @since 1.0.0
 * @created 2025-05-05 13:15:00
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Endpoint_Tester {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_tradepress_test_endpoint', array($this, 'ajax_test_endpoint'));
        
        // Process standard POST requests for endpoint testing (non-Ajax method)
        if (isset($_POST['tradepress_test_endpoint'])) {
            // Log to debug.log for debugging purposes
            if (function_exists('error_log')) {
                error_log('Standard POST submission for endpoint test received: ' . print_r($_POST, true));
            }
            
            // Check for valid nonce - important fix: look for any field that starts with "tradepress_test_endpoint_nonce_"
            $nonce_found = false;
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'tradepress_test_endpoint_nonce_') === 0) {
                    $nonce_found = true;
                    // Verify this nonce against our standard action
                    if (wp_verify_nonce($value, 'tradepress_test_endpoint_nonce')) {
                        // Process the test request directly (not through Ajax)
                        $this->process_test_request();
                    }
                    break;
                }
            }
            
            // Log if no valid nonce was found
            if (!$nonce_found && function_exists('error_log')) {
                error_log('No valid nonce found for endpoint test');
            }
        }
    }
    
    /**
     * AJAX handler for testing endpoints
     */
    public function ajax_test_endpoint() {
        // Verify nonce
        check_ajax_referer('tradepress_test_endpoint_nonce', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tradepress')));
        }
        
        // Get request parameters
        $endpoint_id = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
        $platform_id = isset($_POST['platform']) ? sanitize_text_field($_POST['platform']) : '';
        
        // Validate input
        if (empty($endpoint_id) || empty($platform_id)) {
            wp_send_json_error(array('message' => __('Missing required parameters.', 'tradepress')));
        }
        
        // Get the endpoint details
        $endpoint_details = $this->get_endpoint_details($endpoint_id, $platform_id);
        
        // Test the endpoint
        $result = $this->test_endpoint($endpoint_details, $platform_id);
        
        if (is_wp_error($result)) {
            // Format error report for display
            $error_report = $this->generate_error_report($endpoint_details, $result, $platform_id);
            wp_send_json_error(array('message' => $error_report));
        } else {
            // Return successful response
            wp_send_json_success(array(
                'message' => __('Endpoint test completed successfully!', 'tradepress'),
                'data' => $result,
                'endpoint' => $endpoint_id,
                'platform' => $platform_id
            ));
        }
    }
    
    /**
     * Process a test request directly (non-Ajax)
     */
    private function process_test_request() {
        // Get parameters from POST data
        $endpoint_id = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
        $endpoint_key = isset($_POST['endpoint_key']) ? sanitize_text_field($_POST['endpoint_key']) : '';
        $platform_id = isset($_POST['platform']) ? sanitize_text_field($_POST['platform']) : '';
        
        if (empty($endpoint_id) || empty($platform_id)) {
            // Log the error for debugging
            if (function_exists('error_log')) {
                error_log('Missing required parameters for endpoint test: ' . 
                          'endpoint=' . $endpoint_id . ', platform=' . $platform_id);
            }
            return;
        }
        
        // Get the endpoint details
        $endpoint_details = $this->get_endpoint_details($endpoint_id, $platform_id);
        
        // Test the endpoint
        $test_result = $this->test_endpoint($endpoint_details, $platform_id);
        
        // Store results in transient for the page to display
        $transient_key = 'tradepress_endpoint_test_' . md5($platform_id . '_' . $endpoint_id);
        
        // Determine correct environment value
        $is_demo = false;
        if (function_exists('is_demo_mode')) {
            $is_demo = is_demo_mode();
        } else {
            $is_demo = get_option('TradePress_demo_mode', 'no') === 'yes' || 
                      (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE);
        }
        $environment = $is_demo ? 'Demo' : 'Live';
        
        if (is_wp_error($test_result)) {
            // Generate enhanced error report
            $detailed_error = $this->generate_error_report($endpoint_details, $test_result, $platform_id);
            
            $results = array(
                'success' => false,
                'message' => $test_result->get_error_message(),
                'error_code' => $test_result->get_error_code(),
                'raw_response' => $test_result->get_error_data(),
                'error_report' => $detailed_error,
                'platform' => $platform_id,
                'endpoint' => $endpoint_id,
                'endpoint_key' => $endpoint_key,
                'timestamp' => current_time('mysql'),
                'debug_timestamp' => microtime(true),
                'status_code' => $test_result->get_error_code(),
                'environment' => $environment
            );
        } else {
            $results = array(
                'success' => true,
                'message' => __('Endpoint test completed successfully!', 'tradepress'),
                'data' => $test_result,
                'raw_response' => isset($test_result['raw_response']) ? $test_result['raw_response'] : $test_result,
                'platform' => $platform_id,
                'endpoint' => $endpoint_id,
                'endpoint_key' => $endpoint_key,
                'timestamp' => current_time('mysql'),
                'debug_timestamp' => microtime(true),
                'status_code' => 200,
                'environment' => $environment
            );
        }
        
        // Save the results to transients for retrieval on page reload
        set_transient($transient_key, $results, HOUR_IN_SECONDS);
        
        // Also store a reference for which test was last run so the page knows which results to show
        $last_test = array(
            'platform' => $platform_id,
            'endpoint' => $endpoint_id,
            'endpoint_key' => $endpoint_key,
            'timestamp' => current_time('mysql')
        );
        set_transient('tradepress_last_endpoint_test', $last_test, HOUR_IN_SECONDS);
        
        // Log successful test for debugging
        if (function_exists('error_log')) {
            error_log('Endpoint test processed and stored in transient: ' . $transient_key);
        }
    }
    
    /**
     * Get endpoint details by ID and platform
     * 
     * @param string $endpoint_id The endpoint ID
     * @param string $platform_id The platform ID
     * @return array|false Endpoint details or false if not found
     */
    private function get_endpoint_details($endpoint_id, $platform_id) {
        // For now, return a simple placeholder. In a full implementation,
        // this would retrieve data from the actual endpoint registry
        
        // Standard endpoint details
        $endpoint = array(
            'id' => $endpoint_id,
            'platform' => $platform_id,
            'method' => 'GET',
            'path' => "/{$endpoint_id}",
            'description' => __('API Endpoint', 'tradepress'),
            'parameters' => array(),
            'version' => 'v1'
        );
        
        // Enhance with platform-specific details if available
        switch ($platform_id) {
            case 'alphavantage':
                if ($endpoint_id === 'TIME_SERIES_INTRADAY') {
                    $endpoint['path'] = 'query?function=TIME_SERIES_INTRADAY';
                    $endpoint['description'] = __('Intraday time series (timestamp, open, high, low, close, volume)', 'tradepress');
                    $endpoint['parameters'] = array('symbol', 'interval', 'outputsize', 'datatype');
                }
                break;
                
            case 'alpaca':
                // Add Alpaca-specific endpoint details
                break;
        }
        
        return $endpoint;
    }
    
    /**
     * Test an endpoint
     * 
     * @param array $endpoint Endpoint details
     * @param string $platform_id The platform ID
     * @return array|WP_Error Test result or error
     */
    public function test_endpoint($endpoint, $platform_id) {
        // Get provider details to determine API type
        $provider_info = $this->get_platform_info($platform_id);
        $api_type = isset($provider_info['api_type']) ? $provider_info['api_type'] : 'trading';
        
        // Verify API configuration
        $config_check = $this->verify_api_configuration($platform_id, $api_type);
        
        // Check if we have valid credentials
        if (!$config_check['configured']) {
            $message = sprintf(
                __('API credentials are not configured. Missing: %s', 'tradepress'),
                implode(', ', $config_check['missing'])
            );
            
            // Add more detailed information for troubleshooting
            $details = array(
                'platform' => $platform_id,
                'api_type' => $api_type,
                'missing_credentials' => $config_check['missing'],
                'option_names' => $config_check['option_names']
            );
            
            if (!empty($config_check['suggestions'])) {
                $details['suggestions'] = $config_check['suggestions'];
            }
            
            if (!empty($config_check['settings_page'])) {
                $details['settings_page'] = $config_check['settings_page'];
            }
            
            return new WP_Error( 'missing_credentials',
                $message,
                $details
            );
        }
        
        // Get API credentials
        $api_credentials = $this->get_api_credentials($platform_id, $api_type);
        
        // Check if we're in demo mode
        $is_demo = false;
        if (function_exists('is_demo_mode')) {
            $is_demo = is_demo_mode();
        } else {
            $is_demo = get_option('TradePress_demo_mode', 'no') === 'yes' || 
                      (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE);
        }
        
        if ($is_demo) {
            // Generate demo responses based on platform and endpoint
            switch ($platform_id) {
                case 'alphavantage':
                    if ($endpoint['id'] === 'TIME_SERIES_INTRADAY') {
                        // For successful responses
                        return $this->generate_mock_response($endpoint);
                    }
                    
                    // For other Alpha Vantage endpoints, return a mock success response
                    return $this->generate_mock_response($endpoint);
                    
                default:
                    // Return a generic mock response for other platforms
                    return $this->generate_mock_response($endpoint);
            }
        } else {
            // In a real implementation, make the actual API call here
            // This would use the appropriate API client class for the platform
            
            // Example API call (not fully implemented for all platforms):
            return $this->make_api_request($platform_id, $endpoint, $api_credentials);
        }
    }
    
    /**
     * Verify API configuration is complete
     * 
     * @param string $platform_id The platform ID
     * @param string $api_type The API type (trading or data_only)
     * @return array Configuration status
     */
    private function verify_api_configuration($platform_id, $api_type) {
        $result = array(
            'configured' => false,
            'missing' => array(),
            'option_names' => array(),
            'suggestions' => array()
        );
        
        // Check configuration based on API type
        if ($api_type === 'trading') {
            $trading_mode = get_option("TradePress_api_{$platform_id}_trading_mode", 'paper');
            
            if ($trading_mode === 'paper') {
                $api_key = get_option("TradePress_api_{$platform_id}_papermoney_apikey", '');
                $api_secret = get_option("TradePress_api_{$platform_id}_papermoney_secretkey", '');
                
                $result['option_names'][] = "TradePress_api_{$platform_id}_papermoney_apikey";
                $result['option_names'][] = "TradePress_api_{$platform_id}_papermoney_secretkey";
                
                if (empty($api_key)) {
                    $result['missing'][] = 'Paper Trading API Key';
                }
                
                if (empty($api_secret)) {
                    $result['missing'][] = 'Paper Trading Secret Key';
                }
            } else {
                $api_key = get_option("TradePress_api_{$platform_id}_realmoney_apikey", '');
                $api_secret = get_option("TradePress_api_{$platform_id}_realmoney_secretkey", '');
                
                $result['option_names'][] = "TradePress_api_{$platform_id}_realmoney_apikey";
                $result['option_names'][] = "TradePress_api_{$platform_id}_realmoney_secretkey";
                
                if (empty($api_key)) {
                    $result['missing'][] = 'Live Trading API Key';
                }
                
                if (empty($api_secret)) {
                    $result['missing'][] = 'Live Trading Secret Key';
                }
            }
            
            $result['settings_page'] = admin_url('admin.php?page=tradepress_platforms&tab=' . $platform_id);
            $result['suggestions'][] = sprintf(__('Configure API credentials in %s settings', 'tradepress'), $platform_id);
            
        } else if ($api_type === 'data_only') {
            // For data-only APIs, check for API key in multiple possible option formats
            $option_names = array(
                "TradePress_api_{$platform_id}_key",
                "TradePress_{$platform_id}_api_key",
                "tradepress_{$platform_id}_api_key",
                "TradePress_api_{$platform_id}_apikey",
                "TradePress_alphavantage_api_key" // Special case for Alpha Vantage
            );
            
            $api_key = '';
            foreach ($option_names as $option_name) {
                $option_value = get_option($option_name, '');
                if (!empty($option_value)) {
                    $api_key = $option_value;
                    break;
                }
                $result['option_names'][] = $option_name;
            }
            
            if (empty($api_key)) {
                $result['missing'][] = 'API Key';
                $result['settings_page'] = admin_url('admin.php?page=tradepress_platforms&tab=' . $platform_id);
                $result['suggestions'][] = sprintf(__('Configure API credentials in %s settings', 'tradepress'), $platform_id);
            }
        }
        
        // If no missing credentials, then configuration is good
        $result['configured'] = empty($result['missing']);
        
        return $result;
    }
    
    /**
     * Get API credentials for a platform
     * 
     * @param string $platform_id The platform ID
     * @param string $api_type The API type (trading or data_only)
     * @return array API credentials
     */
    private function get_api_credentials($platform_id, $api_type) {
        $credentials = array(
            'api_key' => '',
            'api_secret' => '',
            'mode' => 'paper'
        );
        
        // Get trading mode for trading platforms
        if ($api_type === 'trading') {
            $trading_mode = get_option("TradePress_api_{$platform_id}_trading_mode", 'paper');
            $credentials['mode'] = $trading_mode;
            
            // Get API keys based on trading mode
            if ($trading_mode === 'paper') {
                $credentials['api_key'] = get_option("TradePress_api_{$platform_id}_papermoney_apikey", '');
                $credentials['api_secret'] = get_option("TradePress_api_{$platform_id}_papermoney_secretkey", '');
            } else {
                $credentials['api_key'] = get_option("TradePress_api_{$platform_id}_realmoney_apikey", '');
                $credentials['api_secret'] = get_option("TradePress_api_{$platform_id}_realmoney_secretkey", '');
            }
        } 
        // For data-only APIs, there's usually just one API key setting
        else if ($api_type === 'data_only') {
            // Different platforms may store API keys in different option names
            switch ($platform_id) {
                case 'alphavantage':
                    // Check both possible option name formats for Alpha Vantage API key
                    $credentials['api_key'] = get_option('tradepress_api_alphavantage_key', '');
                    if (empty($credentials['api_key'])) {
                        // Try alternative option name format
                        $credentials['api_key'] = get_option('TradePress_alphavantage_api_key', '');
                    }
                    if (empty($credentials['api_key'])) {
                        // Try another common format
                        $credentials['api_key'] = get_option('tradepress_alphavantage_api_key', '');
                    }
                    break;
                case 'twelvedata':
                    $credentials['api_key'] = get_option('TradePress_api_twelvedata_key', '');
                    break;
                case 'finnhub':
                    $credentials['api_key'] = get_option('TradePress_api_finnhub_key', '');
                    break;
                default:
                    // Generic fallback for other data APIs
                    $credentials['api_key'] = get_option("TradePress_api_{$platform_id}_key", '');
                    $credentials['api_secret'] = get_option("TradePress_api_{$platform_id}_secret", '');
                    
                    // Try alternative option name formats if the primary option is empty
                    if (empty($credentials['api_key'])) {
                        $credentials['api_key'] = get_option("TradePress_{$platform_id}_api_key", '');
                    }
                    if (empty($credentials['api_key'])) {
                        $credentials['api_key'] = get_option("tradepress_{$platform_id}_api_key", '');
                    }
                    break;
            }
        }
        
        return $credentials;
    }
    
    /**
     * Get platform information
     * 
     * @param string $platform_id The platform ID
     * @return array Platform information
     */
    private function get_platform_info($platform_id) {
        // Default platform info
        $platform_info = array(
            'api_type' => 'trading',
            'name' => ucfirst($platform_id),
            'supports_paper_trading' => true
        );
        
        // Data-only APIs
        $data_only_apis = array('alphavantage', 'twelvedata', 'finnhub', 'iexcloud', 'marketstack', 'fmp');
        if (in_array($platform_id, $data_only_apis)) {
            $platform_info['api_type'] = 'data_only';
            $platform_info['supports_paper_trading'] = false;
        }
        
        // Trading platforms may have specific settings
        switch ($platform_id) {
            case 'alphavantage': 
                $platform_info['name'] = 'Alpha Vantage';
                break;
            case 'iexcloud':
                $platform_info['name'] = 'IEX Cloud';
                break;
        }
        
        // Try to get platform info from API Directory class if it exists
        if (class_exists('TradePress_API_Directory')) {
            $provider = TradePress_API_Directory::get_provider($platform_id);
            if (!empty($provider)) {
                $platform_info = wp_parse_args($provider, $platform_info);
            }
        }
        
        return $platform_info;
    }
    
    /**
     * Make an actual API request
     * 
     * @param string $platform_id The platform ID
     * @param array $endpoint The endpoint details
     * @param array $credentials API credentials
     * @return array|WP_Error API response or error
     */
    private function make_api_request($platform_id, $endpoint, $credentials) {
        // This is a simplified implementation for demonstration
        // In a real implementation, this would use the appropriate API client class
        
        // Check if credentials are available
        if (empty($credentials['api_key'])) {
            return new WP_Error(
                'missing_credentials', 
                __('API credentials are not configured', 'tradepress')
            );
        }
        
        // Build the API URL based on platform and endpoint
        $api_url = $this->build_api_url($platform_id, $endpoint, $credentials);
        
        // Make the API request
        $response = wp_remote_get($api_url, array(
            'timeout' => 15,
            'headers' => $this->get_request_headers($platform_id, $credentials)
        ));
        
        // Handle API response
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Check for HTTP errors
        if ($response_code >= 400) {
            return new WP_Error(
                'api_error',
                sprintf(__('API error: %s', 'tradepress'), $response_code),
                $response_body
            );
        }
        
        // Parse JSON response
        $data = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'invalid_response',
                __('Invalid API response format', 'tradepress'),
                $response_body
            );
        }
        
        return $data;
    }
    
    /**
     * Build API URL based on platform and endpoint
     * 
     * @param string $platform_id The platform ID
     * @param array $endpoint The endpoint details
     * @param array $credentials API credentials
     * @return string The API URL
     */
    private function build_api_url($platform_id, $endpoint, $credentials) {
        // Base URLs for different platforms
        $base_urls = array(
            'alphavantage' => 'https://www.alphavantage.co/query', 
            'alpaca' => $credentials['mode'] === 'paper'  
                ? 'https://paper-api.alpaca.markets/v2'  
                : 'https://api.alpaca.markets/v2',
            'twelvedata' => 'https://api.twelvedata.com',
            'finnhub' => 'https://finnhub.io/api/v1'
        );
        
        $base_url = isset($base_urls[$platform_id]) ? $base_urls[$platform_id] : '';
        
        // For Alpha Vantage, we need to construct the URL with function and API key
        if ($platform_id === 'alphavantage') {
            return add_query_arg(array(
                'function' => $endpoint['id'],
                'symbol' => isset($endpoint['parameters']['symbol']) ? $endpoint['parameters']['symbol'] : 'MSFT', // Default symbol for testing
                'interval' => '5min', // Default interval for testing
                'apikey' => $credentials['api_key']
            ), $base_url);
        }
        
        // For other platforms, combine base URL with endpoint path
        return trailingslashit($base_url) . ltrim($endpoint['path'], '/');
    }
    
    /**
     * Get request headers for API call
     * 
     * @param string $platform_id The platform ID
     * @param array $credentials API credentials
     * @return array Request headers
     */
    private function get_request_headers($platform_id, $credentials) {
        $headers = array();
        
        switch ($platform_id) {
            case 'alpaca':
                $headers['APCA-API-KEY-ID'] = $credentials['api_key'];
                $headers['APCA-API-SECRET-KEY'] = $credentials['api_secret'];
                break;
                
            case 'finnhub':
                $headers['X-Finnhub-Token'] = $credentials['api_key'];
                break;
                
            case 'iexcloud':
                // IEX Cloud uses token in URL, not headers
                break;
        }
        
        return $headers;
    }
    
    /**
     * Generate a mock API response for testing
     * 
     * @param array $endpoint Endpoint details
     * @return array Mock response
     */
    private function generate_mock_response($endpoint) {
        // Generate a mock response based on the endpoint
        $timestamp = date('Y-m-d\TH:i:s.000\Z');
        
        switch ($endpoint['platform']) {
            case 'alphavantage':
                return array(
                    'Meta Data' => array(
                        '1. Information' => 'Intraday (5min) open, high, low, close prices and volume',
                        '2. Symbol' => 'MSFT',
                        '3. Last Refreshed' => $timestamp,
                        '4. Interval' => '5min',
                        '5. Output Size' => 'Compact',
                        '6. Time Zone' => 'US/Eastern'
                    ),
                    'Time Series (5min)' => array(
                        $timestamp => array(
                            '1. open' => '333.9999',
                            '2. high' => '321.9999',
                            '3. low' => '123.9999',
                            '4. close' => '666.9999',
                            '5. volume' => '12345'
                        )
                    )
                );
                
            case 'alpaca':
                return array(
                    'id' => md5(time()),
                    'symbol' => 'AAPL',
                    'exchange' => 'NASDAQ',
                    'class' => 'us_equity',
                    'status' => 'active',
                    'tradable' => true,
                    'marginable' => true,
                    'shortable' => true,
                    'easy_to_borrow' => true
                );
                
            default:
                // Generic response
                return array(
                    'status' => 'success',
                    'timestamp' => time(),
                    'data' => array(
                        'message' => 'Mock response for ' . $endpoint['id']
                    )
                );
        }
    }
    
    /**
     * Generate an error report for a failed API test
     * 
     * @param array $endpoint Endpoint details
     * @param WP_Error $error The error
     * @param string $platform_id The platform ID
     * @return string Formatted error report
     */
    private function generate_error_report($endpoint, $error, $platform_id) {
        // Format platform display name
        $platform_info = $this->get_platform_info($platform_id);
        $platform_name = $platform_info['name'];
        
        // Get trading mode based on platform type
        $trading_mode = 'live';
        if ($platform_info['api_type'] === 'trading' && $platform_info['supports_paper_trading']) {
            $trading_mode = get_option("TradePress_api_{$platform_id}_trading_mode", 'paper');
        } else if ($platform_info['api_type'] === 'data_only') {
            $trading_mode = 'paper'; // Default for display purposes
        }
        
        // Determine correct environment value
        $is_demo = false;
        if (function_exists('is_demo_mode')) {
            $is_demo = is_demo_mode();
        } else {
            $is_demo = get_option('TradePress_demo_mode', 'no') === 'yes' || 
                      (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE);
        }
        $environment = $is_demo ? 'Demo' : 'Live';
        
        // Build the error report
        $report = "### API Test Error Report ###\n";
        $report .= "Platform: " . esc_html($platform_name) . "\n";
        $report .= "Endpoint: " . esc_html($endpoint['id']) . "\n";
        $report .= "Status: Connection Error\n";
        $report .= "Environment: " . $environment . "\n";
        $report .= "Trading Mode: " . esc_html($trading_mode) . "\n";
        $report .= "API Version: " . (isset($endpoint['version']) ? $endpoint['version'] : 'v2') . "\n";
        $report .= "Time: " . current_time('d/m/Y, H:i:s') . "\n";
        $report .= "Error: " . $error->get_error_message() . " [DEBUG: " . date('Y-m-d\TH:i:s', time()) . "+00:00]\n\n";
        
        // Add additional error details
        $report .= "Error Details: \n";
        if ($error->get_error_data()) {
            $error_data = $error->get_error_data();
            if (is_string($error_data)) {
                $raw_response = $error_data;
            } else {
                $raw_response = json_encode($error_data, JSON_PRETTY_PRINT);
            }
        } else {
            $raw_response = '';
        }
        $report .= "Raw Response Size: " . strlen($raw_response) . " characters\n";
        $report .= "            " . wp_html_excerpt($raw_response, 1000, '...') . "\n";
        
        // Add diagnostic file and class information
        $report .= "\nDiagnostic Information for AI Analysis:\n";
        $report .= "Current Class: TradePress_Endpoint_Tester\n";
        $report .= "Current File: admin/page/tradingplatforms/endpoint-tester.php\n";
        
        // Add API configuration files
        $report .= "\nAPI Configuration Files:\n";
        if ($platform_info['api_type'] === 'data_only') {
            $report .= "- admin/page/tradingplatforms/view/partials/config-data-only.php\n";
            $report .= "- includes/api/{$platform_id}/{$platform_id}-direct.php\n";
        } else {
            $report .= "- admin/page/tradingplatforms/view/template.api-tab.php\n";
            $report .= "- includes/api/{$platform_id}/{$platform_id}-api.php\n";
        }
        
        // Add settings information
        $report .= "\nRelated Settings Options:\n";
        if ($platform_info['api_type'] === 'data_only') {
            $report .= "- TradePress_api_{$platform_id}_key: " . (empty(get_option("TradePress_api_{$platform_id}_key")) ? "Not configured" : "Configured") . "\n";
        } else {
            if ($trading_mode === 'paper') {
                $report .= "- TradePress_api_{$platform_id}_papermoney_apikey: " . (empty(get_option("TradePress_api_{$platform_id}_papermoney_apikey")) ? "Not configured" : "Configured") . "\n";
                $report .= "- TradePress_api_{$platform_id}_papermoney_secretkey: " . (empty(get_option("TradePress_api_{$platform_id}_papermoney_secretkey")) ? "Not configured" : "Configured") . "\n";
            } else {
                $report .= "- TradePress_api_{$platform_id}_realmoney_apikey: " . (empty(get_option("TradePress_api_{$platform_id}_realmoney_apikey")) ? "Not configured" : "Configured") . "\n";
                $report .= "- TradePress_api_{$platform_id}_realmoney_secretkey: " . (empty(get_option("TradePress_api_{$platform_id}_realmoney_secretkey")) ? "Not configured" : "Configured") . "\n";
            }
        }
        
        // Get simplified stack trace (focused on plugin files)
        $stack_trace = $this->get_simplified_stack_trace();
        if (!empty($stack_trace)) {
            $report .= "\nStack Trace (Plugin Files Only):\n";
            $report .= $stack_trace;
        }
        
        // Add API request parameters (if available)
        $request_params = $this->get_api_request_params($endpoint, $platform_id);
        if (!empty($request_params)) {
            $report .= "\nAPI Request Parameters:\n";
            $report .= $request_params;
        }
        
        // Add AI analysis suggestions
        $report .= "\nAI Debugging Suggestions:\n";
        
        // Different suggestions based on error types
        if (strpos($error->get_error_message(), 'credentials') !== false) {
            $report .= "1. Examine the credential retrieval in get_api_credentials() method\n";
            $report .= "2. Check if the platform requires special credential handling\n";
            $report .= "3. Verify proper option names for storing {$platform_id} credentials\n";
            $report .= "4. Review platform_info API type detection: " . $platform_info['api_type'] . "\n";
        } else if (strpos($error->get_error_message(), 'connection') !== false) {
            $report .= "1. Check build_api_url() for proper URL construction\n";
            $report .= "2. Review the get_request_headers() method for proper header setup\n";
            $report .= "3. Examine network connectivity issues or firewall settings\n";
        } else {
            $report .= "1. Check the test_endpoint() method implementation\n";
            $report .= "2. Review error handling in make_api_request()\n";
            $report .= "3. Examine the API response format requirements\n";
        }
        
        // Add common troubleshooting steps
        $report .= "\nTroubleshooting Steps:\n";
        switch ($platform_id) {            
            case 'alphavantage':
                $report .= "1. Verify that your Alpha Vantage API key is configured properly\n";
                $report .= "2. Check if you've exceeded your API call limits (typically 5 calls per minute, 500 per day for free tier)\n";
                $report .= "3. Visit Data API settings to configure your Alpha Vantage API key\n";
                $report .= "4. For TIME_SERIES_INTRADAY, make sure required parameters (symbol, interval) are valid\n";
                break;
            case 'alpaca':
                $report .= "1. Check that both API key ID and secret key are correct\n";
                $report .= "2. Verify you're using the correct URL (paper/live trading)\n";
                $report .= "3. Ensure your account has the necessary permissions\n";
                $report .= "4. Try switching between paper and live trading modes\n";
                break;
            default:
                $report .= "1. Verify that your API credentials are configured correctly\n";
                $report .= "2. Check if you've exceeded your API rate limits\n";
                $report .= "3. Ensure the API service is operational\n";
                $report .= "4. Check network connectivity to the API service\n";
        }
        
        return $report;
    }
    
    /**
     * Get a simplified stack trace focused on plugin files
     * 
     * @return string Formatted stack trace
     */
    private function get_simplified_stack_trace() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $plugin_trace = array();
        $plugin_path = plugin_dir_path(dirname(dirname(dirname(__FILE__))));
        
        foreach ($trace as $i => $call) {
            if (isset($call['file']) && strpos($call['file'], $plugin_path) !== false) {
                $rel_path = str_replace($plugin_path, '', $call['file']);
                $plugin_trace[] = "#{$i}: {$rel_path}:{$call['line']} - " . 
                    (isset($call['class']) ? $call['class'] . $call['type'] : '') . 
                    $call['function'] . '()';
                
                // Limit to 5 entries for readability
                if (count($plugin_trace) >= 5) {
                    break;
                }
            }
        }
        
        return !empty($plugin_trace) ? implode("\n", $plugin_trace) : '';
    }
    
    /**
     * Get API request parameters for diagnostics
     * 
     * @param array $endpoint The endpoint details
     * @param string $platform_id The platform ID
     * @return string Formatted request parameters
     */
    private function get_api_request_params($endpoint, $platform_id) {
        $params = array();
        
        // Get credentials for current mode to build mock request
        $provider_info = $this->get_platform_info($platform_id);
        $api_credentials = $this->get_api_credentials($platform_id, $provider_info['api_type']);
                
        // For Alpha Vantage, show typical request parameters
        if ($platform_id === 'alphavantage') {
            $params[] = "function: " . $endpoint['id'];
            $params[] = "symbol: MSFT (default test symbol)";
            $params[] = "interval: 5min (default test interval)";
            $params[] = "apikey: " . (empty($api_credentials['api_key']) ? "NOT CONFIGURED" : "[API KEY CONFIGURED]");
            $params[] = "URL: " . $this->build_api_url($platform_id, $endpoint, $api_credentials);
        } 
        // For Alpaca, show typical headers and endpoint
        else if ($platform_id === 'alpaca') {
            $params[] = "Headers:";
            $params[] = "  APCA-API-KEY-ID: " . (empty($api_credentials['api_key']) ? "NOT CONFIGURED" : "[API KEY CONFIGURED]");
            $params[] = "  APCA-API-SECRET-KEY: " . (empty($api_credentials['api_secret']) ? "NOT CONFIGURED" : "[API SECRET CONFIGURED]");
            $params[] = "URL: " . $this->build_api_url($platform_id, $endpoint, $api_credentials);
        }
        // Generic parameters
        else {
            $params[] = "URL: " . $this->build_api_url($platform_id, $endpoint, $api_credentials);
            $params[] = "API Key Status: " . (empty($api_credentials['api_key']) ? "NOT CONFIGURED" : "CONFIGURED");
        }
        
        return !empty($params) ? implode("\n", $params) : '';
    }
}

// Initialize the endpoint tester
new TradePress_Endpoint_Tester();
