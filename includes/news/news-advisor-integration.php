<?php
/**
 * TradePress News Advisor Integration
 *
 * Handles news analysis integration for Focus Advisor Step 3
 *
 * @package TradePress/News
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TradePress_News_Advisor_Integration {

	/**
	 * Get news analysis for selected symbols
	 *
	 * @param array $symbols Selected symbols from Step 2
	 * @return array News analysis data
	 * @version 1.0.0
	 */
	public function get_news_analysis( $symbols = array() ) {
		if ( empty( $symbols ) ) {
			if ( function_exists( 'tradepress_trace_log' ) ) {
				tradepress_trace_log( 'No symbols provided for news analysis' );
			}
			return array();
		}

		if ( function_exists( 'tradepress_trace_log' ) ) {
			tradepress_trace_log(
				'Getting news analysis for symbols',
				array(
					'symbols'      => $symbols,
					'symbol_count' => count( $symbols ),
				)
			);
		}

		// Get news data for symbols
		$news_data = $this->get_symbol_news( $symbols );

		// Analyze sentiment and impact
		$analysis = $this->analyze_news_impact( $news_data, $symbols );

		if ( function_exists( 'tradepress_trace_log' ) ) {
			tradepress_trace_log(
				'News analysis completed',
				array(
					'analysis_count'   => count( $analysis ),
					'symbols_analyzed' => array_keys( $analysis ),
				)
			);
		}

		return $analysis;
	}

	/**
	 * Get recent news for specific symbols
	 *
	 * @param array $symbols Stock symbols
	 * @return array News items
	 * @version 1.0.0
	 */
	private function get_symbol_news( $symbols ) {
		return array();
	}

	/**
	 * Analyze news impact for symbols
	 *
	 * @param array $news_data News data by symbol
	 * @param array $symbols Selected symbols
	 * @return array Analysis results
	 * @version 1.0.0
	 */
	private function analyze_news_impact( $news_data, $symbols ) {
		$analysis = array();

		foreach ( $symbols as $symbol ) {
			$symbol_news = isset( $news_data[ $symbol ] ) ? $news_data[ $symbol ] : array();

			if ( empty( $symbol_news ) ) {
				$analysis[ $symbol ] = array(
					'overall_sentiment' => 0,
					'news_count'        => 0,
					'impact_score'      => 0,
					'recommendation'    => 'neutral',
					'key_headlines'     => array(),
					'risk_factors'      => array(),
				);
				continue;
			}

			// Calculate overall sentiment
			$total_sentiment = 0;
			$total_impact    = 0;
			$news_count      = count( $symbol_news );
			$key_headlines   = array();
			$risk_factors    = array();

			foreach ( $symbol_news as $news_item ) {
				$total_sentiment += $news_item['sentiment'];
				$total_impact    += $news_item['impact_rating'];

				// Collect key headlines (high impact or extreme sentiment)
				if ( $news_item['impact_rating'] >= 4 || abs( $news_item['sentiment'] ) >= 0.6 ) {
					$key_headlines[] = array(
						'headline'  => $news_item['headline'],
						'sentiment' => $news_item['sentiment'],
						'impact'    => $news_item['impact_rating'],
						'source'    => $news_item['source'],
						'url'       => $news_item['url'],
					);
				}

				// Identify risk factors (negative sentiment with high impact)
				if ( $news_item['sentiment'] < -0.3 && $news_item['impact_rating'] >= 3 ) {
					$risk_factors[] = $news_item['headline'];
				}
			}

			$avg_sentiment = $total_sentiment / $news_count;
			$avg_impact    = $total_impact / $news_count;

			// Generate recommendation
			$recommendation = $this->generate_recommendation( $avg_sentiment, $avg_impact, $risk_factors );

			$analysis[ $symbol ] = array(
				'overall_sentiment' => round( $avg_sentiment, 2 ),
				'news_count'        => $news_count,
				'impact_score'      => round( $avg_impact, 1 ),
				'recommendation'    => $recommendation,
				'key_headlines'     => $key_headlines,
				'risk_factors'      => $risk_factors,
			);
		}

		return $analysis;
	}

	/**
	 * Generate investment recommendation based on news analysis
	 *
	 * @param float $sentiment Average sentiment score
	 * @param float $impact Average impact score
	 * @param array $risk_factors Risk factors identified
	 * @return string Recommendation (bullish, bearish, neutral, caution)
	 * @version 1.0.0
	 */
	private function generate_recommendation( $sentiment, $impact, $risk_factors ) {
		// High positive sentiment with high impact
		if ( $sentiment >= 0.5 && $impact >= 4 && empty( $risk_factors ) ) {
			return 'bullish';
		}

		// High negative sentiment with high impact
		if ( $sentiment <= -0.4 && $impact >= 3 ) {
			return 'bearish';
		}

		// Positive sentiment but with risk factors
		if ( $sentiment > 0.2 && ! empty( $risk_factors ) ) {
			return 'caution';
		}

		// Moderate positive sentiment
		if ( $sentiment >= 0.3 && $impact >= 3 ) {
			return 'bullish';
		}

		// Moderate negative sentiment
		if ( $sentiment <= -0.2 && $impact >= 3 ) {
			return 'bearish';
		}

		return 'neutral';
	}

	/**
	 * Get additional news-based opportunities
	 *
	 * @param array $excluded_symbols Symbols to exclude (already selected)
	 * @return array Additional opportunities
	 * @version 1.0.0
	 */
	public function get_additional_opportunities( $excluded_symbols = array() ) {
		if ( function_exists( 'tradepress_trace_log' ) ) {
			tradepress_trace_log( 'Getting additional news-based opportunities', array( 'excluded_symbols' => $excluded_symbols ) );
		}

		return array();
	}

	/**
	 * Get sentiment indicator class for display
	 *
	 * @param float $sentiment Sentiment score
	 * @return string CSS class
	 * @version 1.0.0
	 */
	public function get_sentiment_class( $sentiment ) {
		if ( $sentiment >= 0.4 ) {
			return 'sentiment-very-positive';
		} elseif ( $sentiment >= 0.1 ) {
			return 'sentiment-positive';
		} elseif ( $sentiment <= -0.4 ) {
			return 'sentiment-very-negative';
		} elseif ( $sentiment <= -0.1 ) {
			return 'sentiment-negative';
		}

		return 'sentiment-neutral';
	}

	/**
	 * Get recommendation badge class
	 *
	 * @param string $recommendation Recommendation type
	 * @return string CSS class
	 * @version 1.0.0
	 */
	public function get_recommendation_class( $recommendation ) {
		switch ( $recommendation ) {
			case 'bullish':
				return 'recommendation-bullish';
			case 'bearish':
				return 'recommendation-bearish';
			case 'caution':
				return 'recommendation-caution';
			default:
				return 'recommendation-neutral';
		}
	}
}
