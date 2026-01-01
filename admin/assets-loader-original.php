<?php
/**
 * TradePress - Original Assets Loader (this is slowly being phased out for a system that is maintained within the assets directory)
 *
 * Load admin only js, css, images and fonts. 
 *
 * @author   Ryan Bayne
 * @category Loading
 * @package  TradePress/Loading
 * @since    1.0.0
 * @version  1.3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_Assets' ) ) :

/**
 * TradePress_Admin_Assets Class.
 */
class TradePress_Admin_Assets {

    /**
     * Enqueue styles for the admin side.
     * 
     * @version 1.7
     */
    public function admin_styles() {
        global $wp_scripts, $tradepress_assets;
        
        // Screen ID Must be set for later arguments
        $screen         = get_current_screen();
        $screen_id      = $screen ? $screen->id : '';
        
        $jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';
                               
        // Register admin styles with consolidated file structure
        wp_register_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version );
        wp_register_style( 'tradepress-setup', TRADEPRESS_PLUGIN_URL . 'assets/css/pages/setup.css', array('dashicons'), TRADEPRESS_VERSION );
        wp_register_style( 'tradepress-test-runner', TRADEPRESS_PLUGIN_URL . 'assets/css/test-runner.css', array(), TRADEPRESS_VERSION );
        
        // Register dashboard widgets CSS
        wp_register_style(
            'tradepress-dashboard-widgets',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/dashboard.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Enqueue dashboard widgets CSS on dashboard
        if ($screen_id === 'dashboard') {
            wp_enqueue_style('tradepress-dashboard-widgets');
        }
        
        // Register data page styles
        wp_register_style(
            'tradepress-data-page',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/data.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register data elements styles
        wp_register_style(
            'tradepress-data-elements',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/data-elements.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Enqueue data page styles when on the data page
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_data') {
            wp_enqueue_style('tradepress-data-page');
            wp_enqueue_style('tradepress-data-elements');
            wp_enqueue_style('tradepress-admin-database');
        }
        
        // Register analysis page styles
        wp_register_style(
            'tradepress-analysis',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/analysis.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Enqueue analysis page assets when on the analysis page
        if (isset($_GET['page']) && ($_GET['page'] === 'tradepress_analysis' || $_GET['page'] === 'tradepress-analysis')) {
            wp_enqueue_style('tradepress-analysis');
        }
        
        // Enqueue analysis page styles when on the analysis page
        if (isset($_GET['page']) && ($_GET['page'] === 'tradepress_analysis' || $_GET['page'] === 'tradepress-analysis')) {
            wp_enqueue_style('tradepress-analysis');
        }
        
        // Register and enqueue all component stylesheets from the asset manager
        if ($tradepress_assets && is_object($tradepress_assets)) {
            $component_assets = $tradepress_assets->get_assets_by_category('css', 'components');
            foreach ($component_assets as $name => $asset) {
                wp_register_style(
                    'tradepress-component-' . $name,
                    TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'],
                    $asset['dependencies'],
                    TRADEPRESS_VERSION
                );
                wp_enqueue_style('tradepress-component-' . $name);
            }
        }
        
        // Register the admin-api-tabs.css file with the correct path
        wp_register_style( 'tradepress-admin-api-tabs', TRADEPRESS_PLUGIN_URL . 'assets/css/admin-api-tabs.css', array(), TRADEPRESS_VERSION );
        
        // Register admin notices positioning CSS
        wp_register_style( 'tradepress-admin-notices-positioning', TRADEPRESS_PLUGIN_URL . 'assets/css/admin-notices-positioning.css', array(), TRADEPRESS_VERSION );
        wp_enqueue_style( 'tradepress-admin-notices-positioning' );
        
        // Register mode indicators CSS
        wp_register_style( 'tradepress-mode-indicators', TRADEPRESS_PLUGIN_URL . 'assets/css/components/mode-indicators.css', array(), TRADEPRESS_VERSION );
        
        // Enqueue mode indicators CSS when developer mode is active
        $developer_mode = get_option('tradepress_developer_mode', false);
        if ($developer_mode === true || $developer_mode === 1 || $developer_mode === '1' || $developer_mode === 'yes') {
            wp_enqueue_style( 'tradepress-mode-indicators' );
        }
        
        // Register Discord API tab styles
        wp_register_style(
            'tradepress-api-discord',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/api-discord.css',
            array(),
            TRADEPRESS_VERSION
        );

        // Register research earnings tab styles
        wp_register_style( 
            'tradepress-research-earnings-tab', 
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/research-earnings-tab.css', 
            array(), 
            TRADEPRESS_VERSION 
        );
        
        // Register research news feed tab styles
        wp_register_style( 
            'tradepress-research-news-feed-tab', 
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/research-news-feed.css', 
            array(), 
            TRADEPRESS_VERSION 
        );
        
        // Register calculators styles - Updated to use new page-specific file
        wp_register_style(
            'tradepress-calculators',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/calculators.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register research page styles
        wp_register_style(
            'tradepress-research-page',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/research.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register economic calendar styles
        wp_register_style(
            'tradepress-economic-calendar',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/economic-calendar.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register market correlations styles
        wp_register_style(
            'tradepress-market-correlations',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/market-correlations.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register scoring directives logs styles
        wp_register_style(
            'tradepress-scoring-directives-logs',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/scoring-directives-logs.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register scoring directives overview styles
        wp_register_style(
            'tradepress-scoring-directives-overview',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/scoring-directives-overview.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register scoring directives styles
        wp_register_style(
            'tradepress-scoring-directives',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/scoring-directives.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register price forecast styles
        wp_register_style(
            'tradepress-price-forecast',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/price-forecast.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register sector rotation styles
        wp_register_style(
            'tradepress-sector-rotation',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/sector-rotation.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register social platforms switches styles
        wp_register_style(
            'tradepress-socialplatforms-switches',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/socialplatforms-switches.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register social platforms settings styles
        wp_register_style(
            'tradepress-socialplatforms-settings',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/socialplatforms-settings.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register StockTwits platform styles
        wp_register_style(
            'tradepress-socialplatforms-stocktwits',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/socialplatforms-stocktwits.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Twitter platform styles
        wp_register_style(
            'tradepress-socialplatforms-twitter',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/socialplatforms-twitter.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Discord settings styles
        wp_register_style(
            'tradepress-socialplatforms-discord-settings',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/socialplatforms-discord-settings.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Create Strategy styles
        wp_register_style(
            'tradepress-trading-create-strategy',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/trading-create-strategy.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Portfolio styles
        wp_register_style(
            'tradepress-trading-portfolio',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/trading-portfolio.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Trading Platforms Comparisons styles
        wp_register_style(
            'tradepress-tradingplatforms-comparisons',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-comparisons.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Trading Platforms Comparisons Toggles styles
        wp_register_style(
            'tradepress-tradingplatforms-comparisons-toggles',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-comparisons-toggles.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Alpha Vantage API tab styles
        wp_register_style(
            'tradepress-tradingplatforms-alphavantage',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-alphavantage.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register API Switches tab styles
        wp_register_style(
            'tradepress-tradingplatforms-api-switches',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-api-switches.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Endpoints tab styles
        wp_register_style(
            'tradepress-tradingplatforms-endpoints',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-endpoints.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Trading API tab styles
        wp_register_style(
            'tradepress-tradingplatforms-tradingapi',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-tradingapi.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Trading API Configuration partial styles
        wp_register_style(
            'tradepress-tradingplatforms-config-trading',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-config-trading.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Diagnostic Buttons partial styles
        wp_register_style(
            'tradepress-tradingplatforms-diagnostic-buttons',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-diagnostic-buttons.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Endpoints Table partial styles
        wp_register_style(
            'tradepress-tradingplatforms-endpoints-table',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-endpoints-table.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Admin styles for WordPress TradePress pages only
        if ( in_array( $screen_id, TradePress_get_screen_ids() ) ) {
            // Other styles are loaded conditionally below
            wp_register_style( 'tradepress-trading-page', TRADEPRESS_PLUGIN_URL . 'assets/css/admin-trading-page.css', array(), TRADEPRESS_VERSION ); 
        }
        
        // Enqueue research page styles when on the research page
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_research') {
            wp_enqueue_style('tradepress-research-page');
            
            // Enqueue economic calendar styles when on the economic-calendar tab
            if (!isset($_GET['tab']) || $_GET['tab'] === 'economic-calendar') {
                wp_enqueue_style('tradepress-economic-calendar');
            }
            
            // Enqueue market correlations styles when on the market-correlations tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'market-correlations') {
                wp_enqueue_style('tradepress-market-correlations');
            }
        }
        
        // Setup wizard styles
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'tradepress-setup' ) {
            wp_enqueue_style( 'tradepress-setup' );
        }
        
        // Test runner page
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'tradepress-tests' ) {
            wp_enqueue_style( 'tradepress-test-runner' );
        }
        
        // API tabs and settings pages - Updated to include Trading Platforms page
        if ( isset( $_GET['page'] ) && ( 
            $_GET['page'] === 'tradepress' || 
            strpos( $_GET['page'], 'tradepress-api' ) !== false ||
            $_GET['page'] === 'tradepress_platforms' // Added Trading Platforms page
        ) ) {
            wp_enqueue_style( 'tradepress-admin-api-tabs' );
            wp_enqueue_style( 'tradepress-api-discord' );
            
            // Enqueue core API layout styles for Trading Platforms page
            if ($_GET['page'] === 'tradepress_platforms') {
                wp_enqueue_style(
                    'tradepress-api-layout',
                    TRADEPRESS_PLUGIN_URL . 'assets/css/layouts/api.css',
                    array(),
                    TRADEPRESS_VERSION
                );
                
                // Enqueue Configure Directives layout for API Management tab
                wp_enqueue_style('tradepress-configure-directives');
                wp_enqueue_style('tradepress-api-management');
                
                // Enqueue essential component styles for API forms
                wp_enqueue_style(
                    'tradepress-component-forms',
                    TRADEPRESS_PLUGIN_URL . 'assets/css/components/forms.css',
                    array(),
                    TRADEPRESS_VERSION
                );
                
                wp_enqueue_style(
                    'tradepress-component-status',
                    TRADEPRESS_PLUGIN_URL . 'assets/css/components/status.css',
                    array(),
                    TRADEPRESS_VERSION
                );
                
                wp_enqueue_style(
                    'tradepress-component-switches',
                    TRADEPRESS_PLUGIN_URL . 'assets/css/components/switches.css',
                    array(),
                    TRADEPRESS_VERSION
                );
            }
            
            // Enqueue comparisons tab styles and scripts when on the comparisons tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'comparisons') {
                wp_enqueue_style('tradepress-tradingplatforms-comparisons');
                wp_enqueue_style('tradepress-tradingplatforms-comparisons-toggles');
                wp_enqueue_script('tradepress-tradingplatforms-comparisons');
            }
            
            // Enqueue Alpha Vantage API tab styles and scripts when on the alphavantage tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'alphavantage') {
                wp_enqueue_style('tradepress-tradingplatforms-alphavantage');
                wp_enqueue_script('tradepress-tradingplatforms-alphavantage');
                
                // Localize script with translation strings and nonces
                wp_localize_script(
                    'tradepress-tradingplatforms-alphavantage',
                    'tradepress_alphavantage_params',
                    array(
                        'nonce' => wp_create_nonce('tradepress_test_alphavantage_endpoint'),
                        'random_symbol' => isset($random_symbol) ? $random_symbol : 'AAPL',
                        'enter_symbol_text' => __('Please enter a symbol', 'tradepress'),
                        'error_text' => __('An error occurred while fetching data', 'tradepress')
                    )
                );
            }
            
            // Enqueue API Switches tab styles when on the api_switches tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'api_switches') {
                wp_enqueue_style('tradepress-tradingplatforms-api-switches');
            }
            
            // Enqueue Endpoints tab styles and scripts when on the endpoints tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'endpoints') {
                wp_enqueue_style('tradepress-tradingplatforms-endpoints');
                wp_enqueue_script('tradepress-tradingplatforms-endpoints');
                
                // Localize script with translation strings and nonces
                wp_localize_script(
                    'tradepress-tradingplatforms-endpoints',
                    'tradepress_endpoints_params',
                    array(
                        'nonce' => wp_create_nonce('tradepress_test_endpoint_nonce'),
                        'current_platform' => isset($_GET['platform']) ? sanitize_text_field($_GET['platform']) : 'alpaca',
                        'testing_text' => __('Testing...', 'tradepress'),
                        'test_text' => __('Test', 'tradepress'),
                        'testing_endpoint_text' => __('Testing endpoint...', 'tradepress'),
                        'test_results_text' => __('Endpoint Test Results', 'tradepress'),
                        'test_successful_text' => __('Test Successful', 'tradepress'),
                        'test_failed_text' => __('Test Failed', 'tradepress'),
                        'platform_text' => __('Platform', 'tradepress'),
                        'time_text' => __('Time', 'tradepress'),
                        'response_data_text' => __('Response Data', 'tradepress'),
                        'troubleshooting_tips_text' => __('Troubleshooting Tips', 'tradepress'),
                        'tip_api_key_text' => __('Check that your API key is properly configured in the platform settings.', 'tradepress'),
                        'tip_rate_limits_text' => __('Verify that you have not exceeded API rate limits.', 'tradepress'),
                        'tip_subscription_text' => __('Ensure that the endpoint is available in your subscription tier.', 'tradepress'),
                        'tip_network_text' => __('Check network connectivity to the API server.', 'tradepress'),
                        'ajax_error_text' => __('AJAX Error', 'tradepress')
                    )
                );
            }
            
            // Enqueue Trading API tab styles and scripts when on the tradingapi tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'tradingapi') {
                wp_enqueue_style('tradepress-tradingplatforms-tradingapi');
                wp_enqueue_script('tradepress-tradingplatforms-tradingapi');
                
                // Localize script with translation strings
                wp_localize_script(
                    'tradepress-tradingplatforms-tradingapi',
                    'tradepress_tradingapi_params',
                    array(
                        'copied_text' => __('Copied!', 'tradepress'),
                        'copy_text' => __('Copy', 'tradepress'),
                        'testing_text' => __('Testing...', 'tradepress'),
                        'test_connection_text' => __('Test Connection', 'tradepress'),
                        'success_text' => __('Success:', 'tradepress'),
                        'connection_success_text' => __('Connection to API server established successfully.', 'tradepress'),
                        'api_version_text' => __('API Version:', 'tradepress'),
                        'server_time_text' => __('Server Time:', 'tradepress'),
                        'response_time_text' => __('Response Time:', 'tradepress'),
                        'confirm_clear_logs_text' => __('Are you sure you want to clear all logs? This cannot be undone.', 'tradepress')
                    )
                );
            }
            
            // Always enqueue config-trading partial styles and scripts for all trading platform tabs
            // since this partial can be included in multiple views
            wp_enqueue_style('tradepress-tradingplatforms-config-trading');
            wp_enqueue_script('tradepress-tradingplatforms-config-trading');
            
            // Also enqueue data-only configuration styles for data-only APIs like Alpha Vantage
            wp_enqueue_style(
                'tradepress-config-data-only',
                TRADEPRESS_PLUGIN_URL . 'assets/css/pages/tradingplatforms-config-data-only.css',
                array(),
                TRADEPRESS_VERSION
            );
            
            // Enqueue core trading platforms layout styles for all tabs
            wp_enqueue_style(
                'tradepress-tradingplatforms-layout',
                TRADEPRESS_PLUGIN_URL . 'assets/css/layouts/tradingplatforms.css',
                array(),
                TRADEPRESS_VERSION
            );
            
            // Always enqueue diagnostic-buttons partial styles and scripts for all trading platform tabs
            // since this partial can be included in multiple views
            wp_enqueue_style('tradepress-tradingplatforms-diagnostic-buttons');
            wp_enqueue_script('tradepress-tradingplatforms-diagnostic-buttons');
            
            // Always enqueue endpoints-table partial styles and scripts for all trading platform tabs
            // since this partial can be included in multiple views
            wp_enqueue_style('tradepress-tradingplatforms-endpoints-table');
            wp_enqueue_script('tradepress-tradingplatforms-endpoints-table');
            
            // Always enqueue event-debugger partial script for all trading platform tabs
            // since this partial can be included in multiple views
            wp_enqueue_script('tradepress-tradingplatforms-event-debugger');
            
            // Ensure diagnostics component CSS is loaded for event debugger partial
            wp_enqueue_style('tradepress-component-diagnostics');
            
            // Localize script with translation strings
            wp_localize_script(
                'tradepress-tradingplatforms-config-trading',
                'tradepress_config_trading_params',
                array(
                    'nonce' => wp_create_nonce('tradepress_test_api_connection'),
                    'testing_text' => __('Testing...', 'tradepress'),
                    'test_live_api_text' => __('Test Live API Connection', 'tradepress'),
                    'test_paper_api_text' => __('Test Paper API Connection', 'tradepress'),
                    'enter_api_credentials_text' => __('Please enter both API key and secret before testing.', 'tradepress'),
                    'enter_paper_api_credentials_text' => __('Please enter both paper API key and secret before testing.', 'tradepress'),
                    'live_api_success_text' => __('Live API connection successful!', 'tradepress'),
                    'live_api_failed_text' => __('Live API connection failed: ', 'tradepress'),
                    'paper_api_success_text' => __('Paper API connection successful!', 'tradepress'),
                    'paper_api_failed_text' => __('Paper API connection failed: ', 'tradepress'),
                    'connection_error_text' => __('Connection test failed due to a server error. Please check your logs.', 'tradepress')
                )
            );
        }
        
        // Database admin page and Data Tables page - Updated to use layouts file
        wp_register_style(
            'tradepress-admin-database',
            TRADEPRESS_PLUGIN_URL . 'assets/css/layouts/database.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        if ( 
            // Original condition for settings page with database tab
            (isset( $_GET['page'] ) && isset( $_GET['tab'] ) && $_GET['page'] === 'tradepress' && $_GET['tab'] === 'database') ||
            // New condition for data page
            (isset( $_GET['page'] ) && $_GET['page'] === 'tradepress_data')
        ) {
            wp_enqueue_style( 'tradepress-admin-database' );
        }
        
        // Database settings page
        wp_register_style(
            'tradepress-settings-database',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/settings-database.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress' && 
            isset($_GET['tab']) && $_GET['tab'] === 'database') {
            wp_enqueue_style('tradepress-settings-database');
        }
        
        // General settings page
        wp_register_style(
            'tradepress-settings-general',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/settings-general.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress' && 
            (!isset($_GET['tab']) || $_GET['tab'] === 'general')) {
            wp_enqueue_style('tradepress-settings-general');
        }
        
        // Shortcodes settings page
        wp_register_style(
            'tradepress-settings-shortcodes',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/settings-shortcodes.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress' && 
            isset($_GET['tab']) && $_GET['tab'] === 'shortcodes') {
            wp_enqueue_style('tradepress-settings-shortcodes');
        }
        
        // Tab features settings page
        wp_register_style(
            'tradepress-settings-tab-features',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/settings-tab-features.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        if ( 'tradepress_page_tradepress_bot' === $screen_id ) {
            wp_enqueue_style(
                'tradepress-admin-bot',
                TRADEPRESS_PLUGIN_URL . 'assets/css/admin-bot.css',
                array('tradepress-admin-theme'),
                TRADEPRESS_VERSION
            );
        }
        
        if ( 'tradepress_page_tradepress_automation' === $screen_id ) {
            wp_enqueue_style(
                'tradepress-admin-automation',
                TRADEPRESS_PLUGIN_URL . 'assets/css/layouts/automation.css',
                array(),
                TRADEPRESS_VERSION
            );
            
            // Enqueue the admin-automation.css for dashboard tab styling
            wp_enqueue_style(
                'tradepress-admin-automation-components',
                TRADEPRESS_PLUGIN_URL . 'assets/css/admin-automation.css',
                array(),
                TRADEPRESS_VERSION
            );
            
            // Load pointers CSS and scripts for settings tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'settings') {
                wp_enqueue_style('wp-pointer');
                wp_enqueue_script('wp-pointer');
                wp_enqueue_style(
                    'tradepress-pointers',
                    TRADEPRESS_PLUGIN_URL . 'assets/css/components/pointers.css',
                    array(),
                    TRADEPRESS_VERSION
                );
                wp_enqueue_script(
                    'tradepress-automation-settings-pointer-test',
                    TRADEPRESS_PLUGIN_URL . 'assets/js/automation-settings-pointer-test.js',
                    array('jquery', 'wp-pointer'),
                    TRADEPRESS_VERSION
                );
            }
        }
        
        // Research page - Earnings tab styles
        if ( isset($_GET['page']) && $_GET['page'] === 'tradepress_research' && 
             (!isset($_GET['tab']) || isset($_GET['tab']) && $_GET['tab'] === 'earnings') ) {
            wp_enqueue_style( 'tradepress-research-earnings-tab' );
        }

        // Research page - News Feed tab styles
        if ( isset($_GET['page']) && $_GET['page'] === 'tradepress_research' && 
             isset($_GET['tab']) && $_GET['tab'] === 'news_feed' ) {
            wp_enqueue_style( 'tradepress-research-news-feed-tab' );
        }

        // Settings page with Fave Tabs section
        if (isset($_GET['page']) && isset($_GET['tab']) && isset($_GET['section']) && 
            $_GET['page'] === 'tradepress' && 
            $_GET['tab'] === 'general' && 
            $_GET['section'] === 'favetabs') {
            wp_enqueue_style('tradepress-settings-favetabs');
        }
        
        // Development page - pointers table styles
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_development' && 
            isset($_GET['tab']) && $_GET['tab'] === 'pointers') {
            wp_enqueue_style(
                'tradepress-education-pointers',
                TRADEPRESS_PLUGIN_URL . 'assets/css/pages/education-pointers.css',
                array(),
                TRADEPRESS_VERSION
            );
        }
        
        // Register development current task styles
        wp_register_style(
            'tradepress-development-current-task',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/development-current-task.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register focus advisor styles
        wp_register_style(
            'tradepress-focus-advisor',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/focus-advisor.css',
            array(),
            TRADEPRESS_VERSION
        );
        

        
        // Register API management styles
        wp_register_style(
            'tradepress-api-management',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/api-management.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register configure directives styles
        wp_register_style(
            'tradepress-configure-directives',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/configure-directives.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Development page styles - Current Task tab - Fixed conditional logic
        if ( isset($_GET['page']) && $_GET['page'] === 'tradepress_development' ) {
            // Load current task styles for current_task tab specifically
            if ( !isset($_GET['tab']) || $_GET['tab'] === 'current_task' ) {
                wp_enqueue_style('tradepress-development-current-task');
            }
            

            
            // Load development page CSS (includes assets tab styles)
            wp_enqueue_style(
                'tradepress-admin-development',
                TRADEPRESS_PLUGIN_URL . 'assets/css/pages/development.css',
                array(),
                TRADEPRESS_VERSION
            );

            // Enqueue development tasks styles
            wp_enqueue_style(
                'tradepress-development-tasks',
                TRADEPRESS_PLUGIN_URL . 'assets/css/pages/development-tasks.css',
                array(),
                TRADEPRESS_VERSION
            );

            // Enqueue development assets tab styles
            wp_enqueue_style(
                'tradepress-development-assets',
                TRADEPRESS_PLUGIN_URL . 'assets/css/pages/development-assets.css',
                array(),
                TRADEPRESS_VERSION
            );
            
            // Load jQuery UI styles for jQuery UI tab
            if ( isset($_GET['tab']) && $_GET['tab'] === 'jquery_ui' ) {
                wp_enqueue_style('jquery-ui-style');
                wp_enqueue_style(
                    'tradepress-jquery-ui-demo',
                    TRADEPRESS_PLUGIN_URL . 'assets/css/pages/jquery-ui.css',
                    array(),
                    TRADEPRESS_VERSION
                );
            }
            
            // Add debugging to check if styles are being loaded
            error_log('TradePress: Loading development page styles');
            
            if ( isset($_GET['tab']) && $_GET['tab'] === 'current_task' ) {
                error_log('TradePress: Current task tab detected - styles should be loaded');
            }
        }
        
        // Register data page styles
        wp_register_style(
            'tradepress-data-page',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/data.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register data elements styles
        wp_register_style(
            'tradepress-data-elements',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/data-elements.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Data page - Enqueue when on data page
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_data') {
            wp_enqueue_style('tradepress-data-page');
            
            // Data Elements tab styles
            if (isset($_GET['tab']) && $_GET['tab'] === 'data-elements') {
                wp_enqueue_style('tradepress-data-elements');
            }
        }
        
        // Scoring directives logs page
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives' && 
            isset($_GET['tab']) && $_GET['tab'] === 'logs') {
            wp_enqueue_style('tradepress-scoring-directives-logs');
        }
        
        // Directives status page
        wp_register_style(
            'tradepress-directives-status',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/directives-status.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives' && 
            isset($_GET['tab']) && $_GET['tab'] === 'directives_status') {
            wp_enqueue_style('tradepress-directives-status');
        }
        
        // Scoring directives overview page
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives' && 
            (!isset($_GET['tab']) || $_GET['tab'] === 'overview')) {
            wp_enqueue_style('tradepress-scoring-directives-overview');
        }
        
        // Scoring directives configure page - load pointers CSS and scripts
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives' && 
            isset($_GET['tab']) && $_GET['tab'] === 'configure_directives') {
            wp_enqueue_style('tradepress-scoring-directives');
            wp_enqueue_style('wp-pointer');
            wp_enqueue_script('wp-pointer');
            wp_enqueue_style(
                'tradepress-pointers',
                TRADEPRESS_PLUGIN_URL . 'assets/css/components/pointers.css',
                array(),
                TRADEPRESS_VERSION
            );
            wp_enqueue_script(
                'tradepress-configure-directives-testing-pointer',
                TRADEPRESS_PLUGIN_URL . 'assets/js/configure-directives-testing-pointer.js',
                array('jquery', 'wp-pointer'),
                TRADEPRESS_VERSION
            );
            wp_localize_script(
                'tradepress-configure-directives-testing-pointer',
                'tradepress_testing_pointer',
                array(
                    'title' => __('Testing Your Configuration', 'tradepress'),
                    'content' => __('Use this section to test how your directive configuration performs with real market data. Select a trading mode and symbol, then click Test to see the scoring results.', 'tradepress'),
                    'nonce' => wp_create_nonce('tradepress_pointer_check')
                )
            );
            
            // Add AJAX handler for pointer status check
            add_action('wp_ajax_tradepress_check_pointer_status', function() {
                check_ajax_referer('tradepress_pointer_check', 'nonce');
                $pointer = sanitize_text_field($_POST['pointer']);
                $dismissed_pointers = explode(',', (string) get_user_meta(get_current_user_id(), 'dismissed_wp_pointers', true));
                wp_send_json(array('dismissed' => in_array($pointer, $dismissed_pointers)));
            });
        }
        
        // Price forecast page
        if (isset($_GET['page']) && ($_GET['page'] === 'tradepress_research' || $_GET['page'] === 'tradepress-research') && 
            (!isset($_GET['tab']) || $_GET['tab'] === 'price_forecast')) {
            wp_enqueue_style('tradepress-price-forecast');
        }
        
        // Sector rotation page
        if (isset($_GET['page']) && ($_GET['page'] === 'tradepress_research' || $_GET['page'] === 'tradepress-research') && 
            isset($_GET['tab']) && $_GET['tab'] === 'sector-rotation') {
            wp_enqueue_style('tradepress-sector-rotation');
        }
        
        // Social platforms switches page
        if (isset($_GET['page']) && 
            ($_GET['page'] === 'tradepress_socialplatforms' || $_GET['page'] === 'TradePress_social') && 
            (!isset($_GET['tab']) || $_GET['tab'] === 'platform_switches')) {
            wp_enqueue_style('tradepress-socialplatforms-switches');
        }
        
        // Register watchlists active symbols styles
        wp_register_style(
            'tradepress-watchlists-active-symbols',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/watchlists-active-symbols.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register watchlists create watchlist styles
        wp_register_style(
            'tradepress-watchlists-create-watchlist',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/watchlists-create-watchlist.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register watchlists user watchlists styles
        wp_register_style(
            'tradepress-watchlists-user-watchlists',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/watchlists-user-watchlists.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register education dashboard styles
        wp_register_style(
            'tradepress-education-dashboard',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/education-dashboard.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Register Discord Simple Admin styles
        wp_register_style(
            'tradepress-discord-simple-admin',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/discord-simple-admin.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        // Watchlists page - enqueue appropriate styles based on tab
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_watchlists') {
            if (!isset($_GET['tab']) || $_GET['tab'] === 'active_symbols') {
                wp_enqueue_style('tradepress-watchlists-active-symbols');
            } else if (isset($_GET['tab']) && $_GET['tab'] === 'create_watchlist') {
                wp_enqueue_style('tradepress-watchlists-create-watchlist');
            } else if (isset($_GET['tab']) && $_GET['tab'] === 'user_watchlists') {
                wp_enqueue_style('tradepress-watchlists-user-watchlists');
            }
        }
        
        // Social platforms settings page
        if (isset($_GET['page']) && $_GET['page'] === 'TradePress_social') {
            // Either no tab specified (default view) or tab is 'settings'
            if (!isset($_GET['tab']) || $_GET['tab'] === 'settings') {
                wp_enqueue_style('tradepress-socialplatforms-settings');
            }
        }
        
        // StockTwits platform page
        if (isset($_GET['page']) && ($_GET['page'] === 'tradepress_socialplatforms' || $_GET['page'] === 'TradePress_social') && 
            isset($_GET['tab']) && $_GET['tab'] === 'stocktwits') {
            wp_enqueue_style('tradepress-socialplatforms-stocktwits');
            wp_enqueue_script('tradepress-socialplatforms-stocktwits');
        }
        
        // Twitter platform page
        if (isset($_GET['page']) && ($_GET['page'] === 'tradepress_socialplatforms' || $_GET['page'] === 'TradePress_social') && 
            isset($_GET['tab']) && $_GET['tab'] === 'twitter') {
            wp_enqueue_style('tradepress-socialplatforms-twitter');
        }
        
        // Discord settings page
        if (isset($_GET['page']) && ($_GET['page'] === 'tradepress_socialplatforms' || $_GET['page'] === 'TradePress_social') && 
            isset($_GET['tab']) && $_GET['tab'] === 'settings' && 
            isset($_GET['section']) && $_GET['section'] === 'discord') {
            wp_enqueue_style('tradepress-socialplatforms-discord-settings');
            wp_enqueue_script('tradepress-socialplatforms-discord-settings');
            
            // Localize script with translation strings and nonces
            wp_localize_script(
                'tradepress-socialplatforms-discord-settings',
                'TRADEPRESS_DISCORD_settings',
                array(
                    'nonces' => array(
                        'test' => wp_create_nonce('TRADEPRESS_DISCORD_test_nonce'),
                        'status' => wp_create_nonce('TRADEPRESS_DISCORD_status_nonce')
                    ),
                    'strings' => array(
                        'enter_token' => __('Please enter a bot token to test.', 'tradepress'),
                        'connection_success' => __('Connection successful!', 'tradepress'),
                        'connection_failed' => __('Connection failed:', 'tradepress'),
                        'error_testing' => __('Error testing Discord connection.', 'tradepress'),
                        'error_refreshing' => __('Error refreshing status.', 'tradepress'),
                        'token_warning' => __('Warning: Token format doesn\'t appear to be valid', 'tradepress'),
                        'bot_name' => __('Bot Name:', 'tradepress'),
                        'bot_id' => __('Bot ID:', 'tradepress')
                    )
                )
            );
        }
        
        // Trading Strategies page (merged tab with sub-tabs)
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_trading' && 
            isset($_GET['tab']) && $_GET['tab'] === 'trading-strategies') {
            wp_enqueue_style('tradepress-trading-create-strategy');
            wp_enqueue_script('tradepress-trading-create-strategy');
            wp_enqueue_style('tradepress-trading-strategies');
            wp_enqueue_script('tradepress-trading-strategies');
        }
        
        // Portfolio page
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_trading' && 
            isset($_GET['tab']) && $_GET['tab'] === 'portfolio') {
            wp_enqueue_style('tradepress-trading-portfolio');
            wp_enqueue_script('tradepress-trading-portfolio');
        }
        
        // Scoring directives main page
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives' && 
            isset($_GET['tab']) && $_GET['tab'] === 'scoring_directives') {
            wp_enqueue_style('tradepress-scoring-directives');
        }
        
        // Scoring strategies page
        wp_register_style(
            'tradepress-scoring-strategies',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/scoring-strategies.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives' && 
            isset($_GET['tab']) && $_GET['tab'] === 'strategies') {
            wp_enqueue_style('tradepress-scoring-strategies');
        }
        
        // Trading strategies page
        wp_register_style(
            'tradepress-trading-strategies',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/trading-strategies.css',
            array(),
            TRADEPRESS_VERSION
        );
        

        
        // Directives testing page
        wp_register_style(
            'tradepress-directives-testing',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/directives-testing.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives' && 
            isset($_GET['tab']) && $_GET['tab'] === 'testing') {
            wp_enqueue_style('tradepress-directives-testing');
        }
        
        // Scoring directives page - Enqueue styles for all tabs
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives') {
            wp_enqueue_style('tradepress-scoring-directives');
        }
    }

    /**
     * Enqueue scripts for the admin side.
     * 
     * @version 1.6
     */
    public function admin_scripts() {
        $screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $action    = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
        $page      = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
        $tab       = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';

        // Register and enqueue jQuery UI core and accordion
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-accordion');
        
        // Register central accordion script
        wp_register_script(
            'tradepress-accordion',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-accordion.js',
            array('jquery', 'jquery-ui-accordion'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register test runner script
        wp_register_script(
            'tradepress-test-runner',
            TRADEPRESS_PLUGIN_URL . 'assets/js/test-runner.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Always load the accordion script on admin pages
        if (is_admin()) {
            wp_enqueue_script('tradepress-accordion');
        }
        
        // Test runner page script
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress-tests') {
            wp_enqueue_script('tradepress-test-runner');
            wp_localize_script('tradepress-test-runner', 'tradePressTests', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tradepress_tests'),
                'i18n' => array(
                    'running' => __('Running...', 'tradepress'),
                    'run' => __('Run', 'tradepress'),
                    'runPhase3' => __('Run Phase 3 Tests', 'tradepress'),
                    'error' => __('Error occurred during test execution', 'tradepress'),
                    'ajaxError' => __('AJAX request failed', 'tradepress'),
                    'resultsCleared' => __('Test results cleared.', 'tradepress'),
                    'testResults' => __('Test Results', 'tradepress'),
                    'overallStatus' => __('Overall Status', 'tradepress'),
                    'executionTime' => __('Execution Time', 'tradepress'),
                    'requirementsSatisfied' => __('Requirements Satisfied', 'tradepress'),
                    'individualResults' => __('Individual Test Results', 'tradepress'),
                    'requirement' => __('Requirement', 'tradepress'),
                    'performanceSummary' => __('Performance Summary', 'tradepress'),
                    'apiCallsSaved' => __('API Calls Saved', 'tradepress'),
                    'cacheHitRate' => __('Cache Hit Rate', 'tradepress'),
                    'memoryUsage' => __('Memory Usage', 'tradepress'),
                    'recommendations' => __('Recommendations', 'tradepress')
                )
            ));
        }

        // Specific styles and scripts for the SEES Demo tab
        if ( 'tradepress_page_tradepress_trading' === $screen_id && 'sees-demo' === $tab ) {
            wp_enqueue_style( 'tradepress-sees-demo-styles', TRADEPRESS_PLUGIN_URL . 'assets/css/pages/sees-demo.css', array(), TRADEPRESS_VERSION );
            wp_enqueue_script( 'tradepress-sees-demo-script', TRADEPRESS_PLUGIN_URL . 'assets/js/sees-demo.js', array('jquery'), TRADEPRESS_VERSION, true );
        }

        // Register scripts
        if ( in_array( $screen_id, TradePress_get_screen_ids() ) ) {   

            // Enqueue admin trading page scripts     
            wp_register_script(
                'tradepress-admin-trading-page',
                TRADEPRESS_PLUGIN_URL . 'assets/js/admin-trading-page.js', 
                array('jquery', 'jquery-ui-tabs'), // Corrected: Added jquery-ui-tabs as a dependency
                TRADEPRESS_VERSION,
                true
            );
            wp_enqueue_script( 'tradepress-admin-trading-page' );
            
        }

        // API tabs script - Register and enqueue the admin-api-tab.js file
        wp_register_script(
            'tradepress-admin-api-tab',
            TRADEPRESS_PLUGIN_URL . 'assets/js/admin-api-tab.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register tables tab script - Now centralized here instead of in tables-tab.php
        wp_register_script(
            'tradepress-tables-tab',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-tables-tab.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register earnings tab script
        wp_register_script(
            'tradepress-earnings-tab-script',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-earnings-tab.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register economic calendar script
        wp_register_script(
            'tradepress-economic-calendar',
            TRADEPRESS_PLUGIN_URL . 'assets/js/economic-calendar.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register market correlations script
        wp_register_script(
            'tradepress-market-correlations',
            TRADEPRESS_PLUGIN_URL . 'assets/js/market-correlations.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register calculators script - Updated to use assets folder
        wp_register_script(
            'tradepress-calculators',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-calculators.js',
            array('jquery', 'jquery-ui-tabs'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register development tabs script
        wp_register_script(
            'tradepress-development-tabs',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-development-tabs.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register current task script
        wp_register_script(
            'tradepress-current-task',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-current-task.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register assets tab script
        wp_register_script(
            'tradepress-assets-tab',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-assets-tab.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register CRON tab script
        wp_register_script(
            'tradepress-cron-tab',
            TRADEPRESS_PLUGIN_URL . 'assets/js/cron-tab.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register scoring directives script
        wp_register_script(
            'tradepress-scoring-directives',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-scoring-directives.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register StockTwits platform script
        wp_register_script(
            'tradepress-socialplatforms-stocktwits',
            TRADEPRESS_PLUGIN_URL . 'assets/js/socialplatforms-stocktwits.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Discord settings script
        wp_register_script(
            'tradepress-socialplatforms-discord-settings',
            TRADEPRESS_PLUGIN_URL . 'assets/js/socialplatforms-discord-settings.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Create Strategy script
        wp_register_script(
            'tradepress-trading-create-strategy',
            TRADEPRESS_PLUGIN_URL . 'assets/js/trading-create-strategy.js',
            array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Portfolio script
        wp_register_script(
            'tradepress-trading-portfolio',
            TRADEPRESS_PLUGIN_URL . 'assets/js/trading-portfolio.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Trading Platforms Comparisons script
        wp_register_script(
            'tradepress-tradingplatforms-comparisons',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-comparisons.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Alpha Vantage API tab script
        wp_register_script(
            'tradepress-tradingplatforms-alphavantage',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-alphavantage.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Endpoints tab script
        wp_register_script(
            'tradepress-tradingplatforms-endpoints',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-endpoints.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Trading API tab script
        wp_register_script(
            'tradepress-tradingplatforms-tradingapi',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-tradingapi.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Trading API Configuration partial script
        wp_register_script(
            'tradepress-tradingplatforms-config-trading',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-config-trading.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Diagnostic Buttons partial script
        wp_register_script(
            'tradepress-tradingplatforms-diagnostic-buttons',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-diagnostic-buttons.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Endpoints Table partial script
        wp_register_script(
            'tradepress-tradingplatforms-endpoints-table',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-endpoints-table.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register Event Debugger partial script
        wp_register_script(
            'tradepress-tradingplatforms-event-debugger',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradingplatforms-event-debugger.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register technical analysis script
        wp_register_script(
            'tradepress-admin-technical-analysis',
            TRADEPRESS_PLUGIN_URL . 'assets/js/admin-technical-analysis.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register data page script
        wp_register_script(
            'tradepress-admin-data',
            TRADEPRESS_PLUGIN_URL . 'assets/js/admin-data.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register research page script
        wp_register_script(
            'tradepress-research',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-research.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register settings page script
        wp_register_script(
            'tradepress-settings',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-settings.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register trading strategies script
        wp_register_script(
            'tradepress-trading-strategies',
            TRADEPRESS_PLUGIN_URL . 'assets/js/trading-strategies.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Register watchlists script
        wp_register_script(
            'tradepress-watchlists',
            TRADEPRESS_PLUGIN_URL . 'assets/js/tradepress-watchlists.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Enqueue API tabs script on the same pages as the API tabs CSS
        if ( isset( $_GET['page'] ) && ( 
            $_GET['page'] === 'tradepress-settings' || 
            strpos( $_GET['page'], 'tradepress-api' ) !== false ||
            $_GET['page'] === 'tradepress_platforms'
        ) ) {
            wp_enqueue_script('tradepress-admin-api-tab');
        }
        
        // Development page scripts
        if ( isset($_GET['page']) && $_GET['page'] === 'tradepress_development' ) {
            wp_enqueue_script('tradepress-development-tabs');
            
            // Localize development tabs script
            wp_localize_script(
                'tradepress-development-tabs',
                'tradepress_dev_tabs',
                array(
                    'nonce' => wp_create_nonce('tradepress_load_log_file'),
                    'ajaxurl' => admin_url('admin-ajax.php')
                )
            );
            
            // Add additional localization for assets tracker if on assets tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'assets') {
                wp_localize_script(
                    'tradepress-development-tabs',
                    'tradepressData',
                    array(
                        'confirmMoveFile' => __('Are you sure you want to move this file to the assets folder?', 'tradepress'),
                        'confirmDeleteFile' => __('Are you sure you want to delete this file? This action cannot be undone.', 'tradepress'),
                        'movingText' => __('Moving...', 'tradepress'),
                        'deletingText' => __('Deleting...', 'tradepress'),
                        'moveFileMessage' => __('File move functionality would be implemented here', 'tradepress'),
                        'deleteFileMessage' => __('File delete functionality would be implemented here', 'tradepress'),
                        'moveToAssetsText' => __('Move to Assets', 'tradepress'),
                        'deleteText' => __('Delete', 'tradepress')
                    )
                );
            }
            
            // Assets tab
            if ( isset($_GET['tab']) && $_GET['tab'] === 'assets' ) {
                wp_enqueue_script('tradepress-assets-tab');
            }
            
            // Current Task tab
            if ( isset($_GET['tab']) && $_GET['tab'] === 'current_task' ) {
                wp_enqueue_script('tradepress-current-task');
                
                // Add localized variables for the JavaScript
                wp_localize_script(
                    'tradepress-current-task',
                    'tradepressCurrentTask',
                    array(
                        'ajaxurl' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('tradepress_current_task_nonce'),
                        'isDemo' => defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE ? true : false,
                        'strings' => array(
                            'creating_issue' => __('Creating GitHub issue...', 'tradepress'),
                            'error_creating_issue' => __('Error creating GitHub issue', 'tradepress'),
                            'start_working' => __('Start Current Task', 'tradepress'),
                            'issue_created' => __('GitHub issue created', 'tradepress'),
                            'demo_mode_active' => __('Demo Mode Active', 'tradepress')
                        ),
                        'repo_owner' => get_option('TRADEPRESS_GITHUB_repo_owner', ''),
                        'repo_name' => get_option('TRADEPRESS_GITHUB_repo_name', '')
                    )
                );
            }
        }
        
        // Tables tab script - enqueue for data page with tables tab active
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_data' && 
            (!isset($_GET['tab']) || $_GET['tab'] === 'tables')) {
            
            // Store table data in a global variable for use in the JS file
            global $wpdb;
            
            wp_enqueue_script('tradepress-tables-tab');
            
            // Localize the script with necessary data
            wp_localize_script(
                'tradepress-tables-tab',
                'tradePressTables',
                array(
                    'wpdbPrefix' => $wpdb->prefix,
                    'tableData' => isset($GLOBALS['tradepress_table_data']) ? $GLOBALS['tradepress_table_data'] : array(),
                    'nonces' => array(
                        'dataOperations' => wp_create_nonce('tradepress_data_operations'),
                    ),
                )
            );
        }
        
        // Bot admin scripts
        if ('tradepress_page_tradepress_bot' === $screen_id) {
            wp_register_script(
                'tradepress-admin-bot',
                TRADEPRESS_PLUGIN_URL . 'assets/js/admin-bot.js',
                array('jquery'),
                TRADEPRESS_VERSION,
                true
            );
            
            $bot_data = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tradepress_bot_nonce')
            );
            
            wp_localize_script('tradepress-admin-bot', 'tradepress_bot', $bot_data);
            wp_enqueue_script('tradepress-admin-bot');
        }
        
        // Automation admin scripts
        if ('tradepress_page_tradepress_automation' === $screen_id) {
            wp_register_script(
                'tradepress-admin-automation',
                TRADEPRESS_PLUGIN_URL . 'assets/js/admin-automation.js',
                array('jquery'),
                TRADEPRESS_VERSION,
                true
            );
            
            // Check if the controller class exists before calling its methods
            $is_algorithm_running = class_exists('TradePress_Admin_Automation_Controller') ? 
                TradePress_Admin_Automation_Controller::is_algorithm_running() : false;
            $is_signals_running = class_exists('TradePress_Admin_Automation_Controller') ? 
                TradePress_Admin_Automation_Controller::is_signals_running() : false;
            $is_trading_running = class_exists('TradePress_Admin_Automation_Controller') ? 
                TradePress_Admin_Automation_Controller::is_trading_running() : false;
            
            $automation_data = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tradepress_automation_nonce'),
                'is_running' => $is_algorithm_running,
                'any_running' => ($is_algorithm_running || $is_signals_running || $is_trading_running),
                'confirm_stop' => __('Are you sure you want to stop the algorithm? Any in-progress operations will be terminated.', 'tradepress'),
                'confirm_stop_all' => __('Are you sure you want to stop all automation components? Any in-progress operations will be terminated.', 'tradepress')
            );
            
            wp_localize_script('tradepress-admin-automation', 'tradepress_automation', $automation_data);
            wp_enqueue_script('tradepress-admin-automation');
        }
        
        // Automation page - CRON tab
        if ( 'tradepress_page_tradepress_automation' === $screen_id && 
             (!isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] === 'cron')) ) {
            wp_enqueue_script('tradepress-cron-tab');
            
            // Add localized data if there's a schedule to restore
            if ($next_run = wp_next_scheduled('tradepress_fetch_earnings_calendar')) {
                $schedule = wp_get_schedule('tradepress_fetch_earnings_calendar');
                wp_localize_script(
                    'tradepress-cron-tab',
                    'tradepress_cron_schedule',
                    $schedule
                );
            }
        }
        
        // Automation page - Directives tab
        if ( 'tradepress_page_tradepress_automation' === $screen_id && isset($_GET['tab']) && $_GET['tab'] === 'directives' ) {
            wp_enqueue_script('tradepress-scoring-directives');
            
            // Enqueue logs styles when on logs view
            isset($_GET['view']) && $_GET['view'] === 'logs' && wp_enqueue_style('tradepress-scoring-directives-logs');
            
            // Localize script with nonce and other data
            wp_localize_script(
                'tradepress-scoring-directives',
                'tradepressScoringDirectives',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('tradepress-scoring-directives'),
                    'strings' => array(
                        'saving' => __('Saving...', 'tradepress'),
                        'saved' => __('✓ Saved successfully', 'tradepress'),
                        'save_failed' => __('✗ Save failed', 'tradepress'),
                        'enable' => __('Enable', 'tradepress'),
                        'disable' => __('Disable', 'tradepress'),
                        'active' => __('Active', 'tradepress'),
                        'inactive' => __('Inactive', 'tradepress')
                    )
                )
            );
        }
        
        // Research page - Tab-specific scripts
        if ( 'toplevel_page_tradepress_research' === $screen_id ) {
            // Always enqueue the main research script
            wp_enqueue_script( 'tradepress-research' );
            
            $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview';
            
            if ( $current_tab === 'earnings' ) {
                wp_enqueue_script( 'tradepress-earnings-tab-script' );
            }
            
            // Economic Calendar tab script
            if ( $current_tab === 'economic-calendar' || $current_tab === '' ) {
                wp_enqueue_script( 'tradepress-economic-calendar' );
                
                // Get today's events for notifications
                $todays_events = array();
                if (function_exists('tradepress_get_todays_economic_events') && function_exists('tradepress_get_demo_economic_events')) {
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d', strtotime('+7 days'));
                    $events = tradepress_get_demo_economic_events($start_date, $end_date, 'all', array('us', 'eu', 'uk', 'jp', 'ca', 'au'));
                    $todays_events = tradepress_get_todays_economic_events($events);
                }
                
                // Localize the script with necessary data
                wp_localize_script(
                    'tradepress-economic-calendar',
                    'tradepressEconomicCalendar',
                    array(
                        'iconUrl' => TRADEPRESS_PLUGIN_URL . 'admin/images/tradepress-icon.png',
                        'todaysEvents' => $todays_events,
                        'tooltipText' => __('Impact depends on current market environment and central bank policy stance. During high inflation periods, higher-than-expected readings can be negative for stocks due to rate hike expectations.', 'tradepress')
                    )
                );
            }
            
            // Market Correlations tab script
            if ( $current_tab === 'market-correlations' ) {
                wp_enqueue_script( 'tradepress-market-correlations' );
            }
        }
        
        // Trading page - Calculators tab
        if ( isset($_GET['page']) && $_GET['page'] === 'tradepress_trading' ) {

            // Portfolio tab - either when it's the default tab (no tab parameter) or explicitly selected
            if (!isset($_GET['tab']) || $_GET['tab'] === 'portfolio') {
                wp_enqueue_style('tradepress-trading-portfolio');
            }

            // Enqueue calculators styles and scripts for all trading tabs
            wp_enqueue_style('tradepress-calculators');
            wp_enqueue_script('tradepress-calculators');
            
            // Specifically for calculators tab
            if ( isset($_GET['tab']) && $_GET['tab'] === 'calculators' ) {
                // Additional scripts can be loaded here if needed
            }
        }
        
        // Education dashboard page
        if ( isset($_GET['page']) && $_GET['page'] === 'tradepress_education' || 
             (isset($_GET['page']) && $_GET['page'] === 'tradepress_education_dashboard')) {
            wp_enqueue_style('tradepress-education-dashboard');
        }
        
        // Discord Simple Admin page
        if ( isset($_GET['page']) && $_GET['page'] === 'tradepress-discord-simple-tester') {
            wp_enqueue_style('tradepress-discord-simple-admin');
        }
        
        // Analysis page scripts
        if (isset($_GET['page']) && ($_GET['page'] === 'tradepress_analysis' || $_GET['page'] === 'tradepress-analysis')) {
            wp_enqueue_script('tradepress-admin-technical-analysis');
            
            // Localize script with translation strings
            wp_localize_script(
                'tradepress-admin-technical-analysis',
                'tradepressAnalysis',
                array(
                    'showDetailsText' => __('Show All Individual Levels', 'tradepress'),
                    'hideDetailsText' => __('Hide All Individual Levels', 'tradepress')
                )
            );
        }
        
        // Data page scripts
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_data') {
            wp_enqueue_script('tradepress-admin-data');
            
            // Prepare localization data
            $localize_data = array(
                'nonce' => wp_create_nonce('tradepress_cache_operations'),
                'confirmDelete' => __('Are you sure you want to delete this source? This cannot be undone.', 'tradepress'),
                'confirmArchive' => __('Are you sure you want to archive this source?', 'tradepress'),
                'fetchingText' => __('Fetching...', 'tradepress'),
                'fetchNowText' => __('Fetch Now', 'tradepress'),
                'sourceNotAvailable' => __('This source type is not available yet. Please select another source type.', 'tradepress'),
                'testingText' => __('Testing...', 'tradepress'),
                'testSourceText' => __('Test Source', 'tradepress'),
                'confirmPurgeCache' => __('Are you sure you want to purge this cache?', 'tradepress'),
                'confirmPurgeAllCaches' => __('Are you sure you want to purge ALL TradePress caches?', 'tradepress')
            );
            
            // Add source types if on source form page
            if (isset($_GET['action']) && ($_GET['action'] === 'new' || $_GET['action'] === 'edit')) {
                $source_types = array(
                    'website' => array(
                        'title' => __('Website (Scraping)', 'tradepress'),
                        'description' => __('Extract data from websites by scraping the content.', 'tradepress'),
                        'ready' => true,
                        'notice' => ''
                    ),
                    'rss' => array(
                        'title' => __('RSS Feed', 'tradepress'),
                        'description' => __('Subscribe to RSS feeds to automatically collect articles.', 'tradepress'),
                        'ready' => true,
                        'notice' => ''
                    ),
                    'api' => array(
                        'title' => __('API Endpoint', 'tradepress'),
                        'description' => __('Connect to third-party APIs to fetch structured data.', 'tradepress'),
                        'ready' => true,
                        'notice' => ''
                    ),
                    'custom' => array(
                        'title' => __('Custom Source', 'tradepress'),
                        'description' => __('Create a custom data source with specialized configuration.', 'tradepress'),
                        'ready' => true,
                        'notice' => ''
                    ),
                    'webhook' => array(
                        'title' => __('Webhook', 'tradepress'),
                        'description' => __('Receive data pushed from external services.', 'tradepress'),
                        'ready' => false,
                        'notice' => __('Webhook processing is currently in development.', 'tradepress')
                    ),
                    'discord' => array(
                        'title' => __('Discord Channel', 'tradepress'),
                        'description' => __('Monitor Discord channels for trading signals.', 'tradepress'),
                        'ready' => false,
                        'notice' => __('Discord integration is in development.', 'tradepress')
                    ),
                    'twitter' => array(
                        'title' => __('Twitter/X.com Profile', 'tradepress'),
                        'description' => __('Track Twitter/X.com accounts for market insights.', 'tradepress'),
                        'ready' => false,
                        'notice' => __('Twitter/X.com API integration is in development.', 'tradepress')
                    ),
                    'youtube' => array(
                        'title' => __('YouTube Channel', 'tradepress'),
                        'description' => __('Follow YouTube channels for video content analysis.', 'tradepress'),
                        'ready' => false,
                        'notice' => __('YouTube API integration is planned.', 'tradepress')
                    ),
                    'reddit' => array(
                        'title' => __('Reddit Subreddit', 'tradepress'),
                        'description' => __('Monitor Reddit communities for trading discussions.', 'tradepress'),
                        'ready' => false,
                        'notice' => __('Reddit API integration is in development.', 'tradepress')
                    )
                );
                
                $localize_data['sourceTypes'] = $source_types;
            }
            
            // Localize script
            wp_localize_script(
                'tradepress-admin-data',
                'tradepressData',
                $localize_data
            );
        }
        
        // Scoring directives page scripts
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_scoring_directives') {
            wp_enqueue_script('tradepress-scoring-directives');
            
            // Localize script with nonce and other data
            wp_localize_script(
                'tradepress-scoring-directives',
                'tradepressScoringDirectives',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('tradepress-scoring-directives')
                )
            );
        }
        
        // Settings page scripts
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress') {
            wp_enqueue_script('tradepress-settings');
        }
        

        
        // Watchlists page scripts
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_watchlists') {
            wp_enqueue_script('tradepress-watchlists');
        }
        
        // Focus page styles
        if (isset($_GET['page']) && $_GET['page'] === 'tradepress_focus') {
            // Enqueue focus advisor styles when on advisor tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'advisor') {
                wp_enqueue_style('tradepress-focus-advisor');
            }
        }
    }

    /**
     * Enqueue scripts and styles for specific admin pages.
     * 
     * @version 1.0
     */
    public function admin_scripts_styles( $hook_suffix ) {
        $screen = get_current_screen();
        $plugin_version = TRADEPRESS_VERSION; // Make sure TRADEPRESS_VERSION is defined

        // Styles and scripts for specific admin pages
        if ( strpos( $hook_suffix, 'tradepress_trading' ) !== false ) {
            wp_enqueue_style( 'tradepress-trading-area-styles', TRADEPRESS_PLUGIN_URL . 'assets/css/trading-area.css', array(), $plugin_version );
            // Enqueue SEES Demo specific assets if on that tab
            if ( isset( $_GET['page'] ) && $_GET['page'] === 'tradepress_trading' && isset( $_GET['tab'] ) && $_GET['tab'] === 'sees-demo' ) {
                wp_enqueue_style( 'tradepress-sees-demo-styles', TRADEPRESS_PLUGIN_URL . 'assets/css/pages/sees-demo.css', array(), $plugin_version );
                wp_enqueue_script( 'tradepress-sees-demo-js', TRADEPRESS_PLUGIN_URL . 'assets/js/sees-demo.js', array( 'jquery' ), $plugin_version, true );
                
                // Localize script for nonce and translatable strings
                wp_localize_script( 'tradepress-sees-demo-js', 'tradepress_sees_demo_nonce', array(
                    'nonce' => wp_create_nonce( 'tradepress_fetch_sees_demo_data_nonce' ),
                    'error_message' => __( 'Error loading SEES data. Please try again.', 'tradepress' ),
                    'no_data_message' => __( 'No SEES data available at the moment.', 'tradepress' ),
                ) );
            }
        } elseif ( strpos( $hook_suffix, 'tradepress_settings' ) !== false ) {
            // Additional styles for settings page
            wp_enqueue_style( 'tradepress-settings-styles', TRADEPRESS_PLUGIN_URL . 'assets/css/settings.css', array(), $plugin_version );
        }
    }
}

endif;

$class = new TradePress_Admin_Assets();

add_action( 'admin_enqueue_scripts', array( $class, 'admin_styles' ) );
add_action( 'admin_enqueue_scripts', array( $class, 'admin_scripts' ) );
add_action( 'admin_enqueue_scripts', array( $class, 'admin_scripts_styles' ), 10, 1 );

unset( $class );