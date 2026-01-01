<?php
/**
 * Admin View: API - Marketstack Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Marketstack endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/marketstack/marketstack-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_Marketstack_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'marketstack';
$api_name = 'Marketstack';
$api_description = __('Real-time, intraday and historical market data API with global stock coverage.', 'tradepress');
$api_version = 'v1';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/marketstack-logo.png';

// Get real local status using helper function
$local_status = get_real_api_local_status($api_id);

$service_status = array(
    'status' => 'operational',
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

// Rate limits structure for Marketstack
$rate_limits = array(
    'monthly_quota' => 10000,
    'monthly_used' => 4325,
    'daily_quota' => 500,
    'daily_used' => 185,
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
        'text' => __('API Documentation', 'tradepress'),
        'url' => 'https://marketstack.com/documentation',
        'icon' => 'external'
    ),
    array(
        'text' => __('API Reference', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://marketstack.com/quickstart'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'eod' => __('End of Day Data', 'tradepress'),
    'intraday' => __('Intraday Data', 'tradepress'),
    'timeseries' => __('Time Series', 'tradepress'),
    'tickers' => __('Tickers', 'tradepress'),
    'exchanges' => __('Exchanges', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
