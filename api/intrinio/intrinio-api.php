<?php
/**
 * TradePress Intrinio API
 *
 * Handles connection and functionality for the Intrinio financial data service
 *
 * @package TradePress
 * @subpackage API\Intrinio
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Intrinio API class
 */
class TradePress_Intrinio_API {
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://api-v2.intrinio.com';
    
    /**
     * API key
     *
     * @var string
     */
    private $api_key;
    
    /**
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'Intrinio',
        'code' => 'intrinio',
        'type' => 'financial_data',
        'tier' => 1,
        'status' => 'active',
        'capabilities' => array(
            'real_time_data' => true,
            'historical_data' => true,
            'intraday_data' => true,
            'fundamental_data' => true,
            'company_data' => true,
            'news_data' => true,
            'technical_indicators' => true,
            'options_data' => true,
            'etf_data' => true,
            'analyst_ratings' => true,
            'institutional_ownership' => true,
            'beta_calculations' => true
        ),
        'data_types' => array(
            'quote' => 'REALTIME_PRICE',
            'bars' => 'SECURITY_PRICES',
            'volume' => 'SECURITY_PRICES',
            'intraday' => 'INTRADAY_PRICES',
            'fundamentals' => 'COMPANY_FUNDAMENTALS',
            'financials' => 'FINANCIALS',
            'company_details' => 'COMPANY_DETAILS',
            'news' => 'COMPANY_NEWS',
            'rsi' => 'RELATIVE_STRENGTH_INDEX',
            'sma' => 'SIMPLE_MOVING_AVERAGE',
            'bollinger_bands' => 'BOLLINGER_BANDS',
            'beta' => 'SECURITY_BETAS',
            'options_chain' => 'OPTIONS_CHAIN',
            'options_expirations' => 'OPTIONS_EXPIRATIONS',
            'option_prices' => 'OPTION_PRICES',
            'etf_holdings' => 'ETF_HOLDINGS',
            'analyst_ratings' => 'ZACKS_ANALYST_RATINGS',
            'institutional_ownership' => 'INSTITUTIONAL_OWNERSHIP',
            'securities_search' => 'SECURITIES_SEARCH',
            'exchanges' => 'EXCHANGES'
        ),
        'rate_limits' => array(
            'starter_daily' => 100,
            'standard_daily' => 10000,
            'professional_custom' => true,
            'max_concurrent' => 5,
            'burst' => 5
        ),
        'supported_markets' => array('US', 'Global'),
        'pricing' => array(
            'free_tier' => false,
            'min_plan' => 'Starter',
            'cost_per_month' => 50.00
        )
    );
    
    /**
     * Constructor
     *
     * @param string $api_key API key
     */
    public function __construct($api_key = '') {
        $this->api_key = $api_key ?: get_option('tradepress_intrinio_api_key', '');
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
     * Get quote data (realtime price)
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        return $this->make_request('REALTIME_PRICE', array('identifier' => $symbol));
    }
    
    /**
     * Get historical bars (security prices)
     *
     * @param string $symbol Stock symbol
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param string $frequency Frequency (daily, weekly, monthly)
     * @return array|WP_Error Historical data or error
     */
    public function get_bars($symbol, $start_date = '', $end_date = '', $frequency = 'daily') {
        $params = array('identifier' => $symbol, 'frequency' => $frequency);
        if ($start_date) $params['start_date'] = $start_date;
        if ($end_date) $params['end_date'] = $end_date;
        
        return $this->make_request('SECURITY_PRICES', $params);
    }
    
    /**
     * Get intraday data
     *
     * @param string $symbol Stock symbol
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param string $interval Interval (1min, 5min, etc.)
     * @return array|WP_Error Intraday data or error
     */
    public function get_intraday($symbol, $start_date = '', $end_date = '', $interval = '1min') {
        $params = array('identifier' => $symbol, 'interval' => $interval);
        if ($start_date) $params['start_date'] = $start_date;
        if ($end_date) $params['end_date'] = $end_date;
        
        return $this->make_request('INTRADAY_PRICES', $params);
    }
    
    /**
     * Get company fundamentals
     *
     * @param string $symbol Stock symbol
     * @param string $statement Statement type (income_statement, balance_sheet, cash_flow_statement)
     * @param int $fiscal_year Fiscal year
     * @param string $fiscal_period Fiscal period (FY, Q1, Q2, Q3, Q4)
     * @return array|WP_Error Fundamentals data or error
     */
    public function get_fundamentals($symbol, $statement = '', $fiscal_year = null, $fiscal_period = '') {
        $params = array('identifier' => $symbol);
        if ($statement) $params['statement'] = $statement;
        if ($fiscal_year) $params['fiscal_year'] = $fiscal_year;
        if ($fiscal_period) $params['fiscal_period'] = $fiscal_period;
        
        return $this->make_request('COMPANY_FUNDAMENTALS', $params);
    }
    
    /**
     * Get financial data for a fundamental
     *
     * @param string $fundamental_id Fundamental ID
     * @return array|WP_Error Financial data or error
     */
    public function get_financials($fundamental_id) {
        return $this->make_request('FINANCIALS', array('fundamental_id' => $fundamental_id));
    }
    
    /**
     * Get company details
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Company data or error
     */
    public function get_company_details($symbol) {
        return $this->make_request('COMPANY_DETAILS', array('identifier' => $symbol));
    }
    
    /**
     * Get company news
     *
     * @param string $symbol Stock symbol
     * @param int $page_size Page size
     * @return array|WP_Error News data or error
     */
    public function get_news($symbol, $page_size = 100) {
        return $this->make_request('COMPANY_NEWS', array('identifier' => $symbol, 'page_size' => $page_size));
    }
    
    /**
     * Get RSI indicator
     *
     * @param string $symbol Stock symbol
     * @param int $period RSI period
     * @param string $price_key Price key (close, open, high, low)
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array|WP_Error RSI data or error
     */
    public function get_rsi($symbol, $period = 14, $price_key = 'close', $start_date = '', $end_date = '') {
        $params = array('identifier' => $symbol, 'period' => $period, 'price_key' => $price_key);
        if ($start_date) $params['start_date'] = $start_date;
        if ($end_date) $params['end_date'] = $end_date;
        
        return $this->make_request('RELATIVE_STRENGTH_INDEX', $params);
    }
    
    /**
     * Get SMA indicator
     *
     * @param string $symbol Stock symbol
     * @param int $period SMA period
     * @param string $price_key Price key (close, open, high, low)
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array|WP_Error SMA data or error
     */
    public function get_sma($symbol, $period = 20, $price_key = 'close', $start_date = '', $end_date = '') {
        $params = array('identifier' => $symbol, 'period' => $period, 'price_key' => $price_key);
        if ($start_date) $params['start_date'] = $start_date;
        if ($end_date) $params['end_date'] = $end_date;
        
        return $this->make_request('SIMPLE_MOVING_AVERAGE', $params);
    }
    
    /**
     * Get Bollinger Bands
     *
     * @param string $symbol Stock symbol
     * @param int $period Period
     * @param float $standard_deviations Standard deviations
     * @param string $price_key Price key
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array|WP_Error Bollinger Bands data or error
     */
    public function get_bollinger_bands($symbol, $period = 20, $standard_deviations = 2.0, $price_key = 'close', $start_date = '', $end_date = '') {
        $params = array('identifier' => $symbol, 'period' => $period, 'standard_deviations' => $standard_deviations, 'price_key' => $price_key);
        if ($start_date) $params['start_date'] = $start_date;
        if ($end_date) $params['end_date'] = $end_date;
        
        return $this->make_request('BOLLINGER_BANDS', $params);
    }
    
    /**
     * Get beta values
     *
     * @param string $symbol Stock symbol
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param string $frequency Frequency
     * @return array|WP_Error Beta data or error
     */
    public function get_beta($symbol, $start_date = '', $end_date = '', $frequency = 'daily') {
        $params = array('identifier' => $symbol, 'frequency' => $frequency);
        if ($start_date) $params['start_date'] = $start_date;
        if ($end_date) $params['end_date'] = $end_date;
        
        return $this->make_request('SECURITY_BETAS', $params);
    }
    
    /**
     * Get options expirations
     *
     * @param string $symbol Underlying symbol
     * @param string $after After date
     * @param string $before Before date
     * @return array|WP_Error Options expirations or error
     */
    public function get_options_expirations($symbol, $after = '', $before = '') {
        $params = array('underlying' => $symbol);
        if ($after) $params['after'] = $after;
        if ($before) $params['before'] = $before;
        
        return $this->make_request('OPTIONS_EXPIRATIONS', $params);
    }
    
    /**
     * Get options chain
     *
     * @param string $symbol Underlying symbol
     * @param string $expiration Expiration date
     * @param float $strike Strike price
     * @param string $type Option type (call, put)
     * @return array|WP_Error Options chain or error
     */
    public function get_options_chain($symbol, $expiration, $strike = null, $type = '') {
        $params = array('underlying' => $symbol, 'expiration' => $expiration);
        if ($strike) $params['strike'] = $strike;
        if ($type) $params['type'] = $type;
        
        return $this->make_request('OPTIONS_CHAIN', $params);
    }
    
    /**
     * Get option prices
     *
     * @param string $option_id Option identifier
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array|WP_Error Option prices or error
     */
    public function get_option_prices($option_id, $start_date = '', $end_date = '') {
        $params = array('identifier' => $option_id);
        if ($start_date) $params['start_date'] = $start_date;
        if ($end_date) $params['end_date'] = $end_date;
        
        return $this->make_request('OPTION_PRICES', $params);
    }
    
    /**
     * Get ETF holdings
     *
     * @param string $symbol ETF symbol
     * @param string $holder_date Holdings date
     * @return array|WP_Error ETF holdings or error
     */
    public function get_etf_holdings($symbol, $holder_date = '') {
        $params = array('identifier' => $symbol);
        if ($holder_date) $params['holder_date'] = $holder_date;
        
        return $this->make_request('ETF_HOLDINGS', $params);
    }
    
    /**
     * Get analyst ratings
     *
     * @param string $symbol Stock symbol
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array|WP_Error Analyst ratings or error
     */
    public function get_analyst_ratings($symbol, $start_date = '', $end_date = '') {
        $params = array('identifier' => $symbol);
        if ($start_date) $params['start_date'] = $start_date;
        if ($end_date) $params['end_date'] = $end_date;
        
        return $this->make_request('ZACKS_ANALYST_RATINGS', $params);
    }
    
    /**
     * Get institutional ownership
     *
     * @param string $symbol Stock symbol
     * @param string $date Date
     * @return array|WP_Error Institutional ownership or error
     */
    public function get_institutional_ownership($symbol, $date = '') {
        $params = array('identifier' => $symbol);
        if ($date) $params['date'] = $date;
        
        return $this->make_request('INSTITUTIONAL_OWNERSHIP', $params);
    }
    
    /**
     * Search securities
     *
     * @param array $filters Search filters
     * @return array|WP_Error Securities search results or error
     */
    public function search_securities($filters = array()) {
        return $this->make_request('SECURITIES_SEARCH', $filters);
    }
    
    /**
     * Get exchanges
     *
     * @param array $filters Exchange filters
     * @return array|WP_Error Exchanges data or error
     */
    public function get_exchanges($filters = array()) {
        return $this->make_request('EXCHANGES', $filters);
    }
    
    /**
     * Make API request
     *
     * @param string $endpoint Endpoint name
     * @param array $params Request parameters
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $params = array()) {
        if (!class_exists('TradePress_Intrinio_Endpoints')) {
            require_once plugin_dir_path(__FILE__) . 'intrinio-endpoints.php';
        }
        
        $url = TradePress_Intrinio_Endpoints::get_endpoint_url($endpoint, $params, $this->api_base_url);
        
        if (empty($url)) {
            return new WP_Error('invalid_endpoint', 'Invalid endpoint specified');
        }
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':' . $this->api_key),
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
            return new WP_Error('api_error', $data['error']);
        }
        
        return $data;
    }
    
    /**
     * Legacy method - Get company data
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Company data or error
     */
    public function get_company_data($symbol) {
        return $this->get_company_details($symbol);
    }
    
    /**
     * Legacy method - Get security prices
     *
     * @param string $symbol Stock symbol
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array|WP_Error Security prices or error
     */
    public function get_security_prices($symbol, $start_date, $end_date) {
        return $this->get_bars($symbol, $start_date, $end_date);
    }
}
