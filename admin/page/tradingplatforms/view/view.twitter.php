<?php
/**
 * Admin View: API - Twitter Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Twitter endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/twitter/twitter-endpoints.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TRADEPRESS_TWITTER_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id = 'twitter';
$api_name = 'Twitter';
$api_description = __('Social media platform API for tweets, trends, and user data.', 'tradepress');
$api_version = 'v2';
$api_logo_url = TRADEPRESS_PLUGIN_URL . 'assets/images/twitter-logo.png';

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

// Rate limits structure for Twitter
$rate_limits = array(
    'minute_quota' => 300,
    'minute_used' => 45,
    'hourly_quota' => 500, 
    'hourly_used' => 245,
    'daily_quota' => 10000,
    'daily_used' => 2567,
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
        'url' => 'https://developer.twitter.com/en/docs/twitter-api',
        'icon' => 'external'
    ),
    array(
        'text' => __('Developer Portal', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://developer.twitter.com/en/portal/dashboard'
    )
);

// Define data types for explorer - Twitter specific
$explorer_data_types = array(
    'tweet_search' => __('Tweet Search', 'tradepress'),
    'user_timeline' => __('User Timeline', 'tradepress'),
    'trends' => __('Trending Topics', 'tradepress'),
    'cashtag_search' => __('Cashtag Search', 'tradepress'),
    'user_lookup' => __('User Lookup', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
