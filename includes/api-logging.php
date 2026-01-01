<?php
/**
 * TradePress API Logging
 *
 * Class for logging API activity, errors, and test results. This centralized logging system
 * tracks all API interactions across the TradePress plugin for debugging, monitoring,
 * and usage analysis.
 * 
 * DATABASE TABLES:
 * - tradepress_calls: Stores API call information including service, type, status, and outcomes
 * - tradepress_endpoints: Tracks endpoint usage statistics and metadata
 * - tradepress_errors: Stores detailed error information related to API calls
 * - tradepress_meta: Stores additional metadata related to API calls
 * 
 * USAGE:
 * 1. Log a new API call using TradePress_API_Logging::log_call()
 * 2. Track endpoint usage with TradePress_API_Logging::track_endpoint()
 * 3. Log errors with TradePress_API_Logging::log_error()
 * 
 * INTEGRATION NOTES:
 * - This class should be used by all API implementations in TradePress
 * - Use the standard methods rather than creating custom logging solutions
 * - The Debug page displays logs from this centralized system
 * 
 * @since      1.0.0
 * @package    TradePress
 * @author     Ryan Bayne
 * @version    1.3.0 (Enhanced with centralized logging and endpoint tracking)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_API_Logging {
    
    /**
     * Check if the logging system is ready and enabled
     *
     * @return bool Whether logging is ready and enabled
     */
    public static function ready() {
        // Check if logging is enabled in settings
        $logging_enabled = get_option('tradepress_api_logging_enabled', 'yes');
        
        // If logging is disabled, return false
        if ($logging_enabled !== 'yes') {
            return false;
        }
        
        // Make sure log directory exists and is writable
        if (defined('TRADEPRESS_LOG_DIR') && !is_dir(TRADEPRESS_LOG_DIR)) {
            wp_mkdir_p(TRADEPRESS_LOG_DIR);
        }
        
        // Return true if log directory exists and is writable
        return defined('TRADEPRESS_LOG_DIR') && is_dir(TRADEPRESS_LOG_DIR) && is_writable(TRADEPRESS_LOG_DIR);
    }
    
    /**
     * Breaks down a WP_Http response and logs it as meta data.
     *
     * @param int   $entry_id   Call entry ID from log_call()
     * @param array $curl_reply The response from a WP_Http request.
     */
    public static function breakdown($entry_id, $curl_reply) {
        if ( ! self::ready() || ! $entry_id || ! is_array($curl_reply) ) {
            return;
        }

        if (isset($curl_reply['headers'])) {
            self::add_meta($entry_id, 'response_headers', $curl_reply['headers']);
        }

        if (isset($curl_reply['body'])) {
            self::add_meta($entry_id, 'response_body', $curl_reply['body']);
        }

        if (isset($curl_reply['response']) && is_array($curl_reply['response'])) {
            if (isset($curl_reply['response']['code'])) {
                self::add_meta($entry_id, 'response_code', $curl_reply['response']['code']);
            }
            if (isset($curl_reply['response']['message'])) {
                self::add_meta($entry_id, 'response_message', $curl_reply['response']['message']);
            }
        }
    }

    /**
     * Log an API call to the tradepress_calls table
     *
     * This is the preferred method for logging API calls in TradePress.
     * It records the call in the standard tradepress_calls table and returns
     * the entry ID for further reference (such as logging errors).
     *
     * @param string $service       API service name (e.g., 'alpaca', 'alphavantage')
     * @param string $function      Function name or identifier for the call
     * @param string $type          Call type (GET, POST, PUT, DELETE, etc.)
     * @param string $status        Call status (success, error, pending, etc.)
     * @param string $file          File that initiated the call
     * @param string $line          Line number that initiated the call
     * @param string $description   Brief description of the call
     * @param string $outcome       Outcome of the call (if known)
     * @param int    $life          TTL for the log entry in seconds
     * @return int                  The call entry ID
     */
    public static function log_call($service, $function, $type = 'GET', $status = 'pending', $file = '', $line = '', $description = '', $outcome = '', $life = 86400) {
        global $wpdb;
        
        // Get current user ID
        $user_id = get_current_user_id();
        
        // Get file and line if not provided
        if (empty($file) || empty($line)) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $file = $backtrace[0]['file'] ?? '';
            $line = $backtrace[0]['line'] ?? '';
        }
        
        // Insert into tradepress_calls table
        $data = array(
            'callid'      => 0, // This would be a reference ID if needed
            'service'     => sanitize_text_field($service),
            'type'        => sanitize_text_field($type),
            'status'      => sanitize_text_field($status),
            'file'        => sanitize_text_field($file),
            'function'    => sanitize_text_field($function),
            'line'        => intval($line),
            'wpuserid'    => $user_id,
            'timestamp'   => current_time('mysql'),
            'description' => $description,
            'outcome'     => $outcome,
            'life'        => intval($life),
        );
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'tradepress_calls',
            $data,
            array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d')
        );
        
        // Add developer notice for API logging with specific function context
        if (class_exists('TradePress_Developer_Notices')) {
            TradePress_Developer_Notices::database_notice('INSERT', 'tradepress_calls', array('service' => $service, 'function' => $function, 'purpose' => $description), $result);
        }
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        // If DB insertion failed, log to error log
        error_log('TradePress API Logging: Failed to log call to ' . $service);
        return 0;
    }
    
    /**
     * Track an API endpoint usage
     *
     * Records endpoint usage in the tradepress_endpoints table.
     * This keeps track of which endpoints are being called and how frequently.
     *
     * @param int    $entry_id    Call entry ID from log_call()
     * @param string $service     API service name
     * @param string $endpoint    Full endpoint URL or path
     * @param array  $parameters  Parameters used in the API call
     * @return int|bool           The endpoint entry ID or false on failure
     */
    public static function track_endpoint($entry_id, $service, $endpoint, $parameters = array()) {
        global $wpdb;
        
        // Check if this endpoint exists
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tradepress_endpoints WHERE service = %s AND endpoint = %s",
                $service,
                $endpoint
            )
        );
        
        // Format parameters as JSON string
        $params_json = json_encode($parameters);
        
        if ($existing) {
            // Update existing endpoint record
            $result = $wpdb->update(
                $wpdb->prefix . 'tradepress_endpoints',
                array(
                    'entryid'    => $entry_id,
                    'lastuse'    => current_time('mysql'),
                    'counter'    => $existing->counter + 1,
                    'parameters' => $params_json,
                ),
                array('endpointid' => $existing->endpointid),
                array('%d', '%s', '%d', '%s'),
                array('%d')
            );
            
            return $result ? $existing->endpointid : false;
        } else {
            // Insert new endpoint record
            $result = $wpdb->insert(
                $wpdb->prefix . 'tradepress_endpoints',
                array(
                    'entryid'    => $entry_id,
                    'service'    => $service,
                    'endpoint'   => $endpoint,
                    'parameters' => $params_json,
                    'firstuse'   => current_time('mysql'),
                    'lastuse'    => current_time('mysql'),
                    'counter'    => 1,
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%d')
            );
            
            return $result ? $wpdb->insert_id : false;
        }
    }
    
    /**
     * Log an API error
     *
     * Records error details in the tradepress_errors table,
     * linked to the original API call entry.
     *
     * @param int    $entry_id   Call entry ID from log_call()
     * @param string $code       Error code
     * @param string $error      Error message
     * @param string $function   Function where error occurred
     * @param string $file       File where error occurred
     * @param int    $line       Line where error occurred
     * @return int|bool          The error entry ID or false on failure
     */
    public static function log_error($entry_id, $code, $error, $function = '', $file = '', $line = 0) {
        global $wpdb;
        
        // Get file, function and line if not provided
        if (empty($file) || empty($function) || empty($line)) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $file = empty($file) ? ($backtrace[0]['file'] ?? '') : $file;
            $function = empty($function) ? ($backtrace[0]['function'] ?? '') : $function;
            $line = empty($line) ? ($backtrace[0]['line'] ?? 0) : $line;
        }
        
        // Update the call status to error
        if ($entry_id) {
            $wpdb->update(
                $wpdb->prefix . 'tradepress_calls',
                array('status' => 'error'),
                array('entryid' => $entry_id),
                array('%s'),
                array('%d')
            );
        }
        
        // Insert into tradepress_errors table
        $result = $wpdb->insert(
            $wpdb->prefix . 'tradepress_errors',
            array(
                'entryid'   => $entry_id,
                'code'      => sanitize_text_field($code),
                'error'     => sanitize_text_field($error),
                'line'      => intval($line),
                'function'  => sanitize_text_field($function),
                'file'      => sanitize_text_field($file),
                'timestamp' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Add metadata to a call entry
     *
     * Stores additional metadata for an API call in the tradepress_meta table.
     *
     * @param int    $entry_id  Call entry ID from log_call()
     * @param string $meta_key  Metadata key
     * @param mixed  $meta_value Metadata value
     * @param string $expiry     Optional expiration datetime
     * @return int|bool          The meta entry ID or false on failure
     */
    public static function add_meta($entry_id, $meta_key, $meta_value, $expiry = null) {
        global $wpdb;
        
        // Insert into tradepress_meta table
        $result = $wpdb->insert(
            $wpdb->prefix . 'tradepress_meta',
            array(
                'entryid'   => $entry_id,
                'metakey'   => sanitize_text_field($meta_key),
                'metavalue' => maybe_serialize($meta_value),
                'timestamp' => current_time('mysql'),
                'expiry'    => $expiry,
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Update an existing call outcome
     *
     * @param int    $entry_id  Call entry ID
     * @param string $outcome   Outcome of the call
     * @param string $status    New status for the call
     * @return bool             Success status
     */
    public static function update_call_outcome($entry_id, $outcome, $status = 'complete') {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'tradepress_calls',
            array(
                'outcome' => sanitize_text_field($outcome),
                'status'  => sanitize_text_field($status),
            ),
            array('entryid' => $entry_id),
            array('%s', '%s'),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Get API call logs
     *
     * Retrieves API call logs from the database with optional filtering
     *
     * @param array $args {
     *     Optional. Arguments to filter the query.
     *
     *     @type string $service       Service name to filter by
     *     @type string $status        Status to filter by (success, error, pending)
     *     @type string $type          Type to filter by (GET, POST, etc.)
     *     @type int    $wpuserid      User ID to filter by
     *     @type string $date_from     Start date (format: Y-m-d)
     *     @type string $date_to       End date (format: Y-m-d)
     *     @type int    $limit         Maximum number of logs to retrieve
     *     @type int    $offset        Offset for pagination
     *     @type string $orderby       Column to sort by (default: timestamp)
     *     @type string $order         Sort order (ASC or DESC, default: DESC)
     * }
     * @return array Array of API call logs
     */
    public static function get_api_calls($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'service'    => '',
            'status'     => '',
            'type'       => '',
            'wpuserid'   => 0,
            'date_from'  => '',
            'date_to'    => '',
            'limit'      => 50,
            'offset'     => 0,
            'orderby'    => 'timestamp',
            'order'      => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Start building the query
        $query = "SELECT * FROM {$wpdb->prefix}tradepress_calls WHERE 1=1";
        $params = array();
        
        // Add filters
        if (!empty($args['service'])) {
            $query .= " AND service = %s";
            $params[] = $args['service'];
        }
        
        if (!empty($args['status'])) {
            $query .= " AND status = %s";
            $params[] = $args['status'];
        }
        
        if (!empty($args['type'])) {
            $query .= " AND type = %s";
            $params[] = $args['type'];
        }
        
        if (!empty($args['wpuserid'])) {
            $query .= " AND wpuserid = %d";
            $params[] = $args['wpuserid'];
        }
        
        if (!empty($args['date_from'])) {
            $query .= " AND timestamp >= %s";
            $params[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $query .= " AND timestamp <= %s";
            $params[] = $args['date_to'] . ' 23:59:59';
        }
        
        // Add order by
        $allowed_columns = array('entryid', 'service', 'type', 'status', 'function', 'timestamp');
        $orderby = in_array($args['orderby'], $allowed_columns) ? $args['orderby'] : 'timestamp';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        $query .= " ORDER BY {$orderby} {$order}";
        
        // Add limit and offset
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $args['limit'];
        $params[] = $args['offset'];
        
        // Prepare and execute the query
        $prepared_query = $wpdb->prepare($query, $params);
        $results = $wpdb->get_results($prepared_query, ARRAY_A);
        
        return $results;
    }

    /**
     * Get API call count
     *
     * Retrieves the count of API calls from the database with optional filtering
     *
     * @param array $args {
     *     Optional. Arguments to filter the query.
     *
     *     @type string $service       Service name to filter by
     *     @type string $status        Status to filter by (success, error, pending)
     *     @type string $type          Type to filter by (GET, POST, etc.)
     *     @type int    $wpuserid      User ID to filter by
     *     @type string $date_from     Start date (format: Y-m-d)
     *     @type string $date_to       End date (format: Y-m-d)
     * }
     * @return int Number of API calls matching the criteria
     */
    public static function get_api_call_count($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'service'    => '',
            'status'     => '',
            'type'       => '',
            'wpuserid'   => 0,
            'date_from'  => '',
            'date_to'    => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Start building the query
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}tradepress_calls WHERE 1=1";
        $params = array();
        
        // Add filters
        if (!empty($args['service'])) {
            $query .= " AND service = %s";
            $params[] = $args['service'];
        }
        
        if (!empty($args['status'])) {
            $query .= " AND status = %s";
            $params[] = $args['status'];
        }
        
        if (!empty($args['type'])) {
            $query .= " AND type = %s";
            $params[] = $args['type'];
        }
        
        if (!empty($args['wpuserid'])) {
            $query .= " AND wpuserid = %d";
            $params[] = $args['wpuserid'];
        }
        
        if (!empty($args['date_from'])) {
            $query .= " AND timestamp >= %s";
            $params[] = $args['date_from'] . ' 00:00:00';
        }
        
        if (!empty($args['date_to'])) {
            $query .= " AND timestamp <= %s";
            $params[] = $args['date_to'] . ' 23:59:59';
        }
        
        // Prepare and execute the query
        $prepared_query = empty($params) ? $query : $wpdb->prepare($query, $params);
        $count = $wpdb->get_var($prepared_query);
        
        return (int) $count;
    }
    
    /**
     * Get service statistics
     * 
     * Retrieves statistics about API calls for each service
     * 
     * @param string $date_from Start date (format: Y-m-d)
     * @param string $date_to End date (format: Y-m-d)
     * @return array Array of service statistics
     */
    public static function get_service_stats($date_from = '', $date_to = '') {
        global $wpdb;
        
        $query = "SELECT 
            service, 
            COUNT(*) as total_calls,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_calls,
            SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_calls,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_calls
        FROM {$wpdb->prefix}tradepress_calls
        WHERE 1=1";
        
        $params = array();
        
        if (!empty($date_from)) {
            $query .= " AND timestamp >= %s";
            $params[] = $date_from . ' 00:00:00';
        }
        
        if (!empty($date_to)) {
            $query .= " AND timestamp <= %s";
            $params[] = $date_to . ' 23:59:59';
        }
        
        $query .= " GROUP BY service ORDER BY total_calls DESC";
        
        // Prepare and execute the query
        $prepared_query = empty($params) ? $query : $wpdb->prepare($query, $params);
        $results = $wpdb->get_results($prepared_query, ARRAY_A);
        
        return $results;
    }
}