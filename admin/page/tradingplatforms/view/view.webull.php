<?php
/**
 * Admin View: API - WeBull Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the WeBull endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/webull/webull-endpoints.php';

// Include helper functions
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_WeBull_Endpoints::get_endpoints();

// Get API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider('webull');

// Status data for the WeBull API
$local_status = array(
    'status' => 'active', // or 'inactive'
    'message' => 'Properly configured and working'
);

$service_status = array(
    'status' => 'operational', // or 'disruption', 'outage', 'maintenance'
    'message' => 'All systems operational',
    'last_updated' => '2025-04-13 09:30:45'
);

$rate_limits = array(
    'daily_quota' => 5000,
    'daily_used' => 1245,
    'hourly_quota' => 500,
    'hourly_used' => 87,
    'minute_quota' => 50,
    'minute_used' => 8,
    'reset_time' => '2025-04-14 00:00:00'
);

// Generate usage data for endpoints
$endpoints = array();
foreach ($real_endpoints as $key => $endpoint) {
    $demo_status = rand(0, 10) > 1 ? 'active' : 'maintenance'; // 90% chance of being active
    $endpoints[] = array(
        'name' => ucfirst(str_replace('_', ' ', $key)),
        'endpoint' => $endpoint['endpoint'],
        'description' => $endpoint['description'],
        'usage_count' => rand(100, 2000),
        'status' => $demo_status,
        'method' => isset($endpoint['method']) ? $endpoint['method'] : 'GET',
    );
}

// Set the API template variables
$api_id = 'webull';
$api_name = 'WeBull';
$api_description = __('Commission-free online broker with advanced trading tools', 'tradepress');
$api_version = 'v1';
$api_logo_url = !empty($provider['logo_url']) ? $provider['logo_url'] : TRADEPRESS_PLUGIN_URL . 'assets/images/webull-logo.png';

// Documentation links
$documentation_links = array(
    array(
        'text' => __('Official Website', 'tradepress'),
        'url' => 'https://www.webull.com/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Integration Guide', 'tradepress'),
        'icon' => 'book',
        'url' => '#'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'quote' => __('Quote Data', 'tradepress'),
    'portfolio' => __('Portfolio Data', 'tradepress'),
    'orders' => __('Orders Data', 'tradepress'),
    'watchlist' => __('Watchlist Data', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
?>