<?php
/**
 * TradePress - The object registry provides object access throughout WordPress
 * without using globals.  
 * 
 * This is a singleton class that stores objects in an array and provides access
 * to them via a key.  This is useful for storing objects that need to be accessed
 * in multiple places throughout the WordPress environment.  This class is used
 * to store objects that are used in the TradePress plugin.
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TradePress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
           
if( !class_exists( 'TradePress_Object_Registry' ) ) :

class TradePress_Object_Registry {

    static $storage = array();

    static function add( $id, $class ) {
        self::$storage[ $id ] = $class;
    }

    static function get( $id ) {
        return array_key_exists( $id, self::$storage ) ? self::$storage[$id] : NULL;    
    }
    
    /**
    * Update the variable in the registry object.
    * 
    * @param string $id
    * @param string $var variable name
    * @param mixed $new new variable value
    * @param mixed $old old variable value
    * 
    * @version 2.0
    */
    static function update_var( $id, $var, $new, $old = null ) { 
        self::$storage[$id]->$var = $new;     
    }
    
    /**
    * Update a value already in the registry using add_action and this function...
    * 
    * @param mixed $args
    */
    static function update_var_action( $args ) {
        self::update_var( $args['id'], $args['var'], $args['new'] );    
    }
}

endif;

