<?php
/**
 * Installation class only - both roles and capabilities are installed. 
 * 
 * When possible we will add roles and capabilities required for extensions to 
 * avoid duplicate capabilities that offer different levels of aaccess. 
 *
 * @package  TradePress/ Classes
 * @category Class
 * @author   Ryan Bayne
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TradePress_Roles_Capabilities_Installation' ) ) :

class TradePress_Roles_Capabilities_Installation {

    /**
    * Array of all default roles and capabilities...
    * 
    * @version 2.0
    */
    public function fullarray() {
        $array = array();
                  
        // Main Twitch Channel Editor 
        $array['TradePress_role_main_channel_editor'] = array(
            'title' => __( 'Main Channel Editor', 'tradepress' ),
            'desc'  => __( '', 'tradepress' ),
            'caps'  => array(        
                'TradePress_edit_stream_info'         => array( 'title' => __( 'Edit Stream Status', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_run_commercials'          => array( 'title' => __( 'Run Commercials', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_edit_video_info'          => array( 'title' => __( 'Edit Videos', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_upload_videos'            => array( 'title' => __( 'Upload Videos', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_create_events'            => array( 'title' => __( 'Create Events', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_start_reruns'             => array( 'title' => __( 'Start Reruns', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_start_premiers'           => array( 'title' => __( 'Star Premiers', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_download_past_broadcasts' => array( 'title' => __( 'Download Past Broadcasts', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
            )
        );
        $array['TradePress_role_main_channel_editor'] = apply_filters( 'TradePress_role_main_channel_editor', $array['TradePress_role_main_channel_editor'] );
        
        // Main Twitch Channel Moderator 
        $array['TradePress_role_main_channel_moderator'] = array(
            'title' => __( 'Main Channel Moderator', 'tradepress' ),
            'desc'  => __( '', 'tradepress' ),
            'caps'  => array(        
                'TradePress_time_out_users'             => array( 'title' => __( 'Time Out Users', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_ban_users'                  => array( 'title' => __( 'Ban Users', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_control_slow_mode'          => array( 'title' => __( 'Control Slow Mode', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_control_sub_chat_mode'      => array( 'title' => __( 'Control Chat Mod', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_control_follower_chat_mode' => array( 'title' => __( 'Control Follower Chat Mode', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
            )
        );
        $array['TradePress_role_main_channel_moderator'] = apply_filters( 'TradePress_role_main_channel_moderator', $array['TradePress_role_main_channel_moderator'] );
        
        // Main Twitch Channel VIP 
        $array['TradePress_role_main_channel_vip'] = array(
            'title' => __( 'Main Channel Editor', 'tradepress' ),
            'desc'  => __( '', 'tradepress' ),
            'caps'  => array(
                'TradePress_no_slow_mode'   => array( 'title' => __( 'Slow Mode Immunity', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_sub_only'       => array( 'title' => __( 'Subscribers Only Access', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_followers_only' => array( 'title' => __( 'Followers Only Access', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_all_chat_rooms' => array( 'title' => __( 'All Chat Rooms', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
                'TradePress_chat_links'     => array( 'title' => __( 'Post Chat Links', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
            )
        );
        $array['TradePress_role_main_channel_vip'] = apply_filters( 'TradePress_role_main_channel_vip', $array['TradePress_role_main_channel_vip'] );
        
        // Main Channel Twitch Subscriber Plan 1000
        $array['TradePress_role_subplan_1000'] = array(
            'title' => __( 'Tier One Subscriber', 'tradepress' ),
            'desc'  => __( '', 'tradepress' ),
            'caps'  => array(        
                //'TradePress_subplan_1000' => array( 'title' => __( '', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
            )
        );
        $array['TradePress_role_subplan_1000'] = apply_filters( 'TradePress_role_subplan_1000', $array['TradePress_role_subplan_1000'] );
        
        // Main Channel Twitch Subscriber Plan 2000
        $array['TradePress_role_subplan_2000'] = array(
            'title' => __( 'Tier Two Subscriber', 'tradepress' ),
            'desc'  => __( '', 'tradepress' ),
            'caps'  => array(        
                //'TradePress_subplan_2000' => array( 'title' => __( '', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
            )
        );
        $array['TradePress_role_subplan_2000'] = apply_filters( 'TradePress_role_subplan_2000', $array['TradePress_role_subplan_2000'] );
        
        // Main Channel Twitch Subscriber Plan 3000
        $array['TradePress_role_subplan_3000'] = array(
            'title' => __( 'Tier Three Subscriber', 'tradepress' ),
            'desc'  => __( '', 'tradepress' ),
            'caps'  => array(        
                //'TradePress_subplan_3000' => array( 'title' => __( '', 'tradepress' ), 'desc' => __( '', 'tradepress' ) ),
            )
        ); 
        $array['TradePress_role_subplan_3000'] = apply_filters( 'TradePress_role_subplan_3000', $array['TradePress_role_subplan_3000'] );
                
        return $array;
    }

    public function add_roles_and_capabilities() {
        $full_array = $this->fullarray();
                           
        foreach( $full_array as $role => $role_array ) 
        {
            $capabilities_array = array(); 
            
            foreach( $role_array['caps'] as $capability => $cap_array )
            {
                $capabilities_array[] = $capability;

                global $wp_roles;
                $wp_roles->add_cap( $role, $capability ); 
            }
            
            add_role( $role, $role_array['title'], $capabilities_array );
        }
    }
    
    public function remove_roles_and_capabilities() { 
        $full_array = $this->fullarray();
        
        foreach( $full_array as $role => $role_array )
        {
            foreach( $role_array['caps'] as $capability => $cap_array )
            {
                $capabilities_array[] = $capability;

                global $wp_roles;
                $wp_roles->remove_cap( $role, $capability ); 
            }
                        
            if( get_role( $role ) ){
                remove_role( $role );
            }
        }
    }

}

endif;