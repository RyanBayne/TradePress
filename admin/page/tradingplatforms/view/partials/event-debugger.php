<?php
/**
 * Event Debugger Component
 * 
 * For diagnosing event handling issues in the API tabs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="event-debugger-container" style="display: none;">
    <h3><?php esc_html_e('Event Debugger', 'tradepress'); ?></h3>
    
    <div class="event-debugger-controls">
        <button type="button" class="button event-debug-start">
            <?php esc_html_e('Start Event Monitoring', 'tradepress'); ?>
        </button>
        
        <button type="button" class="button event-debug-clear">
            <?php esc_html_e('Clear Log', 'tradepress'); ?>
        </button>
        
        <button type="button" class="button event-debug-toggle-container">
            <?php esc_html_e('Show Debugger', 'tradepress'); ?>
        </button>
    </div>
    
    <div class="event-log-container">
        <div class="event-log-header">
            <span><?php esc_html_e('Timestamp', 'tradepress'); ?></span>
            <span><?php esc_html_e('Event Type', 'tradepress'); ?></span>
            <span><?php esc_html_e('Element', 'tradepress'); ?></span>
            <span><?php esc_html_e('Details', 'tradepress'); ?></span>
        </div>
        <div class="event-log-entries"></div>
    </div>
</div>
