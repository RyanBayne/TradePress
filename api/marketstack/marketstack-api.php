<?php
/**
 * TradePress MarketStack API
 *
 * Handles connection and functionality for the MarketStack market data service
 *
 * @package TradePress
 * @subpackage API\MarketStack
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress MarketStack API class
 */
class TradePress_MarketStack_API {
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'http://api.marketstack.com/v1';
    
    /**
     * API access key
     *
     * @var string
     */
    private $access_key;
    
    /**
     * Use HTTPS (available on paid plans)
     *
     * @var bool
     */
    private $use_https;
    
    /**
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'Marketstack',
        'code' => 'marketstack',
        'type' => 'market_data',
        'tier' => 1,
        'status' => 'active',
        'capabilities' => array(
            'real_time_data' => true,
            'historical_data' => true,
            'intraday_data' => true,
            'end_of_day_data' => true,
            'ticker_data' => true,
            'dividends_data' => true,
            'splits_data' => true,
            'exchange_data' => true,
            'currency_data' => true,
            'timezone_data' => true,
            'technical_indicators' => false,
            'fundamental_data' => false,
            'news_data' => false
        ),
        'data_types' => array(
            'quote' => 'EOD_LATEST',
            'bars' => 'EOD',
            'volume' => 'EOD',
            'intraday' => 'INTRADAY',
            'intraday_latest' => 'INTRADAY_LATEST',
            'ticker_eod' => 'TICKER_EOD',
            'ticker_intraday' => 'TICKER_INTRADAY',
            'tickers' => 'TICKERS',
            'dividends' => 'DIVIDENDS',
            'splits' => 'SPLITS',
            'exchanges' => 'EXCHANGES',
            'currencies' => 'CURRENCIES',
            'timezones' => 'TIMEZONES'
        ),
        'rate_limits' => array(
            'free_monthly' => 100,
            'basic_monthly' => 1000,
            'professional_monthly' => 10000,
            'business_monthly' => 100000,
            'per_second' => 5,
            'burst' => 5
        ),
        'supported_markets' => array('US', 'Global'),
        'pricing' => array(
            'free_tier' => true,
            'min_plan' => 'Basic',
            'cost_per_month' => 9.99
        )
    );
    
    /**
     * Constructor
     *
     * @param string $access_key API access key
     * @param bool $use_https Whether to use HTTPS (paid plans only)
     */
    public function __construct($access_key = '', $use_https = false) {
        $this->access_key = $access_key ?: get_option('tradepress_marketstack_access_key', '');
        $this->use_https = $use_https;
        
        if ($this->use_https) {
            $this->api_base_url = 'https://api.marketstack.com/v1';
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
     * Get quote data (latest EOD)
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        return $this->make_request('EOD_LATEST', array('symbols' => $symbol));
    }
    
    /**
     * Get historical bars (EOD data)
     *
     * @param string $symbol Stock symbol
     * @param string $date_from Start date (YYYY-MM-DD)
     * @param string $date_to End date (YYYY-MM-DD)
     * @param int $limit Result limit
     * @return array|WP_Error Historical data or error
     */
    public function get_bars($symbol, $date_from = '', $date_to = '', $limit = 100) {
        $params = array('symbols' => $symbol, 'limit' => $limit);
        if ($date_from) $params['date_from'] = $date_from;
        if ($date_to) $params['date_to'] = $date_to;
        
        return $this->make_request('EOD', $params);
    }
    
    /**
     * Get intraday data
     *
     * @param string $symbol Stock symbol
     * @param string $interval Time interval (1min, 5min, 15min, 30min, 1hour)
     * @param string $date_from Start date
     * @param string $date_to End date
     * @param int $limit Result limit
     * @return array|WP_Error Intraday data or error
     */
    public function get_intraday($symbol, $interval = '1min', $date_from = '', $date_to = '', $limit = 100) {
        $params = array('symbols' => $symbol, 'interval' => $interval, 'limit' => $limit);
        if ($date_from) $params['date_from'] = $date_from;
        if ($date_to) $params['date_to'] = $date_to;
        
        return $this->make_request('INTRADAY', $params);
    }
    
    /**
     * Get latest intraday data
     *
     * @param string $symbol Stock symbol
     * @param string $interval Time interval
     * @return array|WP_Error Latest intraday data or error
     */
    public function get_intraday_latest($symbol, $interval = '1min') {
        return $this->make_request('INTRADAY_LATEST', array('symbols' => $symbol, 'interval' => $interval));
    }
    
    /**
     * Get ticker information
     *
     * @param string $symbol Optional symbol filter
     * @param string $exchange Optional exchange filter
     * @param string $search Optional search term
     * @return array|WP_Error Ticker data or error
     */
    public function get_tickers($symbol = '', $exchange = '', $search = '') {
        $params = array();
        if ($symbol) $params['symbols'] = $symbol;
        if ($exchange) $params['exchange'] = $exchange;
        if ($search) $params['search'] = $search;
        
        return $this->make_request('TICKERS', $params);
    }
    
    /**
     * Get ticker EOD data
     *
     * @param string $symbol Stock symbol
     * @param string $date_from Start date
     * @param string $date_to End date
     * @param int $limit Result limit
     * @return array|WP_Error Ticker EOD data or error
     */
    public function get_ticker_eod($symbol, $date_from = '', $date_to = '', $limit = 100) {
        $params = array('symbol' => $symbol, 'limit' => $limit);
        if ($date_from) $params['date_from'] = $date_from;
        if ($date_to) $params['date_to'] = $date_to;
        
        return $this->make_request('TICKER_EOD', $params);
    }
    
    /**
     * Get ticker intraday data
     *
     * @param string $symbol Stock symbol
     * @param string $interval Time interval
     * @param string $date_from Start date
     * @param string $date_to End date
     * @param int $limit Result limit
     * @return array|WP_Error Ticker intraday data or error
     */
    public function get_ticker_intraday($symbol, $interval = '1min', $date_from = '', $date_to = '', $limit = 100) {
        $params = array('symbol' => $symbol, 'interval' => $interval, 'limit' => $limit);
        if ($date_from) $params['date_from'] = $date_from;
        if ($date_to) $params['date_to'] = $date_to;
        
        return $this->make_request('TICKER_INTRADAY', $params);
    }
    
    /**
     * Get dividends data
     *
     * @param string $symbol Stock symbol
     * @param string $date_from Start date
     * @param string $date_to End date
     * @param int $limit Result limit
     * @return array|WP_Error Dividends data or error
     */
    public function get_dividends($symbol = '', $date_from = '', $date_to = '', $limit = 100) {
        $params = array('limit' => $limit);
        if ($symbol) $params['symbols'] = $symbol;
        if ($date_from) $params['date_from'] = $date_from;
        if ($date_to) $params['date_to'] = $date_to;
        
        return $this->make_request('DIVIDENDS', $params);
    }
    
    /**
     * Get splits data
     *
     * @param string $symbol Stock symbol
     * @param string $date_from Start date
     * @param string $date_to End date
     * @param int $limit Result limit
     * @return array|WP_Error Splits data or error
     */
    public function get_splits($symbol = '', $date_from = '', $date_to = '', $limit = 100) {
        $params = array('limit' => $limit);
        if ($symbol) $params['symbols'] = $symbol;
        if ($date_from) $params['date_from'] = $date_from;
        if ($date_to) $params['date_to'] = $date_to;
        
        return $this->make_request('SPLITS', $params);
    }
    
    /**
     * Get exchanges data
     *
     * @param int $limit Result limit
     * @param int $offset Result offset
     * @return array|WP_Error Exchanges data or error
     */
    public function get_exchanges($limit = 100, $offset = 0) {
        return $this->make_request('EXCHANGES', array('limit' => $limit, 'offset' => $offset));
    }
    
    /**
     * Get currencies data
     *
     * @param int $limit Result limit
     * @param int $offset Result offset
     * @return array|WP_Error Currencies data or error
     */
    public function get_currencies($limit = 100, $offset = 0) {
        return $this->make_request('CURRENCIES', array('limit' => $limit, 'offset' => $offset));
    }
    
    /**
     * Get timezones data
     *
     * @param int $limit Result limit
     * @param int $offset Result offset
     * @return array|WP_Error Timezones data or error
     */
    public function get_timezones($limit = 100, $offset = 0) {
        return $this->make_request('TIMEZONES', array('limit' => $limit, 'offset' => $offset));
    }
    
    /**
     * Make API request
     *
     * @param string $endpoint Endpoint name
     * @param array $params Request parameters
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $params = array()) {
        if (!class_exists('TradePress_MarketStack_Endpoints')) {
            require_once plugin_dir_path(__FILE__) . 'marketstack-endpoints.php';
        }
        
        // Add access key to params
        if (!empty($this->access_key)) {
            $params['access_key'] = $this->access_key;
        }
        
        $url = TradePress_MarketStack_Endpoints::get_endpoint_url($endpoint, $params, $this->api_base_url, $this->use_https);
        
        if (empty($url)) {
            return new WP_Error('invalid_endpoint', 'Invalid endpoint specified');
        }
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'TradePress/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode JSON response');
        }
        
        // Check for API errors
        if (isset($data['error'])) {
            return new WP_Error('api_error', $data['error']['message'] ?? 'API Error');
        }
        
        return $data;
    }
    
    /**
     * Legacy method - Get end-of-day data
     *
     * @param string $symbol Stock symbol
     * @param array $params Optional parameters
     * @return array|WP_Error EOD data or error
     */
    public function get_eod_data($symbol, $params = array()) {
        return $this->get_bars($symbol, $params['date_from'] ?? '', $params['date_to'] ?? '', $params['limit'] ?? 100);
    }
    
    /**
     * Legacy method - Get intraday data
     *
     * @param string $symbol Stock symbol
     * @param array $params Optional parameters
     * @return array|WP_Error Intraday data or error
     */
    public function get_intraday_data($symbol, $params = array()) {
        return $this->get_intraday($symbol, $params['interval'] ?? '1min', $params['date_from'] ?? '', $params['date_to'] ?? '', $params['limit'] ?? 100);
    }
}
