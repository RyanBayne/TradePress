<?php
/**
 * TradePress Admin - Main Admin Class
 *
 * The primary for main add_action() and file includes during an administration side request. There is
 * also a functions.TradePress-admin.php for functions strictly related to admin.  
 * 
 * Do not include files only meant for the frontside.
 * Do not queue scripts or css only meant for frontside. 
 * 
 * @class    TradePress_Admin
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin class.
 */
class TradePress_Admin {

    /**
     * Constructor.
     */
    public function __construct() {         
        add_action( 'init',               array( $this, 'includes_requiring_main_init' ), 1 );
        add_action( 'init',               array( $this, 'includes_requiring_admin_init' ), 1 );
        add_action( 'current_screen',     array( $this, 'conditional_includes' ) );
        add_action( 'admin_init',         array( $this, 'buffer' ), 1 );
        add_action( 'admin_init',         array( $this, 'admin_redirects' ) );
        add_action( 'admin_footer',       'TradePress_print_js', 25 );
        add_filter( 'admin_footer_text',  array( $this, 'admin_footer_text' ), 1 );
        add_action( 'in_plugin_update_message-' . TRADEPRESS_PLUGIN_URL, array( $this, 'in_plugin_update_message' ) );        
        add_action('admin_init',          array( $this, 'require_pages_and_tabs' ) );   
    }

    /**
    * Include required admin files, including the admin menu and settings page, and view/views
    * 
    * @version 2.0
    */
    public function require_pages_and_tabs() {     
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/automation/automation-controller.php' );                     
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/automation/automation-tabs.php' );                                           
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/development-tabs.php' );                             
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/trading-platforms-tabs.php' );                             
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/socialplatforms/socialplatforms-tabs.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/trading/trading-tabs.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/watchlists/watchlists-tabs.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/data/data-tabs.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/analysis/analysis-tabs.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/scoring-directives/scoring-directives-tabs.php' );
    }

    /**
     * Output buffering allows admin screens to make redirects later on.
     */
    public function buffer() {
        ob_start();
    }

    /**
     * Include any classes we need within admin...
     * 
     * @version 2.0
     */
    public function includes_requiring_main_init() {
        
        // Functions
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/tradepress-admin-functions.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/filters.php' );
        
        // Add screen IDs function if not exists
        if (!function_exists('TradePress_get_screen_ids')) {
            function TradePress_get_screen_ids() {
                return array(
                    'toplevel_page_tradepress',
                    'tradepress_page_tradepress_development',
                    'tradepress_page_tradepress_focus', 
                    'tradepress_page_tradepress_data',
                    'tradepress_page_tradepress_watchlists',
                    'tradepress_page_tradepress_automation',
                    'tradepress_page_tradepress_scoring_directives',
                    'tradepress_page_tradepress_research',
                    'tradepress_page_tradepress_analysis',
                    'tradepress_page_tradepress_trading',
                    'tradepress_page_tradepress_platforms',
                    'edit_symbols'
                );
            }
        }
        
        // Class
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . '/admin/config/admin-menus.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . '/admin/config/class-mode-indicators.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . '/admin/assets-loader-original.php' );

        // Help Tabs
        if ( apply_filters( 'TradePress_enable_admin_help_tab', true ) ) {
            require_once( TRADEPRESS_PLUGIN_DIR_PATH . '/admin/admin-help.php' );
        }
                
        // Setup/welcome
        if ( ! empty( $_GET['page'] ) ) {
            switch ( $_GET['page'] ) {
                 case 'tradepress-traces' :
                    require_once( TRADEPRESS_PLUGIN_DIR_PATH . '/views/dataviews/view-trace.php' );
                break;
            }
        }
    }

    /**
    * Include files that aren't needed on core init...
    * 
    * @version 1.0
    */
    public function includes_requiring_admin_init() {
        require_once( dirname( __FILE__ ) . '/notices/admin-pointers.php' );        
    }
    
    /**
     * Include admin files conditionally based on specific page...
     * 
     * @version 2.0
     */
    public function conditional_includes() {

        if ( ! $screen = get_current_screen() ) {       
            return;
        }

        switch ( $screen->id ) {
            case 'dashboard' :      
                require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/dashboard/admin-dashboard.php' );
            break;
            case 'tradepress' :
            break;
            case 'users' :         
                require_once( dirname( __FILE__ ) . '/admin-users.php' );
                new TradePress_Admin_Users();
            break;
            case 'user' :
            break;
            case 'profile' :
            break;
            case 'user-edit' :
            break;
            case 'TradePress-settings' :
            break;
        }
    }

    /**
    * Displays an additional message within the core plugin-update notice...
    * 
    * @param mixed $args
    * 
    * @version 1.0
    */
    static function in_plugin_update_message( $args ) {
        $show_additional_notice = false;
        if ( isset( $args['new_version'] ) ) {
            $old_version_array = explode( '.', TRADEPRESS_VERSION );
            $new_version_array = explode( '.', $args['new_version'] );

            if ( $old_version_array[0] < $new_version_array[0] ) {
                $show_additional_notice = true;
            } else {
                if ( $old_version_array[1] < $new_version_array[1] ) {
                    $show_additional_notice = true;
                }
            }

        }

        if ( $show_additional_notice ) {
            ob_start(); ?>

            <style type="text/css">
                .TradePress_plugin_upgrade_notice {
                    font-weight: 400;
                    color: #fff;
                    background: #d53221;
                    padding: 1em;
                    margin: 9px 0;
                    display: block;
                    box-sizing: border-box;
                    -webkit-box-sizing: border-box;
                    -moz-box-sizing: border-box;
                }

                .TradePress_plugin_upgrade_notice:before {
                    content: "\f348";
                    display: inline-block;
                    font: 400 18px/1 dashicons;
                    margin: 0 8px 0 -2px;
                    -webkit-font-smoothing: antialiased;
                    -moz-osx-font-smoothing: grayscale;
                    vertical-align: top;
                }
            </style>

            <span class="TradePress_plugin_upgrade_notice">
                <?php printf( __( '%s is a major update - please backup of your site before updating.', 'twitch-press' ), $args['new_version'] ); ?>
            </span>

            <?php ob_get_flush();
        }  
    }
    
    /**
     * Handle redirects to setup/welcome page after install and updates.
     *
     * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
     * 
     * @version 1.2
     */
    public function admin_redirects() {

        // Nonced plugin install redirects (whitelisted)
        if ( ! empty( $_GET['TradePress-install-plugin-redirect'] ) ) {
            $plugin_slug = TradePress_clean( $_GET['TradePress-install-plugin-redirect'] );

            if ( current_user_can( 'install_plugins' ) && in_array( $plugin_slug, array( 'TradePress-gateway-stripe' ) ) ) {
                $nonce = wp_create_nonce( 'install-plugin_' . $plugin_slug );
                $url   = self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug . '&_wpnonce=' . $nonce );
            } else {
                $url = admin_url( 'plugin-install.php?tab=search&type=term&s=' . $plugin_slug );
            }
                  
            TradePress_redirect_tracking( $url, __LINE__, __FUNCTION__ );          
            exit;
        }

        // Setup wizard redirect after plugin activation. 
        if ( get_transient( '_TradePress_activation_redirect' ) ) {
            delete_transient( '_TradePress_activation_redirect' );

            if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'tradepress-setup' ) ) ) || is_network_admin() || isset( $_GET['activate-multi'] ) || apply_filters( 'TradePress_prevent_automatic_wizard_redirect', false ) ) {
                return;
            }

            // If the user needs to install, send them to the setup wizard
            if ( TradePress_Admin_Notices::has_notice( 'install' ) ) {
                $admin_url = admin_url( 'index.php?page=tradepress-setup' );        
                TradePress_redirect_tracking( $admin_url, __LINE__, __FUNCTION__ );
                exit;
            }
        }       
    }

    /**
     * Change the admin footer text on WordPress TradePress admin pages.
     */
    public function admin_footer_text( $footer_text ) {
        if ( ! current_user_can( 'manage_TradePress' ) ) {
            return;
        }
        $current_screen = get_current_screen();
        $TradePress_pages   = TradePress_get_screen_ids();

        // Check to make sure we're on a TradePress admin page
        if ( isset( $current_screen->id ) && apply_filters( 'TradePress_display_admin_footer_text', in_array( $current_screen->id, $TradePress_pages ) ) ) {
            //$footer_text = __( 'Thank you for planting a WordPress TradePress. I recommend removing this footer message. This text is an example only.', 'tradepress' );
        }

        return $footer_text;
    }
}

new TradePress_Admin();
