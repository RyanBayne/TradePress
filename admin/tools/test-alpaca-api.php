<?php
/**
 * Alpaca API Test Script
 *
 * This script tests connectivity to the Alpaca API for both trading and market data.
 * To run this script, visit /wp-admin/admin.php?page=tradepress-tools&tool=test-alpaca-api
 *
 * @package TradePress
 * @subpackage Tools
 * @version 1.0.0
 * @since 2025-04-16
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Run Alpaca API tests
 */
function tradepress_test_alpaca_api() {
    // Get the API settings
    $settings = get_option('tradepress_alpaca_settings', array());
    
    // Check if API settings are configured
    if (empty($settings['api_key']) || empty($settings['api_secret'])) {
        echo '<div class="notice notice-error"><p>';
        echo __('Error: Alpaca API settings are not configured. Please configure them in the API Settings page.', 'tradepress');
        echo '</p></div>';
        return;
    }
    
    // Initialize the API
    require_once TRADEPRESS_PLUGIN_DIR . 'api/alpaca/alpaca-api.php';
    $api = new TradePress_Alpaca_API($settings['api_key'], $settings['api_secret'], isset($settings['use_sandbox']) ? (bool) $settings['use_sandbox'] : true);
    
    echo '<h2>' . __('Alpaca API Test Results', 'tradepress') . '</h2>';
    
    // Test 1: Validate credentials
    echo '<h3>' . __('Test 1: API Credentials', 'tradepress') . '</h3>';
    $result = $api->validate_credentials();
    
    if (is_wp_error($result)) {
        echo '<div class="notice notice-error"><p>';
        echo __('Failed: ', 'tradepress') . $result->get_error_message();
        echo '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>';
        echo __('Success: API credentials are valid.', 'tradepress');
        echo '</p></div>';
    }
    
    // Test 2: Get account information
    echo '<h3>' . __('Test 2: Account Information', 'tradepress') . '</h3>';
    $account = $api->get_account();
    
    if (is_wp_error($account)) {
        echo '<div class="notice notice-error"><p>';
        echo __('Failed: ', 'tradepress') . $account->get_error_message();
        echo '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>';
        echo __('Success: Account information retrieved.', 'tradepress');
        echo '</p></div>';
        
        echo '<pre>';
        print_r($account);
        echo '</pre>';
    }
    
    // Test 3: Get market clock
    echo '<h3>' . __('Test 3: Market Clock', 'tradepress') . '</h3>';
    $clock = $api->get_clock();
    
    if (is_wp_error($clock)) {
        echo '<div class="notice notice-error"><p>';
        echo __('Failed: ', 'tradepress') . $clock->get_error_message();
        echo '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>';
        echo __('Success: Market clock information retrieved.', 'tradepress');
        echo '</p></div>';
        
        echo '<pre>';
        print_r($clock);
        echo '</pre>';
    }
    
    // Test 4: Get market data
    echo '<h3>' . __('Test 4: Market Data API - Latest Quote', 'tradepress') . '</h3>';
    $quote = $api->get_latest_quote('AAPL');
    
    if (is_wp_error($quote)) {
        echo '<div class="notice notice-error"><p>';
        echo __('Failed: ', 'tradepress') . $quote->get_error_message();
        echo '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>';
        echo __('Success: Latest quote information retrieved.', 'tradepress');
        echo '</p></div>';
        
        echo '<pre>';
        print_r($quote);
        echo '</pre>';
    }
    
    // Test 5: Get bars
    echo '<h3>' . __('Test 5: Market Data API - Daily Bars', 'tradepress') . '</h3>';
    $bars = $api->get_bars('1D', 'AAPL', array(
        'limit' => 5
    ));
    
    if (is_wp_error($bars)) {
        echo '<div class="notice notice-error"><p>';
        echo __('Failed: ', 'tradepress') . $bars->get_error_message();
        echo '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>';
        echo __('Success: Bar data retrieved.', 'tradepress');
        echo '</p></div>';
        
        echo '<pre>';
        print_r($bars);
        echo '</pre>';
    }
}

// Run the tests
tradepress_test_alpaca_api();