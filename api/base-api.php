<?php
/**
 * TradePress Base API Class
 * 
 * Abstract base class that all API implementations MUST extend.
 * Provides standardized methods, logging, validation, and error handling.
 * 
 * @package TradePress/API
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class TradePress_Base_API extends TradePress_Financial_API_Service {
    
    /**
     * API provider ID (e.g., 'alpaca', 'alphavantage')
     * @var string
     */
    protected $provider_id;
    
    /**
     * API provider configuration from directory
     * @var array
     */
    protected $provider_config;
    
    /**
     * Constructor
     * 
     * @param string $provider_id The API provider ID
     * @param array $args Configuration arguments
     */
    public function __construct($provider_id, $args = array()) {
        $this->provider_id = $provider_id;
        $this->provider_config = TradePress_API_Directory::get_provider($provider_id);
        
        if (!$this->provider_config) {
            throw new Exception("Unknown API provider: {$provider_id}");
        }
        
        parent::__construct($args);
    }
    
    /**
     * Test API connection - MUST be implemented by child classes
     * 
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    abstract public function test_connection();
    
    /**
     * Get standardized quote data
     * 
     * @param string $symbol Stock symbol
     * @return array|WP_Error Normalized quote data
     */
    abstract public function get_quote($symbol);
    
    /**
     * Get provider ID
     * 
     * @return string Provider ID
     */
    public function get_provider_id() {
        return $this->provider_id;
    }
    
    /**
     * Get provider configuration
     * 
     * @return array Provider configuration
     */
    public function get_provider_config() {
        return $this->provider_config;
    }
    
    /**
     * Standardized API request with logging
     * 
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @param string $method HTTP method
     * @return array|WP_Error Response data or error
     */
    public function make_request($endpoint, $params = array(), $method = 'GET') {
        // Load usage tracker
        if (!class_exists('TradePress_API_Usage_Tracker')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
        }
        
        // Log the API call with specific endpoint description
        $endpoint_description = sprintf('%s %s call for %s', 
            $this->provider_config['name'], 
            $endpoint, 
            $params['symbol'] ?? 'data'
        );
        
        $call_entry_id = TradePress_API_Logging::log_call(
            $this->provider_id,
            $endpoint,
            $method,
            'pending',
            __FILE__,
            __LINE__,
            $endpoint_description,
            '',
            86400
        );
        
        // Track endpoint usage
        TradePress_API_Logging::track_endpoint(
            $call_entry_id,
            $this->provider_id,
            $endpoint,
            $params
        );
        
        try {
            // Make the actual request using parent method
            $response = parent::make_request($endpoint, $params, $method);
            
            if (is_wp_error($response)) {
                // Check for rate limiting
                $error_message = $response->get_error_message();
                if (strpos($error_message, 'rate limit') !== false || 
                    strpos($error_message, 'API call frequency') !== false) {
                    TradePress_API_Usage_Tracker::mark_rate_limited($this->provider_id);
                }
                
                // Track failed call
                TradePress_API_Usage_Tracker::track_call($this->provider_id, $endpoint, false);
                
                // Log the error
                TradePress_API_Logging::log_error(
                    $call_entry_id,
                    $response->get_error_code(),
                    $response->get_error_message(),
                    __FUNCTION__
                );
                
                TradePress_API_Logging::update_call_outcome(
                    $call_entry_id,
                    'Error: ' . $response->get_error_message(),
                    'error'
                );
                
                return $response;
            }
            
            // Track successful call
            TradePress_API_Usage_Tracker::track_call($this->provider_id, $endpoint, true);
            
            // Log success
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'Success',
                'success'
            );
            
            return $response;
            
        } catch (Exception $e) {
            // Track failed call
            TradePress_API_Usage_Tracker::track_call($this->provider_id, $endpoint, false);
            
            // Log the exception
            TradePress_API_Logging::log_error(
                $call_entry_id,
                'exception',
                $e->getMessage(),
                __FUNCTION__
            );
            
            TradePress_API_Logging::update_call_outcome(
                $call_entry_id,
                'Exception: ' . $e->getMessage(),
                'error'
            );
            
            return new WP_Error('api_exception', $e->getMessage());
        }
    }
    
    /**
     * Validate required credentials
     * 
     * @param array $required_fields Required credential fields
     * @return bool|WP_Error True if valid, WP_Error if missing
     */
    protected function validate_credentials($required_fields = array()) {
        foreach ($required_fields as $field) {
            if (empty($this->credentials[$field])) {
                return new WP_Error(
                    'missing_credentials',
                    sprintf('Missing required credential: %s', $field)
                );
            }
        }
        
        return true;
    }
    
    /**
     * Get API call statistics for this provider
     * 
     * @return array API call statistics
     */
    public function get_api_stats() {
        return array(
            'daily_calls' => TradePress_API_Logging::get_api_call_count(array(
                'service' => $this->provider_id,
                'date_from' => date('Y-m-d')
            )),
            'total_calls' => TradePress_API_Logging::get_api_call_count(array(
                'service' => $this->provider_id
            )),
            'error_calls' => TradePress_API_Logging::get_api_call_count(array(
                'service' => $this->provider_id,
                'status' => 'error'
            ))
        );
    }
}