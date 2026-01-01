<?php
/**
 * UI Library Accordion Components Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.0
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Accordion Components', 'tradepress'); ?></h3>
    <p><?php esc_html_e('Collapsible content panels, expandable sections, tree-view components, and FAQ-style accordions for organizing information.', 'tradepress'); ?></p>
    
    <div class="tradepress-component-group">
        <!-- Basic Accordion -->
        <div class="component-demo">
            <h4><?php esc_html_e('Basic Accordion', 'tradepress'); ?></h4>
            <div class="tradepress-accordion">
                <div class="tradepress-accordion-item">
                    <div class="tradepress-accordion-header">
                        <h4><?php esc_html_e('Trading Strategy Overview', 'tradepress'); ?></h4>
                        <span class="tradepress-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tradepress-accordion-content">
                        <p><?php esc_html_e('This section contains detailed information about the current trading strategy, including entry and exit criteria, risk management parameters, and performance metrics.', 'tradepress'); ?></p>
                        <div class="tradepress-grid tradepress-grid-2">
                            <div class="tradepress-card">
                                <h5><?php esc_html_e('Entry Criteria', 'tradepress'); ?></h5>
                                <ul>
                                    <li><?php esc_html_e('Moving average crossover', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('Volume confirmation', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('RSI below 30', 'tradepress'); ?></li>
                                </ul>
                            </div>
                            <div class="tradepress-card">
                                <h5><?php esc_html_e('Exit Criteria', 'tradepress'); ?></h5>
                                <ul>
                                    <li><?php esc_html_e('10% profit target', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('5% stop loss', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('Trailing stop at 7%', 'tradepress'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tradepress-accordion-item">
                    <div class="tradepress-accordion-header">
                        <h4><?php esc_html_e('Market Analysis', 'tradepress'); ?></h4>
                        <span class="tradepress-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tradepress-accordion-content">
                        <p><?php esc_html_e('Current market conditions and technical analysis indicators for informed trading decisions.', 'tradepress'); ?></p>
                        <div class="tradepress-table-container">
                            <table class="tradepress-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Indicator', 'tradepress'); ?></th>
                                        <th><?php esc_html_e('Value', 'tradepress'); ?></th>
                                        <th><?php esc_html_e('Signal', 'tradepress'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php esc_html_e('RSI', 'tradepress'); ?></td>
                                        <td>42.5</td>
                                        <td><span class="tradepress-badge tradepress-badge-warning"><?php esc_html_e('Neutral', 'tradepress'); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e('MACD', 'tradepress'); ?></td>
                                        <td>0.75</td>
                                        <td><span class="tradepress-badge tradepress-badge-success"><?php esc_html_e('Bullish', 'tradepress'); ?></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="tradepress-accordion-item">
                    <div class="tradepress-accordion-header">
                        <h4><?php esc_html_e('Portfolio Performance', 'tradepress'); ?></h4>
                        <span class="tradepress-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tradepress-accordion-content">
                        <p><?php esc_html_e('Detailed portfolio metrics and performance analytics.', 'tradepress'); ?></p>
                        <div class="media-progress-bar">
                            <div style="width: 75%;"><?php esc_html_e('75% Target Achievement', 'tradepress'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Style Accordion -->
        <div class="component-demo">
            <h4><?php esc_html_e('FAQ Style Accordion', 'tradepress'); ?></h4>
            <div class="tradepress-accordion">
                <div class="tradepress-accordion-item">
                    <div class="tradepress-accordion-header">
                        <h4><?php esc_html_e('How do I set up automated trading?', 'tradepress'); ?></h4>
                        <span class="tradepress-accordion-icon dashicons dashicons-plus"></span>
                    </div>
                    <div class="tradepress-accordion-content">
                        <p><?php esc_html_e('To set up automated trading, follow these steps:', 'tradepress'); ?></p>
                        <ol>
                            <li><?php esc_html_e('Navigate to the Trading page', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Configure your API settings', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Set up your trading strategies', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Enable automated execution', 'tradepress'); ?></li>
                        </ol>
                    </div>
                </div>
                
                <div class="tradepress-accordion-item">
                    <div class="tradepress-accordion-header">
                        <h4><?php esc_html_e('What are scoring directives?', 'tradepress'); ?></h4>
                        <span class="tradepress-accordion-icon dashicons dashicons-plus"></span>
                    </div>
                    <div class="tradepress-accordion-content">
                        <p><?php esc_html_e('Scoring directives are rules that evaluate securities based on various criteria including technical indicators, fundamental analysis, and market sentiment.', 'tradepress'); ?></p>
                        <div class="tradepress-notice tradepress-notice-info">
                            <p><?php esc_html_e('Each directive contributes to an overall score that helps prioritize trading opportunities.', 'tradepress'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="tradepress-accordion-item">
                    <div class="tradepress-accordion-header">
                        <h4><?php esc_html_e('How do I manage risk in my trades?', 'tradepress'); ?></h4>
                        <span class="tradepress-accordion-icon dashicons dashicons-plus"></span>
                    </div>
                    <div class="tradepress-accordion-content">
                        <p><?php esc_html_e('Risk management is crucial for successful trading. TradePress offers several tools:', 'tradepress'); ?></p>
                        <ul>
                            <li><?php esc_html_e('Position sizing calculators', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Stop-loss and take-profit automation', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Portfolio diversification analysis', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Real-time risk monitoring', 'tradepress'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tree View Accordion -->
        <div class="component-demo">
            <h4><?php esc_html_e('Tree View Component', 'tradepress'); ?></h4>
            <div class="tradepress-tree-view">
                <div class="tradepress-tree-item">
                    <div class="tradepress-tree-header tradepress-tree-expanded">
                        <span class="tradepress-tree-toggle dashicons dashicons-arrow-down"></span>
                        <span class="tradepress-tree-icon dashicons dashicons-portfolio"></span>
                        <span class="tradepress-tree-label"><?php esc_html_e('My Portfolio', 'tradepress'); ?></h4>
                        <span class="tradepress-tree-badge">12</span>
                    </div>
                    <div class="tradepress-tree-children">
                        <div class="tradepress-tree-item">
                            <div class="tradepress-tree-header tradepress-tree-expanded">
                                <span class="tradepress-tree-toggle dashicons dashicons-arrow-down"></span>
                                <span class="tradepress-tree-icon dashicons dashicons-chart-line"></span>
                                <span class="tradepress-tree-label"><?php esc_html_e('Technology Stocks', 'tradepress'); ?></h4>
                                <span class="tradepress-tree-badge">5</span>
                            </div>
                            <div class="tradepress-tree-children">
                                <div class="tradepress-tree-item tradepress-tree-leaf">
                                    <div class="tradepress-tree-header">
                                        <span class="tradepress-tree-icon dashicons dashicons-admin-network"></span>
                                        <span class="tradepress-tree-label">AAPL</span>
                                        <span class="tradepress-tree-value">$150.25</span>
                                        <span class="tradepress-tree-change positive">+2.5%</span>
                                    </div>
                                </div>
                                <div class="tradepress-tree-item tradepress-tree-leaf">
                                    <div class="tradepress-tree-header">
                                        <span class="tradepress-tree-icon dashicons dashicons-admin-network"></span>
                                        <span class="tradepress-tree-label">MSFT</span>
                                        <span class="tradepress-tree-value">$285.75</span>
                                        <span class="tradepress-tree-change positive">+1.8%</span>
                                    </div>
                                </div>
                                <div class="tradepress-tree-item tradepress-tree-leaf">
                                    <div class="tradepress-tree-header">
                                        <span class="tradepress-tree-icon dashicons dashicons-admin-network"></span>
                                        <span class="tradepress-tree-label">GOOGL</span>
                                        <span class="tradepress-tree-value">$2,450.50</span>
                                        <span class="tradepress-tree-change negative">-0.5%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tradepress-tree-item">
                            <div class="tradepress-tree-header">
                                <span class="tradepress-tree-toggle dashicons dashicons-arrow-right"></span>
                                <span class="tradepress-tree-icon dashicons dashicons-money-alt"></span>
                                <span class="tradepress-tree-label"><?php esc_html_e('Financial Stocks', 'tradepress'); ?></h4>
                                <span class="tradepress-tree-badge">4</span>
                            </div>
                        </div>
                        
                        <div class="tradepress-tree-item">
                            <div class="tradepress-tree-header">
                                <span class="tradepress-tree-toggle dashicons dashicons-arrow-right"></span>
                                <span class="tradepress-tree-icon dashicons dashicons-heart"></span>
                                <span class="tradepress-tree-label"><?php esc_html_e('Healthcare Stocks', 'tradepress'); ?></h4>
                                <span class="tradepress-tree-badge">3</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tradepress-tree-item">
                    <div class="tradepress-tree-header">
                        <span class="tradepress-tree-toggle dashicons dashicons-arrow-right"></span>
                        <span class="tradepress-tree-icon dashicons dashicons-star-filled"></span>
                        <span class="tradepress-tree-label"><?php esc_html_e('Watchlist', 'tradepress'); ?></h4>
                        <span class="tradepress-tree-badge">8</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Nested Accordion -->
        <div class="component-demo">
            <h4><?php esc_html_e('Nested Accordion', 'tradepress'); ?></h4>
            <div class="tradepress-accordion tradepress-accordion-nested">
                <div class="tradepress-accordion-item">
                    <div class="tradepress-accordion-header">
                        <h4><?php esc_html_e('Trading Strategies', 'tradepress'); ?></h4>
                        <span class="tradepress-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tradepress-accordion-content">
                        <div class="tradepress-accordion tradepress-accordion-nested-child">
                            <div class="tradepress-accordion-item">
                                <div class="tradepress-accordion-header">
                                    <h5><?php esc_html_e('Day Trading Strategies', 'tradepress'); ?></h5>
                                    <span class="tradepress-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                                </div>
                                <div class="tradepress-accordion-content">
                                    <p><?php esc_html_e('Short-term trading strategies for intraday opportunities.', 'tradepress'); ?></p>
                                    <ul>
                                        <li><?php esc_html_e('Scalping', 'tradepress'); ?></li>
                                        <li><?php esc_html_e('Momentum trading', 'tradepress'); ?></li>
                                        <li><?php esc_html_e('Range trading', 'tradepress'); ?></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="tradepress-accordion-item">
                                <div class="tradepress-accordion-header">
                                    <h5><?php esc_html_e('Swing Trading Strategies', 'tradepress'); ?></h5>
                                    <span class="tradepress-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                                </div>
                                <div class="tradepress-accordion-content">
                                    <p><?php esc_html_e('Medium-term strategies for capturing multi-day moves.', 'tradepress'); ?></p>
                                    <div class="tradepress-notice tradepress-notice-warning">
                                        <p><?php esc_html_e('Swing trading requires patience and proper risk management.', 'tradepress'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Add interactive functionality
    $accordion_script = "
        jQuery(document).ready(function($) {
            // Basic accordion functionality
            $('.tradepress-accordion-header').on('click', function() {
                var \$item = $(this).closest('.tradepress-accordion-item');
                var \$content = \$item.find('.tradepress-accordion-content').first();
                var \$icon = $(this).find('.tradepress-accordion-icon');
                
                // Toggle current item
                \$content.slideToggle(300);
                \$item.toggleClass('tradepress-accordion-expanded');
                
                // Toggle icon for basic accordion
                if (\$item.closest('.tradepress-accordion').hasClass('tradepress-accordion-faq')) {
                    \$icon.toggleClass('dashicons-plus dashicons-minus');
                } else {
                    \$icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
                }
                
                // For non-nested accordions, close other items
                if (!\$item.closest('.tradepress-accordion').hasClass('tradepress-accordion-nested')) {
                    \$item.siblings('.tradepress-accordion-item').each(function() {
                        var \$siblingContent = $(this).find('.tradepress-accordion-content').first();
                        var \$siblingIcon = $(this).find('.tradepress-accordion-icon').first();
                        
                        if (\$siblingContent.is(':visible')) {
                            \$siblingContent.slideUp(300);
                            $(this).removeClass('tradepress-accordion-expanded');
                            
                            if ($(this).closest('.tradepress-accordion').hasClass('tradepress-accordion-faq')) {
                                \$siblingIcon.removeClass('dashicons-minus').addClass('dashicons-plus');
                            } else {
                                \$siblingIcon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                            }
                        }
                    });
                }
            });
            
            // Tree view functionality
            $('.tradepress-tree-toggle').on('click', function(e) {
                e.stopPropagation();
                var \$header = $(this).closest('.tradepress-tree-header');
                var \$item = \$header.closest('.tradepress-tree-item');
                var \$children = \$item.find('.tradepress-tree-children').first();
                
                if (\$children.length > 0) {
                    \$children.slideToggle(200);
                    \$header.toggleClass('tradepress-tree-expanded');
                    $(this).toggleClass('dashicons-arrow-right dashicons-arrow-down');
                }
            });
            
            // Tree item selection
            $('.tradepress-tree-header').on('click', function() {
                $('.tradepress-tree-header').removeClass('tradepress-tree-selected');
                $(this).addClass('tradepress-tree-selected');
            });
        });
    ";
    
    wp_add_inline_script('jquery', $accordion_script);
    ?>
</div>
