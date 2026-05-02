<?php
/**
 * TradePress Confluence Reversal Combo Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_CONFLUENCE_REVERSAL_COMBO extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'confluence_reversal_combo';
		$this->name           = 'Confluence Reversal Combo';
		$this->description    = 'Combines oversold momentum, reversal candle, and support proximity into one reversal score.';
		$this->weight         = 20;
		$this->max_score      = 100;
		$this->bullish_values = 'Multiple reversal signals align';
		$this->bearish_values = 'Few/no reversal confirmations';
		$this->priority       = 27;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$rsi                 = isset( $symbol_data['technical']['rsi'] ) ? (float) $symbol_data['technical']['rsi'] : 50.0;
		$oversold_threshold  = isset( $config['oversold_threshold'] ) ? (float) $config['oversold_threshold'] : 30.0;
		$has_reversal_candle = ! empty( $symbol_data['candlestick']['hammer'] ) || ! empty( $symbol_data['candlestick']['bullish_engulfing'] ) || ! empty( $symbol_data['candlestick']['doji'] );
		$near_support        = ! empty( $symbol_data['technical']['near_support'] );

		$score = 0.0;
		if ( $rsi <= $oversold_threshold ) {
			$score += 35.0;
		}
		if ( $has_reversal_candle ) {
			$score += 35.0;
		}
		if ( $near_support ) {
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
		return 'Confluence score from oversold RSI, reversal candle evidence, and support proximity checks.';
	}
}
