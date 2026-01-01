<?php
/**
 * UI Library Animation Showcase Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.9
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Animation Showcase', 'tradepress'); ?></h3>
    <p><?php esc_html_e('CSS animations and transitions for enhancing user experience and providing visual feedback.', 'tradepress'); ?></p>
    
    <div class="tradepress-component-group">
        <!-- Fade Animations -->
        <div class="component-demo">
            <h4><?php esc_html_e('Fade Animations', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Fade In', 'tradepress'); ?></div>
                    <div class="tradepress-card fade-in-demo" data-animation="tradepress-fade-in"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Fade Out', 'tradepress'); ?></div>
                    <div class="tradepress-card fade-out-demo" data-animation="tradepress-fade-out"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Slide Animations -->
        <div class="component-demo">
            <h4><?php esc_html_e('Slide Animations', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Slide Down', 'tradepress'); ?></div>
                    <div class="tradepress-card slide-down-demo" data-animation="tradepress-slide-in-down"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Slide Up', 'tradepress'); ?></div>
                    <div class="tradepress-card slide-up-demo" data-animation="tradepress-slide-in-up"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Slide Left', 'tradepress'); ?></div>
                    <div class="tradepress-card slide-left-demo" data-animation="tradepress-slide-in-left"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Slide Right', 'tradepress'); ?></div>
                    <div class="tradepress-card slide-right-demo" data-animation="tradepress-slide-in-right"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Continuous Animations -->
        <div class="component-demo">
            <h4><?php esc_html_e('Continuous Animations', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Pulse', 'tradepress'); ?></div>
                    <div class="tradepress-card tradepress-pulse"><?php esc_html_e('Pulse', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Heartbeat', 'tradepress'); ?></div>
                    <div class="tradepress-card tradepress-heartbeat"><?php esc_html_e('Heartbeat', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Spin', 'tradepress'); ?></div>
                    <div class="tradepress-card">
                        <span class="dashicons dashicons-update tradepress-spin"></span>
                    </div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Bounce', 'tradepress'); ?></div>
                    <div class="tradepress-card tradepress-bounce"><?php esc_html_e('Bounce', 'tradepress'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Attention Animations -->
        <div class="component-demo">
            <h4><?php esc_html_e('Attention Animations', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Shake', 'tradepress'); ?></div>
                    <div class="tradepress-card shake-demo" data-animation="tradepress-shake"><?php esc_html_e('Click Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Flash', 'tradepress'); ?></div>
                    <div class="tradepress-card tradepress-flash"><?php esc_html_e('Flash', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Highlight', 'tradepress'); ?></div>
                    <div class="tradepress-card highlight-demo" data-animation="tradepress-highlight"><?php esc_html_e('Click Me', 'tradepress'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Scale Animations -->
        <div class="component-demo">
            <h4><?php esc_html_e('Scale Animations', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Scale In', 'tradepress'); ?></div>
                    <div class="tradepress-card scale-in-demo" data-animation="tradepress-scale-in"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Scale Out', 'tradepress'); ?></div>
                    <div class="tradepress-card scale-out-demo" data-animation="tradepress-scale-out"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Transitions -->
        <div class="component-demo">
            <h4><?php esc_html_e('Transitions', 'tradepress'); ?></h4>
            <div class="tradepress-component-showcase">
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Color Transition', 'tradepress'); ?></div>
                    <div class="tradepress-card tradepress-transition-colors transition-demo"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Transform Transition', 'tradepress'); ?></div>
                    <div class="tradepress-card tradepress-transition-transform transform-demo"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Fast Transition', 'tradepress'); ?></div>
                    <div class="tradepress-card tradepress-transition-fast transition-demo"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
                <div class="animation-item">
                    <div class="animation-label"><?php esc_html_e('Slow Transition', 'tradepress'); ?></div>
                    <div class="tradepress-card tradepress-transition-slow transition-demo"><?php esc_html_e('Hover Me', 'tradepress'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Sequenced Animations -->
        <div class="component-demo">
            <h4><?php esc_html_e('Sequenced Animations', 'tradepress'); ?></h4>
            <div class="animation-sequence">
                <button id="sequence-trigger" class="button button-primary"><?php esc_html_e('Start Sequence', 'tradepress'); ?></button>
                <div class="sequence-container">
                    <div class="sequence-item tradepress-delay-100"><?php esc_html_e('First', 'tradepress'); ?></div>
                    <div class="sequence-item tradepress-delay-300"><?php esc_html_e('Second', 'tradepress'); ?></div>
                    <div class="sequence-item tradepress-delay-500"><?php esc_html_e('Third', 'tradepress'); ?></div>
                    <div class="sequence-item tradepress-delay-700"><?php esc_html_e('Fourth', 'tradepress'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>