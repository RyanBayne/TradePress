<?php
/**
 * TradePress Pin Bar Pattern Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_PIN_BAR_PATTERN extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'pin_bar_pattern';
		$this->name           = 'Pin Bar Pattern';
		$this->description    = 'Detects wick-rejection candles associated with failed breakouts and reversals.';
		$this->weight         = 12;
		$this->max_score      = 100;
		$this->bullish_values = 'Bullish pin bar rejection';
		$this->bearish_values = 'Bearish pin bar rejection';
		$this->priority       = 25;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$bullish = ! empty( $symbol_data['candlestick']['bullish_pin_bar'] ) || ! empty( $symbol_data['technical']['patterns']['bullish_pin_bar'] );
		$bearish = ! empty( $symbol_data['candlestick']['bearish_pin_bar'] ) || ! empty( $symbol_data['technical']['patterns']['bearish_pin_bar'] );

		if ( $bullish ) {
			return 82.0;
		}
		if ( $bearish ) {
			return 18.0;
		}
		return 0.0;
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
		return 'Scores bullish or bearish pin-bar pattern flags with directional weighting.';
	}
}
