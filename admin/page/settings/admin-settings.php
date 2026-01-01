<?php
/**
 * TradePress Admin Settings Class
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_Settings' ) ) :

/**
 * TradePress_Admin_Settings Class.
 */
class TradePress_Admin_Settings {

    /**
     * Setting pages.
     *
     * @var array
     */
    private static $settings = array();

    /**
     * Error messages.
     *
     * @var array
     */
    private static $errors   = array();

    /**
     * Update messages.
     *
     * @var array
     */
    private static $messages = array();

    /**
     * Information messages.
     *
     * @var array
     */
    private static $info = array();
    
    /**
    * This is more about configuration reminding.
    * 
    * @var mixed
    */
    private static $defaulttab = 'general';
    
    /**
     * Include the settings page classes.
     * 
     * @version 1.2
     */
    public static function get_settings_pages() {
        if ( empty( self::$settings ) ) {
            $settings = array();

            // Include form fields class for field handling
            include_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/form-fields.php' );
            // Update paths to correctly reflect current directory structure
            include_once( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/page.php' );
                                                                           
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/general.php' ); 
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/users.php' );    
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/tradingapi.php' );    
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/features.php' );
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/shortcodes.php' );
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/symbols.php' );
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/database.php' );
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/bugnet.php' );
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/sees.php' );
            $settings[] = include( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/education.php' );
            
            // If the core does not have a tab an extension can still add it.
            self::$settings = apply_filters( 'TradePress_get_settings_pages', $settings );
        }

        return self::$settings;
    }

    /**
     * Save the settings.
     * 
     * @version 1.2
     */
    public static function save() {      
        global $current_tab;

        if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'TradePress-settings' ) ) {
            wp_die( __( 'Action failed. Please refresh the page and retry.', 'tradepress' ) );
        }
            
        // Trigger actions
        do_action( 'TradePress_settings_save_' . $current_tab );
        do_action( 'TradePress_update_options_' . $current_tab );
        do_action( 'TradePress_update_options' );

        self::add_message( __( 'Your settings have been saved.', 'tradepress' ) );
        self::check_download_folder_protection();
                      
        do_action( 'TradePress_settings_saved' );
    }

    /**
     * Add a gree-style message for display under the tabs on settings pages...
     * @param string $text
     */
    public static function add_message( $text ) {
        self::$messages[] = $text;
    }

    /**
     * Add an error for display under the tabs on settings pages...
     * @param string $text
     */
    public static function add_error( $text ) {
        self::$errors[] = $text;
    }

    /**
    * Display a blue-style message... 
    * @param mixed $text
    */
    public static function add_info( $text ) {
        self::$info[] = $text;
    }    

    /**
     * Output messages + errors.
     * @return string
     */
    public static function show_messages() {
        if ( sizeof( self::$errors ) > 0 ) {
            foreach ( self::$errors as $error ) {
                echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
            }
        } elseif ( sizeof( self::$messages ) > 0 ) {
            foreach ( self::$messages as $message ) {
                echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
            }
        } elseif ( sizeof( self::$info ) > 0 ) {
            foreach ( self::$info as $information ) {
                echo '<div id="message" class="notice notice-info"><p><strong>' . esc_html( $information ) . '</strong></p></div>';
            }
        }
    }

    /**
     * Settings page.
     *
     * Handles the display of the main TradePress settings page in admin.
     * 
     * @version 1.2
     */
    public static function output() {
        global $current_section, $current_tab, $TradePress_default_section;

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        do_action( 'TradePress_settings_start' );

        wp_localize_script( 'TradePress_settings', 'TradePress_settings_params', array(
            'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'tradepress' )
        ) );

        // Include settings pages
        self::get_settings_pages();

        // Get current tab/section
        $current_tab     = empty( $_GET['tab'] ) ? self::$defaulttab : sanitize_title( $_GET['tab'] );
        $current_section = empty( $_REQUEST['section'] ) ? 'default' : sanitize_title( $_REQUEST['section'] ); 
        
        // Save settings if data has been posted
        if ( ! empty( $_POST ) ) {
            self::save();
        }

        // Add any posted messages
        if ( ! empty( $_GET['TradePress_error'] ) ) {
            self::add_error( stripslashes( $_GET['TradePress_error'] ) );
        }

        if ( ! empty( $_GET['TradePress_message'] ) ) {
            self::add_message( stripslashes( $_GET['TradePress_message'] ) );
        }
        
        if ( ! empty( $_GET['TradePress_info'] ) ) {
            self::add_info( stripslashes( $_GET['TradePress_info'] ) );
        }

        // Get tabs for the settings page
        $tabs = apply_filters( 'TradePress_settings_tabs_array', array() );

        $save_button_text = apply_filters( 'TradePress_settings_save_button_text', __( 'Save changes' , 'tradepress' ) );
        
        // Update the path to the HTML template file
        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/html-admin-settings.php';
    }

    /**
     * Get a setting from the settings API.
     *
     * @param mixed $option_name
     * @return string
     */
    public static function get_option( $option_name, $default = '' ) {
        return TradePress_Form_Fields::get_option( $option_name, $default );
    }

    /**
     * Output admin fields.
     *
     * Loops though the TradePress options array and outputs each field.
     *
     * @param array $options Opens array to output
     */
    public static function output_fields( $options ) {
        TradePress_Form_Fields::output_fields( $options );
    }

    /**
     * Helper function to get the formated description and tip HTML for a
     * given form field. Plugins can call this when implementing their own custom
     * settings types.
     *
     * @param  array $value The form field value array
     * @return array The description and tip as a 2 element array
     * 
     * @version 1.0
     */
    public static function get_field_description( $value ) {
        return TradePress_Form_Fields::get_field_description( $value );
    }

    /**
    * Validate values to confirm to strict requirements...
    * 
    * @param mixed $options
    * @version 1.0
    */
    public static function validate_field( $options ) {
    
    }
    
    /**
     * Save admin fields.
     *
     * Loops though the TradePress options array and outputs each field.
     *
     * @param array $options Options array to output
     * @return bool
     */
    public static function save_fields( $options ) {
        return TradePress_Form_Fields::save_fields( $options );
    }

    /**
     * Checks which method we're using to serve downloads.
     *
     * If using force or x-sendfile, this ensures the .htaccess is in place.
     */
    public static function check_download_folder_protection() {
        $upload_dir      = wp_upload_dir();
        $downloads_url   = $upload_dir['basedir'] . '/tradepress_uploads';
        $download_method = get_option( 'TradePress_file_download_method' );

        if ( 'redirect' == $download_method ) {

            // Redirect method - don't protect
            if ( file_exists( $downloads_url . '/.htaccess' ) ) {
                unlink( $downloads_url . '/.htaccess' );
            }

        } else {

            // Force method - protect, add rules to the htaccess file
            if ( ! file_exists( $downloads_url . '/.htaccess' ) ) {
                if ( $file_handle = @fopen( $downloads_url . '/.htaccess', 'w' ) ) {
                    fwrite( $file_handle, 'deny from all' );
                    fclose( $file_handle );
                }
            }
        }
    }
}

endif;
