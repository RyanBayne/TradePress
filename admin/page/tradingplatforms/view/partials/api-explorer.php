<?php
/**
 * Partial: API Explorer
 * 
 * Provides an interface for exploring and testing API data.
 * 
 * @package TradePress/admin/page/TradingPlatforms
 * @version 1.0.0
 * @created 2024-05-21 15:30:00
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="api-explorer-container">
    <div class="explorer-header">
        <h3><?php esc_html_e('API Data Explorer', 'tradepress'); ?></h3>
        <p class="description"><?php esc_html_e('Use this tool to explore data available through the API.', 'tradepress'); ?></p>
    </div>
    
    <?php if ($is_demo_mode): ?>
    <!-- Demo mode indicator for data explorer -->
    <div class="demo-indicator">
        <div class="demo-icon dashicons dashicons-admin-tools"></div>
        <div class="demo-text">
            <h4><?php esc_html_e('Demo Mode', 'tradepress'); ?></h4>
            <p><?php esc_html_e('The explorer is showing simulated data.', 'tradepress'); ?></p>
        </div>
        <span class="demo-badge"><?php esc_html_e('DEMO', 'tradepress'); ?></span>
    </div>
    <?php endif; ?>
    
    <div class="explorer-controls">
        <!-- Explorer form fields will go here, tailored to the specific API -->
        <?php 
        // Different APIs will have different explorer interfaces
        // Check for a specific explorer partial for this API
        $specific_explorer = dirname(__FILE__) . '/' . $api_id . '-explorer.php';
        if (file_exists($specific_explorer)) {
            include $specific_explorer;
        } else {
            // Generic explorer interface
            ?>
            <div class="explorer-form">
                <div class="form-field">
                    <label for="data-type"><?php esc_html_e('Data Type', 'tradepress'); ?></label>
                    <select id="data-type" name="data_type">
                        <option value="stock"><?php esc_html_e('Stock Data', 'tradepress'); ?></option>
                        <option value="forex"><?php esc_html_e('Forex Data', 'tradepress'); ?></option>
                        <option value="crypto"><?php esc_html_e('Cryptocurrency Data', 'tradepress'); ?></option>
                    </select>
                </div>
                
                <div class="form-field">
                    <label for="symbol"><?php esc_html_e('Symbol', 'tradepress'); ?></label>
                    <input type="text" id="symbol" name="symbol" placeholder="<?php esc_attr_e('e.g. AAPL', 'tradepress'); ?>">
                </div>
                
                <div class="form-actions">
                    <button type="button" id="fetch-data" class="button button-primary">
                        <?php esc_html_e('Fetch Data', 'tradepress'); ?>
                    </button>
                </div>
            </div>
            
            <div class="explorer-results">
                <div class="results-header">
                    <h4><?php esc_html_e('Results', 'tradepress'); ?></h4>
                </div>
                
                <div class="results-content">
                    <div class="loading-indicator" style="display:none;">
                        <span class="spinner is-active"></span>
                        <?php esc_html_e('Loading data...', 'tradepress'); ?>
                    </div>
                    
                    <pre id="results-json"></pre>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
