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
  *
  * @version 1.0.0
 */
function tradepress_ajax_test_directive() {
    // Verify nonce
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (empty($nonce) || !wp_verify_nonce($nonce, 'tradepress_directive_test')) {

        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    $directive_id = isset($_POST['directive_id']) ? sanitize_key(wp_unslash($_POST['directive_id'])) : '';
    if ('' === $directive_id) {
        wp_send_json_error(array('message' => 'Invalid directive id'));
    }

    $symbol = isset($_POST['symbol']) ? sanitize_text_field(wp_unslash($_POST['symbol'])) : 'NVDA';
    $force_fresh_raw = isset($_POST['force_fresh']) ? sanitize_text_field(wp_unslash($_POST['force_fresh'])) : '1';
    $force_fresh = in_array(strtolower($force_fresh_raw), array('1', 'true', 'yes', 'on'), true);
    
    // Test the directive
    $result = TradePress_Directive_Tester::test_directive($directive_id, $symbol, $force_fresh);
    
    wp_send_json($result);
}

add_action('wp_ajax_tradepress_test_directive', 'tradepress_ajax_test_directive');
