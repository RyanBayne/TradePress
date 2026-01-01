<?php
/**
 * UI Library Status Indicators Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.0
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Status Indicators', 'tradepress'); ?></h3>
    <p><?php esc_html_e('Visual indicators for trading status, market conditions, and data states using existing TradePress styles.', 'tradepress'); ?></p>
    
    <div class="tradepress-component-group">
        <!-- Trading Status Badges -->
        <div class="component-demo">
            <h4><?php esc_html_e('Trading Status Badges', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="status-example">
                    <div class="status-label"><?php esc_html_e('Position Status', 'tradepress'); ?></div>
                    <div class="status-badges-row">
                        <span class="tradepress-badge tradepress-badge-success"><?php esc_html_e('Open', 'tradepress'); ?></span>
                        <span class="tradepress-badge tradepress-badge-warning"><?php esc_html_e('Pending', 'tradepress'); ?></span>
                        <span class="tradepress-badge tradepress-badge-error"><?php esc_html_e('Closed', 'tradepress'); ?></span>
                        <span class="tradepress-badge tradepress-badge-info"><?php esc_html_e('Monitoring', 'tradepress'); ?></span>
                    </div>
                </div>
                
                <div class="status-example">
                    <div class="status-label"><?php esc_html_e('Market Status', 'tradepress'); ?></div>
                    <div class="status-badges-row">
                        <span class="tradepress-badge tradepress-badge-success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Market Open', 'tradepress'); ?>
                        </span>
                        <span class="tradepress-badge tradepress-badge-error">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php esc_html_e('Market Closed', 'tradepress'); ?>
                        </span>
                        <span class="tradepress-badge tradepress-badge-warning">
                            <span class="dashicons dashicons-clock"></span>
                            <?php esc_html_e('Pre-Market', 'tradepress'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Connection Status -->
        <div class="component-demo">
            <h4><?php esc_html_e('Connection Status', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="connection-status-grid">
                    <div class="connection-status-item">
                        <div class="connection-status-header">
                            <span class="connection-status-dot connection-status-connected"></span>
                            <span class="connection-status-label"><?php esc_html_e('Alpha Vantage API', 'tradepress'); ?></span>
                        </div>
                        <div class="connection-status-details">
                            <small><?php esc_html_e('Connected • 25ms latency', 'tradepress'); ?></small>
                        </div>
                    </div>
                    
                    <div class="connection-status-item">
                        <div class="connection-status-header">
                            <span class="connection-status-dot connection-status-warning"></span>
                            <span class="connection-status-label"><?php esc_html_e('Trading Platform', 'tradepress'); ?></span>
                        </div>
                        <div class="connection-status-details">
                            <small><?php esc_html_e('Limited • Rate limited', 'tradepress'); ?></small>
                        </div>
                    </div>
                    
                    <div class="connection-status-item">
                        <div class="connection-status-header">
                            <span class="connection-status-dot connection-status-error"></span>
                            <span class="connection-status-label"><?php esc_html_e('News Feed', 'tradepress'); ?></span>
                        </div>
                        <div class="connection-status-details">
                            <small><?php esc_html_e('Disconnected • Check credentials', 'tradepress'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Performance Indicators -->
        <div class="component-demo">
            <h4><?php esc_html_e('Performance Indicators', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="performance-indicators-grid">
                    <div class="performance-card">
                        <div class="performance-value positive">+12.5%</div>
                        <div class="performance-label"><?php esc_html_e('Portfolio Return', 'tradepress'); ?></div>
                        <div class="performance-trend">
                            <span class="dashicons dashicons-arrow-up-alt"></span>
                            <span class="trend-value">+2.3%</span>
                        </div>
                    </div>
                    
                    <div class="performance-card">
                        <div class="performance-value negative">-3.8%</div>
                        <div class="performance-label"><?php esc_html_e('Daily P&L', 'tradepress'); ?></div>
                        <div class="performance-trend">
                            <span class="dashicons dashicons-arrow-down-alt"></span>
                            <span class="trend-value">-1.2%</span>
                        </div>
                    </div>
                    
                    <div class="performance-card">
                        <div class="performance-value neutral">$45,230</div>
                        <div class="performance-label"><?php esc_html_e('Available Balance', 'tradepress'); ?></div>
                        <div class="performance-trend">
                            <span class="dashicons dashicons-minus"></span>
                            <span class="trend-value">0.0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity Status -->
        <div class="component-demo">
            <h4><?php esc_html_e('Activity Status', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="activity-status-list">
                    <div class="activity-status-item">
                        <div class="activity-status-icon">
                            <span class="spinner is-active"></span>
                        </div>
                        <div class="activity-status-content">
                            <div class="activity-status-title"><?php esc_html_e('Processing Order', 'tradepress'); ?></div>
                            <div class="activity-status-description"><?php esc_html_e('BUY 100 AAPL @ Market', 'tradepress'); ?></div>
                        </div>
                        <div class="activity-status-time"><?php esc_html_e('2m ago', 'tradepress'); ?></div>
                    </div>
                    
                    <div class="activity-status-item">
                        <div class="activity-status-icon activity-success">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <div class="activity-status-content">
                            <div class="activity-status-title"><?php esc_html_e('Order Filled', 'tradepress'); ?></div>
                            <div class="activity-status-description"><?php esc_html_e('SELL 50 TSLA @ $245.50', 'tradepress'); ?></div>
                        </div>
                        <div class="activity-status-time"><?php esc_html_e('5m ago', 'tradepress'); ?></div>
                    </div>
                    
                    <div class="activity-status-item">
                        <div class="activity-status-icon activity-error">
                            <span class="dashicons dashicons-warning"></span>
                        </div>
                        <div class="activity-status-content">
                            <div class="activity-status-title"><?php esc_html_e('Order Rejected', 'tradepress'); ?></div>
                            <div class="activity-status-description"><?php esc_html_e('Insufficient buying power', 'tradepress'); ?></div>
                        </div>
                        <div class="activity-status-time"><?php esc_html_e('8m ago', 'tradepress'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Process Status Indicators -->
        <div class="component-demo">
            <h4><?php esc_html_e('Process Status Indicators', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="process-status-examples">
                    <div class="process-status-item">
                        <span class="process-status-dot process-status-running"></span>
                        <span class="process-status-label"><?php esc_html_e('Running Process', 'tradepress'); ?></span>
                        <span class="process-runtime">00:02:45</span>
                    </div>
                    
                    <div class="process-status-item">
                        <span class="process-status-dot process-status-stopped"></span>
                        <span class="process-status-label"><?php esc_html_e('Stopped Process', 'tradepress'); ?></span>
                        <span class="process-runtime">--:--:--</span>
                    </div>
                    
                    <div class="process-status-item">
                        <span class="process-status-dot process-status-error"></span>
                        <span class="process-status-label"><?php esc_html_e('Error State', 'tradepress'); ?></span>
                        <span class="process-runtime">Failed</span>
                    </div>
                </div>
                
                <div class="component-notes">
                    <h5><?php esc_html_e('Usage Notes:', 'tradepress'); ?></h5>
                    <ul>
                        <li><?php esc_html_e('Green dot with pulse animation indicates active/running state', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Red dot indicates stopped/inactive state', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Orange dot indicates error or warning state', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Runtime counter uses monospace font for consistent alignment', 'tradepress'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Data Freshness Indicators -->
        <div class="component-demo">
            <h4><?php esc_html_e('Data Freshness Indicators', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="data-freshness-grid">
                    <div class="data-freshness-item">
                        <div class="data-freshness-header">
                            <span class="data-freshness-title"><?php esc_html_e('Market Data', 'tradepress'); ?></span>
                            <span class="data-freshness-indicator fresh">
                                <span class="dashicons dashicons-update"></span>
                            </span>
                        </div>
                        <div class="data-freshness-timestamp"><?php esc_html_e('Last updated: 2 seconds ago', 'tradepress'); ?></div>
                    </div>
                    
                    <div class="data-freshness-item">
                        <div class="data-freshness-header">
                            <span class="data-freshness-title"><?php esc_html_e('Portfolio Values', 'tradepress'); ?></span>
                            <span class="data-freshness-indicator stale">
                                <span class="dashicons dashicons-clock"></span>
                            </span>
                        </div>
                        <div class="data-freshness-timestamp"><?php esc_html_e('Last updated: 5 minutes ago', 'tradepress'); ?></div>
                    </div>
                    
                    <div class="data-freshness-item">
                        <div class="data-freshness-header">
                            <span class="data-freshness-title"><?php esc_html_e('News Feed', 'tradepress'); ?></span>
                            <span class="data-freshness-indicator error">
                                <span class="dashicons dashicons-dismiss"></span>
                            </span>
                        </div>
                        <div class="data-freshness-timestamp"><?php esc_html_e('Update failed - check connection', 'tradepress'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Health Check Status -->
        <div class="component-demo">
            <h4><?php esc_html_e('System Health Status', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="health-check-container">
                    <div class="health-check-overall">
                        <div class="health-check-score">
                            <div class="health-score-value">87%</div>
                            <div class="health-score-label"><?php esc_html_e('System Health', 'tradepress'); ?></div>
                        </div>
                        <div class="health-check-status health-good">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Good', 'tradepress'); ?>
                        </div>
                    </div>
                    
                    <div class="health-check-details">
                        <div class="health-check-item">
                            <span class="health-check-icon health-check-pass">
                                <span class="dashicons dashicons-yes"></span>
                            </span>
                            <span class="health-check-label"><?php esc_html_e('API Connections', 'tradepress'); ?></span>
                            <span class="health-check-value"><?php esc_html_e('3/3 Active', 'tradepress'); ?></span>
                        </div>
                        
                        <div class="health-check-item">
                            <span class="health-check-icon health-check-warning">
                                <span class="dashicons dashicons-warning"></span>
                            </span>
                            <span class="health-check-label"><?php esc_html_e('Data Sync', 'tradepress'); ?></span>
                            <span class="health-check-value"><?php esc_html_e('2 minute delay', 'tradepress'); ?></span>
                        </div>
                        
                        <div class="health-check-item">
                            <span class="health-check-icon health-check-pass">
                                <span class="dashicons dashicons-yes"></span>
                            </span>
                            <span class="health-check-label"><?php esc_html_e('Database', 'tradepress'); ?></span>
                            <span class="health-check-value"><?php esc_html_e('Optimal', 'tradepress'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trading Signals Status -->
        <div class="component-demo">
            <h4><?php esc_html_e('Trading Signals Status', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="signals-status-grid">
                    <div class="signal-status-card signal-bullish">
                        <div class="signal-status-header">
                            <span class="signal-status-icon">
                                <span class="dashicons dashicons-arrow-up-alt"></span>
                            </span>
                            <span class="signal-status-label"><?php esc_html_e('Bullish Signal', 'tradepress'); ?></span>
                        </div>
                        <div class="signal-status-count">12</div>
                        <div class="signal-status-description"><?php esc_html_e('Active buy signals', 'tradepress'); ?></div>
                    </div>
                    
                    <div class="signal-status-card signal-bearish">
                        <div class="signal-status-header">
                            <span class="signal-status-icon">
                                <span class="dashicons dashicons-arrow-down-alt"></span>
                            </span>
                            <span class="signal-status-label"><?php esc_html_e('Bearish Signal', 'tradepress'); ?></span>
                        </div>
                        <div class="signal-status-count">5</div>
                        <div class="signal-status-description"><?php esc_html_e('Active sell signals', 'tradepress'); ?></div>
                    </div>
                    
                    <div class="signal-status-card signal-neutral">
                        <div class="signal-status-header">
                            <span class="signal-status-icon">
                                <span class="dashicons dashicons-minus"></span>
                            </span>
                            <span class="signal-status-label"><?php esc_html_e('Neutral', 'tradepress'); ?></span>
                        </div>
                        <div class="signal-status-count">28</div>
                        <div class="signal-status-description"><?php esc_html_e('Watchlist items', 'tradepress'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Add interactive demo script
    $status_script = "
        jQuery(document).ready(function($) {
            // Simulate real-time updates for connection status
            function updateConnectionStatus() {
                $('.connection-status-dot').each(function() {
                    var dot = $(this);
                    var statusClasses = ['connection-status-connected', 'connection-status-warning', 'connection-status-error'];
                    var currentClass = statusClasses.find(cls => dot.hasClass(cls));
                    
                    // Randomly change status occasionally
                    if (Math.random() < 0.1) {
                        var newClass = statusClasses[Math.floor(Math.random() * statusClasses.length)];
                        dot.removeClass(statusClasses.join(' ')).addClass(newClass);
                        
                        // Update status text
                        var statusText = '';
                        switch(newClass) {
                            case 'connection-status-connected':
                                statusText = 'Connected • ' + Math.floor(Math.random() * 50 + 10) + 'ms latency';
                                break;
                            case 'connection-status-warning':
                                statusText = 'Limited • Rate limited';
                                break;
                            case 'connection-status-error':
                                statusText = 'Disconnected • Check credentials';
                                break;
                        }
                        dot.closest('.connection-status-item').find('.connection-status-details small').text(statusText);
                    }
                });
            }
            
            // Simulate performance value changes
            function updatePerformanceValues() {
                $('.performance-value').each(function() {
                    var element = $(this);
                    if (Math.random() < 0.2) {
                        var currentText = element.text();
                        var isPercentage = currentText.includes('%');
                        var isDollar = currentText.includes('$');
                        
                        if (isPercentage && !isDollar) {
                            var value = parseFloat(currentText.replace(/[^-\d.]/g, ''));
                            var change = (Math.random() - 0.5) * 2; // -1 to +1
                            var newValue = value + change;
                            var newText = (newValue >= 0 ? '+' : '') + newValue.toFixed(1) + '%';
                            
                            element.text(newText);
                            element.removeClass('positive negative neutral');
                            if (newValue > 0) {
                                element.addClass('positive');
                            } else if (newValue < 0) {
                                element.addClass('negative');
                            } else {
                                element.addClass('neutral');
                            }
                        }
                    }
                });
            }
            
            // Simulate data freshness updates
            function updateDataFreshness() {
                $('.data-freshness-timestamp').each(function() {
                    var element = $(this);
                    var text = element.text();
                    
                    if (text.includes('seconds ago')) {
                        var seconds = parseInt(text.match(/\\d+/)[0]) + 1;
                        if (seconds >= 60) {
                            element.text('Last updated: 1 minute ago');
                            element.siblings('.data-freshness-header').find('.data-freshness-indicator')
                                .removeClass('fresh').addClass('stale');
                        } else {
                            element.text('Last updated: ' + seconds + ' seconds ago');
                        }
                    } else if (text.includes('minute ago') || text.includes('minutes ago')) {
                        var minutes = parseInt(text.match(/\\d+/)[0]) + 1;
                        element.text('Last updated: ' + minutes + ' minute' + (minutes > 1 ? 's' : '') + ' ago');
                        if (minutes > 10) {
                            element.siblings('.data-freshness-header').find('.data-freshness-indicator')
                                .removeClass('fresh stale').addClass('error');
                            element.text('Update failed - check connection');
                        }
                    }
                });
            }
            
            // Start simulations
            setInterval(updateConnectionStatus, 3000);
            setInterval(updatePerformanceValues, 5000);
            setInterval(updateDataFreshness, 2000);
            
            // Click handlers for status badges
            $('.tradepress-badge').on('click', function() {
                alert('Status: ' + $(this).text().trim());
            });
            
            // Click handlers for health check items
            $('.health-check-item').on('click', function() {
                var label = $(this).find('.health-check-label').text();
                var value = $(this).find('.health-check-value').text();
                alert(label + ': ' + value);
            });
        });
    ";
    
    wp_add_inline_script('jquery', $status_script);
    ?>
</div>