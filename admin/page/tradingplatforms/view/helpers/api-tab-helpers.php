<?php
/**
 * API Tab Helper Functions
 * 
 * Common helper functions used across API tab templates
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Determine CSS class for status color based on status value
 *
 * @param string $status Status value (active, inactive, operational, etc.)
 * @return string CSS class name for the status color
 */
function get_status_color($status) {
    switch ($status) {
        case 'active':
        case 'operational':
        case 'success':
            return 'status-green';
        case 'disruption':
        case 'maintenance':
        case 'warning':
            return 'status-orange';
        case 'inactive':
        case 'outage':
        case 'error':
            return 'status-red';
        default:
            return 'status-grey';
    }
}

/**
 * Format JSON for readable display in alerts
 *
 * @param mixed $json The data to format as JSON
 * @return string Formatted JSON string with escaped quotes
 */
function format_json_for_display($json) {
    if (empty($json)) {
        return '';
    }
    
    $formatted = json_encode($json, JSON_PRETTY_PRINT);
    $formatted = str_replace('"', '\"', $formatted); // Escape quotes for JS
    
    return $formatted;
}

/**
 * Get real local status for an API based on its configuration
 *
 * @param string $api_id The API identifier (e.g., 'alpaca', 'alphavantage')
 * @return array Status array with 'status' and 'message' keys
 */
function get_real_api_local_status($api_id) {
    // Check if API is enabled
    $api_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no');
    
    if ($api_enabled !== 'yes') {
        return array(
            'status' => 'error',
            'message' => __('API is disabled', 'tradepress')
        );
    }
    
    // Check for API key
    $api_key = get_option('TradePress_api_' . $api_id . '_realmoney_apikey', '');
    if (empty($api_key)) {
        // Try alternative key names for some APIs
        $alt_key_names = array(
            'tradepress_' . $api_id . '_api_key',
            'TradePress_' . $api_id . '_api_key',
            'TradePress_api_' . $api_id . '_key'
        );
        
        // Special case for Alpha Vantage which uses different option names
        if ($api_id === 'alphavantage') {
            $alt_key_names[] = 'tradepress_api_alphavantage_key';
            $alt_key_names[] = 'TradePress_alphavantage_api_key';
            $alt_key_names[] = 'tradepress_alphavantage_api_key';
        }
        
        foreach ($alt_key_names as $alt_name) {
            $api_key = get_option($alt_name, '');
            if (!empty($api_key)) {
                break;
            }
        }
        
        if (empty($api_key)) {
            return array(
                'status' => 'error',
                'message' => __('API key not configured', 'tradepress')
            );
        }
    }
    
    // For APIs that require a secret key, check that too
    $requires_secret = !in_array($api_id, array('alphavantage', 'polygon', 'finnhub', 'iexcloud', 'marketstack', 'quandl', 'fred'));
    
    if ($requires_secret) {
        $api_secret = get_option('TradePress_api_' . $api_id . '_realmoney_secretkey', '');
        if (empty($api_secret)) {
            return array(
                'status' => 'warning',
                'message' => __('API secret not configured', 'tradepress')
            );
        }
    }
    
    // If we get here, the API appears to be properly configured
    return array(
        'status' => 'active',
        'message' => __('Connected and authenticated', 'tradepress')
    );
}
