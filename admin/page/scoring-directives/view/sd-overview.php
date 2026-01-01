<?php
/**
 * TradePress - Scoring Directives Overview Tab
 * 
 * VIEW FILE - DISPLAYS DIRECTIVE STATISTICS AND OVERVIEW
 * ====================================================== 
 * This is a VIEW FILE that displays overview statistics and information about scoring directives.
 * It does NOT contain directive logic or data - it retrieves data from the centralized
 * directives-loader.php functions for display purposes only.
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

// Calculate statistics
$total_directives = count($all_directives);
$active_directives = 0;
$inactive_directives = 0;
$strategy_assignments = 0;

foreach ($all_directives as $directive) {
    if ($directive['active']) {
        $active_directives++;
    } else {
        $inactive_directives++;
    }
    if (isset($directive['used_in_strategies']) && $directive['used_in_strategies']) {
        $strategy_assignments += $directive['strategy_count'];
    }
}
?>

<div class="tradepress-scoring-overview">
    <div class="scoring-stats-cards">
        <div class="stats-card">
            <div class="stats-icon">📊</div>
            <div class="stats-content">
                <h3><?php echo esc_html($total_directives); ?></h3>
                <p><?php esc_html_e('Total Directives', 'tradepress'); ?></p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-icon">✅</div>
            <div class="stats-content">
                <h3><?php echo esc_html($active_directives); ?></h3>
                <p><?php esc_html_e('Active Directives', 'tradepress'); ?></p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-icon">⚙️</div>
            <div class="stats-content">
                <h3><?php echo esc_html($strategy_assignments); ?></h3>
                <p><?php esc_html_e('Strategy Assignments', 'tradepress'); ?></p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-icon">🎯</div>
            <div class="stats-content">
                <h3>85%</h3>
                <p><?php esc_html_e('System Health', 'tradepress'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="scoring-overview-content">
        <div class="overview-main">
            <h3><?php esc_html_e('Directive Categories', 'tradepress'); ?></h3>
            
            <div class="category-list">
                <div class="category-item">
                    <div class="category-header">
                        <h4><?php esc_html_e('Technical Analysis', 'tradepress'); ?></h4>
                        <span class="category-count">8 directives</span>
                    </div>
                    <div class="category-examples">
                        <span class="example-tag">Price Above SMA 50</span>
                        <span class="example-tag">RSI Oversold</span>
                        <span class="example-tag">MACD Bullish Crossover</span>
                    </div>
                </div>
                
                <div class="category-item">
                    <div class="category-header">
                        <h4><?php esc_html_e('Fundamental Analysis', 'tradepress'); ?></h4>
                        <span class="category-count">6 directives</span>
                    </div>
                    <div class="category-examples">
                        <span class="example-tag">Low P/E Ratio</span>
                        <span class="example-tag">Strong Dividend Yield</span>
                        <span class="example-tag">EPS Growth</span>
                    </div>
                </div>
                
                <div class="category-item">
                    <div class="category-header">
                        <h4><?php esc_html_e('Market Events', 'tradepress'); ?></h4>
                        <span class="category-count">4 directives</span>
                    </div>
                    <div class="category-examples">
                        <span class="example-tag">Earnings Calendar</span>
                        <span class="example-tag">ISA Reset</span>
                        <span class="example-tag">Ex-Dividend Date</span>
                    </div>
                </div>
                
                <div class="category-item">
                    <div class="category-header">
                        <h4><?php esc_html_e('Sentiment Analysis', 'tradepress'); ?></h4>
                        <span class="category-count">3 directives</span>
                    </div>
                    <div class="category-examples">
                        <span class="example-tag">News Sentiment</span>
                        <span class="example-tag">Social Media Buzz</span>
                        <span class="example-tag">Analyst Upgrades</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overview-sidebar">
            <div class="quick-actions">
                <h4><?php esc_html_e('Quick Actions', 'tradepress'); ?></h4>
                <div class="action-buttons">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_scoring_directives&tab=algorithm')); ?>" class="button button-primary">
                        <?php esc_html_e('Configure Algorithm', 'tradepress'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_scoring_directives&tab=testing')); ?>" class="button button-secondary">
                        <?php esc_html_e('Test Directives', 'tradepress'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_scoring_directives&tab=strategies')); ?>" class="button button-secondary">
                        <?php esc_html_e('Manage Strategies', 'tradepress'); ?>
                    </a>
                </div>
            </div>
            
            <div class="recent-activity">
                <h4><?php esc_html_e('Recent Activity', 'tradepress'); ?></h4>
                <div class="activity-list">
                    <div class="activity-item">
                        <span class="activity-time">2 hours ago</span>
                        <span class="activity-text"><?php esc_html_e('RSI Oversold directive triggered for NVDA', 'tradepress'); ?></span>
                    </div>
                    <div class="activity-item">
                        <span class="activity-time">4 hours ago</span>
                        <span class="activity-text"><?php esc_html_e('Algorithm completed processing 247 symbols', 'tradepress'); ?></span>
                    </div>
                    <div class="activity-item">
                        <span class="activity-time">6 hours ago</span>
                        <span class="activity-text"><?php esc_html_e('MACD Bullish Crossover activated for AAPL', 'tradepress'); ?></span>
                    </div>
                    <div class="activity-item">
                        <span class="activity-time">1 day ago</span>
                        <span class="activity-text"><?php esc_html_e('ISA Reset directive configuration updated', 'tradepress'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>