<?php
/**
 * TradePress Technical Indicator Cache Manager
 * 
 * Manages caching of technical indicators with individual expiry times
 * 
 * @package TradePress/Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Technical_Indicator_Cache {
    
    /**
     * Default cache durations for different indicators (in minutes)
     */
    private static $default_cache_durations = array(
        'rsi' => 30,
        'cci' => 30,
        'macd' => 30,
        'adx' => 30,
        'bollinger_bands' => 30,
        'ema' => 30,
        'sma' => 30,
        'volume_analysis' => 10
    );
    
    /**
     * Get cached technical indicator data
     * 
     * @param string $symbol Symbol ticker
     * @param string $indicator Indicator name (rsi, cci, macd, etc.)
     * @param array $parameters Indicator parameters
     * @param int $max_age_minutes Maximum age in minutes
     * @return mixed Cached data or false if not found/stale
     */
    public static function get_cached_indicator($symbol, $indicator, $parameters = array(), $max_age_minutes = null) {
        if ($max_age_minutes === null) {
            $max_age_minutes = self::$default_cache_durations[$indicator] ?? 30;
        }
        
        // Generate cache key
        $cache_key = self::generate_cache_key($symbol, $indicator, $parameters);
        
        // Check Call Register for cached data
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        $cached_result = TradePress_Call_Register::get_cached_result(
            'alphavantage', 
            $indicator, 
            array_merge(array('symbol' => $symbol), $parameters), 
            $max_age_minutes
        );
        
        return $cached_result;
    }
    
    /**
     * Cache technical indicator data
     * 
     * @param string $symbol Symbol ticker
     * @param string $indicator Indicator name
     * @param array $parameters Indicator parameters
     * @param mixed $data Data to cache
     * @param int $cache_minutes Cache duration in minutes
     * @return bool Success status
     */
    public static function cache_indicator($symbol, $indicator, $parameters, $data, $cache_minutes = null) {
        if ($cache_minutes === null) {
            $cache_minutes = self::$default_cache_durations[$indicator] ?? 30;
        }
        
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        // Cache using Call Register
        TradePress_Call_Register::cache_result(
            'alphavantage',
            $indicator,
            array_merge(array('symbol' => $symbol), $parameters),
            $data,
            $cache_minutes
        );
        
        return true;
    }
    
    /**
     * Generate cache key for indicator
     * 
     * @param string $symbol Symbol ticker
     * @param string $indicator Indicator name
     * @param array $parameters Parameters
     * @return string Cache key
     */
    private static function generate_cache_key($symbol, $indicator, $parameters = array()) {
        return TradePress_Call_Register::generate_serial(
            'alphavantage',
            $indicator,
            array_merge(array('symbol' => $symbol), $parameters)
        );
    }
    
    /**
     * Check if indicator data is fresh
     * 
     * @param string $symbol Symbol ticker
     * @param string $indicator Indicator name
     * @param array $parameters Parameters
     * @param int $max_age_minutes Maximum age
     * @return array Status information
     */
    public static function check_indicator_freshness($symbol, $indicator, $parameters = array(), $max_age_minutes = null) {
        if ($max_age_minutes === null) {
            $max_age_minutes = self::$default_cache_durations[$indicator] ?? 30;
        }
        
        $serial = self::generate_cache_key($symbol, $indicator, $parameters);
        
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        return TradePress_Call_Register::check_recent_call($serial, $max_age_minutes);
    }
    
    /**
     * Get or fetch indicator data with caching
     * 
     * @param string $symbol Symbol ticker
     * @param string $indicator Indicator name
     * @param array $parameters Parameters
     * @param callable $fetch_callback Callback to fetch fresh data
     * @return mixed Indicator data
     */
    public static function get_or_fetch_indicator($symbol, $indicator, $parameters, $fetch_callback) {
        // Try cache first
        $cached_data = self::get_cached_indicator($symbol, $indicator, $parameters);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // Fetch fresh data
        $fresh_data = call_user_func($fetch_callback, $symbol, $parameters);
        
        if (!is_wp_error($fresh_data) && $fresh_data !== false) {
            // Cache the fresh data
            self::cache_indicator($symbol, $indicator, $parameters, $fresh_data);
        }
        
        return $fresh_data;
    }
    
    /**
     * Clear cache for specific indicator
     * 
     * @param string $symbol Symbol ticker
     * @param string $indicator Indicator name
     * @param array $parameters Parameters
     * @return bool Success status
     */
    public static function clear_indicator_cache($symbol, $indicator, $parameters = array()) {
        // Note: Call Register uses transients which auto-expire
        // This is a placeholder for manual cache clearing if needed
        return true;
    }
    
    /**
     * Get indicator data with automatic caching
     * 
     * @param string $indicator Indicator type
     * @param string $symbol Symbol ticker
     * @param array $parameters Parameters
     * @param int $cache_seconds Cache duration in seconds
     * @return mixed Indicator data
     */
    public function get_indicator_data($indicator, $symbol, $parameters = array(), $cache_seconds = 1800) {
        $cache_minutes = intval($cache_seconds / 60);
        
        // Check cache first
        $cached_data = self::get_cached_indicator($symbol, $indicator, $parameters, $cache_minutes);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // Fetch fresh data based on indicator type
        switch ($indicator) {
            case 'cci':
                return $this->fetch_cci_data($symbol, $parameters, $cache_minutes);
            case 'rsi':
                return $this->fetch_rsi_data($symbol, $parameters, $cache_minutes);
            case 'macd':
                return $this->fetch_macd_data($symbol, $parameters, $cache_minutes);
            default:
                return new WP_Error('unsupported_indicator', 'Indicator not supported: ' . $indicator);
        }
    }
    
    /**
     * Fetch CCI data from API
     * 
     * @param string $symbol Symbol ticker
     * @param array $parameters Parameters
     * @param int $cache_minutes Cache duration
     * @return mixed CCI data
     */
    private function fetch_cci_data($symbol, $parameters, $cache_minutes) {
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        $api = TradePress_API_Factory::create_from_settings('alphavantage');
        
        if (is_wp_error($api)) {
            return $api;
        }
        
        $cci_response = $api->make_request('CCI', array(
            'symbol' => $symbol,
            'interval' => $parameters['interval'] ?? 'daily',
            'time_period' => $parameters['time_period'] ?? 14
        ));
        
        if (is_wp_error($cci_response)) {
            return $cci_response;
        }
        
        // Extract the most recent CCI value
        $technical_analysis = $cci_response['Technical Analysis: CCI'] ?? array();
        
        if (empty($technical_analysis)) {
            return new WP_Error('no_cci_data', 'No CCI data in API response');
        }
        
        $latest_date = array_key_first($technical_analysis);
        $latest_cci = $technical_analysis[$latest_date]['CCI'] ?? null;
        
        $cci_value = $latest_cci ? (float) $latest_cci : null;
        
        // Cache the result
        if ($cci_value !== null) {
            self::cache_indicator($symbol, 'cci', $parameters, $cci_value, $cache_minutes);
        }
        
        return $cci_value;
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public static function get_cache_stats() {
        // TODO: Implement cache statistics
        return array(
            'total_indicators_cached' => 0,
            'cache_hit_rate' => 0,
            'average_age_minutes' => 0
        );
    }
}