<?php
/**
 * TradePress AI Integration - Directive Testing System
 *
 * @package TradePress/AI
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_AI_Directive_Tester {
    
    /**
     * Get test symbol based on settings
     */
    public static function get_test_symbol($fallback = 'NVDA') {
        // Check if we should use default symbol
        if (get_option('tradepress_use_default_test_symbol', 'yes') === 'yes') {
            return get_option('tradepress_default_test_symbol', 'NVDA');
        }
        
        // Use provided fallback or randomize from fallback list
        $fallback_symbols = get_option('tradepress_fallback_symbols', 'AAPL,MSFT,GOOGL,AMZN,TSLA');
        $symbols = array_map('trim', explode(',', $fallback_symbols));
        
        if (!empty($symbols)) {
            return $symbols[array_rand($symbols)];
        }
        
        return $fallback;
    }
    
    /**
     * Test single directive with real data
     */
    public static function test_directive($directive_id, $symbol = null) {
        // Get symbol from settings if not provided
        if ($symbol === null) {
            $symbol = self::get_test_symbol();
        }
        $start_time = microtime(true);
        
        // Load directive
        $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . "includes/scoring-system/directives/{$directive_id}.php";
        if (!file_exists($directive_file)) {
            return self::error_result("Directive file not found: {$directive_id}.php");
        }
        
        require_once $directive_file;
        $class_name = 'TradePress_Scoring_Directive_' . str_replace('-', '_', ucwords($directive_id, '-'));
        
        if (!class_exists($class_name)) {
            return self::error_result("Class not found: {$class_name}");
        }
        
        try {
            $instance = new $class_name();
            $dummy_data = self::get_test_data($symbol);
            $result = $instance->calculate_score($dummy_data);
            
            return array(
                'success' => true,
                'directive_id' => $directive_id,
                'symbol' => $symbol,
                'result' => $result,
                'execution_time' => round((microtime(true) - $start_time) * 1000, 2) . 'ms',
                'class_name' => $class_name,
                'test_data' => $dummy_data
            );
        } catch (Exception $e) {
            return self::error_result("Execution error: " . $e->getMessage());
        }
    }
    
    /**
     * Get test data for directive testing
     */
    private static function get_test_data($symbol) {
        return array(
            'symbol' => $symbol,
            'price' => 450.00,
            'volume' => 50000000,
            'avg_volume' => 35000000,
            'technical' => array(
                'rsi' => 35.5,
                'adx' => array('adx' => 28.5, 'plus_di' => 25.2, 'minus_di' => 18.7),
                'macd' => array('macd' => 2.5, 'signal' => 1.8, 'histogram' => 0.7),
                'bollinger_bands' => array('upper_band' => 460, 'middle_band' => 450, 'lower_band' => 440),
                'cci' => -85.2,
                'mfi' => 45.8,
                'ema_20' => 445.0,
                'sma_50' => 440.0,
                'obv' => 1500000000
            )
        );
    }
    
    /**
     * Return error result
     */
    private static function error_result($message) {
        return array(
            'success' => false,
            'error' => $message
        );
    }
}