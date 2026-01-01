<?php
/**
 * UI Library Chart Visualization Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.0
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Chart Visualization', 'tradepress'); ?></h3>
    <p><?php esc_html_e('Chart components for financial data visualization, trading patterns, and performance monitoring using existing TradePress styles.', 'tradepress'); ?></p>
    
    <div class="tradepress-component-group">
        <!-- Candlestick Chart Placeholder -->
        <div class="component-demo">
            <h4><?php esc_html_e('Candlestick Chart', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><?php esc_html_e('AAPL - Apple Inc.', 'tradepress'); ?></h5>
                        <div class="chart-controls">
                            <select class="chart-period-select">
                                <option value="1d"><?php esc_html_e('1D', 'tradepress'); ?></option>
                                <option value="1w" selected><?php esc_html_e('1W', 'tradepress'); ?></option>
                                <option value="1m"><?php esc_html_e('1M', 'tradepress'); ?></option>
                                <option value="3m"><?php esc_html_e('3M', 'tradepress'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-card-body">
                        <div class="candlestick-chart-placeholder">
                            <div class="candlestick-placeholder-bars">
                                <div class="candlestick-placeholder-bar bullish" style="height: 60%;"></div>
                                <div class="candlestick-placeholder-bar bearish" style="height: 45%;"></div>
                                <div class="candlestick-placeholder-bar bullish" style="height: 70%;"></div>
                                <div class="candlestick-placeholder-bar bullish" style="height: 80%;"></div>
                                <div class="candlestick-placeholder-bar bearish" style="height: 55%;"></div>
                                <div class="candlestick-placeholder-bar bullish" style="height: 75%;"></div>
                                <div class="candlestick-placeholder-bar bearish" style="height: 50%;"></div>
                                <div class="candlestick-placeholder-bar bullish" style="height: 85%;"></div>
                            </div>
                            <div class="chart-placeholder-text">
                                <span class="dashicons dashicons-chart-line"></span>
                                <?php esc_html_e('Interactive candlestick chart would be rendered here', 'tradepress'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="chart-card-footer">
                        <div class="chart-metrics">
                            <div class="chart-metric">
                                <span class="metric-label"><?php esc_html_e('Open', 'tradepress'); ?></span>
                                <span class="metric-value">$168.22</span>
                            </div>
                            <div class="chart-metric">
                                <span class="metric-label"><?php esc_html_e('High', 'tradepress'); ?></span>
                                <span class="metric-value positive">$172.45</span>
                            </div>
                            <div class="chart-metric">
                                <span class="metric-label"><?php esc_html_e('Low', 'tradepress'); ?></span>
                                <span class="metric-value negative">$166.80</span>
                            </div>
                            <div class="chart-metric">
                                <span class="metric-label"><?php esc_html_e('Close', 'tradepress'); ?></span>
                                <span class="metric-value">$170.15</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Line Chart Performance -->
        <div class="component-demo">
            <h4><?php esc_html_e('Performance Line Chart', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><?php esc_html_e('Portfolio Performance', 'tradepress'); ?></h5>
                        <div class="chart-toolbar">
                            <button class="chart-tool-button active" title="<?php esc_attr_e('Line Chart', 'tradepress'); ?>">
                                <span class="dashicons dashicons-chart-line"></span>
                            </button>
                            <button class="chart-tool-button" title="<?php esc_attr_e('Area Chart', 'tradepress'); ?>">
                                <span class="dashicons dashicons-chart-area"></span>
                            </button>
                            <select class="chart-timeframe-selector">
                                <option value="ytd"><?php esc_html_e('YTD', 'tradepress'); ?></option>
                                <option value="1y" selected><?php esc_html_e('1Y', 'tradepress'); ?></option>
                                <option value="5y"><?php esc_html_e('5Y', 'tradepress'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-card-body">
                        <div class="line-chart-placeholder">
                            <svg class="line-chart-svg" width="100%" height="200">
                                <defs>
                                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#00ba37;stop-opacity:0.3" />
                                        <stop offset="100%" style="stop-color:#00ba37;stop-opacity:0" />
                                    </linearGradient>
                                </defs>
                                <path d="M 20 160 Q 80 120 140 100 T 260 80 T 380 60 T 500 40" 
                                      stroke="#00ba37" stroke-width="2" fill="none"/>
                                <path d="M 20 160 Q 80 120 140 100 T 260 80 T 380 60 T 500 40 L 500 180 L 20 180 Z" 
                                      fill="url(#lineGradient)"/>
                                <circle cx="500" cy="40" r="4" fill="#00ba37"/>
                            </svg>
                            <div class="chart-placeholder-overlay">
                                <span class="dashicons dashicons-chart-line"></span>
                                <?php esc_html_e('Interactive line chart would be rendered here', 'tradepress'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="chart-card-footer">
                        <div class="chart-legend">
                            <div class="chart-legend-item">
                                <span class="legend-color" style="background-color: #00ba37;"></span>
                                <span class="legend-text"><?php esc_html_e('Portfolio Value', 'tradepress'); ?></span>
                            </div>
                            <div class="chart-legend-item">
                                <span class="legend-color" style="background-color: #2271b1;"></span>
                                <span class="legend-text"><?php esc_html_e('Benchmark (S&P 500)', 'tradepress'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bar Chart Comparison -->
        <div class="component-demo">
            <h4><?php esc_html_e('Sector Performance Bar Chart', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><?php esc_html_e('Sector Performance (YTD)', 'tradepress'); ?></h5>
                    </div>
                    <div class="chart-card-body">
                        <div class="bar-chart-placeholder">
                            <div class="bar-chart-grid">
                                <div class="bar-chart-column">
                                    <div class="bar-chart-bar positive" style="height: 85%;" data-value="24.5%"></div>
                                    <div class="bar-chart-label"><?php esc_html_e('Technology', 'tradepress'); ?></div>
                                    <div class="bar-chart-value positive">+24.5%</div>
                                </div>
                                <div class="bar-chart-column">
                                    <div class="bar-chart-bar positive" style="height: 70%;" data-value="18.2%"></div>
                                    <div class="bar-chart-label"><?php esc_html_e('Healthcare', 'tradepress'); ?></div>
                                    <div class="bar-chart-value positive">+18.2%</div>
                                </div>
                                <div class="bar-chart-column">
                                    <div class="bar-chart-bar positive" style="height: 55%;" data-value="12.7%"></div>
                                    <div class="bar-chart-label"><?php esc_html_e('Finance', 'tradepress'); ?></div>
                                    <div class="bar-chart-value positive">+12.7%</div>
                                </div>
                                <div class="bar-chart-column">
                                    <div class="bar-chart-bar positive" style="height: 45%;" data-value="8.9%"></div>
                                    <div class="bar-chart-label"><?php esc_html_e('Consumer', 'tradepress'); ?></div>
                                    <div class="bar-chart-value positive">+8.9%</div>
                                </div>
                                <div class="bar-chart-column">
                                    <div class="bar-chart-bar negative" style="height: 20%;" data-value="-3.2%"></div>
                                    <div class="bar-chart-label"><?php esc_html_e('Energy', 'tradepress'); ?></div>
                                    <div class="bar-chart-value negative">-3.2%</div>
                                </div>
                                <div class="bar-chart-column">
                                    <div class="bar-chart-bar negative" style="height: 35%;" data-value="-8.1%"></div>
                                    <div class="bar-chart-label"><?php esc_html_e('Real Estate', 'tradepress'); ?></div>
                                    <div class="bar-chart-value negative">-8.1%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pie Chart Asset Allocation -->
        <div class="component-demo">
            <h4><?php esc_html_e('Asset Allocation Pie Chart', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><?php esc_html_e('Portfolio Allocation', 'tradepress'); ?></h5>
                    </div>
                    <div class="chart-card-body">
                        <div class="pie-chart-container">
                            <div class="pie-chart">
                                <svg width="200" height="200" viewBox="0 0 200 200">
                                    <circle cx="100" cy="100" r="80" fill="#2271b1" stroke="#fff" stroke-width="2"/>
                                    <circle cx="100" cy="100" r="80" fill="#00ba37" stroke="#fff" stroke-width="2"
                                            stroke-dasharray="125.66 377" stroke-dashoffset="0" transform="rotate(-90 100 100)"/>
                                    <circle cx="100" cy="100" r="80" fill="#dba617" stroke="#fff" stroke-width="2"
                                            stroke-dasharray="87.96 377" stroke-dashoffset="-125.66" transform="rotate(-90 100 100)"/>
                                    <circle cx="100" cy="100" r="80" fill="#d63638" stroke="#fff" stroke-width="2"
                                            stroke-dasharray="62.83 377" stroke-dashoffset="-213.62" transform="rotate(-90 100 100)"/>
                                    <circle cx="100" cy="100" r="30" fill="#fff"/>
                                </svg>
                                <div class="pie-chart-center">
                                    <div class="pie-chart-value">$124.5K</div>
                                    <div class="pie-chart-label"><?php esc_html_e('Total', 'tradepress'); ?></div>
                                </div>
                            </div>
                            <div class="pie-chart-legend">
                                <div class="pie-legend-item">
                                    <span class="pie-legend-color" style="background-color: #2271b1;"></span>
                                    <span class="pie-legend-text"><?php esc_html_e('Stocks (45%)', 'tradepress'); ?></span>
                                </div>
                                <div class="pie-legend-item">
                                    <span class="pie-legend-color" style="background-color: #00ba37;"></span>
                                    <span class="pie-legend-text"><?php esc_html_e('Bonds (25%)', 'tradepress'); ?></span>
                                </div>
                                <div class="pie-legend-item">
                                    <span class="pie-legend-color" style="background-color: #dba617;"></span>
                                    <span class="pie-legend-text"><?php esc_html_e('ETFs (18%)', 'tradepress'); ?></span>
                                </div>
                                <div class="pie-legend-item">
                                    <span class="pie-legend-color" style="background-color: #d63638;"></span>
                                    <span class="pie-legend-text"><?php esc_html_e('Cash (12%)', 'tradepress'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Heatmap Visualization -->
        <div class="component-demo">
            <h4><?php esc_html_e('Stock Performance Heatmap', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="heatmap-container">
                    <div class="heatmap-header">
                        <h5><?php esc_html_e('Top Holdings Performance', 'tradepress'); ?></h5>
                    </div>
                    <div class="heatmap-body">
                        <div class="heatmap-grid">
                            <div class="heatmap-cell" style="background-color: rgba(0, 186, 55, 0.8);">
                                <div class="heatmap-cell-title">AAPL</div>
                                <div class="heatmap-cell-value positive">+5.2%</div>
                            </div>
                            <div class="heatmap-cell" style="background-color: rgba(0, 186, 55, 0.6);">
                                <div class="heatmap-cell-title">MSFT</div>
                                <div class="heatmap-cell-value positive">+3.1%</div>
                            </div>
                            <div class="heatmap-cell" style="background-color: rgba(0, 186, 55, 0.4);">
                                <div class="heatmap-cell-title">GOOGL</div>
                                <div class="heatmap-cell-value positive">+1.8%</div>
                            </div>
                            <div class="heatmap-cell" style="background-color: rgba(214, 54, 56, 0.4);">
                                <div class="heatmap-cell-title">TSLA</div>
                                <div class="heatmap-cell-value negative">-2.1%</div>
                            </div>
                            <div class="heatmap-cell" style="background-color: rgba(102, 102, 102, 0.3);">
                                <div class="heatmap-cell-title">NVDA</div>
                                <div class="heatmap-cell-value neutral">+0.3%</div>
                            </div>
                            <div class="heatmap-cell" style="background-color: rgba(0, 186, 55, 0.5);">
                                <div class="heatmap-cell-title">AMZN</div>
                                <div class="heatmap-cell-value positive">+2.7%</div>
                            </div>
                            <div class="heatmap-cell" style="background-color: rgba(214, 54, 56, 0.6);">
                                <div class="heatmap-cell-title">META</div>
                                <div class="heatmap-cell-value negative">-3.5%</div>
                            </div>
                            <div class="heatmap-cell" style="background-color: rgba(0, 186, 55, 0.3);">
                                <div class="heatmap-cell-title">NFLX</div>
                                <div class="heatmap-cell-value positive">+1.2%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Technical Indicators Dashboard -->
        <div class="component-demo">
            <h4><?php esc_html_e('Technical Indicators Dashboard', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="tradepress-grid tradepress-grid-2-cols">
                    <div class="indicator-card">
                        <div class="indicator-header">
                            <h5><?php esc_html_e('RSI (14)', 'tradepress'); ?></h5>
                            <span class="indicator-value positive">65.2</span>
                        </div>
                        <div class="indicator-body">
                            <div class="indicator-description">
                                <p><?php esc_html_e('Relative Strength Index indicates momentum', 'tradepress'); ?></p>
                            </div>
                            <div class="media-progress-bar">
                                <div style="width: 65.2%;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="indicator-card">
                        <div class="indicator-header">
                            <h5><?php esc_html_e('MACD', 'tradepress'); ?></h5>
                            <span class="indicator-value positive">+0.24</span>
                        </div>
                        <div class="indicator-body">
                            <div class="indicator-description">
                                <p><?php esc_html_e('Moving Average Convergence Divergence', 'tradepress'); ?></p>
                            </div>
                            <div class="indicator-interpretation">
                                <h6><?php esc_html_e('Signal:', 'tradepress'); ?></h6>
                                <ul>
                                    <li class="bullish"><?php esc_html_e('Bullish crossover detected', 'tradepress'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="indicator-card">
                        <div class="indicator-header">
                            <h5><?php esc_html_e('Bollinger Bands', 'tradepress'); ?></h5>
                            <span class="indicator-value warning">Near Upper</span>
                        </div>
                        <div class="indicator-body">
                            <div class="indicator-description">
                                <p><?php esc_html_e('Price approaching upper resistance band', 'tradepress'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="indicator-card">
                        <div class="indicator-header">
                            <h5><?php esc_html_e('Volume MA', 'tradepress'); ?></h5>
                            <span class="indicator-value positive">Above Avg</span>
                        </div>
                        <div class="indicator-body">
                            <div class="indicator-description">
                                <p><?php esc_html_e('Trading volume above 20-day average', 'tradepress'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Add interactive demo script
    $chart_script = "
        jQuery(document).ready(function($) {
            // Chart period selector
            $('.chart-period-select').on('change', function() {
                var period = $(this).val();
                var chartContainer = $(this).closest('.chart-card');
                chartContainer.addClass('chart-loading');
                
                setTimeout(function() {
                    chartContainer.removeClass('chart-loading');
                    // Simulate data update
                    alert('Chart updated for period: ' + period);
                }, 1000);
            });
            
            // Chart toolbar buttons
            $('.chart-tool-button').on('click', function() {
                $(this).siblings('.chart-tool-button').removeClass('active');
                $(this).addClass('active');
            });
            
            // Heatmap cell hover effects
            $('.heatmap-cell').on('mouseenter', function() {
                $(this).css('transform', 'scale(1.05)');
            }).on('mouseleave', function() {
                $(this).css('transform', 'scale(1)');
            });
            
            // Bar chart hover effects
            $('.bar-chart-column').on('mouseenter', function() {
                var value = $(this).find('.bar-chart-bar').data('value');
                if (value) {
                    $(this).append('<div class=\"bar-tooltip\">' + value + '</div>');
                }
            }).on('mouseleave', function() {
                $(this).find('.bar-tooltip').remove();
            });
            
            // Animate progress bars
            function animateIndicators() {
                $('.media-progress-bar div').each(function() {
                    var width = $(this).css('width');
                    $(this).css('width', '0%').animate({
                        width: width
                    }, 1500);
                });
            }
            
            // Initial animation
            animateIndicators();
            
            // Re-animate every 5 seconds
            setInterval(animateIndicators, 5000);
        });
    ";
    
    wp_add_inline_script('jquery', $chart_script);
    ?>
</div>