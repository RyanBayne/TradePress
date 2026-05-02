<?php
/**
 * TradePress Moving Average Crossover Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_MA_CROSSOVER extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'ma_crossover';
		$this->name           = 'Moving Average Crossover';
		$this->description    = 'Scores bullish/bearish trend shifts from fast and slow moving-average relationships.';
		$this->weight         = 15;
		$this->max_score      = 100;
		$this->bullish_values = 'Fast MA above slow MA with improving spread';
		$this->bearish_values = 'Fast MA below slow MA';
		$this->priority       = 16;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$fast      = isset( $symbol_data['technical']['ma_50'] ) ? (float) $symbol_data['technical']['ma_50'] : 0.0;
		$slow      = isset( $symbol_data['technical']['ma_200'] ) ? (float) $symbol_data['technical']['ma_200'] : 0.0;
		$prev_fast = isset( $symbol_data['technical']['prev_ma_50'] ) ? (float) $symbol_data['technical']['prev_ma_50'] : $fast;
		$prev_slow = isset( $symbol_data['technical']['prev_ma_200'] ) ? (float) $symbol_data['technical']['prev_ma_200'] : $slow;

		if ( $fast <= 0.0 || $slow <= 0.0 ) {
			return 0.0;
		}

		$spread      = $fast - $slow;
		$prev_spread = $prev_fast - $prev_slow;

		if ( $spread <= 0 ) {
			return 20.0;
		}

		$base = 70.0;
		if ( $spread > $prev_spread ) {
			$base += 20.0;
		}

		return round( min( 100.0, $base ), 1 );
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
		return 'Scores fast-vs-slow MA alignment and spread improvement (golden-cross style behaviour).';
	}
}
