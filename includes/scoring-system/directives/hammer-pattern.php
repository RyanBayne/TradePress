<?php
/**
 * TradePress Hammer Pattern Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_HAMMER_PATTERN extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'hammer_pattern';
		$this->name           = 'Hammer Pattern';
		$this->description    = 'Detects hammer-style reversal candles.';
		$this->weight         = 12;
		$this->max_score      = 100;
		$this->bullish_values = 'Hammer candle in selloff context';
		$this->bearish_values = 'No hammer signal';
		$this->priority       = 24;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$hammer = ! empty( $symbol_data['candlestick']['hammer'] ) || ! empty( $symbol_data['technical']['patterns']['hammer'] );
		return $hammer ? 80.0 : 0.0;
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
		return 'Returns a positive reversal-oriented score when hammer pattern flags are present.';
	}
}
