<?php
/**
 * TradePress - Page template split screen...
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TradePress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TradePress_Page_Template_Splitscreen' ) ) :

class TradePress_Page_Template_Splitscreen {

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * The array of templates that this plugin tracks.
     */
    protected $templates;

    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    function __construct() {

        $this->templates = array();

        // Add a filter to the attributes metabox to inject template into the cache.
        add_filter( 'theme_page_templates', array( $this, 'add_new_template' ) );

        // Add a filter to the save post to inject out template into the page cache
        add_filter( 'wp_insert_post_data', array( $this, 'register_project_templates' ) );

        // Add a filter to the template include to determine if the page has our
        // template assigned and return it's path
        add_filter( 'template_include', array( $this, 'view_project_template') );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_css' ), 10 );
            
        // Add your templates to this array.
        $this->templates = array(
            'splitscreen-template.php' => 'TradePress Split Screen',
        );
    }
    
    public function enqueue_public_css() {
        wp_register_style( 'TradePress-splitscreen-styles', TRADEPRESS_PLUGIN_URL . '/assets/css/TradePress-splitscreen.css' );            
        wp_enqueue_style( 'TradePress-splitscreen-styles', TRADEPRESS_PLUGIN_URL . '/assets/css/TradePress-splitscreen.css' );        
    }

    /**
     * Adds our template to the page dropdown for v4.7+
     *
     */
    public function add_new_template( $posts_templates ) {
        $posts_templates = array_merge( $posts_templates, $this->templates );
        return $posts_templates;
    }

    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     */
    public function register_project_templates( $atts ) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list.
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache to allow WordPress to pick it up for listing available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    }

    /**
     * Checks if the template is assigned to the page
     */
    public function view_project_template( $template ) {
        // Return the search template if we're searching (instead of the template for the first result)
        if ( is_search() ) {
            return $template;
        }

        // Get global post
        global $post;

        // Return template if post is empty
        if ( ! $post ) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if ( ! isset( $this->templates[get_post_meta(
            $post->ID, '_wp_page_template', true
        )] ) ) {
            return $template;
        }

        // Allows filtering of file path
        $filepath = apply_filters( 'page_templater_plugin_dir_path', plugin_dir_path( __FILE__ ) );

        $file =  $filepath . get_post_meta(
            $post->ID, '_wp_page_template', true
        );

        // Just to be safe, we check if the file exist first
        if ( file_exists( $file ) ) {
            return $file;
        } else {
            echo $file;
        }

        // Return template
        return $template;

    }    
}

endif;

new TradePress_Page_Template_Splitscreen();