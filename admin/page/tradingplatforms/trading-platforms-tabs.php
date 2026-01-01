<?php
/**
 * TradePress - Trading Platform API's Admin Tabs
 *
 * Handles the display and functionality of API admin tabs
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/admin/page
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_TradingPlatforms_Page' ) ) :

/**
 * TradePress_Admin_TradingPlatforms_Page Class
 */
class TradePress_Admin_TradingPlatforms_Page {

    /**
     * Constructor - Initialize hooks for menu page and assets
     */
    public function __construct() {
        // The menu page is already added by TradePress_Admin_Menus, so we don't need to add it again
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Enqueue styles and scripts for Trading Platforms page
     */
    public function enqueue_assets($hook) {
        // Check if we're on the Trading Platforms page
        if ($hook !== 'tradepress_page_tradepress_platforms') {
            return;
        }
        
        // Enqueue trading platforms specific script
        wp_enqueue_script(
            'tradepress-trading-platforms',
            TRADEPRESS_PLUGIN_URL . 'js/admin-trading-platforms.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Localize script for AJAX calls
        wp_localize_script(
            'tradepress-trading-platforms',
            'tradepress_trading_platforms',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tradepress_trading_platforms_nonce'),
            )
        );
    }

    /**
     * Output the API tabs page
     */
    public static function output() {
        // Enqueue jQuery UI for the test dialogs
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
        
        self::tabs_interface();
    }

    /**
     * Display tabs interface
     */
    private static function tabs_interface() {
        // Get the available tabs
        $tabs = self::get_tabs();

        // Get current tab - either from URL parameter or select the first tab as default
        $current_tab = empty( $_GET['tab'] ) ? '' : sanitize_title( $_GET['tab'] );

        // If no tab is selected or the selected tab doesn't exist, select the first available tab
        if ( empty( $current_tab ) || ! isset( $tabs[ $current_tab ] ) ) {
            // Get the keys of the tabs array
            $tab_keys = array_keys( $tabs );
            // Set the current tab to the first available tab
            if ( ! empty( $tab_keys ) ) {
                $current_tab = $tab_keys[0];
            }
        }
        ?>
        <div class="wrap tradepress-admin-wrap">
            <h1>
                <?php 
                echo esc_html__( 'TradePress Trading Platforms', 'tradepress' );
                if (!empty($current_tab) && isset($tabs[$current_tab])) {
                    echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
                    echo esc_html($tabs[$current_tab]['name']);
                }
                ?>
            </h1>
            <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                <?php
                foreach ( $tabs as $tab_id => $tab ) {
                    $active = ( $tab_id === $current_tab ) ? ' nav-tab-active' : '';
                    $tab_url = add_query_arg( array( 'tab' => $tab_id ), admin_url( 'admin.php?page=tradepress_platforms' ) );
                    echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . esc_attr( $active ) . '">' . esc_html( $tab['name'] ) . '</a>';
                }
                ?>
            </nav>
            
            <div class="tradepress-admin-content">
                <?php
                // Check if there are any tabs
                if ( ! empty( $tabs ) && ! empty( $current_tab ) && isset( $tabs[ $current_tab ] ) ) {
                    // Call the callback function for the active tab
                    call_user_func( $tabs[ $current_tab ]['callback'] );
                } else {
                    // Fallback message if no tabs are available
                    echo '<div class="notice notice-warning"><p>' . 
                         esc_html__( 'No trading platform tabs are currently available. Please enable platforms in the Platform Switches tab.', 'tradepress' ) . 
                         '</p></div>';
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get available tabs
     * 
     * @return array Array of tab data
     */
    public static function get_tabs() {
        $tabs = array(
            'api_management' => array(
                'name'     => __( 'API Management', 'tradepress' ),
                'callback' => array( __CLASS__, 'api_management_tab' )
            ),
            'api_efficiency' => array(
                'name'     => __( 'API Efficiency', 'tradepress' ),
                'callback' => array( __CLASS__, 'api_efficiency_tab' )
            ),
            'alltick' => array(
                'name'     => __( 'AllTick', 'tradepress' ),
                'callback' => array( __CLASS__, 'alltick_tab' )
            ),
            'alpaca' => array(
                'name'     => __( 'Alpaca', 'tradepress' ),
                'callback' => array( __CLASS__, 'alpaca_tab' )
            ),
            'iexcloud' => array(
                'name'     => __( 'IEX Cloud', 'tradepress' ),
                'callback' => array( __CLASS__, 'iexcloud_tab' )
            ),
            'polygon' => array(
                'name'     => __( 'Polygon', 'tradepress' ),
                'callback' => array( __CLASS__, 'polygon_tab' )
            ),
            'tradier' => array(
                'name'     => __( 'Tradier', 'tradepress' ),
                'callback' => array( __CLASS__, 'tradier_tab' )
            ),
            'finnhub' => array(
                'name'     => __( 'Finnhub', 'tradepress' ),
                'callback' => array( __CLASS__, 'finnhub_tab' )
            ),
            'twitter' => array(
                'name'     => __( 'Twitter', 'tradepress' ),
                'callback' => array( __CLASS__, 'twitter_tab' )
            ),
            'stocktwits' => array(
                'name'     => __( 'StockTwits', 'tradepress' ),
                'callback' => array( __CLASS__, 'stocktwits_tab' )
            ),
            'twelvedata' => array(
                'name'     => __( 'Twelve Data', 'tradepress' ),
                'callback' => array( __CLASS__, 'twelvedata_tab' )
            ),
            'ibkr' => array(
                'name'     => __( 'Interactive Brokers', 'tradepress' ),
                'callback' => array( __CLASS__, 'ibkr_tab' )
            ),
            'tradingview' => array(
                'name'     => __( 'TradingView', 'tradepress' ),
                'callback' => array( __CLASS__, 'tradingview_tab' )
            ),
            'marketstack' => array(
                'name'     => __( 'Marketstack', 'tradepress' ),
                'callback' => array( __CLASS__, 'marketstack_tab' )
            ),
            'eodhistoricaldata' => array(
                'name'     => __( 'EOD Historical Data', 'tradepress' ),
                'callback' => array( __CLASS__, 'eodhistoricaldata_tab' )
            ),
            'yahoofinance' => array(
                'name'     => __( 'Yahoo Finance', 'tradepress' ),
                'callback' => array( __CLASS__, 'yahoofinance_tab' )
            ),
            'tiingo' => array(
                'name'     => __( 'Tiingo', 'tradepress' ),
                'callback' => array( __CLASS__, 'tiingo_tab' )
            ),
            'alphavantage' => array(
                'name'     => __( 'Alpha Vantage', 'tradepress' ),
                'callback' => array( __CLASS__, 'alphavantage_tab' )
            ),
            'quandl' => array(
                'name'     => __( 'Quandl', 'tradepress' ),
                'callback' => array( __CLASS__, 'quandl_tab' )
            ),
            'fred' => array(
                'name'     => __( 'FRED', 'tradepress' ),
                'callback' => array( __CLASS__, 'fred_tab' )
            ),
            'gemini' => array(
                'name'     => __( 'Gemini', 'tradepress' ),
                'callback' => array( __CLASS__, 'gemini_tab' )
            ),
            'webull' => array(
                'name'     => __( 'WeBull', 'tradepress' ),
                'callback' => array( __CLASS__, 'webull_tab' )
            ),
            'trading212' => array(
                'name'     => __( 'Trading212', 'tradepress' ),
                'callback' => array( __CLASS__, 'trading212_tab' )
            ),
            'comparisons' => array(
                'name'     => __( 'API Provider Comparisons', 'tradepress' ),
                'callback' => array( __CLASS__, 'comparisons_tab' )
            ),
            'api_switches' => array(
                'name'     => __( 'Trading Platform Switches', 'tradepress' ),
                'callback' => array( __CLASS__, 'api_switches_tab' )
            ),
        );
        
        // Filter tabs to only show APIs that have been enabled for display
        $filtered_tabs = array();
        
        // Check each API if its tab should be displayed
        foreach ($tabs as $api_id => $tab) {
            // Skip the API Switches and comparisons tabs - we'll add them at the end
            if ($api_id === 'api_switches' || $api_id === 'comparisons') {
                continue;
            }
            
            // Check if this API tab should be displayed
            $show_tab = false;
            
            // Standard option name format - this controls tab visibility
            $option_name = 'TradePress_switch_' . $api_id . '_api_services';
            if (get_option($option_name) === 'yes') {
                $show_tab = true;
            }
            
            // Alternative option name formats (some APIs might use different conventions)
            $alt_option_names = array(
                'tradepress_' . $api_id . '_activated',
                'tradepress_switch_' . $api_id . '_services',
                'tradepress_' . $api_id . '_api_active',
                'tradepress_api_' . $api_id . '_active'
            );
            
            foreach ($alt_option_names as $alt_name) {
                if (get_option($alt_name) === 'yes') {
                    $show_tab = true;
                    break;
                }
            }
            
            // Special case handling for certain APIs
            if ($api_id === 'trading212') {
                // Check if Trading212 API key exists and is not empty
                $api_settings = get_option('tradepress_api_settings', array());
                if (!empty($api_settings['trading212_api_key'])) {
                    $show_tab = true;
                }
            }
            
            // Include the tab if it should be displayed
            if ($show_tab) {
                $filtered_tabs[$api_id] = $tab;
            }
        }
        
        // Add API Management tab first (always visible)
        if (isset($tabs['api_management'])) {
            $api_management_tab = $tabs['api_management'];
            $filtered_tabs = array('api_management' => $api_management_tab) + $filtered_tabs;
        }
        
        // Add API Efficiency tab second (always visible)
        if (isset($tabs['api_efficiency'])) {
            $api_efficiency_tab = $tabs['api_efficiency'];
            $filtered_tabs = array_slice($filtered_tabs, 0, 1, true) + 
                           array('api_efficiency' => $api_efficiency_tab) + 
                           array_slice($filtered_tabs, 1, null, true);
        }
        
        // Add Comparisons tab second-to-last
        if (isset($tabs['comparisons'])) {
            $filtered_tabs['comparisons'] = $tabs['comparisons'];
        }
        
        // Add API Switches tab last
        if (isset($tabs['api_switches'])) {
            $filtered_tabs['api_switches'] = $tabs['api_switches'];
        }
        
        return apply_filters('tradepress_api_tabs', $filtered_tabs);
    }

    /**
     * AllTick tab content
     */
    public static function alltick_tab() {
        // Check for specific AllTick actions
        if (isset($_POST['alltick_action']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'alltick_api_action')) {
            $action = sanitize_text_field($_POST['alltick_action']);
            
            switch ($action) {
                case 'clear_cache':
                    // Handle cache clearing
                    delete_transient('tradepress_alltick_market_data');
                    delete_transient('tradepress_alltick_quotes');
                    delete_transient('tradepress_alltick_historical');
                    // Add success message
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . 
                             esc_html__('AllTick cache cleared successfully.', 'tradepress') . 
                             '</p></div>';
                    });
                    break;
                    
                case 'refresh_symbols':
                    // Handle symbol list refresh
                    delete_transient('tradepress_alltick_symbols');
                    // Trigger symbol refresh action
                    do_action('tradepress_refresh_alltick_symbols');
                    // Add success message
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . 
                             esc_html__('Symbol list refresh initiated.', 'tradepress') . 
                             '</p></div>';
                    });
                    break;
                    
                case 'update_settings':
                    // Save AllTick specific settings
                    if (isset($_POST['alltick_update_frequency'])) {
                        update_option('tradepress_alltick_update_frequency', 
                                     intval($_POST['alltick_update_frequency']));
                    }
                    
                    if (isset($_POST['alltick_premium_access'])) {
                        update_option('tradepress_alltick_premium_access', 'yes');
                    } else {
                        update_option('tradepress_alltick_premium_access', 'no');
                    }
                    
                    // Add success message
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . 
                             esc_html__('AllTick settings updated successfully.', 'tradepress') . 
                             '</p></div>';
                    });
                    break;
            }
        }
        
        include( dirname( __FILE__ ) . '/view/view.alltick.php' );
    }

    /**
     * Alpaca tab content
     */
    public static function alpaca_tab() {
        include( dirname( __FILE__ ) . '/view/view.alpaca.php' );
    }
    
    /**
     * IEX Cloud tab content
     */
    public static function iexcloud_tab() {
        include( dirname( __FILE__ ) . '/view/view.iexcloud.php' );
    }
    
    /**
     * Polygon tab content
     */
    public static function polygon_tab() {
        include( dirname( __FILE__ ) . '/view/view.polygon.php' );
    }
    
    /**
     * Tradier tab content
     */
    public static function tradier_tab() {
        include( dirname( __FILE__ ) . '/view/view.tradier.php' );
    }
    
    /**
     * Finnhub tab content
     */
    public static function finnhub_tab() {
        include( dirname( __FILE__ ) . '/view/view.finnhub.php' );
    }
    
    /**
     * Twitter tab content
     */
    public static function twitter_tab() {
        include( dirname( __FILE__ ) . '/view/view.twitter.php' );
    }
    
    /**
     * StockTwits tab content
     */
    public static function stocktwits_tab() {
        include( dirname( __FILE__ ) . '/view/view.stocktwits.php' );
    }
    
    /**
     * Twelve Data tab content
     */
    public static function twelvedata_tab() {
        include( dirname( __FILE__ ) . '/view/view.twelvedata.php' );
    }

    /**
     * Interactive Brokers tab content
     */
    public static function ibkr_tab() {
        include( dirname( __FILE__ ) . '/view/view.ibkr.php' );
    }
    
    /**
     * TradingView tab content
     */
    public static function tradingview_tab() {
        include( dirname( __FILE__ ) . '/view/view.tradingview.php' );
    }
    
    /**
     * Marketstack tab content
     */
    public static function marketstack_tab() {
        include( dirname( __FILE__ ) . '/view/view.marketstack.php' );
    }
    
    /**
     * EOD Historical Data tab content
     */
    public static function eodhistoricaldata_tab() {
        include( dirname( __FILE__ ) . '/view/view.eodhistoricaldata.php' );
    }

    /**
     * Yahoo Finance tab content
     */
    public static function yahoofinance_tab() {
        include( dirname( __FILE__ ) . '/view/view.yahoofinance.php' );
    }
    
    /**
     * Tiingo tab content
     */
    public static function tiingo_tab() {
        include( dirname( __FILE__ ) . '/view/view.tiingo.php' );
    }
    
    /**
     * Alpha Vantage tab content
     */
    public static function alphavantage_tab() {
        include( dirname( __FILE__ ) . '/view/view.alphavantage.php' );
    }
    
    /**
     * Quandl tab content
     */
    public static function quandl_tab() {
        include( dirname( __FILE__ ) . '/view/view.quandl.php' );
    }
    
    /**
     * FRED tab content
     */
    public static function fred_tab() {
        include( dirname( __FILE__ ) . '/view/view.fred.php' );
    }

    /**
     * Gemini tab content
     */
    public static function gemini_tab() {
        include( dirname( __FILE__ ) . '/view/view.gemini.php' );
    }

    /**
     * WeBull tab content
     */
    public static function webull_tab() {
        include( dirname( __FILE__ ) . '/view/view.webull.php' );
    }

    /**
     * Trading212 tab content
     */
    public static function trading212_tab() {
        include( dirname( __FILE__ ) . '/view/view.trading212.php' );
    }

    /**
     * Comparisons tab content
     */
    public static function comparisons_tab() {
        require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/tradingplatforms/comparisons.php';
        if (function_exists('tradepress_trading_platforms_comparisons_tab')) {
            tradepress_trading_platforms_comparisons_tab();
        }
    }

    /**
     * Overview tab content
     */
    public static function overview_tab() {
        include( dirname( __FILE__ ) . '/view/view.overview.php' );
    }

    /**
     * Settings tab content 
     * 
     * @todo this method doesn't appear to be used in the current context, is it included for completeness or should it be removed?
     */
    public static function settings_tab() {
        // Process form submission for API switches
        if (isset($_POST['save_api_switches']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'tradepress-api-settings-switches')) {
            
            // Load the API Directory
            if (!class_exists('TradePress_API_Directory')) {
                require_once TRADEPRESS_PLUGIN_DIR_PATH . '/api/api-directory.php';
            }
            
            // Get all API providers
            $all_providers = TradePress_API_Directory::get_all_providers();
            $api_array = array_keys($all_providers);
            
            // Save API switches
            foreach ($api_array as $api) {
                // API services switch
                $switch_name = 'switch_' . $api . '_services';
                $option_name = 'TradePress_switch_' . $api . '_api_services';
                update_option($option_name, isset($_POST[$switch_name]) ? 'yes' : 'no');
                
                // API logs switch
                $log_switch_name = 'switch_' . $api . '_logs';
                $log_option_name = 'TradePress_switch_' . $api . '_api_logs';
                update_option($log_option_name, isset($_POST[$log_switch_name]) ? 'yes' : 'no');
            }
            
            // Set a transient for success message
            set_transient('tradepress_api_settings_updated', true, 30);
            
            // Redirect to avoid form resubmission
            wp_redirect(add_query_arg('tab', 'settings', admin_url('admin.php?page=tradepress_platforms')));
            exit;
        }
        
        // Process form submission for provider-specific settings
        if (isset($_POST['save_api_provider']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'tradepress-api-settings-provider')) {
            $service = sanitize_text_field($_POST['api_provider']);
            
            // Service switches
            $switches = array('services', 'logs', 'premium');
            foreach ($switches as $switch) {
                $switch_name = 'switch_' . $service . '_' . $switch;
                $option_name = 'TradePress_switch_' . $service . '_api_' . $switch;
                update_option($option_name, isset($_POST[$switch_name]) ? 'yes' : 'no');
            }
            
            // API keys
            $key_types = array('realmoney', 'papermoney');
            foreach ($key_types as $type) {
                $key_name = 'api_' . $service . '_' . $type . '_apikey';
                $option_name = 'TradePress_api_' . $service . '_' . $type . '_apikey';
                
                if (isset($_POST[$key_name])) {
                    update_option($option_name, sanitize_text_field($_POST[$key_name]));
                }
            }
            
            // Twitter-specific fields
            if ($service == 'twitter' && isset($_POST['api_' . $service . '_token_secret'])) {
                update_option('TradePress_allapi_' . $service . '_default_access_token_secret', 
                              sanitize_text_field($_POST['api_' . $service . '_token_secret']));
            }
            
            // Run any service-specific procedures
            do_action('TradePress_allapi_application_update_' . $service, $service, 
                     get_option('TradePress_api_' . $service . '_realmoney_apikey', ''),
                     get_option('TradePress_api_' . $service . '_papermoney_apikey', ''));
            
            // Set a transient for success message
            set_transient('tradepress_api_settings_updated', true, 30);
            
            // Redirect to avoid form resubmission
            wp_redirect(add_query_arg(array('tab' => 'settings', 'section' => $service), admin_url('admin.php?page=tradepress_platforms')));
            exit;
        }
        
        // Show success message if settings were updated
        if (get_transient('tradepress_api_settings_updated')) {
            add_action('admin_notices', array(__CLASS__, 'settings_updated_notice'));
            delete_transient('tradepress_api_settings_updated');
        }
        
        include( dirname( __FILE__ ) . '/view/sp-settings.php' );
    }

    /**
     * Display notice when settings are updated
     */
    public static function settings_updated_notice() {
        echo '<div class="notice notice-success is-dismissible"><p>' . 
             esc_html__('API settings updated successfully.', 'tradepress') . 
             '</p></div>';
    }

    /**
     * API Management tab content
     */
    public static function api_management_tab() {
        include( dirname( __FILE__ ) . '/view/view.api_management.php' );
    }

    /**
     * API Efficiency tab content
     */
    public static function api_efficiency_tab() {
        include( dirname( __FILE__ ) . '/view/view.api_efficiency.php' );
    }

    /**
     * API Switches tab content
     */
    public static function api_switches_tab() {
        // Process form submission for API switches
        if (isset($_POST['save_api_switches']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'tradepress-api-switches')) {
            
            // Load the API Directory
            if (!class_exists('TradePress_API_Directory')) {
                require_once TRADEPRESS_PLUGIN_DIR_PATH . '/api/api-directory.php';
            }
            
            // Get all API providers
            $all_providers = TradePress_API_Directory::get_all_providers();
            $api_array = array_keys($all_providers);
            
            // Save API switches
            foreach ($api_array as $api) {
                // API services switch
                $switch_name = 'switch_' . $api . '_services';
                $option_name = 'TradePress_switch_' . $api . '_api_services';
                update_option($option_name, isset($_POST[$switch_name]) ? 'yes' : 'no');
                
                // API logs switch
                $log_switch_name = 'switch_' . $api . '_logs';
                $log_option_name = 'TradePress_switch_' . $api . '_api_logs';
                update_option($log_option_name, isset($_POST[$log_switch_name]) ? 'yes' : 'no');
            }
            
            // Set a transient for success message
            set_transient('tradepress_api_settings_updated', true, 30);
            
            // Redirect to avoid form resubmission
            wp_redirect(add_query_arg('tab', 'api_switches', admin_url('admin.php?page=tradepress_platforms')));
            exit;
        }
        
        // Show success message if settings were updated
        if (get_transient('tradepress_api_settings_updated')) {
            add_action('admin_notices', array(__CLASS__, 'settings_updated_notice'));
            delete_transient('tradepress_api_settings_updated');
        }
        
        include( dirname( __FILE__ ) . '/view/view.api_switches.php' );
    }
}

endif;

// Initialize the class to set up hooks
new TradePress_Admin_TradingPlatforms_Page();
