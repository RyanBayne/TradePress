<?php
/**
 * UI Library Controls and Actions Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.6
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Controls & Actions', 'tradepress'); ?></h3>
    <p><?php esc_html_e('UI controls for user interactions, filtering, and action buttons.', 'tradepress'); ?></p>
    
    <div class="controls-showcase">
        <!-- Action Buttons Group -->
        <div class="component-demo">
            <h4><?php esc_html_e('Action Button Groups', 'tradepress'); ?></h4>
            <div class="control-panel">
                <div class="control-panel-header">
                    <h5><?php esc_html_e('Symbol Actions', 'tradepress'); ?></h5>
                </div>
                <div class="control-panel-body">
                    <div class="control-group">
                        <button class="tradepress-control-button tradepress-control-primary">
                            <span class="control-icon dashicons dashicons-chart-line"></span>
                            <span class="control-text"><?php esc_html_e('Analyze', 'tradepress'); ?></span>
                        </button>
                        <button class="tradepress-control-button">
                            <span class="control-icon dashicons dashicons-portfolio"></span>
                            <span class="control-text"><?php esc_html_e('Add to Portfolio', 'tradepress'); ?></span>
                        </button>
                        <button class="tradepress-control-button">
                            <span class="control-icon dashicons dashicons-star-filled"></span>
                            <span class="control-text"><?php esc_html_e('Watchlist', 'tradepress'); ?></span>
                        </button>
                        <button class="tradepress-control-button tradepress-control-danger">
                            <span class="control-icon dashicons dashicons-dismiss"></span>
                            <span class="control-text"><?php esc_html_e('Ignore', 'tradepress'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Toggle Controls -->
        <div class="component-demo">
            <h4><?php esc_html_e('Toggle Controls', 'tradepress'); ?></h4>
            <div class="control-panel">
                <div class="control-panel-header">
                    <h5><?php esc_html_e('View Options', 'tradepress'); ?></h5>
                </div>
                <div class="control-panel-body">
                    <div class="control-toggle-group">
                        <button class="tradepress-toggle-button active">
                            <span class="dashicons dashicons-grid-view"></span>
                            <span class="control-label"><?php esc_html_e('Grid', 'tradepress'); ?></span>
                        </button>
                        <button class="tradepress-toggle-button">
                            <span class="dashicons dashicons-list-view"></span>
                            <span class="control-label"><?php esc_html_e('List', 'tradepress'); ?></span>
                        </button>
                        <button class="tradepress-toggle-button">
                            <span class="dashicons dashicons-table-row-after"></span>
                            <span class="control-label"><?php esc_html_e('Table', 'tradepress'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Bar -->
        <div class="component-demo">
            <h4><?php esc_html_e('Action Bar', 'tradepress'); ?></h4>
            <div class="action-bar">
                <div class="action-bar-left">
                    <button class="tradepress-action-button">
                        <span class="dashicons dashicons-plus"></span>
                        <?php esc_html_e('Add New', 'tradepress'); ?>
                    </button>
                    <button class="tradepress-action-button">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Edit', 'tradepress'); ?>
                    </button>
                </div>
                <div class="action-bar-right">
                    <button class="tradepress-action-button tradepress-action-secondary">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e('Delete', 'tradepress'); ?>
                    </button>
                    <div class="action-dropdown">
                        <button class="tradepress-action-button tradepress-action-dropdown">
                            <?php esc_html_e('More Actions', 'tradepress'); ?>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                        <div class="action-dropdown-content">
                            <a href="#" class="action-dropdown-item"><?php esc_html_e('Export', 'tradepress'); ?></a>
                            <a href="#" class="action-dropdown-item"><?php esc_html_e('Duplicate', 'tradepress'); ?></a>
                            <a href="#" class="action-dropdown-item"><?php esc_html_e('Share', 'tradepress'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Control Panel -->
        <div class="component-demo">
            <h4><?php esc_html_e('Control Panel', 'tradepress'); ?></h4>
            <div class="control-panel control-panel-expanded">
                <div class="control-panel-header">
                    <h5><?php esc_html_e('Trading Settings', 'tradepress'); ?></h5>
                    <div class="control-panel-actions">
                        <button class="tradepress-control-button tradepress-control-small">
                            <span class="dashicons dashicons-admin-generic"></span>
                        </button>
                        <button class="tradepress-control-button tradepress-control-small tradepress-control-toggle">
                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                        </button>
                    </div>
                </div>
                <div class="control-panel-body">
                    <div class="control-row">
                        <label class="control-label"><?php esc_html_e('Execution Mode', 'tradepress'); ?></label>
                        <div class="control-options">
                            <label class="control-radio">
                                <input type="radio" name="execution_mode" checked>
                                <span><?php esc_html_e('Manual', 'tradepress'); ?></span>
                            </label>
                            <label class="control-radio">
                                <input type="radio" name="execution_mode">
                                <span><?php esc_html_e('Semi-Auto', 'tradepress'); ?></span>
                            </label>
                            <label class="control-radio">
                                <input type="radio" name="execution_mode">
                                <span><?php esc_html_e('Automatic', 'tradepress'); ?></span>
                            </label>
                        </div>
                    </div>
                    <div class="control-row">
                        <label class="control-label"><?php esc_html_e('Risk Level', 'tradepress'); ?></label>
                        <div class="control-slider">
                            <input type="range" min="1" max="10" value="5">
                            <span class="control-value">5</span>
                        </div>
                    </div>
                </div>
                <div class="control-panel-footer">
                    <button class="tp-button tp-button-small tp-button-secondary"><?php esc_html_e('Reset', 'tradepress'); ?></button>
                    <button class="tp-button tp-button-small tp-button-primary"><?php esc_html_e('Apply', 'tradepress'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Add inline script for controls functionality
    $controls_script = "
        jQuery(document).ready(function($) {
            // Toggle buttons
            $('.tradepress-toggle-button').on('click', function() {
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
            });
            
            // Action dropdown
            $('.tradepress-action-dropdown').on('click', function(e) {
                e.preventDefault();
                $(this).next('.action-dropdown-content').toggleClass('show');
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.action-dropdown').length) {
                    $('.action-dropdown-content').removeClass('show');
                }
            });
            
            // Control panel toggle
            $('.tradepress-control-toggle').on('click', function() {
                var panel = $(this).closest('.control-panel');
                panel.toggleClass('control-panel-expanded');
                
                // Toggle icon
                var icon = $(this).find('.dashicons');
                if (panel.hasClass('control-panel-expanded')) {
                    icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
                } else {
                    icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                }
            });
            
            // Update slider value display
            $('.control-slider input[type=\"range\"]').on('input', function() {
                $(this).next('.control-value').text($(this).val());
            });
        });
    ";
    
    wp_add_inline_script('jquery', $controls_script);
    ?>

</div>
