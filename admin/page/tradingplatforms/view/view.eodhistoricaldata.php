<?php
/**
 * Admin View: API - EOD Historical Data Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the EOD Historical Data endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/eodhistoricaldata/eodhistoricaldata-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_EODHistoricalData_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'eodhistoricaldata';
$api_name = 'EOD Historical Data';
$api_description = __('Comprehensive financial data API for stocks, ETFs, funds, and fundamental data.', 'tradepress');
$api_version = 'v1';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/eodhistoricaldata-logo.png';

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

// Rate limits structure
$rate_limits = array(
    'daily_quota' => 100000,
    'daily_used' => 34567,
    'monthly_quota' => 3000000,
    'monthly_used' => 1245678,
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
        'url' => 'https://eodhistoricaldata.com/financial-apis/',
        'icon' => 'external'
    ),
    array(
        'text' => __('API Reference', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://eodhistoricaldata.com/financial-apis/api-documentation/'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'eod' => __('End of Day Data', 'tradepress'),
    'intraday' => __('Intraday Data', 'tradepress'),
    'fundamentals' => __('Fundamentals', 'tradepress'),
    'technical' => __('Technical Indicators', 'tradepress'),
    'splits_dividends' => __('Splits & Dividends', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
