<?php
/**
 * TradePress - Social Platform Switches View
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/admin/page/socialplatforms/view
 * @since    1.0.0
 * @created  April 22, 2025
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get social platform names for display
$platform_display_names = array(
    'discord' => __('Discord', 'tradepress'),
    'stock_vip' => __('Stock VIP', 'tradepress'),
    'twitter' => __('Twitter', 'tradepress'),
    'stocktwits' => __('StockTwits', 'tradepress'),
);

// Get platform array (IDs only)
$platform_array = array_keys($platform_display_names);

?>

<div class="wrap tradepress-social-platform-switches">
    <form method="post" action="">
        <?php wp_nonce_field('tradepress-social-platform-switches'); ?>
        
        <table class="form-table">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Platform', 'tradepress'); ?></th>
                    <th scope="col"><?php esc_html_e('Enable Services', 'tradepress'); ?></th>
                    <th scope="col"><?php esc_html_e('Enable Logging', 'tradepress'); ?></th>
                    <th scope="col"><?php esc_html_e('Status', 'tradepress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($platform_array as $platform): 
                    $services_enabled = get_option('TradePress_switch_' . $platform . '_social_services') === 'yes';
                    $logging_enabled = get_option('TradePress_switch_' . $platform . '_social_logs') === 'yes';
                    $api_key = get_option('TradePress_social_' . $platform . '_apikey', '');
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($platform_display_names[$platform]); ?></strong>
                            <br>
                            <small><?php 
                                switch ($platform) {
                                    case 'discord':
                                        esc_html_e('Webhook notifications and bot integration', 'tradepress');
                                        break;
                                    case 'stock_vip':
                                        esc_html_e('Discord stock alert parsing', 'tradepress');
                                        break;
                                    case 'twitter':
                                        esc_html_e('Automated posting and social media management', 'tradepress');
                                        break;
                                    case 'stocktwits':
                                        esc_html_e('Share trading insights and market updates', 'tradepress');
                                        break;
                                }
                            ?></small>
                        </td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" 
                                       name="switch_<?php echo esc_attr($platform); ?>_services" 
                                       value="yes" 
                                       <?php checked($services_enabled); ?>>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" 
                                       name="switch_<?php echo esc_attr($platform); ?>_logs" 
                                       value="yes" 
                                       <?php checked($logging_enabled); ?>>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td>
                            <div class="platform-status-indicators">
                                <div class="status-item">
                                    <span class="status-label"><?php esc_html_e('Services:', 'tradepress'); ?></span>
                                    <span class="status-badge <?php echo $services_enabled ? 'enabled' : 'disabled'; ?>">
                                        <?php echo $services_enabled ? esc_html__('On', 'tradepress') : esc_html__('Off', 'tradepress'); ?>
                                    </span>
                                </div>
                                <div class="status-item">
                                    <span class="status-label"><?php esc_html_e('API:', 'tradepress'); ?></span>
                                    <span class="status-badge <?php echo !empty($api_key) ? 'configured' : 'not-configured'; ?>">
                                        <?php echo !empty($api_key) ? esc_html__('Set', 'tradepress') : esc_html__('Not Set', 'tradepress'); ?>
                                    </span>
                                </div>
                                <div class="status-item">
                                    <span class="status-label"><?php esc_html_e('Logs:', 'tradepress'); ?></span>
                                    <span class="status-badge <?php echo $logging_enabled ? 'enabled' : 'disabled'; ?>">
                                        <?php echo $logging_enabled ? esc_html__('On', 'tradepress') : esc_html__('Off', 'tradepress'); ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php submit_button(__('Save Platform Switches', 'tradepress'), 'primary', 'save_platform_switches'); ?>
    </form>
</div>