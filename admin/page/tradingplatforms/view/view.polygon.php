<?php
/**
 * Admin View: API - Polygon Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Polygon endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/polygon/polygon-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_Polygon_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'polygon';
$api_name = 'Polygon';
$api_description = __('Financial data API providing real-time and historical market data for stocks, options, forex, and crypto.', 'tradepress');
$api_version = 'v2';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/polygon-logo.png';

// Get real local status using helper function
$local_status = get_real_api_local_status($api_id);

$service_status = array(
    'status' => 'operational',
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

// Rate limits structure for Polygon
$rate_limits = array(
    'minute_quota' => 5,
    'minute_used' => 2,
    'daily_quota' => 1000, 
    'daily_used' => 456,
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
        'text' => __('API Docs', 'tradepress'),
        'url' => 'https://polygon.io/docs',
        'icon' => 'external'
    ),
    array(
        'text' => __('Getting Started', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://polygon.io/docs/getting-started'
    )
);

// Define data types for explorer - Polygon specific
$explorer_data_types = array(
    'quote' => __('Quote Data', 'tradepress'),
    'trades' => __('Trade Data', 'tradepress'),
    'historical' => __('Historical Data', 'tradepress'),
    'company' => __('Company Data', 'tradepress'),
    'news' => __('News', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
