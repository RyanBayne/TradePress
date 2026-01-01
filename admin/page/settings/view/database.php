<?php
/**
 * Database Settings Tab
 *
 * @package    TradePress
 * @subpackage Admin
 * @version    1.0.2
 * @created    2025-04-20
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Database Settings Tab
 */
class TradePress_Settings_Database extends TradePress_Settings_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id    = 'database';
        $this->label = __( 'Database', 'tradepress' );
        
        parent::__construct();
        

    }
    
    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {
        $settings = array(
            array(
                'title' => __( 'Database Management', 'tradepress' ),
                'type'  => 'title',
                'desc'  => __( 'View and manage the database tables used by TradePress. You can install missing tables or check table status.', 'tradepress' ),
                'id'    => 'database_management_options',
            ),
            
            array(
                'type' => 'sectionend',
                'id'   => 'database_management_options',
            ),
        );

        return apply_filters( 'TradePress_data_settings', $settings );
    }
    
    /**
     * Output the settings
     */
    public function output() {
        parent::output();
        
        // Get table information directly
        $tables = $this->get_table_information();
        
        // Get missing tables
        $missing_tables = array();
        $existing_tables = 0;
        
        foreach ($tables as $table) {
            if ($table['exists']) {
                $existing_tables++;
            } else {
                $missing_tables[] = $table['name'];
            }
        }
        

        // Display database overview
        echo '<div class="tradepress-database-summary">';
        echo '<h3>' . esc_html__( 'Database Overview', 'tradepress' ) . '</h3>';
        echo '<p>' . sprintf( 
            esc_html__( 'Total tables: %1$d, Existing: %2$d, Missing: %3$d', 'tradepress' ),
            count( $tables ),
            $existing_tables,
            count( $missing_tables )
        ) . '</p>';
        
        // Display missing tables info
        if ( ! empty( $missing_tables ) ) {
            echo '<div class="tradepress-missing-tables-notice">';
            echo '<h3>' . esc_html__( 'Missing Tables', 'tradepress' ) . '</h3>';
            echo '<p>' . esc_html__( 'The following tables are missing from your database:', 'tradepress' ) . '</p>';
            echo '<ul>';
            foreach ( $missing_tables as $table ) {
                echo '<li>' . esc_html( $table ) . '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        
        // Display table list
        echo '<div class="tradepress-database-tables">';
        echo '<h3>' . esc_html__( 'Database Tables', 'tradepress' ) . '</h3>';
        
        echo '<table class="widefat striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . esc_html__( 'Table Name', 'tradepress' ) . '</th>';
        echo '<th>' . esc_html__( 'Status', 'tradepress' ) . '</th>';
        echo '<th>' . esc_html__( 'Rows', 'tradepress' ) . '</th>';
        echo '<th>' . esc_html__( 'Size', 'tradepress' ) . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ( $tables as $table ) {
            $class = ! $table['exists'] ? 'tradepress-missing-table' : '';
            echo '<tr class="' . esc_attr( $class ) . '">';
            echo '<td>' . esc_html( $table['name'] ) . '</td>';
            echo '<td>';
            if ( $table['exists'] ) {
                echo '<span class="tradepress-table-exists">' . esc_html__( 'Exists', 'tradepress' ) . '</span>';
            } else {
                echo '<span class="tradepress-table-missing">' . esc_html__( 'Missing', 'tradepress' ) . '</span>';
            }
            echo '</td>';
            echo '<td>';
            if ( $table['exists'] ) {
                echo number_format( $table['rows'] );
            } else {
                echo '—';
            }
            echo '</td>';
            echo '<td>';
            if ( $table['exists'] ) {
                echo number_format( $table['size_mb'], 2 ) . ' MB';
            } else {
                echo '—';
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        // Styles moved to assets/css/pages/settings-database.css
    }
    
    /**
     * Get table information using centralized table definitions
     * 
     * @return array Table information
     */
    private function get_table_information() {
        global $wpdb;
        
        // Load the centralized table installation class
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/installation/tables-installation.php';
        $installer = new TradePress_Install_Tables();
        
        // Get table names from centralized source
        $table_names = $installer->get_tables();
        
        // Convert to full table names with prefix
        $tradepress_tables = array();
        foreach ($table_names as $table_name) {
            $tradepress_tables[] = $wpdb->prefix . $table_name;
        }
        
        $table_info = array();
        
        // Check which tables exist and get their info
        foreach ( $tradepress_tables as $table ) {
            $exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table;
            
            $table_data = array(
                'name'      => $table,
                'exists'    => $exists,
                'rows'      => 0,
                'size'      => 0,
                'size_mb'   => 0
            );
            
            if ( $exists ) {
                // Get row count
                $table_data['rows'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
                
                // Get table size
                $status = $wpdb->get_row( "SHOW TABLE STATUS LIKE '$table'" );
                if ( $status && isset( $status->Data_length ) ) {
                    $size = $status->Data_length + $status->Index_length;
                    $table_data['size'] = $size;
                    $table_data['size_mb'] = round( $size / ( 1024 * 1024 ), 2 );
                }
            }
            
            $table_info[] = $table_data;
        }
        
        return $table_info;
    }
    


}

return new TradePress_Settings_Database();
