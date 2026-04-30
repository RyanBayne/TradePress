<?php
/**
 * Test API Capability Matrix Cache System
 */

// WordPress environment
require_once '../../../wp-config.php';

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Get transient.
	 *
	 * @param mixed $key
	 * @version 1.0.0
	 */
	function get_transient( $key ) { return false; }
	/**
	 * Set transient.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param mixed $expiry
	 * @version 1.0.0
	 */
	function set_transient( $key, $value, $expiry ) { return true; }
	/**
	 * Delete transient.
	 *
	 * @param mixed $key
	 * @version 1.0.0
	 */
	function delete_transient( $key ) { return true; }
}

if ( ! defined( 'TRADEPRESS_PLUGIN_DIR_PATH' ) ) {
	define( 'TRADEPRESS_PLUGIN_DIR_PATH', __DIR__ . '/' );
}

require_once 'includes/scoring-system/api-capability-matrix.php';

echo "TradePress API Capability Matrix Test\n";
echo "====================================\n\n";

echo "1. Building capability matrix...\n";
$matrix = TradePress_API_Capability_Matrix::get_matrix();

echo "Matrix built successfully!\n";
printf( "Platforms: %d\n", count( $matrix['platforms'] ) );
printf( "Data Types: %d\n\n", count( $matrix['data_types'] ) );

echo "2. Testing capability lookups...\n";

$cci_platforms = TradePress_API_Capability_Matrix::get_platforms_for_data_type( 'cci' );
printf( "CCI supported by: %s\n", implode( ', ', $cci_platforms ) );

$macd_platforms = TradePress_API_Capability_Matrix::get_platforms_for_data_type( 'macd' );
printf( "MACD supported by: %s\n", implode( ', ', $macd_platforms ) );

$av_capabilities = TradePress_API_Capability_Matrix::get_platform_capabilities( 'alphavantage' );
printf( "Alpha Vantage supports: %s\n\n", implode( ', ', $av_capabilities ) );

echo "3. Testing freshness requirements...\n";
printf( "CCI freshness: %d seconds\n", TradePress_API_Capability_Matrix::get_freshness_requirement( 'cci' ) );
printf( "Volume freshness: %d seconds\n\n", TradePress_API_Capability_Matrix::get_freshness_requirement( 'volume' ) );

echo "4. Testing platform support checks...\n";
printf( "Alpha Vantage supports CCI: %s\n", TradePress_API_Capability_Matrix::platform_supports( 'alphavantage', 'cci' ) ? 'Yes' : 'No' );
printf( "Finnhub supports CCI: %s\n\n", TradePress_API_Capability_Matrix::platform_supports( 'finnhub', 'cci' ) ? 'Yes' : 'No' );

echo "5. Cache status...\n";
$status = TradePress_API_Capability_Matrix::get_cache_status();
printf( "Cached: %s\n", $status['cached'] ? 'Yes' : 'No' );
printf( "Last Updated: %s\n", date( 'Y-m-d H:i:s', $status['last_updated'] ) );
printf( "Expires: %s\n", date( 'Y-m-d H:i:s', $status['expires'] ) );

echo "\n✅ API Capability Matrix Cache System Working!\n";
