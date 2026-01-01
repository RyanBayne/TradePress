<?php 

/**
 * Get endpoint data by endpoint ID
 * 
 * @param int $endpoint_id The ID of the endpoint
 * @return object|null The endpoint data or null if not found
 */
function TradePress_db_get_endpoint( $endpoint_id ) {
    global $wpdb;
    return TradePress_db_selectrow( $wpdb->prefix . 'tradepress_endpoints', "id = " . intval( $endpoint_id ), '*' );
}

/**
 * Get endpoint data by endpoint name
 * 
 * @param string $endpoint_name The name of the endpoint
 * @return object|null The endpoint data or null if not found
 */
function TradePress_db_get_endpoint_by_name( $endpoint_name ) {
    global $wpdb;
    // First check if name column exists
    $name_column = 'name';
    $columns = TradePress_db_get_tablecolumns($wpdb->prefix . 'tradepress_endpoints', true, true);
    if (is_array($columns)) {
        if (in_array('endpoint_name', $columns)) {
            $name_column = 'endpoint_name';
        }
    }
    
    return TradePress_db_selectrow( $wpdb->prefix . 'tradepress_endpoints', "$name_column = '" . esc_sql( $endpoint_name ) . "'", '*' );
}

/**
 * Store a new endpoint in the database
 * 
 * @param array $endpoint_data Array of endpoint data
 * @return int The ID of the inserted endpoint
 */
function TradePress_db_store_endpoint( $endpoint_data ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tradepress_endpoints';
    
    // Ensure counter starts at 0 if not specified
    if ( !isset( $endpoint_data['counter'] ) ) {
        $endpoint_data['counter'] = 0;
    }
    
    return TradePress_db_insert( $table_name, $endpoint_data );
}

/**
 * Update an existing endpoint in the database
 * 
 * @param int $endpoint_id The ID of the endpoint to update
 * @param array $endpoint_data Array of endpoint data to update
 * @return int|false The number of rows updated, or false on error
 */
function TradePress_db_update_endpoint( $endpoint_id, $endpoint_data ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tradepress_endpoints';
    return TradePress_db_update( $table_name, "id = " . intval( $endpoint_id ), $endpoint_data );
}

/**
 * Increment the usage counter for an endpoint
 * 
 * @param int|string $endpoint_identifier The ID or name of the endpoint
 * @return int|false The number of rows updated, or false on error
 */
function TradePress_db_increment_endpoint_counter( $endpoint_identifier ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tradepress_endpoints';
    
    // Check if the identifier is numeric (ID) or string (name)
    if ( is_numeric( $endpoint_identifier ) ) {
        $condition = "id = " . intval( $endpoint_identifier );
    } else {
        $condition = "endpoint_name = '" . esc_sql( $endpoint_identifier ) . "'";
    }
    
    // Use direct SQL to increment the counter
    return $wpdb->query( "UPDATE $table_name SET counter = counter + 1 WHERE $condition" );
}

/**
 * Get the usage count for an endpoint
 * 
 * @param int|string $endpoint_identifier The ID or name of the endpoint
 * @return int The usage count or 0 if endpoint not found
 */
function TradePress_db_get_endpoint_counter( $endpoint_identifier ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tradepress_endpoints';
    
    // Check if the identifier is numeric (ID) or string (name)
    if ( is_numeric( $endpoint_identifier ) ) {
        $condition = "id = " . intval( $endpoint_identifier );
    } else {
        $condition = "endpoint_name = '" . esc_sql( $endpoint_identifier ) . "'";
    }
    
    $result = $wpdb->get_var( "SELECT counter FROM $table_name WHERE $condition" );
    return $result !== null ? intval( $result ) : 0;
}

/**
 * Reset the counter for a specific endpoint
 * 
 * @param int|string $endpoint_identifier The ID or name of the endpoint
 * @return int|false The number of rows updated, or false on error
 */
function TradePress_db_reset_endpoint_counter( $endpoint_identifier ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tradepress_endpoints';
    
    // Check if the identifier is numeric (ID) or string (name)
    if ( is_numeric( $endpoint_identifier ) ) {
        $condition = "id = " . intval( $endpoint_identifier );
    } else {
        $condition = "endpoint_name = '" . esc_sql( $endpoint_identifier ) . "'";
    }
    
    return TradePress_db_update( $table_name, $condition, array( 'counter' => 0 ) );
}

/**
 * Reset all endpoint counters to zero
 * 
 * @return int|false The number of rows updated, or false on error
 */
function TradePress_db_reset_all_endpoint_counters() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tradepress_endpoints';
    return $wpdb->query( "UPDATE $table_name SET counter = 0" );
}

/**
 * Get all endpoints with their usage counts
 * 
 * @param string $orderby Column to order by (default: primary key)
 * @param string $sort Sort direction (ASC or DESC)
 * @return array Array of endpoint objects
 */
function TradePress_db_get_all_endpoints( $orderby = '', $sort = 'ASC' ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tradepress_endpoints';
    
    // First check if the table exists
    if (!TradePress_db_does_table_exist($table_name)) {
        return array();
    }
    
    // Get actual table columns to validate the orderby column
    $columns = TradePress_db_get_tablecolumns($table_name, true, true);
    
    if (!is_array($columns) || empty($columns)) {
        // If we can't get columns, return empty array
        return array();
    }
    
    // If no specific orderby column is provided, try to find the primary key
    if (empty($orderby)) {
        // Try common primary key names
        $primary_keys = array('id', 'endpoint_id', 'ID', 'endpoint_key');
        foreach ($primary_keys as $key) {
            if (in_array($key, $columns)) {
                $orderby = $key;
                break;
            }
        }
        
        // If no primary key found, use first column
        if (empty($orderby) && !empty($columns[0])) {
            $orderby = $columns[0];
        }
    } else {
        // Validate the orderby column to prevent SQL injection
        if (!in_array($orderby, $columns)) {
            // Try to find a suitable column if the requested one doesn't exist
            if (in_array('id', $columns)) {
                $orderby = 'id';
            } elseif (in_array('endpoint_name', $columns)) {
                $orderby = 'endpoint_name';
            } elseif (!empty($columns[0])) {
                $orderby = $columns[0];
            } else {
                return array(); // No valid columns found
            }
        }
    }
    
    // Validate sort direction
    $sort = strtoupper($sort);
    if ($sort !== 'ASC' && $sort !== 'DESC') {
        $sort = 'ASC';
    }
    
    // Make sure we have an orderby column at this point
    if (empty($orderby)) {
        return array();
    }
    
    return TradePress_db_selectorderby($table_name, null, "$orderby $sort");
}
