<?php
/**
 * TradePress Fidelity API Endpoints
 *
 * Defines endpoints and parameters for the Fidelity trading platform
 *
 * @package TradePress
 * @subpackage API\Fidelity
 * @version 1.0.0
 * @since 2025-04-11
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Fidelity API Endpoints class
 */
class TradePress_Fidelity_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            /**
             * Authentication Endpoints
             */
            'LOGIN' => array(
                'endpoint' => '/login/oauth/authorize',
                'method' => 'POST',
                'params' => array(
                    'client_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Your client ID'
                    ),
                    'response_type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Must be "token" for implicit grant flow'
                    ),
                    'redirect_uri' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'The URI to redirect to after authorization'
                    ),
                    'state' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Random string used to prevent CSRF attacks'
                    )
                ),
                'description' => 'OAuth2 authorization endpoint for Fidelity',
                'auth_required' => false,
                'rate_limit' => null
            ),
            'ACCESS_TOKEN' => array(
                'endpoint' => '/login/oauth/access_token',
                'method' => 'POST',
                'params' => array(
                    'grant_type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Must be "authorization_code"'
                    ),
                    'code' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'The authorization code received from authorization endpoint'
                    ),
                    'redirect_uri' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'The same redirect URI used for authorization'
                    ),
                    'client_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Your client ID'
                    ),
                    'client_secret' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Your client secret'
                    )
                ),
                'description' => 'Exchange authorization code for access token',
                'auth_required' => false,
                'rate_limit' => null
            ),
            'REFRESH_TOKEN' => array(
                'endpoint' => '/login/oauth/access_token',
                'method' => 'POST',
                'params' => array(
                    'grant_type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Must be "refresh_token"'
                    ),
                    'refresh_token' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'The refresh token received earlier'
                    ),
                    'client_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Your client ID'
                    ),
                    'client_secret' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Your client secret'
                    )
                ),
                'description' => 'Refresh an expired access token',
                'auth_required' => false,
                'rate_limit' => null
            ),
            'LOGOUT' => array(
                'endpoint' => '/login/oauth/revoke',
                'method' => 'POST',
                'params' => array(
                    'token' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'The access token to revoke'
                    ),
                    'client_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Your client ID'
                    ),
                    'client_secret' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Your client secret'
                    )
                ),
                'description' => 'Revoke an access token',
                'auth_required' => false,
                'rate_limit' => null
            ),

            /**
             * Account Endpoints
             */
            'GET_ACCOUNTS' => array(
                'endpoint' => '/api/v1/accounts',
                'method' => 'GET',
                'params' => array(),
                'description' => 'Get a list of accounts',
                'auth_required' => true,
                'rate_limit' => '100/minute',
                'example_response' => array(
                    'accounts' => array(
                        array(
                            'account_number' => 'Z12345678',
                            'account_name' => 'Individual Investment Account',
                            'account_type' => 'INDIVIDUAL',
                            'registration_type' => 'INDIVIDUAL',
                            'cash_balance' => 10542.36,
                            'buying_power' => 21084.72,
                            'margin_balance' => 0.00,
                            'total_value' => 125786.42,
                            'day_change' => 2345.67,
                            'day_change_percent' => 1.86
                        ),
                        array(
                            'account_number' => 'Z87654321',
                            'account_name' => '401(k)',
                            'account_type' => 'RETIREMENT',
                            'registration_type' => '401K',
                            'cash_balance' => 1528.75,
                            'buying_power' => 1528.75,
                            'margin_balance' => 0.00,
                            'total_value' => 98765.43,
                            'day_change' => -876.54,
                            'day_change_percent' => -0.89
                        )
                    )
                )
            ),
            'GET_ACCOUNT_DETAILS' => array(
                'endpoint' => '/api/v1/accounts/{account_id}',
                'method' => 'GET',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    )
                ),
                'description' => 'Get detailed account information',
                'auth_required' => true,
                'rate_limit' => '100/minute',
                'example_response' => array(
                    'account_number' => 'Z12345678',
                    'account_name' => 'Individual Investment Account',
                    'account_type' => 'INDIVIDUAL',
                    'registration_type' => 'INDIVIDUAL',
                    'open_date' => '2020-01-15',
                    'status' => 'ACTIVE',
                    'cash_balance' => 10542.36,
                    'buying_power' => 21084.72,
                    'margin_balance' => 0.00,
                    'margin_buying_power' => 10542.36,
                    'option_level' => 2,
                    'day_trade_count' => 1,
                    'pattern_day_trader' => false,
                    'total_value' => 125786.42,
                    'day_change' => 2345.67,
                    'day_change_percent' => 1.86,
                    'contribution_year_to_date' => 12000.00,
                    'withdrawal_year_to_date' => 0.00
                )
            ),
            'GET_ACCOUNT_POSITIONS' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/positions',
                'method' => 'GET',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    )
                ),
                'description' => 'Get positions for a specific account',
                'auth_required' => true,
                'rate_limit' => '100/minute',
                'example_response' => array(
                    'positions' => array(
                        array(
                            'symbol' => 'AAPL',
                            'quantity' => 100,
                            'cost_basis' => 120.35,
                            'market_value' => 15786.00,
                            'price' => 157.86,
                            'day_change' => 235.00,
                            'day_change_percent' => 1.51,
                            'total_gain_loss' => 3751.00,
                            'total_gain_loss_percent' => 31.17,
                            'asset_type' => 'EQUITY',
                            'average_price' => 120.35,
                            'settled_quantity' => 100,
                            'margin_able' => true
                        ),
                        array(
                            'symbol' => 'MSFT',
                            'quantity' => 50,
                            'cost_basis' => 200.25,
                            'market_value' => 12450.00,
                            'price' => 249.00,
                            'day_change' => 112.50,
                            'day_change_percent' => 0.91,
                            'total_gain_loss' => 2437.50,
                            'total_gain_loss_percent' => 24.34,
                            'asset_type' => 'EQUITY',
                            'average_price' => 200.25,
                            'settled_quantity' => 50,
                            'margin_able' => true
                        )
                    )
                )
            ),
            'GET_ACCOUNT_BALANCES' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/balances',
                'method' => 'GET',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    )
                ),
                'description' => 'Get account balances',
                'auth_required' => true,
                'rate_limit' => '100/minute',
                'example_response' => array(
                    'cash' => array(
                        'cash_balance' => 10542.36,
                        'cash_available' => 10542.36,
                        'settled_cash' => 10542.36,
                        'uncollected_deposits' => 0.00,
                        'unsettled_cash' => 0.00
                    ),
                    'margin' => array(
                        'margin_balance' => 0.00,
                        'margin_buying_power' => 10542.36,
                        'margin_requirement' => 0.00,
                        'day_trade_buying_power' => 21084.72,
                        'sma_balance' => 10542.36
                    ),
                    'total_value' => 125786.42,
                    'day_change' => 2345.67,
                    'day_change_percent' => 1.86
                )
            ),
            'GET_ACCOUNT_ORDERS' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/orders',
                'method' => 'GET',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'status' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by order status: OPEN, FILLED, CANCELED, REJECTED, ALL (default: OPEN)',
                        'enum' => array('OPEN', 'FILLED', 'CANCELED', 'REJECTED', 'ALL')
                    ),
                    'from_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date for orders (YYYY-MM-DD)'
                    ),
                    'to_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date for orders (YYYY-MM-DD)'
                    )
                ),
                'description' => 'Get orders for a specific account',
                'auth_required' => true,
                'rate_limit' => '100/minute',
                'example_response' => array(
                    'orders' => array(
                        array(
                            'order_id' => '123456789',
                            'symbol' => 'AAPL',
                            'quantity' => 10,
                            'filled_quantity' => 0,
                            'order_type' => 'LIMIT',
                            'limit_price' => 150.00,
                            'side' => 'BUY',
                            'status' => 'OPEN',
                            'time_in_force' => 'DAY',
                            'create_time' => '2025-04-11T09:30:00-04:00',
                            'update_time' => '2025-04-11T09:30:00-04:00',
                            'asset_type' => 'EQUITY'
                        ),
                        array(
                            'order_id' => '987654321',
                            'symbol' => 'MSFT',
                            'quantity' => 5,
                            'filled_quantity' => 5,
                            'order_type' => 'MARKET',
                            'executed_price' => 248.75,
                            'side' => 'BUY',
                            'status' => 'FILLED',
                            'time_in_force' => 'DAY',
                            'create_time' => '2025-04-10T10:15:00-04:00',
                            'update_time' => '2025-04-10T10:15:05-04:00',
                            'asset_type' => 'EQUITY'
                        )
                    )
                )
            ),
            'GET_ORDER' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/orders/{order_id}',
                'method' => 'GET',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'order_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order ID'
                    )
                ),
                'description' => 'Get details for a specific order',
                'auth_required' => true,
                'rate_limit' => '100/minute',
                'example_response' => array(
                    'order_id' => '123456789',
                    'account_id' => 'Z12345678',
                    'symbol' => 'AAPL',
                    'asset_type' => 'EQUITY',
                    'quantity' => 10,
                    'filled_quantity' => 0,
                    'order_type' => 'LIMIT',
                    'limit_price' => 150.00,
                    'stop_price' => null,
                    'side' => 'BUY',
                    'status' => 'OPEN',
                    'time_in_force' => 'DAY',
                    'create_time' => '2025-04-11T09:30:00-04:00',
                    'update_time' => '2025-04-11T09:30:00-04:00',
                    'cancellable' => true,
                    'legs' => array(),
                    'expires_at' => '2025-04-11T16:00:00-04:00'
                )
            ),
            'GET_ACCOUNT_TRANSACTIONS' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/transactions',
                'method' => 'GET',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'type' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Transaction type: TRADE, DIVIDEND, INTEREST, DEPOSIT, WITHDRAWAL, FEE, ALL',
                        'enum' => array('TRADE', 'DIVIDEND', 'INTEREST', 'DEPOSIT', 'WITHDRAWAL', 'FEE', 'ALL')
                    ),
                    'from_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date for transactions (YYYY-MM-DD)'
                    ),
                    'to_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date for transactions (YYYY-MM-DD)'
                    )
                ),
                'description' => 'Get transactions for a specific account',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'transactions' => array(
                        array(
                            'transaction_id' => 'T123456789',
                            'account_id' => 'Z12345678',
                            'type' => 'TRADE',
                            'description' => 'BUY 5 MSFT @ 248.75',
                            'symbol' => 'MSFT',
                            'quantity' => 5,
                            'price' => 248.75,
                            'amount' => -1243.75,
                            'fees' => 0.00,
                            'date' => '2025-04-10',
                            'settlement_date' => '2025-04-12'
                        ),
                        array(
                            'transaction_id' => 'T987654321',
                            'account_id' => 'Z12345678',
                            'type' => 'DIVIDEND',
                            'description' => 'DIVIDEND MSFT',
                            'symbol' => 'MSFT',
                            'quantity' => null,
                            'price' => null,
                            'amount' => 24.50,
                            'fees' => 0.00,
                            'date' => '2025-04-08',
                            'settlement_date' => '2025-04-08'
                        )
                    )
                )
            ),

            /**
             * Trading Endpoints
             */
            'PLACE_ORDER' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/orders',
                'method' => 'POST',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol identifier for the security'
                    ),
                    'quantity' => array(
                        'required' => true,
                        'type' => 'number',
                        'description' => 'Number of shares or contracts'
                    ),
                    'side' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order direction: BUY or SELL',
                        'enum' => array('BUY', 'SELL')
                    ),
                    'order_type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order type: MARKET, LIMIT, STOP, STOP_LIMIT',
                        'enum' => array('MARKET', 'LIMIT', 'STOP', 'STOP_LIMIT')
                    ),
                    'time_in_force' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time in force: DAY, GTC, OPG, FOK, IOC',
                        'enum' => array('DAY', 'GTC', 'OPG', 'FOK', 'IOC')
                    ),
                    'limit_price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Limit price (required for LIMIT and STOP_LIMIT orders)'
                    ),
                    'stop_price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Stop price (required for STOP and STOP_LIMIT orders)'
                    ),
                    'extended_hours' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Allow execution during extended hours'
                    ),
                    'all_or_none' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Order must be filled completely or not at all'
                    )
                ),
                'description' => 'Place a new order',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'order_id' => '123456789',
                    'account_id' => 'Z12345678',
                    'symbol' => 'AAPL',
                    'asset_type' => 'EQUITY',
                    'quantity' => 10,
                    'filled_quantity' => 0,
                    'order_type' => 'LIMIT',
                    'limit_price' => 150.00,
                    'stop_price' => null,
                    'side' => 'BUY',
                    'status' => 'OPEN',
                    'time_in_force' => 'DAY',
                    'create_time' => '2025-04-11T09:30:00-04:00',
                    'update_time' => '2025-04-11T09:30:00-04:00',
                    'cancellable' => true
                )
            ),
            'CANCEL_ORDER' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/orders/{order_id}',
                'method' => 'DELETE',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'order_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order ID'
                    )
                ),
                'description' => 'Cancel an open order',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'order_id' => '123456789',
                    'status' => 'CANCELED',
                    'message' => 'Order successfully canceled'
                )
            ),
            'MODIFY_ORDER' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/orders/{order_id}',
                'method' => 'PUT',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'order_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order ID'
                    ),
                    'quantity' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'New quantity of shares or contracts'
                    ),
                    'order_type' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'New order type: MARKET, LIMIT, STOP, STOP_LIMIT',
                        'enum' => array('MARKET', 'LIMIT', 'STOP', 'STOP_LIMIT')
                    ),
                    'time_in_force' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'New time in force: DAY, GTC, OPG, FOK, IOC',
                        'enum' => array('DAY', 'GTC', 'OPG', 'FOK', 'IOC')
                    ),
                    'limit_price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'New limit price'
                    ),
                    'stop_price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'New stop price'
                    ),
                    'extended_hours' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Allow execution during extended hours'
                    )
                ),
                'description' => 'Modify an existing order',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'order_id' => '123456789',
                    'account_id' => 'Z12345678',
                    'symbol' => 'AAPL',
                    'asset_type' => 'EQUITY',
                    'quantity' => 15,
                    'filled_quantity' => 0,
                    'order_type' => 'LIMIT',
                    'limit_price' => 152.50,
                    'stop_price' => null,
                    'side' => 'BUY',
                    'status' => 'OPEN',
                    'time_in_force' => 'DAY',
                    'create_time' => '2025-04-11T09:30:00-04:00',
                    'update_time' => '2025-04-11T09:45:00-04:00',
                    'cancellable' => true
                )
            ),

            /**
             * Market Data Endpoints
             */
            'GET_QUOTE' => array(
                'endpoint' => '/api/v1/market/quotes/{symbol}',
                'method' => 'GET',
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol identifier for the security'
                    )
                ),
                'description' => 'Get quote data for a symbol',
                'auth_required' => true,
                'rate_limit' => '120/minute',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'last_price' => 157.86,
                    'change' => 2.35,
                    'change_percent' => 1.51,
                    'open' => 155.75,
                    'high' => 158.23,
                    'low' => 155.54,
                    'prev_close' => 155.51,
                    'volume' => 42589631,
                    'bid' => 157.85,
                    'ask' => 157.87,
                    'bid_size' => 800,
                    'ask_size' => 1200,
                    'timestamp' => '2025-04-11T14:32:45-04:00',
                    'extended_hours_last_price' => null,
                    'extended_hours_change' => null,
                    'extended_hours_change_percent' => null,
                    'is_halted' => false
                )
            ),
            'GET_QUOTES' => array(
                'endpoint' => '/api/v1/market/quotes',
                'method' => 'GET',
                'params' => array(
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols'
                    )
                ),
                'description' => 'Get quote data for multiple symbols',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'quotes' => array(
                        'AAPL' => array(
                            'symbol' => 'AAPL',
                            'last_price' => 157.86,
                            'change' => 2.35,
                            'change_percent' => 1.51,
                            'open' => 155.75,
                            'high' => 158.23,
                            'low' => 155.54,
                            'prev_close' => 155.51,
                            'volume' => 42589631,
                            'bid' => 157.85,
                            'ask' => 157.87,
                            'bid_size' => 800,
                            'ask_size' => 1200,
                            'timestamp' => '2025-04-11T14:32:45-04:00'
                        ),
                        'MSFT' => array(
                            'symbol' => 'MSFT',
                            'last_price' => 249.00,
                            'change' => 2.25,
                            'change_percent' => 0.91,
                            'open' => 247.50,
                            'high' => 249.87,
                            'low' => 246.75,
                            'prev_close' => 246.75,
                            'volume' => 18765432,
                            'bid' => 248.98,
                            'ask' => 249.02,
                            'bid_size' => 1000,
                            'ask_size' => 800,
                            'timestamp' => '2025-04-11T14:32:45-04:00'
                        )
                    )
                )
            ),
            'GET_HISTORICAL_DATA' => array(
                'endpoint' => '/api/v1/market/history/{symbol}',
                'method' => 'GET',
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol identifier for the security'
                    ),
                    'period' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Period: 1d, 5d, 1mo, 3mo, 6mo, 1y, 2y, 5y, 10y, ytd, max',
                        'enum' => array('1d', '5d', '1mo', '3mo', '6mo', '1y', '2y', '5y', '10y', 'ytd', 'max')
                    ),
                    'interval' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Interval: 1m, 5m, 15m, 30m, 1h, 1d, 1wk, 1mo',
                        'enum' => array('1m', '5m', '15m', '30m', '1h', '1d', '1wk', '1mo')
                    ),
                    'start_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date (YYYY-MM-DD) - overrides period if provided'
                    ),
                    'end_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date (YYYY-MM-DD) - required if start_date is provided'
                    )
                ),
                'description' => 'Get historical price data for a symbol',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'interval' => '1d',
                    'data' => array(
                        array(
                            'timestamp' => '2025-04-11T16:00:00-04:00',
                            'open' => 155.75,
                            'high' => 158.23,
                            'low' => 155.54,
                            'close' => 157.86,
                            'volume' => 42589631
                        ),
                        array(
                            'timestamp' => '2025-04-10T16:00:00-04:00',
                            'open' => 154.90,
                            'high' => 156.25,
                            'low' => 154.00,
                            'close' => 155.51,
                            'volume' => 38756432
                        ),
                        // Additional historical data points...
                    )
                )
            ),
            'GET_OPTION_CHAIN' => array(
                'endpoint' => '/api/v1/market/options/{symbol}',
                'method' => 'GET',
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol identifier for the underlying security'
                    ),
                    'expiration_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Specific expiration date (YYYY-MM-DD)'
                    ),
                    'strike' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Filter by specific strike price'
                    ),
                    'option_type' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by option type: CALL, PUT, ALL (default: ALL)',
                        'enum' => array('CALL', 'PUT', 'ALL')
                    )
                ),
                'description' => 'Get options chain data for a symbol',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'underlying_price' => 157.86,
                    'expirations' => array(
                        '2025-04-18',
                        '2025-04-25',
                        '2025-05-02',
                        // More expiration dates...
                    ),
                    'options' => array(
                        '2025-04-18' => array(
                            'calls' => array(
                                array(
                                    'symbol' => 'AAPL250418C00155000',
                                    'strike' => 155.00,
                                    'last_price' => 4.35,
                                    'bid' => 4.30,
                                    'ask' => 4.40,
                                    'volume' => 1254,
                                    'open_interest' => 5678,
                                    'implied_volatility' => 0.245,
                                    'delta' => 0.65,
                                    'gamma' => 0.08,
                                    'theta' => -0.24,
                                    'vega' => 0.12
                                ),
                                // More call options...
                            ),
                            'puts' => array(
                                array(
                                    'symbol' => 'AAPL250418P00155000',
                                    'strike' => 155.00,
                                    'last_price' => 1.25,
                                    'bid' => 1.20,
                                    'ask' => 1.30,
                                    'volume' => 876,
                                    'open_interest' => 3456,
                                    'implied_volatility' => 0.255,
                                    'delta' => -0.35,
                                    'gamma' => 0.08,
                                    'theta' => -0.23,
                                    'vega' => 0.11
                                ),
                                // More put options...
                            )
                        ),
                        // More expiration dates with call/put options...
                    )
                )
            ),
            'GET_MARKET_HOURS' => array(
                'endpoint' => '/api/v1/market/hours',
                'method' => 'GET',
                'params' => array(
                    'date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Date to check (YYYY-MM-DD, default is current date)'
                    ),
                    'markets' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Comma-separated list of markets: EQUITY, OPTION, BOND, FOREX, FUTURES'
                    )
                ),
                'description' => 'Get market hours for a specific date',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'date' => '2025-04-11',
                    'markets' => array(
                        'EQUITY' => array(
                            'is_open' => true,
                            'pre_market_start' => '04:00:00-04:00',
                            'regular_market_start' => '09:30:00-04:00',
                            'regular_market_end' => '16:00:00-04:00',
                            'post_market_end' => '20:00:00-04:00',
                            'next_open_date' => '2025-04-14',
                            'next_close_date' => '2025-04-11'
                        ),
                        'OPTION' => array(
                            'is_open' => true,
                            'regular_market_start' => '09:30:00-04:00',
                            'regular_market_end' => '16:00:00-04:00',
                            'next_open_date' => '2025-04-14',
                            'next_close_date' => '2025-04-11'
                        ),
                        // Other markets as requested...
                    )
                )
            ),
            'SEARCH_INSTRUMENTS' => array(
                'endpoint' => '/api/v1/market/instruments/search',
                'method' => 'GET',
                'params' => array(
                    'query' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Search query (symbol or name)'
                    ),
                    'type' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Asset type to filter: EQUITY, OPTION, ETF, MUTUAL_FUND, FIXED_INCOME, ALL',
                        'enum' => array('EQUITY', 'OPTION', 'ETF', 'MUTUAL_FUND', 'FIXED_INCOME', 'ALL')
                    )
                ),
                'description' => 'Search for instruments by symbol or name',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'instruments' => array(
                        array(
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc.',
                            'type' => 'EQUITY',
                            'exchange' => 'NASDAQ'
                        ),
                        array(
                            'symbol' => 'AAPL1',
                            'name' => 'Apple Inc. ADR',
                            'type' => 'EQUITY',
                            'exchange' => 'OTC'
                        ),
                        array(
                            'symbol' => 'AAPL220121C00150000',
                            'name' => 'AAPL Jan 21 2022 150 Call',
                            'type' => 'OPTION',
                            'exchange' => 'OPRA'
                        )
                    )
                )
            ),
            'GET_INSTRUMENT' => array(
                'endpoint' => '/api/v1/market/instruments/{symbol}',
                'method' => 'GET',
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol identifier for the instrument'
                    )
                ),
                'description' => 'Get detailed information about an instrument',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'name' => 'Apple Inc.',
                    'type' => 'EQUITY',
                    'exchange' => 'NASDAQ',
                    'industry' => 'Technology',
                    'sector' => 'Information Technology',
                    'market_cap' => 2580000000000,
                    'pe_ratio' => 27.35,
                    'dividend_yield' => 0.53,
                    'dividend_amount' => 0.88,
                    'dividend_date' => '2025-05-12',
                    'eps' => 5.78,
                    '52_week_high' => 162.34,
                    '52_week_low' => 128.45,
                    'is_shortable' => true,
                    'is_marginable' => true
                )
            ),

            /**
             * Watchlist Endpoints
             */
            'GET_WATCHLISTS' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/watchlists',
                'method' => 'GET',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    )
                ),
                'description' => 'Get all watchlists for an account',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'watchlists' => array(
                        array(
                            'watchlist_id' => 'W123456789',
                            'name' => 'Tech Stocks',
                            'created_at' => '2024-12-10T12:34:56-05:00',
                            'items_count' => 5
                        ),
                        array(
                            'watchlist_id' => 'W987654321',
                            'name' => 'Portfolio Watch',
                            'created_at' => '2025-01-15T09:45:12-05:00',
                            'items_count' => 12
                        )
                    )
                )
            ),
            'GET_WATCHLIST' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/watchlists/{watchlist_id}',
                'method' => 'GET',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID'
                    )
                ),
                'description' => 'Get watchlist details including items',
                'auth_required' => true,
                'rate_limit' => '60/minute',
                'example_response' => array(
                    'watchlist_id' => 'W123456789',
                    'name' => 'Tech Stocks',
                    'created_at' => '2024-12-10T12:34:56-05:00',
                    'items' => array(
                        array(
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc.',
                            'last_price' => 157.86,
                            'change' => 2.35,
                            'change_percent' => 1.51
                        ),
                        array(
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation',
                            'last_price' => 249.00,
                            'change' => 2.25,
                            'change_percent' => 0.91
                        ),
                        array(
                            'symbol' => 'GOOGL',
                            'name' => 'Alphabet Inc.',
                            'last_price' => 2245.75,
                            'change' => -15.50,
                            'change_percent' => -0.69
                        ),
                        array(
                            'symbol' => 'AMZN',
                            'name' => 'Amazon.com, Inc.',
                            'last_price' => 3125.50,
                            'change' => 42.75,
                            'change_percent' => 1.39
                        ),
                        array(
                            'symbol' => 'NVDA',
                            'name' => 'NVIDIA Corporation',
                            'last_price' => 578.25,
                            'change' => 12.80,
                            'change_percent' => 2.26
                        )
                    )
                )
            ),
            'CREATE_WATCHLIST' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/watchlists',
                'method' => 'POST',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'name' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Name of the watchlist'
                    ),
                    'symbols' => array(
                        'required' => false,
                        'type' => 'array',
                        'description' => 'Array of symbols to add to the watchlist'
                    )
                ),
                'description' => 'Create a new watchlist',
                'auth_required' => true,
                'rate_limit' => '30/minute',
                'example_response' => array(
                    'watchlist_id' => 'W123456789',
                    'name' => 'Tech Stocks',
                    'created_at' => '2025-04-11T10:30:45-04:00',
                    'items' => array(
                        array(
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc.'
                        ),
                        array(
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation'
                        ),
                        array(
                            'symbol' => 'GOOGL',
                            'name' => 'Alphabet Inc.'
                        )
                    )
                )
            ),
            'DELETE_WATCHLIST' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/watchlists/{watchlist_id}',
                'method' => 'DELETE',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID'
                    )
                ),
                'description' => 'Delete a watchlist',
                'auth_required' => true,
                'rate_limit' => '30/minute',
                'example_response' => array(
                    'status' => 'SUCCESS',
                    'message' => 'Watchlist successfully deleted'
                )
            ),
            'ADD_WATCHLIST_ITEM' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/watchlists/{watchlist_id}/items',
                'method' => 'POST',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol to add to the watchlist'
                    )
                ),
                'description' => 'Add a symbol to a watchlist',
                'auth_required' => true,
                'rate_limit' => '30/minute',
                'example_response' => array(
                    'status' => 'SUCCESS',
                    'watchlist_id' => 'W123456789',
                    'added_item' => array(
                        'symbol' => 'TSLA',
                        'name' => 'Tesla, Inc.'
                    )
                )
            ),
            'REMOVE_WATCHLIST_ITEM' => array(
                'endpoint' => '/api/v1/accounts/{account_id}/watchlists/{watchlist_id}/items/{symbol}',
                'method' => 'DELETE',
                'params' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID or number'
                    ),
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol to remove from the watchlist'
                    )
                ),
                'description' => 'Remove a symbol from a watchlist',
                'auth_required' => true,
                'rate_limit' => '30/minute',
                'example_response' => array(
                    'status' => 'SUCCESS',
                    'message' => 'Symbol successfully removed from watchlist'
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
        
        if (!$endpoint || empty($endpoint['endpoint'])) {
            return '';
        }
        
        if (empty($base_url)) {
            $base_url = 'https://api.fidelity.com';
        }
        
        $url = $endpoint['endpoint'];
        
        // Replace path parameters
        foreach ($params as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (strpos($url, $placeholder) !== false) {
                $url = str_replace($placeholder, urlencode($value), $url);
                unset($params[$key]); // Remove path params from query params
            }
        }
        
        // Add remaining parameters as query string
        if (!empty($params) && in_array($endpoint['method'], array('GET', 'DELETE'))) {
            $url .= (strpos($url, '?') === false) ? '?' : '&';
            $url .= http_build_query($params);
        }
        
        return $base_url . $url;
    }
}
