<?php
/**
 * TradePress Twelve Data API Endpoints
 *
 * Defines endpoints and parameters for the Twelve Data market data service
 * API Documentation: https://twelvedata.com/docs
 * 
 * @package TradePress
 * @subpackage API\TwelveData
 * @version 1.0.0
 * @since 2025-04-09
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Twelve Data API Endpoints class
 */
class TradePress_TwelveData_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Core Market Data Endpoints
            'time_series' => array(
                'endpoint' => '/time_series',
                'method' => 'GET',
                'description' => 'Get time series data for a symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time interval between two consecutive data points',
                        'enum' => array('1min', '5min', '15min', '30min', '45min', '1h', '2h', '4h', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of data points to retrieve',
                        'default' => 30,
                        'example' => 100
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    ),
                    'dp' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of decimal places for floating values',
                        'default' => 5,
                        'example' => 2
                    ),
                    'timezone' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Timezone for datetime (IANA format)',
                        'default' => 'UTC',
                        'example' => 'America/New_York'
                    ),
                    'start_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date of the time series (format: YYYY-MM-DD HH:MM:SS)',
                        'example' => '2023-01-01'
                    ),
                    'end_date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date of the time series (format: YYYY-MM-DD HH:MM:SS)',
                        'example' => '2023-12-31'
                    ),
                    'previous_close' => array(
                        'required' => false,
                        'type' => 'boolean',
                        'description' => 'Include previous close data',
                        'default' => false,
                        'example' => 'true'
                    ),
                    'order' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Order of data points',
                        'enum' => array('ASC', 'DESC'),
                        'default' => 'ASC',
                        'example' => 'DESC'
                    )
                ),
                'example_response' => array(
                    'meta' => array(
                        'symbol' => 'AAPL',
                        'interval' => '1day',
                        'currency' => 'USD',
                        'exchange_timezone' => 'America/New_York',
                        'exchange' => 'NASDAQ',
                        'type' => 'Common Stock'
                    ),
                    'values' => array(
                        array(
                            'datetime' => '2025-04-08',
                            'open' => 178.08,
                            'high' => 179.43,
                            'low' => 177.85,
                            'close' => 178.85,
                            'volume' => 45870100
                        ),
                        array(
                            'datetime' => '2025-04-07',
                            'open' => 177.92,
                            'high' => 179.25,
                            'low' => 177.45,
                            'close' => 178.10,
                            'volume' => 43265400
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'quote' => array(
                'endpoint' => '/quote',
                'method' => 'GET',
                'description' => 'Get real-time stock quote for a symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Time interval',
                        'enum' => array('1min', '5min', '15min', '30min', '45min', '1h', '2h', '4h', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    ),
                    'dp' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of decimal places for floating values',
                        'default' => 5,
                        'example' => 2
                    ),
                    'timezone' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Timezone for datetime (IANA format)',
                        'default' => 'UTC',
                        'example' => 'America/New_York'
                    )
                ),
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'name' => 'Apple Inc',
                    'exchange' => 'NASDAQ',
                    'currency' => 'USD',
                    'datetime' => '2025-04-09',
                    'open' => 178.25,
                    'high' => 179.80,
                    'low' => 177.65,
                    'close' => 179.20,
                    'volume' => 48563200,
                    'previous_close' => 178.85,
                    'change' => 0.35,
                    'percent_change' => 0.20,
                    'fifty_two_week' => array(
                        'low' => 143.90,
                        'high' => 199.62
                    )
                )
            ),
            
            'price' => array(
                'endpoint' => '/price',
                'method' => 'GET',
                'description' => 'Get the latest price for a symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    ),
                    'dp' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of decimal places for floating values',
                        'default' => 5,
                        'example' => 2
                    )
                ),
                'example_response' => array(
                    'price' => 179.20
                )
            ),
            
            'symbol_search' => array(
                'endpoint' => '/symbol_search',
                'method' => 'GET',
                'description' => 'Search for symbols based on keywords',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Search query, can be a partial symbol or company name',
                        'example' => 'Apple'
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of results to return',
                        'default' => 30,
                        'example' => 10
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'symbol' => 'AAPL',
                            'instrument_name' => 'Apple Inc',
                            'exchange' => 'NASDAQ',
                            'country' => 'United States',
                            'currency' => 'USD',
                            'type' => 'Common Stock'
                        ),
                        array(
                            'symbol' => 'AAPL.XNAS',
                            'instrument_name' => 'Apple Inc',
                            'exchange' => 'NASDAQ',
                            'country' => 'United States',
                            'currency' => 'USD',
                            'type' => 'Common Stock'
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'market_movers' => array(
                'endpoint' => '/market_movers/{direction}',
                'method' => 'GET',
                'description' => 'Get market movers (gainers, losers, most active)',
                'parameters' => array(
                    'direction' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Direction of market movement',
                        'enum' => array('gainers', 'losers', 'most_active'),
                        'example' => 'gainers'
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of results to return',
                        'default' => 30,
                        'example' => 10
                    ),
                    'country' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by country (ISO alpha-2 code)',
                        'example' => 'US'
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'symbol' => 'XYZ',
                            'name' => 'XYZ Corp',
                            'exchange' => 'NYSE',
                            'change' => 5.25,
                            'change_percent' => 12.8,
                            'price' => 46.20,
                            'volume' => 9865432
                        ),
                        array(
                            'symbol' => 'ABC',
                            'name' => 'ABC Industries',
                            'exchange' => 'NASDAQ',
                            'change' => 3.75,
                            'change_percent' => 8.6,
                            'price' => 47.35,
                            'volume' => 7564321
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            // Technical Indicators
            'macd' => array(
                'endpoint' => '/macd',
                'method' => 'GET',
                'description' => 'Moving Average Convergence/Divergence (MACD) indicator',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time interval between two consecutive data points',
                        'enum' => array('1min', '5min', '15min', '30min', '45min', '1h', '2h', '4h', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'fast_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Period for the fast moving average',
                        'default' => 12,
                        'example' => 12
                    ),
                    'slow_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Period for the slow moving average',
                        'default' => 26,
                        'example' => 26
                    ),
                    'signal_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Period for the signal line',
                        'default' => 9,
                        'example' => 9
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of data points to retrieve',
                        'default' => 30,
                        'example' => 100
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'meta' => array(
                        'symbol' => 'AAPL',
                        'interval' => '1day',
                        'currency' => 'USD',
                        'exchange_timezone' => 'America/New_York',
                        'exchange' => 'NASDAQ',
                        'type' => 'Common Stock',
                        'indicator' => array(
                            'name' => 'MACD',
                            'fast_period' => 12,
                            'slow_period' => 26,
                            'signal_period' => 9
                        )
                    ),
                    'values' => array(
                        array(
                            'datetime' => '2025-04-09',
                            'macd' => 0.9834,
                            'macd_signal' => 0.7245,
                            'macd_hist' => 0.2589
                        ),
                        array(
                            'datetime' => '2025-04-08',
                            'macd' => 0.8756,
                            'macd_signal' => 0.6547,
                            'macd_hist' => 0.2209
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'rsi' => array(
                'endpoint' => '/rsi',
                'method' => 'GET',
                'description' => 'Relative Strength Index (RSI) indicator',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time interval between two consecutive data points',
                        'enum' => array('1min', '5min', '15min', '30min', '45min', '1h', '2h', '4h', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'time_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of periods',
                        'default' => 14,
                        'example' => 14
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of data points to retrieve',
                        'default' => 30,
                        'example' => 100
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'meta' => array(
                        'symbol' => 'AAPL',
                        'interval' => '1day',
                        'currency' => 'USD',
                        'exchange_timezone' => 'America/New_York',
                        'exchange' => 'NASDAQ',
                        'type' => 'Common Stock',
                        'indicator' => array(
                            'name' => 'RSI',
                            'time_period' => 14
                        )
                    ),
                    'values' => array(
                        array(
                            'datetime' => '2025-04-09',
                            'rsi' => 58.42
                        ),
                        array(
                            'datetime' => '2025-04-08',
                            'rsi' => 56.78
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'sma' => array(
                'endpoint' => '/sma',
                'method' => 'GET',
                'description' => 'Simple Moving Average (SMA) indicator',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time interval between two consecutive data points',
                        'enum' => array('1min', '5min', '15min', '30min', '45min', '1h', '2h', '4h', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'time_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of periods',
                        'default' => 20,
                        'example' => 20
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of data points to retrieve',
                        'default' => 30,
                        'example' => 100
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'meta' => array(
                        'symbol' => 'AAPL',
                        'interval' => '1day',
                        'currency' => 'USD',
                        'exchange_timezone' => 'America/New_York',
                        'exchange' => 'NASDAQ',
                        'type' => 'Common Stock',
                        'indicator' => array(
                            'name' => 'SMA',
                            'time_period' => 20
                        )
                    ),
                    'values' => array(
                        array(
                            'datetime' => '2025-04-09',
                            'sma' => 176.85
                        ),
                        array(
                            'datetime' => '2025-04-08',
                            'sma' => 175.92
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'ema' => array(
                'endpoint' => '/ema',
                'method' => 'GET',
                'description' => 'Exponential Moving Average (EMA) indicator',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time interval between two consecutive data points',
                        'enum' => array('1min', '5min', '15min', '30min', '45min', '1h', '2h', '4h', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'time_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of periods',
                        'default' => 20,
                        'example' => 20
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of data points to retrieve',
                        'default' => 30,
                        'example' => 100
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'meta' => array(
                        'symbol' => 'AAPL',
                        'interval' => '1day',
                        'currency' => 'USD',
                        'exchange_timezone' => 'America/New_York',
                        'exchange' => 'NASDAQ',
                        'type' => 'Common Stock',
                        'indicator' => array(
                            'name' => 'EMA',
                            'time_period' => 20
                        )
                    ),
                    'values' => array(
                        array(
                            'datetime' => '2025-04-09',
                            'ema' => 177.35
                        ),
                        array(
                            'datetime' => '2025-04-08',
                            'ema' => 176.84
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'stoch' => array(
                'endpoint' => '/stoch',
                'method' => 'GET',
                'description' => 'Stochastic Oscillator (STOCH) indicator',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time interval between two consecutive data points',
                        'enum' => array('1min', '5min', '15min', '30min', '45min', '1h', '2h', '4h', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'fast_k_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Period for Fast %K',
                        'default' => 14,
                        'example' => 14
                    ),
                    'slow_k_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Period for Slow %K',
                        'default' => 3,
                        'example' => 3
                    ),
                    'slow_d_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Period for Slow %D',
                        'default' => 3,
                        'example' => 3
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of data points to retrieve',
                        'default' => 30,
                        'example' => 100
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'meta' => array(
                        'symbol' => 'AAPL',
                        'interval' => '1day',
                        'currency' => 'USD',
                        'exchange_timezone' => 'America/New_York',
                        'exchange' => 'NASDAQ',
                        'type' => 'Common Stock',
                        'indicator' => array(
                            'name' => 'STOCH',
                            'fast_k_period' => 14,
                            'slow_k_period' => 3,
                            'slow_d_period' => 3
                        )
                    ),
                    'values' => array(
                        array(
                            'datetime' => '2025-04-09',
                            'slow_k' => 68.42,
                            'slow_d' => 65.78
                        ),
                        array(
                            'datetime' => '2025-04-08',
                            'slow_k' => 64.65,
                            'slow_d' => 61.53
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'bbands' => array(
                'endpoint' => '/bbands',
                'method' => 'GET',
                'description' => 'Bollinger Bands (BBANDS) indicator',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol ticker of the instrument',
                        'example' => 'AAPL'
                    ),
                    'interval' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time interval between two consecutive data points',
                        'enum' => array('1min', '5min', '15min', '30min', '45min', '1h', '2h', '4h', '1day', '1week', '1month'),
                        'example' => '1day'
                    ),
                    'time_period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of periods',
                        'default' => 20,
                        'example' => 20
                    ),
                    'sd' => array(
                        'required' => false,
                        'type' => 'number',
                        'description' => 'Standard deviation multiplier',
                        'default' => 2,
                        'example' => 2
                    ),
                    'outputsize' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Number of data points to retrieve',
                        'default' => 30,
                        'example' => 100
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'meta' => array(
                        'symbol' => 'AAPL',
                        'interval' => '1day',
                        'currency' => 'USD',
                        'exchange_timezone' => 'America/New_York',
                        'exchange' => 'NASDAQ',
                        'type' => 'Common Stock',
                        'indicator' => array(
                            'name' => 'BBANDS',
                            'time_period' => 20,
                            'sd' => 2
                        )
                    ),
                    'values' => array(
                        array(
                            'datetime' => '2025-04-09',
                            'upper_band' => 188.35,
                            'middle_band' => 176.85,
                            'lower_band' => 165.35
                        ),
                        array(
                            'datetime' => '2025-04-08',
                            'upper_band' => 187.92,
                            'middle_band' => 175.92,
                            'lower_band' => 163.92
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            // Reference Data Endpoints
            'stocks_list' => array(
                'endpoint' => '/stocks',
                'method' => 'GET',
                'description' => 'Get a list of supported stocks',
                'parameters' => array(
                    'symbol' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by symbol prefix',
                        'example' => 'AA'
                    ),
                    'exchange' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by exchange',
                        'example' => 'NASDAQ'
                    ),
                    'country' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by country code',
                        'example' => 'US'
                    ),
                    'type' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by instrument type',
                        'enum' => array('Common Stock', 'ETF', 'FUND', 'Preferred Stock', 'ADR'),
                        'example' => 'Common Stock'
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'symbol' => 'AAPL',
                            'name' => 'Apple Inc',
                            'currency' => 'USD',
                            'exchange' => 'NASDAQ',
                            'country' => 'United States',
                            'type' => 'Common Stock'
                        ),
                        array(
                            'symbol' => 'MSFT',
                            'name' => 'Microsoft Corporation',
                            'currency' => 'USD',
                            'exchange' => 'NASDAQ',
                            'country' => 'United States',
                            'type' => 'Common Stock'
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'forex_pairs' => array(
                'endpoint' => '/forex_pairs',
                'method' => 'GET',
                'description' => 'Get a list of supported forex pairs',
                'parameters' => array(
                    'symbol' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by symbol prefix',
                        'example' => 'USD'
                    ),
                    'currency_base' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by base currency',
                        'example' => 'USD'
                    ),
                    'currency_quote' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by quote currency',
                        'example' => 'EUR'
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'symbol' => 'EUR/USD',
                            'currency_group' => 'Major',
                            'currency_base' => 'EUR',
                            'currency_quote' => 'USD'
                        ),
                        array(
                            'symbol' => 'USD/JPY',
                            'currency_group' => 'Major',
                            'currency_base' => 'USD',
                            'currency_quote' => 'JPY'
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'cryptocurrency_pairs' => array(
                'endpoint' => '/cryptocurrency_pairs',
                'method' => 'GET',
                'description' => 'Get a list of supported cryptocurrency pairs',
                'parameters' => array(
                    'symbol' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by symbol prefix',
                        'example' => 'BTC'
                    ),
                    'currency_base' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by base currency',
                        'example' => 'BTC'
                    ),
                    'currency_quote' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by quote currency',
                        'example' => 'USD'
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'symbol' => 'BTC/USD',
                            'currency_base' => 'BTC',
                            'currency_quote' => 'USD',
                            'available_exchanges' => array('Coinbase', 'Binance', 'Kraken')
                        ),
                        array(
                            'symbol' => 'ETH/USD',
                            'currency_base' => 'ETH',
                            'currency_quote' => 'USD',
                            'available_exchanges' => array('Coinbase', 'Binance', 'Kraken')
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'etf_list' => array(
                'endpoint' => '/etf',
                'method' => 'GET',
                'description' => 'Get a list of supported ETFs',
                'parameters' => array(
                    'symbol' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by symbol prefix',
                        'example' => 'SP'
                    ),
                    'exchange' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by exchange',
                        'example' => 'NYSE'
                    ),
                    'country' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by country code',
                        'example' => 'US'
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'symbol' => 'SPY',
                            'name' => 'SPDR S&P 500 ETF Trust',
                            'currency' => 'USD',
                            'exchange' => 'NYSE ARCA',
                            'country' => 'United States',
                            'type' => 'ETF'
                        ),
                        array(
                            'symbol' => 'QQQ',
                            'name' => 'Invesco QQQ Trust, Series 1',
                            'currency' => 'USD',
                            'exchange' => 'NASDAQ',
                            'country' => 'United States',
                            'type' => 'ETF'
                        )
                    ),
                    'status' => 'ok'
                )
            ),
            
            'exchanges' => array(
                'endpoint' => '/exchanges',
                'method' => 'GET',
                'description' => 'Get a list of supported exchanges',
                'parameters' => array(
                    'type' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter by exchange type',
                        'enum' => array('stock', 'forex', 'crypto'),
                        'example' => 'stock'
                    ),
                    'format' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Format of the data',
                        'enum' => array('JSON', 'CSV'),
                        'default' => 'JSON',
                        'example' => 'JSON'
                    )
                ),
                'example_response' => array(
                    'data' => array(
                        array(
                            'name' => 'NASDAQ',
                            'code' => 'NASDAQ',
                            'country' => 'United States',
                            'timezone' => 'America/New_York'
                        ),
                        array(
                            'name' => 'New York Stock Exchange',
                            'code' => 'NYSE',
                            'country' => 'United States',
                            'timezone' => 'America/New_York'
                        )
                    ),
                    'status' => 'ok'
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
            $base_url = 'https://api.twelvedata.com'; // Default base URL for Twelve Data API
        }
        
        $url = $base_url . $endpoint['endpoint'];
        
        // Replace URL parameters (e.g., {direction} in /market_movers/{direction})
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