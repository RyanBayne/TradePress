<?php
/**
 * TradePress Three White Soldiers Pattern Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_THREE_SOLDIERS_PATTERN extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'three_soldiers_pattern';
		$this->name           = 'Three White Soldiers';
		$this->description    = 'Detects three-consecutive bullish candle continuation/reversal structures.';
		$this->weight         = 16;
		$this->max_score      = 100;
		$this->bullish_values = 'Three white soldiers pattern detected';
		$this->bearish_values = 'Pattern absent';
		$this->priority       = 26;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$present = ! empty( $symbol_data['candlestick']['three_white_soldiers'] ) || ! empty( $symbol_data['technical']['patterns']['three_white_soldiers'] );
		return $present ? 90.0 : 0.0;
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
		return 'Returns a high bullish score when three-white-soldiers pattern flags are present.';
	}
}
