<?php
/**
 * TradePress RSI Oversold Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_RSI_OVERSOLD extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'rsi_oversold';
		$this->name           = 'RSI Oversold';
		$this->description    = 'Scores potential mean-reversion opportunities when RSI is oversold.';
		$this->weight         = 20;
		$this->max_score      = 100;
		$this->bullish_values = 'RSI <= oversold threshold';
		$this->bearish_values = 'RSI above oversold threshold';
		$this->priority       = 19;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$oversold_threshold = isset( $config['oversold_threshold'] ) ? (float) $config['oversold_threshold'] : 30.0;
		$extreme_threshold  = isset( $config['extreme_threshold'] ) ? (float) $config['extreme_threshold'] : 20.0;
		$rsi               = isset( $symbol_data['technical']['rsi'] ) ? (float) $symbol_data['technical']['rsi'] : 0.0;

		if ( $rsi <= 0.0 ) {
			return 0.0;
		}

		if ( $rsi > $oversold_threshold ) {
			return 0.0;
		}

		$score = 60.0 + ( ( $oversold_threshold - $rsi ) / max( 1.0, $oversold_threshold ) ) * 30.0;

		if ( $rsi <= $extreme_threshold ) {
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
		return 'Scores lower RSI values higher up to a capped maximum to represent oversold rebound potential.';
	}
}
