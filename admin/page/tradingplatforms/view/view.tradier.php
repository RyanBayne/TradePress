<?php
/**
 * Admin View: API - Tradier Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Tradier endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/tradier/tradier-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_Tradier_Endpoints::get_endpoints();

// Get API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider('tradier');

// Set up the variables needed for the template
$api_id = 'tradier';
$api_name = 'Tradier';
$api_description = __('Developer platform for trading, market data, and account management features.', 'tradepress');
$api_version = 'v1';
$api_logo_url = !empty($provider['logo_url']) ? $provider['logo_url'] : TRADEPRESS_PLUGIN_URL . 'assets/images/tradier-logo.png';

// Get real local status using helper function
$local_status = get_real_api_local_status($api_id);

$service_status = array(
    'status' => 'operational',
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

// Rate limits structure for Tradier
$rate_limits = array(
    'minute_quota' => 60,
    'minute_used' => 34,
    'daily_quota' => 2000, 
    'daily_used' => 1245,
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
        'url' => 'https://documentation.tradier.com/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Developer Hub', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://developer.tradier.com/'
    )
);

// Define data types for explorer - Tradier specific
$explorer_data_types = array(
    'account' => __('Account Info', 'tradepress'),
    'orders' => __('Orders', 'tradepress'),
    'positions' => __('Positions', 'tradepress'),
    'quotes' => __('Quote Data', 'tradepress'),
    'options' => __('Options Data', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
