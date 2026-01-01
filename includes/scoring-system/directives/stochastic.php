<?php
/**
 * Stochastic Oscillator Scoring Directive
 *
 * The Stochastic Oscillator compares a security's closing price to its price range over a given period.
 * It consists of %K (fast line) and %D (slow line, 3-period SMA of %K). This momentum indicator
 * is excellent for identifying overbought/oversold conditions in ranging markets.
 *
 * Key Features:
 * - Momentum oscillator ranging from 0 to 100
 * - Two lines: %K (fast) and %D (slow)
 * - Excellent for ranging/sideways markets
 * - Identifies momentum shifts and reversals
 *
 * Trading Applications:
 * - %K crosses above %D from below 20: Bullish signal (oversold bounce)
 * - %K crosses below %D from above 80: Bearish signal (overbought decline)
 * - Divergences with price: Potential reversal signals
 * - Best avoided during strong trending markets
 *
 * @package TradePress/ScoringDirectives
 * @version 1.0.0
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_STOCHASTIC extends TradePress_Scoring_Directive_Base {
    
    /**
     * Constructor - Initialize Stochastic directive properties
     *
     * @since 1.0.0
     * @version 1.0.0
     */
    public function __construct() {
        $this->id = 'stochastic';
        $this->name = 'Stochastic Oscillator';
        $this->description = 'Compares closing price to price range over time';
        $this->weight = 15;
        $this->max_score = 75;
        $this->bullish_values = 'K crosses above D from below 20';
        $this->bearish_values = 'K crosses below D from above 80';
        $this->priority = 15;
    }
    
    /**
     * Calculate Stochastic-based score for symbol
     *
     * Analyzes %K and %D crossovers in extreme zones to identify reversal opportunities.
     * Focuses on oversold/overbought conditions for mean-reversion strategies.
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param array $symbol_data Symbol technical data including Stochastic values
     * @param array $config Configuration parameters for scoring
     * @return array Score results with Stochastic analysis
     */
    public function calculate_score($symbol_data, $config = array()) {
        $k_period = $config['k_period'] ?? 14;
        $d_period = $config['d_period'] ?? 3;
        $base_multiplier = $config['base_multiplier'] ?? 1.0;
        $crossover_bonus = $config['crossover_bonus'] ?? 25;
        $trading_mode = $config['trading_mode'] ?? 'long';
        
        $k_value = $symbol_data['technical']['stoch_k'] ?? 50;
        $d_value = $symbol_data['technical']['stoch_d'] ?? 50;
        
        $score = 0;
        $signal_type = 'neutral';
        
        if ($trading_mode === 'long' || $trading_mode === 'both') {
            if ($k_value > $d_value && $k_value < 30) {
                $score = (30 - $k_value) * $base_multiplier + $crossover_bonus;
                $signal_type = 'oversold_bullish';
            }
        }
        
        if ($trading_mode === 'short' || $trading_mode === 'both') {
            if ($k_value < $d_value && $k_value > 70) {
                $short_score = ($k_value - 70) * $base_multiplier + $crossover_bonus;
                $score = max($score, $short_score);
                $signal_type = 'overbought_bearish';
            }
        }
        
        return array(
            'score' => round($score, 2),
            'k_value' => $k_value,
            'd_value' => $d_value,
            'signal_type' => $signal_type,
            'trading_mode' => $trading_mode
        );
    }
    
    /**
     * Get maximum possible score for Stochastic directive
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param array $config Configuration parameters
     * @return float Maximum achievable score
     */
    public function get_max_score($config = array()) {
        $base_multiplier = $config['base_multiplier'] ?? 1.0;
        $crossover_bonus = $config['crossover_bonus'] ?? 25;
        return (30 * $base_multiplier) + $crossover_bonus;
    }
    
    /**
     * Get detailed explanation of Stochastic scoring methodology
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param array $config Current configuration settings
     * @return string Formatted explanation with actual values
     */
    public function get_explanation($config = array()) {
        $k_period = $config['k_period'] ?? 14;
        $d_period = $config['d_period'] ?? 3;
        $multiplier = $config['base_multiplier'] ?? 1.0;
        $bonus = $config['crossover_bonus'] ?? 25;
        
        return "Stochastic Directive ({$k_period},{$d_period}):\n\n" .
               "LONG: %K > %D when %K < 30 (oversold)\n" .
               "SHORT: %K < %D when %K > 70 (overbought)\n\n" .
               "Score = Distance × {$multiplier} + {$bonus} bonus\n\n" .
               "Best for ranging markets and mean-reversion strategies.";
    }
}