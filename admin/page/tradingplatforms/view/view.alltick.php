<?php
/**
 * Admin View: API - AllTick Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the AllTick endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/alltick/alltick-endpoints.php';

// Include helper functions
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_AllTick_Endpoints::get_endpoints();

// Dummy data for demonstration
$local_status = array(
    'status' => 'active', // or 'inactive'
    'message' => 'Properly configured and working'
);

$service_status = array(
    'status' => 'operational', // or 'disruption', 'outage', 'maintenance'
    'message' => 'All systems operational',
    'last_updated' => '2025-04-11 15:45:30'
);

$rate_limits = array(
    'daily_quota' => 10000,
    'daily_used' => 4356,
    'hourly_quota' => 1000,
    'hourly_used' => 234,
    'minute_quota' => 100,
    'minute_used' => 12,
    'reset_time' => '2025-04-12 00:00:00'
);

// Generate dummy usage data for real endpoints
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
        'key' => $key // Save the original endpoint key for accessing example responses
    );
}

// Set the API template variables
$api_id = 'alltick';
$api_name = 'AllTick';
$api_description = __('Comprehensive real-time and historical financial data for global markets.', 'tradepress');
$api_version = 'v2.3.4';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/alltick-logo.png';

// Documentation links
$documentation_links = array(
    array(
        'text' => __('Official Docs', 'tradepress'),
        'url' => '#',
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
    'historical' => __('Historical Data', 'tradepress'),
    'company' => __('Company Data', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
?>

