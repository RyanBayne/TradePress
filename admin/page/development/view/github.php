<?php
/**
 * TradePress Development GitHub Tab
 * 
 * Displays GitHub integration with subtabs for issues and configuration.
 * 
 * @package TradePress\Admin\development
 * @version 1.0.0
 * @since 1.0.0
 * @created 2024-01-15
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TradePress_Admin_Development_GitHub
 * 
 * Handles the rendering and functionality of the GitHub tab
 * in the Development section.
 */
class TradePress_Admin_Development_GitHub {
    
    /**
     * Output the GitHub view
     */
    public static function output() {
        $current_subtab = isset($_GET['subtab']) ? sanitize_title($_GET['subtab']) : 'next-issue';
        $repo_owner = 'your-github-username';
        $repo_name = 'TradePress';
        ?>
        <div class="tab-content" id="github">
            <div class="tradepress-github-container">
                <!-- GitHub Subtabs Navigation -->
                <div class="subsubsub">
                    <a href="<?php echo esc_url(add_query_arg(array('tab' => 'github', 'subtab' => 'next-issue'))); ?>" 
                       class="<?php echo $current_subtab === 'next-issue' ? 'current' : ''; ?>">
                        <?php esc_html_e('Next Issue', 'tradepress'); ?>
                    </a> | 
                    <a href="<?php echo esc_url(add_query_arg(array('tab' => 'github', 'subtab' => 'all-issues'))); ?>" 
                       class="<?php echo $current_subtab === 'all-issues' ? 'current' : ''; ?>">
                        <?php esc_html_e('All Issues', 'tradepress'); ?>
                    </a> | 
                    <a href="<?php echo esc_url(add_query_arg(array('tab' => 'github', 'subtab' => 'create-issue'))); ?>" 
                       class="<?php echo $current_subtab === 'create-issue' ? 'current' : ''; ?>">
                        <?php esc_html_e('Create Issue', 'tradepress'); ?>
                    </a> | 
                    <a href="<?php echo esc_url(add_query_arg(array('tab' => 'github', 'subtab' => 'api-config'))); ?>" 
                       class="<?php echo $current_subtab === 'api-config' ? 'current' : ''; ?>">
                        <?php esc_html_e('API Configuration', 'tradepress'); ?>
                    </a>
                </div>
                <br class="clear" />
                
                <!-- Subtab Content -->
                <div class="tradepress-github-subtab-content">
                    <?php
                    // Load appropriate subtab file
                    $subtab_file = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/github/' . $current_subtab . '.php';
                    
                    // Replace dash with underscore for function name
                    $function_name = str_replace('-', '_', $current_subtab);
                    
                    if (file_exists($subtab_file)) {
                        require_once $subtab_file;
                        $function_name = 'TRADEPRESS_GITHUB_' . $function_name . '_content';
                        if (function_exists($function_name)) {
                            call_user_func($function_name, $repo_owner, $repo_name);
                        } else {
                            echo '<div class="notice notice-error"><p>' . esc_html__('Function for this subtab does not exist.', 'tradepress') . '</p></div>';
                        }
                    } else {
                        echo '<div class="notice notice-error"><p>' . esc_html__('Subtab file not found.', 'tradepress') . '</p></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}
