<?php
/**
 * TradePress EOD Historical Data API Endpoints
 *
 * Defines endpoints and parameters for the EOD Historical Data service
 * API Documentation: https://eodhistoricaldata.com/financial-apis/
 * 
 * @package TradePress
 * @subpackage API\EODHD
 * @version 1.0.0
 * @since 2025-04-09
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress EOD Historical Data API Endpoints class
 */
class TradePress_EODHD_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Historical End-of-Day Data
            'eod_historical' => array(
                'endpoint' => '/api/eod/{symbol}',
                'method' => 'GET',
                'description' => 'Get historical end-of-day data for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format',
                        'example' => '2023-01-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format',
                        'example' => '2023-12-31'
                    ),
                    'period' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Data period',
                        'enum' => array('d', 'w', 'm'),
                        'default' => 'd',
                        'example' => 'd'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    ),
                    'filter' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter specific fields (e.g., "last_close")',
                        'example' => 'last_close'
                    )
                ),
                'rate_limit' => '100,000 API calls per day (1 call per symbol request)',
                'example_response' => array(
                    array(
                        'date' => '2023-12-29',
                        'open' => 197.53,
                        'high' => 198.36,
                        'low' => 196.02,
                        'close' => 197.57,
                        'adjusted_close' => 197.57,
                        'volume' => 40847828
                    ),
                    array(
                        'date' => '2023-12-28',
                        'open' => 194.14,
                        'high' => 197.68,
                        'low' => 193.71,
                        'close' => 197.57,
                        'adjusted_close' => 197.57,
                        'volume' => 46482374
                    )
                )
            ),
            
            // Bulk End-of-Day Data
            'eod_bulk' => array(
                'endpoint' => '/api/eod-bulk-last-day/{exchange}',
                'method' => 'GET',
                'description' => 'Get the latest end-of-day data for all symbols in a specific exchange',
                'parameters' => array(
                    'exchange' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Exchange code (e.g., US for US exchanges)',
                        'example' => 'US'
                    ),
                    'date' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Specific date in YYYY-MM-DD format',
                        'example' => '2023-12-31'
                    ),
                    'symbols' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Comma-separated list of symbols',
                        'example' => 'AAPL.US,MSFT.US,GOOG.US'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => 'Each request counts as 1 API call',
                'example_response' => array(
                    'AAPL.US' => array(
                        'date' => '2023-12-29',
                        'open' => 197.53,
                        'high' => 198.36,
                        'low' => 196.02,
                        'close' => 197.57,
                        'adjusted_close' => 197.57,
                        'volume' => 40847828
                    ),
                    'MSFT.US' => array(
                        'date' => '2023-12-29',
                        'open' => 375.68,
                        'high' => 376.96,
                        'low' => 372.09,
                        'close' => 376.04,
                        'adjusted_close' => 376.04,
                        'volume' => 15234560
                    )
                )
            ),
            
            // Real-time/Live Data
            'real_time' => array(
                'endpoint' => '/api/real-time/{symbol}',
                'method' => 'GET',
                'description' => 'Get real-time (or delayed) data for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    ),
                    's' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Alternative way to specify multiple symbols, comma-separated',
                        'example' => 'AAPL.US,MSFT.US'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => 'Each symbol counts as 1 API call',
                'example_response' => array(
                    'code' => 'AAPL.US',
                    'timestamp' => 1672333200,
                    'gmtoffset' => 0,
                    'open' => 197.53,
                    'high' => 198.36,
                    'low' => 196.02,
                    'close' => 197.57,
                    'volume' => 40847828,
                    'previousClose' => 194.86,
                    'change' => 2.71,
                    'change_p' => 1.39
                )
            ),
            
            // Fundamental Data
            'fundamentals' => array(
                'endpoint' => '/api/fundamentals/{symbol}',
                'method' => 'GET',
                'description' => 'Get fundamental data for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    ),
                    'filter' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Filter for specific data sections (e.g., "General::Code,General,Earnings")',
                        'example' => 'General::Code,General::Name'
                    ),
                    'historical' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Set to "1" to include historical data',
                        'example' => '1'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date for historical data in YYYY-MM-DD format (requires historical=1)',
                        'example' => '2023-01-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date for historical data in YYYY-MM-DD format (requires historical=1)',
                        'example' => '2023-12-31'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => 'Each request costs 10 API calls',
                'example_response' => array(
                    'General' => array(
                        'Code' => 'AAPL.US',
                        'Type' => 'Common Stock',
                        'Name' => 'Apple Inc',
                        'Exchange' => 'NASDAQ',
                        'CurrencyCode' => 'USD',
                        'CurrencyName' => 'US Dollar',
                        'CurrencySymbol' => '$',
                        'CountryName' => 'USA',
                        'CountryISO' => 'US',
                        'ISIN' => 'US0378331005',
                        'CUSIP' => '037833100',
                        'Sector' => 'Technology',
                        'Industry' => 'Consumer Electronics',
                        'Description' => 'Apple Inc. designs, manufactures, and markets smartphones, personal computers, tablets, wearables, and accessories worldwide...',
                        'FullTimeEmployees' => 161000,
                        'UpdatedAt' => '2023-12-29'
                    ),
                    'Highlights' => array(
                        'MarketCapitalization' => 3100000000000,
                        'EBITDA' => 131554000000,
                        'PERatio' => 30.16,
                        'PEGRatio' => 2.75,
                        'WallStreetTargetPrice' => 195.37,
                        'BookValue' => 3.79,
                        'DividendShare' => 0.96,
                        'DividendYield' => 0.0049,
                        'EPS' => 6.56,
                        'RevenuePerShareTTM' => 24.46,
                        'ProfitMargin' => 0.268,
                        'OperatingMarginTTM' => 0.3064,
                        'ReturnOnAssetsTTM' => 0.2049,
                        'ReturnOnEquityTTM' => 1.6547,
                        'RevenueTTM' => 394328000000,
                        'GrossProfitTTM' => 176965000000,
                        'DilutedEpsTTM' => 6.56,
                        'QuarterlyEarningsGrowthYOY' => 0.011,
                        'QuarterlyRevenueGrowthYOY' => 0.007,
                        'AnalystTargetPrice' => 195.37,
                        '52WeekHigh' => 199.62,
                        '52WeekLow' => 124.17,
                        '50DayMovingAverage' => 184.36,
                        '200DayMovingAverage' => 177.56,
                        'SharesOutstanding' => 15634199808,
                        'SharesFloat' => 15628570000,
                        'SharesShort' => 113646000,
                        'SharesShortPriorMonth' => 103655000,
                        'ShortRatio' => 1.92,
                        'ShortPercent' => 0.0073,
                        'Beta' => 1.3043
                    )
                )
            ),
            
            // Technical Indicators
            'technical' => array(
                'endpoint' => '/api/technical/{symbol}',
                'method' => 'GET',
                'description' => 'Get technical indicators for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    ),
                    'function' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Technical indicator function',
                        'enum' => array('sma', 'ema', 'wma', 'rsi', 'splitadjusted', 'macd', 'stochastic', 'stochrsi', 'bbands', 'ad', 'adosc', 'atr', 'cci', 'mfi', 'obv', 'slope', 'dmi'),
                        'example' => 'sma'
                    ),
                    'period' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Period for the technical indicator (depends on function)',
                        'default' => 50,
                        'example' => 20
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format',
                        'example' => '2023-01-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format',
                        'example' => '2023-12-31'
                    ),
                    'order' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Order of results',
                        'enum' => array('a', 'd'),
                        'default' => 'a',
                        'example' => 'a'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => 'Each request counts as 1 API call',
                'example_response' => array(
                    array(
                        'date' => '2023-12-29',
                        'sma' => 192.3545
                    ),
                    array(
                        'date' => '2023-12-28',
                        'sma' => 191.8945
                    )
                )
            ),
            
            // Intraday Historical Data
            'intraday' => array(
                'endpoint' => '/api/intraday/{symbol}',
                'method' => 'GET',
                'description' => 'Get intraday historical data for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    ),
                    'interval' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Time interval',
                        'enum' => array('1m', '5m', '15m', '30m', '1h', '4h'),
                        'example' => '5m'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format or unix timestamp',
                        'example' => '2023-12-28'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format or unix timestamp',
                        'example' => '2023-12-29'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => 'Premium feature - counts as multiple API calls depending on data size',
                'example_response' => array(
                    array(
                        'timestamp' => 1672297200,
                        'datetime' => '2023-12-29 09:30:00',
                        'open' => 197.53,
                        'high' => 197.78,
                        'low' => 196.95,
                        'close' => 197.15,
                        'volume' => 1254863
                    ),
                    array(
                        'timestamp' => 1672297500,
                        'datetime' => '2023-12-29 09:35:00',
                        'open' => 197.16,
                        'high' => 197.32,
                        'low' => 196.88,
                        'close' => 197.05,
                        'volume' => 985241
                    )
                )
            ),
            
            // Exchanges
            'exchanges' => array(
                'endpoint' => '/api/exchanges-list',
                'method' => 'GET',
                'description' => 'Get a list of supported exchanges',
                'parameters' => array(
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => '1 API call',
                'example_response' => array(
                    'US' => array(
                        'name' => 'US Stocks',
                        'code' => 'US',
                        'operating_mic' => 'XNAS,XNYS,ARCX,XASE,BATS',
                        'country' => 'USA',
                        'currency_symbol' => '$',
                        'currency_code' => 'USD'
                    ),
                    'LSE' => array(
                        'name' => 'London Stock Exchange',
                        'code' => 'LSE',
                        'operating_mic' => 'XLON',
                        'country' => 'United Kingdom',
                        'currency_symbol' => '£',
                        'currency_code' => 'GBP'
                    )
                )
            ),
            
            // Exchange Symbols
            'exchange_symbols' => array(
                'endpoint' => '/api/exchange-symbol-list/{exchange}',
                'method' => 'GET',
                'description' => 'Get a list of symbols for a specific exchange',
                'parameters' => array(
                    'exchange' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Exchange code (e.g., US)',
                        'example' => 'US'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => '1 API call',
                'example_response' => array(
                    array(
                        'Code' => 'AAPL.US',
                        'Name' => 'Apple Inc',
                        'Country' => 'USA',
                        'Exchange' => 'NASDAQ',
                        'Currency' => 'USD',
                        'Type' => 'Common Stock',
                        'ISIN' => 'US0378331005'
                    ),
                    array(
                        'Code' => 'MSFT.US',
                        'Name' => 'Microsoft Corporation',
                        'Country' => 'USA',
                        'Exchange' => 'NASDAQ',
                        'Currency' => 'USD',
                        'Type' => 'Common Stock',
                        'ISIN' => 'US5949181045'
                    )
                )
            ),
            
            // News & Sentiment
            'news' => array(
                'endpoint' => '/api/news',
                'method' => 'GET',
                'description' => 'Get financial news and sentiment data',
                'parameters' => array(
                    's' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Symbol(s) to get news for, comma-separated',
                        'example' => 'AAPL.US'
                    ),
                    't' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'News tags (categories), comma-separated',
                        'example' => 'earnings,technology'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format',
                        'example' => '2023-12-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format',
                        'example' => '2023-12-31'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Maximum number of news items to return',
                        'default' => 100,
                        'max' => 1000,
                        'example' => 50
                    ),
                    'offset' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Offset for pagination',
                        'default' => 0,
                        'example' => 0
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => '5-10 API calls per request depending on data size',
                'example_response' => array(
                    array(
                        'date' => '2023-12-29T16:45:00+0000',
                        'title' => 'Apple Stock Year in Review: The Highs and Lows of 2023',
                        'content' => 'Apple (AAPL) had a remarkable year with several new product launches...',
                        'link' => 'https://example.com/apple-2023-review',
                        'symbols' => array('AAPL.US'),
                        'tags' => array('technology', 'stocks'),
                        'sentiment' => 'Positive',
                        'sentiment_score' => 0.78
                    ),
                    array(
                        'date' => '2023-12-28T14:30:00+0000',
                        'title' => 'Apple Vision Pro Launch Delayed to Early 2024',
                        'content' => 'Apple has announced that its much-anticipated Vision Pro headset...',
                        'link' => 'https://example.com/vision-pro-delay',
                        'symbols' => array('AAPL.US'),
                        'tags' => array('technology', 'product-launch'),
                        'sentiment' => 'Neutral',
                        'sentiment_score' => 0.15
                    )
                )
            ),
            
            // Options Data
            'options' => array(
                'endpoint' => '/api/options/{symbol}',
                'method' => 'GET',
                'description' => 'Get options data for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format',
                        'example' => '2023-12-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format',
                        'example' => '2024-03-31'
                    ),
                    'trade_date_to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Last trading date in YYYY-MM-DD format',
                        'example' => '2023-12-29'
                    ),
                    'contract_name' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Contract name for specific option',
                        'example' => 'AAPL240621C00200000'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => 'Premium feature - counts as multiple API calls depending on data size',
                'example_response' => array(
                    'code' => 'AAPL.US',
                    'exchange' => 'OPRA',
                    'lastTradeDate' => '2023-12-29',
                    'lastPrice' => 197.57,
                    'data' => array(
                        '2024-01-19' => array(
                            'expirationDate' => '2024-01-19',
                            'calls' => array(
                                array(
                                    'contractName' => 'AAPL240119C00190000',
                                    'contractSize' => 100,
                                    'currency' => 'USD',
                                    'type' => 'call',
                                    'inTheMoney' => true,
                                    'lastTradeDateTime' => '2023-12-29T19:59:59+0000',
                                    'strike' => 190,
                                    'lastPrice' => 9.55,
                                    'bid' => 9.45,
                                    'ask' => 9.65,
                                    'change' => 0.30,
                                    'changePercent' => 3.24,
                                    'volume' => 2556,
                                    'openInterest' => 12578,
                                    'impliedVolatility' => 25.37
                                )
                            ),
                            'puts' => array(
                                array(
                                    'contractName' => 'AAPL240119P00190000',
                                    'contractSize' => 100,
                                    'currency' => 'USD',
                                    'type' => 'put',
                                    'inTheMoney' => false,
                                    'lastTradeDateTime' => '2023-12-29T19:59:59+0000',
                                    'strike' => 190,
                                    'lastPrice' => 1.25,
                                    'bid' => 1.20,
                                    'ask' => 1.30,
                                    'change' => -0.15,
                                    'changePercent' => -10.71,
                                    'volume' => 1834,
                                    'openInterest' => 9356,
                                    'impliedVolatility' => 24.12
                                )
                            )
                        )
                    )
                )
            ),
            
            // Calendar Data
            'calendar' => array(
                'endpoint' => '/api/calendar/{type}',
                'method' => 'GET',
                'description' => 'Get calendar data for earnings, IPOs, splits, etc.',
                'parameters' => array(
                    'type' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Calendar data type',
                        'enum' => array('earnings', 'ipos', 'splits', 'dividends', 'economic-events'),
                        'example' => 'earnings'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format',
                        'example' => '2023-12-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format',
                        'example' => '2023-12-31'
                    ),
                    'symbols' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Symbols to filter by (comma-separated)',
                        'example' => 'AAPL.US,MSFT.US'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => '1-5 API calls depending on the type and date range',
                'example_response' => array(
                    'earnings' => array(
                        array(
                            'code' => 'AAPL.US',
                            'exchange' => 'NASDAQ',
                            'name' => 'Apple Inc',
                            'reportDate' => '2024-01-31',
                            'reportTime' => 'amc',
                            'estimate' => 2.12,
                            'currency' => 'USD',
                            'fiscalQuarter' => 'Q1 2024',
                            'fiscalYear' => '2024'
                        )
                    )
                )
            ),
            
            // Splits and Dividends
            'splits' => array(
                'endpoint' => '/api/splits/{symbol}',
                'method' => 'GET',
                'description' => 'Get historical splits data for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format',
                        'example' => '2000-01-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format',
                        'example' => '2023-12-31'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => '1 API call per request',
                'example_response' => array(
                    array(
                        'date' => '2020-08-31',
                        'split' => '4:1',
                        'symbol' => 'AAPL.US'
                    ),
                    array(
                        'date' => '2014-06-09',
                        'split' => '7:1',
                        'symbol' => 'AAPL.US'
                    )
                )
            ),
            
            'dividends' => array(
                'endpoint' => '/api/div/{symbol}',
                'method' => 'GET',
                'description' => 'Get historical dividends data for a specific symbol',
                'parameters' => array(
                    'symbol' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Symbol with exchange suffix (e.g., AAPL.US)',
                        'example' => 'AAPL.US'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date in YYYY-MM-DD format',
                        'example' => '2020-01-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date in YYYY-MM-DD format',
                        'example' => '2023-12-31'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => '1 API call per request',
                'example_response' => array(
                    array(
                        'date' => '2023-11-10',
                        'declarationDate' => '2023-11-02',
                        'recordDate' => '2023-11-13',
                        'paymentDate' => '2023-11-16',
                        'value' => 0.24,
                        'unadjustedValue' => 0.24,
                        'currency' => 'USD'
                    ),
                    array(
                        'date' => '2023-08-11',
                        'declarationDate' => '2023-08-03',
                        'recordDate' => '2023-08-14',
                        'paymentDate' => '2023-08-17',
                        'value' => 0.24,
                        'unadjustedValue' => 0.24,
                        'currency' => 'USD'
                    )
                )
            ),
            
            // Market Screener
            'screener' => array(
                'endpoint' => '/api/screener',
                'method' => 'GET',
                'description' => 'Filter stocks based on various fundamental and technical criteria',
                'parameters' => array(
                    'signals' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Technical signals to filter by',
                        'enum' => array('top_gainers', 'top_losers', 'new_high', 'new_low', 'most_volatile', 'most_active', 'unusual_volume', 'overbought', 'oversold', 'downgrades', 'upgrades', 'earnings_before', 'earnings_after', 'recent_insider_buying', 'recent_insider_selling'),
                        'example' => 'top_gainers'
                    ),
                    'filters' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'JSON-encoded filtering criteria',
                        'example' => '{"market_cap_min":1000000000,"price_min":50}'
                    ),
                    'sort' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Sort field and direction',
                        'example' => 'market_cap_basic.desc'
                    ),
                    'limit' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Maximum number of results',
                        'default' => 50,
                        'example' => 100
                    ),
                    'offset' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'Offset for pagination',
                        'default' => 0,
                        'example' => 0
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => '5 API calls per request',
                'example_response' => array(
                    'data' => array(
                        array(
                            'code' => 'AAPL.US',
                            'name' => 'Apple Inc',
                            'exchange' => 'NASDAQ',
                            'currency' => 'USD',
                            'price' => 197.57,
                            'change' => 2.71,
                            'change_p' => 1.39,
                            'market_cap' => 3100000000000,
                            'volume' => 40847828,
                            'avg_volume' => 54321000,
                            'pe' => 30.16,
                            'eps' => 6.56,
                            'sector' => 'Technology',
                            'industry' => 'Consumer Electronics'
                        ),
                        array(
                            'code' => 'MSFT.US',
                            'name' => 'Microsoft Corporation',
                            'exchange' => 'NASDAQ',
                            'currency' => 'USD',
                            'price' => 376.04,
                            'change' => 3.95,
                            'change_p' => 1.06,
                            'market_cap' => 2800000000000,
                            'volume' => 15234560,
                            'avg_volume' => 18765000,
                            'pe' => 36.51,
                            'eps' => 10.30,
                            'sector' => 'Technology',
                            'industry' => 'Software - Infrastructure'
                        )
                    ),
                    'total' => 125
                )
            ),
            
            // Stock Market Holidays
            'exchange_details' => array(
                'endpoint' => '/api/exchange-details/{exchange}',
                'method' => 'GET',
                'description' => 'Get exchange details including trading hours and holidays',
                'parameters' => array(
                    'exchange' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'Exchange code (e.g., US)',
                        'example' => 'US'
                    ),
                    'from' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Start date for holidays in YYYY-MM-DD format',
                        'example' => '2023-01-01'
                    ),
                    'to' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'End date for holidays in YYYY-MM-DD format',
                        'example' => '2023-12-31'
                    ),
                    'fmt' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'Output format',
                        'enum' => array('json', 'csv'),
                        'default' => 'json',
                        'example' => 'json'
                    )
                ),
                'rate_limit' => '1 API call per request',
                'example_response' => array(
                    'name' => 'US Stock Exchanges',
                    'code' => 'US',
                    'operating_mic' => 'XNAS,XNYS,ARCX,XASE,BATS',
                    'country' => 'USA',
                    'currency_symbol' => '$',
                    'currency_code' => 'USD',
                    'trading_hours' => array(
                        'open' => '09:30:00',
                        'close' => '16:00:00',
                        'timezone' => 'America/New_York'
                    ),
                    'holidays' => array(
                        array(
                            'year' => 2023,
                            'month' => 1,
                            'day' => 2,
                            'name' => "New Year's Day (observed)"
                        ),
                        array(
                            'year' => 2023,
                            'month' => 1,
                            'day' => 16,
                            'name' => 'Martin Luther King Jr. Day'
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
            $base_url = 'https://eodhd.com';
        }
        
        $url = $base_url . $endpoint['endpoint'];
        
        // Replace URL parameters (e.g., {symbol}, {exchange})
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
            
            // Always include API token
            if (!isset($query_params['api_token'])) {
                $query_params['api_token'] = '{YOUR_API_TOKEN}'; // Placeholder - replace with actual token in usage
            }
            
            if (!empty($query_params)) {
                $url .= '?' . http_build_query($query_params);
            }
        }
        
        return $url;
    }
}
