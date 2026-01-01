<?php
/**
 * TradePress - Scoring Directives Logs Tab
 * 
 * Monitor scoring algorithm execution, directive performance, and system activity
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="tradepress-logs-interface">
    <div class="logs-controls">
        <div class="log-filters">
            <select id="log-level" class="tp-select">
                <option value="all"><?php esc_html_e('All Levels', 'tradepress'); ?></option>
                <option value="info"><?php esc_html_e('Info', 'tradepress'); ?></option>
                <option value="warning"><?php esc_html_e('Warning', 'tradepress'); ?></option>
                <option value="error"><?php esc_html_e('Error', 'tradepress'); ?></option>
                <option value="debug"><?php esc_html_e('Debug', 'tradepress'); ?></option>
            </select>
            
            <select id="log-source" class="tp-select">
                <option value="all"><?php esc_html_e('All Sources', 'tradepress'); ?></option>
                <option value="algorithm"><?php esc_html_e('Algorithm', 'tradepress'); ?></option>
                <option value="directives"><?php esc_html_e('Directives', 'tradepress'); ?></option>
                <option value="api"><?php esc_html_e('API Calls', 'tradepress'); ?></option>
                <option value="scoring"><?php esc_html_e('Scoring Engine', 'tradepress'); ?></option>
            </select>
            
            <input type="date" id="log-date" class="tp-input" value="<?php echo esc_attr(date('Y-m-d')); ?>">
            
            <button type="button" class="tp-button tp-button-primary refresh-logs-btn">
                <?php esc_html_e('Refresh', 'tradepress'); ?>
            </button>
            
            <button type="button" class="tp-button tp-button-secondary clear-logs-btn">
                <?php esc_html_e('Clear Logs', 'tradepress'); ?>
            </button>
        </div>
    </div>
    
    <div class="logs-content">
        <div class="logs-table-container">
            <table class="widefat logs-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Time', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Level', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Source', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Message', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Context', 'tradepress'); ?></th>
                    </tr>
                </thead>
                <tbody id="logs-tbody">
                    <?php 
                    // Sample log entries for demonstration
                    $sample_logs = array(
                        array(
                            'time' => date('H:i:s'),
                            'level' => 'info',
                            'source' => 'algorithm',
                            'message' => 'Scoring algorithm execution completed successfully',
                            'context' => '247 symbols processed'
                        ),
                        array(
                            'time' => date('H:i:s', strtotime('-2 minutes')),
                            'level' => 'debug',
                            'source' => 'directives',
                            'message' => 'RSI Oversold directive triggered for NVDA',
                            'context' => 'RSI: 28.4, Threshold: 30'
                        ),
                        array(
                            'time' => date('H:i:s', strtotime('-5 minutes')),
                            'level' => 'warning',
                            'source' => 'api',
                            'message' => 'API rate limit approaching',
                            'context' => '95% of daily limit used'
                        ),
                        array(
                            'time' => date('H:i:s', strtotime('-8 minutes')),
                            'level' => 'info',
                            'source' => 'scoring',
                            'message' => 'Symbol score updated: AAPL',
                            'context' => 'Score: 78 (was 72)'
                        ),
                        array(
                            'time' => date('H:i:s', strtotime('-12 minutes')),
                            'level' => 'error',
                            'source' => 'api',
                            'message' => 'Failed to fetch data for MSFT',
                            'context' => 'Connection timeout after 30s'
                        ),
                        array(
                            'time' => date('H:i:s', strtotime('-15 minutes')),
                            'level' => 'info',
                            'source' => 'algorithm',
                            'message' => 'Algorithm execution started',
                            'context' => 'Mode: Full scan, Symbols: 247'
                        )
                    );
                    
                    foreach ($sample_logs as $log): ?>
                        <tr class="log-entry log-<?php echo esc_attr($log['level']); ?>">
                            <td class="log-time"><?php echo esc_html($log['time']); ?></td>
                            <td class="log-level">
                                <span class="level-badge level-<?php echo esc_attr($log['level']); ?>">
                                    <?php echo esc_html(ucfirst($log['level'])); ?>
                                </span>
                            </td>
                            <td class="log-source"><?php echo esc_html(ucfirst($log['source'])); ?></td>
                            <td class="log-message"><?php echo esc_html($log['message']); ?></td>
                            <td class="log-context"><?php echo esc_html($log['context']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="logs-pagination">
            <div class="pagination-info">
                <?php esc_html_e('Showing 1-20 of 156 log entries', 'tradepress'); ?>
            </div>
            <div class="pagination-controls">
                <button type="button" class="tp-button tp-button-outline" disabled>
                    <?php esc_html_e('Previous', 'tradepress'); ?>
                </button>
                <span class="page-numbers">
                    <span class="current">1</span>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <span>...</span>
                    <a href="#">8</a>
                </span>
                <button type="button" class="tp-button tp-button-outline">
                    <?php esc_html_e('Next', 'tradepress'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <div class="logs-statistics">
        <h4><?php esc_html_e('Today\'s Activity Summary', 'tradepress'); ?></h4>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number">247</span>
                <span class="stat-label"><?php esc_html_e('Symbols Processed', 'tradepress'); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number">1,423</span>
                <span class="stat-label"><?php esc_html_e('API Calls Made', 'tradepress'); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number">12</span>
                <span class="stat-label"><?php esc_html_e('Directives Triggered', 'tradepress'); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number">3</span>
                <span class="stat-label"><?php esc_html_e('Errors Logged', 'tradepress'); ?></span>
            </div>
        </div>
    </div>
</div>
