<?php
/**
 * TradePress - Frontend only functions. 
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TradePress/Notices
 * @since    1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}                    
     
################################################################################
#                                                                              #
#                             LIVE MENU FUNCTIONS                              #
#                                                                              #
################################################################################
function _custom_nav_menu_item( $title, $url, $order, $parent = 0 ){            
  $item = new stdClass();
  $item->ID = 1000000 + $order + $parent;
  $item->db_id = $item->ID;
  $item->title = $title;
  $item->url = $url;
  $item->menu_order = $order;
  $item->menu_item_parent = $parent;
  $item->type = '';
  $item->object = '';
  $item->object_id = '';
  $item->classes = array();
  $item->target = '';
  $item->attr_title = '';
  $item->description = '';
  $item->xfn = '';
  $item->status = '';
  return $item;
}

function custom_nav_menu_items( $items, $menu ){       
  // only add item to a specific menu
  if ( $menu->slug == 'main' ){
                                        
    // only add profile link if user is logged in
    if ( get_current_user_id() ){          
      $items[] = _custom_nav_menu_item( 'My Profile', get_author_posts_url( get_current_user_id() ), 3 ); 
    }
  }
    
  return $items;
}

function custom_nav_menu_items2( $items, $menu ) {          
  if ( $menu->slug == 'main' ) {
    $top = _custom_nav_menu_item( 'Live Members', '/some-url', 100 );

    $items[] = $top;
    $items[] = _custom_nav_menu_item( 'nookyyy', 'http://https://www.twitch.tv/sacriel', 101, $top->ID );
    $items[] = _custom_nav_menu_item( 'Sacriel', 'https://www.twitch.tv/sacriel', 103, $top->ID );
    $items[] = _custom_nav_menu_item( 'BadNewsBaron', 'https://www.twitch.tv/badnewsbaron', 102, $top->ID );
  }

  return $items;
}

// Activation is required and is done in plugin settings...
if( 'yes' == get_option( "TradePress_livemenu_switch" ) ) {
    if( get_option( 'TradePress_livemenu_teamid' ) ) {
        add_filter( 'wp_get_nav_menu_items', 'custom_nav_menu_items', 20, 2 ); 
        add_filter( 'wp_get_nav_menu_items', 'custom_nav_menu_items2', 20, 2 );        
    }
}