<?php
/**
 * TradePress Financial Modeling Prep API Endpoints
 *
 * Defines endpoints and parameters for the Financial Modeling Prep service
 *
 * @package TradePress
 * @subpackage API\FMP
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Financial Modeling Prep API Endpoints class
 */
class TradePress_FMP_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Stock Data
            'quote' => array(
                'path' => 'quote/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get real-time stock quote'
            ),
            'historical_price' => array(
                'path' => 'historical-price-full/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('from', 'to', 'timeseries'),
                'method' => 'GET',
                'description' => 'Get historical stock prices'
            ),
            'intraday' => array(
                'path' => 'historical-chart/{interval}/{symbol}',
                'required_params' => array('interval', 'symbol'),
                'optional_params' => array('from', 'to'),
                'method' => 'GET',
                'description' => 'Get intraday stock prices'
            ),
            
            // Company Information
            'company_profile' => array(
                'path' => 'profile/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get company profile information'
            ),
            'company_outlook' => array(
                'path' => 'company-outlook/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get comprehensive company outlook'
            ),
            
            // Financial Statements
            'income_statement' => array(
                'path' => 'income-statement/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'limit'),
                'method' => 'GET',
                'description' => 'Get company income statements'
            ),
            'balance_sheet' => array(
                'path' => 'balance-sheet-statement/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'limit'),
                'method' => 'GET',
                'description' => 'Get company balance sheets'
            ),
            'cash_flow' => array(
                'path' => 'cash-flow-statement/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'limit'),
                'method' => 'GET',
                'description' => 'Get company cash flow statements'
            ),
            
            // Financial Metrics
            'key_metrics' => array(
                'path' => 'key-metrics/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'limit'),
                'method' => 'GET',
                'description' => 'Get company key metrics'
            ),
            'financial_ratios' => array(
                'path' => 'ratios/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'limit'),
                'method' => 'GET',
                'description' => 'Get company financial ratios'
            ),
            'enterprise_values' => array(
                'path' => 'enterprise-values/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'limit'),
                'method' => 'GET',
                'description' => 'Get enterprise values'
            ),
            'financial_growth' => array(
                'path' => 'financial-growth/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'limit'),
                'method' => 'GET',
                'description' => 'Get financial growth metrics'
            ),
            
            // Technical Indicators
            'technical_indicator' => array(
                'path' => 'technical_indicator/{interval}/{symbol}',
                'required_params' => array('interval', 'symbol'),
                'optional_params' => array('type', 'period', 'from', 'to'),
                'method' => 'GET',
                'description' => 'Get technical indicators'
            ),
            
            // News & Analysis
            'market_news' => array(
                'path' => 'stock_news',
                'optional_params' => array('tickers', 'limit'),
                'method' => 'GET',
                'description' => 'Get latest market news'
            ),
            'company_news' => array(
                'path' => 'stock_news',
                'required_params' => array('tickers'),
                'optional_params' => array('limit'),
                'method' => 'GET',
                'description' => 'Get company specific news'
            ),
            'analyst_estimates' => array(
                'path' => 'analyst-estimates/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'limit'),
                'method' => 'GET',
                'description' => 'Get analyst estimates'
            ),
            'price_target' => array(
                'path' => 'price-target/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get analyst price targets'
            ),
            'upgrades_downgrades' => array(
                'path' => 'upgrades-downgrades/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get analyst upgrades and downgrades'
            ),
            
            // Earnings & Calendar
            'earnings_calendar' => array(
                'path' => 'earning_calendar',
                'optional_params' => array('from', 'to'),
                'method' => 'GET',
                'description' => 'Get earnings calendar'
            ),
            'earnings_surprises' => array(
                'path' => 'earnings-surprises/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get earnings surprises'
            ),
            'dividends_calendar' => array(
                'path' => 'stock_dividend_calendar',
                'optional_params' => array('from', 'to'),
                'method' => 'GET',
                'description' => 'Get dividends calendar'
            ),
            'historical_dividends' => array(
                'path' => 'historical-price-full/stock_dividend/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get historical dividends'
            ),
            'stock_splits' => array(
                'path' => 'historical-price-full/stock_split/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get stock splits'
            ),
            
            // Market Data
            'market_cap' => array(
                'path' => 'market-capitalization/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get market capitalization'
            ),
            'sector_performance' => array(
                'path' => 'sectors-performance',
                'method' => 'GET',
                'description' => 'Get sector performance'
            ),
            'gainers' => array(
                'path' => 'gainers',
                'method' => 'GET',
                'description' => 'Get top gainers'
            ),
            'losers' => array(
                'path' => 'losers',
                'method' => 'GET',
                'description' => 'Get top losers'
            ),
            'most_active' => array(
                'path' => 'actives',
                'method' => 'GET',
                'description' => 'Get most active stocks'
            ),
            
            // Institutional & Insider
            'institutional_holders' => array(
                'path' => 'institutional-holder/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get institutional holders'
            ),
            'insider_trading' => array(
                'path' => 'insider-trading/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get insider trading data'
            ),
            
            // ETF Data
            'etf_holdings' => array(
                'path' => 'etf-holder/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get ETF holdings'
            ),
            'etf_sector_weightings' => array(
                'path' => 'etf-sector-weightings/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get ETF sector weightings'
            ),
            
            // Economic Data
            'economic_calendar' => array(
                'path' => 'economic_calendar',
                'optional_params' => array('from', 'to'),
                'method' => 'GET',
                'description' => 'Get economic calendar'
            ),
            'market_risk_premium' => array(
                'path' => 'market_risk_premium',
                'method' => 'GET',
                'description' => 'Get market risk premium'
            ),
            
            // Screening
            'stock_screener' => array(
                'path' => 'stock-screener',
                'optional_params' => array('marketCapMoreThan', 'marketCapLowerThan', 'priceMoreThan', 'priceLowerThan', 'betaMoreThan', 'betaLowerThan', 'volumeMoreThan', 'volumeLowerThan', 'dividendMoreThan', 'dividendLowerThan', 'isEtf', 'isActivelyTrading', 'sector', 'industry', 'country', 'exchange', 'limit'),
                'method' => 'GET',
                'description' => 'Screen stocks based on criteria'
            )
        );
    }
    
    /**
     * Get endpoint configuration by name
     *
     * @param string $endpoint_name The name of the endpoint
     * @return array|false Endpoint configuration or false if not found
     */
    public static function get_endpoint($endpoint_name) {
        $endpoints = self::get_endpoints();
        return isset($endpoints[$endpoint_name]) ? $endpoints[$endpoint_name] : false;
    }
    
    /**
     * Get endpoint URL
     *
     * @param string $endpoint_name The name of the endpoint
     * @param array $params Parameters to include in the URL
     * @param string $base_url Base API URL
     * @return string Complete endpoint URL
     */
    public static function get_endpoint_url($endpoint_name, $params = array(), $base_url = '') {
        $endpoint = self::get_endpoint($endpoint_name);
        
        if (!$endpoint) {
            return '';
        }
        
        // Use provided base URL or default
        if (empty($base_url)) {
            $base_url = 'https://financialmodelingprep.com/api/v3';
        }
        $base_url = rtrim($base_url, '/') . '/';
        
        $path = $endpoint['path'];
        $query_args = array();
        
        // Add API key
        $api_key = get_option('tradepress_fmp_api_key', '');
        if (!empty($api_key)) {
            $query_args['apikey'] = $api_key;
        }
        
        // Replace path parameters with actual values
        if (!empty($endpoint['required_params'])) {
            foreach ($endpoint['required_params'] as $param) {
                if (isset($params[$param])) {
                    $path = str_replace('{'.$param.'}', $params[$param], $path);
                    unset($params[$param]);
                } else {
                    return ''; // Missing required parameter
                }
            }
        }
        
        // Add remaining parameters as query arguments
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (
                    (isset($endpoint['optional_params']) && in_array($key, $endpoint['optional_params'])) || 
                    !isset($endpoint['optional_params'])
                ) {
                    $query_args[$key] = $value;
                }
            }
        }
        
        // Build final URL
        $url = $base_url . $path;
        
        // Add query string if we have arguments
        if (!empty($query_args)) {
            $url = add_query_arg($query_args, $url);
        }
        
        return $url;
    }
}
