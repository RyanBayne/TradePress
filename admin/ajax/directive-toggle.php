<?php
/**
 * TradePress Directive Toggle AJAX Handler
 *
 * @package TradePress/Admin/Ajax
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle directive enable/disable AJAX request
 *
 * @version 1.0.0
 */
function tradepress_ajax_toggle_directive() {
	// Verify nonce
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tradepress_directive_toggle' ) ) {

		wp_die( 'Security check failed' );
	}

	// Check permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );
	}

	$directive_id = isset( $_POST['directive_id'] ) ? sanitize_key( wp_unslash( $_POST['directive_id'] ) ) : '';
	if ( '' === $directive_id ) {
		wp_send_json_error( array( 'message' => 'Invalid directive id' ) );
	}

	$active_raw = isset( $_POST['active'] ) ? sanitize_text_field( wp_unslash( $_POST['active'] ) ) : '0';
	$active     = in_array( strtolower( $active_raw ), array( '1', 'true', 'yes', 'on' ), true );

	// Get current directive states
	$directive_states = get_option( 'tradepress_directive_states', array() );

	// Update the directive state
	$directive_states[ $directive_id ] = $active;

	// Save to WordPress options
	update_option( 'tradepress_directive_states', $directive_states );

	wp_send_json_success(
		array(
			'directive_id' => $directive_id,
			'active'       => $active,
			'message'      => $active ? 'Directive enabled' : 'Directive disabled',
		)
	);
}

add_action( 'wp_ajax_tradepress_toggle_directive', 'tradepress_ajax_toggle_directive' );
