<?php
/**
 * TradePress Doji Pattern Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_DOJI_PATTERN extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'doji_pattern';
		$this->name           = 'Doji Pattern';
		$this->description    = 'Detects indecision candles where open and close are nearly equal.';
		$this->weight         = 12;
		$this->max_score      = 100;
		$this->bullish_values = 'Doji near support after decline';
		$this->bearish_values = 'Doji near resistance after rise';
		$this->priority       = 22;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$pattern = ! empty( $symbol_data['candlestick']['doji'] ) || ! empty( $symbol_data['technical']['patterns']['doji'] );
		if ( ! $pattern ) {
			return 0.0;
		}
		return 75.0;
	}

	/**
	 * Get max score.
	 *
	 * @version 1.0.0
	 */
	public function get_max_score( $config = array() ) {
		return 100.0;
	}

	/**
	 * Get explanation.
	 *
	 * @version 1.0.0
	 */
	public function get_explanation( $config = array() ) {
		return 'Returns a positive score when Doji pattern flags are present in candlestick or technical pattern inputs.';
	}
}
