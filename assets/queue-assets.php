<?php
/**
 * TradePress Asset Queue System
 * 
 * Centralized asset enqueueing based on current page/tab detection
 * 
 * @package TradePress/Assets
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Asset Queue Manager
 */
class TradePress_Asset_Queue {
    
    /**
     * Asset manager instance
     */
    private $asset_manager;
    
    /**
     * Current page context
     */
    private $current_page;
    
    /**
     * Current tab context
     */
    private $current_tab;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize asset manager
        if (!class_exists('TradePress_Asset_Manager')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'assets/manage-assets.php';
        }
        
        global $tradepress_assets;
        $this->asset_manager = $tradepress_assets;
        
        // Detect current context
        $this->detect_current_context();
        
        // Hook into WordPress asset enqueueing with higher priority
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'), 5);
    }
    
    /**
     * Detect current page and tab context
     */
    private function detect_current_context() {
        $this->current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    }
    
    /**
     * Main asset enqueueing method
     */
    public function enqueue_assets() {
        // Always load CSS variables first on admin pages
        if (is_admin()) {
            $this->enqueue_base_variables();
        }
        
        // UI Library specific assets
        if ($this->current_page === 'tradepress_development' && $this->current_tab === 'ui_library') {
            $this->enqueue_ui_library_assets();
        }
    }
    
    /**
     * Enqueue base CSS variables
     */
    private function enqueue_base_variables() {
        wp_enqueue_style(
            'tradepress-variables',
            TRADEPRESS_PLUGIN_URL . 'assets/css/base/variables.css',
            array(),
            TRADEPRESS_VERSION,
            'all'
        );
    }
    
    /**
     * Enqueue UI Library specific assets
     */
    private function enqueue_ui_library_assets() {
        // Use the asset manager to get available assets
        if (!$this->asset_manager) {
            error_log('TradePress: Asset manager not available');
            return;
        }
        
        // Enqueue CSS components with proper dependency chain
        $component_assets = $this->asset_manager->get_assets_by_category('css', 'components');
        foreach ($component_assets as $name => $asset) {
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            
            if (file_exists($asset_path)) {
                // Ensure variables are loaded as dependency for all components
                $dependencies = array_merge(array('tradepress-variables'), $asset['dependencies']);
                
                wp_enqueue_style(
                    'tradepress-component-' . $name,
                    $asset_url,
                    $dependencies,
                    TRADEPRESS_VERSION
                );
            }
        }
        
        // Enqueue layout assets
        $layout_assets = $this->asset_manager->get_assets_by_category('css', 'layouts');
        foreach ($layout_assets as $name => $asset) {
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            
            if (file_exists($asset_path)) {
                // Ensure variables are loaded as dependency for all layouts
                $dependencies = array_merge(array('tradepress-variables'), $asset['dependencies']);
                
                wp_enqueue_style(
                    'tradepress-layout-' . $name,
                    $asset_url,
                    $dependencies,
                    TRADEPRESS_VERSION
                );
            }
        }
        
        // Enqueue UI Library page-specific CSS
        $page_assets = $this->asset_manager->get_assets_by_category('css', 'pages');
        if (isset($page_assets['ui-library'])) {
            $asset = $page_assets['ui-library'];
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            
            if (file_exists($asset_path)) {
                wp_enqueue_style(
                    'tradepress-ui-library-page',
                    $asset_url,
                    array('tradepress-variables', 'tradepress-component-buttons', 'tradepress-component-forms', 'tradepress-component-modals'),
                    TRADEPRESS_VERSION
                );
            } else {
                error_log('TradePress: UI Library CSS file not found at: ' . $asset_path);
            }
        }
        
        // Enqueue UI Library JavaScript
        $js_assets = $this->asset_manager->get_all_assets('js');
        if (isset($js_assets['ui-library'])) {
            $asset = $js_assets['ui-library'];
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            
            if (file_exists($asset_path)) {
                wp_enqueue_script(
                    'tradepress-ui-library-js',
                    $asset_url,
                    array('jquery'),
                    TRADEPRESS_VERSION,
                    true
                );
            }
        }
        
        // Enqueue UI Library Animation JavaScript
        if (isset($js_assets['ui-library-animations'])) {
            $asset = $js_assets['ui-library-animations'];
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            
            if (file_exists($asset_path)) {
                wp_enqueue_script(
                    'tradepress-ui-library-animations-js',
                    $asset_url,
                    array('jquery'),
                    TRADEPRESS_VERSION,
                    true
                );
            }
        }
        
        // Add debugging information
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('TradePress: UI Library assets enqueued for page: ' . $this->current_page . ' tab: ' . $this->current_tab);
        }
    }
}

// Initialize the asset queue system only if not already initialized
if (!class_exists('TradePress_Asset_Queue_Instance')) {
    class TradePress_Asset_Queue_Instance {
        private static $instance = null;
        
        public static function get_instance() {
            if (self::$instance === null) {
                self::$instance = new TradePress_Asset_Queue();
            }
            return self::$instance;
        }
    }
    
    // Initialize the asset queue
    TradePress_Asset_Queue_Instance::get_instance();
}