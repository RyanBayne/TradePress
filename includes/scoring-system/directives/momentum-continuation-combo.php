<?php
/**
 * TradePress Momentum Continuation Combo Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_MOMENTUM_CONTINUATION_COMBO extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'momentum_continuation_combo';
		$this->name           = 'Momentum Continuation Combo';
		$this->description    = 'Combines trend, momentum, and participation signals to score continuation setups.';
		$this->weight         = 20;
		$this->max_score      = 100;
		$this->bullish_values = 'Trend + momentum + volume align';
		$this->bearish_values = 'Alignment weak or absent';
		$this->priority       = 28;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$price      = isset( $symbol_data['price'] ) ? (float) $symbol_data['price'] : 0.0;
		$ema_20     = isset( $symbol_data['technical']['ema_20'] ) ? (float) $symbol_data['technical']['ema_20'] : 0.0;
		$macd_data  = isset( $symbol_data['technical']['macd'] ) ? $symbol_data['technical']['macd'] : array();
		$macd       = isset( $macd_data['macd'] ) ? (float) $macd_data['macd'] : 0.0;
		$signal     = isset( $macd_data['signal'] ) ? (float) $macd_data['signal'] : 0.0;
		$volume     = isset( $symbol_data['volume'] ) ? (float) $symbol_data['volume'] : 0.0;
		$avg_volume = isset( $symbol_data['avg_volume'] ) ? (float) $symbol_data['avg_volume'] : 0.0;

		$score = 0.0;
		if ( $price > 0.0 && $ema_20 > 0.0 && $price >= $ema_20 ) {
			$score += 35.0;
		}
		if ( $macd > $signal ) {
			$score += 35.0;
		}
		if ( $volume > 0.0 && $avg_volume > 0.0 && $volume >= ( 1.2 * $avg_volume ) ) {
			$score += 30.0;
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
		return 'Continuation score from price-above-EMA trend check, bullish MACD relation, and relative volume confirmation.';
	}
}
