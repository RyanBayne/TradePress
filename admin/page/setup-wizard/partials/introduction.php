<?php
/**
 * Setup Wizard - Introduction Step
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$developer_mode = get_option('tradepress_developer_mode', false);
$alpaca_key = get_option('tradepress_alpaca_api_key', '');
$alpaca_secret = get_option('tradepress_alpaca_secret_key', '');
$alpha_vantage_key = get_option('tradepress_alpha_vantage_api_key', '');
?>

<h1><?php _e('Setup & Configuration', 'tradepress'); ?></h1>

<form method="post">
    <div class="tradepress-setup-section">
        <h2><?php _e('Developer Mode', 'tradepress'); ?></h2>
        <p><?php _e('Enable developer mode to access advanced debugging tools, detailed logging, and development features.', 'tradepress'); ?></p>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="tradepress_developer_mode"><?php _e('Developer Mode', 'tradepress'); ?></label></th>
                <td>
                    <input type="checkbox" id="tradepress_developer_mode" name="tradepress_developer_mode" class="input-checkbox" value="1" <?php checked($developer_mode, 1); ?> />
                    <label for="tradepress_developer_mode"><?php _e('Enable developer mode (shows debug info, file paths, and advanced tools)', 'tradepress'); ?></label>
                </td>
            </tr>
        </table>
    </div>

    <div class="tradepress-setup-section">
        <h2><?php _e('API Configuration', 'tradepress'); ?></h2>
        <p><?php _e('Configure your API keys for market data and trading platforms. Currently supporting Alpaca and Alpha Vantage APIs.', 'tradepress'); ?></p>
        <p><em><?php _e('Note: Additional API providers can be configured later in the settings.', 'tradepress'); ?></em></p>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="tradepress_alpaca_api_key"><?php _e('Alpaca API Key', 'tradepress'); ?></label></th>
                <td>
                    <input type="text" id="tradepress_alpaca_api_key" name="tradepress_alpaca_api_key" class="regular-text" value="<?php echo esc_attr($alpaca_key); ?>" placeholder="PKXXXXXXXXXXXXXXXX" />
                    <p class="description"><?php _e('Your Alpaca API key for trading and market data access.', 'tradepress'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tradepress_alpaca_secret_key"><?php _e('Alpaca Secret Key', 'tradepress'); ?></label></th>
                <td>
                    <input type="password" id="tradepress_alpaca_secret_key" name="tradepress_alpaca_secret_key" class="regular-text" value="<?php echo esc_attr($alpaca_secret); ?>" placeholder="Your secret key" />
                    <p class="description"><?php _e('Your Alpaca secret key (kept secure and encrypted).', 'tradepress'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tradepress_alpha_vantage_api_key"><?php _e('Alpha Vantage API Key', 'tradepress'); ?></label></th>
                <td>
                    <input type="text" id="tradepress_alpha_vantage_api_key" name="tradepress_alpha_vantage_api_key" class="regular-text" value="<?php echo esc_attr($alpha_vantage_key); ?>" placeholder="XXXXXXXXXXXXXXXX" />
                    <p class="description"><?php _e('Your Alpha Vantage API key for market data and financial information.', 'tradepress'); ?></p>
                </td>
            </tr>
        </table>
        
        <div class="tradepress-dev-section" id="tradepress-dev-section" style="<?php echo $developer_mode ? '' : 'display: none;'; ?>">
            <h3><?php _e('Development Mode: Paper Trading Credentials', 'tradepress'); ?></h3>
            <p><?php _e('Paste your API credentials (api_name:key:secret format, one per line)', 'tradepress'); ?></p>
            <textarea name="tradepress_mass_credentials" class="large-text" rows="3" placeholder="alpaca:PKXXXXXXXX:your_secret_key&#10;alpha_vantage:PKXXXXXXXXXXX"></textarea>
            <p class="description"><?php _e('Format: api_name:key:secret (or api_name:key for Alpha Vantage). Supported: alpaca, alpha_vantage', 'tradepress'); ?></p>
        </div>
    </div>

    <div class="tradepress-disclaimer-section">
        <h2><?php _e('Important Disclaimers', 'tradepress'); ?></h2>
        
        <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-bottom: 15px;">
            <h3><?php _e('No Financial Advice', 'tradepress'); ?></h3>
            <p><strong><?php _e('TradePress does NOT provide financial, investment, or trading advice.', 'tradepress'); ?></strong></p>
            <p><?php _e('All data and analysis are for informational purposes only. You are solely responsible for all trading decisions and how the plugin is configured. TradePress offers alternative approaches to exploring potential investments with no gaurantees of success. Paper trading and strict success standards is recommended before automated trading is setup.', 'tradepress'); ?></p>
        </div>
    </div>

    <p class="tradepress-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Save & Continue', 'tradepress'); ?>" name="save_step" />
        <a href="<?php echo esc_url(admin_url()); ?>" class="button button-large"><?php _e('Not right now', 'tradepress'); ?></a>
        <?php wp_nonce_field('tradepress-setup'); ?>
    </p>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const devModeCheckbox = document.getElementById('tradepress_developer_mode');
    const devSection = document.getElementById('tradepress-dev-section');
    
    if (devModeCheckbox && devSection) {
        devModeCheckbox.addEventListener('change', function() {
            devSection.style.display = this.checked ? 'block' : 'none';
        });
    }
});
</script>