<?php
/**
 * TradePress - Create Scoring Directives Strategies Tab
 * 
 * VIEW FILE - STRATEGY CREATION INTERFACE
 * ========================================
 * This is a VIEW FILE that displays the interface for creating and managing scoring strategies.
 * It is separate from trading strategies, which are built using directives with the goal of making a decision.
 * This interface does not allow scoring strategies to be assigned to trading strategies, use the Trading Strategy Creator for that.
 * It is focused on assigning directives to strategies and configuring their weights, to determine how they contribute to the overall scoring.
 * 
 * The scores determined by each strategy are used to rank symbols in the TradePress analysis.
 * Trading strategies can then use these scores to make decisions.
 * 
 * Data Source: scoring-system/directives-loader.php
 * Directive Definitions: scoring-system/directives-register.php
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load the centralized directives loader instead of the missing data class
if (!function_exists('tradepress_get_all_directives')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-loader.php';
}

// Get all directives from the centralized loader function
$all_directives = tradepress_get_all_directives();
?>

<div class="tradepress-strategies-interface">
    <div class="strategies-content">
        <div class="strategies-grid">
            <!-- Mock Trading Strategies -->
            <div class="strategy-card">
                <h4><?php esc_html_e('Momentum Trading Strategy', 'tradepress'); ?></h4>
                <p><?php esc_html_e('Focus on stocks with strong price momentum and volume confirmation.', 'tradepress'); ?></p>
                
                <div class="strategy-directives">
                    <h5><?php esc_html_e('Assigned Directives:', 'tradepress'); ?></h5>
                    <ul>
                        <li><span class="directive-name">Price Above SMA 50</span> <span class="directive-weight">30%</span></li>
                        <li><span class="directive-name">MACD Bullish Crossover</span> <span class="directive-weight">25%</span></li>
                        <li><span class="directive-name">Volume Surge</span> <span class="directive-weight">20%</span></li>
                        <li><span class="directive-name">RSI Not Overbought</span> <span class="directive-weight">15%</span></li>
                        <li><span class="directive-name">Earnings Calendar</span> <span class="directive-weight">10%</span></li>
                    </ul>
                </div>
                
                <div class="strategy-actions">
                    <button type="button" class="button button-primary"><?php esc_html_e('Configure', 'tradepress'); ?></button>
                    <button type="button" class="button button-secondary"><?php esc_html_e('Test Strategy', 'tradepress'); ?></button>
                </div>
            </div>
            
            <div class="strategy-card">
                <h4><?php esc_html_e('Value Investing Strategy', 'tradepress'); ?></h4>
                <p><?php esc_html_e('Identify undervalued stocks with strong fundamentals.', 'tradepress'); ?></p>
                
                <div class="strategy-directives">
                    <h5><?php esc_html_e('Assigned Directives:', 'tradepress'); ?></h5>
                    <ul>
                        <li><span class="directive-name">Low P/E Ratio</span> <span class="directive-weight">35%</span></li>
                        <li><span class="directive-name">Strong Dividend Yield</span> <span class="directive-weight">25%</span></li>
                        <li><span class="directive-name">RSI Oversold</span> <span class="directive-weight">20%</span></li>
                        <li><span class="directive-name">Price Below Book Value</span> <span class="directive-weight">20%</span></li>
                    </ul>
                </div>
                
                <div class="strategy-actions">
                    <button type="button" class="button button-primary"><?php esc_html_e('Configure', 'tradepress'); ?></button>
                    <button type="button" class="button button-secondary"><?php esc_html_e('Test Strategy', 'tradepress'); ?></button>
                </div>
            </div>
            
            <div class="strategy-card">
                <h4><?php esc_html_e('Breakout Strategy', 'tradepress'); ?></h4>
                <p><?php esc_html_e('Capture stocks breaking out of consolidation patterns.', 'tradepress'); ?></p>
                
                <div class="strategy-directives">
                    <h5><?php esc_html_e('Assigned Directives:', 'tradepress'); ?></h5>
                    <ul>
                        <li><span class="directive-name">Price Breakout</span> <span class="directive-weight">40%</span></li>
                        <li><span class="directive-name">Volume Confirmation</span> <span class="directive-weight">30%</span></li>
                        <li><span class="directive-name">Bollinger Band Squeeze</span> <span class="directive-weight">20%</span></li>
                        <li><span class="directive-name">Low Volatility</span> <span class="directive-weight">10%</span></li>
                    </ul>
                </div>
                
                <div class="strategy-actions">
                    <button type="button" class="button button-primary"><?php esc_html_e('Configure', 'tradepress'); ?></button>
                    <button type="button" class="button button-secondary"><?php esc_html_e('Test Strategy', 'tradepress'); ?></button>
                </div>
            </div>
        </div>
        
        <div class="create-strategy-section">
            <h3><?php esc_html_e('Create New Strategy', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Build a custom trading strategy by selecting and weighting directives.', 'tradepress'); ?></p>
            
            <form class="create-strategy-form">
                <div class="form-group">
                    <label for="strategy-name"><?php esc_html_e('Strategy Name:', 'tradepress'); ?></label>
                    <input type="text" id="strategy-name" class="regular-text" placeholder="<?php esc_attr_e('Enter strategy name', 'tradepress'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="strategy-description"><?php esc_html_e('Description:', 'tradepress'); ?></label>
                    <textarea id="strategy-description" rows="3" class="regular-text" placeholder="<?php esc_attr_e('Describe your trading strategy', 'tradepress'); ?>"></textarea>
                </div>
                
                <div class="form-group">
                    <label><?php esc_html_e('Select Directives:', 'tradepress'); ?></label>
                    <div class="directives-selection">
                        <?php foreach ($all_directives as $directive_id => $directive): ?>
                            <div class="directive-option">
                                <label>
                                    <input type="checkbox" name="strategy_directives[]" value="<?php echo esc_attr($directive_id); ?>">
                                    <?php echo esc_html($directive['name']); ?>
                                </label>
                                <input type="number" min="1" max="100" value="10" class="directive-weight small-text" placeholder="Weight %">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="button button-primary"><?php esc_html_e('Create Strategy', 'tradepress'); ?></button>
                    <button type="button" class="button button-secondary"><?php esc_html_e('Save as Template', 'tradepress'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
