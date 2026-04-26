<?php
/**
 * TradePress Dark UI Library
 *
 * @package TradePress/Admin/Views
 * @version 1.0.8
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin_Development_Dark_UI_Library Class
 */
class TradePress_Admin_Development_Dark_UI_Library {
    
    /**
     * Output the Dark UI Library view
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
        
        // Add dark theme wrapper with development plan
        echo '<div class="tradepress-dark-theme">';
        
        // Display development plan
        self::display_development_plan();
        
        // Include main UI Library container partial
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/main-container.php';
        
        echo '</div>';
        
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
     * Display development plan for dark theme implementation
     */
    private static function display_development_plan() {
        ?>
        <div style="background: #2d2d2d; color: #e0e0e0; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #4a9eff;">
            <h2 style="color: #4a9eff; margin-top: 0;">🌙 Dark UI Library Development Plan</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <h3 style="color: #46d369;">✅ Phase 1: Foundation (Complete)</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>✅ Created /assets/css/dark/ folder</li>
                        <li>✅ Dark theme variables.css</li>
                        <li>✅ Dark admin.css (containers/backgrounds)</li>
                        <li>✅ Dark tabs.css (navigation)</li>
                        <li>✅ Dark theme wrapper (.tradepress-dark-theme)</li>
                    </ul>
                </div>
                
                <div>
                    <h3 style="color: #f0c33c;">🔄 Phase 2: Core Components (In Progress)</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>⏳ buttons.css - Button styles and states</li>
                        <li>⏳ forms.css - Form controls and inputs</li>
                        <li>⏳ cards.css - Card containers</li>
                        <li>⏳ notices.css - Alert and notice styles</li>
                        <li>⏳ modals.css - Modal dialogs</li>
                    </ul>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <h3 style="color: #8a8a8a;">📋 Phase 3: UI Components (Planned)</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>⏸️ tables.css - Data tables</li>
                        <li>⏸️ progress.css - Progress indicators</li>
                        <li>⏸️ status-indicators.css - Status badges</li>
                        <li>⏸️ tooltips.css - Tooltip styles</li>
                        <li>⏸️ accordion.css - Collapsible sections</li>
                    </ul>
                </div>
                
                <div>
                    <h3 style="color: #8a8a8a;">🎨 Phase 4: Advanced (Future)</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>⏸️ charts.css - Chart visualizations</li>
                        <li>⏸️ animations.css - Transitions/effects</li>
                        <li>⏸️ data-analysis.css - Analysis components</li>
                        <li>⏸️ ui-library.css - Library-specific styles</li>
                        <li>⏸️ Asset registration system</li>
                    </ul>
                </div>
            </div>
            
            <div style="background: #3a3a3a; padding: 15px; border-radius: 4px; margin-top: 15px;">
                <h4 style="color: #4a9eff; margin-top: 0;">🚀 Implementation Strategy</h4>
                <p style="margin: 5px 0;"><strong>Priority Order:</strong> Foundation → Core Components → UI Components → Advanced</p>
                <p style="margin: 5px 0;"><strong>Approach:</strong> Copy original CSS → Convert to dark theme → Register for loading</p>
                <p style="margin: 5px 0;"><strong>Testing:</strong> Each component tested in isolation before moving to next</p>
                <p style="margin: 5px 0;"><strong>Scope:</strong> .tradepress-dark-theme wrapper ensures no conflicts with light theme</p>
            </div>
            
            <div style="background: #1a2d3d; padding: 15px; border-radius: 4px; margin-top: 15px; border: 1px solid #2d4a5a;">
                <h4 style="color: #4a9eff; margin-top: 0;">📝 Current Status</h4>
                <p style="margin: 5px 0;">Most UI components are unstyled because their CSS files are not registered for the dark theme.</p>
                <p style="margin: 5px 0;">Each file needs to be: <code style="background: #2d2d2d; padding: 2px 4px; border-radius: 2px;">copied → converted → registered</code></p>
                <p style="margin: 5px 0;"><strong>Next:</strong> Implement Phase 2 core components (buttons, forms, cards, notices, modals)</p>
            </div>
        </div>
        <?php
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
}