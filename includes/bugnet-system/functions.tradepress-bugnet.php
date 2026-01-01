<?php
/**
 * TradePress BugNet Functions
 *
 * Easy-to-access debugging functions for use throughout WordPress.
 *
 * @package TradePress/BugNet
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Quick debug function
 *
 * @param mixed  $data    Data to debug
 * @param string $label   Optional label
 * @param bool   $console Output to console instead of log
 */
function tradepress_debug( $data, $label = '', $console = false ) {
    $message = $label ? "{$label}: " : '';
    
    if ( is_array( $data ) || is_object( $data ) ) {
        $message .= print_r( $data, true );
    } else {
        $message .= (string) $data;
    }
    
    if ( $console ) {
        TradePress_BugNet::instance()->log( $message, TradePress_BugNet::DEBUG, array(), TradePress_BugNet::OUTPUT_CONSOLE );
    } else {
        TradePress_BugNet::debug( $message );
    }
}

/**
 * Log user action
 *
 * @param string $action  Action performed
 * @param array  $context Additional context
 */
function tradepress_log_user_action( $action, $context = array() ) {
    TradePress_BugNet::user_action( $action, $context );
}

/**
 * Log error with context
 *
 * @param string $message Error message
 * @param array  $context Error context
 */
function tradepress_log_error( $message, $context = array() ) {
    TradePress_BugNet::error( $message, $context );
}

/**
 * Log form submission
 *
 * @param string $form_name Form identifier
 * @param array  $data      Form data (sanitized)
 */
function tradepress_log_form_submission( $form_name, $data = array() ) {
    $context = array(
        'form' => $form_name,
        'data_keys' => array_keys( $data ),
        'page' => isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '',
        'tab' => isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '',
    );
    
    tradepress_log_user_action( 'form_submission', $context );
}

/**
 * Log page navigation
 *
 * @param string $page Page identifier
 * @param string $tab  Tab identifier
 */
function tradepress_log_navigation( $page, $tab = '' ) {
    $context = array(
        'page' => $page,
        'tab' => $tab,
        'referer' => wp_get_referer(),
    );
    
    tradepress_log_user_action( 'navigation', $context );
}

/**
 * Log API call
 *
 * @param string $service  API service name
 * @param string $endpoint Endpoint called
 * @param array  $context  Additional context
 */
function tradepress_log_api_call( $service, $endpoint, $context = array() ) {
    $context['service'] = $service;
    $context['endpoint'] = $endpoint;
    
    TradePress_BugNet::info( "API Call: {$service}/{$endpoint}", $context );
}

/**
 * Trace function execution
 *
 * @param string $function_name Function being traced
 * @param array  $args          Function arguments (optional)
 */
function tradepress_trace_function( $function_name, $args = array() ) {
    $context = array(
        'function' => $function_name,
        'args' => $args,
    );
    
    TradePress_BugNet::trace( "Function execution: {$function_name}", $context );
}

/**
 * Log performance metric
 *
 * @param string $metric_name Metric identifier
 * @param mixed  $value       Metric value
 * @param string $unit        Unit of measurement
 */
function tradepress_log_performance( $metric_name, $value, $unit = '' ) {
    $context = array(
        'metric' => $metric_name,
        'value' => $value,
        'unit' => $unit,
        'memory_usage' => memory_get_usage( true ),
        'peak_memory' => memory_get_peak_usage( true ),
    );
    
    TradePress_BugNet::info( "Performance: {$metric_name}", $context );
}

/**
 * AI debugging log - separate from error logs
 *
 * @param string $message Debug message
 * @param array  $context Additional context
 */
function tradepress_ai_log( $message, $context = array() ) {
    $log_file = WP_CONTENT_DIR . '/ai.log';
    $timestamp = current_time( 'Y-m-d H:i:s' );
    $context_str = ! empty( $context ) ? ' | Context: ' . json_encode( $context ) : '';
    $log_entry = "[{$timestamp}] {$message}{$context_str}\n";
    
    file_put_contents( $log_file, $log_entry, FILE_APPEND | LOCK_EX );
}

/**
 * Trace log for development monitoring - tracks expected flow
 *
 * @param string $message Trace message
 * @param array  $context Additional context
 */
function tradepress_trace_log( $message, $context = array() ) {
    $log_file = WP_CONTENT_DIR . '/trace.log';
    $timestamp = current_time( 'Y-m-d H:i:s' );
    $context_str = ! empty( $context ) ? ' | Context: ' . json_encode( $context ) : '';
    $log_entry = "[{$timestamp}] {$message}{$context_str}\n";
    
    file_put_contents( $log_file, $log_entry, FILE_APPEND | LOCK_EX );
}

/**
 * Automation log for async processes, CRON, JS, and Ajax
 *
 * @param string $message Log message
 * @param array  $context Additional context
 */
function tradepress_automation_log( $message, $context = array() ) {
    $log_file = WP_CONTENT_DIR . '/automation.log';
    $timestamp = current_time( 'Y-m-d H:i:s' );
    $context_str = ! empty( $context ) ? ' | Context: ' . json_encode( $context ) : '';
    $log_entry = "[{$timestamp}] {$message}{$context_str}\n";
    
    file_put_contents( $log_file, $log_entry, FILE_APPEND | LOCK_EX );
}