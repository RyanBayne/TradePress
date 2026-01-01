<?php
/**
 * ADX (Average Directional Index) Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_ADX extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'adx';
        $this->name = 'Average Directional Index';
        $this->description = 'Measures trend strength without indicating direction';
        $this->weight = 10;
        $this->max_score = 100;
        $this->bullish_values = 'ADX > 25 with +DI > -DI';
        $this->bearish_values = 'ADX > 25 with -DI > +DI';
        $this->priority = 10;
        
        // Data freshness requirements (in seconds)
        $this->data_freshness_requirements = array(
            'adx_data' => 1800,      // 30 minutes for ADX technical indicator
            'price_data' => 900,     // 15 minutes for underlying price data
            'volume_data' => 900     // 15 minutes for volume data
        );
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $strong_trend = $config['strong_trend'] ?? 25;
        $very_strong_trend = $config['very_strong_trend'] ?? 40;
        
        // Check data freshness before processing
        $symbol = $symbol_data['symbol'] ?? 'UNKNOWN';
        $this->check_data_freshness($symbol, array('adx_data', 'price_data'));
        
        // Log calculation start
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D1 ADX | {$symbol} | Starting calculation with thresholds: strong={$strong_trend}, very_strong={$very_strong_trend}");
        }
        
        // Try to get ADX data from cache or API
        $adx_data = $this->get_adx_data($symbol, $config);
        
        if (is_wp_error($adx_data) || !$adx_data) {
            if (get_option('bugnet_output_directives') === 'yes') {
                $error_msg = is_wp_error($adx_data) ? $adx_data->get_error_message() : 'No data returned';
                TradePress_Directive_Logger::log("D1 ADX | {$symbol} | ERROR: {$error_msg}");
            }
            return array('score' => 0, 'signal' => 'No ADX data available');
        }
        
        $adx = $adx_data['adx'];
        $plus_di = $adx_data['plus_di'];
        $minus_di = $adx_data['minus_di'];
        
        // Base score starts at 50 (neutral)
        $base_score = 50;
        
        // Determine trend direction
        $is_bullish = $plus_di > $minus_di;
        $di_difference = abs($plus_di - $minus_di);
        
        // Score based on trend strength
        if ($adx >= $very_strong_trend) {
            // Very strong trend
            $trend_bonus = 30;
            $signal = $is_bullish ? 'Very Strong Uptrend' : 'Very Strong Downtrend';
        } elseif ($adx >= $strong_trend) {
            // Strong trend
            $trend_bonus = 20;
            $signal = $is_bullish ? 'Strong Uptrend' : 'Strong Downtrend';
        } elseif ($adx >= 20) {
            // Moderate trend
            $trend_bonus = 10;
            $signal = $is_bullish ? 'Moderate Uptrend' : 'Moderate Downtrend';
        } else {
            // Weak trend/consolidation
            $trend_bonus = -10;
            $signal = 'Weak Trend/Consolidation';
        }
        
        // Additional bonus for clear directional bias
        $direction_bonus = min(15, $di_difference * 0.5);
        
        // Calculate final score
        if ($is_bullish) {
            $final_score = $base_score + $trend_bonus + $direction_bonus;
        } else {
            // For bearish trends, reduce score
            $final_score = $base_score - ($trend_bonus * 0.5) + ($direction_bonus * 0.3);
        }
        
        $result = array(
            'score' => max(0, min(100, round($final_score))),
            'signal' => $signal,
            'adx_value' => round($adx, 2),
            'plus_di' => round($plus_di, 2),
            'minus_di' => round($minus_di, 2),
            'trend_strength' => $adx >= $very_strong_trend ? 'Very Strong' : 
                              ($adx >= $strong_trend ? 'Strong' : 
                              ($adx >= 20 ? 'Moderate' : 'Weak'))
        );
        
        // Enhanced logging for developer mode
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D1 ADX | {$symbol} | ADX={$adx} +DI={$plus_di} -DI={$minus_di} | Bullish={$is_bullish} DI_Diff={$di_difference} | Base={$base_score} Trend_Bonus={$trend_bonus} Dir_Bonus={$direction_bonus} | Final_Score={$result['score']} Signal={$signal}");
        }
        
        // Log calculation if enabled
        $this->log_calculation($symbol_data, $result);
        
        return $result;
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $strong_trend = $config['strong_trend'] ?? 25;
        $very_strong_trend = $config['very_strong_trend'] ?? 40;
        
        return "ADX (Average Directional Index) Directive:\n\n" .
               "Thresholds:\n" .
               "- Strong Trend: ADX ≥ {$strong_trend}\n" .
               "- Very Strong Trend: ADX ≥ {$very_strong_trend}\n\n" .
               "Scoring:\n" .
               "- Very Strong Trend: +30 points\n" .
               "- Strong Trend: +20 points\n" .
               "- Moderate Trend (ADX ≥ 20): +10 points\n" .
               "- Weak Trend (ADX < 20): -10 points\n" .
               "- Direction clarity bonus: up to +15 points\n\n" .
               "ADX measures trend strength regardless of direction. Higher ADX values indicate stronger trends.\n\n" .
               "Data Freshness Requirements:\n" .
               "- ADX Data: 30 minutes\n" .
               "- Price Data: 15 minutes\n" .
               "- Volume Data: 15 minutes";
    }
    
    /**
     * Check data freshness for this directive
     */
    private function check_data_freshness($symbol, $required_data) {
        if (!class_exists('TradePress_Data_Freshness_Manager')) {
            return;
        }
        
        $validation = TradePress_Data_Freshness_Manager::validate_data_freshness(
            $symbol,
            'scoring_algorithms',
            $required_data
        );
        
        if (class_exists('TradePress_Developer_Notices')) {
            TradePress_Developer_Notices::data_freshness_notice(
                'D1 ADX',
                $symbol,
                $validation,
                $this->data_freshness_requirements
            );
        }
    }
    
    public function get_data_freshness_requirements() {
        return $this->data_freshness_requirements;
    }
    
    /**
     * Get ADX data with caching
     */
    private function get_adx_data($symbol, $config = array()) {
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        $parameters = array(
            'symbol' => $symbol,
            'time_period' => $config['time_period'] ?? 14,
            'interval' => $config['interval'] ?? 'daily'
        );
        
        // Check cache first - 30 minute cache for ADX data
        $cached_result = TradePress_Call_Register::get_cached_result(
            'alphavantage', 
            'adx', 
            $parameters, 
            30
        );
        
        if ($cached_result !== false) {
            return $cached_result;
        }
        
        // Fetch fresh data if not cached
        $fresh_data = $this->fetch_fresh_adx_data($symbol, $parameters);
        
        if (!is_wp_error($fresh_data) && $fresh_data !== false) {
            // Cache the result for 30 minutes
            TradePress_Call_Register::cache_result(
                'alphavantage',
                'adx',
                $parameters,
                $fresh_data,
                30
            );
        }
        
        return $fresh_data;
    }
    
    /**
     * Fetch fresh ADX data from API
     */
    private function fetch_fresh_adx_data($symbol, $params) {
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        $api = TradePress_API_Factory::create_from_settings('alphavantage', 'paper', 'technical_indicators');
        
        if (is_wp_error($api)) {
            return $api;
        }
        
        $api_params = array(
            'symbol' => $symbol,
            'interval' => $params['interval'] ?? 'daily',
            'time_period' => (string)($params['time_period'] ?? 14)
        );
        
        // Make API calls for ADX, +DI, and -DI (Alpha Vantage requires separate calls)
        $adx_response = $api->make_request('ADX', $api_params);
        
        if (is_wp_error($adx_response)) {
            return $adx_response;
        }
        
        // Check if ADX response contains all needed data
        $adx_data = $adx_response['Technical Analysis: ADX'] ?? array();
        
        if (empty($adx_data)) {
            return new WP_Error('no_adx_data', 'No ADX data from API');
        }
        
        // Get the most recent date's data
        $latest_date = array_key_first($adx_data);
        $latest_adx_data = $adx_data[$latest_date];
        
        // Make additional calls for +DI and -DI (Alpha Vantage limitation)
        $plus_di_response = $api->make_request('PLUS_DI', $api_params);
        $minus_di_response = $api->make_request('MINUS_DI', $api_params);
        
        $plus_di_data = $plus_di_response['Technical Analysis: PLUS_DI'] ?? array();
        $minus_di_data = $minus_di_response['Technical Analysis: MINUS_DI'] ?? array();
        
        return array(
            'adx' => (float) ($latest_adx_data['ADX'] ?? 0),
            'plus_di' => (float) ($plus_di_data[$latest_date]['PLUS_DI'] ?? 0),
            'minus_di' => (float) ($minus_di_data[$latest_date]['MINUS_DI'] ?? 0)
        );
    }
}