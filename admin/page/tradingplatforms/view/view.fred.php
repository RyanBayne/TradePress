<?php
/**
 * Admin View: API - FRED (Federal Reserve) Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the FRED endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/fred/fred-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_FRED_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'fred';
$api_name = 'FRED';
$api_description = __('Federal Reserve Economic Data - economic time series data from the Federal Reserve.', 'tradepress');
$api_version = 'v1';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/fred-logo.png';

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

// Rate limits structure for FRED
$rate_limits = array(
    'daily_quota' => 1000,
    'daily_used' => 345,
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
        'url' => 'https://fred.stlouisfed.org/docs/api/fred/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Developer Portal', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://fred.stlouisfed.org/docs/api/fred/'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'series' => __('Series', 'tradepress'),
    'releases' => __('Releases', 'tradepress'),
    'categories' => __('Categories', 'tradepress'),
    'sources' => __('Sources', 'tradepress'),
    'tags' => __('Tags', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
