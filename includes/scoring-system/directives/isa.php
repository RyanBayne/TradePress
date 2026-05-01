<?php
/**
 * ISA Reset Directive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TradePress_Scoring_Directive_ISA extends TradePress_Scoring_Directive_Base {

	/**
	 *   C On St Ru Ct.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'isa';
		$this->name           = 'ISA Season';
		$this->description    = 'Increases score during the broader ISA investment season (January-April)';
		$this->weight         = 10;
		$this->max_score      = 50;
		$this->bullish_values = 'Active during ISA season';
		$this->bearish_values = 'Not applicable';
		$this->priority       = 19;
	}

	/**
	 * Calculate score.
	 *
	 * @param mixed $symbol_data
	 * @param array $config
	 *
	 * @return mixed
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol_data, $config = array() ) {
		$score_impact = $config['score_impact'] ?? 10;

		$current_month = (int) current_time( 'n' );

		// ISA season runs January through April (months 1-4)
		if ( $current_month >= 1 && $current_month <= 4 ) {
			return array(
				'score'          => $score_impact,
				'in_season'      => true,
				'current_month'  => $current_month,
			);
		}

		return array(
			'score'         => 0,
			'in_season'     => false,
			'current_month' => $current_month,
		);
	}

	/**
	 * Get max score.
	 *
	 * @param array $config
	 *
	 * @return mixed
	 *
	 * @version 1.0.0
	 */
	public function get_max_score( $config = array() ) {
		return $config['score_impact'] ?? 10;
	}

	/**
	 * Get explanation.
	 *
	 * @param array $config
	 *
	 * @return mixed
	 *
	 * @version 1.0.0
	 */
	public function get_explanation( $config = array() ) {
		$score_impact = $config['score_impact'] ?? 10;

		return "ISA Season Directive:\n\n" .
				"Adds {$score_impact} points during the ISA investment season (January to April).\n\n" .
				'This directive captures the broader seasonal buying pressure as UK investors deploy fresh ISA allowances throughout the tax year opening period.';
	}
}
