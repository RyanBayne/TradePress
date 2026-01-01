<?php
/**
 * TradePress - Public Notice Management (does not handle output)
 *     
 * Do not confuse this files contents with the notices classes/functions which provide
 * the functionality for building and outputting notices. 
 * 
 * This file is purely for managing and accessing the the text.  
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TradePress/Notices
 * @since    1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'TradePress_Public_PreSet_Notices' ) ) :

/**
 * TradePress Class for accessing messages 
 * and registering new messages using extensions.
 * 
 * @class    TradePress_Public_PreSet_Notices
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/UI
 * @version  1.0.0
 */
class TradePress_Public_PreSet_Notices {
    public $message_array = array(); 
    
    public $types = array( 'success', 'warning', 'error', 'info' );

    public $messages = array(); 
 
    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    public function __construct() {      
        //$this->messages = $this->message_list();  
        $this->__set('messages', $this->message_list());            
        // Apply filtering by extensions which need to add more messages to the array. 
        apply_filters( 'TradePress_filter_public_notices_array', $this->messages );                           
    }

    /**
    * Get messages with as many filters as possible.
    * 
    * Allow this method to become complex.
    * 
    * @version 1.0
    */
    public function get_messages( $atts ) {
        $args = shortcode_atts( 
            array(
                'minimum_id'   => '0',
                'maximum_id'   => '99999',
                'ignore_types' => array(),
                'contains'     => '',
            ), 
            $atts
        ); 
        
        return $this->messages_array; 
    }
    
    /**
    * Get a message by TRADEPRESS_PLUGIN_BASENAME and the array key for the
    * plugin/extension being queried. 
    * 
    * @param mixed $plugin
    * @param mixed $integer
    * 
    * @version 1.0
    */
    public function get_message_by_id( $plugin, $integer ) {
        return $this->messages[ $plugin ][ $integer ];    
    }
    
    /**
    * None strict search on ALL message values.
    * 
    * @version 1.0
    */
    public function get_message_by_search() {
        
    }
    
    public function get_message_by_title_strict() {
        
    }
    
    public function get_message_by_title_search() {
        
    }
    
    public function get_message_by_info_strict() {
        
    }
    
    public function get_message_by_info_search() {
        
    }

    /**
    * Get message type by integer. 
    * 
    * 0 = success
    * 1 = warning
    * 2 = error
    * 3 = info
    * 
    * @param mixed $integer
    * 
    * @version 1.0
    */
    public function get_type( $type_key ) {
        return $this->types[ $type_key ];
    }
    
    /**
    * List of the public notices available for applicable procedures.
    * 
    * TYPES
    * 0 = success
    * 1 = warning
    * 2 = error
    * 3 = info
    * 
    * @version 1.0
    */
    public function message_list() {      
        $messages_array = array();
        
        /* 0 = success, 1 = warning, 2 = error, 3 = info */
        $messages_array['TradePress'][0] = array( 'type' => 0, 'title' => __( 'No Update Performed', 'tradepress' ), 'info' => __( 'We already have the latest Twitch data from your account.', 'tradepress' ) );

        // Login by Shortcode - search 'key' => 5 to find messages place...
        $messages_array['login'][0] = array( 'type' => 1, 'title' => __( 'Twitch.tv Reply', 'tradepress' ), 'info' => __( 'Twitch said: %s', 'tradepress' ) );        
        $messages_array['login'][1] = array( 'type' => 1, 'title' => __( 'Login Problem', 'tradepress' ), 'info' => __( 'We could not established your original page when you attempted to login. Please try again and report this problem if it continues.', 'tradepress' ) );
        $messages_array['login'][2] = array( 'type' => 1, 'title' => __( 'Twitch Code Missing', 'tradepress' ), 'info' => __( 'Sorry, it appears Twitch.tv returned you without a code. Please try again and report this issue if it happens again.', 'tradepress' ) );

        // Streamlabs (temporary pending filter)
        $messages_array['officialstreamlabsextension'][0] = array( 'type' => 2, 'title' => __( 'No Update Performed', 'tradepress' ), 'info' => __( 'We already have the latest Streamlabs data for you.', 'tradepress' ) );
             
        return $messages_array;
    } 
}

endif;
