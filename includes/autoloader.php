<?php
/**
 * TradePress - SPL Autoloader Class
 *
 * @author   Ryan Bayne
 * @category System
 * @package  TradePress/System
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    

/**
* TradePress SPL Autoloader Class
*/
class TradePress_Autoloader {

    /**
     * Path to the includes directory.
     *
     * @var string
     */
    private $include_path = '';

    /**
     * The Constructor.
     * 
     * @version 1.2
     */
    public function __construct() {
        if ( function_exists( "__autoload" ) ) {
            spl_autoload_register( "__autoload" );
        }

        spl_autoload_register( array( $this, 'autoload' ) );

        $this->include_path = untrailingslashit( plugin_dir_path( TRADEPRESS_MAINFILE ) ) . '/includes/';
    }

    /**
     * Take a class name and turn it into a file name.
     *
     * @param  string $class
     * @return string
     */
    private function get_file_name_from_class( $class ) {
        return 'class.' . str_replace( '_', '-', $class ) . '.php';
    }

    /**
     * Include a class file.
     *
     * @param  string $path
     * @return bool successful or not
     */
    private function load_file( $path ) {
        if ( $path && is_readable( $path ) ) {
            try {
                include_once( $path );
                return true;
            } catch ( Exception $e ) {
                error_log( 'TradePress Autoloader: Failed to load file ' . $path . ': ' . $e->getMessage() );
                return false;
            }
        }
        return false;
    }

    /**
     * Auto-load TradePress classes on demand to reduce memory consumption.
     *
     * @param string $class
     */
    public function autoload( $class ) {
        $class = strtolower( $class );
        $file  = $this->get_file_name_from_class( $class );
        $path  = '';
   
        if ( strpos( $class, 'TradePress_shortcode_' ) === 0 ) {
            $path = $this->include_path . 'shortcodes/';
        } elseif ( strpos( $class, 'tradepress_meta_box' ) === 0 ) {
            $path = $this->include_path . 'admin/meta-boxes/';
        } elseif ( strpos( $class, 'TradePress_admin' ) === 0 ) {
            $path = $this->include_path . 'admin/';
        } 
    
        if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, 'TradePress-' ) === 0 ) ) {
            $this->load_file( $this->include_path . $file );
        }
    }
}

new TradePress_Autoloader();