<?php
/**
 * TradePress Engulfing Pattern Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_ENGULFING_PATTERN extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'engulfing_pattern';
		$this->name           = 'Engulfing Pattern';
		$this->description    = 'Detects bullish or bearish engulfing reversal structures.';
		$this->weight         = 14;
		$this->max_score      = 100;
		$this->bullish_values = 'Bullish engulfing';
		$this->bearish_values = 'Bearish engulfing';
		$this->priority       = 23;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$bullish = ! empty( $symbol_data['candlestick']['bullish_engulfing'] ) || ! empty( $symbol_data['technical']['patterns']['bullish_engulfing'] );
		$bearish = ! empty( $symbol_data['candlestick']['bearish_engulfing'] ) || ! empty( $symbol_data['technical']['patterns']['bearish_engulfing'] );

		if ( $bullish ) {
			return 85.0;
		}

		if ( $bearish ) {
			return 20.0;
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
		return 'Scores bullish engulfing highest, bearish engulfing low, and zero when no engulfing pattern flag is present.';
	}
}
