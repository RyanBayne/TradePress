<?php
/**
 * TradePress API Test Handler
 *
 * Handles API test requests and records comprehensive diagnostic information
 *
 * @since      1.0.0
 * @package    TradePress
 * @author     GitHub Copilot
 * @version    1.1.0 (Updated on April 18, 2025)
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test connection to Alpaca API
 * 
 * Performs a basic connection test to verify API credentials and connectivity
 * 
 * @param string $environment Optional. The environment to test ('demo' or 'live'). Default 'demo'.
 * @return array Connection test results with status and details
 * @since 1.1.0
 */
function tradepress_test_alpaca_connection($environment = 'demo') {
    // Initialize result array
    $result = array(
        'success' => false,
        'message' => '',
        'data' => array(),
        'log_id' => 0
    );
    
    // Get API credentials
    $api_key = get_option('tradepress_alpaca_key', '');
    $api_secret = get_option('tradepress_alpaca_secret', '');
    
    // Get trading mode setting
    $trading_mode = get_option('tradepress_alpaca_paper_trading', 'yes') === 'yes' ? 'paper' : 'live';
    
    // Initialize the logger
    $logger = new TradePress_API_Logging();
    
    // Check for API credentials
    if (empty($api_key) || empty($api_secret)) {
        // Log the missing credentials
        $log_data = $logger->log_api_test(
            'alpaca',
            'connection_test',
            $environment,
            $trading_mode,
            null,
            'error',
            'API credentials are not configured'
        );
        
        $result['message'] = 'API credentials are not configured';
        $result['log_id'] = $log_data['log_id'];
        return $result;
    }
    
    // Determine API base URL based on mode
    $base_url = $trading_mode === 'paper' 
        ? 'https://paper-api.alpaca.markets' 
        : 'https://api.alpaca.markets';
    
    // Use the account endpoint for basic connectivity testing
    $api_url = $base_url . '/v2/account';
    
    // Set up the API request
    $args = array(
        'headers' => array(
            'APCA-API-KEY-ID' => $api_key,
            'APCA-API-SECRET-KEY' => $api_secret,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 30
    );
    
    // Store request start time
    $request_start_time = microtime(true);
    
    // Make the API request
    $response = wp_remote_get($api_url, $args);
    
    // Calculate request duration
    $request_duration = round((microtime(true) - $request_start_time) * 1000); // in milliseconds
    
    // Check for WP errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        
        // Log the error
        $log_data = $logger->log_api_test(
            'alpaca',
            'connection_test',
            $environment,
            $trading_mode,
            array(
                'error' => $error_message,
                'request_duration_ms' => $request_duration
            ),
            'error',
            $error_message
        );
        
        $result['message'] = 'Connection Error: ' . $error_message;
        $result['log_id'] = $log_data['log_id'];
        $result['data']['duration_ms'] = $request_duration;
        return $result;
    }
    
    // Get response code and body
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    
    // Process the response based on status code
    if ($response_code >= 200 && $response_code < 300) {
        // Success - connection is working
        $log_data = $logger->log_api_test(
            'alpaca',
            'connection_test',
            $environment,
            $trading_mode,
            array(
                'response' => $response_data,
                'request_duration_ms' => $request_duration
            ),
            'success',
            'Connection test successful'
        );
        
        $result['success'] = true;
        $result['message'] = 'Alpaca API connection successful';
        $result['data'] = array(
            'account_id' => isset($response_data['id']) ? $response_data['id'] : 'unknown',
            'status' => isset($response_data['status']) ? $response_data['status'] : 'unknown',
            'currency' => isset($response_data['currency']) ? $response_data['currency'] : 'unknown',
            'buying_power' => isset($response_data['buying_power']) ? $response_data['buying_power'] : 'unknown',
            'cash' => isset($response_data['cash']) ? $response_data['cash'] : 'unknown',
            'trading_mode' => $trading_mode,
            'duration_ms' => $request_duration
        );
        $result['log_id'] = $log_data['log_id'];
        return $result;
    } else {
        // Handle error responses
        $error_message = 'Error ' . $response_code;
        
        if ($response_code === 400) {
            $error_message = 'Bad Request - Connection Error (400)';
        } elseif ($response_code === 401) {
            $error_message = 'Unauthorized - API credentials are invalid';
        } elseif ($response_code === 403) {
            $error_message = 'Forbidden - Access denied';
        } elseif ($response_code === 404) {
            $error_message = 'Not Found - API endpoint does not exist';
        } elseif ($response_code === 429) {
            $error_message = 'Too Many Requests - Rate limit exceeded';
        } elseif ($response_code >= 500) {
            $error_message = 'Server Error - Alpaca API service might be experiencing issues';
        }
        
        // Log the error
        $log_data = $logger->log_api_test(
            'alpaca',
            'connection_test',
            $environment,
            $trading_mode,
            array(
                'response' => $response_data,
                'request_duration_ms' => $request_duration,
                'error_code' => $response_code
            ),
            'error',
            $error_message
        );
        
        $result['message'] = $error_message;
        $result['data'] = array(
            'code' => $response_code,
            'response' => $response_data,
            'duration_ms' => $request_duration
        );
        $result['log_id'] = $log_data['log_id'];
        return $result;
    }
}

/**
 * Process Alpaca API endpoint test requests
 */
function tradepress_test_alpaca_endpoint_handler() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_test_alpaca_endpoint')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    // Get endpoint from request
    $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
    if (empty($endpoint)) {
        wp_send_json_error(array('message' => 'No endpoint specified'));
    }

    // Get environment and mode settings
    $environment = isset($_POST['environment']) ? sanitize_text_field($_POST['environment']) : 'demo';
    $trading_mode = get_option('tradepress_alpaca_paper_trading', 'yes') === 'yes' ? 'paper' : 'live';
    
    // Get API credentials
    $api_key = get_option('tradepress_alpaca_key', '');
    $api_secret = get_option('tradepress_alpaca_secret', '');
    
    if (empty($api_key) || empty($api_secret)) {
        // Create log entry for missing credentials
        $logger = new TradePress_API_Logging();
        $log_data = $logger->log_api_test(
            'alpaca',
            $endpoint,
            $environment,
            $trading_mode,
            null,
            'error',
            'API credentials are not configured'
        );
        
        wp_send_json_error(array(
            'message' => 'API credentials are not configured',
            'log_id' => $log_data['log_id']
        ));
    }
    
    // Determine API base URL based on mode
    $base_url = $trading_mode === 'paper' 
        ? 'https://paper-api.alpaca.markets' 
        : 'https://api.alpaca.markets';
        
    // Build the full endpoint URL
    $api_url = trailingslashit($base_url) . ltrim($endpoint, '/');
    
    // Set up the API request
    $args = array(
        'headers' => array(
            'APCA-API-KEY-ID' => $api_key,
            'APCA-API-SECRET-KEY' => $api_secret,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 30
    );
    
    // Make the API request
    $response = wp_remote_get($api_url, $args);
    
    // Initialize the logger
    $logger = new TradePress_API_Logging();
    
    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        
        // Log the error with comprehensive diagnostics
        $log_data = $logger->log_api_test(
            'alpaca',
            $endpoint,
            $environment,
            $trading_mode,
            array('error' => $error_message),
            'error',
            $error_message
        );
        
        wp_send_json_error(array(
            'message' => 'Connection Error: ' . $error_message,
            'log_id' => $log_data['log_id']
        ));
    }
    
    // Get response code
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    // Process the response based on status code
    if ($response_code >= 200 && $response_code < 300) {
        // Success
        $log_data = $logger->log_api_test(
            'alpaca',
            $endpoint,
            $environment,
            $trading_mode,
            $response_body,
            'success',
            ''
        );
        
        wp_send_json_success(array(
            'message' => 'API connection successful',
            'data' => json_decode($response_body, true),
            'log_id' => $log_data['log_id']
        ));
    } else {
        // Handle error responses
        $error_message = 'Error ' . $response_code;
        
        if ($response_code === 400) {
            $error_message = 'Bad Request - Connection Error (400)';
        } elseif ($response_code === 401) {
            $error_message = 'Unauthorized - API credentials might be invalid';
        } elseif ($response_code === 403) {
            $error_message = 'Forbidden - Access denied';
        } elseif ($response_code === 404) {
            $error_message = 'Not Found - Endpoint does not exist';
        } elseif ($response_code === 429) {
            $error_message = 'Too Many Requests - Rate limit exceeded';
        } elseif ($response_code >= 500) {
            $error_message = 'Server Error - Alpaca API service might be experiencing issues';
        }
        
        // Log the error with comprehensive diagnostics
        $log_data = $logger->log_api_test(
            'alpaca',
            $endpoint,
            $environment,
            $trading_mode,
            $response_body,
            'error',
            $error_message
        );
        
        wp_send_json_error(array(
            'message' => $error_message,
            'code' => $response_code,
            'response' => json_decode($response_body, true),
            'log_id' => $log_data['log_id']
        ));
    }
}
add_action('wp_ajax_tradepress_test_alpaca_endpoint', 'tradepress_test_alpaca_endpoint_handler');

/**
 * Process Alpaca API endpoint test requests with direct implementation
 */
function tradepress_test_alpaca_endpoint_direct() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_test_alpaca_endpoint')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }

    // Get endpoint from request
    $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
    if (empty($endpoint)) {
        wp_send_json_error(array('message' => 'No endpoint specified'));
    }

    // Get environment and mode settings
    $environment = isset($_POST['environment']) ? sanitize_text_field($_POST['environment']) : 'demo';
    $trading_mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : get_option('TradePress_api_alpaca_trading_mode', 'paper');
    
    // Explicitly handle the case if mode is passed in the request
    if ($trading_mode !== 'live' && $trading_mode !== 'paper') {
        $trading_mode = 'paper'; // Default to paper if invalid mode
    }
    
    // Get API credentials for the right mode
    if ($trading_mode === 'paper') {
        $api_key = get_option('TradePress_api_alpaca_papermoney_apikey', '');
        $api_secret = get_option('TradePress_api_alpaca_papermoney_secretkey', '');
    } else {
        $api_key = get_option('TradePress_api_alpaca_realmoney_apikey', '');
        $api_secret = get_option('TradePress_api_alpaca_realmoney_secretkey', '');
    }
    
    if (empty($api_key) || empty($api_secret)) {
        // Create log entry for missing credentials
        $logger = new TradePress_API_Logging();
        $log_data = $logger->log_api_test(
            'alpaca',
            $endpoint,
            $environment,
            $trading_mode,
            null,
            'error',
            'API credentials are not configured'
        );
        
        wp_send_json_error(array(
            'message' => 'API credentials are not configured',
            'log_id' => $log_data['log_id']
        ));
    }
    
    // Load the direct API handler
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/alpaca/alpaca-direct.php';
    
    // Initialize the direct handler with credentials
    $api = new TradePress_Alpaca_Direct($api_key, $api_secret, $trading_mode);
    
    // Start timing the request
    $start_time = microtime(true);
    
    // Make the API request
    if ($endpoint === 'account') {
        $result = $api->get_account();
    } else if ($endpoint === 'watchlists' || $endpoint === 'v2/watchlists') {
        $result = $api->get_watchlists();
    } else {
        // For other endpoints, use the general request method
        $endpoint_path = ltrim(str_replace('v2/', '', $endpoint), '/');
        $result = $api->request($endpoint_path);
    }
    
    // Calculate request duration
    $duration_ms = round((microtime(true) - $start_time) * 1000);
    
    // Initialize the logger
    $logger = new TradePress_API_Logging();
    
    // Process the response
    if ($result['success']) {
        // Log success
        $log_data = $logger->log_api_test(
            'alpaca',
            $endpoint,
            $environment,
            $trading_mode,
            array(
                'response' => $result['data'],
                'request_duration_ms' => $duration_ms
            ),
            'success',
            'API request successful'
        );
        
        wp_send_json_success(array(
            'message' => 'API connection successful',
            'data' => $result['data'],
            'duration_ms' => $duration_ms,
            'log_id' => $log_data['log_id']
        ));
    } else {
        // Log error
        $log_data = $logger->log_api_test(
            'alpaca',
            $endpoint,
            $environment,
            $trading_mode,
            array(
                'error' => $result['message'],
                'code' => $result['code'],
                'data' => isset($result['data']) ? $result['data'] : null,
                'request_duration_ms' => $duration_ms
            ),
            'error',
            $result['message']
        );
        
        wp_send_json_error(array(
            'message' => $result['message'],
            'code' => $result['code'],
            'response' => isset($result['data']) ? $result['data'] : null,
            'duration_ms' => $duration_ms,
            'log_id' => $log_data['log_id']
        ));
    }
}
add_action('wp_ajax_tradepress_test_alpaca_endpoint_direct', 'tradepress_test_alpaca_endpoint_direct');

/**
 * AJAX handler for testing Alpaca API connection
 */
function tradepress_test_alpaca_connection_handler() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_test_alpaca_connection')) {
        wp_send_json_error(array('message' => 'Security check failed'));
    }
    
    // Get environment from request
    $environment = isset($_POST['environment']) ? sanitize_text_field($_POST['environment']) : 'demo';
    
    // Run the connection test
    $result = tradepress_test_alpaca_connection($environment);
    
    // Return appropriate response based on test results
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_tradepress_test_alpaca_connection', 'tradepress_test_alpaca_connection_handler');