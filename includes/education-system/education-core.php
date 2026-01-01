<?php
/**
 * TradePress Education Core
 *
 * Core functionality for the Trading Academy educational system.
 *
 * @package TradePress\Education
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TradePress_Education_Core
 * 
 * Core class for the Trading Academy educational system. Initializes all education
 * related functionality including custom post types, taxonomies, and related systems.
 * 
 * @since 1.0.0
 */
class TradePress_Education_Core {
    
    /**
     * Singleton instance
     *
     * @var TradePress_Education_Core
     */
    private static $instance = null;
    
    /**
     * Get the singleton instance
     *
     * @return TradePress_Education_Core
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize custom post types and taxonomies
        add_action('init', array($this, 'register_post_types_taxonomies'));
        
        // Add admin menu pages
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Register post types and taxonomies
     */
    public function register_post_types_taxonomies() {
        // This will be implemented to register lesson CPT and module taxonomy
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // This will be implemented to add the Trading Academy menu
    }
    
    // Additional methods will be implemented for core functionality
}

// Initialize the education core
add_action('plugins_loaded', array('TradePress_Education_Core', 'get_instance'));
