<?php
/**
 * Scoring Directives - Developer Mode Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="tradepress-developer-mode-tab">
    <h2><?php esc_html_e('Developer Mode - Scoring Directives', 'tradepress'); ?></h2>
    
    <div class="developer-tools-grid">
        <div class="developer-tool-card">
            <h3><?php esc_html_e('Directive Testing', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Test individual directives with live API data', 'tradepress'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_scoring_directives&tab=configure_directives')); ?>" class="button button-primary">
                <?php esc_html_e('Test Directives', 'tradepress'); ?>
            </a>
        </div>
        
        <div class="developer-tool-card">
            <h3><?php esc_html_e('API Call Monitoring', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Monitor API calls and caching for directive testing', 'tradepress'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_data&tab=api-activity')); ?>" class="button">
                <?php esc_html_e('View API Activity', 'tradepress'); ?>
            </a>
        </div>
        
        <div class="developer-tool-card">
            <h3><?php esc_html_e('Directive Logs', 'tradepress'); ?></h3>
            <p><?php esc_html_e('View detailed logs from directive calculations', 'tradepress'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_scoring_directives&tab=logs')); ?>" class="button">
                <?php esc_html_e('View Logs', 'tradepress'); ?>
            </a>
        </div>
        
        <div class="developer-tool-card">
            <h3><?php esc_html_e('Development Status', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Check development status of all directives', 'tradepress'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_scoring_directives&tab=directives_status')); ?>" class="button">
                <?php esc_html_e('View Status', 'tradepress'); ?>
            </a>
        </div>
    </div>
    
    <div class="developer-info-section">
        <h3><?php esc_html_e('Quick Actions', 'tradepress'); ?></h3>
        <div class="quick-actions">
            <button class="button" onclick="location.reload();">
                <?php esc_html_e('Refresh Page', 'tradepress'); ?>
            </button>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_development')); ?>" class="button">
                <?php esc_html_e('Development Hub', 'tradepress'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.developer-tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.developer-tool-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.developer-tool-card h3 {
    margin-top: 0;
    color: #23282d;
}

.developer-info-section {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 4px;
}

.quick-actions {
    margin-top: 15px;
}

.quick-actions .button {
    margin-right: 10px;
}
</style>