<?php
/**
 * Admin View: API - Tiingo Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Tiingo endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/tiingo/tiingo-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_Tiingo_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'tiingo';
$api_name = 'Tiingo';
$api_description = __('Financial data platform providing end-of-day and intraday data for stocks, ETFs, and cryptocurrencies.', 'tradepress');
$api_version = 'v1';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/tiingo-logo.png';

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

// Rate limits structure for Tiingo
$rate_limits = array(
    'daily_quota' => 20000,
    'daily_used' => 7890,
    'hourly_quota' => 1000,
    'hourly_used' => 320,
    'reset_time' => date('Y-m-d H:i:s', strtotime('+1 day'))
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
        'url' => 'https://api.tiingo.com/documentation/general/overview',
        'icon' => 'external'
    ),
    array(
        'text' => __('Getting Started', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://api.tiingo.com/documentation/general/connecting'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'eod' => __('End of Day', 'tradepress'),
    'intraday' => __('Intraday', 'tradepress'),
    'fundamentals' => __('Fundamentals', 'tradepress'),
    'crypto' => __('Cryptocurrency', 'tradepress'),
    'news' => __('News', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
