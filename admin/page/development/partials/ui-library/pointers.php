<?php
/**
 * UI Library Pointers Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.0
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('WordPress Pointers', 'tradepress'); ?></h3>
    <p><?php esc_html_e('WordPress core pointer system for contextual help and guidance using TradePress pointer classes.', 'tradepress'); ?></p>
    
    <div class="tradepress-component-group">
        <!-- Left Header Pointer Demo -->
        <div class="component-demo">
            <h4><?php esc_html_e('Left Header Pointer', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div style="display: flex; gap: 20px; align-items: flex-start;">
                    <div style="flex: 1; background: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
                        <p><?php esc_html_e('Left content area', 'tradepress'); ?></p>
                    </div>
                    <div class="test-pointer-container" style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ddd; position: relative;">
                        <h3><?php esc_html_e('Test Header', 'tradepress'); ?></h3>
                        <p><?php esc_html_e('This container simulates the directive configuration area.', 'tradepress'); ?></p>
                        <button class="button test-pointer-trigger"><?php esc_html_e('Show Pointer', 'tradepress'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pointer Positioning Test -->
        <div class="component-demo">
            <h4><?php esc_html_e('Pointer Positioning', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0;">
                    <div class="pointer-test-box" data-position="left" style="background: #f0f0f0; padding: 15px; text-align: center; border: 1px solid #ccc;">
                        <h4><?php esc_html_e('Left Position', 'tradepress'); ?></h4>
                        <button class="button test-position-pointer"><?php esc_html_e('Test Left', 'tradepress'); ?></button>
                    </div>
                    <div class="pointer-test-box" data-position="right" style="background: #f0f0f0; padding: 15px; text-align: center; border: 1px solid #ccc;">
                        <h4><?php esc_html_e('Right Position', 'tradepress'); ?></h4>
                        <button class="button test-position-pointer"><?php esc_html_e('Test Right', 'tradepress'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- CSS Testing Area -->
        <div class="component-demo">
            <h4><?php esc_html_e('CSS Testing', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <textarea id="pointer-css-test" rows="8" style="width: 100%; font-family: monospace;">
.tradepress-left-header-pointer {
    margin-left: -15px !important;
}
.tradepress-left-header-pointer .wp-pointer-arrow {
    top: 20px !important;
}
                </textarea>
                <button class="button button-primary" id="apply-pointer-css"><?php esc_html_e('Apply CSS', 'tradepress'); ?></button>
                <button class="button" id="reset-pointer-css"><?php esc_html_e('Reset CSS', 'tradepress'); ?></button>
            </div>
        </div>
    </div>
    
    <?php
    // Add pointer testing script
    wp_enqueue_style('wp-pointer');
    wp_enqueue_script('wp-pointer');
    
    $pointer_script = "
        jQuery(document).ready(function($) {
            var testPointer = null;
            
            // Test left header pointer
            $('.test-pointer-trigger').on('click', function(e) {
                e.preventDefault();
                if (testPointer) {
                    testPointer.pointer('close');
                }
                
                var target = $('.test-pointer-container');
                var options = {
                    content: '<h3>Test Pointer</h3><p>This is a test of the left header pointer positioning.</p>',
                    position: {
                        edge: 'right',
                        align: 'top'
                    },
                    pointerClass: 'wp-pointer-right tradepress-left-header-pointer',
                    width: 320,
                    close: function() {
                        testPointer = null;
                    }
                };
                
                testPointer = target.pointer(options);
                testPointer.pointer('open');
            });
            
            // Test position pointers
            $('.test-position-pointer').on('click', function(e) {
                e.preventDefault();
                if (testPointer) {
                    testPointer.pointer('close');
                }
                
                var target = $(this).closest('.pointer-test-box');
                var position = target.data('position');
                var options = {
                    content: '<h3>' + position.charAt(0).toUpperCase() + position.slice(1) + ' Pointer</h3><p>Testing ' + position + ' positioning.</p>',
                    position: {
                        edge: position === 'left' ? 'right' : 'left',
                        align: 'middle'
                    },
                    width: 250,
                    close: function() {
                        testPointer = null;
                    }
                };
                
                testPointer = target.pointer(options);
                testPointer.pointer('open');
            });
            
            // CSS testing
            $('#apply-pointer-css').on('click', function() {
                var css = $('#pointer-css-test').val();
                $('#dynamic-pointer-css').remove();
                $('<style id=\"dynamic-pointer-css\">' + css + '</style>').appendTo('head');
            });
            
            $('#reset-pointer-css').on('click', function() {
                $('#dynamic-pointer-css').remove();
                $('#pointer-css-test').val('.tradepress-left-header-pointer {\\n    margin-left: -15px !important;\\n}\\n.tradepress-left-header-pointer .wp-pointer-arrow {\\n    top: 20px !important;\\n}');
            });
            
            // Close pointers when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.wp-pointer, .test-pointer-trigger, .test-position-pointer').length) {
                    if (testPointer) {
                        testPointer.pointer('close');
                    }
                }
            });
        });
    ";
    
    wp_add_inline_script('wp-pointer', $pointer_script);
    ?>
</div>