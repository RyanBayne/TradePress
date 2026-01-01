<?php
/**
 * TradePress Earnings Calendar Model
 *
 * Handles fetching, storing, and retrieving earnings calendar data
 *
 * @package TradePress
 * @subpackage Models
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Earnings_Calendar {
    /**
     * Database table name
     */
    private static $table_name = 'tradepress_earnings_calendar';
    
    /**
     * Create the earnings calendar database table if it doesn't exist
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            symbol varchar(20) NOT NULL,
            company_name varchar(255) NOT NULL,
            report_date date NOT NULL,
            fiscal_quarter varchar(10) NOT NULL,
            eps_estimate decimal(10,2) NULL,
            reported_eps decimal(10,2) NULL,
            surprise_percent decimal(10,2) NULL,
            report_time varchar(5) NOT NULL,
            last_updated datetime NOT NULL,
            data_source varchar(50) NOT NULL,
            PRIMARY KEY (id),
            KEY symbol (symbol),
            KEY report_date (report_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get upcoming earnings for the specified number of days
     * 
     * @param int $days Number of days to look ahead
     * @param bool $force_refresh Whether to force data refresh from API
     * @return array Earnings data
     */
    public static function get_upcoming_earnings($days = 30, $force_refresh = false) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        
        // First check if we need to refresh the data
        $last_update = self::get_last_update_time();
        $data_stale = $last_update ? (time() - $last_update > 86400) : true; // Stale after 24 hours
        
        if ($force_refresh || $data_stale) {
            self::refresh_earnings_data();
        }
        
        // Get data from the database
        $today = current_time('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+$days days"));
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE report_date BETWEEN %s AND %s
            ORDER BY report_date ASC, symbol ASC",
            $today,
            $end_date
        );
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // If no data is found, return empty array
        if (empty($results)) {
            return array();
        }
        
        return $results;
    }
    
    /**
     * Refresh earnings data from API
     * 
     * @return bool Success or failure
     */
    public static function refresh_earnings_data() {
        // Use API adapter directly
        require_once TRADEPRESS_PLUGIN_DIR . 'api/api-adapter.php';
        
        // Try Alpha Vantage earnings calendar
        $api_key = self::get_api_key('alphavantage');
        
        if (!$api_key) {
            if (function_exists('tradepress_trace_log')) {
                tradepress_trace_log('No Alpha Vantage API key found');
            }
            return false;
        }
        
        if (function_exists('tradepress_trace_log')) {
            tradepress_trace_log('Making Alpha Vantage earnings API call');
        }
        
        $response = TradePress_API_Adapter::get(
            'alphavantage',
            'https://www.alphavantage.co/query',
            array(
                'function' => 'EARNINGS_CALENDAR',
                'horizon' => '3month',
                'apikey' => $api_key
            )
        );
        
        if (is_wp_error($response)) {
            if (function_exists('tradepress_trace_log')) {
                tradepress_trace_log('Alpha Vantage API error: ' . $response->get_error_message());
            }
            return false;
        }
        
        if (empty($response)) {
            if (function_exists('tradepress_trace_log')) {
                tradepress_trace_log('Alpha Vantage API returned empty response');
            }
            return false;
        }
        
        if (function_exists('tradepress_trace_log')) {
            tradepress_trace_log('Alpha Vantage API response received', array('length' => strlen($response)));
        }
        
        $earnings_data = self::parse_alphavantage_earnings($response);
        
        if (!empty($earnings_data)) {
            if (function_exists('tradepress_trace_log')) {
                tradepress_trace_log('Parsed earnings data', array('count' => count($earnings_data)));
            }
            return self::save_earnings_data($earnings_data, 'alphavantage');
        }
        
        if (function_exists('tradepress_trace_log')) {
            tradepress_trace_log('No earnings data parsed from API response');
        }
        
        return false;
    }
    
    /**
     * Parse Alpha Vantage earnings response
     * 
     * @param string $response CSV response from Alpha Vantage
     * @return array Parsed earnings data
     */
    private static function parse_alphavantage_earnings($response) {
        if (empty($response) || !is_string($response)) {
            return array();
        }
        
        $earnings = array();
        $rows = explode("\n", $response);
        
        // Remove header row
        array_shift($rows);
        
        foreach ($rows as $row) {
            if (empty(trim($row))) continue;
            
            $data = str_getcsv($row);
            
            // Alpha Vantage CSV format: symbol,name,report_date,fiscal_date_ending,estimate,currency,report_time
            if (count($data) >= 7) {
                $earnings[] = array(
                    'symbol' => $data[0],
                    'company_name' => $data[1],
                    'report_date' => $data[2],
                    'fiscal_quarter' => $data[3],
                    'eps_estimate' => !empty($data[4]) ? (float)$data[4] : null,
                    'reported_eps' => null, // Not provided in upcoming earnings
                    'surprise_percent' => null, // Not provided in upcoming earnings
                    'report_time' => $data[6] // BMO (before market open) or AMC (after market close)
                );
            }
        }
        
        return $earnings;
    }
    
    /**
     * Save earnings data to the database
     * 
     * @param array $earnings_data Earnings data to save
     * @param string $data_source Source of the data (API name)
     * @return bool Success or failure
     */
    private static function save_earnings_data($earnings_data, $data_source) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        
        // Clear existing upcoming data first
        $today = current_time('Y-m-d');
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE report_date >= %s",
            $today
        ));
        
        // Insert new data
        $now = current_time('mysql');
        $success = true;
        
        foreach ($earnings_data as $item) {
            $result = $wpdb->insert(
                $table_name,
                array(
                    'symbol' => $item['symbol'],
                    'company_name' => $item['company_name'],
                    'report_date' => $item['report_date'],
                    'fiscal_quarter' => $item['fiscal_quarter'],
                    'eps_estimate' => $item['eps_estimate'],
                    'reported_eps' => $item['reported_eps'],
                    'surprise_percent' => $item['surprise_percent'],
                    'report_time' => $item['report_time'],
                    'last_updated' => $now,
                    'data_source' => $data_source
                )
            );
            
            if ($result === false) {
                $success = false;
            }
        }
        
        // Store the last update time
        update_option('tradepress_earnings_calendar_last_update', time());
        
        return $success;
    }
    
    /**
     * Get the last update time for earnings data
     * 
     * @return int|bool Timestamp of last update or false if never updated
     */
    public static function get_last_update_time() {
        return get_option('tradepress_earnings_calendar_last_update', false);
    }
    
    /**
     * Get the name of the data source used for the earnings data
     * 
     * @return string Data source name
     */
    public static function get_data_source_name() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_name;
        
        $data_source = $wpdb->get_var("SELECT data_source FROM $table_name ORDER BY last_updated DESC LIMIT 1");
        
        if ($data_source) {
            return ucfirst($data_source);
        }
        
        return 'Not Available';
    }
    
    /**
     * Get API key for the specified provider
     * 
     * @param string $provider Provider name
     * @return string|bool API key or false if not found
     */
    private static function get_api_key($provider) {
        $api_settings = get_option('tradepress_api_settings', array());
        $key_field = $provider . '_api_key';
        
        return isset($api_settings[$key_field]) ? $api_settings[$key_field] : false;
    }
}

// Create the table when the file is included
add_action('plugins_loaded', array('TradePress_Earnings_Calendar', 'create_table'));
