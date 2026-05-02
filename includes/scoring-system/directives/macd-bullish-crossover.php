<?php
/**
 * TradePress MACD Bullish Crossover Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_MACD_BULLISH_CROSSOVER extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'macd_bullish_crossover';
		$this->name           = 'MACD Bullish Crossover';
		$this->description    = 'Scores bullish momentum when MACD is above signal and histogram supports continuation.';
		$this->weight         = 25;
		$this->max_score      = 100;
		$this->bullish_values = 'MACD above signal';
		$this->bearish_values = 'MACD below signal';
		$this->priority       = 17;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$macd_data = isset( $symbol_data['technical']['macd'] ) ? $symbol_data['technical']['macd'] : array();
		$macd      = isset( $macd_data['macd'] ) ? (float) $macd_data['macd'] : 0.0;
		$signal    = isset( $macd_data['signal'] ) ? (float) $macd_data['signal'] : 0.0;
		$hist      = isset( $macd_data['histogram'] ) ? (float) $macd_data['histogram'] : ( $macd - $signal );

		if ( $macd <= $signal ) {
			return 10.0;
		}

		$spread = abs( $macd - $signal );
		$score  = 60.0 + min( 30.0, $spread * 50.0 );

		if ( $hist > 0 ) {
			$score += 10.0;
		}

		return round( min( 100.0, $score ), 1 );
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
		return 'Scores MACD bullish crossover strength with bonus for positive histogram confirmation.';
	}
}
