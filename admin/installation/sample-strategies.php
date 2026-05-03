<?php
/**
 * TradePress - Sample Strategies Installation
 *
 * Creates sample strategies for demonstration
 *
 * @package TradePress/Admin/Installation
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TradePress_Sample_Strategies {

	/**
	 * Create sample strategies
	 *
	 * @version 1.1.0
	 */
	public static function create_sample_strategies() {
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

		// Check if sample strategies already exist
		$existing = TradePress_Scoring_Strategies_DB::get_strategies( array( 'limit' => 1 ) );
		if ( ! empty( $existing ) ) {
			return; // Sample strategies already exist
		}

		self::create_momentum_confluence_strategy();
		self::create_mean_reversion_strategy();
		self::create_trend_strength_strategy();
	}

	/**
	 * Create Momentum Confluence Strategy
	 *
	 * Oversold RSI + bullish MACD crossover + volume surge + price above EMA.
	 * All four directives have working PHP implementations.
	 *
	 * @version 1.1.0
	 */
	private static function create_momentum_confluence_strategy() {
		$strategy_data = array(
			'name'         => 'Momentum Confluence',
			'description'  => 'Identifies oversold momentum entries confirmed by MACD crossover, volume surge, and price above EMA. Best for swing trading growth stocks.',
			'category'     => 'momentum_confluence',
			'status'       => 'active',
			'risk_level'   => 'medium',
			'time_horizon' => 'short',
			'total_weight' => 100.00,
			'creator_id'   => 1,
		);

		$strategy_id = TradePress_Scoring_Strategies_DB::create_strategy( $strategy_data );

		if ( ! is_wp_error( $strategy_id ) ) {
			$directives = array(
				array(
					'directive_id'   => 'rsi',
					'directive_name' => 'Relative Strength Index',
					'weight'         => 30.00,
					'sort_order'     => 1,
				),
				array(
					'directive_id'   => 'macd',
					'directive_name' => 'MACD',
					'weight'         => 30.00,
					'sort_order'     => 2,
				),
				array(
					'directive_id'   => 'volume',
					'directive_name' => 'Volume Analysis',
					'weight'         => 25.00,
					'sort_order'     => 3,
				),
				array(
					'directive_id'   => 'ema',
					'directive_name' => 'Exponential Moving Average',
					'weight'         => 15.00,
					'sort_order'     => 4,
				),
			);

			foreach ( $directives as $directive ) {
				TradePress_Scoring_Strategies_DB::add_strategy_directive( $strategy_id, $directive );
			}
		}
	}

	/**
	 * Create Mean Reversion Strategy
	 *
	 * RSI + Bollinger Bands + CCI + MFI — four independent oversold signals
	 * that must converge before scoring high. Best for ranging markets.
	 *
	 * @version 1.1.0
	 */
	private static function create_mean_reversion_strategy() {
		$strategy_data = array(
			'name'         => 'Mean Reversion',
			'description'  => 'Identifies oversold conditions from multiple angles using RSI, Bollinger Bands, CCI, and MFI volume-weighted confirmation. Best for ranging markets.',
			'category'     => 'mean_reversion',
			'status'       => 'active',
			'risk_level'   => 'low',
			'time_horizon' => 'short',
			'total_weight' => 100.00,
			'creator_id'   => 1,
		);

		$strategy_id = TradePress_Scoring_Strategies_DB::create_strategy( $strategy_data );

		if ( ! is_wp_error( $strategy_id ) ) {
			$directives = array(
				array(
					'directive_id'   => 'rsi',
					'directive_name' => 'Relative Strength Index',
					'weight'         => 35.00,
					'sort_order'     => 1,
				),
				array(
					'directive_id'   => 'bollinger_bands',
					'directive_name' => 'Bollinger Bands',
					'weight'         => 30.00,
					'sort_order'     => 2,
				),
				array(
					'directive_id'   => 'cci',
					'directive_name' => 'Commodity Channel Index',
					'weight'         => 20.00,
					'sort_order'     => 3,
				),
				array(
					'directive_id'   => 'mfi',
					'directive_name' => 'Money Flow Index',
					'weight'         => 15.00,
					'sort_order'     => 4,
				),
			);

			foreach ( $directives as $directive ) {
				TradePress_Scoring_Strategies_DB::add_strategy_directive( $strategy_id, $directive );
			}
		}
	}

	/**
	 * Create Trend Strength Strategy
	 *
	 * ADX confirms a strong trend exists, moving averages confirm direction,
	 * MACD confirms momentum, volume confirms participation.
	 *
	 * @version 1.1.0
	 */
	private static function create_trend_strength_strategy() {
		$strategy_data = array(
			'name'         => 'Trend Strength',
			'description'  => 'Confirms a strong trend exists before entering. ADX measures trend strength, moving averages confirm direction, MACD confirms momentum, volume confirms participation.',
			'category'     => 'trend_strength',
			'status'       => 'active',
			'risk_level'   => 'medium',
			'time_horizon' => 'long',
			'total_weight' => 100.00,
			'creator_id'   => 1,
		);

		$strategy_id = TradePress_Scoring_Strategies_DB::create_strategy( $strategy_data );

		if ( ! is_wp_error( $strategy_id ) ) {
			$directives = array(
				array(
					'directive_id'   => 'adx',
					'directive_name' => 'Average Directional Index',
					'weight'         => 30.00,
					'sort_order'     => 1,
				),
				array(
					'directive_id'   => 'moving_averages',
					'directive_name' => 'Moving Averages',
					'weight'         => 30.00,
					'sort_order'     => 2,
				),
				array(
					'directive_id'   => 'macd',
					'directive_name' => 'MACD',
					'weight'         => 25.00,
					'sort_order'     => 3,
				),
				array(
					'directive_id'   => 'volume',
					'directive_name' => 'Volume Analysis',
					'weight'         => 15.00,
					'sort_order'     => 4,
				),
			);

			foreach ( $directives as $directive ) {
				TradePress_Scoring_Strategies_DB::add_strategy_directive( $strategy_id, $directive );
			}
		}
	}
}
