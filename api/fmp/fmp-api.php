<?php
/**
 * TradePress Financial Modeling Prep API
 *
 * Handles connection and functionality for the Financial Modeling Prep service
 *
 * @package TradePress
 * @subpackage API\FMP
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Financial Modeling Prep API class
 */
class TradePress_FMP_API {
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://financialmodelingprep.com/api/v3';
    
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
        'name' => 'Financial Modeling Prep',
        'code' => 'fmp',
        'type' => 'financial_data',
        'tier' => 3,
        'status' => 'active',
        'capabilities' => array(
            'real_time_data' => true,
            'historical_data' => true,
            'intraday_data' => true,
            'fundamental_data' => true,
            'financial_statements' => true,
            'company_data' => true,
            'news_data' => true,
            'technical_indicators' => true,
            'analyst_data' => true,
            'earnings_data' => true,
            'dividends_data' => true,
            'splits_data' => true,
            'institutional_data' => true,
            'insider_trading' => true,
            'etf_data' => true,
            'economic_data' => true,
            'screening' => true,
            'market_data' => true
        ),
        'data_types' => array(
            'quote' => 'quote',
            'bars' => 'historical_price',
            'volume' => 'historical_price',
            'intraday' => 'intraday',
            'company_profile' => 'company_profile',
            'company_outlook' => 'company_outlook',
            'income_statement' => 'income_statement',
            'balance_sheet' => 'balance_sheet',
            'cash_flow' => 'cash_flow',
            'key_metrics' => 'key_metrics',
            'financial_ratios' => 'financial_ratios',
            'enterprise_values' => 'enterprise_values',
            'financial_growth' => 'financial_growth',
            'technical_indicators' => 'technical_indicator',
            'news' => 'company_news',
            'market_news' => 'market_news',
            'analyst_estimates' => 'analyst_estimates',
            'price_target' => 'price_target',
            'upgrades_downgrades' => 'upgrades_downgrades',
            'earnings_calendar' => 'earnings_calendar',
            'earnings_surprises' => 'earnings_surprises',
            'dividends' => 'historical_dividends',
            'splits' => 'stock_splits',
            'institutional_holders' => 'institutional_holders',
            'insider_trading' => 'insider_trading',
            'etf_holdings' => 'etf_holdings',
            'screener' => 'stock_screener'
        ),
        'rate_limits' => array(
            'free_daily' => 250,
            'starter_daily' => 10000,
            'professional_daily' => 100000,
            'enterprise_unlimited' => true,
            'burst' => 10
        ),
        'supported_markets' => array('US', 'Global'),
        'pricing' => array(
            'free_tier' => true,
            'min_plan' => 'Starter',
            'cost_per_month' => 14.99
        )
    );
    
    /**
     * Constructor
     *
     * @param string $api_key API key
     */
    public function __construct($api_key = '') {
        $this->api_key = $api_key ?: get_option('tradepress_fmp_api_key', '');
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
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        return $this->make_request('quote', array('symbol' => $symbol));
    }
    
    /**
     * Get historical bars
     *
     * @param string $symbol Stock symbol
     * @param string $from Start date (YYYY-MM-DD)
     * @param string $to End date (YYYY-MM-DD)
     * @return array|WP_Error Historical data or error
     */
    public function get_bars($symbol, $from = '', $to = '') {
        $params = array('symbol' => $symbol);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('historical_price', $params);
    }
    
    /**
     * Get intraday data
     *
     * @param string $symbol Stock symbol
     * @param string $interval Time interval (1min, 5min, 15min, 30min, 1hour, 4hour)
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Intraday data or error
     */
    public function get_intraday($symbol, $interval = '1min', $from = '', $to = '') {
        $params = array('interval' => $interval, 'symbol' => $symbol);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('intraday', $params);
    }
    
    /**
     * Get company profile
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Company profile or error
     */
    public function get_company_profile($symbol) {
        return $this->make_request('company_profile', array('symbol' => $symbol));
    }
    
    /**
     * Get company outlook
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Company outlook or error
     */
    public function get_company_outlook($symbol) {
        return $this->make_request('company_outlook', array('symbol' => $symbol));
    }
    
    /**
     * Get income statement
     *
     * @param string $symbol Stock symbol
     * @param string $period Period (annual, quarter)
     * @param int $limit Limit results
     * @return array|WP_Error Income statement or error
     */
    public function get_income_statement($symbol, $period = 'annual', $limit = 20) {
        return $this->make_request('income_statement', array('symbol' => $symbol, 'period' => $period, 'limit' => $limit));
    }
    
    /**
     * Get balance sheet
     *
     * @param string $symbol Stock symbol
     * @param string $period Period (annual, quarter)
     * @param int $limit Limit results
     * @return array|WP_Error Balance sheet or error
     */
    public function get_balance_sheet($symbol, $period = 'annual', $limit = 20) {
        return $this->make_request('balance_sheet', array('symbol' => $symbol, 'period' => $period, 'limit' => $limit));
    }
    
    /**
     * Get cash flow statement
     *
     * @param string $symbol Stock symbol
     * @param string $period Period (annual, quarter)
     * @param int $limit Limit results
     * @return array|WP_Error Cash flow statement or error
     */
    public function get_cash_flow($symbol, $period = 'annual', $limit = 20) {
        return $this->make_request('cash_flow', array('symbol' => $symbol, 'period' => $period, 'limit' => $limit));
    }
    
    /**
     * Get key metrics
     *
     * @param string $symbol Stock symbol
     * @param string $period Period (annual, quarter)
     * @param int $limit Limit results
     * @return array|WP_Error Key metrics or error
     */
    public function get_key_metrics($symbol, $period = 'annual', $limit = 20) {
        return $this->make_request('key_metrics', array('symbol' => $symbol, 'period' => $period, 'limit' => $limit));
    }
    
    /**
     * Get financial ratios
     *
     * @param string $symbol Stock symbol
     * @param string $period Period (annual, quarter)
     * @param int $limit Limit results
     * @return array|WP_Error Financial ratios or error
     */
    public function get_financial_ratios($symbol, $period = 'annual', $limit = 20) {
        return $this->make_request('financial_ratios', array('symbol' => $symbol, 'period' => $period, 'limit' => $limit));
    }
    
    /**
     * Get technical indicators
     *
     * @param string $symbol Stock symbol
     * @param string $interval Time interval
     * @param string $type Indicator type (sma, ema, rsi, macd, etc.)
     * @param int $period Period
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Technical indicators or error
     */
    public function get_technical_indicators($symbol, $interval = 'daily', $type = 'sma', $period = 20, $from = '', $to = '') {
        $params = array('interval' => $interval, 'symbol' => $symbol, 'type' => $type, 'period' => $period);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('technical_indicator', $params);
    }
    
    /**
     * Get company news
     *
     * @param string $symbol Stock symbol
     * @param int $limit Limit results
     * @return array|WP_Error News data or error
     */
    public function get_news($symbol, $limit = 50) {
        return $this->make_request('company_news', array('tickers' => $symbol, 'limit' => $limit));
    }
    
    /**
     * Get market news
     *
     * @param int $limit Limit results
     * @return array|WP_Error Market news or error
     */
    public function get_market_news($limit = 50) {
        return $this->make_request('market_news', array('limit' => $limit));
    }
    
    /**
     * Get analyst estimates
     *
     * @param string $symbol Stock symbol
     * @param string $period Period (annual, quarter)
     * @param int $limit Limit results
     * @return array|WP_Error Analyst estimates or error
     */
    public function get_analyst_estimates($symbol, $period = 'annual', $limit = 30) {
        return $this->make_request('analyst_estimates', array('symbol' => $symbol, 'period' => $period, 'limit' => $limit));
    }
    
    /**
     * Get price targets
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Price targets or error
     */
    public function get_price_targets($symbol) {
        return $this->make_request('price_target', array('symbol' => $symbol));
    }
    
    /**
     * Get earnings calendar
     *
     * @param string $from Start date
     * @param string $to End date
     * @return array|WP_Error Earnings calendar or error
     */
    public function get_earnings_calendar($from = '', $to = '') {
        $params = array();
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('earnings_calendar', $params);
    }
    
    /**
     * Get dividends data
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Dividends data or error
     */
    public function get_dividends($symbol) {
        return $this->make_request('historical_dividends', array('symbol' => $symbol));
    }
    
    /**
     * Get stock splits
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Stock splits or error
     */
    public function get_splits($symbol) {
        return $this->make_request('stock_splits', array('symbol' => $symbol));
    }
    
    /**
     * Get institutional holders
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Institutional holders or error
     */
    public function get_institutional_holders($symbol) {
        return $this->make_request('institutional_holders', array('symbol' => $symbol));
    }
    
    /**
     * Get insider trading
     *
     * @param string $symbol Stock symbol
     * @return array|WP_Error Insider trading or error
     */
    public function get_insider_trading($symbol) {
        return $this->make_request('insider_trading', array('symbol' => $symbol));
    }
    
    /**
     * Get ETF holdings
     *
     * @param string $symbol ETF symbol
     * @return array|WP_Error ETF holdings or error
     */
    public function get_etf_holdings($symbol) {
        return $this->make_request('etf_holdings', array('symbol' => $symbol));
    }
    
    /**
     * Screen stocks
     *
     * @param array $criteria Screening criteria
     * @return array|WP_Error Screening results or error
     */
    public function screen_stocks($criteria = array()) {
        return $this->make_request('stock_screener', $criteria);
    }
    
    /**
     * Make API request
     *
     * @param string $endpoint Endpoint name
     * @param array $params Request parameters
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $params = array()) {
        if (!class_exists('TradePress_FMP_Endpoints')) {
            require_once plugin_dir_path(__FILE__) . 'fmp-endpoints.php';
        }
        
        // Add API key to params
        if (!empty($this->api_key)) {
            $params['apikey'] = $this->api_key;
        }
        
        $url = TradePress_FMP_Endpoints::get_endpoint_url($endpoint, $params, $this->api_base_url);
        
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
        if (isset($data['Error Message'])) {
            return new WP_Error('api_error', $data['Error Message']);
        }
        
        return $data;
    }
    
    /**
     * Legacy method - Get financial statements
     *
     * @param string $symbol Stock symbol
     * @param string $statement Statement type
     * @return array|WP_Error Financial statements or error
     */
    public function get_financial_statements($symbol, $statement = 'income-statement') {
        switch ($statement) {
            case 'income-statement':
                return $this->get_income_statement($symbol);
            case 'balance-sheet-statement':
                return $this->get_balance_sheet($symbol);
            case 'cash-flow-statement':
                return $this->get_cash_flow($symbol);
            default:
                return $this->get_income_statement($symbol);
        }
    }
}
