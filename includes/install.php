<?php
/**
 * TradePress Installation Class
 *
 * Handles the installation process for the TradePress plugin
 *
 * @package TradePress/Classes
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include database debugging tools
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/installation/db-debug.php';

/**
 * TradePress_Install Class
 */
class TradePress_Install {

    /**
     * Hook in tabs.
     */
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'install'));
        
        // Add upgrade routine
        add_action('admin_init', array($this, 'check_version'), 5);
    }

    /**
     * Check TradePress version and run the updater if required.
     *
     * This check is done on all requests and runs if the versions do not match.
     */
    public function check_version() {
        if (get_option('tradepress_version') !== TRADEPRESS_VERSION) {
            $this->install();
            do_action('tradepress_updated');
        }
    }

    /**
     * Install TradePress
     */
    public function install() {
        // Check if we are not already running this routine
        if ('yes' === get_transient('tradepress_installing')) {
            return;
        }

        // If we made it till here nothing is running yet, lets set the transient now
        set_transient('tradepress_installing', 'yes', MINUTE_IN_SECONDS * 10);
        
        $this->create_options();
        $this->create_roles();
        $this->setup_environment();
        $this->create_cron_jobs();
        $this->create_symbol_posts();
        
        delete_transient('tradepress_installing');
        
        // Update version
        delete_option('tradepress_version');
        add_option('tradepress_version', TRADEPRESS_VERSION);
        
        // Flush rules after install
        flush_rewrite_rules();
        
        // Trigger action
        do_action('tradepress_installed');
    }
    
    /**
     * Create symbol posts for all tracked symbols
     */
    private function create_symbol_posts() {
        // Log the start of symbol creation
        if (class_exists('TradePress_Logger')) {
            $logger = new TradePress_Logger();
            $logger->info('Starting symbol post creation during plugin installation', 'installation');
        }
        
        // Check if symbols post type is registered
        if (!post_type_exists('symbol')) {
            // Register the post type temporarily if it doesn't exist yet
            register_post_type('symbol', array(
                'public' => false,
                'has_archive' => false
            ));
        }
        
        // Get all tracked symbols
        if (!function_exists('tradepress_get_test_stock_symbols')) {
            try {
                require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/data/symbols-data.php';
            } catch ( Exception $e ) {
                if (class_exists('TradePress_Logger')) {
                    $logger->error('Failed to load symbols data functions: ' . $e->getMessage(), 'installation');
                }
                return;
            }
        }
        
        $symbols = tradepress_get_test_stock_symbols();
        
        // Get symbols company details if available
        $company_details = array();
        if (function_exists('tradepress_get_test_company_details')) {
            $company_details = tradepress_get_test_company_details();
        }
        
        // Track how many were created
        $created_count = 0;
        $skipped_count = 0;
        
        // Create a post for each symbol
        foreach ($symbols as $symbol) {
            // Check if this symbol already exists
            $existing = get_posts(array(
                'post_type' => 'symbol',
                'name' => sanitize_title($symbol),
                'posts_per_page' => 1,
                'post_status' => 'any'
            ));
            
            if (!empty($existing)) {
                $skipped_count++;
                continue; // Skip if already exists
            }
            
            // Set up post data
            $post_data = array(
                'post_title' => strtoupper($symbol),
                'post_name' => sanitize_title($symbol),
                'post_type' => 'symbol',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed'
            );
            
            // Add description if available
            if (isset($company_details[$symbol])) {
                $company = $company_details[$symbol];
                $post_data['post_content'] = isset($company['description']) ? $company['description'] : '';
            }
            
            // Insert the post
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                $created_count++;
                
                // Add post meta for the symbol
                update_post_meta($post_id, '_tradepress_symbol', strtoupper($symbol));
                
                // Add company details as meta if available
                if (isset($company_details[$symbol])) {
                    $company = $company_details[$symbol];
                    
                    if (isset($company['sector'])) {
                        update_post_meta($post_id, '_tradepress_sector', $company['sector']);
                    }
                    
                    if (isset($company['industry'])) {
                        update_post_meta($post_id, '_tradepress_industry', $company['industry']);
                    }
                    
                    if (isset($company['exchange'])) {
                        update_post_meta($post_id, '_tradepress_exchange', $company['exchange']);
                    }
                    
                    if (isset($company['country'])) {
                        update_post_meta($post_id, '_tradepress_country', $company['country']);
                    }
                    
                    if (isset($company['market_cap_category'])) {
                        update_post_meta($post_id, '_tradepress_market_cap_category', $company['market_cap_category']);
                    }
                    
                    // Store the full company data as serialized meta
                    update_post_meta($post_id, '_tradepress_company_data', $company);
                    
                    // Generate taxonomy terms and assign them to this symbol
                    if (function_exists('tradepress_generate_symbol_taxonomy_terms')) {
                        tradepress_generate_symbol_taxonomy_terms($post_id, $company);
                    }
                }
                
                // Add some initial scoring data
                update_post_meta($post_id, '_tradepress_score', 50); // Neutral starting score
                update_post_meta($post_id, '_tradepress_last_analyzed', current_time('timestamp'));
                
                // Generate mock price data
                if (function_exists('tradepress_generate_test_price_data')) {
                    $price_data = tradepress_generate_test_price_data($symbol);
                    update_post_meta($post_id, '_tradepress_price_data', $price_data);
                }
                
                // Generate mock technical data
                if (function_exists('tradepress_generate_test_technical_data')) {
                    $technical_data = tradepress_generate_test_technical_data($symbol);
                    update_post_meta($post_id, '_tradepress_technical_data', $technical_data);
                }
                
                // Generate financial ratios if available
                if (function_exists('tradepress_generate_financial_ratios')) {
                    $financial_ratios = tradepress_generate_financial_ratios($symbol);
                    update_post_meta($post_id, '_tradepress_financial_ratios', $financial_ratios);
                }
            }
        }
        
        // Log the results
        if (class_exists('TradePress_Logger')) {
            $logger->info(
                sprintf(
                    'Symbol post creation completed. Created: %d, Skipped (already exist): %d',
                    $created_count,
                    $skipped_count
                ),
                'installation'
            );
        }
        
        // Store stats as an option
        update_option('tradepress_installed_symbols_count', $created_count);
        update_option('tradepress_installed_symbols_date', current_time('mysql'));
    }

    /**
     * Create options
     */
    private function create_options() {
        // Add options here
        add_option('tradepress_installed', 'yes');
        
        // Default settings
        add_option('tradepress_api_service', 'alpaca');
        add_option('tradepress_demo_mode', 'yes');
        add_option('tradepress_log_level', 'info');
        
        // Algorithm defaults
        add_option('tradepress_algorithm_status', 'idle');
        add_option('tradepress_algorithm_run_frequency', 'daily');
        
        // Features configuration - ensure all views default to demo mode
        $features = array(
            'dashboard' => array(
                'enabled' => true,
                'tabs' => array(
                    'overview' => array('mode' => 'demo', 'enabled' => true),
                    'performance' => array('mode' => 'demo', 'enabled' => true),
                    'alerts' => array('mode' => 'demo', 'enabled' => true)
                )
            ),
            'analysis' => array(
                'enabled' => true,
                'tabs' => array(
                    'recent_symbols' => array('mode' => 'demo', 'enabled' => true),
                    'support_resistance' => array('mode' => 'demo', 'enabled' => true)
                )
            ),
            'research' => array(
                'enabled' => true,
                'tabs' => array(
                    'market_overview' => array('mode' => 'demo', 'enabled' => true),
                    'economic_calendar' => array('mode' => 'demo', 'enabled' => true),
                    'news' => array('mode' => 'demo', 'enabled' => true)
                )
            )
        );
        
        add_option('tradepress_features', $features);
    }
    
    /**
     * Create user roles
     */
    private function create_roles() {
        // Add TradePress Admin role
        add_role(
            'tradepress_admin',
            __('TradePress Admin', 'tradepress'),
            array(
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true,
                'publish_posts' => true,
                'upload_files' => true,
                'manage_tradepress' => true,
                'view_tradepress_reports' => true,
                'manage_tradepress_settings' => true,
                'manage_tradepress_api_keys' => true
            )
        );
        
        // Add TradePress Analyst role
        add_role(
            'tradepress_analyst',
            __('TradePress Analyst', 'tradepress'),
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'publish_posts' => false,
                'upload_files' => false,
                'manage_tradepress' => false,
                'view_tradepress_reports' => true,
                'manage_tradepress_settings' => false,
                'manage_tradepress_api_keys' => false
            )
        );
        
        // Add capabilities to admin
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_tradepress');
            $admin->add_cap('view_tradepress_reports');
            $admin->add_cap('manage_tradepress_settings');
            $admin->add_cap('manage_tradepress_api_keys');
        }
    }
    
    /**
     * Setup plugin environment
     */
    private function setup_environment() {
        // Register post types
        $this->register_post_types();
        
        // Register taxonomies
        $this->register_taxonomies();
    }
    
    /**
     * Register core post types
     */
    private function register_post_types() {
        if (!is_blog_installed()) {
            return;
        }
        
        if (!post_type_exists('symbol')) {
            register_post_type('symbol', array(
                'labels' => array(
                    'name' => __('Symbols', 'tradepress'),
                    'singular_name' => __('Symbol', 'tradepress'),
                    'menu_name' => _x('Symbols', 'Admin menu name', 'tradepress'),
                    'add_new' => __('Add Symbol', 'tradepress'),
                    'add_new_item' => __('Add New Symbol', 'tradepress'),
                    'edit' => __('Edit', 'tradepress'),
                    'edit_item' => __('Edit Symbol', 'tradepress'),
                    'new_item' => __('New Symbol', 'tradepress'),
                    'view' => __('View Symbol', 'tradepress'),
                    'view_item' => __('View Symbol', 'tradepress'),
                    'search_items' => __('Search Symbols', 'tradepress'),
                    'not_found' => __('No symbols found', 'tradepress'),
                    'not_found_in_trash' => __('No symbols found in trash', 'tradepress'),
                ),
                'description' => __('Financial symbols for tracking and analysis.', 'tradepress'),
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => 'tradepress',
                'capability_type' => 'post',
                'map_meta_cap' => true,
                'publicly_queryable' => true,
                'exclude_from_search' => false,
                'hierarchical' => false,
                'has_archive' => true,
                'query_var' => true,
                'menu_icon' => 'dashicons-chart-line',
                'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'symbol')
            ));
        }
    }
    
    /**
     * Register taxonomies
     */
    private function register_taxonomies() {
        if (!is_blog_installed()) {
            return;
        }
        
        if (!taxonomy_exists('symbol_category')) {
            register_taxonomy('symbol_category', array('symbol'), array(
                'hierarchical' => true,
                'label' => __('Symbol Categories', 'tradepress'),
                'labels' => array(
                    'name' => __('Symbol Categories', 'tradepress'),
                    'singular_name' => __('Symbol Category', 'tradepress'),
                    'menu_name' => _x('Categories', 'Admin menu name', 'tradepress'),
                    'search_items' => __('Search Symbol Categories', 'tradepress'),
                    'all_items' => __('All Symbol Categories', 'tradepress'),
                    'parent_item' => __('Parent Symbol Category', 'tradepress'),
                    'parent_item_colon' => __('Parent Symbol Category:', 'tradepress'),
                    'edit_item' => __('Edit Symbol Category', 'tradepress'),
                    'update_item' => __('Update Symbol Category', 'tradepress'),
                    'add_new_item' => __('Add New Symbol Category', 'tradepress'),
                    'new_item_name' => __('New Symbol Category Name', 'tradepress')
                ),
                'show_ui' => true,
                'query_var' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'symbol-category')
            ));
        }
        
        if (!taxonomy_exists('symbol_tag')) {
            register_taxonomy('symbol_tag', array('symbol'), array(
                'hierarchical' => false,
                'label' => __('Symbol Tags', 'tradepress'),
                'labels' => array(
                    'name' => __('Symbol Tags', 'tradepress'),
                    'singular_name' => __('Symbol Tag', 'tradepress'),
                    'menu_name' => _x('Tags', 'Admin menu name', 'tradepress'),
                    'search_items' => __('Search Symbol Tags', 'tradepress'),
                    'all_items' => __('All Symbol Tags', 'tradepress'),
                    'edit_item' => __('Edit Symbol Tag', 'tradepress'),
                    'update_item' => __('Update Symbol Tag', 'tradepress'),
                    'add_new_item' => __('Add New Symbol Tag', 'tradepress'),
                    'new_item_name' => __('New Symbol Tag Name', 'tradepress')
                ),
                'show_ui' => true,
                'query_var' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'symbol-tag')
            ));
        }
    }
    
    /**
     * Create scheduled events (cron jobs)
     */
    private function create_cron_jobs() {
        // Schedule daily algorithm run
        if (!wp_next_scheduled('tradepress_daily_algorithm_run')) {
            wp_schedule_event(time(), 'daily', 'tradepress_daily_algorithm_run');
        }
        
        // Schedule data cleanup
        if (!wp_next_scheduled('tradepress_cleanup_old_data')) {
            wp_schedule_event(time(), 'weekly', 'tradepress_cleanup_old_data');
        }
    }
}

// Add a debug link to the plugin's action links
function tradepress_add_db_debug_action_link($links) {
    $debug_link = '<a href="' . admin_url('tools.php?page=tradepress-db-diagnosis') . '">DB Diagnosis</a>';
    array_unshift($links, $debug_link);
    return $links;
}
add_filter('plugin_action_links_tradepress/tradepress.php', 'tradepress_add_db_debug_action_link');

new TradePress_Install();
