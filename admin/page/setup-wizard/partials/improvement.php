<?php
/**
 * Setup Wizard - Improvement Step
 */

if (!defined('ABSPATH')) {
    exit;
}

// Display database creation results if available
$database_results = get_transient('tradepress_database_results');
if ($database_results) {
    echo '<div class="tp-notice tp-notice-success">';
    echo '<div class="tp-notice-icon">💾</div>';
    echo '<div><strong>Database Setup Complete</strong><br>' . esc_html($database_results) . '</div>';
    echo '</div>';
    delete_transient('tradepress_database_results');
}
?>

<h1><?php _e('Options', 'tradepress'); ?></h1>

<form method="post">
    <h3><?php _e('Examples', 'tradepress'); ?></h3>
    <p><?php _e('The following options are for new users of the plugin and are the quickest way to learn how to get the most out of it.', 'tradepress'); ?></p>
    
    <table class="form-table">
        <tr>
            <th scope="row"><label for="TradePress_install_samples"><?php _e('Do you want to install example symbols as custom posts?', 'tradepress'); ?></label></th>
            <td>
                <input type="checkbox" id="TradePress_install_samples" name="TradePress_install_samples" class="input-checkbox" value="yes" />
                <label for="TradePress_install_samples"><?php _e('Yes, install some example posts.', 'tradepress'); ?></label>
            </td>
        </tr>
    </table>
    
    <h3><?php _e('Improvement Program &amp; Feedback Options', 'tradepress'); ?></h3>
    <p><?php _e('The plugin can share information about the features you use and errors. You may also be asked to provide feedback when using new features.', 'tradepress'); ?></p>
    
    <table class="form-table">
        <tr>
            <th scope="row"><label for="TradePress_feedback_data"><?php _e('Allow none-sensitive information to be sent?', 'tradepress'); ?></label></th>
            <td>
                <input type="checkbox" id="TradePress_feedback_data" <?php checked(get_option('TradePress_feedback_data', '') !== 'disabled', true); ?> name="TradePress_feedback_data" class="input-checkbox" value="1" />
                <label for="TradePress_feedback_data"><?php _e('Yes, send configuration and error logs only.', 'tradepress'); ?></label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="TradePress_feedback_prompt"><?php _e('Allow the plugin to prompt you for feedback in the future?', 'tradepress'); ?></label></th>
            <td>
                <input type="checkbox" <?php checked(get_option('TradePress_feedback_prompt', 'no'), 'yes'); ?> id="TradePress_feedback_prompt" name="TradePress_feedback_prompt" class="input-checkbox" value="1" />
                <label for="TradePress_feedback_prompt"><?php _e('Yes, prompt me occasionally.', 'tradepress'); ?></label>
            </td>
        </tr>
    </table>
    
    <p class="tradepress-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'tradepress'); ?>" name="save_step" />
        <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php _e('Skip this step', 'tradepress'); ?></a>
        <?php wp_nonce_field('tradepress-setup'); ?>
    </p>
</form>