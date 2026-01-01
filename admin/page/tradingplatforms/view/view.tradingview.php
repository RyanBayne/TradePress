<?php
/**
 * Admin View: API - TradingView Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the TradingView endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/tradingview/tradingview-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_TradingView_Endpoints::get_endpoints();

// Get API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider('tradingview');

// Set up the variables needed for the template
$api_id = 'tradingview';
$api_name = 'TradingView';
$api_description = __('Advanced charting platform with real-time market data and technical analysis tools.', 'tradepress');
$api_version = 'v1';
$api_logo_url = !empty($provider['logo_url']) ? $provider['logo_url'] : TRADEPRESS_PLUGIN_URL . 'assets/images/tradingview-logo.png';

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

// Rate limits structure for TradingView
$rate_limits = array(
    'minute_quota' => 120,
    'minute_used' => 45,
    'daily_quota' => 10000,
    'daily_used' => 3245,
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
        'text' => __('Charting Library', 'tradepress'),
        'url' => 'https://www.tradingview.com/charting-library-docs/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Public API', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://www.tradingview.com/rest-api-docs/'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'charts' => __('Charts', 'tradepress'),
    'technical_analysis' => __('Technical Analysis', 'tradepress'),
    'screener' => __('Screener', 'tradepress'),
    'ideas' => __('Trading Ideas', 'tradepress'),
    'watchlists' => __('Watchlists', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
