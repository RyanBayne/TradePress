<?php
/**
 * TradePress EOD Historical Data API
 *
 * Handles connection and functionality for the EOD Historical Data service
 *
 * @package TradePress
 * @subpackage API\EODHD
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress EOD Historical Data API class
 */
class TradePress_EODHD_API {
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://eodhistoricaldata.com';
    
    /**
     * API token
     *
     * @var string
     */
    private $api_token;
    
    /**
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'EOD Historical Data',
        'code' => 'eodhd',
        'type' => 'market_data',
        'tier' => 1,
        'status' => 'active',
        'capabilities' => array(
            'historical_data' => true,
            'real_time_data' => true,
            'fundamental_data' => true,
            'technical_indicators' => true,
            'news_data' => true,
            'options_data' => true,
            'calendar_data' => true,
            'screener' => true,
            'intraday_data' => true,
            'bulk_data' => true,
            'exchange_data' => true,
            'splits_dividends' => true
        ),
        'data_types' => array(
            'quote' => 'real_time',
            'bars' => 'eod_historical',
            'volume' => 'eod_historical',
            'rsi' => 'technical',
            'macd' => 'technical',
            'sma' => 'technical',
            'ema' => 'technical',
            'cci' => 'technical',
            'adx' => 'technical',
            'bollinger_bands' => 'technical',
            'stochastic' => 'technical',
            'mfi' => 'technical',
            'obv' => 'technical',
            'atr' => 'technical',
            'fundamentals' => 'fundamentals',
            'news' => 'news',
            'earnings' => 'calendar',
            'dividends' => 'splits',
            'splits' => 'splits',
            'options' => 'options'
        ),
        'rate_limits' => array(
            'daily' => 100000,
            'per_minute' => 1000,
            'burst' => 20
        ),
        'supported_exchanges' => array('US', 'LSE', 'TSX', 'EURONEXT', 'ASX', 'JSE', 'BSE', 'NSE'),
        'pricing' => array(
            'free_tier' => false,
            'min_plan' => 'All World Extended',
            'cost_per_month' => 79.99
        )
    );
    
    /**
     * Constructor
     *
     * @param string $api_token API token
     */
    public function __construct($api_token = '') {
        $this->api_token = $api_token;
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
        if (!$this->supports_data_type($data_type)) {
            return false;
        }
        
        $endpoint_map = array(
            'quote' => 'real_time',
            'bars' => 'eod_historical',
            'volume' => 'eod_historical',
            'rsi' => 'technical',
            'macd' => 'technical',
            'sma' => 'technical',
            'ema' => 'technical',
            'cci' => 'technical',
            'adx' => 'technical',
            'bollinger_bands' => 'technical',
            'stochastic' => 'technical',
            'mfi' => 'technical',
            'obv' => 'technical',
            'atr' => 'technical',
            'fundamentals' => 'fundamentals',
            'news' => 'news',
            'earnings' => 'calendar',
            'dividends' => 'dividends',
            'splits' => 'splits',
            'options' => 'options'
        );
        
        return isset($endpoint_map[$data_type]) ? $endpoint_map[$data_type] : false;
    }
    
    /**
     * Get quote data
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        return $this->make_request('real_time', array('symbol' => $symbol));
    }
    
    /**
     * Get historical bars
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date (YYYY-MM-DD)
     * @param string $to End date (YYYY-MM-DD)
     * @param string $period Period (d, w, m)
     * @return array|WP_Error Historical data or error
     */
    public function get_bars($symbol, $from = '', $to = '', $period = 'd') {
        $params = array('symbol' => $symbol, 'period' => $period);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('eod_historical', $params);
    }
    
    /**
     * Get technical indicator
     *
     * @param string $symbol Stock symbol
     * @param string $function Technical function (rsi, macd, sma, etc.)
     * @param int $period Period for calculation
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Technical data or error
     */
    public function get_technical_indicator($symbol, $function, $period = 14, $from = '', $to = '') {
        $params = array(
            'symbol' => $symbol,
            'function' => $function,
            'period' => $period
        );
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('technical', $params);
    }
    
    /**
     * Get RSI indicator
     *
     * @param string $symbol Stock symbol
     * @param int $period RSI period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error RSI data or error
     */
    public function get_rsi($symbol, $period = 14, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'rsi', $period, $from, $to);
    }
    
    /**
     * Get MACD indicator
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error MACD data or error
     */
    public function get_macd($symbol, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'macd', 12, $from, $to);
    }
    
    /**
     * Get SMA indicator
     *
     * @param string $symbol Stock symbol
     * @param int $period SMA period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error SMA data or error
     */
    public function get_sma($symbol, $period = 20, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'sma', $period, $from, $to);
    }
    
    /**
     * Get EMA indicator
     *
     * @param string $symbol Stock symbol
     * @param int $period EMA period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error EMA data or error
     */
    public function get_ema($symbol, $period = 20, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'ema', $period, $from, $to);
    }
    
    /**
     * Get CCI indicator
     *
     * @param string $symbol Stock symbol
     * @param int $period CCI period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error CCI data or error
     */
    public function get_cci($symbol, $period = 20, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'cci', $period, $from, $to);
    }
    
    /**
     * Get ADX indicator (via DMI function)
     *
     * @param string $symbol Stock symbol
     * @param int $period ADX period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error ADX data or error
     */
    public function get_adx($symbol, $period = 14, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'dmi', $period, $from, $to);
    }
    
    /**
     * Get Bollinger Bands
     *
     * @param string $symbol Stock symbol
     * @param int $period Period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Bollinger Bands data or error
     */
    public function get_bollinger_bands($symbol, $period = 20, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'bbands', $period, $from, $to);
    }
    
    /**
     * Get Stochastic indicator
     *
     * @param string $symbol Stock symbol
     * @param int $period Period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Stochastic data or error
     */
    public function get_stochastic($symbol, $period = 14, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'stochastic', $period, $from, $to);
    }
    
    /**
     * Get MFI indicator
     *
     * @param string $symbol Stock symbol
     * @param int $period MFI period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error MFI data or error
     */
    public function get_mfi($symbol, $period = 14, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'mfi', $period, $from, $to);
    }
    
    /**
     * Get OBV indicator
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error OBV data or error
     */
    public function get_obv($symbol, $from = '', $to = '') {
        return $this->get_technical_indicator($symbol, 'obv', 1, $from, $to);
    }
    
    /**
     * Get fundamental data
     *
     * @param string $symbol Stock symbol
     * @param string $filter Optional filter
     * @return array|WP_Error Fundamental data or error
     */
    public function get_fundamentals($symbol, $filter = '') {
        $params = array('symbol' => $symbol);
        if ($filter) $params['filter'] = $filter;
        
        return $this->make_request('fundamentals', $params);
    }
    
    /**
     * Get news data
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date
     * @param string $to End date
     * @param int $limit Limit results
     * @return array|WP_Error News data or error
     */
    public function get_news($symbol = '', $from = '', $to = '', $limit = 50) {
        $params = array('limit' => $limit);
        if ($symbol) $params['s'] = $symbol;
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('news', $params);
    }
    
    /**
     * Get earnings calendar
     *
     * @param string $from Start date
     * @param string $to End date
     * @param string $symbols Optional symbols filter
     * @return array|WP_Error Earnings data or error
     */
    public function get_earnings($from = '', $to = '', $symbols = '') {
        $params = array('type' => 'earnings');
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        if ($symbols) $params['symbols'] = $symbols;
        
        return $this->make_request('calendar', $params);
    }
    
    /**
     * Get dividends data
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Dividends data or error
     */
    public function get_dividends($symbol, $from = '', $to = '') {
        $params = array('symbol' => $symbol);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('dividends', $params);
    }
    
    /**
     * Get splits data
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Splits data or error
     */
    public function get_splits($symbol, $from = '', $to = '') {
        $params = array('symbol' => $symbol);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('splits', $params);
    }
    
    /**
     * Get options data
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Options data or error
     */
    public function get_options($symbol, $from = '', $to = '') {
        $params = array('symbol' => $symbol);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('options', $params);
    }
    
    /**
     * Get screener results
     *
     * @param string $signals Signal type
     * @param array $filters Additional filters
     * @param int $limit Result limit
     * @return array|WP_Error Screener data or error
     */
    public function get_screener($signals = '', $filters = array(), $limit = 50) {
        $params = array('limit' => $limit);
        if ($signals) $params['signals'] = $signals;
        if (!empty($filters)) $params['filters'] = json_encode($filters);
        
        return $this->make_request('screener', $params);
    }
    
    /**
     * Get intraday data
     *
     * @param string $symbol Stock symbol
     * @param string $interval Time interval
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Intraday data or error
     */
    public function get_intraday($symbol, $interval = '5m', $from = '', $to = '') {
        $params = array('symbol' => $symbol, 'interval' => $interval);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('intraday', $params);
    }
    
    /**
     * Get bulk EOD data
     *
     * @param string $exchange Exchange code
     * @param string $date Specific date
     * @param string $symbols Optional symbols filter
     * @return array|WP_Error Bulk data or error
     */
    public function get_bulk_data($exchange = 'US', $date = '', $symbols = '') {
        $params = array('exchange' => $exchange);
        if ($date) $params['date'] = $date;
        if ($symbols) $params['symbols'] = $symbols;
        
        return $this->make_request('eod_bulk', $params);
    }
    
    /**
     * Get exchange list
     *
     * @return array|WP_Error Exchange data or error
     */
    public function get_exchanges() {
        return $this->make_request('exchanges', array());
    }
    
    /**
     * Get exchange symbols
     *
     * @param string $exchange Exchange code
     * @return array|WP_Error Symbols data or error
     */
    public function get_exchange_symbols($exchange = 'US') {
        return $this->make_request('exchange_symbols', array('exchange' => $exchange));
    }
    
    /**
     * Get exchange details
     *
     * @param string $exchange Exchange code
     * @param string $from Start date for holidays
     * @param string $to End date for holidays
     * @return array|WP_Error Exchange details or error
     */
    public function get_exchange_details($exchange = 'US', $from = '', $to = '') {
        $params = array('exchange' => $exchange);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('exchange_details', $params);
    }
    
    /**
     * Make API request
     *
     * @param string $endpoint Endpoint name
     * @param array $params Request parameters
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $params = array()) {
        if (!class_exists('TradePress_EODHD_Endpoints')) {
            require_once plugin_dir_path(__FILE__) . 'eodhd-endpoints.php';
        }
        
        $url = TradePress_EODHD_Endpoints::get_endpoint_url($endpoint, $params, $this->api_base_url);
        
        if (empty($url)) {
            return new WP_Error('invalid_endpoint', 'Invalid endpoint specified');
        }
        
        // Add API token
        $url = str_replace('{YOUR_API_TOKEN}', $this->api_token, $url);
        
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
        
        return $data;
    }
    
    /**
     * Legacy method - Get historical data
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date (YYYY-MM-DD)
     * @param string $to End date (YYYY-MM-DD)
     * @return array|WP_Error Historical data or error
     */
    public function get_historical_data($symbol, $from, $to) {
        return $this->get_bars($symbol, $from, $to);
    }
    
    /**
     * Legacy method - Get fundamental data
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Fundamental data or error
     */
    public function get_fundamental_data($symbol) {
        return $this->get_fundamentals($symbol);
    }
}
