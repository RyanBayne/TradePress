<?php
/**
 * Admin View: API - Alpaca Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Alpaca endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/alpaca/alpaca-endpoints.php';

// Include helper functions
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Set the API ID first
$api_id = 'alpaca';

// Get real endpoints from the class - ALWAYS use real data
$real_endpoints = TradePress_Alpaca_Endpoints::get_real_endpoints();

// Get real local status using helper function
$local_status = get_real_api_local_status($api_id);

$service_status = array(
    'status' => 'operational', // or 'disruption', 'outage', 'maintenance'
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

$rate_limits = array(
    'daily_quota' => 5000,
    'daily_used' => 350,
    'minute_quota' => 200,
    'minute_used' => 15,
    'reset_time' => date('Y-m-d', strtotime('+1 day')) . ' 00:00:00'
);

// Generate endpoint data
$endpoints = array();
foreach ($real_endpoints as $key => $endpoint) {
    // All endpoints should be active since the API is working
    $endpoints[] = array(
        'name' => ucfirst(str_replace('_', ' ', $key)),
        'endpoint' => $endpoint['endpoint'],
        'description' => $endpoint['description'],
        'usage_count' => 0, // Reset usage counts as they'll be tracked properly
        'status' => 'active', // All endpoints are active
        'method' => isset($endpoint['method']) ? $endpoint['method'] : 'GET',
        'key' => $key // Save the original endpoint key for accessing example responses
    );
}

// Get API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider('alpaca');

// Set the API template variables
$api_name = 'Alpaca';
$api_description = __('Commission-free API-first stock and crypto brokerage that lets you build and test trading apps.', 'tradepress');
$api_version = 'v2';
$api_logo_url = !empty($provider['icon_url']) ? $provider['icon_url'] : TRADEPRESS_PLUGIN_URL . '/assets/images/alpaca-logo.png';

// Documentation links
$documentation_links = array(
    array(
        'text' => __('Official Docs', 'tradepress'),
        'url' => 'https://docs.alpaca.markets/reference/',
        'icon' => 'external'
    ),
    array(
        'text' => __('API Reference', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://docs.alpaca.markets/docs/trading-api'
    ),
    array(
        'text' => __('Market Data', 'tradepress'),
        'icon' => 'chart-bar',
        'url' => 'https://docs.alpaca.markets/docs/about-market-data-api'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'watchlists' => __('Watchlists', 'tradepress'),
    'account' => __('Account Information', 'tradepress'),
    'positions' => __('Positions', 'tradepress'),
    'orders' => __('Orders', 'tradepress'),
    'market_data' => __('Market Data', 'tradepress')
);

// Enqueue the required CSS files
wp_enqueue_style(
    'tradepress-api-layout',
    TRADEPRESS_PLUGIN_URL . 'assets/css/layouts/api.css',
    array(),
    TRADEPRESS_VERSION
);

wp_enqueue_style(
    'tradepress-admin-layout',
    TRADEPRESS_PLUGIN_URL . 'assets/css/layouts/admin.css',
    array(),
    TRADEPRESS_VERSION
);

wp_enqueue_style(
    'tradepress-status-components',
    TRADEPRESS_PLUGIN_URL . 'assets/css/components/status.css',
    array(),
    TRADEPRESS_VERSION
);

wp_enqueue_style(
    'tradepress-status-indicators',
    TRADEPRESS_PLUGIN_URL . 'assets/css/components/status-indicators.css',
    array(),
    TRADEPRESS_VERSION
);

wp_enqueue_style(
    'tradepress-cards',
    TRADEPRESS_PLUGIN_URL . 'assets/css/components/cards.css',
    array(),
    TRADEPRESS_VERSION
);

// Enqueue the Alpaca JavaScript file
wp_enqueue_script(
    'tradepress-alpaca',
    TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-alpaca.js',
    array('jquery'),
    TRADEPRESS_VERSION,
    true
);

// Localize script with data
wp_localize_script('tradepress-alpaca', 'tradePressAlpaca', array(
    'nonce' => wp_create_nonce('tradepress_test_alpaca_endpoint'),
    'tradingMode' => esc_js(get_option('TradePress_api_alpaca_trading_mode', 'paper')),
    'startDate' => date('Y-m-d', strtotime('-1 month')),
    'endDate' => date('Y-m-d'),
    'strings' => array(
        'error' => __('Error', 'tradepress'),
        'connectionError' => __('Connection Error', 'tradepress'),
        'howToFix' => __('How to fix this:', 'tradepress'),
        'troubleshooting' => __('Troubleshooting steps:', 'tradepress'),
        'goToSettings' => __('Go to the Settings tab above', 'tradepress'),
        'enterCredentials' => __('Make sure both your API Key and Secret Key are entered', 'tradepress'),
        'verifyCredentials' => __('Verify that your API credentials are correct', 'tradepress'),
        'saveSettings' => __('Save your settings', 'tradepress'),
        'checkConnection' => __('Check your internet connection', 'tradepress'),
        'checkTradingMode' => __('Make sure you\'re testing in the correct trading mode (paper/live)', 'tradepress'),
        'checkStatus' => __('Check if Alpaca API services are operational at status.alpaca.markets', 'tradepress'),
        'noWatchlists' => __('No watchlists found.', 'tradepress'),
        'yourWatchlists' => __('Your Watchlists', 'tradepress'),
        'noSymbols' => __('No symbols in this watchlist.', 'tradepress')
    )
));

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
?>
