<?php
/**
 * UI Library Progress Indicators Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.7
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Progress Indicators', 'tradepress'); ?></h3>
    <p><?php esc_html_e('Visual indicators to show progress, loading states, and multi-step processes.', 'tradepress'); ?></p>
    
    <div class="tradepress-component-group">
        <!-- Basic Progress Bars -->
        <div class="component-demo">
            <h4><?php esc_html_e('Basic Progress Bars', 'tradepress'); ?></h4>
            
            <div class="progress-example">
                <div class="progress-label"><?php esc_html_e('Default Progress (40%)', 'tradepress'); ?></div>
                <div class="media-progress-bar">
                    <div style="width: 40%;"></div>
                </div>
            </div>
            
            <div class="progress-example">
                <div class="progress-label"><?php esc_html_e('Success Progress (75%)', 'tradepress'); ?></div>
                <div class="media-progress-bar progress-success">
                    <div style="width: 75%;"></div>
                </div>
            </div>
            
            <div class="progress-example">
                <div class="progress-label"><?php esc_html_e('Warning Progress (60%)', 'tradepress'); ?></div>
                <div class="media-progress-bar progress-warning">
                    <div style="width: 60%;"></div>
                </div>
            </div>
            
            <div class="progress-example">
                <div class="progress-label"><?php esc_html_e('Error Progress (25%)', 'tradepress'); ?></div>
                <div class="media-progress-bar progress-error">
                    <div style="width: 25%;"></div>
                </div>
            </div>
        </div>
        
        <!-- WordPress Admin Loading Spinners -->
        <div class="component-demo">
            <h4><?php esc_html_e('Loading Spinners (WordPress Native)', 'tradepress'); ?></h4>
            
            <div class="tradepress-component-showcase">
                <div class="spinner-example">
                    <div class="spinner-label"><?php esc_html_e('WordPress Default', 'tradepress'); ?></div>
                    <span class="spinner is-active"></span>
                </div>
                
                <div class="spinner-example">
                    <div class="spinner-label"><?php esc_html_e('With Text', 'tradepress'); ?></div>
                    <div class="loading-with-text">
                        <span class="spinner is-active"></span>
                        <span class="loading-text"><?php esc_html_e('Loading data...', 'tradepress'); ?></span>
                    </div>
                </div>
                
                <div class="spinner-example">
                    <div class="spinner-label"><?php esc_html_e('Custom Color', 'tradepress'); ?></div>
                    <span class="spinner is-active" style="filter: hue-rotate(120deg);"></span>
                </div>
            </div>
        </div>
        
        <!-- Step Indicators - Using existing patterns -->
        <div class="component-demo">
            <h4><?php esc_html_e('Step Indicators', 'tradepress'); ?></h4>
            
            <div class="step-progress-container">
                <div class="step-progress-item completed">
                    <div class="step-progress-marker">
                        <span class="dashicons dashicons-yes"></span>
                    </div>
                    <div class="step-progress-label"><?php esc_html_e('Account Setup', 'tradepress'); ?></div>
                </div>
                <div class="step-progress-item active">
                    <div class="step-progress-marker">2</div>
                    <div class="step-progress-label"><?php esc_html_e('API Connection', 'tradepress'); ?></div>
                </div>
                <div class="step-progress-item">
                    <div class="step-progress-marker">3</div>
                    <div class="step-progress-label"><?php esc_html_e('Preferences', 'tradepress'); ?></div>
                </div>
                <div class="step-progress-item">
                    <div class="step-progress-marker">4</div>
                    <div class="step-progress-label"><?php esc_html_e('Confirmation', 'tradepress'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Progress with Text - Using inline styles for positioning -->
        <div class="component-demo">
            <h4><?php esc_html_e('Progress with Text', 'tradepress'); ?></h4>
            
            <div class="progress-example">
                <div class="progress-with-text-container">
                    <div class="media-progress-bar">
                        <div style="width: 65%;"></div>
                    </div>
                    <div class="progress-text-overlay">65%</div>
                </div>
            </div>
            
            <div class="progress-example">
                <div class="progress-container-labeled">
                    <div class="progress-header">
                        <div class="progress-label"><?php esc_html_e('Data Processing', 'tradepress'); ?></div>
                        <div class="progress-value">85%</div>
                    </div>
                    <div class="media-progress-bar progress-success">
                        <div style="width: 85%;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Infinite Scroll Loader - Using WordPress spinner -->
        <div class="component-demo">
            <h4><?php esc_html_e('Content Loading States', 'tradepress'); ?></h4>
            
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
                <div class="infinite-scroll-loader" style="display: none;">
                    <span class="spinner is-active"></span>
                    <p><?php esc_html_e('Loading more items...', 'tradepress'); ?></p>
                </div>
                <button class="tp-button tp-button-secondary infinite-scroll-button">
                    <?php esc_html_e('Load More', 'tradepress'); ?>
                </button>
            </div>
        </div>
    </div>

    <?php
    // Add interactive demo script
    $progress_script = "
        jQuery(document).ready(function($) {
            // Simulate progress animation for media-progress-bar
            function animateProgress() {
                $('.media-progress-bar div').each(function() {
                    var targetWidth = $(this).css('width');
                    $(this).css('width', '0%').animate({
                        width: targetWidth
                    }, 1500);
                });
                
                // Reset after 3 seconds to demo again
                setTimeout(function() {
                    animateProgress();
                }, 3000);
            }
            
            // Load more button functionality
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
            
            // Initial animation
            animateProgress();
        });
    ";
    
    wp_add_inline_script('jquery', $progress_script);
    ?>
</div>
