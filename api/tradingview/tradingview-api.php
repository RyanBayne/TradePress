<?php
/**
 * TradePress TradingView API
 *
 * Handles connection and functionality for the TradingView platform
 *
 * @package TradePress
 * @subpackage API\TradingView
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress TradingView API class
 */
class TradePress_TradingView_API {
    
    /**
     * Platform metadata
     * @var array
     */
    private $platform_metadata = array(
        'name' => 'TradingView',
        'code' => 'tradingview',
        'type' => 'charting_platform',
        'tier' => 3,
        'status' => 'partner_access',
        'capabilities' => array(
            'charting' => true,
            'technical_analysis' => true,
            'market_data' => true,
            'screener' => true,
            'economic_calendar' => true,
            'earnings_calendar' => true,
            'news' => true,
            'trading_ideas' => true,
            'social_features' => true,
            'widgets' => true,
            'technical_indicators' => true,
            'drawing_tools' => true,
            'alerts' => true,
            'backtesting' => true
        ),
        'data_types' => array(
            'quotes' => true,
            'bars' => true,
            'technical_indicators' => true,
            'screener_data' => true,
            'economic_events' => true,
            'earnings_data' => true,
            'news' => true,
            'trading_ideas' => true,
            'chart_widgets' => true,
            'symbol_info' => true,
            'search' => true,
            'portfolio' => false,
            'orders' => false,
            'positions' => false
        ),
        'rate_limits' => array(
            'requests_per_minute' => 100,
            'requests_per_hour' => 6000,
            'requests_per_day' => 144000
        ),
        'supported_markets' => array(
            'STOCKS' => true,
            'FOREX' => true,
            'CRYPTO' => true,
            'FUTURES' => true,
            'INDICES' => true,
            'BONDS' => true,
            'COMMODITIES' => true,
            'CFD' => true,
            'ECONOMIC' => true
        ),
        'pricing' => array(
            'free_tier' => true,
            'paid_plans' => true,
            'partner_access' => true,
            'widget_free' => true,
            'api_premium' => true,
            'enterprise' => true
        )
    );
    
    /**
     * API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://www.tradingview.com/api';
    
    /**
     * API key
     * @var string
     */
    private $api_key;
    
    /**
     * Debug mode
     * @var bool
     */
    private $debug_mode;
    
    /**
     * Constructor
     */
    public function __construct() {
        $api_settings = get_option('tradepress_api_settings', array());
        $this->api_key = isset($api_settings['tradingview_api_key']) ? $api_settings['tradingview_api_key'] : '';
        $this->debug_mode = isset($api_settings['enable_api_logging']) && $api_settings['enable_api_logging'];
    }
    
    /**
     * Get platform metadata
     * 
     * @return array Platform metadata
     */
    public function get_platform_metadata() {
        return $this->platform_metadata;
    }
    
    /**
     * Get quote data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @return array|WP_Error Quote data or error
     */
    public function get_quote($symbol) {
        return $this->make_request('quotes', 'GET', array('symbols' => $symbol));
    }
    
    /**
     * Get historical data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @param string $period Time period (mapped to from/to)
     * @param string $interval Data interval
     * @return array|WP_Error Historical data or error
     */
    public function get_historical_data($symbol, $period = '1mo', $interval = 'D') {
        $to = time();
        $from_map = array(
            '1d' => $to - 86400,
            '5d' => $to - (5 * 86400),
            '1mo' => $to - (30 * 86400),
            '3mo' => $to - (90 * 86400),
            '6mo' => $to - (180 * 86400),
            '1y' => $to - (365 * 86400)
        );
        
        $from = isset($from_map[$period]) ? $from_map[$period] : $to - (30 * 86400);
        
        $params = array(
            'symbol' => $symbol,
            'resolution' => $interval,
            'from' => $from,
            'to' => $to
        );
        
        return $this->make_request('chart/data', 'GET', $params);
    }
    
    /**
     * Get account balance (not supported)
     * 
     * @return WP_Error Not supported error
     */
    public function get_account_balance() {
        return new WP_Error('not_supported', 'Account data not available via TradingView API');
    }
    
    /**
     * Get portfolio positions (not supported)
     * 
     * @return WP_Error Not supported error
     */
    public function get_portfolio_positions() {
        return new WP_Error('not_supported', 'Portfolio data not available via TradingView API');
    }
    
    /**
     * Get open orders (not supported)
     * 
     * @return WP_Error Not supported error
     */
    public function get_open_orders() {
        return new WP_Error('not_supported', 'Order data not available via TradingView API');
    }
    
    /**
     * Search for symbols
     * 
     * @param string $query Search query
     * @return array|WP_Error Search results or error
     */
    public function search_symbols($query) {
        return $this->make_request('symbols/search', 'GET', array('query' => $query));
    }
    
    /**
     * Get watchlist symbols (not supported)
     * 
     * @return WP_Error Not supported error
     */
    public function get_watchlist_symbols() {
        return new WP_Error('not_supported', 'Watchlist data not available via TradingView API');
    }
    
    /**
     * Get chart data
     *
     * @param string $symbol Stock symbol
     * @param string $interval Time interval
     * @return array|WP_Error Chart data or error
     */
    public function get_chart_data($symbol, $interval = 'D') {
        return $this->get_historical_data($symbol, '1mo', $interval);
    }
    
    /**
     * Get technical indicators
     * 
     * @param string $symbol Stock symbol
     * @param array $indicators List of indicators with parameters
     * @param string $resolution Chart resolution
     * @return array|WP_Error Technical indicator data or error
     */
    public function get_technical_indicators($symbol, $indicators, $resolution = 'D') {
        $to = time();
        $from = $to - (30 * 86400); // 30 days
        
        $params = array(
            'symbol' => $symbol,
            'indicators' => $indicators,
            'resolution' => $resolution,
            'from' => $from,
            'to' => $to
        );
        
        return $this->make_request('technical/indicators', 'GET', $params);
    }
    
    /**
     * Get screener results
     * 
     * @param array $filters Screener filters
     * @param array $options Additional options
     * @return array|WP_Error Screener results or error
     */
    public function get_screener_results($filters, $options = array()) {
        $params = array(
            'filter' => $filters,
            'options' => $options
        );
        
        return $this->make_request('screener/scan', 'POST', $params);
    }
    
    /**
     * Get economic calendar
     * 
     * @param string $from Start date (YYYY-MM-DD)
     * @param string $to End date (YYYY-MM-DD)
     * @param array $options Additional options
     * @return array|WP_Error Economic events or error
     */
    public function get_economic_calendar($from, $to, $options = array()) {
        $params = array_merge(array(
            'from' => $from,
            'to' => $to
        ), $options);
        
        return $this->make_request('economic-calendar', 'GET', $params);
    }
    
    /**
     * Get earnings calendar
     * 
     * @param string $from Start date (YYYY-MM-DD)
     * @param string $to End date (YYYY-MM-DD)
     * @param array $options Additional options
     * @return array|WP_Error Earnings events or error
     */
    public function get_earnings_calendar($from, $to, $options = array()) {
        $params = array_merge(array(
            'from' => $from,
            'to' => $to
        ), $options);
        
        return $this->make_request('earnings-calendar', 'GET', $params);
    }
    
    /**
     * Get news articles
     * 
     * @param string $symbol Symbol (optional)
     * @param array $options Additional options
     * @return array|WP_Error News articles or error
     */
    public function get_news($symbol = null, $options = array()) {
        $params = $options;
        if ($symbol) {
            $params['symbol'] = $symbol;
        }
        
        return $this->make_request('news', 'GET', $params);
    }
    
    /**
     * Get trading ideas
     * 
     * @param string $symbol Symbol (optional)
     * @param array $options Additional options
     * @return array|WP_Error Trading ideas or error
     */
    public function get_trading_ideas($symbol = null, $options = array()) {
        $params = $options;
        if ($symbol) {
            $params['symbol'] = $symbol;
        }
        
        return $this->make_request('ideas', 'GET', $params);
    }
    
    /**
     * Generate chart widget HTML
     *
     * @param string $symbol Stock symbol
     * @param array $params Optional widget parameters
     * @return string HTML for TradingView widget
     */
    public function get_chart_widget($symbol, $params = array()) {
        $defaults = array(
            'width' => 800,
            'height' => 500,
            'interval' => 'D',
            'theme' => 'light'
        );
        
        $params = array_merge($defaults, $params);
        $params['symbol'] = $symbol;
        
        // Generate widget HTML (simplified version)
        $widget_id = 'tradingview_' . uniqid();
        
        $html = '<div class="tradingview-widget-container" id="' . $widget_id . '">';
        $html .= '<div class="tradingview-widget-container__widget"></div>';
        $html .= '</div>';
        
        $script = '<script type="text/javascript">';
        $script .= 'new TradingView.widget(' . json_encode(array(
            'autosize' => false,
            'width' => $params['width'],
            'height' => $params['height'],
            'symbol' => $params['symbol'],
            'interval' => $params['interval'],
            'timezone' => 'Etc/UTC',
            'theme' => $params['theme'],
            'style' => '1',
            'locale' => 'en',
            'toolbar_bg' => '#f1f3f6',
            'enable_publishing' => false,
            'allow_symbol_change' => true,
            'container_id' => $widget_id
        )) . ');';
        $script .= '</script>';
        
        return $html . $script;
    }
    
    /**
     * Make API request to TradingView
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $params Request parameters
     * @return array|WP_Error Response data or error
     */
    private function make_request($endpoint, $method = 'GET', $params = array()) {
        // Demo mode - return sample data
        if (empty($this->api_key) || defined('TRADEPRESS_DEMO_MODE')) {
            return $this->get_demo_data($endpoint);
        }
        
        $url = $this->api_base_url . '/' . $endpoint;
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            )
        );
        
        if ($method === 'GET' && !empty($params)) {
            $url = add_query_arg($params, $url);
        } elseif (!empty($params)) {
            $args['body'] = json_encode($params);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code < 200 || $response_code >= 300) {
            return new WP_Error(
                'tradingview_api_error',
                sprintf(__('TradingView API error: %s', 'tradepress'), $response_body),
                array('status' => $response_code)
            );
        }
        
        $data = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_parse_error',
                __('Failed to parse TradingView API response', 'tradepress')
            );
        }
        
        return $data;
    }
    
    /**
     * Get demo data for endpoints
     * 
     * @param string $endpoint API endpoint
     * @return array Sample data
     */
    private function get_demo_data($endpoint) {
        switch ($endpoint) {
            case 'quotes':
                return array(
                    'AAPL' => array(
                        'last' => 179.92,
                        'change' => 1.07,
                        'change_percent' => 0.6,
                        'volume' => 46378900,
                        'previous_close' => 178.85,
                        'open' => 179.25,
                        'high' => 180.45,
                        'low' => 178.76
                    )
                );
                
            case 'chart/data':
                return array(
                    's' => 'ok',
                    't' => array(time() - 172800, time() - 86400, time()),
                    'o' => array(177.92, 179.25, 178.08),
                    'h' => array(179.25, 180.45, 179.43),
                    'l' => array(177.45, 178.76, 177.85),
                    'c' => array(178.10, 179.92, 178.85),
                    'v' => array(43265400, 46378900, 45870100)
                );
                
            case 'symbols/search':
                return array(
                    array(
                        'symbol' => 'AAPL',
                        'full_name' => 'NASDAQ:AAPL',
                        'description' => 'APPLE INC',
                        'exchange' => 'NASDAQ',
                        'type' => 'stock'
                    )
                );
                
            case 'news':
                return array(
                    'data' => array(
                        array(
                            'id' => '12345',
                            'title' => 'Apple Reports Strong Quarterly Results',
                            'published_at' => date('Y-m-d\TH:i:s\Z'),
                            'source' => 'TradingView',
                            'summary' => 'Apple Inc. reported strong quarterly results...',
                            'tags' => array('AAPL', 'earnings')
                        )
                    )
                );
                
            default:
                return array();
        }
    }
    
    /**
     * Get platform capabilities for directive support
     * 
     * @return array Platform capabilities
     */
    public function get_platform_capabilities() {
        return array(
            'D1_ADX' => true,
            'D17_RSI' => true,
            'D22_Volume' => true,
            'D4_CCI' => true,
            'D10_MACD' => true,
            'quotes' => true,
            'historical_data' => true,
            'technical_indicators' => true,
            'charting' => true,
            'screener' => true,
            'economic_calendar' => true,
            'earnings_calendar' => true,
            'news' => true,
            'trading_ideas' => true,
            'widgets' => true,
            'social_features' => true,
            'portfolio_data' => false,
            'trading' => false
        );
    }
}
