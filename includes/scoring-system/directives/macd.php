<?php
/**
 * MACD (Moving Average Convergence Divergence) Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_MACD extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'macd';
        $this->name = 'MACD';
        $this->description = 'Momentum indicator that shows the relationship between two moving averages';
        $this->weight = 25;
        $this->max_score = 100;
        $this->bullish_values = 'Positive Crossover';
        $this->bearish_values = 'Negative Crossover';
        $this->priority = 25;
        
        // Data freshness requirements (in seconds)
        $this->data_freshness_requirements = array(
            'macd_data' => 1800,     // 30 minutes for MACD technical indicator
            'price_data' => 900,     // 15 minutes for underlying price data
            'volume_data' => 900     // 15 minutes for volume data
        );
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $crossover_bonus = $config['crossover_bonus'] ?? 30;
        
        // Check data freshness before processing
        $symbol = $symbol_data['symbol'] ?? 'UNKNOWN';
        $this->check_data_freshness($symbol, array('macd_data', 'price_data'));
        
        // Log calculation start
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D10 MACD | {$symbol} | Starting calculation with crossover_bonus={$crossover_bonus}");
        }
        
        // Try to get MACD data from cache or API
        $macd_data = $this->get_macd_data($symbol, $config);
        
        if (is_wp_error($macd_data) || !$macd_data) {
            if (get_option('bugnet_output_directives') === 'yes') {
                $error_msg = is_wp_error($macd_data) ? $macd_data->get_error_message() : 'No MACD data returned';
                TradePress_Directive_Logger::log("D10 MACD | {$symbol} | ERROR: {$error_msg}");
            }
            return array('score' => 0, 'signal' => 'No MACD data available');
        }
        
        $macd_line = $macd_data['macd'];
        $signal_line = $macd_data['signal'];
        $histogram = $macd_data['histogram'];
        
        // Base score starts at 50 (neutral)
        $base_score = 50;
        
        // Determine crossover and momentum
        $is_bullish_crossover = $macd_line > $signal_line;
        $histogram_trend = $histogram > 0 ? 'positive' : 'negative';
        
        // Main scoring logic
        if ($is_bullish_crossover) {
            // MACD above signal line - bullish
            $base_score += $crossover_bonus;
            
            if ($macd_line > 0 && $signal_line > 0) {
                // Both above zero line - very bullish
                $base_score += 15;
                $signal = 'Strong Bullish - Above Zero';
            } else {
                $signal = 'Bullish Crossover';
            }
            
            // Histogram momentum bonus
            if ($histogram > 0) {
                $base_score += 10;
            }
        } else {
            // MACD below signal line - bearish
            $base_score -= ($crossover_bonus * 0.7);
            
            if ($macd_line < 0 && $signal_line < 0) {
                // Both below zero line - very bearish
                $base_score -= 15;
                $signal = 'Strong Bearish - Below Zero';
            } else {
                $signal = 'Bearish Crossover';
            }
            
            // Histogram momentum penalty
            if ($histogram < 0) {
                $base_score -= 10;
            }
        }
        
        // Zero line considerations
        if (abs($macd_line) < 0.1 && abs($signal_line) < 0.1) {
            // Near zero line - consolidation
            $base_score = 50; // Reset to neutral
            $signal = 'Consolidation - Near Zero';
        }
        
        // Divergence detection (simplified)
        $macd_strength = abs($macd_line - $signal_line);
        if ($macd_strength > 1.0) {
            $base_score += ($is_bullish_crossover ? 5 : -5);
        }
        
        $result = array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'macd_line' => round($macd_line, 4),
            'signal_line' => round($signal_line, 4),
            'histogram' => round($histogram, 4),
            'crossover_type' => $is_bullish_crossover ? 'Bullish' : 'Bearish'
        );
        
        // Enhanced logging for developer mode
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D10 MACD | {$symbol} | MACD={$macd_line} Signal={$signal_line} Histogram={$histogram} | Crossover={$result['crossover_type']} | Base=50 Adjustments=" . ($base_score - 50) . " | Final_Score={$result['score']} Signal={$signal}");
        }
        
        return $result;
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $fast_period = $config['fast_period'] ?? 12;
        $slow_period = $config['slow_period'] ?? 26;
        $signal_period = $config['signal_period'] ?? 9;
        $crossover_bonus = $config['crossover_bonus'] ?? 30;
        
        return "MACD (Moving Average Convergence Divergence) Directive:\n\n" .
               "Parameters:\n" .
               "- Fast EMA: {$fast_period} periods\n" .
               "- Slow EMA: {$slow_period} periods\n" .
               "- Signal EMA: {$signal_period} periods\n\n" .
               "Scoring:\n" .
               "- Bullish crossover: +{$crossover_bonus} points\n" .
               "- Bearish crossover: -" . round($crossover_bonus * 0.7) . " points\n" .
               "- Above zero line: +15 points (very bullish)\n" .
               "- Below zero line: -15 points (very bearish)\n" .
               "- Positive histogram: +10 points\n" .
               "- Negative histogram: -10 points\n\n" .
               "MACD crossovers are excellent for momentum plays and trend changes.\n\n" .
               "Data Freshness Requirements:\n" .
               "- MACD Data: 30 minutes\n" .
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
                'D10 MACD',
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
     * Get MACD data with caching
     */
    private function get_macd_data($symbol, $config = array()) {
        if (!class_exists('TradePress_Technical_Indicator_Cache')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/technical-indicator-cache.php';
        }
        
        $parameters = array(
            'fastperiod' => $config['fast_period'] ?? 12,
            'slowperiod' => $config['slow_period'] ?? 26,
            'signalperiod' => $config['signal_period'] ?? 9,
            'interval' => $config['interval'] ?? 'daily'
        );
        
        return TradePress_Technical_Indicator_Cache::get_or_fetch_indicator(
            $symbol,
            'macd',
            $parameters,
            function($symbol, $params) {
                return $this->fetch_fresh_macd_data($symbol, $params);
            }
        );
    }
    
    /**
     * Fetch fresh MACD data from API
     */
    private function fetch_fresh_macd_data($symbol, $params) {
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        $api = TradePress_API_Factory::create_from_settings('alphavantage', 'paper', 'technical_indicators');
        
        if (is_wp_error($api)) {
            return $api;
        }
        
        // Make API call for MACD data
        $macd_response = $api->make_request('MACD', array(
            'symbol' => $symbol,
            'interval' => $params['interval'],
            'fastperiod' => $params['fastperiod'],
            'slowperiod' => $params['slowperiod'],
            'signalperiod' => $params['signalperiod']
        ));
        
        if (is_wp_error($macd_response)) {
            return $macd_response;
        }
        
        // Extract the most recent MACD values
        $technical_analysis = $macd_response['Technical Analysis: MACD'] ?? array();
        
        if (empty($technical_analysis)) {
            return new WP_Error('no_macd_data', 'No MACD data in API response');
        }
        
        // Get the most recent date's data
        $latest_date = array_key_first($technical_analysis);
        $latest_data = $technical_analysis[$latest_date];
        
        return array(
            'macd' => (float) ($latest_data['MACD'] ?? 0),
            'signal' => (float) ($latest_data['MACD_Signal'] ?? 0),
            'histogram' => (float) ($latest_data['MACD_Hist'] ?? 0)
        );
    }
}