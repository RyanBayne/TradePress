<?php
/**
 * TradePress Yahoo Finance API Endpoints
 *
 * Defines endpoints and parameters for the Yahoo Finance service
 * Note: Yahoo Finance doesn't have an official API, using unofficial endpoints
 * 
 * @package TradePress
 * @subpackage API\Yahoo
 * @version 1.0.0
 * @since 2025-04-10
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Yahoo Finance API Endpoints class
 */
class TradePress_Yahoo_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Quote Data
            'quote' => array(
                'path' => 'v8/finance/chart/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('interval', 'range', 'includePrePost', 'events'),
                'method' => 'GET',
                'description' => 'Get real-time stock quote and basic chart data'
            ),
            'quotes' => array(
                'path' => 'v7/finance/quote',
                'required_params' => array('symbols'),
                'optional_params' => array('formatted', 'crumb'),
                'method' => 'GET',
                'description' => 'Get quotes for multiple symbols'
            ),
            
            // Historical Data
            'historical' => array(
                'path' => 'v8/finance/chart/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('period1', 'period2', 'interval', 'includePrePost', 'events'),
                'method' => 'GET',
                'description' => 'Get historical price data'
            ),
            
            // Search
            'search' => array(
                'path' => 'v1/finance/search',
                'required_params' => array('q'),
                'optional_params' => array('lang', 'region', 'quotesCount', 'newsCount'),
                'method' => 'GET',
                'description' => 'Search for stocks, ETFs, and other securities'
            ),
            
            // Options
            'options' => array(
                'path' => 'v7/finance/options/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('date'),
                'method' => 'GET',
                'description' => 'Get options chain data'
            ),
            
            // Fundamentals
            'fundamentals' => array(
                'path' => 'v10/finance/quoteSummary/{symbol}',
                'required_params' => array('symbol'),
                'optional_params' => array('modules'),
                'method' => 'GET',
                'description' => 'Get fundamental data including financials, statistics, etc.'
            ),
            
            // News
            'news' => array(
                'path' => 'v1/finance/search',
                'required_params' => array('q'),
                'optional_params' => array('newsCount', 'quotesCount'),
                'method' => 'GET',
                'description' => 'Get news for a symbol'
            ),
            
            // Market Summary
            'market_summary' => array(
                'path' => 'v6/finance/quote/marketSummary',
                'optional_params' => array('region'),
                'method' => 'GET',
                'description' => 'Get market summary data'
            ),
            
            // Trending
            'trending' => array(
                'path' => 'v1/finance/trending/{region}',
                'required_params' => array('region'),
                'method' => 'GET',
                'description' => 'Get trending stocks for a region'
            ),
            
            // Screener
            'screener' => array(
                'path' => 'v1/finance/screener/predefined/saved',
                'required_params' => array('scrIds'),
                'optional_params' => array('count'),
                'method' => 'GET',
                'description' => 'Get screener results'
            ),
            
            // Spark (Mini Charts)
            'spark' => array(
                'path' => 'v8/finance/spark',
                'required_params' => array('symbols'),
                'optional_params' => array('range', 'interval'),
                'method' => 'GET',
                'description' => 'Get spark chart data for multiple symbols'
            ),
            
            // Recommendations
            'recommendations' => array(
                'path' => 'v6/finance/recommendationsbysymbol/{symbol}',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get stock recommendations'
            ),
            
            // Insights
            'insights' => array(
                'path' => 'ws/insights/v2/finance/insights',
                'required_params' => array('symbol'),
                'method' => 'GET',
                'description' => 'Get stock insights and analysis'
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
        
        // Use provided base URL or default
        if (empty($base_url)) {
            $base_url = 'https://query1.finance.yahoo.com';
        }
        $base_url = rtrim($base_url, '/') . '/';
        
        // Build the path with parameter substitution
        $path = $endpoint['path'];
        
        // Process required parameters in path
        if (!empty($endpoint['required_params'])) {
            foreach ($endpoint['required_params'] as $param) {
                if (isset($params[$param])) {
                    $path = str_replace('{' . $param . '}', $params[$param], $path);
                    unset($params[$param]);
                } else {
                    // For some endpoints, missing params might be OK
                    if (strpos($path, '{' . $param . '}') !== false) {
                        return ''; // Missing required parameter
                    }
                }
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
        
        // Add any remaining parameters
        foreach ($params as $key => $value) {
            if (!isset($query_params[$key])) {
                $query_params[$key] = $value;
            }
        }
        
        // Build the URL
        $url = $base_url . $path;
        
        // Add query parameters if any
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        
        return $url;
    }
}