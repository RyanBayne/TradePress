<?php
if (!defined('ABSPATH')) exit;
$directive = $all_directives['volume_rhythm'];
?>
<div class="directive-section">
    <div class="section-header">
        <h3>
            <span class="temporal-icon dashicons dashicons-chart-bar"></span>
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
                    <input type="number" name="volume_rhythm_weight" value="<?php echo esc_attr($directive['weight'] ?? 14); ?>" min="0" max="50">
                    <span class="setting-help">Impact on overall score</span>
                </div>
            </div>
            <div class="setting-group">
                <label><?php esc_html_e('Lookback Weeks:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" name="volume_rhythm_lookback" value="8" min="4" max="16">
                    <span class="setting-help">Volume pattern analysis period</span>
                </div>
            </div>
            <div class="setting-group">
                <label><?php esc_html_e('Volume Weight:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" name="volume_rhythm_volume_weight" value="30" min="15" max="45">
                    <span class="setting-help">Volume-price correlation impact</span>
                </div>
            </div>
            <div class="setting-group">
                <label><?php esc_html_e('Anomaly Weight:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" name="volume_rhythm_anomaly_weight" value="15" min="5" max="25">
                    <span class="setting-help">Volume anomaly detection impact</span>
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