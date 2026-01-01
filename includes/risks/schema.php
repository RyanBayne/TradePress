<?php
/**
 * Database schema for risk management
 *
 * @package TradePress
 * @subpackage Risks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create risk management database tables
 *
 * @return void
 */
function tradepress_create_risk_management_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'tradepress_risk_monitor_actions';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        position_id bigint(20) NOT NULL,
        action_time datetime NOT NULL,
        action_type varchar(50) NOT NULL,
        previous_stop_loss decimal(10,2) DEFAULT NULL,
        new_stop_loss decimal(10,2) DEFAULT NULL,
        previous_position_size decimal(10,2) DEFAULT NULL,
        new_position_size decimal(10,2) DEFAULT NULL,
        market_volatility decimal(5,2) DEFAULT NULL,
        risk_metric decimal(10,4) NOT NULL,
        reason text NOT NULL,
        result varchar(255) DEFAULT NULL,
        PRIMARY KEY (id),
        KEY position_id (position_id),
        KEY action_time (action_time)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Check if risk management tables exist
 *
 * @return bool True if tables exist
 */
function tradepress_risk_management_tables_exist() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'tradepress_risk_monitor_actions';
    
    return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
}
