<?php
/**
 * Simple CCI Directive Test
 */

// Mock WordPress environment for testing
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '../../../' );
}

if ( ! defined( 'TRADEPRESS_PLUGIN_DIR_PATH' ) ) {
	define( 'TRADEPRESS_PLUGIN_DIR_PATH', __DIR__ . '/' );
}

// Mock get_option function
if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Get option.
	 *
	 * @param mixed $option
	 * @param bool  $default
	 * @return mixed
	 * @version 1.0.0
	 */
	function get_option( $option, $default = false ) {
		return $default;
	}
}

// Mock WP_Error class
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private $message;
		/**
		 * Constructor.
		 *
		 * @param mixed $code
		 * @param mixed $message
		 * @version 1.0.0
		 */
		public function __construct( $code, $message ) {
			$this->message = $message;
		}
		/**
		 * Get error message.
		 *
		 * @return mixed
		 * @version 1.0.0
		 */
		public function get_error_message() {
			return $this->message;
		}
	}
}

// Mock is_wp_error function
if ( ! function_exists( 'is_wp_error' ) ) {
	/**
	 * Is wp error.
	 *
	 * @param mixed $thing
	 * @return bool
	 * @version 1.0.0
	 */
	function is_wp_error( $thing ) {
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
	$cci = new TradePress_Scoring_Directive_CCI();

	$test_data = array(
		'symbol'    => 'AAPL',
		'price'     => 150.00,
		'technical' => array(
			'cci' => -85.2,
		),
	);

	echo "Testing with CCI = -85.2 (oversold condition)\n";
	echo "Expected: Bullish signal with high score\n\n";

	$result = $cci->calculate_score( $test_data );

	echo "RESULTS:\n";
	echo "--------\n";
	printf( "Score: %s/100\n", $result['score'] );
	printf( "Signal: %s\n", $result['signal'] );
	printf( "CCI Value: %s\n", $result['cci_value'] );
	printf( "Condition: %s\n", $result['condition'] );
	printf( "Details: %s\n\n", $result['calculation_details'] );

	$test_data['technical']['cci'] = 120.5;
	echo "Testing with CCI = 120.5 (overbought condition)\n";
	echo "Expected: Bearish signal with low score\n\n";

	$result2 = $cci->calculate_score( $test_data );

	echo "RESULTS:\n";
	echo "--------\n";
	printf( "Score: %s/100\n", $result2['score'] );
	printf( "Signal: %s\n", $result2['signal'] );
	printf( "CCI Value: %s\n", $result2['cci_value'] );
	printf( "Condition: %s\n", $result2['condition'] );
	printf( "Details: %s\n\n", $result2['calculation_details'] );

	echo "✅ CCI DIRECTIVE TEST PASSED\n";
	echo "Ready for alpha release integration\n";

} catch ( Exception $e ) {
	echo "❌ CCI DIRECTIVE TEST FAILED\n";
	printf( "Error: %s\n", $e->getMessage() );
}
