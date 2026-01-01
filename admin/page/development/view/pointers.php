<?php
/**
 * TradePress Development - Pointers Testing
 *
 * @package TradePress/Admin/Development
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Admin_Development_Pointers {
    
    public static function output() {
        wp_enqueue_style('wp-pointer');
        wp_enqueue_script('wp-pointer');
        wp_enqueue_style( 'tradepress-pointers', TRADEPRESS_PLUGIN_URL . 'assets/css/components/pointers.css', array(), TRADEPRESS_VERSION );
        
        ?>
        <?php wp_nonce_field('tradepress_pointer_check', 'tradepress-pointers-nonce'); ?>
        <div class="tradepress-pointers-testing">
            <div class="pointers-header">
                <h2><?php _e('WordPress Pointers Testing & Enhancement', 'tradepress'); ?></h2>
                <p><?php _e('Test and enhance the TradePress pointer system with focus features and advanced positioning.', 'tradepress'); ?></p>
            </div>
            
            <div class="pointers-controls">
                <div class="control-group">
                    <h3><?php _e('Basic Pointer Tests', 'tradepress'); ?></h3>
                    <button id="test-basic-pointer" class="button button-primary"><?php _e('Test Basic Pointer', 'tradepress'); ?></button>
                    <button id="test-focus-pointer" class="button button-secondary"><?php _e('Test Focus Pointer', 'tradepress'); ?></button>
                    <button id="test-chain-pointers" class="button"><?php _e('Test Chained Pointers', 'tradepress'); ?></button>
                </div>
                
                <div class="control-group">
                    <h3><?php _e('Focus Settings', 'tradepress'); ?></h3>
                    <label>
                        <input type="checkbox" id="enable-focus" checked> <?php _e('Enable Focus Mode', 'tradepress'); ?>
                    </label>
                    <label>
                        <input type="range" id="overlay-opacity" min="0.1" max="0.9" step="0.1" value="0.5">
                        <?php _e('Overlay Opacity:', 'tradepress'); ?> <span id="opacity-value">0.5</span>
                    </label>
                </div>
            </div>
            
            <div class="test-targets">
                <h3><?php _e('Test Targets', 'tradepress'); ?></h3>
                <div class="target-grid">
                    <div class="test-target" id="target-1">
                        <h4><?php _e('Target 1', 'tradepress'); ?></h4>
                        <p><?php _e('This is a test target for pointer positioning.', 'tradepress'); ?></p>
                    </div>
                    <div class="test-target" id="target-2">
                        <h4><?php _e('Target 2', 'tradepress'); ?></h4>
                        <p><?php _e('Another target with different content.', 'tradepress'); ?></p>
                    </div>
                    <div class="test-target" id="target-3">
                        <h4><?php _e('Target 3', 'tradepress'); ?></h4>
                        <p><?php _e('Third target for testing various positions.', 'tradepress'); ?></p>
                    </div>
                    <div class="test-target" id="target-4">
                        <h4><?php _e('Target 4', 'tradepress'); ?></h4>
                        <p><?php _e('Final target for comprehensive testing.', 'tradepress'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="pointer-log">
                <h3><?php _e('Pointer Activity Log', 'tradepress'); ?></h3>
                <div id="pointer-log-content"></div>
                <button id="clear-log" class="button"><?php _e('Clear Log', 'tradepress'); ?></button>
            </div>
            
            <div class="all-pointers-section">
                <h3><?php _e('All Registered Pointers', 'tradepress'); ?></h3>
                <p><?php _e('Browse all registered pointers and their current status.', 'tradepress'); ?></p>
                <?php self::output_pointers_table(); ?>
            </div>
        </div>
        

        
        <script>
        jQuery(document).ready(function($) {
            let logContent = $('#pointer-log-content');
            let overlayOpacity = 0.5;
            
            // Initialize automatic focus pointer
            (function initializeAutomaticPointer() {
                // Check if pointer has been dismissed
                $.post(ajaxurl, {
                    action: 'tradepress_check_pointer_status',
                    pointer: 'tradepress_automatic_focus_test',
                    nonce: $('#tradepress-pointers-nonce').val()
                }, function(response) {
                    if (!response.dismissed) {
                        // Use the same enhanced pointer function for automatic pointer
                        setTimeout(function() {
                            showEnhancedPointer('#target-1', 'Automatic Focus Test', 'This pointer appears automatically with focus overlay when the page loads. It demonstrates how automatic pointers can work with focus effects.', {
                                position: {
                                    edge: 'left',
                                    align: 'center'
                                }
                            });
                            log('Automatic focus pointer initialized');
                        }, 800); // Delay to ensure DOM is ready
                    }
                });
            })();
            
            // Update opacity display
            $('#overlay-opacity').on('input', function() {
                overlayOpacity = $(this).val();
                $('#opacity-value').text(overlayOpacity);
            });
            
            // Log function
            function log(message) {
                let timestamp = new Date().toLocaleTimeString();
                logContent.append('[' + timestamp + '] ' + message + '\n');
                logContent.scrollTop(logContent[0].scrollHeight);
            }
            
            // Clear log
            $('#clear-log').click(function() {
                logContent.empty();
            });
            
            // Enhanced pointer function with focus feature
            function showEnhancedPointer(target, title, content, options = {}) {
                let enableFocus = $('#enable-focus').is(':checked');
                let $target = $(target);
                
                log('Showing pointer on: ' + target);
                
                // Create focus overlay if enabled
                if (enableFocus) {
                    let overlay = $('<div class="tradepress-focus-overlay"></div>');
                    overlay.css('background', 'rgba(0, 0, 0, ' + overlayOpacity + ')');
                    $('body').append(overlay);
                    
                    // Highlight target
                    $target.addClass('tradepress-focus-target');
                    
                    log('Focus mode enabled with opacity: ' + overlayOpacity);
                }
                
                // Default pointer options
                let pointerOptions = $.extend({
                    content: '<h3>' + title + '</h3><p>' + content + '</p>',
                    position: {
                        edge: 'left',
                        align: 'center'
                    },
                    close: function() {
                        log('Pointer closed');
                        
                        // Remove focus effects
                        $('.tradepress-focus-overlay').remove();
                        $('.tradepress-focus-target').removeClass('tradepress-focus-target');
                        
                        if (options.onClose) {
                            options.onClose();
                        }
                    }
                }, options);
                
                // Show pointer
                $target.pointer(pointerOptions).pointer('open');
            }
            
            // Test basic pointer
            $('#test-basic-pointer').click(function() {
                // Temporarily disable focus for basic test
                let originalFocus = $('#enable-focus').is(':checked');
                $('#enable-focus').prop('checked', false);
                showEnhancedPointer('#target-1', 'Basic Pointer', 'This is a basic pointer test without any special features.');
                // Restore focus setting after a delay
                setTimeout(function() {
                    $('#enable-focus').prop('checked', originalFocus);
                }, 100);
            });
            
            // Test focus pointer
            $('#test-focus-pointer').click(function() {
                showEnhancedPointer('#target-2', 'Focus Pointer', 'This pointer demonstrates the focus feature that dims the background while highlighting the target element.');
            });
            
            // Test chained pointers
            $('#test-chain-pointers').click(function() {
                showEnhancedPointer('#target-1', 'Step 1 of 4', 'This is the first pointer in a chain. Click the X to continue to the next step.', {
                    onClose: function() {
                        setTimeout(function() {
                            showEnhancedPointer('#target-2', 'Step 2 of 4', 'Second step in the chain. The focus feature works with chained pointers too.', {
                                onClose: function() {
                                    setTimeout(function() {
                                        showEnhancedPointer('#target-3', 'Step 3 of 4', 'Third step demonstrates different positioning options.', {
                                            position: { edge: 'right', align: 'top' },
                                            onClose: function() {
                                                setTimeout(function() {
                                                    showEnhancedPointer('#target-4', 'Step 4 of 4', 'Final step completes the chain. This demonstrates a complete tutorial flow.', {
                                                        position: { edge: 'top', align: 'center' }
                                                    });
                                                }, 300);
                                            }
                                        });
                                    }, 300);
                                }
                            });
                        }, 300);
                    }
                });
            });
            
            log('Pointers testing interface initialized');
        });
        </script>
        <?php
    }
    
    /**
     * Output pointers table.
     */
    public static function output_pointers_table() {
        // Load pointer registry
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/pointers/pointer-registry.php';
        $pointers = TradePress_Pointer_Registry::get_all_pointers_with_status();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Pointer ID', 'tradepress' ); ?></th>
                    <th><?php esc_html_e( 'Title', 'tradepress' ); ?></th>
                    <th><?php esc_html_e( 'Page', 'tradepress' ); ?></th>
                    <th><?php esc_html_e( 'Tab', 'tradepress' ); ?></th>
                    <th><?php esc_html_e( 'Category', 'tradepress' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'tradepress' ); ?></th>
                    <th><?php esc_html_e( 'Your Status', 'tradepress' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $pointers as $pointer ) : ?>
                <tr>
                    <td><code><?php echo esc_html( $pointer['id'] ); ?></code></td>
                    <td><strong><?php echo esc_html( $pointer['title'] ); ?></strong></td>
                    <td><?php echo esc_html( $pointer['page'] ); ?></td>
                    <td><?php echo esc_html( $pointer['tab'] ); ?></td>
                    <td>
                        <span class="pointer-category-<?php echo esc_attr( $pointer['category'] ); ?>">
                            <?php echo esc_html( ucfirst( $pointer['category'] ) ); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ( $pointer['status'] === 'active' ) : ?>
                            <span class="status-active">Active</span>
                        <?php else : ?>
                            <span class="status-inactive">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $pointer['dismissed'] ) : ?>
                            <span class="user-status-dismissed">Dismissed</span>
                        <?php else : ?>
                            <span class="user-status-pending">Pending</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr class="pointer-details">
                    <td colspan="7">
                        <div class="pointer-content">
                            <strong><?php esc_html_e( 'Content:', 'tradepress' ); ?></strong>
                            <p><?php echo esc_html( $pointer['content'] ); ?></p>
                            <strong><?php esc_html_e( 'Target:', 'tradepress' ); ?></strong>
                            <code><?php echo esc_html( $pointer['target'] ); ?></code>
                            <strong><?php esc_html_e( 'Position:', 'tradepress' ); ?></strong>
                            <?php echo esc_html( $pointer['position'] ); ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}