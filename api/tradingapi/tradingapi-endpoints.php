<?php
/**
 * TradePress Trading API Endpoints
 *
 * Defines endpoints and parameters for the Trading API platform
 * API Documentation: https://docs.tradingapi.com
 * 
 * @package TradePress
 * @subpackage API\TradingAPI
 * @version 1.0.0
 * @since 2025-04-09
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Trading API Endpoints class
 */
class TradePress_TradingAPI_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Account Endpoints
            'account' => array(
                'endpoint' => '/v1/account',
                'method' => 'GET',
                'description' => 'Get account information',
                'parameters' => array(),
                'requires_auth' => true,
                'example_response' => array(
                    'account_id' => 'ACC123456',
                    'account_number' => '123456789',
                    'account_type' => 'margin',
                    'status' => 'active',
                    'created_at' => '2024-12-15T09:30:45Z',
                    'currency' => 'USD',
                    'buying_power' => 45000.75,
                    'cash' => 15000.25,
                    'portfolio_value' => 58750.50,
                    'day_trading_buying_power' => 120000.00,
                    'equity' => 58750.50,
                    'last_equity' => 57890.75,
                    'long_market_value' => 43750.25,
                    'short_market_value' => 0,
                    'initial_margin' => 21875.13,
                    'maintenance_margin' => 13125.08,
                    'sma' => 15000.25,
                    'is_pattern_day_trader' => false,
                    'trading_blocked' => false,
                    'transfers_blocked' => false,
                    'account_blocked' => false
                )
            ),
            
            'portfolio_history' => array(
                'endpoint' => '/v1/account/portfolio/history',
                'method' => 'GET',
                'description' => 'Get portfolio history',
                'parameters' => array(
                    'period' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Time period of data',
                        'enum' => array('1d', '5d', '1m', '3m', '6m', '1y', 'all'),
                        'default' => '1m',
                        'example' => '3m'
                    ),
                    'timeframe' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Resolution of time window',
                        'enum' => array('1min', '5min', '15min', '1h', '1d'),
                        'default' => '1d',
                        'example' => '1d'
                    ),
                    'date_end' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date (format: YYYY-MM-DD)',
                        'example' => '2025-04-01'
                    ),
                    'extended_hours' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include extended hours in result',
                        'default' => false,
                        'example' => true
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'timestamp' => [1709251200000, 1709337600000, 1709596800000],
                    'equity' => [56890.25, 57120.50, 57890.75],
                    'profit_loss' => [232.50, 230.25, 770.25],
                    'profit_loss_pct' => [0.41, 0.40, 1.34],
                    'base_value' => 56657.75,
                    'timeframe' => '1d'
                )
            ),
            
            // Positions Endpoints
            'positions' => array(
                'endpoint' => '/v1/positions',
                'method' => 'GET',
                'description' => 'Get all open positions',
                'parameters' => array(),
                'requires_auth' => true,
                'example_response' => array(
                    array(
                        'asset_id' => 'a8b16e4c-8834-4781-91dc-bd4e3d526e96',
                        'symbol' => 'AAPL',
                        'exchange' => 'NASDAQ',
                        'asset_class' => 'us_equity',
                        'avg_entry_price' => 178.85,
                        'qty' => 100,
                        'qty_available' => 100,
                        'side' => 'long',
                        'market_value' => 18025.00,
                        'cost_basis' => 17885.00,
                        'unrealized_pl' => 140.00,
                        'unrealized_plpc' => 0.0078,
                        'unrealized_intraday_pl' => 85.00,
                        'unrealized_intraday_plpc' => 0.0047,
                        'current_price' => 180.25,
                        'lastday_price' => 179.40,
                        'change_today' => 0.0047
                    ),
                    array(
                        'asset_id' => 'b0a461f1-5efd-4c4d-957c-4df58a1b61c1',
                        'symbol' => 'MSFT',
                        'exchange' => 'NASDAQ',
                        'asset_class' => 'us_equity',
                        'avg_entry_price' => 376.55,
                        'qty' => 50,
                        'qty_available' => 50,
                        'side' => 'long',
                        'market_value' => 19077.50,
                        'cost_basis' => 18827.50,
                        'unrealized_pl' => 250.00,
                        'unrealized_plpc' => 0.0133,
                        'unrealized_intraday_pl' => 125.00,
                        'unrealized_intraday_plpc' => 0.0066,
                        'current_price' => 381.55,
                        'lastday_price' => 379.05,
                        'change_today' => 0.0066
                    )
                )
            ),
            
            'position' => array(
                'endpoint' => '/v1/positions/{symbol}',
                'method' => 'GET',
                'description' => 'Get position for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol identifier',
                        'example' => 'AAPL'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'asset_id' => 'a8b16e4c-8834-4781-91dc-bd4e3d526e96',
                    'symbol' => 'AAPL',
                    'exchange' => 'NASDAQ',
                    'asset_class' => 'us_equity',
                    'avg_entry_price' => 178.85,
                    'qty' => 100,
                    'qty_available' => 100,
                    'side' => 'long',
                    'market_value' => 18025.00,
                    'cost_basis' => 17885.00,
                    'unrealized_pl' => 140.00,
                    'unrealized_plpc' => 0.0078,
                    'unrealized_intraday_pl' => 85.00,
                    'unrealized_intraday_plpc' => 0.0047,
                    'current_price' => 180.25,
                    'lastday_price' => 179.40,
                    'change_today' => 0.0047
                )
            ),
            
            'close_position' => array(
                'endpoint' => '/v1/positions/{symbol}',
                'method' => 'DELETE',
                'description' => 'Close (liquidate) a position',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol identifier',
                        'example' => 'AAPL'
                    ),
                    'qty' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Quantity to close (omit to close all)',
                        'example' => 50
                    ),
                    'percentage' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Percentage of position to close (1-100)',
                        'example' => 50
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'status' => 'success',
                    'symbol' => 'AAPL',
                    'qty_closed' => 50,
                    'remaining_qty' => 50,
                    'order_id' => '61e69015-8549-4bfd-b9c3-01e75843f47d'
                )
            ),
            
            'close_all_positions' => array(
                'endpoint' => '/v1/positions',
                'method' => 'DELETE',
                'description' => 'Close (liquidate) all positions',
                'parameters' => array(
                    'cancel_orders' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Cancel open orders before closing positions',
                        'default' => false,
                        'example' => true
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'status' => 'success',
                    'positions_closed' => 3,
                    'orders_canceled' => 2
                )
            ),
            
            // Orders Endpoints
            'orders' => array(
                'endpoint' => '/v1/orders',
                'method' => 'GET',
                'description' => 'Get a list of orders',
                'parameters' => array(
                    'status' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Order status to filter by',
                        'enum' => array('open', 'closed', 'all'),
                        'default' => 'open',
                        'example' => 'all'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of orders to return',
                        'default' => 100,
                        'example' => 50
                    ),
                    'after' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Get orders after this date (format: YYYY-MM-DD)',
                        'example' => '2025-03-01'
                    ),
                    'until' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Get orders until this date (format: YYYY-MM-DD)',
                        'example' => '2025-04-01'
                    ),
                    'direction' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Order direction for results (chronological)',
                        'enum' => array('asc', 'desc'),
                        'default' => 'desc',
                        'example' => 'asc'
                    ),
                    'symbols' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols',
                        'example' => 'AAPL,MSFT,GOOGL'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    array(
                        'id' => '61e69015-8549-4bfd-b9c3-01e75843f47d',
                        'client_order_id' => 'eb9e2aaa-f71a-4f51-b5b4-52a6c565dad4',
                        'created_at' => '2025-04-09T14:15:22Z',
                        'updated_at' => '2025-04-09T14:15:22Z',
                        'submitted_at' => '2025-04-09T14:15:22Z',
                        'filled_at' => null,
                        'expired_at' => null,
                        'canceled_at' => null,
                        'failed_at' => null,
                        'replaced_at' => null,
                        'replaced_by' => null,
                        'replaces' => null,
                        'asset_id' => 'a8b16e4c-8834-4781-91dc-bd4e3d526e96',
                        'symbol' => 'AAPL',
                        'asset_class' => 'us_equity',
                        'notional' => null,
                        'qty' => 25,
                        'filled_qty' => 0,
                        'filled_avg_price' => null,
                        'order_class' => '',
                        'order_type' => 'limit',
                        'type' => 'limit',
                        'side' => 'buy',
                        'time_in_force' => 'day',
                        'limit_price' => '179.75',
                        'stop_price' => null,
                        'status' => 'new',
                        'extended_hours' => false,
                        'legs' => null,
                        'trail_percent' => null,
                        'trail_price' => null,
                        'hwm' => null
                    ),
                    array(
                        'id' => '61e69015-8549-4bfd-b9c3-01e75843f47e',
                        'client_order_id' => 'f71a9e2a-4f51-aaf7-5b54-2a6c565dad41',
                        'created_at' => '2025-04-09T13:29:47Z',
                        'updated_at' => '2025-04-09T13:30:03Z',
                        'submitted_at' => '2025-04-09T13:29:47Z',
                        'filled_at' => '2025-04-09T13:30:03Z',
                        'expired_at' => null,
                        'canceled_at' => null,
                        'failed_at' => null,
                        'replaced_at' => null,
                        'replaced_by' => null,
                        'replaces' => null,
                        'asset_id' => 'b0a461f1-5efd-4c4d-957c-4df58a1b61c1',
                        'symbol' => 'MSFT',
                        'asset_class' => 'us_equity',
                        'notional' => null,
                        'qty' => 10,
                        'filled_qty' => 10,
                        'filled_avg_price' => '379.50',
                        'order_class' => '',
                        'order_type' => 'market',
                        'type' => 'market',
                        'side' => 'buy',
                        'time_in_force' => 'day',
                        'limit_price' => null,
                        'stop_price' => null,
                        'status' => 'filled',
                        'extended_hours' => false,
                        'legs' => null,
                        'trail_percent' => null,
                        'trail_price' => null,
                        'hwm' => null
                    )
                )
            ),
            
            'order' => array(
                'endpoint' => '/v1/orders/{order_id}',
                'method' => 'GET',
                'description' => 'Get a specific order by ID',
                'parameters' => array(
                    'order_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order ID',
                        'example' => '61e69015-8549-4bfd-b9c3-01e75843f47d'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'id' => '61e69015-8549-4bfd-b9c3-01e75843f47d',
                    'client_order_id' => 'eb9e2aaa-f71a-4f51-b5b4-52a6c565dad4',
                    'created_at' => '2025-04-09T14:15:22Z',
                    'updated_at' => '2025-04-09T14:15:22Z',
                    'submitted_at' => '2025-04-09T14:15:22Z',
                    'filled_at' => null,
                    'expired_at' => null,
                    'canceled_at' => null,
                    'failed_at' => null,
                    'replaced_at' => null,
                    'replaced_by' => null,
                    'replaces' => null,
                    'asset_id' => 'a8b16e4c-8834-4781-91dc-bd4e3d526e96',
                    'symbol' => 'AAPL',
                    'asset_class' => 'us_equity',
                    'notional' => null,
                    'qty' => 25,
                    'filled_qty' => 0,
                    'filled_avg_price' => null,
                    'order_class' => '',
                    'order_type' => 'limit',
                    'type' => 'limit',
                    'side' => 'buy',
                    'time_in_force' => 'day',
                    'limit_price' => '179.75',
                    'stop_price' => null,
                    'status' => 'new',
                    'extended_hours' => false,
                    'legs' => null,
                    'trail_percent' => null,
                    'trail_price' => null,
                    'hwm' => null
                )
            ),
            
            'place_order' => array(
                'endpoint' => '/v1/orders',
                'method' => 'POST',
                'description' => 'Submit a new order',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol identifier',
                        'example' => 'AAPL'
                    ),
                    'qty' => array(
                        'required' => true,
                        'type' => 'number',
                        'description' => 'Number of shares to trade',
                        'example' => 100
                    ),
                    'side' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order side',
                        'enum' => array('buy', 'sell'),
                        'example' => 'buy'
                    ),
                    'type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order type',
                        'enum' => array('market', 'limit', 'stop', 'stop_limit'),
                        'example' => 'market'
                    ),
                    'time_in_force' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time in force',
                        'enum' => array('day', 'gtc', 'ioc', 'fok', 'opg', 'cls'),
                        'example' => 'day'
                    ),
                    'limit_price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Limit price (required for limit and stop_limit orders)',
                        'example' => 179.50
                    ),
                    'stop_price' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Stop price (required for stop and stop_limit orders)',
                        'example' => 180.00
                    ),
                    'client_order_id' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Client-specified order ID',
                        'example' => 'eb9e2aaa-f71a-4f51-b5b4-52a6c565dad4'
                    ),
                    'extended_hours' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Whether the order should be allowed to execute during extended hours',
                        'default' => false,
                        'example' => true
                    ),
                    'order_class' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Order class',
                        'enum' => array('simple', 'bracket', 'oco', 'oto'),
                        'default' => 'simple',
                        'example' => 'simple'
                    ),
                    'take_profit' => array(
                        'required' => false,
                        'type' => 'object',
                        'description' => 'Take profit leg for bracket orders',
                        'example' => array(
                            'limit_price' => 195.00
                        )
                    ),
                    'stop_loss' => array(
                        'required' => false,
                        'type' => 'object',
                        'description' => 'Stop loss leg for bracket orders',
                        'example' => array(
                            'stop_price' => 170.00,
                            'limit_price' => 169.50
                        )
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'id' => '61e69015-8549-4bfd-b9c3-01e75843f47d',
                    'client_order_id' => 'eb9e2aaa-f71a-4f51-b5b4-52a6c565dad4',
                    'created_at' => '2025-04-09T14:15:22Z',
                    'updated_at' => '2025-04-09T14:15:22Z',
                    'submitted_at' => '2025-04-09T14:15:22Z',
                    'filled_at' => null,
                    'expired_at' => null,
                    'canceled_at' => null,
                    'failed_at' => null,
                    'replaced_at' => null,
                    'replaced_by' => null,
                    'replaces' => null,
                    'asset_id' => 'a8b16e4c-8834-4781-91dc-bd4e3d526e96',
                    'symbol' => 'AAPL',
                    'asset_class' => 'us_equity',
                    'notional' => null,
                    'qty' => 100,
                    'filled_qty' => 0,
                    'filled_avg_price' => null,
                    'order_class' => 'simple',
                    'order_type' => 'market',
                    'type' => 'market',
                    'side' => 'buy',
                    'time_in_force' => 'day',
                    'limit_price' => null,
                    'stop_price' => null,
                    'status' => 'new',
                    'extended_hours' => false,
                    'legs' => null,
                    'trail_percent' => null,
                    'trail_price' => null,
                    'hwm' => null
                )
            ),
            
            'cancel_order' => array(
                'endpoint' => '/v1/orders/{order_id}',
                'method' => 'DELETE',
                'description' => 'Cancel an open order',
                'parameters' => array(
                    'order_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Order ID',
                        'example' => '61e69015-8549-4bfd-b9c3-01e75843f47d'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'id' => '61e69015-8549-4bfd-b9c3-01e75843f47d',
                    'status' => 'pending_cancel'
                )
            ),
            
            'cancel_all_orders' => array(
                'endpoint' => '/v1/orders',
                'method' => 'DELETE',
                'description' => 'Cancel all open orders',
                'parameters' => array(
                    'symbols' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols to cancel orders for',
                        'example' => 'AAPL,MSFT'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'orders_canceled' => 3
                )
            ),
            
            // Market Data Endpoints
            'last_quote' => array(
                'endpoint' => '/v1/last/quotes',
                'method' => 'GET',
                'description' => 'Get last quotes for symbols',
                'parameters' => array(
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols',
                        'example' => 'AAPL,MSFT,GOOGL'
                    )
                ),
                'example_response' => array(
                    'quotes' => array(
                        'AAPL' => array(
                            'symbol' => 'AAPL',
                            'bid_price' => 179.95,
                            'bid_size' => 100,
                            'ask_price' => 180.05,
                            'ask_size' => 200,
                            'quote_timestamp' => 1712663400000
                        ),
                        'MSFT' => array(
                            'symbol' => 'MSFT',
                            'bid_price' => 381.25,
                            'bid_size' => 50,
                            'ask_price' => 381.45,
                            'ask_size' => 75,
                            'quote_timestamp' => 1712663390000
                        ),
                        'GOOGL' => array(
                            'symbol' => 'GOOGL',
                            'bid_price' => 159.75,
                            'bid_size' => 150,
                            'ask_price' => 159.85,
                            'ask_size' => 120,
                            'quote_timestamp' => 1712663395000
                        )
                    )
                )
            ),
            
            'last_trade' => array(
                'endpoint' => '/v1/last/trades',
                'method' => 'GET',
                'description' => 'Get last trades for symbols',
                'parameters' => array(
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols',
                        'example' => 'AAPL,MSFT,GOOGL'
                    )
                ),
                'example_response' => array(
                    'trades' => array(
                        'AAPL' => array(
                            'symbol' => 'AAPL',
                            'price' => 180.00,
                            'size' => 100,
                            'exchange' => 'Q',
                            'trade_timestamp' => 1712663380000
                        ),
                        'MSFT' => array(
                            'symbol' => 'MSFT',
                            'price' => 381.35,
                            'size' => 25,
                            'exchange' => 'Q',
                            'trade_timestamp' => 1712663370000
                        ),
                        'GOOGL' => array(
                            'symbol' => 'GOOGL',
                            'price' => 159.80,
                            'size' => 75,
                            'exchange' => 'Q',
                            'trade_timestamp' => 1712663375000
                        )
                    )
                )
            ),
            
            'bars' => array(
                'endpoint' => '/v1/bars/{timeframe}',
                'method' => 'GET',
                'description' => 'Get historical bar data for symbols',
                'parameters' => array(
                    'timeframe' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Bar timeframe',
                        'enum' => array('1min', '5min', '15min', '30min', '1hour', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'symbols' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols',
                        'example' => 'AAPL,MSFT'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of bars to return for each symbol',
                        'default' => 100,
                        'example' => 50
                    ),
                    'start' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter bars after this time (format: YYYY-MM-DD or YYYY-MM-DDTHH:MM:SS)',
                        'example' => '2025-03-01'
                    ),
                    'end' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter bars before this time (format: YYYY-MM-DD or YYYY-MM-DDTHH:MM:SS)',
                        'example' => '2025-04-01'
                    ),
                    'adjustment' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Price adjustment type',
                        'enum' => array('raw', 'split', 'dividend', 'all'),
                        'default' => 'raw',
                        'example' => 'all'
                    )
                ),
                'example_response' => array(
                    'bars' => array(
                        'AAPL' => array(
                            array(
                                't' => 1712577000000,
                                'o' => 178.08,
                                'h' => 179.43,
                                'l' => 177.85,
                                'c' => 178.85,
                                'v' => 45870100
                            ),
                            array(
                                't' => 1712490600000,
                                'o' => 177.92,
                                'h' => 179.25,
                                'l' => 177.45,
                                'c' => 178.10,
                                'v' => 43265400
                            )
                        ),
                        'MSFT' => array(
                            array(
                                't' => 1712577000000,
                                'o' => 379.25,
                                'h' => 382.10,
                                'l' => 378.90,
                                'c' => 381.35,
                                'v' => 23567800
                            ),
                            array(
                                't' => 1712490600000,
                                'o' => 378.80,
                                'h' => 380.75,
                                'l' => 377.55,
                                'c' => 379.05,
                                'v' => 21456900
                            )
                        )
                    ),
                    'next_page_token' => null
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
                    array(
                        'id' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890',
                        'name' => 'My Tech Stocks',
                        'created_at' => '2025-02-15T10:30:45Z',
                        'updated_at' => '2025-04-01T15:20:10Z',
                        'account_id' => 'ACC123456'
                    ),
                    array(
                        'id' => 'z9y8x7w6-v5u4-3210-z9y8-x7w6v5u43210',
                        'name' => 'Energy Sector',
                        'created_at' => '2025-03-10T09:15:30Z',
                        'updated_at' => '2025-03-30T11:45:22Z',
                        'account_id' => 'ACC123456'
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
                        'example' => 'Healthcare Stocks'
                    ),
                    'symbols' => array(
                        'required' => false,
                        'type' => 'array',
                        'description' => 'Array of symbols to add to the watchlist',
                        'example' => array('JNJ', 'PFE', 'UNH', 'MRK')
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'id' => 'b2c3d4e5-f6a1-7890-b2c3-d4e5f6a17890',
                    'name' => 'Healthcare Stocks',
                    'created_at' => '2025-04-09T15:30:45Z',
                    'updated_at' => '2025-04-09T15:30:45Z',
                    'account_id' => 'ACC123456',
                    'assets' => array(
                        array('symbol' => 'JNJ', 'name' => 'Johnson & Johnson'),
                        array('symbol' => 'PFE', 'name' => 'Pfizer Inc.'),
                        array('symbol' => 'UNH', 'name' => 'UnitedHealth Group Inc'),
                        array('symbol' => 'MRK', 'name' => 'Merck & Co., Inc.')
                    )
                )
            ),
            
            'get_watchlist' => array(
                'endpoint' => '/v1/watchlists/{watchlist_id}',
                'method' => 'GET',
                'description' => 'Get a specific watchlist by ID',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'id' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890',
                    'name' => 'My Tech Stocks',
                    'created_at' => '2025-02-15T10:30:45Z',
                    'updated_at' => '2025-04-01T15:20:10Z',
                    'account_id' => 'ACC123456',
                    'assets' => array(
                        array('symbol' => 'AAPL', 'name' => 'Apple Inc.'),
                        array('symbol' => 'MSFT', 'name' => 'Microsoft Corporation'),
                        array('symbol' => 'GOOGL', 'name' => 'Alphabet Inc Class A'),
                        array('symbol' => 'AMZN', 'name' => 'Amazon.com Inc.'),
                        array('symbol' => 'NVDA', 'name' => 'NVIDIA Corporation')
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
                        'example' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890'
                    ),
                    'name' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'New watchlist name',
                        'example' => 'Top Tech Picks'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'id' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890',
                    'name' => 'Top Tech Picks',
                    'created_at' => '2025-02-15T10:30:45Z',
                    'updated_at' => '2025-04-09T15:45:30Z',
                    'account_id' => 'ACC123456'
                )
            ),
            
            'add_watchlist_asset' => array(
                'endpoint' => '/v1/watchlists/{watchlist_id}',
                'method' => 'POST',
                'description' => 'Add an asset to a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol to add',
                        'example' => 'TSLA'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'id' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890',
                    'name' => 'Top Tech Picks',
                    'created_at' => '2025-02-15T10:30:45Z',
                    'updated_at' => '2025-04-09T15:55:10Z',
                    'account_id' => 'ACC123456',
                    'asset' => array(
                        'symbol' => 'TSLA',
                        'name' => 'Tesla, Inc.'
                    )
                )
            ),
            
            'delete_watchlist_asset' => array(
                'endpoint' => '/v1/watchlists/{watchlist_id}/{symbol}',
                'method' => 'DELETE',
                'description' => 'Remove an asset from a watchlist',
                'parameters' => array(
                    'watchlist_id' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Watchlist ID',
                        'example' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890'
                    ),
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol to remove',
                        'example' => 'GOOGL'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'status' => 'success',
                    'message' => 'Asset removed from watchlist'
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
                        'example' => 'a1b2c3d4-e5f6-7890-a1b2-c3d4e5f67890'
                    )
                ),
                'requires_auth' => true,
                'example_response' => array(
                    'status' => 'success',
                    'message' => 'Watchlist deleted'
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
            $base_url = 'https://api.tradingapi.com'; // Default base URL for Trading API
        }
        
        $url = $base_url . $endpoint['endpoint'];
        
        // Replace URL parameters (e.g., {watchlist_id}, {order_id}, {symbol})
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