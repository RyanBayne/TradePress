<?php
/**
 * TradePress Demo Mode Toggle AJAX Handler
 *
 * @package TradePress/Admin/Ajax
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle demo mode toggle AJAX request
 */
function tradepress_ajax_demo_mode_toggle() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'tradepress_demo_mode_toggle')) {
        wp_die('Security check failed');
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    // Get current demo mode status
    $current_demo_mode = get_option('tradepress_demo_mode', false);
    
    // Toggle demo mode
    $new_demo_mode = !$current_demo_mode;
    update_option('tradepress_demo_mode', $new_demo_mode);

    // Return success response
    wp_send_json_success(array(
        'demo_mode' => $new_demo_mode,
        'message' => $new_demo_mode ? __('Demo mode enabled', 'tradepress') : __('Demo mode disabled', 'tradepress')
    ));
}

add_action('wp_ajax_tradepress_demo_mode_toggle', 'tradepress_ajax_demo_mode_toggle');