<?php
/**
 * Admin View: API - Trading212 Tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require the Trading212 endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/trading212/trading212-endpoints.php';

// Include helper functions
require_once __DIR__ . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
if ( class_exists( 'TradePress_Trading212_Endpoints' ) ) {
	$real_endpoints = TradePress_Trading212_Endpoints::get_endpoints();
} else {
	$real_endpoints = array();
}

// Status data for the Trading212 API
$local_status = array(
	'status'  => 'unknown',
	'message' => 'Not checked',
);

$service_status = array(
	'status'       => 'unknown',
	'message'      => 'Not checked',
	'last_updated' => '2025-04-13 09:30:45',
);

// Rate limits
$rate_limits = array(
	'daily_quota'  => 5000,
	'daily_used'   => 1250,
	'hourly_quota' => 800,
	'hourly_used'  => 275,
	'minute_quota' => 150,
	'minute_used'  => 45,
	'reset_time'   => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
);

// Build endpoint metadata from declared provider endpoints
$endpoints = array();
if ( ! empty( $real_endpoints ) ) {
	foreach ( $real_endpoints as $key => $endpoint ) {
		if ( in_array( $key, array( 'get_api_restrictions', 'get_endpoint', 'get_endpoint_url' ) ) ) {
			continue; // Skip utility methods
		}

		$endpoint_status = 'unknown';
		$endpoints[] = array(
			'name'        => ucfirst( str_replace( '_', ' ', $key ) ),
			'endpoint'    => isset( $endpoint['endpoint'] ) ? $endpoint['endpoint'] : '',
			'description' => isset( $endpoint['description'] ) ? $endpoint['description'] : '',
			'usage_count' => 0,
			'status'      => $endpoint_status,
			'method'      => isset( $endpoint['method'] ) ? $endpoint['method'] : 'GET',
			'key'         => $key,
		);
	}
}

// Get Trading212 API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider( 'trading212' );

// Set the API template variables
$api_id          = 'trading212';
$api_name        = 'Trading212';
$api_description = __( 'Commission-free investing platform offering access to stocks, ETFs, and CFDs.', 'tradepress' );
$api_version     = 'v1.0';
$api_logo_url    = ! empty( $provider['icon_url'] ) ? $provider['icon_url'] : TRADEPRESS_PLUGIN_URL . '/assets/images/trading212-logo.png';

// Documentation links
$documentation_links = array(
	array(
		'text' => __( 'API Documentation', 'tradepress' ),
		'url'  => 'https://t212public-api-docs.redoc.ly/',
		'icon' => 'external',
	),
	array(
		'text' => __( 'Developer Portal', 'tradepress' ),
		'url'  => 'https://www.trading212.com/api/',
		'icon' => 'book',
	),
);

// Define data types for explorer
$explorer_data_types = array(
	'account'     => __( 'Account Info', 'tradepress' ),
	'portfolio'   => __( 'Portfolio', 'tradepress' ),
	'watchlists'  => __( 'Watchlists', 'tradepress' ),
	'market_data' => __( 'Market Data', 'tradepress' ),
);

// Include the API tab template
require TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php';
