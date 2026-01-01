<?php
/**
 * TradePress Discord Webhook Manager
 *
 * Manages webhook-based notifications to Discord channels
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

// Include necessary dependencies
if (!class_exists('TRADEPRESS_DISCORD_Endpoints')) {
    require_once dirname(__FILE__) . '/discord-endpoints.php';
}

if (!class_exists('TRADEPRESS_DISCORD_Connection_Manager')) {
    require_once dirname(__FILE__) . '/discord-connection-manager.php';
}

/**
 * Discord Webhook Manager Class
 */
class TRADEPRESS_DISCORD_Webhook_Manager {
    
    /**
     * Connection manager instance
     *
     * @var TRADEPRESS_DISCORD_Connection_Manager
     */
    private $connection;
    
    /**
     * Webhook URLs by type
     *
     * @var array
     */
    private $webhooks = array();
    
    /**
     * Default embed color
     *
     * @var int
     */
    private $default_color = 5814783; // Discord Blurple color
    
    /**
     * Constructor
     *
     * @param string $token Optional bot token for connection manager
     */
    public function __construct($token = '') {
        $this->connection = new TRADEPRESS_DISCORD_Connection_Manager($token);
        
        // Set default webhooks from options
        $this->load_webhooks_from_options();
    }
    
    /**
     * Load webhooks from WordPress options
     */
    public function load_webhooks_from_options() {
        $this->webhooks = array(
            'alerts' => get_option('TRADEPRESS_DISCORD_webhook_alerts', ''),
            'market' => get_option('TRADEPRESS_DISCORD_webhook_market', ''),
            'signals' => get_option('TRADEPRESS_DISCORD_webhook_signals', ''),
            'system' => get_option('TRADEPRESS_DISCORD_webhook_system', '')
        );
    }
    
    /**
     * Set webhook URL for a specific type
     *
     * @param string $type Webhook type (alerts, market, signals, system)
     * @param string $url Webhook URL
     * @return bool Success status
     */
    public function set_webhook($type, $url) {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $this->webhooks[$type] = $url;
        update_option('TRADEPRESS_DISCORD_webhook_' . $type, $url);
        
        return true;
    }
    
    /**
     * Get a webhook URL by type
     *
     * @param string $type Webhook type
     * @return string Webhook URL or empty string if not found
     */
    public function get_webhook($type) {
        return isset($this->webhooks[$type]) ? $this->webhooks[$type] : '';
    }
    
    /**
     * Test webhook connection
     *
     * @param string $webhook_url Webhook URL to test
     * @return bool|WP_Error Success status or error
     */
    public function test_webhook($webhook_url) {
        if (empty($webhook_url)) {
            return new WP_Error('empty_url', __('Webhook URL is empty', 'tradepress'));
        }
        
        $test_message = array(
            'content' => __('TradePress webhook test message sent on', 'tradepress') . ' ' . date('Y-m-d H:i:s'),
            'embeds' => array(
                array(
                    'title' => __('Webhook Test Successful', 'tradepress'),
                    'description' => __('If you can see this message, the webhook is configured correctly.', 'tradepress'),
                    'color' => $this->default_color,
                    'footer' => array(
                        'text' => 'TradePress v1.0'
                    ),
                    'timestamp' => date('c')
                )
            )
        );
        
        return $this->send_webhook_message($webhook_url, $test_message);
    }
    
    /**
     * Send a notification to a specific webhook type
     *
     * @param string $type Webhook type (alerts, market, signals, system)
     * @param string $title Message title
     * @param string $message Message content
     * @param array $fields Optional fields for embed
     * @param int $color Optional embed color
     * @return bool|WP_Error Success status or error
     */
    public function send_notification($type, $title, $message, $fields = array(), $color = null) {
        $webhook_url = $this->get_webhook($type);
        
        if (empty($webhook_url)) {
            return new WP_Error('no_webhook', sprintf(__('No webhook URL found for type: %s', 'tradepress'), $type));
        }
        
        // Check if this notification type is enabled
        $notification_enabled = get_option('TradePress_social_discord_notify_' . $type, 'yes') === 'yes';
        if (!$notification_enabled) {
            return new WP_Error('notification_disabled', sprintf(__('Notifications for %s are disabled', 'tradepress'), $type));
        }
        
        $embed = array(
            'title' => $title,
            'description' => $message,
            'color' => $color ?? $this->default_color,
            'fields' => $fields,
            'footer' => array(
                'text' => 'TradePress Notification'
            ),
            'timestamp' => date('c')
        );
        
        $payload = array(
            'embeds' => array($embed)
        );
        
        return $this->send_webhook_message($webhook_url, $payload);
    }
    
    /**
     * Send a stock price alert
     *
     * @param string $symbol Stock symbol
     * @param float $price Current price
     * @param float $previous_price Previous price
     * @param string $alert_type Type of alert (target_reached, sudden_move, etc.)
     * @return bool|WP_Error Success status or error
     */
    public function send_price_alert($symbol, $price, $previous_price, $alert_type = 'target_reached') {
        // Calculate change percentage
        $change = $price - $previous_price;
        $change_percent = 0;
        if ($previous_price !== null && $previous_price > 0) {
            $change_percent = (($price - $previous_price) / $previous_price) * 100;
        }
        $change_formatted = number_format(abs($change_percent), 2) . '%';
        $direction = ($change >= 0) ? 'up' : 'down';
        
        // Determine color based on price movement
        $color = ($change >= 0) ? 5763719 : 15548997; // Green if positive, Red if negative
        
        // Set up alert title based on alert type
        $alert_titles = array(
            'target_reached' => sprintf(__('%s Price Target Reached', 'tradepress'), $symbol),
            'sudden_move' => sprintf(__('%s Sudden Price Movement', 'tradepress'), $symbol),
            'daily_change' => sprintf(__('%s Daily Price Update', 'tradepress'), $symbol),
            'breakout' => sprintf(__('%s Breakout Alert', 'tradepress'), $symbol),
            'threshold' => sprintf(__('%s Threshold Crossed', 'tradepress'), $symbol)
        );
        
        $title = isset($alert_titles[$alert_type]) ? $alert_titles[$alert_type] : $alert_titles['target_reached'];
        
        // Create description based on alert type
        $description = sprintf(
            __('%s is %s %s from previous price of %s', 'tradepress'),
            $symbol,
            $direction,
            $change_formatted,
            number_format($previous_price, 2)
        );
        
        // Create fields
        $fields = array(
            array(
                'name' => __('Current Price', 'tradepress'),
                'value' => '$' . number_format($price, 2),
                'inline' => true
            ),
            array(
                'name' => __('Previous Price', 'tradepress'),
                'value' => '$' . number_format($previous_price, 2),
                'inline' => true
            ),
            array(
                'name' => __('Change', 'tradepress'),
                'value' => ($change >= 0 ? '+' : '') . number_format($change, 2) . ' (' . $change_formatted . ')',
                'inline' => true
            )
        );
        
        // Send the notification to alerts webhook
        return $this->send_notification('alerts', $title, $description, $fields, $color);
    }
    
    /**
     * Send a trade alert
     *
     * @param string $symbol Stock symbol
     * @param string $action Trade action (buy, sell)
     * @param float $price Trade price
     * @param int $quantity Quantity
     * @param string $strategy Strategy name
     * @return bool|WP_Error Success status or error
     */
    public function send_trade_alert($symbol, $action, $price, $quantity, $strategy = '') {
        // Determine color based on action
        $color = ($action === 'buy') ? 5763719 : 15548997; // Green for buy, Red for sell
        
        // Create title
        $title = sprintf(
            __('%s %s Alert', 'tradepress'),
            strtoupper($symbol),
            ucfirst($action)
        );
        
        // Create description
        $description = sprintf(
            __('TradePress has generated a %s signal for %s at $%s', 'tradepress'),
            $action,
            $symbol,
            number_format($price, 2)
        );
        
        // Create fields
        $fields = array(
            array(
                'name' => __('Action', 'tradepress'),
                'value' => ucfirst($action),
                'inline' => true
            ),
            array(
                'name' => __('Price', 'tradepress'),
                'value' => '$' . number_format($price, 2),
                'inline' => true
            ),
            array(
                'name' => __('Quantity', 'tradepress'),
                'value' => number_format($quantity),
                'inline' => true
            )
        );
        
        // Add strategy field if provided
        if (!empty($strategy)) {
            $fields[] = array(
                'name' => __('Strategy', 'tradepress'),
                'value' => $strategy,
                'inline' => false
            );
        }
        
        // Add timestamp field
        $fields[] = array(
            'name' => __('Time', 'tradepress'),
            'value' => date('Y-m-d H:i:s'),
            'inline' => false
        );
        
        // Send the notification to alerts webhook
        return $this->send_notification('alerts', $title, $description, $fields, $color);
    }
    
    /**
     * Send a market update
     *
     * @param string $title Update title
     * @param string $message Update message
     * @param array $indices Market indices data
     * @return bool|WP_Error Success status or error
     */
    public function send_market_update($title, $message, $indices = array()) {
        // Create fields for market indices
        $fields = array();
        
        foreach ($indices as $index_name => $index_data) {
            if (isset($index_data['value']) && isset($index_data['change'])) {
                $is_positive = $index_data['change'] >= 0;
                $change_text = ($is_positive ? '+' : '') . number_format($index_data['change'], 2);
                if (isset($index_data['change_percent'])) {
                    $change_text .= ' (' . ($is_positive ? '+' : '') . number_format($index_data['change_percent'], 2) . '%)';
                }
                
                $fields[] = array(
                    'name' => $index_name,
                    'value' => number_format($index_data['value'], 2) . ' | ' . $change_text,
                    'inline' => true
                );
            }
        }
        
        // Send the notification to market webhook
        return $this->send_notification('market', $title, $message, $fields);
    }
    
    /**
     * Send a score alert
     *
     * @param string $symbol Stock symbol
     * @param float $score Current score
     * @param float $previous_score Previous score
     * @param array $metrics Score breakdown metrics
     * @return bool|WP_Error Success status or error
     */
    public function send_score_alert($symbol, $score, $previous_score, $metrics = array()) {
        // Determine color based on score
        $color = 5814783; // Default blurple
        if ($score >= 80) {
            $color = 5763719; // Green for high scores
        } elseif ($score <= 30) {
            $color = 15548997; // Red for low scores
        } elseif ($score > 50) {
            $color = 16776960; // Yellow for medium-high scores
        }
        
        // Create title
        $title = sprintf(__('%s Score Update', 'tradepress'), $symbol);
        
        // Calculate change
        $change = $score - $previous_score;
        $change_text = ($change >= 0 ? '+' : '') . number_format($change, 1);
        
        // Create description
        $description = sprintf(
            __('%s score is now %s (%s from previous score)', 'tradepress'),
            $symbol,
            number_format($score, 1),
            $change_text
        );
        
        // Create fields for metrics
        $fields = array(
            array(
                'name' => __('Current Score', 'tradepress'),
                'value' => number_format($score, 1) . '/100',
                'inline' => true
            ),
            array(
                'name' => __('Previous Score', 'tradepress'),
                'value' => number_format($previous_score, 1) . '/100',
                'inline' => true
            ),
            array(
                'name' => __('Change', 'tradepress'),
                'value' => $change_text,
                'inline' => true
            )
        );
        
        // Add metrics breakdown if provided
        if (!empty($metrics)) {
            foreach ($metrics as $metric_name => $metric_value) {
                $fields[] = array(
                    'name' => $metric_name,
                    'value' => is_numeric($metric_value) ? number_format($metric_value, 1) . '/100' : $metric_value,
                    'inline' => true
                );
            }
        }
        
        // Send the notification to alerts webhook
        return $this->send_notification('alerts', $title, $description, $fields, $color);
    }
    
    /**
     * Send a system notification
     *
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $level Notification level (info, warning, error)
     * @param array $details Additional details
     * @return bool|WP_Error Success status or error
     */
    public function send_system_notification($title, $message, $level = 'info', $details = array()) {
        // Determine color based on notification level
        $colors = array(
            'info' => 3447003,     // Blue
            'success' => 5763719,  // Green
            'warning' => 16776960, // Yellow
            'error' => 15548997    // Red
        );
        
        $color = isset($colors[$level]) ? $colors[$level] : $colors['info'];
        
        // Create fields for additional details
        $fields = array();
        
        if (!empty($details)) {
            foreach ($details as $name => $value) {
                $fields[] = array(
                    'name' => $name,
                    'value' => $value,
                    'inline' => true
                );
            }
        }
        
        // Add timestamp and system info
        $fields[] = array(
            'name' => __('Time', 'tradepress'),
            'value' => date('Y-m-d H:i:s'),
            'inline' => true
        );
        
        $fields[] = array(
            'name' => __('System', 'tradepress'),
            'value' => 'TradePress v' . TRADEPRESS_VERSION,
            'inline' => true
        );
        
        // Send the notification to system webhook
        return $this->send_notification('system', $title, $message, $fields, $color);
    }
    
    /**
     * Send message directly to a webhook URL
     *
     * @param string $webhook_url The webhook URL
     * @param array $payload The message payload
     * @return bool|WP_Error Success status or error
     */
    private function send_webhook_message($webhook_url, $payload) {
        if (empty($webhook_url)) {
            return new WP_Error('empty_webhook', __('Webhook URL is empty', 'tradepress'));
        }
        
        $args = array(
            'method' => 'POST',
            'timeout' => 15,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'TradePress/1.0 (WordPress; +https://tradepress.com)'
            ),
            'body' => wp_json_encode($payload),
            'cookies' => array(),
            'sslverify' => true
        );
        
        // First, try with normal settings
        $response = wp_remote_post($webhook_url, $args);
        
        // If that fails, try with SSL verification disabled
        if (is_wp_error($response)) {
            $args['sslverify'] = false;
            $response = wp_remote_post($webhook_url, $args);
        }
        
        // Check for errors in the response
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Discord returns 204 No Content on success
        if ($response_code === 204) {
            return true;
        }
        
        // If we get another response code, something went wrong
        $body = wp_remote_retrieve_body($response);
        $error_message = !empty($body) ? $body : sprintf(__('Received HTTP code %d from Discord', 'tradepress'), $response_code);
        
        return new WP_Error('discord_error', $error_message, array('code' => $response_code));
    }
    
    /**
     * Create webhook for a channel
     *
     * @param string $channel_id Channel ID
     * @param string $name Webhook name
     * @return array|WP_Error Webhook data or error
     */
    public function create_webhook($channel_id, $name) {
        if (empty($this->connection)) {
            return new WP_Error('no_connection', __('Discord connection not available', 'tradepress'));
        }
        
        // Get endpoint for creating a webhook
        $endpoint = TRADEPRESS_DISCORD_Endpoints::get_endpoint('CREATE_WEBHOOK');
        if (!$endpoint) {
            return new WP_Error('invalid_endpoint', __('Invalid endpoint: CREATE_WEBHOOK', 'tradepress'));
        }
        
        // Build the URL
        $url = TRADEPRESS_DISCORD_Endpoints::get_endpoint_url('CREATE_WEBHOOK', array('channel_id' => $channel_id));
        
        // Build the payload
        $payload = array(
            'name' => $name
        );
        
        // Avatar can be added here if needed
        
        // Make the request
        $response = $this->connection->request(str_replace('https://discord.com/api/v10/', '', $url), 'POST', $payload);
        
        // Parse the response
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            return new WP_Error('discord_error', $body, array('code' => $response_code));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body) || !isset($body['url'])) {
            return new WP_Error('invalid_response', __('Invalid response from Discord API', 'tradepress'));
        }
        
        return $body;
    }
    
    /**
     * Get all webhooks for a channel
     *
     * @param string $channel_id Channel ID
     * @return array|WP_Error Webhooks data or error
     */
    public function get_channel_webhooks($channel_id) {
        if (empty($this->connection)) {
            return new WP_Error('no_connection', __('Discord connection not available', 'tradepress'));
        }
        
        // Get endpoint for getting webhooks
        $endpoint = TRADEPRESS_DISCORD_Endpoints::get_endpoint('GET_CHANNEL_WEBHOOKS');
        if (!$endpoint) {
            return new WP_Error('invalid_endpoint', __('Invalid endpoint: GET_CHANNEL_WEBHOOKS', 'tradepress'));
        }
        
        // Build the URL
        $url = TRADEPRESS_DISCORD_Endpoints::get_endpoint_url('GET_CHANNEL_WEBHOOKS', array('channel_id' => $channel_id));
        
        // Make the request
        $response = $this->connection->request(str_replace('https://discord.com/api/v10/', '', $url), 'GET');
        
        // Parse the response
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            return new WP_Error('discord_error', $body, array('code' => $response_code));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body)) {
            return new WP_Error('invalid_response', __('Invalid response from Discord API', 'tradepress'));
        }
        
        return $body;
    }
}