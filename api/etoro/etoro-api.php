<?php
/**
 * TradePress eToro API
 *
 * Handles connection and functionality for the eToro trading platform
 *
 * @package TradePress
 * @subpackage API\eToro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress eToro API class
 */
class TradePress_Etoro_API {
    
    /**
     * Platform metadata
     * @var array
     */
    private $platform_metadata = array(
        'name' => 'eToro',
        'code' => 'etoro',
        'type' => 'social_trading_platform',
        'tier' => 2,
        'status' => 'limited',
        'capabilities' => array(
            'social_trading' => true,
            'copy_trading' => true,
            'trading' => true,
            'market_data' => true,
            'portfolio_management' => true,
            'position_management' => true,
            'watchlists' => true,
            'crypto_trading' => true,
            'forex_trading' => true,
            'cfd_trading' => true,
            'stocks_trading' => true,
            'etf_trading' => true,
            'commodities_trading' => true,
            'leverage_trading' => true,
            'demo_account' => true
        ),
        'data_types' => array(
            'quotes' => true,
            'positions' => true,
            'portfolio' => true,
            'watchlist' => true,
            'account' => true,
            'social_data' => true,
            'copy_trades' => true,
            'crypto' => true,
            'forex' => true,
            'cfds' => true,
            'stocks' => true,
            'etfs' => true,
            'commodities' => true,
            'news' => false,
            'fundamentals' => false
        ),
        'rate_limits' => array(
            'requests_per_minute' => 60,
            'requests_per_hour' => 3600,
            'requests_per_day' => 86400
        ),
        'supported_markets' => array(
            'CRYPTO' => true,
            'FOREX' => true,
            'STOCKS' => true,
            'ETFS' => true,
            'COMMODITIES' => true,
            'INDICES' => true,
            'CFDS' => true
        ),
        'pricing' => array(
            'free_tier' => false,
            'paid_plans' => true,
            'commission_free_stocks' => true,
            'spread_based' => true,
            'overnight_fees' => true,
            'withdrawal_fees' => true,
            'minimum_deposit' => 200
        )
    );
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://api.etoro.com';
    
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
        $this->access_token = isset($api_settings['etoro_access_token']) ? $api_settings['etoro_access_token'] : '';
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
     * @param string $symbol Stock symbol or instrument ID
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        // Search for instrument first to get ID
        $search = $this->search_symbols($symbol);
        if (is_wp_error($search) || empty($search['results'])) {
            return new WP_Error('symbol_not_found', 'Symbol not found');
        }
        
        $instrument_id = $search['results'][0]['instrumentId'];
        return $this->make_request("market/instrument/{$instrument_id}");
    }
    
    /**
     * Get historical data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @param string $period Time period (not supported by eToro API)
     * @param string $interval Data interval (not supported by eToro API)
     * @return array|WP_Error Historical data or error
     */
    public function get_historical_data($symbol, $period = '1mo', $interval = '1d') {
        // eToro API doesn't provide historical data endpoints
        return new WP_Error('not_supported', 'Historical data not available via eToro API');
    }
    
    /**
     * Get account balance
     * 
     * @return array|WP_Error Account balance or error
     */
    public function get_account_balance() {
        return $this->make_request('user/accounts');
    }
    
    /**
     * Get portfolio positions
     * 
     * @return array|WP_Error Portfolio positions or error
     */
    public function get_portfolio_positions() {
        return $this->make_request('positions');
    }
    
    /**
     * Get open orders (positions in eToro)
     * 
     * @return array|WP_Error Open positions or error
     */
    public function get_open_orders() {
        return $this->get_portfolio_positions();
    }
    
    /**
     * Search for symbols
     * 
     * @param string $query Search query
     * @return array|WP_Error Search results or error
     */
    public function search_symbols($query) {
        return $this->make_request('market/search', 'GET', array('query' => $query));
    }
    
    /**
     * Get watchlist symbols
     * 
     * @return array|WP_Error Watchlist symbols or error
     */
    public function get_watchlist_symbols() {
        return $this->make_request('watchlist');
    }
    
    /**
     * Get account information
     *
     * @return array|WP_Error Account information or error
     */
    public function get_accounts() {
        return $this->make_request('user/accounts');
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
     * Open a position (eToro's version of placing an order)
     * 
     * @param string $symbol Asset symbol or name
     * @param float $amount Amount to invest
     * @param string $type Position type (BUY/SELL)
     * @param float $leverage Leverage multiplier
     * @param array $options Additional options (stop loss, take profit)
     * @return array|WP_Error Position response or error
     */
    public function open_position($symbol, $amount, $type = 'BUY', $leverage = 1, $options = array()) {
        $params = array(
            'name' => $symbol,
            'type' => strtoupper($type),
            'amount' => $amount,
            'leverage' => $leverage
        );
        
        if (isset($options['stop_loss'])) {
            $params['stopLossRate'] = $options['stop_loss'];
        }
        
        if (isset($options['take_profit'])) {
            $params['takeProfitRate'] = $options['take_profit'];
        }
        
        return $this->make_request('positions/open', 'POST', $params);
    }
    
    /**
     * Close a position
     * 
     * @param string $position_id Position ID to close
     * @return array|WP_Error Close response or error
     */
    public function close_position($position_id) {
        return $this->make_request('positions/close', 'DELETE', array('id' => $position_id));
    }
    
    /**
     * Add symbol to watchlist
     * 
     * @param string $symbol Symbol to add
     * @return array|WP_Error Add response or error
     */
    public function add_to_watchlist($symbol) {
        return $this->make_request('watchlist/byName', 'PUT', array('param' => $symbol));
    }
    
    /**
     * Remove symbol from watchlist
     * 
     * @param string $instrument_id Instrument ID to remove
     * @return array|WP_Error Remove response or error
     */
    public function remove_from_watchlist($instrument_id) {
        return $this->make_request("watchlist/{$instrument_id}", 'DELETE');
    }
    
    /**
     * Get portfolio overview
     * 
     * @return array|WP_Error Portfolio data or error
     */
    public function get_portfolio() {
        return $this->make_request('portfolio');
    }
    
    /**
     * Make API request to eToro
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
        
        $url = $this->api_base_url . '/api/' . $endpoint;
        
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
                'etoro_api_error',
                sprintf(__('eToro API error: %s', 'tradepress'), $response_body),
                array('status' => $response_code)
            );
        }
        
        $data = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_parse_error',
                __('Failed to parse eToro API response', 'tradepress')
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
        if (strpos($endpoint, 'user/accounts') !== false) {
            return array(
                'accounts' => array(
                    array(
                        'accountId' => '12345678',
                        'accountName' => 'Real',
                        'accountType' => 'Real',
                        'currency' => 'USD',
                        'balance' => 5000.50,
                        'availableBalance' => 4500.25,
                        'equity' => 5200.75
                    ),
                    array(
                        'accountId' => '87654321',
                        'accountName' => 'Demo',
                        'accountType' => 'Demo',
                        'currency' => 'USD',
                        'balance' => 100000.00,
                        'availableBalance' => 100000.00,
                        'equity' => 100000.00
                    )
                )
            );
        }
        
        if (strpos($endpoint, 'positions') !== false) {
            return array(
                'positions' => array(
                    array(
                        'positionId' => '1621284697',
                        'instrumentId' => '100000',
                        'symbol' => 'BTC',
                        'name' => 'Bitcoin',
                        'isBuy' => true,
                        'amount' => 100,
                        'leverage' => 2,
                        'openRate' => 50100.1,
                        'currentRate' => 50350.25,
                        'profit' => 25.75,
                        'profitPercentage' => 12.88
                    )
                )
            );
        }
        
        if (strpos($endpoint, 'watchlist') !== false) {
            return array(
                'assets' => array(
                    array(
                        'instrumentId' => '100000',
                        'symbol' => 'BTC',
                        'name' => 'Bitcoin',
                        'type' => 'CRYPTO',
                        'lastPrice' => 50123.45,
                        'change' => 2.35,
                        'changePercent' => 4.91
                    ),
                    array(
                        'instrumentId' => '10012',
                        'symbol' => 'AAPL',
                        'name' => 'Apple Inc',
                        'type' => 'STOCK',
                        'lastPrice' => 175.84,
                        'change' => -1.25,
                        'changePercent' => -0.71
                    )
                )
            );
        }
        
        if (strpos($endpoint, 'market/search') !== false) {
            return array(
                'results' => array(
                    array(
                        'instrumentId' => '100000',
                        'symbol' => 'BTC',
                        'name' => 'Bitcoin',
                        'type' => 'CRYPTO',
                        'lastPrice' => 50123.45
                    )
                )
            );
        }
        
        if (strpos($endpoint, 'market/instrument') !== false) {
            return array(
                'instrumentId' => '100000',
                'symbol' => 'BTC',
                'name' => 'Bitcoin',
                'type' => 'CRYPTO',
                'lastPrice' => 50123.45,
                'bid' => 50120.15,
                'ask' => 50126.75,
                'change' => 2.35,
                'changePercent' => 4.91,
                'volume' => 12547896.32
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
            'historical_data' => false,
            'portfolio_data' => true,
            'trading' => true,
            'social_trading' => true,
            'copy_trading' => true,
            'position_management' => true,
            'watchlists' => true,
            'crypto_trading' => true,
            'forex_trading' => true,
            'cfd_trading' => true,
            'leverage_trading' => true
        );
    }
}
