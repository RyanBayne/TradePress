<?php
/**
 * Directive Configuration Schema
 * Centralized field definitions for all directives
 */

class TradePress_Directive_Config_Schema {
    
    public static function get_directive_fields($directive_id) {
        $schemas = array(
            'rsi' => array(
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 2.0, 'default' => 0.5, 'step' => 0.1),
                'bonus_tier_1' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 20),
                'bonus_tier_2' => array('type' => 'int', 'min' => 0, 'max' => 100, 'default' => 40),
                'bonus_tier_3' => array('type' => 'int', 'min' => 0, 'max' => 200, 'default' => 80),
                'min_score' => array('type' => 'float', 'min' => 0, 'max' => 100, 'default' => 0, 'step' => 0.1),
                'period' => array('type' => 'int', 'min' => 1, 'max' => 50, 'default' => 14)
            ),
            'isa_reset' => array(
                'days_before' => array('type' => 'int', 'min' => 0, 'max' => 30, 'default' => 3),
                'days_after' => array('type' => 'int', 'min' => 0, 'max' => 30, 'default' => 3),
                'score_impact' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 10)
            ),
            'macd' => array(
                'fast_period' => array('type' => 'int', 'min' => 5, 'max' => 20, 'default' => 12),
                'slow_period' => array('type' => 'int', 'min' => 20, 'max' => 40, 'default' => 26),
                'signal_period' => array('type' => 'int', 'min' => 5, 'max' => 15, 'default' => 9),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 3.0, 'default' => 1.0, 'step' => 0.1),
                'crossover_bonus' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 30)
            ),
            'bollinger_bands' => array(
                'period' => array('type' => 'int', 'min' => 10, 'max' => 50, 'default' => 20),
                'std_dev' => array('type' => 'float', 'min' => 1.0, 'max' => 3.0, 'default' => 2.0, 'step' => 0.1),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 2.0, 'default' => 1.0, 'step' => 0.1),
                'band_bonus' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 25)
            ),
            'ema' => array(
                'period' => array('type' => 'int', 'min' => 5, 'max' => 50, 'default' => 21),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 3.0, 'default' => 1.0, 'step' => 0.1),
                'distance_bonus' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 20)
            ),
            'stochastic' => array(
                'k_period' => array('type' => 'int', 'min' => 5, 'max' => 30, 'default' => 14),
                'd_period' => array('type' => 'int', 'min' => 1, 'max' => 10, 'default' => 3),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 2.0, 'default' => 1.0, 'step' => 0.1),
                'crossover_bonus' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 25)
            ),
            'adx' => array(
                'period' => array('type' => 'int', 'min' => 5, 'max' => 30, 'default' => 14),
                'strength_threshold' => array('type' => 'int', 'min' => 15, 'max' => 40, 'default' => 25),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 2.0, 'default' => 1.0, 'step' => 0.1)
            ),
            'obv' => array(
                'lookback_period' => array('type' => 'int', 'min' => 5, 'max' => 30, 'default' => 10),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 2.0, 'default' => 1.0, 'step' => 0.1),
                'trend_bonus' => array('type' => 'int', 'min' => 0, 'max' => 30, 'default' => 15)
            ),
            'mfi' => array(
                'period' => array('type' => 'int', 'min' => 5, 'max' => 30, 'default' => 14),
                'oversold_level' => array('type' => 'int', 'min' => 10, 'max' => 30, 'default' => 20),
                'overbought_level' => array('type' => 'int', 'min' => 70, 'max' => 90, 'default' => 80),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 2.0, 'default' => 1.0, 'step' => 0.1),
                'extreme_bonus' => array('type' => 'int', 'min' => 0, 'max' => 40, 'default' => 20)
            ),
            'cci' => array(
                'period' => array('type' => 'int', 'min' => 10, 'max' => 50, 'default' => 20),
                'oversold_level' => array('type' => 'int', 'min' => -200, 'max' => -50, 'default' => -100),
                'overbought_level' => array('type' => 'int', 'min' => 50, 'max' => 200, 'default' => 100),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 1.0, 'default' => 0.5, 'step' => 0.1),
                'extreme_bonus' => array('type' => 'int', 'min' => 0, 'max' => 40, 'default' => 20)
            ),
            'vwap' => array(
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 2.0, 'default' => 1.0, 'step' => 0.1),
                'distance_bonus' => array('type' => 'int', 'min' => 0, 'max' => 30, 'default' => 15)
            ),
            'moving_averages' => array(
                'ma_20_weight' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 20),
                'ma_50_weight' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 30),
                'ma_200_weight' => array('type' => 'int', 'min' => 0, 'max' => 100, 'default' => 50),
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 2.0, 'default' => 1.0, 'step' => 0.1)
            ),
            'volume' => array(
                'base_multiplier' => array('type' => 'float', 'min' => 0.1, 'max' => 3.0, 'default' => 1.0, 'step' => 0.1),
                'high_volume_bonus' => array('type' => 'int', 'min' => 0, 'max' => 50, 'default' => 25),
                'surge_bonus' => array('type' => 'int', 'min' => 0, 'max' => 100, 'default' => 50),
                'min_score' => array('type' => 'float', 'min' => 0, 'max' => 100, 'default' => 0, 'step' => 0.1)
            ),
            'support_resistance_levels' => array(
                'proximity_percent' => array('type' => 'float', 'min' => 0.1, 'max' => 5.0, 'default' => 1.0, 'step' => 0.1),
                'highly_overlapped_min_methods' => array('type' => 'int', 'min' => 2, 'max' => 6, 'default' => 4),
                'well_overlapped_min_methods' => array('type' => 'int', 'min' => 1, 'max' => 4, 'default' => 2),
                'fib_lookback_days' => array('type' => 'int', 'min' => 30, 'max' => 500, 'default' => 252),
                'swing_lookback' => array('type' => 'int', 'min' => 5, 'max' => 50, 'default' => 20)
            ),

        );
        
        return $schemas[$directive_id] ?? array();
    }
    
    public static function validate_field($directive_id, $field_name, $value) {
        $fields = self::get_directive_fields($directive_id);
        if (!isset($fields[$field_name])) return $value;
        
        $config = $fields[$field_name];
        $value = $config['type'] === 'float' ? floatval($value) : intval($value);
        
        return max($config['min'], min($config['max'], $value));
    }
}