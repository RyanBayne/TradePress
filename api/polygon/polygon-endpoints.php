<?php
/**
 * TradePress Polygon.io API Endpoints
 *
 * Defines endpoints and parameters for the Polygon.io market data service
 *
 * @package TradePress
 * @subpackage API\Polygon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Polygon.io API Endpoints class
 */
class TradePress_Polygon_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Stock Market Data Endpoints
            'TICKER_DETAILS' => array(
                'path' => 'v3/reference/tickers/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array('date'),
                'description' => 'Get ticker details',
            ),
            'TICKERS' => array(
                'path' => 'v3/reference/tickers',
                'required_params' => array(),
                'optional_params' => array('ticker', 'type', 'market', 'exchange', 'cusip', 'cik', 'sort', 'order', 'limit', 'active'),
                'description' => 'Get tickers',
            ),
            'DAILY_OPEN_CLOSE' => array(
                'path' => 'v1/open-close/{ticker}/{date}',
                'required_params' => array('ticker', 'date'),
                'optional_params' => array('adjusted'),
                'description' => 'Get daily open/close data',
            ),
            'AGGREGATES' => array(
                'path' => 'v2/aggs/ticker/{ticker}/range/{multiplier}/{timespan}/{from}/{to}',
                'required_params' => array('ticker', 'multiplier', 'timespan', 'from', 'to'),
                'optional_params' => array('adjusted', 'sort', 'limit'),
                'description' => 'Get aggregated bars for a ticker over a given date range',
                'timespans' => array('minute', 'hour', 'day', 'week', 'month', 'quarter', 'year'),
            ),
            'GROUPED_DAILY' => array(
                'path' => 'v2/aggs/grouped/locale/us/market/stocks/{date}',
                'required_params' => array('date'),
                'optional_params' => array('adjusted'),
                'description' => 'Get daily OHLC for the entire market',
            ),
            'PREVIOUS_CLOSE' => array(
                'path' => 'v2/aggs/ticker/{ticker}/prev',
                'required_params' => array('ticker'),
                'optional_params' => array('adjusted'),
                'description' => 'Get previous day close data',
            ),
            'QUOTES' => array(
                'path' => 'v3/quotes/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array('timestamp', 'order', 'limit', 'sort'),
                'description' => 'Get NBBO quotes for a ticker',
            ),
            'LAST_QUOTE' => array(
                'path' => 'v2/last/nbbo/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array(),
                'description' => 'Get the most recent quote for a ticker',
            ),
            'NEWS' => array(
                'path' => 'v2/reference/news',
                'required_params' => array(),
                'optional_params' => array('ticker', 'published_utc', 'order', 'limit', 'sort'),
                'description' => 'Get news articles',
            ),
            
            // Additional Stock Market Data Endpoints
            'TRADES' => array(
                'path' => 'v3/trades/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array('timestamp', 'order', 'limit', 'sort'),
                'description' => 'Get trades for a ticker',
            ),
            'LAST_TRADE' => array(
                'path' => 'v2/last/trade/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array(),
                'description' => 'Get the most recent trade for a ticker',
            ),
            'TICKER_TYPES' => array(
                'path' => 'v3/reference/tickers/types',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'Get all ticker types',
            ),
            'TICKER_EVENTS' => array(
                'path' => 'v3/reference/ticker-events',
                'required_params' => array(),
                'optional_params' => array('ticker', 'type', 'order', 'limit'),
                'description' => 'Get ticker events like name changes',
            ),
            'MARKET_STATUS' => array(
                'path' => 'v1/marketstatus/now',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'Get current market status',
            ),
            'MARKET_HOLIDAYS' => array(
                'path' => 'v1/marketstatus/upcoming',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'Get upcoming market holidays',
            ),
            'SNAPSHOT_ALL_TICKERS' => array(
                'path' => 'v2/snapshot/locale/us/markets/stocks/tickers',
                'required_params' => array(),
                'optional_params' => array('tickers', 'include_otc'),
                'description' => 'Get snapshots for multiple tickers',
            ),
            'SNAPSHOT_TICKER' => array(
                'path' => 'v2/snapshot/locale/us/markets/stocks/tickers/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array(),
                'description' => 'Get snapshot for a single ticker',
            ),
            'STOCK_SPLITS' => array(
                'path' => 'v3/reference/splits',
                'required_params' => array(),
                'optional_params' => array('ticker', 'execution_date', 'reverse_split', 'order', 'limit', 'sort'),
                'description' => 'Get stock splits',
            ),
            'STOCK_DIVIDENDS' => array(
                'path' => 'v3/reference/dividends',
                'required_params' => array(),
                'optional_params' => array('ticker', 'ex_dividend_date', 'record_date', 'declaration_date', 'pay_date', 'order', 'limit', 'sort'),
                'description' => 'Get stock dividends',
            ),
            'STOCK_FINANCIALS' => array(
                'path' => 'v2/reference/financials/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array('limit', 'sort', 'type', 'timeframe'),
                'description' => 'Get financial data for a ticker',
            ),
            
            // Options Market Data Endpoints
            'OPTIONS_CONTRACTS' => array(
                'path' => 'v3/reference/options/contracts',
                'required_params' => array(),
                'optional_params' => array('ticker', 'underlying_ticker', 'contract_type', 'expiration_date', 'as_of', 'strike_price', 'order', 'limit', 'sort'),
                'description' => 'Get options contracts',
            ),
            'OPTIONS_CONTRACT_DETAILS' => array(
                'path' => 'v3/reference/options/contracts/{options_ticker}',
                'required_params' => array('options_ticker'),
                'optional_params' => array('as_of'),
                'description' => 'Get details for an options contract',
            ),
            'OPTIONS_CHAIN' => array(
                'path' => 'v3/snapshot/options/{underlying_asset}/{expiration_date}',
                'required_params' => array('underlying_asset', 'expiration_date'),
                'optional_params' => array('strike_price', 'contract_type'),
                'description' => 'Get the options chain for an underlying asset on a specific expiration date',
            ),
            'OPTIONS_SNAPSHOT' => array(
                'path' => 'v3/snapshot/options/{options_ticker}',
                'required_params' => array('options_ticker'),
                'optional_params' => array(),
                'description' => 'Get snapshot data for an options contract',
            ),
            'OPTIONS_LAST_TRADE' => array(
                'path' => 'v2/last/trade/{options_ticker}',
                'required_params' => array('options_ticker'),
                'optional_params' => array(),
                'description' => 'Get the last trade for an options contract',
            ),
            
            // Forex Data Endpoints
            'FOREX_CURRENCIES' => array(
                'path' => 'v3/reference/currencies',
                'required_params' => array(),
                'optional_params' => array('symbol', 'search', 'limit'),
                'description' => 'Get list of supported forex currencies',
            ),
            'FOREX_AGGREGATES' => array(
                'path' => 'v2/aggs/ticker/{forex_ticker}/range/{multiplier}/{timespan}/{from}/{to}',
                'required_params' => array('forex_ticker', 'multiplier', 'timespan', 'from', 'to'),
                'optional_params' => array('adjusted', 'sort', 'limit'),
                'description' => 'Get aggregated bars for forex over a given date range',
                'timespans' => array('minute', 'hour', 'day', 'week', 'month', 'quarter', 'year'),
            ),
            'FOREX_GROUPED_DAILY' => array(
                'path' => 'v2/aggs/grouped/locale/global/market/fx/{date}',
                'required_params' => array('date'),
                'optional_params' => array('adjusted'),
                'description' => 'Get daily OHLC for forex market',
            ),
            'FOREX_PREVIOUS_CLOSE' => array(
                'path' => 'v2/aggs/ticker/{forex_ticker}/prev',
                'required_params' => array('forex_ticker'),
                'optional_params' => array('adjusted'),
                'description' => 'Get previous day close data for forex',
            ),
            'FOREX_SNAPSHOT' => array(
                'path' => 'v2/snapshot/locale/global/markets/forex/tickers',
                'required_params' => array(),
                'optional_params' => array('tickers'),
                'description' => 'Get snapshots for forex tickers',
            ),
            
            // Crypto Data Endpoints
            'CRYPTO_TICKERS' => array(
                'path' => 'v3/reference/tickers',
                'required_params' => array(),
                'optional_params' => array('ticker', 'market', 'type', 'order', 'limit', 'sort'),
                'description' => 'Get list of crypto tickers',
                'default_params' => array('market' => 'crypto'),
            ),
            'CRYPTO_AGGREGATES' => array(
                'path' => 'v2/aggs/ticker/{crypto_ticker}/range/{multiplier}/{timespan}/{from}/{to}',
                'required_params' => array('crypto_ticker', 'multiplier', 'timespan', 'from', 'to'),
                'optional_params' => array('adjusted', 'sort', 'limit'),
                'description' => 'Get aggregated bars for crypto over a given date range',
                'timespans' => array('minute', 'hour', 'day', 'week', 'month', 'quarter', 'year'),
            ),
            'CRYPTO_GROUPED_DAILY' => array(
                'path' => 'v2/aggs/grouped/locale/global/market/crypto/{date}',
                'required_params' => array('date'),
                'optional_params' => array('adjusted'),
                'description' => 'Get daily OHLC for crypto market',
            ),
            'CRYPTO_PREVIOUS_CLOSE' => array(
                'path' => 'v2/aggs/ticker/{crypto_ticker}/prev',
                'required_params' => array('crypto_ticker'),
                'optional_params' => array('adjusted'),
                'description' => 'Get previous day close data for crypto',
            ),
            'CRYPTO_SNAPSHOT_ALL' => array(
                'path' => 'v2/snapshot/locale/global/markets/crypto/tickers',
                'required_params' => array(),
                'optional_params' => array('tickers'),
                'description' => 'Get snapshots for all crypto tickers',
            ),
            'CRYPTO_SNAPSHOT_TICKER' => array(
                'path' => 'v2/snapshot/locale/global/markets/crypto/tickers/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array(),
                'description' => 'Get snapshot for a single crypto ticker',
            ),
            'CRYPTO_LAST_TRADE' => array(
                'path' => 'v1/last/crypto/{from}/{to}',
                'required_params' => array('from', 'to'),
                'optional_params' => array(),
                'description' => 'Get last trade for a crypto pair',
            ),
            
            // Technical Indicators
            'SMA' => array(
                'path' => 'v1/indicators/sma/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array('timespan', 'adjusted', 'window', 'series_type', 'order', 'limit'),
                'description' => 'Simple Moving Average (SMA) indicator',
            ),
            'EMA' => array(
                'path' => 'v1/indicators/ema/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array('timespan', 'adjusted', 'window', 'series_type', 'order', 'limit'),
                'description' => 'Exponential Moving Average (EMA) indicator',
            ),
            'MACD' => array(
                'path' => 'v1/indicators/macd/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array('timespan', 'adjusted', 'series_type', 'long_window', 'short_window', 'signal_window', 'order', 'limit'),
                'description' => 'Moving Average Convergence/Divergence (MACD) indicator',
            ),
            'RSI' => array(
                'path' => 'v1/indicators/rsi/{ticker}',
                'required_params' => array('ticker'),
                'optional_params' => array('timespan', 'adjusted', 'window', 'series_type', 'order', 'limit'),
                'description' => 'Relative Strength Index (RSI) indicator',
            ),

            // Reference Data
            'EXCHANGES' => array(
                'path' => 'v3/reference/exchanges',
                'required_params' => array(),
                'optional_params' => array('asset_class', 'locale'),
                'description' => 'Get list of exchanges',
            ),
            'MARKET_INDICES' => array(
                'path' => 'v3/reference/indices',
                'required_params' => array(),
                'optional_params' => array('ticker', 'market', 'order', 'limit', 'sort'),
                'description' => 'Get market indices',
            ),
            'TICKER_DETAILS_VENDING' => array(
                'path' => 'v3/reference/tickers/vending',
                'required_params' => array(),
                'optional_params' => array('ticker', 'type', 'market', 'date', 'search', 'active', 'order', 'limit', 'sort'),
                'description' => 'Get detailed company information',
            ),
            'TICKER_NEWS' => array(
                'path' => 'v2/reference/news',
                'required_params' => array(),
                'optional_params' => array('ticker', 'published_utc', 'order', 'limit', 'sort'),
                'description' => 'Get news articles for a ticker',
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
            $base_url = 'https://api.polygon.io';
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
        
        // Add API key from options
        $api_key = get_option('tradepress_polygon_api_key', '');
        $query_params['apiKey'] = $api_key;
        
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
