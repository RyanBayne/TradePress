<?php
/**
 * Admin View: Trading Platforms - Endpoints Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current platform
$current_platform = isset($_GET['platform']) ? sanitize_text_field($_GET['platform']) : 'alpaca';

// Load platform-specific endpoints class
$class_name = 'TradePress_' . ucfirst($current_platform) . '_Endpoints';
$endpoints = null;

if (class_exists($class_name)) {
    $endpoints = new $class_name();
}

// Get tab mode information
$page_id = 'tradingplatforms';
$tab_id = 'endpoints';
$tab_mode = tradepress_get_tab_mode($page_id, $tab_id);

// Add header with mode indicator
echo '<div class="tradepress-tab-header">';
echo '<h2>' . esc_html__('Available Endpoints', 'tradepress') . '</h2>';
echo tradepress_get_tab_mode_indicator($page_id, $tab_id);
echo '</div>';

// Show demo notice if in demo mode
if ($tab_mode['mode'] === 'demo') {
    TradePress_Admin_Notices::simple_demo_indicator();
}
?>

<div class="endpoints-table-wrapper">
    <?php if ($endpoints): ?>
        <table class="endpoints-table widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e('Method', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Endpoint', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Description', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Actions', 'tradepress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($endpoints->get_all_endpoints() as $endpoint): ?>
                    <tr>
                        <td>
                            <span class="method-badge method-<?php echo esc_attr(strtolower($endpoint['method'])); ?>">
                                <?php echo esc_html($endpoint['method']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="endpoint-path"><?php echo esc_html($endpoint['path']); ?></div>
                            <?php if (!empty($endpoint['version'])): ?>
                                <div class="endpoint-version">v<?php echo esc_html($endpoint['version']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="endpoint-description"><?php echo esc_html($endpoint['description']); ?></div>
                            <?php if (!empty($endpoint['parameters'])): ?>
                                <div class="endpoint-parameters">
                                    <strong><?php esc_html_e('Parameters:', 'tradepress'); ?></strong> 
                                    <?php echo esc_html(implode(', ', $endpoint['parameters'])); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($endpoint['testable']): ?>
                                <button class="test-button" data-endpoint="<?php echo esc_attr($endpoint['id']); ?>">
                                    <?php esc_html_e('Test', 'tradepress'); ?>
                                </button>
                            <?php else: ?>
                                <button class="test-button maintenance" disabled>
                                    <?php esc_html_e('Not Available', 'tradepress'); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="notice notice-warning">
            <p><?php esc_html_e('No endpoints available for this platform.', 'tradepress'); ?></p>
        </div>
    <?php endif; ?>
</div>

<div id="endpoint-test-results" class="api-response" style="display: none;"></div>
