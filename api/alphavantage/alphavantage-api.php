<?php
/**
 * TradePress - Alpha Vantage API Integration
 *
 * ARCHITECTURE NOTE:
 * This class is part of the TradePress API Integration Framework:
 * - It extends TradePress_Financial_API_Service for handling API-specific operations
 * - It should be used with a corresponding adapter class that extends TradePress_API_Adapter
 * - The Financial API Service (this class) handles direct API communication
 * - The API Adapter normalizes data formats for consistent application usage
 *
 * When modifying this class:
 * 1. Ensure changes align with the base class architecture
 * 2. Update the corresponding adapter if the API response format changes
 * 3. Maintain consistent error handling and logging
 *
 * @package TradePress/API/AlphaVantage
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Alpha Vantage API class
 */
class TradePress_AlphaVantage_API extends TradePress_Base_API {
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://www.alphavantage.co/query';
    
    /**
     * API key
     *
     * @var string
     */
    private $api_key;
    
    /**
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'Alpha Vantage',
        'code' => 'alphavantage',
        'type' => 'market_data',
        'tier' => 1,
        'status' => 'active',
        'capabilities' => array(
            'historical_data' => true,
            'real_time_data' => true,
            'technical_indicators' => true,
            'fundamental_data' => true,
            'earnings_data' => true,
            'news_data' => false,
            'options_data' => false,
            'forex_data' => true,
            'crypto_data' => true,
            'commodity_data' => true
        ),
        'data_types' => array(
            'quote' => 'GLOBAL_QUOTE',
            'bars' => 'TIME_SERIES_DAILY',
            'volume' => 'TIME_SERIES_DAILY',
            'rsi' => 'RSI',
            'macd' => 'MACD',
            'sma' => 'SMA',
            'ema' => 'EMA',
            'cci' => 'CCI',
            'adx' => 'ADX',
            'bollinger_bands' => 'BBANDS',
            'stochastic' => 'STOCH',
            'williams_r' => 'WILLR',
            'momentum' => 'MOM',
            'roc' => 'ROC',
            'fundamentals' => 'OVERVIEW',
            'earnings' => 'EARNINGS_CALENDAR',
            'income_statement' => 'INCOME_STATEMENT',
            'balance_sheet' => 'BALANCE_SHEET',
            'cash_flow' => 'CASH_FLOW'
        ),
        'rate_limits' => array(
            'daily_free' => 25,
            'minute_free' => 5,
            'daily_premium' => 500,
            'minute_premium' => 5,
            'burst' => 5
        ),
        'supported_markets' => array('US', 'Global'),
        'pricing' => array(
            'free_tier' => true,
            'min_plan' => 'Premium',
            'cost_per_month' => 49.99
        )
    );
    
    /**
     * Constructor
     * 
     * @param string $provider_id The API provider ID
     * @param array $args Optional. Arguments for the API setup.
     */
    public function __construct($provider_id = 'alphavantage', $args = array()) {
        if (isset($args['api_key'])) {
            $this->api_key = $args['api_key'];
        } else {
            $this->api_key = get_option('TradePress_api_alphavantage_key', '');
        }
        
        parent::__construct($provider_id, $args);
    }
    
    /**
     * Get platform metadata
     *
     * @return array Platform metadata
     */
    public function get_platform_meta() {
        return $this->platform_meta;
    }
    
    /**
     * Get platform capabilities
     *
     * @return array Platform capabilities
     */
    public function get_capabilities() {
        return $this->platform_meta['capabilities'];
    }
    
    /**
     * Check if platform supports specific data type
     *
     * @param string $data_type Data type to check
     * @return bool True if supported
     */
    public function supports_data_type($data_type) {
        return isset($this->platform_meta['data_types'][$data_type]);
    }
    
    /**
     * Get endpoint for data type
     *
     * @param string $data_type Data type
     * @return string|false Endpoint name or false
     */
    public function get_data_type_endpoint($data_type) {
        return $this->supports_data_type($data_type) ? $this->platform_meta['data_types'][$data_type] : false;
    }
    
    /**
     * Test connection to Alpha Vantage API
     * 
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function test_connection() {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('API key is not configured', 'tradepress'));
        }
        
        // Make a simple API call to test the connection
        $result = $this->make_request('GLOBAL_QUOTE', ['symbol' => 'AAPL']);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Check if the response indicates an error
        if (isset($result['Error Message'])) {
            return new WP_Error('api_error', $result['Error Message']);
        }
        
        if (isset($result['Note']) && strpos($result['Note'], 'API call frequency') !== false) {
            return new WP_Error('rate_limit', __('API rate limit exceeded', 'tradepress'));
        }
        
        return true;
    }
    
    /**
     * Make an API request to Alpha Vantage
     * 
     * @param string $endpoint The API function to call
     * @param array $params Additional parameters for the API request
     * @param string $method Request method (always GET for Alpha Vantage)
     * @return array|WP_Error API response or error
     */
    public function make_request($endpoint, $params = array(), $method = 'GET') {
        $function = $endpoint; // Alpha Vantage uses 'function' parameter
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('API key is not configured', 'tradepress'));
        }
        
        // Require the logging class
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/api-logging.php';
        
        // Log the API call in the database
        $call_entry_id = TradePress_API_Logging::log_call(
            'alphavantage', // Service name
            $function,      // Function name
            'GET',          // Request type
            'pending',      // Initial status
            __FILE__,       // File
            __LINE__,       // Line
            sprintf(__('Alpha Vantage API call to %s', 'tradepress'), $function), // Description
            '',             // Outcome (will be updated later)
            86400           // TTL (24 hours)
        );
        
        // Build the URL
        $url = add_query_arg(
            array(
                'function' => $function,
                'apikey' => $this->api_key
            ),
            $this->api_base_url
        );
        
        // Add additional parameters
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }
        
        // Track the endpoint usage
        $endpoint_id = TradePress_API_Logging::track_endpoint(
            $call_entry_id,
            'alphavantage',
            $function,
            $params
        );
        
        // Make the API request
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ));
        
        // Check for request errors
        if (is_wp_error($response)) {
            // Log the error
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'http_error',
                $response->get_error_message(),
                __FUNCTION__
            );
            
            // Update the call status to error
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'Request failed: ' . $response->get_error_message(),
                'error'
            );
            
            return $response;
        }
        
        // Get response code
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Check if the response is not 200 OK
        if ($response_code !== 200) {
            $error_message = wp_remote_retrieve_response_message($response);
            
            // Log the error
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'http_' . $response_code,
                $error_message,
                __FUNCTION__
            );
            
            // Update the call status to error
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'HTTP Error ' . $response_code . ': ' . $error_message,
                'error'
            );
            
            return new WP_Error(
                'http_error',
                sprintf(__('HTTP Error: %d %s', 'tradepress'), $response_code, $error_message)
            );
        }
        
        // Parse the response body
        $body = wp_remote_retrieve_body($response);
        
        // Special handling for endpoints that return CSV (like EARNINGS_CALENDAR)
        if ($function === 'EARNINGS_CALENDAR') {
            $data = $this->parse_csv_response($body, $call_entry_id);
            
            // If parsing succeeded
            if (!is_wp_error($data)) {
                // Update the call outcome to success
                TradePress_API_Logging::update_call_outcome(
                    $call_entry_id,
                    'Success (CSV response)',
                    'success'
                );
            }
            
            return $data;
        }
        
        // For regular JSON responses
        $data = json_decode($body, true);
        
        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log the error
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'json_parse_error',
                'Failed to parse JSON response: ' . json_last_error_msg(),
                __FUNCTION__
            );
            
            // Update the call status to error
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'Response parsing failed',
                'error'
            );
            
            return new WP_Error('parse_error', __('Failed to parse API response', 'tradepress'));
        }
        
        // Check for API errors
        if (isset($data['Error Message'])) {
            $error_message = $data['Error Message'];
            
            // Enhance error message for common issues
            if (strpos($error_message, 'Invalid API call') !== false) {
                $error_message .= '. Common causes: missing required parameters (series_type for technical indicators), invalid symbol, or incorrect function name. Check Alpha Vantage documentation.';
            }
            
            // Log the error
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'api_error',
                $error_message,
                __FUNCTION__
            );
            
            // Update the call status to error
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'API Error: ' . $error_message,
                'error'
            );
            
            return new WP_Error('api_error', $error_message);
        }
        
        // Check for rate limiting
        if (isset($data['Note']) && strpos($data['Note'], 'API call frequency') !== false) {
            // Log the error
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'rate_limit',
                $data['Note'],
                __FUNCTION__
            );
            
            // Update the call status to error
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'Rate limit exceeded',
                'error'
            );
            
            return new WP_Error('rate_limit', $data['Note']);
        }
        
        // Store response metadata
        TradePress_API_Logging::add_meta(
            $call_entry_id,
            'response_size',
            strlen($body)
        );
        
        // Update the call outcome to success
        TradePress_API_Logging::update_call_outcome(
            $call_entry_id,
            'Success',
            'success'
        );
        
        return $data;
    }
    
    /**
     * Parse CSV response from Alpha Vantage API
     * 
     * @param string $csv_data CSV data string
     * @param int $call_entry_id The API call entry ID for logging
     * @return array|WP_Error Parsed data or error
     */
    protected function parse_csv_response($csv_data, $call_entry_id) {
        // Store raw response metadata
        TradePress_API_Logging::add_meta(
            $call_entry_id,
            'response_size',
            strlen($csv_data)
        );
        
        // Ensure we have data
        if (empty($csv_data)) {
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'empty_response',
                'Empty CSV response received',
                __FUNCTION__
            );
            
            return new WP_Error('empty_response', __('Empty response received from API', 'tradepress'));
        }
        
        // Split into rows
        $rows = str_getcsv($csv_data, "\n");
        
        // Ensure we have at least a header row
        if (count($rows) < 1) {
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'invalid_csv',
                'Invalid CSV format: no rows found',
                __FUNCTION__
            );
            
            return new WP_Error('invalid_csv', __('Invalid CSV format in API response', 'tradepress'));
        }
        
        // Parse header row
        $headers = str_getcsv($rows[0]);
        
        // Parse data rows
        $data = array();
        for ($i = 1; $i < count($rows); $i++) {
            // Skip empty rows
            if (empty($rows[$i])) {
                continue;
            }
            
            $values = str_getcsv($rows[$i]);
            
            // Ensure we have values matching headers
            if (count($values) !== count($headers)) {
                // Log warning but continue processing
                TradePress_API_Logging::log_error(
                    $call_entry_id,
                    'csv_row_mismatch',
                    sprintf('CSV row %d has %d values, expected %d', $i, count($values), count($headers)),
                    __FUNCTION__
                );
                continue;
            }
            
            // Convert to associative array
            $row_data = array_combine($headers, $values);
            $data[] = $row_data;
        }
        
        return $data;
    }
    
    /**
     * Get earnings calendar data
     * 
     * @param string $horizon The time horizon for earnings data (3month, 6month, 12month)
     * @param string $symbol Optional. Limit results to a specific symbol
     * @return array|WP_Error Earnings calendar data or error
     */
    public function get_earnings_calendar($horizon = '3month', $symbol = null) {
        $params = array(
            'horizon' => $horizon
        );
        
        // Add symbol parameter if provided
        if (!empty($symbol)) {
            $params['symbol'] = $symbol;
        }
        
        // Make the API request
        $response = $this->make_request('EARNINGS_CALENDAR', $params);
        
        // Process response
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Return the earnings data
        return $response;
    }
    
    /**
     * Get API call statistics
     * 
     * @return array API call statistics
     */
    public function get_api_call_stats() {
        global $wpdb;
        
        // Get call counts from the database
        $daily_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}tradepress_calls 
            WHERE service = %s 
            AND timestamp > %s",
            'alphavantage',
            date('Y-m-d 00:00:00')
        ));
        
        $minute_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}tradepress_calls 
            WHERE service = %s 
            AND timestamp > %s",
            'alphavantage',
            date('Y-m-d H:i:s', strtotime('-1 minute'))
        ));
        
        return array(
            'daily_count' => $daily_count ?: 0,
            'minute_count' => $minute_count ?: 0,
            'daily_limit_free' => 25, 
            'minute_limit_free' => 5,  
            'daily_limit_premium' => 500, 
            'minute_limit_premium' => 5, 
            'is_premium' => get_option('tradepress_alphavantage_premium', 'no') === 'yes'
        );
    }
    
    /**
     * Get a random symbol for testing
     * 
     * @return string A random stock symbol
     */
    public function get_random_symbol() {
        $symbols = array('AAPL', 'MSFT', 'GOOG', 'AMZN', 'FB', 'TSLA', 'NVDA', 'JPM', 'JNJ', 'V', 'PG', 'MA', 'UNH', 'HD', 'BAC');
        return $symbols[array_rand($symbols)];
    }
    
    /**
     * Get quote data (required by base class)
     * 
     * @param string $symbol The stock symbol
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        return $this->get_global_quote($symbol);
    }
    
    /**
     * Get a global quote for a symbol
     * 
     * @param string $symbol The stock symbol to get a quote for
     * @return array|WP_Error Quote data or WP_Error on failure
     */
    public function get_global_quote($symbol) {
        $params = array(
            'symbol' => $symbol
        );
        
        $response = $this->make_request('GLOBAL_QUOTE', $params);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Process the response to get the quote data in a standardized format
        $result = array();
        
        if (isset($response['Global Quote'])) {
            $quote = $response['Global Quote'];
            $result = array(
                'symbol' => isset($quote['01. symbol']) ? $quote['01. symbol'] : '',
                'price' => isset($quote['05. price']) ? (float)$quote['05. price'] : 0,
                'open' => isset($quote['02. open']) ? (float)$quote['02. open'] : 0,
                'high' => isset($quote['03. high']) ? (float)$quote['03. high'] : 0,
                'low' => isset($quote['04. low']) ? (float)$quote['04. low'] : 0,
                'volume' => isset($quote['06. volume']) ? (int)$quote['06. volume'] : 0,
                'latest_trading_day' => isset($quote['07. latest trading day']) ? $quote['07. latest trading day'] : '',
                'previous_close' => isset($quote['08. previous close']) ? (float)$quote['08. previous close'] : 0,
                'change' => isset($quote['09. change']) ? (float)$quote['09. change'] : 0,
                'change_percent' => isset($quote['10. change percent']) ? (float)str_replace('%', '', $quote['10. change percent']) : 0
            );
        }
        
        return $result;
    }
}
