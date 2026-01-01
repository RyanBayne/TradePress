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
     */
    public function get_news_analysis( $symbols = array() ) {
        if ( empty( $symbols ) ) {
            if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log( 'No symbols provided for news analysis' );
        }
            return array();
        }

        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log( 'Getting news analysis for symbols', array( 'symbols' => $symbols, 'symbol_count' => count( $symbols ) ) );
        }

        // Get news data for symbols
        $news_data = $this->get_symbol_news( $symbols );
        
        // Analyze sentiment and impact
        $analysis = $this->analyze_news_impact( $news_data, $symbols );
        
        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log( 'News analysis completed', array( 
                'analysis_count' => count( $analysis ),
                'symbols_analyzed' => array_keys( $analysis )
            ) );
        }
        
        return $analysis;
    }

    /**
     * Get recent news for specific symbols
     *
     * @param array $symbols Stock symbols
     * @return array News items
     */
    private function get_symbol_news( $symbols ) {
        // For now, use demo data similar to the research news feed
        // In production, this would query the news database
        
        $news_items = array();
        
        foreach ( $symbols as $symbol ) {
            $news_items[ $symbol ] = $this->get_demo_news_for_symbol( $symbol );
        }
        
        return $news_items;
    }

    /**
     * Generate demo news for a symbol
     *
     * @param string $symbol Stock symbol
     * @return array News items
     */
    private function get_demo_news_for_symbol( $symbol ) {
        $demo_news = array(
            'AAPL' => array(
                array(
                    'headline' => 'Apple Reports Strong iPhone Sales Despite Market Headwinds',
                    'source' => 'Reuters',
                    'published_at' => date( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
                    'sentiment' => 0.7,
                    'impact_rating' => 4,
                    'url' => 'https://reuters.com/example'
                ),
                array(
                    'headline' => 'Apple Faces Supply Chain Challenges in Q4',
                    'source' => 'Bloomberg',
                    'published_at' => date( 'Y-m-d H:i:s', strtotime( '-5 hours' ) ),
                    'sentiment' => -0.3,
                    'impact_rating' => 3,
                    'url' => 'https://bloomberg.com/example'
                )
            ),
            'MSFT' => array(
                array(
                    'headline' => 'Microsoft Cloud Revenue Exceeds Expectations',
                    'source' => 'CNBC',
                    'published_at' => date( 'Y-m-d H:i:s', strtotime( '-1 hour' ) ),
                    'sentiment' => 0.8,
                    'impact_rating' => 5,
                    'url' => 'https://cnbc.com/example'
                )
            ),
            'TSLA' => array(
                array(
                    'headline' => 'Tesla Deliveries Fall Short of Analyst Estimates',
                    'source' => 'MarketWatch',
                    'published_at' => date( 'Y-m-d H:i:s', strtotime( '-3 hours' ) ),
                    'sentiment' => -0.6,
                    'impact_rating' => 4,
                    'url' => 'https://marketwatch.com/example'
                )
            ),
            'NVDA' => array(
                array(
                    'headline' => 'NVIDIA AI Chip Demand Continues to Surge',
                    'source' => 'TechCrunch',
                    'published_at' => date( 'Y-m-d H:i:s', strtotime( '-4 hours' ) ),
                    'sentiment' => 0.9,
                    'impact_rating' => 5,
                    'url' => 'https://techcrunch.com/example'
                )
            )
        );

        // Return news for the symbol or generate generic news
        if ( isset( $demo_news[ $symbol ] ) ) {
            return $demo_news[ $symbol ];
        }

        // Generate generic positive/negative news for other symbols
        $sentiments = array( 0.6, -0.4, 0.3, -0.2, 0.8 );
        $sources = array( 'Reuters', 'Bloomberg', 'CNBC', 'MarketWatch', 'Yahoo Finance' );
        
        return array(
            array(
                'headline' => $symbol . ' Shows Strong Performance in Latest Quarter',
                'source' => $sources[ array_rand( $sources ) ],
                'published_at' => date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 8 ) . ' hours' ) ),
                'sentiment' => $sentiments[ array_rand( $sentiments ) ],
                'impact_rating' => rand( 2, 5 ),
                'url' => 'https://example.com/news'
            )
        );
    }

    /**
     * Analyze news impact for symbols
     *
     * @param array $news_data News data by symbol
     * @param array $symbols Selected symbols
     * @return array Analysis results
     */
    private function analyze_news_impact( $news_data, $symbols ) {
        $analysis = array();
        
        foreach ( $symbols as $symbol ) {
            $symbol_news = isset( $news_data[ $symbol ] ) ? $news_data[ $symbol ] : array();
            
            if ( empty( $symbol_news ) ) {
                $analysis[ $symbol ] = array(
                    'overall_sentiment' => 0,
                    'news_count' => 0,
                    'impact_score' => 0,
                    'recommendation' => 'neutral',
                    'key_headlines' => array(),
                    'risk_factors' => array()
                );
                continue;
            }
            
            // Calculate overall sentiment
            $total_sentiment = 0;
            $total_impact = 0;
            $news_count = count( $symbol_news );
            $key_headlines = array();
            $risk_factors = array();
            
            foreach ( $symbol_news as $news_item ) {
                $total_sentiment += $news_item['sentiment'];
                $total_impact += $news_item['impact_rating'];
                
                // Collect key headlines (high impact or extreme sentiment)
                if ( $news_item['impact_rating'] >= 4 || abs( $news_item['sentiment'] ) >= 0.6 ) {
                    $key_headlines[] = array(
                        'headline' => $news_item['headline'],
                        'sentiment' => $news_item['sentiment'],
                        'impact' => $news_item['impact_rating'],
                        'source' => $news_item['source'],
                        'url' => $news_item['url']
                    );
                }
                
                // Identify risk factors (negative sentiment with high impact)
                if ( $news_item['sentiment'] < -0.3 && $news_item['impact_rating'] >= 3 ) {
                    $risk_factors[] = $news_item['headline'];
                }
            }
            
            $avg_sentiment = $total_sentiment / $news_count;
            $avg_impact = $total_impact / $news_count;
            
            // Generate recommendation
            $recommendation = $this->generate_recommendation( $avg_sentiment, $avg_impact, $risk_factors );
            
            $analysis[ $symbol ] = array(
                'overall_sentiment' => round( $avg_sentiment, 2 ),
                'news_count' => $news_count,
                'impact_score' => round( $avg_impact, 1 ),
                'recommendation' => $recommendation,
                'key_headlines' => $key_headlines,
                'risk_factors' => $risk_factors
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
     */
    public function get_additional_opportunities( $excluded_symbols = array() ) {
        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log( 'Getting additional news-based opportunities', array( 'excluded_symbols' => $excluded_symbols ) );
        }
        
        // Get trending symbols with positive news
        $trending_symbols = array( 'AMD', 'GOOGL', 'META', 'NFLX', 'CRM' );
        
        $opportunities = array();
        
        foreach ( $trending_symbols as $symbol ) {
            if ( in_array( $symbol, $excluded_symbols ) ) {
                continue;
            }
            
            $news_data = $this->get_demo_news_for_symbol( $symbol );
            $analysis = $this->analyze_news_impact( array( $symbol => $news_data ), array( $symbol ) );
            
            if ( isset( $analysis[ $symbol ] ) && 
                 $analysis[ $symbol ]['recommendation'] === 'bullish' && 
                 $analysis[ $symbol ]['overall_sentiment'] >= 0.4 ) {
                
                $opportunities[] = array(
                    'symbol' => $symbol,
                    'reason' => 'Strong positive news sentiment',
                    'sentiment' => $analysis[ $symbol ]['overall_sentiment'],
                    'impact' => $analysis[ $symbol ]['impact_score'],
                    'key_headline' => ! empty( $analysis[ $symbol ]['key_headlines'] ) ? 
                                    $analysis[ $symbol ]['key_headlines'][0]['headline'] : 
                                    'Positive market sentiment'
                );
            }
        }
        
        $final_opportunities = array_slice( $opportunities, 0, 3 );
        
        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log( 'Additional opportunities found', array( 
                'opportunity_count' => count( $final_opportunities ),
                'opportunities' => array_column( $final_opportunities, 'symbol' )
            ) );
        }
        
        return $final_opportunities;
    }

    /**
     * Get sentiment indicator class for display
     *
     * @param float $sentiment Sentiment score
     * @return string CSS class
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