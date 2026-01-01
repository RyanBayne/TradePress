<?php
/**
 * TradePress Development Duplicate Checker View
 *
 * @package TradePress/Admin/Views
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Admin_Development_Duplicate_Checker {
    
    /**
     * Enqueue required assets
     */
    private static function enqueue_assets() {
        wp_enqueue_style(
            'tradepress-duplicate-checker',
            TRADEPRESS_PLUGIN_URL . 'assets/css/duplicate-checker.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        wp_enqueue_script(
            'tradepress-duplicate-checker',
            TRADEPRESS_PLUGIN_URL . 'assets/js/duplicate-checker.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
    }
    
    /**
     * Output the duplicate checker view
     */
    public static function output() {
        self::enqueue_assets();
        
        $php_duplicates = self::find_duplicate_files('php');
        $css_duplicates = self::find_duplicate_files('css');
        $js_duplicates = self::find_duplicate_files('js');
        
        ?>
        <div class="tradepress-duplicate-checker">
            <div class="tradepress-duplicate-checker-header">
                <h3><?php esc_html_e('TradePress Duplicate File Checker', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Identifies duplicate PHP files including variations with hyphens vs periods in filenames.', 'tradepress'); ?></p>
            </div>
            
            <!-- PHP Files Section -->
            <div class="file-type-section">
                <h4>PHP Files</h4>
                <?php if (!empty($php_duplicates)): ?>
                    <div class="duplicates-found">
                        <h4 style="color: #d63638;"><?php echo count($php_duplicates); ?> duplicate file groups found</h4>
                        
                        <?php foreach ($php_duplicates as $group): ?>
                            <div class="duplicate-group">
                                <h5>Duplicate Files:</h5>
                                <ul>
                                    <?php foreach ($group as $file): ?>
                                        <?php $vscode_url = 'vscode-insiders://file/' . str_replace('\\', '/', $file['full_path']); ?>
                                        <li><a href="javascript:void(0)" class="vscode-link" data-vscode-path="<?php echo esc_attr($vscode_url); ?>" style="color: blue; text-decoration: underline;"><?php echo esc_html($file['relative_path']); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-duplicates">
                        <h4 style="color: #00a32a;">✅ No duplicate files found</h4>
                        <p>All PHP files have unique names.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- CSS Files Section -->
            <div class="file-type-section">
                <h4>CSS Files</h4>
                <?php if (!empty($css_duplicates)): ?>
                    <div class="duplicates-found">
                        <h4 style="color: #d63638;"><?php echo count($css_duplicates); ?> duplicate file groups found</h4>
                        
                        <?php foreach ($css_duplicates as $group): ?>
                            <div class="duplicate-group">
                                <h5>Duplicate Files:</h5>
                                <ul>
                                    <?php foreach ($group as $file): ?>
                                        <?php $vscode_url = 'vscode-insiders://file/' . str_replace('\\', '/', $file['full_path']); ?>
                                        <li><a href="javascript:void(0)" class="vscode-link" data-vscode-path="<?php echo esc_attr($vscode_url); ?>" style="color: blue; text-decoration: underline;"><?php echo esc_html($file['relative_path']); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-duplicates">
                        <h4 style="color: #00a32a;">✅ No duplicate files found</h4>
                        <p>All CSS files have unique names.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- JS Files Section -->
            <div class="file-type-section">
                <h4>JS Files</h4>
                <?php if (!empty($js_duplicates)): ?>
                    <div class="duplicates-found">
                        <h4 style="color: #d63638;"><?php echo count($js_duplicates); ?> duplicate file groups found</h4>
                        
                        <?php foreach ($js_duplicates as $group): ?>
                            <div class="duplicate-group">
                                <h5>Duplicate Files:</h5>
                                <ul>
                                    <?php foreach ($group as $file): ?>
                                        <?php $vscode_url = 'vscode-insiders://file/' . str_replace('\\', '/', $file['full_path']); ?>
                                        <li><a href="javascript:void(0)" class="vscode-link" data-vscode-path="<?php echo esc_attr($vscode_url); ?>" style="color: blue; text-decoration: underline;"><?php echo esc_html($file['relative_path']); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-duplicates">
                        <h4 style="color: #00a32a;">✅ No duplicate files found</h4>
                        <p>All JS files have unique names.</p>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
        <?php
    }
    
    /**
     * Find duplicate files by extension
     */
    private static function find_duplicate_files($extension) {
        $files = self::get_all_files_by_extension($extension);
        $normalized_files = array();
        $duplicates = array();
        
        foreach ($files as $file) {
            $basename = basename($file);
            $normalized = self::normalize_filename($basename);
            
            if (!isset($normalized_files[$normalized])) {
                $normalized_files[$normalized] = array();
            }
            
            $normalized_files[$normalized][] = array(
                'relative_path' => str_replace(TRADEPRESS_PLUGIN_DIR_PATH, '', $file),
                'full_path' => $file
            );
        }
        
        foreach ($normalized_files as $normalized => $file_list) {
            if (count($file_list) > 1) {
                $duplicates[] = $file_list;
            }
        }
        
        return $duplicates;
    }
    
    /**
     * Get all files by extension in plugin
     */
    private static function get_all_files_by_extension($extension) {
        $files = array();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(TRADEPRESS_PLUGIN_DIR_PATH, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === $extension) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Normalize filename for comparison
     */
    private static function normalize_filename($filename) {
        // Remove extension
        $name = pathinfo($filename, PATHINFO_FILENAME);
        
        // Replace hyphens with periods and vice versa for comparison
        $normalized = str_replace(array('-', '.'), '_', $name);
        
        return strtolower($normalized);
    }
}