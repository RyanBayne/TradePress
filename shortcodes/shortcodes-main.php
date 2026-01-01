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

if (!defined('ABSPATH')) {
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
 */
function TradePress_shortcode_init($atts, $content = null)
{
    global $post;

    // Apply defaults to all shortcodes (a standard accross the entire site)
    // Can trigger very different output if changed after page publication...
    $atts = shortcode_atts(array(
        'id' => null,
        'shortcode' => 'Missing',
        'channel_id' => null,
        'channel_name' => null,
        'cache' => true,
        'count' => '10',
        'first' => 10,
        'period' => '',
        'started_at' => '',
        'refresh' => 120,  /* wait this time before fetching newer data */
        'expiry' => 500,
        'cacheexpiry' => 1200, /* maximum cache life, usually longer than refresh value */
        'type' => null,
        'style' => null,
        'value' => null,
        'username' => null,
        'to_id' => null,
        'from_id' => null,
        'orderby' => null,
        'display' => 'all',
        'max' => null,
        'min' => null,
        'delete' => false, /* set to true to delete cache, use in testing only */
        'require_login' => 'yes',
        'text' => 'Lorem Ipsum Dolor',
        'defaulttext' => '',
        'nosubtext' => '',
        'scope' => '',
        'defaultcontent' => 'videos',
        'exclude' => '',
        'include' => '',
        'layout' => '',
        'long' => 'yes',
        'short' => 'yes',
        'profile' => 'default',
        'strategy' => 'default',
        'longbutton' => 'yes',
        'shortbutton' => 'yes',
        'max' => 50,
        'securities_sortables' => null
    ), $atts, 'TradePress_shortcodes_advanced_' . $atts['shortcode']);

    // Caching will be disabled for $content wrapping shortcodes...
    if ($content) {
        $atts['cache'] = true;
    }

    // Reduce cache time for admin to make site building easier...
    if (current_user_can('administrator')) {
        $atts['cacheexpiry'] = 30;
    }

    // Complete the name of requested shortcode method...
    $function_name = 'TradePress_shortcode_' . $atts['shortcode'];

    // Establish channel ID when only the channel name has been provided...
    if (isset($atts['channel_name']) && !isset($atts['channel_id'])) {
        $twitch_api = new TradePress_Twitch_API();
        $atts['channel_id'] = $twitch_api->get_channel_id_by_name($atts['channel_name']);
    }

    // Output buffer is required for this design...
    ob_start();

    // Return if cached HTML found...
    $cache_name = 'TradePress_shortcode_' . $atts['shortcode'] . '_' . $post->ID;

    // Force deletion of cache on every request (meant for roadmap/testing only)...
    if ($atts['delete']) {
        delete_transient($cache_name);
    }

    if ($atts['cache']) {
        $cache = get_transient($cache_name);
        if ($cache && isset($cache['time']) && isset($cache['content'])) {
            // If a refresh is not due then output the existing content...
            $refresh_due = $cache['time'] + $atts['refresh'];
            if ($refresh_due < time()) {
                echo $cache['content'];
                return ob_get_clean(); // return earlier due to existing cache of HTML!
            }
        }
    }

    if (!function_exists($function_name)) {
        return sprintf(
            __('A shortcode has not been configured properly or an extension is missing because the %s() function could not be found.', 'tradepress'),
            $function_name
        );
    }

    // Build new HTML content by calling specific shortcode method...
    $built_content = $function_name($atts, $content);

    // Shortcode function may return array with ['atts'] to modify behaviours...
    if (is_array($built_content)) {
        $html = $built_content['html'];
        $atts = $built_content['atts'];
    } else {
        $html = $built_content;
    }

    if ($atts['cache']) {
        $new_cache_value = array('content' => $html, 'time' => time());
        set_transient($cache_name, $new_cache_value, $atts['cacheexpiry']);
    }

    echo $html;

    return ob_get_clean();
}
add_shortcode('tradepress_shortcodes', 'TradePress_shortcode_init');

/**
 * Display securities in sortable boxes
 *
 * @since 1.0.0
 *
 * @version 2.0
 */
function TradePress_shortcode_sortable_securities($atts, $content = null)
{
    global $post;

    // Apply defaults to all shortcodes (a standard accross the entire site)
    // Can trigger very different output if changed after page publication...
    $atts = shortcode_atts(array(
        'id' => null,
        'cache' => true,
        'first' => 10,
        'period' => '',
        'started_at' => '',
        'cacheexpiry' => 120,  /* minimum cache life, wait this time before fetching newer data */
        'style' => null,
        'layout' => '',
        'value' => null,
        'orderby' => null,
        'display' => 'all',
        'max' => null,
        'min' => null,
        'defaulttext' => '',
        'exclude' => '',
        'include' => '',
        'long' => 'yes',
        'short' => 'yes',
        'profile' => 'default',
        'strategy' => 'default',
        'longbutton' => 'yes',
        'shortbutton' => 'yes',
        'max' => 50,
        'securities_sortables' => null
    ), $atts, 'tradepress_sortable_securities' );

    // Caching will be disabled for $content wrapping shortcodes...
    if ($content) {
        $atts['cache'] = true;
    }

    // Reduce cache time for admin to make site building easier...
    if (current_user_can('administrator')) {
        $atts['cacheexpiry'] = 30;
    }

    // Establish channel ID when only the channel name has been provided...
    if (isset($atts['channel_name']) && !isset($atts['channel_id'])) {
        $twitch_api = new TradePress_Twitch_API();
        $atts['channel_id'] = $twitch_api->get_channel_id_by_name($atts['channel_name']);
    }

    // Output buffer is required for this design...
    ob_start();

    // Return if cached HTML found...
    $cache_name = 'TradePress_sortable_securities_' . $post->ID;

    if ($atts['cache']) {
        $cache = get_transient($cache_name);
        if ($cache && isset($cache['time']) && isset($cache['content'])) {
            // If a refresh is not due then output the existing content...
            $refresh_due = $cache['time'] + $atts['cacheexpiry'];
            if ($refresh_due < time()) {
                echo $cache['content'];
                return ob_get_clean(); // return earlier due to existing cache of HTML!
            }
        }
    }

    // Generate the output
    require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities/sortable-securities.php');
    $shortcode_object = new TradePress_Sortable_Securities();
    $shortcode_object->atts = $atts;
    $html_output = $shortcode_object->output();

    // Cache the output if set to true
    if ($atts['cache']) {

        // Prepend developer information when developing
        if( defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ) {
            $html_output = '<h2>Developer Information</h2>
            <p>TwitchPress Content IS Cached: ' . date('Y-m-d H:i:s') . '</p> 
            <p>Cache Period: ' . $atts['cacheexpiry'] . '</p>' 
            . $html_output;
        } else {
            $html_output = '<!-- TwitchPress Content NOT Cached: ' . date('Y-m-d H:i:s') . ' -->' . $html_output;           
        }

        $new_cache_value = array('content' => $html_output, 'time' => time());
        set_transient($cache_name, $new_cache_value, $atts['cacheexpiry']);
    }

    echo $html_output;

    return ob_get_clean();
}
add_shortcode('tradepress_sortable_securities', 'TradePress_shortcode_sortable_securities');

/**
 * Register the new V2 sortable securities shortcode
 *
 * @since 1.1.0
 */
if (!function_exists('tradepress_sortable_securities_v2_shortcode')) {
    function tradepress_sortable_securities_v2_shortcode($atts) {
        // Include the class file
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities-v2/sortable-securities-v2.php';
        
        // Create instance and return output
        $shortcode = new TradePress_Sortable_Securities_V2();
        return $shortcode->output();
    }
    add_shortcode('tradepress_sortable_securities_v2', 'tradepress_sortable_securities_v2_shortcode');
}

/**
 * This function is used to test the Alpaca API connection.
 *
 * It attempts to connect to the Alpaca API and retrieve a real-time quote for NVDA.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data, or an error message.
 */
function TradePress_shortcode_alpaca_api_status($atts, $content)
{
    // Alpaca API endpoint for real-time quote (replace with the actual endpoint)
    $symbol = "NVDA";
    $alpaca_endpoint = "https://api.alpaca.markets/v2/stocks/$symbol/quotes/latest";

    // Alpaca API key and secret for live trading
    $alpaca_api_key_live = "AKYFA8GDDNB8Z7R29M37";
    $alpaca_api_secret_live = "x1zHngmgVw4MyPIiTI3BXcaxbPIjtWFAao0evJhc";

    // Alpaca API key and secret for paper trading
    $alpaca_api_key_paper = "PKLV5WJ6Z33SZQ3MBXEJ";
    $alpaca_api_secret_paper = "PKLV5WJ6Z33SZQ3MBXEJ";

    // Check if paper trading is enabled
    $use_paper_key = get_option('tradepress_paper_trading_only', false);

    // Choose which API key to use.
    if ( $use_paper_key ) {
        $alpaca_api_key = $alpaca_api_key_paper;
        $alpaca_api_secret = $alpaca_api_secret_paper;
    } else {
        $alpaca_api_key = $alpaca_api_key_live;
        $alpaca_api_secret = $alpaca_api_secret_live;
    }

    // Set up the context for the HTTP request
    $context = stream_context_create([
        'http' => [
            'header' => [
                "APCA-API-KEY-ID: $alpaca_api_key",
                "APCA-API-SECRET-KEY: $alpaca_api_secret",
            ],
        ],
    ]);

    // Attempt to fetch the data from the Alpaca API
    $response = @file_get_contents($alpaca_endpoint, false, $context);

    // Check for API request errors
    if ($response === false) {
        $error = error_get_last()['message'];
        return "Alpaca API connection failed: " . $error . "\n";
    }

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check if the response was successfully decoded
    if ($data === null) {
        return "Alpaca API response could not be decoded.";
    }

    // Check if data was received from the API
    if (isset($data['quote'])) {
        $last_trade_price = $data['quote']['lp'];
        $last_trade_timestamp = date('Y-m-d H:i:s', strtotime($data['quote']['t']));

        $api_status = "Alpaca API connection is working.\n"
            . "Sample Data:\n"
            . "Symbol: NVDA\n"
            . "Last Trade Price: $last_trade_price\n"
            . "Last Trade Time: $last_trade_timestamp\n";
    } else {
        $api_status = "Alpaca API connection is working, but no data was returned.\n" . json_encode($data);
    }

    return $api_status;
}


/**
 * This function is used to test the alpha vantage API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_alphavantage_api_status($atts, $content)
{
    // Replace with your actual Alpha Vantage API interaction logic
    $api_status = "Alpha Vantage API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}
/**
 * This function is used to test the polygon API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_polygon_api_status($atts, $content)
{
    // Replace with your actual Polygon API interaction logic
    $api_status = "Polygon API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}

/**
 * This function is used to test the tradier API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_tradier_api_status($atts, $content)
{
    // Replace with your actual Tradier API interaction logic
    $api_status = "Tradier API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}

/**
 * This function is used to test the TwelveData API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_twelvedata_api_status($atts, $content)
{
    // Replace with your actual TwelveData API interaction logic
    $api_status = "TwelveData API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}

/**
 * This function is used to test the IexCloud API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_iexcloud_api_status($atts, $content)
{
    // Replace with your actual IexCloud API interaction logic
    $api_status = "IexCloud API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}

/**
 * This function is used to test the AllTick API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_alltick_api_status($atts, $content)
{
    // Replace with your actual AllTick API interaction logic
    $api_status = "AllTick API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}

/**
 * This function is used to test the EODHD API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_eodhd_api_status($atts, $content)
{
    $symbol = "NVDA";
    $apikey = "65e88652d0d065.46673402";
    $endpoint = "https://eodhistoricaldata.com/api/real-time/$symbol?api_token=$apikey&fmt=json&s=$symbol.US";

    $data = json_decode(file_get_contents($endpoint), true);
   
    $api_status = "EODHD API connection is working.\n"
        . "Sample Data:\n"
        . json_encode($data);

    return $api_status;
}

/**
 * This function is used to test the FinnHub API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_finnhub_api_status($atts, $content)
{
    $symbol = "NVDA";
    $apikey = "cn03e9pr01qmdv715i5gcn03e9pr01qmdv715i60";
    $endpoint = "https://finnhub.io/api/v1/quote?symbol=$symbol&token=$apikey";

    $data = json_decode(file_get_contents($endpoint), true);
   
    $api_status = "FinnHub API connection is working.\n"
        . "Sample Data:\n"
        . json_encode($data);

    return $api_status;
}
/**
 * This function is used to test the FMP API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_fmp_api_status($atts, $content)
{
    $symbol = "NVDA";
    $apikey = "9e0031e24081eb6df77728742c47a87a";
    $endpoint = "https://financialmodelingprep.com/api/v3/quote/$symbol?apikey=$apikey";

    $data = json_decode(file_get_contents($endpoint), true);
   
    $api_status = "FMP API connection is working.\n"
        . "Sample Data:\n"
        . json_encode($data);

    return $api_status;
}

/**
 * This function is used to test the Intrinio API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_intrinio_api_status($atts, $content)
{
    // Replace with your actual Intrinio API interaction logic
    $api_status = "Intrinio API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}

/**
 * This function is used to test the MarketStack API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_marketstack_api_status($atts, $content)
{
    // Replace with your actual MarketStack API interaction logic
    $symbol = "NVDA";
    $apikey = "4f890a3862395efb2690a31f4582e18f";
    $endpoint = "http://api.marketstack.com/v1/eod/latest?access_key=$apikey&symbols=$symbol";
     $data = json_decode(file_get_contents($endpoint), true);
     $api_status = "MarketStack API connection is working.\n"
        . "Sample Data:\n"
        . json_encode($data);

    return $api_status;
}

/**
 * This function is used to test the Trading212 API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_trading212_api_status($atts, $content)
{
    // Replace with your actual Trading212 API interaction logic
    $api_status = "Trading212 API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}

/**
 * This function is used to test the TradingView API connection.
 *
 * It will make a simple call and return basic details.
 *
 * @param array       $atts    The attributes passed to the shortcode.
 * @param string|null $content The shortcode content (if any).
 *
 * @return string The API connection status and sample data.
 */
function TradePress_shortcode_tradingview_api_status($atts, $content)
{
    // Replace with your actual TradingView API interaction logic
    $api_status = "TradingView API connection is working.\n"
        . "Sample Data:\n"
        . "NVDA (Nvidia) - This will be replaced with live data. \n";

    return $api_status;
}

/**
 * TradePress Shortcodes Main Registration
 *
 * @package TradePress/Shortcodes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register the original sortable securities shortcode
function tradepress_register_sortable_securities_shortcode() {
    if (file_exists(TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities/sortable-securities.php')) {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities/sortable-securities.php';
        
        add_shortcode('tradepress_sortable_securities', function($atts) {
            $sortable_securities = new TradePress_Sortable_Securities();
            return $sortable_securities->output();
        });
    }
}

// Register the V2 sortable securities shortcode
function tradepress_register_sortable_securities_v2_shortcode() {
    if (file_exists(TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities-v2/sortable-securities-v2.php')) {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities-v2/sortable-securities-v2.php';
        
        add_shortcode('tradepress_sortable_securities_v2', function($atts) {
            $sortable_securities_v2 = new TradePress_Sortable_Securities_V2();
            return $sortable_securities_v2->output();
        });
    }
}

// Initialize shortcodes
add_action('init', 'tradepress_register_sortable_securities_shortcode');
add_action('init', 'tradepress_register_sortable_securities_v2_shortcode');

/**
 * Handles the [tradepress_course] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string The shortcode output.
 */
function tradepress_course_shortcode_handler($atts) {
    // For now, just return a placeholder message.
    return 'The e-learning course will be displayed here.';
}
add_shortcode('tradepress_course', 'tradepress_course_shortcode_handler');