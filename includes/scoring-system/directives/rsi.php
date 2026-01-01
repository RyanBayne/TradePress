<?php
/**
 * TradePress RSI Directive
 *
 * @package TradePress
 * @subpackage Scoring/Directives
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

class TradePress_Scoring_Directive_RSI extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'rsi';
        $this->name = 'Relative Strength Index';
        $this->description = 'Momentum oscillator measuring speed and magnitude of price changes';
        $this->weight = 10;
        $this->max_score = 100;
        $this->bullish_values = 'RSI < 30 (oversold)';
        $this->bearish_values = 'RSI > 70 (overbought)';
        $this->priority = 10;
        
        // Data freshness requirements (in seconds)
        $this->data_freshness_requirements = array(
            'rsi_data' => 1800,      // 30 minutes for RSI technical indicator
            'price_data' => 900,     // 15 minutes for underlying price data
            'volume_data' => 900     // 15 minutes for volume data
        );
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $period = $config['period'] ?? 14;
        $oversold = $config['oversold'] ?? 30;
        $overbought = $config['overbought'] ?? 70;
        $extreme_bonus = $config['extreme_bonus'] ?? 25;
        
        // Check data freshness before processing
        $symbol = $symbol_data['symbol'] ?? 'UNKNOWN';
        
        // Start developer flow tracking
        if (!class_exists('TradePress_Developer_Flow_Logger')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/developer-flow-logger.php';
        }
        TradePress_Developer_Flow_Logger::start_flow('D17_RSI_Calculation', "RSI calculation for {$symbol}");
        TradePress_Developer_Flow_Logger::log_action('CONFIG_LOADED', 'Configuration parameters set', array(
            'symbol' => $symbol,
            'period' => $period,
            'oversold' => $oversold,
            'overbought' => $overbought,
            'extreme_bonus' => $extreme_bonus
        ));
        
        $this->check_data_freshness($symbol, array('rsi_data', 'price_data'));
        
        // Log calculation start
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D17 RSI | {$symbol} | Starting calculation with period={$period}, oversold={$oversold}, overbought={$overbought}, extreme_bonus={$extreme_bonus}");
        }
        
        TradePress_Developer_Flow_Logger::log_action('DATA_FETCH_START', 'Beginning RSI data retrieval');
        
        // Try to get RSI data from cache or API
        $rsi_value = $this->get_rsi_data($symbol, $config);
        
        if (is_wp_error($rsi_value) || $rsi_value === null) {
            $error_msg = is_wp_error($rsi_value) ? $rsi_value->get_error_message() : 'No RSI value returned';
            TradePress_Developer_Flow_Logger::end_flow('CALCULATION_FAILED', $error_msg);
            
            if (get_option('bugnet_output_directives') === 'yes') {
                TradePress_Directive_Logger::log("D17 RSI | {$symbol} | ERROR: {$error_msg}");
            }
            return null; // Return null instead of 0 to indicate calculation failure
        }
        
        // Base score calculation
        $base_score = 50; // Neutral starting point
        
        // Long position scoring (lower RSI = higher score)
        if ($rsi_value <= $oversold) {
            $base_score += 30; // Oversold bonus
            
            // Extreme oversold bonus
            if ($rsi_value <= 20) {
                $base_score += $extreme_bonus;
            }
        } elseif ($rsi_value >= $overbought) {
            $base_score -= 20; // Overbought penalty
            
            // Extreme overbought penalty
            if ($rsi_value >= 80) {
                $base_score -= $extreme_bonus;
            }
        } else {
            // Neutral zone - slight adjustment based on position
            $neutral_adjustment = (50 - $rsi_value) * 0.2;
            $base_score += $neutral_adjustment;
        }
        
        $final_score = max(0, round($base_score, 1));
        
        TradePress_Developer_Flow_Logger::log_action('SCORE_CALCULATED', 'Final score computed', array(
            'rsi_value' => $rsi_value,
            'base_score' => $base_score,
            'final_score' => $final_score,
            'condition' => $rsi_value <= $oversold ? 'OVERSOLD' : ($rsi_value >= $overbought ? 'OVERBOUGHT' : 'NEUTRAL')
        ));
        
        TradePress_Developer_Flow_Logger::end_flow('CALCULATION_SUCCESS', "Score: {$final_score}");
        
        // Enhanced logging for developer mode
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            $condition = $rsi_value <= $oversold ? 'OVERSOLD' : ($rsi_value >= $overbought ? 'OVERBOUGHT' : 'NEUTRAL');
            $extreme = ($rsi_value <= 20 || $rsi_value >= 80) ? 'EXTREME' : 'NORMAL';
            TradePress_Directive_Logger::log("D17 RSI | {$symbol} | RSI={$rsi_value} Condition={$condition} Extreme={$extreme} | Base=50 Adjustments=" . ($base_score - 50) . " | Final_Score={$final_score}");
        }
        
        return $final_score;
    }
    
    public function get_max_score($config = array()) {
        $extreme_bonus = $config['extreme_bonus'] ?? 25;
        return round(50 + 30 + $extreme_bonus, 1); // Max for extreme oversold
    }
    
    public function get_explanation($config = array()) {
        $period = $config['period'] ?? 14;
        $oversold = $config['oversold'] ?? 30;
        $overbought = $config['overbought'] ?? 70;
        $extreme_bonus = $config['extreme_bonus'] ?? 25;
        
        return "Relative Strength Index (RSI) Directive (D17)\n\n" .
               "This directive analyzes RSI momentum to identify overbought/oversold conditions.\n\n" .
               "Configuration:\n" .
               "- RSI Period: {$period} periods\n" .
               "- Oversold Threshold: {$oversold}\n" .
               "- Overbought Threshold: {$overbought}\n" .
               "- Extreme Bonus: +{$extreme_bonus} points\n\n" .
               "Scoring Logic:\n" .
               "1. Start with base score of 50 (neutral)\n" .
               "2. RSI ≤ {$oversold}: +30 points (oversold)\n" .
               "3. RSI ≥ {$overbought}: -20 points (overbought)\n" .
               "4. RSI ≤ 20: +{$extreme_bonus} additional points\n" .
               "5. RSI ≥ 80: -{$extreme_bonus} additional points\n" .
               "6. Neutral zone: slight adjustment based on position\n\n" .
               "RSI Calculation:\n" .
               "RSI = 100 - (100 / (1 + RS))\n" .
               "Where RS = Average Gain / Average Loss over {$period} periods\n\n" .
               "Max Score: " . $this->get_max_score($config) . " points\n\n" .
               "Data Freshness Requirements:\n" .
               "- RSI Data: 30 minutes\n" .
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
                'D17 RSI',
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
     * Get RSI data with caching
     */
    private function get_rsi_data($symbol, $config = array()) {
        TradePress_Developer_Flow_Logger::log_action('CACHE_CHECK_START', 'Checking for cached RSI data');
        
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        $parameters = array(
            'symbol' => $symbol,
            'time_period' => $config['period'] ?? 14,
            'interval' => $config['interval'] ?? 'daily'
        );
        
        TradePress_Developer_Flow_Logger::log_action('CACHE_PARAMS', 'Cache parameters prepared', $parameters);
        
        // Check cache first (30 minutes)
        $cached_result = TradePress_Call_Register::get_cached_result('rsi', 'technical_indicators', $parameters, 30);
        
        if ($cached_result !== false) {
            TradePress_Developer_Flow_Logger::log_cache('CHECK', 'rsi_technical_indicators', 'HIT', array(
                'cached_value' => $cached_result,
                'cache_age' => 'within 30 minutes'
            ));
            return $cached_result;
        }
        
        TradePress_Developer_Flow_Logger::log_cache('CHECK', 'rsi_technical_indicators', 'MISS', array(
            'reason' => 'No cached data or expired',
            'will_fetch' => 'fresh_data'
        ));
        
        // Fetch fresh data
        TradePress_Developer_Flow_Logger::log_action('API_FETCH_START', 'Starting fresh API data fetch');
        $fresh_data = $this->fetch_fresh_rsi_data($symbol, $parameters);
        
        if (!is_wp_error($fresh_data) && $fresh_data !== null) {
            TradePress_Developer_Flow_Logger::log_cache('STORE', 'rsi_technical_indicators', 'SUCCESS', array(
                'fresh_value' => $fresh_data,
                'cache_duration' => '30 minutes'
            ));
            // Cache the result for 30 minutes
            TradePress_Call_Register::cache_result('rsi', 'technical_indicators', $parameters, $fresh_data, 30);
        } else {
            TradePress_Developer_Flow_Logger::log_cache('STORE', 'rsi_technical_indicators', 'FAILED', array(
                'error' => is_wp_error($fresh_data) ? $fresh_data->get_error_message() : 'null_data'
            ));
        }
        
        return $fresh_data;
    }
    
    /**
     * Fetch fresh RSI data from API
     */
    public function fetch_fresh_rsi_data($symbol, $params) {
        TradePress_Developer_Flow_Logger::log_action('API_FACTORY_START', 'Creating API instance');
        
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        $api = TradePress_API_Factory::create_from_settings(null, 'paper', 'technical_indicators');
        
        if (is_wp_error($api)) {
            TradePress_Developer_Flow_Logger::log_api('factory', 'create_error', 'FAILED', array(
                'error' => $api->get_error_message()
            ));
            return $api;
        }
        
        $provider_id = $api->get_provider_id();
        TradePress_Developer_Flow_Logger::log_decision('API_PROVIDER_SELECTED', $provider_id, 'Automatic selection based on rate limits');
        
        // Alpha Vantage only for technical indicators
        TradePress_Developer_Flow_Logger::log_action('API_CALL_ALPHAVANTAGE', 'Using Alpha Vantage RSI endpoint', array(
            'symbol' => $symbol,
            'interval' => $params['interval'],
            'time_period' => $params['time_period'],
            'endpoint' => 'function=RSI'
        ));
        
        $rsi_response = $api->make_request('RSI', array(
            'symbol' => $symbol,
            'interval' => $params['interval'],
            'time_period' => $params['time_period'],
            'series_type' => 'close'
        ));
        
        if (is_wp_error($rsi_response)) {
            TradePress_Developer_Flow_Logger::log_api($provider_id, 'rsi_request', 'FAILED', array(
                'error' => $rsi_response->get_error_message()
            ));
            
            // No fallback needed - Alpha Vantage is primary for technical indicators
            
            if (is_wp_error($rsi_response)) {
                return $rsi_response;
            }
        }
        
        TradePress_Developer_Flow_Logger::log_api($provider_id, 'rsi_request', 'SUCCESS', array(
            'response_type' => gettype($rsi_response),
            'has_data' => !empty($rsi_response)
        ));
        
        // Use standardized data adapter
        TradePress_Developer_Flow_Logger::log_action('DATA_STANDARDIZATION', 'Converting provider format to standard format');
        
        if (!class_exists('TradePress_API_Data_Adapter')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-data-adapter.php';
        }
        
        $standardized_rsi = TradePress_API_Data_Adapter::standardize_rsi_data($rsi_response, $provider_id);
        
        if (is_wp_error($standardized_rsi)) {
            TradePress_Developer_Flow_Logger::log_action('STANDARDIZATION_FAILED', 'Data adapter failed', array(
                'error' => $standardized_rsi->get_error_message(),
                'provider' => $provider_id
            ));
            return $standardized_rsi;
        }
        
        TradePress_Developer_Flow_Logger::log_action('STANDARDIZATION_SUCCESS', 'Data converted successfully', array(
            'standardized_value' => $standardized_rsi,
            'provider' => $provider_id
        ));
        
        // Track successful API call
        if (!class_exists('TradePress_API_Usage_Tracker')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
        }
        TradePress_API_Usage_Tracker::track_call($provider_id, 'RSI', true);
        
        // Mark Finnhub as problematic if it failed
        if ($provider_id === 'alphavantage') {
            TradePress_API_Usage_Tracker::mark_rate_limited('finnhub', strtotime('+1 hour'));
        }
        
        TradePress_Developer_Flow_Logger::log_action('USAGE_TRACKED', 'API call tracked for rate limiting', array(
            'provider' => $provider_id,
            'endpoint' => 'RSI',
            'success' => true
        ));
        
        return $standardized_rsi;
    }
}