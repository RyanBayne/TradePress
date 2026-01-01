<?php
/**
* Listens for $_POST activity from Twitch.tv and stores the event for
* processing later by the background processing class...
* 
* This is called as add_action() in loader.php currently...
* 
* @version 1.0
*/
function TradePress_webhooks_eventsub_listener() {
    if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset( $_GET['webhook'] ) || $_GET['webhook'] == 'tradepress_eventsub_notification' ){ return; }
        
    // Expecting valid json...
    if (json_last_error() !== JSON_ERROR_NONE) { return; }
  
    $data = file_get_contents( 'php://input' );
    $events = json_decode( $data, true );
    
    /*
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

    $headers = getallheaders();
    var_dump($headers['Content-Name']);

    public function webhook(Request $request) {
    $json = file_get_contents('php://input');
    Storage::disk('local')->put('file.txt', $json);
    Storage::disk('local')->put('request.txt', Request::header('x-wc-webhook-source'));
    */

    foreach ( $events as $event ) {
        TradePress_webhooks_eventsub_store_event($event);
    }
}

function TradePress_webhooks_eventsub_store_event( $event ) {
    //$caching = new TradePress_Webhooks_Caching( file_location, file_name, 'txt' );
 
    # check event and if a Twitch.tv notification save to cache 
    //$caching->save( $event );     
    
    # then queue the event using background processing 
    
    # TradePress_webhooks_eventsub_queue_event(); 
}

function TradePress_webhooks_eventsub_queue_event() {
     # use aysnc-request.php and background-process.php to fully process notification 
}

function TradePress_webhooks_ready() {
    global $wpdb;
    if( !isset( $wpdb->webhooksmeta ) || !TradePress_db_does_table_exist( $wpdb->prefix . 'webhooksmeta' ) ) {
        return false;            
    } else {
        return true;
    }
}

/**
* Webhook services are not be ready until manual installation is run... 
* 
* @version 1.0
*/
function TradePress_webhooks_activate_service() {              
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    TradePress_create_table_webhooks_meta();   
}

/**
* Creates a meta data table for webhooks...
* 
* @version 1.0
*/
function TradePress_create_table_webhooks_meta() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'webhooksmeta';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $max_index_length = 191;
    
    $install_query = "CREATE TABLE $table_name (
        meta_id bigint(20) unsigned NOT NULL auto_increment,
        webhook_id bigint(20) unsigned NOT NULL default '0',
        meta_key varchar(255) default NULL,
        meta_value longtext,
        PRIMARY KEY  (meta_id),
        KEY webhook_id (webhook_id),
        KEY meta_key (meta_key($max_index_length))
    ) $charset_collate;";
    
    dbDelta( $install_query );
}

/**
 * Adds meta data field to a webhook.
 *
 * @param int    $webhook_id  Webhook ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value.
 * @param bool   $unique     Optional, default is false. Whether the same key should not be added.
 * @return int|false Meta ID on success, false on failure.
 */
function add_webhook_meta($webhook_id, $meta_key, $meta_value, $unique = false) {
    return add_metadata( 'webhook', $webhook_id, $meta_key, $meta_value, $unique );
}

/**
 * Removes metadata matching criteria from a webhook.
 *
 * @param int    $webhook_id    Webhook ID
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value.
 * @return bool True on success, false on failure.
 */
function delete_webhook_meta($webhook_id, $meta_key, $meta_value = '') {
    return delete_metadata( 'webhook', $webhook_id, $meta_key, $meta_value );
}

/**
 * Retrieve meta field for a webhook.
 *
 * @param int    $badge_id Webhook ID.
 * @param string $key     Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool   $single  Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function get_webhook_meta($webhook_id, $key = '', $single = false) {
    return get_metadata( 'webhook', $webhook_id, $key, $single );
}

/**
 * Update webhook meta field based on webhook ID.
 *
 * @param int    $webhook_id   Webhook ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function update_webhook_meta($webhook_id, $meta_key, $meta_value, $prev_value = '') {
    return update_metadata( 'webhook', $webhook_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Integrates webhooksmeta table with $wpdb...
 *
 * @version 1.0
 */
function TradePress_integrate_wpdb_webhooksmeta() {
    global $wpdb;        
    $wpdb->webhooksmeta = $wpdb->prefix . 'webhooksmeta';
    $wpdb->tables[] = 'webhooksmeta';
    return;
}