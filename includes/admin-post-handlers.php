<?php
/**
 * Admin Post Handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load BugNet functions for trace logging
if ( ! function_exists( 'tradepress_trace_log' ) ) {
	require_once TRADEPRESS_PLUGIN_DIR . 'includes/bugnet-system/functions.tradepress-bugnet.php';
}

// Test form handler
add_action( 'admin_post_test_form_submit', 'handle_test_form_submit' );

/**
 * Handle test form submit.
 *
 * @version 1.0.0
 */
function handle_test_form_submit() {

	tradepress_trace_log( 'Test form handler called' );
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'test_form_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'tradepress' ) );
	}

	$test_value = isset( $_POST['test_field'] ) ? sanitize_text_field( wp_unslash( $_POST['test_field'] ) ) : '';
	tradepress_trace_log( 'Test value: ' . $test_value );

	wp_safe_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&test_result=' . urlencode( $test_value ) ) );
	exit;
}

// Advisor handlers
add_action( 'admin_post_tradepress_advisor_step_1', 'tradepress_handle_advisor_step_1' );
add_action( 'admin_post_tradepress_advisor_step_2', 'tradepress_handle_advisor_step_2' );
add_action( 'admin_post_tradepress_advisor_step_3', 'tradepress_handle_advisor_step_3' );
add_action( 'admin_post_tradepress_advisor_step_4', 'tradepress_handle_advisor_step_4' );
add_action( 'admin_post_tradepress_advisor_step_5', 'tradepress_handle_advisor_step_5' );
add_action( 'admin_post_tradepress_advisor_step_6', 'tradepress_handle_advisor_step_6' );

/**
 * Handle advisor step 1.
 *
 * @version 1.0.0
 */
function tradepress_handle_advisor_step_1() {

	tradepress_trace_log( 'Advisor Step 1 handler called' );
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'tradepress_advisor_step_1' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'tradepress' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'tradepress' ) );
	}

	$mode = isset( $_POST['advisor_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['advisor_mode'] ) ) : 'invest';
	tradepress_trace_log( 'Selected mode: ' . $mode );

	// Save mode to session
	require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-session.php';
	$session = new TradePress_Advisor_Session();

	if ( ! $session->has_session() ) {
		$session->start_session( $mode );
	} else {
		$session->set_mode( $mode );
	}

	// Mark step 1 as completed
	$session->mark_step_completed( 1 );
	$session->set_current_step( 2 );

	tradepress_trace_log( 'Session created/updated for mode: ' . $mode . ', Step 1 completed' );

	wp_safe_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2' ) );
	exit;
}

/**
 * Handle advisor step 2.
 *
 * @version 1.0.0
 */
function tradepress_handle_advisor_step_2() {

	tradepress_trace_log( 'Advisor Step 2 handler called' );
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'tradepress_advisor_step_2' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'tradepress' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'tradepress' ) );
	}

	$selected_symbols = isset( $_POST['selected_symbols'] ) && is_array( $_POST['selected_symbols'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['selected_symbols'] ) ) : array();
	tradepress_trace_log( 'Selected symbols', array( 'symbols' => $selected_symbols ) );

	// Save symbols to session
	require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-session.php';
	$session = new TradePress_Advisor_Session();

	if ( $session->has_session() ) {
		$session->set_selected_symbols( $selected_symbols );
		$session->mark_step_completed( 2 );
		$session->set_current_step( 3 );
		tradepress_trace_log( 'Step 2 completed, symbols saved' );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=3' ) );
	exit;
}

/**
 * Handle advisor step 3.
 *
 * @version 1.0.0
 */
function tradepress_handle_advisor_step_3() {

	tradepress_trace_log( 'Advisor Step 3 handler called' );
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'tradepress_advisor_step_3' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'tradepress' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'tradepress' ) );
	}

	// Load session and mark step 3 as completed
	require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-session.php';
	$session = new TradePress_Advisor_Session();

	if ( $session->has_session() ) {
		$session->mark_step_completed( 3 );
		$session->set_current_step( 4 );
		tradepress_trace_log( 'Step 3 completed, news analysis finished' );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=4&message=news_analyzed' ) );
	exit;
}

/**
 * Handle advisor step 4.
 *
 * @version 1.0.0
 */
function tradepress_handle_advisor_step_4() {

	tradepress_trace_log( 'Advisor Step 4 handler called' );
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'tradepress_advisor_step_4' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'tradepress' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'tradepress' ) );
	}

	// Load session and mark step 4 as completed
	require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-session.php';
	$session = new TradePress_Advisor_Session();

	if ( $session->has_session() ) {
		$session->mark_step_completed( 4 );
		$session->set_current_step( 5 );
		tradepress_trace_log( 'Step 4 completed, forecast analysis finished' );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=5&message=forecasts_analyzed' ) );
	exit;
}

/**
 * Handle advisor step 5.
 *
 * @version 1.0.0
 */
function tradepress_handle_advisor_step_5() {

	tradepress_trace_log( 'Advisor Step 5 handler called' );
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'tradepress_advisor_step_5' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'tradepress' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'tradepress' ) );
	}

	// Load session and mark step 5 as completed
	require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-session.php';
	$session = new TradePress_Advisor_Session();

	if ( $session->has_session() ) {
		$session->mark_step_completed( 5 );
		$session->set_current_step( 6 );
		tradepress_trace_log( 'Step 5 completed, economic analysis finished' );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=6&message=economic_analyzed' ) );
	exit;
}

/**
 * Handle advisor step 6.
 *
 * @version 1.0.0
 */
function tradepress_handle_advisor_step_6() {

	tradepress_trace_log( 'Advisor Step 6 handler called' );
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'tradepress_advisor_step_6' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'tradepress' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'tradepress' ) );
	}

	// Process technical indicator selections
	$selected_indicators = isset( $_POST['selected_indicators'] ) && is_array( $_POST['selected_indicators'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['selected_indicators'] ) ) : array();
	$scoring_strategy    = isset( $_POST['scoring_strategy'] ) ? sanitize_text_field( wp_unslash( $_POST['scoring_strategy'] ) ) : 'balanced';
	$time_horizon        = isset( $_POST['time_horizon'] ) ? sanitize_text_field( wp_unslash( $_POST['time_horizon'] ) ) : '1m';

	tradepress_trace_log(
		'Technical settings processed',
		array(
			'indicators' => $selected_indicators,
			'strategy'   => $scoring_strategy,
			'horizon'    => $time_horizon,
		)
	);

	// Load session and save technical settings
	require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-session.php';
	$session = new TradePress_Advisor_Session();

	if ( $session->has_session() ) {
		$session->set_technical_settings(
			array(
				'indicators'       => $selected_indicators,
				'scoring_strategy' => $scoring_strategy,
				'time_horizon'     => $time_horizon,
			)
		);
		$session->mark_step_completed( 6 );
		$session->set_current_step( 7 );
		tradepress_trace_log( 'Step 6 completed, technical settings saved' );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=7&message=technical_configured' ) );
	exit;
}

/**
 * Handle legacy developer mode toggle requests.
 *
 * @version 1.0.0
 */
function tradepress_handle_developer_mode_toggle() {
	wp_die( esc_html__( 'Developer Mode is controlled by the WP_DEVELOPMENT_MODE constant.', 'tradepress' ) );
}
