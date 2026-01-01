<?php
/**
 * UI Library Modal Components Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.6
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Modal Components', 'tradepress'); ?></h3>
    <p><?php esc_html_e('Dialog boxes or pop-up windows that are displayed on top of the current page.', 'tradepress'); ?></p>

    <div class="tradepress-component-group">
        <!-- Basic Modal Demo -->
        <div class="component-demo">
            <h4><?php esc_html_e('Basic Modal', 'tradepress'); ?></h4>
            <button class="tp-button tp-button-primary" id="open-demo-modal"><?php esc_html_e('Open Modal', 'tradepress'); ?></button>

            <!-- Modal Structure (hidden by default) -->
            <div id="ui-library-demo-modal" class="tradepress-modal" style="display:none;">
                <div class="tradepress-modal-content">
                    <div class="tradepress-modal-header">
                        <h2><?php esc_html_e('Sample Modal Title', 'tradepress'); ?></h2>
                        <button class="tradepress-modal-close" aria-label="<?php esc_attr_e('Close modal', 'tradepress'); ?>">&times;</button>
                    </div>
                    <div class="tradepress-modal-body">
                        <p><?php esc_html_e('This is the content of the modal. You can put any HTML here, including forms, text, or other components.', 'tradepress'); ?></p>
                        <p><?php esc_html_e('Modal dialogs are useful for displaying additional information, forms, or confirmation messages without navigating away from the current page.', 'tradepress'); ?></p>
                    </div>
                    <div class="tradepress-modal-footer">
                        <button class="tp-button tp-button-secondary close-demo-modal"><?php esc_html_e('Cancel', 'tradepress'); ?></button>
                        <button class="tp-button tp-button-primary"><?php esc_html_e('Save Changes', 'tradepress'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Detail Modal Demo -->
        <div class="component-demo">
            <h4><?php esc_html_e('Task Detail Modal', 'tradepress'); ?></h4>
            <button class="tp-button tp-button-secondary" id="open-task-modal"><?php esc_html_e('View Task Details', 'tradepress'); ?></button>

            <!-- Task Detail Modal Structure -->
            <div id="ui-library-task-modal" class="tradepress-modal" style="display:none;">
                <div class="tradepress-modal-content">
                    <div class="tradepress-modal-header">
                        <h2><?php esc_html_e('Task Details', 'tradepress'); ?></h2>
                        <button class="tradepress-modal-close" aria-label="<?php esc_attr_e('Close modal', 'tradepress'); ?>">&times;</button>
                    </div>
                    <div class="tradepress-modal-body">
                        <div class="tradepress-task-detail-header">
                            <h3 class="tradepress-task-detail-title"><?php esc_html_e('Analyze AAPL Stock Performance', 'tradepress'); ?></h3>
                        </div>
                        
                        <div class="tradepress-task-detail-meta">
                            <div class="tradepress-task-detail-meta-item">
                                <span class="tradepress-task-detail-meta-label"><?php esc_html_e('Status:', 'tradepress'); ?></span>
                                <span class="status-active"><?php esc_html_e('Active', 'tradepress'); ?></span>
                            </div>
                            <div class="tradepress-task-detail-meta-item">
                                <span class="tradepress-task-detail-meta-label"><?php esc_html_e('Priority:', 'tradepress'); ?></span>
                                <span class="priority-high"><?php esc_html_e('High', 'tradepress'); ?></span>
                            </div>
                            <div class="tradepress-task-detail-meta-item">
                                <span class="tradepress-task-detail-meta-label"><?php esc_html_e('Created:', 'tradepress'); ?></span>
                                <span><?php echo esc_html(date('Y-m-d H:i')); ?></span>
                            </div>
                        </div>

                        <div class="tradepress-task-description">
                            <h4><?php esc_html_e('Description', 'tradepress'); ?></h4>
                            <p><?php esc_html_e('Complete technical analysis of Apple Inc. (AAPL) stock performance over the last quarter. Include price movements, volume analysis, and comparison with sector averages.', 'tradepress'); ?></p>
                        </div>

                        <div class="tradepress-task-attachments">
                            <h4><?php esc_html_e('Attachments', 'tradepress'); ?></h4>
                            <ul>
                                <li><span class="dashicons dashicons-media-spreadsheet"></span> AAPL_Q3_Data.xlsx</li>
                                <li><span class="dashicons dashicons-chart-line"></span> Technical_Indicators.pdf</li>
                            </ul>
                        </div>
                    </div>
                    <div class="tradepress-modal-footer">
                        <button class="tp-button tp-button-secondary close-task-modal"><?php esc_html_e('Close', 'tradepress'); ?></button>
                        <button class="tp-button tp-button-primary"><?php esc_html_e('Edit Task', 'tradepress'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Modal Demo -->
        <div class="component-demo">
            <h4><?php esc_html_e('Loading Modal', 'tradepress'); ?></h4>
            <button class="tp-button tp-button-secondary" id="open-loading-modal"><?php esc_html_e('Show Loading', 'tradepress'); ?></button>

            <!-- Loading Modal Structure -->
            <div id="ui-library-loading-modal" class="tradepress-modal" style="display:none;">
                <div class="tradepress-modal-content">
                    <div class="tradepress-modal-header">
                        <h2><?php esc_html_e('Processing Request', 'tradepress'); ?></h2>
                    </div>
                    <div class="tradepress-modal-body">
                        <div class="tradepress-loading-spinner">
                            <span class="spinner is-active"></span>
                            <p><?php esc_html_e('Please wait while we process your request...', 'tradepress'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal Demo -->
        <div class="component-demo">
            <h4><?php esc_html_e('Confirmation Modal', 'tradepress'); ?></h4>
            <button class="tp-button tp-button-danger" id="open-confirm-modal"><?php esc_html_e('Delete Item', 'tradepress'); ?></button>

            <!-- Confirmation Modal Structure -->
            <div id="ui-library-confirm-modal" class="tradepress-modal" style="display:none;">
                <div class="tradepress-modal-content">
                    <div class="tradepress-modal-header">
                        <h2><?php esc_html_e('Confirm Deletion', 'tradepress'); ?></h2>
                        <button class="tradepress-modal-close" aria-label="<?php esc_attr_e('Close modal', 'tradepress'); ?>">&times;</button>
                    </div>
                    <div class="tradepress-modal-body">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span class="dashicons dashicons-warning" style="color: #d63638; font-size: 32px; width: 32px; height: 32px;"></span>
                            <div>
                                <p style="margin: 0; font-weight: 600;"><?php esc_html_e('Are you sure you want to delete this item?', 'tradepress'); ?></p>
                                <p style="margin: 5px 0 0 0; color: #646970;"><?php esc_html_e('This action cannot be undone.', 'tradepress'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="tradepress-modal-footer">
                        <button class="tp-button tp-button-secondary close-confirm-modal"><?php esc_html_e('Cancel', 'tradepress'); ?></button>
                        <button class="tp-button tp-button-danger"><?php esc_html_e('Delete', 'tradepress'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Add inline script for modal functionality using existing patterns
    $modal_script = "
        jQuery(document).ready(function($) {
            // Basic modal functionality
            $('#open-demo-modal').on('click', function() {
                $('#ui-library-demo-modal').show().addClass('open');
            });

            $('#open-task-modal').on('click', function() {
                $('#ui-library-task-modal').show().addClass('open');
            });

            $('#open-loading-modal').on('click', function() {
                var modal = $('#ui-library-loading-modal');
                modal.show().addClass('open');
                
                // Auto close loading modal after 3 seconds
                setTimeout(function() {
                    modal.hide().removeClass('open');
                }, 3000);
            });

            $('#open-confirm-modal').on('click', function() {
                $('#ui-library-confirm-modal').show().addClass('open');
            });

            // Close modal functionality
            $('.tradepress-modal-close, .close-demo-modal, .close-task-modal, .close-confirm-modal').on('click', function() {
                $(this).closest('.tradepress-modal').hide().removeClass('open');
            });

            // Close modal by clicking outside
            $('.tradepress-modal').on('click', function(event) {
                if ($(event.target).is('.tradepress-modal')) {
                    $(this).hide().removeClass('open');
                }
            });

            // Escape key to close modal
            $(document).on('keydown', function(event) {
                if (event.keyCode === 27) { // ESC key
                    $('.tradepress-modal:visible').hide().removeClass('open');
                }
            });
        });
    ";

    wp_add_inline_script('jquery', $modal_script);
    ?>
</div>