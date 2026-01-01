<?php
/**
 * TradePress Development Assets Tab (Asset Status Management)
 *
 * Displays asset status and validation using the asset management system.
 * Monitor the status of all plugin CSS and JavaScript assets.
 *
 * @package TradePress\Admin\development
 * @version 1.2.0
 * @since 1.0.0
 * @created 2024-12-19
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TradePress_Admin_Development_Assets
 *
 * Handles the rendering and functionality of the Assets tab
 * in the Development section.
 */
class TradePress_Admin_Development_Assets {

    /**
     * Output the Assets view
     */
    public static function output() {
        self::enqueue_assets();
        $asset_manager = self::get_asset_manager();

        if (!$asset_manager) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Asset manager could not be initialized. Please check if the manage-assets.php file exists.', 'tradepress') . '</p></div>';
            return;
        }

        $all_assets = $asset_manager->get_all_assets();
        $css_assets = $all_assets['css'] ?? array();
        $js_assets = $all_assets['js'] ?? array();
        $overall_status = self::check_overall_status($css_assets, $js_assets);
        ?>
        <div class="tab-content" id="assets">
            <div class="tradepress-assets-container">
                <div class="assets-header">
                    <div class="overall-status <?php echo esc_attr($overall_status['class']); ?>">
                        <span class="dashicons <?php echo esc_attr($overall_status['icon']); ?>"></span>
                        <strong><?php echo esc_html($overall_status['message']); ?></strong>
                        <?php if (!empty($overall_status['details'])): ?>
                            <span class="status-details"><?php echo esc_html($overall_status['details']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="assets-tabs" role="tablist">
                    <a href="#" class="assets-tab active" data-tab="css-assets" role="tab" aria-selected="true"><?php esc_html_e('CSS Assets', 'tradepress'); ?></a>
                    <a href="#" class="assets-tab" data-tab="js-assets" role="tab" aria-selected="false"><?php esc_html_e('JavaScript Assets', 'tradepress'); ?></a>
                    <a href="#" class="assets-tab" data-tab="php-files" role="tab" aria-selected="false"><?php esc_html_e('PHP Files', 'tradepress'); ?></a>
                    <a href="#" class="assets-tab" data-tab="php-classes" role="tab" aria-selected="false"><?php esc_html_e('PHP Classes', 'tradepress'); ?></a>
                    <a href="#" class="assets-tab" data-tab="systems" role="tab" aria-selected="false"><?php esc_html_e('Systems', 'tradepress'); ?></a>
                    <a href="#" class="assets-tab" data-tab="summary" role="tab" aria-selected="false"><?php esc_html_e('Summary', 'tradepress'); ?></a>
                </div>
                <div class="assets-content">
                    <!-- CSS Assets Tab -->
                    <div id="css-assets" class="assets-tab-content active" role="tabpanel">
                        <div class="assets-issues-container">
                            <div class="issues-header">
                                <h4><?php esc_html_e('CSS Asset Analysis', 'tradepress'); ?></h4>
                                <p class="description"><?php esc_html_e('All CSS files managed by the TradePress asset system.', 'tradepress'); ?></p>
                                
                                <?php $css_stats = self::get_asset_stats($css_assets); ?>
                                <div class="issues-summary">
                                    <div class="summary-stats">
                                        <span class="stat-item">
                                            <strong><?php echo esc_html($css_stats['total']); ?></strong>
                                            <?php esc_html_e('Total CSS Assets', 'tradepress'); ?>
                                        </span>
                                        <span class="stat-item correct">
                                            <strong><?php echo esc_html($css_stats['found']); ?></strong>
                                            <?php esc_html_e('Available', 'tradepress'); ?>
                                        </span>
                                        <span class="stat-item issue">
                                            <strong><?php echo esc_html($css_stats['missing']); ?></strong>
                                            <?php esc_html_e('Missing', 'tradepress'); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="issues-actions">
                                    <button type="button" class="button button-secondary" id="refresh-css-scan">
                                        <span class="dashicons dashicons-update"></span>
                                        <?php esc_html_e('Refresh Scan', 'tradepress'); ?>
                                    </button>
                                    <button type="button" class="button button-secondary" id="toggle-css-issues">
                                        <span class="dashicons dashicons-filter"></span>
                                        <?php esc_html_e('Show Issues Only', 'tradepress'); ?>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="issues-filters">
                                <label>
                                    <input type="checkbox" id="filter-css-available" checked> 
                                    <?php esc_html_e('Available Assets', 'tradepress'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="filter-css-missing" checked>
                                    <?php esc_html_e('Missing Assets', 'tradepress'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="filter-css-issues-only">
                                    <?php esc_html_e('Issues Only', 'tradepress'); ?>
                                </label>
                            </div>
                            
                            <div class="issues-table-container">
                                <?php self::render_asset_table($css_assets, 'css'); ?>
                            </div>
                        </div>
                    </div>
                    <!-- JavaScript Assets Tab -->
                    <div id="js-assets" class="assets-tab-content" role="tabpanel">
                        <div class="assets-issues-container">
                            <div class="issues-header">
                                <h4><?php esc_html_e('JavaScript Asset Analysis', 'tradepress'); ?></h4>
                                <p class="description"><?php esc_html_e('All JavaScript files managed by the TradePress asset system.', 'tradepress'); ?></p>
                                
                                <?php $js_stats = self::get_asset_stats($js_assets); ?>
                                <div class="issues-summary">
                                    <div class="summary-stats">
                                        <span class="stat-item">
                                            <strong><?php echo esc_html($js_stats['total']); ?></strong>
                                            <?php esc_html_e('Total JS Assets', 'tradepress'); ?>
                                        </span>
                                        <span class="stat-item correct">
                                            <strong><?php echo esc_html($js_stats['found']); ?></strong>
                                            <?php esc_html_e('Available', 'tradepress'); ?>
                                        </span>
                                        <span class="stat-item issue">
                                            <strong><?php echo esc_html($js_stats['missing']); ?></strong>
                                            <?php esc_html_e('Missing', 'tradepress'); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="issues-actions">
                                    <button type="button" class="button button-secondary" id="refresh-js-scan">
                                        <span class="dashicons dashicons-update"></span>
                                        <?php esc_html_e('Refresh Scan', 'tradepress'); ?>
                                    </button>
                                    <button type="button" class="button button-secondary" id="toggle-js-issues">
                                        <span class="dashicons dashicons-filter"></span>
                                        <?php esc_html_e('Show Issues Only', 'tradepress'); ?>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="issues-filters">
                                <label>
                                    <input type="checkbox" id="filter-js-available" checked> 
                                    <?php esc_html_e('Available Assets', 'tradepress'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="filter-js-missing" checked>
                                    <?php esc_html_e('Missing Assets', 'tradepress'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="filter-js-issues-only">
                                    <?php esc_html_e('Issues Only', 'tradepress'); ?>
                                </label>
                            </div>
                            
                            <div class="issues-table-container">
                                <?php self::render_asset_table($js_assets, 'js'); ?>
                            </div>
                        </div>
                    </div>
                    <!-- PHP Files Tab -->
                    <div id="php-files" class="assets-tab-content" role="tabpanel">
                        <h3><?php esc_html_e('PHP Files', 'tradepress'); ?></h3>
                        <?php self::render_php_files(); ?>
                    </div>
                    <!-- PHP Classes Tab -->
                    <div id="php-classes" class="assets-tab-content" role="tabpanel">
                        <h3><?php esc_html_e('PHP Classes', 'tradepress'); ?></h3>
                        <?php self::render_php_classes(); ?>
                    </div>
                    <!-- Systems Tab -->
                    <div id="systems" class="assets-tab-content" role="tabpanel">
                        <h3><?php esc_html_e('Systems', 'tradepress'); ?></h3>
                        <?php self::render_systems(); ?>
                    </div>
                    <!-- Summary Tab -->
                    <div id="summary" class="assets-tab-content" role="tabpanel">
                        <h3><?php esc_html_e('Assets Summary', 'tradepress'); ?></h3>
                        <?php self::render_summary($css_assets, $js_assets); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue required assets
     */
    private static function enqueue_assets() {
        wp_enqueue_style(
            'tradepress-development-assets',
            TRADEPRESS_PLUGIN_URL . 'assets/css/development-assets.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        wp_enqueue_script(
            'tradepress-development-assets',
            TRADEPRESS_PLUGIN_URL . 'assets/js/development-assets.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
    }
    
    /**
     * Get or initialize asset manager
     *
     * @return TradePress_Asset_Manager|false Asset manager instance or false on failure
     */
    private static function get_asset_manager() {
        global $tradepress_assets;

        if ($tradepress_assets && is_object($tradepress_assets)) {
            return $tradepress_assets;
        }

        $manage_assets_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/manage-assets.php';

        if (!file_exists($manage_assets_path)) {
            error_log('TradePress: manage-assets.php not found at: ' . $manage_assets_path);
            return false;
        }

        require_once $manage_assets_path;

        if (class_exists('TradePress_Asset_Manager') && isset($tradepress_assets)) {
            return $tradepress_assets;
        }

        if (class_exists('TradePress_Asset_Manager')) {
            $tradepress_assets = new TradePress_Asset_Manager();
            return $tradepress_assets;
        }

        return false;
    }

    /**
     * Render asset table
     */
    private static function render_asset_table($assets, $type) {
        if (empty($assets) || !is_array($assets)) {
            echo '<p>' . esc_html__('No assets found for this type.', 'tradepress') . '</p>';
            return;
        }
        ?>
        <table class="assets-issues-table" id="assets-<?php echo esc_attr($type); ?>-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Asset Name', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Category', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Purpose', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Status', 'tradepress'); ?></th>
                    <th><?php esc_html_e('File Path', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Pages', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Dependencies', 'tradepress'); ?></th>
                    <th><?php esc_html_e('Actions', 'tradepress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($assets as $category => $category_assets):
                    if (empty($category_assets) || !is_array($category_assets)) continue;
                    
                    foreach ($category_assets as $name => $asset):
                        if (!is_array($asset)) continue;
                        $status = self::check_asset_status($asset, $type);
                        $asset_path = $asset['path'] ?? '';
                        $full_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . ltrim($asset_path, '/');
                        $is_outside_assets = strpos($asset_path, '../') === 0 || strpos($asset_path, '/') === false;
                ?>
                        <tr class="asset-row file-row" 
                            data-type="<?php echo esc_attr($type); ?>" 
                            data-status="<?php echo esc_attr($status['type']); ?>">
                            <td>
                                <code class="file-name"><?php echo esc_html($name); ?></code>
                            </td>
                            <td>
                                <span class="file-type file-type-<?php echo esc_attr($type); ?>">
                                    <?php echo esc_html(ucwords(str_replace('_', ' ', $category))); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($asset['purpose'] ?? __('No description', 'tradepress')); ?></td>
                            <td>
                                <span class="status-indicator status-<?php echo esc_attr($status['type']); ?>">
                                    <span class="dashicons <?php echo esc_attr($status['icon']); ?>"></span>
                                    <?php echo esc_html($status['message']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="file-path" title="<?php echo esc_attr($full_path); ?>">
                                    <?php echo esc_html($asset_path); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $pages = $asset['pages'] ?? array();
                                if (is_array($pages)) {
                                    echo esc_html(implode(', ', array_slice($pages, 0, 3)));
                                    echo count($pages) > 3 ? '...' : '';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $deps = $asset['dependencies'] ?? array();
                                if (is_array($deps)) {
                                    echo esc_html(implode(', ', $deps));
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($status['type'] === 'error' || $is_outside_assets): ?>
                                    <button type="button" class="button button-small move-to-assets" 
                                            data-file="<?php echo esc_attr($asset_path); ?>"
                                            data-name="<?php echo esc_attr($name); ?>">
                                        <?php esc_html_e('Move to Assets', 'tradepress'); ?>
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="button button-small button-link-delete delete-file" 
                                        data-file="<?php echo esc_attr($asset_path); ?>"
                                        data-name="<?php echo esc_attr($name); ?>">
                                    <?php esc_html_e('Delete', 'tradepress'); ?>
                                </button>
                                <a href="vscode://file/<?php echo rawurlencode($full_path); ?>" class="button button-small" target="_blank">
                                    <?php esc_html_e('Open in VS Code', 'tradepress'); ?>
                                </a>
                            </td>
                        </tr>
                <?php 
                    endforeach;
                endforeach; 
                ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render summary
     */
    private static function render_summary($css_assets, $js_assets) {
        $css_stats = self::get_asset_stats($css_assets);
        $js_stats = self::get_asset_stats($js_assets);
        $total_missing = $css_stats['missing'] + $js_stats['missing'];
        $total_assets = $css_stats['total'] + $js_stats['total'];
        $health_percentage = $total_assets > 0 ? round((($total_assets - $total_missing) / $total_assets) * 100) : 100;
        ?>
        <div class="summary-grid">
            <div class="summary-card">
                <h4><?php esc_html_e('CSS Assets', 'tradepress'); ?></h4>
                <p><strong><?php echo esc_html($css_stats['total']); ?></strong> <?php esc_html_e('total assets', 'tradepress'); ?></p>
                <p><span class="status-success"><span class="dashicons dashicons-yes"></span> <?php echo esc_html($css_stats['found']); ?></span> <?php esc_html_e('files found', 'tradepress'); ?></p>
                <p><span class="status-error"><span class="dashicons dashicons-no"></span> <?php echo esc_html($css_stats['missing']); ?></span> <?php esc_html_e('files missing', 'tradepress'); ?></p>
                <p><strong><?php echo esc_html(count($css_assets)); ?></strong> <?php esc_html_e('categories', 'tradepress'); ?></p>
            </div>
            <div class="summary-card">
                <h4><?php esc_html_e('JavaScript Assets', 'tradepress'); ?></h4>
                <p><strong><?php echo esc_html($js_stats['total']); ?></strong> <?php esc_html_e('total assets', 'tradepress'); ?></p>
                <p><span class="status-success"><span class="dashicons dashicons-yes"></span> <?php echo esc_html($js_stats['found']); ?></span> <?php esc_html_e('files found', 'tradepress'); ?></p>
                <p><span class="status-error"><span class="dashicons dashicons-no"></span> <?php echo esc_html($js_stats['missing']); ?></span> <?php esc_html_e('files missing', 'tradepress'); ?></p>
                <p><strong><?php echo esc_html(count($js_assets)); ?></strong> <?php esc_html_e('categories', 'tradepress'); ?></p>
            </div>
            <div class="summary-card">
                <h4><?php esc_html_e('Overall Health', 'tradepress'); ?></h4>
                <p><strong><?php echo esc_html($health_percentage); ?>%</strong> <?php esc_html_e('assets available', 'tradepress'); ?></p>
                <p><strong><?php echo esc_html($total_assets); ?></strong> <?php esc_html_e('total managed assets', 'tradepress'); ?></p>
                <?php if ($total_missing > 0): ?>
                    <p class="status-error">
                        <span class="dashicons dashicons-warning"></span>
                        <?php echo esc_html(sprintf(__('%d assets need attention', 'tradepress'), $total_missing)); ?>
                    </p>
                <?php else: ?>
                    <p class="status-success">
                        <span class="dashicons dashicons-yes"></span>
                        <?php esc_html_e('All assets available', 'tradepress'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Check asset status
     */
    private static function check_asset_status($asset, $type) {
        if (!is_array($asset) || empty($asset['path'])) {
            return array(
                'type' => 'error',
                'icon' => 'dashicons-no',
                'message' => __('Invalid asset data', 'tradepress')
            );
        }
        
        $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . ltrim($asset['path'], '/');
        
        if (file_exists($asset_path)) {
            return array(
                'type' => 'success',
                'icon' => 'dashicons-yes',
                'message' => __('Available', 'tradepress')
            );
        } else {
            return array(
                'type' => 'error',
                'icon' => 'dashicons-no',
                'message' => __('Missing', 'tradepress')
            );
        }
    }

    /**
     * Get asset statistics
     */
    private static function get_asset_stats($assets) {
        $total = 0;
        $found = 0;
        $missing = 0;
        
        if (!is_array($assets)) {
            return array('total' => 0, 'found' => 0, 'missing' => 0);
        }
        
        foreach ($assets as $category_assets) {
            if (!is_array($category_assets)) continue;
            
            foreach ($category_assets as $asset) {
                if (!is_array($asset) || empty($asset['path'])) continue;
                
                $total++;
                $asset_path = TRADEPRESS_PLUGIN_DIR_PATH . 'assets/' . ltrim($asset['path'], '/');
                
                if (file_exists($asset_path)) {
                    $found++;
                } else {
                    $missing++;
                }
            }
        }
        
        return array('total' => $total, 'found' => $found, 'missing' => $missing);
    }

    /**
     * Render PHP Files tab
     */
    private static function render_php_files() {
        $php_files = self::scan_php_files();
        ?>
        <div class="php-files-container">
            <p class="description"><?php esc_html_e('All PHP files in the plugin with documentation extracted from docblocks.', 'tradepress'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('File', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Type', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Description', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Classes', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Functions', 'tradepress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($php_files as $file): ?>
                    <tr>
                        <td><code><?php echo esc_html($file['relative_path']); ?></code></td>
                        <td><?php echo esc_html($file['type']); ?></td>
                        <td><?php echo esc_html($file['description']); ?></td>
                        <td><?php echo esc_html(implode(', ', $file['classes'])); ?></td>
                        <td><?php echo esc_html(count($file['functions'])); ?> functions</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render PHP Classes tab
     */
    private static function render_php_classes() {
        $classes = self::scan_all_classes();
        ?>
        <div class="php-classes-container">
            <p class="description"><?php esc_html_e('All PHP classes in the plugin with their purposes and methods.', 'tradepress'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Class Name', 'tradepress'); ?></th>
                        <th><?php esc_html_e('File', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Description', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Methods', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Type', 'tradepress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><code><?php echo esc_html($class['name']); ?></code></td>
                        <td><?php echo esc_html($class['file']); ?></td>
                        <td><?php echo esc_html($class['description']); ?></td>
                        <td><?php echo esc_html($class['method_count']); ?> methods</td>
                        <td><?php echo esc_html($class['type']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render Systems tab
     */
    private static function render_systems() {
        $systems = self::get_plugin_systems();
        ?>
        <div class="systems-container">
            <p class="description"><?php esc_html_e('Plugin systems and their components for understanding architecture and progress.', 'tradepress'); ?></p>
            
            <?php foreach ($systems as $system): ?>
            <div class="system-section">
                <h4><?php echo esc_html($system['name']); ?></h4>
                <p><?php echo esc_html($system['description']); ?></p>
                
                <div class="system-components">
                    <div class="component-group">
                        <h5><?php esc_html_e('Files', 'tradepress'); ?></h5>
                        <ul>
                            <?php foreach ($system['files'] as $file): ?>
                            <li><code><?php echo esc_html($file); ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="component-group">
                        <h5><?php esc_html_e('Assets', 'tradepress'); ?></h5>
                        <ul>
                            <?php foreach ($system['assets'] as $asset): ?>
                            <li><?php echo esc_html($asset); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="progress-indicator">
                        <span class="status-<?php echo esc_attr($system['status']); ?>">
                            <?php echo esc_html(ucfirst($system['status'])); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    /**
     * Scan PHP files in plugin
     */
    private static function scan_php_files() {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(TRADEPRESS_PLUGIN_DIR_PATH)
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $relative_path = str_replace(TRADEPRESS_PLUGIN_DIR_PATH, '', $file->getPathname());
                $content = file_get_contents($file->getPathname());
                
                $files[] = [
                    'relative_path' => ltrim($relative_path, '/\\'),
                    'type' => self::determine_file_type($relative_path),
                    'description' => self::extract_file_description($content),
                    'classes' => self::extract_classes($content),
                    'functions' => self::extract_functions($content)
                ];
            }
        }
        
        return $files;
    }
    
    /**
     * Scan all classes in plugin
     */
    private static function scan_all_classes() {
        $classes = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(TRADEPRESS_PLUGIN_DIR_PATH)
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $relative_path = str_replace(TRADEPRESS_PLUGIN_DIR_PATH, '', $file->getPathname());
                $relative_path = ltrim($relative_path, '/\\');
                
                // Find all classes in file
                preg_match_all('/class\s+([A-Za-z_][A-Za-z0-9_]*)/', $content, $matches);
                
                foreach ($matches[1] as $class_name) {
                    $classes[] = [
                        'name' => $class_name,
                        'file' => $relative_path,
                        'description' => self::extract_class_description($content, $class_name),
                        'method_count' => self::count_class_methods($content, $class_name),
                        'type' => self::determine_class_type($relative_path)
                    ];
                }
            }
        }
        
        // Sort by class name
        usort($classes, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $classes;
    }
    
    /**
     * Get plugin systems array
     */
    private static function get_plugin_systems() {
        return [
            [
                'name' => 'Background Processing',
                'description' => 'Handles API calls and scoring in background processes',
                'files' => ['includes/data-import-process.php', 'includes/scoring-process.php'],
                'assets' => ['admin-automation.js', 'admin-automation.css'],
                'status' => 'complete'
            ],
            [
                'name' => 'Asset Management',
                'description' => 'Manages and tracks CSS/JS assets across the plugin',
                'files' => ['assets/manage-assets.php', 'admin/page/development/view/assets-tracker.php'],
                'assets' => ['development-assets.css'],
                'status' => 'active'
            ],
            [
                'name' => 'Development Tools',
                'description' => 'Tools for plugin development and debugging',
                'files' => ['admin/page/development/development-tabs.php', 'admin/page/development/view/duplicate-checker.php'],
                'assets' => ['duplicate-checker.css', 'duplicate-checker.js'],
                'status' => 'active'
            ]
        ];
    }
    
    /**
     * Helper methods for file analysis
     */
    private static function determine_file_type($path) {
        if (strpos($path, 'admin') !== false) return 'Admin';
        if (strpos($path, 'includes') !== false) return 'Core';
        if (strpos($path, 'assets') !== false) return 'Assets';
        return 'Other';
    }
    
    private static function extract_file_description($content) {
        if (preg_match('/\*\s*(.+?)\s*\*\//s', $content, $matches)) {
            $lines = explode('\n', $matches[1]);
            foreach ($lines as $line) {
                $line = trim(str_replace('*', '', $line));
                if (!empty($line) && !preg_match('/^@|^\*|^\s*$/', $line)) {
                    return $line;
                }
            }
        }
        return 'No description available';
    }
    
    private static function extract_classes($content) {
        preg_match_all('/class\s+([A-Za-z_][A-Za-z0-9_]*)/', $content, $matches);
        return $matches[1] ?? [];
    }
    
    private static function extract_functions($content) {
        preg_match_all('/function\s+([A-Za-z_][A-Za-z0-9_]*)/', $content, $matches);
        return $matches[1] ?? [];
    }
    
    private static function extract_class_description($content, $class_name) {
        // Look for docblock before class declaration
        $pattern = '/\/\*\*([^*]|\*(?!\/))*\*\/\s*(?:abstract\s+|final\s+)?class\s+' . preg_quote($class_name) . '/s';
        if (preg_match($pattern, $content, $matches)) {
            $docblock = $matches[0];
            // Extract first meaningful line from docblock
            if (preg_match('/\*\s*([^@*\n]+)/', $docblock, $desc_matches)) {
                return trim($desc_matches[1]);
            }
        }
        return 'No description available';
    }
    
    private static function count_class_methods($content, $class_name) {
        // Find class content between class declaration and next class or end of file
        $pattern = '/class\s+' . preg_quote($class_name) . '\s*[^{]*\{(.*?)(?=class\s+|$)/s';
        if (preg_match($pattern, $content, $matches)) {
            $class_content = $matches[1];
            preg_match_all('/(?:public|private|protected)\s+function\s+/', $class_content, $method_matches);
            return count($method_matches[0]);
        }
        return 0;
    }
    
    private static function determine_class_type($path) {
        if (strpos($path, 'admin') !== false) return 'Admin';
        if (strpos($path, 'includes') !== false) return 'Core';
        if (strpos($path, 'assets') !== false) return 'Assets';
        if (strpos($path, 'automation') !== false) return 'Automation';
        return 'Other';
    }
    
    /**
     * Check overall system status
     */
    private static function check_overall_status($css_assets, $js_assets) {
        $css_stats = self::get_asset_stats($css_assets);
        $js_stats = self::get_asset_stats($js_assets);
        
        $total_missing = $css_stats['missing'] + $js_stats['missing'];
        $total_assets = $css_stats['total'] + $js_stats['total'];
        
        if ($total_missing === 0) {
            return array(
                'class' => 'status-success',
                'icon' => 'dashicons-yes',
                'message' => __('All Assets Available', 'tradepress'),
                'details' => sprintf(__('%d assets managed successfully', 'tradepress'), $total_assets)
            );
        } elseif ($total_missing <= 3) {
            return array(
                'class' => 'status-warning',
                'icon' => 'dashicons-warning',
                'message' => __('Minor Issues Detected', 'tradepress'),
                'details' => sprintf(__('%d of %d assets missing', 'tradepress'), $total_missing, $total_assets)
            );
        } else {
            return array(
                'class' => 'status-error',
                'icon' => 'dashicons-no',
                'message' => __('Multiple Assets Missing', 'tradepress'),
                'details' => sprintf(__('%d of %d assets missing', 'tradepress'), $total_missing, $total_assets)
            );
        }
    }
}