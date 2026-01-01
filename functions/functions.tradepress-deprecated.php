<?php
/**
 * TradePress - Deprecated Functions
 *
 * Please add the WordPress core function for triggering and error if a
 * Deprecated function is used. 
 * 
 * Use: _deprecated_function( 'TradePress_function_called', '2.1', 'TradePress_replacement_function' );  
 *
 * @author   Ryan Bayne
 * @category Core
 * @package  TradePress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} 
  
/**
 * @deprecated example only
 */
function TradePress_function_called() {
    _deprecated_function( 'TradePress_function_called', '2.1', 'TradePress_replacement_function' );
    //TradePress_replacement_function();
}