<?php
/**
 * TradePress Volume Surge Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_VOLUME_SURGE extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'volume_surge';
		$this->name           = 'Volume Surge';
		$this->description    = 'Scores unusual volume spikes versus average volume.';
		$this->weight         = 12;
		$this->max_score      = 100;
		$this->bullish_values = 'Volume significantly above average';
		$this->bearish_values = 'Volume at/below average';
		$this->priority       = 21;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$current = isset( $symbol_data['volume'] ) ? (float) $symbol_data['volume'] : 0.0;
		$average = isset( $symbol_data['avg_volume'] ) ? (float) $symbol_data['avg_volume'] : 0.0;
		$target  = isset( $config['target_ratio'] ) ? (float) $config['target_ratio'] : 2.0;

		if ( $current <= 0.0 || $average <= 0.0 ) {
			return 0.0;
		}

		$ratio = $current / $average;
		if ( $ratio <= 1.0 ) {
			return 10.0;
		}

		$score = min( 100.0, ( $ratio / max( 0.1, $target ) ) * 100.0 );
		return round( $score, 1 );
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
		return 'Scores the current-to-average volume ratio, with higher ratios indicating stronger participation.';
	}
}
