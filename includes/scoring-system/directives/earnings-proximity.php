<?php
/**
 * TradePress Earnings Proximity Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_EARNINGS_PROXIMITY extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'earnings_proximity';
		$this->name           = 'Earnings Proximity';
		$this->description    = 'Scores symbols with upcoming earnings events inside a configurable window.';
		$this->weight         = 15;
		$this->max_score      = 100;
		$this->bullish_values = 'Earnings event inside configured window';
		$this->bearish_values = 'No near-term event';
		$this->priority       = 15;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$window_days = isset( $config['window_days'] ) ? (int) $config['window_days'] : 7;
		$earnings    = isset( $symbol_data['earnings'] ) && is_array( $symbol_data['earnings'] ) ? $symbol_data['earnings'] : array();

		if ( empty( $earnings ) ) {
			return 0.0;
		}

		$now = time();
		$min = null;

		foreach ( $earnings as $event ) {
			if ( ! isset( $event['date'] ) ) {
				continue;
			}
			$ts = strtotime( (string) $event['date'] );
			if ( false === $ts || $ts < $now ) {
				continue;
			}
			$days = (int) floor( ( $ts - $now ) / DAY_IN_SECONDS );
			if ( null === $min || $days < $min ) {
				$min = $days;
			}
		}

		if ( null === $min || $min > $window_days ) {
			return 0.0;
		}

		$score = ( 1.0 - ( $min / max( 1, $window_days ) ) ) * 100.0;
		return round( max( 0.0, min( 100.0, $score ) ), 1 );
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
		return 'Scores nearer upcoming earnings events higher inside a configurable forward-day window.';
	}
}
