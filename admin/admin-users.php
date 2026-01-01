<?php
/**
* TradePress Users
*
* @author Ryan Bayne
* @package TradePress
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' ); 

if ( ! class_exists ( 'TradePress_Admin_Users' ) ) :

class TradePress_Admin_Users {

    /**
     * Admin_Users constructor...
     * 
     * @version 1.0
     */
    function __construct() {
        if( get_option( 'TradePress_twitchsubscribers_switch') !== 'yes' ){ return false; }
        add_filter( 'pre_user_query', array( $this, 'sort_by_newest' ) );
        add_filter( 'pre_user_query', array( $this, 'filter_users_by_status' ) );
        add_filter( 'views_users', array( $this, 'add_status_links' ) );
        add_action( 'um_admin_user_action_hook', array( $this, 'user_action_hook' ), 10, 1 );
    }

    /**
     * Does an action to user asap
     *
     * @param string $action
     * 
     * @version 1.0
     */
    function user_action_hook( $action ) {
        switch ( $action ) {
            default:

                break;
 
            case 'twitchsync':

                break;
        }
    }

    /**
     * Change default sorting...
     *
     * @param $query
     * @return mixed
     * 
     * @version 1.0
     */
    function sort_by_newest( $query ) {
        global $pagenow;

        if ( is_admin() && $pagenow == 'users.php' ) {
            if ( ! isset( $_REQUEST['orderby'] ) ) {
                $query->query_vars["order"] = 'desc';
                $query->query_orderby = " ORDER BY user_registered " . ( $query->query_vars["order"] == 'desc' ? 'desc ' : 'asc ' ); //set sort order
            }
        }

        return $query;
    }

    /**
     * Filter WP users by Twitch subscription status...
     *
     * @param $query
     * @return mixed
     * 
     * @version 1.0
     */
    function filter_users_by_status( $query ) {
        global $wpdb, $pagenow;

        if ( is_admin() && $pagenow == 'users.php' && ! empty( $_GET['TradePress_status'] ) ) {

            $status = sanitize_key( $_GET['TradePress_status'] );

            $meta_key = 'TradePress_sub_plan_' . TradePress_get_main_channels_twitchid();
            
            $query->query_where = str_replace('WHERE 1=1',
                "WHERE 1=1 AND {$wpdb->users}.ID IN (
                SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta
                WHERE {$wpdb->usermeta}.meta_key = '{$meta_key}'
                AND {$wpdb->usermeta}.meta_value = '{$status}')",
                $query->query_where
            );

        }

        return $query;
    }

    /**
     * Add status links to WP Users List Table...
     *
     * @param $views
     * @return array|mixed|void
     * 
     * @version 1.0
     */
    function add_status_links( $views ) {

        $custom = array(
            'twitchsubs' => __( 'Twitch Subscribers', 'tradepress' ),
            '1000'       => __( 'Twitch Sub Tier 1', 'tradepress' ),
            '2000'       => __( 'Twitch Sub Tier 2', 'tradepress' ),
            '3000'       => __( 'Twitch Sub Tier 3', 'tradepress' ),
            'Prime'      => __( 'Twitch Sub Prime', 'tradepress' ),
        );

        foreach ( $custom as $k => $v ) {
            if ( isset( $_REQUEST['TradePress_status'] ) && sanitize_key( $_REQUEST['TradePress_status'] ) == $k ) {
                $current = 'class="current"';
            } else {
                $current = '';
            }

            $href = esc_url( admin_url( 'users.php' ) . '?TradePress_status=' . $k );
            $views[ $k ] = '<a href="' . $href  . '" ' . $current . '>' . $v . ' <span class="count">(' . TradePress_count_users_by_status( $k ) . ')</span></a>';
        }
                    
        return $views;
    }   
}

endif;