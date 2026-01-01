<?php
/**
 * TradePress Finnhub API Endpoints
 *
 * Defines endpoints and parameters for the Finnhub financial data service
 * API Documentation: https://finnhub.io/docs/api
 *
 * @package TradePress
 * @subpackage API\Finnhub
 * @version 1.1.0
 * @since 2025-04-11
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Finnhub API Endpoints class
 */
class TradePress_Finnhub_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Stocks / Equities
            'QUOTE' => array(
                'path' => 'quote',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Real-time quote data',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'c' => 261.74, // Current price
                    'h' => 263.31, // High price of the day
                    'l' => 260.68, // Low price of the day
                    'o' => 261.07, // Open price of the day
                    'pc' => 259.45, // Previous close price
                    't' => 1582641000, // Timestamp
                ),
            ),
            'COMPANY_PROFILE' => array(
                'path' => 'stock/profile2',
                'required_params' => array('symbol'),
                'optional_params' => array('isin', 'cusip'),
                'description' => 'Company information including name, logo, industry, etc.',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'country' => 'US',
                    'currency' => 'USD',
                    'exchange' => 'NASDAQ/NMS (GLOBAL MARKET)',
                    'ipo' => '1980-12-12',
                    'marketCapitalization' => 1415993,
                    'name' => 'Apple Inc',
                    'phone' => '14089961010',
                    'shareOutstanding' => 4375.47998046875,
                    'ticker' => 'AAPL',
                    'weburl' => 'https://www.apple.com/',
                    'logo' => 'https://static.finnhub.io/logo/87cb30d8-80df-11ea-8951-00000000092a.png',
                    'finnhubIndustry' => 'Technology'
                ),
            ),
            'NEWS' => array(
                'path' => 'company-news',
                'required_params' => array('symbol', 'from', 'to'),
                'optional_params' => array(),
                'description' => 'Company news with headlines, summaries, and related data',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'category' => 'technology',
                        'datetime' => 1569550360,
                        'headline' => 'Apple Makes Another Acquisition',
                        'id' => 25330,
                        'image' => 'https://image.finnhub.io/image/high/abc',
                        'related' => 'AAPL',
                        'source' => 'CNBC',
                        'summary' => 'Apple has acquired a startup focused on AI and machine learning',
                        'url' => 'https://www.cnbc.com/2019/09/27/apple-makes-another-acquisition.html'
                    )
                ),
            ),
            'PEERS' => array(
                'path' => 'stock/peers',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Company peers with similar business models in the same sector',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array('MSFT', 'GOOG', 'AMZN', 'META'),
            ),
            'BASIC_FINANCIALS' => array(
                'path' => 'stock/metric',
                'required_params' => array('symbol', 'metric'),
                'optional_params' => array(),
                'description' => 'Basic financial metrics including P/E ratio, market cap, and dividend yield',
                'metrics' => array('all', 'price', 'valuation', 'growth', 'margin', 'management', 'financial', 'technical'),
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'metric' => array(
                        'peBasicExclExtraTTM' => 30.6195,
                        '52WeekHigh' => 327.85,
                        '52WeekLow' => 170.27,
                        'beta' => 1.3736,
                        'dividendYieldIndicatedAnnual' => 0.5733,
                    ),
                    'metricType' => 'all',
                    'series' => array(
                        'annual' => array(
                            'currentRatio' => array(
                                array('period' => '2019', 'v' => 1.5401),
                                array('period' => '2018', 'v' => 1.1329),
                            )
                        )
                    )
                ),
            ),
            'CANDLES' => array(
                'path' => 'stock/candle',
                'required_params' => array('symbol', 'resolution', 'from', 'to'),
                'optional_params' => array('adjusted'),
                'description' => 'Stock price candlestick data for technical analysis',
                'resolutions' => array('1', '5', '15', '30', '60', 'D', 'W', 'M'),
                'method' => 'GET', 
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'c' => array(217.68, 221.03, 219.89), // Close prices
                    'h' => array(222.49, 221.5, 220.94),  // High prices
                    'l' => array(217.19, 217.14, 218.83), // Low prices
                    'o' => array(221.03, 218.55, 220),    // Open prices
                    't' => array(1569297600, 1569384000, 1569470400), // Timestamps
                    'v' => array(33463820, 24018876, 20730608), // Volumes
                    's' => 'ok', // Status
                ),
            ),
            'RECOMMENDATION_TRENDS' => array(
                'path' => 'stock/recommendation',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Analyst recommendation trends with buy/sell/hold ratings',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'buy' => 19,
                        'hold' => 6,
                        'period' => '2020-03-01',
                        'sell' => 0,
                        'strongBuy' => 13,
                        'strongSell' => 0,
                        'symbol' => 'AAPL'
                    )
                ),
            ),
            'PRICE_TARGET' => array(
                'path' => 'stock/price-target',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Latest price target consensus among analysts',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'lastUpdated' => '2020-03-21',
                    'symbol' => 'AAPL',
                    'targetHigh' => 370,
                    'targetLow' => 190,
                    'targetMean' => 320.08,
                    'targetMedian' => 330,
                ),
            ),
            'EARNINGS' => array(
                'path' => 'stock/earnings',
                'required_params' => array('symbol'),
                'optional_params' => array('limit'),
                'description' => 'Company earnings data for recent quarters',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'actual' => 2.46,
                        'estimate' => 2.36,
                        'period' => '2019-12-31',
                        'symbol' => 'AAPL',
                        'surprise' => 0.1,
                        'surprisePercent' => 4.2373
                    )
                ),
            ),
            'MARKET_NEWS' => array(
                'path' => 'news',
                'required_params' => array('category'),
                'optional_params' => array('minId'),
                'description' => 'Market news across different categories',
                'categories' => array('general', 'forex', 'crypto', 'merger'),
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'category' => 'general',
                        'datetime' => 1569550360,
                        'headline' => 'Latest Market News',
                        'id' => 25330,
                        'image' => 'https://image.finnhub.io/news/image.jpg',
                        'related' => '',
                        'source' => 'CNBC',
                        'summary' => 'Summary of the latest market news',
                        'url' => 'https://www.cnbc.com/2019/09/27/latest-market-news.html'
                    )
                ),
            ),
            'SYMBOLS' => array(
                'path' => 'stock/symbol',
                'required_params' => array('exchange'),
                'optional_params' => array('mic', 'securityType', 'currency'),
                'description' => 'Stock symbols by exchange',
                'exchanges' => array(
                    'US' => 'US exchanges including NASDAQ, NYSE',
                    'BA' => 'Buenos Aires Stock Exchange',
                    'F' => 'Frankfurt Stock Exchange',
                    'L' => 'London Stock Exchange',
                    'SS' => 'Shanghai Stock Exchange',
                    'SZ' => 'Shenzhen Stock Exchange',
                    'TO' => 'Toronto Stock Exchange',
                    'V' => 'Vienna Stock Exchange',
                ),
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'currency' => 'USD', 
                        'description' => 'APPLE INC',
                        'displaySymbol' => 'AAPL',
                        'figi' => 'BBG000B9XRY4',
                        'mic' => 'XNAS',
                        'symbol' => 'AAPL',
                        'type' => 'Common Stock'
                    )
                ),
            ),
            
            // New endpoints
            'COMPANY_EARNINGS_CALENDAR' => array(
                'path' => 'calendar/earnings',
                'required_params' => array(),
                'optional_params' => array('symbol', 'from', 'to'),
                'description' => 'Company earnings calendar with upcoming earnings announcements',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'earningsCalendar' => array(
                        array(
                            'date' => '2020-02-19',
                            'epsActual' => null,
                            'epsEstimate' => 1.01,
                            'hour' => 'amc',
                            'quarter' => 1,
                            'revenueActual' => null,
                            'revenueEstimate' => 9420160000,
                            'symbol' => 'NVDA',
                            'year' => 2020
                        )
                    )
                ),
            ),
            
            'COMPANY_IPO_CALENDAR' => array(
                'path' => 'calendar/ipo',
                'required_params' => array(),
                'optional_params' => array('from', 'to'),
                'description' => 'IPO calendar with upcoming public offerings',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'ipoCalendar' => array(
                        array(
                            'date' => '2020-01-15',
                            'exchange' => 'NASDAQ Global',
                            'name' => 'Company Name',
                            'numberOfShares' => 19500000,
                            'price' => '10-12',
                            'status' => 'expected',
                            'symbol' => 'ABCD',
                            'totalSharesValue' => 214500000
                        )
                    )
                ),
            ),
            
            'STOCK_DIVIDENDS' => array(
                'path' => 'stock/dividend',
                'required_params' => array('symbol', 'from', 'to'),
                'optional_params' => array(),
                'description' => 'Historical dividends data for a company',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'adjustedDiv' => 0.57,
                        'amount' => 0.57,
                        'date' => '2020-02-07',
                        'declarationDate' => '2020-01-28',
                        'paymentDate' => '2020-02-13',
                        'recordDate' => '2020-02-10',
                        'symbol' => 'AAPL'
                    )
                ),
            ),
            
            'STOCK_SPLITS' => array(
                'path' => 'stock/split',
                'required_params' => array('symbol', 'from', 'to'),
                'optional_params' => array(),
                'description' => 'Historical stock splits data',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'date' => '2020-08-31',
                        'fromFactor' => 1,
                        'ratio' => 0.25,
                        'symbol' => 'AAPL',
                        'toFactor' => 4
                    )
                ),
            ),
            
            'SEC_FILINGS' => array(
                'path' => 'stock/filings',
                'required_params' => array(),
                'optional_params' => array('symbol', 'cik', 'accessNumber', 'form', 'from', 'to'),
                'description' => 'SEC filings for U.S. publicly listed companies',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'accessNumber' => '0000320193-20-000052',
                        'cik' => '320193',
                        'filingDate' => '2020-04-30',
                        'form' => '10-Q',
                        'reportUrl' => 'https://finnhub.io/api/v1/stock/filings/report?accessNumber=0000320193-20-000052',
                        'symbol' => 'AAPL',
                        'title' => 'Quarterly Report'
                    )
                ),
            ),
            
            'INSIDER_TRANSACTIONS' => array(
                'path' => 'stock/insider-transactions',
                'required_params' => array('symbol'),
                'optional_params' => array('from', 'to'),
                'description' => 'Company insider transactions data filed with SEC',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'data' => array(
                        array(
                            'change' => -989,
                            'filingDate' => '2020-02-05',
                            'name' => 'COOK TIMOTHY D',
                            'share' => 980211,
                            'symbol' => 'AAPL',
                            'transactionCode' => 'S',
                            'transactionDate' => '2020-02-03',
                            'transactionPrice' => 311.15,
                            'value' => 307728.35
                        )
                    ),
                    'symbol' => 'AAPL'
                ),
            ),
            
            'SENTIMENT' => array(
                'path' => 'news-sentiment',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'News sentiment analysis and buzz for companies',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'buzz' => array(
                        'articlesInLastWeek' => 365,
                        'buzz' => 1.4694,
                        'weeklyAverage' => 248.4
                    ),
                    'companyNewsScore' => 0.7595,
                    'sectorAverageBullishPercent' => 0.6482,
                    'sectorAverageNewsScore' => 0.6048,
                    'sentiment' => array(
                        'bearishPercent' => 0.1905,
                        'bullishPercent' => 0.8095
                    ),
                    'symbol' => 'AAPL'
                ),
            ),
            
            'PATTERNS' => array(
                'path' => 'scan/pattern',
                'required_params' => array('symbol', 'resolution'),
                'optional_params' => array(),
                'description' => 'Candlestick patterns detection for technical analysis',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'points' => array(
                        array(
                            'aprice' => 330.71,
                            'bullishvol' => -1.5141,
                            'pattern' => 'CDLHAMMER',
                            'patternname' => 'Hammer',
                            'patterntype' => 'bullish',
                            'price' => 326.28,
                            'timestamp' => 1582930800,
                            'volume' => 75221200
                        )
                    )
                ),
            ),
            
            'SUPPORT_RESISTANCE' => array(
                'path' => 'scan/support-resistance',
                'required_params' => array('symbol', 'resolution'),
                'optional_params' => array(),
                'description' => 'Support and resistance levels for technical analysis',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'levels' => array(240.4, 247.74, 253.95, 257.81, 263.51)
                ),
            ),
            
            'AGGREGATE_INDICATORS' => array(
                'path' => 'scan/technical-indicator',
                'required_params' => array('symbol', 'resolution'),
                'optional_params' => array(),
                'description' => 'Aggregate technical indicators with trading signals',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'technicalAnalysis' => array(
                        'count' => array(
                            'buy' => 7,
                            'neutral' => 8, 
                            'sell' => 3
                        ),
                        'signal' => 'buy'
                    ),
                    'trend' => array(
                        'adx' => 37.12,
                        'trending' => true
                    )
                ),
            ),
            
            'ETF_HOLDERS' => array(
                'path' => 'etf/holdings',
                'required_params' => array('symbol'),
                'optional_params' => array('skip', 'limit'),
                'description' => 'ETF holdings data with allocation percentages',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'atDate' => '2020-03-02',
                    'holdingInfoList' => array(
                        array(
                            'cusip' => '037833100',
                            'name' => 'APPLE INC',
                            'percent' => 3.46,
                            'share' => 1940395,
                            'symbol' => 'AAPL',
                            'value' => 558406953
                        )
                    ),
                    'symbol' => 'QQQ'
                ),
            ),
            
            'EARNINGS_SURPRISES' => array(
                'path' => 'stock/earnings-surprises',
                'required_params' => array('symbol'),
                'optional_params' => array('limit'),
                'description' => 'Historical earnings surprises with actual vs. estimated data',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'actual' => 1.68,
                        'estimate' => 1.63,
                        'period' => '2021-06-30', 
                        'quarter' => 3,
                        'symbol' => 'AAPL',
                        'year' => 2021
                    ),
                ),
            ),
            
            'FDA_CALENDAR' => array(
                'path' => 'calendar/fda',
                'required_params' => array(),
                'optional_params' => array('from', 'to'),
                'description' => 'FDA approval calendar for biotech companies',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'fdaCalendar' => array(
                        array(
                            'companyName' => 'Bio-pharmaceutical Company',
                            'date' => '2020-03-28',
                            'drugName' => 'Drug-XYZ',
                            'stage' => 'PDUFA',
                            'status' => 'Calendar',
                            'symbol' => 'BPMC',
                            'targetIndication' => 'Cancer'
                        )
                    )
                ),
            ),
            
            'COVID19' => array(
                'path' => 'covid19/us',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'US COVID-19 statistics (state and national data)',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'case' => 150473,
                        'date' => '2020-03-30',
                        'death' => 2771,
                        'state' => 'United States'
                    )
                ),
            ),
            
            'COUNTRY_METRICS' => array(
                'path' => 'country',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'Country economic metrics with GDP, population data',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'code2' => 'US',
                        'code3' => 'USA',
                        'codeNo' => '840',
                        'country' => 'United States of America',
                        'currency' => array(
                            'code' => 'USD',
                            'name' => 'U.S. Dollar'
                        ),
                        'economicData' => array(
                            'gdp' => 2.149e+13,
                            'gdpPerCapita' => 65297.52,
                            'grossNationalProduct' => 2.1765e+13,
                            'grossSavings' => 4.524e+12,
                            'population' => 329064917
                        )
                    )
                ),
            ),
            
            'FOREX_EXCHANGE' => array(
                'path' => 'forex/exchange',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'List of supported forex exchanges',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array('fxcm', 'oanda', 'coinbase')
            ),
            
            'FOREX_SYMBOL' => array(
                'path' => 'forex/symbol',
                'required_params' => array('exchange'),
                'optional_params' => array(),
                'description' => 'List of forex symbols supported by an exchange',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'description' => 'Euro/US Dollar',
                        'displaySymbol' => 'EUR/USD',
                        'symbol' => 'OANDA:EUR_USD'
                    )
                )
            ),
            
            'CRYPTO_EXCHANGE' => array(
                'path' => 'crypto/exchange',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'List of supported cryptocurrency exchanges',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array('binance', 'coinbase', 'kraken')
            ),
            
            'CRYPTO_SYMBOL' => array(
                'path' => 'crypto/symbol',
                'required_params' => array('exchange'),
                'optional_params' => array(),
                'description' => 'List of cryptocurrency symbols supported by an exchange',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'description' => 'Bitcoin/USD',
                        'displaySymbol' => 'BTC/USD',
                        'symbol' => 'BINANCE:BTCUSDT'
                    )
                )
            ),
            
            'STOCK_REVENUE_BREAKDOWN' => array(
                'path' => 'stock/revenue-breakdown',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Revenue breakdown by product/segment and geographic areas',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'data' => array(
                        array(
                            'breakdown' => array(
                                array(
                                    'product' => 'iPhone',
                                    'rev' => 33362000000,
                                    'revpct' => 44.3
                                )
                            ),
                            'period' => '2020-06-30',
                            'symbol' => 'AAPL'
                        )
                    ),
                    'symbol' => 'AAPL'
                ),
            ),
            
            // Webhook and Real-time Data
            'WEBSOCKET_TRADES' => array(
                'path' => 'websocket',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Real-time trade data via WebSocket connection',
                'method' => 'WEBSOCKET',
                'rate_limit' => 'Real-time streaming',
                'webhook_support' => true,
                'example_response' => array(
                    'data' => array(
                        array(
                            'p' => 7296.89, // Price
                            's' => 'BINANCE:BTCUSDT', // Symbol
                            't' => 1575526691134, // Timestamp
                            'v' => 0.011467 // Volume
                        )
                    ),
                    'type' => 'trade'
                ),
            ),
            
            'ECONOMIC_CALENDAR' => array(
                'path' => 'calendar/economic',
                'required_params' => array(),
                'optional_params' => array('from', 'to'),
                'description' => 'Economic events calendar with impact ratings',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'economicCalendar' => array(
                        array(
                            'actual' => 8.1,
                            'country' => 'US',
                            'estimate' => 8.2,
                            'event' => 'Unemployment Rate',
                            'impact' => 'high',
                            'prev' => 8.3,
                            'time' => '2020-04-03 12:30:00',
                            'unit' => '%'
                        )
                    )
                ),
            ),
            
            'INSTITUTIONAL_OWNERSHIP' => array(
                'path' => 'stock/institutional-ownership',
                'required_params' => array('symbol'),
                'optional_params' => array('limit'),
                'description' => 'Institutional ownership data with holdings percentages',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'data' => array(
                        array(
                            'investorName' => 'Vanguard Group Inc',
                            'change' => 1234567,
                            'filingDate' => '2020-03-31',
                            'numOfShares' => 334553475,
                            'percentOfShares' => 7.74,
                            'symbol' => 'AAPL'
                        )
                    ),
                    'symbol' => 'AAPL'
                ),
            ),
            
            'MARKET_HOLIDAYS' => array(
                'path' => 'stock/market-holiday',
                'required_params' => array('exchange'),
                'optional_params' => array(),
                'description' => 'Market holidays for different exchanges',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    'data' => array(
                        array(
                            'atDate' => '2020-01-01',
                            'tradingHour' => '',
                            'eventName' => 'New Year\'s Day',
                            'exchange' => 'US'
                        )
                    )
                ),
            ),
            
            'STOCK_UPGRADES_DOWNGRADES' => array(
                'path' => 'stock/upgrade-downgrade',
                'required_params' => array(),
                'optional_params' => array('symbol', 'from', 'to'),
                'description' => 'Stock upgrades and downgrades from analysts',
                'method' => 'GET',
                'rate_limit' => '30 calls per second',
                'example_response' => array(
                    array(
                        'company' => 'Apple Inc',
                        'fromGrade' => 'Buy',
                        'gradeTime' => '2020-03-25 11:50:44',
                        'newGrade' => 'Strong Buy',
                        'symbol' => 'AAPL'
                    )
                ),
            ),
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
        
        // Use the provided base_url or get from options
        if (empty($base_url)) {
            $base_url = 'https://finnhub.io/api/v1';
        }
        
        // Build the path
        $path = $endpoint['path'];
        
        // Start building query parameters
        $query_params = array();
        
        // Add required parameters
        foreach ($endpoint['required_params'] as $param) {
            if (isset($params[$param])) {
                $query_params[$param] = $params[$param];
            } else {
                // Missing required parameter
                return '';
            }
        }
        
        // Add optional parameters
        foreach ($endpoint['optional_params'] as $param) {
            if (isset($params[$param])) {
                $query_params[$param] = $params[$param];
            }
        }
        
        // Add API key from options
        $api_key = get_option('tradepress_finnhub_api_key', '');
        $query_params['token'] = $api_key;
        
        // Build the URL
        $url = $base_url . '/' . $path;
        
        // Add query parameters
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        
        return $url;
    }
}
