<?php
/**
 * TradePress Fidelity API
 *
 * Handles connection and functionality for the Fidelity trading platform
 *
 * @package TradePress
 * @subpackage API\Fidelity
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Fidelity API class
 */
class TradePress_Fidelity_API {
    
    /**
     * Platform metadata
     * @var array
     */
    private $platform_metadata = array(
        'name' => 'Fidelity',
        'code' => 'fidelity',
        'type' => 'trading_platform',
        'tier' => 2,
        'status' => 'active',
        'capabilities' => array(
            'trading' => true,
            'market_data' => true,
            'portfolio_management' => true,
            'order_management' => true,
            'account_data' => true,
            'historical_data' => true,
            'real_time_data' => true,
            'options_trading' => true,
            'mutual_funds' => true,
            'etfs' => true,
            'bonds' => true,
            'watchlists' => true,
            'fundamentals' => true,
            'research' => true,
            'retirement_accounts' => true
        ),
        'data_types' => array(
            'quotes' => true,
            'bars' => true,
            'trades' => true,
            'portfolio' => true,
            'orders' => true,
            'positions' => true,
            'account' => true,
            'fundamentals' => true,
            'options' => true,
            'mutual_funds' => true,
            'bonds' => true,
            'news' => false,
            'crypto' => false
        ),
        'rate_limits' => array(
            'requests_per_minute' => 120,
            'requests_per_hour' => 7200,
            'requests_per_day' => 172800
        ),
        'supported_markets' => array(
            'US' => true,
            'NASDAQ' => true,
            'NYSE' => true,
            'AMEX' => true,
            'OTC' => true,
            'MUTUAL_FUNDS' => true,
            'OPTIONS' => true,
            'BONDS' => true
        ),
        'pricing' => array(
            'free_tier' => false,
            'paid_plans' => true,
            'commission_free_stocks' => true,
            'commission_free_etfs' => true,
            'options_fees' => true,
            'mutual_fund_fees' => false,
            'account_minimums' => false
        )
    );
    
    /**
     * API base URL for production
     *
     * @var string
     */
    private $api_base_url = 'https://api.fidelity.com';
    
    /**
     * API base URL for sandbox/paper trading
     *
     * @var string
     */
    private $api_sandbox_url = 'https://api-sandbox.fidelity.com';
    
    /**
     * API key
     * @var string
     */
    private $api_key;
    
    /**
     * Access token
     * @var string
     */
    private $access_token;
    
    /**
     * Debug mode
     * @var bool
     */
    private $debug_mode;
    
    /**
     * Constructor
     */
    public function __construct() {
        $api_settings = get_option('tradepress_api_settings', array());
        $this->api_key = isset($api_settings['fidelity_api_key']) ? $api_settings['fidelity_api_key'] : '';
        $this->access_token = isset($api_settings['fidelity_access_token']) ? $api_settings['fidelity_access_token'] : '';
        $this->debug_mode = isset($api_settings['enable_api_logging']) && $api_settings['enable_api_logging'];
    }
    
    /**
     * Get platform metadata
     * 
     * @return array Platform metadata
     */
    public function get_platform_metadata() {
        return $this->platform_metadata;
    }
    
    /**
     * Get quote data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        return $this->make_request("market/quotes/{$symbol}");
    }
    
    /**
     * Get historical data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @param string $period Time period (1d, 5d, 1mo, etc.)
     * @param string $interval Data interval (1m, 5m, 1h, 1d)
     * @return array|WP_Error Historical data or error
     */
    public function get_historical_data($symbol, $period = '1mo', $interval = '1d') {
        $params = array(
            'period' => $period,
            'interval' => $interval
        );
        return $this->make_request("market/history/{$symbol}", 'GET', $params);
    }
    
    /**
     * Get account balance
     * 
     * @param string $account_id Account ID
     * @return array|WP_Error Account balance or error
     */
    public function get_account_balance($account_id = null) {
        if (!$account_id) {
            $accounts = $this->get_accounts();
            if (is_wp_error($accounts) || empty($accounts['accounts'])) {
                return new WP_Error('no_accounts', 'No accounts found');
            }
            $account_id = $accounts['accounts'][0]['account_number'];
        }
        return $this->make_request("accounts/{$account_id}/balances");
    }
    
    /**
     * Get portfolio positions
     * 
     * @param string $account_id Account ID
     * @return array|WP_Error Portfolio positions or error
     */
    public function get_portfolio_positions($account_id = null) {
        if (!$account_id) {
            $accounts = $this->get_accounts();
            if (is_wp_error($accounts) || empty($accounts['accounts'])) {
                return new WP_Error('no_accounts', 'No accounts found');
            }
            $account_id = $accounts['accounts'][0]['account_number'];
        }
        return $this->make_request("accounts/{$account_id}/positions");
    }
    
    /**
     * Get open orders
     * 
     * @param string $account_id Account ID
     * @return array|WP_Error Open orders or error
     */
    public function get_open_orders($account_id = null) {
        if (!$account_id) {
            $accounts = $this->get_accounts();
            if (is_wp_error($accounts) || empty($accounts['accounts'])) {
                return new WP_Error('no_accounts', 'No accounts found');
            }
            $account_id = $accounts['accounts'][0]['account_number'];
        }
        return $this->make_request("accounts/{$account_id}/orders", 'GET', array('status' => 'OPEN'));
    }
    
    /**
     * Search for symbols
     * 
     * @param string $query Search query
     * @return array|WP_Error Search results or error
     */
    public function search_symbols($query) {
        return $this->make_request('market/instruments/search', 'GET', array('query' => $query));
    }
    
    /**
     * Get watchlist symbols
     * 
     * @param string $account_id Account ID
     * @param string $watchlist_id Watchlist ID
     * @return array|WP_Error Watchlist symbols or error
     */
    public function get_watchlist_symbols($account_id = null, $watchlist_id = null) {
        if (!$account_id) {
            $accounts = $this->get_accounts();
            if (is_wp_error($accounts) || empty($accounts['accounts'])) {
                return new WP_Error('no_accounts', 'No accounts found');
            }
            $account_id = $accounts['accounts'][0]['account_number'];
        }
        
        if ($watchlist_id) {
            return $this->make_request("accounts/{$account_id}/watchlists/{$watchlist_id}");
        }
        return $this->make_request("accounts/{$account_id}/watchlists");
    }
    
    /**
     * Get account information
     *
     * @return array|WP_Error Account information or error
     */
    public function get_accounts() {
        return $this->make_request('accounts');
    }
    
    /**
     * Get positions
     *
     * @param string $account_id Account ID
     * @return array|WP_Error Positions or error
     */
    public function get_positions($account_id = null) {
        return $this->get_portfolio_positions($account_id);
    }
    
    /**
     * Place a market order
     * 
     * @param string $account_id Account ID
     * @param string $symbol Stock symbol
     * @param float $quantity Quantity to trade
     * @param string $side Order side (BUY/SELL)
     * @return array|WP_Error Order response or error
     */
    public function place_market_order($account_id, $symbol, $quantity, $side = 'BUY') {
        $params = array(
            'symbol' => $symbol,
            'quantity' => $quantity,
            'side' => strtoupper($side),
            'order_type' => 'MARKET',
            'time_in_force' => 'DAY'
        );
        return $this->make_request("accounts/{$account_id}/orders", 'POST', $params);
    }
    
    /**
     * Cancel an order
     * 
     * @param string $account_id Account ID
     * @param string $order_id Order ID to cancel
     * @return array|WP_Error Cancel response or error
     */
    public function cancel_order($account_id, $order_id) {
        return $this->make_request("accounts/{$account_id}/orders/{$order_id}", 'DELETE');
    }
    
    /**
     * Make API request to Fidelity
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $params Request parameters
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $method = 'GET', $params = array()) {
        // Demo mode - return sample data
        if (empty($this->access_token) || defined('TRADEPRESS_DEMO_MODE')) {
            return $this->get_demo_data($endpoint);
        }
        
        $url = $this->api_base_url . '/api/v1/' . $endpoint;
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json'
            )
        );
        
        if ($method === 'GET' && !empty($params)) {
            $url = add_query_arg($params, $url);
        } elseif (!empty($params)) {
            $args['body'] = json_encode($params);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code < 200 || $response_code >= 300) {
            return new WP_Error(
                'fidelity_api_error',
                sprintf(__('Fidelity API error: %s', 'tradepress'), $response_body),
                array('status' => $response_code)
            );
        }
        
        $data = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_parse_error',
                __('Failed to parse Fidelity API response', 'tradepress')
            );
        }
        
        return $data;
    }
    
    /**
     * Get demo data for endpoints
     * 
     * @param string $endpoint API endpoint
     * @return array Sample data
     */
    private function get_demo_data($endpoint) {
        if (strpos($endpoint, 'accounts') === 0) {
            if ($endpoint === 'accounts') {
                return array(
                    'accounts' => array(
                        array(
                            'account_number' => 'Z12345678',
                            'account_name' => 'Individual Investment Account',
                            'account_type' => 'INDIVIDUAL',
                            'cash_balance' => 25420.75,
                            'total_value' => 187650.25
                        )
                    )
                );
            }
            
            if (strpos($endpoint, 'positions') !== false) {
                return array(
                    'positions' => array(
                        array(
                            'symbol' => 'AAPL',
                            'quantity' => 150,
                            'market_value' => 23679.00,
                            'price' => 157.86
                        )
                    )
                );
            }
        }
        
        if (strpos($endpoint, 'market/quotes') === 0) {
            return array(
                'symbol' => 'AAPL',
                'last_price' => 157.86,
                'change' => 2.35,
                'change_percent' => 1.51,
                'volume' => 42589631
            );
        }
        
        return array();
    }
    
    /**
     * Get platform capabilities for directive support
     * 
     * @return array Platform capabilities
     */
    public function get_platform_capabilities() {
        return array(
            'D1_ADX' => false,
            'D17_RSI' => false,
            'D22_Volume' => true,
            'D4_CCI' => false,
            'D10_MACD' => false,
            'quotes' => true,
            'historical_data' => true,
            'portfolio_data' => true,
            'trading' => true,
            'order_management' => true,
            'account_data' => true,
            'watchlists' => true,
            'fundamentals' => true,
            'options_trading' => true
        );
    }
}
