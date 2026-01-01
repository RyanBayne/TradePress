<?php
/**
 * Admin View: API - StockTwits Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the StockTwits endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/stocktwits/stocktwits-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_StockTwits_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'stocktwits';
$api_name = 'StockTwits';
$api_description = __('Social platform for investors and traders to share ideas and market insights.', 'tradepress');
$api_version = 'v2';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/stocktwits-logo.png';

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

// Rate limits structure for StockTwits
$rate_limits = array(
    'minute_quota' => 200,
    'minute_used' => 55,
    'hourly_quota' => 1000, 
    'hourly_used' => 580,
    'daily_quota' => 10000,
    'daily_used' => 4500,
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
        'url' => 'https://api.stocktwits.com/developers/docs',
        'icon' => 'external'
    ),
    array(
        'text' => __('Developer Portal', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://api.stocktwits.com/developers'
    )
);

// Define data types for explorer - StockTwits specific
$explorer_data_types = array(
    'symbol_stream' => __('Symbol Stream', 'tradepress'),
    'trending' => __('Trending Symbols', 'tradepress'),
    'user_stream' => __('User Stream', 'tradepress'),
    'symbol_info' => __('Symbol Info', 'tradepress'),
    'sentiment' => __('Sentiment Data', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
