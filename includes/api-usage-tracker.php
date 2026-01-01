<?php
/**
 * API Usage Tracker and Fallback System
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_API_Usage_Tracker {
    
    /**
     * Track API call usage
     */
    public static function track_call($provider_id, $endpoint, $success = true) {
        $today = date('Y-m-d');
        $usage_key = "tradepress_api_usage_{$provider_id}_{$today}";
        
        $usage = get_option($usage_key, array(
            'total_calls' => 0,
            'successful_calls' => 0,
            'failed_calls' => 0,
            'endpoints' => array(),
            'last_call' => null,
            'rate_limited' => false
        ));
        
        $usage['total_calls']++;
        if ($success) {
            $usage['successful_calls']++;
        } else {
            $usage['failed_calls']++;
        }
        
        if (!isset($usage['endpoints'][$endpoint])) {
            $usage['endpoints'][$endpoint] = 0;
        }
        $usage['endpoints'][$endpoint]++;
        
        $usage['last_call'] = current_time('mysql');
        
        update_option($usage_key, $usage);
    }
    
    /**
     * Mark API as rate limited
     */
    public static function mark_rate_limited($provider_id, $reset_time = null) {
        $today = date('Y-m-d');
        $usage_key = "tradepress_api_usage_{$provider_id}_{$today}";
        
        $usage = get_option($usage_key, array());
        $usage['rate_limited'] = true;
        $usage['rate_limit_time'] = current_time('mysql');
        if ($reset_time) {
            $usage['rate_limit_reset'] = $reset_time;
        }
        
        update_option($usage_key, $usage);
        
        // Developer notice
        if (get_option('tradepress_developer_mode') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/developer-notices.php';
            TradePress_Developer_Notices::api_call_notice(
                $provider_id, 
                'rate_limit_detected', 
                'N/A', 
                array('message' => 'API rate limit detected, switching to fallback')
            );
        }
    }
    
    /**
     * Check if API is likely rate limited with cooling period
     */
    public static function is_likely_rate_limited($provider_id) {
        $today = date('Y-m-d');
        $usage_key = "tradepress_api_usage_{$provider_id}_{$today}";
        $usage = get_option($usage_key, array(
            'total_calls' => 0,
            'rate_limited' => false,
            'rate_limit_time' => null
        ));
        
        // Check if explicitly marked as rate limited with cooling period
        if (!empty($usage['rate_limited']) && !empty($usage['rate_limit_time'])) {
            $limit_time = strtotime($usage['rate_limit_time']);
            $cooling_period = self::get_cooling_period($provider_id);
            
            // If still in cooling period, remain rate limited
            if (time() - $limit_time < $cooling_period) {
                return true;
            }
            
            // Cooling period expired - clear rate limit flag
            $usage['rate_limited'] = false;
            $usage['rate_limit_time'] = null;
            update_option($usage_key, $usage);
        }
        
        // Check usage patterns for Alpha Vantage (25 calls/day limit)
        if ($provider_id === 'alphavantage' && $usage['total_calls'] >= 23) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get best available API for data type with dynamic priority
     */
    public static function get_best_api_for_data($data_type) {
        $base_priority = array(
            'quote' => array('alphavantage', 'finnhub', 'alpaca'),
            'technical_indicators' => array('alphavantage'), // Finnhub doesn't support technical indicators
            'news' => array('finnhub', 'alphavantage'),
            'fundamentals' => array('alphavantage', 'finnhub')
        );
        
        $providers = $base_priority[$data_type] ?? array('alphavantage');
        
        // Reorder providers: move rate-limited ones to end
        $available_providers = array();
        $rate_limited_providers = array();
        
        foreach ($providers as $provider_id) {
            // Check if enabled
            if (get_option("TradePress_switch_{$provider_id}_api_services") !== 'yes') {
                continue;
            }
            
            if (self::is_likely_rate_limited($provider_id)) {
                $rate_limited_providers[] = $provider_id;
            } else {
                $available_providers[] = $provider_id;
            }
        }
        
        // Try available providers first, then rate-limited ones
        $ordered_providers = array_merge($available_providers, $rate_limited_providers);
        
        foreach ($ordered_providers as $provider_id) {
            // Skip if rate limited (unless it's the last option)
            if (self::is_likely_rate_limited($provider_id) && count($ordered_providers) > 1) {
                if (get_option('tradepress_developer_mode') === 'yes') {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/developer-notices.php';
                    TradePress_Developer_Notices::api_call_notice(
                        $provider_id, 
                        'skipped_rate_limited', 
                        $data_type, 
                        array('message' => "Skipping {$provider_id} - in cooling period")
                    );
                }
                continue;
            }
            
            // Try to create API instance
            $api = TradePress_API_Factory::create_from_settings($provider_id);
            if (!is_wp_error($api)) {
                if (get_option('tradepress_developer_mode') === 'yes') {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/developer-notices.php';
                    TradePress_Developer_Notices::api_call_notice(
                        $provider_id, 
                        'selected_for_fallback', 
                        $data_type, 
                        array('message' => "Selected {$provider_id} for {$data_type}")
                    );
                }
                return $api;
            }
        }
        
        return new WP_Error('no_available_api', "No available API for {$data_type}");
    }
    
    /**
     * Get cooling period for rate limited API
     */
    private static function get_cooling_period($provider_id) {
        $cooling_periods = array(
            'alphavantage' => 3600, // 1 hour
            'finnhub' => 60,        // 1 minute
            'alpaca' => 300         // 5 minutes
        );
        
        return $cooling_periods[$provider_id] ?? 1800; // Default 30 minutes
    }
    
    /**
     * Get usage statistics
     */
    public static function get_usage_stats($provider_id, $days = 7) {
        $stats = array();
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $usage_key = "tradepress_api_usage_{$provider_id}_{$date}";
            $usage = get_option($usage_key, array(
                'total_calls' => 0,
                'successful_calls' => 0,
                'failed_calls' => 0,
                'endpoints' => array(),
                'last_call' => null,
                'rate_limited' => false
            ));
            
            $stats[$date] = $usage;
        }
        
        return $stats;
    }
}