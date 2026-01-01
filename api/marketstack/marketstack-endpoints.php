<?php
/**
 * TradePress MarketStack API Endpoints
 *
 * Defines endpoints and parameters for the MarketStack financial data service
 * API Documentation: https://marketstack.com/documentation
 * 
 * @package TradePress
 * @subpackage API\MarketStack
 * @version 1.0.0
 * @since 2025-04-10
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress MarketStack API Endpoints class
 */
class TradePress_MarketStack_Endpoints {
    
    /**
     * API Restrictions and Rate Limits
     * 
     * Based on MarketStack API documentation
     * 
     * @return array API restrictions information
     */
    public static function get_api_restrictions() {
        return array(
            'rate_limits' => array(
                'description' => 'Maximum number of requests per time window',
                'details' => array(
                    'free_tier' => '100 requests per month, 5 requests per second',
                    'basic_tier' => '1,000 requests per month, 5 requests per second',
                    'professional_tier' => '10,000 requests per month, 5 requests per second',
                    'business_tier' => '100,000 requests per month, 5 requests per second',
                    'request_limit' => '5 requests per second for all plans'
                )
            ),
            'authentication' => array(
                'description' => 'API Authentication methods',
                'details' => array(
                    'access_key' => 'Query parameter in URL (?access_key=YOUR_ACCESS_KEY)'
                )
            ),
            'environments' => array(
                'description' => 'Available API environments',
                'details' => array(
                    'production' => 'http://api.marketstack.com/v1',
                    'secured' => 'https://api.marketstack.com/v1', // HTTPS available on paid plans
                    'v2_production' => 'http://api.marketstack.com/v2', // Newer API version
                    'v2_secured' => 'https://api.marketstack.com/v2' // HTTPS for newer API version
                )
            ),
            'pagination' => array(
                'description' => 'Pagination parameters',
                'details' => array(
                    'limit' => 'Number of results per page (default: 100, max: 1000)',
                    'offset' => 'Pagination offset (default: 0)'
                )
            )
        );
    }
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // End-of-day Data
            'EOD' => array(
                'path' => 'eod',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'symbols',
                    'date_from',
                    'date_to',
                    'limit',
                    'offset',
                    'exchange',
                    'sort'
                ),
                'description' => 'End-of-day stock price data',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 100,
                        'total' => 253
                    ),
                    'data' => array(
                        array(
                            'open' => 127.82,
                            'high' => 129.19,
                            'low' => 127.11,
                            'close' => 128.95,
                            'volume' => 83804191,
                            'adj_high' => 129.19,
                            'adj_low' => 127.11,
                            'adj_close' => 128.95,
                            'adj_open' => 127.82,
                            'adj_volume' => 83804191,
                            'split_factor' => 1,
                            'dividend' => 0,
                            'symbol' => 'AAPL',
                            'exchange' => 'XNAS',
                            'date' => '2021-03-04T00:00:00+0000'
                        )
                    )
                )
            ),
            'EOD_LATEST' => array(
                'path' => 'eod/latest',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'symbols',
                    'limit',
                    'offset',
                    'exchange',
                    'sort'
                ),
                'description' => 'Latest end-of-day stock price data',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 1,
                        'total' => 1
                    ),
                    'data' => array(
                        array(
                            'open' => 127.82,
                            'high' => 129.19,
                            'low' => 127.11,
                            'close' => 128.95,
                            'volume' => 83804191,
                            'adj_high' => 129.19,
                            'adj_low' => 127.11,
                            'adj_close' => 128.95,
                            'adj_open' => 127.82,
                            'adj_volume' => 83804191,
                            'split_factor' => 1,
                            'dividend' => 0,
                            'symbol' => 'AAPL',
                            'exchange' => 'XNAS',
                            'date' => '2025-04-09T00:00:00+0000'
                        )
                    )
                )
            ),
            
            // Intraday Data
            'INTRADAY' => array(
                'path' => 'intraday',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'symbols',
                    'date_from',
                    'date_to',
                    'limit',
                    'offset',
                    'exchange',
                    'interval',
                    'sort'
                ),
                'description' => 'Intraday stock price data at specified intervals',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 100,
                        'total' => 8562
                    ),
                    'data' => array(
                        array(
                            'open' => 127.82,
                            'high' => 127.89,
                            'low' => 127.75,
                            'close' => 127.85,
                            'volume' => 842501,
                            'symbol' => 'AAPL',
                            'exchange' => 'XNAS',
                            'date' => '2025-04-10T14:30:00+0000'
                        )
                    )
                )
            ),
            'INTRADAY_LATEST' => array(
                'path' => 'intraday/latest',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'symbols',
                    'limit',
                    'offset',
                    'exchange',
                    'interval',
                    'sort'
                ),
                'description' => 'Latest intraday stock price data',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 1,
                        'total' => 1
                    ),
                    'data' => array(
                        array(
                            'open' => 127.82,
                            'high' => 127.89,
                            'low' => 127.75,
                            'close' => 127.85,
                            'volume' => 842501,
                            'symbol' => 'AAPL',
                            'exchange' => 'XNAS',
                            'date' => '2025-04-10T14:30:00+0000'
                        )
                    )
                )
            ),
            
            // Ticker Data
            'TICKERS' => array(
                'path' => 'tickers',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'symbols',
                    'exchange',
                    'limit',
                    'offset',
                    'search'
                ),
                'description' => 'Stock ticker data for companies and indices',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 1,
                        'total' => 1
                    ),
                    'data' => array(
                        array(
                            'name' => 'Apple Inc',
                            'symbol' => 'AAPL',
                            'has_intraday' => true,
                            'has_eod' => true,
                            'country' => null,
                            'stock_exchange' => array(
                                'name' => 'NASDAQ Stock Exchange',
                                'acronym' => 'NASDAQ',
                                'mic' => 'XNAS',
                                'country' => 'USA',
                                'country_code' => 'US',
                                'city' => 'New York',
                                'website' => 'www.nasdaq.com'
                            )
                        )
                    )
                )
            ),
            'TICKER_EOD' => array(
                'path' => 'tickers/{symbol}/eod',
                'method' => 'GET',
                'required_params' => array('symbol'),
                'optional_params' => array(
                    'date_from',
                    'date_to',
                    'limit',
                    'offset',
                    'sort'
                ),
                'description' => 'End-of-day data for a specific ticker',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 100,
                        'total' => 253
                    ),
                    'data' => array(
                        array(
                            'open' => 127.82,
                            'high' => 129.19,
                            'low' => 127.11,
                            'close' => 128.95,
                            'volume' => 83804191,
                            'adj_high' => 129.19,
                            'adj_low' => 127.11,
                            'adj_close' => 128.95,
                            'adj_open' => 127.82,
                            'adj_volume' => 83804191,
                            'split_factor' => 1,
                            'dividend' => 0,
                            'symbol' => 'AAPL',
                            'exchange' => 'XNAS',
                            'date' => '2025-04-09T00:00:00+0000'
                        )
                    )
                )
            ),
            'TICKER_INTRADAY' => array(
                'path' => 'tickers/{symbol}/intraday',
                'method' => 'GET',
                'required_params' => array('symbol'),
                'optional_params' => array(
                    'date_from',
                    'date_to',
                    'limit',
                    'offset',
                    'interval',
                    'sort'
                ),
                'description' => 'Intraday data for a specific ticker',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 100,
                        'total' => 8562
                    ),
                    'data' => array(
                        array(
                            'open' => 127.82,
                            'high' => 127.89,
                            'low' => 127.75,
                            'close' => 127.85,
                            'volume' => 842501,
                            'symbol' => 'AAPL',
                            'exchange' => 'XNAS',
                            'date' => '2025-04-10T14:30:00+0000'
                        )
                    )
                )
            ),
            
            // Dividends Data
            'DIVIDENDS' => array(
                'path' => 'dividends',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'symbols',
                    'date_from',
                    'date_to',
                    'limit',
                    'offset',
                    'sort'
                ),
                'description' => 'Dividend data for stocks',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 1,
                        'total' => 1
                    ),
                    'data' => array(
                        array(
                            'date' => '2025-02-07',
                            'dividend' => 0.22,
                            'symbol' => 'AAPL'
                        )
                    )
                )
            ),
            
            // Splits Data
            'SPLITS' => array(
                'path' => 'splits',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'symbols',
                    'date_from',
                    'date_to',
                    'limit',
                    'offset',
                    'sort'
                ),
                'description' => 'Stock split data',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 1,
                        'total' => 1
                    ),
                    'data' => array(
                        array(
                            'date' => '2024-08-28',
                            'split_factor' => 4.0,
                            'symbol' => 'AAPL'
                        )
                    )
                )
            ),
            
            // Exchange Data
            'EXCHANGES' => array(
                'path' => 'exchanges',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'limit',
                    'offset'
                ),
                'description' => 'Stock exchange data',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 71,
                        'total' => 71
                    ),
                    'data' => array(
                        array(
                            'name' => 'NASDAQ Stock Exchange',
                            'acronym' => 'NASDAQ',
                            'mic' => 'XNAS',
                            'country' => 'USA',
                            'country_code' => 'US',
                            'city' => 'New York',
                            'website' => 'www.nasdaq.com',
                            'timezone' => array(
                                'timezone' => 'America/New_York',
                                'abbr' => 'EST',
                                'abbr_dst' => 'EDT'
                            )
                        )
                    )
                )
            ),
            
            // Currency Data
            'CURRENCIES' => array(
                'path' => 'currencies',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'limit',
                    'offset'
                ),
                'description' => 'Currency data',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 40,
                        'total' => 40
                    ),
                    'data' => array(
                        array(
                            'code' => 'USD',
                            'name' => 'US Dollar',
                            'symbol' => '$',
                            'symbol_native' => '$'
                        )
                    )
                )
            ),
            
            // Timezone Data
            'TIMEZONES' => array(
                'path' => 'timezones',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'limit',
                    'offset'
                ),
                'description' => 'Timezone data',
                'example_response' => array(
                    'pagination' => array(
                        'limit' => 100,
                        'offset' => 0,
                        'count' => 100,
                        'total' => 430
                    ),
                    'data' => array(
                        array(
                            'timezone' => 'America/New_York',
                            'abbr' => 'EST',
                            'abbr_dst' => 'EDT',
                            'is_dst' => false
                        )
                    )
                )
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
     * @param bool $use_https Whether to use HTTPS encryption (available on paid plans)
     * @return string Complete endpoint URL
     */
    public static function get_endpoint_url($endpoint_name, $params = array(), $base_url = '', $use_https = false) {
        $endpoint = self::get_endpoint($endpoint_name);
        
        if (!$endpoint) {
            return '';
        }
        
        // Use the provided base_url or construct based on protocol preference
        if (empty($base_url)) {
            if ($use_https) {
                $base_url = 'https://api.marketstack.com/v1';
            } else {
                $base_url = 'http://api.marketstack.com/v1';
            }
        }
        
        // Build the path with parameter substitution
        $path = $endpoint['path'];
        
        // Process required parameters in path
        if (isset($endpoint['required_params'])) {
            foreach ($endpoint['required_params'] as $param) {
                if (isset($params[$param])) {
                    $path = str_replace('{' . $param . '}', $params[$param], $path);
                    unset($params[$param]); // Remove it so we don't add it as a query param
                } else {
                    // Missing required parameter
                    return '';
                }
            }
        }
        
        // Start building query parameters
        $query_params = array();
        
        // Add API access key from options
        $access_key = get_option('tradepress_marketstack_access_key', '');
        if (!empty($access_key)) {
            $query_params['access_key'] = $access_key;
        }
        
        // Add remaining optional parameters
        if (isset($endpoint['optional_params'])) {
            foreach ($endpoint['optional_params'] as $param) {
                if (isset($params[$param])) {
                    $query_params[$param] = $params[$param];
                }
            }
        }
        
        // Build the URL
        $url = $base_url . '/' . $path;
        
        // Add query parameters if any
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        
        return $url;
    }
}