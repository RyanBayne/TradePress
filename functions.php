<?php
/**
 * TradePress Core Functions
 * #TODO: Add more information here...
 * #TODO: consider which functions may be moved to the other functions files to reduce the size of this file. Makes it easier for AI to read and make changes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


require_once plugin_basename( 'integration.php' );
require_once plugin_basename( 'functions/functions.tradepress-get.php' );
require_once plugin_basename( 'functions/functions.tradepress-database.php' );
require_once plugin_basename( 'functions/database/functions.tradepress-table-endpoints.php' );

/**
 * Is backend login.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_is_backend_login() {
	$ABSPATH_MY = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, ABSPATH );
	return ( ( in_array( $ABSPATH_MY . 'wp-login.php', get_included_files() ) || in_array( $ABSPATH_MY . 'wp-register.php', get_included_files() ) ) || $GLOBALS['pagenow'] === 'wp-login.php' || $_SERVER['PHP_SELF'] == '/wp-login.php' );
}

/**
 * Used with WP core login form...
 *
 * @param mixed $message
 *
 * @version 1.0
 */
function TradePress_login_error( $message ) {
	$login_messages = new TradePress_Custom_Login_Messages();
	$login_messages->add_error( $message );
	unset( $login_messages );
}

/**
 * Provides the string for populating parent value in Twitch.tv embeds
 *
 * @return void
 * @version 1.0.0
 */
function embed_parent_string() {
	$urlparts = wp_parse_url( home_url() );
	return $urlparts['host'];
}

/**
 * Applies a custom slug using an external source i.e. Twitch channel logo. This
 * approaches ensures that the avatar is always the current one on Twitch without
 * needing an update process.
 *
 * @param mixed $avatar
 * @param mixed $id_or_email
 * @param mixed $size - used for both height and width by default
 * @param mixed $default
 * @param mixed $alt
 * @param mixed $buddypress
 * @param mixed $height - used with BuddyPress hack
 *
 * @version 2.0
 */
function TradePress_filter_slug_get_avatar( $avatar, $id_or_email = null, $size = null, $default = false, $alt = '', $buddypress = false, $height = null ) {

	if ( is_object( $id_or_email ) && isset( $id_or_email->comment_author_email ) ) {
		$user = get_user_by( 'email', $id_or_email->comment_author_email );
		if ( $user ) {
			$id_or_email = $user->ID;
		} else {
			return $avatar; // may be a comment loop and the email address is not registered
		}
	}

	// If is email, try and find user ID...
	if ( ! is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
		if ( $user ) {
			$id_or_email = $user->ID;
		}
	}

	// If still no user ID, return the unfiltered content...
	if ( ! is_numeric( $id_or_email ) ) {
		return $avatar;
	}

	// Find URL of saved avatar in user meta...
	$saved = get_user_meta( $id_or_email, 'TradePress_avatar_url', true );

	// check if it is a URL
	if ( filter_var( $saved, FILTER_VALIDATE_URL ) ) {
		if ( $buddypress ) {
			if ( $alt && is_string( $alt ) ) {
				$alt = ' alt="' . $alt . '"';
			}
			// HACK - The img is being output at 300 despite styles indicating otherwise so this hack is applied for now...
			$saved = str_replace( '300x300', '150x150', $saved );
			return sprintf( '<img src="%s"%s%s%s />', esc_url( $saved ), esc_attr( $alt ), $size, $height );
		}
		return sprintf( '<img src="%s?s=%s" alt="%s" width="%s" height="%s" />', esc_url( $saved ), $size, esc_attr( $alt ), $size, $size );
	}

	// return normal
	return $avatar;
}
add_filter( 'get_avatar', 'TradePress_filter_slug_get_avatar', 10, 5 );

/**
 * For use with filter: get_avatar_url
 *
 * @param mixed $avatar
 * @param mixed $id_or_email
 * @param mixed $size
 * @param mixed $default
 * @param mixed $alt
 *
 * @version 2.0
 */
function TradePress_filter_slug_get_avatar_url( $avatar, $id_or_email = null, $size = null, $default = false, $alt = '' ) {

	if ( is_object( $id_or_email ) ) {
		if ( isset( $id_or_email->data->ID ) ) {
			$id_or_email = $id_or_email->data->ID;
		} elseif ( is_object( $id_or_email ) && isset( $id_or_email->comment_author_email ) ) {
			$user = get_user_by( 'email', $id_or_email->comment_author_email );
			if ( $user ) {
				$id_or_email = $user->ID;
			}
		} elseif ( ! is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			if ( $user ) {
				$id_or_email = $user->ID;
			}
		}
	}

	// If still no user ID, return...
	if ( ! is_numeric( $id_or_email ) || is_object( $id_or_email ) ) {
		return $avatar;
	}

	// Find URL of saved avatar in user meta...
	$saved = get_user_meta( $id_or_email, 'TradePress_avatar_url', true );

	// Check if it is a URL...
	if ( filter_var( $saved, FILTER_VALIDATE_URL ) ) {
		return $avatar;
	}

	// Return normal...
	return $avatar;
}
add_filter( 'get_avatar_url', 'TradePress_filter_slug_get_avatar_url', 10, 5 );

/**
 * BuddyPress avatar override, allowing Twitch.tv user logos as avatar...
 *
 * Returns HTML
 *
 * @param mixed $avatar
 * @param mixed $params
 * @param mixed $item_id
 * @param mixed $avatar_dir
 * @param mixed $html_css_id
 * @param mixed $html_width
 * @param mixed $html_height
 * @param mixed $avatar_folder_url
 * @param mixed $avatar_folder_dir
 *
 * @version 1.0
 */
function TradePress_bp_fetch_avatar( $avatar, $params, $item_id, $avatar_dir, $html_css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir ) {
	return TradePress_filter_slug_get_avatar( $avatar, $item_id, $html_width, false, '', true, $html_height );
}
add_filter( 'bp_core_fetch_avatar', 'TradePress_bp_fetch_avatar', 99, 9 );

/**
 * BuddyPress avatar override, allowing Twitch.tv user logos as avatar...
 *
 * Returns URL
 *
 * @param mixed $avatar
 * @param mixed $params
 *
 * @version 1.0
 */
function TradePress_bp_fetch_avatar_url( $gravatar, $params ) {
	return TradePress_filter_slug_get_avatar_url( $gravatar, $params['email'] );
}
add_filter( 'bp_core_fetch_avatar_url', 'TradePress_bp_fetch_avatar_url', 99, 2 );

/**
 * Updates user avatar with a 48x48 image with cropping possible...
 *
 * @param mixed $wp_user_id
 * @param mixed $url
 *
 * @version 2.0
 *
 * @deprecated
 */
function TradePress_update_user_meta_avatar( $wp_user_id, $url ) {
	if ( isset( $url ) && $url ) {
		return update_user_meta( $wp_user_id, 'TradePress_avatar_url', $url );
	}
	return false;
}

/**
 * Updates user avatar with a 48x48 image with cropping possible...
 *
 * @param mixed $wp_user_id
 * @param mixed $url
 *
 * @version 2.0
 */
function TradePress_update_user_meta_twitch_logourl( $wp_user_id, $url ) {
	if ( isset( $url ) && $url ) {
		return update_user_meta( $wp_user_id, 'TradePress_twitch_logo_url', $url );
	}
	return false;
}

/**
 * Redirect during shortcode processing,
 * with parameters for displaying a front-end notice.
 *
 * @param mixed $message_source is the plugin name i.e. "core" or "subscribermanagement" or "loginextension" etc
 * @param mixed $message_key
 *
 * @version 1.0
 */
function TradePress_shortcode_procedure_redirect( $message_key, $title_values_array = array(), $info_values_array = array(), $message_source = 'tradepress' ) {

	// Store values array in shortlife transient and use when generating output.
	set_transient(
		'TradePress_shortcode_' . $message_source . $message_key,
		array(
			'title_values' => $title_values_array,
			'info_values'  => $info_values_array,
		),
		120
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'TradePress_notice' => time(),
				'key'               => $message_key,
				'source'            => $message_source,
			),
			wp_get_referer()
		)
	);
	exit;
}

/**
 * Get slug from path
 *
 * @param  string $key
 * @return string
 * @version 1.0.0
 */
function TradePress_format_plugin_slug( $key ) {
	$slug = explode( '/', $key );
	$slug = explode( '.', end( $slug ) );
	return $slug[0];
}

/**
 * Get custom capabilities for this package. These are assigned to
 * all administrators and are available for applying to moderator
 * level users.
 *
 * Caps are assigned during installation or reset.
 *
 * @return array
 *
 * @version 1.0
 */
function TradePress_get_core_capabilities() {
	$capabilities = array();

	$capabilities['core'] = array(
		'manage_TradePress',
	);

	return $capabilities;
}

/**
 * Update users oauth2 token.
 *
 * @param mixed $user_id
 * @param mixed $token
 *
 * @version 3.0
 */
function TradePress_update_user_token( $wp_user_id, $token ) {
	$v    = sanitize_key( $token );
	$time = time();
	update_user_meta( $wp_user_id, 'TradePress_auth_time', $time );
	if ( TradePress_CURRENTUSERID == $wp_user_id ) {
		TradePress_Object_Registry::update_var( 'currentusertwitch', 'user_auth_time', $time );
	}
	update_user_meta( $wp_user_id, 'TradePress_token', $v );
	if ( TradePress_CURRENTUSERID == $wp_user_id ) {
		TradePress_Object_Registry::update_var( 'currentusertwitch', 'user_token', $v );
	}
}

/**
 * Update user bot token.
 *
 * @param mixed $user_id
 * @param mixed $token
 *
 * @version 1.0.0
 */
function TradePress_update_user_bot_token( $user_id, $token ) {
	update_user_meta( $user_id, 'TradePress_bot_auth_time', time() );
	update_user_meta( $user_id, 'TradePress_bot_token', $token );
}

/**
 * Get users token scopes.
 *
 * @param mixed $user_id
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_users_token_scopes( $user_id ) {
	return get_user_meta( $user_id, 'TradePress_token_scope', true );
}

/**
 * Get the token_refresh string for extending a session.
 *
 * @param integer $user_id
 * @param boolean $single
 *
 * @version 1.0
 */
function TradePress_get_user_token_refresh( $user_id, $single = true ) {
	return get_user_meta( $user_id, 'TradePress_token_refresh', $single );
}

/**
 * Get user bot token refresh.
 *
 * @param mixed $user_id
 * @param bool  $single
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_user_bot_token_refresh( $user_id, $single = true ) {
	return get_user_meta( $user_id, 'TradePress_bot_token_refresh', $single );
}

/**
 * Update users oauth2 token_refresh string.
 *
 * @param integer $user_id
 * @param boolean $token
 *
 * @version 1.0
 */
function TradePress_update_user_token_refresh( $wp_user_id, $token ) {
	$v = sanitize_key( $token );
	update_user_meta( $wp_user_id, 'TradePress_token_refresh', $v );
	if ( TradePress_CURRENTUSERID == $wp_user_id ) {
		return TradePress_Object_Registry::update_var( 'currentusertwitch', 'user_refresh', $v );
	}
}

/**
 * Update user bot token refresh.
 *
 * @param mixed $user_id
 * @param mixed $token
 *
 * @version 1.0.0
 */
function TradePress_update_user_bot_token_refresh( $user_id, $token ) {
	update_user_meta( $user_id, 'TradePress_bot_token_refresh', $token );
}

/**
 * Get the giving users Twitch subscription plan for the giving or main channel...
 *
 * @param mixed $wp_user_id
 * @param mixed $twitch_channel_id
 *
 * @version 2.0
 */
function TradePress_get_sub_plan( $wp_user_id, $twitch_channel_id = null ) {
	if ( ! $twitch_channel_id ) {
		$twitch_channel_id = TradePress_get_main_channels_twitchid(); }
	return get_user_meta( $wp_user_id, 'TradePress_sub_plan_' . $twitch_channel_id, true );
}

/**
 * Update user token expires in.
 *
 * @param mixed $wp_user_id
 * @param mixed $expires_in
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_user_token_expires_in( $wp_user_id, $expires_in ) {
	update_user_meta( $wp_user_id, 'TradePress_twitch_expires_in', $expires_in );
	if ( TradePress_CURRENTUSERID == $wp_user_id ) {
		return TradePress_Object_Registry::update_var( 'currentusertwitch', 'user_expires_in', $expires_in );
	}
}

/**
 * Update user token authtime.
 *
 * @param mixed $wp_user_id
 * @param mixed $time
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_user_token_authtime( $wp_user_id, $time ) {
	update_user_meta( $wp_user_id, 'TradePress_auth_time', $time );
	if ( TradePress_CURRENTUSERID == $wp_user_id ) {
		return TradePress_Object_Registry::update_var( 'currentusertwitch', 'user_auth_time', $time );
	}
}

/**
 * Update user token scope.
 *
 * @param mixed $wp_user_id
 * @param mixed $scope
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_user_token_scope( $wp_user_id, $scope ) {
	update_user_meta( $wp_user_id, 'TradePress_token_scope', $scope );
	if ( TradePress_CURRENTUSERID == $wp_user_id ) {
		return TradePress_Object_Registry::update_var( 'currentusertwitch', 'user_scope', $scope );
	}
}

//
//
// MAIN CHANNEL [GET]                          #
//
//

/**
 * Get the main channel name.
 * This is entered by the key holder during the setup wizard.
 *
 * @version 2.0
 */
function TradePress_get_main_channels_name() {
	$obj = TradePress_Object_Registry::get( 'mainchannelauth' );
	return isset( $obj->main_channels_name ) ? $obj->main_channels_name : null;
}

/**
 * Get the main/default/official channel ID for the WP site.
 *
 * @version 2.0
 */
function TradePress_get_main_channels_twitchid() {
	$obj = TradePress_Object_Registry::get( 'mainchannelauth' );
	return isset( $obj->main_channels_id ) ? $obj->main_channels_id : null;
}

/**
 * Get the channels token which is the same value as the channel owners token but this
 * can make it easier to obtain that value outside of a user based procedure.
 *
 * @version 2.0
 */
function TradePress_get_main_channels_token() {
	$obj = TradePress_Object_Registry::get( 'mainchannelauth' );
	return isset( $obj->main_channels_token ) ? $obj->main_channels_token : null;
}

/**
 * Get the main channels code which is the same as the channel owners code.
 *
 * @version 2.0
 */
function TradePress_get_main_channels_code() {
	$obj = TradePress_Object_Registry::get( 'mainchannelauth' );
	return isset( $obj->main_channels_code ) ? $obj->main_channels_code : null;
}

/**
 * Returns the WordPress ID of the main channel owner.
 * This is added to the database during the plugin Setup Wizard.
 *
 * @version 2.0
 */
function TradePress_get_main_channels_wpowner_id() {
	$obj = TradePress_Object_Registry::get( 'mainchannelauth' );
	return isset( $obj->main_channels_wpowner_id ) ? $obj->main_channels_wpowner_id : null;
}

/**
 * Get main channels refresh.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_main_channels_refresh() {
	$obj = TradePress_Object_Registry::get( 'mainchannelauth' );
	return isset( $obj->main_channels_refresh ) ? $obj->main_channels_refresh : null;
}

/**
 * Get the scopes that the channel owner agreed to. The value is also stored in user-meta.
 *
 * @version 1.0
 */
function TradePress_get_main_channels_scopes() {
	$obj = TradePress_Object_Registry::get( 'mainchannelauth' );
	return isset( $obj->main_channels_scopes ) ? $obj->main_channels_scopes : null;
}

/**
 * Get the main/default/official channels related post ID.
 *
 * @version 1.0
 */
function TradePress_get_main_channels_postid() {
	$obj = TradePress_Object_Registry::get( 'mainchannelauth' );
	return isset( $obj->main_channels_postid ) ? $obj->main_channels_postid : null;
}

//
//
// MAIN CHANNEL [UPDATE]                       #
//
//

/**
 * Update main channels code.
 *
 * @param mixed $new_code
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_main_channels_code( $new_code ) {
	update_option( 'TradePress_main_channels_code', $new_code, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_code', $new_code );
}

/**
 * Update main channels wpowner id.
 *
 * @param mixed $wp_user_id
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_main_channels_wpowner_id( $wp_user_id ) {
	update_option( 'TradePress_main_channels_wpowner_id', $wp_user_id, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_wpowner_id', $wp_user_id );
}

/**
 * Update main channels token.
 *
 * @param mixed $new_token
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_main_channels_token( $new_token ) {
	update_option( 'TradePress_main_channels_token', $new_token, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_token', $new_token );
}

/**
 * Updates main channels refresh token in options table and object registry.
 *
 * @param mixed $new_refresh_token
 *
 * @version 2.0
 */
function TradePress_update_main_channels_refresh_token( $new_refresh_token ) {
	update_option( 'TradePress_main_channels_refresh_token', $new_refresh_token, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_refresh_token', $new_refresh_token );
}

/**
 * Updates main channels accepted scopes in relation to the owner/admins accepted
 * scopes during authorization. Storing them as the channels scopes is a simplier
 * way to obtain the data.
 *
 * Updates option table and object registry.
 *
 * @param mixed $new_main_channels_scopes
 *
 * @version 2.0
 */
function TradePress_update_main_channels_scopes( $new_main_channels_scopes ) {
	update_option( 'TradePress_main_channels_scopes', $new_main_channels_scopes, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_scopes', $new_main_channels_scopes );
}

/**
 * Update main channels authtime.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_main_channels_authtime() {
	$time = time();
	update_option( 'TradePress_main_channels_authtime', $time, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_authtime', $time );
}

/**
 * Updates option table and object registry with new main channel name.
 *
 * @param mixed $new_main_channels_name
 *
 * @version 2.0
 */
function TradePress_update_main_channels_name( $new_main_channels_name ) {
	update_option( 'TradePress_main_channels_name', $new_main_channels_name, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_name', $new_main_channels_name );
}

/**
 * Updates option table and object registry with new main channel (twitch)ID.
 *
 * @param mixed $new_main_channels_id
 *
 * @version 2.0
 */
function TradePress_update_main_channels_id( $new_main_channels_id ) {
	update_option( 'TradePress_main_channels_id', $new_main_channels_id, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_id', $new_main_channels_id );
}

/**
 * Updates option table and object registry with new main channel post ID.
 *
 * @param mixed $new_main_channels_postid
 *
 * @version 2.0
 */
function TradePress_update_main_channels_postid( $new_main_channels_postid ) {
	update_option( 'TradePress_main_channels_postid', $new_main_channels_postid, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_postid', $new_main_channels_postid );
}

/**
 * Update main channels expires in.
 *
 * @param mixed $expires_in
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_main_channels_expires_in( $expires_in ) {
	update_option( 'TradePress_main_channels_expires_in', $expires_in, false );
	return TradePress_Object_Registry::update_var( 'mainchannelauth', 'main_channels_postid', $expires_in );
}

//
//
// APPLICATION [GET]                           #
//
//

/**
 * Get app id.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_app_id() {
	$obj = TradePress_Object_Registry::get( 'twitchapp' );
	return isset( $obj->app_id ) ? $obj->app_id : null;
}

/**
 * Get app secret.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_app_secret() {
	$obj = TradePress_Object_Registry::get( 'twitchapp' );
	return isset( $obj->app_secret ) ? $obj->app_secret : null;
}

/**
 * Get main client token.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_main_client_token() {
	$obj = TradePress_Object_Registry::get( 'twitchapp' );
	return isset( $obj->app_token ) ? $obj->app_token : null;
}

/**
 * Get app redirect.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_app_redirect() {
	$obj = TradePress_Object_Registry::get( 'twitchapp' );
	return isset( $obj->app_redirect ) ? $obj->app_redirect : null;
}

/**
 * Get app token.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_app_token() {
	$obj = TradePress_Object_Registry::get( 'twitchapp' );
	return isset( $obj->app_token ) ? $obj->app_token : null;
}

/**
 * Get app token scopes.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_app_token_scopes() {
	$obj = TradePress_Object_Registry::get( 'twitchapp' );
	return isset( $obj->app_scopes ) ? $obj->app_scopes : null;
}

/**
 * Get app token expiry.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_app_token_expiry() {
	$obj = TradePress_Object_Registry::get( 'twitchapp' );
	return isset( $obj->token_expiry ) ? $obj->token_expiry : null;
}

//
//
// APPLICATION [UPDATE]                          #
//
//

/**
 * Update app id.
 *
 * @param mixed $new_app_id
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_app_id( $new_app_id ) {
	update_option( 'TradePress_app_id', $new_app_id, true );
	return TradePress_Object_Registry::update_var( 'twitchapp', 'app_id', $new_app_id );
}

/**
 * Update app secret.
 *
 * @param mixed $new_app_secret
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_app_secret( $new_app_secret ) {
	update_option( 'TradePress_app_secret', $new_app_secret, true );
	return TradePress_Object_Registry::update_var( 'twitchapp', 'app_secret', $new_app_secret );
}

/**
 * Update app redirect.
 *
 * @param mixed $new_app_redirect
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_app_redirect( $new_app_redirect ) {
	update_option( 'TradePress_app_redirect', $new_app_redirect, true );
	return TradePress_Object_Registry::update_var( 'twitchapp', 'app_redirect', $new_app_redirect );
}

/**
 * Update app token.
 *
 * @param mixed $new_app_token
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_app_token( $new_app_token ) {
	update_option( 'TradePress_app_token', $new_app_token, true );
	return TradePress_Object_Registry::update_var( 'twitchapp', 'app_token', $new_app_token );
}

/**
 * Update app token expiry.
 *
 * @param mixed $new_app_token_expiry
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_app_token_expiry( $new_app_token_expiry ) {
	update_option( 'TradePress_app_expiry', $new_app_token_expiry, true );
	return TradePress_Object_Registry::update_var( 'twitchapp', 'app_expiry', $new_app_token_expiry );
}

/**
 * Update app token scopes.
 *
 * @param mixed $new_app_scopes
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_update_app_token_scopes( $new_app_scopes ) {
	update_option( 'TradePress_app_scopes', $new_app_scopes, true );
	return TradePress_Object_Registry::update_var( 'twitchapp', 'app_scopes', $new_app_scopes );
}

/**
 * Generate an oAuth2 Twitch API URL for an administrator only. The procedure
 * for public visitors will use different methods for total clarity when it comes to
 * security.
 *
 * @author Ryan Bayne
 * @version 6.0
 *
 * @param array $permitted_scopes
 * @param array $state_array
 */
function TradePress_generate_authorization_url( $permitted_scopes, $local_state ) {

	// Scope value will be a random code that can be matched to a transient on return.
	if ( ! isset( $local_state['random14'] ) ) {
		$local_state['random14'] = TradePress_random14();}

	// Primary request handler - value is checked on return from Twitch.tv
	set_transient( 'TradePress_oauth_' . $local_state['random14'], $local_state, 6000 );

	// After installation $permitted_scopes can be empty, results in $scope being an array...
	$scope = '';
	if ( $permitted_scopes ) {
		$scope = TradePress_prepare_scopes( $permitted_scopes, true );
	}

	// Build Twitch.tv oauth2 URL...
	$url = 'https://id.twitch.tv/oauth2/authorize?' .
		'response_type=code' . '&' .
		'client_id=' . TradePress_get_app_id() . '&' .
		'redirect_uri=' . get_option( 'TradePress_app_redirect', 'Redirect Value Not Set In WordPress' ) . '&' .
		'scope=' . $scope . '&' .
		'state=' . $local_state['random14'];

	return $url;
}

/**
 * is_ajax - Returns true when the page is loaded via ajax.
 *
 * The DOING_AJAX constant is set by WordPress.
 *
 * @return bool
 * @version 1.0.0
 */
function TradePress_is_ajax() {
	return defined( 'DOING_AJAX' );
}

/**
 * Check if the home URL (stored during WordPress installation) is HTTPS.
 * If it is, we don't need to do things such as 'force ssl'.
 *
 * @return bool
 * @version 1.0.0
 */
function TradePress_is_https() {
	return false !== strstr( get_option( 'home' ), 'https:' );
}

/**
 * Use to check for Ajax or XMLRPC request. Use this function to avoid
 * running none urgent tasks during existing operations and demanding requests.
 *
 * @version 1.0.0
 */
function TradePress_is_background_process() {
	if ( ( 'wp-login.php' === basename( $_SERVER['SCRIPT_FILENAME'] ) )
		|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
		|| ( defined( 'DOING_CRON' ) && DOING_CRON )
		|| ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return true;
	}

	return false;
}

/**
 * Output any queued javascript code in the footer.
 *
 * @version 1.0.0
 */
function TradePress_print_js() {
	global $TradePress_queued_js;

	if ( ! empty( $TradePress_queued_js ) ) {
		// Sanitize.
		$TradePress_queued_js = wp_check_invalid_utf8( $TradePress_queued_js );
		$TradePress_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $TradePress_queued_js );
		$TradePress_queued_js = str_replace( "\r", '', $TradePress_queued_js );

		$js = "<!-- TradePress JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $TradePress_queued_js });\n</script>\n";

		/**
		 * TradePress_queued_js filter.
		 *
		 * @since 2.6.0
		 * @param string $js JavaScript code.
		 */
		echo apply_filters( 'TradePress_queued_js', $js );

		unset( $TradePress_queued_js );
	}
}

/**
 * Display a WordPress TradePress help tip.
 *
 * @since  2.5.0
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 *
 * @version 2.0
 */
function TradePress_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = TradePress_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="TradePress-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 * @version 1.0.0
 */
function TradePress_enqueue_js( $code ) {
	global $TradePress_queued_js;

	if ( empty( $TradePress_queued_js ) ) {
		$TradePress_queued_js = '';
	}

	$TradePress_queued_js .= "\n" . $code . "\n";
}

/**
 * Get permalink settings for TradePress independent of the user locale.
 *
 * @since  1.0.0
 * @return array
 *
 * @version 2.0
 */
function TradePress_get_permalink_structure() {
	if ( function_exists( 'switch_to_locale' ) && did_action( 'admin_init' ) ) {
		switch_to_locale( get_locale() );
	}

	$permalinks = wp_parse_args(
		(array) get_option( 'TradePress_permalinks', array() ),
		array(
			'symbol_base'            => '',
			'category_base'          => '',
			'tag_base'               => '',
			'attribute_base'         => '',
			'use_verbose_page_rules' => false,
		)
	);

	// Ensure rewrite slugs are set.
	$permalinks['symbols_rewrite_slug']   = untrailingslashit( empty( $permalinks['symbol_base'] ) ? _x( 'symbols', 'slug', 'tradepress' ) : $permalinks['symbol_base'] );
	$permalinks['attribute_rewrite_slug'] = untrailingslashit( empty( $permalinks['attribute_base'] ) ? '' : $permalinks['attribute_base'] );

	if ( function_exists( 'restore_current_locale' ) && did_action( 'admin_init' ) ) {
		restore_current_locale();
	}
	return $permalinks;
}

/**
 * Log a PHP error with extra information. Bypasses any WP configuration.

 * Common Use: TradePress_error( 'DEEPTRACE', 0, null, null, __LINE__, __FUNCTION__, __CLASS__, time() );
 *
 * @version 1.2
 *
 * @param string $message
 * @param int    $message_type 0=PHP logger|1=Email|2=Deprecated|3=Append to file|4=SAPI logging handler
 * @param string $destination
 * @param string $extra_headers
 * @param mixed  $line
 * @param mixed  $function
 * @param mixed  $class
 * @param mixed  $time
 */
function TradePress_error( $message, $message_type = 0, $destination = null, $extra_headers = null, $line = null, $function = null, $class = null, $time = null ) {
	$error  = 'TradePress Plugin: ';
	$error .= $message;
	$error .= ' (get info@ryanbayne.uk)';

	// Add extra information.
	if ( $line != null || $function != null || $class != null || $time != null ) {
		if ( $line ) {
			$error .= ' Line: ' . $line;
		}

		if ( $function ) {
			$error .= ' Function: ' . $function;
		}

		if ( $class ) {
			$error .= ' Class: ' . $class;
		}

		if ( $time ) {
			$error .= ' Time: ' . $time;
		}
	}

	return error_log( $error, $message_type, $destination, $extra_headers );
}

/**
 * Create a nonced URL for returning to the current page.
 *
 * @param mixed $new_parameters_array
 *
 * @version 1.2
 */
function TradePress_returning_url_nonced( $new_parameters_array, $action, $specified_url = null ) {
	$url = add_query_arg( $new_parameters_array, $specified_url );

	$url = wp_nonce_url( $url, $action );

	return $url;
}

/**
 * What type of request is this?
 *
 * Functions and constants are WordPress core. This function will allow
 * you to avoid large operations or output at the wrong time.
 *
 * @param  string $type admin, ajax, cron or frontend.
 * @return bool
 * @version 1.0.0
 */
function TradePress_is_request( $type ) {
	switch ( $type ) {
		case 'admin':
			return is_admin();
		case 'ajax':
			return defined( 'DOING_AJAX' );
		case 'cron':
			return defined( 'DOING_CRON' );
		case 'frontend':
			return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
	}
}

/**
 * Validate the value passed as a $_GET['code'] prior to using it.
 *
 * @return boolean false if not valid else true
 *
 * @version 1.0
 *
 * @param mixed $code
 */
function TradePress_validate_code( $code ) {
	if ( strlen( $code ) !== 30 ) {
		return false;
	}

	if ( ! ctype_alnum( $code ) ) {
		return false;
	}

	return true;
}

/**
 * Validates a token string as appearing suitable or not...
 *
 * @return boolean false if not valid else true
 *
 * @version 1.0
 *
 * @param mixed $token
 */
function TradePress_validate_token( $token ) {
	if ( strlen( $token ) !== 30 ) {
		return false;
	}

	if ( ! ctype_alnum( $token ) ) {
		return false;
	}

	return true;
}

/**
 * Determines if the value returned by generateToken() is a token or not.
 *
 * Does not check if the token is valid as this is intended for use straight
 * after a token is generated.
 *
 * @returns boolean true if the value appears normal.
 *
 * @version 1.0
 *
 * @param mixed $returned_value
 */
function TradePress_was_valid_token_returned( $returned_value ) {

	if ( ! array( $returned_value ) ) {
		return false;
	}

	if ( ! isset( $returned_value['access_token'] ) ) {
		return false;
	}

	if ( ! TradePress_validate_token( $returned_value['access_token'] ) ) {
		return false;
	}

	return true;
}

/**
 * A helix function for confirming valid access has been granted through a token...
 *
 * @returns boolean true if the value appears normal.
 *
 * @version 1.0
 *
 * @param mixed $token_obj
 */
function TradePress_was_valid_token_returned_from_helix( $token_obj ) {

	if ( ! is_object( $token_obj ) ) {
		return false;
	}

	if ( ! isset( $token_obj->access_token ) ) {
		return false;
	}

	if ( ! TradePress_validate_token( $token_obj->access_token ) ) {
		return false;
	}

	return true;
}

/**
 * Schedule an event for syncing feed posts into WP.
 *
 * @version 1.0
 */
function TradePress_schedule_sync_channel_to_wp() {
	wp_schedule_event(
		time() + 2,
		3600,
		'TradePress_sync_feed_to_wp'
	);
}

/**
 * Queries the custom post type 'twitchchannels' and returns post ID's that
 * have a specific meta key and specific meta value.
 *
 * @version 1.0
 *
 * @param mixed $post_meta_key
 * @param mixed $post_meta_value
 * @param int   $limit
 */
function TradePress_get_channel_posts_by_meta( $post_meta_key, $post_meta_value, $limit = 100 ) {
	// args to query for your key
	$args = array(
		'post_type'  => 'symbols',
		'meta_query' => array(
			array(
				'key'   => $post_meta_key,
				'value' => $post_meta_value,
			),
		),
		'fields'     => 'ids',
	);

	// perform the query
	$query = new WP_Query( $args );

	if ( ! empty( $query->posts ) ) {
		return true;
	}

	return false;
}

/**
 * Check if giving post name (slug) already exists in wp_posts.
 *
 * @param mixed $post_name
 *
 * @version 1.0
 */
function TradePress_does_post_name_exist( $post_name ) {
	global $wpdb;
	$result = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = '%s'", $post_name ), 'ARRAY_A' );
	if ( $result ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Converts "2016-11-29T15:52:27Z" format into a timestamp.
 *
 * @param mixed $date_time_string
 *
 * @version 1.0
 */
function TradePress_convert_created_at_to_timestamp( $date_time_string ) {
	return date_timestamp_get( date_create( $date_time_string ) );
}

/**
 * Checks if the giving post type is one that
 * has been permitted for sharing to Twitch channel feeds.
 *
 * @version 1.0
 *
 * @param string $post_type
 */
function TradePress_is_posttype_shareable( $post_type ) {
	if ( get_option( 'TradePress_shareable_posttype_' . $post_type ) ) {
		return true;
	}
	return false;
}

/**
 * Handles redirects with log entries and added arguments to URL for
 * easy visual monitoring.
 *
 * @param mixed $url
 * @param mixed $line
 * @param mixed $function
 * @param mixed $file
 *
 * @version 2.0
 */
function TradePress_redirect_tracking( $url, $line, $function, $file = '', $safe = false ) {

	$redirect_counter = 1;

	// Refuse the redirect and log if TradePressredirected=2 in giving $url.
	if ( strstr( $url, 'TradePressredirected=1' ) ) {
		++$redirect_counter;
	} elseif ( strstr( $url, 'TradePressredirected=2' ) ) {
		return;
	}

	// Tracking adds more values to help trace where redirect was requested.
	if ( get_option( 'TradePress_redirect_tracking_switch' ) == 'yes' ) {
		$url = add_query_arg(
			array(
				'redirected-line'     => $line,
				'redirected-function' => $function,
			),
			esc_url_raw( $url )
		);
	}

	if ( $safe ) {
		wp_safe_redirect( add_query_arg( array( 'TradePressredirected' => $redirect_counter ), $url ) );
		exit;
	}

	// Add TradePressredirected to show that the URL has had a redirect.
	// If it ever becomes normal to redirect again, we can increase the integer.
	wp_redirect( add_query_arg( array( 'TradePressredirected' => $redirect_counter ), $url ) );
	exit;
}

/**
 * Determines if giving value is a valid Twitch subscription plan.
 *
 * @param mixed $value
 *
 * @returns boolean true if the $value is valid.
 *
 * @version 1.0
 */
function TradePress_is_valid_sub_plan( $value ) {
	$sub_plans = array( 'prime', 1000, 2000, 3000 );
	if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
		return false;}
	if ( is_string( $value ) ) {
		$value = strtolower( $value ); }
	if ( in_array( $value, $sub_plans ) ) {
		return true;}
	return false;
}

/**
 * Generates a random 14 character string.
 *
 * @version 2.0
 */
function TradePress_random14() {
	return rand( 10000000, 99999999 ) . rand( 100000, 999999 );
}

/**
 * Dump the giving value but only if the current user is allowed to see dumps.
 *
 * @param mixed $var
 *
 * @version 2.0
 */
function var_dump_TradePress( $var ) {
	$numargs  = func_num_args();
	$arg_list = func_get_args();
	for ( $i = 0; $i < $numargs; $i++ ) {
		echo '<pre>';
		var_dump( $arg_list[ $i ] );
		echo '</pre>'; // DO NOT REMOVE #
	}
}

/**
 * Like tradepress_var_dump() but always requires an administrator and does not
 * need to be removed before version release.
 *
 * @param mixed $var
 *
 * @version 3.0
 */
function tradepress_var_dump_safer( $var = null, $levels = 2 ) {
	if ( ! TradePress_are_errors_allowed() ) {
		return false; }
	tradepress_var_dump( $var, $levels ); // DO NOT REMOVE #
	wp_die( 'WordPress died at Line ' . __LINE__ . ' - ' . __FILE__ ); // DO NOT REMOVE #
}

/**
 * The original var_dump() with some formatting and settings control.
 *
 * Please use tradepress_var_dump_safer() for additional security using TradePress_are_errors_allowed()
 * but do use this when the security measures complicate the ability to generate output.
 *
 * @param mixed $var
 * @param mixed $wp_die
 *
 * @version 4.0
 */
function tradepress_var_dump( $var = null, $levels = 2, $additional = array() ) {
	$bt = debug_backtrace();

	$atts = shortcode_atts(
		array(
			'title'       => 'Developer Information',
			'description' => null,
			'offline'     => __( 'Channel Offline', 'tradepress' ),
		),
		$additional
	);

	$header  = '<h2>' . $atts['title'] . '</h2>';
	$header .= '<p>' . $atts['description'] . '</p>';
	$header .= '<h3>PHP Trace...</h3>';

	for ( $i = 1; $i <= $levels; $i++ ) {
		if ( isset( $bt[ $i ]['function'] ) ) {
			$header .= '<li>Func: ' . $bt[ $i ]['function'] . '</li>';
		}
		if ( isset( $bt[ $i ]['file'] ) ) {
			$header .= '<li>File: ' . $bt[ $i ]['file'] . '</li>';
		}
		if ( isset( $bt[ $i ]['line'] ) ) {
			$header .= '<li>Line: ' . $bt[ $i ]['line'] . '</li>';
		}
	}

	$header .= '</ul>';

	echo '<pre>';
	var_dump( $header );
	echo '</pre>';  // DO NOT REMOVE #
	echo '<h3>Dump...</h3>';
	echo '<pre>';
	var_dump( $var );
	echo '</pre>';  // DO NOT REMOVE #
}

/**
 * Wp die trade press.
 *
 * @param mixed $html
 *
 * @version 1.0.0
 */
function wp_die_TradePress( $html ) {
	if ( ! TradePress_are_errors_allowed() ) {
		return; }
	wp_die( esc_html( $html ) );
}

/**
 * Checks if the current user is permitted to view
 * error dumps for the entire blog.
 *
 * Assumes the BugNet library.
 *
 * @version 2.0
 */
function TradePress_are_errors_allowed() {

	if ( TradePress_is_background_process() ) {
		return false;
	}

	if ( TradePress_is_ajax() ) {
		// return false;
	}

	global $pagenow;
	if ( ( $pagenow == 'post.php' ) || ( get_post_type() == 'post' ) || $pagenow == 'post-new.php' ) {
		return false;
	}

	if ( ! get_option( 'TradePress_displayerrors' ) || get_option( 'TradePress_displayerrors' ) !== 'yes' ) {
		return false;
	}

	$whitelist = array(
		'127.0.0.1',
		'::1',
	);

	if ( in_array( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '', $whitelist ) ) {
		return true;
	}

	// We can bypass the protection to display errors for a specified user.
	if ( 'BYPASS' == get_option( 'bugnet_error_dump_user_id' ) ) {
		return true;
	}

	// A value of ADMIN allows anyone with "activate_plugins" permission to see errors.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return false;
	} elseif ( 'ADMIN' == get_option( 'bugnet_error_dump_user_id' ) ) {
		return true;
	}

	// Match current users ID to the entered ID which restricts error display to a single user.
	if ( get_current_user_id() != get_option( 'bugnet_error_dump_user_id' ) ) {
		return false;
	}

	return true;
}

/**
 * Adds spaces between each scope as required by the Twitch API.
 *
 * @param mixed $scopes_array
 * @param mixed $for_url
 *
 * @version 2.0
 */
function TradePress_prepare_scopes( $scopes_array ) {
	if ( ! $scopes_array ) {
		return ''; }

	$scopes_string = '';

	foreach ( $scopes_array as $s ) {

		$scopes_string .= $s . '+';
	}

	$prepped_scopes = rtrim( $scopes_string, '+' );

	return $prepped_scopes;
}

/**
 * Scopecheckbox required icon.
 *
 * @param mixed $scope
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_scopecheckbox_required_icon( $scope ) {
	global $system_scopes_status;

	$required = false;

	// Do not assume every extension has set this global properly.
	if ( ! is_array( $system_scopes_status ) || empty( $system_scopes_status ) ) {
		return ''; }

	// Check if $scope is required for the admins main account.
	foreach ( $system_scopes_status['admin'] as $extension_slug => $scope_information ) {
		if ( in_array( $scope, $scope_information['required'] ) ) {
			$required = true;
			break; }
	}

	if ( $required ) {
		$icon = '<span class="dashicons dashicons-yes"></span>';
	} else {
		$icon = '<span class="dashicons dashicons-no"></span>';
	}

	return $icon;
}

/**
 * Scopecheckboxpublic required icon.
 *
 * @param mixed $scope
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_scopecheckboxpublic_required_icon( $scope ) {
	global $system_scopes_status;

	$required = false;

	// Do not assume every extension has set this global properly.
	if ( ! is_array( $system_scopes_status ) || empty( $system_scopes_status ) ) {
		return ''; }

	// Check if $scope is required for visitors accounts.
	foreach ( $system_scopes_status['public'] as $extension_slug => $scope_information ) {
		if ( in_array( $scope, $scope_information['required'] ) ) {
			$required = true;
			break; }
	}

	if ( $required ) {
		$icon = '<span class="dashicons dashicons-yes"></span>';
	} else {
		$icon = '<span class="dashicons dashicons-no"></span>';
	}

	return $icon;
}

/**
 * CSS for API Requests table.
 *
 * TODO: This should be moved to a CSS file.
 *
 * @version 1.0
 */
function TradePress_css_listtable_apirequests() {
	if ( ! isset( $_GET['page'] ) ) {
		return; }
	if ( ! isset( $_GET['tab'] ) ) {
		return; }
	if ( $_GET['page'] !== 'TradePress_data' ) {
		return; }

	echo '<style type="text/css">';
	echo '.wp-list-table .column-time { width: 10%; }';
	echo '.wp-list-table .column-function { width: 20%; }';
	echo '.wp-list-table .column-header { width: 30%; }';
	echo '.wp-list-table .column-url { width: 20%; }';
	echo '</style>';
}
add_action( 'admin_head', 'TradePress_css_listtable_apirequests' );

/**
 * CSS for API Errors table.
 * TODO: This should be moved to a CSS file.
 *
 * @version 1.0
 */
function TradePress_css_listtable_apiresponses() {
	if ( ! isset( $_GET['page'] ) ) {
		return; }
	if ( ! isset( $_GET['tab'] ) ) {
		return; }
	if ( $_GET['page'] !== 'TradePress_data' ) {
		return; }
	if ( $_GET['tab'] !== 'apiresponses_list_tables' ) {
		return; }

	echo '<style type="text/css">';
	echo '.wp-list-table .column-time { width: 10%; }';
	echo '.wp-list-table .column-httpdstatus { width: 10%; }';
	echo '.wp-list-table .column-function { width: 20%; }';
	echo '.wp-list-table .column-error_no { width: 10%; }';
	echo '.wp-list-table .column-result { width: 50%; }';
	echo '</style>';
}

/**
 * CSS for API Errors table.
 * TODO: This should be moved to a CSS file.
 *
 * @version 1.0
 */
function TradePress_css_listtable_apierrors() {
	if ( ! isset( $_GET['page'] ) ) {
		return; }
	if ( ! isset( $_GET['tab'] ) ) {
		return; }
	if ( $_GET['page'] !== 'TradePress_data' ) {
		return; }
	if ( $_GET['tab'] !== 'apierrors_list_tables' ) {
		return; }

	echo '<style type="text/css">';
	echo '.wp-list-table .column-time { width: 10%; }';
	echo '.wp-list-table .column-function { width: 20%; }';
	echo '.wp-list-table .column-error_string { width: 30%; }';
	echo '.wp-list-table .column-error_no { width: 10%; }';
	echo '.wp-list-table .column-curl_url { width: 40%; }';
	echo '</style>';
}
add_action( 'admin_head', 'TradePress_css_listtable_apierrors' );

/**
 * Get the sync timing array which holds delays for top level sync activity.
 *
 * This option avoids having to creation options per service at the top level
 * but if needed services can have additional options to control individual
 * processes.
 *
 * @version 1.0
 */
function TradePress_get_sync_timing() {
	$sync_timing_array = get_option( 'TradePress_sync_timing' );
	if ( ! $sync_timing_array || ! is_array( $sync_timing_array ) ) {
		return array(); }
	return $sync_timing_array;
}

/**
 * Update sync timing.
 *
 * @param mixed $sync_timing_array
 *
 * @version 1.0.0
 */
function TradePress_update_sync_timing( $sync_timing_array ) {
	update_option( 'TradePress_sync_timing', $sync_timing_array, false );
}

/**
 * Add a new sync time for a giving procedure.
 *
 * @param mixed $file
 * @param mixed $function
 * @param mixed $line
 * @param mixed $delay
 *
 * @version 1.0
 */
function TradePress_add_sync_timing( $file, $function, $line, $delay ) {
	$sync_timing_array = TradePress_get_sync_timing();
	$sync_timing_array[ $file ][ $function ][ $line ]['delay'] = $delay;
	$sync_timing_array[ $file ][ $function ][ $line ]['time']  = time();
	TradePress_update_sync_timing( $sync_timing_array );
}

/**
 * A standard method for establishing time delay and if a giving method is
 * due to run. Use this within any function/method to end it early.
 *
 * Sets new time() if due to make it easier to manage delays within procedures.
 *
 * @param mixed $function
 * @param mixed $line
 * @param mixed $file
 * @param mixed $delay
 *
 * @returns boolean true if delay has passed already else false.
 *
 * @version 2.0
 */
function TradePress_is_sync_due( $file, $function, $line, $delay ) {
	$sync_timing_array = TradePress_get_sync_timing();

	// Init the delay for the first time
	if ( ! isset( $sync_timing_array[ $file ][ $function ][ $line ] ) ) {
		TradePress_add_sync_timing( $file, $function, $line, $delay );
		return true;
	} else {
		$last_time    = $sync_timing_array[ $file ][ $function ][ $line ]['time'];
		$soonest_time = $last_time + $delay;
		if ( $soonest_time > time() ) {
			$sync_timing_array[ $file ][ $function ][ $line ]['delay'] = $delay;
			$sync_timing_array[ $file ][ $function ][ $line ]['time']  = time();
			TradePress_update_sync_timing( $sync_timing_array );
			return true;
		}

		// Not enough time has passed since the last event.
		return false;
	}
}

/**
 * Flood protector.
 *
 * @param mixed $file
 * @param mixed $function
 * @param mixed $line
 * @param mixed $delay
 *
 * @version 1.0.0
 */
function TradePress_flood_protector( $file, $function, $line, $delay ) {
	TradePress_is_sync_due( $file, $function, $line, $delay );
}

/**
 * Determines if the current logged in user is also the owner of the main channel.
 *
 * @version 2.0
 *
 * @param mixed $wp_user_id
 */
function TradePress_is_current_user_main_channel_owner( $wp_user_id = null ) {
	if ( ! $wp_user_id ) {
		$wp_user_id = get_current_user_id(); }

	// Avoid processing the owner of the main channel (might not be admin with ID 1)
	if ( TradePress_get_main_channels_wpowner_id() == $wp_user_id ) {
		return true; }
	return false;
}

/**
 * Returns the user meta value for the last time their Twitch data (as a whole)
 * was synced with WordPress. Do not use this when dealing with individual
 * values such as the users Twitch subscription to the main channel.
 *
 * @returns integer time set using time() or false/null.
 * @version 1.0
 *
 * @param mixed $user_id
 */
function TradePress_get_user_sync_time( $user_id ) {
	return get_user_meta( $user_id, 'TradePress_sync_time', true );
}

/**
 * Use when handling the WordPress current authenticated user.
 *
 * This function is used to determine if the current user needs to be
 * processed within the function or should the procedure be ended early.
 *
 * @version 1.0
 *
 * @param mixed $function
 * @param mixed $line
 * @param mixed $file
 * @param mixed $seconds
 */
function TradePress_is_current_user_sync_due( $function, $line, $file, $seconds ) {
	return TradePress_is_users_sync_due( TradePress_CURRENTUSERID, $function, $line, $file, $seconds );
}

/**
 * Not the same as TradePress_get_user_sync_time() which is a basic timer for
 * when syncing large amounts of data.
 *
 * This function can be used when making calls to the API for smaller amounts of data
 * and so allows anti-flooding in more locations while not using a single value to
 * block all requests.
 *
 * @param mixed $wp_user_id
 * @param mixed $function
 * @param mixed $line
 * @param mixed $file
 * @param mixed $seconds
 *
 * @version 2.0
 */
function TradePress_is_users_sync_due( $wp_user_id, $function, $line, $file, $seconds ) {

	$sync_data = TradePress_get_current_user_sync_transient();
	if ( isset( $sync_data[ $wp_user_id ][ $function ][ $line ][ $file ] ) ) {
		$earliest_time = $sync_data[ $wp_user_id ][ $function ][ $line ][ $file ] + $seconds;

		if ( $earliest_time < time() ) {
			$sync_data[ $wp_user_id ][ $function ][ $line ][ $file ] = time();

			TradePress_update_current_user_sync_transient( $sync_data );

			return true;// because $seconds have passed since the last sync!
		} else {
			return false;// because $seconds have not yet passed since the last sync!
		}
	} else {
		$sync_data[ $wp_user_id ][ $function ][ $line ][ $file ] = time();
		TradePress_update_current_user_sync_transient( $sync_data );
		return true;
	}
}

/**
 * Update current user sync transient.
 *
 * @param mixed $sync_data
 *
 * @version 1.0.0
 */
function TradePress_update_current_user_sync_transient( $sync_data ) {
	delete_transient( 'TradePress_current_user_syncing_' . date( 'Ymd' ) );
	set_transient( 'TradePress_current_user_syncing_' . date( 'Ymd' ), $sync_data, 3600 );
}

/**
 * A daily transient is created for storing some user related sync data. Rather than
 * make many user meta data values we maintain one array and can add it to the object
 * registry after it's initial use.
 *
 * @returns boolean false if no data has been set before else returns array
 *
 * @version 1.0
 */
function TradePress_get_current_user_sync_transient() {
	$transient_name = 'TradePress_current_user_syncing_' . date( 'Ymd' );

	// Get or set transient array of user sync times...
	$trans_val = get_transient( $transient_name );
	if ( $trans_val && ! is_array( $trans_val ) ) {
		TradePress_update_current_user_sync_transient( array() );
		return false;
	}

	return $trans_val;
}

/**
 * Creates a unique transient named based on API request.
 *
 * @param mixed $endpoint
 * @param mixed $originating_function
 * @param mixed $origination_line
 *
 * @version 2.0
 */
function TradePress_encode_transient_name( $endpoint, $originating_function, $origination_line ) {
	return base64_encode( serialize( array( $endpoint, $originating_function, $origination_line ) ) );
}

/**
 * Get call count.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_call_count() {
	return get_option( 'TradePress_twitchapi_call_count' );
}

/**
 * Get new call id.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_get_new_call_id() {
	$old_call_count = TradePress_get_call_count();
	$new_call_count = $old_call_count + 1;
	update_option( 'TradePress_twitchapi_call_count', $new_call_count, true );
	return $new_call_count;
}

/**
 * Login prevent redirect.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function TradePress_login_prevent_redirect() {
	if ( 'yes' == get_option( 'TradePress_login_prevent_redirect' ) ) {
		return true;
	}
	return false;
}

/**
 * Get Twitch app overall status from object registry...
 *
 * @version 1.0
 */
function TradePress_get_app_status() {
	$obj = TradePress_Object_Registry::get( 'twitchapp' );
	return isset( $obj->app_status ) ? $obj->app_status : null;
}

/**
 * Gets the transient set prior to sending visitor to Twitch API
 * for oAuth2 process. The transient tells us where the visitor initiated
 * the request and what should be done when they are re-directed back to WP.
 *
 * @param mixed $state a random code also nicknamed random14 within this plugin
 *
 * @version 1.0
 */
function TradePress_get_transient_oauth_state( $state_code ) {
	return get_transient( 'TradePress_oauth_' . $state_code );
}

/**
 * Check if giving stock symbol is already in the database.
 *
 * @param [type] $stocksymbol
 * @return void
 * @version 1.0.0
 */
function TradePress_is_stocksymbol_in_postmeta( $stocksymbol ) {
	$args = array(
		'post_type'  => 'symbols',
		'meta_query' => array(
			array(
				'key'   => 'stocksymbol',
				'value' => $stocksymbol,
			),
		),
		'fields'     => 'ids',
	);

	$query = new WP_Query( $args );

	if ( ! empty( $query->posts ) ) {
		return true;
	}

	return false;
}

/**
 * Count users by status
 *
 * @param $status
 *
 * @return int
 * @version 1.0.0
 */
function TradePress_count_users_by_status( $status ) {
	$args              = array(
		'fields' => 'ID',
		'number' => 0,
	);
	$twitch_channel_id = TradePress_get_main_channels_twitchid();

	if ( $status == 'twitchsubs' ) {
		$args['meta_query'][] = array( array( 'key' => 'TradePress_sub_plan_' . $twitch_channel_id ) );
		$users                = new \WP_User_Query( $args );
	} else {
		$twitch_channel_id    = TradePress_get_main_channels_twitchid();
		$args['meta_query'][] = array(
			array(
				'key'     => 'TradePress_sub_plan_' . $twitch_channel_id,
				'value'   => $status,
				'compare' => '=',
			),
		);
	}

	$users = new \WP_User_Query( $args );
	return count( $users->results );
}

/**
 * Memory report.
 *
 * @version 1.0.0
 */
function TradePress_memory_report() {
	$b = debug_backtrace();
	var_dump( '<br><br>FILE 1: ', $b[0]['file'], '<br>' );  // DO NOT REMOVE #
	var_dump( 'FUNCTION: ', $b[0]['function'], $b[0]['line'], '<br>' );  // DO NOT REMOVE #
	var_dump( '<br><br>FILE 2: ', $b[1]['file'], '<br>' );  // DO NOT REMOVE #
	var_dump( 'FUNCTION: ', $b[1]['function'], $b[1]['line'], '<br>' );  // DO NOT REMOVE #
	var_dump( '<br><br>FILE 3: ', $b[2]['file'], '<br>' );  // DO NOT REMOVE #
	var_dump( 'FUNCTION: ', $b[2]['function'], $b[2]['line'], '<br>' );   // DO NOT REMOVE #
	var_dump( '<br><br>FILE 4: ', $b[3]['file'], '<br>' );  // DO NOT REMOVE #
	var_dump( 'FUNCTION: ', $b[3]['function'], $b[3]['line'], '<br>' );  // DO NOT REMOVE #
	var_dump( 'USAGE: ', memory_get_usage(), '<br>' ); // DO NOT REMOVE #
	var_dump( 'PEAK: ', memory_get_peak_usage(), '<br>' );  // DO NOT REMOVE #
}

/**
 * Send to console.
 *
 * @param mixed $debug_output
 *
 * @version 1.0.0
 */
function TradePress_send_to_console( $debug_output ) {

	$cleaned_string = '';
	if ( ! is_string( $debug_output ) ) {
		$debug_output = print_r( $debug_output, true );
	}

		$str_len = strlen( $debug_output );
	for ( $i = 0; $i < $str_len; $i++ ) {
			$cleaned_string .= '\\x' . sprintf( '%02x', ord( substr( $debug_output, $i, 1 ) ) );
	}

	$javascript_ouput = "<script>console.log('Debug Info: " . $cleaned_string . "');</script>";
	echo esc_html( $javascript_ouput );
}

/**
 * Include custom post type in main query.
 *
 * @param mixed $query
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function tradepress_include_custom_post_type_in_main_query( $query ) {
	if ( is_home() || is_front_page() || is_archive() ) {
		$query->set( 'post_type', array( 'post', 'page', 'symbols' ) );
	}
	return $query;
}
add_filter( 'pre_get_posts', 'tradepress_include_custom_post_type_in_main_query' );

/**
 * Cache API call details in a transient for debugging purposes.
 * Only works when TRADEPRESS_TESTING is set to true.
 *
 * @since 1.0.1
 * @param string $api_id        The API identifier (e.g., 'alpaca', 'alltick')
 * @param string $endpoint      The endpoint that was called
 * @param array  $request_data  The data sent with the request
 * @param mixed  $response      The response received from the API
 * @param string $method        The HTTP method used (GET, POST, etc.)
 * @return bool                 Whether the data was cached successfully
 * @version 1.0.0
 */
function tradepress_cache_api_call( $api_id, $endpoint, $request_data = array(), $response = null, $method = 'GET' ) {
	// Only cache if testing mode is enabled
	if ( ! defined( 'TRADEPRESS_TESTING' ) || ! TRADEPRESS_TESTING ) {
		return false;
	}

	$call_data = array(
		'api_id'        => $api_id,
		'endpoint'      => $endpoint,
		'request_data'  => $request_data,
		'response'      => $response,
		'method'        => $method,
		'timestamp'     => current_time( 'mysql' ),
		'timestamp_gmt' => current_time( 'mysql', true ),
	);

	// Create a unique transient name for each API
	$transient_name = 'tradepress_latest_api_call_' . sanitize_key( $api_id );

	// Cache for 24 hours (86400 seconds)
	return set_transient( $transient_name, $call_data, 86400 );
}

/**
 * Get the latest cached API call for a specific API.
 *
 * @since 1.0.1
 * @param string $api_id  The API identifier (e.g., 'alpaca', 'alltick')
 * @return array|false    The cached API call data or false if none exists
 * @version 1.0.0
 */
function tradepress_get_latest_api_call( $api_id ) {
	$transient_name = 'tradepress_latest_api_call_' . sanitize_key( $api_id );
	return get_transient( $transient_name );
}

/**
 * Get the current environment mode.
 *
 * @return string Environment mode
 * @version 1.0.0
 */
function get_environment_mode() {
	return 'Live';
}

/**
 * Check whether explicit TradePress demo mode is active.
 *
 * Demo mode is separate from Developer Mode. Demo mode allows generated or
 * placeholder data paths to exist, while Developer Mode controls who may see
 * them in admin screens.
 *
 * @return bool True when TradePress demo mode is enabled.
 * @version 1.0.0
 */
function tradepress_is_demo_mode() {

	return defined( 'TRADEPRESS_DEMO_MODE' ) && TRADEPRESS_DEMO_MODE;
}

if ( ! function_exists( 'is_demo_mode' ) ) {
	/**
	 * Backwards-compatible demo mode helper.
	 *
	 * Older TradePress code and documentation refer to is_demo_mode(). Keep this
	 * thin wrapper so those call sites resolve through the canonical plugin
	 * helper.
	 *
	 * @return bool True when TradePress demo mode is enabled.
	 * @version 1.0.0
	 */
	function is_demo_mode() {

		return tradepress_is_demo_mode();
	}
}

/**
 * Check if developer mode is active
 *
 * @return bool True if developer mode is enabled
 * @version 1.0.0
 */
function tradepress_is_developer_mode() {

	if ( ! defined( 'WP_DEVELOPMENT_MODE' ) ) {
		return false;
	}

	$wp_development_mode_value = constant( 'WP_DEVELOPMENT_MODE' );

	if ( is_array( $wp_development_mode_value ) ) {
		return ! empty( $wp_development_mode_value );
	}

	if ( is_string( $wp_development_mode_value ) ) {
		return '' !== trim( $wp_development_mode_value )
			&& ! in_array( strtolower( $wp_development_mode_value ), array( '0', 'false', 'off' ), true );
	}

	return (bool) $wp_development_mode_value;
}

/**
 * Check whether the current user may access development-only admin surfaces.
 *
 * Demo-only, mock-data, and unfinished trading/advisor views must not be visible
 * to regular users. Developer Mode is the product gate; manage_options is the
 * capability gate for direct URL access.
 *
 * @return bool True when development-only UI may be shown.
 */
function tradepress_can_access_development_views() {

	return tradepress_is_developer_mode() && current_user_can( 'manage_options' );
}

/**
 * Append a developer reference code to UI text in Developer Mode.
 *
 * Keep the reference registry in this function so codes remain easy to audit as
 * this convention is applied across the plugin.
 *
 * @param string $text UI text.
 * @param string $code Reference code.
 * @return string UI text with reference code when Developer Mode is active.
 */
function tradepress_ui_reference( $text, $code ) {
	$tab_registry = tradepress_get_tab_reference_registry();
	$tab_codes    = array();
	foreach ( $tab_registry as $tab_entry ) {
		if ( isset( $tab_entry['code'], $tab_entry['description'] ) ) {
			$tab_codes[ $tab_entry['code'] ] = $tab_entry['description'];
		}
	}

	$used_codes = array(
		'TIT01' => 'Testing page title',
		'PAN04' => 'Testing UI crawl run summary panel',
		'PAN05' => 'Testing UI crawl URL results panel',
		'BUT01' => 'Testing Discover Tests button',
		'BUT02' => 'Testing Run Phase 3 Tests button',
		'BUT03' => 'Testing Run Trading212 Tests button',
		'BUT04' => 'Testing Run UI Crawl button',
		'BUT05' => 'Testing Run All Directive Tests button',
	);

	$used_codes = $used_codes + $tab_codes;

	$code = strtoupper( sanitize_key( $code ) );
	$code = str_replace( '_', '', $code );

	if ( '' === $code || ! isset( $used_codes[ $code ] ) ) {
		return $text;
	}

	if ( ! function_exists( 'tradepress_is_developer_mode' ) || ! tradepress_is_developer_mode() ) {
		return $text;
	}

	return sprintf( '%1$s [%2$s]', $text, $code );
}

/**
 * Global tab reference registry.
 *
 * Keys must be unique in the format "page_slug|tab_id".
 * Codes must be unique across the entire plugin.
 *
 * @return array<string,array<string,string>>
 */
function tradepress_get_tab_reference_registry() {
	return array(
		// Testing page tabs.
		'tradepress-tests|active'             => array( 'code' => 'TAB01', 'description' => 'Testing Active Tests tab view' ),
		'tradepress-tests|standard'           => array( 'code' => 'TAB02', 'description' => 'Testing Standard Tests tab view' ),
		'tradepress-tests|bugs'               => array( 'code' => 'TAB03', 'description' => 'Testing Bug Investigation tab view' ),
		'tradepress-tests|performance'        => array( 'code' => 'TAB04', 'description' => 'Testing Performance Tests tab view' ),
		'tradepress-tests|phase3'             => array( 'code' => 'TAB05', 'description' => 'Testing Phase 3 Tests tab view' ),
		'tradepress-tests|feature_status'     => array( 'code' => 'TAB06', 'description' => 'Testing Feature Status tab view' ),
		'tradepress-tests|ui_crawl'           => array( 'code' => 'TAB07', 'description' => 'Testing UI Crawl tab view' ),
		'tradepress-tests|trading212'         => array( 'code' => 'TAB08', 'description' => 'Testing Trading212 API tab view' ),
		'tradepress-tests|directives'         => array( 'code' => 'TAB09', 'description' => 'Testing Scoring Directives tab view' ),

		// Analysis page tabs.
		'tradepress_analysis|recent_symbols'      => array( 'code' => 'TAB10', 'description' => 'Analysis Recently Analysed Symbols tab view' ),
		'tradepress_analysis|support_resistance'  => array( 'code' => 'TAB11', 'description' => 'Analysis Support and Resistance tab view' ),
		'tradepress_analysis|volatility_analysis' => array( 'code' => 'TAB12', 'description' => 'Analysis Volatility Analysis tab view' ),

		// Automation page tabs.
		'tradepress_automation|data_import' => array( 'code' => 'TAB13', 'description' => 'Automation Data Import tab view' ),
		'tradepress_automation|cron'        => array( 'code' => 'TAB14', 'description' => 'Automation CRON Jobs tab view' ),
		'tradepress_automation|dashboard'   => array( 'code' => 'TAB15', 'description' => 'Automation Dashboard tab view' ),
		'tradepress_automation|algorithm'   => array( 'code' => 'TAB16', 'description' => 'Automation Algorithm tab view' ),
		'tradepress_automation|signals'     => array( 'code' => 'TAB17', 'description' => 'Automation Signals tab view' ),
		'tradepress_automation|trading'     => array( 'code' => 'TAB18', 'description' => 'Automation Trading tab view' ),
		'tradepress_automation|settings'    => array( 'code' => 'TAB19', 'description' => 'Automation Settings tab view' ),

		// Data page tabs.
		'tradepress_data|sources'          => array( 'code' => 'TAB20', 'description' => 'Data Sources tab view' ),
		'tradepress_data|data-elements'    => array( 'code' => 'TAB21', 'description' => 'Data Elements tab view' ),
		'tradepress_data|import'           => array( 'code' => 'TAB22', 'description' => 'Data Manual Import tab view' ),
		'tradepress_data|decoder'          => array( 'code' => 'TAB23', 'description' => 'Data Decoder tab view' ),
		'tradepress_data|tables'           => array( 'code' => 'TAB24', 'description' => 'Data Tables tab view' ),
		'tradepress_data|api-activity'     => array( 'code' => 'TAB25', 'description' => 'Data API Activity tab view' ),
		'tradepress_data|api-endpoints'    => array( 'code' => 'TAB26', 'description' => 'Data API Endpoints tab view' ),
		'tradepress_data|transient-caches' => array( 'code' => 'TAB27', 'description' => 'Data Transient Caches tab view' ),
		'tradepress_data|create_source'    => array( 'code' => 'TAB28', 'description' => 'Data Create Source tab view' ),
		'tradepress_data|api-keys'         => array( 'code' => 'TAB29', 'description' => 'Data API Keys tab view' ),
		'tradepress_data|sources_list'     => array( 'code' => 'TAB30', 'description' => 'Data Sources List tab view' ),
		'tradepress_data|api-errors'       => array( 'code' => 'TAB31', 'description' => 'Data API Errors tab view' ),

		// Watchlists page tabs.
		'tradepress_watchlists|active-symbols'   => array( 'code' => 'TAB32', 'description' => 'Watchlists Active Symbols tab view' ),
		'tradepress_watchlists|user-watchlists'  => array( 'code' => 'TAB33', 'description' => 'Watchlists User Watchlists tab view' ),
		'tradepress_watchlists|create-watchlist' => array( 'code' => 'TAB34', 'description' => 'Watchlists Create Watchlist tab view' ),

		// Focus page tabs.
		'tradepress_focus|priority' => array( 'code' => 'TAB35', 'description' => 'Focus Priority tab view' ),
		'tradepress_focus|daily'    => array( 'code' => 'TAB36', 'description' => 'Focus Daily tab view' ),
		'tradepress_focus|weekly'   => array( 'code' => 'TAB37', 'description' => 'Focus Weekly tab view' ),
		'tradepress_focus|advisor'  => array( 'code' => 'TAB38', 'description' => 'Focus Advisor tab view' ),

		// Development page tabs.
		'tradepress_development|current_task'       => array( 'code' => 'TAB39', 'description' => 'Development Current Task tab view' ),
		'tradepress_development|architecture'       => array( 'code' => 'TAB40', 'description' => 'Development Architecture Map tab view' ),
		'tradepress_development|algorithm_debugger' => array( 'code' => 'TAB41', 'description' => 'Development Algorithm Debugger tab view' ),
		'tradepress_development|diagrams'           => array( 'code' => 'TAB42', 'description' => 'Development Diagrams tab view' ),
		'tradepress_development|tasks'              => array( 'code' => 'TAB43', 'description' => 'Development Explore Tasks tab view' ),
		'tradepress_development|duplicate_checker'  => array( 'code' => 'TAB44', 'description' => 'Development Duplicate Checker tab view' ),
		'tradepress_development|github'             => array( 'code' => 'TAB45', 'description' => 'Development Create Task tab view' ),
		'tradepress_development|ai'                 => array( 'code' => 'TAB46', 'description' => 'Development AI tab view' ),
		'tradepress_development|pointers'           => array( 'code' => 'TAB47', 'description' => 'Development Pointers tab view' ),
		'tradepress_development|layouts'            => array( 'code' => 'TAB48', 'description' => 'Development Layouts tab view' ),
		'tradepress_development|ui_library'         => array( 'code' => 'TAB49', 'description' => 'Development Light UI Library tab view' ),
		'tradepress_development|dark_ui_library'    => array( 'code' => 'TAB50', 'description' => 'Development Dark UI Library tab view' ),
		'tradepress_development|jquery_ui'          => array( 'code' => 'TAB51', 'description' => 'Development jQuery UI tab view' ),
		'tradepress_development|assets'             => array( 'code' => 'TAB52', 'description' => 'Development Assets tab view' ),
		'tradepress_development|snippets'           => array( 'code' => 'TAB53', 'description' => 'Development Developer Snippets tab view' ),
		'tradepress_development|listener_testing'   => array( 'code' => 'TAB54', 'description' => 'Development Listener Testing tab view' ),

		// Scoring directives page tabs.
		'tradepress_scoring_directives|overview'             => array( 'code' => 'TAB55', 'description' => 'Scoring Directives Overview tab view' ),
		'tradepress_scoring_directives|configure_directives' => array( 'code' => 'TAB56', 'description' => 'Scoring Directives Configure Directives tab view' ),
		'tradepress_scoring_directives|directives_status'    => array( 'code' => 'TAB57', 'description' => 'Scoring Directives Development Status tab view' ),
		'tradepress_scoring_directives|algorithm'            => array( 'code' => 'TAB58', 'description' => 'Scoring Directives Algorithm tab view' ),
		'tradepress_scoring_directives|create_strategies'    => array( 'code' => 'TAB59', 'description' => 'Scoring Directives Create Strategies tab view' ),
		'tradepress_scoring_directives|manage_strategies'    => array( 'code' => 'TAB60', 'description' => 'Scoring Directives Manage Strategies tab view' ),
		'tradepress_scoring_directives|view_strategies'      => array( 'code' => 'TAB61', 'description' => 'Scoring Directives View Strategies tab view' ),
		'tradepress_scoring_directives|logs'                 => array( 'code' => 'TAB62', 'description' => 'Scoring Directives Activity Logs tab view' ),
		'tradepress_scoring_directives|developer'            => array( 'code' => 'TAB63', 'description' => 'Scoring Directives Developer tab view' ),

		// Trading page tabs.
		'tradepress_trading|trading-strategies' => array( 'code' => 'TAB64', 'description' => 'Trading Strategies tab view' ),
		'tradepress_trading|calculators'        => array( 'code' => 'TAB65', 'description' => 'Trading Calculators tab view' ),
		'tradepress_trading|portfolio'          => array( 'code' => 'TAB66', 'description' => 'Trading Portfolio tab view' ),
		'tradepress_trading|trade-history'      => array( 'code' => 'TAB67', 'description' => 'Trading Trade History tab view' ),
		'tradepress_trading|manual-trade'       => array( 'code' => 'TAB68', 'description' => 'Trading Manual Trading tab view' ),
		'tradepress_trading|sees-demo'          => array( 'code' => 'TAB69', 'description' => 'Trading SEES Demo tab view' ),
		'tradepress_trading|sees-diagnostics'   => array( 'code' => 'TAB70', 'description' => 'Trading SEES Diagnostics tab view' ),
		'tradepress_trading|sees-ready'         => array( 'code' => 'TAB71', 'description' => 'Trading SEES Ready tab view' ),
		'tradepress_trading|sees-pro'           => array( 'code' => 'TAB72', 'description' => 'Trading SEES Pro tab view' ),

		// Research page tabs.
		'tradepress_research|overview'             => array( 'code' => 'TAB73', 'description' => 'Research Overview tab view' ),
		'tradepress_research|sector-rotation'      => array( 'code' => 'TAB74', 'description' => 'Research Sector Rotation tab view' ),
		'tradepress_research|market-correlations'  => array( 'code' => 'TAB75', 'description' => 'Research Market Correlations tab view' ),
		'tradepress_research|technical-indicators' => array( 'code' => 'TAB76', 'description' => 'Research Technical Indicators tab view' ),
		'tradepress_research|news_feed'            => array( 'code' => 'TAB77', 'description' => 'Research News Feed tab view' ),
		'tradepress_research|earnings'             => array( 'code' => 'TAB78', 'description' => 'Research Earnings Calendar tab view' ),
		'tradepress_research|social_networks'      => array( 'code' => 'TAB79', 'description' => 'Research Social Networks tab view' ),
		'tradepress_research|price_forecast'       => array( 'code' => 'TAB80', 'description' => 'Research Price Forecast tab view' ),
		'tradepress_research|economic-calendar'    => array( 'code' => 'TAB81', 'description' => 'Research Economic Calendar tab view' ),

		// Trading platforms page tabs.
		'tradepress_platforms|api_management'    => array( 'code' => 'TAB82', 'description' => 'Trading Platforms API Management tab view' ),
		'tradepress_platforms|api_efficiency'    => array( 'code' => 'TAB83', 'description' => 'Trading Platforms API Efficiency tab view' ),
		'tradepress_platforms|alltick'           => array( 'code' => 'TAB84', 'description' => 'Trading Platforms AllTick tab view' ),
		'tradepress_platforms|alpaca'            => array( 'code' => 'TAB85', 'description' => 'Trading Platforms Alpaca tab view' ),
		'tradepress_platforms|iexcloud'          => array( 'code' => 'TAB86', 'description' => 'Trading Platforms IEX Cloud tab view' ),
		'tradepress_platforms|polygon'           => array( 'code' => 'TAB87', 'description' => 'Trading Platforms Polygon tab view' ),
		'tradepress_platforms|tradier'           => array( 'code' => 'TAB88', 'description' => 'Trading Platforms Tradier tab view' ),
		'tradepress_platforms|finnhub'           => array( 'code' => 'TAB89', 'description' => 'Trading Platforms Finnhub tab view' ),
		'tradepress_platforms|twitter'           => array( 'code' => 'TAB90', 'description' => 'Trading Platforms Twitter tab view' ),
		'tradepress_platforms|stocktwits'        => array( 'code' => 'TAB91', 'description' => 'Trading Platforms StockTwits tab view' ),
		'tradepress_platforms|twelvedata'        => array( 'code' => 'TAB92', 'description' => 'Trading Platforms Twelve Data tab view' ),
		'tradepress_platforms|ibkr'              => array( 'code' => 'TAB93', 'description' => 'Trading Platforms Interactive Brokers tab view' ),
		'tradepress_platforms|tradingview'       => array( 'code' => 'TAB94', 'description' => 'Trading Platforms TradingView tab view' ),
		'tradepress_platforms|marketstack'       => array( 'code' => 'TAB95', 'description' => 'Trading Platforms Marketstack tab view' ),
		'tradepress_platforms|eodhistoricaldata' => array( 'code' => 'TAB96', 'description' => 'Trading Platforms EOD Historical Data tab view' ),
		'tradepress_platforms|yahoofinance'      => array( 'code' => 'TAB97', 'description' => 'Trading Platforms Yahoo Finance tab view' ),
		'tradepress_platforms|tiingo'            => array( 'code' => 'TAB98', 'description' => 'Trading Platforms Tiingo tab view' ),
		'tradepress_platforms|alphavantage'      => array( 'code' => 'TAB99', 'description' => 'Trading Platforms Alpha Vantage tab view' ),
		'tradepress_platforms|quandl'            => array( 'code' => 'TAB100', 'description' => 'Trading Platforms Quandl tab view' ),
		'tradepress_platforms|fred'              => array( 'code' => 'TAB101', 'description' => 'Trading Platforms FRED tab view' ),
		'tradepress_platforms|gemini'            => array( 'code' => 'TAB102', 'description' => 'Trading Platforms Gemini tab view' ),
		'tradepress_platforms|webull'            => array( 'code' => 'TAB103', 'description' => 'Trading Platforms WeBull tab view' ),
		'tradepress_platforms|trading212'        => array( 'code' => 'TAB104', 'description' => 'Trading Platforms Trading212 tab view' ),
		'tradepress_platforms|comparisons'       => array( 'code' => 'TAB105', 'description' => 'Trading Platforms API Provider Comparisons tab view' ),
		'tradepress_platforms|api_switches'      => array( 'code' => 'TAB106', 'description' => 'Trading Platforms Switches tab view' ),

		// Social platforms page tabs.
		'tradepress_social|settings'          => array( 'code' => 'TAB107', 'description' => 'Social Platforms Settings tab view' ),
		'tradepress_social|platform_switches' => array( 'code' => 'TAB108', 'description' => 'Social Platforms Switches tab view' ),
		'tradepress_social|twitter'           => array( 'code' => 'TAB109', 'description' => 'Social Platforms Twitter tab view' ),
		'tradepress_social|stocktwits'        => array( 'code' => 'TAB110', 'description' => 'Social Platforms StockTwits tab view' ),

		// Symbols page tabs.
		'tradepress_symbols|alltick'           => array( 'code' => 'TAB111', 'description' => 'Symbols AllTick tab view' ),
		'tradepress_symbols|alpaca'            => array( 'code' => 'TAB112', 'description' => 'Symbols Alpaca tab view' ),
		'tradepress_symbols|iexcloud'          => array( 'code' => 'TAB113', 'description' => 'Symbols IEX Cloud tab view' ),
		'tradepress_symbols|polygon'           => array( 'code' => 'TAB114', 'description' => 'Symbols Polygon tab view' ),
		'tradepress_symbols|tradier'           => array( 'code' => 'TAB115', 'description' => 'Symbols Tradier tab view' ),
		'tradepress_symbols|finnhub'           => array( 'code' => 'TAB116', 'description' => 'Symbols Finnhub tab view' ),
		'tradepress_symbols|twitter'           => array( 'code' => 'TAB117', 'description' => 'Symbols Twitter tab view' ),
		'tradepress_symbols|stocktwits'        => array( 'code' => 'TAB118', 'description' => 'Symbols StockTwits tab view' ),
		'tradepress_symbols|twelvedata'        => array( 'code' => 'TAB119', 'description' => 'Symbols Twelve Data tab view' ),
		'tradepress_symbols|ibkr'              => array( 'code' => 'TAB120', 'description' => 'Symbols Interactive Brokers tab view' ),
		'tradepress_symbols|tradingview'       => array( 'code' => 'TAB121', 'description' => 'Symbols TradingView tab view' ),
		'tradepress_symbols|marketstack'       => array( 'code' => 'TAB122', 'description' => 'Symbols Marketstack tab view' ),
		'tradepress_symbols|eodhistoricaldata' => array( 'code' => 'TAB123', 'description' => 'Symbols EOD Historical Data tab view' ),
		'tradepress_symbols|yahoofinance'      => array( 'code' => 'TAB124', 'description' => 'Symbols Yahoo Finance tab view' ),
		'tradepress_symbols|tiingo'            => array( 'code' => 'TAB125', 'description' => 'Symbols Tiingo tab view' ),
		'tradepress_symbols|alphavantage'      => array( 'code' => 'TAB126', 'description' => 'Symbols Alpha Vantage tab view' ),
		'tradepress_symbols|quandl'            => array( 'code' => 'TAB127', 'description' => 'Symbols Quandl tab view' ),
		'tradepress_symbols|fred'              => array( 'code' => 'TAB128', 'description' => 'Symbols FRED tab view' ),
		'tradepress_symbols|gemini'            => array( 'code' => 'TAB129', 'description' => 'Symbols Gemini tab view' ),
		'tradepress_symbols|webull'            => array( 'code' => 'TAB130', 'description' => 'Symbols WeBull tab view' ),
		'tradepress_symbols|trading212'        => array( 'code' => 'TAB131', 'description' => 'Symbols Trading212 tab view' ),
		'tradepress_symbols|comparisons'       => array( 'code' => 'TAB132', 'description' => 'Symbols Comparisons tab view' ),
		'tradepress_symbols|api_switches'      => array( 'code' => 'TAB133', 'description' => 'Symbols Platform Switches tab view' ),
	);
}

/**
 * Return a registered tab reference code for the current page/tab pair.
 *
 * @param string $page_slug Page slug.
 * @param string $tab_id Tab id.
 * @return string
 */
function tradepress_get_registered_tab_reference_code( $page_slug, $tab_id ) {
	$registry = tradepress_get_tab_reference_registry();
	$key      = sanitize_key( $page_slug ) . '|' . sanitize_key( $tab_id );

	if ( isset( $registry[ $key ]['code'] ) ) {
		return (string) $registry[ $key ]['code'];
	}

	return '';
}

/**
 * Remove development-only tabs when Developer Mode is disabled.
 *
 * Tab controllers should call this after local tabs and extension filters have
 * been applied. It keeps the release rule explicit: if a view exists mainly for
 * demo data, diagnostics, or unfinished workflows, list it here and hide it from
 * regular users until the view is backed by live data.
 *
 * @param array $tabs                 Associative array of tabs.
 * @param array $development_tab_ids  Tab IDs that require Developer Mode.
 * @return array Filtered tabs.
 */
function tradepress_filter_development_tabs( $tabs, $development_tab_ids ) {

	if ( tradepress_can_access_development_views() ) {
		return $tabs;
	}

	foreach ( (array) $development_tab_ids as $tab_id ) {
		unset( $tabs[ $tab_id ] );
	}

	return $tabs;
}

/**
 * Check whether a tab is only available through Developer Mode.
 *
 * @param string $tab_id              Tab ID.
 * @param array  $development_tab_ids Tab IDs that require Developer Mode.
 * @return bool True when the current tab is development-only and visible.
 */
function tradepress_is_development_tab( $tab_id, $development_tab_ids ) {

	return tradepress_can_access_development_views()
		&& in_array( (string) $tab_id, array_map( 'strval', (array) $development_tab_ids ), true );
}

/**
 * Return an escaped tab label with a Developer Mode marker when appropriate.
 *
 * Use this only when echoing tab labels; the label text is escaped here because
 * the marker intentionally contains trusted Dashicons markup.
 *
 * @param string $tab_id              Tab ID.
 * @param string $tab_label           Tab label.
 * @param array  $development_tab_ids Tab IDs that require Developer Mode.
 * @return string Escaped HTML label.
 */
function tradepress_get_development_tab_label( $tab_id, $tab_label, $development_tab_ids ) {

	$label     = esc_html( $tab_label );
	$page_slug = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	if ( function_exists( 'tradepress_is_developer_mode' ) && tradepress_is_developer_mode() ) {
		$tab_ref_code = tradepress_get_registered_tab_reference_code( $page_slug, (string) $tab_id );
		if ( '' !== $tab_ref_code ) {
			$label       .= ' <span class="tradepress-tab-reference" aria-label="' . esc_attr__( 'Tab reference code', 'tradepress' ) . '">[' . esc_html( $tab_ref_code ) . ']</span>';
		}
	}

	if ( ! tradepress_is_development_tab( $tab_id, $development_tab_ids ) ) {
		return $label;
	}

	$indicator_label = esc_attr__( 'Development mode tab', 'tradepress' );

	return $label . ' <span class="tradepress-development-tab-indicator" title="' . $indicator_label . '" aria-label="' . $indicator_label . '"><span class="dashicons dashicons-admin-tools" aria-hidden="true"></span></span>';
}
