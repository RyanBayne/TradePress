<?php
/**
 * Diagnostic Buttons Component
 * 
 * For testing button functionality and diagnosing event handling issues
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="diagnostic-container">
    <h3><?php esc_html_e('Button Diagnostics', 'tradepress'); ?></h3>
    
    <div class="diagnostic-description">
        <p><?php esc_html_e('This component allows testing button functionality in isolation from the rest of the UI.', 'tradepress'); ?></p>
    </div>
    
    <div class="diagnostic-section">
        <h4><?php esc_html_e('Test Buttons', 'tradepress'); ?></h4>
        
        <div class="diagnostic-button-row">
            <button type="button" class="button diagnostic-button" data-target="test-section-1">
                <?php esc_html_e('Test Button 1', 'tradepress'); ?>
            </button>
            
            <button type="button" class="button diagnostic-button" data-target="test-section-2">
                <?php esc_html_e('Test Button 2', 'tradepress'); ?>
            </button>
            
            <button type="button" class="button diagnostic-button" data-target="test-section-3">
                <?php esc_html_e('Test Button 3', 'tradepress'); ?>
            </button>
        </div>
        
        <div class="diagnostic-results">
            <div id="test-section-1" class="diagnostic-result-section" style="display: none;">
                <h5><?php esc_html_e('Test Section 1', 'tradepress'); ?></h5>
                <p><?php esc_html_e('This is test section 1. If you can see this, button 1 is working correctly.', 'tradepress'); ?></p>
            </div>
            
            <div id="test-section-2" class="diagnostic-result-section" style="display: none;">
                <h5><?php esc_html_e('Test Section 2', 'tradepress'); ?></h5>
                <p><?php esc_html_e('This is test section 2. If you can see this, button 2 is working correctly.', 'tradepress'); ?></p>
            </div>
            
            <div id="test-section-3" class="diagnostic-result-section" style="display: none;">
                <h5><?php esc_html_e('Test Section 3', 'tradepress'); ?></h5>
                <p><?php esc_html_e('This is test section 3. If you can see this, button 3 is working correctly.', 'tradepress'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="diagnostic-section">
        <h4><?php esc_html_e('Event Listener Test', 'tradepress'); ?></h4>
        <p><?php esc_html_e('Click the button below to test if event listeners are working properly:', 'tradepress'); ?></p>
        
        <button type="button" class="button" id="event-test-button">
            <?php esc_html_e('Test Event Listener', 'tradepress'); ?>
        </button>
        
        <div id="event-test-result" style="margin-top: 10px; padding: 10px; background: #f8f8f8; border-left: 4px solid #ccc;">
            <?php esc_html_e('No events detected yet. Click the button above.', 'tradepress'); ?>
        </div>
    </div>
</div>
