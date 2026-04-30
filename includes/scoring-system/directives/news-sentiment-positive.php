<?php
/**
 * TradePress Positive News Sentiment Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-tradepress-scoring-directive-base.php';

class TradePress_News_Sentiment_Positive_Directive extends TradePress_Scoring_Directive_Base {

	/**
	 * Calculate score.
	 *
	 * @param mixed  $symbol
	 * @param string $trading_mode
	 * @param array  $config
	 *
	 * @return mixed
	 *
	 * @version 1.0.0
	 */
	public function calculate_score( $symbol, $trading_mode = 'long', $config = array() ) {

		if ( is_array( $symbol ) ) {
			$symbol = $symbol['symbol'] ?? '';
		}

		if ( ! is_string( $symbol ) || $symbol === '' ) {
			return 0;
		}

		$sentiment_threshold    = $config['sentiment_threshold'] ?? 0.6;
		$lookback_days          = $config['lookback_days'] ?? 7;
		$strong_sentiment_bonus = $config['strong_sentiment_bonus'] ?? 25;
		$volume_multiplier      = $config['volume_multiplier'] ?? 1.5;

		// Get news sentiment data (placeholder - would integrate with news API)
		$sentiment_data = $this->get_news_sentiment( $symbol, $lookback_days );

		if ( ! $sentiment_data ) {
			return 0;
		}

		$avg_sentiment = $sentiment_data['average_sentiment'];
		$news_count    = $sentiment_data['news_count'];

		// Base score calculation
		if ( $avg_sentiment < $sentiment_threshold ) {
			return 0; // Below threshold
		}

		// Calculate base score (0-100 based on sentiment strength)
		$base_score = ( $avg_sentiment - $sentiment_threshold ) / ( 1.0 - $sentiment_threshold ) * 100;

		// Strong sentiment bonus
		if ( $avg_sentiment >= 0.8 ) {
			$base_score += $strong_sentiment_bonus;
		}

		// News volume multiplier
		if ( $news_count >= 5 ) {
			$base_score *= $volume_multiplier;
		}

		return round( $base_score, 1 );
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
		$strong_sentiment_bonus = $config['strong_sentiment_bonus'] ?? 25;
		$volume_multiplier      = $config['volume_multiplier'] ?? 1.5;

		return round( ( 100 + $strong_sentiment_bonus ) * $volume_multiplier, 1 );
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
		$sentiment_threshold    = $config['sentiment_threshold'] ?? 0.6;
		$lookback_days          = $config['lookback_days'] ?? 7;
		$strong_sentiment_bonus = $config['strong_sentiment_bonus'] ?? 25;
		$volume_multiplier      = $config['volume_multiplier'] ?? 1.5;

		return "News Sentiment Analysis Directive (D14)\n\n" .
				"This directive analyzes recent news sentiment to identify positive market outlook.\n\n" .
				"Configuration:\n" .
				"- Sentiment Threshold: {$sentiment_threshold} (minimum for positive signal)\n" .
				"- Lookback Period: {$lookback_days} days\n" .
				"- Strong Sentiment Bonus: +{$strong_sentiment_bonus} points for ≥0.8 sentiment\n" .
				"- News Volume Multiplier: {$volume_multiplier}x for ≥5 news articles\n\n" .
				"Scoring Logic:\n" .
				"1. Analyze news sentiment over lookback period\n" .
				"2. Calculate average sentiment score (0.0 to 1.0)\n" .
				"3. Base score: (sentiment - threshold) / (1.0 - threshold) × 100\n" .
				"4. Add strong sentiment bonus if sentiment ≥ 0.8\n" .
				"5. Apply volume multiplier if news count ≥ 5\n\n" .
				"Example: 0.75 sentiment with 6 articles:\n" .
				"Base: (0.75 - 0.6) / 0.4 × 100 = 37.5\n" .
				"Volume multiplier: 37.5 × 1.5 = 56.3 points\n\n" .
				'Max Score: ' . $this->get_max_score( $config ) . ' points';
	}

	/**
	 * Get news sentiment.
	 *
	 * @param mixed $symbol
	 * @param mixed $lookback_days
	 *
	 * @return mixed
	 *
	 * @version 1.0.0
	 */
	private function get_news_sentiment( $symbol, $lookback_days ) {
		// Demo/dev path: return mock sentiment data only when both demo mode and developer access are active.
		$can_show_demo = function_exists( 'is_demo_mode' ) && is_demo_mode()
			&& function_exists( 'tradepress_can_access_development_views' ) && tradepress_can_access_development_views();

		if ( $can_show_demo ) {
			$mock_sentiments = array(
				'AAPL'  => array(
					'average_sentiment' => 0.75,
					'news_count'        => 8,
				),
				'TSLA'  => array(
					'average_sentiment' => 0.65,
					'news_count'        => 12,
				),
				'MSFT'  => array(
					'average_sentiment' => 0.70,
					'news_count'        => 6,
				),
				'GOOGL' => array(
					'average_sentiment' => 0.68,
					'news_count'        => 7,
				),
				'AMZN'  => array(
					'average_sentiment' => 0.72,
					'news_count'        => 9,
				),
			);

			return $mock_sentiments[ $symbol ] ?? array(
				'average_sentiment' => 0.55,
				'news_count'        => 3,
			);
		}

		// Live path: query stored news sentiment from the database (not yet implemented).
		// Return null so calculate_score() scores 0 rather than using fake data.
		return null;
	}
}
