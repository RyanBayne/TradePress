<?php
/**
 * Admin View: Automation - Strategies Tab
 * 
 * Displays a table of trading strategies with management options
 * 
 * @package TradePress/Admin
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Initialize search query if provided
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Initialize filter for archived strategies
$show_archived = isset($_GET['show_archived']) ? (bool)$_GET['show_archived'] : false;

// Filter strategies based on search and archive status
$filtered_strategies = array();
foreach ($strategies as $strategy) {
    // Skip archived strategies unless we're explicitly showing them
    if ($strategy['status'] == 'archived' && !$show_archived) {
        continue;
    }
    
    // Apply search filter if search query exists
    if (!empty($search_query)) {
        // Search in name and description
        if (stripos($strategy['name'], $search_query) === false && 
            stripos($strategy['description'], $search_query) === false) {
            continue;
        }
    }
    
    $filtered_strategies[] = $strategy;
}
?>

<div class="wrap strategies-tab-content">
    <!-- Demo Data Notice -->
    <div class="notice notice-info">
        <p>
            <strong><?php esc_html_e('Demo Data', 'tradepress'); ?>:</strong> 
            <?php esc_html_e('These are sample trading strategies for demonstration purposes. Create your own strategies using the "Create Strategies" tab.', 'tradepress'); ?>
        </p>
    </div>

    <div class="strategies-header">
        <div class="strategies-actions">
            <a href="#" class="button button-primary add-new-strategy">
                <?php esc_html_e('Create New Strategy', 'tradepress'); ?>
            </a>
            
            <a href="<?php echo esc_url(add_query_arg(array('tab' => 'strategies', 'show_archived' => $show_archived ? '0' : '1'))); ?>" class="button show-archived-toggle">
                <?php echo $show_archived ? esc_html__('Hide Archived', 'tradepress') : esc_html__('Show Archived', 'tradepress'); ?>
            </a>
        </div>
        
        <div class="strategies-search">
            <form method="get">
                <input type="hidden" name="page" value="tradepress_automation">
                <input type="hidden" name="tab" value="strategies">
                <input type="hidden" name="show_archived" value="<?php echo esc_attr($show_archived ? '1' : '0'); ?>">
                <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Search strategies...', 'tradepress'); ?>">
                <button type="submit" class="button"><?php esc_html_e('Search', 'tradepress'); ?></button>
            </form>
        </div>
    </div>
    
    <?php if (empty($filtered_strategies)): ?>
    <div class="no-strategies-found">
        <p>
            <?php 
            if (!empty($search_query)) {
                esc_html_e('No strategies found matching your search.', 'tradepress');
            } else if ($show_archived) {
                esc_html_e('No archived strategies found.', 'tradepress');
            } else {
                esc_html_e('No active strategies found. Create your first strategy to get started.', 'tradepress');
            }
            ?>
        </p>
    </div>
    <?php else: ?>
    <div class="strategies-table-container">
        <table class="wp-list-table widefat fixed striped strategies-table">
            <thead>
                <tr>
                    <th class="column-name"><?php esc_html_e('Name', 'tradepress'); ?></th>
                    <th class="column-status"><?php esc_html_e('Status', 'tradepress'); ?></th>
                    <th class="column-success"><?php esc_html_e('Success Rate', 'tradepress'); ?></th>
                    <th class="column-trades"><?php esc_html_e('Trades', 'tradepress'); ?></th>
                    <th class="column-profit"><?php esc_html_e('Profit/Loss', 'tradepress'); ?></th>
                    <th class="column-created"><?php esc_html_e('Created', 'tradepress'); ?></th>
                    <th class="column-actions"><?php esc_html_e('Actions', 'tradepress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filtered_strategies as $strategy): ?>
                <tr id="strategy-row-<?php echo esc_attr($strategy['id']); ?>" class="strategy-row <?php echo $strategy['status'] == 'archived' ? 'strategy-archived' : ''; ?>">
                    <td class="column-name">
                        <strong><a href="#" class="view-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>"><?php echo esc_html($strategy['name']); ?></a></strong>
                        <div class="row-actions">
                            <span class="view"><a href="#" class="view-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>"><?php esc_html_e('View', 'tradepress'); ?></a> | </span>
                            <span class="quick-view"><a href="#" class="quick-view-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>"><?php esc_html_e('Quick View', 'tradepress'); ?></a> | </span>
                            <span class="copy"><a href="#" class="copy-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>"><?php esc_html_e('Copy', 'tradepress'); ?></a> | </span>
                            <?php if ($strategy['status'] == 'archived'): ?>
                            <span class="unarchive"><a href="#" class="unarchive-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>"><?php esc_html_e('Unarchive', 'tradepress'); ?></a></span>
                            <?php else: ?>
                            <span class="archive"><a href="#" class="archive-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>"><?php esc_html_e('Archive', 'tradepress'); ?></a></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="column-status">
                        <?php if ($strategy['status'] == 'active'): ?>
                            <span class="strategy-status status-active"><?php esc_html_e('Active', 'tradepress'); ?></span>
                        <?php elseif ($strategy['status'] == 'archived'): ?>
                            <span class="strategy-status status-archived"><?php esc_html_e('Archived', 'tradepress'); ?></span>
                        <?php else: ?>
                            <span class="strategy-status status-<?php echo esc_attr($strategy['status']); ?>"><?php echo esc_html(ucfirst($strategy['status'])); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($strategy['status'] != 'archived'): ?>
                            <div class="status-toggle">
                                <label class="switch">
                                    <input type="checkbox" class="strategy-toggle" data-id="<?php echo esc_attr($strategy['id']); ?>" <?php checked($strategy['status'], 'active'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="column-success">
                        <div class="success-rate">
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo esc_attr($strategy['success_rate']); ?>%;">
                                    <span><?php echo esc_html(number_format($strategy['success_rate'], 1)); ?>%</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="column-trades">
                        <?php echo esc_html($strategy['trades_count']); ?>
                    </td>
                    <td class="column-profit <?php echo $strategy['profit_loss'] >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                        <?php 
                        echo esc_html(sprintf(
                            '%s$%s', 
                            $strategy['profit_loss'] >= 0 ? '+' : '', 
                            number_format($strategy['profit_loss'], 2)
                        )); 
                        ?>
                    </td>
                    <td class="column-created">
                        <?php echo esc_html(date('M j, Y', strtotime($strategy['created']))); ?>
                    </td>
                    <td class="column-actions">
                        <div class="strategy-actions">
                            <button type="button" class="button view-trades" data-id="<?php echo esc_attr($strategy['id']); ?>" title="<?php esc_attr_e('View Trades', 'tradepress'); ?>">
                                <span class="dashicons dashicons-list-view"></span>
                            </button>
                            <button type="button" class="button view-performance" data-id="<?php echo esc_attr($strategy['id']); ?>" title="<?php esc_attr_e('Performance', 'tradepress'); ?>">
                                <span class="dashicons dashicons-chart-line"></span>
                            </button>
                            <button type="button" class="button copy-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>" title="<?php esc_attr_e('Copy Strategy', 'tradepress'); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- Quick View Row (Hidden by default, shown when Quick View is clicked) -->
                <tr id="quick-view-row-<?php echo esc_attr($strategy['id']); ?>" class="quick-view-row" style="display: none;">
                    <td colspan="7">
                        <div class="quick-view-content">
                            <div class="quick-view-header">
                                <h3><?php echo esc_html($strategy['name']); ?> <span class="quick-view-label"><?php esc_html_e('Quick View', 'tradepress'); ?></span></h3>
                                <button type="button" class="close-quick-view" data-id="<?php echo esc_attr($strategy['id']); ?>">×</button>
                            </div>
                            <div class="quick-view-details">
                                <div class="quick-view-section">
                                    <h4><?php esc_html_e('Description', 'tradepress'); ?></h4>
                                    <p><?php echo esc_html($strategy['description']); ?></p>
                                </div>
                                <div class="quick-view-section">
                                    <h4><?php esc_html_e('Performance Summary', 'tradepress'); ?></h4>
                                    <table class="quick-view-stats">
                                        <tr>
                                            <th><?php esc_html_e('Success Rate:', 'tradepress'); ?></th>
                                            <td><?php echo esc_html(number_format($strategy['success_rate'], 1)); ?>%</td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e('Total Trades:', 'tradepress'); ?></th>
                                            <td><?php echo esc_html($strategy['trades_count']); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e('Profit/Loss:', 'tradepress'); ?></th>
                                            <td class="<?php echo $strategy['profit_loss'] >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                                                <?php echo esc_html(sprintf(
                                                    '%s$%s', 
                                                    $strategy['profit_loss'] >= 0 ? '+' : '', 
                                                    number_format($strategy['profit_loss'], 2)
                                                )); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e('Created:', 'tradepress'); ?></th>
                                            <td><?php echo esc_html(date('F j, Y g:i a', strtotime($strategy['created']))); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e('Author:', 'tradepress'); ?></th>
                                            <td><?php echo esc_html($strategy['author']); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="quick-view-actions">
                                <a href="#" class="button view-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>"><?php esc_html_e('View Full Details', 'tradepress'); ?></a>
                                <a href="#" class="button copy-strategy" data-id="<?php echo esc_attr($strategy['id']); ?>"><?php esc_html_e('Copy Strategy', 'tradepress'); ?></a>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="strategies-pagination">
        <!-- Pagination placeholder - will be replaced with real pagination once we have database integration -->
        <span class="displaying-num"><?php printf(esc_html__('%s strategies', 'tradepress'), count($filtered_strategies)); ?></span>
        <span class="pagination-links">
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
            <span class="paging-input">
                <span class="current-page">1</span>
                <span class="tablenav-paging-text"> <?php esc_html_e('of', 'tradepress'); ?> <span class="total-pages">1</span></span>
            </span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
            <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
        </span>
    </div>
    <?php endif; ?>
</div>

<!-- Create/Edit Strategy Modal - will be shown when the user clicks "Create New Strategy" or "Copy Strategy" -->
<div id="strategy-modal" class="strategy-modal" style="display: none;">
    <div class="strategy-modal-content">
        <div class="strategy-modal-header">
            <h3 id="strategy-modal-title"><?php esc_html_e('Create New Strategy', 'tradepress'); ?></h3>
            <button type="button" class="close-strategy-modal">×</button>
        </div>
        <div class="strategy-modal-body">
            <form id="strategy-form" method="post">
                <?php wp_nonce_field('save_strategy', 'strategy_nonce'); ?>
                <input type="hidden" name="action" value="save_strategy">
                <input type="hidden" id="strategy-id" name="strategy_id" value="">
                <input type="hidden" id="strategy-is-copy" name="is_copy" value="0">
                
                <div class="form-row">
                    <label for="strategy-name"><?php esc_html_e('Strategy Name', 'tradepress'); ?> <span class="required">*</span></label>
                    <input type="text" id="strategy-name" name="strategy_name" required>
                </div>
                
                <div class="form-row">
                    <label for="strategy-description"><?php esc_html_e('Description', 'tradepress'); ?> <span class="required">*</span></label>
                    <textarea id="strategy-description" name="strategy_description" rows="3" required></textarea>
                    <p class="description"><?php esc_html_e('Briefly describe the strategy goals and approach.', 'tradepress'); ?></p>
                </div>
                
                <div class="form-row">
                    <h4><?php esc_html_e('Trading Rules', 'tradepress'); ?></h4>
                </div>
                
                <div class="form-row">
                    <label for="strategy-trading-mode"><?php esc_html_e('Trading Mode', 'tradepress'); ?></label>
                    <select id="strategy-trading-mode" name="trading_mode">
                        <option value="paper"><?php esc_html_e('Paper Trading Only', 'tradepress'); ?></option>
                        <option value="live"><?php esc_html_e('Live Trading', 'tradepress'); ?></option>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="strategy-max-funds"><?php esc_html_e('Maximum Funds', 'tradepress'); ?></label>
                    <div class="input-with-prefix">
                        <span class="input-prefix">$</span>
                        <input type="number" id="strategy-max-funds" name="max_funds" min="0" step="1" value="10000">
                    </div>
                    <p class="description"><?php esc_html_e('Maximum amount of capital to use for this strategy.', 'tradepress'); ?></p>
                </div>
                
                <div class="form-row">
                    <label for="strategy-allow-margin"><?php esc_html_e('Allow Margin', 'tradepress'); ?></label>
                    <div class="checkbox-toggle">
                        <input type="checkbox" id="strategy-allow-margin" name="allow_margin">
                        <label for="strategy-allow-margin"></label>
                    </div>
                    <p class="description"><?php esc_html_e('Enable to allow trades using margin.', 'tradepress'); ?></p>
                </div>
                
                <div class="form-row margin-settings" style="display: none;">
                    <label for="strategy-max-margin"><?php esc_html_e('Maximum Margin', 'tradepress'); ?></label>
                    <div class="input-with-suffix">
                        <input type="number" id="strategy-max-margin" name="max_margin" min="0" max="100" step="1" value="50">
                        <span class="input-suffix">%</span>
                    </div>
                    <p class="description"><?php esc_html_e('Maximum percentage of margin to use.', 'tradepress'); ?></p>
                </div>
                
                <div class="form-row">
                    <h4><?php esc_html_e('Entry and Exit Conditions', 'tradepress'); ?></h4>
                </div>
                
                <div class="form-row">
                    <label for="strategy-entry-conditions"><?php esc_html_e('Entry Conditions', 'tradepress'); ?></label>
                    <textarea id="strategy-entry-conditions" name="entry_conditions" rows="3"></textarea>
                    <p class="description"><?php esc_html_e('Describe when the strategy should enter a position.', 'tradepress'); ?></p>
                </div>
                
                <div class="form-row">
                    <label for="strategy-exit-conditions"><?php esc_html_e('Exit Conditions', 'tradepress'); ?></label>
                    <textarea id="strategy-exit-conditions" name="exit_conditions" rows="3"></textarea>
                    <p class="description"><?php esc_html_e('Describe when the strategy should exit a position.', 'tradepress'); ?></p>
                </div>
                
                <div class="form-row">
                    <h4><?php esc_html_e('Risk Management', 'tradepress'); ?></h4>
                </div>
                
                <div class="form-row">
                    <label for="strategy-stop-loss"><?php esc_html_e('Stop Loss', 'tradepress'); ?></label>
                    <div class="input-with-suffix">
                        <input type="number" id="strategy-stop-loss" name="stop_loss" min="0" step="0.1" value="5">
                        <span class="input-suffix">%</span>
                    </div>
                    <p class="description"><?php esc_html_e('Default stop loss percentage.', 'tradepress'); ?></p>
                </div>
                
                <div class="form-row">
                    <label for="strategy-take-profit"><?php esc_html_e('Take Profit', 'tradepress'); ?></label>
                    <div class="input-with-suffix">
                        <input type="number" id="strategy-take-profit" name="take_profit" min="0" step="0.1" value="10">
                        <span class="input-suffix">%</span>
                    </div>
                    <p class="description"><?php esc_html_e('Default take profit percentage.', 'tradepress'); ?></p>
                </div>
            </form>
        </div>
        <div class="strategy-modal-footer">
            <button type="button" class="button" id="cancel-strategy"><?php esc_html_e('Cancel', 'tradepress'); ?></button>
            <button type="button" class="button button-primary" id="save-strategy"><?php esc_html_e('Create Strategy', 'tradepress'); ?></button>
        </div>
    </div>
</div>

<!-- View Strategy Details Modal -->
<div id="strategy-details-modal" class="strategy-modal" style="display: none;">
    <div class="strategy-modal-content strategy-details-content">
        <div class="strategy-modal-header">
            <h3 id="strategy-details-title"><?php esc_html_e('Strategy Details', 'tradepress'); ?></h3>
            <button type="button" class="close-strategy-details">×</button>
        </div>
        <div class="strategy-modal-body strategy-details-body">
            <div class="strategy-details-loading">
                <span class="spinner is-active"></span>
                <p><?php esc_html_e('Loading strategy details...', 'tradepress'); ?></p>
            </div>
            
            <div class="strategy-details-content" style="display: none;">
                <!-- This will be populated with the strategy details via AJAX -->
            </div>
        </div>
        <div class="strategy-modal-footer">
            <button type="button" class="button close-strategy-details"><?php esc_html_e('Close', 'tradepress'); ?></button>
            <button type="button" class="button button-primary copy-viewed-strategy"><?php esc_html_e('Copy Strategy', 'tradepress'); ?></button>
        </div>
    </div>
</div>