<?php
/**
 * Admin View: API - IEX Cloud Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the IEX Cloud endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/iexcloud/iexcloud-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_IEXCloud_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'iexcloud';
$api_name = 'IEX Cloud';
$api_description = __('Financial data platform with real-time and historical market data, fundamentals, and reference data.', 'tradepress');
$api_version = 'v1';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/iexcloud-logo.png';

// Get real local status using helper function
$local_status = get_real_api_local_status($api_id);

$service_status = array(
    'status' => 'operational',
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

// Rate limits structure for IEX Cloud
$rate_limits = array(
    'daily_quota' => 500000,
    'daily_used' => 127350,
    'monthly_quota' => 5000000,
    'monthly_used' => 2356789,
    'reset_time' => date('Y-m-d', strtotime('first day of next month'))
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
        'url' => 'https://iexcloud.io/docs/api/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Getting Started', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://iexcloud.io/documentation/getting-started'
    )
);

// Define data types for explorer - IEX Cloud specific
$explorer_data_types = array(
    'quote' => __('Quote Data', 'tradepress'),
    'historical' => __('Historical Data', 'tradepress'),
    'company' => __('Company Data', 'tradepress'),
    'news' => __('News', 'tradepress'),
    'financials' => __('Financials', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
