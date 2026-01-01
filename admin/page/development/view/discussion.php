<?php
/**
 * TradePress Development Discussion Tab
 * 
 * Displays Discord integration for development discussion.
 * 
 * @package TradePress\Admin\development
 * @version 1.0.0
 * @since 1.0.0
 * @created 2024-01-15
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TradePress_Admin_Development_Discussion
 * 
 * Handles the rendering and functionality of the Discussion tab
 * in the Development section.
 */
class TradePress_Admin_Development_Discussion {
    
    /**
     * Output the Discussion view
     */
    public static function output() {
        $channels = self::get_discord_channels();
        $current_channel = isset($_GET['channel']) ? sanitize_text_field($_GET['channel']) : '';
        $messages = !empty($current_channel) ? self::get_discord_messages($current_channel) : array();
        ?>
        <div class="tab-content" id="discussion">
            <div class="tradepress-discussion-container">
                <div class="tradepress-discussion-header">
                    <h2><?php esc_html_e('Development Discussion', 'tradepress'); ?></h2>
                    
                    <?php if (!empty($channels) && !is_wp_error($channels)) : ?>
                        <div class="discord-channel-selector">
                            <span class="channel-label"><?php esc_html_e('Channel:', 'tradepress'); ?></span>
                            <select id="discord-channel">
                                <option value=""><?php esc_html_e('Select a channel', 'tradepress'); ?></option>
                                <?php foreach ($channels as $channel) : ?>
                                    <option value="<?php echo esc_attr($channel->id); ?>" <?php selected($current_channel, $channel->id); ?>>
                                        <?php echo esc_html($channel->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($current_channel)) : ?>
                    <div class="tradepress-notice tradepress-notice-info">
                        <p><?php esc_html_e('Select a Discord channel to view messages.', 'tradepress'); ?></p>
                    </div>
                <?php elseif (is_wp_error($messages)) : ?>
                    <div class="tradepress-notice tradepress-notice-error">
                        <p><?php esc_html_e('Error retrieving Discord messages:', 'tradepress'); ?> <?php echo esc_html($messages->get_error_message()); ?></p>
                    </div>
                <?php else : ?>
                    <div class="discord-messages-container">
                        <div class="discord-messages" id="discord-messages">
                            <?php if (empty($messages)) : ?>
                                <div class="no-messages">
                                    <p><?php esc_html_e('No messages found in this channel.', 'tradepress'); ?></p>
                                </div>
                            <?php else : ?>
                                <?php foreach ($messages as $message) : ?>
                                    <div class="discord-message">
                                        <div class="message-avatar">
                                            <img src="<?php echo esc_url($message->author->avatar_url); ?>" alt="">
                                        </div>
                                        <div class="message-content">
                                            <div class="message-header">
                                                <span class="message-author"><?php echo esc_html($message->author->username); ?></span>
                                                <span class="message-timestamp"><?php echo esc_html(date('M j, Y g:i A', strtotime($message->timestamp))); ?></span>
                                            </div>
                                            <div class="message-text">
                                                <?php echo wp_kses_post($message->content); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="discord-input">
                            <form id="discord-message-form">
                                <input type="hidden" name="channel_id" value="<?php echo esc_attr($current_channel); ?>">
                                <textarea id="discord-message-text" placeholder="<?php esc_attr_e('Type a message...', 'tradepress'); ?>"></textarea>
                                <button type="submit" class="button button-primary">
                                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get Discord channels
     */
    private static function get_discord_channels() {
        $discord_token = get_option('TRADEPRESS_DISCORD_token', '');
        if (empty($discord_token)) {
            return new WP_Error('missing_token', __('Discord API token not configured.', 'tradepress'));
        }
        $channels = array(
            (object) array('id' => 'general', 'name' => 'general'),
            (object) array('id' => 'development', 'name' => 'development')
        );
        return $channels;
    }
    
    /**
     * Get Discord messages
     */
    private static function get_discord_messages($channel_id) {
        $discord_token = get_option('TRADEPRESS_DISCORD_token', '');
        if (empty($discord_token)) {
            return new WP_Error('missing_token', __('Discord API token not configured.', 'tradepress'));
        }
        $messages = array(
            (object) array(
                'id' => '1',
                'content' => 'Welcome to the TradePress development channel!',
                'timestamp' => date('c', strtotime('-3 days')),
                'author' => (object) array(
                    'username' => 'TradePressAdmin',
                    'avatar_url' => 'https://gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y'
                )
            )
        );
        return $messages;
    }
}
