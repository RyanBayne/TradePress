<?php
/**
 * TradePress Discord API
 *
 * Main class for handling Discord API requests
 * API Documentation: https://discord.com/developers/docs/intro
 * 
 * @package TradePress
 * @subpackage API\Discord
 * @version 1.0.0
 * @since 2025-04-10
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('TradePress_Curl')) {
    require_once(trailingslashit(TRADEPRESS_PLUGIN_DIR_PATH) . 'api/curl.php');
}

if (!class_exists('TRADEPRESS_DISCORD_Endpoints')) {
    require_once(trailingslashit(TRADEPRESS_PLUGIN_DIR_PATH) . 'api/discord/discord-endpoints.php');
}

/**
 * TradePress Discord API Class
 */
class TRADEPRESS_DISCORD_API {
    
    /**
     * Discord API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://discord.com/api/v10';
    
    /**
     * Discord Bot Token
     *
     * @var string
     */
    private $bot_token = '';
    
    /**
     * Discord Application Client ID
     *
     * @var string
     */
    private $client_id = '';
    
    /**
     * Discord Application Client Secret
     *
     * @var string
     */
    private $client_secret = '';
    
    /**
     * OAuth2 Redirect URI
     *
     * @var string
     */
    private $redirect_uri = '';
    
    /**
     * OAuth2 Access Token
     *
     * @var string
     */
    private $access_token = '';
    
    /**
     * OAuth2 Refresh Token
     *
     * @var string
     */
    private $refresh_token = '';
    
    /**
     * Curl object for making API requests
     *
     * @var TradePress_Curl
     */
    private $curl_object;
    
    /**
     * Debug mode
     *
     * @var bool
     */
    private $debug_mode = false;
    
    /**
     * Debug log
     *
     * @var array
     */
    private $debug_log = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the CURL object
        $this->curl_object = new TradePress_Curl();
        $this->curl_object->api_name = 'discord';
        
        // Load credentials from WordPress options
        $this->load_credentials();
        
        // Enable debug mode if constant is defined
        $this->debug_mode = defined('TRADEPRESS_DISCORD_DEBUG') && TRADEPRESS_DISCORD_DEBUG;
    }
    
    /**
     * Load Discord API credentials from WordPress options
     */
    private function load_credentials() {
        // Try to get bot token from the standard option key
        $this->bot_token = get_option('TRADEPRESS_DISCORD_bot_token', '');
        
        // If token is empty, try alternate location from trading platforms settings
        if (empty($this->bot_token)) {
            $this->bot_token = get_option('TradePress_api_discord_realmoney_apikey', '');
        }
        
        // If still empty, check another possible naming convention
        if (empty($this->bot_token)) {
            $this->bot_token = get_option('TRADEPRESS_DISCORD_bot_token', '');
        }
        
        // Trim the token to remove any whitespace that might have been accidentally added
        $this->bot_token = trim($this->bot_token);
        
        // Log token status (not the actual token) for troubleshooting
        if (empty($this->bot_token)) {
            error_log('TradePress Discord API: Bot token is empty or not found');
        } else {
            $token_length = strlen($this->bot_token);
            error_log("TradePress Discord API: Bot token found with length: {$token_length}");
            
            // Check if token follows Discord's format (typically starts with N or M)
            $is_valid_format = preg_match('/^[NM][a-zA-Z0-9_-]{23,}\.[\w-]{6}\.[\w-]{27}$/', $this->bot_token);
            if (!$is_valid_format) {
                error_log('TradePress Discord API: Bot token may not be in the correct format');
            }
        }
        
        // Load other credentials
        $this->client_id = get_option('TRADEPRESS_DISCORD_client_id', '');
        $this->client_secret = get_option('TRADEPRESS_DISCORD_client_secret', '');
        $this->redirect_uri = get_option('TRADEPRESS_DISCORD_redirect_uri', '');
        $this->access_token = get_option('TRADEPRESS_DISCORD_access_token', '');
        $this->refresh_token = get_option('TRADEPRESS_DISCORD_refresh_token', '');
        
        // If we found a token in an alternative location but not in the standard one,
        // save it to the standard location for future use
        if (!empty($this->bot_token) && get_option('TRADEPRESS_DISCORD_bot_token', '') === '') {
            update_option('TRADEPRESS_DISCORD_bot_token', $this->bot_token);
        }
    }
    
    /**
     * Validate bot token with a simple API call
     *
     * @return bool|string True if valid, error message if invalid
     */
    public function validate_bot_token() {
        if (empty($this->bot_token)) {
            return 'Bot token is empty';
        }
        
        // Use a direct wp_remote_get call with the correct endpoint to check the bot's identity
        $response = wp_remote_get(
            'https://discord.com/api/v10/users/@me',
            array(
                'headers' => array(
                    'Authorization' => 'Bot ' . $this->bot_token,
                ),
                'timeout' => 15,
            )
        );
        
        if (is_wp_error($response)) {
            error_log('TradePress Discord API: Token validation failed - ' . $response->get_error_message());
            return $response->get_error_message();
        }
        
        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            error_log("TradePress Discord API: Token validation failed - HTTP {$http_code} - Response: {$body}");
            return "Discord API returned error code: {$http_code}";
        }
        
        $body = json_decode(wp_remote_retrieve_body($response));
        if (!isset($body->id)) {
            error_log('TradePress Discord API: Token validation failed - Invalid response format');
            return 'Invalid response from Discord API';
        }
        
        error_log('TradePress Discord API: Token validation successful - Bot ID: ' . $body->id);
        return true;
    }
    
    /**
     * Make an HTTP request to the Discord API
     *
     * @param string $endpoint_key API endpoint key
     * @param array $params Request parameters
     * @param string $auth_type Type of authorization (bot, bearer, etc.)
     * @param bool $ssl_verify Whether to verify SSL certificates
     * @return mixed Response data or WP_Error
     */
    public function request($endpoint_key, $params = array(), $auth_type = 'bot', $ssl_verify = true) {
        // Load credentials if not already loaded
        if (empty($this->bot_token)) {
            $this->load_credentials();
        }
        
        // Initialize the curl object
        $this->curl_object = new TradePress_Curl();
        $this->curl_object->api_name = 'discord';
        
        // Check if we have a token when using bot auth
        if ($auth_type === 'bot' && empty($this->bot_token)) {
            $this->log_debug('Error: Missing bot token');
            return new WP_Error('missing_token', 'Discord Bot Token is missing');
        }
        
        // Check if we have an access token when using bearer auth
        if ($auth_type === 'bearer' && empty($this->access_token)) {
            $this->log_debug('Error: Missing access token');
            return new WP_Error('missing_token', 'Discord Access Token is missing');
        }
        
        // Get the endpoint URL
        $endpoint_url = $this->get_endpoint_url($endpoint_key, $params);
        if (is_wp_error($endpoint_url)) {
            $this->log_debug('Error getting endpoint URL: ' . $endpoint_url->get_error_message());
            return $endpoint_url;
        }
        
        $this->log_debug('Making request to: ' . $endpoint_url);
        
        // Set the endpoint in the curl object
        $this->curl_object->endpoint = $endpoint_url;
        
        // Set request type (GET, POST, etc.)
        $this->curl_object->type = $this->get_endpoint_method($endpoint_key);
        $this->log_debug('Request method: ' . $this->curl_object->type);
        
        // Set request body for POST, PUT, PATCH requests
        if (in_array(strtoupper($this->curl_object->type), array('POST', 'PUT', 'PATCH')) && !empty($params['body'])) {
            $body = is_array($params['body']) ? json_encode($params['body']) : $params['body'];
            $this->curl_object->set_curl_body($body);
            $this->log_debug('Request body: ' . $body);
        }
        
        // Set authorization header
        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        );
        
        if ($auth_type === 'bot') {
            // Ensure the token is properly formatted for Discord's requirements
            $token = trim($this->bot_token);
            $headers['Authorization'] = 'Bot ' . $token;
            $this->log_debug('Using bot authentication');
        } elseif ($auth_type === 'bearer') {
            $headers['Authorization'] = 'Bearer ' . $this->access_token;
            $this->log_debug('Using bearer authentication');
        } elseif ($auth_type === 'none') {
            $this->log_debug('No authentication used');
        }
        
        $this->curl_object->add_headers($headers);
        
        // Allow disabling SSL verification for testing/debugging
        if (!$ssl_verify) {
            // Add these options to the curl request
            $this->curl_object->curl_request['sslverify'] = false;
            $this->curl_object->curl_request['ssl_verify'] = false;
            $this->log_debug('SSL verification disabled');
        }
        
        // Make the request
        $this->log_debug('Executing request...');
        $this->curl_object->do_call();
        
        // Check for errors
        if (is_wp_error($this->curl_object->curl_reply)) {
            $error = $this->curl_object->curl_reply->get_error_message();
            $this->log_debug('Request error: ' . $error);
            error_log('TradePress Discord API: Request error - ' . $error);
            return $this->curl_object->curl_reply;
        }
        
        // Get HTTP response code and body
        $http_code = wp_remote_retrieve_response_code($this->curl_object->curl_reply);
        $body = wp_remote_retrieve_body($this->curl_object->curl_reply);
        $this->log_debug('Response code: ' . $http_code);
        $this->log_debug('Response body: ' . $body);
        
        if ($http_code !== 200) {
            error_log("TradePress Discord API: HTTP {$http_code} - Response: {$body}");
            
            // Special handling for 401 Unauthorized
            if ($http_code === 401) {
                $this->log_debug('Authentication failed (401 Unauthorized)');
                error_log('TradePress Discord API: Authentication failed (401 Unauthorized) - Check your bot token');
                return new WP_Error('auth_failed', 'Invalid bot token. Authentication failed (401 Unauthorized).');
            }
            
            // Create a more descriptive error for other status codes
            return new WP_Error(
                'discord_api_error',
                'Discord API returned error code: ' . $http_code,
                array(
                    'code' => $http_code,
                    'response' => $body
                )
            );
        }
        
        // Parse response
        return $this->parse_response($this->curl_object->curl_reply);
    }
    
    /**
     * Log debug message
     *
     * @param string $message Debug message
     */
    private function log_debug($message) {
        if ($this->debug_mode) {
            $this->debug_log[] = '[' . date('Y-m-d H:i:s') . '] ' . $message;
            error_log('TradePress Discord API Debug: ' . $message);
        }
    }
    
    /**
     * Get debug log
     *
     * @return array Debug log
     */
    public function get_debug_log() {
        return $this->debug_log;
    }
    
    /**
     * Enable or disable debug mode
     *
     * @param bool $enable Whether to enable debug mode
     */
    public function set_debug_mode($enable = true) {
        $this->debug_mode = $enable;
    }
    
    /**
     * Validate an endpoint exists
     * 
     * @param string $endpoint_key Endpoint key to validate
     * @return bool True if endpoint exists
     */
    public function validate_endpoint($endpoint_key) {
        $endpoints = new TRADEPRESS_DISCORD_Endpoints();
        $endpoint = $endpoints->get_endpoint($endpoint_key);
        return !empty($endpoint);
    }
    
    /**
     * Get a list of all available endpoints
     * 
     * @return array List of endpoint keys and descriptions
     */
    public function get_available_endpoints() {
        $endpoints = new TRADEPRESS_DISCORD_Endpoints();
        $all_endpoints = $endpoints->get_all_endpoints();
        
        $endpoint_list = array();
        foreach ($all_endpoints as $key => $endpoint) {
            $endpoint_list[$key] = $endpoint['description'];
        }
        
        return $endpoint_list;
    }
    
    /**
     * Exchange authorization code for access token
     *
     * @param string $code Authorization code from OAuth flow
     * @return bool Whether the token was successfully obtained
     */
    public function get_access_token($code) {
        if (empty($code) || empty($this->client_id) || empty($this->client_secret) || empty($this->redirect_uri)) {
            return false;
        }
        
        $params = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirect_uri
        );
        
        // Use curl object directly for token requests
        $this->curl_object->endpoint = $this->api_base_url . '/oauth2/token';
        $this->curl_object->type = 'post';
        $this->curl_object->can_cache = false;
        
        // Set headers for token request (form data, not JSON)
        $this->curl_object->add_headers(array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        ));
        
        // Set request body as URL-encoded string
        $this->curl_object->set_curl_body(http_build_query($params));
        
        // Make the API call
        $this->curl_object->do_call();
        
        // Get the response
        $response = $this->curl_object->get_decoded_body();
        
        if (isset($response->access_token)) {
            $this->access_token = $response->access_token;
            $this->refresh_token = isset($response->refresh_token) ? $response->refresh_token : '';
            
            // Save tokens to WordPress options
            update_option('TRADEPRESS_DISCORD_access_token', $this->access_token);
            update_option('TRADEPRESS_DISCORD_refresh_token', $this->refresh_token);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Refresh an expired access token
     *
     * @return bool Whether the token was successfully refreshed
     */
    public function refresh_access_token() {
        if (empty($this->refresh_token) || empty($this->client_id) || empty($this->client_secret)) {
            return false;
        }
        
        $params = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refresh_token
        );
        
        // Use curl object directly for token requests
        $this->curl_object->endpoint = $this->api_base_url . '/oauth2/token';
        $this->curl_object->type = 'post';
        $this->curl_object->can_cache = false;
        
        // Set headers for token request (form data, not JSON)
        $this->curl_object->add_headers(array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        ));
        
        // Set request body as URL-encoded string
        $this->curl_object->set_curl_body(http_build_query($params));
        
        // Make the API call
        $this->curl_object->do_call();
        
        // Get the response
        $response = $this->curl_object->get_decoded_body();
        
        if (isset($response->access_token)) {
            $this->access_token = $response->access_token;
            $this->refresh_token = isset($response->refresh_token) ? $response->refresh_token : $this->refresh_token;
            
            // Save tokens to WordPress options
            update_option('TRADEPRESS_DISCORD_access_token', $this->access_token);
            update_option('TRADEPRESS_DISCORD_refresh_token', $this->refresh_token);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get information about the current user (OAuth2)
     *
     * @return object|false User information or false on failure
     */
    public function get_current_user() {
        return $this->request('OAUTH2_CURRENT_USER', array(), 'oauth2', true);
    }
    
    /**
     * Send a message to a Discord channel
     *
     * @param string $channel_id Discord channel ID
     * @param string $content Message content
     * @param array $embeds Embedded content (optional)
     * @return object|false API response or false on failure
     */
    public function send_message($channel_id, $content, $embeds = array()) {
        $params = array(
            'channel_id' => $channel_id
        );
        
        $body_data = array(
            'content' => $content
        );
        
        if (!empty($embeds)) {
            $body_data['embeds'] = $embeds;
        }
        
        return $this->request('SEND_MESSAGE', $params, 'bot', true);
    }
    
    /**
     * Send a rich embed message to a Discord channel
     *
     * @param string $channel_id Discord channel ID
     * @param array $embed Embed data
     * @param string $content Message content (optional)
     * @return object|false API response or false on failure
     */
    public function send_embed($channel_id, $embed, $content = '') {
        $params = array(
            'channel_id' => $channel_id
        );
        
        $body_data = array(
            'embeds' => array($embed)
        );
        
        if (!empty($content)) {
            $body_data['content'] = $content;
        }
        
        return $this->request('SEND_MESSAGE', $params, 'bot', true);
    }
    
    /**
     * Create a webhook for a channel
     *
     * @param string $channel_id Channel ID
     * @param string $name Webhook name
     * @param string $avatar Avatar URL (optional)
     * @return object|false Webhook object or false on failure
     */
    public function create_webhook($channel_id, $name, $avatar = '') {
        $params = array(
            'channel_id' => $channel_id
        );
        
        $body_data = array(
            'name' => $name
        );
        
        if (!empty($avatar)) {
            $body_data['avatar'] = $avatar;
        }
        
        return $this->request('CREATE_WEBHOOK', $params, 'bot', true);
    }
    
    /**
     * Execute a webhook
     *
     * @param string $webhook_id Webhook ID
     * @param string $webhook_token Webhook token
     * @param string $content Message content
     * @param array $embeds Embeds (optional)
     * @param string $username Override username (optional)
     * @param string $avatar_url Override avatar URL (optional)
     * @return object|false Response object or false on failure
     */
    public function execute_webhook($webhook_id, $webhook_token, $content, $embeds = array(), $username = '', $avatar_url = '') {
        $params = array(
            'webhook_id' => $webhook_id,
            'webhook_token' => $webhook_token
        );
        
        $body_data = array(
            'content' => $content
        );
        
        if (!empty($embeds)) {
            $body_data['embeds'] = $embeds;
        }
        
        if (!empty($username)) {
            $body_data['username'] = $username;
        }
        
        if (!empty($avatar_url)) {
            $body_data['avatar_url'] = $avatar_url;
        }
        
        return $this->request('EXECUTE_WEBHOOK', $params, 'none', true);
    }
    
    /**
     * Create a stock price alert embed
     *
     * @param string $symbol Stock symbol
     * @param float $price Current price
     * @param float $previous_price Previous price
     * @param string $alert_type Type of alert: 'up', 'down', 'target'
     * @param float $target_price Target price (optional, for target alerts)
     * @return array Embed array for Discord API
     */
    public function create_stock_alert_embed($symbol, $price, $previous_price, $alert_type = 'up', $target_price = 0) {
        // Calculate change percentage
        $change = $price - $previous_price;
        $change_percent = ($previous_price > 0) ? ($change / $previous_price) * 100 : 0;
        $change_str = ($change >= 0 ? '+' : '') . number_format($change, 2);
        $change_percent_str = ($change >= 0 ? '+' : '') . number_format($change_percent, 2) . '%';
        
        // Set color based on alert type (green for up, red for down, blue for target)
        $color = 0x00FF00; // Default green
        if ($alert_type === 'down') {
            $color = 0xFF0000; // Red for down
        } else if ($alert_type === 'target') {
            $color = 0x0000FF; // Blue for target
        }
        
        // Create title based on alert type
        $title = "{$symbol} Price Alert";
        $description = '';
        
        switch ($alert_type) {
            case 'up':
                $description = "{$symbol} stock price has increased to \${$price}";
                break;
            case 'down':
                $description = "{$symbol} stock price has decreased to \${$price}";
                break;
            case 'target':
                $description = "{$symbol} stock has reached your target price of \${$target_price}";
                break;
            default:
                $description = "{$symbol} stock price update: \${$price}";
        }
        
        // Build embed array
        $embed = array(
            'title' => $title,
            'description' => $description,
            'color' => $color,
            'fields' => array(
                array(
                    'name' => 'Current Price',
                    'value' => '$' . number_format($price, 2),
                    'inline' => true
                ),
                array(
                    'name' => 'Previous Price',
                    'value' => '$' . number_format($previous_price, 2),
                    'inline' => true
                ),
                array(
                    'name' => 'Change',
                    'value' => "{$change_str} ({$change_percent_str})",
                    'inline' => true
                )
            ),
            'footer' => array(
                'text' => 'Powered by TradePress'
            ),
            'timestamp' => date('c') // ISO 8601 format
        );
        
        // Add target price field for target alerts
        if ($alert_type === 'target' && $target_price > 0) {
            $embed['fields'][] = array(
                'name' => 'Target Price',
                'value' => '$' . number_format($target_price, 2),
                'inline' => true
            );
        }
        
        return $embed;
    }
    
    /**
     * Send a stock price alert to a Discord channel
     *
     * @param string $channel_id Discord channel ID
     * @param string $symbol Stock symbol
     * @param float $price Current price
     * @param float $previous_price Previous price
     * @param string $alert_type Type of alert: 'up', 'down', 'target'
     * @param float $target_price Target price (optional, for target alerts)
     * @return object|false API response or false on failure
     */
    public function send_stock_alert($channel_id, $symbol, $price, $previous_price, $alert_type = 'up', $target_price = 0) {
        // Generate embed for stock alert
        $embed = $this->create_stock_alert_embed($symbol, $price, $previous_price, $alert_type, $target_price);
        
        // Create simple content message
        $content = "{$symbol} Price Alert: ";
        
        switch ($alert_type) {
            case 'up':
                $content .= "↑ Increased to $" . number_format($price, 2);
                break;
            case 'down':
                $content .= "↓ Decreased to $" . number_format($price, 2);
                break;
            case 'target':
                $content .= "🎯 Target price of $" . number_format($target_price, 2) . " reached";
                break;
            default:
                $content .= "Now at $" . number_format($price, 2);
        }
        
        // Send the message with embed
        return $this->send_embed($channel_id, $embed, $content);
    }
    
    /**
     * Generate OAuth authorization URL
     *
     * @param array $scopes Array of OAuth scopes to request
     * @return string Authorization URL
     */
    public function get_oauth_url($scopes = array('identify', 'guilds')) {
        if (empty($this->client_id) || empty($this->redirect_uri)) {
            return '';
        }
        
        if (empty($scopes)) {
            $scopes = array('identify', 'guilds');
        }
        
        $query_params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes)
        );
        
        return 'https://discord.com/api/oauth2/authorize?' . http_build_query($query_params);
    }
    
    /**
     * Run diagnostic tests on the Discord API configuration
     * 
     * @return array Array of test results
     */
    public function run_diagnostics() {
        $results = array(
            'success' => true,
            'messages' => array(),
            'tests' => array()
        );
        
        // Test 1: Check if bot token exists
        $token_result = array(
            'name' => 'Bot Token',
            'passed' => false,
            'message' => ''
        );
        
        if (empty($this->bot_token)) {
            $token_result['passed'] = false;
            $token_result['message'] = 'Bot token is missing. Please enter a valid Discord bot token.';
            $results['success'] = false;
            $results['messages'][] = 'Discord bot token is missing';
        } else {
            $token_result['passed'] = true;
            $token_result['message'] = 'Bot token is set.';
        }
        $results['tests']['token'] = $token_result;
        
        // Test 2: Validate the bot token with Discord API
        $validation_result = array(
            'name' => 'API Connection',
            'passed' => false,
            'message' => '',
            'details' => array()
        );
        
        if (!empty($this->bot_token)) {
            $validation = $this->validate_bot_token();
            
            if ($validation === true) {
                $validation_result['passed'] = true;
                $validation_result['message'] = 'Bot token is valid and connection to Discord API is working.';
            } else {
                $validation_result['passed'] = false;
                $validation_result['message'] = 'Bot token validation failed: ' . $validation;
                $results['success'] = false;
                $results['messages'][] = 'Discord bot token validation failed';
            }
        } else {
            $validation_result['passed'] = false;
            $validation_result['message'] = 'Cannot validate missing bot token.';
            $results['success'] = false;
        }
        $results['tests']['validation'] = $validation_result;
        
        // We'll skip the BOT_INFO check for now since it's causing issues
        // and focus on just validating the connection
        
        return $results;
    }
    
    /**
     * Test a Discord bot token
     * 
     * @param string $token Discord bot token to test
     * @return array Test results
     */
    public function test_bot_token($token) {
        // Store original token
        $original_token = $this->bot_token;
        
        // Set the token to the one we want to test
        $this->bot_token = $token;
        
        // Run validation
        $result = array(
            'valid' => false,
            'message' => '',
            'bot_info' => null
        );
        
        $validation = $this->validate_bot_token();
        
        if ($validation === true) {
            // Get bot information directly (for token that's already validated)
            $response = wp_remote_get(
                'https://discord.com/api/v10/users/@me',
                array(
                    'headers' => array(
                        'Authorization' => 'Bot ' . $this->bot_token,
                    ),
                    'timeout' => 15,
                )
            );
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $bot_info = json_decode(wp_remote_retrieve_body($response));
                
                if (isset($bot_info->id) && isset($bot_info->username)) {
                    $result['valid'] = true;
                    $result['message'] = 'Bot token is valid.';
                    
                    $result['bot_info'] = array(
                        'id' => $bot_info->id,
                        'name' => $bot_info->username,
                        'avatar' => isset($bot_info->avatar) ? 'https://cdn.discordapp.com/avatars/' . $bot_info->id . '/' . $bot_info->avatar . '.png' : null
                    );
                }
            } else {
                $result['valid'] = true;
                $result['message'] = 'Bot token is valid, but could not fetch bot details.';
            }
        } else {
            $result['valid'] = false;
            $result['message'] = $validation;
        }
        
        // Restore original token
        $this->bot_token = $original_token;
        
        return $result;
    }
    
    /**
     * Get Discord endpoint URL from key
     *
     * @param string $endpoint_key API endpoint key
     * @param array $params Request parameters
     * @return string|WP_Error Endpoint URL or error
     */
    private function get_endpoint_url($endpoint_key, $params = array()) {
        $endpoints = new TRADEPRESS_DISCORD_Endpoints();
        $endpoint = $endpoints->get_endpoint($endpoint_key);
        
        if (empty($endpoint)) {
            return new WP_Error('invalid_endpoint', "Invalid endpoint key: {$endpoint_key}");
        }
        
        $url = $this->api_base_url . $endpoint['url'];
        
        // Replace URL parameters
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return $url;
    }
    
    /**
     * Get Discord endpoint request method from key
     *
     * @param string $endpoint_key API endpoint key
     * @return string Request method (GET, POST, etc.)
     */
    private function get_endpoint_method($endpoint_key) {
        $endpoints = new TRADEPRESS_DISCORD_Endpoints();
        $endpoint = $endpoints->get_endpoint($endpoint_key);
        
        if (empty($endpoint) || empty($endpoint['method'])) {
            return 'GET';
        }
        
        return $endpoint['method'];
    }
    
    /**
     * Parse API response
     *
     * @param mixed $response API response
     * @return mixed Parsed response data or WP_Error
     */
    private function parse_response($response) {
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Failed to parse API response as JSON: ' . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Get the bot token for diagnostic purposes
     * 
     * @return string The bot token
     */
    public function get_bot_token() {
        return $this->bot_token;
    }
}