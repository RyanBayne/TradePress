<?php
/**
 * Handle AJAX request to test an Alpaca API endpoint
 * 
 * @since 1.0.1
 * @return void
 */
function tradepress_ajax_test_alpaca_endpoint() {
    // Security check
    check_ajax_referer('tradepress_test_alpaca_endpoint', 'security');
    
    // Check for valid user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'Permission denied',
            'details' => 'You do not have sufficient permissions to perform this operation'
        ));
        return;
    }
    
    // Get the endpoint to test
    $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
    $trading_mode = isset($_POST['trading_mode']) ? sanitize_text_field($_POST['trading_mode']) : 'paper';
    
    if (empty($endpoint)) {
        wp_send_json_error(array(
            'message' => 'Invalid request',
            'details' => 'No endpoint specified'
        ));
        return;
    }
    
    // Additional data for logging
    $additional_data = array(
        'user_id' => get_current_user_id(),
        'request_time' => current_time('mysql'),
        'request_ip' => $_SERVER['REMOTE_ADDR'],
        'trading_mode' => $trading_mode
    );
    
    // Get API credentials
    $api_key = get_option('TradePress_api_alpaca_key', '');
    $api_secret = get_option('TradePress_api_alpaca_secret', '');
    
    // Validate credentials
    if (empty($api_key) || empty($api_secret)) {
        $error_message = 'Missing API credentials';
        
        // Log the error
        TradePress_API_Logging::log_api_test(
            $endpoint, 
            'Error', 
            $error_message, 
            $additional_data
        );
        
        wp_send_json_error(array(
            'message' => $error_message,
            'details' => 'Please configure your Alpaca API key and secret in the settings'
        ));
        return;
    }
    
    // Determine correct base URL based on trading mode
    $base_url = $trading_mode === 'live' 
        ? 'https://api.alpaca.markets' 
        : 'https://paper-api.alpaca.markets';
    
    // Prepare request
    $api_version = get_option('TradePress_api_alpaca_version', 'v2');
    $request_url = $base_url . '/' . $api_version . '/' . $endpoint;
    
    // Make the request
    $response = wp_remote_get(
        $request_url,
        array(
            'headers' => array(
                'APCA-API-KEY-ID' => $api_key,
                'APCA-API-SECRET-KEY' => $api_secret,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15,
            'sslverify' => true
        )
    );
    
    // Check for WP error
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        
        // Log the error with our enhanced logging
        $log_id = TradePress_API_Logging::log_api_test(
            $endpoint, 
            'Connection Error', 
            $error_message, 
            array_merge($additional_data, array(
                'error_type' => 'wp_error',
                'error_code' => $response->get_error_code()
            ))
        );
        
        wp_send_json_error(array(
            'message' => 'Connection error: ' . $error_message,
            'details' => array(
                'error_code' => $response->get_error_code(),
                'log_id' => $log_id
            )
        ));
        return;
    }
    
    // Check response code
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $response_data = json_decode($response_body, true);
    
    // Add response code to logging data
    $additional_data['response_code'] = $response_code;
    
    // Handle error responses
    if ($response_code >= 400) {
        $error_message = 'API Error: ' . $response_code;
        
        if (is_array($response_data) && isset($response_data['message'])) {
            $error_message .= ' - ' . $response_data['message'];
        }
        
        // Log the error with enhanced details
        $log_id = TradePress_API_Logging::log_api_test(
            $endpoint, 
            'Error', 
            $error_message, 
            array_merge($additional_data, array(
                'response_body' => $response_body,
                'response_message' => is_array($response_data) && isset($response_data['message']) 
                    ? $response_data['message'] 
                    : 'No message provided'
            ))
        );
        
        wp_send_json_error(array(
            'message' => $error_message,
            'details' => $response_data,
            'log_id' => $log_id
        ));
        return;
    }
    
    // Log successful test
    $log_id = TradePress_API_Logging::log_api_test(
        $endpoint, 
        'Success', 
        '', 
        array_merge($additional_data, array(
            'response_body' => $response_body
        ))
    );
    
    // Return successful response
    wp_send_json_success(array(
        'data' => $response_data,
        'log_id' => $log_id
    ));
}
add_action('wp_ajax_tradepress_test_alpaca_endpoint', 'tradepress_ajax_test_alpaca_endpoint');