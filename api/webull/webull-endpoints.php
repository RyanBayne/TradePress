<?php
/**
 * TradePress WeBull API Endpoints
 *
 * Defines endpoints and parameters for the WeBull trading platform
 * Based on WeBull API documentation and open-source implementations
 * 
 * @package TradePress
 * @subpackage API\WeBull
 * @version 1.0.0
 * @since 2025-04-13
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress WeBull API Endpoints class
 */
class TradePress_WeBull_Endpoints {
    
    /**
     * API Restrictions and Rate Limits
     * 
     * Based on WeBull API behavior and community knowledge
     * 
     * @return array API restrictions information
     */
    public static function get_api_restrictions() {
        return array(
            'rate_limits' => array(
                'description' => 'Maximum number of requests per time window',
                'details' => array(
                    'default' => 'Approximately 60 requests per minute',
                    'market_data' => '120 requests per minute',
                    'order_operations' => '60 requests per minute',
                    'account_data' => '30 requests per minute'
                )
            ),
            'authentication' => array(
                'description' => 'API Authentication methods',
                'details' => array(
                    'oauth' => array(
                        'token_based' => 'Access token obtained through login flow',
                        'device_id' => 'Required for authentication',
                        'trade_token' => 'Required for trading operations'
                    ),
                    'headers' => array(
                        'did' => 'Device ID',
                        'access_token' => 'Access token for authenticated requests'
                    )
                )
            ),
            'environments' => array(
                'description' => 'Available API environments',
                'details' => array(
                    'production' => 'https://quoteapi.webull.com/ & https://userapi.webull.com/',
                    'paper_trading' => 'Available through the same API with different account settings'
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
            // Authentication Endpoints
            'get_app_version' => array(
                'endpoint' => '/api/config/version',
                'method' => 'GET',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Get the current app version information',
                'parameters' => array(),
                'auth_required' => false,
                'example_response' => array(
                    'success' => true,
                    'data' => array(
                        'versionId' => '1.2.3',
                        'versionName' => 'WebTrader',
                        'updateTime' => '2025-04-13T12:00:00Z',
                    )
                )
            ),
            'generate_device_id' => array(
                'endpoint' => '/api/user/getDeviceId',
                'method' => 'GET',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Generate a new device ID for API authentication',
                'parameters' => array(),
                'auth_required' => false,
                'example_response' => array(
                    'id' => 'abcdef1234567890abcdef1234567890'
                )
            ),
            'send_login_code' => array(
                'endpoint' => '/api/passport/account/getVerificationCode',
                'method' => 'POST',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Send login verification code to email or phone',
                'parameters' => array(
                    'account' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Email address or phone number',
                        'example' => 'user@example.com'
                    ),
                    'accountType' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Account type (2 for phone, 3 for email)',
                        'example' => 3
                    ),
                    'deviceId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Device ID for this session',
                        'example' => 'abcdef1234567890abcdef1234567890'
                    )
                ),
                'auth_required' => false,
                'example_response' => array(
                    'success' => true,
                    'data' => array(
                        'accountType' => 3,
                        'codeType' => 5,
                        'cycle' => 60,
                        'status' => 'SENT'
                    )
                )
            ),
            'login' => array(
                'endpoint' => '/api/passport/login/v5/account',
                'method' => 'POST',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Login to WeBull account',
                'parameters' => array(
                    'account' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Email address or phone number',
                        'example' => 'user@example.com'
                    ),
                    'accountType' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Account type (2 for phone, 3 for email)',
                        'example' => 3
                    ),
                    'password' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Password (MD5 encrypted)',
                        'example' => 'e807f1fcf82d132f9bb018ca6738a19f'
                    ),
                    'deviceId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Device ID for this session',
                        'example' => 'abcdef1234567890abcdef1234567890'
                    ),
                    'deviceName' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Device name',
                        'example' => 'TradePress'
                    ),
                    'code' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Verification code (if requested)',
                        'example' => '123456'
                    )
                ),
                'auth_required' => false,
                'example_response' => array(
                    'success' => true,
                    'data' => array(
                        'accessToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
                        'refreshToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
                        'tokenExpireTime' => 1742772000000,
                        'uuid' => '12345678-abcd-1234-abcd-1234567890ab',
                        'username' => 'JohnDoe',
                        'email' => 'user@example.com'
                    )
                )
            ),
            
            // Market Data Endpoints
            'get_ticker_info' => array(
                'endpoint' => '/api/securities/ticker/v5/full',
                'method' => 'GET',
                'base_url' => 'https://quoteapi.webull.com',
                'description' => 'Get detailed information for a ticker symbol',
                'parameters' => array(
                    'tickerId' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Ticker ID',
                        'example' => 913256135
                    )
                ),
                'auth_required' => false,
                'example_response' => array(
                    'tickerId' => 913256135,
                    'exchangeId' => 12,
                    'name' => 'Apple Inc',
                    'symbol' => 'AAPL',
                    'disSymbol' => 'AAPL',
                    'tinyName' => 'Apple',
                    'listStatus' => 1,
                    'currencyId' => 307,
                    'currencyCode' => 'USD',
                    'regionId' => 6,
                    'exchangeCode' => 'NASDAQ',
                    'regionName' => 'US',
                    'marketStatus' => 'open',
                    'timeZone' => 'America/New_York',
                    'pPrice' => 178.84,
                    'pChange' => 0.18,
                    'pChRatio' => 0.1
                )
            ),
            'get_quotes' => array(
                'endpoint' => '/api/quote/v5/real-time',
                'method' => 'GET',
                'base_url' => 'https://quoteapi.webull.com',
                'description' => 'Get real-time quotes for a list of ticker IDs',
                'parameters' => array(
                    'ids' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated list of ticker IDs',
                        'example' => '913256135,913256136'
                    )
                ),
                'auth_required' => false,
                'example_response' => array(
                    array(
                        'tickerId' => 913256135,
                        'lastPrice' => 178.84,
                        'changeRatio' => 0.1,
                        'change' => 0.18,
                        'volume' => 1234567,
                        'open' => 177.50,
                        'high' => 179.25,
                        'low' => 177.33,
                        'preClose' => 178.66,
                        'marketValue' => 2850000000000,
                        'bid' => 178.83,
                        'ask' => 178.85,
                        'bidSize' => 500,
                        'askSize' => 300
                    )
                )
            ),
            'search_ticker' => array(
                'endpoint' => '/api/securities/v5/new-stock/query',
                'method' => 'GET',
                'base_url' => 'https://quoteapi.webull.com',
                'description' => 'Search for ticker symbols',
                'parameters' => array(
                    'keyword' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Search keyword (ticker symbol or company name)',
                        'example' => 'AAPL'
                    ),
                    'regionId' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Region ID (6 for US)',
                        'example' => 6
                    )
                ),
                'auth_required' => false,
                'example_response' => array(
                    'data' => array(
                        array(
                            'tickerId' => 913256135,
                            'exchangeId' => 12,
                            'name' => 'Apple Inc',
                            'symbol' => 'AAPL',
                            'disSymbol' => 'AAPL',
                            'type' => 2,
                            'regionId' => 6,
                            'regionName' => 'US'
                        )
                    )
                )
            ),
            'get_bars' => array(
                'endpoint' => '/api/quote/v5/kline',
                'method' => 'GET',
                'base_url' => 'https://quoteapi.webull.com',
                'description' => 'Get historical price bars for a ticker',
                'parameters' => array(
                    'tickerId' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Ticker ID',
                        'example' => 913256135
                    ),
                    'type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Bar timeframe (m1, m5, m15, m30, h1, h2, h4, d1, w1, mn1)',
                        'example' => 'm5'
                    ),
                    'count' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of bars to return',
                        'example' => 100
                    ),
                    'extendTrading' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Include extended hours (0 or 1)',
                        'example' => 1
                    )
                ),
                'auth_required' => false,
                'example_response' => array(
                    'data' => array(
                        array(
                            't' => 1681383600000, // Timestamp
                            'o' => 165.08, // Open
                            'h' => 166.45, // High
                            'l' => 164.89, // Low
                            'c' => 165.56, // Close
                            'v' => 3456789 // Volume
                        )
                    )
                )
            ),
            
            // Account Endpoints
            'get_account_info' => array(
                'endpoint' => '/api/account/v5/account',
                'method' => 'GET',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Get account information',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'a123456789'
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'id' => 12345678,
                    'accountType' => 'MARGIN',
                    'brokerId' => 5,
                    'brokerName' => 'WeBull Financial',
                    'currencyId' => 307,
                    'currencyCode' => 'USD',
                    'accountMembers' => array(
                        array(
                            'id' => 34567890,
                            'username' => 'JohnDoe',
                            'accountId' => 'a123456789',
                            'email' => 'user@example.com',
                            'phoneCountry' => 'US',
                            'phone' => '5551234567'
                        )
                    )
                )
            ),
            'get_positions' => array(
                'endpoint' => '/api/account/v5/positions',
                'method' => 'GET',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Get current positions',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'a123456789'
                    ),
                    'secAccountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Security Account ID',
                        'example' => '12345678'
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'positions' => array(
                        array(
                            'positionId' => 12345678,
                            'ticker' => array(
                                'tickerId' => 913256135,
                                'symbol' => 'AAPL',
                                'name' => 'Apple Inc',
                                'currencyId' => 307,
                                'currencyCode' => 'USD'
                            ),
                            'position' => 100,
                            'avgPrice' => 150.45,
                            'marketValue' => 17884.00,
                            'costPrice' => 15045.00,
                            'unrealizedProfitLoss' => 2839.00,
                            'unrealizedProfitLossRate' => 0.1887
                        )
                    )
                )
            ),
            'get_account_values' => array(
                'endpoint' => '/api/v2/home/account',
                'method' => 'GET',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Get account balance and values',
                'parameters' => array(
                    'secAccountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Security Account ID',
                        'example' => '12345678'
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'netLiquidation' => 48750.25,
                    'cash' => 30866.25,
                    'moneyMarket' => 0,
                    'settledCash' => 30866.25,
                    'unsettledCash' => 0,
                    'dayBuyingPower' => 123465.00,
                    'overnightBuyingPower' => 61732.50,
                    'currency' => 'USD',
                    'currencyId' => 307
                )
            ),
            'get_orders' => array(
                'endpoint' => '/api/trades/v5/all-orders',
                'method' => 'GET',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Get order history',
                'parameters' => array(
                    'secAccountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Security Account ID',
                        'example' => '12345678'
                    ),
                    'startTime' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Start time (milliseconds since epoch)',
                        'example' => 1680307200000
                    ),
                    'endTime' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'End time (milliseconds since epoch)',
                        'example' => 1681516800000
                    ),
                    'status' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Order status (Filled, Cancelled, Working, Rejected, etc.)',
                        'example' => 'Filled'
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'data' => array(
                        array(
                            'orderId' => 123456789,
                            'accountId' => 'a123456789',
                            'secAccountId' => '12345678',
                            'ticker' => array(
                                'tickerId' => 913256135,
                                'symbol' => 'AAPL',
                                'name' => 'Apple Inc'
                            ),
                            'action' => 'BUY',
                            'orderType' => 'LMT',
                            'timeInForce' => 'DAY',
                            'quantity' => 10,
                            'filledQuantity' => 10,
                            'price' => 165.50,
                            'avgFilledPrice' => 165.48,
                            'status' => 'Filled',
                            'placedTime' => 1681383600000,
                            'filledTime' => 1681383660000
                        )
                    )
                )
            ),
            
            // Trading Endpoints
            'place_order' => array(
                'endpoint' => '/api/trades/v5/orders/place',
                'method' => 'POST',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Place a new order',
                'parameters' => array(
                    'secAccountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Security Account ID',
                        'example' => '12345678'
                    ),
                    'tickerId' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Ticker ID',
                        'example' => 913256135
                    ),
                    'action' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order action (BUY, SELL)',
                        'example' => 'BUY'
                    ),
                    'orderType' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order type (MKT, LMT, STP, STP_LMT)',
                        'example' => 'LMT'
                    ),
                    'timeInForce' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time in force (DAY, GTC, etc.)',
                        'example' => 'GTC'
                    ),
                    'quantity' => array(
                        'required' => true,
                        'type' => 'number',
                        'description' => 'Order quantity',
                        'example' => 10
                    ),
                    'price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Price for limit orders',
                        'example' => 165.50
                    ),
                    'stopPrice' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Stop price for stop orders',
                        'example' => 170.00
                    ),
                    'extendedHours' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Allow execution during extended hours',
                        'example' => true
                    ),
                    'lmtOffset' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Limit offset for trailing stop orders',
                        'example' => 0.05
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'orderId' => 123456789,
                    'success' => true,
                    'ticker' => array(
                        'tickerId' => 913256135,
                        'symbol' => 'AAPL'
                    ),
                    'status' => 'Working'
                )
            ),
            'cancel_order' => array(
                'endpoint' => '/api/trades/v5/orders/cancel',
                'method' => 'POST',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Cancel an existing order',
                'parameters' => array(
                    'secAccountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Security Account ID',
                        'example' => '12345678'
                    ),
                    'orderId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order ID to cancel',
                        'example' => '123456789'
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'success' => true,
                    'orderId' => 123456789,
                    'status' => 'Cancelled'
                )
            ),
            'modify_order' => array(
                'endpoint' => '/api/trades/v5/orders/modify',
                'method' => 'POST',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Modify an existing order',
                'parameters' => array(
                    'secAccountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Security Account ID',
                        'example' => '12345678'
                    ),
                    'orderId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order ID to modify',
                        'example' => '123456789'
                    ),
                    'price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'New price for limit orders',
                        'example' => 166.00
                    ),
                    'quantity' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'New order quantity',
                        'example' => 15
                    ),
                    'stopPrice' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'New stop price for stop orders',
                        'example' => 169.00
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'success' => true,
                    'orderId' => 123456789,
                    'status' => 'Working'
                )
            ),
            
            // Watchlist Endpoints
            'get_watchlists' => array(
                'endpoint' => '/api/wlas/watchlists',
                'method' => 'GET',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Get all watchlists',
                'parameters' => array(),
                'auth_required' => true,
                'example_response' => array(
                    'data' => array(
                        array(
                            'id' => 12345,
                            'name' => 'Default',
                            'tickerCount' => 15,
                            'viewMode' => 1
                        ),
                        array(
                            'id' => 67890,
                            'name' => 'Tech Stocks',
                            'tickerCount' => 8,
                            'viewMode' => 1
                        )
                    )
                )
            ),
            'get_watchlist_items' => array(
                'endpoint' => '/api/wlas/watchlists/{id}',
                'method' => 'GET',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Get items in a watchlist',
                'parameters' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Watchlist ID',
                        'example' => 12345
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'id' => 12345,
                    'name' => 'Default',
                    'tickerCount' => 15,
                    'viewMode' => 1,
                    'tickers' => array(
                        array(
                            'tickerId' => 913256135,
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc',
                            'disSymbol' => 'AAPL',
                            'status' => 'ACTIVE'
                        ),
                        array(
                            'tickerId' => 913254367,
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation',
                            'disSymbol' => 'MSFT',
                            'status' => 'ACTIVE'
                        )
                    )
                )
            ),
            'add_to_watchlist' => array(
                'endpoint' => '/api/wlas/watchlists/{id}/tickers',
                'method' => 'POST',
                'base_url' => 'https://userapi.webull.com',
                'description' => 'Add a ticker to a watchlist',
                'parameters' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Watchlist ID',
                        'example' => 12345
                    ),
                    'tickerId' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Ticker ID to add',
                        'example' => 913256135
                    )
                ),
                'auth_required' => true,
                'example_response' => array(
                    'success' => true
                )
            )
        );
    }
}