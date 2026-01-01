<?php
/**
 * VWAP Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_VWAP extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'vwap';
        $this->name = 'Volume Weighted Average Price';
        $this->description = 'Average price weighted by volume to identify intraday trends';
        $this->weight = 10;
        $this->max_score = 50;
        $this->bullish_values = 'Price below VWAP';
        $this->bearish_values = 'Price above VWAP';
        $this->priority = 20;
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $base_multiplier = $config['base_multiplier'] ?? 1.0;
        $distance_bonus = $config['distance_bonus'] ?? 15;
        $trading_mode = $config['trading_mode'] ?? 'long';
        
        $current_price = $symbol_data['price'] ?? 0;
        $vwap_value = $symbol_data['technical']['vwap'] ?? 0;
        
        if ($vwap_value == 0) {
            return array('score' => 0, 'signal_type' => 'no_data');
        }
        
        $distance_percent = (($current_price - $vwap_value) / $vwap_value) * 100;
        $score = 0;
        $signal_type = 'neutral';
        
        if ($trading_mode === 'long' || $trading_mode === 'both') {
            if ($current_price < $vwap_value) {
                $score = abs($distance_percent) * $base_multiplier + $distance_bonus;
                $signal_type = 'below_vwap_value';
            }
        }
        
        if ($trading_mode === 'short' || $trading_mode === 'both') {
            if ($current_price > $vwap_value) {
                $short_score = $distance_percent * $base_multiplier + $distance_bonus;
                $score = max($score, $short_score);
                $signal_type = 'above_vwap_resistance';
            }
        }
        
        return array(
            'score' => round($score, 2),
            'distance_percent' => round($distance_percent, 2),
            'vwap_value' => $vwap_value,
            'signal_type' => $signal_type,
            'trading_mode' => $trading_mode
        );
    }
    
    public function get_max_score($config = array()) {
        $base_multiplier = $config['base_multiplier'] ?? 1.0;
        $distance_bonus = $config['distance_bonus'] ?? 15;
        return (10 * $base_multiplier) + $distance_bonus;
    }
    
    public function get_explanation($config = array()) {
        $multiplier = $config['base_multiplier'] ?? 1.0;
        $bonus = $config['distance_bonus'] ?? 15;
        
        return "VWAP Directive:\n\n" .
               "Score = Distance% × {$multiplier} + {$bonus} bonus\n\n" .
               "LONG: Price below VWAP (value opportunity)\n" .
               "SHORT: Price above VWAP (resistance level)\n\n" .
               "VWAP is the institutional benchmark for fair value.";
    }
}