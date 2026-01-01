<?php
/**
 * Functions that directly integrate with the WP core and enhance WP 
 * most common UI features...
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress
 * @version  1.0
 */

function TradePress_integration() {
    add_filter( 'plugin_action_links_' . TRADEPRESS_PLUGIN_BASENAME, 'TradePress_plugin_action_links' );
    add_filter( 'plugin_row_meta', 'TradePress_plugin_row_meta', 10, 2 );
    
    // Register post types
    TradePress_Post_types::register_post_types();
    TradePress_Post_types::register_taxonomies();    
}

                       
/**
 * Show action links on the plugin screen.
 *
 * @param    mixed $links Plugin Action links
 * @return    array
 * 
 * @version 1.2
 */
function TradePress_plugin_action_links( $links ) {
    $action_links = array(
        'settings' => '<a href="' . admin_url( 'admin.php?page=TradePress' ) . '" title="' . esc_attr( __( 'View TradePress Settings', 'tradepress' ) ) . '">' . __( 'Settings', 'tradepress' ) . '</a>',
        'wizard' => '<a href="' . admin_url( 'index.php?page=tradepress-setup' ) . '" title="' . esc_attr( __( 'Start TradePress Setup Wizard', 'tradepress' ) ) . '">' . __( 'Setup Wizard', 'tradepress' ) . '</a>',
    );

    return array_merge( $action_links, $links );
}

/**
 * Show row meta on the plugin screen.
 *
 * @param    mixed $links Plugin Row Meta
 * @param    mixed $file  Plugin Base file
 * @return    array
 * 
 * @version 1.0
 */
function TradePress_plugin_row_meta( $links, $file ) {     
    if ( $file == TRADEPRESS_PLUGIN_BASENAME ) {
        $row_meta = array(
            'discord' => '<a href="' . esc_url( apply_filters( 'TradePress_support_url', 'https://discord.gg/ScrhXPE' ) ) . '" title="' . esc_attr( __( 'Visit Discord for support', 'tradepress' ) ) . '">' . __( 'Discord', 'tradepress' ) . '</a>',
            'github'  => '<a href="' . esc_url( apply_filters( 'TRADEPRESS_GITHUB_url', 'https://github.com/RyanBayne/TradePress/issues' ) ) . '" title="' . esc_attr( __( 'Visit Project GitHub', 'tradepress' ) ) . '">' . __( 'GitHub', 'tradepress' ) . '</a>',
            'donate'  => '<a href="' . esc_url( apply_filters( 'TRADEPRESS_DONATE_url', TRADEPRESS_DONATE ) ) . '" title="' . esc_attr( __( 'Donate to Project', 'tradepress' ) ) . '">' . __( 'Donate', 'tradepress' ) . '</a>',
            'twitch'  => '<a href="https://twitch.tv/lolindark1" title="' . esc_attr( __( 'Donate to Project', 'tradepress' ) ) . '">' . __( 'Twitch', 'tradepress' ) . '</a>',
            'blog'    => '<a href="http://TradePress.wordpress.com" title="' . esc_attr( __( 'Get project updates from the blog.', 'tradepress' ) ) . '">' . __( 'Blog', 'tradepress' ) . '</a>',
        );

        return array_merge( $links, $row_meta );
    }

    return (array) $links;
}

/**
* Adds a step to a BugNet trace. Does what
* function bugnet_add_trace_steps() does.
* 
* @param mixed $code
* @param mixed $description
*/
function TradePress_bugnet_add_trace_steps( $code, $description ) {
    if( 'yes' !== get_option( 'bugnet_activate_tracing' ) ) { return; }
    global $wpdb;
    
    $back_trace = debug_backtrace( false, 1 );

    $wpdb->insert(
        $wpdb->prefix . "bugnet_tracing_steps",
        array(  
            'code'        => $code,
            'request'     => TRADEPRESS_REQUEST_KEY,
            'description' => $description,
            'microtime'   => microtime( true ),
            'line'        => $back_trace[0]['line'],
            'function'    => $back_trace[0]['function']
        )
    );
}