<?php
/**
 * TradePress On-Balance Volume (OBV) Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-tradepress-scoring-directive-base.php';

class TradePress_Scoring_Directive_OBV extends TradePress_Scoring_Directive_Base {
    
    public function calculate_score($symbol_data, $config = array()) {
        $trend_bonus = $config['trend_bonus'] ?? 20;
        $divergence_penalty = $config['divergence_penalty'] ?? 15;
        $volume_threshold = $config['volume_threshold'] ?? 1.2;
        
        if (!isset($symbol_data['technical']['obv'])) {
            return 0;
        }
        
        $obv_data = $symbol_data['technical']['obv'];
        $current_price = $symbol_data['price'] ?? 0;
        
        // Calculate OBV trend (simplified - would use historical data in real implementation)
        $obv_trend = $this->calculate_obv_trend($obv_data);
        $price_trend = $this->calculate_price_trend($current_price);
        
        // Base score based on OBV momentum
        $base_score = 50; // Neutral starting point
        
        // Trend confirmation bonus
        if ($obv_trend === $price_trend && $obv_trend !== 'neutral') {
            $base_score += $trend_bonus;
        }
        
        // Divergence penalty
        if ($obv_trend !== $price_trend && $obv_trend !== 'neutral' && $price_trend !== 'neutral') {
            $base_score -= $divergence_penalty;
        }
        
        // Volume strength adjustment
        $volume_ratio = $symbol_data['volume_ratio'] ?? 1.0;
        if ($volume_ratio >= $volume_threshold) {
            $base_score *= 1.2; // 20% boost for strong volume
        }
        
        return max(0, round($base_score, 1));
    }
    
    public function get_max_score($config = array()) {
        $trend_bonus = $config['trend_bonus'] ?? 20;
        return round((50 + $trend_bonus) * 1.2, 1); // Max with volume boost
    }
    
    public function get_explanation($config = array()) {
        $trend_bonus = $config['trend_bonus'] ?? 20;
        $divergence_penalty = $config['divergence_penalty'] ?? 15;
        $volume_threshold = $config['volume_threshold'] ?? 1.2;
        
        return "On-Balance Volume (OBV) Directive (D15)\n\n" .
               "This directive analyzes volume flow to confirm price trends and detect potential reversals.\n\n" .
               "Configuration:\n" .
               "- Trend Confirmation Bonus: +{$trend_bonus} points\n" .
               "- Divergence Penalty: -{$divergence_penalty} points\n" .
               "- Volume Threshold: {$volume_threshold}x average volume\n\n" .
               "Scoring Logic:\n" .
               "1. Start with base score of 50 (neutral)\n" .
               "2. Add trend bonus when OBV confirms price trend\n" .
               "3. Subtract penalty when OBV diverges from price\n" .
               "4. Apply 20% boost for volume ≥ threshold\n\n" .
               "OBV Calculation:\n" .
               "- If close > previous close: OBV = previous OBV + volume\n" .
               "- If close < previous close: OBV = previous OBV - volume\n" .
               "- If close = previous close: OBV = previous OBV\n\n" .
               "Max Score: " . $this->get_max_score($config) . " points";
    }
    
    private function calculate_obv_trend($obv_data) {
        // Simplified trend calculation - would use historical OBV values in real implementation
        if (is_array($obv_data) && isset($obv_data['trend'])) {
            return $obv_data['trend'];
        }
        
        // Mock trend based on OBV value
        $obv_value = is_numeric($obv_data) ? $obv_data : 1000000;
        
        if ($obv_value > 1200000) return 'bullish';
        if ($obv_value < 800000) return 'bearish';
        return 'neutral';
    }
    
    private function calculate_price_trend($current_price) {
        // Simplified price trend - would use historical prices in real implementation
        // For now, assume bullish trend for prices > $100, bearish < $50
        if ($current_price > 100) return 'bullish';
        if ($current_price < 50) return 'bearish';
        return 'neutral';
    }
}