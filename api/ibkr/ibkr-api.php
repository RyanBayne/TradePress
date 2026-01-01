<?php
/**
 * TradePress Interactive Brokers API
 *
 * Handles connection and functionality for the Interactive Brokers trading platform
 *
 * @package TradePress
 * @subpackage API\IBKR
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Interactive Brokers API class
 */
class TradePress_IBKR_API {
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'http://localhost:5000';
    
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
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'Interactive Brokers',
        'code' => 'ibkr',
        'type' => 'broker_trading',
        'tier' => 2,
        'status' => 'active',
        'capabilities' => array(
            'trading' => true,
            'paper_trading' => true,
            'real_time_data' => true,
            'historical_data' => true,
            'portfolio_management' => true,
            'order_management' => true,
            'account_data' => true,
            'market_data' => true,
            'options_trading' => true,
            'futures_trading' => true,
            'forex_trading' => true,
            'bonds_trading' => true,
            'mutual_funds' => true,
            'etf_trading' => true,
            'international_markets' => true,
            'margin_trading' => true,
            'short_selling' => true,
            'algorithmic_trading' => true
        ),
        'data_types' => array(
            'account' => 'portfolio_summary',
            'positions' => 'portfolio_positions',
            'orders' => 'order_status',
            'pnl' => 'account_pnl',
            'ledger' => 'account_ledger',
            'quote' => 'market_data_snapshot',
            'bars' => 'market_data_history',
            'volume' => 'market_data_history',
            'historical' => 'market_data_history',
            'contract_search' => 'contract_search',
            'contract_details' => 'contract_details',
            'auth_status' => 'auth_status'
        ),
        'rate_limits' => array(
            'general_per_minute' => 180,
            'market_data_per_minute' => 100,
            'orders_per_minute' => 50,
            'portfolio_per_minute' => 60,
            'burst' => 10
        ),
        'supported_markets' => array('US', 'Europe', 'Asia', 'Global'),
        'pricing' => array(
            'account_minimum' => 0,
            'commission_stocks' => 0.005,
            'commission_options' => 0.65,
            'data_fees' => 'varies'
        )
    );
    
    /**
     * Constructor
     *
     * @param string $access_token Access token
     * @param string $account_id Account ID
     * @param string $base_url Base URL (default: localhost gateway)
     */
    public function __construct($access_token = '', $account_id = '', $base_url = '') {
        $this->access_token = $access_token;
        $this->account_id = $account_id ?: get_option('tradepress_ibkr_account_id', '');
        
        if ($base_url) {
            $this->api_base_url = $base_url;
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
     * Check authentication status
     *
     * @return array|WP_Error Auth status or error
     */
    public function check_auth_status() {
        return $this->make_request('auth_status');
    }
    
    /**
     * Get portfolio accounts
     *
     * @return array|WP_Error Accounts or error
     */
    public function get_accounts() {
        return $this->make_request('portfolio_accounts');
    }
    
    /**
     * Get account summary
     *
     * @param string $account_id Account ID
     * @return array|WP_Error Account summary or error
     */
    public function get_account($account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('portfolio_summary', array('accountId' => $account_id));
    }
    
    /**
     * Get positions
     *
     * @param string $account_id Account ID
     * @param int $page_id Page number
     * @return array|WP_Error Positions or error
     */
    public function get_positions($account_id = '', $page_id = 0) {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('portfolio_positions', array('accountId' => $account_id, 'pageId' => $page_id));
    }
    
    /**
     * Get P&L
     *
     * @param string $account_id Account ID
     * @return array|WP_Error P&L data or error
     */
    public function get_pnl($account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('account_pnl', array('accountId' => $account_id));
    }
    
    /**
     * Get account ledger
     *
     * @param string $account_id Account ID
     * @return array|WP_Error Ledger data or error
     */
    public function get_ledger($account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('account_ledger', array('accountId' => $account_id));
    }
    
    /**
     * Get quote data (market snapshot)
     *
     * @param array $contract_ids Contract IDs
     * @param array $fields Field codes
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($contract_ids, $fields = array()) {
        $conids = is_array($contract_ids) ? implode(',', $contract_ids) : $contract_ids;
        $params = array('conids' => $conids);
        
        if (!empty($fields)) {
            $params['fields'] = is_array($fields) ? implode(',', $fields) : $fields;
        }
        
        return $this->make_request('market_data_snapshot', $params);
    }
    
    /**
     * Get historical bars
     *
     * @param int $contract_id Contract ID
     * @param string $period Time period
     * @param string $bar Bar size
     * @return array|WP_Error Historical data or error
     */
    public function get_bars($contract_id, $period = '1m', $bar = '1d') {
        return $this->make_request('market_data_history', array(
            'conid' => $contract_id,
            'period' => $period,
            'bar' => $bar
        ));
    }
    
    /**
     * Search for contracts
     *
     * @param string $symbol Symbol to search
     * @param string $sec_type Security type
     * @param int $limit Result limit
     * @return array|WP_Error Search results or error
     */
    public function search_contracts($symbol, $sec_type = 'STK', $limit = 10) {
        return $this->make_request('contract_search', array(
            'symbol' => $symbol,
            'secType' => $sec_type,
            'limit' => $limit
        ), 'POST');
    }
    
    /**
     * Get contract details
     *
     * @param int $contract_id Contract ID
     * @return array|WP_Error Contract details or error
     */
    public function get_contract_details($contract_id) {
        return $this->make_request('contract_details', array('conid' => $contract_id));
    }
    
    /**
     * Place order
     *
     * @param array $order_data Order parameters
     * @param string $account_id Account ID
     * @return array|WP_Error Order result or error
     */
    public function place_order($order_data, $account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        $order_data['accountId'] = $account_id;
        
        return $this->make_request('order_place', $order_data, 'POST');
    }
    
    /**
     * Get order status
     *
     * @param string $account_id Account ID
     * @return array|WP_Error Orders or error
     */
    public function get_orders($account_id = '') {
        $account_id = $account_id ?: $this->account_id;
        return $this->make_request('order_status', array('accountId' => $account_id));
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
        return $this->make_request('order_cancel', array(
            'accountId' => $account_id,
            'orderId' => $order_id
        ), 'DELETE');
    }
    
    /**
     * Logout
     *
     * @return array|WP_Error Logout result or error
     */
    public function logout() {
        return $this->make_request('auth_logout', array(), 'POST');
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
        if (!class_exists('TradePress_IBKR_Endpoints')) {
            require_once plugin_dir_path(__FILE__) . 'ibkr-endpoints.php';
        }
        
        $url = TradePress_IBKR_Endpoints::get_endpoint_url($endpoint, $params, $this->api_base_url);
        
        if (empty($url)) {
            return new WP_Error('invalid_endpoint', 'Invalid endpoint specified');
        }
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'TradePress/1.0'
            )
        );
        
        // Add authorization header if token is available
        if (!empty($this->access_token)) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
        }
        
        // Add body for POST requests
        if ($method === 'POST' && !empty($params)) {
            // For POST requests, params go in body, not URL
            $url = TradePress_IBKR_Endpoints::get_endpoint_url($endpoint, array(), $this->api_base_url);
            $args['body'] = json_encode($params);
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
        
        // Check for IBKR specific errors
        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']);
        }
        
        return $data;
    }
}
