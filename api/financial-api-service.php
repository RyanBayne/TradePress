<?php
/**
 * TradePress Financial API Service
 *
 * Base class for all financial API services in TradePress.
 * This class provides core functionality shared across all financial API integrations
 * including authentication handling, rate limiting, error handling, and standardized
 * method signatures.
 *
 * ARCHITECTURE NOTE:
 * - This class works together with TradePress_API_Adapter in the following way:
 *   - Financial API Service: Handles the API-specific implementation details including
 *     endpoints, authentication, and direct API communication
 *   - API Adapter: Normalizes data between different API formats, providing a consistent
 *     interface for the rest of the application regardless of the underlying API
 * 
 * @package TradePress/API
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

abstract class TradePress_Financial_API_Service {
    /**
     * API Key or credentials
     * @var array
     */
    protected $credentials = array();
    
    /**
     * Base URL for the API
     * @var string
     */
    protected $base_url;
    
    /**
     * Default request timeout in seconds
     * @var int
     */
    protected $timeout = 15;
    
    /**
     * Whether to verify SSL certificates
     * @var bool
     */
    protected $verify_ssl = true;
    
    /**
     * API version
     * @var string
     */
    protected $api_version;
    
    /**
     * Last error encountered
     * @var WP_Error|null
     */
    protected $last_error = null;
    
    /**
     * Last response received
     * @var array|WP_Error
     */
    protected $last_response = null;
    
    /**
     * Rate limit information
     * @var array
     */
    protected $rate_limits = array();

    /**
     * Constructor
     * 
     * @param array $args Configuration arguments
     */
    public function __construct($args = array()) {
        // Process arguments and set properties
        $this->process_args($args);
        
        // Initialize the service
        $this->init();
    }
    
    /**
     * Process constructor arguments
     * 
     * @param array $args Configuration arguments
     */
    protected function process_args($args) {
        // Set timeout if provided
        if (isset($args['timeout']) && is_numeric($args['timeout'])) {
            $this->timeout = (int)$args['timeout'];
        }
        
        // Set SSL verification option
        if (isset($args['verify_ssl'])) {
            $this->verify_ssl = (bool)$args['verify_ssl'];
        }
        
        // Set API version if provided
        if (isset($args['api_version'])) {
            $this->api_version = $args['api_version'];
        }
        
        // Set credentials if provided
        if (isset($args['api_key'])) {
            $this->credentials['api_key'] = $args['api_key'];
        }
        
        if (isset($args['api_secret'])) {
            $this->credentials['api_secret'] = $args['api_secret'];
        }
    }
    
    /**
     * Initialize the service
     * Child classes should override this if needed
     */
    protected function init() {
        // Child classes can override this for additional initialization
    }
    
    /**
     * Make a request to the API
     * 
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @param string $method Request method (GET, POST, PUT, DELETE)
     * @return array|WP_Error Response data or error
     */
    public function make_request($endpoint, $params = array(), $method = 'GET') {
        // Build the request URL
        $url = $this->build_request_url($endpoint, $params, $method);
        
        // Prepare request arguments
        $args = $this->prepare_request_args($params, $method);
        
        // Execute the request
        return $this->execute_request($url, $args);
    }
    
    /**
     * Build the request URL
     * 
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @param string $method Request method
     * @return string Complete URL
     */
    protected function build_request_url($endpoint, $params = array(), $method = 'GET') {
        $url = trailingslashit($this->base_url) . $endpoint;
        
        // For GET requests, add parameters to the URL
        if ($method === 'GET' && !empty($params)) {
            $url = add_query_arg($params, $url);
        }
        
        return $url;
    }
    
    /**
     * Prepare the arguments for wp_remote_request
     * 
     * @param array $params Request parameters
     * @param string $method Request method
     * @return array Request arguments
     */
    protected function prepare_request_args($params = array(), $method = 'GET') {
        $args = array(
            'method'      => $method,
            'timeout'     => $this->timeout,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking'    => true,
            'headers'     => $this->get_request_headers(),
            'cookies'     => array(),
            'sslverify'   => $this->verify_ssl,
        );
        
        // For non-GET requests, add the body
        if ($method !== 'GET' && !empty($params)) {
            $args['body'] = json_encode($params);
        }
        
        return $args;
    }
    
    /**
     * Get headers for the API request
     * Child classes should override this as needed
     * 
     * @return array Headers
     */
    protected function get_request_headers() {
        $headers = array(
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        );
        
        // Add authentication headers if available
        if (!empty($this->credentials['api_key'])) {
            $headers['Authorization'] = $this->get_auth_header();
        }
        
        return $headers;
    }
    
    /**
     * Get the authentication header
     * Child classes must implement this based on their API requirements
     * 
     * @return string Authentication header value
     */
    protected function get_auth_header() {
        // Default implementation - child classes should override
        if (!empty($this->credentials['api_key'])) {
            return 'Bearer ' . $this->credentials['api_key'];
        }
        
        return '';
    }
    
    /**
     * Execute the API request
     * 
     * @param string $url The request URL
     * @param array $args Request arguments
     * @return array|WP_Error Response data or error
     */
    protected function execute_request($url, $args) {
        // Log the request if debugging is enabled
        $this->log_request($url, $args);
        
        // Make the request
        $response = wp_remote_request($url, $args);
        
        // Store the last response
        $this->last_response = $response;
        
        // Check for HTTP errors
        if (is_wp_error($response)) {
            $this->last_error = $response;
            return $response;
        }
        
        // Get the response code
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Update rate limit information
        $this->update_rate_limits($response);
        
        // Check if the response code indicates an error
        if ($response_code < 200 || $response_code >= 300) {
            $error_message = wp_remote_retrieve_response_message($response);
            $body = wp_remote_retrieve_body($response);
            
            // Try to get more error details from the body
            $body_data = json_decode($body, true);
            if (!empty($body_data['error'])) {
                $error_message = $body_data['error'];
            } elseif (!empty($body_data['message'])) {
                $error_message = $body_data['message'];
            }
            
            $this->last_error = new WP_Error(
                'api_error',
                sprintf('API Error (%d): %s', $response_code, $error_message),
                array(
                    'response_code' => $response_code,
                    'response' => $response
                )
            );
            
            return $this->last_error;
        }
        
        // Parse the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // If json_decode failed, return the raw body
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('body' => $body);
        }
        
        return $data;
    }
    
    /**
     * Update rate limit information from the response headers
     * Child classes should override this for API-specific rate limit handling
     * 
     * @param array $response The API response
     */
    protected function update_rate_limits($response) {
        // Default implementation - child classes should override
        $headers = wp_remote_retrieve_headers($response);
        
        // Example implementation - APIs will have different header names
        if (isset($headers['x-ratelimit-limit'])) {
            $this->rate_limits['limit'] = (int)$headers['x-ratelimit-limit'];
        }
        
        if (isset($headers['x-ratelimit-remaining'])) {
            $this->rate_limits['remaining'] = (int)$headers['x-ratelimit-remaining'];
        }
        
        if (isset($headers['x-ratelimit-reset'])) {
            $this->rate_limits['reset'] = (int)$headers['x-ratelimit-reset'];
        }
    }
    
    /**
     * Log the API request for debugging
     * 
     * @param string $url The request URL
     * @param array $args Request arguments
     */
    protected function log_request($url, $args) {
        if (defined('TRADEPRESS_API_DEBUG') && TRADEPRESS_API_DEBUG) {
            // Remove sensitive data
            $log_args = $args;
            if (isset($log_args['headers']['Authorization'])) {
                $log_args['headers']['Authorization'] = 'REDACTED';
            }
            
            // Log the request
            error_log(sprintf('TradePress API Request: %s, Args: %s', $url, wp_json_encode($log_args)));
        }
    }
    
    /**
     * Get the last error
     * 
     * @return WP_Error|null Last error or null if no error
     */
    public function get_last_error() {
        return $this->last_error;
    }
    
    /**
     * Get the last response
     * 
     * @return array|WP_Error Last response or error
     */
    public function get_last_response() {
        return $this->last_response;
    }
    
    /**
     * Get the current rate limit status
     * 
     * @return array Rate limit information
     */
    public function get_rate_limits() {
        return $this->rate_limits;
    }
    
    /**
     * Check if we've exceeded the rate limit
     * 
     * @return bool True if rate limited, false otherwise
     */
    public function is_rate_limited() {
        if (isset($this->rate_limits['remaining']) && $this->rate_limits['remaining'] <= 0) {
            return true;
        }
        
        return false;
    }
}
