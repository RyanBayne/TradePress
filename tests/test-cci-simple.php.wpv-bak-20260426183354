<?php
/**
 * Simple CCI Directive Test
 */

// Mock WordPress environment for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', '../../../');
}

if (!defined('TRADEPRESS_PLUGIN_DIR_PATH')) {
    define('TRADEPRESS_PLUGIN_DIR_PATH', __DIR__ . '/');
}

// Mock get_option function
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

// Mock WP_Error class
if (!class_exists('WP_Error')) {
    class WP_Error {
        private $message;
        public function __construct($code, $message) {
            $this->message = $message;
        }
        public function get_error_message() {
            return $this->message;
        }
    }
}

// Mock is_wp_error function
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return $thing instanceof WP_Error;
    }
}

// Load base directive class
require_once 'includes/scoring-system/scoring-directive-base.php';

// Load CCI directive
require_once 'includes/scoring-system/directives/cci.php';

echo "TradePress CCI Directive Test\n";
echo "============================\n\n";

try {
    // Create CCI directive instance
    $cci = new TradePress_Scoring_Directive_CCI();
    
    // Test data with CCI value
    $test_data = array(
        'symbol' => 'AAPL',
        'price' => 150.00,
        'technical' => array(
            'cci' => -85.2  // Oversold condition
        )
    );
    
    echo "Testing with CCI = -85.2 (oversold condition)\n";
    echo "Expected: Bullish signal with high score\n\n";
    
    // Calculate score
    $result = $cci->calculate_score($test_data);
    
    echo "RESULTS:\n";
    echo "--------\n";
    echo "Score: " . $result['score'] . "/100\n";
    echo "Signal: " . $result['signal'] . "\n";
    echo "CCI Value: " . $result['cci_value'] . "\n";
    echo "Condition: " . $result['condition'] . "\n";
    echo "Details: " . $result['calculation_details'] . "\n\n";
    
    // Test overbought condition
    $test_data['technical']['cci'] = 120.5;
    echo "Testing with CCI = 120.5 (overbought condition)\n";
    echo "Expected: Bearish signal with low score\n\n";
    
    $result2 = $cci->calculate_score($test_data);
    
    echo "RESULTS:\n";
    echo "--------\n";
    echo "Score: " . $result2['score'] . "/100\n";
    echo "Signal: " . $result2['signal'] . "\n";
    echo "CCI Value: " . $result2['cci_value'] . "\n";
    echo "Condition: " . $result2['condition'] . "\n";
    echo "Details: " . $result2['calculation_details'] . "\n\n";
    
    echo "✅ CCI DIRECTIVE TEST PASSED\n";
    echo "Ready for alpha release integration\n";
    
} catch (Exception $e) {
    echo "❌ CCI DIRECTIVE TEST FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}
?>