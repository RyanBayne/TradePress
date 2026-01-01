<?php
/**
 * TradePress Automation Dashboard
 *
 * Main admin interface for the algorithm automation features
 *
 * @package TradePress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Create logger instance for diagnostic panel
$logger = new TradePress_Logger();
?>

<div class="wrap tradepress-automation-wrap">
    <h1><?php _e('TradePress Automation', 'tradepress'); ?></h1>
    
    <div class="dashboard-controls">
        <button class="button button-primary start-all-button">
            <?php _e('Start All Components', 'tradepress'); ?>
        </button>
        <button class="button stop-all-button">
            <?php _e('Stop All Components', 'tradepress'); ?>
        </button>
    </div>
    
    <!-- Updated components grid with two-column layout -->
    <div class="components-grid">
        <!-- Left column with stacked component cards -->
        <div class="component-cards-column">
            <!-- Scoring Algorithm Component -->
            <div class="component-card" id="scoring-algorithm">
                <h4><?php _e('Scoring Algorithm', 'tradepress'); ?></h4>
                <div class="component-metrics">
                    <div>
                        <span class="metric-label"><?php _e('Status', 'tradepress'); ?></span>
                        <span class="metric-value status-value">Active</span>
                    </div>
                    <div>
                        <span class="metric-label"><?php _e('Symbols', 'tradepress'); ?></span>
                        <span class="metric-value">156</span>
                    </div>
                </div>
                <div class="component-controls">
                    <button class="button button-primary start-button">
                        <?php _e('Start', 'tradepress'); ?>
                    </button>
                    <button class="button stop-button">
                        <?php _e('Stop', 'tradepress'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Trading Signals Component -->
            <div class="component-card" id="trading-signals">
                <h4><?php _e('Trading Signals', 'tradepress'); ?></h4>
                <div class="component-metrics">
                    <div>
                        <span class="metric-label"><?php _e('Status', 'tradepress'); ?></span>
                        <span class="metric-value status-value">Inactive</span>
                    </div>
                    <div>
                        <span class="metric-label"><?php _e('Active Signals', 'tradepress'); ?></span>
                        <span class="metric-value">0</span>
                    </div>
                </div>
                <div class="component-controls">
                    <button class="button button-primary start-button">
                        <?php _e('Start', 'tradepress'); ?>
                    </button>
                    <button class="button stop-button" disabled>
                        <?php _e('Stop', 'tradepress'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Trading Bot Component -->
            <div class="component-card" id="trading-bot">
                <h4><?php _e('Trading Bot', 'tradepress'); ?></h4>
                <div class="component-metrics">
                    <div>
                        <span class="metric-label"><?php _e('Status', 'tradepress'); ?></span>
                        <span class="metric-value status-value">Inactive</span>
                    </div>
                    <div>
                        <span class="metric-label"><?php _e('Open Positions', 'tradepress'); ?></span>
                        <span class="metric-value">0</span>
                    </div>
                </div>
                <div class="component-controls">
                    <button class="button button-primary start-button">
                        <?php _e('Start', 'tradepress'); ?>
                    </button>
                    <button class="button stop-button" disabled>
                        <?php _e('Stop', 'tradepress'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Right column with information display -->
        <div class="component-info-column">
            <h2><?php _e('Market Intelligence', 'tradepress'); ?></h2>
            
            <!-- Highest Scoring Stock -->
            <div class="info-item">
                <h3><?php _e('Highest Scoring Stock', 'tradepress'); ?></h3>
                <div class="info-value">AAPL (87.5)</div>
                <div class="info-meta"><?php _e('Updated 5 minutes ago', 'tradepress'); ?></div>
                <div class="info-details">
                    <p><?php _e('Strong bullish indicators with positive RSI momentum', 'tradepress'); ?></p>
                </div>
            </div>
            
            <!-- Best Trading Signal -->
            <div class="info-item">
                <h3><?php _e('Best Trading Signal', 'tradepress'); ?></h3>
                <div class="info-value"><?php _e('Buy MSFT (Confidence: High)', 'tradepress'); ?></div>
                <div class="info-meta"><?php _e('Generated 15 minutes ago', 'tradepress'); ?></div>
                <div class="info-details">
                    <p><?php _e('MACD crossover with increasing volume', 'tradepress'); ?></p>
                </div>
            </div>
            
            <!-- Latest Trade -->
            <div class="info-item">
                <h3><?php _e('Latest Trade', 'tradepress'); ?></h3>
                <div class="info-value"><?php _e('No trades executed', 'tradepress'); ?></div>
                <div class="info-meta"><?php _e('Bot is currently inactive', 'tradepress'); ?></div>
                <div class="info-details">
                    <p><?php _e('Enable the Trading Bot to execute trades based on signals', 'tradepress'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rest of the automation container with diagnostic panel, etc. -->
    <div class="tradepress-automation-container">
        <div class="tradepress-automation-left-column">
            <!-- ALGORITHM CONTROL BOX -->
            <div class="tradepress-automation-box" id="tradepress-algorithm-control">
                <h2><?php _e('Algorithm Control', 'tradepress'); ?></h2>
                
                <div class="tradepress-control-buttons">
                    <button class="button button-primary" id="tradepress-start-algorithm">
                        <?php _e('Start Algorithm', 'tradepress'); ?>
                    </button>
                    
                    <button class="button" id="tradepress-stop-algorithm" disabled>
                        <?php _e('Stop Algorithm', 'tradepress'); ?>
                    </button>
                </div>
                
                <div class="tradepress-algorithm-status">
                    <p><strong><?php _e('Status:', 'tradepress'); ?></strong> 
                        <span id="tradepress-algorithm-status-text"><?php _e('Idle', 'tradepress'); ?></span>
                    </p>
                    
                    <p><strong><?php _e('Runtime:', 'tradepress'); ?></strong> 
                        <span id="tradepress-algorithm-runtime">00:00:00</span>
                    </p>
                    
                    <p><strong><?php _e('Last Run:', 'tradepress'); ?></strong> 
                        <span id="tradepress-algorithm-last-run">
                            <?php 
                            $last_run = get_option('tradepress_algorithm_last_run');
                            echo $last_run ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_run) : __('Never', 'tradepress'); 
                            ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <!-- SCHEDULE SETTINGS BOX -->
            <div class="tradepress-automation-box" id="tradepress-schedule-settings">
                <h2><?php _e('Schedule Settings', 'tradepress'); ?></h2>
                
                <form id="tradepress-schedule-form">
                    <div class="tradepress-form-group">
                        <label for="tradepress-schedule-type">
                            <?php _e('Schedule Type:', 'tradepress'); ?>
                        </label>
                        <select id="tradepress-schedule-type" name="schedule_type">
                            <option value="manual"><?php _e('Manual Only', 'tradepress'); ?></option>
                            <option value="hourly"><?php _e('Hourly', 'tradepress'); ?></option>
                            <option value="daily"><?php _e('Daily', 'tradepress'); ?></option>
                            <option value="market_hours"><?php _e('During Market Hours', 'tradepress'); ?></option>
                            <option value="custom"><?php _e('Custom Schedule', 'tradepress'); ?></option>
                        </select>
                    </div>
                    
                    <div class="tradepress-form-group tradepress-market-hours-options" style="display: none;">
                        <label>
                            <input type="checkbox" name="market_hours_nyse" value="1" checked> 
                            <?php _e('NYSE Hours (9:30 AM - 4:00 PM ET)', 'tradepress'); ?>
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="market_hours_lse" value="1"> 
                            <?php _e('LSE Hours (8:00 AM - 4:30 PM GMT)', 'tradepress'); ?>
                        </label>
                    </div>
                    
                    <div class="tradepress-form-group tradepress-daily-options" style="display: none;">
                        <label for="tradepress-daily-time">
                            <?php _e('Run daily at:', 'tradepress'); ?>
                        </label>
                        <input type="time" id="tradepress-daily-time" name="daily_time" value="18:00">
                    </div>
                    
                    <div class="tradepress-form-group">
                        <button type="submit" class="button button-primary">
                            <?php _e('Save Schedule', 'tradepress'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- API STATUS BOX -->
            <div class="tradepress-automation-box" id="tradepress-api-status">
                <h2><?php _e('API Status', 'tradepress'); ?></h2>
                
                <table class="widefat tradepress-api-status-table">
                    <thead>
                        <tr>
                            <th><?php _e('API', 'tradepress'); ?></th>
                            <th><?php _e('Status', 'tradepress'); ?></th>
                            <th><?php _e('Rate Limit', 'tradepress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>AlphaVantage</td>
                            <td>
                                <span class="tradepress-api-status-indicator active"></span>
                                <?php _e('Active', 'tradepress'); ?>
                            </td>
                            <td>
                                <span class="tradepress-api-rate-limit" data-api="alphavantage">
                                    <?php _e('80/100 remaining', 'tradepress'); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Trading212</td>
                            <td>
                                <span class="tradepress-api-status-indicator active"></span>
                                <?php _e('Active', 'tradepress'); ?>
                            </td>
                            <td>
                                <span class="tradepress-api-rate-limit" data-api="trading212">
                                    <?php _e('Unlimited', 'tradepress'); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Polygon</td>
                            <td>
                                <span class="tradepress-api-status-indicator inactive"></span>
                                <?php _e('Not Configured', 'tradepress'); ?>
                            </td>
                            <td>
                                <span class="tradepress-api-rate-limit" data-api="polygon">
                                    <?php _e('N/A', 'tradepress'); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- DIAGNOSTIC PANEL -->
            <div class="tradepress-automation-diagnostic-panel" id="tradepress-diagnostic-panel">
                <div class="tradepress-diagnostic-header">
                    <h2><?php _e('Diagnostic Information', 'tradepress'); ?></h2>
                    <span class="tradepress-diagnostic-refresh" id="tradepress-refresh-diagnostics">
                        <span class="dashicons dashicons-update"></span> <?php _e('Refresh', 'tradepress'); ?>
                    </span>
                </div>
                
                <div class="tradepress-diagnostic-tabs">
                    <div class="tradepress-diagnostic-tab active" data-tab="logs"><?php _e('Logs', 'tradepress'); ?></div>
                    <div class="tradepress-diagnostic-tab" data-tab="metrics"><?php _e('Metrics', 'tradepress'); ?></div>
                    <div class="tradepress-diagnostic-tab" data-tab="api"><?php _e('API Calls', 'tradepress'); ?></div>
                    <div class="tradepress-diagnostic-tab" data-tab="system"><?php _e('System', 'tradepress'); ?></div>
                </div>
                
                <div class="tradepress-diagnostic-content-container">
                    <!-- Logs Tab Content -->
                    <div class="tradepress-diagnostic-content active" id="tradepress-tab-logs">
                        <div class="tradepress-log-filters">
                            <select id="tradepress-log-level-filter">
                                <option value=""><?php _e('All Levels', 'tradepress'); ?></option>
                                <option value="error"><?php _e('Errors Only', 'tradepress'); ?></option>
                                <option value="warning"><?php _e('Warnings & Errors', 'tradepress'); ?></option>
                                <option value="info"><?php _e('Info & Above', 'tradepress'); ?></option>
                                <option value="debug"><?php _e('Debug (All)', 'tradepress'); ?></option>
                            </select>
                            
                            <select id="tradepress-log-category-filter">
                                <option value=""><?php _e('All Categories', 'tradepress'); ?></option>
                                <option value="algorithm"><?php _e('Algorithm', 'tradepress'); ?></option>
                                <option value="api"><?php _e('API', 'tradepress'); ?></option>
                                <option value="trading"><?php _e('Trading', 'tradepress'); ?></option>
                                <option value="system"><?php _e('System', 'tradepress'); ?></option>
                            </select>
                            
                            <button id="tradepress-apply-log-filters" class="button">
                                <?php _e('Apply Filters', 'tradepress'); ?>
                            </button>
                        </div>
                        
                        <div class="tradepress-log-entries">
                            <?php
                            // Display the most recent logs
                            $log_entries = $logger->get_log_entries(array(
                                'number' => 20,
                                'orderby' => 'timestamp',
                                'order' => 'DESC'
                            ));
                            
                            if (empty($log_entries)) {
                                echo '<div class="tradepress-empty-logs">';
                                _e('No log entries found. Log entries will appear here when the algorithm runs.', 'tradepress');
                                echo '</div>';
                            } else {
                                foreach ($log_entries as $entry) {
                                    $level_class = sanitize_html_class($entry['level']);
                                    echo '<div class="tradepress-log-entry ' . $level_class . '">';
                                    echo '<span class="tradepress-log-timestamp">' . date_i18n('Y-m-d H:i:s', strtotime($entry['timestamp'])) . '</span> ';
                                    echo '<span class="tradepress-log-level">[' . strtoupper($entry['level']) . ']</span> ';
                                    echo '<span class="tradepress-log-category">[' . esc_html($entry['category']) . ']</span> ';
                                    echo '<span class="tradepress-log-message">' . esc_html($entry['message']) . '</span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Metrics Tab Content -->
                    <div class="tradepress-diagnostic-content" id="tradepress-tab-metrics" style="display: none;">
                        <h3><?php _e('Performance Metrics', 'tradepress'); ?></h3>
                        
                        <table class="tradepress-metrics-table">
                            <tr>
                                <th><?php _e('Metric', 'tradepress'); ?></th>
                                <th><?php _e('Current', 'tradepress'); ?></th>
                                <th><?php _e('Average', 'tradepress'); ?></th>
                                <th><?php _e('Peak', 'tradepress'); ?></th>
                            </tr>
                            <tr>
                                <td><?php _e('Execution Time', 'tradepress'); ?></td>
                                <td id="metric-exec-time-current">-</td>
                                <td id="metric-exec-time-avg">-</td>
                                <td id="metric-exec-time-peak">-</td>
                            </tr>
                            <tr>
                                <td><?php _e('Memory Usage', 'tradepress'); ?></td>
                                <td id="metric-memory-current">-</td>
                                <td id="metric-memory-avg">-</td>
                                <td id="metric-memory-peak">-</td>
                            </tr>
                            <tr>
                                <td><?php _e('Database Queries', 'tradepress'); ?></td>
                                <td id="metric-queries-current">-</td>
                                <td id="metric-queries-avg">-</td>
                                <td id="metric-queries-peak">-</td>
                            </tr>
                            <tr>
                                <td><?php _e('API Calls', 'tradepress'); ?></td>
                                <td id="metric-api-current">-</td>
                                <td id="metric-api-avg">-</td>
                                <td id="metric-api-peak">-</td>
                            </tr>
                        </table>
                        
                        <h3><?php _e('Algorithm Statistics', 'tradepress'); ?></h3>
                        
                        <table class="tradepress-metrics-table">
                            <tr>
                                <th><?php _e('Statistic', 'tradepress'); ?></th>
                                <th><?php _e('Value', 'tradepress'); ?></th>
                            </tr>
                            <tr>
                                <td><?php _e('Symbols Processed', 'tradepress'); ?></td>
                                <td id="stat-symbols-processed">-</td>
                            </tr>
                            <tr>
                                <td><?php _e('Scoring Directives Active', 'tradepress'); ?></td>
                                <td id="stat-directives-active">-</td>
                            </tr>
                            <tr>
                                <td><?php _e('Highest Symbol Score', 'tradepress'); ?></td>
                                <td id="stat-highest-score">-</td>
                            </tr>
                            <tr>
                                <td><?php _e('Average Score', 'tradepress'); ?></td>
                                <td id="stat-avg-score">-</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- API Calls Tab Content -->
                    <div class="tradepress-diagnostic-content" id="tradepress-tab-api" style="display: none;">
                        <h3><?php _e('Recent API Calls', 'tradepress'); ?></h3>
                        
                        <div class="tradepress-api-calls-list">
                            <div class="tradepress-empty-api-calls">
                                <?php _e('API call data will be displayed here when available.', 'tradepress'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Tab Content -->
                    <div class="tradepress-diagnostic-content" id="tradepress-tab-system" style="display: none;">
                        <h3><?php _e('System Information', 'tradepress'); ?></h3>
                        
                        <table class="tradepress-metrics-table">
                            <tr>
                                <th><?php _e('Setting', 'tradepress'); ?></th>
                                <th><?php _e('Value', 'tradepress'); ?></th>
                            </tr>
                            <tr>
                                <td><?php _e('PHP Version', 'tradepress'); ?></td>
                                <td><?php echo PHP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><?php _e('WordPress Version', 'tradepress'); ?></td>
                                <td><?php echo get_bloginfo('version'); ?></td>
                            </tr>
                            <tr>
                                <td><?php _e('TradePress Version', 'tradepress'); ?></td>
                                <td><?php echo TRADEPRESS_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><?php _e('Memory Limit', 'tradepress'); ?></td>
                                <td><?php echo WP_MEMORY_LIMIT; ?></td>
                            </tr>
                            <tr>
                                <td><?php _e('Max Execution Time', 'tradepress'); ?></td>
                                <td><?php echo ini_get('max_execution_time') . ' ' . __('seconds', 'tradepress'); ?></td>
                            </tr>
                            <tr>
                                <td><?php _e('CRON Enabled', 'tradepress'); ?></td>
                                <td><?php echo defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? __('No', 'tradepress') : __('Yes', 'tradepress'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
