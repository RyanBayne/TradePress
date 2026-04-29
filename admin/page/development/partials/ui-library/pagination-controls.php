<?php
/**
 * UI Library Pagination Controls Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.6
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section tradepress-ui-section--pagination-controls">
    <h3><?php esc_html_e('Pagination Controls', 'tradepress'); ?></h3>
    <p><?php esc_html_e('Navigation controls for paginated content and data tables.', 'tradepress'); ?></p>
    
    <div class="pagination-showcase">
        <!-- Standard Pagination -->
        <div class="component-demo">
            <h4><?php esc_html_e('Standard Pagination', 'tradepress'); ?></h4>
            <div class="pagination-container">
                <div class="pagination">
                    <a href="#" class="pagination-item disabled">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                        <span class="pagination-text"><?php esc_html_e('Previous', 'tradepress'); ?></span>
                    </a>
                    <a href="#" class="pagination-item active">1</a>
                    <a href="#" class="pagination-item">2</a>
                    <a href="#" class="pagination-item">3</a>
                    <span class="pagination-ellipsis">...</span>
                    <a href="#" class="pagination-item">12</a>
                    <a href="#" class="pagination-item">
                        <span class="pagination-text"><?php esc_html_e('Next', 'tradepress'); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Table Pagination with Page Size -->
        <div class="component-demo">
            <h4><?php esc_html_e('Table Pagination with Page Size', 'tradepress'); ?></h4>
            <div class="pagination-container pagination-with-options">
                <div class="pagination-left">
                    <span class="pagination-status"><?php esc_html_e('Showing 1-10 of 243 items', 'tradepress'); ?></span>
                </div>
                <div class="pagination-center">
                    <div class="pagination">
                        <a href="#" class="pagination-item disabled">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                        </a>
                        <a href="#" class="pagination-item active">1</a>
                        <a href="#" class="pagination-item">2</a>
                        <a href="#" class="pagination-item">3</a>
                        <span class="pagination-ellipsis">...</span>
                        <a href="#" class="pagination-item">25</a>
                        <a href="#" class="pagination-item">
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                        </a>
                    </div>
                </div>
                <div class="pagination-right">
                    <div class="pagination-page-size">
                        <label for="page-size-select"><?php esc_html_e('Show:', 'tradepress'); ?></label>
                        <select id="page-size-select" class="tradepress-select tradepress-select-small">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <?php
    // Add inline script for pagination functionality
    $pagination_script = "
        jQuery(document).ready(function($) {
            // Pagination item click handling
            $('.pagination-item:not(.disabled), .tradepress-pagination-item:not(.disabled)').on('click', function(e) {
                e.preventDefault();
                if (!$(this).hasClass('pagination-prev') && !$(this).hasClass('pagination-next') && 
                    !$(this).hasClass('tradepress-pagination-prev') && !$(this).hasClass('tradepress-pagination-next')) {
                    $(this).closest('ul, div').find('.active').removeClass('active');
                    $(this).addClass('active');
                }
            });
            
            // Page size change
            $('#page-size-select').on('change', function() {
                alert('Page size changed to: ' + $(this).val() + ' items per page');
            });
        });
    ";
    
    wp_add_inline_script('jquery', $pagination_script);
    ?>
</div>
