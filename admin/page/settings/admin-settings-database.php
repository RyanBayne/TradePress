<?php
/**
 * Admin Database Settings Tab
 *
 * @package    TradePress
 * @subpackage Admin
 * @version    1.0.0
 * @created    2025-04-20
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * TradePress_Admin_Settings_Database Class
 * 
 * Handles the database tab functionality in TradePress settings
 */
class TradePress_Admin_Settings_Database {
    
    /**
     * Constructor
     */
    public function __construct() {
        // AJAX handler for manual table installation
        add_action( 'wp_ajax_tradepress_install_tables', array( $this, 'ajax_install_tables' ) );
    }
    
    /**
     * Get all TradePress tables and their information
     * 
     * @return array Table information
     */
    public function get_table_information() {
        global $wpdb;
        
        // Define the tables to check
        $tradepress_tables = array(
            // Core tables
            $wpdb->prefix . 'tradepress_calls',
            $wpdb->prefix . 'tradepress_errors',
            $wpdb->prefix . 'tradepress_endpoints',
            $wpdb->prefix . 'tradepress_meta',
            
            // Symbol tables
            $wpdb->prefix . 'tradepress_symbols',
            $wpdb->prefix . 'tradepress_price_levels',
            $wpdb->prefix . 'tradepress_price_history',
            
            // Scoring tables
            $wpdb->prefix . 'tradepress_symbol_scores',
            $wpdb->prefix . 'tradepress_directive_scores',
            $wpdb->prefix . 'tradepress_strategies',
            $wpdb->prefix . 'tradepress_strategy_symbols',
            $wpdb->prefix . 'tradepress_score_analysis',
            
            // Bot tables
            $wpdb->prefix . 'tradepress_trades',
            $wpdb->prefix . 'tradepress_algorithm_runs',
            
            // Prediction tables
            $wpdb->prefix . 'tradepress_prediction_sources',
            $wpdb->prefix . 'tradepress_price_predictions',
            $wpdb->prefix . 'tradepress_source_performance',
            
            // Other tables
            $wpdb->prefix . 'tradepress_api_credentials',
            $wpdb->prefix . 'tradepress_portfolios',
            $wpdb->prefix . 'tradepress_positions',
            $wpdb->prefix . 'tradepress_transactions',
            $wpdb->prefix . 'tradepress_watchlists',
            $wpdb->prefix . 'tradepress_watchlist_symbols',
            $wpdb->prefix . 'tradepress_alerts',
        );
        
        $table_info = array();
        
        // Check which tables exist and get their info
        foreach ( $tradepress_tables as $table ) {
            $exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table;
            
            $table_data = array(
                'name'      => $table,
                'exists'    => $exists,
                'rows'      => 0,
                'size'      => 0,
                'size_mb'   => 0,
                'engine'    => '',
                'warning'   => false,
                'message'   => '',
            );
            
            if ( $exists ) {
                // Get row count
                $table_data['rows'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
                
                // Get table status
                $status = $wpdb->get_row( "SHOW TABLE STATUS LIKE '$table'" );
                
                if ( $status ) {
                    // Calculate size (index + data)
                    $size = $status->Data_length + $status->Index_length;
                    $table_data['size'] = $size;
                    $table_data['size_mb'] = round( $size / ( 1024 * 1024 ), 2 );
                    $table_data['engine'] = $status->Engine;
                    
                    // Add warnings for large tables
                    if ( $table_data['size_mb'] > 100 ) {
                        $table_data['warning'] = true;
                        $table_data['message'] = __( 'Table is larger than 100MB. Consider implementing a cleanup strategy.', 'tradepress' );
                    }
                    
                    // Add warnings for tables with many rows
                    if ( $table_data['rows'] > 1000000 ) {
                        $table_data['warning'] = true;
                        $table_data['message'] = __( 'Table has over 1 million rows. Consider implementing a cleanup strategy.', 'tradepress' );
                    }
                    
                    // Warning for non-InnoDB tables with foreign keys
                    if ( $status->Engine !== 'InnoDB' && $this->table_has_foreign_keys( $table ) ) {
                        $table_data['warning'] = true;
                        $table_data['message'] = __( 'Table should use InnoDB engine to support foreign keys.', 'tradepress' );
                    }
                }
            }
            
            $table_info[] = $table_data;
        }
        
        return $table_info;
    }
    
    /**
     * Check if a table has foreign key constraints
     * 
     * @param string $table Table name
     * @return bool True if table has foreign keys
     */
    private function table_has_foreign_keys( $table ) {
        global $wpdb;
        
        // This works for MySQL/MariaDB
        $result = $wpdb->get_var( "
            SELECT COUNT(*) 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' 
            AND TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '" . esc_sql( $table ) . "'"
        );
        
        return $result > 0;
    }
    
    /**
     * Group tables by type
     * 
     * @param array $tables Table information
     * @return array Grouped tables
     */
    public function group_tables_by_type( $tables ) {
        $groups = array(
            'core'      => array(
                'label' => __( 'Core Tables', 'tradepress' ),
                'tables' => array(),
            ),
            'symbol'    => array(
                'label' => __( 'Symbol Data Tables', 'tradepress' ),
                'tables' => array(),
            ),
            'scoring'   => array(
                'label' => __( 'Scoring System Tables', 'tradepress' ),
                'tables' => array(),
            ),
            'bot'       => array(
                'label' => __( 'Bot & Trading Tables', 'tradepress' ),
                'tables' => array(),
            ),
            'prediction'=> array(
                'label' => __( 'Prediction Tables', 'tradepress' ),
                'tables' => array(),
            ),
            'other'     => array(
                'label' => __( 'Other Tables', 'tradepress' ),
                'tables' => array(),
            ),
        );
        
        foreach ( $tables as $table ) {
            $name = $table['name'];
            
            if ( strpos( $name, '_activity' ) !== false || 
                 strpos( $name, '_errors' ) !== false || 
                 strpos( $name, '_endpoints' ) !== false ||
                 strpos( $name, '_meta' ) !== false ) {
                $groups['core']['tables'][] = $table;
            } 
            elseif ( strpos( $name, '_symbols' ) !== false ||
                    strpos( $name, '_price_levels' ) !== false ||
                    strpos( $name, '_price_history' ) !== false ) {
                $groups['symbol']['tables'][] = $table;
            }
            elseif ( strpos( $name, '_symbol_scores' ) !== false ||
                    strpos( $name, '_directive_scores' ) !== false ||
                    strpos( $name, '_strategies' ) !== false ||
                    strpos( $name, '_strategy_symbols' ) !== false ||
                    strpos( $name, '_score_analysis' ) !== false ) {
                $groups['scoring']['tables'][] = $table;
            }
            elseif ( strpos( $name, '_trades' ) !== false ||
                    strpos( $name, '_algorithm_runs' ) !== false ) {
                $groups['bot']['tables'][] = $table;
            }
            elseif ( strpos( $name, '_prediction_sources' ) !== false ||
                    strpos( $name, '_price_predictions' ) !== false ||
                    strpos( $name, '_source_performance' ) !== false ) {
                $groups['prediction']['tables'][] = $table;
            }
            else {
                $groups['other']['tables'][] = $table;
            }
        }
        
        return $groups;
    }
    
    /**
     * Get missing tables
     * 
     * @param array $tables Table information
     * @return array Missing tables
     */
    public function get_missing_tables( $tables ) {
        $missing = array();
        
        foreach ( $tables as $table ) {
            if ( ! $table['exists'] ) {
                $missing[] = $table['name'];
            }
        }
        
        return $missing;
    }
    
    /**
     * AJAX handler for manual table installation
     */
    public function ajax_install_tables() {
        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to perform this action.', 'tradepress' )
            ) );
        }
        
        // Verify nonce
        if ( ! check_ajax_referer( 'tradepress-install-tables', 'nonce', false ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security check failed. Please refresh the page and try again.', 'tradepress' )
            ) );
        }
        
        try {
            // Run installation
            require_once TRADEPRESS_PLUGIN_DIR . 'installation/tables-installation.php';
            
            if ( class_exists( 'TradePress_Install_Tables' ) ) {
                $installer = new TradePress_Install_Tables();
                $installer->update();
                
                wp_send_json_success( array(
                    'message' => __( 'Tables installed successfully! Please refresh the page to see updated information.', 'tradepress' )
                ) );
            } else {
                wp_send_json_error( array(
                    'message' => __( 'Installation class not found.', 'tradepress' )
                ) );
            }
        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => sprintf( __( 'Error: %s', 'tradepress' ), $e->getMessage() )
            ) );
        }
    }
}
