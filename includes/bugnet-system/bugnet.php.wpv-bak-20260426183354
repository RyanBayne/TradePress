<?php
/**
 * TradePress BugNet System
 *
 * Primary debugging and monitoring system for TradePress plugin.
 *
 * @package TradePress/BugNet
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_BugNet {

    /**
     * Log levels
     */
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    /**
     * Output types
     */
    const OUTPUT_ERRORS  = 'errors';   // debug.log
    const OUTPUT_CONSOLE = 'console';  // scripts/css
    const OUTPUT_TRACES  = 'traces';   // trace.log
    const OUTPUT_USERS   = 'users';    // users.log

    /**
     * Instance
     *
     * @var TradePress_BugNet
     */
    private static $instance = null;

    /**
     * Settings
     *
     * @var array
     */
    private $settings = array();

    /**
     * Get instance
     *
     * @return TradePress_BugNet
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_settings();
        $this->init_hooks();
    }

    /**
     * Load settings
     */
    private function load_settings() {
        $this->settings = array(
            'depth_level' => get_option( 'bugnet_depth_level', 3 ),
            'output_errors' => get_option( 'bugnet_output_errors', 'yes' ),
            'output_console' => get_option( 'bugnet_output_console', 'no' ),
            'output_traces' => get_option( 'bugnet_output_traces', 'no' ),
            'output_users' => get_option( 'bugnet_output_users', 'no' ),
            'level_emergency' => get_option( 'bugnet_levelswitch_emergency', 'yes' ),
            'level_alert' => get_option( 'bugnet_levelswitch_alert', 'yes' ),
            'level_critical' => get_option( 'bugnet_levelswitch_critical', 'yes' ),
            'level_error' => get_option( 'bugnet_levelswitch_error', 'yes' ),
            'level_warning' => get_option( 'bugnet_levelswitch_warning', 'no' ),
            'level_notice' => get_option( 'bugnet_levelswitch_notice', 'no' ),
        );
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_footer', array( $this, 'output_console_logs' ) );
        add_action( 'admin_footer', array( $this, 'output_console_logs' ) );
    }

    /**
     * Log message
     *
     * @param string $message Message to log
     * @param string $level   Log level
     * @param array  $context Additional context
     * @param string $output  Output type
     */
    public function log( $message, $level = self::INFO, $context = array(), $output = self::OUTPUT_ERRORS ) {
        // Check if level is enabled
        if ( ! $this->is_level_enabled( $level ) ) {
            return;
        }

        // Check if output type is enabled
        if ( ! $this->is_output_enabled( $output ) ) {
            return;
        }

        // Format message
        $formatted_message = $this->format_message( $message, $level, $context );

        // Output based on type
        switch ( $output ) {
            case self::OUTPUT_ERRORS:
                $this->log_to_file( $formatted_message, 'debug.log' );
                break;
            case self::OUTPUT_TRACES:
                $this->log_to_file( $formatted_message, 'trace.log' );
                break;
            case self::OUTPUT_USERS:
                $this->log_to_file( $formatted_message, 'users.log' );
                break;
            case self::OUTPUT_CONSOLE:
                $this->add_console_log( $formatted_message, $level );
                break;
        }
    }

    /**
     * Log user action
     *
     * @param string $action  Action performed
     * @param array  $context Additional context
     */
    public function log_user_action( $action, $context = array() ) {
        $context['user_id'] = get_current_user_id();
        $context['user_ip'] = $this->get_user_ip();
        $context['timestamp'] = current_time( 'mysql' );
        
        $this->log( "User Action: {$action}", self::INFO, $context, self::OUTPUT_USERS );
    }

    /**
     * Log trace
     *
     * @param string $message Message to log
     * @param array  $context Additional context
     */
    public function trace( $message, $context = array() ) {
        $context['backtrace'] = $this->get_backtrace();
        $this->log( $message, self::DEBUG, $context, self::OUTPUT_TRACES );
    }

    /**
     * Check if level is enabled
     *
     * @param string $level Log level
     * @return bool
     */
    private function is_level_enabled( $level ) {
        $setting_key = 'level_' . $level;
        return isset( $this->settings[ $setting_key ] ) && $this->settings[ $setting_key ] === 'yes';
    }

    /**
     * Check if output type is enabled
     *
     * @param string $output Output type
     * @return bool
     */
    private function is_output_enabled( $output ) {
        $setting_key = 'output_' . $output;
        return isset( $this->settings[ $setting_key ] ) && $this->settings[ $setting_key ] === 'yes';
    }

    /**
     * Format message
     *
     * @param string $message Message
     * @param string $level   Level
     * @param array  $context Context
     * @return string
     */
    private function format_message( $message, $level, $context ) {
        $timestamp = current_time( 'Y-m-d H:i:s' );
        $formatted = "[{$timestamp}] TradePress.{$level}: {$message}";
        
        if ( ! empty( $context ) ) {
            $formatted .= ' ' . wp_json_encode( $context );
        }
        
        return $formatted;
    }

    /**
     * Log to file
     *
     * @param string $message  Message
     * @param string $filename Filename
     */
    private function log_to_file( $message, $filename ) {
        $log_dir = $this->get_log_directory();
        $file_path = $log_dir . '/' . $filename;
        
        error_log( $message . PHP_EOL, 3, $file_path );
    }

    /**
     * Get log directory (same as debug.log)
     *
     * @return string
     */
    private function get_log_directory() {
        // Use WordPress content directory where debug.log is located
        return WP_CONTENT_DIR;
    }

    /**
     * Add console log
     *
     * @param string $message Message
     * @param string $level   Level
     */
    private function add_console_log( $message, $level ) {
        if ( ! isset( $GLOBALS['tradepress_console_logs'] ) ) {
            $GLOBALS['tradepress_console_logs'] = array();
        }
        
        $GLOBALS['tradepress_console_logs'][] = array(
            'message' => $message,
            'level' => $level
        );
    }

    /**
     * Output console logs
     */
    public function output_console_logs() {
        if ( ! isset( $GLOBALS['tradepress_console_logs'] ) || empty( $GLOBALS['tradepress_console_logs'] ) ) {
            return;
        }

        echo '<script>';
        echo 'console.group("TradePress BugNet");';
        
        foreach ( $GLOBALS['tradepress_console_logs'] as $log ) {
            $method = in_array( $log['level'], array( 'error', 'warning' ) ) ? $log['level'] : 'log';
            echo 'console.' . $method . '(' . wp_json_encode( $log['message'] ) . ');';
        }
        
        echo 'console.groupEnd();';
        echo '</script>';
    }

    /**
     * Get user IP
     *
     * @return string
     */
    private function get_user_ip() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Get backtrace
     *
     * @return array
     */
    private function get_backtrace() {
        $trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, $this->settings['depth_level'] );
        
        // Remove this method from trace
        array_shift( $trace );
        
        return $trace;
    }

    /**
     * Static logging methods for easy access
     */
    public static function emergency( $message, $context = array() ) {
        self::instance()->log( $message, self::EMERGENCY, $context );
    }

    public static function alert( $message, $context = array() ) {
        self::instance()->log( $message, self::ALERT, $context );
    }

    public static function critical( $message, $context = array() ) {
        self::instance()->log( $message, self::CRITICAL, $context );
    }

    public static function error( $message, $context = array() ) {
        self::instance()->log( $message, self::ERROR, $context );
    }

    public static function warning( $message, $context = array() ) {
        self::instance()->log( $message, self::WARNING, $context );
    }

    public static function notice( $message, $context = array() ) {
        self::instance()->log( $message, self::NOTICE, $context );
    }

    public static function info( $message, $context = array() ) {
        self::instance()->log( $message, self::INFO, $context );
    }

    public static function debug( $message, $context = array() ) {
        self::instance()->log( $message, self::DEBUG, $context );
    }

    public static function user_action( $action, $context = array() ) {
        self::instance()->log_user_action( $action, $context );
    }

    public static function trace( $message, $context = array() ) {
        self::instance()->trace( $message, $context );
    }
}