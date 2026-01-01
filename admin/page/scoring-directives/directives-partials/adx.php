<?php
if (!defined('ABSPATH')) exit;
$directive = $all_directives['adx'];
?>
<div class="directive-section">
    <div class="section-header">
        <h3>
            <span class="construction-icon dashicons dashicons-admin-tools"></span>
            <?php echo esc_html($directive['name']); ?>
        </h3>
    </div>
    <div class="section-content">
        <div class="directive-description">
            <p><?php echo esc_html($directive['description']); ?></p>
        </div>
        <div class="directive-settings">
            <div class="setting-group">
                <label><?php esc_html_e('Weight:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" value="<?php echo esc_attr($directive['weight'] ?? 10); ?>" min="0" max="100">
                </div>
            </div>
            <div class="setting-group">
                <label><?php esc_html_e('ADX Period:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" value="14" min="5" max="30">
                </div>
            </div>
            <div class="setting-group">
                <label><?php esc_html_e('Trend Threshold:', 'tradepress'); ?></label>
                <div class="setting-control">
                    <input type="number" value="25" min="15" max="40">
                    <p class="setting-description"><?php esc_html_e('ADX value above which trend is considered strong.', 'tradepress'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>