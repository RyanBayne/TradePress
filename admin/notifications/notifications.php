<?php
/**
 * TradePress Notifications
 *
 * @package  TradePress
 * @category Core
 * @author   TradePress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Notifications Class
 * Handles various types of notifications including email, admin notices, and browser notifications
 */
class TradePress_Notifications {

    /**
     * Store registered notification types
     */
    private static $notification_types = array();

    /**
     * Initialize the notifications system
     */
    public static function init() {
        // Register default notification types
        self::register_notification_type( 'price_alert', __( 'Price Alerts', 'tradepress' ) );
        self::register_notification_type( 'system_alert', __( 'System Alerts', 'tradepress' ) );
        self::register_notification_type( 'trade_alert', __( 'Trade Alerts', 'tradepress' ) );
        
        // Allow plugins to register custom notification types
        do_action( 'tradepress_register_notification_types' );
        
        // Setup hooks for processing notifications
        add_action( 'tradepress_process_notifications', array( __CLASS__, 'process_pending_notifications' ) );
        
        // Schedule the notification processing event if not already scheduled
        if ( ! wp_next_scheduled( 'tradepress_process_notifications' ) ) {
            wp_schedule_event( time(), 'hourly', 'tradepress_process_notifications' );
        }
    }

    /**
     * Register a notification type
     *
     * @param string $type Unique notification type identifier
     * @param string $label Human-readable label for this notification type
     * @return bool Success
     */
    public static function register_notification_type( $type, $label ) {
        if ( ! isset( self::$notification_types[$type] ) ) {
            self::$notification_types[$type] = array(
                'label' => $label
            );
            return true;
        }
        return false;
    }

    /**
     * Get all registered notification types
     *
     * @return array Notification types
     */
    public static function get_notification_types() {
        return self::$notification_types;
    }

    /**
     * Create a new notification
     *
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $args Optional arguments
     * @return int|bool Notification ID or false on failure
     */
    public static function create_notification( $type, $message, $args = array() ) {
        // Validate notification type
        if ( ! isset( self::$notification_types[$type] ) ) {
            return false;
        }
        
        $defaults = array(
            'user_id'    => 0, // 0 means system-wide, otherwise specific user
            'priority'   => 'normal', // 'high', 'normal', 'low'
            'send_email' => false,
            'expiration' => null, // Timestamp when notification expires
            'data'       => array(), // Additional data for the notification
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Store notification in the database
        $notification_id = self::store_notification( $type, $message, $args );
        
        // Process immediate actions if needed
        if ( $args['send_email'] ) {
            self::send_email_notification( $notification_id, $type, $message, $args );
        }
        
        // Trigger action for other processors
        do_action( 'tradepress_notification_created', $notification_id, $type, $message, $args );
        
        return $notification_id;
    }

    /**
     * Store a notification in the database
     *
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $args Notification arguments
     * @return int|bool Notification ID or false on failure
     */
    private static function store_notification( $type, $message, $args ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_notifications';
        
        // Check if table exists, create if not
        self::maybe_create_tables();
        
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'type'       => $type,
                'message'    => $message,
                'user_id'    => $args['user_id'],
                'priority'   => $args['priority'],
                'is_read'    => 0,
                'created_at' => current_time( 'mysql' ),
                'expires_at' => $args['expiration'] ? date( 'Y-m-d H:i:s', $args['expiration'] ) : null,
                'data'       => maybe_serialize( $args['data'] ),
            ),
            array( '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s' )
        );
        
        if ( $inserted ) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Send email notification
     *
     * @param int $notification_id Notification ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $args Notification arguments
     * @return bool Success
     */
    private static function send_email_notification( $notification_id, $type, $message, $args ) {
        $user_id = $args['user_id'];
        
        // If user ID is 0, this is a system-wide notification
        // Send to admin instead
        if ( $user_id === 0 ) {
            $user_email = get_option( 'admin_email' );
            $user_name = get_bloginfo( 'name' ) . ' Admin';
        } else {
            $user = get_userdata( $user_id );
            if ( ! $user ) {
                return false;
            }
            $user_email = $user->user_email;
            $user_name = $user->display_name;
        }
        
        $subject = sprintf( __( '[%s] %s Notification', 'tradepress' ), 
            get_bloginfo( 'name' ), 
            self::$notification_types[$type]['label'] 
        );
        
        $email_body = sprintf(
            __( "Hello %s,\n\nYou have received a new notification:\n\n%s\n\nTo manage your notifications, visit your account dashboard.\n\nRegards,\n%s Team", 'tradepress' ),
            $user_name,
            $message,
            get_bloginfo( 'name' )
        );
        
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        
        return wp_mail( $user_email, $subject, $email_body, $headers );
    }

    /**
     * Process pending notifications
     */
    public static function process_pending_notifications() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_notifications';
        
        // Process expired notifications
        $wpdb->query( "UPDATE $table_name SET is_read = 1 WHERE expires_at IS NOT NULL AND expires_at < NOW()" );
        
        // Process other scheduled actions for notifications
        do_action( 'tradepress_process_scheduled_notifications' );
    }

    /**
     * Get notifications for a user
     *
     * @param int $user_id User ID (0 for system notifications)
     * @param array $args Query arguments
     * @return array Notifications
     */
    public static function get_notifications( $user_id = 0, $args = array() ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_notifications';
        
        $defaults = array(
            'limit'      => 20,
            'offset'     => 0,
            'type'       => '', // Empty for all types
            'is_read'    => null, // null for both read and unread
            'orderby'    => 'created_at',
            'order'      => 'DESC',
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        $where = array();
        $where[] = $wpdb->prepare( "user_id = %d OR user_id = 0", $user_id ); // User-specific or system-wide
        
        if ( $args['type'] ) {
            $where[] = $wpdb->prepare( "type = %s", $args['type'] );
        }
        
        if ( $args['is_read'] !== null ) {
            $where[] = $wpdb->prepare( "is_read = %d", $args['is_read'] );
        }
        
        // Only show unexpired or never-expiring notifications
        $where[] = "(expires_at IS NULL OR expires_at > NOW())";
        
        $where_clause = implode( ' AND ', $where );
        
        $orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
        
        $limit_clause = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );
        
        $notifications = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE $where_clause ORDER BY $orderby $limit_clause"
        );
        
        // Process the notifications
        foreach ( $notifications as &$notification ) {
            $notification->data = maybe_unserialize( $notification->data );
        }
        
        return $notifications;
    }

    /**
     * Mark a notification as read
     *
     * @param int $notification_id Notification ID
     * @param int $user_id User ID making the change
     * @return bool Success
     */
    public static function mark_as_read( $notification_id, $user_id = 0 ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_notifications';
        
        // Only allow users to mark their own notifications as read
        $where = array(
            'id' => $notification_id,
        );
        
        if ( $user_id > 0 ) {
            $where['user_id'] = $user_id;
        }
        
        $updated = $wpdb->update(
            $table_name,
            array( 'is_read' => 1 ),
            $where
        );
        
        return $updated !== false;
    }

    /**
     * Create the notification tables if they don't exist
     */
    public static function maybe_create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_notifications';
        
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            // Table doesn't exist, create it
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                type varchar(50) NOT NULL,
                message text NOT NULL,
                user_id bigint(20) NOT NULL DEFAULT 0,
                priority varchar(20) NOT NULL DEFAULT 'normal',
                is_read tinyint(1) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL,
                expires_at datetime DEFAULT NULL,
                data longtext DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY user_read (user_id, is_read),
                KEY type (type)
            ) $charset_collate;";
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }
}

// Initialize notifications system
add_action( 'init', array( 'TradePress_Notifications', 'init' ) );
