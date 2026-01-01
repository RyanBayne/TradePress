<?php
/**
 * TradePress Alpaca API
 *
 * Handles connection and functionality for the Alpaca trading platform API
 *
 * @package TradePress
 * @subpackage API\Alpaca
 * @version 1.0.2
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Require base API class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/base-api.php';

/**
 * TradePress Alpaca API class
 */
class TradePress_Alpaca_API extends TradePress_Base_API {
    
    /**
     * API key
     *
     * @var string
     */
    private $api_key;
    
    /**
     * API secret
     *
     * @var string
     */
    private $api_secret;
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_url;
    
    /**
     * API data URL
     *
     * @var string
     */
    private $data_url;
    
    /**
     * Trading mode (paper/live)
     *
     * @var string
     */
    private $mode;
    
    /**
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'Alpaca',
        'code' => 'alpaca',
        'type' => 'broker_trading',
        'tier' => 1,
        'status' => 'active',
        'capabilities' => array(
            'trading' => true,
            'paper_trading' => true,
            'real_time_data' => true,
            'historical_data' => true,
            'portfolio_management' => true,
            'order_management' => true,
            'watchlists' => true,
            'account_data' => true,
            'market_data' => true,
            'fractional_shares' => true
        ),
        'data_types' => array(
            'quote' => 'stocks/quotes/latest',
            'bars' => 'stocks/bars',
            'volume' => 'stocks/bars',
            'trades' => 'stocks/trades/latest',
            'account' => 'account',
            'positions' => 'positions',
            'orders' => 'orders',
            'portfolio_history' => 'account/portfolio/history',
            'watchlists' => 'watchlists',
            'assets' => 'assets'
        ),
        'rate_limits' => array(
            'per_minute_data' => 200,
            'per_minute_trading' => 200,
            'burst' => 10
        ),
        'supported_markets' => array('US'),
        'pricing' => array(
            'free_tier' => true,
            'commission_free' => true,
            'min_account' => 0
        )
    );
    
    /**
     * Constructor
     * 
     * @param string $provider_id Provider ID (should be 'alpaca')
     * @param array $args Optional. Arguments for the API setup.
     */
    public function __construct($provider_id = 'alpaca', $args = array()) {
        // Call parent constructor
        parent::__construct($provider_id, $args);
        
        // Set the trading mode (paper or live)
        $this->mode = isset($args['mode']) ? $args['mode'] : 'paper';
        
        // Set base URLs based on mode
        if ($this->mode === 'live') {
            $this->base_url = 'https://api.alpaca.markets/v2/';
            $this->data_url = 'https://data.alpaca.markets/v2/';
        } else {
            $this->base_url = 'https://paper-api.alpaca.markets/v2/';
            $this->data_url = 'https://data.alpaca.markets/v2/';
        }
        
        // Set credentials from args or options
        if (isset($args['api_key'])) {
            $this->api_key = $args['api_key'];
        } else {
            $this->api_key = get_option('TradePress_api_alpaca_key', '');
        }
        
        if (isset($args['api_secret'])) {
            $this->api_secret = $args['api_secret'];
        } else {
            $this->api_secret = get_option('TradePress_api_alpaca_secret', '');
        }
        
        // Set API URLs
        $this->api_url = $this->base_url;
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
     * Test connection to Alpaca API
     * 
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function test_connection() {
        if (empty($this->api_key) || empty($this->api_secret)) {
            return new WP_Error('missing_credentials', __('API credentials are not configured', 'tradepress'));
        }
        
        // Make a simple API call to test the connection
        $account_info = $this->get_account();
        
        if (is_wp_error($account_info)) {
            return $account_info;
        }
        
        return true;
    }
    
    /**
     * Get account information
     * 
     * @return array|WP_Error Account data or error
     */
    public function get_account() {
        return $this->make_request('account');
    }
    
    /**
     * Get standardized quote data
     * 
     * @param string $symbol Stock symbol
     * @return array|WP_Error Normalized quote data
     */
    public function get_quote($symbol) {
        // Implementation for getting quote from Alpaca
        $response = $this->make_request('stocks/quotes/latest', array('symbols' => $symbol));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Normalize the response format
        if (isset($response['quotes'][$symbol])) {
            $quote = $response['quotes'][$symbol];
            return array(
                'symbol' => $symbol,
                'price' => isset($quote['ap']) ? $quote['ap'] : 0,
                'bid' => isset($quote['bp']) ? $quote['bp'] : 0,
                'ask' => isset($quote['ap']) ? $quote['ap'] : 0,
                'timestamp' => isset($quote['t']) ? $quote['t'] : current_time('timestamp')
            );
        }
        
        return new WP_Error('no_data', 'No quote data available');
    }
    
    /**
     * Make an API request to Alpaca
     * 
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array $body Request body for POST/PUT requests
     * @return array|WP_Error Response data or error
     */
    public function make_request($endpoint, $params = array(), $method = 'GET', $body = array()) {
        if (empty($this->api_key) || empty($this->api_secret)) {
            return new WP_Error('missing_credentials', __('API credentials are not configured', 'tradepress'));
        }
        
        // Require the logging class
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/api-logging.php';
        
        // Determine if this is a market data request or trading request
        $is_market_data = strpos($endpoint, 'market-data') !== false || 
                         strpos($endpoint, 'bars') !== false || 
                         strpos($endpoint, 'quotes') !== false || 
                         strpos($endpoint, 'trades') !== false;
        
        // Use the appropriate base URL
        $base_url = $is_market_data ? $this->data_url : $this->base_url;
        
        // Build the full URL
        $url = $base_url . ltrim($endpoint, '/');
        
        // Add query parameters if present
        if (!empty($params) && ($method === 'GET' || $method === 'DELETE')) {
            $url = add_query_arg($params, $url);
        }
        
        // Log the API call in the database
        $call_entry_id = TradePress_API_Logging::log_call(
            'alpaca',       // Service name
            $endpoint,      // Function/endpoint name
            $method,        // Request type
            'pending',      // Initial status
            __FILE__,       // File
            __LINE__,       // Line
            sprintf(__('Alpaca API call to %s (%s mode)', 'tradepress'), $endpoint, $this->mode), // Description
            '',             // Outcome (will be updated later)
            86400           // TTL (24 hours)
        );
        
        // Track the endpoint usage
        $endpoint_id = TradePress_API_Logging::track_endpoint(
            $call_entry_id,
            'alpaca',
            $endpoint,
            $params
        );
        
        // Add metadata about the request
        TradePress_API_Logging::add_meta($call_entry_id, 'mode', $this->mode);
        TradePress_API_Logging::add_meta($call_entry_id, 'is_market_data', $is_market_data);
        
        // Prepare the request arguments
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'APCA-API-KEY-ID' => $this->api_key,
                'APCA-API-SECRET-KEY' => $this->api_secret,
                'Content-Type' => 'application/json'
            ),
        );
        
        // Add body for POST/PUT requests
        if (($method === 'POST' || $method === 'PUT') && !empty($body)) {
            $args['body'] = json_encode($body);
        }
        
        // Make the API request
        $response = wp_remote_request($url, $args);
        
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
        
        // Check if the response is successful (2xx)
        if ($response_code < 200 || $response_code >= 300) {
            $error_message = wp_remote_retrieve_response_message($response);
            $body = wp_remote_retrieve_body($response);
            
            // Try to get more detailed error from body
            $error_data = json_decode($body, true);
            if (isset($error_data['message'])) {
                $error_message = $error_data['message'];
            }
            
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
                sprintf(__('HTTP Error: %d %s', 'tradepress'), $response_code, $error_message),
                $error_data
            );
        }
        
        // Parse the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Check for JSON parsing errors
        if (json_last_error() !== JSON_ERROR_NONE && !empty($body)) {
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
        
        // Store response metadata
        TradePress_API_Logging::add_meta(
            $call_entry_id,
            'response_size',
            strlen($body)
        );
        
        // Store rate limit info if available
        $rate_limit = wp_remote_retrieve_header($response, 'X-Ratelimit-Limit');
        $rate_remaining = wp_remote_retrieve_header($response, 'X-Ratelimit-Remaining');
        
        if ($rate_limit) {
            TradePress_API_Logging::add_meta($call_entry_id, 'rate_limit', $rate_limit);
        }
        
        if ($rate_remaining) {
            TradePress_API_Logging::add_meta($call_entry_id, 'rate_remaining', $rate_remaining);
        }
        
        // Update the call outcome to success
        TradePress_API_Logging::update_call_outcome(
            $call_entry_id,
            'Success',
            'success'
        );
        
        // Return the response data or empty array if none
        return $data ?: array();
    }
    
    /**
     * Get positions
     * 
     * @return array|WP_Error Positions or error
     */
    public function get_positions() {
        return $this->make_request('positions');
    }
    
    /**
     * Get orders
     * 
     * @param array $params Query parameters
     * @return array|WP_Error Orders or error
     */
    public function get_orders($params = array()) {
        return $this->make_request('orders', $params);
    }
    
    /**
     * Place an order
     * 
     * @param array $order_data Order details
     * @return array|WP_Error Order result or error
     */
    public function place_order($order_data) {
        return $this->make_request('orders', array(), 'POST', $order_data);
    }
    
    /**
     * Get market data bars
     * 
     * @param array $params Query parameters
     * @return array|WP_Error Bars data or error
     */
    public function get_bars($params) {
        return $this->make_request('bars/1Day', $params);
    }
    
    /**
     * Call specific endpoint by name (for directive compatibility)
     * 
     * @param string $endpoint_name Endpoint name
     * @param array $params Parameters
     * @return array|WP_Error Response or error
     */
    public function call_endpoint($endpoint_name, $params = array()) {
        switch ($endpoint_name) {
            case 'portfolio_history':
                return $this->make_request('account/portfolio/history', $params);
            case 'watchlists':
                return $this->make_request('watchlists');
            case 'account':
                return $this->get_account();
            case 'positions':
                return $this->get_positions();
            case 'orders':
                return $this->get_orders($params);
            default:
                return new WP_Error('unknown_endpoint', 'Unknown endpoint: ' . $endpoint_name);
        }
    }
}
