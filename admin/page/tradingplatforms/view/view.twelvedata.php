<?php
/**
 * Admin View: API - Twelve Data Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Twelve Data endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/twelvedata/twelvedata-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_TwelveData_Endpoints::get_endpoints();

// Get API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider('twelvedata');

// Set up the variables needed for the template
$api_id = 'twelvedata';
$api_name = 'Twelve Data';
$api_description = __('Financial market data API for stocks, forex, cryptocurrencies, and ETFs.', 'tradepress');
$api_version = 'v1';
$api_logo_url = !empty($provider['logo_url']) ? $provider['logo_url'] : TRADEPRESS_PLUGIN_URL . 'assets/images/twelvedata-logo.png';

// Get real local status using helper function
$local_status = get_real_api_local_status($api_id);

$service_status = array(
    'status' => 'operational',
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

// Rate limits structure for Twelve Data
$rate_limits = array(
    'minute_quota' => 8,
    'minute_used' => 3,
    'daily_quota' => 800, 
    'daily_used' => 423,
    'monthly_quota' => 15000,
    'monthly_used' => 6789,
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
        'url' => 'https://twelvedata.com/docs',
        'icon' => 'external'
    ),
    array(
        'text' => __('Getting Started', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://twelvedata.com/docs/getting-started'
    )
);

// Define data types for explorer - Twelve Data specific
$explorer_data_types = array(
    'time_series' => __('Time Series', 'tradepress'),
    'quote' => __('Quote', 'tradepress'),
    'technical_indicators' => __('Technical Indicators', 'tradepress'),
    'reference_data' => __('Reference Data', 'tradepress'),
    'exchange_rate' => __('Exchange Rate', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
