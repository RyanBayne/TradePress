<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Requires core WP List Table class...
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * List table for viewing all API activity...
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TradePress/Views
 * @version     1.0
 */
class TradePress_ListTable_APIActivity extends WP_List_Table {

    /**
     * Max items.
     *
     * @var int
     */
    protected $max_items;

    public $items = array();
    
    /**
     * Constructor.
     */
    public function __construct() {

        parent::__construct( array(
            'singular'  => __( 'Entry', 'tradepress' ),
            'plural'    => __( 'Entries', 'tradepress' ),
            'ajax'      => false
        ) );
        
        // Apply default items to the $items object.
        $this->default_items();
    }   
    
    /**
    * Setup default items. 
    * 
    * This is not required and was only implemented for demonstration purposes. 
    * 
    * @version 1.3
    */
    public function default_items() {
        global $wpdb;
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
        
        $activity = $wpdb->tradepress_calls;
        $meta = $wpdb->tradepress_meta;
        
        /*
        $records = $wpdb->get_results( "
            SELECT a.*,m.metavalue AS rawbody  
            FROM $activity a
            INNER JOIN $meta m    
            ON a.entryid = m.entryid
            WHERE m.metakey = 'rawbody' 
        ", 
        'OBJECT' );
        */
        
        //$records = TradePress_db_selectorderby( $wpdb->tradepress_calls, null, 'callid' );       

        /*
        $records = $wpdb->get_results( "
        SELECT a.*, m1.metavalue AS rawresponse, m2.metavalue AS rawbody, m3.metavalue as rawheader
        FROM $activity AS a
        LEFT JOIN $meta AS m1 ON a.entryid = m1.entryid
        LEFT JOIN $meta AS m2 ON a.entryid = m1.entryid
        LEFT JOIN $meta AS m3 ON a.entryid = m1.entryid
        WHERE m1.metakey = 'rawresponse'
        AND m2.metakey = 'rawbody'
        AND m3.metakey = 'rawheader'
        LIMIT 10
        ", 
        'OBJECT' );        
        */
        
        /*
        $records = $wpdb->get_results( "
        SELECT a.*, m1.metavalue AS rawresponse, m2.metavalue AS rawbody, m3.metavalue as rawheader
        FROM $activity AS a
        LEFT JOIN $meta AS m1 ON a.entryid = m1.entryid
        LEFT JOIN $meta AS m2 ON a.entryid = m1.entryid
        LEFT JOIN $meta AS m3 ON a.entryid = m1.entryid
        WHERE m1.metakey = 'rawresponse'
        AND m2.metakey = 'rawbody'
        AND m3.metakey = 'rawheader'
        LIMIT 10
        ", 
        'OBJECT' );        
        */
        
        $records = $wpdb->get_results( "
            SELECT a.*,m.metavalue AS rawresponse  
            FROM $activity a
            INNER JOIN $meta m    
            ON a.entryid = m.entryid
            WHERE m.metakey = 'rawresponse' 
        ", 
        'OBJECT' );
                
        if( !isset( $records ) || !is_array( $records ) ) { $records = array(); } 

        // Loop on individual trace entries. 
        foreach( $records as $key => $row ) {

            ++$entry_counter;
            
            // Create new array entry and get it's key...
            $this->items[]['entry_number'] = $entry_counter; 
            end( $this->items);
            $key = key( $this->items );
            
            $this->items[$key]['entryid']     = $row->entryid;
            $this->items[$key]['callid']      = $row->callid;
            $this->items[$key]['service']     = $row->service;
            $this->items[$key]['type']        = $row->type;
            $this->items[$key]['outcome']     = $row->outcome;
            $this->items[$key]['timestamp']   = $row->timestamp;
            $this->items[$key]['wpuserid']    = $row->wpuserid;     
            $this->items[$key]['rawresponse'] = $row->rawresponse;
        }   
        
        $this->items = array_reverse( $this->items );                                                   
    }
    
    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No items found.', 'tradepress' );
    }

    public function display_tablenav( $position ) {
        if ( $position != 'top' ) { parent::display_tablenav( $position ); }
    }

    /**
     * Output the tabled report
     */
    public function output_result() {     
        $this->prepare_items();
        add_thickbox();
        echo '<div id="poststuff" class="TradePress-tablelist-wide">';
        $this->display();
        echo '</div>';
    }

    /**
     * Get column value.
     *
     * @param mixed $item
     * @param string $column_name   
     * 
     * @version 2.0
     */
    public function column_default( $item, $column_name ) {
        
        $url = add_query_arg( array( 'view_record' => $item['entryid'] ) );
        switch( $column_name ) {
            case 'timestamp' :
                $time_passed = human_time_diff( strtotime( $item['timestamp'] ), current_time( 'timestamp' ) );
                echo sprintf( __( '%s ago', 'tradepress' ), $time_passed );
                echo '<pre><a href="' . $url . '">' . $item['callid'] . '</a></pre>';
            break;
            case 'callid' :
                echo '<pre>'; print_r( $item['service'] ); echo '</pre>';
                echo '<pre>'; print_r( $item['type'] ); echo '</pre>';
                echo '<pre>'; print_r( $item['outcome'] ); echo '</pre>';                
            break;
            
            case 'user' :
                echo '<pre>'; _e( 'WP ID: ', 'tradepress' ); print_r( $item['wpuserid'] ); echo '</pre>';
            break;            
        
            case 'type' :
                echo '<pre>'; print_r( $item['type'] ); echo '</pre>';
            break;            
            
            case 'outcome' :
                echo '<pre>'; print_r( $item['outcome'] ); echo '</pre>';
            break;                                    
            
            case 'wpuserid' :
                echo '<pre>'; print_r( $item['wpuserid'] ); echo '</pre>';
            break;  
            case 'rawresponse' :
                echo '<textarea rows="3" cols="25">' . print_r( $item['rawresponse'], true ) . '</textarea>';
            break;                                                                                                       
        }
    }

    /**
     * Get columns.
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'timestamp'   => __( 'Time/Call ID', 'tradepress' ),
            'callid'      => __( 'General', 'tradepress' ),
            'user'        => __( 'User', 'tradepress' ),
            'rawresponse' => __( 'Response', 'tradepress' ),
            'custom'      => __( 'Custom', 'tradepress' ),
        );
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'TradePress_listtable_apiactivity_items_per_page', 20 );

        $this->get_items( $current_page, $per_page );

        /**
         * Pagination.
         */
        $this->set_pagination_args( array(
            'total_items' => $this->max_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $this->max_items / $per_page )
        ) );
    } 
    
    public function display() {        
        if( isset( $_REQUEST['view_record'] ) && is_numeric( $_REQUEST['view_record']  ) ) {
            $this->record_listed( $_REQUEST['view_record']  );
            return;
        }
        
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
    <thead>
    <tr>
        <?php $this->print_column_headers(); ?>
    </tr>
    </thead>

    <tbody id="the-list"
        <?php
        if ( $singular ) {
            echo " data-wp-lists='list:$singular'";
        }
        ?>
        >
        <?php $this->display_rows_or_placeholder(); ?>
    </tbody>

    <tfoot>
    <tr>
        <?php $this->print_column_headers( false ); ?>
    </tr>
    </tfoot>

</table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
    
    public function record_listed( $entry_id ) {
        global $wpdb;
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
        
        $activity = $wpdb->tradepress_calls;
        $meta = $wpdb->tradepress_meta;
        $records = $wpdb->get_row( "
            SELECT a.*,m.metavalue AS rawbody  
            FROM $activity a
            INNER JOIN $meta m   
            ON a.entryid = m.entryid
            WHERE a.entryid = $entry_id 
        ", 
        'OBJECT' );
                          
        if( !isset( $records ) || !is_object( $records ) ) { 
            _e( 'Record not found', 'tradepress' );
            return;
        } 
        
        echo '
        <table>
            <tr><td></td><td>' . __( '<strong>Information</strong>', 'tradepress' ) . '</td></tr>
            <tr><td>' . __( 'Entry ID', 'tradepress' ) . '</td><td>' . $records->entryid . '</td></tr>
            <tr><td>' . __( 'Call ID', 'tradepress' ) . '</td><td>' . $records->callid . '</td></tr>
            <tr><td>' . __( 'Service', 'tradepress' ) . '</td><td>' . $records->service . '</td></tr>
            <tr><td>' . __( 'Type', 'tradepress' ) . '</td><td>' . $records->type . '</td></tr>
            <tr><td>' . __( 'Status', 'tradepress' ) . '</td><td>' . $records->status . '</td></tr>
            <tr><td>' . __( 'File', 'tradepress' ) . '</td><td><a href="' . $records->file . '">' . $records->file . '</a></td></tr>
            <tr><td>' . __( 'Function', 'tradepress' ) . '</td><td>' . $records->function . '</td></tr>
            <tr><td>' . __( 'Line', 'tradepress' ) . '</td><td>' . $records->line . '</td></tr>
            <tr><td>' . __( 'WP User ID', 'tradepress' ) . '</td><td>' . $records->wpuserid . '</td></tr>
            <tr><td>' . __( 'Timestamp', 'tradepress' ) . '</td><td>' . $records->timestamp . '</td></tr>
            <tr><td>' . __( 'Description', 'tradepress' ) . '</td><td>' . $records->description . '</td></tr>
            <tr><td>' . __( 'Outcome', 'tradepress' ) . '</td><td>' . $records->outcome . '</td></tr>
            <tr><td>' . __( 'Life', 'tradepress' ) . '</td><td>' . $records->life . '</td></tr>
            <tr><td>' . __( 'Rawbody', 'tradepress' ) . '</td><td>' . $records->rawbody . '</td></tr>
        </table>
        ';
  
    }
    
}
            
