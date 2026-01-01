<?php
/**
 * Admin View: API - Overview Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue overview tab specific styles
wp_enqueue_style(
    'tradepress-overview-tab-styles',
    TRADEPRESS_PLUGIN_URL . 'assets/css/api-view/overview.css',
    array(),
    TRADEPRESS_VERSION
);

?>

<div class="wrap">
    <h1><?php esc_html_e('Trading API Overview', 'tradepress'); ?></h1>
    
    <div class="api-overview-content">
        <div class="overview-intro">
            <h2><?php esc_html_e('Trading API Integrations', 'tradepress'); ?></h2>
            <p>
                <?php esc_html_e('TradePress integrates with various trading APIs to provide real-time market data, trading execution, and account management.', 'tradepress'); ?>
            </p>
        </div>
        
        <div class="overview-section">
            <h3><?php esc_html_e('Available Integrations', 'tradepress'); ?></h3>
            <ul>
                <li><?php esc_html_e('Alpaca API - Real-time trading and market data.', 'tradepress'); ?></li>
                <li><?php esc_html_e('Alpha Vantage API - Comprehensive market data.', 'tradepress'); ?></li>
                <li><?php esc_html_e('Discord API - Notifications and community engagement.', 'tradepress'); ?></li>
            </ul>
        </div>
        
        <div class="overview-section">
            <h3><?php esc_html_e('Key Features', 'tradepress'); ?></h3>
            <ul>
                <li><?php esc_html_e('Real-time market data updates.', 'tradepress'); ?></li>
                <li><?php esc_html_e('Automated trading execution.', 'tradepress'); ?></li>
                <li><?php esc_html_e('Customizable notifications and alerts.', 'tradepress'); ?></li>
                <li><?php esc_html_e('Integration with Discord for community engagement.', 'tradepress'); ?></li>
            </ul>
        </div>
        
        <div class="overview-section">
            <h3><?php esc_html_e('Getting Started', 'tradepress'); ?></h3>
            <p>
                <?php esc_html_e('To get started, navigate to the API settings tab and configure your preferred trading APIs.', 'tradepress'); ?>
            </p>
            <p>
                <?php esc_html_e('Ensure you have the necessary API keys and credentials for each integration.', 'tradepress'); ?>
            </p>
        </div>
    </div>
</div>