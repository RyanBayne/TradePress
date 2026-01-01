<?php
/**
 * UI Library Pagination Controls Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.6
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
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
        
        <!-- TradePress Legacy Pagination -->
        <div class="component-demo">
            <h4><?php esc_html_e('TradePress Style Pagination', 'tradepress'); ?></h4>
            <div class="tradepress-pagination">
                <a href="#" class="tradepress-pagination-prev disabled"><?php esc_html_e('Previous', 'tradepress'); ?></a>
                <ul class="tradepress-pagination-list">
                    <li><a href="#" class="tradepress-pagination-item active">1</a></li>
                    <li><a href="#" class="tradepress-pagination-item">2</a></li>
                    <li><a href="#" class="tradepress-pagination-item">3</a></li>
                    <li><span class="tradepress-pagination-item">...</span></li>
                    <li><a href="#" class="tradepress-pagination-item">24</a></li>
                </ul>
                <a href="#" class="tradepress-pagination-next"><?php esc_html_e('Next', 'tradepress'); ?></a>
            </div>
        </div>
        
        <!-- Compact Pagination -->
        <div class="component-demo">
            <h4><?php esc_html_e('Compact Pagination', 'tradepress'); ?></h4>
            <div class="pagination-container">
                <div class="pagination-compact">
                    <button class="pagination-button" disabled>
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <span class="pagination-info">Page 1 of 24</span>
                    <button class="pagination-button">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
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
        
        <!-- Infinite Scroll Pagination -->
        <div class="component-demo">
            <h4><?php esc_html_e('Infinite Scroll Pagination', 'tradepress'); ?></h4>
            <div class="infinite-scroll-container">
                <div class="infinite-scroll-items">
                    <div class="infinite-scroll-item">
                        <h5><?php esc_html_e('Item 1', 'tradepress'); ?></h5>
                        <p><?php esc_html_e('Example content for the first item in the infinite scroll list.', 'tradepress'); ?></p>
                    </div>
                    <div class="infinite-scroll-item">
                        <h5><?php esc_html_e('Item 2', 'tradepress'); ?></h5>
                        <p><?php esc_html_e('Example content for the second item in the infinite scroll list.', 'tradepress'); ?></p>
                    </div>
                    <div class="infinite-scroll-item">
                        <h5><?php esc_html_e('Item 3', 'tradepress'); ?></h5>
                        <p><?php esc_html_e('Example content for the third item in the infinite scroll list.', 'tradepress'); ?></p>
                    </div>
                </div>
                <div class="infinite-scroll-loader">
                    <div class="infinite-scroll-spinner"></div>
                    <p><?php esc_html_e('Loading more items...', 'tradepress'); ?></p>
                </div>
                <button class="tp-button tp-button-secondary infinite-scroll-button">
                    <?php esc_html_e('Load More', 'tradepress'); ?>
                </button>
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
            
            // Load more button
            $('.infinite-scroll-button').on('click', function() {
                var button = $(this);
                var loader = $('.infinite-scroll-loader');
                
                button.hide();
                loader.show();
                
                setTimeout(function() {
                    loader.hide();
                    
                    var newItems = '';
                    for (var i = 4; i <= 6; i++) {
                        newItems += '<div class=\"infinite-scroll-item\">' +
                                    '<h5>Item ' + i + '</h5>' +
                                    '<p>Example content for item ' + i + ' in the infinite scroll list.</p>' +
                                    '</div>';
                    }
                    
                    $('.infinite-scroll-items').append(newItems);
                    button.show();
                }, 1500);
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
