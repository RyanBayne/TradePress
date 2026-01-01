<?php
/**
 * TradePress AllTick API Endpoints
 *
 * Defines endpoints and parameters for the AllTick market data service
 * API Documentation: https://github.com/alltick/alltick-realtime-forex-crypto-stock-tick-finance-websocket-api
 * Additional Documentation: https://en.apis.alltick.co/
 * 
 * @package TradePress
 * @subpackage API\AllTick
 * @version 1.0.0
 * @since 2025-04-08
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress AllTick API Endpoints class
 */
class TradePress_AllTick_Endpoints {
    
    /**
     * API Restrictions
     * 
     * Based on common financial API restrictions and AllTick specific information.
     * 
     * @return array API restrictions information
     */
    public static function get_api_restrictions() {
        return array(
            'rate_limits' => array(
                'description' => 'Maximum number of requests per time window',
                'details' => array(
                    'default' => '120 requests per minute',
                    'websocket_connections' => '10 concurrent connections',
                    'historical_data' => '60 requests per hour'
                )
            ),
            'authentication' => array(
                'description' => 'API Key requirements',
                'details' => array(
                    'api_key_format' => 'Format: [32-character hex string]-c-app (e.g., 0b9198bc4d1d5b2bb183fe49f94a03ef-c-app)',
                    'header' => 'X-API-KEY',
                    'query_param' => 'apikey'
                )
            ),
            'request_size' => array(
                'description' => 'Limitations on request size and data range',
                'details' => array(
                    'historical_max_days' => '7300 days (20 years)',
                    'batch_symbols' => '25 symbols per request',
                    'max_bars' => '10000 candles per request'
                )
            ),
            'data_formats' => array(
                'description' => 'Supported data formats',
                'details' => array(
                    'json' => 'Default response format',
                    'csv' => 'Available for historical data endpoints'
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
            // REST API Endpoints
            'stock_quote' => array(
                'endpoint' => '/api/v1/quote',
                'method' => 'GET',
                'description' => 'Get real-time quote data for a specific stock',
                'parameters' => array(
                    'code' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Stock symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    )
                ),
                'rate_limit' => '5 requests per second',
                'example_response' => array(
                    'code' => 'AAPL.US',
                    'name' => 'Apple Inc',
                    'price' => 228.75,
                    'change' => 3.25,
                    'change_percent' => 1.44,
                    'volume' => 12450789,
                    'timestamp' => '2025-04-08T14:30:00Z'
                )
            ),
            'market_status' => array(
                'endpoint' => '/api/v1/market/status',
                'method' => 'GET',
                'description' => 'Get current market status (open/closed)',
                'parameters' => array(
                    'exchange' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Exchange code',
                        'example' => 'US'
                    )
                ),
                'rate_limit' => '2 requests per minute',
                'example_response' => array(
                    'exchange' => 'US',
                    'status' => 'open',
                    'next_open' => '2025-04-09T13:30:00Z',
                    'next_close' => '2025-04-08T20:00:00Z'
                )
            ),
            'company_profile' => array(
                'endpoint' => '/api/v1/company',
                'method' => 'GET',
                'description' => 'Get company profile information',
                'parameters' => array(
                    'code' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Stock symbol with exchange suffix',
                        'example' => 'NVDA.US'
                    )
                ),
                'rate_limit' => '5 requests per minute',
                'example_response' => array(
                    'code' => 'NVDA.US',
                    'name' => 'NVIDIA Corporation',
                    'exchange' => 'NASDAQ',
                    'currency' => 'USD',
                    'country' => 'United States',
                    'industry' => 'Semiconductors',
                    'sector' => 'Technology',
                    'employees' => 26196,
                    'website' => 'https://www.nvidia.com',
                    'description' => 'NVIDIA Corporation designs and manufactures computer graphics processors, chipsets, and related multimedia software.'
                )
            ),
            'historical_data' => array(
                'endpoint' => '/api/v1/historical',
                'method' => 'GET',
                'description' => 'Get historical OHLCV data for a specific stock',
                'parameters' => array(
                    'code' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Stock symbol with exchange suffix',
                        'example' => 'MSFT.US'
                    ),
                    'resolution' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Data resolution/interval',
                        'enum' => array('1m', '5m', '15m', '30m', '1h', '4h', '1d', '1w', '1M'),
                        'example' => '1d'
                    ),
                    'from' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'UNIX timestamp for start date',
                        'example' => 1704067200 // 2024-01-01
                    ),
                    'to' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'UNIX timestamp for end date',
                        'example' => 1712534400 // 2024-04-08
                    ),
                    'adjust' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Adjustment type (0: No adjustment, 1: Split adjustment, 2: Dividend adjustment, 3: Both)',
                        'enum' => array(0, 1, 2, 3),
                        'default' => 0,
                        'example' => 3
                    )
                ),
                'rate_limit' => '5 requests per minute',
                'example_response' => array(
                    'code' => 'MSFT.US',
                    'resolution' => '1d',
                    'data' => array(
                        array(
                            'timestamp' => 1712361600, // 2025-04-06
                            'open' => 425.75,
                            'high' => 429.80,
                            'low' => 424.20,
                            'close' => 428.65,
                            'volume' => 15687423
                        ),
                        array(
                            'timestamp' => 1712448000, // 2025-04-07
                            'open' => 428.90,
                            'high' => 432.45,
                            'low' => 427.30,
                            'close' => 431.85,
                            'volume' => 14523698
                        )
                    )
                )
            ),
            'search_symbols' => array(
                'endpoint' => '/api/v1/search',
                'method' => 'GET',
                'description' => 'Search for stock symbols',
                'parameters' => array(
                    'q' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Search query',
                        'example' => 'Apple'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Maximum number of results',
                        'default' => 10,
                        'example' => 5
                    )
                ),
                'rate_limit' => '10 requests per minute',
                'example_response' => array(
                    'results' => array(
                        array(
                            'code' => 'AAPL.US',
                            'name' => 'Apple Inc',
                            'exchange' => 'NASDAQ'
                        ),
                        array(
                            'code' => 'AAPL.L',
                            'name' => 'Apple Inc',
                            'exchange' => 'LSE'
                        )
                    )
                )
            ),
            'news' => array(
                'endpoint' => '/api/v1/news',
                'method' => 'GET',
                'description' => 'Get news articles for a specific company or market',
                'parameters' => array(
                    'code' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Stock symbol with exchange suffix',
                        'example' => 'TSLA.US'
                    ),
                    'category' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'News category',
                        'enum' => array('general', 'forex', 'crypto', 'merger', 'earnings'),
                        'example' => 'earnings'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'format' => 'date',
                        'description' => 'Start date (YYYY-MM-DD)',
                        'example' => '2025-04-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'format' => 'date',
                        'description' => 'End date (YYYY-MM-DD)',
                        'example' => '2025-04-08'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Maximum number of results',
                        'default' => 10,
                        'example' => 20
                    )
                ),
                'rate_limit' => '5 requests per minute',
                'example_response' => array(
                    'news' => array(
                        array(
                            'id' => 12345,
                            'title' => 'Tesla Reports Record Quarterly Deliveries',
                            'summary' => 'Tesla announced record quarterly deliveries of 520,000 vehicles...',
                            'source' => 'AllTick News',
                            'url' => 'https://www.alltick.com/news/12345',
                            'datetime' => '2025-04-07T15:30:00Z',
                            'image' => 'https://www.alltick.com/images/news/12345.jpg',
                            'related' => array('TSLA.US')
                        )
                    )
                )
            ),
            
            // WebSocket API Endpoints
            'tick_data' => array(
                'endpoint' => '/ws/tick',
                'method' => 'WS',
                'description' => 'Subscribe to real-time tick-by-tick data via WebSocket',
                'parameters' => array(
                    'trace' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Trace/request ID',
                        'example' => '1111111111111111111111111'
                    ),
                    'data' => array(
                        'required' => true,
                        'type' => 'object',
                        'description' => 'Request parameters',
                        'properties' => array(
                            'code' => array(
                                'required' => true,
                                'type' => 'string',
                                'description' => 'Stock symbol with exchange suffix',
                                'example' => 'AAPL.US'
                            ),
                            'kline_type' => array(
                                'required' => true,
                                'type' => 'integer',
                                'description' => 'K-line type (1: Time-based, 2: Tick-based)',
                                'enum' => array(1, 2),
                                'example' => 1
                            ),
                            'kline_timestamp_end' => array(
                                'required' => true,
                                'type' => 'integer',
                                'description' => 'End timestamp (0 for latest)',
                                'example' => 0
                            ),
                            'query_kline_num' => array(
                                'required' => true,
                                'type' => 'integer',
                                'description' => 'Number of k-lines to query',
                                'example' => 10
                            ),
                            'adjust_type' => array(
                                'required' => true,
                                'type' => 'integer',
                                'description' => 'Adjustment type',
                                'example' => 0
                            )
                        )
                    )
                ),
                'example_request' => '{"trace":"1111111111111111111111111","data":{"code":"AAPL.US","kline_type":1,"kline_timestamp_end":0,"query_kline_num":10,"adjust_type":0}}',
                'example_response' => array(
                    'trace' => '1111111111111111111111111',
                    'data' => array(
                        'code' => 'AAPL.US',
                        'k_list' => array(
                            array(
                                'timestamp' => 1712577500, // 2025-04-08 14:25:00
                                'open' => 227.50,
                                'high' => 228.75,
                                'low' => 227.40,
                                'close' => 228.65,
                                'volume' => 12450,
                                'amount' => 2836942.50
                            )
                        )
                    )
                )
            ),
            'market_depth' => array(
                'endpoint' => '/ws/depth',
                'method' => 'WS',
                'description' => 'Subscribe to market depth (order book) data via WebSocket',
                'parameters' => array(
                    'trace' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Trace/request ID',
                        'example' => '2222222222222222222222222'
                    ),
                    'data' => array(
                        'required' => true,
                        'type' => 'object',
                        'description' => 'Request parameters',
                        'properties' => array(
                            'code' => array(
                                'required' => true,
                                'type' => 'string',
                                'description' => 'Stock symbol with exchange suffix',
                                'example' => 'AAPL.US'
                            ),
                            'depth' => array(
                                'required' => false,
                                'type' => 'integer',
                                'description' => 'Order book depth (5, 10, 20)',
                                'enum' => array(5, 10, 20),
                                'default' => 10,
                                'example' => 10
                            )
                        )
                    )
                ),
                'example_request' => '{"trace":"2222222222222222222222222","data":{"code":"AAPL.US","depth":10}}',
                'example_response' => array(
                    'trace' => '2222222222222222222222222',
                    'data' => array(
                        'code' => 'AAPL.US',
                        'timestamp' => 1712577805, // 2025-04-08 14:30:05
                        'asks' => array(
                            array(228.75, 100),
                            array(228.80, 250),
                            array(228.85, 500)
                        ),
                        'bids' => array(
                            array(228.70, 150),
                            array(228.65, 300),
                            array(228.60, 450)
                        )
                    )
                )
            ),
            'trade_feed' => array(
                'endpoint' => '/ws/trades',
                'method' => 'WS',
                'description' => 'Subscribe to real-time trade execution feed via WebSocket',
                'parameters' => array(
                    'trace' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Trace/request ID',
                        'example' => '3333333333333333333333333'
                    ),
                    'data' => array(
                        'required' => true,
                        'type' => 'object',
                        'description' => 'Request parameters',
                        'properties' => array(
                            'code' => array(
                                'required' => true,
                                'type' => 'string',
                                'description' => 'Stock symbol with exchange suffix',
                                'example' => 'AAPL.US'
                            )
                        )
                    )
                ),
                'example_request' => '{"trace":"3333333333333333333333333","data":{"code":"AAPL.US"}}',
                'example_response' => array(
                    'trace' => '3333333333333333333333333',
                    'data' => array(
                        'code' => 'AAPL.US',
                        'trades' => array(
                            array(
                                'id' => '9876543210',
                                'price' => 228.75,
                                'volume' => 100,
                                'timestamp' => 1712577810, // 2025-04-08 14:30:10
                                'side' => 'buy'
                            )
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
     * @return string Complete endpoint URL
     */
    public static function get_endpoint_url($endpoint_name, $params = array(), $base_url = '') {
        $endpoint = self::get_endpoint($endpoint_name);
        
        if (!$endpoint) {
            return '';
        }
        
        if (empty($base_url)) {
            $base_url = 'https://api.alltick.com'; // Base URL from the API class
        }
        
        $url = $base_url . $endpoint['endpoint'];
        
        // For WebSocket endpoints, return the WebSocket URL
        if ($endpoint['method'] === 'WS') {
            return str_replace('https://', 'wss://', $base_url) . $endpoint['endpoint'];
        }
        
        // For GET requests, add query parameters
        if ($endpoint['method'] === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}
