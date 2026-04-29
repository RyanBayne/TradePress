<?php
/**
 * TradePress AJAX Handlers
 *
 * Handles AJAX requests for the TradePress plugin
 *
 * @package TradePress/Admin
 * @version 1.0.7
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register AJAX handlers
  *
  * @version 1.0.0
 */
function tradepress_register_ajax_handlers() {
    add_action('wp_ajax_update_trading_mode', 'tradepress_ajax_update_trading_mode');
    add_action('wp_ajax_update_api_operational_status', 'tradepress_ajax_update_api_operational_status');
    add_action('wp_ajax_test_api_credentials', 'tradepress_ajax_test_api_credentials');
    add_action('wp_ajax_tradepress_render_github_markdown', 'tradepress_render_github_markdown_ajax');
    add_action('wp_ajax_tradepress_get_api_call_details', 'tradepress_ajax_get_api_call_details');
    add_action('wp_ajax_tradepress_ai_diagram_analysis', 'tradepress_ajax_ai_diagram_analysis');

}
add_action('init', 'tradepress_register_ajax_handlers');



// Register specialized AJAX handlers
add_action('wp_ajax_tradepress_save_discord_webhooks', 'tradepress_ajax_save_discord_webhooks');
add_action('wp_ajax_tradepress_test_discord_webhooks', 'tradepress_ajax_test_discord_webhooks');
add_action('wp_ajax_tradepress_get_discord_status', 'tradepress_ajax_get_discord_status');

/**
 * AJAX handler for updating trading mode toggle
  *
  * @version 1.0.0
 */
function tradepress_ajax_update_trading_mode() {
    // Sanitize api_id first so it can be safely used in nonce key construction.
    $api_id = isset( $_POST['api_id'] ) ? sanitize_key( wp_unslash( $_POST['api_id'] ) ) : '';
    if ( empty( $api_id ) ) {
        wp_send_json_error( array( 'message' => 'Missing API identifier' ) );
        return;
    }

    // Verify nonce
    $nonce_key  = 'tradepress_' . $api_id . '_trading_mode_nonce';
    $nonce_action = 'tradepress_' . $api_id . '_api_settings';
    $nonce_value  = isset( $_POST[ $nonce_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonce_key ] ) ) : '';
    if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, $nonce_action ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed' ) );
        return;
    }

    // Check if user has permission
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Permission denied' ) );
        return;
    }

    $trading_mode = isset( $_POST[ 'TradePress_api_' . $api_id . '_trading_mode' ] ) ?
                  sanitize_text_field( wp_unslash( $_POST[ 'TradePress_api_' . $api_id . '_trading_mode' ] ) ) : 'paper';

    // Save the trading mode
    update_option('TradePress_api_' . $api_id . '_trading_mode', $trading_mode);

    // Return success
    wp_send_json_success(array(
        'message' => 'Trading mode updated successfully',
        'trading_mode' => $trading_mode,
        'api_id' => $api_id
     ));
}

/**
 * AJAX handler for updating API operational status toggle
  *
  * @version 1.0.0
 */
function tradepress_ajax_update_api_operational_status() {
    // Sanitize api_id first so it can be safely used in nonce key construction.
    $api_id = isset( $_POST['api_id'] ) ? sanitize_key( wp_unslash( $_POST['api_id'] ) ) : '';
    if ( empty( $api_id ) ) {
        wp_send_json_error( array( 'message' => 'Missing API identifier' ) );
        return;
    }

    // Verify nonce
    $nonce_key    = 'tradepress_' . $api_id . '_operational_nonce';
    $nonce_action = 'tradepress_' . $api_id . '_api_settings';
    $nonce_value  = isset( $_POST[ $nonce_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonce_key ] ) ) : '';
    if ( empty( $nonce_value ) || ! wp_verify_nonce( $nonce_value, $nonce_action ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed' ) );
        return;
    }

    // Check if user has permission
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Permission denied' ) );
        return;
    }

    $api_enabled = isset( $_POST[ 'TradePress_switch_' . $api_id . '_api_services' ] ) ?
                 sanitize_text_field( wp_unslash( $_POST[ 'TradePress_switch_' . $api_id . '_api_services' ] ) ) : 'no';

    // Save the API enabled setting
    update_option('TradePress_switch_' . $api_id . '_api_services', $api_enabled);

    // Return success
    wp_send_json_success(array(
        'message' => 'API operational status updated successfully',
        'status' => $api_enabled,
        'api_id' => $api_id
     ));
}

/**
 * AJAX handler for testing API credentials
  *
  * @version 1.0.0
 */
function tradepress_ajax_test_api_credentials() {
    // Verify nonce
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'test-api-credentials')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }

    // Get parameters
    $api_id = isset($_POST['api_id']) ? sanitize_key($_POST['api_id']) : '';
    $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'paper'; // 'paper' or 'real'
    
    if (empty($api_id)) {
        wp_send_json_error(array('message' => 'API identifier is missing'));
        return;
    }
    
    // Get API credentials based on mode
    if ($mode === 'real') {
        $api_key = get_option('TradePress_api_' . $api_id . '_realmoney_apikey', '');
        $api_secret = get_option('TradePress_api_' . $api_id . '_realmoney_secretkey', '');
    } else {
        $api_key = get_option('TradePress_api_' . $api_id . '_papermoney_apikey', '');
        $api_secret = get_option('TradePress_api_' . $api_id . '_papermoney_secretkey', '');
    }
    
    // Check if credentials are set
    if (empty($api_key)) {
        wp_send_json_error(array(
            'message' => 'API key is not configured',
            'api_id' => $api_id,
            'mode' => $mode
        ));
        return;
    }
    
    // For APIs that require a secret key (not Alpaca), check if it's set
    if ($api_id !== 'alpaca' && empty($api_secret)) {
        wp_send_json_error(array(
            'message' => 'API secret key is not configured',
            'api_id' => $api_id,
            'mode' => $mode
        ));
        return;
    }
    
    // Test the API based on the API identifier
    $test_result = tradepress_test_api_connection($api_id, $mode);
    
    if ($test_result['success']) {
        wp_send_json_success(array(
            'message' => $test_result['message'],
            'api_id' => $api_id,
            'mode' => $mode,
            'details' => isset($test_result['details']) ? $test_result['details'] : ''
        ));
    } else {
        wp_send_json_error(array(
            'message' => $test_result['message'],
            'api_id' => $api_id,
            'mode' => $mode,
            'details' => isset($test_result['details']) ? $test_result['details'] : ''
        ));
    }
}

/**
 * Test API connection based on API identifier
 *
 * @param string $api_id The API identifier
 * @param string $mode The mode (paper or real)
 * @return array Result of the test with success status and message
  * @version 1.0.0
 */
function tradepress_test_api_connection($api_id, $mode = 'paper') {
    // Get the credentials based on mode
    if ($mode === 'real') {
        $api_key = get_option('TradePress_api_' . $api_id . '_realmoney_apikey', '');
        $api_secret = get_option('TradePress_api_' . $api_id . '_realmoney_secretkey', '');
    } else {
        $api_key = get_option('TradePress_api_' . $api_id . '_papermoney_apikey', '');
        $api_secret = get_option('TradePress_api_' . $api_id . '_papermoney_secretkey', '');
    }
    
    // Check if there's a specific test function for this API
    $test_function = 'tradepress_test_' . $api_id . '_api_connection';
    if (function_exists($test_function)) {
        return call_user_func($test_function, $api_key, $api_secret, $mode);
    }
    
    // Default implementation for a simple connection test
    // This is a basic implementation that should be customized for each API
    $endpoint = '';
    $headers = array();
    
    switch ($api_id) {
        case 'alpaca':
            $endpoint = $mode === 'paper' 
                ? 'https://paper-api.alpaca.markets/v2/account' 
                : 'https://api.alpaca.markets/v2/account';
            $headers = array(
                'APCA-API-KEY-ID' => $api_key,
                'APCA-API-SECRET-KEY' => $api_secret
            );
            break;
            
        case 'alphavantage':
            $endpoint = 'https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=IBM&interval=5min&apikey=' . $api_key;
            break;
            
        case 'alltick':
            // Implement specific API test
            return array(
                'success' => false,
                'message' => 'AllTick API testing is not implemented yet'
            );
            
        // Add cases for other APIs
            
        default:
            return array(
                'success' => false,
                'message' => 'Unknown API identifier or testing not implemented'
            );
    }
    
    // Make the request
    $response = wp_remote_get($endpoint, array(
        'headers' => $headers,
        'timeout' => 15
    ));
    
    // Store API call information in a transient for the Latest API Call Information section
    $api_call_data = array(
        'api_id' => $api_id,
        'endpoint' => $endpoint,
        'method' => 'GET',
        'timestamp' => current_time('mysql'),
        'request_data' => array(
            'headers' => $headers,
            'mode' => $mode
        ),
        'response' => is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response)
    );
    
    // Store the API call information in a transient
    set_transient('tradepress_latest_api_call_' . $api_id, $api_call_data, 300); // Store for 5 minutes
    
    // Check if the request was successful
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Request failed: ' . $response->get_error_message()
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    if ($response_code >= 200 && $response_code < 300) {
        return array(
            'success' => true,
            'message' => 'API connection successful',
            'details' => array(
                'response_code' => $response_code,
                'response_body' => $response_body
            )
        );
    } else {
        return array(
            'success' => false,
            'message' => 'API returned error code: ' . $response_code,
            'details' => array(
                'response_code' => $response_code,
                'response_body' => $response_body
            )
        );
    }
}

// =============================================================================
// SPECIALIZED AJAX HANDLERS (from includes/ajax/ajax-handlers.php)
// =============================================================================

/**
 * Handle AJAX request to test Alpaca API endpoints
  *
  * @version 1.0.0
 */
function tradepress_test_alpaca_endpoint() {
    // Check nonce for security
    check_ajax_referer('tradepress_test_alpaca_endpoint', 'security');
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tradepress')));
    }
    
    // Get the endpoint to test
    $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
    if (empty($endpoint)) {
        wp_send_json_error(array('message' => __('No endpoint specified.', 'tradepress')));
    }
    
    // Get trading mode
    $trading_mode = isset($_POST['trading_mode']) ? sanitize_text_field($_POST['trading_mode']) : 'paper';
    
    // Load the Alpaca Direct API handler
    $api_class_path = TRADEPRESS_PLUGIN_DIR_PATH . 'api/alpaca/alpaca-direct.php';
    if (!file_exists($api_class_path)) {
        wp_send_json_error(array('message' => __('Alpaca Direct API class not found.', 'tradepress')));
    }
    
    require_once $api_class_path;
    
    // Get API credentials based on trading mode
    if ($trading_mode === 'paper') {
        $api_key = get_option('TradePress_api_alpaca_papermoney_apikey', '');
        $api_secret = get_option('TradePress_api_alpaca_papermoney_secretkey', '');
    } else {
        $api_key = get_option('TradePress_api_alpaca_realmoney_apikey', '');
        $api_secret = get_option('TradePress_api_alpaca_realmoney_secretkey', '');
    }
    
    // Check if credentials are configured
    if (empty($api_key) || empty($api_secret)) {
        wp_send_json_error(array(
            'message' => __('API credentials are not configured', 'tradepress'),
            'details' => array(
                'trading_mode' => $trading_mode,
                'has_key' => !empty($api_key),
                'has_secret' => !empty($api_secret)
            )
        ));
        return;
    }
    
    try {
        // Initialize the API client
        $api = new TradePress_Alpaca_Direct($api_key, $api_secret, $trading_mode);
        
        // Make request based on endpoint
        if ($endpoint === 'account') {
            $result = $api->get_account();
        } else if ($endpoint === 'watchlists') {
            $result = $api->get_watchlists();
        } else {
            $result = $api->request($endpoint);
        }
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error(array(
                'message' => $result['message'],
                'details' => array(
                    'endpoint' => $endpoint,
                    'trading_mode' => $trading_mode,
                    'code' => isset($result['code']) ? $result['code'] : null
                )
            ));
        }
    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => $e->getMessage(),
            'details' => array(
                'endpoint' => $endpoint,
                'trading_mode' => $trading_mode
            )
        ));
    }
}
add_action('wp_ajax_tradepress_test_alpaca_endpoint', 'tradepress_test_alpaca_endpoint');

/**
 * Ajax handler for saving Discord settings
  *
  * @version 1.0.0
 */
function tradepress_ajax_save_discord_settings() {
    // Verify nonce
    if (!check_ajax_referer('TRADEPRESS_DISCORD_settings_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid security token');
    }
    
    // Verify user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action');
    }
    
    // Get the values from the request
    $bot_token = isset($_POST['bot_token']) ? sanitize_text_field($_POST['bot_token']) : '';
    $client_id = isset($_POST['client_id']) ? sanitize_text_field($_POST['client_id']) : '';
    $client_secret = isset($_POST['client_secret']) ? sanitize_text_field($_POST['client_secret']) : '';
    $redirect_uri = isset($_POST['redirect_uri']) ? esc_url_raw($_POST['redirect_uri']) : '';
    
    // Save the settings
    update_option('TRADEPRESS_DISCORD_bot_token', $bot_token);
    update_option('TRADEPRESS_DISCORD_client_id', $client_id);
    update_option('TRADEPRESS_DISCORD_client_secret', $client_secret);
    update_option('TRADEPRESS_DISCORD_redirect_uri', $redirect_uri);
    
    // Return success response
    wp_send_json_success();
}
add_action('wp_ajax_tradepress_save_discord_settings', 'tradepress_ajax_save_discord_settings');

/**
 * Ajax handler for testing Discord connection
  *
  * @version 1.0.0
 */
function tradepress_ajax_test_discord_connection() {
    // Verify nonce
    if (!check_ajax_referer('TRADEPRESS_DISCORD_test_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid security token');
    }
    
    // Verify user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action');
    }
    
    // Get the bot token from the request
    $bot_token = isset($_POST['bot_token']) ? sanitize_text_field($_POST['bot_token']) : '';
    
    if (empty($bot_token)) {
        wp_send_json_error(array('message' => 'Bot token is required'));
    }
    
    // Load Discord API class
    if (!class_exists('TRADEPRESS_DISCORD_API')) {
        require_once(trailingslashit(TRADEPRESS_PLUGIN_DIR_PATH) . 'api/discord/discord-api.php');
    }
    
    // Initialize the Discord API instance
    $discord_api = new TRADEPRESS_DISCORD_API();
    
    // Test the bot token
    $result = $discord_api->test_bot_token($bot_token);
    
    // Return the result
    wp_send_json_success($result);
}
add_action('wp_ajax_tradepress_test_discord_connection', 'tradepress_ajax_test_discord_connection');

/**
 * Ajax handler for saving Discord webhooks
  *
  * @version 1.0.0
 */
function tradepress_ajax_save_discord_webhooks() {
    // Verify nonce
    if (!check_ajax_referer('TRADEPRESS_DISCORD_webhooks_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid security token');
    }
    
    // Verify user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action');
    }
    
    // Get the values from the request
    $stock_alerts_webhook = isset($_POST['stock_alerts_webhook']) ? esc_url_raw($_POST['stock_alerts_webhook']) : '';
    $news_webhook = isset($_POST['news_webhook']) ? esc_url_raw($_POST['news_webhook']) : '';
    $market_updates_webhook = isset($_POST['market_updates_webhook']) ? esc_url_raw($_POST['market_updates_webhook']) : '';
    
    // Save the webhooks
    update_option('TRADEPRESS_DISCORD_stock_alerts_webhook', $stock_alerts_webhook);
    update_option('TRADEPRESS_DISCORD_news_webhook', $news_webhook);
    update_option('TRADEPRESS_DISCORD_market_updates_webhook', $market_updates_webhook);
    
    // Return success response
    wp_send_json_success();
}

/**
 * Ajax handler for testing Discord webhooks
  *
  * @version 1.0.0
 */
function tradepress_ajax_test_discord_webhooks() {
    // Verify nonce
    if (!check_ajax_referer('TRADEPRESS_DISCORD_webhook_test_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid security token');
    }
    
    // Verify user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action');
    }
    
    // Get the webhook URLs from the request
    $stock_alerts_webhook = isset($_POST['stock_alerts_webhook']) ? esc_url_raw($_POST['stock_alerts_webhook']) : '';
    $news_webhook = isset($_POST['news_webhook']) ? esc_url_raw($_POST['news_webhook']) : '';
    $market_updates_webhook = isset($_POST['market_updates_webhook']) ? esc_url_raw($_POST['market_updates_webhook']) : '';
    
    $errors = array();
    
    // Test stock alerts webhook
    if (!empty($stock_alerts_webhook)) {
        $test_result = test_discord_webhook($stock_alerts_webhook, 'Test Stock Alert');
        if (is_wp_error($test_result)) {
            $errors[] = 'Stock Alerts Webhook Error: ' . $test_result->get_error_message();
        }
    }
    
    // Test news webhook
    if (!empty($news_webhook)) {
        $test_result = test_discord_webhook($news_webhook, 'Test News Alert');
        if (is_wp_error($test_result)) {
            $errors[] = 'News Webhook Error: ' . $test_result->get_error_message();
        }
    }
    
    // Test market updates webhook
    if (!empty($market_updates_webhook)) {
        $test_result = test_discord_webhook($market_updates_webhook, 'Test Market Update');
        if (is_wp_error($test_result)) {
            $errors[] = 'Market Updates Webhook Error: ' . $test_result->get_error_message();
        }
    }
    
    // Return errors or success
    if (!empty($errors)) {
        wp_send_json_error(implode("\n", $errors));
    } else {
        wp_send_json_success();
    }
}

/**
 * Helper function to test a Discord webhook
  *
  * @version 1.0.0
  *
  * @param mixed $webhook_url
  * @param string $message
 */
function test_discord_webhook($webhook_url, $message = 'Test Message') {
    $timestamp = date('c');
    $data = array(
        'content' => null,
        'embeds' => array(
            array(
                'title' => 'TradePress Webhook Test',
                'description' => $message,
                'color' => 3447003,
                'fields' => array(
                    array(
                        'name' => 'Site',
                        'value' => get_bloginfo('name'),
                        'inline' => true
                    ),
                    array(
                        'name' => 'URL',
                        'value' => site_url(),
                        'inline' => true
                    )
                ),
                'footer' => array(
                    'text' => 'TradePress Discord Integration'
                ),
                'timestamp' => $timestamp
            )
        ),
        'username' => 'TradePress Bot'
    );
    
    $args = array(
        'body' => json_encode($data),
        'headers' => array(
            'Content-Type' => 'application/json'
        ),
        'timeout' => 15
    );
    
    $response = wp_remote_post($webhook_url, $args);
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    
    if ($response_code < 200 || $response_code >= 300) {
        $body = wp_remote_retrieve_body($response);
        return new WP_Error(
            'webhook_error',
            'Discord webhook returned error code: ' . $response_code . ' - ' . $body
        );
    }
    
    return true;
}

/**
 * Ajax handler for getting Discord connection status
  *
  * @version 1.0.0
 */
function tradepress_ajax_get_discord_status() {
    // Verify nonce
    if (!check_ajax_referer('TRADEPRESS_DISCORD_status_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid security token');
    }
    
    // Verify user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have permission to perform this action');
    }
    
    // Load Discord API class
    if (!class_exists('TRADEPRESS_DISCORD_API')) {
        require_once(trailingslashit(TRADEPRESS_PLUGIN_DIR_PATH) . 'api/discord/discord-api.php');
    }
    
    // Initialize the Discord API instance
    $discord_api = new TRADEPRESS_DISCORD_API();
    
    // Get diagnostic results
    $diagnostics = $discord_api->run_diagnostics();
    
    wp_send_json_success(array(
        'html' => 'Discord status loaded',
        'data' => $diagnostics
    ));
}

// Legacy API connection test function
/**
 * Legacy test api connection.
 *
 * @param mixed $api_id
 * @param string $mode
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function tradepress_legacy_test_api_connection($api_id, $mode = 'paper') {
    set_transient('tradepress_latest_api_call_' . $api_id, array(), 24 * HOUR_IN_SECONDS);
    return array(
        'success' => false,
        'message' => 'Legacy function - use main tradepress_test_api_connection instead'
    );
}

/**
 * Render GitHub Markdown via AJAX
  *
  * @version 1.0.0
 */
function tradepress_render_github_markdown_ajax() {
    // Check nonce
    check_ajax_referer('tradepress_markdown_preview', 'security');
    
    // Check if user has permission
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => __('You do not have permission to do this.', 'tradepress')
        ));
    }
    
    // Get markdown text
    $markdown = isset($_POST['markdown']) ? wp_unslash($_POST['markdown']) : '';
    
    if (empty($markdown)) {
        wp_send_json_error(array(
            'message' => __('No markdown provided.', 'tradepress')
        ));
    }
    
    $html = '';
    
    // Get GitHub API settings
    $github_token = get_option('TRADEPRESS_GITHUB_token', '');
    
    // Use GitHub API to render markdown if token exists
    if (!empty($github_token)) {
        $api_url = 'https://api.github.com/markdown';
        
        $response = wp_remote_post(
            $api_url,
            array(
                'headers' => array(
                    'Accept' => 'application/vnd.github+json',
                    'Authorization' => 'Bearer ' . $github_token,
                    'User-Agent' => 'TradePress',
                    'X-GitHub-Api-Version' => '2022-11-28',
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'text' => $markdown,
                    'mode' => 'gfm',
                )),
                'timeout' => 15,
            )
        );
        
        if (!is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code === 200) {
                $html = wp_remote_retrieve_body($response);
            }
        }
    }
    
    // Fall back to basic markdown parsing if GitHub API call failed
    if (empty($html)) {
        $html = wpautop(wp_kses_post(TRADEPRESS_GITHUB_parse_markdown($markdown)));
    }
    
    wp_send_json_success(array(
        'html' => $html
    ));
}
add_action('wp_ajax_tradepress_render_github_markdown', 'tradepress_render_github_markdown_ajax');

/**
 * AJAX handler for getting API call details.
 *
 * Retrieves a single API call record along with its associated errors,
 * metadata, and endpoint statistics, then returns them as an HTML fragment.
 * All translatable label strings are output via esc_html_e() rather than
 * the bare _e() function to satisfy WordPress.Security.EscapeOutput
 * (E-86a150cb). Dynamic values from the database are escaped via esc_html()
 * or esc_attr() before output.
 *
 * @since 1.0.7
 * @version 1.0.95
 */
function tradepress_ajax_get_api_call_details() {
    // Check nonce
    $security_nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
    if ( empty( $security_nonce ) || ! wp_verify_nonce( $security_nonce, 'tradepress_api_details_nonce' ) ) {
        wp_send_json_error( 'Security check failed' );
        return;
    }
    
    // Check for log ID
    if (!isset($_POST['log_id']) || empty($_POST['log_id'])) {
        wp_send_json_error('No log ID provided');
        return;
    }
    
    $log_id = intval($_POST['log_id']);
    
    global $wpdb;
    
    // Get the call details
    $call = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}tradepress_calls WHERE entryid = %d",
        $log_id
    ), ARRAY_A);
    
    if (!$call) {
        wp_send_json_error('API call not found');
        return;
    }
    
    // Get any errors associated with this call
    $errors = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}tradepress_errors WHERE entryid = %d ORDER BY timestamp DESC",
        $log_id
    ), ARRAY_A);
    
    // Get metadata for this call
    $meta = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}tradepress_meta WHERE entryid = %d ORDER BY timestamp DESC",
        $log_id
    ), ARRAY_A);
    
    // Get endpoint information
    $endpoint = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}tradepress_endpoints WHERE entryid = %d",
        $log_id
    ), ARRAY_A);
    
    // Build the HTML output.
    // All translatable strings use esc_html_e() instead of _e() so the output
    // is both translated and HTML-escaped, satisfying
    // WordPress.Security.EscapeOutput.UnsafePrintingFunction (E-86a150cb).
    ob_start();
    ?>
    <div class="api-details-section">
        <h3><?php esc_html_e('Basic Information', 'tradepress'); ?></h3>
        <table class="widefat fixed">
            <tr>
                <th><?php esc_html_e('Entry ID', 'tradepress'); ?></th>
                <td><?php echo esc_html($call['entryid']); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Service', 'tradepress'); ?></th>
                <td><?php echo esc_html($call['service']); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Function/Endpoint', 'tradepress'); ?></th>
                <td><?php echo esc_html($call['function']); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Type', 'tradepress'); ?></th>
                <td><?php echo esc_html($call['type']); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Status', 'tradepress'); ?></th>
                <td>
                    <span class="status-badge status-<?php echo esc_attr(strtolower($call['status'])); ?>">
                        <?php echo esc_html($call['status']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Timestamp', 'tradepress'); ?></th>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($call['timestamp']))); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Description', 'tradepress'); ?></th>
                <td><?php echo esc_html($call['description']); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Outcome', 'tradepress'); ?></th>
                <td><?php echo esc_html($call['outcome']); ?></td>
            </tr>
            <?php if (!empty($endpoint)): ?>
            <tr>
                <th><?php esc_html_e('Endpoint', 'tradepress'); ?></th>
                <td><?php echo esc_html($endpoint['endpoint']); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Call Count', 'tradepress'); ?></th>
                <td><?php echo esc_html($endpoint['counter']); ?> <?php esc_html_e('calls', 'tradepress'); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('First Used', 'tradepress'); ?></th>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($endpoint['firstuse']))); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <?php if (!empty($errors)): ?>
    <div class="api-details-section">
        <h3><?php esc_html_e('Errors', 'tradepress'); ?></h3>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Error Code', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Error Message', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Function', 'tradepress'); ?></th>
                    <th><?php esc_html_e('File', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Timestamp', 'tradepress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($errors as $error): ?>
                <tr>
                    <td><?php echo esc_html($error['code']); ?></td>
                    <td><?php echo esc_html($error['error']); ?></td>
                    <td><?php echo esc_html($error['function']); ?></td>
                    <td><?php echo esc_html(basename($error['file'])); ?></td>
                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($error['timestamp']))); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($meta)): ?>
    <div class="api-details-section">
        <h3><?php esc_html_e('Metadata', 'tradepress'); ?></h3>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Key', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Value', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Timestamp', 'tradepress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meta as $item): ?>
                <tr>
                    <td><?php echo esc_html($item['metakey']); ?></td>
                    <td>
                        <?php 
                        $value = maybe_unserialize($item['metavalue']);
                        if (is_array($value) || is_object($value)) {
                            echo '<pre>' . esc_html(print_r($value, true)) . '</pre>';
                        } else {
                            echo esc_html($value);
                        }
                        ?>
                    </td>
                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['timestamp']))); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($endpoint) && !empty($endpoint['parameters'])): ?>
    <div class="api-details-section">
        <h3><?php esc_html_e('Request Parameters', 'tradepress'); ?></h3>
        <?php 
        $parameters = json_decode($endpoint['parameters'], true);
        if (is_array($parameters) && !empty($parameters)):
        ?>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Parameter', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Value', 'tradepress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parameters as $param => $value): ?>
                <tr>
                    <td><?php echo esc_html($param); ?></td>
                    <td>
                        <?php 
                        if (is_array($value)) {
                            echo '<pre>' . esc_html(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>';
                        } else {
                            echo esc_html($value);
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p><?php esc_html_e('No parameters available for this request.', 'tradepress'); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php
    $html = ob_get_clean();
    wp_send_json_success($html);
}
add_action('wp_ajax_tradepress_get_api_call_details', 'tradepress_ajax_get_api_call_details');

/**
 * AJAX handler for AI diagram analysis
  *
  * @version 1.0.0
 */
function tradepress_ajax_ai_diagram_analysis() {
    // Include the AI analysis handler
    require_once(plugin_dir_path(__FILE__) . '../admin/ajax/ai-diagram-analysis.php');
    
    // Call the analysis function
    tradepress_handle_ai_diagram_analysis();
}
add_action('wp_ajax_tradepress_ai_diagram_analysis', 'tradepress_ajax_ai_diagram_analysis');
