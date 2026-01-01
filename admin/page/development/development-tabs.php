<?php
/**
 * TradePress Development Views
 *
 * @package TradePress/Admin/Views
 * @version 1.0.6
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include GitHub helper functions
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/github/github-helpers.php';

/**
 * TradePress_Admin_Development_Page Class
 */
class TradePress_Admin_Development_Page {
    /**
     * Output the development view
     */
    public static function output() {
        self::enqueue_assets();
        self::view_wrapper_start();
        self::tabs();
        self::active_tab_content();
        self::view_wrapper_end();
    }
    
    /**
     * Get the tabs array (single source of truth)
     */
    public static function get_tabs() {
        return array(
            'current_task' => __('Current Task', 'tradepress'),
            'architecture' => __('Architecture Map', 'tradepress'),
            'algorithm_debugger' => __('Algorithm Debugger', 'tradepress'),
            'diagrams' => __('Diagrams', 'tradepress'),
            'tasks' => __('Tasks', 'tradepress'),
            'duplicate_checker' => __('Duplicate Checker', 'tradepress'),
            'github' => __('GitHub', 'tradepress'),
            'changes' => __('Change Log', 'tradepress'),
            'discussion' => __('Discussion', 'tradepress'),
            'notes' => __('Notes', 'tradepress'),
            'feature_status' => __('Feature Status', 'tradepress'),
            'ai' => __('AI', 'tradepress'),
            'pointers' => __('Pointers', 'tradepress'),
            'layouts' => __('Layouts', 'tradepress'),

            'ui_library' => __('Light UI Library', 'tradepress'),
            'dark_ui_library' => __('Dark UI Library', 'tradepress'),
            'jquery_ui' => __('jQuery UI', 'tradepress'),
            'assets' => __('Assets', 'tradepress'),
            'snippets' => __('Developer Snippets', 'tradepress'),
            'listener_testing' => __('Listener Testing', 'tradepress'),
        );
    }

    /**
     * Enqueue required assets for the development tabs
     * 
     * @todo use the asset system for better performance and delete this method when ready
     */
    private static function enqueue_assets() {
        wp_enqueue_script(
            'tradepress-development-tabs', 
            TRADEPRESS_PLUGIN_URL . 'js/tradepress-development-tabs.js', 
            array('jquery'), 
            TRADEPRESS_VERSION, 
            true
        );
        
        // Enqueue Mermaid.js for diagrams tab (local copy)
        wp_enqueue_script(
            'mermaid-js',
            TRADEPRESS_PLUGIN_URL . 'assets/js/libs/mermaid.min.js',
            array(),
            '10.6.1',
            true
        );
        
        // Add development notes to script
        $development_notes = self::get_development_notes();
        if (!is_wp_error($development_notes)) {
            wp_add_inline_script(
                'tradepress-development-tabs',
                'window.developmentNotes = ' . wp_json_encode($development_notes) . ';',
                'before'
            );
        }
        
        // Add AI notes to script
        $ai_notes = self::get_ai_notes();
        if (!is_wp_error($ai_notes)) {
            wp_add_inline_script(
                'tradepress-development-tabs',
                'window.aiNotes = ' . wp_json_encode($ai_notes) . ';',
                'before'
            );
        }
        
        // Add Gemini notes to script
        $gemini_notes = self::get_gemini_notes();
        if (!is_wp_error($gemini_notes)) {
            wp_add_inline_script(
                'tradepress-development-tabs',
                'window.geminiNotes = ' . wp_json_encode($gemini_notes) . ';',
                'before'
            );
        }
    }
    
    /**
     * Development view wrapper start
     */
    private static function view_wrapper_start() {
        $current_tab = isset($_GET['tab']) ? sanitize_title($_GET['tab']) : 'current_task';
        $tabs = self::get_tabs();
        $tab_title = isset($tabs[$current_tab]) ? $tabs[$current_tab] : '';
        
        ?>
        <div class="wrap tradepress-development-wrap">
            <h1>
                <?php esc_html_e('TradePress Development', 'tradepress'); ?>
                <?php if (!empty($tab_title)) : ?>
                    <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span>
                    <?php echo esc_html($tab_title); ?>
                <?php endif; ?>
            </h1>
        <?php
    }
    
    /**
     * Development view wrapper end
     */
    private static function view_wrapper_end() {
        ?>
        </div><!-- .wrap -->
        <?php
    }
    
    /**
     * Display the tabs
     */
    private static function tabs() {
        $current_tab = isset($_GET['tab']) ? sanitize_title($_GET['tab']) : 'current_task';
        $tabs = self::get_tabs();
        ?>
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ($tabs as $tab_id => $tab_title) {
                $active_class = ($current_tab === $tab_id) ? 'nav-tab-active' : '';
                printf(
                    '<a href="%s" class="nav-tab %s">%s</a>',
                    esc_url(add_query_arg('tab', $tab_id, remove_query_arg('channel'))),
                    esc_attr($active_class),
                    esc_html($tab_title)
                );
            }
            ?>
        </h2>
        <?php
    }
    
    /**
     * Display the active tab content
     */
    private static function active_tab_content() {
        $current_tab = isset($_GET['tab']) ? sanitize_title($_GET['tab']) : 'current_task';
        
        switch ($current_tab) {
            case 'architecture':
                require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/architecture-mapper.php';
                self::output_architecture_tab();
                break;
            case 'algorithm_debugger':
                if (!class_exists('TradePress_Admin_Algorithm_Debugger_Page')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/algorithm-debugger/algorithm-debugger.php';
                }
                $debugger = new TradePress_Admin_Algorithm_Debugger_Page();
                $debugger->output();
                break;
            case 'diagrams':
                if (!class_exists('TradePress_Admin_Development_Diagrams')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/diagrams.php';
                }
                TradePress_Admin_Development_Diagrams::output();
                break;
            case 'duplicate_checker':
                if (!class_exists('TradePress_Admin_Development_Duplicate_Checker')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/duplicate-checker.php';
                }
                TradePress_Admin_Development_Duplicate_Checker::output();
                break;
            case 'github':
                if (!class_exists('TradePress_Admin_Development_GitHub')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/github.php';
                }
                TradePress_Admin_Development_GitHub::output();
                break;
            case 'current_task':
                if (!class_exists('TradePress_Admin_Development_Current_Task')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/current-task.php';
                }
                TradePress_Admin_Development_Current_Task::output();
                break;
            case 'changes':
                if (!class_exists('TradePress_Admin_Development_Changes')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/changes.php';
                }
                TradePress_Admin_Development_Changes::output();
                break;
            case 'discussion':
                if (!class_exists('TradePress_Admin_Development_Discussion')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/discussion.php';
                }
                TradePress_Admin_Development_Discussion::output();
                break;
            case 'notes':
                if (!class_exists('TradePress_Admin_Development_Notes')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/notes.php';
                }
                TradePress_Admin_Development_Notes::output();
                break;
            case 'feature_status':
                if (!class_exists('TradePress_Admin_Development_Feature_Status')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/feature-status.php';
                }
                TradePress_Admin_Development_Feature_Status::output();
                break;
            case 'tasks':
                if (!class_exists('TradePress_Admin_Development_Tasks')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/tasks.php';
                }
                TradePress_Admin_Development_Tasks::output();
                break;
            case 'ai':
                if (!class_exists('TradePress_Admin_Development_AI')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/ai.php';
                }
                TradePress_Admin_Development_AI::output();
                break;
            case 'pointers':
                if (!class_exists('TradePress_Admin_Development_Pointers')) {
                    $file_path = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/pointers.php';
                    if (file_exists($file_path)) {
                        require_once $file_path;
                    } else {
                        echo '<div class="notice notice-error"><p>Pointers file not found: ' . esc_html($file_path) . '</p></div>';
                        break;
                    }
                }
                if (class_exists('TradePress_Admin_Development_Pointers')) {
                    TradePress_Admin_Development_Pointers::output();
                } else {
                    echo '<div class="notice notice-error"><p>TradePress_Admin_Development_Pointers class not found</p></div>';
                }
                break;
            case 'layouts':
                if (!class_exists('TradePress_Admin_Development_Layouts')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/layouts.php';
                }
                TradePress_Admin_Development_Layouts::output();
                break;

            case 'ui_library':
                if (!class_exists('TradePress_Admin_Development_UI_Library')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/ui-library.php';
                }
                TradePress_Admin_Development_UI_Library::output();
                break;
            case 'dark_ui_library':
                if (!class_exists('TradePress_Admin_Development_Dark_UI_Library')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/dark-ui-library.php';
                }
                TradePress_Admin_Development_Dark_UI_Library::output();
                break;
            case 'assets':
                if (!class_exists('TradePress_Admin_Development_Assets')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/assets-tracker.php';
                }
                TradePress_Admin_Development_Assets::output();
                break;
            case 'listener_testing':
                if (!class_exists('TradePress_Admin_Development_Listener_Testing')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/listener-testing.php';
                }
                TradePress_Admin_Page_Listener_Testing::output();
                break;
            case 'jquery_ui':
                if (!class_exists('TradePress_Admin_Development_jQuery_UI')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/jquery-ui.php';
                }
                TradePress_Admin_Development_jQuery_UI::output();
                break;
            case 'snippets':
                if (!class_exists('TradePress_Admin_Development_Snippets')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/snippets.php';
                }
                TradePress_Admin_Development_Snippets::output();
                break;
            default:
                if (!class_exists('TradePress_Admin_Development_Tasks')) {
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/tasks.php';
                }
                TradePress_Admin_Development_Tasks::output();
                break;
        }
    }
    
    /**
     * Get development notes from DEVELOPMENT.md
     */
    private static function get_development_notes() {
        $development_path = TRADEPRESS_PLUGIN_DIR . 'DEVELOPMENT.md';
        if (!file_exists($development_path)) {
            return new WP_Error('missing_file', __('DEVELOPMENT.md file not found.', 'tradepress'));
        }
        return file_get_contents($development_path);
    }
    
    /**
     * Get AI notes from AI.md
     */
    private static function get_ai_notes() {
        $ai_path = TRADEPRESS_PLUGIN_DIR . 'AI.md';
        if (!file_exists($ai_path)) {
            return new WP_Error('missing_file', __('AI.md file not found.', 'tradepress'));
        }
        return file_get_contents($ai_path);
    }
    
    /**
     * Get Gemini notes from GEMINI.md
     */
    private static function get_gemini_notes() {
        $gemini_path = TRADEPRESS_PLUGIN_DIR . 'GEMINI.md';
        if (!file_exists($gemini_path)) {
            return new WP_Error('missing_file', __('GEMINI.md file not found.', 'tradepress'));
        }
        return file_get_contents($gemini_path);
    }
    
    /**
     * Output the architecture map tab
     */
    private static function output_architecture_tab() {
        ?>
        <style><?php echo TradePress_Architecture_Mapper::get_tree_styles(); ?></style>
        
        <div class="tradepress-architecture-container">
            <div class="architecture-header">
                <h2><?php _e('TradePress Architecture Map', 'tradepress'); ?></h2>
                <p><?php _e('Visual guide to systems, classes, files, and relationships for development and AI assistance.', 'tradepress'); ?></p>
            </div>
            
            <div class="architecture-controls">
                <button id="expand-all" class="button"><?php _e('Expand All', 'tradepress'); ?></button>
                <button id="collapse-all" class="button"><?php _e('Collapse All', 'tradepress'); ?></button>
            </div>
            
            <?php echo TradePress_Architecture_Mapper::render_tree(); ?>
        </div>
        
        <script>
        <?php echo TradePress_Architecture_Mapper::get_tree_scripts(); ?>
        
        // Additional controls
        document.getElementById('expand-all').addEventListener('click', function() {
            document.querySelectorAll('.tree-children').forEach(el => {
                el.style.display = 'block';
            });
            document.querySelectorAll('.tree-toggle').forEach(el => {
                el.textContent = '▼';
            });
        });
        
        document.getElementById('collapse-all').addEventListener('click', function() {
            document.querySelectorAll('.tree-children').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('.tree-toggle').forEach(el => {
                el.textContent = '▶';
            });
        });
        </script>
        <?php
    }
}
