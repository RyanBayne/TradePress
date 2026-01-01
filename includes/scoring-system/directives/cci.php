<?php
/**
 * CCI (Commodity Channel Index) Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_CCI extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'cci';
        $this->name = 'Commodity Channel Index';
        $this->description = 'Measures current price level relative to average price over time';
        $this->weight = 10;
        $this->max_score = 100;
        $this->bullish_values = 'Below -100';
        $this->bearish_values = 'Above +100';
        $this->priority = 10;
        
        // Data freshness requirements (in seconds)
        $this->data_freshness_requirements = array(
            'cci_data' => 1800,      // 30 minutes for CCI technical indicator
            'price_data' => 900,     // 15 minutes for underlying price data
            'volume_data' => 900     // 15 minutes for volume data
        );
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $oversold = $config['oversold'] ?? -100;
        $overbought = $config['overbought'] ?? 100;
        
        // Check data freshness before processing
        $symbol = $symbol_data['symbol'] ?? ($symbol_data['price'] ? 'SYMBOL_FROM_PRICE' : 'UNKNOWN');
        
        // Log calculation start
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D4 CCI | {$symbol} | Starting calculation with oversold={$oversold}, overbought={$overbought}");
        }
        
        $this->check_data_freshness($symbol, array('cci_data', 'price_data'));
        
        // Try to get CCI data from cache or API
        $cci_data = $this->get_cci_data($symbol, $config);
        
        if (is_wp_error($cci_data) || $cci_data === null) {
            if (get_option('bugnet_output_directives') === 'yes') {
                $error_msg = is_wp_error($cci_data) ? $cci_data->get_error_message() : 'No CCI value returned';
                TradePress_Directive_Logger::log("D4 CCI | {$symbol} | ERROR: {$error_msg}");
            }
            return array('score' => 0, 'signal' => 'No CCI data available', 'debug' => $cci_data);
        }
        
        $cci_value = is_array($cci_data) ? ($cci_data['cci'] ?? $cci_data) : $cci_data;
        
        // Base score starts at 50 (neutral)
        $base_score = 50;
        
        // Determine signal and scoring
        if ($cci_value <= $oversold) {
            // Oversold condition - bullish signal
            $signal = 'Oversold - Bullish';
            $intensity = min(abs($cci_value - $oversold) / 50, 2); // Max 2x multiplier
            $base_score += 30 + ($intensity * 10);
        } elseif ($cci_value >= $overbought) {
            // Overbought condition - bearish signal
            $signal = 'Overbought - Bearish';
            $intensity = min(abs($cci_value - $overbought) / 50, 2);
            $base_score -= 20 + ($intensity * 10);
        } elseif ($cci_value > -50 && $cci_value < 50) {
            // Neutral zone
            $signal = 'Neutral Zone';
            $base_score += 5;
        } elseif ($cci_value < 0) {
            // Approaching oversold
            $signal = 'Approaching Oversold';
            $base_score += 15;
        } else {
            // Approaching overbought
            $signal = 'Approaching Overbought';
            $base_score -= 10;
        }
        
        $result = array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'cci_value' => round($cci_value, 2),
            'condition' => $cci_value <= $oversold ? 'Oversold' : 
                          ($cci_value >= $overbought ? 'Overbought' : 'Normal'),
            'calculation_details' => "CCI={$cci_value}, Oversold<={$oversold}, Overbought>={$overbought}, BaseScore={$base_score}",
            'debug_info' => "Symbol={$symbol}, CCI_Raw={$cci_data}, Final_Score=" . max(0, min(100, round($base_score)))
        );
        
        // Enhanced logging for developer mode
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D4 CCI | {$symbol} | CCI={$cci_value} Condition={$result['condition']} | Base=50 Adjustments=" . ($base_score - 50) . " | Final_Score={$result['score']} Signal={$signal}");
        }
        
        return $result;
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $oversold = $config['oversold'] ?? -100;
        $overbought = $config['overbought'] ?? 100;
        
        return "CCI (Commodity Channel Index) Directive:\n\n" .
               "Thresholds:\n" .
               "- Oversold: CCI ≤ {$oversold}\n" .
               "- Overbought: CCI ≥ {$overbought}\n\n" .
               "Scoring:\n" .
               "- Oversold condition: +30 to +50 points (bullish)\n" .
               "- Overbought condition: -20 to -40 points (bearish)\n" .
               "- Approaching oversold: +15 points\n" .
               "- Approaching overbought: -10 points\n" .
               "- Neutral zone (-50 to +50): +5 points\n\n" .
               "CCI identifies cyclical turning points and momentum shifts.\n\n" .
               "Data Freshness Requirements:\n" .
               "- CCI Data: 30 minutes\n" .
               "- Price Data: 15 minutes\n" .
               "- Volume Data: 15 minutes";
    }
    
    /**
     * Check data freshness for this directive
     * 
     * @param string $symbol Symbol ticker
     * @param array $required_data Required data types
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
        
        // Developer notice for data freshness check
        if (class_exists('TradePress_Developer_Notices')) {
            TradePress_Developer_Notices::data_freshness_notice(
                'D4 CCI',
                $symbol,
                $validation,
                $this->data_freshness_requirements
            );
        }
    }
    
    /**
     * Get data freshness requirements for this directive
     * 
     * @return array Freshness requirements in seconds
     */
    public function get_data_freshness_requirements() {
        return $this->data_freshness_requirements;
    }
    
    /**
     * Get CCI data with caching
     * 
     * @param string $symbol Symbol ticker
     * @param array $config Configuration
     * @return mixed CCI data or error
     */
    private function get_cci_data($symbol, $config = array()) {
        // Log entry to debug
        error_log(sprintf(
            "[%s] DEBUG: get_cci_data called with symbol=%s",
            date('Y-m-d H:i:s'),
            $symbol
        ) . "\n", 3, TRADEPRESS_PLUGIN_DIR_PATH . 'directives.log');
        
        if (!class_exists('TradePress_Technical_Indicator_Cache')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/technical-indicator-cache.php';
        }
        
        $parameters = array(
            'time_period' => $config['time_period'] ?? 14,
            'interval' => $config['interval'] ?? 'daily'
        );
        
        // Check cache first using Call Register directly
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        $cached_result = TradePress_Call_Register::get_cached_result(
            'alphavantage', 
            'cci', 
            array_merge(array('symbol' => $symbol), $parameters), 
            30 // 30 minutes
        );
        
        if ($cached_result !== false) {
            return $cached_result;
        }
        
        // If no cache, fetch fresh data
        return $this->fetch_fresh_cci_data($symbol, $parameters);
    }
    
    /**
     * Fetch fresh CCI data from API
     * 
     * @param string $symbol Symbol ticker
     * @param array $params Parameters
     * @return mixed CCI data or error
     */
    public function fetch_fresh_cci_data($symbol, $params) {
        // Log API call attempt
        error_log(sprintf(
            "[%s] DEBUG: fetch_fresh_cci_data called with symbol=%s, params=%s",
            date('Y-m-d H:i:s'),
            $symbol,
            json_encode($params)
        ) . "\n", 3, TRADEPRESS_PLUGIN_DIR_PATH . 'directives.log');
        
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        $api = TradePress_API_Factory::create_from_settings('alphavantage', 'paper', 'technical_indicators');
        
        if (is_wp_error($api)) {
            error_log(sprintf(
                "[%s] ERROR: API Factory failed: %s",
                date('Y-m-d H:i:s'),
                $api->get_error_message()
            ) . "\n", 3, TRADEPRESS_PLUGIN_DIR_PATH . 'directives.log');
            return $api;
        }
        
        // Make API call for CCI data
        $cci_response = $api->make_request('CCI', array(
            'symbol' => $symbol,
            'interval' => $params['interval'],
            'time_period' => $params['time_period']
        ));
        
        // Log API response
        error_log(sprintf(
            "[%s] DEBUG: CCI API Response: %s",
            date('Y-m-d H:i:s'),
            is_wp_error($cci_response) ? $cci_response->get_error_message() : json_encode(array_keys($cci_response))
        ) . "\n", 3, TRADEPRESS_PLUGIN_DIR_PATH . 'directives.log');
        
        if (is_wp_error($cci_response)) {
            return $cci_response;
        }
        
        // Extract the most recent CCI value
        $technical_analysis = $cci_response['Technical Analysis: CCI'] ?? array();
        
        if (empty($technical_analysis)) {
            error_log(sprintf(
                "[%s] ERROR: No Technical Analysis CCI data in response. Keys: %s",
                date('Y-m-d H:i:s'),
                json_encode(array_keys($cci_response))
            ) . "\n", 3, TRADEPRESS_PLUGIN_DIR_PATH . 'directives.log');
            return new WP_Error('no_cci_data', 'No CCI data in API response');
        }
        
        // Get the most recent date's data
        $latest_date = array_key_first($technical_analysis);
        $latest_cci = $technical_analysis[$latest_date]['CCI'] ?? null;
        
        error_log(sprintf(
            "[%s] DEBUG: CCI extracted - Date: %s, Value: %s",
            date('Y-m-d H:i:s'),
            $latest_date,
            $latest_cci
        ) . "\n", 3, TRADEPRESS_PLUGIN_DIR_PATH . 'directives.log');
        
        $cci_value = $latest_cci ? (float) $latest_cci : null;
        
        // Cache the result for 30 minutes
        if ($cci_value !== null) {
            TradePress_Call_Register::cache_result(
                'alphavantage',
                'cci',
                array_merge(array('symbol' => $symbol), $params),
                $cci_value,
                30
            );
        }
        
        return $cci_value;
    }
}