<?php
/**
 * TradePress Alpaca Direct API Handler
 *
 * A direct implementation of Alpaca API connectivity that bypasses the existing
 * request middleware and connects directly to the Alpaca API. This implementation
 * is based on the working approach in the SortingBoxes project but without Guzzle dependency.
 * 
 * @package TradePress
 * @subpackage API\Alpaca
 * @version 1.0.0
 * @since 2025-04-19
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Alpaca Direct API Handler class
 */
class TradePress_Alpaca_Direct {
    
    /**
     * API key ID
     *
     * @var string
     */
    private $key = '';
    
    /**
     * API secret key
     *
     * @var string
     */
    private $secret = '';
    
    /**
     * API mode (paper or live)
     *
     * @var string
     */
    private $mode = 'paper';
    
    /**
     * Constructor
     *
     * @param string $key API key ID
     * @param string $secret API secret key
     * @param string $mode Trading mode ('paper' or 'live')
     */
    public function __construct($key = '', $secret = '', $mode = 'paper') {
        $this->key = $key;
        $this->secret = $secret;
        $this->mode = $mode;
    }
    
    /**
     * Get the base URL for API requests
     *
     * @return string API base URL
     */
    private function get_base_url() {
        if ($this->mode === 'paper') {
            return 'https://paper-api.alpaca.markets';
        } else {
            return 'https://api.alpaca.markets';
        }
    }
    
    /**
     * Build a full URL for an API request
     *
     * @param string $path API endpoint path
     * @param array $query_params Query parameters
     * @param string $version API version
     * @return string Complete URL
     */
    private function build_url($path, $query_params = [], $version = 'v2') {
        $path = trim($path, '/');
        $base_url = $this->get_base_url();
        
        // Add version if specified
        if (!empty($version)) {
            $base_url .= '/' . $version . '/';
        } else {
            $base_url .= '/';
        }
        
        // Build query string
        $query_string = '';
        if (!empty($query_params)) {
            $query_parts = [];
            foreach ($query_params as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                $query_parts[] = $key . '=' . urlencode($value);
            }
            if (!empty($query_parts)) {
                $query_string = '?' . implode('&', $query_parts);
            }
        }
        
        return $base_url . $path . $query_string;
    }
    
    /**
     * Make a request to the Alpaca API
     *
     * @param string $path API endpoint path
     * @param array $params Query parameters
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array|null $body Request body data
     * @param string $version API version
     * @return array Response data or error information
     */
    public function request($path, $params = [], $method = 'GET', $body = null, $version = 'v2') {
        // Build the URL
        $url = $this->build_url($path, $params, $version);
        
        // Set up standard headers
        $headers = [
            'APCA-API-KEY-ID: ' . $this->key,
            'APCA-API-SECRET-KEY: ' . $this->secret,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set basic cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Set method-specific options
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        } else if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }
        
        // Log the request (debugging only)
        error_log('[TradePress_Alpaca_Direct] Making ' . $method . ' request to ' . $url);
        error_log('[TradePress_Alpaca_Direct] Headers: ' . json_encode($headers));
        if ($body !== null) {
            error_log('[TradePress_Alpaca_Direct] Body: ' . json_encode($body));
        }
        
        // Execute the request
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check for cURL errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            
            error_log('[TradePress_Alpaca_Direct] cURL Error: ' . $error);
            
            return [
                'success' => false,
                'code' => 0,
                'message' => 'Connection Error: ' . $error
            ];
        }
        
        // Close cURL
        curl_close($ch);
        
        // Log the response (debugging only)
        error_log('[TradePress_Alpaca_Direct] Status Code: ' . $status_code);
        error_log('[TradePress_Alpaca_Direct] Response: ' . $response);
        
        // Parse the response
        $parsed_response = json_decode($response, true);
        
        // Check if valid JSON was returned
        if (json_last_error() !== JSON_ERROR_NONE && !empty($response)) {
            return [
                'success' => false,
                'code' => $status_code,
                'message' => 'Invalid JSON response',
                'raw_response' => $response
            ];
        }
        
        // Build result array
        if ($status_code >= 200 && $status_code < 300) {
            return [
                'success' => true,
                'code' => $status_code,
                'data' => $parsed_response
            ];
        } else {
            $error_message = isset($parsed_response['message']) ? $parsed_response['message'] : 'Unknown error';
            
            return [
                'success' => false,
                'code' => $status_code,
                'message' => $error_message,
                'data' => $parsed_response
            ];
        }
    }
    
    /**
     * Test the API connection
     *
     * @return array Connection test results
     */
    public function test_connection() {
        return $this->request('account');
    }
    
    /**
     * Get account information
     *
     * @return array Account information
     */
    public function get_account() {
        return $this->request('account');
    }
    
    /**
     * Get all watchlists
     *
     * @return array Watchlists data
     */
    public function get_watchlists() {
        return $this->request('watchlists');
    }
    
    /**
     * Get a specific watchlist by ID
     *
     * @param string $watchlist_id Watchlist ID
     * @return array Watchlist data
     */
    public function get_watchlist($watchlist_id) {
        return $this->request('watchlists/' . $watchlist_id);
    }
    
    /**
     * Create a new watchlist
     *
     * @param string $name Watchlist name
     * @param array $symbols Array of symbols to add to the watchlist
     * @return array New watchlist data
     */
    public function create_watchlist($name, $symbols = []) {
        $body = [
            'name' => $name
        ];
        
        if (!empty($symbols)) {
            $body['symbols'] = $symbols;
        }
        
        return $this->request('watchlists', [], 'POST', $body);
    }
}