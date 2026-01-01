<?php
if (!defined('ABSPATH')) exit;
$directive = $all_directives['institutional_timing'];
?>
<div class="directive-section">
    <div class="section-header">
        <h3>
            <span class="temporal-icon dashicons dashicons-building"></span>
            <?php echo esc_html($directive['name']); ?>
            <span class="directive-code"><?php echo esc_html($directive['code']); ?></span>
        </h3>
        <div class="directive-meta">
            <span class="api-cost low"><?php echo esc_html($directive['api_cost']); ?> API Cost</span>
            <span class="update-freq"><?php echo esc_html($directive['update_frequency']); ?></span>
        </div>
    </div>
    <div class="section-content">
        <div class="directive-description">
            <p><?php echo esc_html($directive['description']); ?></p>
            <div class="directive-tip">
                <span class="tip-icon dashicons dashicons-lightbulb"></span>
                <strong>Trading Tip:</strong> <?php echo esc_html($directive['tip']); ?>
            </div>
        </div>
        <div class="directive-settings">
            <div class="setting-group">
                <label><?php esc_html_e('Weight:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" name="institutional_timing_weight" value="<?php echo esc_attr($directive['weight'] ?? 16); ?>" min="0" max="50">
                    <span class="setting-help">Impact on overall score</span>
                </div>
            </div>
            <div class="setting-group">
                <label><?php esc_html_e('Lookback Months:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" name="institutional_timing_lookback" value="6" min="3" max="12">
                    <span class="setting-help">Historical analysis period</span>
                </div>
            </div>
            <div class="setting-group">
                <label><?php esc_html_e('Period Weight:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" name="institutional_timing_period_weight" value="25" min="15" max="35">
                    <span class="setting-help">Month/quarter-end effect impact</span>
                </div>
            </div>
            <div class="setting-group">
                <label><?php esc_html_e('Flow Weight:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" name="institutional_timing_flow_weight" value="20" min="10" max="30">
                    <span class="setting-help">Institutional flow impact</span>
                </div>
            </div>
        </div>
        <div class="directive-signals">
            <div class="signal bullish">
                <span class="signal-icon dashicons dashicons-arrow-up-alt"></span>
                <strong>Bullish:</strong> <?php echo esc_html($directive['bullish']); ?>
            </div>
            <div class="signal bearish">
                <span class="signal-icon dashicons dashicons-arrow-down-alt"></span>
                <strong>Bearish:</strong> <?php echo esc_html($directive['bearish']); ?>
            </div>
        </div>
    </div>
</div>