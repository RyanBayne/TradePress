<?php
/**
 * TradePress Gemini API Endpoints
 *
 * Defines endpoints and parameters for the Gemini cryptocurrency exchange
 *
 * @package TradePress
 * @subpackage API\GeminiAPI
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Gemini API Endpoints class
 */
class TradePress_GeminiAPI_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            // Public API Endpoints
            'symbols' => array(
                'method' => 'GET',
                'endpoint' => '/v1/symbols',
                'auth_required' => false,
                'description' => 'Get all available symbols'
            ),
            'ticker' => array(
                'method' => 'GET',
                'endpoint' => '/v1/pubticker/:symbol',
                'auth_required' => false,
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'description' => 'The symbol to get ticker information for'
                    )
                ),
                'description' => 'Get ticker information for a symbol'
            ),
            'candles' => array(
                'method' => 'GET',
                'endpoint' => '/v2/candles/:symbol/:timeframe',
                'auth_required' => false,
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'description' => 'The symbol to get candlestick data for'
                    ),
                    'timeframe' => array(
                        'required' => true,
                        'options' => array('1m', '5m', '15m', '30m', '1h', '6h', '1d'),
                        'description' => 'The timeframe for the candlestick data'
                    )
                ),
                'description' => 'Get candlestick data for a symbol and timeframe'
            ),
            'orderbook' => array(
                'method' => 'GET',
                'endpoint' => '/v1/book/:symbol',
                'auth_required' => false,
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'description' => 'The symbol to get order book data for'
                    ),
                    'limit_bids' => array(
                        'required' => false,
                        'description' => 'Limit the number of bids returned'
                    ),
                    'limit_asks' => array(
                        'required' => false,
                        'description' => 'Limit the number of asks returned'
                    )
                ),
                'description' => 'Get order book data for a symbol'
            ),
            'trades' => array(
                'method' => 'GET',
                'endpoint' => '/v1/trades/:symbol',
                'auth_required' => false,
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'description' => 'The symbol to get trade data for'
                    ),
                    'limit_trades' => array(
                        'required' => false,
                        'description' => 'Limit the number of trades returned'
                    ),
                    'timestamp' => array(
                        'required' => false,
                        'description' => 'Only return trades after this timestamp'
                    )
                ),
                'description' => 'Get recent trades for a symbol'
            ),
            
            // Authenticated API Endpoints
            'balances' => array(
                'method' => 'POST',
                'endpoint' => '/v1/balances',
                'auth_required' => true,
                'description' => 'Get account balances'
            ),
            'new_order' => array(
                'method' => 'POST',
                'endpoint' => '/v1/order/new',
                'auth_required' => true,
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'description' => 'The symbol for the new order'
                    ),
                    'amount' => array(
                        'required' => true,
                        'description' => 'The quantity to buy or sell'
                    ),
                    'price' => array(
                        'required' => true,
                        'description' => 'The price at which to buy or sell'
                    ),
                    'side' => array(
                        'required' => true,
                        'options' => array('buy', 'sell'),
                        'description' => 'Order side (buy or sell)'
                    ),
                    'type' => array(
                        'required' => true,
                        'options' => array('exchange limit', 'exchange market'),
                        'description' => 'Order type'
                    ),
                    'options' => array(
                        'required' => false,
                        'description' => 'Additional order execution options'
                    )
                ),
                'description' => 'Create a new order'
            ),
            'cancel_order' => array(
                'method' => 'POST',
                'endpoint' => '/v1/order/cancel',
                'auth_required' => true,
                'params' => array(
                    'order_id' => array(
                        'required' => true,
                        'description' => 'The ID of the order to cancel'
                    )
                ),
                'description' => 'Cancel an existing order'
            ),
            'active_orders' => array(
                'method' => 'POST',
                'endpoint' => '/v1/orders',
                'auth_required' => true,
                'description' => 'Get active orders'
            ),
            'past_trades' => array(
                'method' => 'POST',
                'endpoint' => '/v1/mytrades',
                'auth_required' => true,
                'params' => array(
                    'symbol' => array(
                        'required' => true,
                        'description' => 'The symbol to get trade data for'
                    ),
                    'limit_trades' => array(
                        'required' => false,
                        'description' => 'Limit the number of trades returned'
                    ),
                    'timestamp' => array(
                        'required' => false,
                        'description' => 'Only return trades after this timestamp'
                    )
                ),
                'description' => 'Get past trades for a symbol'
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
        
        // Set default base URL if not provided
        if (empty($base_url)) {
            $base_url = 'https://api.gemini.com';
        }
        
        $url = $base_url . $endpoint['endpoint'];
        
        // Replace URL parameters (those with :param format)
        if (preg_match_all('/:([a-zA-Z0-9_]+)/', $url, $matches)) {
            foreach ($matches[1] as $param_name) {
                if (isset($params[$param_name])) {
                    $url = str_replace(':' . $param_name, urlencode($params[$param_name]), $url);
                    // Remove the parameter from the array so it's not added as a query parameter
                    unset($params[$param_name]);
                } else {
                    // If a required path parameter is missing, return empty URL
                    if (!empty($endpoint['params'][$param_name]['required'])) {
                        return '';
                    }
                }
            }
        }
        
        // For GET requests, add remaining parameters as query string
        if ($endpoint['method'] === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}
