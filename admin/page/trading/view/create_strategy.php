<?php
/**
 * Trading Area View: Create Strategies Tab
 * 
 * Provides a drag-and-drop interface for building trading strategies
 * by combining different scoring directives
 * 
 * @package TradePress/Admin/Trading
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue required scripts for drag and drop
wp_enqueue_script('jquery-ui-draggable');
wp_enqueue_script('jquery-ui-droppable');
wp_enqueue_script('jquery-ui-sortable');

// Get available scoring directives
$directives = array();

// Check if we have a scoring directives registry
if (class_exists('TradePress_Scoring_Directives_Registry')) {
    try {
        $registry = TradePress_Scoring_Directives_Registry::instance();
        if (method_exists($registry, 'get_directives')) {
            $directives = $registry->get_directives();
        }
    } catch (Exception $e) {
        // Log error
        error_log('Error loading scoring directives: ' . $e->getMessage());
    }
}

// If registry is empty or not loaded, use default directives
if (empty($directives)) {
    $directives = array(
        'rsi' => array(
            'name' => 'RSI (Relative Strength Index)',
            'description' => 'Momentum oscillator measuring speed and magnitude of price changes',
            'weight' => 15,
            'bullish' => 'RSI < 30 (oversold)',
            'bearish' => 'RSI > 70 (overbought)'
        ),
        'macd' => array(
            'name' => 'MACD (Moving Average Convergence Divergence)',
            'description' => 'Trend-following momentum indicator',
            'weight' => 20,
            'bullish' => 'MACD line crosses above signal line',
            'bearish' => 'MACD line crosses below signal line'
        ),
        'sma_20' => array(
            'name' => '20-Day Simple Moving Average',
            'description' => 'Average price over the last 20 days',
            'weight' => 10,
            'bullish' => 'Price above SMA 20',
            'bearish' => 'Price below SMA 20'
        ),
        'volume' => array(
            'name' => 'Volume Analysis',
            'description' => 'Trading volume compared to average',
            'weight' => 12,
            'bullish' => 'Above average volume on up days',
            'bearish' => 'Above average volume on down days'
        ),
        'bollinger_bands' => array(
            'name' => 'Bollinger Bands',
            'description' => 'Volatility indicator with upper and lower bands',
            'weight' => 8,
            'bullish' => 'Price near lower band',
            'bearish' => 'Price near upper band'
        ),
        'support_resistance' => array(
            'name' => 'Support & Resistance',
            'description' => 'Key price levels where stock tends to reverse',
            'weight' => 18,
            'bullish' => 'Price bouncing off support',
            'bearish' => 'Price breaking below support'
        ),
        'earnings_momentum' => array(
            'name' => 'Earnings Momentum',
            'description' => 'Recent earnings performance and guidance',
            'weight' => 17,
            'bullish' => 'Positive earnings surprise',
            'bearish' => 'Negative earnings surprise'
        )
    );
}
?>

<div class="tradepress-create-strategy-container">
    <?php
    // Check if demo mode is active
    if (function_exists('is_demo_mode') && is_demo_mode()) {
        echo '<div class="demo-indicator">';
        echo '<div class="demo-icon dashicons dashicons-admin-tools"></div>';
        echo '<div class="demo-text">';
        echo '<h4>Strategy Builder - Demo Mode</h4>';
        echo '<p>Create custom trading strategies by combining technical indicators and scoring rules.</p>';
        echo '</div>';
        echo '<span class="demo-badge">DEMO</span>';
        echo '</div>';
    }
    ?>

    <div class="create-strategy-header">
        <div class="strategy-meta-wrapper">
            <div class="strategy-name-field">
                <label for="strategy-name"><?php esc_html_e('Strategy Name:', 'tradepress'); ?></label>
                <input type="text" id="strategy-name" name="strategy_name" placeholder="<?php esc_attr_e('Enter strategy name', 'tradepress'); ?>" value="">
            </div>
            <div class="strategy-description-field">
                <label for="strategy-description"><?php esc_html_e('Description:', 'tradepress'); ?></label>
                <textarea id="strategy-description" name="strategy_description" placeholder="<?php esc_attr_e('Describe your strategy...', 'tradepress'); ?>"></textarea>
            </div>
        </div>
        <div class="strategy-actions">
            <button type="button" class="button button-primary save-strategy-button" id="save-strategy">
                <?php esc_html_e('Save Strategy', 'tradepress'); ?>
            </button>
            <button type="button" class="button button-secondary reset-strategy-button" id="reset-strategy">
                <?php esc_html_e('Reset', 'tradepress'); ?>
            </button>
        </div>
    </div>
    
    <form id="strategy-builder-form" method="post">
        <?php wp_nonce_field('save_custom_strategy', 'strategy_builder_nonce'); ?>
        <input type="hidden" name="strategy_id" value="<?php echo esc_attr('strategy_' . time() . '_' . rand(1000, 9999)); ?>">
        <input type="hidden" name="action" value="tradepress_save_custom_strategy">
        
        <div class="strategy-builder-columns">
            <!-- Left column: Available directives -->
            <div class="available-directives-column column">
                <h3><?php esc_html_e('Available Indicators', 'tradepress'); ?></h3>
                <p class="column-description"><?php esc_html_e('Drag indicators to the right column to build your strategy.', 'tradepress'); ?></p>
                
                <div class="search-directives">
                    <input type="text" id="search-directives" placeholder="<?php esc_attr_e('Search indicators...', 'tradepress'); ?>">
                </div>
                
                <div class="directives-list-wrapper">
                    <div class="directives-list available-list">
                        <?php foreach ($directives as $dir_id => $directive): ?>
                            <div class="directive-item" 
                                data-id="<?php echo esc_attr($dir_id); ?>"
                                data-name="<?php echo esc_attr($directive['name']); ?>"
                                data-description="<?php echo esc_attr($directive['description']); ?>"
                                data-bullish="<?php echo esc_attr($directive['bullish']); ?>"
                                data-bearish="<?php echo esc_attr($directive['bearish']); ?>"
                                data-weight="<?php echo esc_attr($directive['weight']); ?>">
                                <div class="directive-handle dashicons dashicons-menu"></div>
                                <div class="directive-content">
                                    <div class="directive-name"><?php echo esc_html($directive['name']); ?></div>
                                    <div class="directive-description"><?php echo esc_html($directive['description']); ?></div>
                                </div>
                                <div class="directive-actions">
                                    <span class="directive-info dashicons dashicons-info-outline" title="<?php esc_attr_e('View details', 'tradepress'); ?>"></span>
                                    <span class="add-directive dashicons dashicons-plus-alt" title="<?php esc_attr_e('Add to strategy', 'tradepress'); ?>"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right column: Strategy builder -->
            <div class="strategy-builder-column column">
                <h3><?php esc_html_e('Strategy Builder', 'tradepress'); ?></h3>
                <p class="column-description"><?php esc_html_e('Drag indicators to reorder. Add settings and weights to customize your strategy.', 'tradepress'); ?></p>
                
                <div class="strategy-settings-wrapper">
                    <div class="strategy-settings-toggle">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e('Strategy Settings', 'tradepress'); ?>
                        <span class="toggle-indicator dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="strategy-settings-panel">
                        <div class="settings-grid">
                            <div class="settings-column">
                                <div class="settings-group">
                                    <h4><?php esc_html_e('Trading Rules', 'tradepress'); ?></h4>
                                    <div class="settings-field">
                                        <label for="strategy-signal-threshold"><?php esc_html_e('Signal Threshold:', 'tradepress'); ?></label>
                                        <input type="number" id="strategy-signal-threshold" name="signal_threshold" min="50" max="100" value="75" step="1">
                                        <p class="field-description"><?php esc_html_e('Minimum score (%) required to generate a trading signal', 'tradepress'); ?></p>
                                    </div>
                                    <div class="settings-field">
                                        <label for="strategy-confirmation-count"><?php esc_html_e('Confirmation Count:', 'tradepress'); ?></label>
                                        <input type="number" id="strategy-confirmation-count" name="confirmation_count" min="1" max="10" value="2" step="1">
                                        <p class="field-description"><?php esc_html_e('Number of periods to confirm before generating a signal', 'tradepress'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="settings-column">
                                <div class="settings-group">
                                    <h4><?php esc_html_e('Risk Management', 'tradepress'); ?></h4>
                                    <div class="settings-field">
                                        <label for="strategy-stop-loss"><?php esc_html_e('Stop Loss (%):', 'tradepress'); ?></label>
                                        <input type="number" id="strategy-stop-loss" name="stop_loss" min="0" max="100" value="5" step="0.1">
                                    </div>
                                    <div class="settings-field">
                                        <label for="strategy-take-profit"><?php esc_html_e('Take Profit (%):', 'tradepress'); ?></label>
                                        <input type="number" id="strategy-take-profit" name="take_profit" min="0" max="100" value="15" step="0.1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="strategy-directives-wrapper">
                    <div class="strategy-directives-list" id="strategy-directives">
                        <div class="empty-strategy-message">
                            <div class="empty-icon">
                                <span class="dashicons dashicons-arrow-left-alt"></span>
                            </div>
                            <p><?php esc_html_e('Drag indicators from the left panel to build your strategy.', 'tradepress'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="total-weight-indicator">
                    <div class="weight-label"><?php esc_html_e('Total Weight:', 'tradepress'); ?></div>
                    <div class="weight-bar-wrapper">
                        <div class="weight-bar">
                            <div class="weight-progress" style="width: 0%"></div>
                        </div>
                        <div class="weight-value">0%</div>
                    </div>
                    <div class="weight-status">
                        <span class="weight-message"><?php esc_html_e('Add indicators to your strategy', 'tradepress'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Directive Details Modal -->
<div id="directive-details-modal" class="tp-modal">
    <div class="tp-modal-content">
        <div class="tp-modal-header">
            <h3 id="modal-directive-name"></h3>
            <span class="tp-close-modal">&times;</span>
        </div>
        <div class="tp-modal-body">
            <div class="directive-details">
                <div class="directive-details-section">
                    <h4><?php esc_html_e('Description', 'tradepress'); ?></h4>
                    <p id="modal-directive-description"></p>
                </div>
                <div class="directive-details-section">
                    <h4><?php esc_html_e('Signal Conditions', 'tradepress'); ?></h4>
                    <div class="signal-conditions">
                        <div class="condition-item">
                            <strong><?php esc_html_e('Bullish:', 'tradepress'); ?></strong>
                            <span id="modal-directive-bullish"></span>
                        </div>
                        <div class="condition-item">
                            <strong><?php esc_html_e('Bearish:', 'tradepress'); ?></strong>
                            <span id="modal-directive-bearish"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tp-modal-footer">
            <button type="button" class="button add-to-strategy"><?php esc_html_e('Add to Strategy', 'tradepress'); ?></button>
            <button type="button" class="button tp-close-button"><?php esc_html_e('Close', 'tradepress'); ?></button>
        </div>
    </div>
</div>

<!-- Directive Settings Template (JS will clone this) -->
<template id="directive-settings-template">
    <div class="directive-settings-panel" style="display: none;">
        <div class="settings-grid">
            <div class="settings-column">
                <div class="settings-field">
                    <label><?php esc_html_e('Weight:', 'tradepress'); ?></label>
                    <div class="weight-slider-container">
                        <input type="range" class="directive-weight-slider" min="1" max="100" value="10">
                        <span class="weight-value">10%</span>
                        <input type="hidden" class="directive-weight-input" name="directive_weights[]" value="10">
                    </div>
                </div>
                <div class="settings-field">
                    <label><?php esc_html_e('Signal Threshold:', 'tradepress'); ?></label>
                    <select class="directive-threshold-select" name="directive_thresholds[]">
                        <option value="low"><?php esc_html_e('Low Sensitivity', 'tradepress'); ?></option>
                        <option value="medium" selected><?php esc_html_e('Medium Sensitivity', 'tradepress'); ?></option>
                        <option value="high"><?php esc_html_e('High Sensitivity', 'tradepress'); ?></option>
                    </select>
                </div>
            </div>
            <div class="settings-column">
                <div class="settings-field">
                    <label><?php esc_html_e('Signal Direction:', 'tradepress'); ?></label>
                    <div class="radio-options">
                        <label>
                            <input type="radio" class="directive-direction-input" name="directive_direction_{ID}" value="both" checked>
                            <?php esc_html_e('Both', 'tradepress'); ?>
                        </label>
                        <label>
                            <input type="radio" class="directive-direction-input" name="directive_direction_{ID}" value="bullish">
                            <?php esc_html_e('Bullish Only', 'tradepress'); ?>
                        </label>
                        <label>
                            <input type="radio" class="directive-direction-input" name="directive_direction_{ID}" value="bearish">
                            <?php esc_html_e('Bearish Only', 'tradepress'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Strategy Directive Template (JS will clone this) -->
<template id="strategy-directive-template">
    <div class="strategy-directive" data-id="">
        <input type="hidden" name="directive_ids[]" value="">
        <div class="directive-drag-handle dashicons dashicons-menu"></div>
        <div class="directive-content">
            <div class="directive-header">
                <div class="directive-title"></div>
                <div class="directive-actions">
                    <span class="directive-weight-badge">10%</span>
                    <span class="directive-settings-toggle dashicons dashicons-admin-generic" title="<?php esc_attr_e('Settings', 'tradepress'); ?>"></span>
                    <span class="directive-remove dashicons dashicons-no-alt" title="<?php esc_attr_e('Remove', 'tradepress'); ?>"></span>
                </div>
            </div>
            <div class="directive-body">
                <div class="directive-description"></div>
            </div>
            <!-- Directive settings panel will be inserted here -->
        </div>
    </div>
</template>