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
     * Normalize internal dependency names to registered WordPress style handles.
     *
     * @param array  $dependencies Raw dependency names.
     * @param string $asset_type   Component or layout dependency context.
     * @return array
      * @version 1.0.0
     */
    private function normalize_style_dependencies($dependencies, $asset_type = 'component') {
        $normalized = array();

        foreach ((array) $dependencies as $dependency) {
            if (strpos($dependency, 'tradepress-') === 0) {
                $normalized[] = $dependency;
                continue;
            }

            if ($dependency === 'variables') {
                $normalized[] = 'tradepress-variables';
                continue;
            }

            if ($asset_type === 'layout') {
                $normalized[] = 'tradepress-layout-' . $dependency;
                continue;
            }

            $normalized[] = 'tradepress-component-' . $dependency;
        }

        return array_values(array_unique($normalized));
    }
    
    /**
     * Constructor
      *
      * @version 1.0.0
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
      *
      * @version 1.0.0
     */
    private function detect_current_context() {
        $this->current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    }
    
    /**
     * Main asset enqueueing method
      *
      * @version 1.0.0
     */
    public function enqueue_assets() {
        // Always load CSS variables first on admin pages
        if (is_admin()) {
            $this->enqueue_base_variables();
        }

        // Test Runner page assets.
        if ($this->current_page === 'tradepress-tests') {
            $this->enqueue_test_runner_assets();
        }
        
        // UI Library specific assets
        if ($this->current_page === 'tradepress_development' && $this->current_tab === 'ui_library') {
            $this->enqueue_ui_library_assets();
        }

        // Configure directives CSS — shared by the directives, data tables, and endpoints views.
        if (
            ( $this->current_page === 'tradepress_scoring_directives' ) ||
            ( $this->current_page === 'tradepress_data' && in_array( $this->current_tab, array( 'tables', 'api_endpoints' ), true ) )
        ) {
            $this->enqueue_configure_directives_assets();
        }
    }
    
    /**
     * Enqueue base CSS variables
      *
      * @version 1.0.0
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
      *
      * @version 1.0.0
     */
    private function enqueue_ui_library_assets() {
        // Use the asset manager to get available assets
        if (!$this->asset_manager) {
            return;
        }
        
        // Enqueue CSS components with proper dependency chain
        $component_assets = $this->asset_manager->get_assets_by_category('css', 'components');
        foreach ($component_assets as $name => $asset) {
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            
            if (file_exists($asset_path)) {
                // Ensure variables are loaded as dependency for all components
                $dependencies = array_merge(
                    array('tradepress-variables'),
                    $this->normalize_style_dependencies($asset['dependencies'], 'component')
                );
                
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
                $dependencies = array_merge(
                    array('tradepress-variables'),
                    $this->normalize_style_dependencies($asset['dependencies'], 'layout')
                );
                
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
            $version = file_exists($asset_path) ? (string) filemtime($asset_path) : TRADEPRESS_VERSION;
            
            if (file_exists($asset_path)) {
                if (wp_style_is('tradepress-ui-library-page', 'registered')) {
                    wp_deregister_style('tradepress-ui-library-page');
                }

                wp_register_style(
                    'tradepress-ui-library-page',
                    $asset_url,
                    array('tradepress-variables', 'tradepress-component-buttons', 'tradepress-component-forms', 'tradepress-component-modals'),
                    $version
                );

                wp_enqueue_style('tradepress-ui-library-page');
            } else {
            }
        }
        
        // Enqueue UI Library JavaScript
        $js_assets = $this->asset_manager->get_all_assets('js');
        if (isset($js_assets['ui-library'])) {
            $asset = $js_assets['ui-library'];
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            $version = file_exists($asset_path) ? (string) filemtime($asset_path) : TRADEPRESS_VERSION;
            
            if (file_exists($asset_path)) {
                if (wp_script_is('tradepress-ui-library-js', 'registered')) {
                    wp_deregister_script('tradepress-ui-library-js');
                }

                wp_register_script(
                    'tradepress-ui-library-js',
                    $asset_url,
                    array('jquery'),
                    $version,
                    true
                );

                wp_enqueue_script('tradepress-ui-library-js');
            }
        }
        
        // Enqueue UI Library Animation JavaScript
        if (isset($js_assets['ui-library-animations'])) {
            $asset = $js_assets['ui-library-animations'];
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            $version = file_exists($asset_path) ? (string) filemtime($asset_path) : TRADEPRESS_VERSION;
            
            if (file_exists($asset_path)) {
                if (wp_script_is('tradepress-ui-library-animations-js', 'registered')) {
                    wp_deregister_script('tradepress-ui-library-animations-js');
                }

                wp_register_script(
                    'tradepress-ui-library-animations-js',
                    $asset_url,
                    array('jquery'),
                    $version,
                    true
                );

                wp_enqueue_script('tradepress-ui-library-animations-js');
            }
        }
        
        // Add debugging information
        if (defined('WP_DEBUG') && WP_DEBUG) {
        }
    }

    /**
     * Enqueue configure-directives page CSS.
     *
     * Shared by the scoring directives page and the data tables/endpoints tabs.
     * Centralised here so view files do not need direct enqueue calls.
     *
     * @version 1.0.7
     */
    private function enqueue_configure_directives_assets() {
        wp_enqueue_style(
            'tradepress-configure-directives',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/configure-directives.css',
            array(),
            TRADEPRESS_VERSION
        );
    }

    /**
     * Enqueue TradePress Testing page assets.
      *
      * @version 1.0.0
     */
    private function enqueue_test_runner_assets() {
        if (!$this->asset_manager) {
            return;
        }

        $js_assets = $this->asset_manager->get_all_assets('js');
        if (isset($js_assets['test-runner'])) {
            $asset = $js_assets['test-runner'];
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            $version = file_exists($asset_path) ? (string) filemtime($asset_path) : TRADEPRESS_VERSION;

            if (file_exists($asset_path)) {
                wp_enqueue_script(
                    'tradepress-test-runner',
                    $asset_url,
                    array('jquery'),
                    $version,
                    true
                );
            }
        }

        $style_assets = $this->asset_manager->get_all_assets('css');
        if (isset($style_assets['test-runner'])) {
            $asset = $style_assets['test-runner'];
            $asset_url = TRADEPRESS_PLUGIN_URL . 'assets/' . $asset['path'];
            $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . $asset['path'];
            $version = file_exists($asset_path) ? (string) filemtime($asset_path) : TRADEPRESS_VERSION;

            if (file_exists($asset_path)) {
                wp_enqueue_style(
                    'tradepress-test-styles',
                    $asset_url,
                    array(),
                    $version
                );
            }
        }
    }
}

// Initialize the asset queue system only if not already initialized
if (!class_exists('TradePress_Asset_Queue_Instance')) {
    class TradePress_Asset_Queue_Instance {
        private static $instance = null;
        
        /**
         * Get instance.
         *
         * @return mixed
         *
         * @version 1.0.0
         */
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
