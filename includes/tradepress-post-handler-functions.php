<?php
/**
 * TradePress POST Handler Functions
 * 
 * Helper functions for working with the centralized POST submission system.
 * 
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress
 * @since    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register a handler for a POST action
 * 
 * @param string $action Action identifier
 * @param callable $callback The function to call to handle the action
 * @param string $capability Optional. The capability required to perform the action
 * @return bool Whether the handler was registered
 */
function tradepress_register_post_handler($action, $callback, $capability = '') {
    return TradePress_POST_Handler::register_handler($action, $callback, $capability);
}

/**
 * Create a nonce field for a TradePress form
 * 
 * @param string $action Action identifier
 * @param bool $referer Whether to include the referer field
 * @param bool $echo Whether to echo the field
 * @return string The nonce field HTML
 */
function tradepress_nonce_field($action, $referer = true, $echo = true) {
    return TradePress_POST_Handler::nonce_field($action, $referer, $echo);
}

// Note: tradepress_form_open() function has been removed to avoid duplication
// Use the function already defined in functions.php

/**
 * Create a simple success notification for POST handler response
 *
 * @param string $message Success message
 * @param string $redirect_url URL to redirect to
 * @return array Response array for POST handler
 */
function tradepress_post_success($message, $redirect_url = '') {
    $response = array(
        'notice' => $message,
        'notice_type' => 'success',
    );
    
    if (!empty($redirect_url)) {
        $response['redirect'] = $redirect_url;
    }
    
    return $response;
}

/**
 * Create an error notification for POST handler response
 *
 * @param string $message Error message
 * @param string $redirect_url URL to redirect to
 * @return array Response array for POST handler
 */
function tradepress_post_error($message, $redirect_url = '') {
    $response = array(
        'notice' => $message,
        'notice_type' => 'error',
    );
    
    if (!empty($redirect_url)) {
        $response['redirect'] = $redirect_url;
    }
    
    return $response;
}

/**
 * Get POST handling result data if available
 * 
 * @return array|null Result data or null if none available
 */
function tradepress_get_post_result() {
    if (isset($_GET['tradepress_result'])) {
        $transient_id = sanitize_key($_GET['tradepress_result']);
        $result = get_transient($transient_id);
        
        if ($result) {
            // Clean up the transient
            delete_transient($transient_id);
            return $result;
        }
    }
    
    return null;
}
