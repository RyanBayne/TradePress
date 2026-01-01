<?php
/**
 * TradePress Yahoo Finance API
 *
 * Handles connection and functionality for the Yahoo Finance service
 * Note: Yahoo Finance doesn't have an official API, using unofficial endpoints
 *
 * @package TradePress
 * @subpackage API\Yahoo
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Yahoo Finance API class
 */
class TradePress_Yahoo_API {
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://query1.finance.yahoo.com';
    
    /**
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'Yahoo Finance',
        'code' => 'yahoo',
        'type' => 'market_data',
        'tier' => 3,
        'status' => 'active',
        'capabilities' => array(
            'real_time_data' => true,
            'historical_data' => true,
            'quote_data' => true,
            'search' => true,
            'options_data' => true,
            'fundamental_data' => true,
            'news_data' => true,
            'market_summary' => true,
            'trending_data' => true,
            'screener' => true,
            'recommendations' => true,
            'insights' => true,
            'spark_charts' => true,
            'technical_indicators' => false,
            'intraday_data' => true
        ),
        'data_types' => array(
            'quote' => 'quote',
            'quotes' => 'quotes',
            'bars' => 'historical',
            'volume' => 'historical',
            'historical' => 'historical',
            'intraday' => 'quote',
            'search' => 'search',
            'options' => 'options',
            'fundamentals' => 'fundamentals',
            'news' => 'news',
            'market_summary' => 'market_summary',
            'trending' => 'trending',
            'screener' => 'screener',
            'spark' => 'spark',
            'recommendations' => 'recommendations',
            'insights' => 'insights'
        ),
        'rate_limits' => array(
            'requests_per_hour' => 2000,
            'requests_per_day' => 48000,
            'burst' => 10,
            'note' => 'Unofficial API - limits may vary'
        ),
        'supported_markets' => array('US', 'Global'),
        'pricing' => array(
            'free_tier' => true,
            'cost' => 0,
            'note' => 'Free but unofficial API'
        )
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        // Yahoo Finance doesn't require API key for basic endpoints
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
     * @param string $symbol Stock symbol
     * @param string $interval Time interval (1m, 2m, 5m, 15m, 30m, 60m, 90m, 1h, 1d, 5d, 1wk, 1mo, 3mo)
     * @param string $range Time range (1d, 5d, 1mo, 3mo, 6mo, 1y, 2y, 5y, 10y, ytd, max)
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol, $interval = '1d', $range = '1d') {
        return $this->make_request('quote', array(
            'symbol' => $symbol,
            'interval' => $interval,
            'range' => $range,
            'includePrePost' => 'true'
        ));
    }
    
    /**
     * Get quotes for multiple symbols
     *
     * @param array $symbols Array of stock symbols
     * @return array|WP_Error Quotes data or error
     */
    public function get_quotes($symbols) {
        $symbols_string = is_array($symbols) ? implode(',', $symbols) : $symbols;
        return $this->make_request('quotes', array('symbols' => $symbols_string));
    }
    
    /**
     * Get historical bars
     *
     * @param string $symbol Stock symbol
     * @param int $period1 Start timestamp
     * @param int $period2 End timestamp
     * @param string $interval Time interval
     * @return array|WP_Error Historical data or error
     */
    public function get_bars($symbol, $period1 = null, $period2 = null, $interval = '1d') {
        $params = array('symbol' => $symbol, 'interval' => $interval);
        if ($period1) $params['period1'] = $period1;
        if ($period2) $params['period2'] = $period2;
        
        return $this->make_request('historical', $params);
    }
    
    /**
     * Get intraday data
     *
     * @param string $symbol Stock symbol
     * @param string $interval Intraday interval (1m, 2m, 5m, 15m, 30m, 60m, 90m)
     * @param string $range Time range (1d, 5d)
     * @return array|WP_Error Intraday data or error
     */
    public function get_intraday($symbol, $interval = '5m', $range = '1d') {
        return $this->get_quote($symbol, $interval, $range);
    }
    
    /**
     * Search for securities
     *
     * @param string $query Search query
     * @param int $quotes_count Number of quote results
     * @param int $news_count Number of news results
     * @return array|WP_Error Search results or error
     */
    public function search($query, $quotes_count = 10, $news_count = 5) {
        return $this->make_request('search', array(
            'q' => $query,
            'quotesCount' => $quotes_count,
            'newsCount' => $news_count
        ));
    }
    
    /**
     * Get options data
     *
     * @param string $symbol Stock symbol
     * @param int $date Expiration date timestamp (optional)
     * @return array|WP_Error Options data or error
     */
    public function get_options($symbol, $date = null) {
        $params = array('symbol' => $symbol);
        if ($date) $params['date'] = $date;
        
        return $this->make_request('options', $params);
    }
    
    /**
     * Get fundamental data
     *
     * @param string $symbol Stock symbol
     * @param array $modules Modules to fetch (defaultKeyStatistics, financialData, summaryProfile, etc.)
     * @return array|WP_Error Fundamental data or error
     */
    public function get_fundamentals($symbol, $modules = array()) {
        $default_modules = array(
            'defaultKeyStatistics',
            'financialData',
            'summaryProfile',
            'summaryDetail',
            'assetProfile',
            'incomeStatementHistory',
            'balanceSheetHistory',
            'cashflowStatementHistory'
        );
        
        $modules = !empty($modules) ? $modules : $default_modules;
        
        return $this->make_request('fundamentals', array(
            'symbol' => $symbol,
            'modules' => implode(',', $modules)
        ));
    }
    
    /**
     * Get news for a symbol
     *
     * @param string $symbol Stock symbol
     * @param int $count Number of news items
     * @return array|WP_Error News data or error
     */
    public function get_news($symbol, $count = 10) {
        return $this->make_request('news', array(
            'q' => $symbol,
            'newsCount' => $count,
            'quotesCount' => 0
        ));
    }
    
    /**
     * Get market summary
     *
     * @param string $region Region code (US, GB, etc.)
     * @return array|WP_Error Market summary or error
     */
    public function get_market_summary($region = 'US') {
        return $this->make_request('market_summary', array('region' => $region));
    }
    
    /**
     * Get trending stocks
     *
     * @param string $region Region code (US, GB, etc.)
     * @return array|WP_Error Trending data or error
     */
    public function get_trending($region = 'US') {
        return $this->make_request('trending', array('region' => $region));
    }
    
    /**
     * Get screener results
     *
     * @param array $screener_ids Screener IDs
     * @param int $count Number of results
     * @return array|WP_Error Screener results or error
     */
    public function get_screener($screener_ids = array(), $count = 25) {
        $default_screeners = array('most_actives', 'day_gainers', 'day_losers');
        $screener_ids = !empty($screener_ids) ? $screener_ids : $default_screeners;
        
        return $this->make_request('screener', array(
            'scrIds' => implode(',', $screener_ids),
            'count' => $count
        ));
    }
    
    /**
     * Get spark chart data
     *
     * @param array $symbols Array of symbols
     * @param string $range Time range\n     * @param string $interval Time interval
     * @return array|WP_Error Spark data or error
     */
    public function get_spark($symbols, $range = '1d', $interval = '5m') {
        $symbols_string = is_array($symbols) ? implode(',', $symbols) : $symbols;
        
        return $this->make_request('spark', array(
            'symbols' => $symbols_string,
            'range' => $range,
            'interval' => $interval
        ));
    }
    
    /**
     * Get recommendations
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Recommendations or error
     */
    public function get_recommendations($symbol) {
        return $this->make_request('recommendations', array('symbol' => $symbol));
    }
    
    /**
     * Get insights
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Insights or error
     */
    public function get_insights($symbol) {
        return $this->make_request('insights', array('symbol' => $symbol));
    }
    
    /**
     * Make API request
     *
     * @param string $endpoint Endpoint name
     * @param array $params Request parameters
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $params = array()) {
        if (!class_exists('TradePress_Yahoo_Endpoints')) {
            require_once plugin_dir_path(__FILE__) . 'yahoo-endpoints.php';
        }
        
        $url = TradePress_Yahoo_Endpoints::get_endpoint_url($endpoint, $params, $this->api_base_url);
        
        if (empty($url)) {
            return new WP_Error('invalid_endpoint', 'Invalid endpoint specified');
        }
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error('http_error', 'HTTP Error: ' . $response_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_decode_error', 'Failed to decode JSON response');
        }
        
        // Check for Yahoo Finance specific errors
        if (isset($data['chart']['error'])) {
            return new WP_Error('api_error', $data['chart']['error']['description'] ?? 'API Error');
        }
        
        if (isset($data['finance']['error'])) {
            return new WP_Error('api_error', $data['finance']['error']['description'] ?? 'API Error');
        }
        
        return $data;
    }
}