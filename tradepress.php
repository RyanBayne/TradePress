<?php
/**
 * Plugin Name: TradePress
 * Plugin URI: https://TradePress.wordpress.com/
 * Github URI: https://github.com/RyanBayne/TradePress
 * Description: Display real-time stock market data, news, and analysis on your WordPress site.  
 * Version: 1.0.95
 * Author: Ryan Bayne
 * Author URI: https://ryanbayne.wordpress.com/
 * Requires at least: 6.2
 * Tested up to: 6.8.0
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /i18n/languages/
 */
 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!defined('TRADEPRESS_VERSION')) { define('TRADEPRESS_VERSION', '1.0.4'); }
if (!defined('TRADEPRESS_PLUGIN_DIR_PATH')) { define('TRADEPRESS_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__)); }
if (!defined('TRADEPRESS_PLUGIN_DIR')) { define('TRADEPRESS_PLUGIN_DIR', plugin_dir_path(__FILE__)); }
if (!defined('TRADEPRESS_PLUGIN_URL')) { define('TRADEPRESS_PLUGIN_URL', plugin_dir_url(__FILE__)); } // http://domain/tradepress/wp-content/plugins/TradePress/
if (!defined('TRADEPRESS_PLUGIN_FILE')) { define('TRADEPRESS_PLUGIN_FILE', TRADEPRESS_PLUGIN_DIR); }
if (!defined('TRADEPRESS_PLUGIN_BASENAME')) { define('TRADEPRESS_PLUGIN_BASENAME', plugin_basename(__FILE__)); }// TradePress/tradepress.php
if (!defined('TRADEPRESS_MAINFILE')) { define('TRADEPRESS_MAINFILE', __FILE__ ); }
if (!defined('TRADEPRESS_TESTING')) { define('TRADEPRESS_TESTING', true); }

if ( ! class_exists( 'WordPressTradePress' ) ) :

    // Create a request key for tracing/debugging...
    if( !defined( 'TRADEPRESS_REQUEST_KEY' ) ) { define( 'TRADEPRESS_REQUEST_KEY', $_SERVER["REQUEST_TIME_FLOAT"] . rand( 10000, 99999 ) ); }
                                        
    try {
        // Load object registry class to handle class objects without using $global. 
        require_once( plugin_basename( 'includes/object-registry.php' ) );
                         
        // Load core functions with importance on making them available to third-party.                                            
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'functions.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'deprecated.php' );            
        // Formatting functions moved to includes/functions.tradepress-formatting.php
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'functions/functions.TradePress-validate.php' );
        
        // Include formatting functions
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/functions.tradepress-formatting.php'; // All formatting functions
        
        // Include the sortable-securities.php file here
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'shortcodes/sortablesecurities/sortable-securities.php' );# TODO: move this line to another file

        // Include required files
        require_once plugin_dir_path(__FILE__) . 'includes/github-functions.php';# TODO: move this line to another file
        
        // Run the plugin...
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'loader.php' );
        
        // Load symbol testing AJAX handlers
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ajax-symbol-testing.php' );
        
        // Load admin post handlers
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'includes/admin-post-handlers.php' );
        
        // Load centralized form handler
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'includes/forms/form-handler.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'includes/forms/form-validator.php' );
        
        // Load directive testing AJAX handler
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/ajax/directive-testing.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/ajax/directive-toggle.php' );
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/ajax/demo-mode-toggle.php' );
        
        // Load strategy management handler
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/scoring-directives/strategy-handler.php' );
        
        // Load mode indicators
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/config/class-mode-indicators.php' );
        
        // Load test runner (developer mode only)
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'tests/test-runner.php' );
        
        // Load feature help loader
        require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'includes/feature-help-loader.php' );
    } catch ( Exception $e ) {
        error_log( 'TradePress: Critical error loading plugin files - ' . $e->getMessage() );
        if ( is_admin() ) {
            add_action( 'admin_notices', function() use ( $e ) {
                echo '<div class="notice notice-error"><p><strong>TradePress Error:</strong> Failed to load plugin files - ' . esc_html( $e->getMessage() ) . '</p></div>';
            });
        }
        return;
    }
    
    // Installation and uninstallation hooks
    try {
        require_once( plugin_basename( 'admin/installation/plugin-activation-procedure.php' ) );
    } catch ( Exception $e ) {
        error_log( 'TradePress: Failed to load activation procedure - ' . $e->getMessage() );
    }
    register_activation_hook( __FILE__, 'TradePress_activation_installation' );
    register_deactivation_hook( __FILE__, array( 'TradePress_Admin_Deactivate', 'deactivate' ) );    
    register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

endif;

