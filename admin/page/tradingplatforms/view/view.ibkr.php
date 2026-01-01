<?php
/**
 * Admin View: API - Interactive Brokers Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the IBKR endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/ibkr/ibkr-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_IBKR_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'ibkr';
$api_name = 'Interactive Brokers';
$api_description = __('Professional trading platform with global market access and advanced order types.', 'tradepress');
$api_version = 'v1';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/ibkr-logo.png';

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

// Rate limits structure for IBKR
$rate_limits = array(
    'minute_quota' => 50,
    'minute_used' => 18,
    'daily_quota' => 5000,
    'daily_used' => 1230,
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
        'text' => __('Client Portal API', 'tradepress'),
        'url' => 'https://interactivebrokers.github.io/cpapi/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Developer Guide', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://www.interactivebrokers.com/en/index.php?f=5041'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'account' => __('Account Info', 'tradepress'),
    'portfolio' => __('Portfolio', 'tradepress'),
    'orders' => __('Orders', 'tradepress'),
    'market_data' => __('Market Data', 'tradepress'),
    'scanners' => __('Market Scanners', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
