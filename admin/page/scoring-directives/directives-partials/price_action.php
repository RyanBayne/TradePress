<?php
if (!defined('ABSPATH')) exit;
$directive = $all_directives['price_action'];
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
                    <input type="number" value="<?php echo esc_attr($directive['weight'] ?? 20); ?>" min="0" max="100">
                </div>
            </div>
        </div>
    </div>
</div>