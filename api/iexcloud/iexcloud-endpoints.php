<?php
/**
 * TradePress IEX Cloud API Endpoints
 *
 * Defines endpoints and parameters for the IEX Cloud market data service
 * API Documentation: https://iexcloud.io/docs/api/
 * 
 * @package TradePress
 * @subpackage API\IEX
 * @version 1.0.0
 * @since 2025-04-10
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress IEX Cloud API Endpoints class
 */
class TradePress_IEX_Endpoints {
    
    /**
     * API Restrictions and Rate Limits
     * 
     * Based on IEX Cloud API documentation
     * 
     * @return array API restrictions information
     */
    public static function get_api_restrictions() {
        return array(
            'rate_limits' => array(
                'description' => 'Maximum number of requests per time window',
                'details' => array(
                    'free_tier' => '50,000 messages per month',
                    'standard_tier' => 'Starting at 1 million messages per month',
                    'premium_tier' => 'Custom allocation based on needs',
                    'burst_rate' => 'Up to 100 requests per second for pay-as-you-go accounts'
                )
            ),
            'authentication' => array(
                'description' => 'API Authentication methods',
                'details' => array(
                    'public_token' => 'Query parameter in URL (?token=YOUR_TOKEN)',
                    'secret_token' => 'Used for secure endpoints with sk_ prefix',
                    'headers' => array(
                        'Authorization' => 'Bearer YOUR_TOKEN'
                    )
                )
            ),
            'environments' => array(
                'description' => 'Available API environments',
                'details' => array(
                    'production' => 'https://cloud.iexapis.com/stable',
                    'sandbox' => 'https://sandbox.iexapis.com/stable',
                    'v1' => 'https://cloud.iexapis.com/v1',
                    'beta' => 'https://cloud.iexapis.com/beta'
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
            // Stock Core Data
            'QUOTE' => array(
                'path' => 'stock/{symbol}/quote',
                'required_params' => array('symbol'),
                'optional_params' => array('displayPercent'),
                'description' => 'Real-time quote data',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'companyName' => 'Apple Inc',
                    'primaryExchange' => 'NASDAQ',
                    'calculationPrice' => 'close',
                    'open' => 172.17,
                    'openTime' => 1649675400000,
                    'openSource' => 'official',
                    'close' => 175.23,
                    'closeTime' => 1649698800000,
                    'closeSource' => 'official',
                    'high' => 176.24,
                    'highTime' => 1649698800000,
                    'highSource' => 'delayed',
                    'low' => 172.14,
                    'lowTime' => 1649698800000,
                    'lowSource' => 'delayed',
                    'latestPrice' => 175.23,
                    'latestSource' => 'Close',
                    'latestTime' => 'April 11, 2025',
                    'latestUpdate' => 1649698800000,
                    'latestVolume' => 68632290,
                    'volume' => 68632290,
                    'iexRealtimePrice' => 175.23,
                    'iexRealtimeSize' => 100,
                    'iexLastUpdated' => 1649698800000,
                    'delayedPrice' => 175.23,
                    'delayedPriceTime' => 1649698800000,
                    'extendedPrice' => 175.28,
                    'extendedChange' => 0.05,
                    'extendedChangePercent' => 0.00029,
                    'extendedPriceTime' => 1649705100000,
                    'previousClose' => 172.14,
                    'previousVolume' => 72233503,
                    'change' => 3.09,
                    'changePercent' => 0.01795,
                    'avgTotalVolume' => 78626578,
                    'marketCap' => 2857544225280,
                    'peRatio' => 28.53,
                    'week52High' => 182.94,
                    'week52Low' => 122.25,
                    'ytdChange' => -0.0155,
                    'isUSMarketOpen' => false
                )
            ),
            'COMPANY' => array(
                'path' => 'stock/{symbol}/company',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Company information',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'companyName' => 'Apple Inc',
                    'exchange' => 'NASDAQ',
                    'industry' => 'Telecommunications Equipment',
                    'website' => 'https://www.apple.com',
                    'description' => 'Apple Inc. designs, manufactures, and markets smartphones, personal computers, tablets, wearables, and accessories worldwide. It also sells various related services...',
                    'CEO' => 'Timothy Cook',
                    'securityName' => 'Apple Inc',
                    'issueType' => 'cs',
                    'sector' => 'Technology',
                    'primarySicCode' => 3571,
                    'employees' => 154000,
                    'tags' => array('Technology', 'Consumer Electronics', 'Computers', 'Mobile Phones'),
                    'address' => 'One Apple Park Way',
                    'address2' => null,
                    'state' => 'CA',
                    'city' => 'Cupertino',
                    'zip' => '95014',
                    'country' => 'US',
                    'phone' => '408-996-1010'
                )
            ),
            'PRICE' => array(
                'path' => 'stock/{symbol}/price',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Latest price',
                'example_response' => 175.23
            ),
            'CHART' => array(
                'path' => 'stock/{symbol}/chart/{range}',
                'required_params' => array('symbol', 'range'),
                'optional_params' => array('chartCloseOnly', 'chartSimplify', 'chartInterval'),
                'description' => 'Historical price data',
                'ranges' => array('1d', '5d', '1m', '3m', '6m', 'ytd', '1y', '2y', '5y', 'max'),
                'example_response' => array(
                    array(
                        'date' => '2025-04-11',
                        'open' => 172.17,
                        'high' => 176.24,
                        'low' => 172.14,
                        'close' => 175.23,
                        'volume' => 68632290,
                        'uOpen' => 172.17,
                        'uHigh' => 176.24,
                        'uLow' => 172.14,
                        'uClose' => 175.23,
                        'uVolume' => 68632290,
                        'change' => 3.09,
                        'changePercent' => 0.01795,
                        'label' => 'Apr 11',
                        'changeOverTime' => 0.01795
                    )
                )
            ),
            'FINANCIALS' => array(
                'path' => 'stock/{symbol}/financials',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'last'),
                'description' => 'Income statement, balance sheet, and cash flow data',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'financials' => array(
                        array(
                            'reportDate' => '2025-03-31',
                            'fiscalDate' => '2025-03-31',
                            'currency' => 'USD',
                            'totalRevenue' => 97278000000,
                            'costOfRevenue' => 54763000000,
                            'grossProfit' => 42515000000,
                            'researchAndDevelopment' => 6392000000,
                            'sellingGeneralAndAdmin' => 6151000000,
                            'operatingExpense' => 12543000000,
                            'operatingIncome' => 29972000000,
                            'otherIncomeExpenseNet' => 121000000,
                            'ebit' => 29972000000,
                            'interestIncome' => null,
                            'pretaxIncome' => 30093000000,
                            'incomeTax' => 4418000000,
                            'minorityInterest' => null,
                            'netIncome' => 25675000000,
                            'netIncomeBasic' => 25675000000
                        )
                    )
                )
            ),
            'BALANCE_SHEET' => array(
                'path' => 'stock/{symbol}/balance-sheet',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'last'),
                'description' => 'Balance sheet data',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'balancesheet' => array(
                        array(
                            'reportDate' => '2025-03-31',
                            'fiscalDate' => '2025-03-31',
                            'currency' => 'USD',
                            'currentCash' => 38916000000,
                            'shortTermInvestments' => 27228000000,
                            'receivables' => 18245000000,
                            'inventory' => 5945000000,
                            'otherCurrentAssets' => 14162000000,
                            'currentAssets' => 104496000000,
                            'longTermInvestments' => 100544000000,
                            'propertyPlantEquipment' => 40235000000,
                            'goodwill' => null,
                            'intangibleAssets' => null,
                            'otherAssets' => 48369000000,
                            'totalAssets' => 293644000000,
                            'accountsPayable' => 46725000000,
                            'currentLongTermDebt' => 11134000000,
                            'otherCurrentLiabilities' => 43292000000,
                            'totalCurrentLiabilities' => 101151000000,
                            'longTermDebt' => 98328000000,
                            'otherLiabilities' => 52428000000,
                            'minorityInterest' => null,
                            'totalLiabilities' => 251907000000,
                            'commonStock' => 64849000000,
                            'retainedEarnings' => -23112000000,
                            'treasuryStock' => null,
                            'capitalSurplus' => null,
                            'shareholderEquity' => 41737000000,
                            'netTangibleAssets' => 41737000000
                        )
                    )
                )
            ),
            'CASH_FLOW' => array(
                'path' => 'stock/{symbol}/cash-flow',
                'required_params' => array('symbol'),
                'optional_params' => array('period', 'last'),
                'description' => 'Cash flow statement data',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'cashflow' => array(
                        array(
                            'reportDate' => '2025-03-31',
                            'fiscalDate' => '2025-03-31',
                            'currency' => 'USD',
                            'netIncome' => 25675000000,
                            'depreciation' => 2843000000,
                            'changesInReceivables' => -1806000000,
                            'changesInInventories' => 211000000,
                            'cashChange' => -7256000000,
                            'cashFlow' => 28635000000,
                            'capitalExpenditures' => -2310000000,
                            'investments' => -34091000000,
                            'investingActivityOther' => 29581000000,
                            'totalInvestingCashFlows' => -6820000000,
                            'dividendsPaid' => -3830000000,
                            'netBorrowings' => 2519000000,
                            'otherFinancingCashFlows' => -22042000000,
                            'cashFlowFinancing' => -23353000000,
                            'exchangeRateEffect' => null
                        )
                    )
                )
            ),
            'NEWS' => array(
                'path' => 'stock/{symbol}/news',
                'required_params' => array('symbol'),
                'optional_params' => array('last'),
                'description' => 'News articles for a symbol',
                'example_response' => array(
                    array(
                        'datetime' => 1649756400000,
                        'headline' => 'Apple Announces New iPhone Model with Revolutionary Features',
                        'source' => 'The Wall Street Journal',
                        'url' => 'https://www.example.com/news/apple-new-iphone',
                        'summary' => 'Apple Inc. unveiled its newest iPhone model today with several groundbreaking features...',
                        'related' => 'AAPL',
                        'image' => 'https://cloud.iexapis.com/images/sample_image.jpg',
                        'lang' => 'en',
                        'hasPaywall' => true
                    )
                )
            ),
            'PEERS' => array(
                'path' => 'stock/{symbol}/peers',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Similar companies',
                'example_response' => array('MSFT', 'GOOGL', 'META', 'AMZN', 'NVDA', 'DELL', 'HPQ')
            ),
            'MARKET' => array(
                'path' => 'stock/market/list/{list_type}',
                'required_params' => array('list_type'),
                'optional_params' => array('displayPercent', 'listLimit'),
                'description' => 'Market lists (gainers, losers, most active)',
                'list_types' => array('mostactive', 'gainers', 'losers', 'iexvolume', 'iexpercent'),
                'example_response' => array(
                    array(
                        'symbol' => 'XYZ',
                        'companyName' => 'XYZ Corp',
                        'primaryExchange' => 'NASDAQ',
                        'calculationPrice' => 'close',
                        'open' => 98.5,
                        'openTime' => 1649675400000,
                        'close' => 110.25,
                        'closeTime' => 1649698800000,
                        'high' => 111.35,
                        'low' => 97.73,
                        'latestPrice' => 110.25,
                        'latestSource' => 'Close',
                        'latestUpdate' => 1649698800000,
                        'latestVolume' => 28765423,
                        'previousClose' => 98.45,
                        'change' => 11.8,
                        'changePercent' => 0.1199,
                        'marketCap' => 45678912345
                    )
                )
            ),
            
            // Advanced Data
            'EARNINGS' => array(
                'path' => 'stock/{symbol}/earnings',
                'required_params' => array('symbol'),
                'optional_params' => array('last'),
                'description' => 'Earnings data for a symbol',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'earnings' => array(
                        array(
                            'actualEPS' => 1.52,
                            'consensusEPS' => 1.43,
                            'announceTime' => 'AMC',
                            'numberOfEstimates' => 34,
                            'EPSSurpriseDollar' => 0.09,
                            'EPSReportDate' => '2025-04-28',
                            'fiscalPeriod' => 'Q2 2025',
                            'fiscalEndDate' => '2025-03-31',
                            'yearAgo' => 1.4,
                            'yearAgoChangePercent' => 0.0857
                        )
                    )
                )
            ),
            'DIVIDENDS' => array(
                'path' => 'stock/{symbol}/dividends/{range}',
                'required_params' => array('symbol', 'range'),
                'optional_params' => array(),
                'description' => 'Dividend information for a symbol',
                'ranges' => array('5y', '2y', '1y', 'ytd', '6m', '3m', '1m', 'next'),
                'example_response' => array(
                    array(
                        'exDate' => '2025-02-07',
                        'paymentDate' => '2025-02-13',
                        'recordDate' => '2025-02-10',
                        'declaredDate' => '2025-01-28',
                        'amount' => 0.23,
                        'flag' => 'Cash',
                        'currency' => 'USD',
                        'description' => 'Ordinary Shares',
                        'frequency' => 'quarterly'
                    )
                )
            ),
            'INSIDER_TRANSACTIONS' => array(
                'path' => 'stock/{symbol}/insider-transactions',
                'required_params' => array('symbol'),
                'optional_params' => array('limit'),
                'description' => 'Insider transactions for a symbol',
                'example_response' => array(
                    array(
                        'conversionOrExercisePrice' => 0,
                        'directIndirect' => 'D',
                        'effectiveDate' => 1649635200000,
                        'filingDate' => 1649721600000,
                        'fullName' => 'COOK TIMOTHY D',
                        'is10b51' => false,
                        'postShares' => 3279537,
                        'reportedTitle' => 'Chief Executive Officer',
                        'symbol' => 'AAPL',
                        'transactionCode' => 'S',
                        'transactionDate' => '2025-04-11',
                        'transactionPrice' => 175.23,
                        'transactionShares' => 20000,
                        'transactionValue' => 3504600,
                        'id' => 'INSXXXX'
                    )
                )
            ),
            'STATS' => array(
                'path' => 'stock/{symbol}/stats',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Key stats for a symbol',
                'example_response' => array(
                    'companyName' => 'Apple Inc',
                    'marketcap' => 2857544225280,
                    'week52high' => 182.94,
                    'week52low' => 122.25,
                    'week52highDate' => '2025-01-04',
                    'week52lowDate' => '2024-05-12',
                    'week52change' => 0.2731,
                    'shortInterest' => 51466176,
                    'shortDate' => '2025-04-15',
                    'dividendRate' => 0.92,
                    'dividendYield' => 0.0053,
                    'exDividendDate' => '2025-02-07',
                    'latestEPS' => 6.15,
                    'latestEPSDate' => '2024-09-30',
                    'sharesOutstanding' => 16319441000,
                    'float' => 16304896356,
                    'returnOnEquity' => 0.16172,
                    'consensusEPS' => 1.43,
                    'ttmEPS' => 6.15,
                    'peRatio' => 28.53
                )
            ),
            'INTRADAY_PRICES' => array(
                'path' => 'stock/{symbol}/intraday-prices',
                'required_params' => array('symbol'),
                'optional_params' => array('chartIEXOnly', 'chartSimplify', 'chartInterval', 'chartReset'),
                'description' => 'Intraday price data for a symbol',
                'example_response' => array(
                    array(
                        'date' => '2025-04-11',
                        'minute' => '09:30',
                        'label' => '09:30 AM',
                        'open' => 172.17,
                        'high' => 172.45,
                        'low' => 172.05,
                        'close' => 172.33,
                        'volume' => 1357864,
                        'notional' => 233925763.8,
                        'numberOfTrades' => 6482
                    )
                )
            ),
            
            // Reference Data
            'SYMBOLS' => array(
                'path' => 'ref-data/symbols',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'List of all available symbols',
                'example_response' => array(
                    array(
                        'symbol' => 'AAPL',
                        'exchange' => 'NASDAQ',
                        'name' => 'Apple Inc',
                        'date' => '2019-03-08',
                        'type' => 'cs',
                        'iexId' => 'IEX_4D48333344362D52',
                        'region' => 'US',
                        'currency' => 'USD',
                        'isEnabled' => true
                    )
                )
            ),
            'EXCHANGES' => array(
                'path' => 'ref-data/exchanges',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'List of exchanges',
                'example_response' => array(
                    array(
                        'exchange' => 'NASDAQ',
                        'region' => 'US',
                        'description' => 'NASDAQ Stock Market',
                        'mic' => 'XNAS',
                        'exchangeSuffix' => '.NQ'
                    )
                )
            ),
            'US_HOLIDAYS' => array(
                'path' => 'ref-data/us/dates/holiday/{direction}/{last}',
                'required_params' => array('direction', 'last'),
                'optional_params' => array(),
                'description' => 'List of market holidays',
                'directions' => array('next', 'last'),
                'example_response' => array(
                    array(
                        'date' => '2025-05-26',
                        'settlement' => '2025-05-27',
                        'exchange' => 'Independence Day',
                        'type' => 'public'
                    )
                )
            ),

            // Alternative Data
            'SENTIMENT' => array(
                'path' => 'stock/{symbol}/sentiment/{type}/{date}',
                'required_params' => array('symbol', 'type'),
                'optional_params' => array('date'),
                'description' => 'Social sentiment data',
                'types' => array('daily', 'minute'),
                'example_response' => array(
                    'sentiment' => 0.75,
                    'totalScores' => 250,
                    'positive' => 0.85,
                    'negative' => 0.15
                )
            ),
            'CEO_COMPENSATION' => array(
                'path' => 'stock/{symbol}/ceo-compensation',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'CEO compensation data',
                'example_response' => array(
                    'symbol' => 'AAPL',
                    'name' => 'Timothy D. Cook',
                    'companyName' => 'Apple Inc.',
                    'location' => 'Cupertino, California',
                    'salary' => 3000000,
                    'bonus' => 12000000,
                    'stockAwards' => 82000000,
                    'optionAwards' => 0,
                    'nonEquityIncentives' => 10850000,
                    'pensionAndDeferred' => 0,
                    'otherComp' => 1139597,
                    'total' => 98989597,
                    'year' => '2024'
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
        
        // Use the provided base_url or get from options
        if (empty($base_url)) {
            $base_url = 'https://cloud.iexapis.com/stable';
        }
        
        // Build the path with parameter substitution
        $path = $endpoint['path'];
        
        // Process required parameters in path
        foreach ($endpoint['required_params'] as $param) {
            if (isset($params[$param])) {
                $path = str_replace('{' . $param . '}', $params[$param], $path);
                unset($params[$param]); // Remove it so we don't add it as a query param
            } else {
                // Missing required parameter
                return '';
            }
        }
        
        // Start building query parameters
        $query_params = array();
        
        // Add API token from options
        $api_token = get_option('tradepress_iex_api_token', '');
        $query_params['token'] = $api_token;
        
        // Add remaining optional parameters
        foreach ($endpoint['optional_params'] as $param) {
            if (isset($params[$param])) {
                $query_params[$param] = $params[$param];
            }
        }
        
        // Build the URL
        $url = $base_url . '/' . $path;
        
        // Add query parameters if any
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        
        return $url;
    }
}
