<?php
/**
 * Template Loader for TradePress
 *
 * @class     TradePress_Template_Loader
 * @version   1.0.0
 * @package   TradePress/Includes
 * @category  Class
 * @author    Ryan Bayne
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class TradePress_Template_Loader {
    
    /**
     * Path to the plugin directory
     *
     * @var string
     */
    protected $plugin_path;

    /**
     * Initialize the template loader
     */
    public function __construct() {
        $this->plugin_path = plugin_dir_path( dirname( __FILE__ ) );
        
        // Add filters to load templates from our plugin
        add_filter( 'template_include', array( $this, 'template_loader' ) );
    }

    /**
     * Load a template from the plugin, if it exists
     *
     * @param string $template Template file path
     * @return string
     */
    public function template_loader( $template ) {
        $post_type = get_post_type();
        
        // Only override templates for our custom post types
        if ( !in_array( $post_type, array( 'symbols', 'tp_step' ) ) ) {
            return $template;
        }
        
        // Check if a custom template exists in the theme
        $custom_template_path = $this->get_template_path_from_theme();
        if ( !empty( $custom_template_path ) ) {
            return $custom_template_path;
        }
        
        // Otherwise, use our plugin template
        if ( is_singular( 'symbols' ) ) {
            $plugin_template = $this->plugin_path . 'theme/single-symbols.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        } elseif ( is_post_type_archive( 'symbols' ) ) {
            $plugin_template = $this->plugin_path . 'theme/archive-symbols.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        } elseif ( is_singular( 'tp_step' ) ) {
            $plugin_template = $this->plugin_path . 'theme/single-tp_step.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Check if theme has a template that should be used instead
     *
     * @return string|boolean
     */
    private function get_template_path_from_theme() {
        if ( is_singular( 'symbols' ) ) {
            return locate_template( 'single-symbols.php' );
        } elseif ( is_post_type_archive( 'symbols' ) ) {
            return locate_template( 'archive-symbols.php' );
        } elseif ( is_singular( 'tp_step' ) ) {
            return locate_template( 'single-tp_step.php' );
        }
        return false;
    }
    
    /**
     * Get template part from plugin templates directory
     *
     * @param string $slug Template slug
     * @param string $name Template name (optional)
     */
    public static function get_template_part( $slug, $name = '' ) {
        $plugin_path = plugin_dir_path( dirname( __FILE__ ) );
        $template = '';
        
        // Look in the theme first
        if ( $name ) {
            $template = locate_template( array( "{$slug}-{$name}.php", "tradepress/{$slug}-{$name}.php" ) );
        }
        
        if ( !$template && $name && file_exists( $plugin_path . "templates/{$slug}-{$name}.php" ) ) {
            $template = $plugin_path . "templates/{$slug}-{$name}.php";
        }
        
        // If name is empty or template not found, look for slug
        if ( !$template ) {
            $template_path = $plugin_path . "includes/posts/templates/{$slug}.php";
            if ( $name ) {
                $template = locate_template( array( "{$slug}.php", "tradepress/{$slug}.php" ) );
            }
            
            if ( !$template && file_exists( $template_path ) ) {
                $template = $template_path;
            }
        }
        
        // Allow 3rd party plugins to filter template file
        $template = apply_filters( 'tradepress_get_template_part', $template, $slug, $name );
        
        if ( $template ) {
            load_template( $template, false );
        }
    }
}
