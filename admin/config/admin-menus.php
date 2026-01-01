<?php
/**
 * TradePress - Plugin Menus
 *
 * Maintain plugins admin menu and tab-menus here.  
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TradePress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_Menus' ) ) :

/**
 * TradePress_Admin_Menus Class.
 * 
 * @version 2.0
 */
class TradePress_Admin_Menus {
    var $slug = 'TradePress';
    public $pagehook = null;

    function primary_admin_menu() {
        $setup_complete = get_option('tradepress_setup_complete', false);
        
        $this->pagehook = add_menu_page( __('TradePress', $this->slug), __('TradePress', $this->slug), 'manage_options', $this->slug, array(&$this, 'settings_page'), 'dashicons-admin-users', '42.78578' );
        
        if (!$setup_complete) {
            // Only show setup wizard when setup is not complete
            require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/setup-wizard/setup-wizard.php');
            return;
        }
        
        // Show all menu items only after setup is complete
        // 1. Settings (always at the top)
        add_submenu_page( $this->slug, __('Settings', $this->slug), __('Settings', $this->slug), 'manage_options', $this->slug, array(&$this, 'settings_page') );
        
        // 2. Development (only when developer mode is enabled)
        if (get_option('tradepress_developer_mode', false)) {
            add_submenu_page( $this->slug, __('Development',  $this->slug), __('Development',  $this->slug), 'manage_options', 'tradepress_development', array( $this, 'development_page' ) );
        }
        
        // 3. Focus (daily trading routine and priorities)
        add_submenu_page( $this->slug, __('Focus', 'tradepress'), __('Focus', 'tradepress'), 'manage_options', 'tradepress_focus', array( $this, 'focus_page' ) );
        
        // 4. Data (needs development before automation)
        add_submenu_page( $this->slug, __('Data', 'tradepress'), __('Data', 'tradepress'), 'manage_options', 'tradepress_data', array( $this, 'data_page' ) );
        
        // 5. Watchlists (need to know which stocks to monitor)
        add_submenu_page( $this->slug, __('Watchlists', 'tradepress'), __('Watchlists', 'tradepress'), 'manage_options', 'tradepress_watchlists', array( $this, 'watchlists_page' ) );
        
        // 6. Automation (needs smooth operation for all-day scoring)
        add_submenu_page( $this->slug, __('Automation',  $this->slug), __('Automation',  $this->slug), 'manage_options', 'tradepress_automation', array( $this, 'automation_page' ) );
        
        // 7. Scoring Directives (first major milestone)
        add_submenu_page( $this->slug, __('Scoring Directives', 'tradepress'), __('Scoring Directives', 'tradepress'), 'manage_options', 'tradepress_scoring_directives', array( $this, 'scoring_directives_page' ) );
        
        // 8. Research (mix of automatic and manual import) - now includes Social Networks
        add_submenu_page( $this->slug, __('Research',  $this->slug), __('Research',  $this->slug), 'manage_options', 'tradepress_research', array( $this, 'research_page' ) );
        
        // 9. Analysis (results important for trading strategies)
        add_submenu_page( $this->slug, __('Analysis', 'tradepress'), __('Analysis', 'tradepress'), 'manage_options', 'tradepress_analysis', array( $this, 'analysis_page' ) );
        
        // 10. Trading (paper trading early, live trading later)
        add_submenu_page( $this->slug, __('Trading', 'tradepress'), __('Trading', 'tradepress'), 'manage_options', 'tradepress_trading', array( $this, 'trading_page' ) );
        
        // Trading Platforms (keep for now)
        add_submenu_page( $this->slug, __('Trading Platforms', $this->slug), __('Trading Platforms', $this->slug), 'manage_options', 'tradepress_platforms', array( $this, 'platforms_page' ) );
    }
    
    /**
    * Adds items to the plugins administration menu...
    * 
    * @version 2.0
    */
    function secondary_menu_items() {
        $setup_complete = get_option('tradepress_setup_complete', false);
        if (!$setup_complete) {
            return; // Don't show secondary menu items until setup is complete
        }
        
        // 10. Symbols (custom post type) - moved to bottom
        add_submenu_page( $this->slug, __('Symbols',      $this->slug), __('Symbols', $this->slug), 'manage_options', 'edit.php?post_type=symbols', '' );
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (!class_exists('TradePress_Admin_Settings')) {
            include_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/admin-settings.php';
        }
        
        // Also make sure to load the settings loader
        $settings_loader = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/settings-loader.php';
        if (file_exists($settings_loader)) {
            require_once $settings_loader;
        }
        
        TradePress_Admin_Settings::output();
    }
    public function platforms_page() {
        TradePress_Admin_TradingPlatforms_Page::output(); # TODO: rename from views to tabs    
    }   
    public function automation_page() {
        TradePress_Admin_Automation_Page::output(); # TODO: rename from area to tabs   
    } 
    public function research_page() {
        require_once(TRADEPRESS_PLUGIN_DIR . 'admin/page/research/research-tabs.php');
        
        // Debug output to help identify issues
        if (!class_exists('TradePress_Research')) {
            echo '<div class="wrap"><h1>' . __('Research', 'tradepress') . '</h1>';
            echo '<div class="notice notice-error"><p>' . __('Error: TradePress_Research class not found. Check file path and class name.', 'tradepress') . '</p>';
            echo '<p>Looking for file at: ' . TRADEPRESS_PLUGIN_DIR . 'admin/page/research/research-tabs.php</p></div>';
            echo '</div>';
            return;
        }
        
        // Create an instance of the research class and display the page
        $research = new TradePress_Research('tradepress', TRADEPRESS_VERSION);
        $research->display_page();
    }    
    public function development_page() {
        TradePress_Admin_Development_Page::output();    
    }    
    public function trading_page() {
        require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/trading/trading-tabs.php');
        $trading = new TradePress_Admin_Trading_Page();
        $trading->output();
    }

    public function watchlists_page() {
        require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/watchlists/watchlists-tabs.php');
        
        $watchlists = new TradePress_Admin_Watchlists_Page();
        $watchlists->output();
    }
    
    /**
     * Social platforms page
     */
    public function social_page() {
        require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/socialplatforms/socialplatforms-tabs.php');
        
        // Add debug to discover the current screen ID
        global $current_screen;
        if ($current_screen) {
            // Write the current screen ID to a temporary file for debugging
            $debug_file = TRADEPRESS_PLUGIN_DIR_PATH . 'debug_screen_id.txt';
            file_put_contents($debug_file, "Current screen ID: " . $current_screen->id);
            
            // Force help tabs to display directly on this screen
            require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/admin-help.php');
            $help = new TradePress_Admin_Help();
            
            // Basic help sidebar
            $current_screen->set_help_sidebar(
                '<p><strong>' . __('For more information:', 'tradepress') . '</strong></p>' .
                '<p><a href="https://github.com/ryanbayne/TradePress" target="_blank">' . __('GitHub', 'tradepress') . '</a></p>' .
                '<p><a href="https://TradePress.wordpress.com" target="_blank">' . __('Blog', 'tradepress') . '</a></p>' .
                '<p><a href="https://discord.gg/ScrhXPE" target="_blank">' . __('Discord', 'tradepress') . '</a></p>'
            );
            
            // Add basic help tab
            $current_screen->add_help_tab(array(
                'id'      => 'social_overview',
                'title'   => __('Social Platforms Help', 'tradepress'),
                'content' => '<h2>' . __('Social Platforms Overview', 'tradepress') . '</h2>' .
                            '<p>' . __('The Social Platforms page allows you to configure and manage integrations with various social media platforms.', 'tradepress') . '</p>' .
                            '<p>' . __('Use the tabs below to navigate between different platform settings.', 'tradepress') . '</p>'
            ));
        }
        
        // Output the social platforms tabs interface
        TradePress_Admin_SocialPlatforms_Tabs::output(); 
    }

    // Output the Data tab content
    public function data_page() {
        require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/data/data-tabs.php');
        TradePress_Admin_Data_Tabs::output();
    }

    // Output the Analysis tab content
    public function analysis_page() {
        TradePress_Admin_Analysis_Tabs::output();
    }

    public function focus_page() {
        require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/focus/focus-tabs.php');
        $focus = new TradePress_Admin_Focus_Page();
        $focus->output();
    }

    public function scoring_directives_page() {
        // Ensure the main tabs controller for Scoring Directives is loaded
        $tabs_file = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/scoring-directives/scoring-directives-tabs.php';
        if (file_exists($tabs_file)) {
            require_once $tabs_file;
            if (class_exists('TradePress_Admin_Scoring_Directives_Page')) {
                $scoring_directives_page = new TradePress_Admin_Scoring_Directives_Page();
                $scoring_directives_page->output();
            } else {
                // Handle class not found error
                echo '<div class="error"><p>' . esc_html__('Scoring Directives page class not found.', 'tradepress') . '</p></div>';
            }
        } else {
            // Handle file not found error
            echo '<div class="error"><p>' . esc_html__('Scoring Directives tabs file not found.', 'tradepress') . '</p></div>';
        }
    }
}

endif;

$class = new TradePress_Admin_Menus();

add_action('admin_menu', array( $class, 'primary_admin_menu'), 0);
add_action('admin_menu', array( $class, 'secondary_menu_items'), 1000); 

unset($class);