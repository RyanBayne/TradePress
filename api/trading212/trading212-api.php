<?php
/**
 * Trading212 API Integration for TradePress
 *
 * Handles communication with Trading212 API
 *
 * @package TradePress/API
 * @version 1.0.0
 * @since 2023-07-11 10:45
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress_Trading212_API Class
 */
class TradePress_Trading212_API {

    /**
     * Platform metadata
     * @var array
     */
    private $platform_metadata = array(
        'name' => 'Trading212',
        'code' => 'trading212',
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
            'fractional_shares' => true,
            'pies' => true,
            'watchlists' => true,
            'fundamentals' => true,
            'charting' => true,
            'search' => true
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
            'news' => false,
            'options' => false,
            'crypto' => false
        ),
        'rate_limits' => array(
            'requests_per_minute' => 60,
            'requests_per_hour' => 3600,
            'requests_per_day' => 86400
        ),
        'supported_markets' => array(
            'US' => true,
            'EU' => true,
            'UK' => true,
            'DE' => true,
            'FR' => true,
            'NL' => true,
            'ES' => true,
            'IT' => true
        ),
        'pricing' => array(
            'free_tier' => false,
            'paid_plans' => true,
            'commission_free' => true,
            'fx_fees' => true,
            'inactivity_fees' => false
        )
    );

    /**
     * API base URL
     * @var string
     */
    private $api_base_url = 'https://live.trading212.com/api/v0/';
    
    /**
     * Trading212 API endpoints
     * @var array
     */
    private $endpoints = array(
        'account' => array(
            'info' => 'equity/account/info',
            'cash' => 'equity/account/cash',
            'metadata' => 'equity/account/metadata',
        ),
        'instruments' => array(
            'all' => 'equity/instruments',
            'details' => 'equity/instruments/{instrument_code}',
            'dividends' => 'equity/instruments/{instrument_code}/dividends',
        ),
        'positions' => array(
            'all' => 'equity/positions',
            'details' => 'equity/positions/{instrument_code}',
        ),
        'orders' => array(
            'all' => 'equity/orders',
            'place' => 'equity/orders',
            'cancel' => 'equity/orders/{order_id}',
        ),
        'transactions' => array(
            'history' => 'equity/history/transactions',
            'dividends' => 'equity/history/dividends',
        ),
        'market' => array(
            'quotes' => 'equity/quotes',
            'candles' => 'charting/candles',
        ),
        'watchlists' => array(
            'all' => 'equity/watchlists',
            'create' => 'equity/watchlists',
            'delete' => 'equity/watchlists/{watchlist_id}',
        )
    );
    
    /**
     * API key
     * @var string
     */
    private $api_key;
    
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
        $this->api_key = isset($api_settings['trading212_api_key']) ? $api_settings['trading212_api_key'] : '';
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
     * Get all available endpoints
     * 
     * @return array List of endpoints
     */
    public function get_endpoints() {
        return $this->endpoints;
    }
    
    /**
     * Get quote data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        return $this->get_market_quotes(array($symbol));
    }
    
    /**
     * Get historical data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @param string $period Time period (1D, 1W, 1M)
     * @param int $limit Number of data points
     * @return array|WP_Error Historical data or error
     */
    public function get_historical_data($symbol, $period = '1D', $limit = 100) {
        return $this->get_chart_data($symbol, $period, $limit);
    }
    
    /**
     * Get account balance
     * 
     * @return array|WP_Error Account balance or error
     */
    public function get_account_balance() {
        return $this->get_account_cash();
    }
    
    /**
     * Get portfolio positions
     * 
     * @return array|WP_Error Portfolio positions or error
     */
    public function get_portfolio_positions() {
        return $this->get_positions();
    }
    
    /**
     * Get open orders
     * 
     * @return array|WP_Error Open orders or error
     */
    public function get_open_orders() {
        return $this->get_orders();
    }
    
    /**
     * Search for symbols
     * 
     * @param string $query Search query
     * @return array|WP_Error Search results or error
     */
    public function search_symbols($query) {
        return $this->search_instruments($query);
    }
    
    /**
     * Get watchlist symbols
     * 
     * @param string $watchlist_id Watchlist ID
     * @return array|WP_Error Watchlist symbols or error
     */
    public function get_watchlist_symbols($watchlist_id = null) {
        if ($watchlist_id) {
            return $this->get_watchlist_tickers($watchlist_id);
        }
        return $this->get_watchlists();
    }
    
    /**
     * Get account information
     * 
     * @return array|WP_Error Account information or error
     */
    public function get_account_info() {
        return $this->make_request('account/info');
    }
    
    /**
     * Get account cash/balance
     * 
     * @return array|WP_Error Account cash information or error
     */
    public function get_account_cash() {
        return $this->make_request('account/cash');
    }
    
    /**
     * Get all available instruments
     * 
     * @return array|WP_Error List of instruments or error
     */
    public function get_instruments() {
        return $this->make_request('equity/instruments');
    }
    
    /**
     * Get specific instrument details
     * 
     * @param string $instrument_code The instrument code
     * @return array|WP_Error Instrument details or error
     */
    public function get_instrument_details($instrument_code) {
        $endpoint = str_replace('{instrument_code}', $instrument_code, 'equity/instruments/{instrument_code}');
        return $this->make_request($endpoint);
    }
    
    /**
     * Get all open positions
     * 
     * @return array|WP_Error List of positions or error
     */
    public function get_positions() {
        return $this->make_request('equity/positions');
    }
    
    /**
     * Get all orders
     * 
     * @return array|WP_Error List of orders or error
     */
    public function get_orders() {
        return $this->make_request('equity/orders');
    }
    
    /**
     * Get transaction history
     * 
     * @param array $params Optional parameters (startDate, endDate, limit)
     * @return array|WP_Error Transaction history or error
     */
    public function get_transaction_history($params = array()) {
        return $this->make_request('equity/history/transactions', 'GET', $params);
    }
    
    /**
     * Get market quotes for instruments
     * 
     * @param array $instrument_codes Array of instrument codes
     * @return array|WP_Error Market quotes or error
     */
    public function get_market_quotes($instrument_codes = array()) {
        if (empty($instrument_codes)) {
            return new WP_Error('missing_symbols', 'No instrument codes provided');
        }
        return $this->make_request('equity/quotes', 'GET', array(
            'instruments' => implode(',', $instrument_codes)
        ));
    }
    
    /**
     * Get watchlists
     * 
     * @since 1.0.0
     * @return array|WP_Error List of watchlists or error
     */
    public function get_watchlists() {
        // Fix: Use the correct request method with proper error handling
        $response = $this->make_request('v3/watchlists');
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }
    
    /**
     * Make API request to Trading212
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $params Request parameters
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $method = 'GET', $params = array()) {
        // Demo mode - return sample data
        if (empty($this->api_key) || defined('TRADEPRESS_DEMO_MODE')) {
            return $this->get_demo_data($endpoint);
        }
        
        $url = $this->api_base_url . $endpoint;
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ),
        );
        
        if ($method === 'GET' && !empty($params)) {
            $url = add_query_arg($params, $url);
        } elseif (!empty($params)) {
            $args['body'] = json_encode($params);
        }
        
        // Log the request if logging is enabled
        if ($this->debug_mode) {
            $this->log_request($url, $args);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Log the response if logging is enabled
        if ($this->debug_mode) {
            $this->log_response($response_code, $response_body);
        }
        
        if ($response_code < 200 || $response_code >= 300) {
            return new WP_Error(
                'trading212_api_error',
                sprintf(__('Trading212 API error: %s', 'tradepress'), $response_body),
                array('status' => $response_code)
            );
        }
        
        $data = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_parse_error',
                __('Failed to parse Trading212 API response', 'tradepress')
            );
        }
        
        return $data;
    }

    /**
     * Get instruments in a watchlist
     *
     * @since 1.0.0
     * @param string $watchlist_id ID of the watchlist
     * @return array|WP_Error Watchlist tickers or error
     */
    public function get_watchlist_tickers($watchlist_id) {
        // Fix: Use the correct request method with proper error handling
        $response = $this->make_request("v3/watchlists/{$watchlist_id}/tickers");
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get charting data
     *
     * @since 1.0.0
     * @param string $ticker Instrument ticker
     * @param string $period Period type (1D, 1W, 1M, etc.)
     * @param int $size Number of candles to return
     * @param bool $include_fake Whether to include fake candles
     * @return array|WP_Error Charting data or error
     */
    public function get_chart_data($ticker, $period = '1D', $size = 100, $include_fake = false) {
        $payload = [
            'candles' => [
                [
                    'ticker' => $ticker,
                    'period' => $period,
                    'size' => $size,
                    'includeFake' => $include_fake
                ]
            ]
        ];
        
        // Fix: Use the correct request method with POST and proper headers
        $response = $this->make_request('charting/v2/batch', 'POST', $payload);
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get batch chart data for multiple tickers
     *
     * @since 1.0.0
     * @param array $tickers Array of ticker data with period and size
     * @return array|WP_Error Charting data or error
     */
    public function get_batch_chart_data($tickers) {
        $candles = [];
        
        foreach ($tickers as $ticker_data) {
            $candles[] = [
                'ticker' => $ticker_data['ticker'],
                'period' => isset($ticker_data['period']) ? $ticker_data['period'] : '1D',
                'size' => isset($ticker_data['size']) ? $ticker_data['size'] : 100,
                'includeFake' => isset($ticker_data['include_fake']) ? $ticker_data['include_fake'] : false
            ];
        }
        
        $payload = ['candles' => $candles];
        
        // Fix: Use the correct request method with POST and proper headers
        $response = $this->make_request('charting/v2/batch', 'POST', $payload);
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get instruments list by type
     *
     * @since 1.0.0
     * @param string $type Instrument type (EQUITY, CFD-pro, CFD-retail)
     * @param string $version Version parameter (appears to be a timestamp or hash)
     * @return array|WP_Error Instruments data or error
     */
    public function get_instruments_by_type($type = 'EQUITY', $version = '-1830575850') {
        // Fix: Use the correct request method with proper error handling
        $response = $this->make_request("instruments/{$type}/{$version}");
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get instrument folders
     *
     * @since 1.0.0
     * @param string $type Instrument type (EQUITY, CFD)
     * @param string $version Version parameter (appears to be a timestamp or hash)
     * @return array|WP_Error Instrument folders data or error
     */
    public function get_instrument_folders($type = 'EQUITY', $version = '-1830575850') {
        // Fix: Use the correct request method with proper error handling
        $response = $this->make_request("instrument-folders/{$type}/{$version}");
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get company fundamentals
     *
     * @since 1.0.0
     * @param string $ticker Company ticker
     * @return array|WP_Error Company fundamentals or error
     */
    public function get_company_fundamentals($ticker) {
        // Fix: Use the correct request method with proper error handling and URL encoding
        $response = $this->make_request("companies/fundamentals?ticker=" . urlencode($ticker));
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get company financial ratios
     *
     * @since 1.0.0
     * @param string $ticker Company ticker
     * @return array|WP_Error Company financial ratios or error
     */
    public function get_company_ratios($ticker) {
        // Fix: Use the correct request method with proper error handling and URL encoding
        $response = $this->make_request("companies/full-ratios?ticker=" . urlencode($ticker));
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get company financial statements
     *
     * @since 1.0.0
     * @param string $ticker Company ticker
     * @param string $statement Statement type (income, balance, cash)
     * @return array|WP_Error Company financial statement or error
     */
    public function get_company_financial($ticker, $statement = 'income') {
        // Fix: Use the correct request method with proper error handling and URL encoding
        $response = $this->make_request("companies/full-financial/{$statement}?ticker=" . urlencode($ticker));
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Search for instruments
     *
     * @since 1.0.0
     * @param string $query Search query
     * @return array|WP_Error Search results or error
     */
    public function search_instruments($query) {
        // Fix: Use the correct request method with proper error handling and URL encoding
        $response = $this->make_request("v2/instruments/search?query=" . urlencode($query));
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get additional instrument information
     *
     * @since 1.0.0
     * @param string $ticker Instrument ticker
     * @return array|WP_Error Additional instrument info or error
     */
    public function get_instrument_additional_info($ticker) {
        // Fix: Use the correct request method with proper error handling and URL encoding
        $response = $this->make_request("v2/instruments/additional-info/" . urlencode($ticker));
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get price deviations (performance over time)
     *
     * @since 1.0.0
     * @param array $tickers Array of ticker symbols
     * @param bool $use_ask_prices Whether to use ask prices instead of bid
     * @return array|WP_Error Price deviation data or error
     */
    public function get_price_deviations($tickers, $use_ask_prices = false) {
        $deviations = [];
        foreach ($tickers as $ticker) {
            $deviations[] = [
                "ticker" => $ticker,
                "useAskPrices" => $use_ask_prices
            ];
        }
        
        $payload = ["deviations" => $deviations];
        
        // Fix: Use the correct request method with POST and proper error handling
        $response = $this->make_request("charting/v2/batch", 'POST', $payload);
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get portfolio overview
     * 
     * @since 1.0.0
     * @return array|WP_Error Portfolio summary or error
     */
    public function get_portfolio() {
        $response = $this->make_request('v2/portfolio');
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Get detailed portfolio information
     * 
     * @since 1.0.0
     * @return array|WP_Error Detailed portfolio information or error
     */
    public function get_portfolio_info() {
        $response = $this->make_request('v2/portfolio/info');
        if (is_wp_error($response)) {
            return $response;
        }
        return $response;
    }

    /**
     * Save Trading212 session token
     * 
     * @since 1.0.0
     * @param string $token The Trading212 session token
     * @return bool Whether the token was saved successfully
     */
    public function save_session_token($token) {
        // Optionally validate token format before saving
        if (empty($token)) {
            return false;
        }
        
        // Store encrypted or at least in a way that's not directly visible
        return update_option('tradepress_trading212_session', $token);
    }

    /**
     * Get stored Trading212 session token
     * 
     * @since 1.0.0
     * @return string The stored token or empty string
     */
    public function get_session_token() {
        return get_option('tradepress_trading212_session', '');
    }

    /**
     * Get demo data for endpoints
     * 
     * @param string $endpoint API endpoint
     * @return array Sample data
     */
    public function get_demo_data($endpoint) {
        $demo_data = array();
        
        // Account information demo data
        if ($endpoint === 'account/info') {
            $demo_data = array(
                'accountId' => 'demo12345',
                'currency' => 'USD',
                'type' => 'LIVE',
                'maxLeverage' => 1,
                'canUseNews' => true,
                'isBlocked' => false,
                'alias' => 'My Trading Account'
            );
        }
        
        // Account cash demo data
        elseif ($endpoint === 'account/cash') {
            $demo_data = array(
                'free' => 25000.50,
                'total' => 50000.75,
                'blocked' => 25000.25,
                'ppl' => 3500.10,
                'guarantees' => 1500.00,
                'currency' => 'USD'
            );
        }
        
        // Instruments demo data
        elseif ($endpoint === 'equity/instruments') {
            $demo_data = array(
                array(
                    'instrumentCode' => 'AAPL',
                    'name' => 'Apple Inc.',
                    'currencyCode' => 'USD',
                    'isin' => 'US0378331005',
                    'marketId' => 'NASDAQ',
                    'productType' => 'STOCK',
                    'shortEnabled' => true,
                    'fractionalEnabled' => true,
                    'minTrade' => 1,
                    'maxTrade' => 100000
                ),
                array(
                    'instrumentCode' => 'MSFT',
                    'name' => 'Microsoft Corporation',
                    'currencyCode' => 'USD',
                    'isin' => 'US5949181045',
                    'marketId' => 'NASDAQ',
                    'productType' => 'STOCK',
                    'shortEnabled' => true,
                    'fractionalEnabled' => true,
                    'minTrade' => 1,
                    'maxTrade' => 100000
                ),
                array(
                    'instrumentCode' => 'GOOGL',
                    'name' => 'Alphabet Inc.',
                    'currencyCode' => 'USD',
                    'isin' => 'US02079K3059',
                    'marketId' => 'NASDAQ',
                    'productType' => 'STOCK',
                    'shortEnabled' => true,
                    'fractionalEnabled' => true,
                    'minTrade' => 1,
                    'maxTrade' => 100000
                ),
                array(
                    'instrumentCode' => 'TSLA',
                    'name' => 'Tesla, Inc.',
                    'currencyCode' => 'USD',
                    'isin' => 'US88160R1014',
                    'marketId' => 'NASDAQ',
                    'productType' => 'STOCK',
                    'shortEnabled' => true,
                    'fractionalEnabled' => true,
                    'minTrade' => 1,
                    'maxTrade' => 100000
                ),
                array(
                    'instrumentCode' => 'AMZN',
                    'name' => 'Amazon.com, Inc.',
                    'currencyCode' => 'USD',
                    'isin' => 'US0231351067',
                    'marketId' => 'NASDAQ',
                    'productType' => 'STOCK',
                    'shortEnabled' => true,
                    'fractionalEnabled' => true,
                    'minTrade' => 1,
                    'maxTrade' => 100000
                )
            );
        }
        
        // Positions demo data
        elseif ($endpoint === 'equity/positions') {
            $demo_data = array(
                array(
                    'instrumentCode' => 'AAPL',
                    'quantity' => 10,
                    'averagePrice' => 150.25,
                    'currentPrice' => 165.75,
                    'ppl' => 155.00,
                    'result' => array(
                        'value' => 155.00,
                        'percentage' => 10.32
                    )
                ),
                array(
                    'instrumentCode' => 'MSFT',
                    'quantity' => 5,
                    'averagePrice' => 240.50,
                    'currentPrice' => 260.25,
                    'ppl' => 98.75,
                    'result' => array(
                        'value' => 98.75,
                        'percentage' => 8.21
                    )
                ),
                array(
                    'instrumentCode' => 'TSLA',
                    'quantity' => 8,
                    'averagePrice' => 800.00,
                    'currentPrice' => 785.50,
                    'ppl' => -116.00,
                    'result' => array(
                        'value' => -116.00,
                        'percentage' => -1.81
                    )
                )
            );
        }
        
        // Orders demo data
        elseif ($endpoint === 'equity/orders') {
            $demo_data = array(
                array(
                    'orderId' => 'ord12345',
                    'instrumentCode' => 'AAPL',
                    'quantity' => 2,
                    'type' => 'MARKET',
                    'status' => 'PENDING',
                    'timeValidity' => 'DAY',
                    'createdTimestamp' => date('Y-m-d\TH:i:s\Z', strtotime('-1 hour')),
                    'timeInForce' => 'GTC'
                ),
                array(
                    'orderId' => 'ord12346',
                    'instrumentCode' => 'GOOGL',
                    'quantity' => 1,
                    'type' => 'LIMIT',
                    'status' => 'PENDING',
                    'limitPrice' => 2500.00,
                    'timeValidity' => 'DAY',
                    'createdTimestamp' => date('Y-m-d\TH:i:s\Z', strtotime('-2 hours')),
                    'timeInForce' => 'GTC'
                )
            );
        }
        
        // Transaction history demo data
        elseif ($endpoint === 'equity/history/transactions') {
            $demo_data = array(
                array(
                    'id' => 'tx12345',
                    'instrumentCode' => 'AAPL',
                    'type' => 'BUY',
                    'timestamp' => date('Y-m-d\TH:i:s\Z', strtotime('-3 days')),
                    'price' => 150.25,
                    'quantity' => 10,
                    'amount' => 1502.50,
                    'fee' => 0,
                    'currencyCode' => 'USD'
                ),
                array(
                    'id' => 'tx12346',
                    'instrumentCode' => 'MSFT',
                    'type' => 'BUY',
                    'timestamp' => date('Y-m-d\TH:i:s\Z', strtotime('-2 days')),
                    'price' => 240.50,
                    'quantity' => 5,
                    'amount' => 1202.50,
                    'fee' => 0,
                    'currencyCode' => 'USD'
                ),
                array(
                    'id' => 'tx12347',
                    'instrumentCode' => 'NFLX',
                    'type' => 'SELL',
                    'timestamp' => date('Y-m-d\TH:i:s\Z', strtotime('-1 day')),
                    'price' => 525.50,
                    'quantity' => 3,
                    'amount' => 1576.50,
                    'fee' => 0,
                    'currencyCode' => 'USD'
                )
            );
        }
        
        // Market quotes demo data
        elseif ($endpoint === 'equity/quotes') {
            $demo_data = array(
                array(
                    'instrumentCode' => 'AAPL',
                    'bid' => 165.50,
                    'ask' => 165.75,
                    'spread' => 0.25,
                    'timestamp' => date('Y-m-d\TH:i:s\Z'),
                    'price' => 165.63,
                    'change' => 2.15,
                    'changePercent' => 1.31
                ),
                array(
                    'instrumentCode' => 'MSFT',
                    'bid' => 260.00,
                    'ask' => 260.25,
                    'spread' => 0.25,
                    'timestamp' => date('Y-m-d\TH:i:s\Z'),
                    'price' => 260.13,
                    'change' => 3.25,
                    'changePercent' => 1.27
                ),
                array(
                    'instrumentCode' => 'GOOGL',
                    'bid' => 2540.75,
                    'ask' => 2541.25,
                    'spread' => 0.50,
                    'timestamp' => date('Y-m-d\TH:i:s\Z'),
                    'price' => 2541.00,
                    'change' => 15.50,
                    'changePercent' => 0.61
                ),
                array(
                    'instrumentCode' => 'TSLA',
                    'bid' => 785.25,
                    'ask' => 785.75,
                    'spread' => 0.50,
                    'timestamp' => date('Y-m-d\TH:i:s\Z'),
                    'price' => 785.50,
                    'change' => -12.25,
                    'changePercent' => -1.54
                ),
                array(
                    'instrumentCode' => 'AMZN',
                    'bid' => 3300.50,
                    'ask' => 3301.25,
                    'spread' => 0.75,
                    'timestamp' => date('Y-m-d\TH:i:s\Z'),
                    'price' => 3300.88,
                    'change' => 25.75,
                    'changePercent' => 0.79
                )
            );
        }
        
        // Watchlists demo data
        elseif ($endpoint === 'equity/watchlists' || strpos($endpoint, 'v3/watchlists') !== false) {
            $demo_data = array(
                array(
                    'id' => 'wl12345',
                    'name' => 'Tech Stocks',
                    'instruments' => array('AAPL', 'MSFT', 'GOOGL', 'TSLA', 'AMZN')
                ),
                array(
                    'id' => 'wl12346',
                    'name' => 'Financial Stocks',
                    'instruments' => array('JPM', 'BAC', 'WFC', 'C', 'GS')
                ),
                array(
                    'id' => 'wl12347',
                    'name' => 'Healthcare Stocks',
                    'instruments' => array('JNJ', 'PFE', 'UNH', 'ABBV', 'MRK')
                )
            );
        }
        
        // Chart data demo
        elseif (strpos($endpoint, 'charting') !== false) {
            $demo_data = array(
                'candles' => array(
                    array(
                        'time' => strtotime('-5 days') * 1000,
                        'open' => 160.25,
                        'high' => 162.50,
                        'low' => 159.75,
                        'close' => 161.80,
                        'volume' => 45000000
                    ),
                    array(
                        'time' => strtotime('-4 days') * 1000,
                        'open' => 161.80,
                        'high' => 164.20,
                        'low' => 161.50,
                        'close' => 163.45,
                        'volume' => 52000000
                    ),
                    array(
                        'time' => strtotime('-3 days') * 1000,
                        'open' => 163.45,
                        'high' => 165.80,
                        'low' => 162.90,
                        'close' => 164.75,
                        'volume' => 48000000
                    ),
                    array(
                        'time' => strtotime('-2 days') * 1000,
                        'open' => 164.75,
                        'high' => 166.25,
                        'low' => 163.80,
                        'close' => 165.20,
                        'volume' => 41000000
                    ),
                    array(
                        'time' => strtotime('-1 day') * 1000,
                        'open' => 165.20,
                        'high' => 167.50,
                        'low' => 164.90,
                        'close' => 165.63,
                        'volume' => 39000000
                    )
                )
            );
        }
        
        // Search results demo
        elseif (strpos($endpoint, 'search') !== false) {
            $demo_data = array(
                'results' => array(
                    array(
                        'ticker' => 'AAPL',
                        'name' => 'Apple Inc.',
                        'type' => 'STOCK',
                        'exchange' => 'NASDAQ'
                    ),
                    array(
                        'ticker' => 'MSFT',
                        'name' => 'Microsoft Corporation',
                        'type' => 'STOCK',
                        'exchange' => 'NASDAQ'
                    )
                )
            );
        }
        
        return $demo_data;
    }
    
    /**
     * Place a market order
     * 
     * @param string $symbol Stock symbol
     * @param float $quantity Quantity to trade
     * @param string $side Order side (buy/sell)
     * @return array|WP_Error Order response or error
     */
    public function place_market_order($symbol, $quantity, $side = 'buy') {
        $params = array(
            'ticker' => $symbol,
            'quantity' => $side === 'sell' ? -abs($quantity) : abs($quantity)
        );
        return $this->make_request('equity/orders/market', 'POST', $params);
    }
    
    /**
     * Place a limit order
     * 
     * @param string $symbol Stock symbol
     * @param float $quantity Quantity to trade
     * @param float $price Limit price
     * @param string $side Order side (buy/sell)
     * @param string $time_validity Order validity (DAY/GOOD_TILL_CANCEL)
     * @return array|WP_Error Order response or error
     */
    public function place_limit_order($symbol, $quantity, $price, $side = 'buy', $time_validity = 'DAY') {
        $params = array(
            'ticker' => $symbol,
            'quantity' => $side === 'sell' ? -abs($quantity) : abs($quantity),
            'limitPrice' => $price,
            'timeValidity' => $time_validity
        );
        return $this->make_request('equity/orders/limit', 'POST', $params);
    }
    
    /**
     * Cancel an order
     * 
     * @param string $order_id Order ID to cancel
     * @return array|WP_Error Cancel response or error
     */
    public function cancel_order($order_id) {
        return $this->make_request("equity/orders/{$order_id}", 'DELETE');
    }
    
    /**
     * Log API request
     * 
     * @param string $url Request URL
     * @param array $args Request arguments
     */
    private function log_request($url, $args) {
        // Sanitize URL to remove sensitive information
        $sanitized_url = preg_replace('/([?&])(api_key|Authorization)=[^&]+/', '$1$2=REDACTED', $url);
        
        // Copy args and remove sensitive information
        $sanitized_args = $args;
        if (isset($sanitized_args['headers']['Authorization'])) {
            $sanitized_args['headers']['Authorization'] = 'REDACTED';
        }
        
        $log_data = array(
            'time' => current_time('mysql'),
            'service' => 'Trading212',
            'request_url' => $sanitized_url,
            'request_args' => $sanitized_args
        );
        
        // Store logs in a custom table or use WP options as an easy solution
        $api_logs = get_option('tradepress_trading212_request_logs', array());
        array_unshift($api_logs, $log_data);
        
        // Keep only the last 100 logs
        if (count($api_logs) > 100) {
            array_pop($api_logs);
        }
        
        update_option('tradepress_trading212_request_logs', $api_logs);
    }
    
    /**
     * Log API response
     * 
     * @param int $code Response code
     * @param string $body Response body
     */
    private function log_response($code, $body) {
        $log_data = array(
            'time' => current_time('mysql'),
            'service' => 'Trading212',
            'response_code' => $code,
            'response_body' => substr($body, 0, 500) . (strlen($body) > 500 ? '...' : '') // Limit response size
        );
        
        // Store logs in a custom table or use WP options as an easy solution
        $api_logs = get_option('tradepress_trading212_response_logs', array());
        array_unshift($api_logs, $log_data);
        
        // Keep only the last 100 logs
        if (count($api_logs) > 100) {
            array_pop($api_logs);
        }
        
        update_option('tradepress_trading212_response_logs', $api_logs);
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
            'fundamentals' => true
        );
    }
}
