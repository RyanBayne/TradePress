<?php
/**
 * TradePress Data Query System
 *
 * Centralized data access for directive testing and scoring algorithms
 *
 * @package TradePress/Data
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress_Data_Query Class
 */
class TradePress_Data_Query {

    /**
     * Data freshness thresholds (in seconds)
     */
    const DATA_FRESHNESS = array(
        'price_data' => 300,      // 5 minutes
        'earnings' => 86400,      // 24 hours
        'news' => 3600,           // 1 hour
        'fundamentals' => 604800, // 1 week
        'technical' => 900        // 15 minutes
    );

    /**
     * Get symbol data for directive testing
     *
     * @param string $symbol Symbol ticker
     * @param array $data_types Required data types
     * @param bool $force_fresh Force fresh import
     * @return array Symbol data
     */
    public static function get_symbol_data($symbol, $data_types = array(), $force_fresh = false) {
        $data = array();
        
        foreach ($data_types as $type) {
            $data[$type] = self::get_data_by_type($symbol, $type, $force_fresh);
        }
        
        return $data;
    }

    /**
     * Get data by type with freshness checking
     *
     * @param string $symbol Symbol ticker
     * @param string $type Data type
     * @param bool $force_fresh Force fresh import
     * @return mixed Data or null if unavailable
     */
    private static function get_data_by_type($symbol, $type, $force_fresh = false) {
        if (!$force_fresh && self::is_data_fresh($symbol, $type)) {
            return self::get_cached_data($symbol, $type);
        }
        
        if ($force_fresh) {
            self::trigger_data_import($symbol, $type);
        }
        
        return self::get_cached_data($symbol, $type);
    }

    /**
     * Check if data is fresh enough
     *
     * @param string $symbol Symbol ticker
     * @param string $type Data type
     * @return bool True if data is fresh
     */
    private static function is_data_fresh($symbol, $type) {
        global $wpdb;
        
        $threshold = isset(self::DATA_FRESHNESS[$type]) ? self::DATA_FRESHNESS[$type] : 3600;
        $cutoff = current_time('timestamp') - $threshold;
        
        $table = self::get_table_for_type($type);
        if (!$table) return false;
        
        $last_update = $wpdb->get_var($wpdb->prepare(
            "SELECT UNIX_TIMESTAMP(updated_at) FROM {$table} WHERE symbol = %s ORDER BY updated_at DESC LIMIT 1",
            $symbol
        ));
        
        return $last_update && $last_update > $cutoff;
    }

    /**
     * Get cached data from database
     *
     * @param string $symbol Symbol ticker
     * @param string $type Data type
     * @return mixed Cached data
     */
    private static function get_cached_data($symbol, $type) {
        global $wpdb;
        
        switch ($type) {
            case 'price_data':
                return $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}tradepress_price_history WHERE symbol = %s ORDER BY date DESC LIMIT 1",
                    $symbol
                ), ARRAY_A);
                
            case 'earnings':
                return $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}tradepress_earnings_calendar WHERE symbol = %s AND date >= CURDATE() ORDER BY date ASC LIMIT 5",
                    $symbol
                ), ARRAY_A);
                
            case 'fundamentals':
                return $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}tradepress_symbol_meta WHERE symbol = %s AND meta_type = 'fundamentals'",
                    $symbol
                ), ARRAY_A);
                
            default:
                return null;
        }
    }

    /**
     * Trigger data import for specific symbol and type
     *
     * @param string $symbol Symbol ticker
     * @param string $type Data type
     */
    private static function trigger_data_import($symbol, $type) {
        // Add to import queue for background processing
        do_action('tradepress_queue_data_import', $symbol, $type);
    }

    /**
     * Get database table for data type
     *
     * @param string $type Data type
     * @return string|null Table name
     */
    private static function get_table_for_type($type) {
        global $wpdb;
        
        $tables = array(
            'price_data' => $wpdb->prefix . 'tradepress_price_history',
            'earnings' => $wpdb->prefix . 'tradepress_earnings_calendar',
            'fundamentals' => $wpdb->prefix . 'tradepress_symbol_meta',
            'news' => $wpdb->prefix . 'tradepress_news',
            'technical' => $wpdb->prefix . 'tradepress_price_history'
        );
        
        return isset($tables[$type]) ? $tables[$type] : null;
    }

    /**
     * Get data freshness status for UI display
     *
     * @param string $symbol Symbol ticker
     * @param string $type Data type
     * @return array Status information
     */
    public static function get_data_status($symbol, $type) {
        $is_fresh = self::is_data_fresh($symbol, $type);
        $last_update = self::get_last_update_time($symbol, $type);
        
        return array(
            'is_fresh' => $is_fresh,
            'last_update' => $last_update,
            'age_minutes' => $last_update ? round((current_time('timestamp') - $last_update) / 60) : null,
            'status' => $is_fresh ? 'fresh' : 'stale'
        );
    }

    /**
     * Get last update time for data type
     *
     * @param string $symbol Symbol ticker
     * @param string $type Data type
     * @return int|null Unix timestamp
     */
    private static function get_last_update_time($symbol, $type) {
        global $wpdb;
        
        $table = self::get_table_for_type($type);
        if (!$table) return null;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT UNIX_TIMESTAMP(updated_at) FROM {$table} WHERE symbol = %s ORDER BY updated_at DESC LIMIT 1",
            $symbol
        ));
    }
}