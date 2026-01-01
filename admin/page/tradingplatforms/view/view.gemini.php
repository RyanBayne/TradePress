<?php
/**
 * Admin View: API - Gemini Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Gemini endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/gemini/gemini-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_Gemini_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'gemini';
$api_name = 'Gemini';
$api_description = __('Cryptocurrency exchange platform API for trading and market data access.', 'tradepress');
$api_version = 'v1';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/gemini-logo.png';

// Status data
$local_status = array(
    'status' => 'active',
    'message' => 'Connected and authenticated'
);

$service_status = array(
    'status' => 'operational',
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

// Rate limits structure for Gemini
$rate_limits = array(
    'minute_quota' => 150,
    'minute_used' => 45,
    'hourly_quota' => 600,
    'hourly_used' => 230,
    'reset_time' => date('Y-m-d H:i:s', strtotime('+1 minute'))
);

// Generate dummy usage data for real endpoints
$endpoints = array();
foreach ($real_endpoints as $key => $endpoint) {
    if (!is_array($endpoint) || !isset($endpoint['endpoint'])) {
        continue; // Skip non-endpoint items or utility methods
    }
    
    $demo_status = rand(0, 10) > 1 ? 'active' : 'maintenance';
    $endpoints[] = array(
        'name' => ucfirst(str_replace('_', ' ', $key)),
        'endpoint' => $endpoint['endpoint'],
        'description' => isset($endpoint['description']) ? $endpoint['description'] : '',
        'usage_count' => rand(100, 2000),
        'status' => $demo_status,
        'method' => isset($endpoint['method']) ? $endpoint['method'] : 'GET',
        'key' => $key
    );
}

// Documentation links
$documentation_links = array(
    array(
        'text' => __('API Documentation', 'tradepress'),
        'url' => 'https://docs.gemini.com/rest-api/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Getting Started', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://docs.gemini.com/'
    )
);

// Define data types for explorer - Gemini specific
$explorer_data_types = array(
    'ticker' => __('Ticker', 'tradepress'),
    'orderbook' => __('Order Book', 'tradepress'),
    'trades' => __('Trades', 'tradepress'),
    'candles' => __('Candles', 'tradepress'),
    'balances' => __('Account Balances', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
