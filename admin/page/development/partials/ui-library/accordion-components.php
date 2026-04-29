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
        <!-- Accordion with Table -->
        <div class="component-demo">
            <h4><?php esc_html_e('Accordion with Table', 'tradepress'); ?></h4>
            <div class="tp-accordion-container">
                <div class="tp-accordion-item">
                    <div class="tp-accordion-header">
                        <h4 class="tp-accordion-title"><?php esc_html_e('API Connections', 'tradepress'); ?></h4>
                        <span class="tp-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tp-accordion-content">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Provider', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Status', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Type', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Mode', 'tradepress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Trading 212</strong></td>
                                    <td><span class="tradepress-badge tradepress-badge-success"><?php esc_html_e('Operational', 'tradepress'); ?></span></td>
                                    <td><?php esc_html_e('Trading', 'tradepress'); ?></td>
                                    <td><?php esc_html_e('Paper', 'tradepress'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Alpha Vantage</strong></td>
                                    <td><span class="tradepress-badge tradepress-badge-success"><?php esc_html_e('Operational', 'tradepress'); ?></span></td>
                                    <td><?php esc_html_e('Data Only', 'tradepress'); ?></td>
                                    <td><?php esc_html_e('N/A', 'tradepress'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Finnhub</strong></td>
                                    <td><span class="tradepress-badge tradepress-badge-warning"><?php esc_html_e('Disabled', 'tradepress'); ?></span></td>
                                    <td><?php esc_html_e('Data Only', 'tradepress'); ?></td>
                                    <td><?php esc_html_e('N/A', 'tradepress'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tp-accordion-item">
                    <div class="tp-accordion-header">
                        <h4 class="tp-accordion-title"><?php esc_html_e('Active Positions', 'tradepress'); ?></h4>
                        <span class="tp-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tp-accordion-content">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Symbol', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Quantity', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Entry Price', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('P&amp;L', 'tradepress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>AAPL</strong></td>
                                    <td>10</td>
                                    <td>$172.40</td>
                                    <td><span class="tradepress-badge tradepress-badge-success">+$84.50</span></td>
                                </tr>
                                <tr>
                                    <td><strong>MSFT</strong></td>
                                    <td>5</td>
                                    <td>$418.20</td>
                                    <td><span class="tradepress-badge tradepress-badge-error">&minus;$22.10</span></td>
                                </tr>
                                <tr>
                                    <td><strong>NVDA</strong></td>
                                    <td>3</td>
                                    <td>$875.00</td>
                                    <td><span class="tradepress-badge tradepress-badge-success">+$142.60</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Standard Accordion -->
        <div class="component-demo">
            <h4><?php esc_html_e('Standard Accordion', 'tradepress'); ?></h4>
            <div class="tp-accordion-container">
                <div class="tp-accordion-item">
                    <div class="tp-accordion-header">
                        <h4 class="tp-accordion-title"><?php esc_html_e('Scoring Directives', 'tradepress'); ?></h4>
                        <span class="tp-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tp-accordion-content">
                        <p><?php esc_html_e('Directives evaluate securities based on technical indicators and market conditions to produce a ranked score used for trade selection.', 'tradepress'); ?></p>
                    </div>
                </div>
                <div class="tp-accordion-item">
                    <div class="tp-accordion-header">
                        <h4 class="tp-accordion-title"><?php esc_html_e('Risk Management', 'tradepress'); ?></h4>
                        <span class="tp-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tp-accordion-content">
                        <ul>
                            <li><?php esc_html_e('Position sizing calculators', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Stop-loss and take-profit automation', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Real-time risk monitoring', 'tradepress'); ?></li>
                        </ul>
                    </div>
                </div>
                <div class="tp-accordion-item">
                    <div class="tp-accordion-header">
                        <h4 class="tp-accordion-title"><?php esc_html_e('API Configuration', 'tradepress'); ?></h4>
                        <span class="tp-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tp-accordion-content">
                        <p><?php esc_html_e('Connect trading platforms and data providers via the API Management page. Each provider requires an API key and optional trading mode configuration.', 'tradepress'); ?></p>
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
