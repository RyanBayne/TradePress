<?php
/**
 * TradePress Price Above SMA 50 Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-tradepress-scoring-directive-base.php';

class TradePress_Scoring_Directive_PRICE_ABOVE_SMA_50 extends TradePress_Scoring_Directive_Base {
    
    public function calculate_score($symbol_data, $config = array()) {
        $sma_period = $config['sma_period'] ?? 50;
        $distance_bonus = $config['distance_bonus'] ?? 15;
        $proximity_penalty = $config['proximity_penalty'] ?? 10;
        
        if (!isset($symbol_data['technical']['sma']) || !isset($symbol_data['price'])) {
            return 0;
        }
        
        $current_price = $symbol_data['price'];
        $sma_value = $symbol_data['technical']['sma'];
        
        if ($sma_value <= 0) {
            return 0;
        }
        
        // Calculate distance percentage
        $distance_percent = (($current_price - $sma_value) / $sma_value) * 100;
        
        // Base score
        $base_score = 50;
        
        if ($current_price > $sma_value) {
            // Price above SMA - bullish
            $base_score += $distance_bonus;
            
            // Additional bonus for significant distance above
            if ($distance_percent > 5) {
                $base_score += ($distance_bonus * 0.5);
            }
        } else {
            // Price below SMA - bearish
            $base_score -= $proximity_penalty;
            
            // Additional penalty for significant distance below
            if ($distance_percent < -5) {
                $base_score -= ($proximity_penalty * 0.5);
            }
        }
        
        return max(0, round($base_score, 1));
    }
    
    public function get_max_score($config = array()) {
        $distance_bonus = $config['distance_bonus'] ?? 15;
        return round(50 + $distance_bonus + ($distance_bonus * 0.5), 1);
    }
    
    public function get_explanation($config = array()) {
        $sma_period = $config['sma_period'] ?? 50;
        $distance_bonus = $config['distance_bonus'] ?? 15;
        $proximity_penalty = $config['proximity_penalty'] ?? 10;
        
        return "Price Above SMA 50 Directive (D16)\n\n" .
               "This directive analyzes price position relative to the Simple Moving Average.\n\n" .
               "Configuration:\n" .
               "- SMA Period: {$sma_period} days\n" .
               "- Distance Bonus: +{$distance_bonus} points\n" .
               "- Proximity Penalty: -{$proximity_penalty} points\n\n" .
               "Scoring Logic:\n" .
               "1. Start with base score of 50 (neutral)\n" .
               "2. Add distance bonus when price > SMA\n" .
               "3. Subtract penalty when price < SMA\n" .
               "4. Extra bonus/penalty for >5% distance\n\n" .
               "SMA Calculation:\n" .
               "SMA = (Sum of closing prices over N periods) / N\n" .
               "Where N = {$sma_period} periods\n\n" .
               "Max Score: " . $this->get_max_score($config) . " points";
    }
}