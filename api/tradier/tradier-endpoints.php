<?php
/**
 * TradePress Tradier API Endpoints
 *
 * Defines endpoints and parameters for the Tradier brokerage service
 * API Documentation: https://documentation.tradier.com/brokerage-api
 * 
 * @package TradePress
 * @subpackage API\Tradier
 * @version 1.0.0
 * @since 2025-04-09
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Tradier API Endpoints class
 */
class TradePress_Tradier_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Market Data - Quote Endpoints
            'quotes' => array(
                'endpoint' => '/v1/markets/quotes',
                'method' => 'GET',
                'description' => 'Get quotes for one or more symbols',
                'parameters' => array(
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'One or more symbols separated by commas',
                        'example' => 'AAPL,MSFT,GOOG'
                    ),
                    'greeks' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include greek values for options',
                        'default' => false,
                        'example' => 'true'
                    )
                ),
                'example_response' => array(
                    'quotes' => array(
                        'quote' => array(
                            array(
                                'symbol' => 'AAPL',
                                'description' => 'Apple Inc',
                                'exch' => 'Q',
                                'type' => 'stock',
                                'last' => 178.85,
                                'change' => 0.75,
                                'volume' => 45908910,
                                'open' => 177.23,
                                'high' => 179.43,
                                'low' => 176.92,
                                'close' => null,
                                'bid' => 178.85,
                                'ask' => 178.86,
                                'change_percentage' => 0.42,
                                'average_volume' => 57504984,
                                'last_volume' => 100,
                                'trade_date' => 1712624400000,
                                'prevclose' => 178.10,
                                'week_52_high' => 199.62,
                                'week_52_low' => 143.90,
                                'bidsize' => 1,
                                'bidexch' => 'P',
                                'bid_date' => 1712624399000,
                                'asksize' => 1,
                                'askexch' => 'P',
                                'ask_date' => 1712624399000,
                                'root_symbols' => 'AAPL'
                            )
                        )
                    )
                )
            ),
            
            'option_quotes' => array(
                'endpoint' => '/v1/markets/options/quotes',
                'method' => 'GET',
                'description' => 'Get quotes for one or more option symbols',
                'parameters' => array(
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'One or more option symbols separated by commas',
                        'example' => 'AAPL250117C00200000,MSFT250117P00300000'
                    ),
                    'greeks' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include greek values',
                        'default' => false,
                        'example' => 'true'
                    )
                ),
                'example_response' => array(
                    'quotes' => array(
                        'quote' => array(
                            array(
                                'symbol' => 'AAPL250117C00200000',
                                'description' => 'AAPL Jan 17 2025 $200.00 Call',
                                'exch' => 'Z',
                                'type' => 'option',
                                'last' => 16.20,
                                'change' => 0.30,
                                'volume' => 245,
                                'open' => 15.70,
                                'high' => 16.40,
                                'low' => 15.70,
                                'close' => null,
                                'bid' => 16.10,
                                'ask' => 16.30,
                                'underlying' => 'AAPL',
                                'strike' => 200.00,
                                'change_percentage' => 1.89,
                                'average_volume' => 354,
                                'last_volume' => 5,
                                'trade_date' => 1712624400000,
                                'prevclose' => 15.90,
                                'week_52_high' => 23.60,
                                'week_52_low' => 8.15,
                                'bidsize' => 10,
                                'bidexch' => 'A',
                                'bid_date' => 1712624399000,
                                'asksize' => 15,
                                'askexch' => 'A',
                                'ask_date' => 1712624399000,
                                'open_interest' => 4532,
                                'contract_size' => 100,
                                'expiration_date' => '2025-01-17',
                                'expiration_type' => 'standard',
                                'option_type' => 'call',
                                'root_symbol' => 'AAPL'
                            )
                        )
                    )
                )
            ),
            
            'option_chains' => array(
                'endpoint' => '/v1/markets/options/chains',
                'method' => 'GET',
                'description' => 'Get option chain for a specific underlying symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Underlying symbol',
                        'example' => 'AAPL'
                    ),
                    'expiration' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Option expiration date in YYYY-MM-DD format',
                        'example' => '2025-01-17'
                    ),
                    'greeks' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include greek values',
                        'default' => false,
                        'example' => 'true'
                    )
                ),
                'example_response' => array(
                    'options' => array(
                        'option' => array(
                            array(
                                'symbol' => 'AAPL250117C00180000',
                                'description' => 'AAPL Jan 17 2025 $180.00 Call',
                                'exch' => 'Z',
                                'type' => 'option',
                                'last' => 25.30,
                                'change' => 0.65,
                                'volume' => 124,
                                'open' => 24.80,
                                'high' => 25.40,
                                'low' => 24.70,
                                'close' => null,
                                'bid' => 25.25,
                                'ask' => 25.35,
                                'underlying' => 'AAPL',
                                'strike' => 180.00,
                                'contract_size' => 100,
                                'expiration_date' => '2025-01-17',
                                'expiration_type' => 'standard',
                                'option_type' => 'call'
                            ),
                            array(
                                'symbol' => 'AAPL250117P00180000',
                                'description' => 'AAPL Jan 17 2025 $180.00 Put',
                                'exch' => 'Z',
                                'type' => 'option',
                                'last' => 19.45,
                                'change' => -0.35,
                                'volume' => 89,
                                'open' => 19.80,
                                'high' => 19.85,
                                'low' => 19.40,
                                'close' => null,
                                'bid' => 19.40,
                                'ask' => 19.50,
                                'underlying' => 'AAPL',
                                'strike' => 180.00,
                                'contract_size' => 100,
                                'expiration_date' => '2025-01-17',
                                'expiration_type' => 'standard',
                                'option_type' => 'put'
                            )
                        )
                    )
                )
            ),
            
            'option_expirations' => array(
                'endpoint' => '/v1/markets/options/expirations',
                'method' => 'GET',
                'description' => 'Get expiration dates for a specific underlying symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Underlying symbol',
                        'example' => 'AAPL'
                    ),
                    'includeAllRoots' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include all option roots',
                        'default' => false,
                        'example' => 'true'
                    ),
                    'strikes' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include strikes in response',
                        'default' => false,
                        'example' => 'true'
                    )
                ),
                'example_response' => array(
                    'expirations' => array(
                        'date' => array(
                            '2025-01-17',
                            '2025-02-21',
                            '2025-03-21',
                            '2025-04-18',
                            '2025-06-20',
                            '2025-09-19',
                            '2026-01-16'
                        ),
                        'strikes' => array(
                            100.0, 
                            110.0, 
                            120.0, 
                            130.0, 
                            140.0, 
                            150.0, 
                            160.0, 
                            170.0, 
                            180.0, 
                            190.0, 
                            200.0, 
                            210.0, 
                            220.0
                        )
                    )
                )
            ),
            
            'option_strikes' => array(
                'endpoint' => '/v1/markets/options/strikes',
                'method' => 'GET',
                'description' => 'Get strike prices for a specific underlying symbol and expiration date',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Underlying symbol',
                        'example' => 'AAPL'
                    ),
                    'expiration' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Option expiration date in YYYY-MM-DD format',
                        'example' => '2025-01-17'
                    )
                ),
                'example_response' => array(
                    'strikes' => array(
                        'strike' => array(
                            100.0, 
                            110.0, 
                            120.0, 
                            130.0, 
                            140.0, 
                            150.0, 
                            160.0, 
                            170.0, 
                            180.0, 
                            190.0, 
                            200.0, 
                            210.0, 
                            220.0
                        )
                    )
                )
            ),
            
            'historical_quotes' => array(
                'endpoint' => '/v1/markets/history',
                'method' => 'GET',
                'description' => 'Get historical quotes for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Data interval/frequency',
                        'enum' => array('daily', 'weekly', 'monthly'),
                        'default' => 'daily',
                        'example' => 'daily'
                    ),
                    'start' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format',
                        'example' => '2025-01-01'
                    ),
                    'end' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format',
                        'example' => '2025-03-31'
                    )
                ),
                'example_response' => array(
                    'history' => array(
                        'day' => array(
                            array(
                                'date' => '2025-03-31',
                                'open' => 178.08,
                                'high' => 179.43,
                                'low' => 177.85,
                                'close' => 178.85,
                                'volume' => 45870100
                            ),
                            array(
                                'date' => '2025-03-28',
                                'open' => 177.92,
                                'high' => 179.25,
                                'low' => 177.45,
                                'close' => 178.10,
                                'volume' => 43265400
                            )
                        )
                    )
                )
            ),
            
            'time_and_sales' => array(
                'endpoint' => '/v1/markets/timesales',
                'method' => 'GET',
                'description' => 'Get time and sales data for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Data interval',
                        'enum' => array('tick', '1min', '5min', '15min'),
                        'default' => '15min',
                        'example' => '5min'
                    ),
                    'start' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start time in YYYY-MM-DD HH:MM format',
                        'example' => '2025-04-09 09:30'
                    ),
                    'end' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End time in YYYY-MM-DD HH:MM format',
                        'example' => '2025-04-09 16:00'
                    ),
                    'session_filter' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter for specific sessions',
                        'enum' => array('open', 'all'),
                        'default' => 'all',
                        'example' => 'open'
                    )
                ),
                'example_response' => array(
                    'series' => array(
                        'data' => array(
                            array(
                                'time' => '2025-04-09 09:30:00',
                                'timestamp' => 1712665800000,
                                'price' => 178.25,
                                'open' => 178.25,
                                'high' => 178.45,
                                'low' => 177.95,
                                'close' => 178.10,
                                'volume' => 1254863
                            ),
                            array(
                                'time' => '2025-04-09 09:35:00',
                                'timestamp' => 1712666100000,
                                'price' => 178.15,
                                'open' => 178.10,
                                'high' => 178.35,
                                'low' => 178.00,
                                'close' => 178.25,
                                'volume' => 985241
                            )
                        )
                    )
                )
            ),
            
            'clock' => array(
                'endpoint' => '/v1/markets/clock',
                'method' => 'GET',
                'description' => 'Get market clock information',
                'parameters' => array(),
                'example_response' => array(
                    'clock' => array(
                        'date' => '2025-04-09',
                        'description' => 'Market is open',
                        'state' => 'open',
                        'timestamp' => 1712665800000,
                        'next_change' => '16:00',
                        'next_state' => 'closed'
                    )
                )
            ),
            
            'calendar' => array(
                'endpoint' => '/v1/markets/calendar',
                'method' => 'GET',
                'description' => 'Get market calendar information for a specific month',
                'parameters' => array(
                    'month' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Calendar month (1-12)',
                        'example' => '4'
                    ),
                    'year' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Calendar year',
                        'example' => '2025'
                    )
                ),
                'example_response' => array(
                    'calendar' => array(
                        'month' => 4,
                        'year' => 2025,
                        'days' => array(
                            'day' => array(
                                array(
                                    'date' => '2025-04-01',
                                    'status' => 'open',
                                    'description' => 'Market is open',
                                    'premarket' => array(
                                        'start' => '04:00',
                                        'end' => '09:30'
                                    ),
                                    'open' => array(
                                        'start' => '09:30',
                                        'end' => '16:00'
                                    ),
                                    'postmarket' => array(
                                        'start' => '16:00',
                                        'end' => '20:00'
                                    )
                                ),
                                array(
                                    'date' => '2025-04-05',
                                    'status' => 'closed',
                                    'description' => 'Weekend'
                                )
                            )
                        )
                    )
                )
            ),
            
            'search' => array(
                'endpoint' => '/v1/markets/search',
                'method' => 'GET',
                'description' => 'Search for symbols by company name or symbol',
                'parameters' => array(
                    'q' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Search query',
                        'example' => 'Apple'
                    ),
                    'indexes' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include indexes in results',
                        'default' => false,
                        'example' => 'true'
                    )
                ),
                'example_response' => array(
                    'securities' => array(
                        'security' => array(
                            array(
                                'symbol' => 'AAPL',
                                'exchange' => 'Q',
                                'type' => 'stock',
                                'description' => 'Apple Inc'
                            ),
                            array(
                                'symbol' => 'AAPL.OLD',
                                'exchange' => 'S',
                                'type' => 'stock',
                                'description' => 'Apple Inc - When Issued'
                            )
                        )
                    )
                )
            ),
            
            'lookup' => array(
                'endpoint' => '/v1/markets/lookup',
                'method' => 'GET',
                'description' => 'Lookup symbols by company name or symbol',
                'parameters' => array(
                    'q' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Search query',
                        'example' => 'Apple'
                    ),
                    'exchanges' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Specific exchanges to search (comma separated)',
                        'example' => 'Q,N'
                    ),
                    'types' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Specific security types to search (comma separated)',
                        'enum' => array('stock', 'option', 'etf', 'index', 'mutual_fund'),
                        'example' => 'stock,etf'
                    )
                ),
                'example_response' => array(
                    'securities' => array(
                        'security' => array(
                            array(
                                'symbol' => 'AAPL',
                                'exchange' => 'NASDAQ',
                                'type' => 'stock',
                                'description' => 'Apple Inc'
                            ),
                            array(
                                'symbol' => 'APLE',
                                'exchange' => 'NYSE',
                                'type' => 'stock',
                                'description' => 'Apple Hospitality REIT Inc'
                            )
                        )
                    )
                )
            ),
            
            // Account Endpoints
            'user_profile' => array(
                'endpoint' => '/v1/user/profile',
                'method' => 'GET',
                'description' => 'Get user profile information',
                'parameters' => array(),
                'requires_auth' => true,
                'example_response' => array(
                    'profile' => array(
                        'id' => '123456789',
                        'name' => 'John Smith',
                        'account' => array(
                            array(
                                'account_number' => 'ABC12345',
                                'classification' => 'individual',
                                'date_created' => '2024-01-15',
                                'day_trader' => false,
                                'option_level' => 2,
                                'status' => 'active',
                                'type' => 'margin',
                                'last_update_date' => '2025-04-01'
                            )
                        )
                    )
                )
            ),
            
            'balances' => array(
                'endpoint' => '/v1/accounts/{account_id}/balances',
                'method' => 'GET',
                'description' => 'Get account balances',
                'parameters' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'ABC12345'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'balances' => array(
                        'option_short_value' => 0,
                        'total_equity' => 45389.23,
                        'account_number' => 'ABC12345',
                        'account_type' => 'margin',
                        'close_pl' => 0,
                        'current_requirement' => 0,
                        'equity' => 45389.23,
                        'long_market_value' => 32589.87,
                        'market_value' => 32589.87,
                        'open_pl' => 1125.25,
                        'option_long_value' => 2345.50,
                        'option_requirement' => 0,
                        'pending_orders_count' => 2,
                        'short_market_value' => 0,
                        'stock_long_value' => 30244.37,
                        'total_cash' => 10454.36,
                        'uncleared_funds' => 0,
                        'pending_cash' => 0,
                        'margin' => array(
                            'fed_call' => 0,
                            'maintenance_call' => 0,
                            'option_buying_power' => 20908.72,
                            'stock_buying_power' => 20908.72,
                            'stock_short_value' => 0,
                            'sweep' => 0
                        )
                    )
                )
            ),
            
            'positions' => array(
                'endpoint' => '/v1/accounts/{account_id}/positions',
                'method' => 'GET',
                'description' => 'Get account positions',
                'parameters' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'ABC12345'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'positions' => array(
                        'position' => array(
                            array(
                                'cost_basis' => 17845.00,
                                'date_acquired' => '2024-11-05',
                                'id' => 98765432,
                                'quantity' => 100,
                                'symbol' => 'AAPL',
                                'price' => 178.45
                            ),
                            array(
                                'cost_basis' => 12399.87,
                                'date_acquired' => '2025-02-15',
                                'id' => 98765433,
                                'quantity' => 33,
                                'symbol' => 'MSFT',
                                'price' => 376.55
                            )
                        )
                    )
                )
            ),
            
            'orders' => array(
                'endpoint' => '/v1/accounts/{account_id}/orders',
                'method' => 'GET',
                'description' => 'Get account orders',
                'parameters' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'ABC12345'
                    ),
                    'includeInactive' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include inactive orders',
                        'default' => false,
                        'example' => 'true'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'orders' => array(
                        'order' => array(
                            array(
                                'id' => 12345678,
                                'status' => 'filled',
                                'partner_id' => 'API1234',
                                'created_at' => '2025-04-08T14:30:15Z',
                                'updated_at' => '2025-04-08T14:30:45Z',
                                'submitted_at' => '2025-04-08T14:30:15Z',
                                'filled_at' => '2025-04-08T14:30:45Z',
                                'expired_at' => null,
                                'canceled_at' => null,
                                'class' => 'equity',
                                'duration' => 'day',
                                'price' => 178.45,
                                'avg_fill_price' => 178.45,
                                'exec_quantity' => 100,
                                'last_fill_price' => 178.45,
                                'last_fill_quantity' => 100,
                                'remaining_quantity' => 0,
                                'order_type' => 'limit',
                                'side' => 'buy',
                                'symbol' => 'AAPL',
                                'quantity' => 100
                            )
                        )
                    )
                )
            ),
            
            'order' => array(
                'endpoint' => '/v1/accounts/{account_id}/orders/{order_id}',
                'method' => 'GET',
                'description' => 'Get a specific order by ID',
                'parameters' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'ABC12345'
                    ),
                    'order_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Order ID',
                        'example' => '12345678'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'order' => array(
                        'id' => 12345678,
                        'status' => 'filled',
                        'partner_id' => 'API1234',
                        'created_at' => '2025-04-08T14:30:15Z',
                        'updated_at' => '2025-04-08T14:30:45Z',
                        'submitted_at' => '2025-04-08T14:30:15Z',
                        'filled_at' => '2025-04-08T14:30:45Z',
                        'expired_at' => null,
                        'canceled_at' => null,
                        'class' => 'equity',
                        'duration' => 'day',
                        'price' => 178.45,
                        'avg_fill_price' => 178.45,
                        'exec_quantity' => 100,
                        'last_fill_price' => 178.45,
                        'last_fill_quantity' => 100,
                        'remaining_quantity' => 0,
                        'order_type' => 'limit',
                        'side' => 'buy',
                        'symbol' => 'AAPL',
                        'quantity' => 100
                    )
                )
            ),
            
            // Trading Endpoints
            'equity_order' => array(
                'endpoint' => '/v1/accounts/{account_id}/orders',
                'method' => 'POST',
                'description' => 'Place an equity order',
                'parameters' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'ABC12345'
                    ),
                    'class' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order class',
                        'enum' => array('equity', 'option', 'multileg'),
                        'example' => 'equity'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol',
                        'example' => 'AAPL'
                    ),
                    'side' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order side',
                        'enum' => array('buy', 'sell', 'buy_to_cover', 'sell_short'),
                        'example' => 'buy'
                    ),
                    'quantity' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Order quantity',
                        'example' => 100
                    ),
                    'type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order type',
                        'enum' => array('market', 'limit', 'stop', 'stop_limit'),
                        'example' => 'limit'
                    ),
                    'duration' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order duration',
                        'enum' => array('day', 'gtc', 'pre', 'post'),
                        'example' => 'day'
                    ),
                    'price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Order price (required for limit and stop_limit orders)',
                        'example' => 178.45
                    ),
                    'stop' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Stop price (required for stop and stop_limit orders)',
                        'example' => 180.00
                    ),
                    'tag' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Order tag/reference',
                        'example' => 'my-tag-1'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'order' => array(
                        'id' => 12345679,
                        'status' => 'pending',
                        'partner_id' => 'API1234',
                        'created_at' => '2025-04-09T10:15:25Z',
                        'updated_at' => '2025-04-09T10:15:25Z',
                        'submitted_at' => '2025-04-09T10:15:25Z',
                        'filled_at' => null,
                        'expired_at' => null,
                        'canceled_at' => null,
                        'class' => 'equity',
                        'duration' => 'day',
                        'price' => 178.45,
                        'avg_fill_price' => null,
                        'exec_quantity' => 0,
                        'last_fill_price' => null,
                        'last_fill_quantity' => null,
                        'remaining_quantity' => 100,
                        'order_type' => 'limit',
                        'side' => 'buy',
                        'symbol' => 'AAPL',
                        'quantity' => 100
                    )
                )
            ),
            
            'option_order' => array(
                'endpoint' => '/v1/accounts/{account_id}/orders',
                'method' => 'POST',
                'description' => 'Place an option order',
                'parameters' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'ABC12345'
                    ),
                    'class' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order class',
                        'enum' => array('equity', 'option', 'multileg'),
                        'example' => 'option'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Option symbol',
                        'example' => 'AAPL250117C00200000'
                    ),
                    'side' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order side',
                        'enum' => array('buy_to_open', 'buy_to_close', 'sell_to_open', 'sell_to_close'),
                        'example' => 'buy_to_open'
                    ),
                    'quantity' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Order quantity',
                        'example' => 1
                    ),
                    'type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order type',
                        'enum' => array('market', 'limit', 'stop', 'stop_limit'),
                        'example' => 'limit'
                    ),
                    'duration' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order duration',
                        'enum' => array('day', 'gtc'),
                        'example' => 'day'
                    ),
                    'price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Order price (required for limit and stop_limit orders)',
                        'example' => 16.20
                    ),
                    'stop' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Stop price (required for stop and stop_limit orders)',
                        'example' => 16.50
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'order' => array(
                        'id' => 12345680,
                        'status' => 'pending',
                        'partner_id' => 'API1234',
                        'created_at' => '2025-04-09T10:20:35Z',
                        'updated_at' => '2025-04-09T10:20:35Z',
                        'submitted_at' => '2025-04-09T10:20:35Z',
                        'filled_at' => null,
                        'expired_at' => null,
                        'canceled_at' => null,
                        'class' => 'option',
                        'duration' => 'day',
                        'price' => 16.20,
                        'avg_fill_price' => null,
                        'exec_quantity' => 0,
                        'last_fill_price' => null,
                        'last_fill_quantity' => null,
                        'remaining_quantity' => 1,
                        'order_type' => 'limit',
                        'side' => 'buy_to_open',
                        'symbol' => 'AAPL250117C00200000',
                        'quantity' => 1
                    )
                )
            ),
            
            'cancel_order' => array(
                'endpoint' => '/v1/accounts/{account_id}/orders/{order_id}',
                'method' => 'DELETE',
                'description' => 'Cancel an open order',
                'parameters' => array(
                    'account_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Account ID',
                        'example' => 'ABC12345'
                    ),
                    'order_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'description' => 'Order ID',
                        'example' => '12345679'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'order' => array(
                        'id' => 12345679,
                        'status' => 'canceled',
                        'partner_id' => 'API1234',
                        'created_at' => '2025-04-09T10:15:25Z',
                        'updated_at' => '2025-04-09T10:22:15Z',
                        'submitted_at' => '2025-04-09T10:15:25Z',
                        'filled_at' => null,
                        'expired_at' => null,
                        'canceled_at' => '2025-04-09T10:22:15Z',
                        'class' => 'equity',
                        'duration' => 'day',
                        'price' => 178.45,
                        'avg_fill_price' => null,
                        'exec_quantity' => 0,
                        'last_fill_price' => null,
                        'last_fill_quantity' => null,
                        'remaining_quantity' => 0,
                        'order_type' => 'limit',
                        'side' => 'buy',
                        'symbol' => 'AAPL',
                        'quantity' => 100
                    )
                )
            ),
            
            // Watchlist Endpoints
            'watchlists' => array(
                'endpoint' => '/v1/watchlists',
                'method' => 'GET',
                'description' => 'Get all watchlists',
                'parameters' => array(),
                'requires_auth' => true,
                'example_response' => array(
                    'watchlists' => array(
                        'watchlist' => array(
                            array(
                                'id' => 'default',
                                'name' => 'Default',
                                'public' => false,
                                'items_count' => 5
                            ),
                            array(
                                'id' => 'tech-stocks',
                                'name' => 'Tech Stocks',
                                'public' => false,
                                'items_count' => 8
                            )
                        )
                    )
                )
            ),
            
            'watchlist' => array(
                'endpoint' => '/v1/watchlists/{watchlist_id}',
                'method' => 'GET',
                'description' => 'Get a specific watchlist by ID',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => 'tech-stocks'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'watchlist' => array(
                        'id' => 'tech-stocks',
                        'name' => 'Tech Stocks',
                        'public' => false,
                        'items' => array(
                            'item' => array(
                                array(
                                    'symbol' => 'AAPL',
                                    'description' => 'Apple Inc',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'MSFT',
                                    'description' => 'Microsoft Corporation',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'NVDA',
                                    'description' => 'NVIDIA Corporation',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                )
                            )
                        )
                    )
                )
            ),
            
            'create_watchlist' => array(
                'endpoint' => '/v1/watchlists',
                'method' => 'POST',
                'description' => 'Create a new watchlist',
                'parameters' => array(
                    'name' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist name',
                        'example' => 'Energy Stocks'
                    ),
                    'symbols' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Initial symbols (comma separated)',
                        'example' => 'XOM,CVX,COP,SLB'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'watchlist' => array(
                        'id' => 'energy-stocks',
                        'name' => 'Energy Stocks',
                        'public' => false,
                        'items' => array(
                            'item' => array(
                                array(
                                    'symbol' => 'XOM',
                                    'description' => 'Exxon Mobil Corporation',
                                    'exchange' => 'NYSE',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'CVX',
                                    'description' => 'Chevron Corporation',
                                    'exchange' => 'NYSE',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'COP',
                                    'description' => 'ConocoPhillips',
                                    'exchange' => 'NYSE',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'SLB',
                                    'description' => 'Schlumberger Limited',
                                    'exchange' => 'NYSE',
                                    'type' => 'stock'
                                )
                            )
                        )
                    )
                )
            ),
            
            'update_watchlist' => array(
                'endpoint' => '/v1/watchlists/{watchlist_id}',
                'method' => 'PUT',
                'description' => 'Update a watchlist name',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => 'energy-stocks'
                    ),
                    'name' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'New watchlist name',
                        'example' => 'Oil & Gas Stocks'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'watchlist' => array(
                        'id' => 'energy-stocks',
                        'name' => 'Oil & Gas Stocks',
                        'public' => false
                    )
                )
            ),
            
            'delete_watchlist' => array(
                'endpoint' => '/v1/watchlists/{watchlist_id}',
                'method' => 'DELETE',
                'description' => 'Delete a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => 'energy-stocks'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'watchlist' => array(
                        'id' => 'energy-stocks',
                        'name' => 'Oil & Gas Stocks',
                        'public' => false
                    )
                )
            ),
            
            'add_watchlist_symbols' => array(
                'endpoint' => '/v1/watchlists/{watchlist_id}/symbols',
                'method' => 'POST',
                'description' => 'Add symbols to a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => 'tech-stocks'
                    ),
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbols to add (comma separated)',
                        'example' => 'GOOGL,AMZN,META'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'watchlist' => array(
                        'id' => 'tech-stocks',
                        'name' => 'Tech Stocks',
                        'public' => false,
                        'items' => array(
                            'item' => array(
                                array(
                                    'symbol' => 'AAPL',
                                    'description' => 'Apple Inc',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'MSFT',
                                    'description' => 'Microsoft Corporation',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'NVDA',
                                    'description' => 'NVIDIA Corporation',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'GOOGL',
                                    'description' => 'Alphabet Inc Class A',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'AMZN',
                                    'description' => 'Amazon.com Inc',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                ),
                                array(
                                    'symbol' => 'META',
                                    'description' => 'Meta Platforms Inc',
                                    'exchange' => 'NASDAQ',
                                    'type' => 'stock'
                                )
                            )
                        )
                    )
                )
            ),
            
            'remove_watchlist_symbol' => array(
                'endpoint' => '/v1/watchlists/{watchlist_id}/symbols/{symbol}',
                'method' => 'DELETE',
                'description' => 'Remove a symbol from a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => 'tech-stocks'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol to remove',
                        'example' => 'META'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'watchlist' => array(
                        'id' => 'tech-stocks',
                        'name' => 'Tech Stocks',
                        'public' => false
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
            $base_url = 'https://api.tradier.com'; // Default base URL for Tradier API
        }
        
        $url = $base_url . $endpoint['endpoint'];
        
        // Replace URL parameters (e.g., {account_id}, {order_id}, {watchlist_id})
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