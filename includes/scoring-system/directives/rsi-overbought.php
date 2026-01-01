<?php
/**
 * TradePress RSI Overbought Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_RSI_OVERBOUGHT extends TradePress_Scoring_Directive_Base {
    
    public function calculate_score($symbol_data, $config = array()) {
        $period = $config['period'] ?? 14;
        $overbought_threshold = $config['overbought_threshold'] ?? 70;
        $extreme_overbought_bonus = $config['extreme_overbought_bonus'] ?? 20;
        
        if (!isset($symbol_data['technical']['rsi'])) {
            return 0;
        }
        
        $rsi_value = $symbol_data['technical']['rsi'];
        
        // Base score - this is a bearish directive
        $base_score = 0;
        
        if ($rsi_value >= $overbought_threshold) {
            // RSI is overbought - bearish signal
            $base_score = 50; // Base overbought score
            
            // Additional scoring based on how overbought
            $overbought_intensity = ($rsi_value - $overbought_threshold) / (100 - $overbought_threshold);
            $base_score += ($overbought_intensity * 30);
            
            // Extreme overbought bonus
            if ($rsi_value >= 80) {
                $base_score += $extreme_overbought_bonus;
            }
        }
        
        return round($base_score, 1);
    }
    
    public function get_max_score($config = array()) {
        $extreme_overbought_bonus = $config['extreme_overbought_bonus'] ?? 20;
        return round(50 + 30 + $extreme_overbought_bonus, 1);
    }
    
    public function get_explanation($config = array()) {
        $period = $config['period'] ?? 14;
        $overbought_threshold = $config['overbought_threshold'] ?? 70;
        $extreme_overbought_bonus = $config['extreme_overbought_bonus'] ?? 20;
        
        return "RSI Overbought Directive (D18)\n\n" .
               "This bearish directive identifies overbought conditions for potential pullbacks.\n\n" .
               "Configuration:\n" .
               "- RSI Period: {$period} periods\n" .
               "- Overbought Threshold: {$overbought_threshold}\n" .
               "- Extreme Overbought Bonus: +{$extreme_overbought_bonus} points\n\n" .
               "Scoring Logic:\n" .
               "1. No score if RSI < {$overbought_threshold}\n" .
               "2. Base score of 50 when RSI ≥ {$overbought_threshold}\n" .
               "3. Additional points based on overbought intensity\n" .
               "4. RSI ≥ 80: +{$extreme_overbought_bonus} additional points\n\n" .
               "Use Case:\n" .
               "- Identify potential pullback opportunities\n" .
               "- Risk management for existing long positions\n" .
               "- Short selling signal identification\n\n" .
               "Max Score: " . $this->get_max_score($config) . " points";
    }
}