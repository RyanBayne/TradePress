<?php
/**
 * TradePress WeBull API
 *
 * Handles connection and functionality for the WeBull trading platform
 * Based on WeBull API documentation and open-source implementations
 * 
 * Note: This class is used in conjunction with TradePress_Financial_API_Service 
 * which handles data normalization, caching, and scheduling. When implementing
 * methods in this class, remember that response formatting should follow standards
 * that can be parsed by the Financial API Service.
 * 
 * @package TradePress
 * @subpackage API\WeBull
 * @version 1.0.0
 * @since 2025-04-13
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('TradePress_Curl')) {
    require_once(trailingslashit(TRADEPRESS_PLUGIN_PATH) . 'api/curl.php');
}

if (!class_exists('TradePress_WeBull_Endpoints')) {
    require_once(trailingslashit(TRADEPRESS_PLUGIN_PATH) . 'api/webull/webull-endpoints.php');
}

/**
 * TradePress WeBull API Class
 */
class TradePress_WeBull_API {
    
    /**
     * Platform metadata
     * @var array
     */
    private $platform_metadata = array(
        'name' => 'Webull',
        'code' => 'webull',
        'type' => 'trading_platform',
        'tier' => 2,
        'status' => 'limited',
        'capabilities' => array(
            'trading' => true,
            'market_data' => true,
            'portfolio_management' => true,
            'order_management' => true,
            'account_data' => true,
            'historical_data' => true,
            'real_time_data' => true,
            'watchlists' => true,
            'extended_hours' => true,
            'fractional_shares' => true,
            'crypto_trading' => true,
            'paper_trading' => true
        ),
        'data_types' => array(
            'quotes' => true,
            'bars' => true,
            'trades' => true,
            'portfolio' => true,
            'orders' => true,
            'positions' => true,
            'account' => true,
            'watchlist' => true,
            'search' => true,
            'crypto' => true,
            'news' => false,
            'fundamentals' => false
        ),
        'rate_limits' => array(
            'requests_per_minute' => 60,
            'requests_per_hour' => 3600,
            'requests_per_day' => 86400
        ),
        'supported_markets' => array(
            'US' => true,
            'NASDAQ' => true,
            'NYSE' => true,
            'AMEX' => true,
            'OTC' => true,
            'CRYPTO' => true
        ),
        'pricing' => array(
            'free_tier' => true,
            'commission_free_stocks' => true,
            'commission_free_etfs' => true,
            'commission_free_crypto' => true,
            'account_minimums' => false
        )
    );
    
    /**
     * Quote API base URL
     *
     * @var string
     */
    private $quote_api_base_url = 'https://quoteapi.webull.com';
    
    /**
     * User API base URL
     *
     * @var string
     */
    private $user_api_base_url = 'https://userapi.webull.com';
    
    /**
     * Device ID
     *
     * @var string
     */
    private $device_id = '';
    
    /**
     * Access token
     *
     * @var string
     */
    private $access_token = '';
    
    /**
     * Refresh token
     *
     * @var string
     */
    private $refresh_token = '';
    
    /**
     * Trade token
     *
     * @var string
     */
    private $trade_token = '';
    
    /**
     * User ID
     *
     * @var string
     */
    private $user_id = '';
    
    /**
     * Account ID
     *
     * @var string
     */
    private $account_id = '';
    
    /**
     * Security Account ID
     *
     * @var string
     */
    private $sec_account_id = '';
    
    /**
     * Curl object for making API requests
     *
     * @var TradePress_Curl
     */
    private $curl_object;
    
    /**
     * Logger object for logging API interactions
     *
     * @var object
     */
    private $logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->curl_object = new TradePress_Curl();
        
        // Load credentials from options
        $this->load_credentials();
        
        // Set up logger if available
        if (class_exists('TradePress_API_Logging')) {
            $this->logger = new TradePress_API_Logging('webull');
        }
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
        if (empty($this->access_token) || defined('TRADEPRESS_DEMO_MODE')) {
            return $this->get_demo_data('get_quotes');
        }
        
        $search_result = $this->search_ticker($symbol);
        if (is_wp_error($search_result) || empty($search_result)) {
            return new WP_Error('symbol_not_found', 'Symbol not found');
        }
        
        $ticker_id = $search_result[0]['tickerId'];
        return $this->get_quotes(array($ticker_id));
    }
    
    /**
     * Get historical data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @param string $period Time period
     * @param string $interval Data interval
     * @return array|WP_Error Historical data or error
     */
    public function get_historical_data($symbol, $period = '1mo', $interval = 'd1') {
        if (empty($this->access_token) || defined('TRADEPRESS_DEMO_MODE')) {
            return array(
                array(
                    'timestamp' => time() - 86400,
                    'open' => 177.50,
                    'high' => 179.25,
                    'low' => 177.33,
                    'close' => 178.84,
                    'volume' => 1234567
                )
            );
        }
        
        $search_result = $this->search_ticker($symbol);
        if (is_wp_error($search_result) || empty($search_result)) {
            return new WP_Error('symbol_not_found', 'Symbol not found');
        }
        
        $ticker_id = $search_result[0]['tickerId'];
        return $this->get_bars($ticker_id, $interval, 100);
    }
    
    /**
     * Get account balance
     * 
     * @return array|WP_Error Account balance or error
     */
    public function get_account_balance() {
        if (empty($this->access_token) || defined('TRADEPRESS_DEMO_MODE')) {
            return $this->get_demo_data('get_account_values');
        }
        return $this->get_account_values();
    }
    
    /**
     * Get portfolio positions
     * 
     * @return array|WP_Error Portfolio positions or error
     */
    public function get_portfolio_positions() {
        if (empty($this->access_token) || defined('TRADEPRESS_DEMO_MODE')) {
            return $this->get_demo_data('get_positions');
        }
        return $this->get_positions();
    }
    
    /**
     * Get open orders
     * 
     * @return array|WP_Error Open orders or error
     */
    public function get_open_orders() {
        if (empty($this->access_token) || defined('TRADEPRESS_DEMO_MODE')) {
            return $this->get_demo_data('get_orders');
        }
        return $this->get_orders(null, null, 'Working');
    }
    
    /**
     * Search for symbols
     * 
     * @param string $query Search query
     * @return array|WP_Error Search results or error
     */
    public function search_symbols($query) {
        if (empty($this->access_token) || defined('TRADEPRESS_DEMO_MODE')) {
            return $this->get_demo_data('search_ticker');
        }
        return $this->search_ticker($query);
    }
    
    /**
     * Get watchlist symbols
     * 
     * @param string $watchlist_id Watchlist ID
     * @return array|WP_Error Watchlist symbols or error
     */
    public function get_watchlist_symbols($watchlist_id = null) {
        if (empty($this->access_token) || defined('TRADEPRESS_DEMO_MODE')) {
            return array(
                array('id' => 12345, 'name' => 'Default', 'tickerCount' => 5)
            );
        }
        
        if ($watchlist_id) {
            return $this->get_watchlist_items($watchlist_id);
        }
        return $this->get_watchlists();
    }
    
    /**
     * Load credentials from WordPress options
     */
    private function load_credentials() {
        $this->device_id = get_option('tradepress_webull_device_id', '');
        $this->access_token = get_option('tradepress_webull_access_token', '');
        $this->refresh_token = get_option('tradepress_webull_refresh_token', '');
        $this->trade_token = get_option('tradepress_webull_trade_token', '');
        $this->user_id = get_option('tradepress_webull_user_id', '');
        $this->account_id = get_option('tradepress_webull_account_id', '');
        $this->sec_account_id = get_option('tradepress_webull_sec_account_id', '');
    }
    
    /**
     * Save credentials to WordPress options
     */
    private function save_credentials() {
        update_option('tradepress_webull_device_id', $this->device_id);
        update_option('tradepress_webull_access_token', $this->access_token);
        update_option('tradepress_webull_refresh_token', $this->refresh_token);
        update_option('tradepress_webull_trade_token', $this->trade_token);
        update_option('tradepress_webull_user_id', $this->user_id);
        update_option('tradepress_webull_account_id', $this->account_id);
        update_option('tradepress_webull_sec_account_id', $this->sec_account_id);
    }
    
    /**
     * Generate a device ID
     *
     * @return string|WP_Error Device ID or error
     */
    public function generate_device_id() {
        $endpoint = '/api/user/getDeviceId';
        $url = $this->user_api_base_url . $endpoint;
        
        $response = $this->curl_object->get($url);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to generate device ID', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['id'])) {
            $this->device_id = $data['id'];
            $this->save_credentials();
            return $this->device_id;
        }
        
        return new WP_Error('invalid_response', 'Failed to parse device ID from response');
    }
    
    /**
     * Send login verification code
     *
     * @param string $account Email address or phone number
     * @param int $account_type Account type (2 for phone, 3 for email)
     * @return bool|WP_Error Success status or error
     */
    public function send_login_code($account, $account_type = 3) {
        if (empty($this->device_id)) {
            $device_id_result = $this->generate_device_id();
            if (is_wp_error($device_id_result)) {
                return $device_id_result;
            }
        }
        
        $endpoint = '/api/passport/account/getVerificationCode';
        $url = $this->user_api_base_url . $endpoint;
        
        $data = array(
            'account' => $account,
            'accountType' => $account_type,
            'deviceId' => $this->device_id
        );
        
        $response = $this->curl_object->post($url, json_encode($data), array(
            'Content-Type' => 'application/json'
        ));
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to send verification code', $response->get_error_message());
            return $response;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['success']) && $result['success'] === true) {
            return true;
        }
        
        return new WP_Error('verification_failed', 'Failed to send verification code', $result);
    }
    
    /**
     * Login to WeBull account
     *
     * @param string $account Email address or phone number
     * @param string $password Password (plaintext, will be MD5 encrypted)
     * @param int $account_type Account type (2 for phone, 3 for email)
     * @param string $code Verification code (if required)
     * @return bool|WP_Error Success status or error
     */
    public function login($account, $password, $account_type = 3, $code = '') {
        if (empty($this->device_id)) {
            $device_id_result = $this->generate_device_id();
            if (is_wp_error($device_id_result)) {
                return $device_id_result;
            }
        }
        
        $endpoint = '/api/passport/login/v5/account';
        $url = $this->user_api_base_url . $endpoint;
        
        // MD5 encrypt the password
        $md5_password = md5($password);
        
        $data = array(
            'account' => $account,
            'accountType' => $account_type,
            'password' => $md5_password,
            'deviceId' => $this->device_id,
            'deviceName' => 'TradePress',
        );
        
        if (!empty($code)) {
            $data['code'] = $code;
        }
        
        $response = $this->curl_object->post($url, json_encode($data), array(
            'Content-Type' => 'application/json'
        ));
        
        if (is_wp_error($response)) {
            $this->log_error('Login failed', $response->get_error_message());
            return $response;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['success']) && $result['success'] === true && isset($result['data']['accessToken'])) {
            // Save tokens and user info
            $this->access_token = $result['data']['accessToken'];
            $this->refresh_token = $result['data']['refreshToken'] ?? '';
            $this->user_id = $result['data']['uuid'] ?? '';
            
            $this->save_credentials();
            
            // Fetch account IDs
            $this->fetch_account_id();
            
            return true;
        }
        
        return new WP_Error('login_failed', 'Failed to login', $result);
    }
    
    /**
     * Fetch account ID after login
     *
     * @return bool|WP_Error Success status or error
     */
    private function fetch_account_id() {
        if (empty($this->access_token)) {
            return new WP_Error('no_auth', 'Not authenticated');
        }
        
        $endpoint = '/api/account/getSecAccountList/v4';
        $url = $this->user_api_base_url . $endpoint;
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token
        );
        
        $response = $this->curl_object->get($url, array(), $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to fetch account IDs', $response->get_error_message());
            return $response;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['success']) && $result['success'] === true && !empty($result['data'])) {
            foreach ($result['data'] as $account) {
                if (isset($account['secAccountId']) && isset($account['brokerId'])) {
                    // Save the first account found (typically there's only one)
                    $this->account_id = $account['accountId'] ?? '';
                    $this->sec_account_id = $account['secAccountId'];
                    $this->save_credentials();
                    return true;
                }
            }
        }
        
        return new WP_Error('account_fetch_failed', 'Failed to fetch account IDs', $result);
    }
    
    /**
     * Get ticker information
     *
     * @param int $ticker_id WeBull ticker ID
     * @return array|WP_Error Ticker information or error
     */
    public function get_ticker_info($ticker_id) {
        $endpoint = '/api/securities/ticker/v5/full';
        $url = $this->quote_api_base_url . $endpoint;
        
        $params = array(
            'tickerId' => $ticker_id
        );
        
        $response = $this->curl_object->get($url, $params);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get ticker info', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['tickerId'])) {
            return $data;
        }
        
        return new WP_Error('invalid_response', 'Failed to get ticker info', $data);
    }
    
    /**
     * Search for a ticker symbol
     *
     * @param string $keyword Search keyword (symbol or company name)
     * @param int $region_id Region ID (6 for US)
     * @return array|WP_Error Search results or error
     */
    public function search_ticker($keyword, $region_id = 6) {
        $endpoint = '/api/securities/v5/new-stock/query';
        $url = $this->quote_api_base_url . $endpoint;
        
        $params = array(
            'keyword' => $keyword,
            'regionId' => $region_id
        );
        
        $response = $this->curl_object->get($url, $params);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to search ticker', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }
        
        return new WP_Error('invalid_response', 'Failed to search ticker', $data);
    }
    
    /**
     * Get real-time quotes for multiple tickers
     *
     * @param array $ticker_ids Array of ticker IDs
     * @return array|WP_Error Quote data or error
     */
    public function get_quotes($ticker_ids) {
        if (!is_array($ticker_ids) || empty($ticker_ids)) {
            return new WP_Error('invalid_tickers', 'Invalid ticker IDs provided');
        }
        
        $endpoint = '/api/quote/v5/real-time';
        $url = $this->quote_api_base_url . $endpoint;
        
        $params = array(
            'ids' => implode(',', $ticker_ids)
        );
        
        $response = $this->curl_object->get($url, $params);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get quotes', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (is_array($data)) {
            // Format the response to match Financial API Service expectations
            $formatted_data = array();
            
            foreach ($data as $quote) {
                if (isset($quote['tickerId'])) {
                    $formatted_data[] = array(
                        'symbol' => $quote['symbol'] ?? '',
                        'price' => $quote['lastPrice'] ?? 0,
                        'change' => $quote['change'] ?? 0,
                        'change_percent' => $quote['changeRatio'] ?? 0,
                        'volume' => $quote['volume'] ?? 0,
                        'open' => $quote['open'] ?? 0,
                        'high' => $quote['high'] ?? 0,
                        'low' => $quote['low'] ?? 0,
                        'prev_close' => $quote['preClose'] ?? 0,
                        'bid' => $quote['bid'] ?? 0,
                        'ask' => $quote['ask'] ?? 0,
                        'source' => 'webull',
                        'timestamp' => time()
                    );
                }
            }
            
            return $formatted_data;
        }
        
        return new WP_Error('invalid_response', 'Failed to get quotes', $data);
    }
    
    /**
     * Get historical price bars for a ticker
     *
     * @param int $ticker_id Ticker ID
     * @param string $timeframe Bar timeframe (m1, m5, m15, m30, h1, h2, h4, d1, w1, mn1)
     * @param int $count Number of bars to return
     * @param bool $extended_hours Include extended hours data
     * @return array|WP_Error Bar data or error
     */
    public function get_bars($ticker_id, $timeframe = 'd1', $count = 100, $extended_hours = false) {
        $endpoint = '/api/quote/v5/kline';
        $url = $this->quote_api_base_url . $endpoint;
        
        $params = array(
            'tickerId' => $ticker_id,
            'type' => $timeframe,
            'count' => $count,
            'extendTrading' => $extended_hours ? 1 : 0
        );
        
        $response = $this->curl_object->get($url, $params);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get bars', $response->get_error_message());
            return $response;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['data']) && is_array($result['data'])) {
            // Format the response to match Financial API Service expectations
            $formatted_data = array();
            
            foreach ($result['data'] as $bar) {
                if (isset($bar['t'])) {
                    $formatted_data[] = array(
                        'timestamp' => floor($bar['t'] / 1000), // Convert from milliseconds to seconds
                        'open' => $bar['o'] ?? 0,
                        'high' => $bar['h'] ?? 0,
                        'low' => $bar['l'] ?? 0,
                        'close' => $bar['c'] ?? 0,
                        'volume' => $bar['v'] ?? 0
                    );
                }
            }
            
            return $formatted_data;
        }
        
        return new WP_Error('invalid_response', 'Failed to get bars', $result);
    }
    
    /**
     * Get account information
     *
     * @return array|WP_Error Account information or error
     */
    public function get_account_info() {
        if (empty($this->access_token) || empty($this->account_id)) {
            return new WP_Error('no_auth', 'Not authenticated or missing account ID');
        }
        
        $endpoint = '/api/account/v5/account';
        $url = $this->user_api_base_url . $endpoint;
        
        $params = array(
            'accountId' => $this->account_id
        );
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token
        );
        
        $response = $this->curl_object->get($url, $params, $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get account info', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['id'])) {
            return $data;
        }
        
        return new WP_Error('invalid_response', 'Failed to get account info', $data);
    }
    
    /**
     * Get account balance and values
     *
     * @return array|WP_Error Account values or error
     */
    public function get_account_values() {
        if (empty($this->access_token) || empty($this->sec_account_id)) {
            return new WP_Error('no_auth', 'Not authenticated or missing security account ID');
        }
        
        $endpoint = '/api/v2/home/account';
        $url = $this->user_api_base_url . $endpoint;
        
        $params = array(
            'secAccountId' => $this->sec_account_id
        );
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token
        );
        
        $response = $this->curl_object->get($url, $params, $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get account values', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['netLiquidation'])) {
            // Format the response to match Financial API Service expectations
            return array(
                'account_value' => $data['netLiquidation'] ?? 0,
                'buying_power' => $data['dayBuyingPower'] ?? 0,
                'cash' => $data['cash'] ?? 0,
                'currency' => $data['currency'] ?? 'USD'
            );
        }
        
        return new WP_Error('invalid_response', 'Failed to get account values', $data);
    }
    
    /**
     * Get current positions
     *
     * @return array|WP_Error Positions or error
     */
    public function get_positions() {
        if (empty($this->access_token) || empty($this->account_id) || empty($this->sec_account_id)) {
            return new WP_Error('no_auth', 'Not authenticated or missing account IDs');
        }
        
        $endpoint = '/api/account/v5/positions';
        $url = $this->user_api_base_url . $endpoint;
        
        $params = array(
            'accountId' => $this->account_id,
            'secAccountId' => $this->sec_account_id
        );
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token
        );
        
        $response = $this->curl_object->get($url, $params, $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get positions', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['positions']) && is_array($data['positions'])) {
            // Format the response to match Financial API Service expectations
            $formatted_positions = array();
            
            foreach ($data['positions'] as $position) {
                if (isset($position['ticker']) && isset($position['position'])) {
                    $formatted_positions[] = array(
                        'symbol' => $position['ticker']['symbol'] ?? '',
                        'quantity' => $position['position'] ?? 0,
                        'avg_price' => $position['avgPrice'] ?? 0,
                        'market_value' => $position['marketValue'] ?? 0,
                        'cost_basis' => $position['costPrice'] ?? 0,
                        'unrealized_pl' => $position['unrealizedProfitLoss'] ?? 0,
                        'unrealized_pl_percent' => $position['unrealizedProfitLossRate'] ?? 0,
                        'ticker_id' => $position['ticker']['tickerId'] ?? 0
                    );
                }
            }
            
            return $formatted_positions;
        }
        
        return new WP_Error('invalid_response', 'Failed to get positions', $data);
    }
    
    /**
     * Get order history
     *
     * @param int $start_time Start timestamp (milliseconds)
     * @param int $end_time End timestamp (milliseconds)
     * @param string $status Order status filter
     * @return array|WP_Error Orders or error
     */
    public function get_orders($start_time = null, $end_time = null, $status = null) {
        if (empty($this->access_token) || empty($this->sec_account_id)) {
            return new WP_Error('no_auth', 'Not authenticated or missing security account ID');
        }
        
        $endpoint = '/api/trades/v5/all-orders';
        $url = $this->user_api_base_url . $endpoint;
        
        $params = array(
            'secAccountId' => $this->sec_account_id
        );
        
        if ($start_time) {
            $params['startTime'] = $start_time;
        }
        
        if ($end_time) {
            $params['endTime'] = $end_time;
        }
        
        if ($status) {
            $params['status'] = $status;
        }
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token
        );
        
        $response = $this->curl_object->get($url, $params, $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get orders', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['data']) && is_array($data['data'])) {
            // Format the response to match Financial API Service expectations
            $formatted_orders = array();
            
            foreach ($data['data'] as $order) {
                if (isset($order['orderId'])) {
                    $formatted_orders[] = array(
                        'id' => $order['orderId'] ?? '',
                        'symbol' => $order['ticker']['symbol'] ?? '',
                        'side' => $order['action'] ?? '',
                        'type' => $order['orderType'] ?? '',
                        'quantity' => $order['quantity'] ?? 0,
                        'filled_quantity' => $order['filledQuantity'] ?? 0,
                        'price' => $order['price'] ?? 0,
                        'avg_fill_price' => $order['avgFilledPrice'] ?? 0,
                        'status' => $order['status'] ?? '',
                        'created_at' => isset($order['placedTime']) ? floor($order['placedTime'] / 1000) : 0,
                        'filled_at' => isset($order['filledTime']) ? floor($order['filledTime'] / 1000) : 0,
                        'time_in_force' => $order['timeInForce'] ?? ''
                    );
                }
            }
            
            return $formatted_orders;
        }
        
        return new WP_Error('invalid_response', 'Failed to get orders', $data);
    }
    
    /**
     * Place an order
     *
     * @param array $order_data Order parameters
     * @return array|WP_Error Order result or error
     */
    public function place_order($order_data) {
        if (empty($this->access_token) || empty($this->sec_account_id)) {
            return new WP_Error('no_auth', 'Not authenticated or missing security account ID');
        }
        
        // Check for required trade token
        if (empty($this->trade_token)) {
            return new WP_Error('no_trade_token', 'Trading requires a trade token');
        }
        
        // Validate required fields
        $required_fields = array('tickerId', 'action', 'orderType', 'timeInForce', 'quantity');
        foreach ($required_fields as $field) {
            if (!isset($order_data[$field]) || empty($order_data[$field])) {
                return new WP_Error('missing_field', "Missing required field: $field");
            }
        }
        
        $endpoint = '/api/trades/v5/orders/place';
        $url = $this->user_api_base_url . $endpoint;
        
        // Add security account ID to order data
        $order_data['secAccountId'] = $this->sec_account_id;
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token,
            'td_token' => $this->trade_token
        );
        
        $response = $this->curl_object->post($url, json_encode($order_data), $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to place order', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['success']) && $data['success'] === true && isset($data['orderId'])) {
            return array(
                'id' => $data['orderId'],
                'status' => $data['status'] ?? 'Working',
                'ticker_id' => $order_data['tickerId'],
                'symbol' => $data['ticker']['symbol'] ?? ''
            );
        }
        
        return new WP_Error('order_failed', 'Failed to place order', $data);
    }
    
    /**
     * Cancel an order
     *
     * @param string $order_id Order ID to cancel
     * @return bool|WP_Error Success status or error
     */
    public function cancel_order($order_id) {
        if (empty($this->access_token) || empty($this->sec_account_id)) {
            return new WP_Error('no_auth', 'Not authenticated or missing security account ID');
        }
        
        // Check for required trade token
        if (empty($this->trade_token)) {
            return new WP_Error('no_trade_token', 'Trading requires a trade token');
        }
        
        $endpoint = '/api/trades/v5/orders/cancel';
        $url = $this->user_api_base_url . $endpoint;
        
        $data = array(
            'secAccountId' => $this->sec_account_id,
            'orderId' => $order_id
        );
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token,
            'td_token' => $this->trade_token
        );
        
        $response = $this->curl_object->post($url, json_encode($data), $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to cancel order', $response->get_error_message());
            return $response;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['success']) && $result['success'] === true) {
            return true;
        }
        
        return new WP_Error('cancel_failed', 'Failed to cancel order', $result);
    }
    
    /**
     * Get all watchlists
     *
     * @return array|WP_Error Watchlists or error
     */
    public function get_watchlists() {
        if (empty($this->access_token)) {
            return new WP_Error('no_auth', 'Not authenticated');
        }
        
        $endpoint = '/api/wlas/watchlists';
        $url = $this->user_api_base_url . $endpoint;
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token
        );
        
        $response = $this->curl_object->get($url, array(), $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get watchlists', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }
        
        return new WP_Error('invalid_response', 'Failed to get watchlists', $data);
    }
    
    /**
     * Get items in a watchlist
     *
     * @param int $watchlist_id Watchlist ID
     * @return array|WP_Error Watchlist items or error
     */
    public function get_watchlist_items($watchlist_id) {
        if (empty($this->access_token)) {
            return new WP_Error('no_auth', 'Not authenticated');
        }
        
        $endpoint = '/api/wlas/watchlists/' . $watchlist_id;
        $url = $this->user_api_base_url . $endpoint;
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token
        );
        
        $response = $this->curl_object->get($url, array(), $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to get watchlist items', $response->get_error_message());
            return $response;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['id']) && isset($data['tickers'])) {
            return $data;
        }
        
        return new WP_Error('invalid_response', 'Failed to get watchlist items', $data);
    }
    
    /**
     * Add a ticker to a watchlist
     *
     * @param int $watchlist_id Watchlist ID
     * @param int $ticker_id Ticker ID to add
     * @return bool|WP_Error Success status or error
     */
    public function add_to_watchlist($watchlist_id, $ticker_id) {
        if (empty($this->access_token)) {
            return new WP_Error('no_auth', 'Not authenticated');
        }
        
        $endpoint = '/api/wlas/watchlists/' . $watchlist_id . '/tickers';
        $url = $this->user_api_base_url . $endpoint;
        
        $data = array(
            'tickerId' => $ticker_id
        );
        
        $headers = array(
            'Content-Type' => 'application/json',
            'did' => $this->device_id,
            'access_token' => $this->access_token
        );
        
        $response = $this->curl_object->post($url, json_encode($data), $headers);
        
        if (is_wp_error($response)) {
            $this->log_error('Failed to add to watchlist', $response->get_error_message());
            return $response;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['success']) && $result['success'] === true) {
            return true;
        }
        
        return new WP_Error('add_failed', 'Failed to add to watchlist', $result);
    }
    
    /**
     * Format data for TradePress Financial API Service
     *
     * @param string $endpoint Endpoint being accessed
     * @param array $data Raw data from API
     * @return array Formatted data for TradePress
     */
    public function format_for_financial_service($endpoint, $data) {
        // Implementation depends on specific requirements of TradePress_Financial_API_Service
        // This is a placeholder for future implementation
        return $data;
    }
    
    /**
     * Get demo data for endpoints when not authenticated
     * 
     * @param string $method Method being called
     * @return array Sample data
     */
    private function get_demo_data($method) {
        switch ($method) {
            case 'get_account_values':
                return array(
                    'account_value' => 50000.00,
                    'buying_power' => 100000.00,
                    'cash' => 25000.00,
                    'currency' => 'USD'
                );
                
            case 'get_positions':
                return array(
                    array(
                        'symbol' => 'AAPL',
                        'quantity' => 100,
                        'avg_price' => 150.25,
                        'market_value' => 17886.00,
                        'cost_basis' => 15025.00,
                        'unrealized_pl' => 2861.00,
                        'unrealized_pl_percent' => 0.1904
                    )
                );
                
            case 'get_orders':
                return array(
                    array(
                        'id' => '123456789',
                        'symbol' => 'MSFT',
                        'side' => 'BUY',
                        'type' => 'LMT',
                        'quantity' => 25,
                        'price' => 250.00,
                        'status' => 'Working'
                    )
                );
                
            case 'search_ticker':
                return array(
                    array(
                        'tickerId' => 913256135,
                        'symbol' => 'AAPL',
                        'name' => 'Apple Inc'
                    )
                );
                
            case 'get_quotes':
                return array(
                    array(
                        'symbol' => 'AAPL',
                        'price' => 178.84,
                        'change' => 0.18,
                        'change_percent' => 0.1,
                        'volume' => 1234567
                    )
                );
                
            default:
                return array();
        }
    }
    
    /**
     * Log error to TradePress logging system
     *
     * @param string $message Error message
     * @param mixed $details Additional error details
     */
    private function log_error($message, $details = '') {
        if ($this->logger) {
            $error_data = array(
                'message' => $message,
                'details' => $details
            );
            $this->logger->log_error($error_data);
        }
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
            'search' => true,
            'crypto_trading' => true,
            'extended_hours' => true,
            'fractional_shares' => true
        );
    }
}