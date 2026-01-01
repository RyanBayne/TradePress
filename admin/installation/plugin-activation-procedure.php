<?php
/**
 * Installation functions, excluding plugin updating and some optional installation
 * features that might relate to none active API or extension integration.
 * 
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Core
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* Installs the TradePress plugin on normal activation through the admin...
*         
* @version 2.0
*/
function TradePress_activation_installation() {
    global $wpdb;

    if ( ! defined( 'TradePress_INSTALLING' ) ) {
        define( 'TradePress_INSTALLING', true );
    }

    // Additional file includes, version checks, conflict checks...
    TradePress_installation_prepare();
    
    // Install/update core database tables...
    include_once( TRADEPRESS_PLUGIN_DIR . 'admin/installation/tables-installation.php' );
    $tables_handler = new TradePress_Install_Tables();
    $tables_handler->create_tables(); // Changed from install() to create_tables()

    // Run individual installation functions...
    TradePress_installation_add_developer_role();
    TradePress_installation_roles_and_capabilities();
    TradePress_installation_create_files();
    TradePress_installation_create_options();
    TradePress_installation_add_capabilities_keyholder();    
     
    // Run automatic updates. 
    TradePress_installation_update();
    
    TradePress_update_package_version();

    do_action( 'TradePress_installed' );
}

/**
* Register core tables...
* 
* @version 1.0
*/
function TradePress_register_tables() {
    global $wpdb;
    
    // Core tables
    $wpdb->tradepress_calls  = "{$wpdb->prefix}tradepress_calls";
    $wpdb->tradepress_errors    = "{$wpdb->prefix}tradepress_errors";
    $wpdb->tradepress_endpoints = "{$wpdb->prefix}tradepress_endpoints";
    $wpdb->tradepress_meta      = "{$wpdb->prefix}tradepress_meta";
    
    // Symbol tables
    $wpdb->tradepress_symbols        = "{$wpdb->prefix}tradepress_symbols";
    $wpdb->tradepress_price_levels   = "{$wpdb->prefix}tradepress_price_levels";
    $wpdb->tradepress_price_history  = "{$wpdb->prefix}tradepress_price_history";
    
    // Scoring tables
    $wpdb->tradepress_symbol_scores    = "{$wpdb->prefix}tradepress_symbol_scores";
    $wpdb->tradepress_directive_scores = "{$wpdb->prefix}tradepress_directive_scores";
    $wpdb->tradepress_strategies       = "{$wpdb->prefix}tradepress_strategies";
    $wpdb->tradepress_strategy_symbols = "{$wpdb->prefix}tradepress_strategy_symbols";
    $wpdb->tradepress_score_analysis   = "{$wpdb->prefix}tradepress_score_analysis";
    
    // Trading bot tables
    $wpdb->tradepress_trades         = "{$wpdb->prefix}tradepress_trades";
    $wpdb->tradepress_algorithm_runs = "{$wpdb->prefix}tradepress_algorithm_runs";
    
    // Prediction tables
    $wpdb->tradepress_prediction_sources  = "{$wpdb->prefix}tradepress_prediction_sources";
    $wpdb->tradepress_price_predictions   = "{$wpdb->prefix}tradepress_price_predictions";
    $wpdb->tradepress_source_performance  = "{$wpdb->prefix}tradepress_source_performance";
    
    // Social alerts tables
    $wpdb->tradepress_social_alerts       = "{$wpdb->prefix}tradepress_social_alerts";
    $wpdb->tradepress_alert_outcomes      = "{$wpdb->prefix}tradepress_alert_outcomes";
    $wpdb->tradepress_alert_source_metrics = "{$wpdb->prefix}tradepress_alert_source_metrics";
    
    // Logs table
    $wpdb->tradepress_logs = "{$wpdb->prefix}tradepress_logs";
}

function TradePress_installation_prepare() {
    include_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/notices/admin-notices.php' );
    
    // Flush old notices to avoid confusion during a new installation...
    TradePress_Admin_Notices::remove_all_notices(); 
    
    // Queue upgrades/setup wizard
    $current_installed_version = get_option( 'TRADEPRESS_VERSION', null );

    // No versions? This is a new install :)
    if ( is_null( $current_installed_version ) && apply_filters( 'TradePress_enable_setup_wizard', true ) ) {  
        TradePress_Admin_Notices::add_notice( 'install' );
        delete_transient( '_TradePress_activation_redirect' );
        set_transient( '_TradePress_activation_redirect', 1, 30 );
    }                                  
}

function TradePress_installation_roles_and_capabilities() {
   require_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/admin-roles.php' );
   $roles_obj = new TradePress_Roles_Capabilities_Installation();
   $roles_obj->add_roles_and_capabilities();
}

/**
* Automatic updater - runs when plugin is activated which happens during
* a standard WordPress plugin update.
* 
* @version 2.0
*/
function TradePress_installation_update() {
              
}
    
/**
* If key values are missing we will offer the wizard. 
* 
* Does not apply when the setup wizard has not been complete. This is
* currently done by checking 
* 
* @version 3.1
*/
function TradePress_offer_wizard() {
    return false;
    $offer_wizard = false;
                                      
    if( !current_user_can( 'administrator' ) ) {        
        return;    
    }
    
    // Avoid registering notice during the Setup Wizard.
    if( isset( $_GET['page'] ) && $_GET['page'] == 'tradepress-setup' ) {     
        return;    
    }
    
    // If already displaying the install notice, do not display.
    if( TradePress_Admin_Notices::has_notice( 'install' ) ) {        
        return;
    }

    $a = get_option( 'TradePress_main_channels_name' );               
    $b = get_option( 'TradePress_main_channels_id' );           
    $c = get_option( 'TradePress_app_id' );                           
    $d = get_option( 'TradePress_app_secret' );                       
    $e = get_option( 'TradePress_main_channels_code' );               
    $f = get_option( 'TradePress_main_channels_token' );             
    
    if( !$a ) { $offer_wizard = 'TradePress_main_channels_name'; } 
    elseif( !$b ) { $offer_wizard = 'TradePress_main_channels_id'; } 
    elseif( !$c ) { $offer_wizard = 'TradePress_app_id'; } 
    elseif( !$d ) { $offer_wizard = 'TradePress_app_secret'; } 
    elseif( !$e ) { $offer_wizard = 'TradePress_main_channels_code'; } 
    elseif( !$f ) { $offer_wizard = 'TradePress_main_channels_token'; }     
    
    if( $offer_wizard === false ) { return; }
    
    // Build a link to wizard...
    $wizard_link = '<p><a href="' . esc_url(admin_url( 'index.php?page=tradepress-setup' )) . '" class="button button-primary">' . __( 'Setup Wizard', 'tradepress' ) . '</a></p>';
    
    // Add a new installation notice if it appears to be a fresh installation...
    if( !$a && !$b && !$c && !$d && !$e && !$f ) {
        
        TradePress_Admin_Notices::add_wordpress_notice(
            'noappvaluesofferwizard',
            'info',
            false,
            __( 'Setup Wizard', 'tradepress' ),
            sprintf( __( 'TradePress includes a Setup Wizard to help you get the plugin configured, please complete it now. %s', 'tradepress'), $wizard_link )    
        );

    } else {

        TradePress_Admin_Notices::add_wordpress_notice(
            'missingvaluesofferwizard',
            'info',
            false,
            __( 'Twitch API Credentials Missing', 'tradepress' ),
            sprintf( __( 'TradePress is not ready because the %s option is missing. If you have already been using the plugin and this notice suddenly appears then it suggests important options have been deleted or renamed. You can go through the Setup Wizard again to correct this problem. You should also report it. %s', 'tradepress'), $offer_wizard, $wizard_link )    
        );      
    }     
}
    
/**
* Update plugin version.
* 
* @version 1.0
*/
function TradePress_update_package_version() {
    update_option( 'TRADEPRESS_VERSION', TRADEPRESS_VERSION );
} 
        
/**
 * Update DB version to current.
 */
function TradePress_update_db_version( $version = null ) {
    update_option( 'TradePress_db_version', is_null( $version ) ? TRADEPRESS_VERSION : $version );
} 
    
/**
* Very strict capabilities for professional developers only.
* 
* @version 1.0
*/
function TradePress_get_developer_capabilities() {
    $capabilities = array();

    $capabilities['core'] = array(
        'TradePress_developer',
        'code_TradePress',
        'TradePressdevelopertoolbar'
    );

    return $capabilities;        
}

/**
 * Add the special developer role. 
 * 
 * Function originally named "TradePress_create_roles"
 * 
 * @version 2.0
 */
function TradePress_installation_add_developer_role() {
    global $wp_roles;

    if ( ! class_exists( 'WP_Roles' ) ) {
        return;
    }

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    // TradePress Developer role
    add_role( 'TradePressdeveloper', __( 'TradePress Developer', 'tradepress' ), array(
        'level_9'                => true,
        'level_8'                => true,
        'level_7'                => true,
        'level_6'                => true,
        'level_5'                => true,
        'level_4'                => true,
        'level_3'                => true,
        'level_2'                => true,
        'level_1'                => true,
        'level_0'                => true,
        'read'                   => true,
        'read_private_pages'     => true,
        'read_private_posts'     => true,
        'edit_users'             => true,
        'edit_posts'             => true,
        'edit_pages'             => true,
        'edit_published_posts'   => true,
        'edit_published_pages'   => true,
        'edit_private_pages'     => true,
        'edit_private_posts'     => true,
        'edit_others_posts'      => true,
        'edit_others_pages'      => true,
        'publish_posts'          => true,
        'publish_pages'          => true,
        'delete_posts'           => true,
        'delete_pages'           => true,
        'delete_private_pages'   => true,
        'delete_private_posts'   => true,
        'delete_published_pages' => true,
        'delete_published_posts' => true,
        'delete_others_posts'    => true,
        'delete_others_pages'    => true,
        'manage_categories'      => true,
        'manage_links'           => true,
        'moderate_comments'      => true,
        'unfiltered_html'        => true,
        'upload_files'           => true,
        'export'                 => true,
        'import'                 => true,
        'list_users'             => true
    ) );

    // Add custom capabilities to our new TradePress Developers role. 
    $new_admin_capabilities = TradePress_get_developer_capabilities();
    foreach ( $new_admin_capabilities as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $wp_roles->add_cap( 'TradePressdeveloper', $cap );                
        }
    }        
    
}

/**
 * Create files/directories with .htaccess and index files added by default.
 * 
 * @version 1.0
 */
function TradePress_installation_create_files() {
    // Install files and folders for uploading files and prevent hotlinking
    $upload_dir      = wp_upload_dir();
    $download_method = get_option( 'TradePress_file_download_method', 'force' );
                                         
    $files = array(
        array(
            'base'         => $upload_dir['basedir'] . '/tradepress_uploads',
            'file'         => 'index.html',
            'content'     => ''
        ),
    );

    if ( 'redirect' !== $download_method ) {
        $files[] = array(
            'base'         => $upload_dir['basedir'] . '/tradepress_uploads',
            'file'         => '.htaccess',
            'content'     => 'deny from all'
        );
    }

    foreach ( $files as $file ) {
        if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
            if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
                fwrite( $file_handle, $file['content'] );
                fclose( $file_handle );
            }
        }
    }
}
    
/**
 * Adds default options from settings files.
 * 
 * @version 1.0
 */
function TradePress_installation_create_options() {
    // Include settings so that we can run through defaults
    include_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/admin-settings.php';
    $settings = TradePress_Admin_Settings::get_settings_pages();

    foreach ( $settings as $section ) {
        if ( !method_exists( $section, 'get_settings' ) ) {
            continue;
        }
        
        $subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

        foreach ( $subsections as $subsection ) {
            foreach ( $section->get_settings( $subsection ) as $value ) {
                if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
                    $autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
                    add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
                }
            }
        }
    }
}

/**
* Use to add default capabilities to the key holder.
* 
* @version 1.0
*/
function TradePress_installation_add_capabilities_keyholder() {    
    $user = new WP_User( 1 );// Give the site owner permission to do everything a TradePress Developer would...
    foreach ( TradePress_get_developer_capabilities() as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $user->add_cap( $cap );                 
        }
    }        
}