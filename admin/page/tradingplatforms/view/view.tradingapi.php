<?php
/**
 * Admin View: API - Trading API Tab
 * 
 * Shows the same content as the general settings "Other API" tab
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider('tradingapi');

// Load the same content as the other API tab from general settings
$general_settings = array(
    'api_enabled' => get_option('tradepress_general_api_enabled', 'no'),
    'api_key' => get_option('tradepress_general_api_key', ''),
    'api_endpoint' => get_option('tradepress_general_api_endpoint', 'https://api.example.com/v1'),
    'api_timeout' => get_option('tradepress_general_api_timeout', '30'),
    'api_retry' => get_option('tradepress_general_api_retry', '3')
);

// Get available API providers
$api_providers = array(
    'provider1' => 'Provider One',
    'provider2' => 'Provider Two',
    'provider3' => 'Provider Three',
    'custom' => 'Custom Provider'
);

// Get selected provider
$selected_provider = get_option('tradepress_general_api_provider', 'provider1');

// Get API authentication methods
$auth_methods = array(
    'key' => 'API Key',
    'oauth' => 'OAuth 2.0',
    'basic' => 'Basic Auth',
    'token' => 'Bearer Token'
);

// Get selected auth method
$selected_auth = get_option('tradepress_general_api_auth_method', 'key');

// Check if demo mode is active
$is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;



<div class="wrap tradepress-api-settings">
    <h2><?php esc_html_e('General API Settings', 'tradepress'); ?></h2>
    
    <p><?php esc_html_e('Configure API settings for external services and data providers.', 'tradepress'); ?></p>
    
    <!-- Navigation Tabs -->
    <nav class="nav-tab-wrapper api-subtabs-wrapper">
        <a href="#general-settings" class="nav-tab nav-tab-active"><?php esc_html_e('General Settings', 'tradepress'); ?></a>
        <a href="#authentication" class="nav-tab"><?php esc_html_e('Authentication', 'tradepress'); ?></a>
        <a href="#rate-limiting" class="nav-tab"><?php esc_html_e('Rate Limiting', 'tradepress'); ?></a>
        <a href="#webhooks" class="nav-tab"><?php esc_html_e('Webhooks', 'tradepress'); ?></a>
        <a href="#logs" class="nav-tab"><?php esc_html_e('Logs', 'tradepress'); ?></a>
    </nav>
    
    <div class="api-settings-container">
        <form method="post" id="general-api-settings-form" action="">
            <?php wp_nonce_field('tradepress-general-api-settings'); ?>
            
            <!-- General Settings Tab -->
            <div id="general-settings" class="api-tab-content active">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="api_enabled"><?php esc_html_e('Enable API Integration', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <label class="switch">
                                    <input name="api_enabled" id="api_enabled" type="checkbox" value="yes" <?php checked($general_settings['api_enabled'], 'yes'); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Turn on to enable API integration with external services.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_provider"><?php esc_html_e('API Provider', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <select name="api_provider" id="api_provider">
                                    <?php foreach ($api_providers as $key => $name) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_provider, $key); ?>><?php echo esc_html($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php esc_html_e('Select your API data provider.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr id="custom_endpoint_row" <?php echo $selected_provider !== 'custom' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">
                                <label for="api_endpoint"><?php esc_html_e('API Endpoint', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_endpoint" id="api_endpoint" type="url" class="regular-text" value="<?php echo esc_attr($general_settings['api_endpoint']); ?>" placeholder="https://api.example.com/v1">
                                <p class="description"><?php esc_html_e('Enter the base URL for the API.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_timeout"><?php esc_html_e('Request Timeout', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_timeout" id="api_timeout" type="number" min="1" max="120" class="small-text" value="<?php echo esc_attr($general_settings['api_timeout']); ?>">
                                <span><?php esc_html_e('seconds', 'tradepress'); ?></span>
                                <p class="description"><?php esc_html_e('Maximum time to wait for API responses.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_retry"><?php esc_html_e('Retry Attempts', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_retry" id="api_retry" type="number" min="0" max="5" class="small-text" value="<?php echo esc_attr($general_settings['api_retry']); ?>">
                                <p class="description"><?php esc_html_e('Number of retry attempts for failed requests. Set to 0 to disable retries.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Authentication Tab -->
            <div id="authentication" class="api-tab-content">
                <h3><?php esc_html_e('API Authentication', 'tradepress'); ?></h3>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="api_auth_method"><?php esc_html_e('Authentication Method', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <select name="api_auth_method" id="api_auth_method">
                                    <?php foreach ($auth_methods as $key => $name) : ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_auth, $key); ?>><?php echo esc_html($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php esc_html_e('Select authentication method required by the API.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        
                        <!-- API Key Authentication -->
                        <tr class="auth-method-fields auth-key" <?php echo $selected_auth !== 'key' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">
                                <label for="api_key"><?php esc_html_e('API Key', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_key" id="api_key" type="text" class="regular-text" value="<?php echo esc_attr($general_settings['api_key']); ?>">
                                <p class="description"><?php esc_html_e('Enter your API key.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        
                        <!-- OAuth Authentication -->
                        <tr class="auth-method-fields auth-oauth" <?php echo $selected_auth !== 'oauth' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">
                                <label for="api_client_id"><?php esc_html_e('Client ID', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_client_id" id="api_client_id" type="text" class="regular-text" value="<?php echo esc_attr(get_option('tradepress_general_api_client_id', '')); ?>">
                                <p class="description"><?php esc_html_e('Enter your OAuth Client ID.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr class="auth-method-fields auth-oauth" <?php echo $selected_auth !== 'oauth' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">
                                <label for="api_client_secret"><?php esc_html_e('Client Secret', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_client_secret" id="api_client_secret" type="password" class="regular-text" value="<?php echo esc_attr(get_option('tradepress_general_api_client_secret', '')); ?>">
                                <p class="description"><?php esc_html_e('Enter your OAuth Client Secret.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr class="auth-method-fields auth-oauth" <?php echo $selected_auth !== 'oauth' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">
                                <label for="api_redirect_uri"><?php esc_html_e('Redirect URI', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_redirect_uri" id="api_redirect_uri" type="url" class="regular-text" value="<?php echo esc_url(admin_url('admin.php?page=tradepress_platforms&tab=tradingapi')); ?>" readonly>
                                <button type="button" class="button button-small copy-uri"><?php esc_html_e('Copy', 'tradepress'); ?></button>
                                <p class="description"><?php esc_html_e('Use this URL as the redirect URI in your OAuth application settings.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        
                        <!-- Basic Auth -->
                        <tr class="auth-method-fields auth-basic" <?php echo $selected_auth !== 'basic' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">
                                <label for="api_username"><?php esc_html_e('Username', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_username" id="api_username" type="text" class="regular-text" value="<?php echo esc_attr(get_option('tradepress_general_api_username', '')); ?>">
                                <p class="description"><?php esc_html_e('Enter your API username.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr class="auth-method-fields auth-basic" <?php echo $selected_auth !== 'basic' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">
                                <label for="api_password"><?php esc_html_e('Password', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_password" id="api_password" type="password" class="regular-text" value="<?php echo esc_attr(get_option('tradepress_general_api_password', '')); ?>">
                                <p class="description"><?php esc_html_e('Enter your API password.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        
                        <!-- Bearer Token -->
                        <tr class="auth-method-fields auth-token" <?php echo $selected_auth !== 'token' ? 'style="display:none;"' : ''; ?>>
                            <th scope="row">
                                <label for="api_token"><?php esc_html_e('Bearer Token', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_token" id="api_token" type="text" class="regular-text" value="<?php echo esc_attr(get_option('tradepress_general_api_token', '')); ?>">
                                <p class="description"><?php esc_html_e('Enter your Bearer Token.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="api-test-container">
                    <button type="button" id="test_api_connection" class="button button-secondary">
                        <?php esc_html_e('Test Connection', 'tradepress'); ?>
                    </button>
                    <div id="api-test-results" class="api-test-results" style="display:none;">
                        <h4><?php esc_html_e('API Connection Test Results', 'tradepress'); ?></h4>
                        <div class="test-result-content"></div>
                    </div>
                </div>
            </div>
            
            <!-- Rate Limiting Tab -->
            <div id="rate-limiting" class="api-tab-content">
                <h3><?php esc_html_e('Rate Limiting Settings', 'tradepress'); ?></h3>
                <p class="tab-description"><?php esc_html_e('Configure rate limiting to manage API request frequency and prevent quota overages.', 'tradepress'); ?></p>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="api_rate_limiting"><?php esc_html_e('Enable Rate Limiting', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <label class="switch">
                                    <input name="api_rate_limiting" id="api_rate_limiting" type="checkbox" value="yes" <?php checked(get_option('tradepress_general_api_rate_limiting', 'yes'), 'yes'); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Turn on to enable internal rate limiting for API requests.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_max_requests"><?php esc_html_e('Maximum Requests', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_max_requests" id="api_max_requests" type="number" min="1" class="small-text" value="<?php echo esc_attr(get_option('tradepress_general_api_max_requests', '60')); ?>">
                                <select name="api_time_period" id="api_time_period">
                                    <option value="minute" <?php selected(get_option('tradepress_general_api_time_period', 'minute'), 'minute'); ?>><?php esc_html_e('Per Minute', 'tradepress'); ?></option>
                                    <option value="hour" <?php selected(get_option('tradepress_general_api_time_period', 'minute'), 'hour'); ?>><?php esc_html_e('Per Hour', 'tradepress'); ?></option>
                                    <option value="day" <?php selected(get_option('tradepress_general_api_time_period', 'minute'), 'day'); ?>><?php esc_html_e('Per Day', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Maximum number of API requests allowed per time period.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_quota_buffer"><?php esc_html_e('Quota Buffer', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_quota_buffer" id="api_quota_buffer" type="number" min="0" max="100" class="small-text" value="<?php echo esc_attr(get_option('tradepress_general_api_quota_buffer', '10')); ?>">
                                <span class="percentage-symbol">%</span>
                                <p class="description"><?php esc_html_e('Reserve this percentage of your API quota as a safety buffer.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_rate_limiting_behavior"><?php esc_html_e('When Limit Reached', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <select name="api_rate_limiting_behavior" id="api_rate_limiting_behavior">
                                    <option value="queue" <?php selected(get_option('tradepress_general_api_rate_limiting_behavior', 'queue'), 'queue'); ?>><?php esc_html_e('Queue requests and process later', 'tradepress'); ?></option>
                                    <option value="error" <?php selected(get_option('tradepress_general_api_rate_limiting_behavior', 'queue'), 'error'); ?>><?php esc_html_e('Return error immediately', 'tradepress'); ?></option>
                                    <option value="wait" <?php selected(get_option('tradepress_general_api_rate_limiting_behavior', 'queue'), 'wait'); ?>><?php esc_html_e('Wait and retry automatically', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Choose how to handle requests when the rate limit is reached.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Webhooks Tab -->
            <div id="webhooks" class="api-tab-content">
                <h3><?php esc_html_e('Webhooks Configuration', 'tradepress'); ?></h3>
                <p class="tab-description"><?php esc_html_e('Configure incoming webhooks to receive real-time data from APIs.', 'tradepress'); ?></p>
                
                <div class="webhook-endpoint-info">
                    <h4><?php esc_html_e('Your Webhook Endpoint', 'tradepress'); ?></h4>
                    <div class="webhook-url-container">
                        <code id="webhook-url"><?php echo esc_url(site_url('wp-json/tradepress/v1/webhook')); ?></code>
                        <button type="button" class="button button-small copy-webhook"><?php esc_html_e('Copy', 'tradepress'); ?></button>
                    </div>
                    <p class="description"><?php esc_html_e('Use this URL in your API provider settings to receive webhook notifications.', 'tradepress'); ?></p>
                </div>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="api_enable_webhooks"><?php esc_html_e('Enable Webhooks', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <label class="switch">
                                    <input name="api_enable_webhooks" id="api_enable_webhooks" type="checkbox" value="yes" <?php checked(get_option('tradepress_general_api_enable_webhooks', 'no'), 'yes'); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Turn on to accept incoming webhook data from the API.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="api_webhook_secret"><?php esc_html_e('Webhook Secret', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input name="api_webhook_secret" id="api_webhook_secret" type="text" class="regular-text" value="<?php echo esc_attr(get_option('tradepress_general_api_webhook_secret', '')); ?>">
                                <button type="button" id="generate_webhook_secret" class="button button-secondary"><?php esc_html_e('Generate', 'tradepress'); ?></button>
                                <p class="description"><?php esc_html_e('Secret key to validate incoming webhooks. Set this same value in your API provider settings.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Subscribed Events Section -->
                <h4><?php esc_html_e('Webhook Events', 'tradepress'); ?></h4>
                <p class="description"><?php esc_html_e('Select the events you want to receive via webhooks:', 'tradepress'); ?></p>
                
                <div class="webhook-events">
                    <?php
                    $webhook_events = array(
                        'data_update' => __('Data Updates', 'tradepress'),
                        'price_alert' => __('Price Alerts', 'tradepress'),
                        'order_execution' => __('Order Execution', 'tradepress'),
                        'account_activity' => __('Account Activity', 'tradepress'),
                        'market_event' => __('Market Events', 'tradepress')
                    );
                    
                    $selected_events = get_option('tradepress_general_api_webhook_events', array('data_update'));
                    
                    foreach ($webhook_events as $event => $label) :
                        $checked = in_array($event, (array)$selected_events) ? 'checked="checked"' : '';
                    ?>
                    <div class="webhook-event-item">
                        <label>
                            <input type="checkbox" name="api_webhook_events[]" value="<?php echo esc_attr($event); ?>" <?php echo $checked; ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Logs Tab -->
            <div id="logs" class="api-tab-content">
                <h3><?php esc_html_e('API Activity Logs', 'tradepress'); ?></h3>
                <p class="tab-description"><?php esc_html_e('View and manage logs of API interactions for troubleshooting and debugging.', 'tradepress'); ?></p>
                
                <div class="log-controls">
                    <label for="log_level"><?php esc_html_e('Log Level:', 'tradepress'); ?></label>
                    <select name="log_level" id="log_level">
                        <option value="all"><?php esc_html_e('All', 'tradepress'); ?></option>
                        <option value="error"><?php esc_html_e('Errors only', 'tradepress'); ?></option>
                        <option value="warning"><?php esc_html_e('Warnings & Errors', 'tradepress'); ?></option>
                        <option value="info"><?php esc_html_e('Info & above', 'tradepress'); ?></option>
                    </select>
                    
                    <button type="button" id="refresh_logs" class="button button-secondary">
                        <span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh', 'tradepress'); ?>
                    </button>
                    
                    <button type="button" id="clear_logs" class="button button-secondary">
                        <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Clear Logs', 'tradepress'); ?>
                    </button>
                </div>
                
                <!-- Demo Log Entries -->
                <div class="api-logs-container">
                    <table class="wp-list-table widefat fixed striped api-logs-table">
                        <thead>
                            <tr>
                                <th class="column-time"><?php esc_html_e('Time', 'tradepress'); ?></th>
                                <th class="column-level"><?php esc_html_e('Level', 'tradepress'); ?></th>
                                <th class="column-endpoint"><?php esc_html_e('Endpoint', 'tradepress'); ?></th>
                                <th class="column-message"><?php esc_html_e('Message', 'tradepress'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="api-logs-body">
                            <!-- Demo entries with demo indicator -->
                            <tr>
                                <td colspan="4">
                                    <div class="demo-indicator" style="margin-bottom: 10px;">
                                        <div class="demo-icon dashicons dashicons-info"></div>
                                        <div class="demo-text">
                                            <h4><?php esc_html_e('Demo Data', 'tradepress'); ?></h4>
                                            <p><?php esc_html_e('Sample log entries for demonstration purposes.', 'tradepress'); ?></p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="log-entry log-info">
                                <td>2025-04-11 14:32:05</td>
                                <td><span class="log-level info">INFO</span></td>
                                <td>/api/v1/quote</td>
                                <td>Successfully fetched quote data for AAPL.US</td>
                            </tr>
                            <tr class="log-entry log-warning">
                                <td>2025-04-11 14:30:18</td>
                                <td><span class="log-level warning">WARNING</span></td>
                                <td>/api/v1/historical</td>
                                <td>Partial data returned for date range 2025-03-01 to 2025-04-01</td>
                            </tr>
                            <tr class="log-entry log-error">
                                <td>2025-04-11 14:15:42</td>
                                <td><span class="log-level error">ERROR</span></td>
                                <td>/api/v1/company</td>
                                <td>API request failed: 429 Too Many Requests - Rate limit exceeded</td>
                            </tr>
                            <tr class="log-entry log-info">
                                <td>2025-04-11 14:10:22</td>
                                <td><span class="log-level info">INFO</span></td>
                                <td>/api/v1/search</td>
                                <td>API search completed with 15 results for query "tech"</td>
                            </tr>
                            <tr class="log-entry log-info">
                                <td>2025-04-11 14:05:17</td>
                                <td><span class="log-level info">INFO</span></td>
                                <td>/ws/tick</td>
                                <td>WebSocket connection established</td>
                            </tr>
                            <!-- More demo entries could be added here -->
                        </tbody>
                    </table>
                </div>
                
                <div class="log-settings">
                    <h4><?php esc_html_e('Log Settings', 'tradepress'); ?></h4>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="api_enable_logging"><?php esc_html_e('Enable API Logging', 'tradepress'); ?></label>
                                </th>
                                <td>
                                    <label class="switch">
                                        <input name="api_enable_logging" id="api_enable_logging" type="checkbox" value="yes" <?php checked(get_option('tradepress_general_api_enable_logging', 'yes'), 'yes'); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                    <p class="description"><?php esc_html_e('Turn on to enable logging of API requests and responses.', 'tradepress'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="api_log_retention"><?php esc_html_e('Log Retention', 'tradepress'); ?></label>
                                </th>
                                <td>
                                    <select name="api_log_retention" id="api_log_retention">
                                        <option value="1" <?php selected(get_option('tradepress_general_api_log_retention', '7'), '1'); ?>><?php esc_html_e('1 day', 'tradepress'); ?></option>
                                        <option value="7" <?php selected(get_option('tradepress_general_api_log_retention', '7'), '7'); ?>><?php esc_html_e('7 days', 'tradepress'); ?></option>
                                        <option value="30" <?php selected(get_option('tradepress_general_api_log_retention', '7'), '30'); ?>><?php esc_html_e('30 days', 'tradepress'); ?></option>
                                        <option value="90" <?php selected(get_option('tradepress_general_api_log_retention', '7'), '90'); ?>><?php esc_html_e('90 days', 'tradepress'); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Automatically delete logs older than the selected period.', 'tradepress'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <p class="submit">
                <button type="submit" name="save_general_api_settings" class="button-primary" value="Save Changes">
                    <?php esc_html_e('Save Changes', 'tradepress'); ?>
                </button>
            </p>
        </form>
    </div>
</div>

