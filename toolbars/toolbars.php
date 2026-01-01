<?php
/**
 * TradePress - Toolbars Class by Ryan Bayne
 *
 * Add menus to the admin toolbar, front and backend.  
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Toolbars
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  

if( !class_exists( 'TradePress_Toolbars' ) ) :

class TradePress_Toolbars {
    
    public function __construct() {     
        // This is admin side only bars not administrator only. Security is done deeper into toolbar classes.
        add_action( 'wp_before_admin_bar_render', array( $this, 'admin_only_toolbars' ) );
        
        // Register admin post handlers for toolbar actions
        add_action( 'admin_post_TradePress_beta_testing_switch', array( $this, 'handle_beta_testing_switch' ) );
        add_action( 'admin_post_TradePress_demo_mode_switch', array( $this, 'handle_demo_mode_switch' ) );
        add_action( 'admin_post_TradePress_backup_plugin_increment_version', array( $this, 'handle_backup_plugin' ) );
        add_action( 'admin_post_tradepress_reset_pointers', array( $this, 'handle_reset_pointers' ) );
    }   
    
    public function admin_only_toolbars() {       
        if( !current_user_can( 'activate_plugins' ) ) return;  
        
        // Include developer toolbar (requires special capability AND developer mode enabled)
        $developer_mode = get_option('tradepress_developer_mode', false);
        $is_dev_mode_active = ($developer_mode === true || $developer_mode === 1 || $developer_mode === '1' || $developer_mode === 'yes');
        
        if( current_user_can( 'TradePressdevelopertoolbar' ) && $is_dev_mode_active ) {
            include_once( 'toolbar-developers.php' );
        }
        
        // Include QuickTools toolbar (available to all administrators)
        include_once( 'toolbar-quicktools.php' );
    }
    
    /**
     * Handle beta testing toggle
     */
    public function handle_beta_testing_switch() {
        // Check capabilities
        if (!current_user_can('TradePressdevelopertoolbar')) {
            wp_die(__('Insufficient permissions', 'tradepress'));
        }

        // Get current status
        $current_status = get_option('TradePress_beta_testing', false);
        $new_status = !$current_status;
        
        // Update the option
        update_option('TradePress_beta_testing', $new_status);
        
        // Create notice message
        $status_text = $new_status ? __('activated', 'tradepress') : __('deactivated', 'tradepress');
        $message = sprintf(__('Beta testing has been %s.', 'tradepress'), $status_text);
        
        // Add success notice
        $this->add_toolbar_notice('success', __('TradePress Beta Testing', 'tradepress'), $message);
        
        // Redirect back
        wp_safe_redirect(wp_get_referer() ?: admin_url());
        exit;
    }
    
    /**
     * Handle demo mode toggle
     */
    public function handle_demo_mode_switch() {
        // Check capabilities
        if (!current_user_can('TradePressdevelopertoolbar')) {
            wp_die(__('Insufficient permissions', 'tradepress'));
        }

        // Check for demo mode function
        if (!function_exists('is_demo_mode')) {
            require_once TRADEPRESS_PLUGIN_DIR . 'functions/functions.tradepress-test-data.php';
        }
        
        // Get current status
        $current_status = function_exists('is_demo_mode') ? is_demo_mode() : false;
        $new_status = !$current_status;
        
        // Update the option
        update_option('TradePress_demo_mode', $new_status ? 'yes' : 'no');
        
        // Create notice message
        $status_text = $new_status ? __('enabled', 'tradepress') : __('disabled', 'tradepress');
        $message = sprintf(__('Demo mode has been %s.', 'tradepress'), $status_text);
        
        // Add success notice
        $this->add_toolbar_notice('success', __('TradePress Demo Mode', 'tradepress'), $message);
        
        // Redirect back
        wp_safe_redirect(wp_get_referer() ?: admin_url());
        exit;
    }
    
    /**
     * Handle plugin backup with version increment
     */
    public function handle_backup_plugin() {
        // Check capabilities
        if (!current_user_can('TradePressdevelopertoolbar')) {
            wp_die(__('Insufficient permissions', 'tradepress'));
        }

        // Verify nonce
        $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : (isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '');
        if (!wp_verify_nonce($nonce, 'tradepress_backup_plugin_nonce')) {
            wp_die(__('Security check failed', 'tradepress'));
        }

        try {
            // Log backup attempt
            tradepress_trace_log('TradePress Backup: Starting backup process');
            
            // Get current version from plugin file
            $plugin_file = TRADEPRESS_PLUGIN_DIR . 'tradepress.php';
            if (!file_exists($plugin_file)) {
                throw new Exception('Plugin file not found: ' . $plugin_file);
            }
            
            $plugin_content = file_get_contents($plugin_file);
            
            // Extract current version
            preg_match('/Version:\s*(\d+\.\d+\.\d+)/', $plugin_content, $matches);
            if (!$matches) {
                throw new Exception('Could not find version in plugin file');
            }
            
            $current_version = $matches[1];
            $version_parts = explode('.', $current_version);
            $version_parts[2] = intval($version_parts[2]) + 1; // Increment patch version
            $new_version = implode('.', $version_parts);
            
            // Diagnose Google Drive access issue
            $google_drive_base = 'G:\\My Drive\\Backup\\';
            $local_backup_base = 'C:\\wamp64\\www\\Backups\\';
            $current_month = date('Y-m');
            
            // Log detailed system information
            tradepress_trace_log('TradePress Backup Diagnostics:');
            tradepress_trace_log('- PHP User: ' . (function_exists('get_current_user') ? get_current_user() : 'unknown'));
            tradepress_trace_log('- Server Software: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown'));
            tradepress_trace_log('- PHP SAPI: ' . php_sapi_name());
            tradepress_trace_log('- Working Directory: ' . getcwd());
            tradepress_trace_log('- Google Drive Path: ' . $google_drive_base);
            
            // Test different path formats
            $google_paths = array(
                'G:\\My Drive\\Backup\\',
                'G:/My Drive/Backup/',
                '\\\\?\\G:\\My Drive\\Backup\\',  // Long path format
                realpath('G:\\My Drive\\Backup\\') ?: 'realpath_failed'
            );
            
            foreach ($google_paths as $i => $path) {
                tradepress_trace_log('- Path Test ' . ($i+1) . ': ' . $path . ' - Exists: ' . (is_dir($path) ? 'YES' : 'NO') . ', Writable: ' . (is_writable($path) ? 'YES' : 'NO'));
            }
            
            // Check if Google Drive is accessible with any path format
            $google_accessible = false;
            foreach ($google_paths as $path) {
                if (is_dir($path) && is_writable($path)) {
                    $google_drive_base = $path;
                    $google_accessible = true;
                    break;
                }
            }
            
            if ($google_accessible) {
                $backup_base = $google_drive_base;
                $backup_location = 'Google Drive';
                tradepress_trace_log('TradePress Backup: Google Drive accessible with path: ' . $google_drive_base);
            } else {
                $backup_base = $local_backup_base;
                $backup_location = 'WAMP Backups Directory';
                
                // Create local backup directory if it doesn't exist
                if (!is_dir($backup_base)) {
                    wp_mkdir_p($backup_base);
                }
                
                tradepress_trace_log('TradePress Backup: Using fallback - Google Drive not accessible to Apache service');
                
                // Log WAMP solutions
                tradepress_trace_log('TradePress Backup: WAMP Solutions Available:');
                $solutions = $this->get_wamp_solutions();
                foreach ($solutions as $title => $steps) {
                    tradepress_trace_log('- ' . strip_tags($title));
                    foreach ($steps as $step) {
                        tradepress_trace_log('  ' . $step);
                    }
                }
            }
            
            $backup_dir = $backup_base . $current_month . '\\';
            
            tradepress_trace_log('TradePress Backup: Using ' . $backup_location . ': ' . $backup_dir);
            tradepress_trace_log('TradePress Backup: Base exists: ' . (is_dir($backup_base) ? 'YES' : 'NO'));
            tradepress_trace_log('TradePress Backup: Base writable: ' . (is_writable($backup_base) ? 'YES' : 'NO'));
            
            // Create directory if it doesn't exist
            if (!is_dir($backup_dir)) {
                tradepress_trace_log('TradePress Backup: Creating directory: ' . $backup_dir);
                if (!wp_mkdir_p($backup_dir)) {
                    $error = error_get_last();
                    tradepress_trace_log('TradePress Backup Error: ' . ($error ? $error['message'] : 'Unknown mkdir error'));
                    throw new Exception('Could not create backup directory: ' . $backup_dir . ' (tried ' . $backup_location . ')');
                }
                tradepress_trace_log('TradePress Backup: Directory created successfully');
            } else {
                tradepress_trace_log('TradePress Backup: Directory already exists');
            }
            
            // Determine backup folder name
            $backup_folder_name = 'tradepress-' . $new_version;
            $backup_full_path = $backup_dir . $backup_folder_name;
            
            tradepress_trace_log('TradePress Backup: Full path: ' . $backup_full_path);
            tradepress_trace_log('TradePress Backup: Source: ' . TRADEPRESS_PLUGIN_DIR);
            
            // Copy plugin directory
            tradepress_trace_log('TradePress Backup: Starting copy operation');
            if (!$this->copy_directory(TRADEPRESS_PLUGIN_DIR, $backup_full_path)) {
                throw new Exception('Failed to copy plugin directory from ' . TRADEPRESS_PLUGIN_DIR . ' to ' . $backup_full_path);
            }
            tradepress_trace_log('TradePress Backup: Copy operation completed');
            
            // Update version in original plugin files
            tradepress_trace_log('TradePress Backup: Updating version to ' . $new_version);
            $this->update_version_in_files($new_version);
            tradepress_trace_log('TradePress Backup: Version update completed');
            
            // Log success
            tradepress_trace_log('TradePress Backup Success: Backed up to ' . $backup_full_path);
            
            // Add success notice with location info
            $message = sprintf(__('Plugin backed up successfully to %s:<br><strong>%s</strong><br>Version updated to: %s', 'tradepress'), 
                $backup_location, esc_html($backup_full_path), esc_html($new_version));
            $this->add_toolbar_notice('success', __('TradePress Backup Complete', 'tradepress'), $message);
            
        } catch (Exception $e) {
            // Log detailed error
            tradepress_trace_log('TradePress Backup Error: ' . $e->getMessage());
            tradepress_trace_log('TradePress Backup Error Context: ' . $e->getTraceAsString());
            
            // Add error notice with WAMP-specific guidance
            $message = sprintf(__('Backup failed: %s<br><small><strong>WAMP Issue:</strong> Apache service cannot access Google Drive. Solutions:<br>1. Run Apache as your user account<br>2. Use local backup in wp-content/backups/<br>3. Map Google Drive to a different drive letter</small>', 'tradepress'), esc_html($e->getMessage()));
            $this->add_toolbar_notice('error', __('TradePress Backup Failed', 'tradepress'), $message);
        }
        
        // Redirect back
        wp_safe_redirect(wp_get_referer() ?: admin_url());
        exit;
    }
    
    /**
     * Get WAMP Google Drive solutions
     */
    private function get_wamp_solutions() {
        $solutions = array(
            '<strong>Solution 1: Run Apache as your user account</strong>' => array(
                '1. Open WAMP Manager → Apache → Service administration → Install Service',
                '2. Set "Log on as" to your Windows user account',
                '3. Restart Apache service'
            ),
            '<strong>Solution 2: Use subst command (Recommended)</strong>' => array(
                '1. Open Command Prompt as Administrator',
                '2. Run: subst B: "G:\\My Drive"',
                '3. Update backup path to use B: drive',
                '4. Add to Windows startup for persistence'
            ),
            '<strong>Solution 3: Create junction/symlink</strong>' => array(
                '1. Open Command Prompt as Administrator',
                '2. Run: mklink /J "C:\\GoogleDriveBackup" "G:\\My Drive\\Backup"',
                '3. Update backup path to C:\\GoogleDriveBackup'
            )
        );
        
        return $solutions;
    }
    
    /**
     * Handle reset all pointers
     */
    public function handle_reset_pointers() {
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'tradepress'));
        }

        // Verify nonce
        $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
        if (!wp_verify_nonce($nonce, 'tradepress_reset_pointers')) {
            wp_die(__('Security check failed', 'tradepress'));
        }
        
        // Reset dismissed pointers for current user
        delete_user_meta(get_current_user_id(), 'dismissed_wp_pointers');
        
        // Add success notice
        $this->add_toolbar_notice('success', __('Pointers Reset', 'tradepress'), __('All pointers have been reset and will appear again.', 'tradepress'));
        
        // Redirect back
        wp_safe_redirect(wp_get_referer() ?: admin_url());
        exit;
    }
    
    /**
     * Add a standardized toolbar notice
     */
    private function add_toolbar_notice($type, $title, $message) {
        // Generate unique notice ID
        $notice_id = 'toolbar_action_' . time() . '_' . rand(100, 999);
        
        // Add the notice using the correct method name
        TradePress_Admin_Notices::add_wordpress_notice(
            $notice_id,
            $type,
            false,
            $title,
            $message
        );
    }
    
    /**
     * Recursively copy directory
     */
    private function copy_directory($src, $dst) {
        if (!is_dir($src)) {
            return false;
        }
        
        if (!is_dir($dst)) {
            if (!wp_mkdir_p($dst)) {
                return false;
            }
        }
        
        $dir = opendir($src);
        if (!$dir) {
            return false;
        }
        
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $src_file = $src . DIRECTORY_SEPARATOR . $file;
            $dst_file = $dst . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($src_file)) {
                if (!$this->copy_directory($src_file, $dst_file)) {
                    closedir($dir);
                    return false;
                }
            } else {
                if (!copy($src_file, $dst_file)) {
                    closedir($dir);
                    return false;
                }
            }
        }
        
        closedir($dir);
        return true;
    }
    
    /**
     * Update version numbers in plugin files
     */
    private function update_version_in_files($new_version) {
        // Update tradepress.php
        $plugin_file = TRADEPRESS_PLUGIN_DIR . 'tradepress.php';
        $content = file_get_contents($plugin_file);
        $content = preg_replace('/Version:\s*\d+\.\d+\.\d+/', 'Version: ' . $new_version, $content);
        file_put_contents($plugin_file, $content);
        
        // Update readme.txt
        $readme_file = TRADEPRESS_PLUGIN_DIR . 'readme.txt';
        if (file_exists($readme_file)) {
            $content = file_get_contents($readme_file);
            $content = preg_replace('/Stable tag:\s*\d+\.\d+\.\d+/', 'Stable tag: ' . $new_version, $content);
            file_put_contents($readme_file, $content);
        }
    }
} 

endif;

return new TradePress_Toolbars();