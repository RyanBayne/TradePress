<?php
/**
 * TradePress Tradier API
 *
 * Handles connection and functionality for the Tradier trading platform
 *
 * @package TradePress
 * @subpackage API\Tradier
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Tradier API class
 */
class TradePress_Tradier_API {
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://api.tradier.com';
    
    /**
     * Access token
     *
     * @var string
     */
    private $access_token;
    
    /**
     * Account ID
     *
     * @var string
     */
    private $account_id;
    
    /**
     * Use sandbox environment
     *
     * @var bool
     */
    private $use_sandbox;
    
    /**
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'Tradier',
        'code' => 'tradier',
        'type' => 'broker_trading',
        'tier' => 2,
        'status' => 'active',
        'capabilities' => array(
            'trading' => true,
            'paper_trading' => true,
            'real_time_data' => true,
            'historical_data' => true,
            'options_trading' => true,
            'options_data' => true,
            'portfolio_management' => true,
            'order_management' => true,
            'account_data' => true,
            'market_data' => true,
            'watchlists' => true,
            'time_and_sales' => true,
            'market_calendar' => true,
            'symbol_search' => true,
            'intraday_data' => true
        ),
        'data_types' => array(
            'quote' => 'quotes',
            'quotes' => 'quotes',
            'bars' => 'historical_quotes',
            'volume' => 'historical_quotes',
            'historical' => 'historical_quotes',
            'intraday' => 'time_and_sales',
            'options_quotes' => 'option_quotes',
            'options_chains' => 'option_chains',
            'options_expirations' => 'option_expirations',
            'options_strikes' => 'option_strikes',
            'search' => 'search',
            'lookup' => 'lookup',
            'clock' => 'clock',
            'calendar' => 'calendar',
            'account' => 'user_profile',
            'balances' => 'balances',
            'positions' => 'positions',
            'orders' => 'orders',
            'watchlists' => 'watchlists'
        ),
        'rate_limits' => array(
            'requests_per_minute' => 120,
            'burst' => 10,
            'note' => 'Rate limits vary by plan'
        ),
        'supported_markets' => array('US'),
        'pricing' => array(
            'account_minimum' => 0,
            'commission_stocks' => 0,
            'commission_options' => 0.35,
            'data_fees' => 'included'
        )
    );
    
    /**
     * Constructor
     *
     * @param string $access_token Access token
     * @param string $account_id Account ID
     * @param bool $use_sandbox Use sandbox environment
     */
    public function __construct($access_token = '', $account_id = '', $use_sandbox = false) {
        $this->access_token = $access_token ?: get_option('tradepress_tradier_access_token', '');
        $this->account_id = $account_id ?: get_option('tradepress_tradier_account_id', '');
        $this->use_sandbox = $use_sandbox;
        
        if ($this->use_sandbox) {
            $this->api_base_url = 'https://sandbox.tradier.com';
        }
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
     * Get quote data
     *
     * @param array|string $symbols Stock symbols
     * @param bool $greeks Include Greeks for options
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbols, $greeks = false) {
        $symbols_string = is_array($symbols) ? implode(',', $symbols) : $symbols;
        return $this->make_request('quotes', array('symbols' => $symbols_string, 'greeks' => $greeks));
    }
    
    /**
     * Get historical bars
     *
     * @param string $symbol Stock symbol
     * @param string $interval Data interval
     * @param string $start Start date
     * @param string $end End date
     * @return array|WP_Error Historical data or error
     */
    public function get_bars($symbol, $interval = 'daily', $start = '', $end = '') {
        $params = array('symbol' => $symbol, 'interval' => $interval);
        if ($start) $params['start'] = $start;
        if ($end) $params['end'] = $end;
        
        return $this->make_request('historical_quotes', $params);
    }
    
    /**
     * Get intraday data (time and sales)
     *
     * @param string $symbol Stock symbol
     * @param string $interval Time interval
     * @param string $start Start time
     * @param string $end End time
     * @param string $session_filter Session filter
     * @return array|WP_Error Intraday data or error
     */
    public function get_intraday($symbol, $interval = '5min', $start = '', $end = '', $session_filter = 'all') {
        $params = array('symbol' => $symbol, 'interval' => $interval, 'session_filter' => $session_filter);
        if ($start) $params['start'] = $start;
        if ($end) $params['end'] = $end;
        
        return $this->make_request('time_and_sales', $params);
    }
    
    /**
     * Get option quotes
     *
     * @param array|string $symbols Option symbols
     * @param bool $greeks Include Greeks
     * @return array|WP_Error Option quotes or error
     */
    public function get_option_quotes($symbols, $greeks = true) {
        $symbols_string = is_array($symbols) ? implode(',', $symbols) : $symbols;
        return $this->make_request('option_quotes', array('symbols' => $symbols_string, 'greeks' => $greeks));
    }
    
    /**
     * Get option chains
     *
     * @param string $symbol Underlying symbol
     * @param string $expiration Expiration date
     * @param bool $greeks Include Greeks
     * @return array|WP_Error Option chains or error
     */
    public function get_option_chains($symbol, $expiration, $greeks = true) {
        return $this->make_request('option_chains', array(
            'symbol' => $symbol,
            'expiration' => $expiration,
            'greeks' => $greeks
        ));
    }
    
    /**
     * Get option expirations
     *
     * @param string $symbol Underlying symbol
     * @param bool $include_all_roots Include all roots
     * @param bool $strikes Include strikes
     * @return array|WP_Error Option expirations or error
     */
    public function get_option_expirations($symbol, $include_all_roots = false, $strikes = false) {
        return $this->make_request('option_expirations', array(
            'symbol' => $symbol,
            'includeAllRoots' => $include_all_roots,
            'strikes' => $strikes
        ));
    }
    
    /**
     * Get option strikes
     *
     * @param string $symbol Underlying symbol
     * @param string $expiration Expiration date
     * @return array|WP_Error Option strikes or error
     */
    public function get_option_strikes($symbol, $expiration) {
        return $this->make_request('option_strikes', array(
            'symbol' => $symbol,
            'expiration' => $expiration
        ));
    }
    
    /**
     * Search symbols
     *
     * @param string $query Search query
     * @param bool $indexes Include indexes
     * @return array|WP_Error Search results or error
     */
    public function search($query, $indexes = false) {
        return $this->make_request('search', array('q' => $query, 'indexes' => $indexes));
    }
    
    /**
     * Lookup symbols
     *
     * @param string $query Search query
     * @param string $exchanges Specific exchanges
     * @param string $types Security types
     * @return array|WP_Error Lookup results or error
     */
    public function lookup($query, $exchanges = '', $types = '') {
        $params = array('q' => $query);
        if ($exchanges) $params['exchanges'] = $exchanges;
        if ($types) $params['types'] = $types;
        
        return $this->make_request('lookup', $params);
    }
    
    /**
     * Get market clock
     *
     * @return array|WP_Error Market clock or error
     */
    public function get_market_clock() {
        return $this->make_request('clock');
    }
    
    /**
     * Get market calendar
     *
     * @param int $month Month
     * @param int $year Year
     * @return array|WP_Error Market calendar or error
     */
    public function get_market_calendar($month = null, $year = null) {
        $params = array();
        if ($month) $params['month'] = $month;
        if ($year) $params['year'] = $year;
        
        return $this->make_request('calendar', $params);
    }
    
    /**
     * Get user profile
     *
     * @return array|WP_Error User profile or error
     */
    public function get_user_profile() {
        return $this->make_request('user_profile');
    }
    
    /**
     * Get account balances
     *
     * @param string $account_id Account ID
     * @return array|WP_Error Account balances or error
     */
    public function get_account($account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('balances', array('account_id' => $account_id));
    }
    
    /**
     * Get positions
     *
     * @param string $account_id Account ID
     * @return array|WP_Error Positions or error
     */
    public function get_positions($account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('positions', array('account_id' => $account_id));
    }
    
    /**
     * Get orders
     *
     * @param string $account_id Account ID
     * @param bool $include_inactive Include inactive orders
     * @return array|WP_Error Orders or error
     */
    public function get_orders($account_id = '', $include_inactive = false) {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('orders', array(
            'account_id' => $account_id,
            'includeInactive' => $include_inactive
        ));
    }
    
    /**
     * Get specific order
     *
     * @param int $order_id Order ID
     * @param string $account_id Account ID
     * @return array|WP_Error Order or error
     */
    public function get_order($order_id, $account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('order', array(
            'account_id' => $account_id,
            'order_id' => $order_id
        ));
    }
    
    /**
     * Place equity order
     *
     * @param array $order_data Order data
     * @param string $account_id Account ID
     * @return array|WP_Error Order result or error
     */
    public function place_order($order_data, $account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        $order_data['account_id'] = $account_id;
        
        return $this->make_request('equity_order', $order_data, 'POST');
    }
    
    /**
     * Place option order
     *
     * @param array $order_data Option order data
     * @param string $account_id Account ID
     * @return array|WP_Error Order result or error
     */
    public function place_option_order($order_data, $account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        $order_data['account_id'] = $account_id;
        $order_data['class'] = 'option';
        
        return $this->make_request('option_order', $order_data, 'POST');
    }
    
    /**
     * Cancel order
     *
     * @param int $order_id Order ID
     * @param string $account_id Account ID
     * @return array|WP_Error Cancel result or error
     */
    public function cancel_order($order_id, $account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('cancel_order', array(
            'account_id' => $account_id,
            'order_id' => $order_id
        ), 'DELETE');
    }
    
    /**
     * Get watchlists
     *
     * @return array|WP_Error Watchlists or error
     */
    public function get_watchlists() {
        return $this->make_request('watchlists');
    }
    
    /**
     * Get specific watchlist
     *
     * @param string $watchlist_id Watchlist ID
     * @return array|WP_Error Watchlist or error
     */
    public function get_watchlist($watchlist_id) {
        return $this->make_request('watchlist', array('watchlist_id' => $watchlist_id));
    }
    
    /**
     * Create watchlist
     *
     * @param string $name Watchlist name
     * @param string $symbols Initial symbols
     * @return array|WP_Error Watchlist or error
     */
    public function create_watchlist($name, $symbols = '') {
        $params = array('name' => $name);
        if ($symbols) $params['symbols'] = $symbols;
        
        return $this->make_request('create_watchlist', $params, 'POST');
    }
    
    /**
     * Make API request
     *
     * @param string $endpoint Endpoint name
     * @param array $params Request parameters
     * @param string $method HTTP method
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $params = array(), $method = 'GET') {
        if (!class_exists('TradePress_Tradier_Endpoints')) {
            require_once plugin_dir_path(__FILE__) . 'tradier-endpoints.php';
        }
        
        $url = TradePress_Tradier_Endpoints::get_endpoint_url($endpoint, $params, $this->api_base_url);
        
        if (empty($url)) {
            return new WP_Error('invalid_endpoint', 'Invalid endpoint specified');
        }
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Accept' => 'application/json',
                'User-Agent' => 'TradePress/1.0'
            )
        );
        
        // Add authorization header
        if (!empty($this->access_token)) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
        }
        
        // Add body for POST requests
        if ($method === 'POST' && !empty($params)) {
            // For POST requests, params go in body, not URL
            $url = TradePress_Tradier_Endpoints::get_endpoint_url($endpoint, array(), $this->api_base_url);
            $args['body'] = $params;
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code < 200 || $response_code >= 300) {
            return new WP_Error('http_error', 'HTTP Error: ' . $response_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode JSON response');
        }
        
        // Check for Tradier specific errors
        if (isset($data['fault'])) {
            return new WP_Error('api_error', $data['fault']['faultstring'] ?? 'API Error');
        }
        
        return $data;
    }
}
