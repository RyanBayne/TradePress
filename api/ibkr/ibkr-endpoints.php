<?php
/**
 * TradePress Interactive Brokers API Endpoints
 *
 * Defines endpoints and parameters for the Interactive Brokers trading platform
 * API Documentation: https://interactivebrokers.github.io/cpwebapi/
 * 
 * @package TradePress
 * @subpackage API\IBKR
 * @version 1.0.0
 * @since 2025-04-10
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Interactive Brokers API Endpoints class
 */
class TradePress_IBKR_Endpoints {
    
    /**
     * API Restrictions and Rate Limits
     * 
     * Based on IBKR API documentation
     * 
     * @return array API restrictions information
     */
    public static function get_api_restrictions() {
        return array(
            'rate_limits' => array(
                'description' => 'Maximum number of requests per time window',
                'details' => array(
                    'general_requests' => '180 requests per minute',
                    'market_data_requests' => '100 requests per minute',
                    'order_requests' => '50 requests per minute',
                    'portfolio_requests' => '60 requests per minute'
                )
            ),
            'authentication' => array(
                'description' => 'API Authentication methods',
                'details' => array(
                    'session_based' => 'Session-based authentication using SSO login flow',
                    'oauth2' => 'OAuth2 authentication for third-party applications',
                    'headers' => array(
                        'Authorization' => 'Bearer YOUR_ACCESS_TOKEN'
                    )
                )
            ),
            'environments' => array(
                'description' => 'Available API environments',
                'details' => array(
                    'live' => 'https://api.interactivebrokers.com',
                    'paper' => 'https://api.interactivebrokers.com/paper-trading',
                    'gateway' => 'http://localhost:5000'
                )
            ),
            'request_size' => array(
                'description' => 'Limitations on request size',
                'details' => array(
                    'order_submission' => 'Single order per request',
                    'batch_reports' => 'Limited to 50 reports per request',
                    'market_data_symbols' => '200 symbols per request'
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
            'auth_status' => array(
                'endpoint' => '/portal/sso/validate',
                'method' => 'GET',
                'description' => 'Check authentication status',
                'parameters' => array(),
                'example_response' => array(
                    'authenticated' => true, 
                    'competing' => false, 
                    'connected' => true,
                    'message' => 'Authenticated',
                    'MAC' => 'AB:CD:EF:12:34:56'
                )
            ),
            'auth_logout' => array(
                'endpoint' => '/portal/logout',
                'method' => 'POST',
                'description' => 'Logout of the current session',
                'parameters' => array(),
                'example_response' => array(
                    'status' => 'success'
                )
            ),
            
            // Portfolio Endpoints
            'portfolio_accounts' => array(
                'endpoint' => '/portal/portfolio/accounts',
                'method' => 'GET',
                'description' => 'List portfolio accounts',
                'parameters' => array(),
                'example_response' => array(
                    'accounts' => array('U123456', 'U234567')
                )
            ),
            'portfolio_subaccounts' => array(
                'endpoint' => '/portal/portfolio/subaccounts',
                'method' => 'GET',
                'description' => 'List portfolio subaccounts',
                'parameters' => array(),
                'example_response' => array(
                    array('id' => 'U123456', 'accountId' => 'U123456', 'accountTitle' => 'Individual Account')
                )
            ),
            'portfolio_summary' => array(
                'endpoint' => '/portal/portfolio/{accountId}/summary',
                'method' => 'GET',
                'description' => 'Get portfolio summary',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'U123456'
                    )
                ),
                'example_response' => array(
                    'accountReady' => true,
                    'accountType' => 'INDIVIDUAL',
                    'availableFunds' => 50000,
                    'buyingPower' => 100000,
                    'cashBalance' => 50000,
                    'currency' => 'USD',
                    'equity' => 75000,
                    'equityWithLoanValue' => 75000,
                    'excessLiquidity' => 35000,
                    'fullAvailableFunds' => 50000,
                    'fullExcessLiquidity' => 35000,
                    'fullInitMarginReq' => 25000,
                    'fullMaintMarginReq' => 15000,
                    'grossPositionValue' => 25000,
                    'initMarginReq' => 25000,
                    'leverageComp' => 0.333,
                    'maintMarginReq' => 15000,
                    'netLiquidation' => 75000,
                    'previousEquity' => 74800
                )
            ),
            'portfolio_positions' => array(
                'endpoint' => '/portal/portfolio/{accountId}/positions/{pageId}',
                'method' => 'GET',
                'description' => 'Get portfolio positions',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'U123456'
                    ),
                    'pageId' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Page number (0-based)',
                        'example' => '0'
                    )
                ),
                'example_response' => array(
                    'positions' => array(
                        array(
                            'acctId' => 'U123456',
                            'assetClass' => 'STK',
                            'avgCost' => 145.23,
                            'conid' => 265598,
                            'contractDesc' => 'AAPL',
                            'position' => 100,
                            'ticker' => 'AAPL',
                            'mktPrice' => 150.75,
                            'mktValue' => 15075,
                            'currency' => 'USD'
                        )
                    )
                )
            ),
            
            // Market Data Endpoints
            'market_data_history' => array(
                'endpoint' => '/portal/iserver/marketdata/history',
                'method' => 'GET',
                'description' => 'Get historical market data',
                'parameters' => array(
                    'conid' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Contract ID',
                        'example' => '265598'
                    ),
                    'period' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time period',
                        'enum' => array('1d', '1w', '1m', '3m', '6m', '1y', '2y', '3y', '5y', '10y'),
                        'example' => '1m'
                    ),
                    'bar' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Bar size',
                        'enum' => array('1min', '5min', '15min', '1h', '1d', '1w', '1m'),
                        'example' => '1d'
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        'barLength' => 86400,
                        'currency' => 'USD',
                        'data' => array(
                            array(
                                'c' => 150.75, // close
                                'h' => 152.30, // high
                                'l' => 149.20, // low
                                'o' => 149.50, // open
                                't' => 1649289600000, // timestamp
                                'v' => 65432100 // volume
                            )
                        ),
                        'points' => 30,
                        'symbol' => 'AAPL',
                        'text' => 'AAPL'
                    )
                )
            ),
            'market_data_snapshot' => array(
                'endpoint' => '/portal/iserver/marketdata/snapshot',
                'method' => 'GET',
                'description' => 'Get market data snapshot',
                'parameters' => array(
                    'conids' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated contract IDs',
                        'example' => '265598,8314'
                    ),
                    'fields' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Comma-separated field codes',
                        'example' => '31,84,85,86'
                    )
                ),
                'example_response' => array(
                    array(
                        '31' => 150.75, // last price
                        '84' => 149.20, // low
                        '85' => 152.30, // high
                        '86' => 149.50, // open
                        'conid' => '265598'
                    )
                )
            ),
            
            // Order Endpoints
            'order_place' => array(
                'endpoint' => '/portal/iserver/account/{accountId}/order',
                'method' => 'POST',
                'description' => 'Place an order',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'U123456'
                    ),
                    'conid' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Contract ID',
                        'example' => 265598
                    ),
                    'secType' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Security type',
                        'enum' => array('STK', 'OPT', 'FUT', 'CASH', 'CFD', 'WAR', 'BOND', 'FUND'),
                        'example' => 'STK'
                    ),
                    'side' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order side',
                        'enum' => array('BUY', 'SELL'),
                        'example' => 'BUY'
                    ),
                    'orderType' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order type',
                        'enum' => array('MKT', 'LMT', 'STP', 'STP_LMT', 'MIDPRICE'),
                        'example' => 'LMT'
                    ),
                    'price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Order price (required for LMT orders)',
                        'example' => 150.50
                    ),
                    'quantity' => array(
                        'required' => true,
                        'type' => 'number',
                        'description' => 'Order quantity',
                        'example' => 100
                    ),
                    'tif' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Time in force',
                        'enum' => array('DAY', 'GTC', 'IOC', 'GTD'),
                        'default' => 'DAY',
                        'example' => 'DAY'
                    )
                ),
                'example_response' => array(
                    'id' => '123456789',
                    'message' => 'Order placed successfully'
                )
            ),
            'order_status' => array(
                'endpoint' => '/portal/iserver/account/{accountId}/orders',
                'method' => 'GET',
                'description' => 'Get order status for all orders',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'U123456'
                    )
                ),
                'example_response' => array(
                    'orders' => array(
                        array(
                            'acct' => 'U123456',
                            'conid' => 265598,
                            'orderId' => 123456789,
                            'orderType' => 'LMT',
                            'listingExchange' => 'NASDAQ',
                            'outsideRth' => false,
                            'price' => 150.50,
                            'side' => 'BUY',
                            'ticker' => 'AAPL',
                            'tif' => 'DAY',
                            'remainingQuantity' => 100,
                            'filledQuantity' => 0,
                            'status' => 'Submitted',
                            'text' => 'Buy 100 AAPL LMT 150.50'
                        )
                    )
                )
            ),
            'order_cancel' => array(
                'endpoint' => '/portal/iserver/account/{accountId}/order/{orderId}',
                'method' => 'DELETE',
                'description' => 'Cancel an order',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'U123456'
                    ),
                    'orderId' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Order ID',
                        'example' => 123456789
                    )
                ),
                'example_response' => array(
                    'order_id' => 123456789,
                    'msg' => 'Order Cancelled',
                    'conid' => 265598,
                    'account' => 'U123456'
                )
            ),
            
            // Account Endpoints
            'account_pnl' => array(
                'endpoint' => '/portal/portfolio/{accountId}/pnl',
                'method' => 'GET',
                'description' => 'Get account P&L',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'U123456'
                    )
                ),
                'example_response' => array(
                    'dpl' => 1250.75, // daily P&L
                    'nl' => 75000.00, // net liquidation
                    'upl' => 3750.50, // unrealized P&L
                    'rpl' => 1250.75 // realized P&L
                )
            ),
            'account_ledger' => array(
                'endpoint' => '/portal/portfolio/{accountId}/ledger',
                'method' => 'GET',
                'description' => 'Get account ledger',
                'parameters' => array(
                    'accountId' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'U123456'
                    )
                ),
                'example_response' => array(
                    'BASE' => array(
                        'commoditymarketvalue' => 0,
                        'futuremarketvalue' => 0,
                        'issueroptionsmarketvalue' => 0,
                        'netliquidationvalue' => 75000.00,
                        'stockmarketvalue' => 25000.00,
                        'cash' => 50000.00,
                        'warrantmarketvalue' => 0
                    ),
                    'USD' => array(
                        'commoditymarketvalue' => 0,
                        'futuremarketvalue' => 0,
                        'issueroptionsmarketvalue' => 0,
                        'netliquidationvalue' => 75000.00,
                        'stockmarketvalue' => 25000.00,
                        'cash' => 50000.00,
                        'warrantmarketvalue' => 0
                    )
                )
            ),
            
            // Contract Search Endpoints
            'contract_search' => array(
                'endpoint' => '/portal/iserver/secdef/search',
                'method' => 'POST',
                'description' => 'Search for contracts',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol to search',
                        'example' => 'AAPL'
                    ),
                    'secType' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Security type',
                        'enum' => array('STK', 'OPT', 'FUT', 'CASH', 'CFD', 'WAR', 'BOND', 'FUND'),
                        'example' => 'STK'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Maximum number of results',
                        'default' => 10,
                        'example' => 5
                    )
                ),
                'example_response' => array(
                    array(
                        'conid' => 265598,
                        'symbol' => 'AAPL',
                        'name' => 'APPLE INC',
                        'secType' => 'STK',
                        'exchange' => 'NASDAQ',
                        'currency' => 'USD'
                    )
                )
            ),
            'contract_details' => array(
                'endpoint' => '/portal/iserver/contract/{conid}/info',
                'method' => 'GET',
                'description' => 'Get contract details',
                'parameters' => array(
                    'conid' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Contract ID',
                        'example' => 265598
                    )
                ),
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'full_name' => 'APPLE INC',
                    'company_name' => 'Apple Inc.',
                    'conid' => 265598,
                    'secType' => 'STK',
                    'exchange' => 'NASDAQ',
                    'listingExchange' => 'NASDAQ',
                    'currency' => 'USD',
                    'minTick' => 0.01,
                    'industry' => 'Technology',
                    'category' => 'Computers'
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
            // Default to the live API URL
            $base_url = self::get_api_restrictions()['environments']['details']['gateway'];
        }
        
        $endpoint_url = $endpoint['endpoint'];
        
        // Replace path parameters
        foreach ($params as $key => $value) {
            $endpoint_url = str_replace('{' . $key . '}', $value, $endpoint_url);
        }
        
        return trailingslashit($base_url) . ltrim($endpoint_url, '/');
    }
}
