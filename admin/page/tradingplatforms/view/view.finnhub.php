<?php
/**
 * Admin View: API - Finnhub Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Finnhub endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/finnhub/finnhub-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_Finnhub_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'finnhub';
$api_name = 'Finnhub';
$api_description = __('Real-time RESTful APIs for stocks, forex, and crypto data.', 'tradepress');
$api_version = 'v1';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/finnhub-logo.png';

// Get real local status using helper function
$local_status = get_real_api_local_status($api_id);

$service_status = array(
    'status' => 'operational',
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

// Rate limits structure for Finnhub
$rate_limits = array(
    'minute_quota' => 30,
    'minute_used' => 12,
    'daily_quota' => 43200, 
    'daily_used' => 12567,
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
        'url' => 'https://finnhub.io/docs/api',
        'icon' => 'external'
    ),
    array(
        'text' => __('Getting Started', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://finnhub.io/docs/getting-started'
    )
);

// Define data types for explorer - Finnhub specific
$explorer_data_types = array(
    'quote' => __('Quote Data', 'tradepress'),
    'company_profile' => __('Company Profile', 'tradepress'),
    'financials' => __('Financial Data', 'tradepress'),
    'news' => __('News', 'tradepress'),
    'sentiment' => __('Sentiment Analysis', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
