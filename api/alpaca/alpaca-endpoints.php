<?php
/**
 * TradePress Alpaca API Endpoints
 *
 * Defines endpoints and parameters for the Alpaca trading platform
 * API Documentation: https://alpaca.markets/docs/api-documentation/
 * 
 * @package TradePress
 * @subpackage API\Alpaca
 * @version 1.0.0
 * @since 2025-04-08
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Alpaca API Endpoints class
 */
class TradePress_Alpaca_Endpoints {
    
    /**
     * API Restrictions and Rate Limits
     * 
     * Based on Alpaca API documentation
     * 
     * @return array API restrictions information
     */
    public static function get_api_restrictions() {
        return array(
            'rate_limits' => array(
                'description' => 'Maximum number of requests per time window',
                'details' => array(
                    'trading_api' => '200 requests per minute per API key',
                    'market_data_api_basic' => '200 requests per minute per API key',
                    'market_data_api_unlimited' => '10,000 requests per minute per API key',
                    'account_activities' => '100 requests per minute',
                    'orders' => '100 requests per minute'
                )
            ),
            'subscription_plans' => array(
                'description' => 'Available Market Data subscription plans',
                'details' => array(
                    'basic' => array(
                        'name' => 'Basic',
                        'price' => 'Free',
                        'equities' => array(
                            'securities_coverage' => 'US Stocks & ETFs',
                            'real_time_market_coverage' => 'IEX only',
                            'all_us_exchanges' => false,
                            'websocket_subscriptions' => '30 symbols',
                            'historical_timeframe' => 'Since 2016',
                            'historical_data_limitation' => 'latest 15 minutes only',
                            'historical_api_calls' => '200 / min'
                        ),
                        'options' => array(
                            'securities_coverage' => 'US Options Securities',
                            'real_time_market_coverage' => 'Indicative Pricing Feed only',
                            'websocket_subscriptions' => '200 quotes',
                            'historical_data_limitation' => 'latest 15 minutes only',
                            'historical_api_calls' => '200 / min'
                        )
                    ),
                    'algo_trader_plus' => array(
                        'name' => 'Algo Trader Plus',
                        'price' => '$99 / month',
                        'equities' => array(
                            'securities_coverage' => 'US Stocks & ETFs',
                            'real_time_market_coverage' => 'All US Stock Exchanges',
                            'all_us_exchanges' => true,
                            'websocket_subscriptions' => 'Unlimited',
                            'historical_timeframe' => 'Since 2016',
                            'historical_data_limitation' => 'no restriction',
                            'historical_api_calls' => '10,000 / min'
                        ),
                        'options' => array(
                            'securities_coverage' => 'US Options Securities',
                            'real_time_market_coverage' => 'OPRA Feed',
                            'websocket_subscriptions' => '1000 quotes',
                            'historical_data_limitation' => 'no restriction',
                            'historical_api_calls' => '10,000 / min'
                        )
                    ),
                    'broker_partners' => array(
                        'name' => 'Broker API',
                        'details' => array(
                            'standard' => array(
                                'name' => 'Standard',
                                'rpm' => '1,000',
                                'stream_connection_limit' => '5',
                                'stream_symbol_limit' => 'unlimited',
                                'price' => 'included',
                                'options_indicative_feed' => 'included',
                                'options_opra_feed' => 'additional $1,000 per month'
                            ),
                            'standard_plus_3000' => array(
                                'name' => 'StandardPlus3000',
                                'rpm' => '3,000',
                                'stream_connection_limit' => '5',
                                'stream_symbol_limit' => 'unlimited',
                                'price' => '$500',
                                'options_indicative_feed' => 'included',
                                'options_opra_feed' => 'additional $1,000 per month'
                            )
                        )
                    )
                )
            ),
            'authentication' => array(
                'description' => 'API Authentication methods',
                'details' => array(
                    'market_data_api' => array(
                        'headers' => array(
                            'APCA-API-KEY-ID' => 'Your API key ID',
                            'APCA-API-SECRET-KEY' => 'Your API secret key'
                        )
                    ),
                    'broker_api' => array(
                        'method' => 'HTTP Basic authentication',
                        'format' => 'key:secret (base-64 encoded)',
                        'header' => 'Authorization: Basic {base64_encoded_credentials}'
                    ),
                    'oauth' => array(
                        'header' => 'Authorization: Bearer YOUR_ACCESS_TOKEN'
                    )
                )
            ),
            'environments' => array(
                'description' => 'Available API environments',
                'details' => array(
                    'live' => 'https://api.alpaca.markets',
                    'paper' => 'https://paper-api.alpaca.markets',
                    'market_data' => 'https://data.alpaca.markets'
                )
            ),
            'data_formats' => array(
                'description' => 'Supported data formats',
                'details' => array(
                    'json' => 'Default response format'
                )
            ),
            'data_sources' => array(
                'description' => 'Market data sources',
                'details' => array(
                    'equities' => 'CTA (Consolidated Tape Association) and UTP (Unlisted Trading Privileges) stream',
                    'options' => 'OPRA (Options Price Reporting Authority)'
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
            // Account endpoints
            'account' => array(
                'endpoint' => '/v2/account',
                'method' => 'GET',
                'description' => 'Get account information',
                'parameters' => array(),
                'example_response' => array(
                    'id' => '904837e3-3b76-47ec-b432-046db621571b',
                    'account_number' => 'ALPCA123456',
                    'status' => 'ACTIVE',
                    'currency' => 'USD',
                    'cash' => 4235.76,
                    'portfolio_value' => 15000.32,
                    'pattern_day_trader' => false,
                    'trading_blocked' => false,
                    'transfers_blocked' => false,
                    'account_blocked' => false,
                    'created_at' => '2025-03-17T09:46:21Z',
                    'shorting_enabled' => true,
                    'buying_power' => 8471.52,
                    'daytrading_buying_power' => 16943.04,
                    'non_marginable_buying_power' => 4235.76,
                    'equity' => 15000.32,
                    'last_equity' => 14897.71,
                    'multiplier' => '2'
                )
            ),
            'account_configurations' => array(
                'endpoint' => '/v2/account/configurations',
                'method' => 'GET',
                'description' => 'Get account configuration settings',
                'parameters' => array(),
                'example_response' => array(
                    'dtbp_check' => 'entry',
                    'no_shorting' => false,
                    'suspend_trade' => false,
                    'trade_confirm_email' => 'all',
                    'pdt_check' => 'entry'
                )
            ),
            'update_account_configurations' => array(
                'endpoint' => '/v2/account/configurations',
                'method' => 'PATCH',
                'description' => 'Update account configuration settings',
                'parameters' => array(
                    'dtbp_check' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Day trading buying power check',
                        'enum' => array('entry', 'exit', 'both', 'none'),
                        'example' => 'entry'
                    ),
                    'no_shorting' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Whether to disable shorting',
                        'example' => false
                    ),
                    'suspend_trade' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Whether to suspend new trades',
                        'example' => false
                    ),
                    'trade_confirm_email' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Trade confirmation email preferences',
                        'enum' => array('all', 'none'),
                        'example' => 'all'
                    ),
                    'pdt_check' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Pattern Day Trader check',
                        'enum' => array('entry', 'exit', 'both', 'none'),
                        'example' => 'entry'
                    )
                ),
                'example_response' => array(
                    'dtbp_check' => 'entry',
                    'no_shorting' => false,
                    'suspend_trade' => false,
                    'trade_confirm_email' => 'all',
                    'pdt_check' => 'entry'
                )
            ),
            'portfolio_history' => array(
                'endpoint' => '/v2/account/portfolio/history',
                'method' => 'GET',
                'description' => 'Get portfolio history',
                'parameters' => array(
                    'period' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Time period of data',
                        'enum' => array('1D', '1W', '1M', '3M', '6M', '1A', 'all'),
                        'example' => '1M'
                    ),
                    'timeframe' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Resolution of time window',
                        'enum' => array('1Min', '5Min', '15Min', '1H', '1D'),
                        'example' => '1D'
                    ),
                    'date_end' => array(
                        'required' => false,
                        'type' => 'string',
                        'format' => 'date',
                        'description' => 'End date of the history (YYYY-MM-DD)',
                        'example' => '2025-04-01'
                    ),
                    'extended_hours' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Whether to include extended hours in the result',
                        'example' => false
                    )
                ),
                'example_response' => array(
                    'timestamp' => array(1714521600, 1714608000, 1714694400),
                    'equity' => array(14932.16, 15012.38, 15000.32),
                    'profit_loss' => array(32.16, 80.22, -12.06),
                    'profit_loss_pct' => array(0.00215, 0.00537, -0.00080),
                    'base_value' => 14900.00,
                    'timeframe' => '1D'
                )
            ),
            
            // Watchlist endpoints
            'watchlists' => array(
                'endpoint' => '/v2/watchlists',
                'method' => 'GET',
                'description' => 'Get all watchlists',
                'parameters' => array(),
                'example_response' => array(
                    array(
                        'id' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc',
                        'account_id' => '904837e3-3b76-47ec-b432-046db621571b',
                        'name' => 'Tech Stocks',
                        'created_at' => '2025-03-21T11:32:52.011Z',
                        'updated_at' => '2025-04-07T15:16:34.543Z',
                        'assets' => array(
                            array(
                                'id' => 'b0b6dd9d-8b9b-48a9-ba46-b9d54906e415',
                                'class' => 'us_equity',
                                'exchange' => 'NASDAQ',
                                'symbol' => 'AAPL',
                                'name' => 'Apple Inc.',
                                'status' => 'active',
                                'tradable' => true
                            ),
                            array(
                                'id' => '4ea14189-c075-4046-96c8-3f5862319aa5',
                                'class' => 'us_equity',
                                'exchange' => 'NASDAQ',
                                'symbol' => 'MSFT',
                                'name' => 'Microsoft Corporation',
                                'status' => 'active',
                                'tradable' => true
                            )
                        )
                    ),
                    array(
                        'id' => 'b3c7b827-4aa5-486d-a187-34938a9a7f3e',
                        'account_id' => '904837e3-3b76-47ec-b432-046db621571b',
                        'name' => 'Energy Sector',
                        'created_at' => '2025-03-22T14:52:37.923Z',
                        'updated_at' => '2025-04-05T18:03:12.147Z',
                        'assets' => array(
                            array(
                                'id' => 'f23bb14d-6c7a-434c-9a5b-7cf2c913e1d9',
                                'class' => 'us_equity',
                                'exchange' => 'NYSE',
                                'symbol' => 'XOM',
                                'name' => 'Exxon Mobil Corporation',
                                'status' => 'active',
                                'tradable' => true
                            ),
                            array(
                                'id' => 'a8b16c5e-94a3-4f9c-b52c-096dcd1b7e4a',
                                'class' => 'us_equity',
                                'exchange' => 'NYSE',
                                'symbol' => 'CVX',
                                'name' => 'Chevron Corporation',
                                'status' => 'active',
                                'tradable' => true
                            )
                        )
                    )
                )
            ),
            'create_watchlist' => array(
                'endpoint' => '/v2/watchlists',
                'method' => 'POST',
                'description' => 'Create a new watchlist',
                'parameters' => array(
                    'name' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Name of the watchlist',
                        'example' => 'Tech Stocks'
                    ),
                    'symbols' => array(
                        'required' => false,
                        'type' => 'array',
                        'description' => 'Array of symbols to add to the watchlist',
                        'example' => array('AAPL', 'MSFT', 'GOOGL')
                    )
                ),
                'example_response' => array(
                    'id' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc',
                    'account_id' => '904837e3-3b76-47ec-b432-046db621571b',
                    'name' => 'Tech Stocks',
                    'created_at' => '2025-04-17T13:54:16.347Z',
                    'updated_at' => '2025-04-17T13:54:16.347Z',
                    'assets' => array(
                        array(
                            'id' => 'b0b6dd9d-8b9b-48a9-ba46-b9d54906e415',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc.',
                            'status' => 'active',
                            'tradable' => true
                        ),
                        array(
                            'id' => '4ea14189-c075-4046-96c8-3f5862319aa5',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation',
                            'status' => 'active',
                            'tradable' => true
                        ),
                        array(
                            'id' => '83d6842e-7b2e-4cb9-a63c-6c73c0da79fd',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'GOOGL',
                            'name' => 'Alphabet Inc.',
                            'status' => 'active',
                            'tradable' => true
                        )
                    )
                )
            ),
            'get_watchlist' => array(
                'endpoint' => '/v2/watchlists/{watchlist_id}',
                'method' => 'GET',
                'description' => 'Get a specific watchlist by ID',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc'
                    )
                ),
                'example_response' => array(
                    'id' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc',
                    'account_id' => '904837e3-3b76-47ec-b432-046db621571b',
                    'name' => 'Tech Stocks',
                    'created_at' => '2025-03-21T11:32:52.011Z',
                    'updated_at' => '2025-04-07T15:16:34.543Z',
                    'assets' => array(
                        array(
                            'id' => 'b0b6dd9d-8b9b-48a9-ba46-b9d54906e415',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc.',
                            'status' => 'active',
                            'tradable' => true
                        ),
                        array(
                            'id' => '4ea14189-c075-4046-96c8-3f5862319aa5',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation',
                            'status' => 'active',
                            'tradable' => true
                        )
                    )
                )
            ),
            'update_watchlist' => array(
                'endpoint' => '/v2/watchlists/{watchlist_id}',
                'method' => 'PUT',
                'description' => 'Update a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc'
                    ),
                    'name' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'New name for the watchlist',
                        'example' => 'Tech Leaders'
                    )
                ),
                'example_response' => array(
                    'id' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc',
                    'account_id' => '904837e3-3b76-47ec-b432-046db621571b',
                    'name' => 'Tech Leaders',
                    'created_at' => '2025-03-21T11:32:52.011Z',
                    'updated_at' => '2025-04-17T14:23:45.612Z',
                    'assets' => array(
                        array(
                            'id' => 'b0b6dd9d-8b9b-48a9-ba46-b9d54906e415',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc.',
                            'status' => 'active',
                            'tradable' => true
                        ),
                        array(
                            'id' => '4ea14189-c075-4046-96c8-3f5862319aa5',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation',
                            'status' => 'active',
                            'tradable' => true
                        )
                    )
                )
            ),
            'delete_watchlist' => array(
                'endpoint' => '/v2/watchlists/{watchlist_id}',
                'method' => 'DELETE',
                'description' => 'Delete a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc'
                    )
                ),
                'example_response' => null
            ),
            'add_to_watchlist' => array(
                'endpoint' => '/v2/watchlists/{watchlist_id}',
                'method' => 'POST',
                'description' => 'Add an asset to a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol to add to watchlist',
                        'example' => 'GOOGL'
                    )
                ),
                'example_response' => array(
                    'id' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc',
                    'account_id' => '904837e3-3b76-47ec-b432-046db621571b',
                    'name' => 'Tech Stocks',
                    'created_at' => '2025-03-21T11:32:52.011Z',
                    'updated_at' => '2025-04-17T14:25:16.823Z',
                    'assets' => array(
                        array(
                            'id' => 'b0b6dd9d-8b9b-48a9-ba46-b9d54906e415',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc.',
                            'status' => 'active',
                            'tradable' => true
                        ),
                        array(
                            'id' => '4ea14189-c075-4046-96c8-3f5862319aa5',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation',
                            'status' => 'active',
                            'tradable' => true
                        ),
                        array(
                            'id' => '83d6842e-7b2e-4cb9-a63c-6c73c0da79fd',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'GOOGL',
                            'name' => 'Alphabet Inc.',
                            'status' => 'active',
                            'tradable' => true
                        )
                    )
                )
            ),
            'remove_from_watchlist' => array(
                'endpoint' => '/v2/watchlists/{watchlist_id}/{symbol}',
                'method' => 'DELETE',
                'description' => 'Remove an asset from a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol to remove from watchlist',
                        'example' => 'MSFT'
                    )
                ),
                'example_response' => array(
                    'id' => '7271c1fa-36cc-4a20-9c1a-4197206fa3bc',
                    'account_id' => '904837e3-3b76-47ec-b432-046db621571b',
                    'name' => 'Tech Stocks',
                    'created_at' => '2025-03-21T11:32:52.011Z',
                    'updated_at' => '2025-04-17T14:26:43.152Z',
                    'assets' => array(
                        array(
                            'id' => 'b0b6dd9d-8b9b-48a9-ba46-b9d54906e415',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc.',
                            'status' => 'active',
                            'tradable' => true
                        ),
                        array(
                            'id' => '83d6842e-7b2e-4cb9-a63c-6c73c0da79fd',
                            'class' => 'us_equity',
                            'exchange' => 'NASDAQ',
                            'symbol' => 'GOOGL',
                            'name' => 'Alphabet Inc.',
                            'status' => 'active',
                            'tradable' => true
                        )
                    )
                )
            ),
            
            // Market Data API endpoints
            'bars' => array(
                'endpoint' => '/v2/stocks/{symbol}/bars',
                'method' => 'GET',
                'description' => 'Get historical bars for a stock',
                'base_url' => 'https://data.alpaca.markets',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Stock symbol',
                        'example' => 'AAPL'
                    ),
                    'timeframe' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Bar timeframe',
                        'enum' => array('1Min', '5Min', '15Min', '30Min', '1Hour', '1Day', '1Week', '1Month'),
                        'example' => '1Day'
                    ),
                    'start' => array(
                        'required' => false,
                        'type' => 'string',
                        'format' => 'datetime',
                        'description' => 'Start time (RFC3339 format)',
                        'example' => '2025-01-01T00:00:00Z'
                    ),
                    'end' => array(
                        'required' => false,
                        'type' => 'string',
                        'format' => 'datetime',
                        'description' => 'End time (RFC3339 format)',
                        'example' => '2025-04-01T00:00:00Z'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of bars to return (max 10000)',
                        'example' => 1000
                    )
                ),
                'example_response' => array(
                    'bars' => array(
                        array(
                            't' => '2025-04-01T04:00:00Z',
                            'o' => 150.25,
                            'h' => 152.75,
                            'l' => 149.80,
                            'c' => 151.50,
                            'v' => 25678900,
                            'n' => 156789,
                            'vw' => 151.23
                        )
                    ),
                    'symbol' => 'AAPL',
                    'next_page_token' => null
                )
            ),
            'latest_bars' => array(
                'endpoint' => '/v2/stocks/bars/latest',
                'method' => 'GET',
                'description' => 'Get latest bars for one or more stocks',
                'base_url' => 'https://data.alpaca.markets',
                'parameters' => array(
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols',
                        'example' => 'AAPL,MSFT,GOOGL'
                    )
                ),
                'example_response' => array(
                    'bars' => array(
                        'AAPL' => array(
                            't' => '2025-04-17T20:00:00Z',
                            'o' => 151.50,
                            'h' => 152.25,
                            'l' => 150.75,
                            'c' => 151.90,
                            'v' => 18456789,
                            'n' => 98765,
                            'vw' => 151.82
                        )
                    )
                )
            ),
            'snapshots' => array(
                'endpoint' => '/v2/stocks/snapshots',
                'method' => 'GET',
                'description' => 'Get snapshots for one or more stocks',
                'base_url' => 'https://data.alpaca.markets',
                'parameters' => array(
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols',
                        'example' => 'AAPL,MSFT,GOOGL'
                    )
                ),
                'example_response' => array(
                    'snapshots' => array(
                        'AAPL' => array(
                            'latestTrade' => array(
                                't' => '2025-04-17T20:00:00.123456789Z',
                                'p' => 151.92,
                                's' => 100
                            ),
                            'latestQuote' => array(
                                't' => '2025-04-17T20:00:00.123456789Z',
                                'ap' => 151.95,
                                'as' => 100,
                                'bp' => 151.90,
                                'bs' => 200
                            ),
                            'minuteBar' => array(
                                't' => '2025-04-17T19:59:00Z',
                                'o' => 151.85,
                                'h' => 151.95,
                                'l' => 151.80,
                                'c' => 151.92,
                                'v' => 12345
                            ),
                            'dailyBar' => array(
                                't' => '2025-04-17T04:00:00Z',
                                'o' => 150.25,
                                'h' => 152.75,
                                'l' => 149.80,
                                'c' => 151.92,
                                'v' => 25678900
                            )
                        )
                    )
                )
            )
        );
    }
    
    /**
     * Get a specific endpoint configuration
     *
     * @param string $endpoint_name Name of the endpoint
     * @return array|false Endpoint configuration or false if not found
     */
    public static function get_endpoint($endpoint_name) {
        $endpoints = self::get_endpoints();
        return isset($endpoints[$endpoint_name]) ? $endpoints[$endpoint_name] : false;
    }
    
    /**
     * Get the full URL for an endpoint
     *
     * @param string $endpoint_name Name of the endpoint
     * @param array $params Parameters to include in the URL
     * @param string $base_url_override Optional base URL override
     * @param bool $use_paper Whether to use paper trading URL
     * @return string Full URL
     */
    public static function get_endpoint_url($endpoint_name, $params = array(), $base_url_override = '', $use_paper = true) {
        $endpoint = self::get_endpoint($endpoint_name);
        if (!$endpoint) {
            return '';
        }
        
        // Determine base URL
        if (!empty($base_url_override)) {
            $base_url = $base_url_override;
        } else {
            // Default Alpaca API base URLs
            if ($use_paper) {
                $base_url = 'https://paper-api.alpaca.markets';
            } else {
                $base_url = 'https://api.alpaca.markets';
            }
        }
        
        // Get endpoint path
        $path = $endpoint['endpoint'];
        
        // Replace path parameters
        if (preg_match_all('/{([^}]+)}/', $path, $matches)) {
            foreach ($matches[1] as $param_name) {
                if (isset($params[$param_name])) {
                    $path = str_replace('{' . $param_name . '}', urlencode($params[$param_name]), $path);
                }
            }
        }
        
        // Build query string for GET requests with non-path parameters
        $query = '';
        if ($endpoint['method'] === 'GET') {
            $query_params = array();
            
            // Get path parameters
            $path_params = array();
            if (preg_match_all('/{([^}]+)}/', $endpoint['endpoint'], $matches)) {
                $path_params = $matches[1];
            }
            
            // Add non-path parameters to query string
            foreach ($params as $key => $value) {
                if (!in_array($key, $path_params)) {
                    if (is_array($value)) {
                        $query_params[$key] = implode(',', $value);
                    } else {
                        $query_params[$key] = $value;
                    }
                }
            }
            
            if (!empty($query_params)) {
                $query = '?' . http_build_query($query_params);
            }
        }
        
        return $base_url . $path . $query;
    }

    /**
     * Get endpoints based on real API data instead of example data
     *
     * @param bool $force_real_data Whether to force using real API data
     * @return array Array of endpoints with real data
     */
    public static function get_real_endpoints() {
        // Start with the standard endpoints structure
        $endpoints = self::get_endpoints();
        
        // Force all endpoints to use real API calls
        foreach ($endpoints as $key => &$endpoint) {
            if (isset($endpoint['example_response'])) {
                // Flag each endpoint to never use example data
                $endpoint['use_real_data'] = true;
            }
        }
        
        return $endpoints;
    }
}
