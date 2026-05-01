<?php
/**
 * Admin View: API - Quandl Tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Require the Quandl endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/quandl/quandl-endpoints.php';

// Include helper functions if not already included
require_once __DIR__ . '/helpers/api-tab-helpers.php';

// Get real endpoints from the class
$real_endpoints = TradePress_Quandl_Endpoints::get_endpoints();

// Set up the variables needed for the template
$api_id          = 'quandl';
$api_name        = 'Quandl';
$api_description = __( 'Financial, economic, and alternative datasets for investment professionals.', 'tradepress' );
$api_version     = 'v3';
$api_logo_url    = TRADEPRESS_PLUGIN_URL . 'assets/images/quandl-logo.png';

// Status data
$local_status = array(
	'status'  => 'unknown',
	'message' => 'Not checked',
);

$service_status = array(
	'status'       => 'unknown',
	'message'      => 'Not checked',
	'last_updated' => date( 'Y-m-d H:i:s' ),
);

// Rate limits structure for Quandl
$rate_limits = array(
	'daily_quota'   => 50000,
	'daily_used'    => 12450,
	'monthly_quota' => 1000000,
	'monthly_used'  => 345670,
	'reset_time'    => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
);

// Build endpoint metadata from declared provider endpoints
$endpoints = array();
foreach ( $real_endpoints as $key => $endpoint ) {
	if ( ! is_array( $endpoint ) || ! isset( $endpoint['endpoint'] ) ) {
		continue; // Skip non-endpoint items or utility methods
	}

	$endpoint_status = 'unknown';
	$endpoints[] = array(
		'name'        => ucfirst( str_replace( '_', ' ', $key ) ),
		'endpoint'    => $endpoint['endpoint'],
		'description' => isset( $endpoint['description'] ) ? $endpoint['description'] : '',
		'usage_count' => 0,
		'status'      => $endpoint_status,
		'method'      => isset( $endpoint['method'] ) ? $endpoint['method'] : 'GET',
		'key'         => $key,
	);
}

// Documentation links
$documentation_links = array(
	array(
		'text' => __( 'API Documentation', 'tradepress' ),
		'url'  => 'https://docs.data.nasdaq.com/docs',
		'icon' => 'external',
	),
	array(
		'text' => __( 'API Reference', 'tradepress' ),
		'icon' => 'book',
		'url'  => 'https://docs.data.nasdaq.com/docs/in-depth-usage',
	),
);

// Define data types for explorer
$explorer_data_types = array(
	'time_series' => __( 'Time Series', 'tradepress' ),
	'tables'      => __( 'Tables', 'tradepress' ),
	'databases'   => __( 'Databases', 'tradepress' ),
	'codes'       => __( 'Codes', 'tradepress' ),
	'datasets'    => __( 'Datasets', 'tradepress' ),
);

// Include the API tab template
require TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php';
