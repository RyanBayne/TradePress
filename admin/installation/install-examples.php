<?php
/** 
* TradePress Install Example Pages
* 
* This class will install many example pages including an index page that
* lists all other pages created for easier browsing. 
* #TODO: Add a way to remove these pages quickly, maybe a quick tool. 
* TODO: if the plugin is being shared with others, update this file to install API related shortcodes only and remove the old Twitch.tv related ones. 
* @package TradePress
* @author Ryan Bayne   
* @since 1.1
*/

if ( ! defined( 'ABSPATH' ) ) { 
    throw new Exception('Direct access not allowed');
}
if ( ! class_exists( 'TradePress_Install_Examples' ) ) :

class TradePress_Install_Examples {
    public static function everything() {
        $pages_array = self::pages_array();
        foreach( $pages_array as $key => $page ) {
            $page['post_type'] = 'page';
            $page['post_author'] = 1;
            $page['post_status'] = 'private';
            $pages_array[$key]['post_id'] = wp_insert_post( $page );
        }
  
        // Generate content for an index page...
        $index = '<ol>';
        foreach( $pages_array as $key => $page ) {
            $url = get_post_permalink( $page['post_id'], true );
            $index .= '<li><a href="' . $url . '">'. get_the_title( $page['post_id'] ) . '</a></li>';        
        }
        $index .= '</ol>';
        
        // Create the index page... 
        $id = wp_insert_post( array(
              'post_title'    => 'TradePress Examples Index',
              'post_content'  => $index,
              'post_status'   => 'private',
              'post_author'   => 1,
            )
        ); 
    }
    
    public static function pages_array() {
        return array(
            array(
              'post_title'    => 'TradePress Example: Login',
              'post_content'  => self::example_content_login(),
            ),            
            array(
              'post_title'    => 'TradePress Example: Video and Chat',
              'post_content'  => self::example_content_embedeverything(),
            ), 
            array(
              'post_title'    => 'TradePress Example: Random Channels List',
              'post_content'  => self::example_content_channel_list(),
            ),            
            array(
              'post_title'    => 'TradePress Example: Ambassadors Team Channel List',
              'post_content'  => self::example_content_channel_list_team(),
            ),               
            array(
              'post_title'    => 'TradePress Example: Display Game Information',
              'post_content'  => self::example_content_get_game(),
            ),
            array(
              'post_title'    => 'TradePress Example: Basic Clips List',
              'post_content'  => self::example_content_get_clips(),
            ),
            array(
              'post_title'    => 'TradePress Example: Display Videos',
              'post_content'  => self::example_content_videos(),
            ),
            array(
              'post_title'    => 'TradePress Example: Top Games List',
              'post_content'  => self::example_content_top_games_list(),
            ),
            array(
              'post_title'    => 'TradePress Example: Display Channel Status',
              'post_content'  => self::example_content_channel_status(),
            ),
            array(
              'post_title'    => 'TradePress Example: Display Status Line',
              'post_content'  => self::example_content_status_line(),
            ),
            array(
              'post_title'    => 'TradePress Example: Status Box',
              'post_content'  => self::example_content_status_box(),
            ),
            array(
              'post_title'    => 'TradePress Example: Twitch Connect Button',
              'post_content'  => self::example_content_twitch_connect_button(),
            ),
            array(
              'post_title'    => 'TradePress Example: Follower Only Content',
              'post_content'  => self::example_content_followers_only(),
            ),  
            array(
              'post_title'    => 'TradePress Example: Live Stream Default Content Multiple Videos',
              'post_content'  => self::example_content_live_stream_default_videos(),
            ),  
            array(
              'post_title'    => 'TradePress Example: Live Stream Default Content Single Video',
              'post_content'  => self::example_content_live_stream_default_video(),
            ),  
                               
        );
    }
    
    public static function example_content_login() {
        return '[TradePress_connect_button]';   
    }    
    
    public static function example_content_embedeverything() {
        return sprintf( '[TradePress_embed_everything channel="%s"]', TradePress_get_main_channels_name() );   
    }
       
    public static function example_content_channel_list() {
        return '[TradePress_shortcodes type="team" team="test" shortcode="channel_list"]';   
    }
       
    public static function example_content_channel_list_team() {
      return '[TradePress_shortcodes type="team" team="ambassadors" shortcode="channel_list"]';   
    }

    public static function example_content_get_game() {
        return '[TradePress_shortcodes shortcode="get_game" refresh="500" game_name="Conan Exiles"]';   
    }

    public static function example_content_get_clips() {
        return '[TradePress_shortcodes shortcode="get_clips" refresh="500" broadcaster_id="120841817"]';   
    }

    public static function example_content_videos() {
        return sprintf( '[TradePress_videos user_id="%s"]', TradePress_get_main_channels_twitchid() );   
    }

    public static function example_content_top_games_list() {
        return '[TradePress_get_top_games_list total="5"]';   
    }

    public static function example_content_channel_status() {
        return sprintf( '[TradePress_channel_status channel_name="%s"]', TradePress_get_main_channels_name() );   
    }

    public static function example_content_status_line() {
        return sprintf( '[TradePress_channel_status_line channel_id="%s"]', TradePress_get_main_channels_twitchid() );   
    }

    public static function example_content_status_box() {
        return sprintf( '[TradePress_shortcode_channel_status_box channel_id="%s"]', TradePress_get_main_channels_twitchid() );   
    }

    public static function example_content_twitch_connect_button() {
        return '[TradePress_connect_button]';   
    }

    public static function example_content_followers_only() {
        return '[TradePress_followers_only]Some content for Twitch.tv followers only.[/TradePress_followers_only]';   
    }
    
    public static function example_content_live_stream_default_videos() {
        return '[TradePress_embed_everything channel="LOLinDark1" defaultcontent="videos"]';   
    }
    
    public static function example_content_live_stream_default_video() {
        return '[TradePress_embed_everything channel="LOLinDark1" defaultcontent="video" videoid="1040648073"]';   
    }
    
}

endif;

