<?php
/**
 * TradePress - Social Platform API's Admin Tabs
 *
 * Handles the display and functionality of Social Platform admin tabs
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/admin/page
 * @since    1.0.0
 * @created  April 22, 2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_SocialPlatforms_Tabs' ) ) :

/**
 * TradePress_Admin_SocialPlatforms_Tabs Class
 */
class TradePress_Admin_SocialPlatforms_Tabs {

    /**
     * Hook into WordPress to register the help tabs
     */
    public static function init() {
        // Add action to hook into the appropriate screen load
        add_action('admin_head', array(__CLASS__, 'add_help_tabs'));
    }

    /**
     * Output the Social Platform tabs page
     */
    public static function output() {

        // Enqueue jQuery UI for the test dialogs
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-jquery-ui-dialog');
        
        self::tabs_interface();
    }

    /**
     * Add help tabs to the screen
     * 
     * @todo The help tab is currently not showing, there are plans to fix this and improve the Help tab for all views
     */
    public static function add_help_tabs() {
        // Get the current screen
        $screen = get_current_screen();
        
        if (!$screen) {
            return;
        }
        
        // Define the page IDs where we want to show help tabs
        $social_platform_pages = array(
            'toplevel_page_tradepress_social',
            'tradepress_page_tradepress_social',
            'admin_page_tradepress_social'
        );
        
        // Convert screen ID to lowercase for consistent comparison
        $current_screen_id = strtolower($screen->id);
        
        // Check if current screen matches any of our target pages
        $is_social_page = false;
        foreach ($social_platform_pages as $page_id) {
            if (strpos($current_screen_id, strtolower($page_id)) !== false) {
                $is_social_page = true;
                break;
            }
        }
        
        // Also check based on page parameter for custom admin pages
        if (!$is_social_page && isset($_GET['page']) && ($_GET['page'] === 'TradePress_social' || $_GET['page'] === 'tradepress_social')) {
            $is_social_page = true;
        }
        
        // Exit if not on a social platforms page
        if (!$is_social_page) {
            return;
        }
        
        // Remove existing help tabs to prevent duplicates
        $screen->remove_help_tabs();
        
        // Overview tab
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_overview_help',
            'title'   => __('Overview', 'tradepress'),
            'content' => '<h2>' . __('Social Platforms Overview', 'tradepress') . '</h2>' .
                        '<p>' . __('The Social Platforms page allows you to configure and manage integrations with various social media platforms. You can connect your TradePress installation to services like Discord, Twitter, and more to enable automatic posting, notifications, and other social media features.', 'tradepress') . '</p>' .
                        '<p>' . __('Use the tabs below to navigate between different sections of the Social Platforms configuration.', 'tradepress') . '</p>'
        ));
        
        // Dashboard tab help
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_dashboard_help',
            'title'   => __('Dashboard', 'tradepress'),
            'content' => '<h2>' . __('Social Platforms Dashboard', 'tradepress') . '</h2>' .
                        '<p>' . __('The Dashboard tab provides an overview of all your connected social platforms with status information and quick actions.', 'tradepress') . '</p>' .
                        '<p>' . __('From here, you can see:', 'tradepress') . '</p>' .
                        '<ul>' .
                        '<li>' . __('Connection status for each platform', 'tradepress') . '</li>' .
                        '<li>' . __('API usage statistics', 'tradepress') . '</li>' .
                        '<li>' . __('Recent activity from your connected platforms', 'tradepress') . '</li>' .
                        '<li>' . __('Quick links to platform-specific settings', 'tradepress') . '</li>' .
                        '</ul>' .
                        '<p>' . __('Use the Dashboard to monitor the health and status of your social media integrations at a glance.', 'tradepress') . '</p>'
        ));
        
        // Twitter tab help
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_twitter_help',
            'title'   => __('Twitter', 'tradepress'),
            'content' => '<h2>' . __('Twitter Integration', 'tradepress') . '</h2>' .
                        '<p>' . __('The Twitter tab allows you to configure and manage your Twitter integration.', 'tradepress') . '</p>' .
                        '<p>' . __('Key features include:', 'tradepress') . '</p>' .
                        '<ul>' .
                        '<li>' . __('Automatic posting of trading signals', 'tradepress') . '</li>' .
                        '<li>' . __('Market updates and news sharing', 'tradepress') . '</li>' .
                        '<li>' . __('Custom tweet templates', 'tradepress') . '</li>' .
                        '<li>' . __('Scheduled tweets', 'tradepress') . '</li>' .
                        '</ul>' .
                        '<p>' . __('To set up Twitter integration, you need to create a Twitter Developer account and configure the API keys in the settings.', 'tradepress') . '</p>'
        ));
        
        // Settings tab help
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_settings_help',
            'title'   => __('Settings', 'tradepress'),
            'content' => '<h2>' . __('Social Platforms Settings', 'tradepress') . '</h2>' .
                        '<p>' . __('The Settings tab allows you to configure global settings for all social platforms as well as platform-specific settings.', 'tradepress') . '</p>' .
                        '<p>' . __('Key setting categories include:', 'tradepress') . '</p>' .
                        '<ul>' .
                        '<li>' . __('API credentials and authentication', 'tradepress') . '</li>' .
                        '<li>' . __('Posting frequency and schedules', 'tradepress') . '</li>' .
                        '<li>' . __('Message templates and formatting', 'tradepress') . '</li>' .
                        '<li>' . __('Notification preferences', 'tradepress') . '</li>' .
                        '<li>' . __('Connection settings', 'tradepress') . '</li>' .
                        '</ul>' .
                        '<p>' . __('Each platform has its own set of specific settings that can be accessed by selecting the platform from the settings menu.', 'tradepress') . '</p>'
        ));
        
        // Logs tab help
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_logs_help',
            'title'   => __('Logs', 'tradepress'),
            'content' => '<h2>' . __('Social Platforms Logs', 'tradepress') . '</h2>' .
                        '<p>' . __('The Logs tab provides detailed logs of all social platform activity, including API requests, errors, and successful operations.', 'tradepress') . '</p>' .
                        '<p>' . __('Log features include:', 'tradepress') . '</p>' .
                        '<ul>' .
                        '<li>' . __('Filtering by platform, event type, and date range', 'tradepress') . '</li>' .
                        '<li>' . __('Detailed error messages and troubleshooting information', 'tradepress') . '</li>' .
                        '<li>' . __('Export options for logs', 'tradepress') . '</li>' .
                        '<li>' . __('Log rotation and management settings', 'tradepress') . '</li>' .
                        '</ul>' .
                        '<p>' . __('Use the logs to troubleshoot connection issues, monitor API usage, and track the history of your social media activity.', 'tradepress') . '</p>'
        ));
        
        // Stock VIP tab help
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_stockvip_help',
            'title'   => __('Stock VIP', 'tradepress'),
            'content' => '<h2>' . __('Stock VIP Integration', 'tradepress') . '</h2>' .
                        '<p>' . __('The Stock VIP tab allows you to configure Discord stock alert parsing and processing.', 'tradepress') . '</p>' .
                        '<p>' . __('Key features include:', 'tradepress') . '</p>' .
                        '<ul>' .
                        '<li>' . __('Parsing stock alerts from Discord messages', 'tradepress') . '</li>' .
                        '<li>' . __('Automatic extraction of ticker symbols and signals', 'tradepress') . '</li>' .
                        '<li>' . __('Historical alert tracking and performance analysis', 'tradepress') . '</li>' .
                        '<li>' . __('Custom alert filtering and categorization', 'tradepress') . '</li>' .
                        '</ul>' .
                        '<p>' . __('Configure the Stock VIP parser to automatically detect and process stock alerts from your connected Discord channels.', 'tradepress') . '</p>'
        ));
        
        // Sidebar help content
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'tradepress') . '</strong></p>' .
            '<p><a href="https://tradepress.io/docs/social-platforms/" target="_blank">' . __('Documentation on Social Platforms', 'tradepress') . '</a></p>' .
            '<p><a href="https://tradepress.io/support/" target="_blank">' . __('Support', 'tradepress') . '</a></p>' .
            '<p><a href="https://discord.com/developers/docs/intro" target="_blank">' . __('Discord API Documentation', 'tradepress') . '</a></p>' .
            '<p><a href="https://developer.twitter.com/en/docs" target="_blank">' . __('Twitter API Documentation', 'tradepress') . '</a></p>'
        );
    }

    /**
     * Display tabs interface - Fixed implementation
     */
    private static function tabs_interface() {
        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_title($_GET['tab']) : 'settings';
        
        // Get available tabs
        $tabs = self::get_tabs();
        
        ?>
        <div class="wrap tradepress-social-platforms-wrap">
            <h1>
                <?php 
                echo esc_html__('TradePress Social Platforms', 'tradepress');
                if (isset($tabs[$current_tab])) {
                    echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
                    echo esc_html($tabs[$current_tab]['name']);
                }
                ?>
            </h1>

            <h2 class="nav-tab-wrapper">
                <?php
                foreach ($tabs as $tab_id => $tab_data) {
                    $active_class = ($current_tab === $tab_id) ? 'nav-tab-active' : '';
                    printf(
                        '<a href="%s" class="nav-tab %s">%s</a>',
                        esc_url(add_query_arg('tab', $tab_id, remove_query_arg('section'))),
                        esc_attr($active_class),
                        esc_html($tab_data['name'])
                    );
                }
                ?>
            </h2>
            
            <div class="tab-content">
                <?php
                // Display current tab content
                if (isset($tabs[$current_tab]) && isset($tabs[$current_tab]['callback'])) {
                    call_user_func($tabs[$current_tab]['callback']);
                } else {
                    // Default to settings tab
                    self::settings_tab();
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
            'settings' => array(
                'name'     => __( 'Settings', 'tradepress' ),
                'callback' => array( __CLASS__, 'settings_tab' )
            ),
            'platform_switches' => array(
                'name'     => __( 'Platform Switches', 'tradepress' ),
                'callback' => array( __CLASS__, 'platform_switches_tab' )
            ),          
            'twitter' => array(
                'name'     => __( 'Twitter', 'tradepress' ),
                'callback' => array( __CLASS__, 'twitter_tab' )
            ),
            'stocktwits' => array(
                'name'     => __( 'StockTwits', 'tradepress' ),
                'callback' => array( __CLASS__, 'stocktwits_tab' )
            ),
        );
        
        // Filter tabs to only show Social Platforms that have been enabled for display
        $filtered_tabs = array();
        
        // Always include the Settings and Platform Switches tabs
        $filtered_tabs['settings'] = $tabs['settings'];
        $filtered_tabs['platform_switches'] = $tabs['platform_switches'];
        
        // Check each platform if its tab should be displayed
        foreach ($tabs as $platform_id => $tab) {
            // Skip the settings and Platform Switches tabs as we've already added them
            if ($platform_id === 'settings' || $platform_id === 'platform_switches') {
                continue;
            }
            
            // Check if this platform tab should be displayed
            $show_tab = false;
            
            // Standard option name format - this controls tab visibility
            $option_name = 'TradePress_switch_' . $platform_id . '_social_services';
            if (get_option($option_name) === 'yes') {
                $show_tab = true;
            }
            
            // Alternative option name formats (some platforms might use different conventions)
            $alt_option_names = array(
                'tradepress_' . $platform_id . '_activated',
                'tradepress_switch_' . $platform_id . '_services',
                'tradepress_' . $platform_id . '_social_active',
                'tradepress_social_' . $platform_id . '_active'
            );
            
            foreach ($alt_option_names as $alt_name) {
                if (get_option($alt_name) === 'yes') {
                    $show_tab = true;
                    break;
                }
            }
            
            // Include the tab if it should be displayed
            if ($show_tab) {
                $filtered_tabs[$platform_id] = $tab;
            }
        }
        
        return apply_filters('tradepress_social_platform_tabs', $filtered_tabs);
    }

    /**
     * Discord tab content
     */
    public static function discord_tab() {
        // Initially, this will redirect to the Discord tab in Trading Platforms
        // Later it will be replaced with direct functionality
        echo '<div class="notice notice-info is-dismissible"><p>' . 
             esc_html__('Discord functionality will be migrated to this section in a future update.', 'tradepress') . 
             '</p></div>';
             
        // Include Discord tab view (to be implemented later)
        if (file_exists(dirname(__FILE__) . '/view/discord/discord.php')) {
            include(dirname(__FILE__) . '/view/discord/discord.php');
        } else {
            echo '<p>' . esc_html__('Discord view file not found.', 'tradepress') . '</p>';
        }
    }
    
    /**
     * Twitter tab content
     */
    public static function twitter_tab() {
        if (file_exists(dirname(__FILE__) . '/view/twitter.php')) {
            include(dirname(__FILE__) . '/view/twitter.php');
        } else {
            echo '<p>' . esc_html__('Twitter view file not found.', 'tradepress') . '</p>';
        }
    }
    
    /**
     * StockTwits tab content
     */
    public static function stocktwits_tab() {
        if (file_exists(dirname(__FILE__) . '/view/stocktwits.php')) {
            include(dirname(__FILE__) . '/view/stocktwits.php');
        } else {
            echo '<p>' . esc_html__('StockTwits view file not found.', 'tradepress') . '</p>';
        }
    }

    /**
     * Stock VIP tab content for displaying Discord stock alerts
     */
    public static function stock_vip_tab() {
        // Include necessary scripts and styles for the Stock VIP tab
        wp_enqueue_style('stock-vip-styles', TRADEPRESS_PLUGIN_URL . 'css/admin-stockvip.css', array(), TRADEPRESS_VERSION);
        wp_enqueue_script('stock-vip-script', TRADEPRESS_PLUGIN_URL . 'js/stock-vip.js', array('jquery'), TRADEPRESS_VERSION, true);
        
        // Include and initialize the Discord Stock VIP Parser
        $parser_file = dirname(__FILE__) . '/view/discord/stockvip/class-tradepress-discord-stock-vip-parser.php';
        if (file_exists($parser_file)) {
            require_once($parser_file);
            $stock_vip_parser = new TRADEPRESS_DISCORD_Stock_VIP_Parser();
            $stock_vip_parser->init();
        }
        
        // Add AJAX nonce for secure Stock VIP operations
        wp_localize_script('stock-vip-script', 'stockVipAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('TRADEPRESS_DISCORD_stock_vip_ajax')
        ));
        
        // Include Discord Stock VIP tab view
        $stock_vip_view = dirname(__FILE__) . '/view/discord/stockvip/view.stock-vip.php';
        if (file_exists($stock_vip_view)) {
            include($stock_vip_view);
        } else {
            echo '<p>' . esc_html__('Stock VIP view file not found.', 'tradepress') . '</p>';
        }
    }

    /**
     * Settings tab content 
     */
    public static function settings_tab() {
        // Process form submission for Social Platform switches
        if (isset($_POST['save_platform_switches']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'tradepress-social-settings-switches')) {
            
            // Get all Social Platform providers
            $platform_array = array('discord', 'stock_vip', 'twitter', 'stocktwits');
            
            // Save platform switches
            foreach ($platform_array as $platform) {
                // Platform services switch
                $switch_name = 'switch_' . $platform . '_services';
                $option_name = 'TradePress_switch_' . $platform . '_social_services';
                update_option($option_name, isset($_POST[$switch_name]) ? 'yes' : 'no');
                
                // Platform logs switch
                $log_switch_name = 'switch_' . $platform . '_logs';
                $log_option_name = 'TradePress_switch_' . $platform . '_social_logs';
                update_option($log_option_name, isset($_POST[$log_switch_name]) ? 'yes' : 'no');
            }
            
            // Set a transient for success message
            set_transient('tradepress_social_settings_updated', true, 30);
            
            // Redirect to avoid form resubmission
            wp_redirect(add_query_arg('tab', 'settings', admin_url('admin.php?page=TradePress_social')));
            exit;
        }
        
        // Process form submission for provider-specific settings
        if (isset($_POST['save_social_provider']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'tradepress-social-settings-provider')) {
            $service = sanitize_text_field($_POST['social_provider']);
            
            // Service switches
            $switches = array('services', 'logs');
            foreach ($switches as $switch) {
                $switch_name = 'switch_' . $service . '_' . $switch;
                $option_name = 'TradePress_switch_' . $service . '_social_' . $switch;
                update_option($option_name, isset($_POST[$switch_name]) ? 'yes' : 'no');
            }
            
            // API keys
            if (isset($_POST['api_' . $service . '_apikey'])) {
                $option_name = 'TradePress_social_' . $service . '_apikey';
                update_option($option_name, sanitize_text_field($_POST['api_' . $service . '_apikey']));
            }
            
            // Twitter-specific fields
            if ($service == 'twitter' && isset($_POST['api_' . $service . '_token_secret'])) {
                update_option('TradePress_social_' . $service . '_token_secret', 
                              sanitize_text_field($_POST['api_' . $service . '_token_secret']));
            }
            
            // Run any service-specific procedures
            do_action('TradePress_social_platform_update_' . $service, $service, 
                     get_option('TradePress_social_' . $service . '_apikey', ''));
            
            // Set a transient for success message
            set_transient('tradepress_social_settings_updated', true, 30);
            
            // Redirect to avoid form resubmission
            wp_redirect(add_query_arg(array('tab' => 'settings', 'section' => $service), admin_url('admin.php?page=TradePress_social')));
            exit;
        }
        
        // Show success message if settings were updated
        if (get_transient('tradepress_social_settings_updated')) {
            // Call the settings_updated_notice immediately rather than adding to action hook
            self::settings_updated_notice();
            delete_transient('tradepress_social_settings_updated');
        }
        
        $settings_file = dirname(__FILE__) . '/view/sp-settings.php';
        if (file_exists($settings_file)) {
            include($settings_file);
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Settings view file not found.', 'tradepress') . '</p></div>';
            echo '<p>' . esc_html__('Expected file location:', 'tradepress') . ' ' . esc_html($settings_file) . '</p>';
        }
    }

    /**
     * Display notice when settings are updated
     */
    public static function settings_updated_notice() {
        echo '<div class="notice notice-success is-dismissible"><p>' . 
             esc_html__('Social platform settings updated successfully.', 'tradepress') . 
             '</p></div>';
    }

    /**
     * Platform Switches tab content
     */
    public static function platform_switches_tab() {
        // Process form submission for platform switches
        if (isset($_POST['save_platform_switches']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'tradepress-social-platform-switches')) {
            
            // Get all social platform providers
            $platform_array = array('discord', 'stock_vip', 'twitter', 'stocktwits');
            
            // Save platform switches
            foreach ($platform_array as $platform) {
                // Platform services switch
                $switch_name = 'switch_' . $platform . '_services';
                $option_name = 'TradePress_switch_' . $platform . '_social_services';
                update_option($option_name, isset($_POST[$switch_name]) ? 'yes' : 'no');
                
                // Platform logs switch
                $log_switch_name = 'switch_' . $platform . '_logs';
                $log_option_name = 'TradePress_switch_' . $platform . '_social_logs';
                update_option($log_option_name, isset($_POST[$log_switch_name]) ? 'yes' : 'no');
            }
            
            // Set a transient for success message
            set_transient('tradepress_social_settings_updated', true, 30);
            
            // Redirect to avoid form resubmission
            wp_redirect(add_query_arg('tab', 'platform_switches', admin_url('admin.php?page=TradePress_social')));
            exit;
        }
        
        // Show success message if settings were updated
        if (get_transient('tradepress_social_settings_updated')) {
            // Call the settings_updated_notice immediately rather than adding to action hook
            self::settings_updated_notice();
            delete_transient('tradepress_social_settings_updated');
        }
        
        $switches_file = dirname(__FILE__) . '/view/platform_switches.php';
        if (file_exists($switches_file)) {
            include($switches_file);
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Platform switches view file not found.', 'tradepress') . '</p></div>';
            echo '<p>' . esc_html__('Expected file location:', 'tradepress') . ' ' . esc_html($switches_file) . '</p>';
        }
    }
}

endif;