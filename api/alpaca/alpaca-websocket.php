<?php
/**
 * TradePress Alpaca WebSocket Client
 *
 * Handles connections to Alpaca's WebSocket streams for real-time market data
 * API Documentation: https://docs.alpaca.markets/docs/streaming-market-data
 * 
 * @package TradePress
 * @subpackage API\Alpaca
 * @version 1.0.0
 * @since 2025-04-16
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Alpaca WebSocket Client class
 * 
 * Provides functionality to connect to Alpaca's WebSocket streams for real-time market data
 */
class TradePress_Alpaca_WebSocket {
    
    /**
     * WebSocket stream URLs
     * 
     * @var array
     */
    private static $websocket_urls = array(
        'market_data' => array(
            'stocks' => 'wss://stream.data.alpaca.markets/v2/iex',
            'stocks_all_exchanges' => 'wss://stream.data.alpaca.markets/v2/sip',
            'crypto' => 'wss://stream.data.alpaca.markets/v2/crypto',
            'options' => 'wss://stream.data.alpaca.markets/v2/options',
        ),
        'account_updates' => 'wss://api.alpaca.markets/stream',
        'paper_account_updates' => 'wss://paper-api.alpaca.markets/stream'
    );
    
    /**
     * Connection state
     * 
     * @var boolean
     */
    private $is_connected = false;
    
    /**
     * Stream type (stocks, crypto, options, account)
     * 
     * @var string
     */
    private $stream_type = '';
    
    /**
     * Trading mode (live or paper)
     * 
     * @var string
     */
    private $trading_mode = 'paper';
    
    /**
     * API subscription plan (basic or unlimited)
     * 
     * @var string
     */
    private $subscription_plan = 'basic';
    
    /**
     * Socket connection
     * 
     * @var resource
     */
    private $socket = null;
    
    /**
     * Subscribed symbols
     * 
     * @var array
     */
    private $subscribed_symbols = array();
    
    /**
     * Subscription types (trades, quotes, bars)
     * 
     * @var array
     */
    private $subscriptions = array();
    
    /**
     * Authentication details
     * 
     * @var array
     */
    private $auth_details = array();
    
    /**
     * Callbacks for different data types
     * 
     * @var array
     */
    private $callbacks = array();
    
    /**
     * Error log
     * 
     * @var array
     */
    private $error_log = array();
    
    /**
     * Constructor
     * 
     * @param string $stream_type The type of stream to connect to (stocks, crypto, options, account)
     * @param string $trading_mode Trading mode (live or paper)
     * @param string $subscription_plan API subscription plan (basic or unlimited)
     */
    public function __construct($stream_type = 'stocks', $trading_mode = 'paper', $subscription_plan = 'basic') {
        $this->stream_type = $stream_type;
        $this->trading_mode = $trading_mode;
        $this->subscription_plan = $subscription_plan;
        
        // Initialize callbacks array
        $this->callbacks = array(
            'trades' => array(),
            'quotes' => array(),
            'bars' => array(),
            'dailybars' => array(),
            'statuses' => array(),
            'lulds' => array(),
            'cancel_errors' => array(),
            'corrections' => array(),
            'success' => array(),
            'error' => array(),
            'connection' => array(),
            'disconnection' => array()
        );
        
        // Load API keys based on trading mode
        $this->load_auth_details();
    }
    
    /**
     * Load authentication details based on trading mode
     */
    private function load_auth_details() {
        $api_id = 'alpaca';
        
        if ($this->stream_type === 'account') {
            // For account updates, we need the trading API keys
            if ($this->trading_mode === 'paper') {
                $this->auth_details = array(
                    'key_id' => get_option('TradePress_api_' . $api_id . '_papermoney_apikey', ''),
                    'secret' => get_option('TradePress_api_' . $api_id . '_papermoney_secretkey', '')
                );
            } else {
                $this->auth_details = array(
                    'key_id' => get_option('TradePress_api_' . $api_id . '_realmoney_apikey', ''),
                    'secret' => get_option('TradePress_api_' . $api_id . '_realmoney_secretkey', '')
                );
            }
        } else {
            // For market data, we always use the market data API keys
            $this->auth_details = array(
                'key_id' => get_option('TradePress_api_' . $api_id . '_marketdata_apikey', ''),
                'secret' => get_option('TradePress_api_' . $api_id . '_marketdata_secretkey', '')
            );
        }
    }
    
    /**
     * Connect to WebSocket stream
     * 
     * @return boolean True if connection successful, false otherwise
     */
    public function connect() {
        if ($this->is_connected) {
            return true;
        }
        
        // Determine WebSocket URL based on stream type and subscription plan
        $ws_url = $this->get_websocket_url();
        
        if (empty($ws_url)) {
            $this->add_error('Invalid stream type: ' . $this->stream_type);
            return false;
        }
        
        // Check if authentication details are set
        if (empty($this->auth_details['key_id']) || empty($this->auth_details['secret'])) {
            $this->add_error('Missing API credentials. Please check your API settings.');
            return false;
        }
        
        // Connect to WebSocket (this is a placeholder - actual implementation would depend on WebSocket client library)
        // For a real implementation, you would use a WebSocket client library like:
        // - Ratchet (https://github.com/ratchetphp/Ratchet)
        // - ReactPHP WebSocket (https://github.com/reactphp/websocket)
        // - Or implement AJAX polling with WordPress hooks for frontend updates
        
        // Placeholder for connection logic
        // $this->socket = new WebSocketClient($ws_url);
        // $connected = $this->socket->connect();
        
        // For roadmap/demonstration purposes, we'll assume connection is successful
        $connected = true;
        
        if ($connected) {
            $this->is_connected = true;
            
            // Send authentication message
            $this->authenticate();
            
            // Call connection callbacks
            $this->trigger_callbacks('connection', array('url' => $ws_url));
            
            return true;
        } else {
            $this->add_error('Failed to connect to WebSocket server: ' . $ws_url);
            return false;
        }
    }
    
    /**
     * Get WebSocket URL based on stream type and subscription plan
     * 
     * @return string WebSocket URL
     */
    private function get_websocket_url() {
        if ($this->stream_type === 'account') {
            return ($this->trading_mode === 'paper') ? 
                   self::$websocket_urls['paper_account_updates'] : 
                   self::$websocket_urls['account_updates'];
        } elseif (isset(self::$websocket_urls['market_data'][$this->stream_type])) {
            // For stocks, determine which URL to use based on subscription plan
            if ($this->stream_type === 'stocks' && $this->subscription_plan === 'unlimited') {
                return self::$websocket_urls['market_data']['stocks_all_exchanges'];
            } else {
                return self::$websocket_urls['market_data'][$this->stream_type];
            }
        }
        
        return '';
    }
    
    /**
     * Authenticate with the WebSocket server
     * 
     * @return boolean True if authentication successful, false otherwise
     */
    private function authenticate() {
        if (!$this->is_connected) {
            $this->add_error('Cannot authenticate: Not connected to WebSocket server');
            return false;
        }
        
        // Prepare authentication message based on stream type
        if ($this->stream_type === 'account') {
            // Account updates stream authentication
            $auth_msg = json_encode(array(
                'action' => 'authenticate',
                'data' => array(
                    'key_id' => $this->auth_details['key_id'],
                    'secret_key' => $this->auth_details['secret']
                )
            ));
        } else {
            // Market data stream authentication
            $auth_msg = json_encode(array(
                'action' => 'auth',
                'key' => $this->auth_details['key_id'],
                'secret' => $this->auth_details['secret']
            ));
        }
        
        // Send authentication message
        // In a real implementation, you would send this through the WebSocket connection
        // $this->socket->send($auth_msg);
        
        // For roadmap/demonstration purposes, we'll assume authentication is successful
        $this->trigger_callbacks('success', array('message' => 'Authentication successful'));
        
        return true;
    }
    
    /**
     * Subscribe to data for specific symbols
     * 
     * @param array  $symbols Array of stock symbols to subscribe to
     * @param array  $channels Data channels to subscribe to (trades, quotes, bars)
     * @return boolean True if subscription successful, false otherwise
     */
    public function subscribe($symbols, $channels = array('trades', 'quotes', 'bars')) {
        if (!$this->is_connected) {
            if (!$this->connect()) {
                return false;
            }
        }
        
        // Validate channels
        $valid_channels = array('trades', 'quotes', 'bars', 'dailybars', 'statuses', 'lulds', 'cancel_errors', 'corrections');
        $channels = array_intersect($channels, $valid_channels);
        
        if (empty($channels)) {
            $this->add_error('No valid channels specified for subscription');
            return false;
        }
        
        // Update subscribed symbols and channels
        $this->subscribed_symbols = array_unique(array_merge($this->subscribed_symbols, $symbols));
        $this->subscriptions = array_unique(array_merge($this->subscriptions, $channels));
        
        // Prepare subscription message
        $sub_msg = json_encode(array(
            'action' => 'subscribe',
            'trades' => in_array('trades', $channels) ? $symbols : [],
            'quotes' => in_array('quotes', $channels) ? $symbols : [],
            'bars' => in_array('bars', $channels) ? $symbols : [],
            'dailyBars' => in_array('dailybars', $channels) ? $symbols : [],
            'statuses' => in_array('statuses', $channels) ? $symbols : [],
            'lulds' => in_array('lulds', $channels) ? $symbols : [],
            'cancelErrors' => in_array('cancel_errors', $channels) ? $symbols : [],
            'corrections' => in_array('corrections', $channels) ? $symbols : []
        ));
        
        // Send subscription message
        // In a real implementation, you would send this through the WebSocket connection
        // $this->socket->send($sub_msg);
        
        // For roadmap/demonstration purposes, we'll assume subscription is successful
        $this->trigger_callbacks('success', array(
            'message' => 'Subscription successful', 
            'symbols' => $symbols, 
            'channels' => $channels
        ));
        
        return true;
    }
    
    /**
     * Unsubscribe from data for specific symbols
     * 
     * @param array  $symbols Array of stock symbols to unsubscribe from
     * @param array  $channels Data channels to unsubscribe from (trades, quotes, bars)
     * @return boolean True if unsubscription successful, false otherwise
     */
    public function unsubscribe($symbols, $channels = array('trades', 'quotes', 'bars')) {
        if (!$this->is_connected) {
            $this->add_error('Cannot unsubscribe: Not connected to WebSocket server');
            return false;
        }
        
        // Validate channels
        $valid_channels = array('trades', 'quotes', 'bars', 'dailybars', 'statuses', 'lulds', 'cancel_errors', 'corrections');
        $channels = array_intersect($channels, $valid_channels);
        
        if (empty($channels)) {
            $this->add_error('No valid channels specified for unsubscription');
            return false;
        }
        
        // Prepare unsubscription message
        $unsub_msg = json_encode(array(
            'action' => 'unsubscribe',
            'trades' => in_array('trades', $channels) ? $symbols : [],
            'quotes' => in_array('quotes', $channels) ? $symbols : [],
            'bars' => in_array('bars', $channels) ? $symbols : [],
            'dailyBars' => in_array('dailybars', $channels) ? $symbols : [],
            'statuses' => in_array('statuses', $channels) ? $symbols : [],
            'lulds' => in_array('lulds', $channels) ? $symbols : [],
            'cancelErrors' => in_array('cancel_errors', $channels) ? $symbols : [],
            'corrections' => in_array('corrections', $channels) ? $symbols : []
        ));
        
        // Send unsubscription message
        // In a real implementation, you would send this through the WebSocket connection
        // $this->socket->send($unsub_msg);
        
        // Update subscribed symbols (remove unsubscribed symbols)
        $this->subscribed_symbols = array_diff($this->subscribed_symbols, $symbols);
        
        // For roadmap/demonstration purposes, we'll assume unsubscription is successful
        $this->trigger_callbacks('success', array(
            'message' => 'Unsubscription successful', 
            'symbols' => $symbols, 
            'channels' => $channels
        ));
        
        return true;
    }
    
    /**
     * Disconnect from WebSocket server
     * 
     * @return boolean True if disconnection successful, false otherwise
     */
    public function disconnect() {
        if (!$this->is_connected) {
            return true; // Already disconnected
        }
        
        // In a real implementation, you would close the WebSocket connection
        // $this->socket->close();
        
        $this->is_connected = false;
        $this->subscribed_symbols = array();
        $this->subscriptions = array();
        
        // Call disconnection callbacks
        $this->trigger_callbacks('disconnection', array());
        
        return true;
    }
    
    /**
     * Add callback for specific data type
     * 
     * @param string   $type     The type of data (trades, quotes, bars, etc.)
     * @param callable $callback The callback function
     */
    public function add_callback($type, $callback) {
        if (isset($this->callbacks[$type]) && is_callable($callback)) {
            $this->callbacks[$type][] = $callback;
            return true;
        }
        return false;
    }
    
    /**
     * Trigger callbacks for a specific data type
     * 
     * @param string $type The type of data (trades, quotes, bars, etc.)
     * @param array  $data The data to pass to the callbacks
     */
    private function trigger_callbacks($type, $data) {
        if (isset($this->callbacks[$type])) {
            foreach ($this->callbacks[$type] as $callback) {
                call_user_func($callback, $data);
            }
        }
    }
    
    /**
     * Add error to error log
     * 
     * @param string $message Error message
     */
    private function add_error($message) {
        $this->error_log[] = array(
            'timestamp' => current_time('mysql'),
            'message' => $message
        );
        
        // Trigger error callbacks
        $this->trigger_callbacks('error', array('message' => $message));
        
        // Log error if logging is enabled
        if (get_option('TradePress_switch_alpaca_api_logs', 'no') === 'yes') {
            error_log('[TradePress_Alpaca_WebSocket] ' . $message);
        }
    }
    
    /**
     * Get error log
     * 
     * @return array Error log
     */
    public function get_error_log() {
        return $this->error_log;
    }
    
    /**
     * Check if connected to WebSocket server
     * 
     * @return boolean True if connected, false otherwise
     */
    public function is_connected() {
        return $this->is_connected;
    }
    
    /**
     * Get subscribed symbols
     * 
     * @return array Subscribed symbols
     */
    public function get_subscribed_symbols() {
        return $this->subscribed_symbols;
    }
    
    /**
     * Get active subscriptions
     * 
     * @return array Active subscriptions
     */
    public function get_subscriptions() {
        return $this->subscriptions;
    }
    
    /**
     * Get stream type
     * 
     * @return string Stream type
     */
    public function get_stream_type() {
        return $this->stream_type;
    }
    
    /**
     * Get trading mode
     * 
     * @return string Trading mode
     */
    public function get_trading_mode() {
        return $this->trading_mode;
    }
    
    /**
     * Process incoming WebSocket message
     * 
     * This method would be called when a message is received from the WebSocket server.
     * In a real implementation, this would be handled by the WebSocket client's message callback.
     * 
     * @param string $message The raw message from the WebSocket server
     */
    public function process_message($message) {
        $data = json_decode($message, true);
        
        if (!$data) {
            $this->add_error('Failed to parse WebSocket message: ' . $message);
            return;
        }
        
        // Check if it's a success/error message
        if (isset($data['type'])) {
            if ($data['type'] === 'success') {
                $this->trigger_callbacks('success', $data);
                return;
            } elseif ($data['type'] === 'error') {
                $this->add_error($data['msg']);
                return;
            }
        }
        
        // Process data messages based on message type
        if (isset($data['T'])) {
            switch ($data['T']) {
                case 't': // Trade
                    $this->trigger_callbacks('trades', $data);
                    break;
                case 'q': // Quote
                    $this->trigger_callbacks('quotes', $data);
                    break;
                case 'b': // Bar/candle
                    $this->trigger_callbacks('bars', $data);
                    break;
                case 'd': // Daily bar
                    $this->trigger_callbacks('dailybars', $data);
                    break;
                case 's': // Status
                    $this->trigger_callbacks('statuses', $data);
                    break;
                case 'l': // LULD
                    $this->trigger_callbacks('lulds', $data);
                    break;
                case 'x': // Cancel error
                    $this->trigger_callbacks('cancel_errors', $data);
                    break;
                case 'c': // Correction
                    $this->trigger_callbacks('corrections', $data);
                    break;
                default:
                    $this->add_error('Unknown message type: ' . $data['T']);
            }
        } else {
            // Handle account update messages
            if (isset($data['stream'])) {
                // Account update message
                $this->trigger_callbacks($data['stream'], $data);
            } else {
                $this->add_error('Unknown message format: ' . $message);
            }
        }
    }
}