<?php
/**
 * TradePress - Finnhub API Integration
 *
 * @package TradePress/API/Finnhub
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Finnhub_API extends TradePress_Base_API {
    
    private $api_base_url = 'https://finnhub.io/api/v1';
    private $api_key;
    
    /**
     * Platform metadata
     *
     * @var array
     */
    private $platform_meta = array(
        'name' => 'Finnhub',
        'code' => 'finnhub',
        'type' => 'market_data',
        'tier' => 1,
        'status' => 'active',
        'capabilities' => array(
            'real_time_data' => true,
            'historical_data' => true,
            'news_data' => true,
            'earnings_data' => true,
            'technical_indicators' => true,
            'fundamental_data' => true,
            'insider_trading' => true,
            'recommendation_trends' => true,
            'price_targets' => true,
            'patterns' => true,
            'support_resistance' => true
        ),
        'data_types' => array(
            'quote' => 'quote',
            'bars' => 'stock/candle',
            'volume' => 'stock/candle',
            'news' => 'company-news',
            'earnings' => 'calendar/earnings',
            'fundamentals' => 'stock/metric',
            'insider_trading' => 'stock/insider-transactions',
            'recommendation' => 'stock/recommendation',
            'price_target' => 'stock/price-target',
            'patterns' => 'scan/pattern',
            'support_resistance' => 'scan/support-resistance',
            'technical_indicator' => 'indicator',
            'aggregate_indicators' => 'stock/aggregate-indicators'
        ),
        'rate_limits' => array(
            'per_second_free' => 1,
            'per_minute_free' => 60,
            'per_second_premium' => 30,
            'per_minute_premium' => 300,
            'burst' => 5
        ),
        'supported_markets' => array('US', 'Global'),
        'pricing' => array(
            'free_tier' => true,
            'min_plan' => 'Basic',
            'cost_per_month' => 59.99
        )
    );
    
    public function __construct($provider_id = 'finnhub', $args = array()) {
        if (isset($args['api_key'])) {
            $this->api_key = $args['api_key'];
        } else {
            $this->api_key = get_option('TradePress_api_finnhub_key', '');
        }
        
        parent::__construct($provider_id, $args);
    }
    
    /**
     * Get platform metadata
     *
     * @return array Platform metadata
     */
    public function get_platform_meta() {
        return $this->platform_meta;
    }
    
    /**
     * Get platform capabilities
     *
     * @return array Platform capabilities
     */
    public function get_capabilities() {
        return $this->platform_meta['capabilities'];
    }
    
    /**
     * Check if platform supports specific data type
     *
     * @param string $data_type Data type to check
     * @return bool True if supported
     */
    public function supports_data_type($data_type) {
        return isset($this->platform_meta['data_types'][$data_type]);
    }
    
    /**
     * Get endpoint for data type
     *
     * @param string $data_type Data type
     * @return string|false Endpoint name or false
     */
    public function get_data_type_endpoint($data_type) {
        return $this->supports_data_type($data_type) ? $this->platform_meta['data_types'][$data_type] : false;
    }
    
    public function test_connection() {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('API key is not configured', 'tradepress'));
        }
        
        $result = $this->make_request('quote', ['symbol' => 'AAPL']);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        if (isset($result['error'])) {
            return new WP_Error('api_error', $result['error']);
        }
        
        return true;
    }
    
    public function make_request($endpoint, $params = array(), $method = 'GET') {
        if (empty($this->api_key)) {
            return new WP_Error('missing_api_key', __('API key is not configured', 'tradepress'));
        }
        
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/api-logging.php';
        
        $call_entry_id = TradePress_API_Logging::log_call(
            'finnhub',
            $endpoint,
            'GET',
            'pending',
            __FILE__,
            __LINE__,
            sprintf(__('Finnhub API call to %s', 'tradepress'), $endpoint),
            '',
            86400
        );
        
        $url = $this->api_base_url . '/' . $endpoint;
        $params['token'] = $this->api_key;
        $url = add_query_arg($params, $url);
        
        TradePress_API_Logging::track_endpoint(
            $call_entry_id,
            'finnhub',
            $endpoint,
            $params
        );
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ));
        
        if (is_wp_error($response)) {
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'http_error',
                $response->get_error_message(),
                __FUNCTION__
            );
            
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'Request failed: ' . $response->get_error_message(),
                'error'
            );
            
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            $error_message = wp_remote_retrieve_response_message($response);
            
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'http_' . $response_code,
                $error_message,
                __FUNCTION__
            );
            
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'HTTP Error ' . $response_code . ': ' . $error_message,
                'error'
            );
            
            return new WP_Error(
                'http_error',
                sprintf(__('HTTP Error: %d %s', 'tradepress'), $response_code, $error_message)
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'json_parse_error',
                'Failed to parse JSON response: ' . json_last_error_msg(),
                __FUNCTION__
            );
            
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'Response parsing failed',
                'error'
            );
            
            return new WP_Error('parse_error', __('Failed to parse API response', 'tradepress'));
        }
        
        if (isset($data['error'])) {
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'api_error',
                $data['error'],
                __FUNCTION__
            );
            
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'API Error: ' . $data['error'],
                'error'
            );
            
            return new WP_Error('api_error', $data['error']);
        }
        
        TradePress_API_Logging::add_meta(
            $call_entry_id,
            'response_size',
            strlen($body)
        );
        
        TradePress_API_Logging::update_call_outcome(
            $call_entry_id,
            'Success',
            'success'
        );
        
        return $data;
    }
    
    public function get_quote($symbol) {
        $response = $this->make_request('quote', ['symbol' => $symbol]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'symbol' => $symbol,
            'price' => isset($response['c']) ? (float)$response['c'] : 0,
            'open' => isset($response['o']) ? (float)$response['o'] : 0,
            'high' => isset($response['h']) ? (float)$response['h'] : 0,
            'low' => isset($response['l']) ? (float)$response['l'] : 0,
            'previous_close' => isset($response['pc']) ? (float)$response['pc'] : 0,
            'change' => isset($response['d']) ? (float)$response['d'] : 0,
            'change_percent' => isset($response['dp']) ? (float)$response['dp'] : 0,
            'timestamp' => isset($response['t']) ? $response['t'] : time()
        );
    }
    
    public function get_candles($symbol, $resolution = 'D', $from = null, $to = null, $count = 200) {
        if (!$from) {
            $from = strtotime('-' . $count . ' days');
        }
        if (!$to) {
            $to = time();
        }
        
        $params = array(
            'symbol' => $symbol,
            'resolution' => $resolution,
            'from' => $from,
            'to' => $to
        );
        
        $response = $this->make_request('stock/candle', $params);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if (!isset($response['c']) || empty($response['c'])) {
            return new WP_Error('no_data', __('No historical data available', 'tradepress'));
        }
        
        $candles = array();
        $count = count($response['c']);
        
        for ($i = 0; $i < $count; $i++) {
            $candles[] = array(
                'timestamp' => $response['t'][$i],
                'open' => $response['o'][$i],
                'high' => $response['h'][$i],
                'low' => $response['l'][$i],
                'close' => $response['c'][$i],
                'volume' => $response['v'][$i]
            );
        }
        
        return $candles;
    }
    
    public function get_technical_indicator($symbol, $indicator, $resolution = 'D', $timeperiod = 14, $from = null, $to = null) {
        if (!$from) {
            $from = strtotime('-200 days');
        }
        if (!$to) {
            $to = time();
        }
        
        $params = array(
            'symbol' => $symbol,
            'resolution' => $resolution,
            'from' => $from,
            'to' => $to,
            'indicator' => strtolower($indicator),
            'timeperiod' => $timeperiod
        );
        
        return $this->make_request('indicator', $params);
    }
    
    public function get_moving_average($symbol, $period = 20, $resolution = 'D', $from = null, $to = null) {
        return $this->get_technical_indicator($symbol, 'sma', $resolution, $period, $from, $to);
    }
    
    public function get_rsi($symbol, $period = 14, $resolution = 'D', $from = null, $to = null) {
        return $this->get_technical_indicator($symbol, 'rsi', $resolution, $period, $from, $to);
    }
    
    public function get_macd($symbol, $resolution = 'D', $from = null, $to = null) {
        return $this->get_technical_indicator($symbol, 'macd', $resolution, 12, $from, $to);
    }
}