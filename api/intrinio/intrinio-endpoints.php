<?php
/**
 * TradePress Intrinio API Endpoints
 *
 * Defines endpoints and parameters for the Intrinio financial data service
 * API Documentation: https://docs.intrinio.com/documentation/api_v2
 * 
 * @package TradePress
 * @subpackage API\Intrinio
 * @version 1.0.0
 * @since 2025-04-10
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Intrinio API Endpoints class
 */
class TradePress_Intrinio_Endpoints {
    
    /**
     * API Restrictions and Rate Limits
     * 
     * Based on Intrinio API documentation
     * 
     * @return array API restrictions information
     */
    public static function get_api_restrictions() {
        return array(
            'rate_limits' => array(
                'description' => 'Maximum number of requests per time window',
                'details' => array(
                    'starter_tier' => '100 requests per day',
                    'standard_tier' => '10,000 requests per day',
                    'professional_tier' => 'Custom allocation based on needs',
                    'max_concurrent' => '5 concurrent requests per API key',
                    'data_points' => 'Varying data point values for different API calls'
                )
            ),
            'authentication' => array(
                'description' => 'API Authentication methods',
                'details' => array(
                    'api_key' => 'Basic authentication using API key as username and password',
                    'headers' => array(
                        'Authorization' => 'Basic base64(api_key:api_key)'
                    )
                )
            ),
            'environments' => array(
                'description' => 'Available API environments',
                'details' => array(
                    'production' => 'https://api-v2.intrinio.com'
                )
            ),
            'pagination' => array(
                'description' => 'Pagination parameters',
                'details' => array(
                    'page_size' => 'Number of results per page (default varies by endpoint)',
                    'next_page' => 'URL to the next page of results'
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
            // Stock Price Data
            'SECURITY_PRICES' => array(
                'path' => 'securities/{identifier}/prices',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'start_date', 
                    'end_date', 
                    'frequency', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Historical end-of-day stock prices for a security',
                'example_response' => array(
                    'securities' => array(
                        'id' => 'AAPL',
                        'name' => 'Apple Inc',
                        'code' => 'AAPL',
                        'stock_exchange' => 'XNAS',
                        'primary_listing' => true
                    ),
                    'stock_prices' => array(
                        array(
                            'date' => '2025-04-09',
                            'open' => 172.36,
                            'high' => 173.98,
                            'low' => 170.05,
                            'close' => 171.78,
                            'volume' => 67218938,
                            'adj_open' => 172.36,
                            'adj_high' => 173.98,
                            'adj_low' => 170.05,
                            'adj_close' => 171.78,
                            'adj_volume' => 67218938
                        )
                    ),
                    'next_page' => 'https://api-v2.intrinio.com/securities/AAPL/prices?end_date=2025-04-01'
                )
            ),
            'REALTIME_PRICE' => array(
                'path' => 'securities/{identifier}/prices/realtime',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array('source'),
                'description' => 'Realtime stock price for a security',
                'example_response' => array(
                    'last_price' => 171.82,
                    'last_time' => '2025-04-10T15:35:07.123Z',
                    'last_size' => 100,
                    'bid_price' => 171.81,
                    'bid_size' => 300,
                    'ask_price' => 171.83,
                    'ask_size' => 200,
                    'open_price' => 172.36,
                    'high_price' => 173.98,
                    'low_price' => 170.05,
                    'exchange_volume' => 55324620,
                    'market_volume' => 67218938,
                    'updated_on' => '2025-04-10T15:35:07.123Z',
                    'source' => 'intrinio_mx',
                    'security' => array(
                        'id' => 'AAPL',
                        'code' => 'AAPL',
                        'name' => 'Apple Inc'
                    )
                )
            ),
            'INTRADAY_PRICES' => array(
                'path' => 'securities/{identifier}/prices/intraday',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'start_date', 
                    'start_time', 
                    'end_date', 
                    'end_time', 
                    'interval', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Intraday stock prices for a security at one-minute intervals',
                'example_response' => array(
                    'intraday_prices' => array(
                        array(
                            'time' => '2025-04-10T09:30:00-04:00',
                            'last_price' => 172.36,
                            'last_size' => 1500,
                            'volume' => 1500,
                            'open' => 172.36,
                            'high' => 172.41,
                            'low' => 172.30,
                            'close' => 172.36,
                            'ask_price' => 172.37,
                            'ask_size' => 800,
                            'bid_price' => 172.35,
                            'bid_size' => 500,
                            'source' => 'intrinio_mx'
                        )
                    ),
                    'security' => array(
                        'id' => 'AAPL',
                        'code' => 'AAPL',
                        'name' => 'Apple Inc'
                    ),
                    'source' => 'intrinio_mx',
                    'next_page' => 'https://api-v2.intrinio.com/securities/AAPL/prices/intraday?end_time=2025-04-10T09:30:00-04:00'
                )
            ),
            
            // Company Fundamentals
            'COMPANY_FUNDAMENTALS' => array(
                'path' => 'companies/{identifier}/fundamentals',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'statement', 
                    'fiscal_year', 
                    'fiscal_period', 
                    'reported_only', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Fundamentals (income statement, balance sheet, cash flow) for a company',
                'example_response' => array(
                    'fundamentals' => array(
                        array(
                            'id' => 'fun_abcdef12345',
                            'statement_code' => 'income_statement',
                            'fiscal_year' => 2024,
                            'fiscal_period' => 'FY',
                            'filing_date' => '2025-01-25',
                            'filing_type' => '10-K',
                            'is_preliminary' => false,
                            'start_date' => '2024-01-01',
                            'end_date' => '2024-12-31'
                        )
                    ),
                    'company' => array(
                        'id' => 'AAPL',
                        'name' => 'Apple Inc',
                        'ticker' => 'AAPL'
                    ),
                    'next_page' => 'https://api-v2.intrinio.com/companies/AAPL/fundamentals?fiscal_year=2023'
                )
            ),
            'FINANCIALS' => array(
                'path' => 'fundamentals/{fundamental_id}/standardized_financials',
                'method' => 'GET',
                'required_params' => array('fundamental_id'),
                'optional_params' => array('page_size', 'next_page'),
                'description' => 'Standardized financial data for a specific fundamental',
                'example_response' => array(
                    'standardized_financials' => array(
                        array(
                            'data_tag' => array(
                                'id' => 'tag_abcdef12345',
                                'name' => 'Revenue',
                                'tag' => 'revenue',
                                'statement_type' => 'income_statement',
                                'parent' => null,
                                'sequence' => 10,
                                'factor' => 'pretax_income',
                                'balance' => null,
                                'type' => 'USD'
                            ),
                            'value' => 394328000000
                        )
                    ),
                    'fundamental' => array(
                        'id' => 'fun_abcdef12345',
                        'statement_code' => 'income_statement',
                        'fiscal_year' => 2024,
                        'fiscal_period' => 'FY'
                    ),
                    'next_page' => null
                )
            ),
            
            // Company Information
            'COMPANY_DETAILS' => array(
                'path' => 'companies/{identifier}',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(),
                'description' => 'Detailed information for a company',
                'example_response' => array(
                    'id' => 'AAPL',
                    'ticker' => 'AAPL',
                    'name' => 'Apple Inc',
                    'lei' => '000000000000000000000',
                    'cik' => '0000320193',
                    'legal_name' => 'Apple Inc.',
                    'stock_exchange' => 'NASDAQ Global Select',
                    'sic' => '3571',
                    'short_description' => 'Apple designs, manufactures, and markets smartphones, personal computers, tablets, wearables, and accessories.',
                    'long_description' => 'Apple Inc. designs, manufactures, and markets smartphones, personal computers, tablets, wearables, and accessories worldwide. It also sells various related services...',
                    'company_url' => 'http://www.apple.com',
                    'business_address' => 'One Apple Park Way',
                    'business_phone_number' => '408-996-1010',
                    'business_fax_number' => null,
                    'hq_address1' => 'One Apple Park Way',
                    'hq_address2' => null,
                    'hq_address_city' => 'Cupertino',
                    'hq_address_postal_code' => '95014',
                    'entity_legal_form' => 'Corporation',
                    'securities' => array(
                        array(
                            'id' => 'sec_abcdef12345',
                            'company_id' => 'AAPL',
                            'name' => 'Apple Inc',
                            'code' => 'AAPL',
                            'currency' => 'USD',
                            'ticker' => 'AAPL',
                            'composite_ticker' => 'AAPL:US',
                            'figi' => 'BBG000B9XRY4',
                            'primary_listing' => true
                        )
                    )
                )
            ),
            'COMPANY_NEWS' => array(
                'path' => 'companies/{identifier}/news',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'page_size', 
                    'next_page'
                ),
                'description' => 'News articles for a company',
                'example_response' => array(
                    'news' => array(
                        array(
                            'id' => 'news_abcdef12345',
                            'title' => 'Apple Reports Record Q1 Revenues',
                            'publication_date' => '2025-01-31T16:35:00Z',
                            'summary' => 'Apple Inc. reported record quarterly revenue of $123.9 billion, up 11% year-over-year...',
                            'url' => 'https://www.example.com/articles/apple-reports-record-q1-revenues',
                            'author' => 'Jane Smith',
                            'company_ids' => array('AAPL'),
                            'tags' => array('earnings', 'technology', 'revenue', 'growth'),
                            'image_url' => 'https://www.example.com/images/apple-earnings.jpg',
                            'source' => 'Example Financial News'
                        )
                    ),
                    'company' => array(
                        'id' => 'AAPL',
                        'name' => 'Apple Inc'
                    ),
                    'next_page' => 'https://api-v2.intrinio.com/companies/AAPL/news?next_page=abcdefg'
                )
            ),
            
            // Stock Market Data
            'SECURITIES_SEARCH' => array(
                'path' => 'securities',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'active', 
                    'delisted', 
                    'code', 
                    'currency', 
                    'ticker', 
                    'name', 
                    'composite_ticker', 
                    'exchange_ticker', 
                    'stock_exchange', 
                    'has_stock_prices', 
                    'primary_listing', 
                    'type', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Search for securities',
                'example_response' => array(
                    'securities' => array(
                        array(
                            'id' => 'sec_abcdef12345',
                            'company_id' => 'AAPL',
                            'name' => 'Apple Inc',
                            'code' => 'AAPL',
                            'currency' => 'USD',
                            'ticker' => 'AAPL',
                            'composite_ticker' => 'AAPL:US',
                            'figi' => 'BBG000B9XRY4',
                            'composite_figi' => 'BBG000B9XRY4',
                            'share_class_figi' => 'BBG001S5N8V8',
                            'primary_listing' => true,
                            'stock_exchange' => 'XNAS',
                            'primary_security_id' => null
                        )
                    ),
                    'next_page' => 'https://api-v2.intrinio.com/securities?next_page=abcdefg'
                )
            ),
            'EXCHANGES' => array(
                'path' => 'stock_exchanges',
                'method' => 'GET',
                'required_params' => array(),
                'optional_params' => array(
                    'city', 
                    'country', 
                    'country_code', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Stock exchanges',
                'example_response' => array(
                    'stock_exchanges' => array(
                        array(
                            'id' => 'XNAS',
                            'name' => 'NASDAQ Stock Exchange',
                            'mic' => 'XNAS',
                            'acronym' => 'NASDAQ',
                            'city' => 'New York',
                            'country' => 'United States',
                            'country_code' => 'US',
                            'website' => 'www.nasdaq.com',
                            'first_stock_price_date' => '1998-01-01',
                            'last_stock_price_date' => '2025-04-09'
                        )
                    ),
                    'next_page' => null
                )
            ),
            
            // Technical Indicators
            'SECURITY_BETAS' => array(
                'path' => 'securities/{identifier}/betas',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'start_date', 
                    'end_date', 
                    'frequency', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Beta values between a security and an index',
                'example_response' => array(
                    'betas' => array(
                        array(
                            'date' => '2025-04-01',
                            'value' => 1.25,
                            'r_squared' => 0.76
                        )
                    ),
                    'security' => array(
                        'id' => 'AAPL',
                        'ticker' => 'AAPL',
                        'name' => 'Apple Inc'
                    ),
                    'next_page' => null
                )
            ),
            'RELATIVE_STRENGTH_INDEX' => array(
                'path' => 'securities/{identifier}/prices/technicals/rsi',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'period', 
                    'price_key', 
                    'start_date', 
                    'end_date', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Relative Strength Index technical indicator',
                'example_response' => array(
                    'technicals' => array(
                        array(
                            'date_time' => '2025-04-09T00:00:00.000Z',
                            'rsi' => 56.34
                        )
                    ),
                    'indicator' => array(
                        'name' => 'Relative Strength Index',
                        'indicator_key' => 'rsi',
                        'params' => array(
                            'period' => 14,
                            'price_key' => 'close'
                        ),
                        'categories' => array('Momentum')
                    ),
                    'security' => array(
                        'id' => 'AAPL',
                        'ticker' => 'AAPL',
                        'name' => 'Apple Inc'
                    ),
                    'next_page' => null
                )
            ),
            'SIMPLE_MOVING_AVERAGE' => array(
                'path' => 'securities/{identifier}/prices/technicals/sma',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'period', 
                    'price_key', 
                    'start_date', 
                    'end_date', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Simple Moving Average technical indicator',
                'example_response' => array(
                    'technicals' => array(
                        array(
                            'date_time' => '2025-04-09T00:00:00.000Z',
                            'sma' => 171.35
                        )
                    ),
                    'indicator' => array(
                        'name' => 'Simple Moving Average',
                        'indicator_key' => 'sma',
                        'params' => array(
                            'period' => 20,
                            'price_key' => 'close'
                        ),
                        'categories' => array('Trend', 'Moving Average')
                    ),
                    'security' => array(
                        'id' => 'AAPL',
                        'ticker' => 'AAPL',
                        'name' => 'Apple Inc'
                    ),
                    'next_page' => null
                )
            ),
            'BOLLINGER_BANDS' => array(
                'path' => 'securities/{identifier}/prices/technicals/bbands',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'period', 
                    'standard_deviations', 
                    'price_key', 
                    'start_date', 
                    'end_date', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Bollinger Bands technical indicator',
                'example_response' => array(
                    'technicals' => array(
                        array(
                            'date_time' => '2025-04-09T00:00:00.000Z',
                            'lower_band' => 165.78,
                            'middle_band' => 171.35,
                            'upper_band' => 176.92
                        )
                    ),
                    'indicator' => array(
                        'name' => 'Bollinger Bands',
                        'indicator_key' => 'bbands',
                        'params' => array(
                            'period' => 20,
                            'standard_deviations' => 2,
                            'price_key' => 'close'
                        ),
                        'categories' => array('Trend', 'Volatility', 'Moving Average')
                    ),
                    'security' => array(
                        'id' => 'AAPL',
                        'ticker' => 'AAPL',
                        'name' => 'Apple Inc'
                    ),
                    'next_page' => null
                )
            ),
            
            // Options Data
            'OPTIONS_EXPIRATIONS' => array(
                'path' => 'options/expirations',
                'method' => 'GET',
                'required_params' => array('underlying'),
                'optional_params' => array(
                    'after', 
                    'before'
                ),
                'description' => 'Options expiration dates for a security',
                'example_response' => array(
                    'expirations' => array(
                        '2025-04-18',
                        '2025-04-25',
                        '2025-05-02',
                        '2025-05-16',
                        '2025-06-20',
                        '2025-09-19',
                        '2026-01-16',
                        '2026-06-18',
                        '2027-01-15'
                    )
                )
            ),
            'OPTIONS_CHAIN' => array(
                'path' => 'options/chain',
                'method' => 'GET',
                'required_params' => array('underlying', 'expiration'),
                'optional_params' => array('strike', 'type'),
                'description' => 'Options chain for a security and expiration date',
                'example_response' => array(
                    'chain' => array(
                        array(
                            'id' => 'opt_abcdef12345',
                            'code' => 'AAPL250418C00170000',
                            'ticker' => 'AAPL  250418C00170000',
                            'expiration' => '2025-04-18',
                            'strike' => 170.0,
                            'type' => 'call',
                            'underlying' => 'AAPL',
                            'exercise_style' => 'american',
                            'last_price' => 5.45,
                            'bid' => 5.40,
                            'ask' => 5.50,
                            'change' => 0.30,
                            'volume' => 1242,
                            'open_interest' => 5678,
                            'implied_volatility' => 0.285,
                            'delta' => 0.58,
                            'gamma' => 0.04,
                            'theta' => -0.05,
                            'vega' => 0.12,
                            'close_price' => 5.45,
                            'update_date' => '2025-04-10',
                            'main_exchange' => 'American Options'
                        )
                    )
                )
            ),
            'OPTION_PRICES' => array(
                'path' => 'options/{identifier}/prices',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'start_date', 
                    'end_date', 
                    'source', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'End-of-day option prices for an option contract',
                'example_response' => array(
                    'prices' => array(
                        array(
                            'date' => '2025-04-09',
                            'close' => 5.15,
                            'close_bid' => 5.10,
                            'close_ask' => 5.20,
                            'volume' => 987,
                            'open_interest' => 5640,
                            'implied_volatility' => 0.283,
                            'delta' => 0.57,
                            'gamma' => 0.04,
                            'theta' => -0.05,
                            'vega' => 0.12
                        )
                    ),
                    'option' => array(
                        'id' => 'opt_abcdef12345',
                        'code' => 'AAPL250418C00170000',
                        'ticker' => 'AAPL  250418C00170000',
                        'expiration' => '2025-04-18',
                        'strike' => 170.0,
                        'type' => 'call',
                        'underlying' => 'AAPL'
                    ),
                    'next_page' => null
                )
            ),
            
            // ETF Data
            'ETF_HOLDINGS' => array(
                'path' => 'etfs/{identifier}/holdings',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'holder_date',
                    'page_size', 
                    'next_page'
                ),
                'description' => 'ETF holdings data',
                'example_response' => array(
                    'holdings' => array(
                        array(
                            'etf_id' => 'SPY',
                            'etf_ticker' => 'SPY',
                            'etf_name' => 'SPDR S&P 500 ETF Trust',
                            'holding_id' => 'AAPL',
                            'holding_ticker' => 'AAPL',
                            'holding_name' => 'Apple Inc',
                            'holding_date' => '2025-03-31',
                            'weight_percentage' => 7.25,
                            'share_count' => 147300000,
                            'market_value' => 25286034000
                        )
                    ),
                    'etf' => array(
                        'id' => 'SPY',
                        'name' => 'SPDR S&P 500 ETF Trust',
                        'ticker' => 'SPY'
                    ),
                    'next_page' => null
                )
            ),
            
            // Zacks Data
            'ZACKS_ANALYST_RATINGS' => array(
                'path' => 'companies/{identifier}/zacks/analyst_ratings',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'start_date', 
                    'end_date', 
                    'mean', 
                    'strong_buys', 
                    'buys', 
                    'holds', 
                    'sells', 
                    'strong_sells', 
                    'total', 
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Zacks analyst ratings for a company',
                'example_response' => array(
                    'ratings' => array(
                        array(
                            'date' => '2025-04-09',
                            'mean' => 1.8,
                            'strong_buys' => 12,
                            'buys' => 18,
                            'holds' => 8,
                            'sells' => 1,
                            'strong_sells' => 0,
                            'total' => 39
                        )
                    ),
                    'company' => array(
                        'id' => 'AAPL',
                        'ticker' => 'AAPL',
                        'name' => 'Apple Inc'
                    ),
                    'next_page' => null
                )
            ),
            
            // Institutional Ownership
            'INSTITUTIONAL_OWNERSHIP' => array(
                'path' => 'securities/{identifier}/institutional_ownership',
                'method' => 'GET',
                'required_params' => array('identifier'),
                'optional_params' => array(
                    'date',
                    'page_size', 
                    'next_page'
                ),
                'description' => 'Institutional ownership for a security',
                'example_response' => array(
                    'ownership' => array(
                        array(
                            'owner_cik' => '0001067983',
                            'owner_name' => 'Vanguard Group Inc',
                            'value' => 195378359863,
                            'amount' => 1137792135,
                            'percent_of_class' => 7.25,
                            'filing_date' => '2025-03-31',
                            'report_date' => '2025-03-31'
                        )
                    ),
                    'security' => array(
                        'id' => 'AAPL',
                        'ticker' => 'AAPL',
                        'name' => 'Apple Inc'
                    ),
                    'next_page' => null
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
        
        // Use the provided base_url or default to production API
        if (empty($base_url)) {
            $base_url = 'https://api-v2.intrinio.com';
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
        
        // Add remaining optional parameters
        if (isset($endpoint['optional_params'])) {
            foreach ($endpoint['optional_params'] as $param) {
                if (isset($params[$param])) {
                    $query_params[$param] = $params[$param];
                }
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