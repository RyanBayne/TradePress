<?php
/**
 * TradePress - Social Platforms Settings View
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/admin/page/socialplatforms/view
 * @since    1.0.0
 * @created  April 22, 2025
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current section (platform)
$current_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : '';

// Available social platforms
$social_platforms = array(
    'discord' => __('Discord', 'tradepress'),
    'twitter' => __('Twitter', 'tradepress'),
    'stocktwits' => __('StockTwits', 'tradepress'),
    'stock_vip' => __('Stock VIP', 'tradepress'),
);

?>
<div class="wrap tradepress-social-settings">
    <?php if (empty($current_section)): ?>
        <div class="social-platforms-settings">
            <div class="platform-cards">
                <?php foreach ($social_platforms as $platform_id => $platform_name) : 
                    $is_enabled = get_option('TradePress_switch_' . $platform_id . '_social_services') === 'yes';
                    $api_key = get_option('TradePress_social_' . $platform_id . '_apikey', '');
                ?>
                    <div class="platform-card">
                        <h3><?php echo esc_html($platform_name); ?></h3>
                        
                        <div class="platform-status <?php echo $is_enabled ? 'enabled' : 'disabled'; ?>">
                            <span class="status-indicator"></span>
                            <?php echo $is_enabled ? esc_html__('Enabled', 'tradepress') : esc_html__('Disabled', 'tradepress'); ?>
                        </div>
                        
                        <p class="platform-description">
                            <?php 
                            switch ($platform_id) {
                                case 'discord':
                                    esc_html_e('Webhook notifications and bot integration', 'tradepress');
                                    break;
                                case 'twitter':
                                    esc_html_e('Automated posting and social media management', 'tradepress');
                                    break;
                                case 'stocktwits':
                                    esc_html_e('Share trading insights and market updates', 'tradepress');
                                    break;
                                case 'stock_vip':
                                    esc_html_e('Discord stock alert parsing and processing', 'tradepress');
                                    break;
                            }
                            ?>
                        </p>
                        
                        <div class="platform-api-status">
                            <?php if (!empty($api_key)): ?>
                                <span class="api-configured"><?php esc_html_e('API Configured', 'tradepress'); ?></span>
                            <?php else: ?>
                                <span class="api-not-configured"><?php esc_html_e('API Not Configured', 'tradepress'); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=social_networks&subtab=settings&section=' . $platform_id)); ?>" class="button button-primary">
                            <?php esc_html_e('Configure', 'tradepress'); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Platform-specific settings -->
        <div class="platform-specific-settings">
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=social_networks&subtab=settings')); ?>">
                    &larr; <?php esc_html_e('Back to Platform Overview', 'tradepress'); ?>
                </a>
            </p>
            
            <h3><?php echo esc_html($social_platforms[$current_section] ?? ucfirst($current_section)); ?> <?php esc_html_e('Settings', 'tradepress'); ?></h3>
            
            <?php
            // Include platform-specific settings
            $platform_settings_file = dirname(__FILE__) . '/settings/' . $current_section . '-settings.php';
            if (file_exists($platform_settings_file)) {
                include($platform_settings_file);
            } else {
                // Generic settings form
                ?>
                <form method="post" action="">
                    <?php wp_nonce_field('tradepress-social-settings-provider'); ?>
                    <input type="hidden" name="social_provider" value="<?php echo esc_attr($current_section); ?>">
                    
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="api_<?php echo esc_attr($current_section); ?>_apikey">
                                        <?php esc_html_e('API Key', 'tradepress'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           name="api_<?php echo esc_attr($current_section); ?>_apikey" 
                                           id="api_<?php echo esc_attr($current_section); ?>_apikey" 
                                           value="<?php echo esc_attr(get_option('TradePress_social_' . $current_section . '_apikey', '')); ?>" 
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('Enter your API key for this platform.', 'tradepress'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="switch_<?php echo esc_attr($current_section); ?>_services">
                                        <?php esc_html_e('Enable Services', 'tradepress'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" 
                                           name="switch_<?php echo esc_attr($current_section); ?>_services" 
                                           id="switch_<?php echo esc_attr($current_section); ?>_services" 
                                           value="yes" 
                                           <?php checked(get_option('TradePress_switch_' . $current_section . '_social_services'), 'yes'); ?>>
                                    <label for="switch_<?php echo esc_attr($current_section); ?>_services">
                                        <?php esc_html_e('Enable this platform', 'tradepress'); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="switch_<?php echo esc_attr($current_section); ?>_logs">
                                        <?php esc_html_e('Enable Logging', 'tradepress'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="checkbox" 
                                           name="switch_<?php echo esc_attr($current_section); ?>_logs" 
                                           id="switch_<?php echo esc_attr($current_section); ?>_logs" 
                                           value="yes" 
                                           <?php checked(get_option('TradePress_switch_' . $current_section . '_social_logs'), 'yes'); ?>>
                                    <label for="switch_<?php echo esc_attr($current_section); ?>_logs">
                                        <?php esc_html_e('Enable logging for this platform', 'tradepress'); ?>
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <?php submit_button(__('Save Settings', 'tradepress'), 'primary', 'save_social_provider'); ?>
                </form>
                <?php
            }
            ?>
        </div>
    <?php endif; ?>
</div>
