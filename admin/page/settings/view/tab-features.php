<?php
/**
 * Features Tab
 * 
 * Manages feature toggles and configuration options for TradePress
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get saved settings with defaults
$enable_market_data = get_option('TradePress_enable_market_data', 'yes');
$enable_portfolio_tracking = get_option('TradePress_enable_portfolio_tracking', 'yes');
$enable_trading_signals = get_option('TradePress_enable_trading_signals', 'yes');
$enable_api_integrations = get_option('TradePress_enable_api_integrations', 'yes');
$enable_advanced_charts = get_option('TradePress_enable_advanced_charts', 'yes');
$enable_risk_management = get_option('TradePress_enable_risk_management', 'no');
$enable_strategy_backtesting = get_option('TradePress_enable_strategy_backtesting', 'no');
$enable_social_trading = get_option('TradePress_enable_social_trading', 'no');

// Process form submission
if (isset($_POST['tradepress_features_settings_nonce']) && wp_verify_nonce($_POST['tradepress_features_settings_nonce'], 'tradepress_features_settings')) {
    
    // Update feature toggles
    $enable_market_data = isset($_POST['TradePress_enable_market_data']) ? 'yes' : 'no';
    update_option('TradePress_enable_market_data', $enable_market_data);
    
    $enable_portfolio_tracking = isset($_POST['TradePress_enable_portfolio_tracking']) ? 'yes' : 'no';
    update_option('TradePress_enable_portfolio_tracking', $enable_portfolio_tracking);
    
    $enable_trading_signals = isset($_POST['TradePress_enable_trading_signals']) ? 'yes' : 'no';
    update_option('TradePress_enable_trading_signals', $enable_trading_signals);
    
    $enable_api_integrations = isset($_POST['TradePress_enable_api_integrations']) ? 'yes' : 'no';
    update_option('TradePress_enable_api_integrations', $enable_api_integrations);
    
    $enable_advanced_charts = isset($_POST['TradePress_enable_advanced_charts']) ? 'yes' : 'no';
    update_option('TradePress_enable_advanced_charts', $enable_advanced_charts);
    
    $enable_risk_management = isset($_POST['TradePress_enable_risk_management']) ? 'yes' : 'no';
    update_option('TradePress_enable_risk_management', $enable_risk_management);
    
    $enable_strategy_backtesting = isset($_POST['TradePress_enable_strategy_backtesting']) ? 'yes' : 'no';
    update_option('TradePress_enable_strategy_backtesting', $enable_strategy_backtesting);
    
    $enable_social_trading = isset($_POST['TradePress_enable_social_trading']) ? 'yes' : 'no';
    update_option('TradePress_enable_social_trading', $enable_social_trading);
    
    // Show success message
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Features settings saved successfully.', 'tradepress'); ?></p>
        </div>
        <?php
    });
}
?>

<div class="tradepress-settings-container">
    <div class="tradepress-settings-header">
        <h2><?php esc_html_e('Features Management', 'tradepress'); ?></h2>
        <p class="description"><?php esc_html_e('Enable or disable specific features in the TradePress plugin.', 'tradepress'); ?></p>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field('tradepress_features_settings', 'tradepress_features_settings_nonce'); ?>
        
        <div class="tradepress-settings-grid">
            <!-- Core Features Section -->
            <div class="tradepress-settings-card">
                <h3><?php esc_html_e('Core Features', 'tradepress'); ?></h3>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Market Data', 'tradepress'); ?></th>
                        <td>
                            <label class="tradepress-switch">
                                <input type="checkbox" name="TradePress_enable_market_data" value="1" <?php checked($enable_market_data, 'yes'); ?>>
                                <span class="tradepress-slider round"></span>
                            </label>
                            <p class="description"><?php esc_html_e('Enable market data retrieval and display', 'tradepress'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Portfolio Tracking', 'tradepress'); ?></th>
                        <td>
                            <label class="tradepress-switch">
                                <input type="checkbox" name="TradePress_enable_portfolio_tracking" value="1" <?php checked($enable_portfolio_tracking, 'yes'); ?>>
                                <span class="tradepress-slider round"></span>
                            </label>
                            <p class="description"><?php esc_html_e('Enable portfolio tracking and performance monitoring', 'tradepress'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Trading Signals', 'tradepress'); ?></th>
                        <td>
                            <label class="tradepress-switch">
                                <input type="checkbox" name="TradePress_enable_trading_signals" value="1" <?php checked($enable_trading_signals, 'yes'); ?>>
                                <span class="tradepress-slider round"></span>
                            </label>
                            <p class="description"><?php esc_html_e('Enable trading signal generation and alerts', 'tradepress'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('API Integrations', 'tradepress'); ?></th>
                        <td>
                            <label class="tradepress-switch">
                                <input type="checkbox" name="TradePress_enable_api_integrations" value="1" <?php checked($enable_api_integrations, 'yes'); ?>>
                                <span class="tradepress-slider round"></span>
                            </label>
                            <p class="description"><?php esc_html_e('Enable third-party API integrations', 'tradepress'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Advanced Features Section -->
            <div class="tradepress-settings-card">
                <h3><?php esc_html_e('Advanced Features', 'tradepress'); ?></h3>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Advanced Charting', 'tradepress'); ?></th>
                        <td>
                            <label class="tradepress-switch">
                                <input type="checkbox" name="TradePress_enable_advanced_charts" value="1" <?php checked($enable_advanced_charts, 'yes'); ?>>
                                <span class="tradepress-slider round"></span>
                            </label>
                            <p class="description"><?php esc_html_e('Enable advanced charting capabilities', 'tradepress'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Risk Management Tools', 'tradepress'); ?></th>
                        <td>
                            <label class="tradepress-switch">
                                <input type="checkbox" name="TradePress_enable_risk_management" value="1" <?php checked($enable_risk_management, 'yes'); ?>>
                                <span class="tradepress-slider round"></span>
                            </label>
                            <p class="description"><?php esc_html_e('Enable advanced risk management tools and calculations', 'tradepress'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Strategy Backtesting', 'tradepress'); ?></th>
                        <td>
                            <label class="tradepress-switch">
                                <input type="checkbox" name="TradePress_enable_strategy_backtesting" value="1" <?php checked($enable_strategy_backtesting, 'yes'); ?>>
                                <span class="tradepress-slider round"></span>
                            </label>
                            <p class="description"><?php esc_html_e('Enable strategy backtesting capabilities', 'tradepress'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Social Trading', 'tradepress'); ?></th>
                        <td>
                            <label class="tradepress-switch">
                                <input type="checkbox" name="TradePress_enable_social_trading" value="1" <?php checked($enable_social_trading, 'yes'); ?>>
                                <span class="tradepress-slider round"></span>
                            </label>
                            <p class="description"><?php esc_html_e('Enable social trading and community features', 'tradepress'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary" name="save_features_settings">
                <?php esc_html_e('Save Changes', 'tradepress'); ?>
            </button>
        </p>
    </form>
</div>

<!-- Styles moved to assets/css/pages/settings-tab-features.css -->
