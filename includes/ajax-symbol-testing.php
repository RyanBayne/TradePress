<?php
/**
 * TradePress Symbol Testing AJAX Handlers
 *
 * AJAX handlers for one-time data import testing in Data > Import tab.
 * These are separate from automation - they test individual operations.
 *
 * AI GUIDANCE:
 * - These handlers are for ONE-TIME testing, not automation
 * - Use existing symbol classes: TradePress_Symbol, TradePress_Symbols
 * - Always check nonce and user permissions
 * - Return detailed JSON responses for debugging
 * - Track API calls in tradepress_api_calls table
 * - Store symbol data in tradepress_symbols and tradepress_symbol_meta tables
 *
 * AVAILABLE HANDLERS:
 * - tradepress_test_symbol_fetch: Test fetching single symbol data
 * - tradepress_update_symbols_manual: Update single symbol from API
 * - tradepress_test_batch_import: Test importing multiple symbols
 * - tradepress_test_api_connection: Test API provider connections
 * - tradepress_check_database_status: Check database table status
 *
 * @package TradePress
 * @subpackage Includes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Test symbol fetch AJAX handler
add_action('wp_ajax_tradepress_test_symbol_fetch', 'tradepress_test_symbol_fetch_handler');
function tradepress_test_symbol_fetch_handler() {
    check_ajax_referer('tradepress_admin', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $symbol = sanitize_text_field($_POST['symbol'] ?? 'AAPL');
    
    // Use existing symbol class
    require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbol.php';
    require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbols.php';
    
    // First try to get existing symbol
    $symbol_obj = TradePress_Symbols::get_symbol($symbol);
    
    if (!$symbol_obj) {
        // Create new symbol and fetch from API
        $symbol_obj = new TradePress_Symbol();
        
        // Create symbol record first
        global $wpdb;
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        $fields = array(
            'symbol' => $symbol,
            'name' => $symbol, // Will be updated from API
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'active' => 1
        );
        
        $result = $wpdb->insert($table_name, $fields);
        
        if ($result > 0) {
            // Load the newly created symbol
            $symbol_obj->load($symbol, 'symbol');
            
            // Update from API
            $api_result = $symbol_obj->update_from_api('alphavantage');
            
            if (!$api_result) {
                wp_send_json_error('Failed to fetch data from API');
            }
        } else {
            wp_send_json_error('Failed to create symbol record');
        }
    }
    
    $complete_data = $symbol_obj->get_complete_data(false);
    
    wp_send_json_success(array(
        'symbol' => $symbol,
        'data' => $complete_data,
        'message' => 'Symbol data retrieved successfully'
    ));
}

// Manual symbol update AJAX handler
add_action('wp_ajax_tradepress_update_symbols_manual', 'tradepress_update_symbols_manual_handler');
function tradepress_update_symbols_manual_handler() {
    check_ajax_referer('tradepress_admin', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $symbol = sanitize_text_field($_POST['symbol'] ?? '');
    
    if (empty($symbol)) {
        wp_send_json_error('Symbol required');
    }
    
    // Use existing symbol class
    require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbol.php';
    require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbols.php';
    
    $symbol_obj = TradePress_Symbols::get_symbol($symbol);
    
    if (!$symbol_obj) {
        // Create new symbol record first
        global $wpdb;
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        $fields = array(
            'symbol' => $symbol,
            'name' => $symbol,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'active' => 1
        );
        
        $insert_result = $wpdb->insert($table_name, $fields);
        
        if ($insert_result > 0) {
            $symbol_obj = new TradePress_Symbol($symbol);
        } else {
            wp_send_json_error("Failed to create symbol record for $symbol");
        }
    }
    
    $result = $symbol_obj->update_from_api('alphavantage');
    
    if ($result) {
        wp_send_json_success("Symbol $symbol updated successfully from API");
    } else {
        // Add debugging info
        $api_key = get_option('tradepress_api_alphavantage_key', '');
        if (empty($api_key)) {
            $api_key = get_option('tradepress_alphavantage_api_key', '');
        }
        if (empty($api_key)) {
            $api_key = get_option('TradePress_alphavantage_api_key', '');
        }
        $debug_info = array(
            'symbol_id' => $symbol_obj->get_id(),
            'api_key_configured' => !empty($api_key),
            'api_key_length' => strlen($api_key)
        );
        wp_send_json_error(array(
            'message' => "Failed to update symbol $symbol from API",
            'debug' => $debug_info
        ));
    }
}

// Batch import test AJAX handler
add_action('wp_ajax_tradepress_test_batch_import', 'tradepress_test_batch_import_handler');
function tradepress_test_batch_import_handler() {
    check_ajax_referer('tradepress_admin', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $symbols_input = sanitize_text_field($_POST['symbols'] ?? '');
    $symbols = array_map('trim', explode(',', $symbols_input));
    
    require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbol.php';
    require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbols.php';
    
    $results = array();
    global $wpdb;
    $table_name = $wpdb->prefix . 'tradepress_symbols';
    
    foreach ($symbols as $symbol) {
        if (empty($symbol)) continue;
        
        $symbol = strtoupper($symbol);
        
        // Check if symbol exists
        $symbol_obj = TradePress_Symbols::get_symbol($symbol);
        
        if (!$symbol_obj) {
            // Create new symbol record first
            $fields = array(
                'symbol' => $symbol,
                'name' => $symbol, // Will be updated from API
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'active' => 1
            );
            
            $insert_result = $wpdb->insert($table_name, $fields);
            
            if ($insert_result > 0) {
                // Load the newly created symbol
                $symbol_obj = new TradePress_Symbol();
                $symbol_obj->load($symbol, 'symbol');
            } else {
                $results[$symbol] = 'Failed - Could not create record';
                continue;
            }
        }
        
        // Update from API
        $update_result = $symbol_obj->update_from_api('alphavantage');
        
        if ($update_result) {
            $results[$symbol] = 'Success';
        } else {
            // Add more specific error information
            $api_key = get_option('tradepress_api_alphavantage_key', '');
            if (empty($api_key)) {
                $api_key = get_option('TradePress_alphavantage_api_key', '');
            }
            if (empty($api_key)) {
                $api_key = get_option('tradepress_alphavantage_api_key', '');
            }
            
            if (empty($api_key)) {
                $results[$symbol] = 'Failed - No API key configured';
            } else {
                $results[$symbol] = 'Failed - API update failed (key configured)';
            }
        }
    }
    
    wp_send_json_success(array(
        'message' => 'Batch import completed',
        'results' => $results,
        'total_symbols' => count($symbols)
    ));
}

// API connection test AJAX handler
add_action('wp_ajax_tradepress_test_api_connection', 'tradepress_test_api_connection_handler');
function tradepress_test_api_connection_handler() {
    check_ajax_referer('tradepress_admin', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $provider = sanitize_text_field($_POST['provider'] ?? 'alphavantage');
    
    $connection_status = array(
        'provider' => $provider,
        'status' => 'Unknown',
        'message' => 'Connection test not implemented for this provider'
    );
    
    // Test based on provider
    switch ($provider) {
        case 'alphavantage':
            $api_key = get_option('tradepress_alphavantage_api_key', '');
            if (empty($api_key)) {
                $connection_status['status'] = 'Error';
                $connection_status['message'] = 'Alpha Vantage API key not configured';
            } else {
                $connection_status['status'] = 'Configured';
                $connection_status['message'] = 'API key is configured';
                $connection_status['api_key_length'] = strlen($api_key);
            }
            break;
        case 'alpaca':
            $connection_status['status'] = 'Not Implemented';
            $connection_status['message'] = 'Alpaca connection test not yet implemented';
            break;
        default:
            $connection_status['status'] = 'Unknown Provider';
            $connection_status['message'] = 'Provider not recognized';
    }
    
    wp_send_json_success($connection_status);
}

// Database status check AJAX handler
add_action('wp_ajax_tradepress_check_database_status', 'tradepress_check_database_status_handler');
function tradepress_check_database_status_handler() {
    check_ajax_referer('tradepress_admin', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    global $wpdb;
    
    $status = array(
        'symbols_table' => array(),
        'symbol_meta_table' => array(),
        'api_calls_table' => array()
    );
    
    // Check symbols table
    $symbols_table = $wpdb->prefix . 'tradepress_symbols';
    $symbols_count = $wpdb->get_var("SELECT COUNT(*) FROM $symbols_table");
    $status['symbols_table'] = array(
        'exists' => $wpdb->get_var("SHOW TABLES LIKE '$symbols_table'") === $symbols_table,
        'count' => intval($symbols_count),
        'recent_symbols' => $wpdb->get_results("SELECT symbol, name, created_at FROM $symbols_table ORDER BY created_at DESC LIMIT 5", ARRAY_A)
    );
    
    // Check symbol meta table
    $meta_table = $wpdb->prefix . 'tradepress_symbol_meta';
    $meta_count = $wpdb->get_var("SELECT COUNT(*) FROM $meta_table");
    $status['symbol_meta_table'] = array(
        'exists' => $wpdb->get_var("SHOW TABLES LIKE '$meta_table'") === $meta_table,
        'count' => intval($meta_count)
    );
    
    // Check API calls table
    $api_table = $wpdb->prefix . 'tradepress_api_calls';
    $api_count = $wpdb->get_var("SELECT COUNT(*) FROM $api_table");
    $status['api_calls_table'] = array(
        'exists' => $wpdb->get_var("SHOW TABLES LIKE '$api_table'") === $api_table,
        'count' => intval($api_count),
        'recent_calls' => $wpdb->get_results("SELECT provider, endpoint, call_time, response_code FROM $api_table ORDER BY call_time DESC LIMIT 5", ARRAY_A)
    );
    
    wp_send_json_success($status);
}