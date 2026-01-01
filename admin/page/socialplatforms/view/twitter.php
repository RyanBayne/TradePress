<?php
/**
 * TradePress - Twitter Social Platform Tab
 *
 * Displays Twitter integration settings and functionality
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
?>

<div class="wrap tradepress-twitter-integration">

    
    <h2><?php esc_html_e('Twitter Integration', 'tradepress'); ?></h2>
    
    <div class="twitter-connection-settings">
        <h3><?php esc_html_e('Twitter API Connection', 'tradepress'); ?></h3>
        
        <form method="post" action="">
            <?php wp_nonce_field('tradepress-twitter-settings'); ?>
            <input type="hidden" name="social_provider" value="twitter">
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="api_twitter_apikey"><?php esc_html_e('API Key', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="api_twitter_apikey" id="api_twitter_apikey" 
                                   value="<?php echo esc_attr(get_option('TradePress_social_twitter_apikey', '')); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Enter your Twitter API key from the Twitter Developer Portal.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="api_twitter_token_secret"><?php esc_html_e('API Secret', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input type="password" name="api_twitter_token_secret" id="api_twitter_token_secret" 
                                   value="<?php echo esc_attr(get_option('TradePress_social_twitter_token_secret', '')); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Enter your Twitter API secret from the Twitter Developer Portal.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="switch_twitter_services"><?php esc_html_e('Twitter Integration', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input name="switch_twitter_services" type="checkbox" id="switch_twitter_services" 
                                       value="yes" <?php checked(get_option('TradePress_switch_twitter_social_services', 'no'), 'yes'); ?>>
                                <?php esc_html_e('Enable Twitter Integration', 'tradepress'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Enable Twitter integration for social sharing and market analysis.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="switch_twitter_logs"><?php esc_html_e('API Logging', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input name="switch_twitter_logs" type="checkbox" id="switch_twitter_logs" 
                                       value="yes" <?php checked(get_option('TradePress_switch_twitter_social_logs', 'no'), 'yes'); ?>>
                                <?php esc_html_e('Enable Twitter API Logs', 'tradepress'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Enable logging for Twitter API calls for debugging purposes.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_social_provider" class="button button-primary" value="<?php esc_attr_e('Save Twitter Settings', 'tradepress'); ?>">
            </p>
        </form>
    </div>
    
    <div class="twitter-content-settings">
        <h3><?php esc_html_e('Twitter Content Settings', 'tradepress'); ?></h3>
        
        <form method="post" action="">
            <?php wp_nonce_field('tradepress-twitter-content-settings'); ?>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="twitter_post_format"><?php esc_html_e('Post Format', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <select name="twitter_post_format" id="twitter_post_format">
                                <option value="simple" <?php selected(get_option('TRADEPRESS_TWITTER_post_format', 'simple'), 'simple'); ?>>
                                    <?php esc_html_e('Simple Text Only', 'tradepress'); ?>
                                </option>
                                <option value="with_ticker" <?php selected(get_option('TRADEPRESS_TWITTER_post_format', 'simple'), 'with_ticker'); ?>>
                                    <?php esc_html_e('Include Ticker Symbol ($TICKER)', 'tradepress'); ?>
                                </option>
                                <option value="with_chart" <?php selected(get_option('TRADEPRESS_TWITTER_post_format', 'simple'), 'with_chart'); ?>>
                                    <?php esc_html_e('Include Chart Image', 'tradepress'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Format for automated Twitter posts.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="twitter_hashtags"><?php esc_html_e('Default Hashtags', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="twitter_hashtags" id="twitter_hashtags" 
                                   value="<?php echo esc_attr(get_option('TRADEPRESS_TWITTER_hashtags', '#trading #stocks')); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Default hashtags to include in posts (separated by spaces).', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_twitter_content_settings" class="button button-primary" value="<?php esc_attr_e('Save Content Settings', 'tradepress'); ?>">
            </p>
        </form>
    </div>
    
    <div class="twitter-testing">
        <h3><?php esc_html_e('Twitter API Testing', 'tradepress'); ?></h3>
        <p><?php esc_html_e('Test your Twitter API connection and post functionality.', 'tradepress'); ?></p>
        
        <div class="test-buttons">
            <button type="button" class="button" disabled><?php esc_html_e('Test Connection', 'tradepress'); ?></button>
            <button type="button" class="button" disabled><?php esc_html_e('Send Test Post', 'tradepress'); ?></button>
        </div>
        
        <div class="test-results" style="display: none;">
            <h4><?php esc_html_e('Test Results', 'tradepress'); ?></h4>
            <pre class="test-output"></pre>
        </div>
    </div>
</div>