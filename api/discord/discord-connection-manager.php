<?php
/**
 * TradePress Discord Connection Manager
 *
 * This class manages connections to the Discord API with intelligent fallbacks for different environments
 * 
 * @package TradePress
 * @subpackage API\Discord
 * @version 1.0.0
 * @since 2025-04-24
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the troubleshooter class if it's not already loaded
if (!class_exists('TRADEPRESS_DISCORD_Troubleshooter')) {
    require_once dirname(__FILE__) . '/discord-troubleshooter.php';
}

/**
 * Discord Connection Manager Class
 */
class TRADEPRESS_DISCORD_Connection_Manager {
    /**
     * Bot token
     *
     * @var string
     */
    private $token = '';
    
    /**
     * Connection settings
     *
     * @var array
     */
    private $settings = array();
    
    /**
     * Troubleshooter instance
     *
     * @var TRADEPRESS_DISCORD_Troubleshooter
     */
    private $troubleshooter;
    
    /**
     * Constructor
     *
     * @param string $token Bot token
     */
    public function __construct($token = '') {
        $this->token = $token;
        $this->troubleshooter = new TRADEPRESS_DISCORD_Troubleshooter();
        
        // Default settings
        $this->settings = array(
            'ssl_verify' => true,
            'timeout' => 15,
            'user_agent' => 'TradePress/1.0 (WordPress; +https://tradepress.com)',
            'auto_retry' => true,
            'debug_mode' => false
        );
    }
    
    /**
     * Update settings
     *
     * @param array $settings Settings to update
     * @return self For method chaining
     */
    public function set_settings($settings) {
        if (is_array($settings)) {
            $this->settings = array_merge($this->settings, $settings);
        }
        
        return $this;
    }
    
    /**
     * Set the bot token
     *
     * @param string $token Bot token
     * @return self For method chaining
     */
    public function set_token($token) {
        $this->token = $token;
        return $this;
    }
    
    /**
     * Test the connection to Discord API
     *
     * @return bool Whether the connection test was successful
     */
    public function test_connection() {
        // Try standard connection first
        $standard_test = $this->troubleshooter->test_network_connectivity();
        
        if ($standard_test) {
            // Standard connection works, use standard settings
            $this->settings['ssl_verify'] = true;
            return true;
        }
        
        // Try insecure connection if standard fails
        $insecure_test = $this->troubleshooter->test_network_connectivity_insecure();
        
        if ($insecure_test) {
            // Insecure connection works, use insecure settings
            $this->settings['ssl_verify'] = false;
            return true;
        }
        
        // Both connection methods failed
        return false;
    }
    
    /**
     * Validate the bot token
     *
     * @return bool Whether the token is valid
     */
    public function validate_token() {
        if (empty($this->token)) {
            return false;
        }
        
        // First, make sure we can connect
        $can_connect = $this->test_connection();
        
        if (!$can_connect) {
            return false;
        }
        
        return $this->troubleshooter->validate_bot_token($this->token);
    }
    
    /**
     * Get request headers for Discord API
     *
     * @return array Headers
     */
    public function get_headers() {
        $headers = array(
            'User-Agent' => $this->settings['user_agent']
        );
        
        if (!empty($this->token)) {
            $headers['Authorization'] = 'Bot ' . $this->token;
        }
        
        return $headers;
    }
    
    /**
     * Make a request to the Discord API
     *
     * @param string $endpoint API endpoint (without base URL)
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array $data Request data (for POST, PUT)
     * @return array|WP_Error Response or error
     */
    public function request($endpoint, $method = 'GET', $data = array()) {
        $url = 'https://discord.com/api/v10/' . ltrim($endpoint, '/');
        
        $args = array(
            'method' => $method,
            'timeout' => $this->settings['timeout'],
            'sslverify' => $this->settings['ssl_verify'],
            'headers' => $this->get_headers(),
        );
        
        if ($method === 'POST' || $method === 'PUT') {
            $args['body'] = wp_json_encode($data);
            $args['headers']['Content-Type'] = 'application/json';
        }
        
        // Send the request
        $response = wp_remote_request($url, $args);
        
        // Debug mode
        if ($this->settings['debug_mode']) {
            // Log the request/response if needed
        }
        
        if (is_wp_error($response)) {
            // If auto retry is enabled and SSL is not verified, try again with SSL disabled
            if ($this->settings['auto_retry'] && $this->settings['ssl_verify']) {
                $this->settings['ssl_verify'] = false;
                $args['sslverify'] = false;
                $response = wp_remote_request($url, $args);
                
                if (!is_wp_error($response)) {
                    // Successfully connected with SSL disabled
                    return $response;
                }
            }
            
            return $response; // Still an error
        }
        
        return $response;
    }
    
    /**
     * Get the troubleshooter instance
     *
     * @return TRADEPRESS_DISCORD_Troubleshooter Troubleshooter instance
     */
    public function get_troubleshooter() {
        return $this->troubleshooter;
    }
    
    /**
     * Get diagnostics report
     *
     * @return array Diagnostics report
     */
    public function get_diagnostics() {
        $report = array(
            'connection' => array(
                'standard' => $this->troubleshooter->test_network_connectivity(),
                'insecure' => $this->troubleshooter->test_network_connectivity_insecure(),
                'curl_debug' => $this->troubleshooter->run_curl_debug_test()
            ),
            'token' => array(
                'is_valid' => !empty($this->token) ? $this->troubleshooter->validate_bot_token($this->token) : false,
                'errors' => $this->troubleshooter->get_errors(),
                'success' => $this->troubleshooter->get_success()
            ),
            'environment' => array(
                'os' => php_uname('s') . ' ' . php_uname('r'),
                'php_version' => PHP_VERSION,
                'ssl_supported' => extension_loaded('openssl'),
                'curl_version' => function_exists('curl_version') ? curl_version()['version'] : 'Not installed',
                'ssl_cert_file' => ini_get('openssl.cafile'),
                'ssl_cert_dir' => ini_get('openssl.capath'),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ),
            'settings' => $this->settings
        );
        
        return $report;
    }
}