<?php
/**
 * TradePress UI Library
 *
 * @package TradePress/Admin/Views
 * @version 1.0.8
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin_Development_UI_Library Class
 */
class TradePress_Admin_Development_UI_Library {
    
    /**
     * Output the UI Library view
     */
    public static function output() {
        // Include form handlers
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ui-library-forms.php';
        
        // Display success messages
        TradePress_UI_Library_Forms::display_messages();
        
        // Verify assets are loaded by queue-assets.php
        self::verify_required_assets();
        
        // Localize script for UI Library functionality
        // (Script must be already enqueued by queue-assets.php)
        if (wp_script_is('tradepress-ui-library-js', 'enqueued')) {
            wp_localize_script('tradepress-ui-library-js', 'TradePressUILibrary', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tradepress_ui_library_nonce'),
                'strings' => array(
                    'colorInfo' => __('Color Information', 'tradepress'),
                    'colorName' => __('Color Name:', 'tradepress'),
                    'hexValue' => __('Hex Value:', 'tradepress'),
                    'cssClass' => __('CSS Class:', 'tradepress')
                )
            ));
        }
        
        // Include main UI Library container partial
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/main-container.php';
        
        // Add CSS class checker if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::output_css_class_checker();
        }
    }

    /**
     * Output CSS Class Checker for debugging
     */
    private static function output_css_class_checker() {
        echo '<div class="wrap" style="margin-top: 20px; padding: 20px; background: #f1f1f1; border: 1px solid #ccc;">';
        echo '<h2>🔍 CSS Class Checker (Debug Mode)</h2>';
        
        $plugin_dir = TRADEPRESS_PLUGIN_DIR_PATH;
        $ui_library_path = $plugin_dir . 'admin/page/development/partials/ui-library/';
        $css_path = $plugin_dir . 'assets/css/';
        
        // Check if paths exist
        if (!is_dir($ui_library_path)) {
            echo '<p style="color: red;">❌ UI Library path not found: ' . esc_html($ui_library_path) . '</p>';
            echo '</div>';
            return;
        }
        
        if (!is_dir($css_path)) {
            echo '<p style="color: red;">❌ CSS assets path not found: ' . esc_html($css_path) . '</p>';
            echo '</div>';
            return;
        }
        
        $defined_css_classes = self::get_css_classes($css_path);
        $used_html_classes = self::get_html_classes($ui_library_path);
        
        echo '<p><strong>Found:</strong> ' . count($defined_css_classes) . ' unique CSS class definitions</p>';
        echo '<p><strong>Scanning:</strong> ' . count($used_html_classes) . ' UI Library files</p>';
        
        $mismatches = array();
        $mismatch_count = 0;
        
        foreach ($used_html_classes as $file => $classes) {
            $file_mismatches = array();
            foreach ($classes as $class) {
                // Ignore empty strings, PHP template tags, WordPress core classes
                if (empty($class) || 
                    strpos($class, '<?') !== false || 
                    strpos($class, '{{') !== false ||
                    strpos($class, 'dashicons') === 0 ||
                    strpos($class, 'spinner') === 0 ||
                    strpos($class, 'wrap') === 0 ||
                    $class === 'tradepress-accordion-faq' || // Replaced with standard tradepress-accordion
                    in_array($class, ['is-active', 'active', 'disabled', 'show', 'hidden'])) {
                    continue;
                }
                if (!in_array($class, $defined_css_classes, true)) {
                    $file_mismatches[] = $class;
                }
            }
            if (!empty($file_mismatches)) {
                $mismatches[$file] = $file_mismatches;
                $mismatch_count += count($file_mismatches);
            }
        }
        
        if (empty($mismatches)) {
            echo '<div style="background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">';
            echo '✅ <strong>All checks passed!</strong> No undefined classes found in UI Library files.';
            echo '</div>';
        } else {
            echo '<div style="background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24; margin-bottom: 15px;">';
            echo '❌ <strong>Found ' . $mismatch_count . ' potentially undefined classes in ' . count($mismatches) . ' files</strong>';
            echo '</div>';
            
            echo '<div style="max-height: 400px; overflow-y: auto; background: white; padding: 10px; border: 1px solid #ddd;">';
            foreach ($mismatches as $file => $classes) {
                $short_file = str_replace($plugin_dir, '', $file);
                echo '<h4 style="margin: 15px 0 5px 0; color: #d63638;">📄 ' . esc_html($short_file) . '</h4>';
                echo '<ul style="margin: 5px 0 10px 20px;">';
                foreach ($classes as $class) {
                    echo '<li><code style="background: #f1f1f1; padding: 2px 4px;">' . esc_html($class) . '</code></li>';
                }
                echo '</ul>';
            }
            echo '</div>';
            
            echo '<p style="margin-top: 10px; font-size: 12px; color: #666;">';
            echo '<strong>Note:</strong> Some reported classes might be dynamically generated, come from WordPress core, ';
            echo 'or be intentionally undefined for testing purposes. Please review carefully.';
            echo '</p>';
        }
        
        echo '</div>';
    }

    /**
     * Get all files with specific extension from directory
     */
    private static function get_all_files($dir, $extension) {
        $files = array();
        if (!is_dir($dir)) {
            return array();
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) === $extension) {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    /**
     * Extract CSS class names from CSS files
     */
    private static function get_css_classes($path) {
        $css_files = self::get_all_files($path, 'css');
        $defined_classes = array();

        foreach ($css_files as $file) {
            $contents = file_get_contents($file);
            // Regex to find class selectors
            preg_match_all('/\.(-?[_a-zA-Z]+[_a-zA-Z0-9-]*)/', $contents, $matches);
            if (!empty($matches[1])) {
                $defined_classes = array_merge($defined_classes, $matches[1]);
            }
        }

        return array_unique($defined_classes);
    }

    /**
     * Extract HTML class names from PHP files
     */
    private static function get_html_classes($path) {
        $php_files = self::get_all_files($path, 'php');
        $used_classes = array();

        foreach ($php_files as $file) {
            $contents = file_get_contents($file);
            // Regex to find class attributes in HTML
            preg_match_all('/class=(["\'])(.*?)\1/', $contents, $matches);

            $file_classes = array();
            if (!empty($matches[2])) {
                foreach ($matches[2] as $class_string) {
                    // Split space-separated classes into an array
                    $file_classes = array_merge($file_classes, preg_split('/\s+/', $class_string));
                }
            }

            if (!empty($file_classes)) {
                $used_classes[$file] = array_filter(array_unique($file_classes));
            }
        }

        return $used_classes;
    }

    /**
     * Verify required assets are loaded
     */
    private static function verify_required_assets() {
        $required_styles = array(
            'tradepress-base-variables',
            'tradepress-ui-buttons',
            'tradepress-ui-forms',
            'tradepress-ui-modals',
            'tradepress-ui-library-page'
        );
        
        $missing_assets = array();
        foreach ($required_styles as $style_handle) {
            if (!wp_style_is($style_handle, 'enqueued') && !wp_style_is($style_handle, 'registered')) {
                $missing_assets[] = $style_handle;
            }
        }
        
        if (!empty($missing_assets) && defined('WP_DEBUG') && WP_DEBUG) {
            error_log("TradePress UI Library: Missing required assets - " . implode(', ', $missing_assets));
        }
    }

    /**
     * Render Color Palette Section
     */
    private static function render_color_palette_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/color-palette.php';
    }

    /**
     * Render Button Components Section
     */
    private static function render_button_components_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/button-components.php';
    }

    /**
     * Render Form Components Section
     */
    private static function render_form_components_section() {
        // Check if file exists before including
        $form_path = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/form-components.php';
        if (file_exists($form_path)) {
            require_once $form_path;
        } else {
            echo '<div class="ui-section"><h3>' . esc_html__('Form Components', 'tradepress') . '</h3>';
            echo '<p>' . esc_html__('Partial file not found. This section will be implemented next.', 'tradepress') . '</p></div>';
        }
    }

    /**
     * Render Controls and Actions Section
     */
    private static function render_controls_actions_section() {
        // This method is now deprecated as controls-actions.php is included directly in main-container.php
        // Keeping for backward compatibility but will be removed in future versions
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/controls-actions.php';
    }
    
    /**
     * Render Filters and Search Section
     */
    private static function render_filters_search_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/filters-search.php';
    }

    /**
     * Render Pagination Controls Section
     */
    private static function render_pagination_controls_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/pagination-controls.php';
    }

    /**
     * Render Progress Indicators Section
     */
    private static function render_progress_indicators_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/progress-indicators.php';
    }
    
    /**
     * Render Animation Showcase Section
     */
    private static function render_animation_showcase_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/animation-showcase.php';
    }
    
    /**
     * Render Working Notes Section
     */
    private static function render_working_notes_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/working-notes.php';
    }

    /**
     * Render Accordion Components Section
     */
    private static function render_accordion_components_section() {
        // Check if file exists before including
        $accordion_path = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/accordion-components.php';
        if (file_exists($accordion_path)) {
            require_once $accordion_path;
        } else {
            echo '<div class="ui-section"><h3>' . esc_html__('Accordion Components', 'tradepress') . '</h3>';
            echo '<p>' . esc_html__('Partial file not found. Please create accordion-components.php in the partials/ui-library directory.', 'tradepress') . '</p></div>';
        }
    }

    /**
     * Render Data Analysis Components Section
     */
    private static function render_data_analysis_components_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/data-analysis-components.php';
    }

    /**
     * Render Chart Visualization Section
     */
    private static function render_chart_visualization_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/chart-visualization.php';
    }

    /**
     * Render Modal Components Section
     */
    private static function render_modal_components_section() {
        // Check if file exists before including
        $modal_path = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/modal-components.php';
        if (file_exists($modal_path)) {
            require_once $modal_path;
        } else {
            echo '<div class="ui-section"><h3>' . esc_html__('Modal Components', 'tradepress') . '</h3>';
            echo '<p>' . esc_html__('Partial file not found. Please create modal-components.php in the partials/ui-library directory.', 'tradepress') . '</p></div>';
        }
    }

    /**
     * Render Status Indicators Section
     */
    private static function render_status_indicators_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/status-indicators.php';
    }

    /**
     * Render Tooltips Section
     */
    private static function render_tooltips_section() {
        // Check if file exists before including
        $tooltips_path = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/tooltips.php';
        if (file_exists($tooltips_path)) {
            require_once $tooltips_path;
        } else {
            echo '<div class="ui-section"><h3>' . esc_html__('Tooltips', 'tradepress') . '</h3>';
            echo '<p>' . esc_html__('Partial file not found. Please create tooltips.php in the partials/ui-library directory.', 'tradepress') . '</p></div>';
        }
    }

    /**
     * Render Notice Components Section
     */
    private static function render_notice_components_section() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/notice-components.php';
    }
}