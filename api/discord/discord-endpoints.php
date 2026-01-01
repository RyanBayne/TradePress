<?php
/**
 * TradePress Discord Endpoints
 * 
 * Class to manage Discord API endpoints and plugin sub-tabs
 * 
 * @package TradePress/API/Discord
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Discord Endpoints Class
 */
class TRADEPRESS_DISCORD_Endpoints {
    
    /**
     * Get Discord API endpoints
     *
     * @return array Endpoints configuration
     */
    public static function get_endpoints() {
        $endpoints = array(
            'channels' => array(
                'path' => '/api/v10/channels/{channel.id}',
                'method' => 'GET',
                'description' => 'Get a channel by ID'
            ),
            'messages' => array(
                'path' => '/api/v10/channels/{channel.id}/messages',
                'method' => 'POST',
                'description' => 'Send a message to a channel'
            ),
            'get_messages' => array(
                'path' => '/api/v10/channels/{channel.id}/messages',
                'method' => 'GET',
                'description' => 'Get messages from a channel'
            ),
            'guild' => array(
                'path' => '/api/v10/guilds/{guild.id}',
                'method' => 'GET',
                'description' => 'Get guild information'
            ),
            'webhooks' => array(
                'path' => '/api/v10/webhooks/{webhook.id}/{webhook.token}',
                'method' => 'POST',
                'description' => 'Execute webhook'
            )
        );
        
        // Allow other plugins to filter and modify endpoints
        return apply_filters('TRADEPRESS_DISCORD_endpoints', $endpoints);
    }
    
    /**
     * Get a specific Discord API endpoint
     *
     * @param string $endpoint_id The endpoint identifier
     * @return string|array|false The endpoint URL or data, or false if not found
     */
    public static function get_endpoint($endpoint_id) {
        // Discord API endpoints map
        $endpoints = array(
            'GET_CURRENT_APPLICATION' => '/api/v10/oauth2/applications/@me',
            'GET_CURRENT_BOT' => '/api/v10/users/@me',
            'GET_GUILD' => '/api/v10/guilds/{guild.id}',
            'GET_CHANNEL' => '/api/v10/channels/{channel.id}',
            'GET_CHANNELS' => '/api/v10/guilds/{guild.id}/channels',
            'GET_MESSAGES' => '/api/v10/channels/{channel.id}/messages',
            'CREATE_MESSAGE' => '/api/v10/channels/{channel.id}/messages',
            'EXECUTE_WEBHOOK' => '/api/v10/webhooks/{webhook.id}/{webhook.token}',
            'GET_WEBHOOK' => '/api/v10/webhooks/{webhook.id}',
            'CREATE_WEBHOOK' => '/api/v10/channels/{channel.id}/webhooks',
            'DELETE_WEBHOOK' => '/api/v10/webhooks/{webhook.id}',
            'MODIFY_WEBHOOK' => '/api/v10/webhooks/{webhook.id}',
            'GET_GUILD_WEBHOOKS' => '/api/v10/guilds/{guild.id}/webhooks',
            'GET_CHANNEL_WEBHOOKS' => '/api/v10/channels/{channel.id}/webhooks'
        );
        
        // Add method information for each endpoint
        $endpoint_methods = array(
            'GET_CURRENT_APPLICATION' => 'GET',
            'GET_CURRENT_BOT' => 'GET',
            'GET_GUILD' => 'GET',
            'GET_CHANNEL' => 'GET',
            'GET_CHANNELS' => 'GET',
            'GET_MESSAGES' => 'GET',
            'CREATE_MESSAGE' => 'POST',
            'EXECUTE_WEBHOOK' => 'POST',
            'GET_WEBHOOK' => 'GET',
            'CREATE_WEBHOOK' => 'POST',
            'DELETE_WEBHOOK' => 'DELETE',
            'MODIFY_WEBHOOK' => 'PATCH',
            'GET_GUILD_WEBHOOKS' => 'GET',
            'GET_CHANNEL_WEBHOOKS' => 'GET'
        );
        
        if (isset($endpoints[$endpoint_id])) {
            // Return both the endpoint URL and the HTTP method
            return array(
                'url' => $endpoints[$endpoint_id],
                'method' => isset($endpoint_methods[$endpoint_id]) ? $endpoint_methods[$endpoint_id] : 'GET'
            );
        }
        
        return false;
    }
}