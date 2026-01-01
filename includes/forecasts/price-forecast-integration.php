<?php
/**
 * TradePress Price Forecast Integration
 *
 * Handles price forecast data retrieval and analysis for Focus Advisor Step 4
 *
 * @package TradePress/Forecasts
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Price_Forecast_Integration {

    /**
     * Get price forecast analysis for selected symbols
     *
     * @param array $symbols Selected symbols from previous steps
     * @return array Forecast analysis data
     */
    public function get_forecast_analysis( $symbols = array() ) {
        if ( empty( $symbols ) ) {
            if ( function_exists( 'tradepress_ai_log' ) ) {
                tradepress_ai_log( 'No symbols provided for forecast analysis' );
            }
            return array();
        }

        if ( function_exists( 'tradepress_ai_log' ) ) {
            tradepress_ai_log( 'Getting forecast analysis for symbols', array( 'symbols' => $symbols, 'symbol_count' => count( $symbols ) ) );
        }

        // Get forecast data for symbols
        $forecast_data = $this->get_symbol_forecasts( $symbols );
        
        // Analyze forecasts and calculate metrics
        $analysis = $this->analyze_forecast_data( $forecast_data, $symbols );
        
        if ( function_exists( 'tradepress_ai_log' ) ) {
            tradepress_ai_log( 'Forecast analysis completed', array( 
                'analysis_count' => count( $analysis ),
                'symbols_analyzed' => array_keys( $analysis )
            ) );
        }
        
        return $analysis;
    }

    /**
     * Get forecast data for specific symbols
     *
     * @param array $symbols Stock symbols
     * @return array Forecast data
     */
    private function get_symbol_forecasts( $symbols ) {
        $forecast_data = array();
        
        foreach ( $symbols as $symbol ) {
            $forecast_data[ $symbol ] = $this->get_demo_forecast_for_symbol( $symbol );
        }
        
        return $forecast_data;
    }

    /**
     * Generate demo forecast data for a symbol
     *
     * @param string $symbol Stock symbol
     * @return array Forecast data
     */
    private function get_demo_forecast_for_symbol( $symbol ) {
        // Generate current price between $10 and $1000
        $current_price = mt_rand( 100, 10000 ) / 10;
        
        // Generate confidence score between 50 and 95
        $base_confidence = mt_rand( 500, 950 ) / 10;
        
        // Create forecasts for different time periods with varying confidence
        $forecasts = array(
            '1m' => array(
                'price' => $current_price * ( 1 + mt_rand( -50, 100 ) / 1000 ),
                'confidence' => $base_confidence + mt_rand( -50, 100 ) / 10,
                'sources' => array( 'Goldman Sachs', 'Morgan Stanley', 'JP Morgan' ),
                'source_count' => 3
            ),
            '3m' => array(
                'price' => $current_price * ( 1 + mt_rand( -100, 200 ) / 1000 ),
                'confidence' => $base_confidence + mt_rand( -100, 50 ) / 10,
                'sources' => array( 'Goldman Sachs', 'Morgan Stanley', 'Barclays', 'Credit Suisse' ),
                'source_count' => 4
            ),
            '6m' => array(
                'price' => $current_price * ( 1 + mt_rand( -150, 300 ) / 1000 ),
                'confidence' => $base_confidence + mt_rand( -150, 0 ) / 10,
                'sources' => array( 'Goldman Sachs', 'Morgan Stanley', 'Barclays' ),
                'source_count' => 3
            ),
            '1y' => array(
                'price' => $current_price * ( 1 + mt_rand( -200, 400 ) / 1000 ),
                'confidence' => $base_confidence + mt_rand( -200, -50 ) / 10,
                'sources' => array( 'Goldman Sachs', 'Barclays' ),
                'source_count' => 2
            )
        );
        
        return array(
            'current_price' => $current_price,
            'forecasts' => $forecasts,
            'last_updated' => date( 'Y-m-d H:i:s', strtotime( '-' . mt_rand( 1, 72 ) . ' hours' ) )
        );
    }

    /**
     * Analyze forecast data and calculate metrics
     *
     * @param array $forecast_data Forecast data by symbol
     * @param array $symbols Selected symbols
     * @return array Analysis results
     */
    private function analyze_forecast_data( $forecast_data, $symbols ) {
        $analysis = array();
        
        foreach ( $symbols as $symbol ) {
            $symbol_data = isset( $forecast_data[ $symbol ] ) ? $forecast_data[ $symbol ] : array();
            
            if ( empty( $symbol_data ) ) {
                $analysis[ $symbol ] = array(
                    'current_price' => 0,
                    'forecasts' => array(),
                    'upside_potential' => 0,
                    'recommendation' => 'neutral',
                    'risk_level' => 'medium'
                );
                continue;
            }
            
            $current_price = $symbol_data['current_price'];
            $forecasts = $symbol_data['forecasts'];
            
            // Calculate upside potential and risk metrics
            $upside_potential = $this->calculate_upside_potential( $current_price, $forecasts );
            $recommendation = $this->generate_forecast_recommendation( $upside_potential, $forecasts );
            $risk_level = $this->assess_risk_level( $forecasts );
            
            // Calculate price distances
            $price_distances = array();
            foreach ( $forecasts as $period => $forecast ) {
                $distance = ( ( $forecast['price'] - $current_price ) / $current_price ) * 100;
                $price_distances[ $period ] = array(
                    'distance_percent' => round( $distance, 2 ),
                    'distance_absolute' => round( $forecast['price'] - $current_price, 2 ),
                    'target_price' => $forecast['price'],
                    'confidence' => $forecast['confidence'],
                    'sources' => $forecast['sources'],
                    'source_count' => $forecast['source_count']
                );
            }
            
            $analysis[ $symbol ] = array(
                'current_price' => $current_price,
                'forecasts' => $price_distances,
                'upside_potential' => $upside_potential,
                'recommendation' => $recommendation,
                'risk_level' => $risk_level,
                'last_updated' => $symbol_data['last_updated']
            );
        }
        
        return $analysis;
    }

    /**
     * Calculate upside potential based on forecasts
     *
     * @param float $current_price Current stock price
     * @param array $forecasts Forecast data
     * @return float Upside potential percentage
     */
    private function calculate_upside_potential( $current_price, $forecasts ) {
        // Use 1-year forecast for upside potential, fallback to 6-month
        $target_forecast = isset( $forecasts['1y'] ) ? $forecasts['1y'] : 
                          ( isset( $forecasts['6m'] ) ? $forecasts['6m'] : null );
        
        if ( ! $target_forecast ) {
            return 0;
        }
        
        return ( ( $target_forecast['price'] - $current_price ) / $current_price ) * 100;
    }

    /**
     * Generate investment recommendation based on forecasts
     *
     * @param float $upside_potential Upside potential percentage
     * @param array $forecasts Forecast data
     * @return string Recommendation (strong_buy, buy, hold, sell, strong_sell)
     */
    private function generate_forecast_recommendation( $upside_potential, $forecasts ) {
        // Get average confidence across all forecasts
        $total_confidence = 0;
        $forecast_count = 0;
        
        foreach ( $forecasts as $forecast ) {
            $total_confidence += $forecast['confidence'];
            $forecast_count++;
        }
        
        $avg_confidence = $forecast_count > 0 ? $total_confidence / $forecast_count : 0;
        
        // Generate recommendation based on upside potential and confidence
        if ( $upside_potential >= 20 && $avg_confidence >= 75 ) {
            return 'strong_buy';
        } elseif ( $upside_potential >= 10 && $avg_confidence >= 65 ) {
            return 'buy';
        } elseif ( $upside_potential >= -5 && $upside_potential <= 15 ) {
            return 'hold';
        } elseif ( $upside_potential <= -10 && $avg_confidence >= 60 ) {
            return 'sell';
        } elseif ( $upside_potential <= -20 && $avg_confidence >= 70 ) {
            return 'strong_sell';
        }
        
        return 'hold';
    }

    /**
     * Assess risk level based on forecast volatility
     *
     * @param array $forecasts Forecast data
     * @return string Risk level (low, medium, high)
     */
    private function assess_risk_level( $forecasts ) {
        // Calculate volatility based on confidence spread
        $confidences = array();
        foreach ( $forecasts as $forecast ) {
            $confidences[] = $forecast['confidence'];
        }
        
        if ( empty( $confidences ) ) {
            return 'medium';
        }
        
        $min_confidence = min( $confidences );
        $max_confidence = max( $confidences );
        $confidence_spread = $max_confidence - $min_confidence;
        
        if ( $confidence_spread <= 10 && $min_confidence >= 70 ) {
            return 'low';
        } elseif ( $confidence_spread >= 25 || $min_confidence <= 50 ) {
            return 'high';
        }
        
        return 'medium';
    }

    /**
     * Get recommendation badge class
     *
     * @param string $recommendation Recommendation type
     * @return string CSS class
     */
    public function get_recommendation_class( $recommendation ) {
        switch ( $recommendation ) {
            case 'strong_buy':
                return 'recommendation-strong-buy';
            case 'buy':
                return 'recommendation-buy';
            case 'hold':
                return 'recommendation-hold';
            case 'sell':
                return 'recommendation-sell';
            case 'strong_sell':
                return 'recommendation-strong-sell';
            default:
                return 'recommendation-neutral';
        }
    }

    /**
     * Get risk level badge class
     *
     * @param string $risk_level Risk level
     * @return string CSS class
     */
    public function get_risk_class( $risk_level ) {
        switch ( $risk_level ) {
            case 'low':
                return 'risk-low';
            case 'high':
                return 'risk-high';
            default:
                return 'risk-medium';
        }
    }

    /**
     * Get distance indicator class based on percentage
     *
     * @param float $distance_percent Distance percentage
     * @return string CSS class
     */
    public function get_distance_class( $distance_percent ) {
        if ( $distance_percent >= 15 ) {
            return 'distance-very-positive';
        } elseif ( $distance_percent >= 5 ) {
            return 'distance-positive';
        } elseif ( $distance_percent >= -5 ) {
            return 'distance-neutral';
        } elseif ( $distance_percent >= -15 ) {
            return 'distance-negative';
        } else {
            return 'distance-very-negative';
        }
    }
}