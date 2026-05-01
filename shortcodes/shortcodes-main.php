<?php
/**
 * TradePress - Include shortcode files here (added April 2018)
 *
 * There are some shortcodes that do not have their own file and are loaded in
 * another file pre-2018.
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TradePress/Core
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Master shortcode function for calling one of many methods that will actually generate content.
 *
 * All shortcode setup begins with this function. Do not create more lines of "add_shortcode" without good reason.
 *
 * @since 1.0.0
 *
 * @version 2.0
 *
 * @param mixed $atts
 * @param mixed $content
 */
function TradePress_shortcode_init( $atts, $content = null ) {
	global $post;

	// Apply defaults to all shortcodes (a standard accross the entire site)
	// Can trigger very different output if changed after page publication...
	$atts = shortcode_atts(
		array(
			'id'                   => null,
			'shortcode'            => 'Missing',
			'channel_id'           => null,
			'channel_name'         => null,
			'cache'                => true,
			'count'                => '10',
			'first'                => 10,
			'period'               => '',
			'started_at'           => '',
			'refresh'              => 120,  /* wait this time before fetching newer data */
			'expiry'               => 500,
			'cacheexpiry'          => 1200, /* maximum cache life, usually longer than refresh value */
			'type'                 => null,
			'style'                => null,
			'value'                => null,
			'username'             => null,
			'to_id'                => null,
			'from_id'              => null,
			'orderby'              => null,
			'display'              => 'all',
			'max'                  => null,
			'min'                  => null,
			'delete'               => false, /* set to true to delete cache, use in testing only */
			'require_login'        => 'yes',
			'text'                 => 'Lorem Ipsum Dolor',
			'defaulttext'          => '',
			'nosubtext'            => '',
			'scope'                => '',
			'defaultcontent'       => 'videos',
			'exclude'              => '',
			'include'              => '',
			'layout'               => '',
			'long'                 => 'yes',
			'short'                => 'yes',
			'profile'              => 'default',
			'strategy'             => 'default',
			'longbutton'           => 'yes',
			'shortbutton'          => 'yes',
			'max'                  => 50,
			'securities_sortables' => null,
		),
		$atts,
		'TradePress_shortcodes_advanced_' . $atts['shortcode']
	);

	// Caching will be disabled for $content wrapping shortcodes...
	if ( $content ) {
		$atts['cache'] = true;
	}

	// Reduce cache time for admin to make site building easier...
	if ( current_user_can( 'administrator' ) ) {
		$atts['cacheexpiry'] = 30;
	}

	// Complete the name of requested shortcode method...
	$function_name = 'TradePress_shortcode_' . $atts['shortcode'];

	// Establish channel ID when only the channel name has been provided...
	if ( isset( $atts['channel_name'] ) && ! isset( $atts['channel_id'] ) ) {
		$twitch_api         = new TradePress_Twitch_API();
		$atts['channel_id'] = $twitch_api->get_channel_id_by_name( $atts['channel_name'] );
	}

	// Output buffer is required for this design...
	ob_start();

	// Return if cached HTML found...
	$cache_name = 'TradePress_shortcode_' . $atts['shortcode'] . '_' . $post->ID;

	// Force deletion of cache on every request (meant for roadmap/testing only)...
	if ( $atts['delete'] ) {
		delete_transient( $cache_name );
	}

	if ( $atts['cache'] ) {
		$cache = get_transient( $cache_name );
		if ( $cache && isset( $cache['time'] ) && isset( $cache['content'] ) ) {
			// If a refresh is not due then output the existing content...
			$refresh_due = $cache['time'] + $atts['refresh'];
			if ( $refresh_due < time() ) {
				echo esc_html( $cache['content'] );
				return ob_get_clean(); // return earlier due to existing cache of HTML!
			}
		}
	}

	if ( ! function_exists( $function_name ) ) {
		return sprintf(
			/* translators: %s: string value */
			__( 'A shortcode has not been configured properly or an extension is missing because the %s() function could not be found.', 'tradepress' ),
			$function_name
		);
	}

	// Build new HTML content by calling specific shortcode method...
	$built_content = $function_name( $atts, $content );

	// Shortcode function may return array with ['atts'] to modify behaviours...
	if ( is_array( $built_content ) ) {
		$html = $built_content['html'];
		$atts = $built_content['atts'];
	} else {
		$html = $built_content;
	}

	if ( $atts['cache'] ) {
		$new_cache_value = array(
			'content' => $html,
			'time'    => time(),
		);
		set_transient( $cache_name, $new_cache_value, $atts['cacheexpiry'] );
	}

	echo esc_html( $html );

	return ob_get_clean();
}
add_shortcode( 'tradepress_shortcodes', 'TradePress_shortcode_init' );

/**
 * Display securities in sortable boxes
 *
 * @since 1.0.0
 *
 * @version 2.0
 *
 * @param mixed $atts
 * @param mixed $content
 */
function TradePress_shortcode_sortable_securities( $atts, $content = null ) {
	global $post;

	// Apply defaults to all shortcodes (a standard accross the entire site)
	// Can trigger very different output if changed after page publication...
	$atts = shortcode_atts(
		array(
			'id'                   => null,
			'cache'                => true,
			'first'                => 10,
			'period'               => '',
			'started_at'           => '',
			'cacheexpiry'          => 120,  /* minimum cache life, wait this time before fetching newer data */
			'style'                => null,
			'layout'               => '',
			'value'                => null,
			'orderby'              => null,
			'display'              => 'all',
			'max'                  => null,
			'min'                  => null,
			'defaulttext'          => '',
			'exclude'              => '',
			'include'              => '',
			'long'                 => 'yes',
			'short'                => 'yes',
			'profile'              => 'default',
			'strategy'             => 'default',
			'longbutton'           => 'yes',
			'shortbutton'          => 'yes',
			'max'                  => 50,
			'securities_sortables' => null,
		),
		$atts,
		'tradepress_sortable_securities'
	);

	// Caching will be disabled for $content wrapping shortcodes...
	if ( $content ) {
		$atts['cache'] = true;
	}

	// Reduce cache time for admin to make site building easier...
	if ( current_user_can( 'administrator' ) ) {
		$atts['cacheexpiry'] = 30;
	}

	// Establish channel ID when only the channel name has been provided...
	if ( isset( $atts['channel_name'] ) && ! isset( $atts['channel_id'] ) ) {
		$twitch_api         = new TradePress_Twitch_API();
		$atts['channel_id'] = $twitch_api->get_channel_id_by_name( $atts['channel_name'] );
	}

	// Output buffer is required for this design...
	ob_start();

	// Return if cached HTML found...
	$cache_name = 'TradePress_sortable_securities_' . $post->ID;

	if ( $atts['cache'] ) {
		$cache = get_transient( $cache_name );
		if ( $cache && isset( $cache['time'] ) && isset( $cache['content'] ) ) {
			// If a refresh is not due then output the existing content...
			$refresh_due = $cache['time'] + $atts['cacheexpiry'];
			if ( $refresh_due < time() ) {
				echo esc_html( $cache['content'] );
				return ob_get_clean(); // return earlier due to existing cache of HTML!
			}
		}
	}

	// Generate the output
	require_once TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities/sortable-securities.php';
	$shortcode_object       = new TradePress_Sortable_Securities();
	$shortcode_object->atts = $atts;
	$html_output            = $shortcode_object->output();

	// Cache the output if set to true
	if ( $atts['cache'] ) {

		// Prepend developer information when developing
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$html_output = '<h2>Developer Information</h2>
            <p>TwitchPress Content IS Cached: ' . date( 'Y-m-d H:i:s' ) . '</p> 
            <p>Cache Period: ' . $atts['cacheexpiry'] . '</p>'
			. $html_output;
		} else {
			$html_output = '<!-- TwitchPress Content NOT Cached: ' . date( 'Y-m-d H:i:s' ) . ' -->' . $html_output;
		}

		$new_cache_value = array(
			'content' => $html_output,
			'time'    => time(),
		);
		set_transient( $cache_name, $new_cache_value, $atts['cacheexpiry'] );
	}

	echo esc_html( $html_output );

	return ob_get_clean();
}
add_shortcode( 'tradepress_sortable_securities', 'TradePress_shortcode_sortable_securities' );

/**
 * Register the new V2 sortable securities shortcode
 *
 * @since 1.1.0
 */
if ( ! function_exists( 'tradepress_sortable_securities_v2_shortcode' ) ) {
	/**
	 * Sortable securities v2 shortcode.
	 *
	 * @param mixed $atts
	 *
	 * @return mixed
	 *
	 * @version 1.0.0
	 */
	function tradepress_sortable_securities_v2_shortcode( $atts ) {
		// Include the class file
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities-v2/sortable-securities-v2.php';

		// Create instance and return output
		$shortcode = new TradePress_Sortable_Securities_V2();
		return $shortcode->output();
	}
	add_shortcode( 'tradepress_sortable_securities_v2', 'tradepress_sortable_securities_v2_shortcode' );
}

/**
 * Return a clear unavailable message for legacy API status shortcodes.
 *
 * These shortcodes previously returned misleading provider status output.
 * They now fail closed until each provider is wired to real configured credentials.
 *
 * @param string $provider Provider display name.
 * @return string
 */
function tradepress_api_status_shortcode_unavailable( $provider ) {
	return sprintf(
		/* translators: %s: API provider name */
		__( '%s API status is not available from this legacy shortcode. Configure and test the provider from Trading Platforms using real credentials/data.', 'tradepress' ),
		$provider
	);
}

function TradePress_shortcode_alpaca_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'Alpaca' );
}

function TradePress_shortcode_alphavantage_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'Alpha Vantage' );
}

function TradePress_shortcode_polygon_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'Polygon' );
}

function TradePress_shortcode_tradier_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'Tradier' );
}

function TradePress_shortcode_twelvedata_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'TwelveData' );
}

function TradePress_shortcode_iexcloud_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'IEX Cloud' );
}

function TradePress_shortcode_alltick_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'AllTick' );
}

function TradePress_shortcode_eodhd_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'EODHD' );
}

function TradePress_shortcode_finnhub_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'Finnhub' );
}

function TradePress_shortcode_fmp_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'Financial Modeling Prep' );
}

function TradePress_shortcode_intrinio_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'Intrinio' );
}

function TradePress_shortcode_marketstack_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'MarketStack' );
}

function TradePress_shortcode_trading212_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'Trading212' );
}

function TradePress_shortcode_tradingview_api_status( $atts, $content ) {
	return tradepress_api_status_shortcode_unavailable( 'TradingView' );
}
/**
 * TradePress Shortcodes Main Registration
 *
 * @package TradePress/Shortcodes
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Register the original sortable securities shortcode
/**
 * Register sortable securities shortcode.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function tradepress_register_sortable_securities_shortcode() {
	if ( file_exists( TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities/sortable-securities.php' ) ) {
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities/sortable-securities.php';

		add_shortcode(
			'tradepress_sortable_securities',
			function ( $atts ) {
				$sortable_securities = new TradePress_Sortable_Securities();
				return $sortable_securities->output();
			}
		);
	}
}

// Register the V2 sortable securities shortcode
/**
 * Register sortable securities v2 shortcode.
 *
 * @return mixed
 *
 * @version 1.0.0
 */
function tradepress_register_sortable_securities_v2_shortcode() {
	if ( file_exists( TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities-v2/sortable-securities-v2.php' ) ) {
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities-v2/sortable-securities-v2.php';

		add_shortcode(
			'tradepress_sortable_securities_v2',
			function ( $atts ) {
				$sortable_securities_v2 = new TradePress_Sortable_Securities_V2();
				return $sortable_securities_v2->output();
			}
		);
	}
}

// Initialize shortcodes
add_action( 'init', 'tradepress_register_sortable_securities_shortcode' );
add_action( 'init', 'tradepress_register_sortable_securities_v2_shortcode' );

/**
 * Handles the [tradepress_course] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode output.
 * @version 1.0.0
 */
function tradepress_course_shortcode_handler( $atts ) {
	// For now, just return a placeholder message.
	return 'The e-learning course will be displayed here.';
}
add_shortcode( 'tradepress_course', 'tradepress_course_shortcode_handler' );
