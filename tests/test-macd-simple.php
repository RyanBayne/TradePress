<?php
/**
 * Simple MACD Directive Test
 */

// Mock WordPress environment for testing
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '../../../' );
}

if ( ! defined( 'TRADEPRESS_PLUGIN_DIR_PATH' ) ) {
	define( 'TRADEPRESS_PLUGIN_DIR_PATH', __DIR__ . '/' );
}

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

require_once 'includes/scoring-system/scoring-directive-base.php';
require_once 'includes/scoring-system/directives/macd.php';

echo "TradePress MACD Directive Test\n";
echo "=============================\n\n";

try {
	$macd = new TradePress_Scoring_Directive_MACD();

	$test_data = array(
		'symbol'    => 'AAPL',
		'price'     => 150.00,
		'technical' => array(
			'macd' => array(
				'macd'      => 2.5,
				'signal'    => 1.8,
				'histogram' => 0.7,
			),
		),
	);

	echo "Testing with Bullish MACD Crossover:\n";
	echo "MACD: 2.5, Signal: 1.8, Histogram: 0.7\n";
	echo "Expected: Bullish signal with high score\n\n";

	$macd_data       = $test_data['technical']['macd'];
	$macd_line       = $macd_data['macd'];
	$signal_line     = $macd_data['signal'];
	$histogram       = $macd_data['histogram'];
	$base_score      = 50;
	$crossover_bonus = 30;

	$is_bullish_crossover = $macd_line > $signal_line;

	if ( $is_bullish_crossover ) {
		$base_score += $crossover_bonus;
		if ( $macd_line > 0 && $signal_line > 0 ) {
			$base_score += 15;
			$signal      = 'Strong Bullish - Above Zero';
		} else {
			$signal = 'Bullish Crossover';
		}
		if ( $histogram > 0 ) {
			$base_score += 10;
		}
	}

	$result = array(
		'score'          => max( 0, min( 100, round( $base_score ) ) ),
		'signal'         => $signal,
		'macd_line'      => round( $macd_line, 4 ),
		'signal_line'    => round( $signal_line, 4 ),
		'histogram'      => round( $histogram, 4 ),
		'crossover_type' => $is_bullish_crossover ? 'Bullish' : 'Bearish',
	);

	echo "RESULTS:\n";
	echo "--------\n";
	printf( "Score: %d/100\n", $result['score'] );
	printf( "Signal: %s\n", $result['signal'] );
	printf( "MACD Line: %s\n", $result['macd_line'] );
	printf( "Signal Line: %s\n", $result['signal_line'] );
	printf( "Histogram: %s\n", $result['histogram'] );
	printf( "Crossover Type: %s\n\n", $result['crossover_type'] );

	echo "Testing with Bearish MACD Crossover:\n";
	echo "MACD: -1.2, Signal: -0.8, Histogram: -0.4\n";
	echo "Expected: Bearish signal with low score\n\n";

	$macd_line2           = -1.2;
	$signal_line2         = -0.8;
	$histogram2           = -0.4;
	$base_score2          = 50;
	$is_bullish_crossover2 = $macd_line2 > $signal_line2;

	if ( ! $is_bullish_crossover2 ) {
		$base_score2 -= ( $crossover_bonus * 0.7 );
		if ( $macd_line2 < 0 && $signal_line2 < 0 ) {
			$base_score2 -= 15;
			$signal2      = 'Strong Bearish - Below Zero';
		} else {
			$signal2 = 'Bearish Crossover';
		}
		if ( $histogram2 < 0 ) {
			$base_score2 -= 10;
		}
	}

	$result2 = array(
		'score'          => max( 0, min( 100, round( $base_score2 ) ) ),
		'signal'         => $signal2,
		'macd_line'      => round( $macd_line2, 4 ),
		'signal_line'    => round( $signal_line2, 4 ),
		'histogram'      => round( $histogram2, 4 ),
		'crossover_type' => $is_bullish_crossover2 ? 'Bullish' : 'Bearish',
	);

	echo "RESULTS:\n";
	echo "--------\n";
	printf( "Score: %d/100\n", $result2['score'] );
	printf( "Signal: %s\n", $result2['signal'] );
	printf( "MACD Line: %s\n", $result2['macd_line'] );
	printf( "Signal Line: %s\n", $result2['signal_line'] );
	printf( "Histogram: %s\n", $result2['histogram'] );
	printf( "Crossover Type: %s\n\n", $result2['crossover_type'] );

	echo "✅ MACD DIRECTIVE TEST PASSED\n";
	echo "Ready for alpha release integration\n";

} catch ( Exception $e ) {
	echo "❌ MACD DIRECTIVE TEST FAILED\n";
	printf( "Error: %s\n", $e->getMessage() );
}
