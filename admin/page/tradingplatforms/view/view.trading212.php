<?php
/**
 * Admin View: API - Trading212 Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Trading212 endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/trading212/trading212-endpoints.php';

// Include helper functions
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
if (class_exists('TradePress_Trading212_Endpoints')) {
    $real_endpoints = TradePress_Trading212_Endpoints::get_endpoints();
} else {
    $real_endpoints = array();
}

// Status data for the Trading212 API
$local_status = array(
    'status' => 'active', // or 'inactive'
    'message' => 'Properly configured and working'
);

$service_status = array(
    'status' => 'operational', // or 'disruption', 'outage', 'maintenance'
    'message' => 'All systems operational',
    'last_updated' => '2025-04-13 09:30:45'
);

// Rate limits
$rate_limits = array(
    'daily_quota' => 5000,
    'daily_used' => 1250,
    'hourly_quota' => 800,
    'hourly_used' => 275,
    'minute_quota' => 150,
    'minute_used' => 45,
    'reset_time' => date('Y-m-d H:i:s', strtotime('+1 day'))
);

// Generate usage data for endpoints
$endpoints = array();
if (!empty($real_endpoints)) {
    foreach ($real_endpoints as $key => $endpoint) {
        if (in_array($key, array('get_api_restrictions', 'get_endpoint', 'get_endpoint_url'))) {
            continue; // Skip utility methods
        }
        
        $demo_status = rand(0, 10) > 1 ? 'active' : 'maintenance'; // 90% chance of being active
        $endpoints[] = array(
            'name' => ucfirst(str_replace('_', ' ', $key)),
            'endpoint' => isset($endpoint['endpoint']) ? $endpoint['endpoint'] : '',
            'description' => isset($endpoint['description']) ? $endpoint['description'] : '',
            'usage_count' => rand(100, 2000),
            'status' => $demo_status,
            'method' => isset($endpoint['method']) ? $endpoint['method'] : 'GET',
            'key' => $key
        );
    }
} else {
    // Fallback dummy data if the endpoints class isn't fully implemented yet
    $dummy_endpoints = array(
        'account_info' => 'Get account information and balance',
        'portfolio' => 'Get portfolio positions and performance',
        'watchlists' => 'Manage personal watchlists',
        'market_quotes' => 'Get real-time quotes for instruments',
        'instruments' => 'Get available trading instruments',
        'orders' => 'Manage and view orders',
        'transactions' => 'View transaction history',
        'candles' => 'Get historical price data'
    );
    
    foreach ($dummy_endpoints as $key => $description) {
        $endpoints[] = array(
            'name' => ucfirst(str_replace('_', ' ', $key)),
            'endpoint' => '/api/v1/' . $key,
            'description' => $description,
            'usage_count' => rand(100, 2000),
            'status' => 'active',
            'method' => 'GET',
            'key' => $key
        );
    }
}

// Get Trading212 API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider('trading212');

// Set the API template variables
$api_id = 'trading212';
$api_name = 'Trading212';
$api_description = __('Commission-free investing platform offering access to stocks, ETFs, and CFDs.', 'tradepress');
$api_version = 'v1.0';
$api_logo_url = !empty($provider['icon_url']) ? $provider['icon_url'] : TRADEPRESS_PLUGIN_URL . '/assets/images/trading212-logo.png';

// Documentation links
$documentation_links = array(
    array(
        'text' => __('API Documentation', 'tradepress'),
        'url' => 'https://t212public-api-docs.redoc.ly/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Developer Portal', 'tradepress'),
        'url' => 'https://www.trading212.com/api/',
        'icon' => 'book'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'account' => __('Account Info', 'tradepress'),
    'portfolio' => __('Portfolio', 'tradepress'),
    'watchlists' => __('Watchlists', 'tradepress'),
    'market_data' => __('Market Data', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');