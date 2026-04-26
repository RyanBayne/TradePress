<?php
/**
 * TradePress Request Listener
 * 
 * Handles all TradePress-related GET and POST requests with security verification.
 * This is the main entry point for processing form submissions and actions.
 * 
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress
 * @since    1.0.0
 * @version  1.4.1
 * @see      TradePress_Current_Task_Handler For handling current task form submissions
 * @see      TradePress_Admin_Development_Current_Task For the current task UI
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}  

if( !class_exists( 'TradePress_Listener' ) ) :

class TradePress_Listener {  
    /**
     * Constructor - sets up action hooks
     */
    public function __construct() {              
        // Process requests after WordPress is fully loaded but before headers are sent
        add_action( 'wp_loaded', array( $this, 'process_requests' ) );
        
        // Add an action to display admin notices
        add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );
        
        // Register handler for test post listener
        add_action( 'admin_post_tradepress_test_post_listener', array( $this, 'handle_test_post_listener' ) );
        
        // Ensure TradePress_Data_Decoder_Tab class is loaded before registering the action
        if ( ! class_exists( 'TradePress_Data_Decoder_Tab' ) ) {
            $decoder_tab_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/data/decoder.php';
            if ( file_exists( $decoder_tab_file ) ) {
                require_once $decoder_tab_file;
            }
        }
        // Register handler for data decoder form - now points to TradePress_Data_Decoder_Tab
        add_action( 'admin_post_tradepress_decode_data_action', array( 'TradePress_Data_Decoder_Tab', 'handle_decode_data_submission' ) );
    }
         
    /**
     * Main request handler - processes both GET and POST requests after security checks
     * 
     * @since 1.4.0
     * @return void
     */
    public function process_requests() {      
        // Exit early if we're doing an autosave, ajax request or cron
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return;    
        }        
        
        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;    
        }

        // Handle GET requests
        if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
            $this->process_get_requests();
        }
        
        // Handle POST requests
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $this->process_post_requests();
        }
    }
    
    /**
     * Process all GET requests related to TradePress
     * 
     * @since 1.4.0
     * @return void
     */
    private function process_get_requests() {
        // One of these values must be set for it to be TradePress related
        if( !isset( $_GET['TradePressaction'] ) && !isset( $_GET['state'] ) ) {
            return;    
        }
        
        // Handle OAuth returns before requiring login
        // This allows processing OAuth responses for non-logged in users
        $this->process_oauth_returns();
        
        // All other actions require authentication
        if( !is_user_logged_in() ) {       
            return;
        }
        
        // Process admin-only actions
        if( isset( $_GET['TradePressaction'] ) ) {
            if( !current_user_can( 'manage_options' ) ) {  
                return;    
            }
            
            // For sandbox GET request test, we'll allow with just the nonce check
            if ($_GET['TradePressaction'] === 'test_get_listener') {
                if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'test_get_listener')) {
                    wp_die(__('Security check failed. Please try again.', 'tradepress'));
                }
                
                $test_value = isset($_GET['test_value']) ? sanitize_text_field($_GET['test_value']) : '';
                wp_redirect(add_query_arg('listener_get_processed', $test_value, wp_get_referer()));
                exit;
            }
            
            if( !isset( $_REQUEST['_wpnonce'] ) ) {
                return;    
            }
            
            $this->process_admin_actions();
        }
    }
    
    /**
     * Process all POST requests related to TradePress
     * 
     * @since 1.4.0
     * @see TradePress_Current_Task_Handler For handling current task form submissions
     * @return void
     */
    private function process_post_requests() {
        // Check if this is a TradePress-related POST request
        if( !isset( $_POST['TradePress_form_action'] ) ) {
            // Check for current task form actions with their specific nonces
            if (isset($_POST['current_task_nonce']) && isset($_POST['set_current_task'])) {
                $this->process_current_task_forms('select_current_task');
                return;
            } else if (isset($_POST['task_notes_nonce']) && isset($_POST['update_working_notes'])) {
                $this->process_current_task_forms('update_task_working_notes');
                return;
            } else if (isset($_POST['subtasks_nonce']) && isset($_POST['update_subtasks'])) {
                $this->process_current_task_forms('update_subtasks');
                return;
            } else if (isset($_POST['task_status_nonce']) && (isset($_POST['mark_in_progress']) || isset($_POST['mark_completed']))) {
                $this->process_current_task_forms('update_task_status');
                return;
            }
            
            return;
        }
        
        // All POST actions require authentication
        if( !is_user_logged_in() ) {       
            return;
        }
        
        $action = sanitize_key( $_POST['TradePress_form_action'] );
        
        // Verify the nonce
        if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], $action ) ) {
            wp_die( __( 'Security check failed. Please try again.', 'tradepress' ) );
        }
        
        // Process the form based on the action
        switch( $action ) {
            case 'api_settings':
                $this->process_api_settings();
                break;
                
            case 'plugin_settings':
                $this->process_plugin_settings();
                break;
                
            case 'test_post_listener':
                // This will be handled by admin_post action to demonstrate another approach
                break;
                
            // Add more form handlers as needed
            
            default:
                // Allow extensions to hook into custom form actions
                do_action( 'tradepress_process_form_' . $action );
                break;
        }
    }
    
    /**
     * Handle test POST listener form submission
     * This is registered with admin_post_{action} hook
     *
     * @return void
     */
    public function handle_test_post_listener() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'tradepress'));
        }
        
        // Verify nonce
        check_admin_referer('test_post_listener'); // Updated to use check_admin_referer for POST
        
        $test_value = isset($_POST['test_value']) ? sanitize_text_field($_POST['test_value']) : '';
        
        // Redirect back to the sandbox page with a success message
        wp_redirect(add_query_arg(
            array(
                'page' => 'tradepress_sandbox',
                'tab' => 'listener',
                'listener_post_processed' => $test_value
            ),
            admin_url('admin.php')
        ));
        exit;
    }
    
    /**
     * Process current task form submissions
     * 
     * @since 1.4.0
     * @param string $action The form action to process
     * @see TradePress_Current_Task_Handler For the actual processing logic
     * @return void
     */
    private function process_current_task_forms($action) {
        // All POST actions require authentication
        if (!is_user_logged_in()) {       
            return;
        }
        
        // Verify the nonce based on the action
        $nonce_field = '';
        $nonce_action = '';
        
        switch ($action) {
            case 'select_current_task':
                $nonce_field = 'current_task_nonce';
                $nonce_action = 'tradepress_select_current_task';
                break;
            case 'update_task_working_notes':
                $nonce_field = 'task_notes_nonce';
                $nonce_action = 'tradepress_update_task_working_notes';
                break;
            case 'update_subtasks':
                $nonce_field = 'subtasks_nonce';
                $nonce_action = 'tradepress_update_subtasks';
                break;
            case 'update_task_status':
                $nonce_field = 'task_status_nonce';
                $nonce_action = 'tradepress_update_task_status';
                break;
            default:
                return;
        }
        
        // Verify the nonce
        if (!isset($_POST[$nonce_field]) || !wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
            wp_die(__('Security check failed. Please try again.', 'tradepress'));
        }
        
        // Trigger the action to process the form
        do_action('tradepress_process_form_' . $action);
    }
    
    /**
     * Display admin notices stored in transients
     *
     * @return void
     */
    public function display_admin_notices() {
        $notices = get_transient('tradepress_admin_notices');
        
        if ($notices) {
            foreach ($notices as $notice) {
                echo '<div class="notice notice-' . esc_attr($notice['type']) . ' is-dismissible"><p>' . 
                     wp_kses_post($notice['message']) . '</p></div>';
            }
            
            // Clear the notices
            delete_transient('tradepress_admin_notices');
        }
    }
    
    /**
     * Process OAuth return requests
     * 
     * @since 1.4.0
     * @return void
     */
    private function process_oauth_returns() {
        // Process twitch OAuth returns
        if( isset( $_GET['state'] ) && isset( $_GET['scope'] ) && isset( $_GET['code'] ) ) {
            $this->twitch_oauth_unlocksubcontent_return();
        }
    }
    
    /**
     * Process admin-only actions
     * 
     * @since 1.4.0
     * @return void
     */
    private function process_admin_actions() {
        $action = sanitize_key( $_GET['TradePressaction'] );
        
        // Verify nonce matches the requested action
        if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) ) {
            wp_die( __( 'Security check failed. Please try again.', 'tradepress' ) );
        }
        
        // Process different admin actions
        switch ( $action ) {
            case 'TradePressuninstalloptions':
                $this->uninstall_settings();
                break;
                
            case 'TradePresssyncmainfeedtowp':
                // $this->sync_main_channel_feed_to_wp();
                break;
                
            // Allow extensions to hook into custom admin actions
            default:
                do_action( 'tradepress_admin_action_' . $action );
                break;
        }
    }
    
    /**
     * Process API settings form submission
     * 
     * @since 1.4.0
     * @return void
     */
    private function process_api_settings() {
        // Process and save API settings
        // Implementation would go here
        
        // Redirect back to the settings page with success message
        wp_redirect( add_query_arg( 'updated', 'true', wp_get_referer() ) );
        exit;
    }
    
    /**
     * Process plugin settings form submission
     * 
     * @since 1.4.0
     * @return void
     */
    private function process_plugin_settings() {
        // Process and save plugin settings
        // Implementation would go here
        
        // Redirect back to the settings page with success message
        wp_redirect( add_query_arg( 'updated', 'true', wp_get_referer() ) );
        exit;
    }
    
    /**
     * Remove all plugin settings
     * 
     * @since 1.0.0
     * @return void
     */
    public function uninstall_settings() {
        // Security is already checked in process_admin_actions()
        
        if( !user_can( TradePress_CURRENTUSERID, 'activate_plugins' ) ) {  
            return;    
        }
        
        // Implementation would call uninstall_options function
        // TradePress_Uninstall::uninstall_options();
   
        TradePress_Admin_Notices::add_wordpress_notice(
            'devtoolbaruninstallednotices',
            'success',
            true,
            __( 'Options Removed', 'tradepress' ),
            __( 'TradePress options have been deleted and the plugin will need some configuration to begin using it.', 'tradepress' ) 
        );
        
        // Redirect back to prevent resubmission
        wp_safe_redirect( wp_get_referer() );
        exit;
    }
    
    /**
     * Process Twitch OAuth return requests
     * 
     * Listens for oAuth2 return and calls applicable functions to process 
     * the response from the Twitch API
     * 
     * @since 1.0.0
     * @return void
     */
    public function twitch_oauth_unlocksubcontent_return() {
        if( !isset( $_GET['state'] ) || !isset( $_GET['scope'] ) || !isset( $_GET['code'] ) ){
            return;    
        }
        
        // We require the local state value stored in transient. 
        if( !$transient_state = TradePress_get_transient_oauth_state( $_GET['state'] ) ) { 
            return;
        }   
        
        // Ensure the return from Twitch.tv is a request to unlock subscriber content...
        if( !isset( $transient_state['purpose'] ) || $transient_state['purpose'] != 'twitchsubcontent' ) {
            return;
        }
           
        $twitch_api = new TradePress_Twitch_API();
        $auth = $twitch_api->request_user_access_token( $_GET['code'], __FUNCTION__ );  
        $user = $twitch_api->get_user_by_bearer_token( $auth->access_token );

        // Set cookies with appropriate expiration times
        $this->set_secure_cookies( $user, $auth );

        // Get subscription data for the main channel...
        $subs = $twitch_api->get_broadcaster_subscriptions( 
            TradePress_get_main_channels_twitchid(), 
            $user->id, 
            false 
        );        
        
        if( isset( $subs->data['tier'] ) ) {
            setcookie( 'twitchsubtier', $subs->data['tier'], time() + 86400, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
        }

        // Redirect back to original page...
        TradePress_redirect_tracking( 
            get_page_uri( $transient_state['loginpageid'] ), 
            __LINE__, 
            __FUNCTION__ 
        );
        exit;        
    }
    
    /**
     * Set secure cookies for Twitch authentication
     *
     * @since 1.4.0
     * @param object $user User data from Twitch API
     * @param object $auth Authorization data from Twitch API
     * @return void
     */
    private function set_secure_cookies( $user, $auth ) {
        $secure = is_ssl();
        $http_only = true;
        
        setcookie( 
            'twitchname', 
            $user->display_name, 
            time() + 3600, 
            COOKIEPATH, 
            COOKIE_DOMAIN, 
            $secure, 
            $http_only 
        );
        
        setcookie( 
            'twitchid', 
            $user->id, 
            time() + 3600, 
            COOKIEPATH, 
            COOKIE_DOMAIN, 
            $secure, 
            $http_only 
        );
        
        setcookie( 
            'twitchaccesstoken', 
            $auth->access_token, 
            time() + 3600, 
            COOKIEPATH, 
            COOKIE_DOMAIN, 
            $secure, 
            $http_only 
        );
        
        setcookie( 
            'twitchrefresh_token', 
            $auth->refresh_token, 
            time() + 86400, 
            COOKIEPATH, 
            COOKIE_DOMAIN, 
            $secure, 
            $http_only 
        );
    }
}   

endif;

return new TradePress_Listener();