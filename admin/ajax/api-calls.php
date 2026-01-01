<?php
/**
 * AJAX handler for API calls display
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register AJAX handlers
add_action('wp_ajax_tradepress_get_api_calls', 'tradepress_handle_get_api_calls');

function tradepress_handle_get_api_calls() {
    if (!wp_verify_nonce($_POST['nonce'], 'tradepress_api_calls') || !current_user_can('manage_options')) {
        wp_send_json_error('Security check failed');
    }
    
    $directive = sanitize_text_field($_POST['directive']);
    
    // Load Call Register
    if (!class_exists('TradePress_Call_Register')) {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
    }
    
    // Get recent calls from transients
    $calls = array();
    
    // Check current and previous hour transients
    $current_key = 'tradepress_call_register_' . date('YmdH');
    $previous_key = 'tradepress_call_register_' . date('YmdH', strtotime('-1 hour'));
    
    $current_register = get_transient($current_key);
    $previous_register = get_transient($previous_key);
    
    // Combine and filter calls related to this directive
    $all_calls = array();
    if ($current_register) $all_calls = array_merge($all_calls, $current_register);
    if ($previous_register) $all_calls = array_merge($all_calls, $previous_register);
    
    foreach ($all_calls as $serial => $call_data) {
        // Filter calls related to this directive (RSI, quote data, etc.)
        if ($directive === 'rsi' && (strpos($serial, 'rsi') !== false || strpos($serial, 'get_quote') !== false)) {
            $calls[] = array(
                'platform' => 'alphavantage',
                'method' => strpos($serial, 'rsi') !== false ? 'RSI' : 'get_quote',
                'parameters' => array('symbol' => 'extracted_from_serial'),
                'timestamp' => date('Y-m-d H:i:s', $call_data['timestamp']),
                'age_minutes' => round((time() - $call_data['timestamp']) / 60, 1)
            );
        }
    }
    
    wp_send_json_success($calls);
}