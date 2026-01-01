<?php
/**
 * TradePress eToro API Endpoints
 *
 * Defines endpoints and parameters for the eToro trading platform
 * Based on unofficial API documentation and open source implementations
 * 
 * @package TradePress
 * @subpackage API\eToro
 * @version 1.0.0
 * @since 2025-04-09
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress eToro API Endpoints class
 */
class TradePress_Etoro_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Authentication & Account
            'login' => array(
                'endpoint' => '/api/sts/v2/oauth/auth',
                'method' => 'POST',
                'description' => 'Authenticate with eToro and obtain access token',
                'parameters' => array(
                    'username' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'eToro account username or email',
                        'example' => 'example@email.com'
                    ),
                    'password' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'eToro account password',
                        'example' => 'password123'
                    ),
                    'client_request_id' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Client request identifier',
                        'example' => 'web-eb9c5c4d-ef4a-4699-9e6b-85f13b2a2e62'
                    )
                ),
                'example_response' => array(
                    'accessToken' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6IlMwUk...',
                    'expiresIn' => 86400,
                    'refreshToken' => 'c764f3a0-7c5c-4efc-b6db-7f0b40603a1a',
                    'refreshTokenExpiresIn' => 604800
                )
            ),
            
            'account_info' => array(
                'endpoint' => '/api/user/accounts',
                'method' => 'GET',
                'description' => 'Get user account information',
                'parameters' => array(),
                'requires_auth' => true,
                'example_response' => array(
                    'accounts' => array(
                        array(
                            'accountId' => '12345678',
                            'accountName' => 'Real',
                            'accountType' => 'Real',
                            'currency' => 'USD',
                            'balance' => 5000.50,
                            'availableBalance' => 4500.25,
                            'equity' => 5200.75
                        ),
                        array(
                            'accountId' => '87654321',
                            'accountName' => 'Demo',
                            'accountType' => 'Demo',
                            'currency' => 'USD',
                            'balance' => 100000.00,
                            'availableBalance' => 100000.00,
                            'equity' => 100000.00
                        )
                    )
                )
            ),
            
            'account_portfolio' => array(
                'endpoint' => '/api/portfolio',
                'method' => 'GET',
                'description' => 'Get user portfolio information',
                'parameters' => array(
                    'mode' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Account mode (Real or Demo)',
                        'enum' => array('Real', 'Demo'),
                        'default' => 'Real',
                        'example' => 'Demo'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'totalBalance' => 5200.75,
                    'invested' => 2500.00,
                    'unrealizedPnl' => 200.25,
                    'freeBalance' => 2700.75,
                    'positions' => array(
                        // Position information
                    )
                )
            ),
            
            // Watchlist Management
            'watchlist_get' => array(
                'endpoint' => '/api/watchlist',
                'method' => 'GET',
                'description' => 'Get user watchlist',
                'parameters' => array(
                    'mode' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Account mode (Real or Demo)',
                        'enum' => array('Real', 'Demo'),
                        'default' => 'Real',
                        'example' => 'Demo'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'assets' => array(
                        array(
                            'instrumentId' => '100000',
                            'symbol' => 'BTC',
                            'name' => 'Bitcoin',
                            'type' => 'CRYPTO',
                            'exchange' => '',
                            'lastPrice' => 50123.45,
                            'change' => 2.35,
                            'changePercent' => 4.91
                        ),
                        array(
                            'instrumentId' => '10012',
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc',
                            'type' => 'STOCK',
                            'exchange' => 'NASDAQ',
                            'lastPrice' => 175.84,
                            'change' => -1.25,
                            'changePercent' => -0.71
                        )
                    )
                )
            ),
            
            'watchlist_add_by_name' => array(
                'endpoint' => '/api/watchlist/byName',
                'method' => 'PUT',
                'description' => 'Add asset to watchlist by name',
                'parameters' => array(
                    'param' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Asset name or symbol',
                        'example' => 'btc'
                    ),
                    'mode' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Account mode (Real or Demo)',
                        'enum' => array('Real', 'Demo'),
                        'default' => 'Real',
                        'example' => 'Demo'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'status' => 'OK',
                    'instrumentId' => '100000',
                    'instrumentName' => 'Bitcoin'
                )
            ),
            
            'watchlist_add_by_id' => array(
                'endpoint' => '/api/watchlist/byId',
                'method' => 'PUT',
                'description' => 'Add asset to watchlist by ID',
                'parameters' => array(
                    'param' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Asset ID',
                        'example' => '100000'
                    ),
                    'mode' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Account mode (Real or Demo)',
                        'enum' => array('Real', 'Demo'),
                        'default' => 'Real',
                        'example' => 'Demo'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'status' => 'OK',
                    'instrumentId' => '100000',
                    'instrumentName' => 'Bitcoin'
                )
            ),
            
            'watchlist_remove' => array(
                'endpoint' => '/api/watchlist/{instrumentId}',
                'method' => 'DELETE',
                'description' => 'Remove asset from watchlist',
                'parameters' => array(
                    'instrumentId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Asset ID to remove',
                        'example' => '100000'
                    ),
                    'mode' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Account mode (Real or Demo)',
                        'enum' => array('Real', 'Demo'),
                        'default' => 'Real',
                        'example' => 'Demo'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'status' => 'OK',
                    'instrumentId' => '100000'
                )
            ),
            
            // Position Management
            'positions_open' => array(
                'endpoint' => '/api/positions/open',
                'method' => 'POST',
                'description' => 'Open a new trading position',
                'parameters' => array(
                    'name' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Asset name or symbol',
                        'example' => 'btc'
                    ),
                    'type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Position type (BUY or SELL)',
                        'enum' => array('BUY', 'SELL'),
                        'example' => 'BUY'
                    ),
                    'amount' => array(
                        'required' => true,
                        'type' => 'number',
                        'description' => 'Amount to invest',
                        'example' => 100
                    ),
                    'leverage' => array(
                        'required' => true,
                        'type' => 'number',
                        'description' => 'Position leverage',
                        'example' => 2
                    ),
                    'takeProfitRate' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Take profit price level',
                        'example' => 13000
                    ),
                    'stopLossRate' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Stop loss price level',
                        'example' => 8000
                    ),
                    'mode' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Account mode (Real or Demo)',
                        'enum' => array('Real', 'Demo'),
                        'default' => 'Real',
                        'example' => 'Demo'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'date' => '2025-04-09T12:28:53.574543',
                    'requestToken' => 'a859ec38-12bd-41b2-87f3-205515f2d608',
                    'errorMessageCode' => 0,
                    'notificationParams' => null,
                    'position' => array(
                        'leverage' => 2,
                        'stopLossRate' => 8000,
                        'takeProfitRate' => 13000,
                        'amount' => 100,
                        'instrumentID' => '100000',
                        'positionID' => '1621284697',
                        'isBuy' => true,
                        'isTslEnabled' => false,
                        'view_MaxPositionUnits' => 0,
                        'view_Units' => 0,
                        'view_openByUnits' => null,
                        'isDiscounted' => false,
                        'viewRateContext' => null,
                        'openDateTime' => '2025-04-09T12:28:53.3993309',
                        'openRate' => 10100.1
                    )
                )
            ),
            
            'positions_close' => array(
                'endpoint' => '/api/positions/close',
                'method' => 'DELETE',
                'description' => 'Close an existing position',
                'parameters' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Position ID to close',
                        'example' => '1621284697'
                    ),
                    'mode' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Account mode (Real or Demo)',
                        'enum' => array('Real', 'Demo'),
                        'default' => 'Real',
                        'example' => 'Demo'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'status' => 'OK',
                    'positionId' => '1621284697',
                    'profit' => 25.75,
                    'closeRate' => 10350.25
                )
            ),
            
            'positions_list' => array(
                'endpoint' => '/api/positions',
                'method' => 'GET',
                'description' => 'Get list of open positions',
                'parameters' => array(
                    'mode' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Account mode (Real or Demo)',
                        'enum' => array('Real', 'Demo'),
                        'default' => 'Real',
                        'example' => 'Demo'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'positions' => array(
                        array(
                            'positionId' => '1621284697',
                            'instrumentId' => '100000',
                            'symbol' => 'BTC',
                            'name' => 'Bitcoin',
                            'isBuy' => true,
                            'amount' => 100,
                            'leverage' => 2,
                            'stopLossRate' => 8000,
                            'takeProfitRate' => 13000,
                            'openRate' => 10100.1,
                            'currentRate' => 10350.25,
                            'openDateTime' => '2025-04-09T12:28:53.3993309',
                            'profit' => 25.75,
                            'profitPercentage' => 12.88
                        ),
                        array(
                            'positionId' => '1621284698',
                            'instrumentId' => '10012',
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc',
                            'isBuy' => false,
                            'amount' => 200,
                            'leverage' => 1,
                            'stopLossRate' => 190,
                            'takeProfitRate' => 160,
                            'openRate' => 175.84,
                            'currentRate' => 173.21,
                            'openDateTime' => '2025-04-08T14:35:27.1235698',
                            'profit' => 5.26,
                            'profitPercentage' => 1.49
                        )
                    )
                )
            ),
            
            // Market Data
            'market_instrument' => array(
                'endpoint' => '/api/market/instrument/{instrumentId}',
                'method' => 'GET',
                'description' => 'Get detailed information about a specific instrument',
                'parameters' => array(
                    'instrumentId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Instrument ID',
                        'example' => '100000'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'instrumentId' => '100000',
                    'symbol' => 'BTC',
                    'name' => 'Bitcoin',
                    'fullName' => 'Bitcoin / USD',
                    'type' => 'CRYPTO',
                    'exchange' => '',
                    'marketStatus' => 'OPEN',
                    'lastPrice' => 50123.45,
                    'bid' => 50120.15,
                    'ask' => 50126.75,
                    'high' => 51234.56,
                    'low' => 49875.32,
                    'change' => 2.35,
                    'changePercent' => 4.91,
                    'volume' => 12547896.32,
                    'marketCap' => 946789500000.00,
                    'leverageOptions' => array(1, 2, 5, 10),
                    'minPositionAmount' => 25,
                    'maxPositionAmount' => 100000,
                    'tradingHours' => array(
                        'isOpen' => true,
                        'openTime' => '00:00:00',
                        'closeTime' => '24:00:00',
                        'timezone' => 'UTC'
                    )
                )
            ),
            
            'market_search' => array(
                'endpoint' => '/api/market/search',
                'method' => 'GET',
                'description' => 'Search for instruments by name or symbol',
                'parameters' => array(
                    'query' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Search query (name or symbol)',
                        'example' => 'bitcoin'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Maximum number of results',
                        'default' => 10,
                        'example' => 5
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'results' => array(
                        array(
                            'instrumentId' => '100000',
                            'symbol' => 'BTC',
                            'name' => 'Bitcoin',
                            'type' => 'CRYPTO',
                            'exchange' => '',
                            'lastPrice' => 50123.45
                        ),
                        array(
                            'instrumentId' => '100035',
                            'symbol' => 'BTCUSD',
                            'name' => 'Bitcoin USD',
                            'type' => 'CFD',
                            'exchange' => '',
                            'lastPrice' => 50123.45
                        ),
                        array(
                            'instrumentId' => '10487',
                            'symbol' => 'GBTC',
                            'name' => 'Grayscale Bitcoin Trust',
                            'type' => 'ETF',
                            'exchange' => 'NASDAQ',
                            'lastPrice' => 35.78
                        )
                    )
                )
            ),
            
            'market_list' => array(
                'endpoint' => '/api/market/list',
                'method' => 'GET',
                'description' => 'Get list of available markets/categories',
                'parameters' => array(
                    'type' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by instrument type',
                        'enum' => array('STOCK', 'ETF', 'CRYPTO', 'CURRENCY', 'COMMODITY', 'INDEX'),
                        'example' => 'CRYPTO'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'categories' => array(
                        array(
                            'id' => 'stocks',
                            'name' => 'Stocks',
                            'count' => 3000,
                            'subcategories' => array(
                                array('id' => 'tech', 'name' => 'Technology', 'count' => 500),
                                array('id' => 'finance', 'name' => 'Financial', 'count' => 450),
                                array('id' => 'consumer', 'name' => 'Consumer', 'count' => 400)
                            )
                        ),
                        array(
                            'id' => 'crypto',
                            'name' => 'Cryptocurrencies',
                            'count' => 120,
                            'subcategories' => array()
                        ),
                        array(
                            'id' => 'forex',
                            'name' => 'Currencies',
                            'count' => 50,
                            'subcategories' => array()
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
            $base_url = 'https://api.etoro.com'; // Default base URL for eToro API
        }
        
        $url = $base_url . $endpoint['endpoint'];
        
        // Replace URL parameters (e.g., {instrumentId}, {positionId})
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
