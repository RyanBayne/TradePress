<?php
/**
 * TradePress Attractive Dividend Yield Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_DIVIDEND_YIELD_ATTRACTIVE extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'dividend_yield_attractive';
		$this->name           = 'Attractive Dividend Yield';
		$this->description    = 'Rewards yields above sector baseline while avoiding extreme unsustainable spikes.';
		$this->weight         = 10;
		$this->max_score      = 100;
		$this->bullish_values = 'Yield above benchmark with moderate sustainability range';
		$this->bearish_values = 'Yield below benchmark';
		$this->priority       = 14;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$yield               = isset( $symbol_data['fundamentals']['dividend_yield'] ) ? (float) $symbol_data['fundamentals']['dividend_yield'] : 0.0;
		$benchmark           = isset( $config['benchmark_yield'] ) ? (float) $config['benchmark_yield'] : 3.0;
		$high_risk_threshold = isset( $config['high_risk_threshold'] ) ? (float) $config['high_risk_threshold'] : 10.0;

		if ( $yield <= 0.0 ) {
			return 0.0;
		}

		$relative = ( $yield / max( 0.1, $benchmark ) ) * 100.0;
		$score    = min( 100.0, $relative );

		if ( $yield >= $high_risk_threshold ) {
			$score *= 0.5;
		}

		return round( max( 0.0, $score ), 1 );
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
		return 'Scores dividend yield relative to configurable benchmark with a penalty for extreme unsustainable yield levels.';
	}
}
