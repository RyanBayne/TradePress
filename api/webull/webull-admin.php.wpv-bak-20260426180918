<?php
/**
 * TradePress WeBull API Admin
 *
 * Handles the admin settings for WeBull API integration
 * 
 * @package TradePress
 * @subpackage API\WeBull
 * @version 1.0.0
 * @since 2025-04-13
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress WeBull API Admin Class
 */
class TradePress_WeBull_Admin {
    
    /**
     * Settings group name
     *
     * @var string
     */
    private $group = 'tradepress_webull_settings';
    
    /**
     * Settings section name
     *
     * @var string
     */
    private $section = 'tradepress_webull_api_section';
    
    /**
     * Settings page slug
     *
     * @var string
     */
    private $page = 'tradepress_api_settings';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add settings tab
        add_filter('tradepress_api_settings_tabs', array($this, 'add_settings_tab'), 10, 1);
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add settings content
        add_action('tradepress_api_settings_content_webull', array($this, 'settings_content'));
    }
    
    /**
     * Add WeBull settings tab to API settings page
     *
     * @param array $tabs Existing tabs
     * @return array Modified tabs
     */
    public function add_settings_tab($tabs) {
        $tabs['webull'] = array(
            'title' => __('WeBull', 'tradepress'),
            'icon' => 'webull.png'
        );
        return $tabs;
    }
    
    /**
     * Register WeBull API settings
     */
    public function register_settings() {
        // Register the settings section
        add_settings_section(
            $this->section, 
            __('WeBull API Settings', 'tradepress'),
            array($this, 'section_description'), 
            $this->page
        );
        
        // Register settings fields
        register_setting($this->group, 'tradepress_webull_enabled');
        register_setting($this->group, 'tradepress_webull_sandbox_mode');
        register_setting($this->group, 'tradepress_webull_email');
        register_setting($this->group, 'tradepress_webull_password', array(
            'sanitize_callback' => array($this, 'sanitize_password')
        ));
        register_setting($this->group, 'tradepress_webull_device_id');
        register_setting($this->group, 'tradepress_webull_access_token');
        register_setting($this->group, 'tradepress_webull_refresh_token');
        register_setting($this->group, 'tradepress_webull_trade_token');
        register_setting($this->group, 'tradepress_webull_cache_duration', array(
            'default' => 300 // 5 minutes default
        ));
        
        // Add settings fields
        add_settings_field(
            'tradepress_webull_enabled',
            __('Enable WeBull Integration', 'tradepress'),
            array($this, 'enabled_field'),
            $this->page,
            $this->section
        );
        
        add_settings_field(
            'tradepress_webull_sandbox_mode',
            __('Sandbox Mode', 'tradepress'),
            array($this, 'sandbox_mode_field'),
            $this->page,
            $this->section
        );
        
        add_settings_field(
            'tradepress_webull_email',
            __('Email Address', 'tradepress'),
            array($this, 'email_field'),
            $this->page,
            $this->section
        );
        
        add_settings_field(
            'tradepress_webull_password',
            __('Password', 'tradepress'),
            array($this, 'password_field'),
            $this->page,
            $this->section
        );
        
        add_settings_field(
            'tradepress_webull_device_id',
            __('Device ID', 'tradepress'),
            array($this, 'device_id_field'),
            $this->page,
            $this->section
        );
        
        add_settings_field(
            'tradepress_webull_access_token',
            __('Access Token', 'tradepress'),
            array($this, 'access_token_field'),
            $this->page,
            $this->section
        );
        
        add_settings_field(
            'tradepress_webull_refresh_token',
            __('Refresh Token', 'tradepress'),
            array($this, 'refresh_token_field'),
            $this->page,
            $this->section
        );
        
        add_settings_field(
            'tradepress_webull_trade_token',
            __('Trade Token', 'tradepress'),
            array($this, 'trade_token_field'),
            $this->page,
            $this->section
        );
        
        add_settings_field(
            'tradepress_webull_cache_duration',
            __('Cache Duration (seconds)', 'tradepress'),
            array($this, 'cache_duration_field'),
            $this->page,
            $this->section
        );
    }
    
    /**
     * Settings section description
     */
    public function section_description() {
        echo '<p>' . __('Configure your WeBull API settings to integrate with TradePress. You\'ll need your WeBull account credentials to get started.', 'tradepress') . '</p>';
        echo '<p>' . __('Note that WeBull does not offer an official API. TradePress uses a reverse-engineered client-facing API which may change without notice.', 'tradepress') . '</p>';
    }
    
    /**
     * Enable field HTML
     */
    public function enabled_field() {
        $enabled = get_option('tradepress_webull_enabled', 'no');
        ?>
        <label for="tradepress_webull_enabled">
            <input type="checkbox" id="tradepress_webull_enabled" name="tradepress_webull_enabled" value="yes" <?php checked($enabled, 'yes'); ?> />
            <?php _e('Enable WeBull API integration', 'tradepress'); ?>
        </label>
        <p class="description"><?php _e('Check this box to enable the WeBull API integration.', 'tradepress'); ?></p>
        <?php
    }
    
    /**
     * Sandbox mode field HTML
     */
    public function sandbox_mode_field() {
        $sandbox = get_option('tradepress_webull_sandbox_mode', 'yes');
        ?>
        <label for="tradepress_webull_sandbox_mode">
            <input type="checkbox" id="tradepress_webull_sandbox_mode" name="tradepress_webull_sandbox_mode" value="yes" <?php checked($sandbox, 'yes'); ?> />
            <?php _e('Use paper trading (recommended)', 'tradepress'); ?>
        </label>
        <p class="description"><?php _e('Check this box to use paper trading (practice) instead of live trading.', 'tradepress'); ?></p>
        <?php
    }
    
    /**
     * Email field HTML
     */
    public function email_field() {
        $email = get_option('tradepress_webull_email', '');
        ?>
        <input type="email" id="tradepress_webull_email" name="tradepress_webull_email" value="<?php echo esc_attr($email); ?>" class="regular-text" />
        <p class="description"><?php _e('Enter your WeBull account email address.', 'tradepress'); ?></p>
        <?php
    }
    
    /**
     * Password field HTML
     */
    public function password_field() {
        $password = get_option('tradepress_webull_password', '');
        $masked = !empty($password) ? '••••••••••••••••' : '';
        ?>
        <input type="password" id="tradepress_webull_password" name="tradepress_webull_password" value="<?php echo esc_attr($masked); ?>" class="regular-text" 
        data-has-value="<?php echo !empty($password) ? 'true' : 'false'; ?>" />
        <p class="description"><?php _e('Enter your WeBull account password. Leave blank to keep the existing password.', 'tradepress'); ?></p>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var $password = $('#tradepress_webull_password');
                $password.on('focus', function() {
                    if ($(this).data('has-value') === 'true') {
                        $(this).val('');
                    }
                });
            });
        </script>
        <?php
    }
    
    /**
     * Device ID field HTML
     */
    public function device_id_field() {
        $device_id = get_option('tradepress_webull_device_id', '');
        ?>
        <input type="text" id="tradepress_webull_device_id" name="tradepress_webull_device_id" value="<?php echo esc_attr($device_id); ?>" class="regular-text" readonly />
        <button type="button" id="generate_device_id" class="button"><?php _e('Generate New Device ID', 'tradepress'); ?></button>
        <p class="description"><?php _e('WeBull Device ID. System generated - do not change this unless necessary.', 'tradepress'); ?></p>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#generate_device_id').on('click', function() {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'tradepress_generate_webull_device_id',
                            nonce: '<?php echo wp_create_nonce('tradepress-webull-nonce'); ?>'
                        },
                        success: function(response) {
                            if(response.success) {
                                $('#tradepress_webull_device_id').val(response.data.device_id);
                            } else {
                                alert(response.data.message);
                            }
                        }
                    });
                });
            });
        </script>
        <?php
    }
    
    /**
     * Access Token field HTML
     */
    public function access_token_field() {
        $token = get_option('tradepress_webull_access_token', '');
        $display = !empty($token) ? substr($token, 0, 10) . '...' : '';
        ?>
        <input type="text" id="tradepress_webull_access_token" name="tradepress_webull_access_token" value="<?php echo esc_attr($token); ?>" class="regular-text" />
        <p class="description"><?php _e('System generated during authentication - do not modify manually.', 'tradepress'); ?></p>
        <?php
    }
    
    /**
     * Refresh Token field HTML
     */
    public function refresh_token_field() {
        $token = get_option('tradepress_webull_refresh_token', '');
        $display = !empty($token) ? substr($token, 0, 10) . '...' : '';
        ?>
        <input type="text" id="tradepress_webull_refresh_token" name="tradepress_webull_refresh_token" value="<?php echo esc_attr($token); ?>" class="regular-text" />
        <p class="description"><?php _e('System generated during authentication - do not modify manually.', 'tradepress'); ?></p>
        <?php
    }
    
    /**
     * Trade Token field HTML
     */
    public function trade_token_field() {
        $token = get_option('tradepress_webull_trade_token', '');
        ?>
        <input type="text" id="tradepress_webull_trade_token" name="tradepress_webull_trade_token" value="<?php echo esc_attr($token); ?>" class="regular-text" />
        <p class="description"><?php _e('Trade token required for placing orders. System generated - do not modify manually.', 'tradepress'); ?></p>
        <?php
    }
    
    /**
     * Cache Duration field HTML
     */
    public function cache_duration_field() {
        $duration = get_option('tradepress_webull_cache_duration', 300);
        ?>
        <input type="number" id="tradepress_webull_cache_duration" name="tradepress_webull_cache_duration" value="<?php echo esc_attr($duration); ?>" class="small-text" min="0" step="1" />
        <p class="description"><?php _e('How long to cache API responses in seconds (0 to disable caching).', 'tradepress'); ?></p>
        <?php
    }
    
    /**
     * Settings page content
     */
    public function settings_content() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields($this->group);
            do_settings_sections($this->page);
            
            // Add a login button
            $email = get_option('tradepress_webull_email', '');
            $device_id = get_option('tradepress_webull_device_id', '');
            $has_login_info = !empty($email) && !empty($device_id);
            ?>
            
            <div class="webull-login-section">
                <h3><?php _e('WeBull Authentication', 'tradepress'); ?></h3>
                <p><?php _e('After entering your email and password above, click the button below to authenticate with WeBull:', 'tradepress'); ?></p>
                <button type="button" id="tradepress_webull_login" class="button button-primary" <?php echo !$has_login_info ? 'disabled' : ''; ?>>
                    <?php _e('Authenticate with WeBull', 'tradepress'); ?>
                </button>
                <span id="webull-login-status"></span>
                
                <script type="text/javascript">
                    jQuery(document).ready(function($) {
                        $('#tradepress_webull_login').on('click', function() {
                            var $button = $(this);
                            var $status = $('#webull-login-status');
                            
                            $button.prop('disabled', true);
                            $status.html('<span style="color:#0073aa;"><?php _e('Authenticating...', 'tradepress'); ?></span>');
                            
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'tradepress_webull_authenticate',
                                    email: $('#tradepress_webull_email').val(),
                                    password: $('#tradepress_webull_password').val(),
                                    device_id: $('#tradepress_webull_device_id').val(),
                                    nonce: '<?php echo wp_create_nonce('tradepress-webull-nonce'); ?>'
                                },
                                success: function(response) {
                                    $button.prop('disabled', false);
                                    
                                    if(response.success) {
                                        $status.html('<span style="color:green;"><?php _e('Authentication successful!', 'tradepress'); ?></span>');
                                        
                                        // Update token fields with new values
                                        $('#tradepress_webull_access_token').val(response.data.access_token);
                                        $('#tradepress_webull_refresh_token').val(response.data.refresh_token);
                                    } else {
                                        $status.html('<span style="color:red;">' + response.data.message + '</span>');
                                    }
                                },
                                error: function() {
                                    $button.prop('disabled', false);
                                    $status.html('<span style="color:red;"><?php _e('Error communicating with the server.', 'tradepress'); ?></span>');
                                }
                            });
                        });
                        
                        // Enable/disable login button based on email and device ID
                        $('#tradepress_webull_email').on('change', checkLoginButtonState);
                        $('#tradepress_webull_device_id').on('change', checkLoginButtonState);
                        
                        function checkLoginButtonState() {
                            var email = $('#tradepress_webull_email').val();
                            var deviceId = $('#tradepress_webull_device_id').val();
                            $('#tradepress_webull_login').prop('disabled', !email || !deviceId);
                        }
                    });
                </script>
            </div>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    /**
     * Sanitize password field
     *
     * @param string $input Password input
     * @return string Sanitized password
     */
    public function sanitize_password($input) {
        if (empty($input) || $input === '••••••••••••••••') {
            return get_option('tradepress_webull_password', '');
        }
        
        return $input;
    }

}

// Initialize the admin class
$tradepress_webull_admin = new TradePress_WeBull_Admin();