<?php
/**
 * TradePress - Frontend Notices 
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TradePress/Notices
 * @since    1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} 

add_action( 'wp_head', 'TradePress_display_frontend_notices', 1 );
function TradePress_display_frontend_notices() {     
    add_filter( 'the_content', 'TradePress_display_frontend_notices_the_content' );
}

function TradePress_frontend_notice_types() {
    return array( 'error', 'success', 'warning', 'info' );
}

/**
* Builds a single notice for frontend output
* 
* Use $_GET values to trigger the display of a specific notice
* 
* Transient can be used during a procedure to store dynamic values for use within
* notice stranges after redirect
*                        
* @param mixed $post_content
* 
* @version 1.0
*/
function TradePress_display_frontend_notices_the_content( $post_content ) {  
    global $GLOBALS;
                                              
    if( !isset( $_GET['TradePress_notice'] ) || !is_string( $_GET['TradePress_notice'] ) ) { return $post_content; }
    elseif( !isset( $_GET['source'] ) || !is_string( $_GET['source'] ) ) { return $post_content; }
    elseif( !isset( $_GET['key'] ) || !is_numeric( $_GET['key'] ) ) { return $post_content; }

    // Get our frontend notice from public-notices.php
    $the_message = $GLOBALS['TradePress']->public_notices->get_message_by_id( $_GET['source'], $_GET['key'] );
    
    // If title or info contain placeholders, get the short life transient holding the applicable values. 
    if( strstr( $the_message[ 'title'], '%s' ) || strstr( $the_message[ 'info'], '%s' ) ) 
    {
        // Get values stored in transient, required for inserting into messages.
        $transient = get_transient( 'TradePress_public_notice_values_' . $_GET['source'] . $_GET['key'] );
        
        $the_message[ 'title'] = sprintf( $the_message[ 'title'], $transient['title_values'] );        
        $the_message[ 'info'] = sprintf( $the_message[ 'info'], $transient['info_values'] );        
    }
                           
    $content = "
    <h3>" . esc_html( $the_message[ 'title'] ) . "</h3>
    <p class='TradePress-frontend-message'>
    " . esc_html( $the_message[ 'info'] ) . "
    </p>" . $post_content;
    
    // Remove the action calling this function once it's run, to prevent it running elsewhere.
    remove_filter( 'the_content', 'TradePress_display_frontend_notices_the_content', 5 );
    remove_filter( 'post_updated', 'TradePress_display_frontend_notices_the_content', 5 );
    
    return $content;
}                   