<?php
/**
 * Admin View: API - API Switches Tab
 * 
 * Interface for controlling which API tabs are visible and which APIs are enabled
 *
 * @package TradePress/Admin
 * @version 1.0.0
 * @since 2025-04-19
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the API Directory if needed
if (!class_exists('TradePress_API_Directory')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . '/api/api-directory.php';
}

// Get API data from directory
$all_providers = TradePress_API_Directory::get_all_providers();
$api_array = array_keys($all_providers);
$api_display_names = array();
foreach ($all_providers as $id => $provider) {
    $api_display_names[$id] = $provider['name'];
}

// Remove Yahoo Finance from the list of APIs
unset($api_display_names['yahoo_finance']);
unset($api_array[array_search('yahoo_finance', $api_array)]);

// Check if demo mode is active
$is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;
?>



<div class="wrap tradepress-api-switches">

    <form method="post" id="api-switches-form" action="<?php echo esc_url(admin_url('admin.php?page=tradepress_platforms&tab=api_switches')); ?>">
        <?php wp_nonce_field('tradepress-api-switches'); ?>

        <table class="form-table">
            <tbody>
                <?php
                foreach ($api_array as $api) {
                    $display_name = isset($api_display_names[$api]) ? $api_display_names[$api] : ucfirst($api);
                    ?>
                    <tr>
                        <th scope="row">
                            <label><?php echo esc_html($display_name); ?></label>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span><?php echo esc_html($display_name); ?></span>
                                </legend>
                                <label>
                                    <input name="switch_<?php echo esc_attr($api); ?>_services" type="checkbox" value="yes" <?php checked(get_option('TradePress_switch_' . $api . '_api_services', 'no'), 'yes'); ?>>
                                    <?php printf(esc_html__('Display %s Tab', 'tradepress'), $display_name); ?>
                                </label>
                                <br>
                                <label>
                                    <input name="switch_<?php echo esc_attr($api); ?>_logs" type="checkbox" value="yes" <?php checked(get_option('TradePress_switch_' . $api . '_api_logs', 'no'), 'yes'); ?>>
                                    <?php printf(esc_html__('Log %s API Activity', 'tradepress'), $display_name); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" name="save_api_switches" class="button-primary" value="Save Changes">
                <?php esc_html_e('Save Changes', 'tradepress'); ?>
            </button>
        </p>
    </form>
</div>