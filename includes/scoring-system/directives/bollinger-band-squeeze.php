<?php
/**
 * TradePress Bollinger Band Squeeze Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_BOLLINGER_BAND_SQUEEZE extends TradePress_Scoring_Directive_Base {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'bollinger_band_squeeze';
		$this->name           = 'Bollinger Band Squeeze';
		$this->description    = 'Detects volatility compression before potential breakout expansion.';
		$this->weight         = 8;
		$this->max_score      = 100;
		$this->bullish_values = 'Narrow band width with expansion setup';
		$this->bearish_values = 'Wide/expanding bands after squeeze release';
		$this->priority       = 12;
	}

	/**
	 * Calculate score.
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$target_width_percent = isset( $config['target_width_percent'] ) ? (float) $config['target_width_percent'] : 5.0;
		$max_width_percent    = isset( $config['max_width_percent'] ) ? (float) $config['max_width_percent'] : 20.0;

		$bollinger = $symbol_data['technical']['bollinger'] ?? array();
		$upper     = isset( $bollinger['upper'] ) ? (float) $bollinger['upper'] : 0.0;
		$lower     = isset( $bollinger['lower'] ) ? (float) $bollinger['lower'] : 0.0;
		$middle    = isset( $bollinger['middle'] ) ? (float) $bollinger['middle'] : 0.0;

		if ( $upper <= 0.0 || $lower <= 0.0 || $middle <= 0.0 || $upper <= $lower ) {
			return 0.0;
		}

		$width_percent = ( ( $upper - $lower ) / $middle ) * 100.0;

		if ( $width_percent <= $target_width_percent ) {
			return 100.0;
		}

		if ( $width_percent >= $max_width_percent ) {
			return 0.0;
		}

		$normalized = 1.0 - ( ( $width_percent - $target_width_percent ) / max( 0.01, ( $max_width_percent - $target_width_percent ) ) );
		return round( max( 0.0, min( 100.0, $normalized * 100.0 ) ), 1 );
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
		return "Bollinger Band Squeeze scores tighter band width higher.\n" .
			"Lower width percentage implies volatility compression and potential breakout setup.";
	}
}
