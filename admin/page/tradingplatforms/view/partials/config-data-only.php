<?php
/**
 * Partial: Data-Only API Configuration
 * 
 * This partial template contains configuration options for data-only API services
 * that don't offer trading functionality.
 * 
 * @package TradePress
 * @subpackage admin/page/TradingPlatforms
 * @version 1.0.1
 * @since 1.0.0
 * @created 2023-06-15 14:30:00
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables that must be defined before including this template:
// $api_id - The API identifier (e.g., 'alphavantage')

// Get the current API key setting
$api_key_option_name = 'TradePress_api_' . $api_id . '_key';

// Special case for Alpha Vantage to ensure consistency
if ($api_id === 'alphavantage') {
    $api_key_option_name = 'tradepress_api_alphavantage_key';
}

$api_key = get_option($api_key_option_name, '');

// Check if there's a value in alternative option formats and migrate if needed
if (empty($api_key) && $api_id === 'alphavantage') {
    $alt_key = get_option('TradePress_alphavantage_api_key', '');
    if (!empty($alt_key)) {
        update_option($api_key_option_name, $alt_key);
        $api_key = $alt_key;
    } else {
        $alt_key_2 = get_option('tradepress_alphavantage_api_key', '');
        if (!empty($alt_key_2)) {
            update_option($api_key_option_name, $alt_key_2);
            $api_key = $alt_key_2;
        }
    }
}

$api_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no') === 'yes';
$update_frequency = get_option('tradepress_' . $api_id . '_update_frequency', 'daily');
$data_retention = get_option('tradepress_' . $api_id . '_data_retention', '30');
$data_priority = get_option('tradepress_' . $api_id . '_data_priority', 'normal');
?>


        <?php if (empty($api_key)): ?>
        <div class="notice notice-warning inline">
            <p>
                <span class="dashicons dashicons-warning"></span>
                <?php esc_html_e('API key not configured. Enter your API key below to enable data access.', 'tradepress'); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <?php
        // Display settings updated message if set
        if (get_transient('tradepress_' . $api_id . '_settings_updated')) {
            ?>
            <div class="notice notice-success inline">
                <p>
                    <span class="dashicons dashicons-yes"></span>
                    <?php esc_html_e('Settings saved successfully.', 'tradepress'); ?>
                </p>
            </div>
            <?php
            delete_transient('tradepress_' . $api_id . '_settings_updated');
        }
        ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="api-settings-form">
            <?php wp_nonce_field('tradepress_save_' . $api_id . '_settings', 'tradepress_' . $api_id . '_nonce'); ?>
            <input type="hidden" name="action" value="tradepress_save_<?php echo esc_attr($api_id); ?>_settings">
            <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
            
            <!-- Check if the user has the required capabilities -->
            <?php if (current_user_can('manage_options')): ?>
            
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
                            <?php esc_html_e('Enable or disable this data source', 'tradepress'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="<?php echo esc_attr($api_key_option_name); ?>">
                            <?php esc_html_e('API Key', 'tradepress'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="password" id="<?php echo esc_attr($api_key_option_name); ?>" 
                               name="<?php echo esc_attr($api_key_option_name); ?>" 
                               value="<?php echo esc_attr($api_key); ?>" 
                               class="regular-text" autocomplete="off">
                        <button type="button" class="button toggle-password" 
                                data-target="<?php echo esc_attr($api_key_option_name); ?>">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                        <p class="description">
                            <?php 
                            if ($api_id === 'alphavantage') {
                                printf(
                                    __('Enter your Alpha Vantage API key. You can get a free API key from %1$s<a href="%2$s" target="_blank">alphavantage.co</a>%3$s.', 'tradepress'),
                                    '<strong>',
                                    esc_url('https://www.alphavantage.co/support/#api-key'),
                                    '</strong>'
                                );
                            } else {
                                esc_html_e('Enter your API key for data access', 'tradepress');
                            }
                            ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="<?php echo esc_attr($api_id); ?>_update_frequency">
                            <?php esc_html_e('Update Frequency', 'tradepress'); ?>
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
                            <?php esc_html_e('How often to refresh data from this API', 'tradepress'); ?>
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
            
            <div class="api-settings-actions">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Save Settings', 'tradepress'); ?>
                </button>
                <button type="button" class="button test-api-connection" data-api="<?php echo esc_attr($api_id); ?>" id="test-connection-<?php echo esc_attr($api_id); ?>">
                    <span class="button-text"><?php esc_html_e('Test Connection (API Key Required)', 'tradepress'); ?></span>
                </button>
            </div>
            
            <?php else: ?>
            <div class="notice notice-error inline">
                <p>
                    <span class="dashicons dashicons-warning"></span>
                    <?php esc_html_e('You do not have sufficient permissions to modify these settings.', 'tradepress'); ?>
                </p>
            </div>
            <?php endif; ?>
        </form>


<script>
jQuery(document).ready(function($) {
    // Update test connection button based on API key field
    function updateTestButton() {
        var apiKey = $('#<?php echo esc_js($api_key_option_name); ?>').val().trim();
        var button = $('#test-connection-<?php echo esc_js($api_id); ?>');
        var buttonText = button.find('.button-text');
        
        if (apiKey.length > 0) {
            button.prop('disabled', false).removeClass('disabled');
            buttonText.text('<?php esc_html_e('Test Connection (Ready)', 'tradepress'); ?>');
        } else {
            button.prop('disabled', true).addClass('disabled');
            buttonText.text('<?php esc_html_e('Test Connection (API Key Required)', 'tradepress'); ?>');
        }
    }
    
    // Update button on page load
    updateTestButton();
    
    // Update button when API key field changes
    $('#<?php echo esc_js($api_key_option_name); ?>').on('input keyup paste', updateTestButton);
});
</script>
