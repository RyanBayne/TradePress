<?php
/**
 * Scoring Directives - Demo Mode Tab
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="tradepress-demo-mode-tab">
    <h2><?php esc_html_e('Demo Mode - Scoring Directives', 'tradepress'); ?></h2>
    
    <div class="demo-notice">
        <div class="notice notice-info">
            <p>
                <span class="dashicons dashicons-admin-tools"></span>
                <strong><?php esc_html_e('Demo Mode Active:', 'tradepress'); ?></strong>
                <?php esc_html_e('All scoring directives are running in demonstration mode. No live trading data is affected.', 'tradepress'); ?>
            </p>
        </div>
    </div>
    
    <div class="demo-features-grid">
        <div class="demo-feature-card">
            <h3><?php esc_html_e('Safe Testing Environment', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Test directive configurations without affecting live strategies', 'tradepress'); ?></p>
            <span class="demo-status active"><?php esc_html_e('Active', 'tradepress'); ?></span>
        </div>
        
        <div class="demo-feature-card">
            <h3><?php esc_html_e('Mock Data Sources', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Directives use simulated market data for testing', 'tradepress'); ?></p>
            <span class="demo-status active"><?php esc_html_e('Active', 'tradepress'); ?></span>
        </div>
        
        <div class="demo-feature-card">
            <h3><?php esc_html_e('Strategy Simulation', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Create and test strategies with demo data', 'tradepress'); ?></p>
            <span class="demo-status active"><?php esc_html_e('Active', 'tradepress'); ?></span>
        </div>
        
        <div class="demo-feature-card">
            <h3><?php esc_html_e('API Call Simulation', 'tradepress'); ?></h3>
            <p><?php esc_html_e('API calls return cached demo responses', 'tradepress'); ?></p>
            <span class="demo-status active"><?php esc_html_e('Active', 'tradepress'); ?></span>
        </div>
    </div>
    
    <div class="demo-controls-section">
        <h3><?php esc_html_e('Demo Controls', 'tradepress'); ?></h3>
        <div class="demo-controls">
            <button class="button button-primary" onclick="testDemoDirective();">
                <?php esc_html_e('Test Demo Directive', 'tradepress'); ?>
            </button>
            <button class="button" onclick="resetDemoData();">
                <?php esc_html_e('Reset Demo Data', 'tradepress'); ?>
            </button>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_scoring_directives&tab=create_strategies')); ?>" class="button">
                <?php esc_html_e('Create Demo Strategy', 'tradepress'); ?>
            </a>
        </div>
    </div>
    
    <div class="demo-info-section">
        <h3><?php esc_html_e('Demo Mode Information', 'tradepress'); ?></h3>
        <ul>
            <li><?php esc_html_e('All directive calculations use simulated market data', 'tradepress'); ?></li>
            <li><?php esc_html_e('Strategy scores are calculated but not saved to live systems', 'tradepress'); ?></li>
            <li><?php esc_html_e('API calls are intercepted and return demo responses', 'tradepress'); ?></li>
            <li><?php esc_html_e('No real trading decisions are made based on demo results', 'tradepress'); ?></li>
        </ul>
    </div>
</div>

<script>
function testDemoDirective() {
    alert('<?php esc_js(__('Demo directive test initiated. Check the logs tab for results.', 'tradepress')); ?>');
}

function resetDemoData() {
    if (confirm('<?php esc_js(__('Are you sure you want to reset all demo data?', 'tradepress')); ?>')) {
        alert('<?php esc_js(__('Demo data has been reset.', 'tradepress')); ?>');
    }
}
</script>

<style>
.demo-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.demo-feature-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    position: relative;
}

.demo-feature-card h3 {
    margin-top: 0;
    color: #23282d;
}

.demo-status {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
}

.demo-status.active {
    background: #46b450;
    color: white;
}

.demo-controls-section, .demo-info-section {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 4px;
}

.demo-controls {
    margin-top: 15px;
}

.demo-controls .button {
    margin-right: 10px;
}

.demo-info-section ul {
    margin-left: 20px;
}

.demo-info-section li {
    margin-bottom: 8px;
}
</style>