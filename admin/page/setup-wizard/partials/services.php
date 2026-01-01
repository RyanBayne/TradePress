<?php
/**
 * Setup Wizard - Services Step
 */

if (!defined('ABSPATH')) {
    exit;
}

$gateways = $this->get_wizard_services();

// Display API test results if available
if (isset($_GET['api_test']) && $_GET['api_test'] === 'completed') {
    $test_results = get_transient('tradepress_api_test_results');
    if ($test_results) {
        echo '<div class="tradepress-api-test-results">';
        echo $test_results;
        echo '</div>';
        delete_transient('tradepress_api_test_results');
    }
}
?>

<h1><?php _e('Services', 'tradepress'); ?></h1>   
<p><?php _e('Activate premium services that are supported by cloud services. Currently limited to testers.', 'tradepress'); ?></p>

<form method="post" class="TradePress-wizard-plugin-extensions-form">
    <ul class="TradePress-wizard-plugin-extensions">
        <?php foreach ($gateways as $gateway_id => $gateway) : ?>
            <li class="TradePress-wizard-extension TradePress-wizard-extension-<?php echo esc_attr($gateway_id); ?>">
                <div class="TradePress-wizard-extension-enable">
                    <input type="checkbox" name="TradePress-wizard-extension-<?php echo esc_attr($gateway_id); ?>-enabled" class="input-checkbox" value="yes" />
                    <label><?php echo esc_html($gateway['name']); ?></label>
                </div>
                <div class="TradePress-wizard-extension-description">
                    <?php echo wp_kses_post(wpautop($gateway['description'])); ?>
                </div>
                <?php if (!empty($gateway['settings'])) : ?>
                    <table class="form-table TradePress-wizard-extension-settings">
                        <?php foreach ($gateway['settings'] as $setting_id => $setting) : ?>
                            <tr>
                                <th scope="row"><label for="<?php echo esc_attr($gateway_id); ?>_<?php echo esc_attr($setting_id); ?>"><?php echo esc_html($setting['label']); ?>:</label></th>
                                <td>
                                    <input
                                        type="<?php echo esc_attr($setting['type']); ?>"
                                        id="<?php echo esc_attr($gateway_id); ?>_<?php echo esc_attr($setting_id); ?>"
                                        name="<?php echo esc_attr($gateway_id); ?>_<?php echo esc_attr($setting_id); ?>"
                                        class="input-text"
                                        value="<?php echo esc_attr($setting['value']); ?>"
                                        placeholder="<?php echo esc_attr($setting['placeholder']); ?>"
                                        />
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
   
    <p class="tradepress-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'tradepress'); ?>" name="save_step" />
        <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php _e('Skip this step', 'tradepress'); ?></a>
        <?php wp_nonce_field('tradepress-setup'); ?>
    </p>
</form>