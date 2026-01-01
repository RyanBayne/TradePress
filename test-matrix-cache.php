<?php
/**
 * Test API Capability Matrix Cache System
 */

// WordPress environment
require_once '../../../wp-config.php';

// Mock WordPress functions if needed
if (!function_exists('get_transient')) {
    function get_transient($key) { return false; }
    function set_transient($key, $value, $expiry) { return true; }
    function delete_transient($key) { return true; }
}

if (!defined('TRADEPRESS_PLUGIN_DIR_PATH')) {
    define('TRADEPRESS_PLUGIN_DIR_PATH', __DIR__ . '/');
}

// Load the matrix cache system
require_once 'includes/scoring-system/api-capability-matrix.php';

echo "TradePress API Capability Matrix Test\n";
echo "====================================\n\n";

// Test matrix building
echo "1. Building capability matrix...\n";
$matrix = TradePress_API_Capability_Matrix::get_matrix();

echo "Matrix built successfully!\n";
echo "Platforms: " . count($matrix['platforms']) . "\n";
echo "Data Types: " . count($matrix['data_types']) . "\n\n";

// Test specific lookups
echo "2. Testing capability lookups...\n";

// Test CCI support
$cci_platforms = TradePress_API_Capability_Matrix::get_platforms_for_data_type('cci');
echo "CCI supported by: " . implode(', ', $cci_platforms) . "\n";

// Test MACD support  
$macd_platforms = TradePress_API_Capability_Matrix::get_platforms_for_data_type('macd');
echo "MACD supported by: " . implode(', ', $macd_platforms) . "\n";

// Test Alpha Vantage capabilities
$av_capabilities = TradePress_API_Capability_Matrix::get_platform_capabilities('alphavantage');
echo "Alpha Vantage supports: " . implode(', ', $av_capabilities) . "\n\n";

// Test freshness requirements
echo "3. Testing freshness requirements...\n";
echo "CCI freshness: " . TradePress_API_Capability_Matrix::get_freshness_requirement('cci') . " seconds\n";
echo "Volume freshness: " . TradePress_API_Capability_Matrix::get_freshness_requirement('volume') . " seconds\n\n";

// Test platform support checks
echo "4. Testing platform support checks...\n";
echo "Alpha Vantage supports CCI: " . (TradePress_API_Capability_Matrix::platform_supports('alphavantage', 'cci') ? 'Yes' : 'No') . "\n";
echo "Finnhub supports CCI: " . (TradePress_API_Capability_Matrix::platform_supports('finnhub', 'cci') ? 'Yes' : 'No') . "\n\n";

// Show cache status
echo "5. Cache status...\n";
$status = TradePress_API_Capability_Matrix::get_cache_status();
echo "Cached: " . ($status['cached'] ? 'Yes' : 'No') . "\n";
echo "Last Updated: " . date('Y-m-d H:i:s', $status['last_updated']) . "\n";
echo "Expires: " . date('Y-m-d H:i:s', $status['expires']) . "\n";

echo "\n✅ API Capability Matrix Cache System Working!\n";
?>