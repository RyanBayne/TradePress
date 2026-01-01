<?php
/**
 * TradePress Economic Impact Integration
 *
 * Handles economic impact analysis for Focus Advisor Step 5
 *
 * @package TradePress/Economic
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Economic_Impact_Integration {

    /**
     * Get economic impact analysis for selected symbols
     *
     * @param array $symbols Selected symbols from previous steps
     * @return array Economic impact analysis data
     */
    public function get_economic_analysis( $symbols = array() ) {
        if ( empty( $symbols ) ) {
            if ( function_exists( 'tradepress_trace_log' ) ) {
                tradepress_trace_log( 'No symbols provided for economic analysis' );
            }
            return array();
        }

        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log( 'Getting economic analysis for symbols', array( 'symbols' => $symbols, 'symbol_count' => count( $symbols ) ) );
        }

        // Get economic factors and their impact on symbols
        $economic_factors = $this->get_current_economic_factors();
        $symbol_analysis = $this->analyze_symbol_economic_impact( $symbols, $economic_factors );
        
        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log( 'Economic analysis completed', array( 
                'analysis_count' => count( $symbol_analysis ),
                'factors_analyzed' => count( $economic_factors )
            ) );
        }
        
        return array(
            'economic_factors' => $economic_factors,
            'symbol_analysis' => $symbol_analysis,
            'overall_outlook' => $this->generate_overall_outlook( $economic_factors )
        );
    }

    /**
     * Get current economic factors affecting markets
     *
     * @return array Economic factors data
     */
    private function get_current_economic_factors() {
        return array(
            'interest_rates' => array(
                'title' => 'Federal Interest Rates',
                'current_value' => '5.25%',
                'trend' => 'stable',
                'impact_level' => 'high',
                'description' => 'Fed maintains rates at current levels, signaling potential pause in hiking cycle',
                'market_impact' => 'neutral_to_positive',
                'sectors_affected' => array( 'Banking', 'Real Estate', 'Technology' ),
                'last_updated' => date( 'Y-m-d', strtotime( '-2 days' ) )
            ),
            'inflation' => array(
                'title' => 'Consumer Price Index (CPI)',
                'current_value' => '3.2%',
                'trend' => 'declining',
                'impact_level' => 'high',
                'description' => 'Inflation continues gradual decline toward Fed target of 2%',
                'market_impact' => 'positive',
                'sectors_affected' => array( 'Consumer Goods', 'Retail', 'Energy' ),
                'last_updated' => date( 'Y-m-d', strtotime( '-5 days' ) )
            ),
            'trade_policy' => array(
                'title' => 'US-China Trade Relations',
                'current_value' => 'Cautious Optimism',
                'trend' => 'improving',
                'impact_level' => 'medium',
                'description' => 'Recent diplomatic talks show signs of easing tensions',
                'market_impact' => 'positive',
                'sectors_affected' => array( 'Technology', 'Manufacturing', 'Agriculture' ),
                'last_updated' => date( 'Y-m-d', strtotime( '-1 week' ) )
            ),
            'employment' => array(
                'title' => 'Unemployment Rate',
                'current_value' => '3.8%',
                'trend' => 'stable',
                'impact_level' => 'medium',
                'description' => 'Labor market remains tight with steady employment levels',
                'market_impact' => 'positive',
                'sectors_affected' => array( 'Consumer Discretionary', 'Services', 'Retail' ),
                'last_updated' => date( 'Y-m-d', strtotime( '-1 week' ) )
            ),
            'gdp_growth' => array(
                'title' => 'GDP Growth Rate',
                'current_value' => '2.1%',
                'trend' => 'stable',
                'impact_level' => 'high',
                'description' => 'Economy shows resilient growth despite global headwinds',
                'market_impact' => 'positive',
                'sectors_affected' => array( 'All Sectors' ),
                'last_updated' => date( 'Y-m-d', strtotime( '-2 weeks' ) )
            ),
            'geopolitical' => array(
                'title' => 'Geopolitical Tensions',
                'current_value' => 'Moderate Risk',
                'trend' => 'stable',
                'impact_level' => 'medium',
                'description' => 'Regional conflicts continue but limited global economic impact',
                'market_impact' => 'neutral_to_negative',
                'sectors_affected' => array( 'Energy', 'Defense', 'Commodities' ),
                'last_updated' => date( 'Y-m-d', strtotime( '-3 days' ) )
            )
        );
    }

    /**
     * Analyze economic impact on specific symbols
     *
     * @param array $symbols Stock symbols
     * @param array $economic_factors Economic factors data
     * @return array Symbol-specific economic analysis
     */
    private function analyze_symbol_economic_impact( $symbols, $economic_factors ) {
        $symbol_analysis = array();
        
        foreach ( $symbols as $symbol ) {
            $sector = $this->get_symbol_sector( $symbol );
            $relevant_factors = $this->get_relevant_factors_for_sector( $sector, $economic_factors );
            
            $symbol_analysis[ $symbol ] = array(
                'sector' => $sector,
                'relevant_factors' => $relevant_factors,
                'overall_impact' => $this->calculate_overall_impact( $relevant_factors ),
                'risk_level' => $this->assess_economic_risk( $relevant_factors ),
                'opportunities' => $this->identify_opportunities( $relevant_factors, $sector ),
                'threats' => $this->identify_threats( $relevant_factors, $sector )
            );
        }
        
        return $symbol_analysis;
    }

    /**
     * Get sector for a symbol (simplified mapping)
     *
     * @param string $symbol Stock symbol
     * @return string Sector name
     */
    private function get_symbol_sector( $symbol ) {
        $sector_mapping = array(
            'AAPL' => 'Technology',
            'MSFT' => 'Technology',
            'GOOGL' => 'Technology',
            'AMZN' => 'Consumer Discretionary',
            'TSLA' => 'Consumer Discretionary',
            'META' => 'Technology',
            'NVDA' => 'Technology',
            'AMD' => 'Technology',
            'NFLX' => 'Consumer Discretionary',
            'CRM' => 'Technology',
            'JPM' => 'Banking',
            'BAC' => 'Banking',
            'XOM' => 'Energy',
            'CVX' => 'Energy'
        );
        
        return isset( $sector_mapping[ $symbol ] ) ? $sector_mapping[ $symbol ] : 'Mixed';
    }

    /**
     * Get relevant economic factors for a sector
     *
     * @param string $sector Sector name
     * @param array $economic_factors All economic factors
     * @return array Relevant factors for the sector
     */
    private function get_relevant_factors_for_sector( $sector, $economic_factors ) {
        $relevant_factors = array();
        
        foreach ( $economic_factors as $factor_key => $factor_data ) {
            if ( in_array( $sector, $factor_data['sectors_affected'] ) || 
                 in_array( 'All Sectors', $factor_data['sectors_affected'] ) ) {
                $relevant_factors[ $factor_key ] = $factor_data;
            }
        }
        
        return $relevant_factors;
    }

    /**
     * Calculate overall economic impact score
     *
     * @param array $relevant_factors Relevant economic factors
     * @return array Impact score and description
     */
    private function calculate_overall_impact( $relevant_factors ) {
        $impact_scores = array(
            'positive' => 2,
            'neutral_to_positive' => 1,
            'neutral' => 0,
            'neutral_to_negative' => -1,
            'negative' => -2
        );
        
        $total_score = 0;
        $factor_count = 0;
        
        foreach ( $relevant_factors as $factor ) {
            $weight = $factor['impact_level'] === 'high' ? 2 : 1;
            $score = isset( $impact_scores[ $factor['market_impact'] ] ) ? $impact_scores[ $factor['market_impact'] ] : 0;
            $total_score += $score * $weight;
            $factor_count += $weight;
        }
        
        $average_score = $factor_count > 0 ? $total_score / $factor_count : 0;
        
        if ( $average_score >= 1 ) {
            return array( 'score' => $average_score, 'outlook' => 'positive', 'description' => 'Favorable economic conditions' );
        } elseif ( $average_score <= -1 ) {
            return array( 'score' => $average_score, 'outlook' => 'negative', 'description' => 'Challenging economic headwinds' );
        } else {
            return array( 'score' => $average_score, 'outlook' => 'neutral', 'description' => 'Mixed economic signals' );
        }
    }

    /**
     * Assess economic risk level
     *
     * @param array $relevant_factors Relevant economic factors
     * @return string Risk level (low, medium, high)
     */
    private function assess_economic_risk( $relevant_factors ) {
        $high_impact_negative = 0;
        $total_factors = count( $relevant_factors );
        
        foreach ( $relevant_factors as $factor ) {
            if ( $factor['impact_level'] === 'high' && 
                 in_array( $factor['market_impact'], array( 'negative', 'neutral_to_negative' ) ) ) {
                $high_impact_negative++;
            }
        }
        
        if ( $high_impact_negative >= 2 || ( $high_impact_negative >= 1 && $total_factors <= 3 ) ) {
            return 'high';
        } elseif ( $high_impact_negative >= 1 ) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Identify economic opportunities
     *
     * @param array $relevant_factors Relevant economic factors
     * @param string $sector Sector name
     * @return array Opportunities
     */
    private function identify_opportunities( $relevant_factors, $sector ) {
        $opportunities = array();
        
        foreach ( $relevant_factors as $factor_key => $factor ) {
            if ( $factor['market_impact'] === 'positive' ) {
                $opportunities[] = array(
                    'factor' => $factor['title'],
                    'description' => $this->get_opportunity_description( $factor_key, $sector, $factor )
                );
            }
        }
        
        return $opportunities;
    }

    /**
     * Identify economic threats
     *
     * @param array $relevant_factors Relevant economic factors
     * @param string $sector Sector name
     * @return array Threats
     */
    private function identify_threats( $relevant_factors, $sector ) {
        $threats = array();
        
        foreach ( $relevant_factors as $factor_key => $factor ) {
            if ( in_array( $factor['market_impact'], array( 'negative', 'neutral_to_negative' ) ) ) {
                $threats[] = array(
                    'factor' => $factor['title'],
                    'description' => $this->get_threat_description( $factor_key, $sector, $factor )
                );
            }
        }
        
        return $threats;
    }

    /**
     * Get opportunity description for factor and sector
     *
     * @param string $factor_key Factor key
     * @param string $sector Sector name
     * @param array $factor Factor data
     * @return string Opportunity description
     */
    private function get_opportunity_description( $factor_key, $sector, $factor ) {
        $descriptions = array(
            'inflation' => array(
                'Technology' => 'Declining inflation may boost tech valuations and consumer spending on technology products',
                'Consumer Discretionary' => 'Lower inflation increases consumer purchasing power for discretionary items',
                'default' => 'Declining inflation generally positive for equity valuations'
            ),
            'trade_policy' => array(
                'Technology' => 'Improved trade relations may reduce supply chain disruptions and tariff costs',
                'default' => 'Better trade relations support global business operations'
            ),
            'employment' => array(
                'Consumer Discretionary' => 'Strong employment supports consumer spending on discretionary items',
                'default' => 'Healthy employment levels support overall economic growth'
            ),
            'gdp_growth' => array(
                'default' => 'Steady GDP growth provides supportive backdrop for corporate earnings'
            )
        );
        
        if ( isset( $descriptions[ $factor_key ][ $sector ] ) ) {
            return $descriptions[ $factor_key ][ $sector ];
        } elseif ( isset( $descriptions[ $factor_key ]['default'] ) ) {
            return $descriptions[ $factor_key ]['default'];
        }
        
        return 'Positive economic factor may benefit sector performance';
    }

    /**
     * Get threat description for factor and sector
     *
     * @param string $factor_key Factor key
     * @param string $sector Sector name
     * @param array $factor Data
     * @return string Threat description
     */
    private function get_threat_description( $factor_key, $sector, $factor ) {
        $descriptions = array(
            'geopolitical' => array(
                'Energy' => 'Geopolitical tensions may cause energy price volatility and supply disruptions',
                'Technology' => 'Global tensions may impact international technology supply chains',
                'default' => 'Geopolitical uncertainty may increase market volatility'
            ),
            'interest_rates' => array(
                'Technology' => 'Higher rates may pressure high-growth tech stock valuations',
                'Banking' => 'Rate changes affect net interest margins and lending activity',
                'default' => 'Interest rate changes impact borrowing costs and valuations'
            )
        );
        
        if ( isset( $descriptions[ $factor_key ][ $sector ] ) ) {
            return $descriptions[ $factor_key ][ $sector ];
        } elseif ( isset( $descriptions[ $factor_key ]['default'] ) ) {
            return $descriptions[ $factor_key ]['default'];
        }
        
        return 'Economic factor may present challenges for sector';
    }

    /**
     * Generate overall economic outlook
     *
     * @param array $economic_factors All economic factors
     * @return array Overall outlook
     */
    private function generate_overall_outlook( $economic_factors ) {
        $positive_factors = 0;
        $negative_factors = 0;
        $neutral_factors = 0;
        
        foreach ( $economic_factors as $factor ) {
            switch ( $factor['market_impact'] ) {
                case 'positive':
                    $positive_factors++;
                    break;
                case 'negative':
                    $negative_factors++;
                    break;
                default:
                    $neutral_factors++;
                    break;
            }
        }
        
        if ( $positive_factors > $negative_factors ) {
            $outlook = 'positive';
            $description = 'Economic conditions are generally supportive for equity markets';
        } elseif ( $negative_factors > $positive_factors ) {
            $outlook = 'negative';
            $description = 'Economic headwinds present challenges for market performance';
        } else {
            $outlook = 'neutral';
            $description = 'Mixed economic signals suggest cautious approach';
        }
        
        return array(
            'outlook' => $outlook,
            'description' => $description,
            'positive_factors' => $positive_factors,
            'negative_factors' => $negative_factors,
            'neutral_factors' => $neutral_factors
        );
    }

    /**
     * Get impact level badge class
     *
     * @param string $impact_level Impact level
     * @return string CSS class
     */
    public function get_impact_class( $impact_level ) {
        switch ( $impact_level ) {
            case 'high':
                return 'impact-high';
            case 'medium':
                return 'impact-medium';
            default:
                return 'impact-low';
        }
    }

    /**
     * Get trend indicator class
     *
     * @param string $trend Trend direction
     * @return string CSS class
     */
    public function get_trend_class( $trend ) {
        switch ( $trend ) {
            case 'improving':
            case 'declining':
                return 'trend-positive';
            case 'stable':
                return 'trend-neutral';
            case 'worsening':
                return 'trend-negative';
            default:
                return 'trend-neutral';
        }
    }

    /**
     * Get outlook badge class
     *
     * @param string $outlook Outlook type
     * @return string CSS class
     */
    public function get_outlook_class( $outlook ) {
        switch ( $outlook ) {
            case 'positive':
                return 'outlook-positive';
            case 'negative':
                return 'outlook-negative';
            default:
                return 'outlook-neutral';
        }
    }
}