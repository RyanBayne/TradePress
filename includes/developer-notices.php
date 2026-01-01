<?php
/**
 * TradePress Developer Notices Interface
 * 
 * Centralized interface for developer mode notifications.
 * Groups API calls and database operations for better visibility.
 * 
 * @package TradePress/Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Developer_Notices {
    
    /**
     * Check if developer mode is enabled
     * 
     * @return bool True if developer mode is on
     */
    public static function is_developer_mode() {
        $mode = get_option('tradepress_developer_mode', 'no');
        error_log('[' . date('Y-m-d H:i:s') . '] Developer mode check: option=' . $mode . ', result=' . ($mode === 'yes' ? 'true' : 'false'), 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
        return $mode === 'yes';
    }
    
    /**
     * Add API call notice in developer mode
     * 
     * @param string $provider API provider name
     * @param string $endpoint Endpoint called
     * @param string $symbol Symbol requested
     * @param mixed $result API result
     */
    public static function api_call_notice($provider, $endpoint, $symbol, $result) {
        error_log('[' . date('Y-m-d H:i:s') . '] API notice called: ' . $provider . ' -> ' . $endpoint . '(' . $symbol . ')', 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
        
        if (!self::is_developer_mode() || !is_admin()) {
            error_log('[' . date('Y-m-d H:i:s') . '] API notice skipped: dev_mode=' . (self::is_developer_mode() ? 'true' : 'false') . ', is_admin=' . (is_admin() ? 'true' : 'false'), 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
            return;
        }
        
        $status = is_wp_error($result) ? 'error' : 'success';
        $rate_limit_info = '';
        
        // Add rate limit information for Alpha Vantage
        if ($provider === 'alphavantage') {
            $rate_limit_info = self::get_alphavantage_rate_limit_info();
        }
        
        $message = sprintf(
            '<strong>API Call:</strong> %s → %s(%s) - %s%s',
            $provider,
            $endpoint,
            $symbol,
            $status === 'error' ? $result->get_error_message() : 'Success',
            $rate_limit_info
        );
        
        error_log('[' . date('Y-m-d H:i:s') . '] Adding API notice: ' . $message, 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
        add_settings_error('tradepress_directives', 'api_call_' . uniqid(), $message, 'notice-info');
    }
    
    /**
     * Get Alpha Vantage rate limit information
     * 
     * @return string Rate limit info string
     */
    private static function get_alphavantage_rate_limit_info() {
        global $wpdb;
        
        // Get today's call count
        $daily_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}tradepress_calls 
            WHERE service = %s 
            AND timestamp > %s",
            'alphavantage',
            date('Y-m-d 00:00:00')
        ));
        
        $daily_count = $daily_count ?: 0;
        $daily_limit = 25; // Free tier limit
        
        // Calculate remaining calls
        $remaining = $daily_limit - $daily_count;
        
        // Determine warning level
        if ($remaining <= 0) {
            $warning = ' <span style="color: #dc3232; font-weight: bold;">⚠️ LIMIT EXCEEDED</span>';
        } elseif ($remaining <= 5) {
            $warning = ' <span style="color: #dc3232; font-weight: bold;">⚠️ ' . $remaining . ' calls left</span>';
        } elseif ($remaining <= 10) {
            $warning = ' <span style="color: #ffb900;">⚠️ ' . $remaining . ' calls left</span>';
        } else {
            $warning = ' <span style="color: #46b450;">✓ ' . $remaining . ' calls left</span>';
        }
        
        return ' (' . $daily_count . '/' . $daily_limit . ' today)' . $warning;
    }
    
    /**
     * Add database operation notice in developer mode
     * 
     * @param string $operation Operation type (INSERT, SELECT, UPDATE)
     * @param string $table Table name
     * @param array $data Data involved (truncated if large)
     * @param mixed $result Operation result
     */
    public static function database_notice($operation, $table, $data = array(), $result = null) {
        error_log('[' . date('Y-m-d H:i:s') . '] DB notice called: ' . $operation . ' on ' . $table, 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
        
        if (!self::is_developer_mode() || !is_admin()) {
            error_log('[' . date('Y-m-d H:i:s') . '] DB notice skipped: dev_mode=' . (self::is_developer_mode() ? 'true' : 'false') . ', is_admin=' . (is_admin() ? 'true' : 'false'), 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
            return;
        }
        
        $data_summary = self::truncate_data($data);
        $result_text = $result === false ? 'Failed' : 'Success';
        
        $message = sprintf(
            '<strong>DB %s:</strong> %s - %s %s',
            $operation,
            $table,
            $result_text,
            $data_summary ? "({$data_summary})" : ''
        );
        
        error_log('[' . date('Y-m-d H:i:s') . '] Adding DB notice: ' . $message, 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
        add_settings_error('tradepress_directives', 'db_op_' . uniqid(), $message, 'notice-info');
    }
    
    /**
     * Truncate data for display
     * 
     * @param mixed $data Data to truncate
     * @return string Truncated data summary
     */
    private static function truncate_data($data) {
        if (empty($data)) {
            return '';
        }
        
        if (is_array($data)) {
            $keys = array_keys($data);
            if (count($keys) > 3) {
                $keys = array_slice($keys, 0, 3);
                return implode(', ', $keys) . '...';
            }
            return implode(', ', $keys);
        }
        
        if (is_string($data) && strlen($data) > 50) {
            return substr($data, 0, 47) . '...';
        }
        
        return (string) $data;
    }
    
    /**
     * Add data freshness check notice in developer mode
     * 
     * @param string $directive Directive name
     * @param string $symbol Symbol being checked
     * @param array $validation Validation results
     * @param array $requirements Freshness requirements
     */
    public static function data_freshness_notice($directive, $symbol, $validation, $requirements) {
        if (!self::is_developer_mode() || !is_admin()) {
            return;
        }
        
        $status_summary = array();
        foreach ($validation['data_status'] as $data_type => $status) {
            $age_text = $status['age_seconds'] ? round($status['age_seconds'] / 60, 1) . 'm' : 'N/A';
            $max_age_text = round($requirements[$data_type] / 60, 1) . 'm';
            $status_icon = $status['status'] === 'fresh' ? '✓' : ($status['status'] === 'stale' ? '⚠️' : '❌');
            $status_summary[] = "{$data_type}: {$status_icon} {$age_text}/{$max_age_text}";
        }
        
        $overall_status = $validation['overall_fresh'] ? 'Fresh' : 'Needs Update';
        $overall_icon = $validation['overall_fresh'] ? '✓' : '⚠️';
        
        $message = sprintf(
            '<strong>Data Freshness Check:</strong> %s(%s) - %s %s [%s]',
            $directive,
            $symbol,
            $overall_icon,
            $overall_status,
            implode(', ', $status_summary)
        );
        
        add_settings_error('tradepress_directives', 'freshness_' . uniqid(), $message, 'notice-info');
    }
    
    /**
     * Add cache check notice in developer mode
     * 
     * @param string $platform API platform name
     * @param string $method API method name
     * @param string $symbol Symbol being checked
     * @param array $cache_result Cache check result
     * @param array $parameters Parameters used for cache key
     */
    public static function cache_check_notice($platform, $method, $symbol, $cache_result, $parameters = array()) {
        if (!self::is_developer_mode() || !is_admin()) {
            return;
        }
        
        $serial = TradePress_Call_Register::generate_serial($platform, $method, $parameters);
        $serial_short = substr($serial, 0, 8);
        
        if ($cache_result['found']) {
            $age_text = round($cache_result['age_minutes'], 1) . 'm';
            $source_text = $cache_result['source'] === 'current_hour' ? 'current' : 'previous';
            $message = sprintf(
                '<strong>Cache Check:</strong> %s → %s(%s) - ✓ HIT [%s ago, %s hour] Serial: %s',
                $platform,
                $method,
                $symbol,
                $age_text,
                $source_text,
                $serial_short
            );
        } else {
            $message = sprintf(
                '<strong>Cache Check:</strong> %s → %s(%s) - ✗ MISS [will make API call] Serial: %s',
                $platform,
                $method,
                $symbol,
                $serial_short
            );
        }
        
        add_settings_error('tradepress_directives', 'cache_' . uniqid(), $message, 'notice-info');
    }
}