<?php
/**
 * Moving Averages Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_MOVING_AVERAGES extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'moving_averages';
        $this->name = 'Moving Averages';
        $this->description = 'Analysis of price in relation to various moving averages';
        $this->weight = 15;
        $this->max_score = 100;
        $this->bullish_values = 'Price above key moving averages';
        $this->bearish_values = 'Price below key moving averages';
        $this->priority = 15;
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $alignment_bonus = $config['alignment_bonus'] ?? 25;
        
        $current_price = $symbol_data['price'] ?? 0;
        $ma_data = $symbol_data['technical']['moving_averages'] ?? null;
        
        if (!$ma_data || $current_price <= 0) {
            return array('score' => 0, 'signal' => 'No MA data');
        }
        
        $short_ma = $ma_data['short_ma'];
        $long_ma = $ma_data['long_ma'];
        
        // Base score starts at 50 (neutral)
        $base_score = 50;
        
        // Analyze price position relative to MAs
        $above_short = $current_price > $short_ma;
        $above_long = $current_price > $long_ma;
        $ma_alignment = $short_ma > $long_ma; // Short MA above long MA = bullish
        
        // Main scoring logic
        if ($above_short && $above_long) {
            // Price above both MAs - bullish
            $base_score += $alignment_bonus;
            
            if ($ma_alignment) {
                // Perfect bullish alignment
                $base_score += 20;
                $signal = 'Perfect Bullish Alignment';
            } else {
                $signal = 'Above Both MAs';
            }
        } elseif (!$above_short && !$above_long) {
            // Price below both MAs - bearish
            $base_score -= ($alignment_bonus * 0.8);
            
            if (!$ma_alignment) {
                // Perfect bearish alignment
                $base_score -= 15;
                $signal = 'Perfect Bearish Alignment';
            } else {
                $signal = 'Below Both MAs';
            }
        } elseif ($above_short && !$above_long) {
            // Price between MAs - mixed signal
            if ($ma_alignment) {
                $base_score += 10;
                $signal = 'Between MAs - Bullish Bias';
            } else {
                $base_score -= 5;
                $signal = 'Between MAs - Mixed';
            }
        } else {
            // Price below short MA but above long MA - unusual
            $signal = 'Below Short MA, Above Long MA';
            $base_score -= 10;
        }
        
        // Distance considerations
        $short_distance = abs(($current_price - $short_ma) / $short_ma) * 100;
        $long_distance = abs(($current_price - $long_ma) / $long_ma) * 100;
        
        // Bonus for being close to MAs (support/resistance)
        if ($short_distance <= 2) {
            $base_score += 10; // Near short MA
        }
        if ($long_distance <= 3) {
            $base_score += 8; // Near long MA
        }
        
        // Penalty for being too far from MAs
        if ($short_distance > 10) {
            $base_score -= 5;
        }
        
        return array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'short_ma' => round($short_ma, 2),
            'long_ma' => round($long_ma, 2),
            'short_distance' => round($short_distance, 2),
            'long_distance' => round($long_distance, 2),
            'ma_alignment' => $ma_alignment ? 'Bullish' : 'Bearish'
        );
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $short_period = $config['short_period'] ?? 20;
        $long_period = $config['long_period'] ?? 50;
        $ma_type = $config['ma_type'] ?? 'SMA';
        $alignment_bonus = $config['alignment_bonus'] ?? 25;
        
        return "Moving Averages Directive:\n\n" .
               "Configuration:\n" .
               "- Short MA: {$short_period}-period {$ma_type}\n" .
               "- Long MA: {$long_period}-period {$ma_type}\n" .
               "- Alignment Bonus: {$alignment_bonus} points\n\n" .
               "Scoring:\n" .
               "- Above both MAs: +{$alignment_bonus} points\n" .
               "- Perfect bullish alignment: +20 additional points\n" .
               "- Below both MAs: -" . round($alignment_bonus * 0.8) . " points\n" .
               "- Perfect bearish alignment: -15 additional points\n" .
               "- Between MAs: ±5 to ±10 points (depends on MA alignment)\n" .
               "- Near MA (≤2-3%): +8 to +10 points\n" .
               "- Far from MA (>10%): -5 points\n\n" .
               "Moving averages define trend direction and key support/resistance levels.";
    }
}