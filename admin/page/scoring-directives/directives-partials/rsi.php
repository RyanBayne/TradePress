<?php
/**
 * RSI Directive Configuration Partial
 * 
 * @package TradePress/Admin/Directives/Partials
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get RSI directive data
$directive = $all_directives['rsi'];
?>

<div class="directive-section">
    <div class="section-header">
        <h3>
            <span class="construction-icon dashicons dashicons-admin-tools" title="<?php esc_attr_e('Under Construction', 'tradepress'); ?>"></span>
            <?php echo esc_html($directive['name']); ?>
        </h3>
    </div>
    
    <div class="section-content">
        <?php if (!empty($directive['tip'])) : ?>
        <div class="directive-tip-box">
            <h4><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e('Usage Tip', 'tradepress'); ?></h4>
            <p class="directive-tip-content"><?php echo esc_html($directive['tip']); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="directive-description">
            <p><?php echo esc_html($directive['description']); ?></p>
        </div>
        
        <div class="directive-settings">
            <div class="setting-group">
                <label><?php esc_html_e('Weight:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" value="<?php echo esc_attr($directive['weight'] ?? 10); ?>" min="0" max="100">
                    <p class="setting-description"><?php esc_html_e('Importance of this directive in the overall scoring strategy.', 'tradepress'); ?></p>
                </div>
            </div>
            
            <div class="setting-group">
                <label><?php esc_html_e('RSI Period:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" value="14" min="1" max="50">
                    <p class="setting-description"><?php esc_html_e('Number of periods to calculate RSI (typically 14).', 'tradepress'); ?></p>
                </div>
            </div>
            
            <div class="setting-group">
                <label><?php esc_html_e('Oversold Threshold:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" value="30" min="10" max="40">
                    <p class="setting-description"><?php esc_html_e('RSI level considered oversold (buy signal).', 'tradepress'); ?></p>
                </div>
            </div>
            
            <div class="setting-group">
                <label><?php esc_html_e('Overbought Threshold:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" value="70" min="60" max="90">
                    <p class="setting-description"><?php esc_html_e('RSI level considered overbought (sell signal).', 'tradepress'); ?></p>
                </div>
            </div>
            
            <div class="directive-conditions">
                <h4><?php esc_html_e('Conditions', 'tradepress'); ?></h4>
                <div class="condition-group">
                    <label><?php esc_html_e('Bullish Signal:', 'tradepress'); ?></label>
                    <span><?php echo esc_html($directive['bullish'] ?? 'RSI < 30 (Oversold)'); ?></span>
                </div>
                <div class="condition-group">
                    <label><?php esc_html_e('Bearish Signal:', 'tradepress'); ?></label>
                    <span><?php echo esc_html($directive['bearish'] ?? 'RSI > 70 (Overbought)'); ?></span>
                </div>
            </div>
            
            <div class="directive-actions">
                <button type="button" class="button button-primary">
                    <?php esc_html_e('Save Configuration', 'tradepress'); ?>
                </button>
            </div>
        </div>
    </div>
</div>