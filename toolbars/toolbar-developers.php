<?php
/**
 * TradePress - Developer Toolbar
 *
 * The developer toolbar requires the "TradePressdevelopertoolbar" custom capability. The
 * toolbar allows actions not all key holders should be giving access to. The
 * menu is intended for developers to already have access to a range of
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Toolbars
 * @since    1.0
 * 
 * @version 7.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  

if( !class_exists( 'TradePress_Admin_Toolbar_Developers' ) ) :

class TradePress_Admin_Toolbar_Developers {
    public function __construct() {
        // This is a precaution as the same check is done when including the file.
        if( !current_user_can( 'TradePressdevelopertoolbar' ) ) {
            return false;
        }
        
        // Initialize the toolbar.
        $this->init(); 
    }    
    
    /**
    * Calls a method per group of items.
    * 
    * @version 1.2
    */
    private function init() {
        global $wp_admin_bar;  

        self::parent_level();
        self::second_level_configuration_options();
        self::second_level_backup_tools();
    }

    private static function parent_level() {
        global $wp_admin_bar;   
        
        // Top Level/Level One
        $args = array(
            'id'     => 'TradePress-toolbarmenu-developers',
            'title'  => __( 'TradePress Developers', 'text_domain' ),          
        );
        $wp_admin_bar->add_menu( $args );        
    }
    
    /**
    * Adds a group of configuration options i.e. uninstall. 
    * 
    * @version 1.0
    */
    private static function second_level_configuration_options() {
        global $wp_admin_bar;
        
        // Group - Configuration Options
        $args = array(
            'id'     => 'TradePress-toolbarmenu-configurationoptions',
            'parent' => 'TradePress-toolbarmenu-developers',
            'title'  => __( 'Configuration Options', 'text_domain' ), 
            'meta'   => array( 'class' => 'second-toolbar-group' )         
        );        
        $wp_admin_bar->add_menu( $args );        
            
            // NEW ITEM - reinstall plugin options.
            $thisaction = 'TradePressuninstalloptions';     
        
                $href = TradePress_returning_url_nonced( array( 'TradePressaction' => $thisaction ), $thisaction, $_SERVER['REQUEST_URI'] );
                            
                $args = array(
                    'id'     => 'TradePress-toolbarmenu-uninstallsettings',
                    'parent' => 'TradePress-toolbarmenu-configurationoptions',
                    'title'  => __( 'Un-Install Settings', 'tradepress' ),
                    'href'   => esc_url( $href ),            
                );
            
            $wp_admin_bar->add_menu( $args );     
            
            // NEW ITEM - Beta Testing Switch 
            $thisaction = 'TradePress_beta_testing_switch';     
        
                // $_POST processing function can be found in post.php    
                $href = admin_url( 'admin-post.php?action=' . $thisaction );
                
                if( get_option( 'TradePress_beta_testing' ) )
                {
                    $title = __( 'Disable Beta Testing', 'tradepress' );        
                }
                elseif( !get_option( 'TradePress_beta_mode' ) )
                {
                    $title = __( 'Activate Beta Testing', 'tradepress' );;    
                }
                   
                $args = array(
                    'id'     => 'TradePress-toolbarmenu-activatebetatesting',
                    'parent' => 'TradePress-toolbarmenu-configurationoptions',
                    'title'  => $title,
                    'href'   => esc_url( $href ),            
                );
            
            $wp_admin_bar->add_menu( $args );    
            
            // NEW ITEM - Demo Mode Switch
            $thisaction = 'TradePress_demo_mode_switch';
                
                // $_POST processing function can be found in post.php    
                $href = admin_url( 'admin-post.php?action=' . $thisaction );
                
                // We need to check using require_once if the function exists, to ensure we use the right value
                if (!function_exists('is_demo_mode')) {
                    require_once TRADEPRESS_PLUGIN_DIR . 'functions/functions.tradepress-test-data.php';
                }
                
                $is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;
                
                if ($is_demo) {
                    $title = __( '✅ Demo Mode: ON', 'tradepress' );        
                } else {
                    $title = __( '❌ Demo Mode: OFF', 'tradepress' );    
                }
                   
                $args = array(
                    'id'     => 'TradePress-toolbarmenu-toggledemomode',
                    'parent' => 'TradePress-toolbarmenu-configurationoptions',
                    'title'  => $title,
                    'href'   => esc_url( $href ),            
                );
            
            $wp_admin_bar->add_menu( $args );
            
            // NEW ITEM - Developer Mode Toggle
            $developer_mode = get_option('tradepress_developer_mode', false);
            $thisaction = 'tradepress_toggle_developer_mode_toolbar';
            
            $href = admin_url( 'admin-post.php?action=' . $thisaction );
            
            if ($developer_mode) {
                $title = __( '✅ Developer Mode: ON', 'tradepress' );
            } else {
                $title = __( '❌ Developer Mode: OFF', 'tradepress' );
            }
            
            $args = array(
                'id'     => 'TradePress-toolbarmenu-toggledevelopermode',
                'parent' => 'TradePress-toolbarmenu-configurationoptions',
                'title'  => $title,
                'href'   => esc_url( wp_nonce_url( $href, 'tradepress_developer_mode_nonce' ) ),
            );
            
            $wp_admin_bar->add_menu( $args );
            
            // NEW ITEM - Reset Setup Wizard
            $thisaction = 'tradepress_reset_setup_wizard_toolbar';
            
            $href = admin_url( 'admin-post.php?action=' . $thisaction );
            
            $args = array(
                'id'     => 'TradePress-toolbarmenu-resetsetupwizard',
                'parent' => 'TradePress-toolbarmenu-configurationoptions',
                'title'  => __( 'Reset Setup Wizard', 'tradepress' ),
                'href'   => esc_url( wp_nonce_url( $href, 'tradepress_reset_setup_wizard_nonce' ) ),
            );
            
            $wp_admin_bar->add_menu( $args );
            
        // Group - Cache Management
        $args = array(
            'id'     => 'TradePress-toolbarmenu-cache',
            'parent' => 'TradePress-toolbarmenu-developers',
            'title'  => __( 'Cache Management', 'tradepress' ), 
            'meta'   => array( 'class' => 'second-toolbar-group' )         
        );        
        $wp_admin_bar->add_menu( $args );
        
        // API Capability Matrix Cache Refresh
        $thisaction = 'tradepress_refresh_api_matrix_cache';
        $href = admin_url( 'admin-post.php?action=' . $thisaction );
        
        $args = array(
            'id'     => 'TradePress-toolbarmenu-refreshapicache',
            'parent' => 'TradePress-toolbarmenu-cache',
            'title'  => __( 'Refresh API Matrix Cache', 'tradepress' ),
            'href'   => esc_url( wp_nonce_url( $href, 'tradepress_refresh_api_cache_nonce' ) ),
        );
        $wp_admin_bar->add_menu( $args );
            
        // Group - BugNet Controls
        $args = array(
            'id'     => 'TradePress-toolbarmenu-bugnet',
            'parent' => 'TradePress-toolbarmenu-developers',
            'title'  => __( 'BugNet Controls', 'tradepress' ), 
            'meta'   => array( 'class' => 'second-toolbar-group' )         
        );        
        $wp_admin_bar->add_menu( $args );
        
        // Depth Level Control
        $current_depth = get_option( 'bugnet_depth_level', 3 );
        $args = array(
            'id'     => 'TradePress-toolbarmenu-bugnet-depth',
            'parent' => 'TradePress-toolbarmenu-bugnet',
            'title'  => sprintf( __( 'Debug Depth: %d', 'tradepress' ), $current_depth ),
            'href'   => admin_url( 'admin.php?page=tradepress&tab=bugnet' ),            
        );
        $wp_admin_bar->add_menu( $args );
        
        // Output Type Toggles
        $outputs = array(
            'errors' => __( 'Errors (debug.log)', 'tradepress' ),
            'console' => __( 'Console Output', 'tradepress' ),
            'traces' => __( 'Traces (trace.log)', 'tradepress' ),
            'users' => __( 'Users (users.log)', 'tradepress' ),
            'ai' => __( 'AI Debug (ai.log)', 'tradepress' ),
            'automation' => __( 'Automation (automation.log)', 'tradepress' )
        );
        
        foreach ( $outputs as $output_key => $output_label ) {
            $is_enabled = get_option( 'bugnet_output_' . $output_key, $output_key === 'errors' ? 'yes' : 'no' ) === 'yes';
            $status = $is_enabled ? '✅' : '❌';
            
            $args = array(
                'id'     => 'TradePress-toolbarmenu-bugnet-' . $output_key,
                'parent' => 'TradePress-toolbarmenu-bugnet',
                'title'  => $status . ' ' . $output_label,
                'href'   => admin_url( 'admin.php?page=tradepress&tab=bugnet' ),            
            );
            $wp_admin_bar->add_menu( $args );
        }
        
        // Clear Logs option
        $args = array(
            'id'     => 'TradePress-toolbarmenu-bugnet-clearlogs',
            'parent' => 'TradePress-toolbarmenu-bugnet',
            'title'  => __( 'Clear Logs', 'tradepress' ),
            'href'   => wp_nonce_url( admin_url( 'admin.php?page=TradePress&tab=bugnet&action=clear_logs' ), 'tradepress_clear_logs', 'nonce' ),
        );
        $wp_admin_bar->add_menu( $args );
    }

    /**
    * Adds backup and version management tools
    * 
    * @version 1.0
    */
    private static function second_level_backup_tools() {
        global $wp_admin_bar;
        
        // Group - Backup Tools
        $args = array(
            'id'     => 'TradePress-toolbarmenu-backuptools',
            'parent' => 'TradePress-toolbarmenu-developers',
            'title'  => __( 'Backup Tools', 'tradepress' ), 
            'meta'   => array( 'class' => 'second-toolbar-group' )         
        );        
        $wp_admin_bar->add_menu( $args );        
            
        // NEW ITEM - Plugin Backup with Version Increment
        $thisaction = 'TradePress_backup_plugin_increment_version';     
    
            $href = admin_url( 'admin-post.php?action=' . $thisaction );
                        
            $args = array(
                'id'     => 'TradePress-toolbarmenu-backupplugin',
                'parent' => 'TradePress-toolbarmenu-backuptools',
                'title'  => __( 'Backup Plugin & Increment Version', 'tradepress' ),
                'href'   => esc_url( wp_nonce_url( $href, 'tradepress_backup_plugin_nonce' ) ),            
            );
        
        $wp_admin_bar->add_menu( $args );
    }

    /**
     * Handle backup plugin with version increment
     */
    public static function handle_backup_plugin_increment_version() {
        // Start logging immediately
        tradepress_trace_log('TradePress Backup: Starting backup process');
        
        // Check capabilities first
        if (!current_user_can('TradePressdevelopertoolbar')) {
            tradepress_trace_log('TradePress Backup Error: Insufficient permissions');
            wp_die(__('Insufficient permissions', 'tradepress'));
        }

        // Verify nonce - check both GET and POST
        $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : (isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '');
        if (!wp_verify_nonce($nonce, 'tradepress_backup_plugin_nonce')) {
            tradepress_trace_log('TradePress Backup Error: Security check failed');
            wp_die(__('Security check failed', 'tradepress'));
        }
        
        tradepress_trace_log('TradePress Backup: Security checks passed');

        try {
            tradepress_trace_log('TradePress Backup: Starting version extraction');
            
            // Get current version from plugin file
            $plugin_file = TRADEPRESS_PLUGIN_DIR . 'tradepress.php';
            if (!file_exists($plugin_file)) {
                throw new Exception('Plugin file not found: ' . $plugin_file);
            }
            
            tradepress_trace_log('TradePress Backup: Plugin file found: ' . $plugin_file);
            
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
            
            tradepress_trace_log('TradePress Backup: Version increment: ' . $current_version . ' -> ' . $new_version);
            
            // Create backup directory path - use Windows format since manual creation worked
            $backup_base = 'G:\\My Drive\\Backup\\';
            $current_month = date('Y-m');
            $backup_dir = $backup_base . $current_month . '\\';
            
            tradepress_trace_log('TradePress Backup: Using Windows path format: ' . $backup_dir);
            tradepress_trace_log('TradePress Backup: Current working directory: ' . getcwd());
            tradepress_trace_log('TradePress Backup: PHP version: ' . PHP_VERSION);
            tradepress_trace_log('TradePress Backup: WordPress ABSPATH: ' . ABSPATH);
            
            // Log backup attempt details
            tradepress_trace_log('TradePress Backup: Attempting backup to: ' . $backup_dir);
            tradepress_trace_log('TradePress Backup: Base directory exists: ' . (is_dir($backup_base) ? 'YES' : 'NO'));
            tradepress_trace_log('TradePress Backup: Base directory writable: ' . (is_writable($backup_base) ? 'YES' : 'NO'));
            tradepress_trace_log('TradePress Backup: Current working directory: ' . getcwd());
            tradepress_trace_log('TradePress Backup: PHP user: ' . (function_exists('get_current_user') ? get_current_user() : 'unknown'));
            
            // Check detailed permissions
            if (is_dir($backup_base)) {
                self::check_directory_permissions($backup_base);
            }
            
            // Check if base backup directory exists and is accessible
            if (!is_dir($backup_base)) {
                tradepress_trace_log('TradePress Backup Error: Base backup directory does not exist: ' . $backup_base);
                throw new Exception('Base backup directory does not exist: ' . $backup_base);
            }
            
            if (!is_writable($backup_base)) {
                tradepress_trace_log('TradePress Backup Error: Base backup directory is not writable: ' . $backup_base);
                throw new Exception('Base backup directory is not writable: ' . $backup_base);
            }
            
            // Check if directory exists (it was manually created)
            if (!is_dir($backup_dir)) {
                tradepress_trace_log('TradePress Backup Error: Expected directory does not exist: ' . $backup_dir);
                tradepress_trace_log('TradePress Backup: Attempting to create directory...');
                
                // Try to create it anyway
                if (!wp_mkdir_p($backup_dir)) {
                    throw new Exception('Could not create backup directory: ' . $backup_dir);
                }
                tradepress_trace_log('TradePress Backup: Directory created successfully');
            } else {
                tradepress_trace_log('TradePress Backup: Using existing directory: ' . $backup_dir);
            }
            
            // Determine backup folder name
            $backup_folder_name = 'tradepress-' . $new_version;
            $backup_full_path = $backup_dir . $backup_folder_name;
            
            tradepress_trace_log('TradePress Backup: Full backup path: ' . $backup_full_path);
            tradepress_trace_log('TradePress Backup: Source directory: ' . TRADEPRESS_PLUGIN_DIR);
            tradepress_trace_log('TradePress Backup: Source directory exists: ' . (is_dir(TRADEPRESS_PLUGIN_DIR) ? 'YES' : 'NO'));
            
            // Copy plugin directory
            tradepress_trace_log('TradePress Backup: Starting copy operation');
            tradepress_trace_log('TradePress Backup: From: ' . TRADEPRESS_PLUGIN_DIR);
            tradepress_trace_log('TradePress Backup: To: ' . $backup_full_path);
            
            if (!self::copy_directory(TRADEPRESS_PLUGIN_DIR, $backup_full_path)) {
                throw new Exception('Failed to copy plugin directory');
            }
            tradepress_trace_log('TradePress Backup: Copy completed successfully');
            
            // Update version in original plugin files
            tradepress_trace_log('TradePress Backup: Updating version to ' . $new_version);
            self::update_version_in_files($new_version);
            tradepress_trace_log('TradePress Backup: Version update completed');
            
            // Show success notice
            add_action('admin_notices', function() use ($new_version, $backup_full_path) {
                echo '<div class="notice notice-success"><p>';
                echo sprintf(__('✅ Plugin backed up successfully to: %s<br>Version updated to: %s', 'tradepress'), 
                    esc_html($backup_full_path), esc_html($new_version));
                echo '</p></div>';
            });
            
            // Log successful backup
            tradepress_trace_log('TradePress Backup Success: Backed up to ' . $backup_full_path . ' with version ' . $new_version);
            
        } catch (Exception $e) {
            // Log the error for debugging with more context
            tradepress_trace_log('TradePress Backup Error: ' . $e->getMessage());
            tradepress_trace_log('TradePress Backup Error Context: User=' . wp_get_current_user()->user_login . ', Time=' . date('Y-m-d H:i:s'));
            tradepress_trace_log('TradePress Backup Error: Full exception: ' . $e->getTraceAsString());
            
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>';
                echo sprintf(__('❌ Backup failed: %s<br><small>Check wp-content/debug.log for detailed error information.</small>', 'tradepress'), esc_html($e->getMessage()));
                echo '</p></div>';
            });
        }
        
        // Redirect back to referring page
        tradepress_trace_log('TradePress Backup: Process completed, redirecting');
        wp_safe_redirect(wp_get_referer() ?: admin_url());
        exit;
    }
    
    /**
     * Recursively copy directory with enhanced error logging
     */
    private static function copy_directory($src, $dst) {
        tradepress_trace_log('TradePress Backup Copy: ' . $src . ' -> ' . $dst);
        if (!is_dir($src)) {
            return false;
        }
        
        if (!is_dir($dst)) {
            if (!wp_mkdir_p($dst)) {
                tradepress_trace_log('TradePress Backup Error: Failed to create destination directory: ' . $dst);
                return false;
            }
        }
        
        $dir = opendir($src);
        if (!$dir) {
            tradepress_trace_log('TradePress Backup Error: Cannot open source directory: ' . $src);
            return false;
        }
        
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $src_file = $src . DIRECTORY_SEPARATOR . $file;
            $dst_file = $dst . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($src_file)) {
                if (!self::copy_directory($src_file, $dst_file)) {
                    tradepress_trace_log('TradePress Backup Error: Failed to copy directory: ' . $src_file);
                    closedir($dir);
                    return false;
                }
            } else {
                if (!copy($src_file, $dst_file)) {
                    tradepress_trace_log('TradePress Backup Error: Failed to copy file: ' . $src_file . ' to ' . $dst_file);
                    closedir($dir);
                    return false;
                }
            }
        }
        
        closedir($dir);
        return true;
    }
    
    /**
     * Test directory creation with various methods
     */
    private static function test_directory_creation($path) {
        tradepress_trace_log('TradePress Backup Test: Testing directory creation for: ' . $path);
        
        // Test 1: Basic mkdir
        $test1 = @mkdir($path, 0755, true);
        tradepress_trace_log('TradePress Backup Test: mkdir() result: ' . ($test1 ? 'SUCCESS' : 'FAILED'));
        if (!$test1) {
            $error = error_get_last();
            tradepress_trace_log('TradePress Backup Test: mkdir() error: ' . ($error ? $error['message'] : 'Unknown'));
        }
        
        // Test 2: wp_mkdir_p
        if (!$test1) {
            $test2 = wp_mkdir_p($path);
            tradepress_trace_log('TradePress Backup Test: wp_mkdir_p() result: ' . ($test2 ? 'SUCCESS' : 'FAILED'));
        }
        
        // Test 3: Check if directory exists after attempts
        $exists = is_dir($path);
        tradepress_trace_log('TradePress Backup Test: Directory exists after tests: ' . ($exists ? 'YES' : 'NO'));
        
        return $exists;
    }
    
    /**
     * Check directory permissions and log detailed information
     */
    private static function check_directory_permissions($path) {
        $info = array();
        
        $info['path'] = $path;
        $info['exists'] = is_dir($path);
        $info['readable'] = is_readable($path);
        $info['writable'] = is_writable($path);
        
        if (function_exists('fileperms')) {
            $perms = fileperms($path);
            $info['permissions'] = substr(sprintf('%o', $perms), -4);
        }
        
        if (function_exists('posix_getpwuid') && function_exists('fileowner')) {
            $owner_info = posix_getpwuid(fileowner($path));
            $info['owner'] = $owner_info['name'] ?? 'unknown';
        }
        
        tradepress_trace_log('TradePress Backup Permissions Check: ' . json_encode($info));
        return $info;
    }
    
    /**
     * Update version numbers in plugin files
     */
    private static function update_version_in_files($new_version) {
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

// Only instantiate the toolbar if user has the capability
if (current_user_can('TradePressdevelopertoolbar')) {
    return new TradePress_Admin_Toolbar_Developers();
}
if (current_user_can('TradePressdevelopertoolbar')) {
    return new TradePress_Admin_Toolbar_Developers();
}
