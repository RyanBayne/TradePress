<?php
/**
 * TradePress - Discord Platform Settings
 *
 * Settings page for Discord integration - ADMIN ONLY FUNCTIONALITY
 * This integration is intended for WordPress admin users only, not for public users.
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/admin/page
 * @since    1.0.0
 * @created  April 22, 2025
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if form submitted
if (isset($_POST['save_discord_settings']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'tradepress-discord-settings')) {
    
    // Save Bot Token
    if (isset($_POST['discord_bot_token'])) {
        $bot_token = sanitize_text_field($_POST['discord_bot_token']);
        update_option('TRADEPRESS_DISCORD_bot_token', $bot_token);
    }
    
    // Save Bot Permissions Integer
    if (isset($_POST['discord_bot_permissions'])) {
        $bot_permissions = sanitize_text_field($_POST['discord_bot_permissions']);
        update_option('TRADEPRESS_DISCORD_bot_permissions', $bot_permissions);
    }
    
    // Save Client ID
    if (isset($_POST['discord_client_id'])) {
        $client_id = sanitize_text_field($_POST['discord_client_id']);
        update_option('TRADEPRESS_DISCORD_client_id', $client_id);
    }
    
    // Save Client Secret
    if (isset($_POST['discord_client_secret'])) {
        $client_secret = sanitize_text_field($_POST['discord_client_secret']);
        update_option('TRADEPRESS_DISCORD_client_secret', $client_secret);
    }
    
    // Save Public Key
    if (isset($_POST['discord_public_key'])) {
        $public_key = sanitize_text_field($_POST['discord_public_key']);
        update_option('TRADEPRESS_DISCORD_public_key', $public_key);
    }
    
    // Save Application ID
    if (isset($_POST['discord_application_id'])) {
        $application_id = sanitize_text_field($_POST['discord_application_id']);
        update_option('TRADEPRESS_DISCORD_application_id', $application_id);
    }
    
    // Save Guild ID
    if (isset($_POST['discord_guild_id'])) {
        $guild_id = sanitize_text_field($_POST['discord_guild_id']);
        update_option('TRADEPRESS_DISCORD_guild_id', $guild_id);
    }
    
    // Save Channel Settings
    if (isset($_POST['discord_alerts_channel'])) {
        update_option('TRADEPRESS_DISCORD_alerts_channel', sanitize_text_field($_POST['discord_alerts_channel']));
    }
    
    if (isset($_POST['discord_market_channel'])) {
        update_option('TRADEPRESS_DISCORD_market_channel', sanitize_text_field($_POST['discord_market_channel']));
    }
    
    if (isset($_POST['discord_signals_channel'])) {
        update_option('TRADEPRESS_DISCORD_signals_channel', sanitize_text_field($_POST['discord_signals_channel']));
    }
    
    // Save Redirect URI if present
    if (isset($_POST['discord_redirect_uri'])) {
        $redirect_uri = esc_url_raw($_POST['discord_redirect_uri']);
        update_option('TRADEPRESS_DISCORD_redirect_uri', $redirect_uri);
    }
    
    // Save notification settings
    $notification_types = array(
        'trade_alerts',
        'price_alerts',
        'score_alerts',
        'market_updates',
        'system_notifications'
    );
    
    foreach ($notification_types as $type) {
        $option_name = 'TradePress_social_discord_notify_' . $type;
        $value = isset($_POST['discord_notify_' . $type]) ? 'yes' : 'no';
        update_option($option_name, $value);
    }
    
    // Set success message
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Discord settings saved successfully.', 'tradepress') . '</p></div>';
}

// Get current values
$bot_token = get_option('TRADEPRESS_DISCORD_bot_token', '');
$bot_permissions = get_option('TRADEPRESS_DISCORD_bot_permissions', '0');
$client_id = get_option('TRADEPRESS_DISCORD_client_id', '');
$client_secret = get_option('TRADEPRESS_DISCORD_client_secret', '');
$public_key = get_option('TRADEPRESS_DISCORD_public_key', '');
$application_id = get_option('TRADEPRESS_DISCORD_application_id', '');
$guild_id = get_option('TRADEPRESS_DISCORD_guild_id', '');
$alerts_channel = get_option('TRADEPRESS_DISCORD_alerts_channel', '');
$market_channel = get_option('TRADEPRESS_DISCORD_market_channel', '');
$signals_channel = get_option('TRADEPRESS_DISCORD_signals_channel', '');
$redirect_uri = get_option('TRADEPRESS_DISCORD_redirect_uri', site_url('/discord-callback'));

// Load Discord API class
if (!class_exists('TRADEPRESS_DISCORD_API')) {
    require_once(trailingslashit(TRADEPRESS_PLUGIN_DIR_PATH) . 'api/discord/discord-api.php');
}

// Initialize the Discord API instance
$discord_api = new TRADEPRESS_DISCORD_API();

// Get diagnostic results
$diagnostics = $discord_api->run_diagnostics();

?>

<div class="discord-settings-page">
    <h2><?php esc_html_e('Discord Integration Settings', 'tradepress'); ?></h2>
    
    <div class="discord-settings-container">
        <!-- Discord Setup Guide -->
        <div class="discord-setup-guide">
            <div class="accordion-header">
                <h3><?php esc_html_e('Discord Bot Setup Guide', 'tradepress'); ?></h3>
                <button type="button" class="accordion-toggle" aria-expanded="false">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
            </div>
            
            <div class="accordion-content" style="display: none;">
                <div class="setup-note">
                    <p><strong><?php esc_html_e('Important Note:', 'tradepress'); ?></strong></p>
                    <p><?php esc_html_e('You only need a Bot Token to send messages to Discord channels using a bot. The OAuth2 fields (Client ID, Client Secret, and Redirect URI) are only needed if you want to connect your Discord account to this admin panel.', 'tradepress'); ?></p>
                </div>
                
                <ol>
                    <li>
                        <strong><?php esc_html_e('Create a Discord Application', 'tradepress'); ?></strong>
                        <p><?php esc_html_e('Go to the Discord Developer Portal and create a new application.', 'tradepress'); ?></p>
                        <a href="https://discord.com/developers/applications" target="_blank" class="button button-secondary">
                            <span class="dashicons dashicons-external"></span> <?php esc_html_e('Discord Developer Portal', 'tradepress'); ?>
                        </a>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Set Up a Bot', 'tradepress'); ?></strong>
                        <p><?php esc_html_e('In your application settings, navigate to the "Bot" tab and click "Add Bot".', 'tradepress'); ?></p>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Copy Bot Token', 'tradepress'); ?></strong>
                        <p><?php esc_html_e('In the Bot tab, click "Reset Token" to generate a new token, then copy it. NEVER share this token publicly.', 'tradepress'); ?></p>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Set Bot Permissions', 'tradepress'); ?></strong>
                        <p><?php esc_html_e('In the Bot section, scroll down to ensure "Message Content Intent" is enabled. This allows your bot to read message content.', 'tradepress'); ?></p>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Invite Bot to Server', 'tradepress'); ?></strong>
                        <p><?php esc_html_e('In the OAuth2 → URL Generator tab, select "bot" scope and the required permissions (Send Messages, Embed Links, Attach Files), then use the generated URL to add the bot to your server.', 'tradepress'); ?></p>
                    </li>
                </ol>
                
                <div class="troubleshooting-tips">
                    <h4><?php esc_html_e('Troubleshooting Tips', 'tradepress'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('If you get a 401 Unauthorized error, your bot token may be invalid. Try resetting it in the Discord Developer Portal.', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Make sure your bot has been added to your Discord server using the OAuth2 URL Generator.', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Check that your bot has permission to send messages in the channels you\'ve configured.', 'tradepress'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Discord Settings Form -->
        <div class="discord-settings-form">
            <form method="post" action="">
                <?php wp_nonce_field('tradepress-discord-settings'); ?>
                
                <h3><?php esc_html_e('Discord API Configuration', 'tradepress'); ?></h3>
                <div class="settings-section-note">
                    <span class="dashicons dashicons-info"></span> 
                    <p><?php esc_html_e('Only the Bot Token is required for basic Discord integration. The remaining OAuth2 fields are optional and only needed if you want admin login with Discord.', 'tradepress'); ?></p>
                </div>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="discord_bot_token"><?php esc_html_e('Bot Token', 'tradepress'); ?></label>
                                <span class="required">*</span>
                            </th>
                            <td>
                                <input type="password" name="discord_bot_token" id="discord_bot_token" 
                                       value="<?php echo esc_attr($bot_token); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Your Discord bot token from the Bot tab in the Discord Developer Portal. This is required to send messages to Discord.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_bot_permissions"><?php esc_html_e('Bot Permissions Integer', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="discord_bot_permissions" id="discord_bot_permissions" 
                                       value="<?php echo esc_attr($bot_permissions); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('The permissions integer for your bot. Required permissions include: Send Messages (2048), Embed Links (16384), Attach Files (32768).', 'tradepress'); ?>
                                    <br>
                                    <?php 
                                    // Calculate recommended permissions (Send Messages + Embed Links + Attach Files)
                                    $recommended_permissions = 2048 + 16384 + 32768; // = 51200
                                    echo sprintf(
                                        esc_html__('Recommended value: %s. ', 'tradepress'), 
                                        '<strong>' . $recommended_permissions . '</strong>'
                                    ); 
                                    ?>
                                    <a href="https://discord.com/developers/docs/topics/permissions" target="_blank"><?php esc_html_e('Learn more about Discord permissions', 'tradepress'); ?></a>
                                </p>
                            </td>
                        </tr>
                        <tr class="oauth-divider">
                            <td colspan="2">
                                <h4><?php esc_html_e('OAuth2 Settings (Optional)', 'tradepress'); ?></h4>
                                <p class="description"><?php esc_html_e('These settings are only needed if you want to allow admin login with Discord or access admin-specific Discord data.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_redirect_uri"><?php esc_html_e('Redirect URI', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="url" name="discord_redirect_uri" id="discord_redirect_uri" 
                                       value="<?php echo esc_attr($redirect_uri); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('The callback URL for OAuth2 authentication. Add this URL in the Discord Developer Portal under OAuth2 → Redirects.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_client_id"><?php esc_html_e('Client ID', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="discord_client_id" id="discord_client_id" 
                                       value="<?php echo esc_attr($client_id); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Your Discord application Client ID from the Discord Developer Portal.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_client_secret"><?php esc_html_e('Client Secret', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="password" name="discord_client_secret" id="discord_client_secret" 
                                       value="<?php echo esc_attr($client_secret); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Your Discord application Client Secret from the Discord Developer Portal.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_public_key"><?php esc_html_e('Public Key', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="discord_public_key" id="discord_public_key" 
                                       value="<?php echo esc_attr($public_key); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Your Discord application Public Key from the Discord Developer Portal.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_application_id"><?php esc_html_e('Application ID', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="discord_application_id" id="discord_application_id" 
                                       value="<?php echo esc_attr($application_id); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Your Discord application ID from the Discord Developer Portal.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_guild_id"><?php esc_html_e('Guild ID (Server ID)', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="discord_guild_id" id="discord_guild_id" 
                                       value="<?php echo esc_attr($guild_id); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('The ID of your Discord server. Enable Developer Mode in Discord, right-click your server icon, and select "Copy ID".', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <h3><?php esc_html_e('Channel Configuration', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Specify Discord channel IDs for different notification types. Enable Developer Mode in Discord, right-click a channel, and select "Copy ID".', 'tradepress'); ?></p>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="discord_alerts_channel"><?php esc_html_e('Alerts Channel', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="discord_alerts_channel" id="discord_alerts_channel" 
                                       value="<?php echo esc_attr($alerts_channel); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Channel for trade and price alerts.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_market_channel"><?php esc_html_e('Market Updates Channel', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="discord_market_channel" id="discord_market_channel" 
                                       value="<?php echo esc_attr($market_channel); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Channel for general market updates and news.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="discord_signals_channel"><?php esc_html_e('Trading Signals Channel', 'tradepress'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="discord_signals_channel" id="discord_signals_channel" 
                                       value="<?php echo esc_attr($signals_channel); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Channel for algorithmic trading signals.', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <h3><?php esc_html_e('Notification Settings', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Configure which events trigger Discord notifications.', 'tradepress'); ?></p>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Enable Notifications For:', 'tradepress'); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="discord_notify_trade_alerts">
                                        <input name="discord_notify_trade_alerts" type="checkbox" id="discord_notify_trade_alerts" 
                                               value="yes" <?php checked(get_option('TradePress_social_discord_notify_trade_alerts', 'yes'), 'yes'); ?>>
                                        <?php esc_html_e('Trade Alerts', 'tradepress'); ?>
                                    </label>
                                    <br>
                                    <label for="discord_notify_price_alerts">
                                        <input name="discord_notify_price_alerts" type="checkbox" id="discord_notify_price_alerts" 
                                               value="yes" <?php checked(get_option('TradePress_social_discord_notify_price_alerts', 'yes'), 'yes'); ?>>
                                        <?php esc_html_e('Price Alerts', 'tradepress'); ?>
                                    </label>
                                    <br>
                                    <label for="discord_notify_score_alerts">
                                        <input name="discord_notify_score_alerts" type="checkbox" id="discord_notify_score_alerts" 
                                               value="yes" <?php checked(get_option('TradePress_social_discord_notify_score_alerts', 'no'), 'yes'); ?>>
                                        <?php esc_html_e('Score Alerts', 'tradepress'); ?>
                                    </label>
                                    <br>
                                    <label for="discord_notify_market_updates">
                                        <input name="discord_notify_market_updates" type="checkbox" id="discord_notify_market_updates" 
                                               value="yes" <?php checked(get_option('TradePress_social_discord_notify_market_updates', 'yes'), 'yes'); ?>>
                                        <?php esc_html_e('Market Updates', 'tradepress'); ?>
                                    </label>
                                    <br>
                                    <label for="discord_notify_system_notifications">
                                        <input name="discord_notify_system_notifications" type="checkbox" id="discord_notify_system_notifications" 
                                               value="yes" <?php checked(get_option('TradePress_social_discord_notify_system_notifications', 'no'), 'yes'); ?>>
                                        <?php esc_html_e('System Notifications', 'tradepress'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="save_discord_settings" class="button button-primary" value="<?php esc_attr_e('Save Discord Settings', 'tradepress'); ?>">
                    <input type="submit" name="test_discord_connection" class="button button-secondary" value="<?php esc_attr_e('Test Connection', 'tradepress'); ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=TradePress_social&tab=discord')); ?>" class="button button-secondary">
                        <?php esc_html_e('Back to Discord', 'tradepress'); ?>
                    </a>
                </p>
            </form>
        </div>
        
        <!-- Third Container: Discord Connection Status -->
        <div class="discord-third-container">
            <h3><?php esc_html_e('Discord Connection Status', 'tradepress'); ?></h3>
            <div id="discord_status_container">
                <div class="status-panel">
                    <!-- Overall Status -->
                    <div class="status-section">
                        <h4><?php esc_html_e('Overall Status', 'tradepress'); ?></h4>
                        <div class="status-indicator <?php echo $diagnostics['success'] ? 'status-success' : 'status-error'; ?>">
                            <span class="dashicons <?php echo $diagnostics['success'] ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
                            <span class="status-text">
                                <?php echo $diagnostics['success'] ? esc_html__('Connection Ready', 'tradepress') : esc_html__('Configuration Issues Detected', 'tradepress'); ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($diagnostics['messages'])): ?>
                            <div class="status-messages">
                                <ul>
                                    <?php foreach ($diagnostics['messages'] as $message): ?>
                                        <li><?php echo esc_html($message); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Bot Token Status -->
                    <div class="status-section">
                        <h4><?php esc_html_e('Bot Token', 'tradepress'); ?></h4>
                        <div class="status-indicator <?php echo $diagnostics['tests']['token']['passed'] ? 'status-success' : 'status-error'; ?>">
                            <span class="dashicons <?php echo $diagnostics['tests']['token']['passed'] ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
                            <span class="status-text">
                                <?php echo esc_html($diagnostics['tests']['token']['message']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- API Connection -->
                    <div class="status-section">
                        <h4><?php esc_html_e('API Connection', 'tradepress'); ?></h4>
                        <div class="status-indicator <?php echo $diagnostics['tests']['validation']['passed'] ? 'status-success' : 'status-error'; ?>">
                            <span class="dashicons <?php echo $diagnostics['tests']['validation']['passed'] ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
                            <span class="status-text">
                                <?php echo esc_html($diagnostics['tests']['validation']['message']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Bot Information -->
                    <?php if (isset($diagnostics['tests']['bot_info']) && $diagnostics['tests']['bot_info']['passed'] && !empty($diagnostics['tests']['bot_info']['data'])): ?>
                        <div class="status-section">
                            <h4><?php esc_html_e('Bot Information', 'tradepress'); ?></h4>
                            <div class="bot-info">
                                <?php $bot_data = $diagnostics['tests']['bot_info']['data']; ?>
                                
                                <div class="bot-avatar-container">
                                    <?php if (!empty($bot_data['avatar'])): ?>
                                        <img src="<?php echo esc_url($bot_data['avatar']); ?>" alt="Bot Avatar" class="bot-avatar" />
                                    <?php else: ?>
                                        <div class="bot-avatar-placeholder">
                                            <span class="dashicons dashicons-admin-users"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="bot-details">
                                    <div class="bot-name"><?php echo esc_html($bot_data['name']); ?></div>
                                    <div class="bot-id">ID: <?php echo esc_html($bot_data['id']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="status-actions">
                        <button type="button" id="refresh_discord_status" class="button">
                            <span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh Status', 'tradepress'); ?>
                        </button>
                        <button type="button" id="test_discord_connection" class="button">
                            <span class="dashicons dashicons-shield"></span> <?php esc_html_e('Test Connection', 'tradepress'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>