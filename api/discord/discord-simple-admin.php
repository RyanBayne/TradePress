<?php
/**
 * TradePress Discord Troubleshooting Admin Page
 *
 * Provides an admin interface for troubleshooting Discord API connections
 * Non-Ajax version for reliable testing
 * 
 * @package TradePress
 * @subpackage API\Discord
 * @version 1.0.0
 * @since 2025-04-23
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('TRADEPRESS_DISCORD_Troubleshooter')) {
    require_once(trailingslashit(TRADEPRESS_PLUGIN_DIR_PATH) . 'api/discord/discord-troubleshooter.php');
}

/**
 * TradePress Discord Simple Troubleshooting Admin Page
 * No Ajax, just simple form submissions for reliability
 */
class TRADEPRESS_DISCORD_Simple_Admin {
    
    /**
     * Discord Troubleshooter instance
     *
     * @var TRADEPRESS_DISCORD_Troubleshooter
     */
    private $troubleshooter;
    
    /**
     * Admin notices to display
     *
     * @var array
     */
    private $admin_notices = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the troubleshooter
        $this->troubleshooter = new TRADEPRESS_DISCORD_Troubleshooter();
        
        // Add the admin menu item
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Handle form submissions
        add_action('admin_init', array($this, 'handle_form_submissions'));
        
        // Add admin notices
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tradepress', // Parent menu slug
            'Discord Simple Tester', // Page title
            'Discord Simple Tester', // Menu title
            'manage_options', // Capability
            'tradepress-discord-simple-tester', // Menu slug
            array($this, 'render_admin_page') // Function to render the page
        );
    }
    
    /**
     * Add admin notice
     *
     * @param string $message Notice message
     * @param string $type Notice type (success, error, warning, info)
     */
    public function add_notice($message, $type = 'info') {
        $this->admin_notices[] = array(
            'message' => $message,
            'type' => $type
        );
    }
    
    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        // Only show notices on our page
        $screen = get_current_screen();
        if (!isset($screen->id) || $screen->id !== 'tradepress_page_tradepress-discord-simple-tester') {
            return;
        }
        
        foreach ($this->admin_notices as $notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
        }
    }
    
    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        // Only process on our admin page
        if (!isset($_GET['page']) || $_GET['page'] !== 'tradepress-discord-simple-tester') {
            return;
        }
        
        // Verify user permissions
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Test bot token
        if (isset($_POST['test_token']) && isset($_POST['discord_bot_token'])) {
            if (!isset($_POST['discord_nonce']) || !wp_verify_nonce($_POST['discord_nonce'], 'TRADEPRESS_DISCORD_simple_test')) {
                $this->add_notice('Security check failed.', 'error');
                return;
            }
            
            $token = sanitize_text_field($_POST['discord_bot_token']);
            
            if (empty($token)) {
                $this->add_notice('Token is required.', 'error');
                return;
            }
            
            // Test the token
            $is_valid = $this->troubleshooter->validate_bot_token($token);
            
            if ($is_valid) {
                $this->add_notice(implode(' ', $this->troubleshooter->get_success()), 'success');
            } else {
                $this->add_notice(implode(' ', $this->troubleshooter->get_errors()), 'error');
            }
        }
        
        // Save bot token
        if (isset($_POST['save_token']) && isset($_POST['discord_bot_token'])) {
            if (!isset($_POST['discord_nonce']) || !wp_verify_nonce($_POST['discord_nonce'], 'TRADEPRESS_DISCORD_simple_test')) {
                $this->add_notice('Security check failed.', 'error');
                return;
            }
            
            $token = sanitize_text_field($_POST['discord_bot_token']);
            
            if (empty($token)) {
                $this->add_notice('Token is required.', 'error');
                return;
            }
            
            // Test and save the token
            $is_valid = $this->troubleshooter->validate_bot_token($token);
            
            if ($is_valid) {
                update_option('TRADEPRESS_DISCORD_bot_token', $token);
                $this->add_notice('Token validated and saved successfully.', 'success');
            } else {
                $this->add_notice('Could not save token: ' . implode(' ', $this->troubleshooter->get_errors()), 'error');
            }
        }
        
        // Test channel
        if (isset($_POST['test_channel']) && isset($_POST['discord_channel_id'])) {
            if (!isset($_POST['discord_nonce']) || !wp_verify_nonce($_POST['discord_nonce'], 'TRADEPRESS_DISCORD_simple_test')) {
                $this->add_notice('Security check failed.', 'error');
                return;
            }
            
            $channel_id = sanitize_text_field($_POST['discord_channel_id']);
            
            if (empty($channel_id)) {
                $this->add_notice('Channel ID is required.', 'error');
                return;
            }
            
            // Validate the channel
            $is_valid = $this->troubleshooter->validate_channel($channel_id);
            
            if ($is_valid) {
                update_option('TRADEPRESS_DISCORD_channel_id', $channel_id);
                $this->add_notice(implode(' ', $this->troubleshooter->get_success()), 'success');
            } else {
                $this->add_notice(implode(' ', $this->troubleshooter->get_errors()), 'error');
            }
        }
        
        // Send test message
        if (isset($_POST['send_test']) && isset($_POST['discord_channel_id']) && isset($_POST['test_message'])) {
            if (!isset($_POST['discord_nonce']) || !wp_verify_nonce($_POST['discord_nonce'], 'TRADEPRESS_DISCORD_simple_test')) {
                $this->add_notice('Security check failed.', 'error');
                return;
            }
            
            $channel_id = sanitize_text_field($_POST['discord_channel_id']);
            $message = sanitize_textarea_field($_POST['test_message']);
            
            if (empty($channel_id)) {
                $this->add_notice('Channel ID is required.', 'error');
                return;
            }
            
            if (empty($message)) {
                $this->add_notice('Message content is required.', 'error');
                return;
            }
            
            // Send the test message
            $sent = $this->troubleshooter->test_message_delivery($channel_id, $message);
            
            if ($sent) {
                $this->add_notice(implode(' ', $this->troubleshooter->get_success()), 'success');
            } else {
                $this->add_notice(implode(' ', $this->troubleshooter->get_errors()), 'error');
            }
        }
        
        // Run diagnostics
        if (isset($_POST['run_diagnostics'])) {
            if (!isset($_POST['discord_nonce']) || !wp_verify_nonce($_POST['discord_nonce'], 'TRADEPRESS_DISCORD_simple_test')) {
                $this->add_notice('Security check failed.', 'error');
                return;
            }
            
            $channel_id = isset($_POST['discord_channel_id']) ? sanitize_text_field($_POST['discord_channel_id']) : '';
            
            // Generate the report
            $diagnostics = $this->troubleshooter->run_diagnostics($channel_id);
            
            if (!empty($diagnostics['errors'])) {
                foreach ($diagnostics['errors'] as $error) {
                    $this->add_notice($error, 'error');
                }
            }
            
            if (!empty($diagnostics['success'])) {
                foreach ($diagnostics['success'] as $success) {
                    $this->add_notice($success, 'success');
                }
            }
        }
    }
    
    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // Get current settings
        $bot_token = get_option('TRADEPRESS_DISCORD_bot_token', '');
        $channel_id = get_option('TRADEPRESS_DISCORD_channel_id', '');
        
        ?>
        <div class="wrap">
            <h1>Discord Simple Tester</h1>
            
            <div class="notice notice-info">
                <p>This is a simplified version of the Discord troubleshooter that uses direct form submissions instead of Ajax for more reliable testing.</p>
            </div>
            
            <div class="card">
                <h2>1. Verify Bot Token</h2>
                <p>First, let's verify your Discord bot token is valid.</p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('TRADEPRESS_DISCORD_simple_test', 'discord_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Bot Token</th>
                            <td>
                                <input type="password" id="discord_bot_token" name="discord_bot_token" value="<?php echo esc_attr($bot_token); ?>" class="regular-text">
                                <button type="button" id="show_token" class="button button-secondary">Show</button>
                                <p class="description">Your Discord bot token from the Discord Developer Portal.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p>
                        <input type="submit" name="test_token" class="button button-primary" value="Test Bot Token">
                        <input type="submit" name="save_token" class="button button-secondary" value="Save Token">
                    </p>
                </form>
            </div>
            
            <div class="card">
                <h2>2. Test Channel Connection</h2>
                <p>Verify that your bot can access a specific channel.</p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('TRADEPRESS_DISCORD_simple_test', 'discord_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Channel ID</th>
                            <td>
                                <input type="text" id="discord_channel_id" name="discord_channel_id" value="<?php echo esc_attr($channel_id); ?>" class="regular-text">
                                <p class="description">The ID of the Discord channel for alerts. Enable Developer Mode in Discord and right-click a channel to copy its ID.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p>
                        <input type="submit" name="test_channel" class="button button-primary" value="Test Channel Access">
                    </p>
                </form>
            </div>
            
            <div class="card">
                <h2>3. Send Test Message</h2>
                <p>Send a test message to verify everything is working correctly.</p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('TRADEPRESS_DISCORD_simple_test', 'discord_nonce'); ?>
                    
                    <input type="hidden" name="discord_channel_id" value="<?php echo esc_attr($channel_id); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Test Message</th>
                            <td>
                                <textarea id="test_message" name="test_message" class="regular-text" rows="3">This is a test message from TradePress Discord Simple Tester.</textarea>
                            </td>
                        </tr>
                    </table>
                    
                    <p>
                        <input type="submit" name="send_test" class="button button-primary" value="Send Test Message">
                    </p>
                </form>
            </div>
            
            <div class="card">
                <h2>4. Run Full Diagnostics</h2>
                <p>Run a complete health check on your Discord integration.</p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('TRADEPRESS_DISCORD_simple_test', 'discord_nonce'); ?>
                    
                    <input type="hidden" name="discord_channel_id" value="<?php echo esc_attr($channel_id); ?>">
                    
                    <p>
                        <input type="submit" name="run_diagnostics" class="button button-primary" value="Run Full Diagnostics">
                    </p>
                </form>
                
                <?php if (isset($_POST['run_diagnostics'])): ?>
                    <div id="diagnostics_report">
                        <?php echo $this->troubleshooter->generate_report(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Styles are now loaded from discord-simple-admin.css -->
        
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Show/hide token
                $('#show_token').on('click', function() {
                    var $input = $('#discord_bot_token');
                    if ($input.attr('type') === 'password') {
                        $input.attr('type', 'text');
                        $(this).text('Hide');
                    } else {
                        $input.attr('type', 'password');
                        $(this).text('Show');
                    }
                });
            });
        </script>
        <?php
    }
}

// Initialize the admin page
new TRADEPRESS_DISCORD_Simple_Admin();