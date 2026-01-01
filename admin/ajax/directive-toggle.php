<?php
/**
 * TradePress Directive Toggle AJAX Handler
 *
 * @package TradePress/Admin/Ajax
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle directive enable/disable AJAX request
 */
function tradepress_ajax_toggle_directive() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'tradepress_directive_toggle')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $directive_id = sanitize_text_field($_POST['directive_id']);
    $active = (bool)$_POST['active'];
    
    // Get current directive states
    $directive_states = get_option('tradepress_directive_states', array());
    
    // Update the directive state
    $directive_states[$directive_id] = $active;
    
    // Save to WordPress options
    update_option('tradepress_directive_states', $directive_states);
    
    wp_send_json_success(array(
        'directive_id' => $directive_id,
        'active' => $active,
        'message' => $active ? 'Directive enabled' : 'Directive disabled'
    ));
}

add_action('wp_ajax_tradepress_toggle_directive', 'tradepress_ajax_toggle_directive');