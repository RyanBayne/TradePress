<?php
/**
 * TradePress - Primary Sidebar Widgets File
 *
 * @author   Ryan Bayne
 * @category Widgets
 * @package  TradePress/Widgets
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include widget classes.
//include_once( 'abstracts/abstract-TradePress-widget.php' );

/**
 * Register Widgets.
 */
function TradePress_register_widgets() {
    //register_widget( 'TradePress_Widget_Example' );
}
add_action( 'widgets_init', 'TradePress_register_widgets' );