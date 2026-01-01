<?php
/**
 * TradePress Directive Testing AJAX Handler
 *
 * @package TradePress/Admin/Ajax
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-directive-tester.php';

/**
 * Handle directive testing AJAX request
 */
function tradepress_ajax_test_directive() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'tradepress_directive_test')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $directive_id = sanitize_text_field($_POST['directive_id']);
    $symbol = isset($_POST['symbol']) ? sanitize_text_field($_POST['symbol']) : 'NVDA';
    $force_fresh = isset($_POST['force_fresh']) ? (bool)$_POST['force_fresh'] : true;
    
    // Test the directive
    $result = TradePress_Directive_Tester::test_directive($directive_id, $symbol, $force_fresh);
    
    wp_send_json($result);
}

add_action('wp_ajax_tradepress_test_directive', 'tradepress_ajax_test_directive');