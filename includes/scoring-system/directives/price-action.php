<?php
/**
 * Price Action Scoring Directive
 *
 * @package TradePress/ScoringDirectives
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Scoring_Directive_Price_Action Class
 */
class TradePress_Scoring_Directive_Price_Action extends TradePress_Scoring_Directive_Base {
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'price_action';
        $this->name = 'Price Action';
        $this->description = 'Scores symbols based on price movements relative to moving averages.';
        $this->weight = 20;
        $this->bullish_values = 'Above MA(50)';
        $this->bearish_values = 'Below MA(200)';
        $this->priority = 20;
    }
    
    /**
     * Calculate Price Action-based score for symbol
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param array $symbol_data Symbol data including price and moving averages
     * @param array $config Configuration parameters for scoring
     * @return array Score results with price action analysis
     */
    public function calculate_score($symbol_data, $config = array()) {
        // Get current price and moving averages from symbol data
        $current_price = isset($symbol_data['price']['current']) ? $symbol_data['price']['current'] : 0;
        $ma50 = isset($symbol_data['technical']['moving_averages']['sma_50']) ? $symbol_data['technical']['moving_averages']['sma_50'] : 0;
        $ma200 = isset($symbol_data['technical']['moving_averages']['sma_200']) ? $symbol_data['technical']['moving_averages']['sma_200'] : 0;
        
        // Skip if we don't have valid data
        if ($current_price <= 0 || $ma50 <= 0 || $ma200 <= 0) {
            return 50; // Neutral score
        }
        
        // Calculate score based on price relative to moving averages
        $score = 50; // Start neutral
        
        // Price above MA(50) is bullish
        if ($current_price > $ma50) {
            $percent_above = (($current_price / $ma50) - 1) * 100;
            $score += min(25, $percent_above * 5); // Max +25 points
        }
        // Price below MA(50) is bearish
        else if ($current_price < $ma50) {
            $percent_below = (1 - ($current_price / $ma50)) * 100;
            $score -= min(25, $percent_below * 5); // Max -25 points
        }
        
        // Price above MA(200) is bullish
        if ($current_price > $ma200) {
            $percent_above = (($current_price / $ma200) - 1) * 100;
            $score += min(25, $percent_above * 5); // Max +25 points
        }
        // Price below MA(200) is bearish
        else if ($current_price < $ma200) {
            $percent_below = (1 - ($current_price / $ma200)) * 100;
            $score -= min(25, $percent_below * 5); // Max -25 points
        }
        
        $ma_20_weight = $config['ma_20_weight'] ?? 20;
        $ma_50_weight = $config['ma_50_weight'] ?? 30;
        $ma_200_weight = $config['ma_200_weight'] ?? 50;
        $distance_multiplier = $config['distance_multiplier'] ?? 2.0;
        $max_distance_score = $config['max_distance_score'] ?? 25;
        $trading_mode = $config['trading_mode'] ?? 'long';
        
        $current_price = $symbol_data['price'] ?? 0;
        $ma_20 = $symbol_data['technical']['ma_20'] ?? 0;
        $ma_50 = $symbol_data['technical']['ma_50'] ?? 0;
        $ma_200 = $symbol_data['technical']['ma_200'] ?? 0;
        
        if ($current_price <= 0) {
            return array('score' => 0, 'signal_type' => 'no_data');
        }
        
        $score = 0;
        $above_count = 0;
        $signal_type = 'neutral';
        
        if ($trading_mode === 'long' || $trading_mode === 'both') {
            if ($ma_20 > 0 && $current_price > $ma_20) {
                $distance_percent = (($current_price / $ma_20) - 1) * 100;
                $distance_score = min($max_distance_score, $distance_percent * $distance_multiplier);
                $score += $ma_20_weight + $distance_score;
                $above_count++;
            }
            
            if ($ma_50 > 0 && $current_price > $ma_50) {
                $distance_percent = (($current_price / $ma_50) - 1) * 100;
                $distance_score = min($max_distance_score, $distance_percent * $distance_multiplier);
                $score += $ma_50_weight + $distance_score;
                $above_count++;
            }
            
            if ($ma_200 > 0 && $current_price > $ma_200) {
                $distance_percent = (($current_price / $ma_200) - 1) * 100;
                $distance_score = min($max_distance_score, $distance_percent * $distance_multiplier);
                $score += $ma_200_weight + $distance_score;
                $above_count++;
            }
            
            if ($above_count == 3) $signal_type = 'strong_uptrend';
            elseif ($above_count == 2) $signal_type = 'uptrend';
            elseif ($above_count == 1) $signal_type = 'weak_uptrend';
        }
        
        return array(
            'score' => round($score, 2),
            'above_count' => $above_count,
            'signal_type' => $signal_type,
            'trading_mode' => $trading_mode
        );
    }
    
    /**
     * Get maximum possible score for Price Action directive
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param array $config Configuration parameters
     * @return float Maximum achievable score
     */
    public function get_max_score($config = array()) {
        $ma_20_weight = $config['ma_20_weight'] ?? 20;
        $ma_50_weight = $config['ma_50_weight'] ?? 30;
        $ma_200_weight = $config['ma_200_weight'] ?? 50;
        $max_distance_score = $config['max_distance_score'] ?? 25;
        
        return ($ma_20_weight + $ma_50_weight + $ma_200_weight) + (3 * $max_distance_score);
    }
    
    /**
     * Get detailed explanation of Price Action scoring methodology
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param array $config Current configuration settings
     * @return string Formatted explanation with actual values
     */
    public function get_explanation($config = array()) {
        $ma_20_weight = $config['ma_20_weight'] ?? 20;
        $ma_50_weight = $config['ma_50_weight'] ?? 30;
        $ma_200_weight = $config['ma_200_weight'] ?? 50;
        $distance_multiplier = $config['distance_multiplier'] ?? 2.0;
        $max_distance = $config['max_distance_score'] ?? 25;
        
        return "Price Action Directive Scoring:\n\n" .
               "BASE WEIGHTS:\n" .
               "• MA 20: {$ma_20_weight} points\n" .
               "• MA 50: {$ma_50_weight} points\n" .
               "• MA 200: {$ma_200_weight} points\n\n" .
               "DISTANCE BONUS:\n" .
               "Distance% × {$distance_multiplier} (max {$max_distance} per MA)\n\n" .
               "LONG: Price above MAs\n" .
               "SHORT: Price below MAs\n\n" .
               "Maximum Score: " . $this->get_max_score($config) . "\n\n" .
               "EXAMPLES:\n" .
               "Price 5% above all MAs: " . (($ma_20_weight + $ma_50_weight + $ma_200_weight) + (3 * min($max_distance, 5 * $distance_multiplier))) . " points";
    }
}
