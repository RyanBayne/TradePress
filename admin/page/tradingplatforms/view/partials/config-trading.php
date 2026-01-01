<?php
/**
 * Partial: Trading API Configuration
 * 
 * This partial template contains configuration options for API services
 * that offer trading functionality.
 * 
 * @package TradePress
 * @subpackage admin/page/TradingPlatforms
 * @version 1.0.0
 * @since 1.0.0
 * @created 2023-06-15 14:30:00
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables that must be defined before including this template:
// $api_id - The API identifier (e.g., 'alpaca')

// Get the current API settings
$api_key = get_option('tradepress_' . $api_id . '_api_key', '');
$api_secret = get_option('tradepress_' . $api_id . '_api_secret', '');
$api_enabled = get_option('tradepress_' . $api_id . '_enabled', 'no') === 'yes';
$trading_mode = get_option('tradepress_' . $api_id . '_trading_mode', 'paper');
$paper_api_key = get_option('tradepress_' . $api_id . '_paper_api_key', '');
$paper_api_secret = get_option('tradepress_' . $api_id . '_paper_api_secret', '');
$update_frequency = get_option('tradepress_' . $api_id . '_update_frequency', 'daily');
$data_retention = get_option('tradepress_' . $api_id . '_data_retention', '30');
$data_priority = get_option('tradepress_' . $api_id . '_data_priority', 'normal');

// Trading settings
$max_position_size = get_option('tradepress_' . $api_id . '_max_position_size', '5');
$stop_loss_percent = get_option('tradepress_' . $api_id . '_stop_loss_percent', '5');
$take_profit_percent = get_option('tradepress_' . $api_id . '_take_profit_percent', '10');
$trading_enabled = get_option('tradepress_' . $api_id . '_trading_enabled', 'no') === 'yes';
?>


        <?php if (empty($api_key) || empty($api_secret)): ?>
        <div class="notice notice-warning inline">
            <p>
                <span class="dashicons dashicons-warning"></span>
                <?php esc_html_e('API credentials not configured. Enter your API key and secret below to enable trading and data access.', 'tradepress'); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="api-settings-form">
            <?php wp_nonce_field('tradepress_save_' . $api_id . '_settings', 'tradepress_' . $api_id . '_nonce'); ?>
            <input type="hidden" name="action" value="tradepress_save_<?php echo esc_attr($api_id); ?>_settings">
            <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
            
            <ul class="api-settings-tabs">
                <li><a href="#general-settings" class="active"><?php esc_html_e('General', 'tradepress'); ?></a></li>
                <li><a href="#live-trading"><?php esc_html_e('Live Trading', 'tradepress'); ?></a></li>
                <li><a href="#paper-trading"><?php esc_html_e('Paper Trading', 'tradepress'); ?></a></li>
                <li><a href="#trading-rules"><?php esc_html_e('Trading Rules', 'tradepress'); ?></a></li>
            </ul>
            
            <div class="api-settings-tab-content">
                <!-- General Settings Tab -->
                <div id="general-settings" class="tab-content active">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_enabled">
                                    <?php esc_html_e('Enable API', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="<?php echo esc_attr($api_id); ?>_enabled" 
                                           name="<?php echo esc_attr($api_id); ?>_enabled" 
                                           value="yes" <?php checked($api_enabled); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('Enable or disable this API integration', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_trading_mode">
                                    <?php esc_html_e('Trading Mode', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr($api_id); ?>_trading_mode" 
                                        name="<?php echo esc_attr($api_id); ?>_trading_mode">
                                    <option value="paper" <?php selected($trading_mode, 'paper'); ?>>
                                        <?php esc_html_e('Paper Trading (Practice)', 'tradepress'); ?>
                                    </option>
                                    <option value="live" <?php selected($trading_mode, 'live'); ?>>
                                        <?php esc_html_e('Live Trading (Real Money)', 'tradepress'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Select between paper trading (simulated) and live trading (real money)', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_update_frequency">
                                    <?php esc_html_e('Data Update Frequency', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr($api_id); ?>_update_frequency" 
                                        name="<?php echo esc_attr($api_id); ?>_update_frequency">
                                    <option value="hourly" <?php selected($update_frequency, 'hourly'); ?>>
                                        <?php esc_html_e('Hourly', 'tradepress'); ?>
                                    </option>
                                    <option value="daily" <?php selected($update_frequency, 'daily'); ?>>
                                        <?php esc_html_e('Daily', 'tradepress'); ?>
                                    </option>
                                    <option value="weekly" <?php selected($update_frequency, 'weekly'); ?>>
                                        <?php esc_html_e('Weekly', 'tradepress'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('How often to refresh market data', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_data_retention">
                                    <?php esc_html_e('Data Retention (days)', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" id="<?php echo esc_attr($api_id); ?>_data_retention" 
                                       name="<?php echo esc_attr($api_id); ?>_data_retention" 
                                       value="<?php echo esc_attr($data_retention); ?>" 
                                       class="small-text" min="1" max="365">
                                <p class="description">
                                    <?php esc_html_e('Number of days to keep historical data', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_data_priority">
                                    <?php esc_html_e('Data Priority', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr($api_id); ?>_data_priority" 
                                        name="<?php echo esc_attr($api_id); ?>_data_priority">
                                    <option value="high" <?php selected($data_priority, 'high'); ?>>
                                        <?php esc_html_e('High - Preferred Source', 'tradepress'); ?>
                                    </option>
                                    <option value="normal" <?php selected($data_priority, 'normal'); ?>>
                                        <?php esc_html_e('Normal', 'tradepress'); ?>
                                    </option>
                                    <option value="low" <?php selected($data_priority, 'low'); ?>>
                                        <?php esc_html_e('Low - Fallback Only', 'tradepress'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Priority for this data source when multiple sources are available', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Live Trading Tab -->
                <div id="live-trading" class="tab-content">
                    <div class="notice notice-warning">
                        <p>
                            <strong><?php esc_html_e('Warning:', 'tradepress'); ?></strong>
                            <?php esc_html_e('Live trading uses real money. Make sure you understand the risks before enabling.', 'tradepress'); ?>
                        </p>
                    </div>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_api_key">
                                    <?php esc_html_e('Live API Key', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" id="<?php echo esc_attr($api_id); ?>_api_key" 
                                       name="<?php echo esc_attr($api_id); ?>_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       class="regular-text" autocomplete="off">
                                <button type="button" class="button toggle-password" 
                                        data-target="<?php echo esc_attr($api_id); ?>_api_key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    <?php esc_html_e('Your live trading API key', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_api_secret">
                                    <?php esc_html_e('Live API Secret', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" id="<?php echo esc_attr($api_id); ?>_api_secret" 
                                       name="<?php echo esc_attr($api_id); ?>_api_secret" 
                                       value="<?php echo esc_attr($api_secret); ?>" 
                                       class="regular-text" autocomplete="off">
                                <button type="button" class="button toggle-password" 
                                        data-target="<?php echo esc_attr($api_id); ?>_api_secret">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    <?php esc_html_e('Your live trading API secret', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_trading_enabled">
                                    <?php esc_html_e('Enable Automated Trading', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" id="<?php echo esc_attr($api_id); ?>_trading_enabled" 
                                           name="<?php echo esc_attr($api_id); ?>_trading_enabled" 
                                           value="yes" <?php checked($trading_enabled); ?>>
                                    <span class="slider round"></span>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('Allow the system to automatically execute trades based on signals', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="api-settings-actions">
                        <button type="button" class="button test-live-api" data-api="<?php echo esc_attr($api_id); ?>">
                            <?php esc_html_e('Test Live API Connection', 'tradepress'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Paper Trading Tab -->
                <div id="paper-trading" class="tab-content">
                    <div class="notice notice-info">
                        <p>
                            <?php esc_html_e('Paper trading allows you to test strategies without risking real money.', 'tradepress'); ?>
                        </p>
                    </div>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_paper_api_key">
                                    <?php esc_html_e('Paper API Key', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" id="<?php echo esc_attr($api_id); ?>_paper_api_key" 
                                       name="<?php echo esc_attr($api_id); ?>_paper_api_key" 
                                       value="<?php echo esc_attr($paper_api_key); ?>" 
                                       class="regular-text" autocomplete="off">
                                <button type="button" class="button toggle-password" 
                                        data-target="<?php echo esc_attr($api_id); ?>_paper_api_key">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    <?php esc_html_e('Your paper trading API key', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_paper_api_secret">
                                    <?php esc_html_e('Paper API Secret', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" id="<?php echo esc_attr($api_id); ?>_paper_api_secret" 
                                       name="<?php echo esc_attr($api_id); ?>_paper_api_secret" 
                                       value="<?php echo esc_attr($paper_api_secret); ?>" 
                                       class="regular-text" autocomplete="off">
                                <button type="button" class="button toggle-password" 
                                        data-target="<?php echo esc_attr($api_id); ?>_paper_api_secret">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                                <p class="description">
                                    <?php esc_html_e('Your paper trading API secret', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="api-settings-actions">
                        <button type="button" class="button test-paper-api" data-api="<?php echo esc_attr($api_id); ?>">
                            <?php esc_html_e('Test Paper API Connection', 'tradepress'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Trading Rules Tab -->
                <div id="trading-rules" class="tab-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_max_position_size">
                                    <?php esc_html_e('Maximum Position Size (%)', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" id="<?php echo esc_attr($api_id); ?>_max_position_size" 
                                       name="<?php echo esc_attr($api_id); ?>_max_position_size" 
                                       value="<?php echo esc_attr($max_position_size); ?>" 
                                       class="small-text" min="1" max="100" step="0.1">
                                <p class="description">
                                    <?php esc_html_e('Maximum percentage of portfolio to allocate to a single position', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_stop_loss_percent">
                                    <?php esc_html_e('Default Stop Loss (%)', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" id="<?php echo esc_attr($api_id); ?>_stop_loss_percent" 
                                       name="<?php echo esc_attr($api_id); ?>_stop_loss_percent" 
                                       value="<?php echo esc_attr($stop_loss_percent); ?>" 
                                       class="small-text" min="0.1" max="50" step="0.1">
                                <p class="description">
                                    <?php esc_html_e('Default percentage below purchase price to set stop loss orders', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($api_id); ?>_take_profit_percent">
                                    <?php esc_html_e('Default Take Profit (%)', 'tradepress'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" id="<?php echo esc_attr($api_id); ?>_take_profit_percent" 
                                       name="<?php echo esc_attr($api_id); ?>_take_profit_percent" 
                                       value="<?php echo esc_attr($take_profit_percent); ?>" 
                                       class="small-text" min="0.1" max="100" step="0.1">
                                <p class="description">
                                    <?php esc_html_e('Default percentage above purchase price to set take profit orders', 'tradepress'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="api-settings-actions">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Save All Settings', 'tradepress'); ?>
                </button>
            </div>
        </form>
