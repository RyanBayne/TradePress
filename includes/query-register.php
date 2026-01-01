<?php
/**
 * TradePress Recent Call Register
 * 
 * Time-based transient system for tracking and preventing duplicate API calls.
 * Uses hourly transient rotation to automatically limit storage and provide cleanup.
 * 
 * @package TradePress/Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Call_Register {
    
    /**
     * Generate unique serial number for API call
     * 
     * @param string $platform API platform name (alphavantage, finnhub, etc.)
     * @param string $method API method name (get_quote, get_historical, etc.)
     * @param array $parameters Call parameters
     * @return string Unique serial identifier
     */
    public static function generate_serial($platform, $method, $parameters = array()) {
        // Sort parameters for consistent serialization
        ksort($parameters);
        
        // Create unique identifier including platform for platform-specific responses
        $serial_string = $platform . '|' . $method . '|' . serialize($parameters);
        
        return md5($serial_string);
    }
    
    /**
     * Get current hour-based transient key
     * 
     * @return string Transient key for current hour
     */
    private static function get_current_transient_key() {
        // Generate transient key based on current hour
        // Format: tradepress_call_register_YYYYMMDDHH
        // This ensures automatic hourly rotation
        return 'tradepress_call_register_' . date('YmdH');
    }
    
    /**
     * Check if call was recently executed
     * 
     * @param string $serial Call serial number
     * @param int $max_age_minutes Maximum age in minutes to consider recent
     * @return array Call status with timestamp and age
     */
    public static function check_recent_call($serial, $max_age_minutes = 60) {
        // Check current hour transient
        $current_key = self::get_current_transient_key();
        $current_register = get_transient($current_key);
        
        if ($current_register && isset($current_register[$serial])) {
            $call_data = $current_register[$serial];
            $age_minutes = (time() - $call_data['timestamp']) / 60;
            
            if ($age_minutes <= $max_age_minutes) {
                return array(
                    'found' => true,
                    'timestamp' => $call_data['timestamp'],
                    'age_minutes' => $age_minutes,
                    'data' => $call_data['result'],
                    'source' => 'current_hour'
                );
            }
        }
        
        // Check previous hour transient
        $previous_key = 'tradepress_call_register_' . date('YmdH', strtotime('-1 hour'));
        $previous_register = get_transient($previous_key);
        
        if ($previous_register && isset($previous_register[$serial])) {
            $call_data = $previous_register[$serial];
            $age_minutes = (time() - $call_data['timestamp']) / 60;
            
            if ($age_minutes <= $max_age_minutes) {
                return array(
                    'found' => true,
                    'timestamp' => $call_data['timestamp'],
                    'age_minutes' => $age_minutes,
                    'data' => $call_data['result'],
                    'source' => 'previous_hour'
                );
            }
        }
        
        return array('found' => false);
    }
    
    /**
     * Register new call execution
     * 
     * @param string $serial Call serial number
     * @param mixed $result Call result data
     * @param int $lifespan_minutes How long to keep this call record
     * @return bool Success status
     */
    public static function register_call($serial, $result, $lifespan_minutes = 60) {
        // Get current hour register
        $current_key = self::get_current_transient_key();
        $register = get_transient($current_key);
        
        if (!$register) {
            $register = array();
        }
        
        // Store call data
        $register[$serial] = array(
            'timestamp' => time(),
            'result' => $result,
            'lifespan_minutes' => $lifespan_minutes
        );
        
        // Set transient to expire at end of next hour (ensures 2 hours max retention)
        $expiry = 7200; // 2 hours in seconds
        
        return set_transient($current_key, $register, $expiry);
    }
    
    /**
     * Get call result if available and fresh
     * 
     * @param string $platform API platform name
     * @param string $method API method name
     * @param array $parameters Call parameters
     * @param int $max_age_minutes Maximum age to consider fresh
     * @return mixed Call result or false if not found/stale
     */
    public static function get_cached_result($platform, $method, $parameters = array(), $max_age_minutes = 60) {
        $serial = self::generate_serial($platform, $method, $parameters);
        $call_status = self::check_recent_call($serial, $max_age_minutes);
        
        // Add developer notice for cache check
        if (class_exists('TradePress_Developer_Notices')) {
            $symbol = isset($parameters['symbol']) ? $parameters['symbol'] : 'N/A';
            TradePress_Developer_Notices::cache_check_notice($platform, $method, $symbol, $call_status, $parameters);
        }
        
        return $call_status['found'] ? $call_status['data'] : false;
    }
    
    /**
     * Store call result with automatic registration
     * 
     * @param string $platform API platform name
     * @param string $method API method name
     * @param array $parameters Call parameters
     * @param mixed $result Call result to cache
     * @param int $lifespan_minutes Cache lifespan
     * @return string Generated serial number
     */
    public static function cache_result($platform, $method, $parameters, $result, $lifespan_minutes = 60) {
        $serial = self::generate_serial($platform, $method, $parameters);
        self::register_call($serial, $result, $lifespan_minutes);
        
        return $serial;
    }
    
    /**
     * Clean up old transients (manual cleanup if needed)
     * 
     * @param int $hours_to_keep How many hours of transients to keep
     * @return int Number of transients cleaned
     */
    public static function cleanup_old_transients($hours_to_keep = 2) {
        // TODO: Remove transients older than specified hours
        // This is backup cleanup - transients should auto-expire
        return 0;
    }
    
    /**
     * Get register statistics
     * 
     * @return array Statistics about current call register
     */
    public static function get_statistics() {
        // TODO: Return stats like total calls, hit rate, current hour count
        return array(
            'current_hour_calls' => 0,
            'previous_hour_calls' => 0,
            'total_cached_results' => 0
        );
    }
    
    /**
     * Integration with object registry
     * 
     * @param string $registry_key Object registry key
     * @param string $platform API platform name
     * @param string $method API method name
     * @param array $parameters Parameters
     * @return mixed Cached result or false
     */
    public static function get_from_registry($registry_key, $platform, $method, $parameters = array()) {
        // TODO: Check object registry first, then call register
        // Integrate with TradePress_Object_Registry
        return false;
    }
}