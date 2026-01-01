<?php
/**
 * TradePress - Quick Tools Toolbar
 *
 * Displays a toolbar with quick access to favorite tabs and frequently used tools
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Toolbars
 * @since    1.0
 * 
 * @version 1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  

if( !class_exists( 'TradePress_Admin_Toolbar_QuickTools' ) ) :

class TradePress_Admin_Toolbar_QuickTools {
    /**
     * Favorite tabs data
     *
     * @var array
     */
    private $favorite_tabs = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        // Load favorite tabs directly from database to avoid caching issues
        global $wpdb;
        $this->load_favorite_tabs();
        
        // Debug output to reveal what's being loaded
        if (current_user_can('manage_options')) {
            add_action('admin_notices', array($this, 'debug_favorite_tabs'));
        }
        
        // Initialize the toolbar
        $this->init();
    }

    /**
     * Debug favorite tabs data
     */
    public function debug_favorite_tabs() {
        // Hide in production - only for troubleshooting
        if (!isset($_GET['debug_quicktools'])) {
            return;
        }
        
        echo '<div class="notice notice-info"><p>Favorite Tabs Debug: ';
        echo 'Count: ' . count($this->favorite_tabs) . ', ';
        echo 'Data: ' . esc_html(wp_json_encode($this->favorite_tabs));
        
        // Also check direct database query
        global $wpdb;
        $direct_option = $wpdb->get_var($wpdb->prepare("
            SELECT option_value FROM $wpdb->options 
            WHERE option_name = %s LIMIT 1
        ", 'tradepress_favorite_tabs'));
        
        echo '<br>Direct DB Value: ' . (is_null($direct_option) ? 'NULL' : esc_html($direct_option));
        echo '</p></div>';
    }
    
    /**
     * Load favorite tabs from options
     */
    private function load_favorite_tabs() {
        // Bypass potential transient/cache issues by loading directly
        global $wpdb;
        $option_value = $wpdb->get_var($wpdb->prepare("
            SELECT option_value FROM $wpdb->options 
            WHERE option_name = %s LIMIT 1
        ", 'tradepress_favorite_tabs'));
        
        if (!is_null($option_value)) {
            $this->favorite_tabs = maybe_unserialize($option_value);
            if (!is_array($this->favorite_tabs)) {
                // Ensure it's an array even if data is corrupt
                $this->favorite_tabs = array();
            }
        } else {
            // Option doesn't exist yet, create it with empty array
            $this->favorite_tabs = array();
            update_option('tradepress_favorite_tabs', array());
        }
    }
    
    /**
     * Initialize the toolbar
     */
    private function init() {
        global $wp_admin_bar;
        
        // Add parent menu
        $this->add_parent_menu();
        
        // Add favorite tabs submenu
        $this->add_favorite_tabs_menu();
        
        // Add quick tools submenu
        $this->add_quick_tools_menu();
        
        // Add development notice if in demo mode
        $this->add_development_notice();
    }
    
    /**
     * Add parent menu to toolbar
     */
    private function add_parent_menu() {
        global $wp_admin_bar;
        
        // Parent menu
        $args = array(
            'id'    => 'tradepress-toolbar-quicktools',
            'title' => __( 'TradePress QuickTools', 'tradepress' ),
        );
        
        $wp_admin_bar->add_menu($args);
    }
    
    /**
     * Add favorite tabs to submenu
     */
    private function add_favorite_tabs_menu() {
        global $wp_admin_bar;
        
        // Add favorite tabs section parent
        $args = array(
            'id'     => 'tradepress-favorite-tabs-section',
            'parent' => 'tradepress-toolbar-quicktools',
            'title'  => __( 'Favorite Tabs', 'tradepress' ),
            'href'   => admin_url('admin.php?page=tradepress-settings&tab=general&section=favetabs'),
        );
        $wp_admin_bar->add_menu($args);
        
        // Get all tabs info for displaying favorites
        $all_tabs = $this->get_all_tabs();
        
        // Check if we have any favorite tabs
        if (empty($this->favorite_tabs)) {
            // No favorites message - add as a child of the section
            $args = array(
                'id'     => 'tradepress-no-favorites',
                'parent' => 'tradepress-favorite-tabs-section',
                'title'  => __( 'No favorite tabs selected', 'tradepress' ),
                'href'   => admin_url('admin.php?page=tradepress-settings&tab=general&section=favetabs'),
                'meta'   => array(
                    'class' => 'quicktools-no-favorites'
                )
            );
            $wp_admin_bar->add_menu($args);
        } else {
            // Add each favorite tab as a menu item under the favorite tabs section
            foreach ($this->favorite_tabs as $tab_id) {
                // Skip if the tab doesn't exist in our all_tabs array
                if (!isset($all_tabs[$tab_id])) {
                    continue;
                }
                
                $tab = $all_tabs[$tab_id];
                
                $args = array(
                    'id'     => 'tradepress-favorite-' . $tab_id,
                    'parent' => 'tradepress-favorite-tabs-section', // Use the section as parent
                    'title'  => $tab['title'],
                    'href'   => $tab['url'],
                );
                
                $wp_admin_bar->add_menu($args);
            }
        }
        
        // Add link to manage favorites - add directly to the parent to separate from the favorites list
        $args = array(
            'id'     => 'tradepress-manage-favorites',
            'parent' => 'tradepress-toolbar-quicktools',
            'title'  => __( 'Manage Favorites', 'tradepress' ),
            'href'   => admin_url('admin.php?page=tradepress-settings&tab=general&section=favetabs'),
            'meta'   => array(
                'class' => 'quicktools-manage-favorites'
            )
        );
        
        $wp_admin_bar->add_menu($args);
    }
    
    /**
     * Add quick tools to submenu
     */
    private function add_quick_tools_menu() {
        global $wp_admin_bar;
        
        // Add tools section parent
        $args = array(
            'id'     => 'tradepress-quick-tools-section',
            'parent' => 'tradepress-toolbar-quicktools',
            'title'  => __( 'Quick Tools', 'tradepress' ),
        );
        $wp_admin_bar->add_menu($args);
        
        // Add API test tool - as child of tools section
        $args = array(
            'id'     => 'tradepress-quick-api-test',
            'parent' => 'tradepress-quick-tools-section', // Use the section as parent
            'title'  => __( 'API Connection Test', 'tradepress' ),
            'href'   => admin_url('admin.php?page=tradepress-settings&tab=api'),
        );
        $wp_admin_bar->add_menu($args);
        
        // Add earnings update tool - as child of tools section
        $args = array(
            'id'     => 'tradepress-quick-earnings-update',
            'parent' => 'tradepress-quick-tools-section', // Use the section as parent
            'title'  => __( 'Update Earnings Data', 'tradepress' ),
            'href'   => esc_url(wp_nonce_url(add_query_arg(array(
                'action' => 'run_earnings_calendar', 
                'page' => 'tradepress_automation', 
                'tab' => 'cron'
            ), admin_url('admin.php')), 'tradepress_run_cron')),
        );
        $wp_admin_bar->add_menu($args);
        
        // Add another useful quick tool - Stock Research
        $args = array(
            'id'     => 'tradepress-quick-stock-research',
            'parent' => 'tradepress-quick-tools-section',
            'title'  => __( 'Stock Research', 'tradepress' ),
            'href'   => admin_url('admin.php?page=tradepress_research'),
        );
        $wp_admin_bar->add_menu($args);
        
        // Add Data Tables tool
        $args = array(
            'id'     => 'tradepress-quick-data-tables',
            'parent' => 'tradepress-quick-tools-section',
            'title'  => __( 'Data Tables', 'tradepress' ),
            'href'   => admin_url('admin.php?page=tradepress_data&tab=tables'),
        );
        $wp_admin_bar->add_menu($args);
        
        // Add Reset All Pointers tool
        $args = array(
            'id'     => 'tradepress-quick-reset-pointers',
            'parent' => 'tradepress-quick-tools-section',
            'title'  => __( 'Reset All Pointers', 'tradepress' ),
            'href'   => esc_url(wp_nonce_url(add_query_arg(array(
                'action' => 'tradepress_reset_pointers'
            ), admin_url('admin-post.php')), 'tradepress_reset_pointers')),
        );
        $wp_admin_bar->add_menu($args);
    }
    
    /**
     * Add development notice to toolbar if in demo mode
     */
    private function add_development_notice() {
        global $wp_admin_bar;
        
        // Check if demo mode is active
        $is_demo_mode = function_exists('is_demo_mode') ? is_demo_mode() : false;
        
        if ($is_demo_mode) {
            $settings_url = admin_url('admin.php?page=TradePress&tab=general');
            
            $args = array(
                'id'     => 'tradepress-dev-notice',
                'title'  => __('Development in Progress', 'tradepress') . ' ⚠ ' . __('Demo Data Active', 'tradepress'),
                'href'   => $settings_url,
                'meta'   => array(
                    'class' => 'tradepress-toolbar-dev-notice',
                    'title' => __('Click to go to General Settings', 'tradepress')
                )
            );
            
            $wp_admin_bar->add_menu($args);
        }
    }
    
    /**
     * Get all available tabs in the system
     * 
     * @return array All available tabs with their information
     */
    private function get_all_tabs() {
        // This is a comprehensive mapping of tab IDs to their information
        return array(
            // Research page tabs
            'research_overview' => array(
                'title' => __('Research Overview', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research')
            ),
            'research_earnings' => array(
                'title' => __('Earnings Calendar', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=earnings')
            ),
            'research_technical' => array(
                'title' => __('Technical Analysis', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=technical')
            ),
            'research_fundamental' => array(
                'title' => __('Fundamental Analysis', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=fundamental')
            ),
            'research_news' => array(
                'title' => __('News', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=news')
            ),
            'research_chatter' => array(
                'title' => __('Chatter', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=chatter')
            ),
            
            // Automation tabs
            'automation_dashboard' => array(
                'title' => __('Automation Dashboard', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation')
            ),
            'automation_algorithm' => array(
                'title' => __('Scoring Algorithm', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation&tab=algorithm')
            ),
            'automation_signals' => array(
                'title' => __('Trading Signals', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation&tab=signals')
            ),
            'automation_trading' => array(
                'title' => __('Trading', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation&tab=trading')
            ),
            'automation_cron' => array(
                'title' => __('CRON Jobs', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation&tab=cron')
            ),
            
            // Data tabs
            'data_tables' => array(
                'title' => __('Data Tables', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_data&tab=tables')
            ),
            'data_symbols' => array(
                'title' => __('Symbols', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_data&tab=symbols')
            ),
            'data_sources' => array(
                'title' => __('Data Sources', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_data&tab=sources')
            ),
            
            // Trading tabs
            'trading_platforms' => array(
                'title' => __('Trading Platforms', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_platforms')
            ),
            
            // Settings tabs
            'settings_general' => array(
                'title' => __('General Settings', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress-settings&tab=general')
            ),
            'settings_api' => array(
                'title' => __('API Settings', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress-settings&tab=api')
            ),
            'settings_favetabs' => array(
                'title' => __('Favorite Tabs', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress-settings&tab=general&section=favetabs')
            ),
            
            // Bot tabs
            'bot_dashboard' => array(
                'title' => __('Bot Dashboard', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_bot')
            ),
        );
    }
}

endif;

return new TradePress_Admin_Toolbar_QuickTools();
