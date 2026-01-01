<?php
/**
 * TradePress TradingView API Endpoints
 *
 * Defines endpoints and parameters for the TradingView platform
 * API Documentation: https://www.tradingview.com/brokerage-integration/
 * 
 * @package TradePress
 * @subpackage API\TradingView
 * @version 1.0.0
 * @since 2025-04-10
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress TradingView API Endpoints class
 */
class TradePress_TradingView_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Chart Data Endpoints
            'chart_data' => array(
                'endpoint' => '/chart/data',
                'method' => 'GET',
                'description' => 'Get chart data for a symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'resolution' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Chart resolution/timeframe',
                        'enum' => array('1', '5', '15', '30', '60', '240', 'D', 'W', 'M'),
                        'example' => 'D'
                    ),
                    'from' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Unix timestamp (seconds) for from date',
                        'example' => 1617235200
                    ),
                    'to' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Unix timestamp (seconds) for to date',
                        'example' => 1717580800
                    ),
                    'countback' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of bars to return',
                        'example' => 300
                    ),
                ),
                'example_response' => array(
                    's' => 'ok',
                    't' => [1717494400, 1717408000, 1717321600],
                    'o' => [179.25, 178.08, 177.92],
                    'h' => [180.45, 179.43, 179.25],
                    'l' => [178.76, 177.85, 177.45],
                    'c' => [179.92, 178.85, 178.10],
                    'v' => [46378900, 45870100, 43265400]
                )
            ),
            
            'symbols_search' => array(
                'endpoint' => '/symbols/search',
                'method' => 'GET',
                'description' => 'Search for symbols',
                'parameters' => array(
                    'query' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Search query text',
                        'example' => 'Apple'
                    ),
                    'type' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Symbol type filter',
                        'enum' => array('stock', 'forex', 'crypto', 'futures', 'index', 'cfd', 'bond', 'economic'),
                        'example' => 'stock'
                    ),
                    'exchange' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Exchange filter',
                        'example' => 'NASDAQ'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Maximum number of symbols to return',
                        'default' => 30,
                        'example' => 10
                    )
                ),
                'example_response' => array(
                    array(
                        'symbol' => 'AAPL',
                        'full_name' => 'NASDAQ:AAPL',
                        'description' => 'APPLE INC',
                        'exchange' => 'NASDAQ',
                        'type' => 'stock',
                        'currency_code' => 'USD'
                    ),
                    array(
                        'symbol' => 'AAPL.US',
                        'full_name' => 'NASDAQ:AAPL',
                        'description' => 'APPLE INC',
                        'exchange' => 'NASDAQ',
                        'type' => 'stock',
                        'currency_code' => 'USD'
                    )
                )
            ),
            
            'symbols_info' => array(
                'endpoint' => '/symbols/info',
                'method' => 'GET',
                'description' => 'Get detailed information about a symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol name',
                        'example' => 'AAPL'
                    )
                ),
                'example_response' => array(
                    'name' => 'AAPL',
                    'full_name' => 'NASDAQ:AAPL',
                    'base_name' => array(
                        'symbol' => 'AAPL',
                        'exchange' => 'NASDAQ'
                    ),
                    'pro_name' => 'AAPL',
                    'short_name' => 'AAPL',
                    'exchange' => 'NASDAQ',
                    'listed_exchange' => 'NASDAQ',
                    'type' => 'stock',
                    'currency_code' => 'USD',
                    'description' => 'APPLE INC',
                    'has_intraday' => true,
                    'has_daily' => true,
                    'has_weekly_and_monthly' => true,
                    'session' => '0930-1600',
                    'timezone' => 'America/New_York',
                    'price_scale' => 2
                )
            ),
            
            // Market Data Endpoints
            'quotes' => array(
                'endpoint' => '/quotes',
                'method' => 'GET',
                'description' => 'Get real-time quotes for a symbol',
                'parameters' => array(
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols',
                        'example' => 'AAPL,MSFT,GOOGL'
                    )
                ),
                'example_response' => array(
                    'AAPL' => array(
                        'last' => 179.92,
                        'change' => 1.07,
                        'change_percent' => 0.6,
                        'volume' => 46378900,
                        'previous_close' => 178.85,
                        'open' => 179.25,
                        'high' => 180.45,
                        'low' => 178.76
                    ),
                    'MSFT' => array(
                        'last' => 415.36,
                        'change' => 2.53,
                        'change_percent' => 0.61,
                        'volume' => 25361800,
                        'previous_close' => 412.83,
                        'open' => 413.42,
                        'high' => 416.89,
                        'low' => 412.05
                    )
                )
            ),
            
            // Screener Endpoints
            'screener' => array(
                'endpoint' => '/screener/scan',
                'method' => 'POST',
                'description' => 'Run a stock screener with specified filters',
                'parameters' => array(
                    'filter' => array(
                        'required' => true,
                        'type' => 'array',
                        'description' => 'Array of filter conditions',
                        'example' => array(
                            array(
                                'left' => 'market_cap_basic',
                                'operation' => 'greater',
                                'right' => 1000000000
                            ),
                            array(
                                'left' => 'sector',
                                'operation' => 'equal',
                                'right' => 'Technology'
                            )
                        )
                    ),
                    'options' => array(
                        'required' => false,
                        'type' => 'object',
                        'description' => 'Additional options',
                        'example' => array(
                            'limit' => 50,
                            'offset' => 0,
                            'sort' => array(
                                'sortBy' => 'market_cap_basic',
                                'sortOrder' => 'desc'
                            )
                        )
                    ),
                    'markets' => array(
                        'required' => false,
                        'type' => 'array',
                        'description' => 'Filter by markets',
                        'example' => array('america')
                    ),
                    'columns' => array(
                        'required' => false,
                        'type' => 'array',
                        'description' => 'Columns to include in results',
                        'example' => array(
                            'name', 'close', 'change', 'change_abs', 'volume', 'market_cap_basic', 
                            'price_earnings_ttm', 'earnings_per_share_basic_ttm', 'sector', 'description'
                        )
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'name' => 'AAPL',
                            'close' => 179.92,
                            'change' => 0.6,
                            'change_abs' => 1.07,
                            'volume' => 46378900,
                            'market_cap_basic' => 2760000000000,
                            'price_earnings_ttm' => 29.68,
                            'earnings_per_share_basic_ttm' => 6.06,
                            'sector' => 'Technology',
                            'description' => 'APPLE INC'
                        ),
                        array(
                            'name' => 'MSFT',
                            'close' => 415.36,
                            'change' => 0.61,
                            'change_abs' => 2.53,
                            'volume' => 25361800,
                            'market_cap_basic' => 3090000000000,
                            'price_earnings_ttm' => 35.26,
                            'earnings_per_share_basic_ttm' => 11.78,
                            'sector' => 'Technology',
                            'description' => 'MICROSOFT CORP'
                        )
                    ),
                    'totalCount' => 2
                )
            ),
            
            // Technical Indicators
            'technical_indicators' => array(
                'endpoint' => '/technical/indicators',
                'method' => 'GET',
                'description' => 'Get technical indicator data for a symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'indicators' => array(
                        'required' => true,
                        'type' => 'array',
                        'description' => 'List of indicators with parameters',
                        'example' => array(
                            array(
                                'name' => 'macd',
                                'params' => array(
                                    'fast_length' => 12,
                                    'slow_length' => 26,
                                    'signal_length' => 9
                                )
                            ),
                            array(
                                'name' => 'rsi',
                                'params' => array(
                                    'length' => 14
                                )
                            )
                        )
                    ),
                    'resolution' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Chart resolution/timeframe',
                        'enum' => array('1', '5', '15', '30', '60', '240', 'D', 'W', 'M'),
                        'example' => 'D'
                    ),
                    'from' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Unix timestamp (seconds) for from date',
                        'example' => 1617235200
                    ),
                    'to' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Unix timestamp (seconds) for to date',
                        'example' => 1717580800
                    )
                ),
                'example_response' => array(
                    'macd' => array(
                        'timestamp' => [1717494400, 1717408000, 1717321600],
                        'macd_line' => [1.244, 1.127, 0.985],
                        'signal_line' => [0.958, 0.912, 0.876],
                        'histogram' => [0.286, 0.215, 0.109]
                    ),
                    'rsi' => array(
                        'timestamp' => [1717494400, 1717408000, 1717321600],
                        'value' => [58.76, 56.53, 54.87]
                    )
                )
            ),
            
            // Economic Calendar Endpoints
            'economic_calendar' => array(
                'endpoint' => '/economic-calendar',
                'method' => 'GET',
                'description' => 'Get economic events calendar',
                'parameters' => array(
                    'from' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD)',
                        'example' => '2025-04-01'
                    ),
                    'to' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD)',
                        'example' => '2025-04-30'
                    ),
                    'importance' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Minimum importance level',
                        'enum' => array('low', 'medium', 'high'),
                        'default' => 'medium',
                        'example' => 'high'
                    ),
                    'countries' => array(
                        'required' => false,
                        'type' => 'array',
                        'description' => 'Filter by country codes',
                        'example' => array('US', 'EU')
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'id' => '6789',
                            'date' => '2025-04-15T12:30:00Z',
                            'title' => 'US CPI m/m',
                            'country' => 'US',
                            'importance' => 'high',
                            'forecast' => '0.3%',
                            'previous' => '0.2%',
                            'actual' => '0.4%'
                        ),
                        array(
                            'id' => '6790',
                            'date' => '2025-04-16T14:00:00Z',
                            'title' => 'Fed Chair Powell Speech',
                            'country' => 'US',
                            'importance' => 'high',
                            'forecast' => null,
                            'previous' => null,
                            'actual' => null
                        )
                    ),
                    'totalCount' => 2
                )
            ),
            
            // Earnings Calendar Endpoints
            'earnings_calendar' => array(
                'endpoint' => '/earnings-calendar',
                'method' => 'GET',
                'description' => 'Get earnings announcements calendar',
                'parameters' => array(
                    'from' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD)',
                        'example' => '2025-04-01'
                    ),
                    'to' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD)',
                        'example' => '2025-04-30'
                    ),
                    'market_cap_min' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Minimum market cap in billions USD',
                        'example' => 10
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Maximum number of events to return',
                        'default' => 50,
                        'example' => 20
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc',
                            'date' => '2025-04-28',
                            'time' => 'amc',
                            'eps_estimate' => 1.75,
                            'eps_actual' => null,
                            'revenue_estimate' => 95800000000,
                            'revenue_actual' => null,
                            'market_cap' => 2760000000000
                        ),
                        array(
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation',
                            'date' => '2025-04-23',
                            'time' => 'amc',
                            'eps_estimate' => 2.98,
                            'eps_actual' => 3.02,
                            'revenue_estimate' => 56500000000,
                            'revenue_actual' => 57200000000,
                            'market_cap' => 3090000000000
                        )
                    ),
                    'totalCount' => 2
                )
            ),
            
            // News and Analysis Endpoints
            'news' => array(
                'endpoint' => '/news',
                'method' => 'GET',
                'description' => 'Get news articles for a symbol or category',
                'parameters' => array(
                    'symbol' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Symbol ticker (leave empty for general news)',
                        'example' => 'AAPL'
                    ),
                    'category' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'News category',
                        'enum' => array('general', 'stocks', 'forex', 'crypto', 'economy'),
                        'example' => 'stocks'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of news items to retrieve',
                        'default' => 20,
                        'example' => 10
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'id' => '12345',
                            'title' => 'Apple Reports Strong Q2 2025 Earnings',
                            'published_at' => '2025-04-28T16:45:00Z',
                            'url' => 'https://www.tradingview.com/news/12345/',
                            'source' => 'TradingView',
                            'summary' => 'Apple Inc. reported quarterly revenue of $98.5 billion, up 8% year over year...',
                            'tags' => array('AAPL', 'earnings')
                        ),
                        array(
                            'id' => '12346',
                            'title' => 'Apple Unveils New iPad Pro With Advanced AI Features',
                            'published_at' => '2025-04-23T10:30:00Z',
                            'url' => 'https://www.tradingview.com/news/12346/',
                            'source' => 'TradingView',
                            'summary' => 'Apple today announced its latest iPad Pro featuring...',
                            'tags' => array('AAPL', 'product launch')
                        )
                    ),
                    'totalCount' => 2
                )
            ),
            
            'ideas' => array(
                'endpoint' => '/ideas',
                'method' => 'GET',
                'description' => 'Get trading ideas from TradingView community',
                'parameters' => array(
                    'symbol' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by symbol',
                        'example' => 'AAPL'
                    ),
                    'timeframe' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Timeframe filter',
                        'enum' => array('all', '1d', '1w', '1m', '3m'),
                        'default' => 'all',
                        'example' => '1w'
                    ),
                    'sort' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Sort method',
                        'enum' => array('recent', 'popular', 'trending'),
                        'default' => 'recent',
                        'example' => 'trending'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of ideas to retrieve',
                        'default' => 20,
                        'example' => 10
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'id' => '5432',
                            'title' => 'AAPL: Bullish Setup After Pullback',
                            'created_at' => '2025-04-09T08:15:00Z',
                            'user' => array(
                                'username' => 'chart_master',
                                'reputation' => 98
                            ),
                            'symbol' => 'AAPL',
                            'timeframe' => 'D',
                            'direction' => 'long',
                            'likes' => 45,
                            'comments' => 12,
                            'url' => 'https://www.tradingview.com/chart/AAPL/5432/'
                        ),
                        array(
                            'id' => '5433',
                            'title' => 'Apple: Double Bottom Pattern Forming',
                            'created_at' => '2025-04-08T14:23:00Z',
                            'user' => array(
                                'username' => 'technical_trader',
                                'reputation' => 82
                            ),
                            'symbol' => 'AAPL',
                            'timeframe' => 'D',
                            'direction' => 'long',
                            'likes' => 38,
                            'comments' => 8,
                            'url' => 'https://www.tradingview.com/chart/AAPL/5433/'
                        )
                    ),
                    'totalCount' => 2
                )
            ),
            
            // Widget API
            'widget_chart' => array(
                'endpoint' => '/widget/chart',
                'method' => 'GET',
                'description' => 'Generate chart widget embedding code',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker with exchange prefix',
                        'example' => 'NASDAQ:AAPL'
                    ),
                    'interval' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Chart timeframe',
                        'enum' => array('1', '5', '15', '30', '60', '120', '240', 'D', 'W'),
                        'default' => 'D',
                        'example' => 'D'
                    ),
                    'theme' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Chart theme',
                        'enum' => array('light', 'dark'),
                        'default' => 'light',
                        'example' => 'light'
                    ),
                    'width' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Widget width in pixels',
                        'default' => 800,
                        'example' => 800
                    ),
                    'height' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Widget height in pixels',
                        'default' => 500,
                        'example' => 500
                    ),
                    'studies' => array(
                        'required' => false,
                        'type' => 'array',
                        'description' => 'Technical indicators to display',
                        'example' => array('MACD', 'RSI')
                    )
                ),
                'example_response' => array(
                    'html' => '<div class="tradingview-widget-container">...</div>',
                    'script' => '<script type="text/javascript">...</script>'
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
     * @return string Complete endpoint URL
     */
    public static function get_endpoint_url($endpoint_name, $params = array(), $base_url = '') {
        $endpoint = self::get_endpoint($endpoint_name);
        
        if (!$endpoint) {
            return '';
        }
        
        if (empty($base_url)) {
            $base_url = 'https://www.tradingview.com/api'; // Default base URL for TradingView API
        }
        
        $url = $base_url . $endpoint['endpoint'];
        
        // Replace URL parameters (e.g., {param} in /endpoint/{param})
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url = str_replace('{' . $key . '}', urlencode($value), $url);
            }
        }
        
        // For GET requests, add query parameters
        if ($endpoint['method'] === 'GET' && !empty($params)) {
            $query_params = array();
            
            // Extract URL parameters that are already used in path
            $path_params = array();
            preg_match_all('/\{([^}]+)\}/', $endpoint['endpoint'], $matches);
            if (!empty($matches[1])) {
                $path_params = $matches[1];
            }
            
            // Add remaining parameters as query string
            foreach ($params as $key => $value) {
                if (!in_array($key, $path_params)) {
                    $query_params[$key] = $value;
                }
            }
            
            if (!empty($query_params)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query_params);
            }
        }
        
        return $url;
    }
}