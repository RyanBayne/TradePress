<?php
/**
 * Queue Database Schema
 * 
 * Creates database tables for background processing queue system
 *
 * @package TradePress
 * @subpackage BackgroundProcessing
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Queue_Schema {
    
    /**
     * Create queue tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Queue items table
        $queue_table = $wpdb->prefix . 'tradepress_queue';
        $queue_sql = "CREATE TABLE $queue_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            queue_name varchar(50) NOT NULL,
            priority int(11) NOT NULL DEFAULT 10,
            status varchar(20) NOT NULL DEFAULT 'pending',
            item_type varchar(50) NOT NULL,
            item_data longtext NOT NULL,
            attempts int(11) NOT NULL DEFAULT 0,
            max_attempts int(11) NOT NULL DEFAULT 3,
            created_at datetime NOT NULL,
            scheduled_at datetime NOT NULL,
            started_at datetime NULL,
            completed_at datetime NULL,
            error_message text NULL,
            PRIMARY KEY (id),
            KEY queue_status (queue_name, status),
            KEY priority_scheduled (priority DESC, scheduled_at ASC),
            KEY status_attempts (status, attempts)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($queue_sql);
        
        // Update database version
        update_option('tradepress_queue_db_version', '1.0.0');
    }
    
    /**
     * Check if tables exist and create if needed
     */
    public static function maybe_create_tables() {
        $db_version = get_option('tradepress_queue_db_version', '0');
        
        if (version_compare($db_version, '1.0.0', '<')) {
            self::create_tables();
        }
    }
    
    /**
     * Add item to queue
     */
    public static function add_item($queue_name, $item_type, $item_data, $priority = 10, $scheduled_at = null) {
        global $wpdb;
        
        self::maybe_create_tables();
        
        if (!$scheduled_at) {
            $scheduled_at = current_time('mysql');
        }
        
        $table = $wpdb->prefix . 'tradepress_queue';
        
        return $wpdb->insert(
            $table,
            array(
                'queue_name' => $queue_name,
                'priority' => $priority,
                'item_type' => $item_type,
                'item_data' => json_encode($item_data),
                'created_at' => current_time('mysql'),
                'scheduled_at' => $scheduled_at
            ),
            array('%s', '%d', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Get next item from queue
     */
    public static function get_next_item($queue_name) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_queue';
        
        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table 
            WHERE queue_name = %s 
            AND status = 'pending' 
            AND scheduled_at <= %s 
            AND attempts < max_attempts
            ORDER BY priority DESC, scheduled_at ASC 
            LIMIT 1
        ", $queue_name, current_time('mysql')));
    }
}