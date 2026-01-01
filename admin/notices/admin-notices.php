<?php
/**
 * TradePress - Admin Notices
 *
 * Management of notice data and arguments controlling notice presentation.
 * 
 * An array of notice names are stored in option "TradePress_admin_notices". 
 * 
 * Each notice is also stored as an option "'TradePress_admin_notice_' . $name" where
 * it can be used as a persistent notice.  
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TradePress/Notices
 * @since    1.0.0
 * @version  1.0.4
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TradePress_Admin_Notices') ) :

class TradePress_Admin_Notices {

    /**
    * Stores notices.
    * @var array
    */
    private static $notices = array();
    
    /**
     * Array of notices - name => callback.
     * 
     * @var array
     */
    private static $core_notices = array(
        'install'             => 'install_notice',
        'update'              => 'update_notice',
    );

    /**
     * Constructor.
     * 
     * @package TradePress
     */
    public static function init() {             
        self::$notices = get_option( 'TradePress_admin_notices', array() );
        add_action( 'TradePress_installed', array( __CLASS__, 'reset_admin_notices' ) );
        add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
        add_action( 'shutdown', array( __CLASS__, 'store_notices' ) );
   
        if ( current_user_can( 'activate_plugins' ) ) {
            add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
        }
        
        add_action( 'admin_notices', array( __CLASS__, 'output_custom_notices' ) );
        add_action('admin_notices', array( __CLASS__, 'display_notices'));
        add_action('admin_init', array( __CLASS__, 'dismiss_notices'));        
    }
       
    /**
    * Box for presenting the status and progress of something.
    * 
    * Includes HTML5 progress bars.
    * 
    * @author Ryan Bayne
    * @package TradePress
    */    
    public function progress_box( $title, $intro, $progress_array = array() ){
        $progress_html = '';
        
        if (!empty($progress_array)) {
            foreach ($progress_array as $label => $value) {
                $progress_html .= '<p><strong>' . esc_html($label) . ':</strong> ';
                $progress_html .= '<progress max="100" value="' . intval($value) . '"></progress> ';
                $progress_html .= intval($value) . '%</p>';
            }
        } else {
            $progress_html = self::info_area('', '                    
            Free Edition: <progress max="100" value="24"></progress> 24%<br>
            Premium Edition: <progress max="100" value="36"></progress> 36%<br>
            Support Content: <progress max="100" value="67"></progress> 67%<br>
            Translation: <progress max="100" value="87"></progress> 87%');
        }
        
        echo '
        <div class="TradePress_status_box_container">
            <div class="welcome-panel">
            
                <h3>' . esc_html(ucfirst( $title )) . '</h3>
                
                <div class="welcome-panel-content">
                    <p class="about-description">' . esc_html(ucfirst( $intro )) . '</p>
                    
                    ' . $progress_html . '
                    
                    <p>' . __( 'Pledge £9.99 to the TradePress project for 50% discount on the premium edition.', 'tradepress' ) . '</p>                                                     
                </div>

            </div> 
        </div>';  
    }
    
    /**
    * Present information at the head of a page with option to dismiss.   
    * 
    * @author Ryan Bayne
    * @package TradePress
    */
    public function intro_box( $title, $intro, $info_area = false, $info_area_title = '', $info_area_content = '', $footer = false, $dismissable_id = false ){
        global $current_user;
                                               
        // handling user action - hide notice and update user meta
        if ( isset($_GET[ $dismissable_id ]) && 'dismiss' == $_GET[ $dismissable_id ] ) {
            add_user_meta( $current_user->ID, $dismissable_id, 'true', true );
            return;
        }
               
        // a dismissed intro
        if ( $dismissable_id !== false && get_user_meta( $current_user->ID, $dismissable_id ) ) {
            return;
        }
                              
        // highlighted area within the larger box
        $highlighted_info = '';
        if( $info_area == true && is_string( $info_area_title ) ) {
            $highlighted_info = '<h4>' . esc_html($info_area_title) . '</h4>';
            $highlighted_info .= self::info_area( false, $info_area_content );
        }
                      
        // footer text within the larger box, smaller text, good for contact info or a link
        $footer_text = '<br>';
        if( $footer ) {
            $footer_text = '<p>' . wp_kses_post($footer) . '</p>';    
        }
        
        // add dismiss button
        $dismissable_button = '';
        if( $dismissable_id !== false ) {
            $dismissable_button = sprintf( 
                ' <a href="%s&%s=dismiss" class="button button-primary"> ' . __( 'Hide', 'tradepress' ) . ' </a>', 
                esc_url($_SERVER['REQUEST_URI']), 
                esc_attr($dismissable_id)
            );
        }
                
        echo '
        <div class="TradePress_status_box_container">
            <div class="welcome-panel">
                <div class="welcome-panel-content">
                    
                    <h1>' . esc_html(ucfirst( $title )) . $dismissable_button . '</h1>
                    
                    <p class="about-description">' . esc_html(ucfirst( $intro )) . '</p>
 
                    ' . $highlighted_info . '
                    
                    ' . $footer_text . '
                  
                </div>
            </div> 
        </div>';  
    } 

    /**
    * A mid grey area for attracting focus to a small amount of information. Is 
    * an alternative to standard WordPress notices and good for tips. 
    * 
    * @param mixed $title
    * @param mixed $message
    * 
    * @author Ryan Bayne
    * @package TradePress 
    */
    public function info_area( $title, $message, $admin_only = true ){   
        if( $admin_only == true && current_user_can( 'manage_options' ) || $admin_only !== true){
            
            $area_title = '';
            if( $title ){
                $area_title = '<h4>' . esc_html($title) . '</h4>';
            }
            
            return '
            <div style="background:#f0f0f1;border:1px solid #c3c4c7;padding:10px 15px;margin:10px 0;border-radius:4px;">
                ' . $area_title  . '
                <p>' . wp_kses_post($message) . '</p>
            </div>';          
        }
    }                          

    /**
     * Store notices to DB
     */
    public static function store_notices() {
        update_option( 'TradePress_admin_notices', self::get_notices() );
    }
                                
    /**
     * Get notices
     * @return array
     */
    public static function get_notices() {
        return self::$notices;
    }
                                  
    /**
     * Remove all notices.
     */
    public static function remove_all_notices() {
        self::$notices = array();
    }

    /**
     * Remove a notice from being displayed.
     * @param  string $name
     */
    public static function remove_notice( $name ) {
        self::$notices = array_diff( self::get_notices(), array( $name ) );
        delete_option( 'TradePress_admin_notice_' . $name );
    }

    /**
     * Add a notice to be displayed.
     * 
     * This method maintains compatibility with existing code that calls add_notice().
     * 
     * @param string $name Notice identifier/name
     * @param string $type Notice type (success, error, warning, info) - optional
     * @param bool $dismissible Whether notice can be dismissed - optional
     * @return void
     * 
     * @version 1.0
     */
    public static function add_notice($name, $type = 'info', $dismissible = false) {
        // Add the notice name to the notices array if not already present
        if (!in_array($name, self::$notices)) {
            self::$notices[] = $name;
        }
    }

    /**
     * When theme is switched or new version detected, reset notices.
     */
    public static function reset_admin_notices() {

    }
        
    /**
     * See if a notice is being shown.
     * @param  string  $name
     * @return boolean
     */
    public static function has_notice( $name ) {
        return in_array( $name, self::get_notices() );
    }
                                       
    /**
     * Hide a notice if the GET variable is set.
     */
    public static function hide_notices() {
        if ( isset( $_GET['TradePress-hide-notice'] ) && isset( $_GET['_TradePress_notice_nonce'] ) ) {
            if ( ! wp_verify_nonce( $_GET['_TradePress_notice_nonce'], 'TradePress_hide_notices_nonce' ) ) {
                wp_die( __( 'Action failed. Please refresh the page and retry.', 'tradepress' ) );
            }
            
            $hide_notice = sanitize_text_field( $_GET['TradePress-hide-notice'] );
            self::remove_notice( $hide_notice );
            do_action( 'TradePress_hide_' . $hide_notice . '_notice' );
        }
    }
                                         
    /**
     * Add notices + styles if needed.
     * 
     * @version 1.1
     */
    public static function add_notices() {
        $notices = self::get_notices();
        if ( ! empty( $notices ) ) {
            wp_enqueue_style( 'TradePress-activation', plugins_url(  '/assets/css/activation.css', TRADEPRESS_MAINFILE ) );
            foreach ( $notices as $notice ) {
                if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'TradePress_show_admin_notice', true, $notice ) ) {
                    add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
                } 
            }
        }
    }

    /**
     * Add a custom notice.
     * 
     * Example: TradePress_Admin_Notices::add_custom_notice( 'mycustomnotice', 'My name is <strong>Ryan Bayne</strong>' );
     * 
     * @param string $name
     * @param string $notice_html
     */
    public static function add_custom_notice( $name, $notice_html ) {
        self::add_notice( $name );
        update_option( 'TradePress_admin_notice_' . $name, wp_kses_post( $notice_html ) );
    }
    
    /**
    * Create a notice that uses WordPress own basic notice div and styling.
    * 
    * @param mixed $name
    * @param mixed $type error|warning|success|info
    * @param mixed $dismissible
    * @param mixed $title
    * @param mixed $description
    * 
    * @version 2.0
    */
    public static function add_wordpress_notice( $name, $type = 'success', $dismissible = false, $title = 'Sorry!', $description = 'Information about your last request has not been established, sorry about that.' ) {
        $wordpress_notice_array = array(
            'type' => $type,
            'dismissible' => $dismissible,
            'title' => $title,
            'description' => wp_kses_post( $description )
        );
        
        // Register the notice as normal, the output process will ensure the correct approach is applied.
        self::add_notice( $name );
        
        // We store the notice data in its own option. 
        update_option( 'TradePress_admin_notice_' . $name, $wordpress_notice_array );        
    }
                                        
    /**
     * Output any stored custom notices.
     */
    public static function output_custom_notices() {
        $notices = self::get_notices();

        $notices = apply_filters( 'TradePress_notices', $notices );
        
        if ( ! empty( $notices ) ) {
            foreach ( $notices as $notice ) {
                if ( empty( self::$core_notices[ $notice ] ) ) {
                    $notice_html = get_option( 'TradePress_admin_notice_' . $notice );

                    if ( is_string( $notice_html ) ) {
                        include( __DIR__ . '/custom.php' );
                        self::remove_notice( $notice );
                    } elseif( is_array( $notice_html ) ) {
                        // The notice does not use the default custom.php file.
                        self::notice( 
                            $notice_html['type'],
                            $notice_html['title'], 
                            $notice_html['description'],
                            $notice_html['dismissible']
                        ); 
                        
                        // Cleanup none dismissible notices.
                        self::remove_notice( $notice );  
                    }
                }
            }
        }
    }                               
 
    /**
     * If we need to update, include a message with the update button.
     */
    public static function update_notice() {
        if ( version_compare( get_option( 'TradePress_db_version' ), TRADEPRESS_VERSION, '<' ) ) {
            $updater = new TradePress_Background_Updater();
            if ( $updater->is_updating() || ! empty( $_GET['do_update_TradePress'] ) ) {
                include( 'notices/updating.php' );
            } else {
                include( 'notices/update.php' );
            }
        } else {
            include( 'notices/updated.php' );
        }
    }

    /**
     * If we have just installed, show a message with the install pages button.
     */
    public static function install_notice() {
        include( 'notices/install.php' );
    }
    
    /**
    * Create custom notice without a html file.
    * 
    * @param mixed $type error|warning|success|info
    * @param mixed $title
    * @param mixed $description
    * @param mixed $dismissible
    * 
    * @version 1.1
    */
    public static function notice( $type, $title, $description, $dismissible = false ) {
        self::$type( $title, $description, $dismissible );    
    }
               
    /**
    * Instant error notice output.
    * 
    * @param mixed $title
    * @param mixed $desc
    * @param mixed $dismissible
    * 
    * @version 1.1
    */
    public static function error( $title, $desc, $dismissible = false ) {
        $d = ''; if( $dismissible ){ $d = ' is-dismissible'; }
        ?><div class="notice notice-error<?php echo $d; ?>"><p><?php echo '<strong>' . $title . ': </strong>' . $desc; ?></p></div><?php     
    } 
    
    /**
    * Instant warning notice output.
    * 
    * @param mixed $title
    * @param mixed $desc
    * @param mixed $dismissible
    * 
    * @version 1.1
    */
    public static function warning( $title, $desc, $dismissible = false ) {
        $d = ''; if( $dismissible ){ $d = ' is-dismissible'; }
        ?><div class="notice notice-warning<?php echo $d; ?>"><p><?php echo '<strong>' . $title . ': </strong>' . $desc; ?></p></div><?php     
    } 
    
    /**
    * Instant success notice output.
    * 
    * @param mixed $title
    * @param mixed $desc
    * @param mixed $dismissible
    * 
    * @version 1.1
    */
    public static function success( $title, $desc, $dismissible = false ) {
        $d = ''; if( $dismissible ){ $d = ' is-dismissible'; }
        ?><div class="notice notice-success<?php echo $d; ?>"><p><?php echo '<strong>' . $title . ': </strong>' . $desc; ?></p></div><?php     
    } 
    
    /**
    * Instant info notice output.
    * 
    * @param mixed $title
    * @param mixed $desc
    * @param mixed $dismissible
    * 
    * @version 1.1
    */
    public static function info( $title, $desc, $dismissible = false ) {
        $d = ''; if( $dismissible ){ $d = ' is-dismissible'; }
        ?><div class="notice notice-info<?php echo $d; ?>"><p><?php echo '<strong>' . $title . ': </strong>' . $desc; ?></p></div><?php     
    }    

    /**
     * Display admin notices
     *
     * @since 1.0.0
     * @return void
     */
    public static function display_notices() {
        $notices = self::get_all_notices();
        
        if (empty($notices)) {
            return;
        }
        
        foreach ($notices as $id => $notice) {
            // Check if notice has been dismissed
            if ($notice['dismissible'] && get_option('tradepress_dismissed_' . $id, false)) {
                continue;
            }
            
            $dismissible_class = $notice['dismissible'] ? 'is-dismissible' : '';
            $dismiss_url = $notice['dismissible'] ? wp_nonce_url(add_query_arg('tradepress_dismiss_notice', $id), 'tradepress_dismiss_notice_' . $id) : '';
            
            ?>
            <div class="notice notice-<?php echo esc_attr($notice['type']); ?> <?php echo esc_attr($dismissible_class); ?>">
                <p><?php echo wp_kses_post($notice['message']); ?></p>
                <?php if ($notice['dismissible']) : ?>
                <a href="<?php echo esc_url($dismiss_url); ?>" class="notice-dismiss-link" style="text-decoration: none;">
                    <span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'tradepress'); ?></span>
                </a>
                <?php endif; ?>
            </div>
            <?php
        }
    }

    /**
     * Handle notice dismissal
     *
     * @since 1.0.0
     * @return void
     */
    public static function dismiss_notices() {
        if (!isset($_GET['tradepress_dismiss_notice'])) {
            return;
        }
        
        $notice_id = sanitize_text_field($_GET['tradepress_dismiss_notice']);
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'tradepress_dismiss_notice_' . $notice_id)) {
            wp_die(__('Security check failed', 'tradepress'));
        }
        
        // Save dismissal to user meta
        update_option('tradepress_dismissed_' . $notice_id, true);
        
        // Redirect back
        wp_safe_redirect(remove_query_arg(array('tradepress_dismiss_notice', '_wpnonce')));
        exit;
    }

    /**
     * Get all registered notices
     *
     * @since 1.0.0
     * @return array Array of notices
     */
    public static function get_all_notices() {
        return apply_filters('tradepress_admin_notices', self::$notices);
    }

    /**
     * Clear all notices
     *
     * @since 1.0.0
     * @return void
     */
    public static function clear_notices() {
        self::$notices = array();
    }

    /**
     * Remove a specific notice
     *
     * @since 1.0.0
     * @param string $id The notice ID to remove
     * @return void
     */
    public static function remove_notice_by_id($id) {
        if (isset(self::$notices[$id])) {
            unset(self::$notices[$id]);
        }
    }
    
    /**
     * Check if a plugin's requirements are met and display notices if not
     *
     * @since 1.0.0
     * @return bool True if requirements are met, false otherwise
     */
    public static function check_plugin_requirements() {
        $requirements_met = true;
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            self::add_notice(
                'php_version',
                sprintf(__('TradePress requires PHP version 7.0 or higher. You are running version %s.', 'tradepress'), PHP_VERSION),
                'error',
                false
            );
            $requirements_met = false;
        }
        
        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            self::add_notice(
                'wp_version',
                sprintf(__('TradePress requires WordPress version 5.0 or higher. You are running version %s.', 'tradepress'), $wp_version),
                'error',
                false
            );
            $requirements_met = false;
        }
        
        // Check for required PHP extensions
        $required_extensions = array('curl', 'json', 'mysqli');
        $missing_extensions = array();
        
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing_extensions[] = $ext;
            }
        }
        
        if (!empty($missing_extensions)) {
            self::add_notice(
                'missing_extensions',
                sprintf(__('TradePress requires the following PHP extensions: %s. Please contact your hosting provider.', 'tradepress'), implode(', ', $missing_extensions)),
                'error',
                false
            );
            $requirements_met = false;
        }
        
        return $requirements_met;
    }

    /**
     * Display demo mode or live mode indicator
     * 
     * Shows appropriate indicator based on current demo mode status
     * 
     * @param string $demo_title Optional custom title for demo mode
     * @param string $demo_message Optional custom message for demo mode
     * @param string $live_title Optional custom title for live mode
     * @param string $live_message Optional custom message for live mode
     * @param string $position Position of notice: 'default', 'top-right', 'floating'
     * @return void
     * 
     * @version 1.1
     */
    public static function demo_mode_indicator($demo_title = '', $demo_message = '', $live_title = '', $live_message = '', $position = 'default') {
        // Check if demo mode is active using the global function
        $is_demo_mode = function_exists('is_demo_mode') ? is_demo_mode() : false;
        
        // Set default messages
        if (empty($demo_title)) {
            $demo_title = __('Development in Progress', 'tradepress');
        }
        if (empty($demo_message)) {
            $demo_message = __('Running in demo mode (TRADEPRESS_DEMO_MODE is enabled). API requests are simulated with test data.', 'tradepress');
        }
        if (empty($live_title)) {
            $live_title = __('Live Mode Active', 'tradepress');
        }
        if (empty($live_message)) {
            $live_message = __('Running in live mode. Making real API calls with actual credentials.', 'tradepress');
        }
        
        $position_class = '';
        $container_style = '';
        
        switch ($position) {
            case 'top-right':
                $position_class = 'tradepress-indicator-top-right';
                $container_style = 'position: fixed; top: 32px; right: 20px; z-index: 9999; max-width: 300px;';
                break;
            case 'floating':
                $position_class = 'tradepress-indicator-floating';
                $container_style = 'position: absolute; top: 10px; right: 10px; z-index: 100; max-width: 280px;';
                break;
            default:
                $position_class = 'tradepress-indicator-default';
                break;
        }
        
        if ($is_demo_mode): ?>
            <!-- Demo Indicator -->
            <div class="demo-indicator <?php echo esc_attr($position_class); ?>" <?php if ($container_style) echo 'style="' . esc_attr($container_style) . '"'; ?>>
                <div class="demo-icon dashicons dashicons-admin-tools"></div>
                <div class="demo-text">
                    <h4><?php echo esc_html($demo_title); ?></h4>
                    <p><?php echo esc_html($demo_message); ?></p>
                </div>
                <span class="demo-badge"><?php esc_html_e('DEMO', 'tradepress'); ?></span>
            </div>
        <?php else: ?>
            <!-- Live Mode Indicator -->
            <div class="live-indicator <?php echo esc_attr($position_class); ?>" <?php if ($container_style) echo 'style="' . esc_attr($container_style) . '"'; ?>>
                <div class="live-icon dashicons dashicons-yes-alt"></div>
                <div class="live-text">
                    <h4><?php echo esc_html($live_title); ?></h4>
                    <p><?php echo esc_html($live_message); ?></p>
                </div>
                <span class="live-badge"><?php esc_html_e('LIVE', 'tradepress'); ?></span>
            </div>
        <?php endif;
    }

    /**
     * Display simple demo mode indicator for pages that just need basic demo notice
     * 
     * @param string $title Optional custom title
     * @param string $message Optional custom message
     * @param array $additional_info Optional array of additional information to display
     * @param string $position Position of notice: 'default', 'top-right', 'floating'
     * @return void
     * 
     * @version 1.2
     */
    public static function simple_demo_indicator($title = '', $message = '', $additional_info = array(), $position = 'default') {
        // Check if demo mode is active
        $is_demo_mode = function_exists('is_demo_mode') ? is_demo_mode() : true; // Default true for UI Library
        
        if (!$is_demo_mode) {
            return; // Don't show anything if not in demo mode
        }
        
        // Set default messages
        if (empty($title)) {
            $title = __('Development in Progress', 'tradepress');
        }
        if (empty($message)) {
            $message = __('This page contains placeholder data and functionality. Further implementation required.', 'tradepress');
        }
        
        $position_class = '';
        $container_style = '';
        
        switch ($position) {
            case 'top-right':
                $position_class = 'tradepress-demo-top-right';
                $container_style = 'position: fixed; top: 32px; right: 20px; z-index: 9999; max-width: 300px;';
                break;
            case 'floating':
                $position_class = 'tradepress-demo-floating';
                $container_style = 'position: absolute; top: 10px; right: 10px; z-index: 100; max-width: 280px;';
                break;
            default:
                $position_class = 'tradepress-demo-default';
                break;
        }
        
        ?>
        <div class="demo-indicator <?php echo esc_attr($position_class); ?>" <?php if ($container_style) echo 'style="' . esc_attr($container_style) . '"'; ?>>
            <div class="demo-icon dashicons dashicons-admin-tools"></div>
            <div class="demo-text">
                <h4><?php echo esc_html($title); ?></h4>
                <p><?php echo esc_html($message); ?></p>
                <?php if (!empty($additional_info)): ?>
                    <ul class="demo-additional-info">
                        <?php foreach ($additional_info as $info): ?>
                            <li><?php echo esc_html($info); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <span class="demo-badge"><?php esc_html_e('DEMO', 'tradepress'); ?></span>
        </div>
        <?php
    }
    
    /**
     * Display development in progress notice
     * 
     * @param string $feature_name Name of the feature being developed
     * @param array $progress_items Optional array of progress items
     * @param string $position Position of notice: 'default', 'top-right', 'floating'
     * @return void
     * 
     * @version 1.2
     */
    public static function development_progress_notice($feature_name = '', $progress_items = array(), $position = 'default') {
        // Disable all development progress notices
        return;
    }
    
    /**
     * Display development pending notice for incomplete features
     * 
     * @param string $feature_name Name of the feature
     * @param string $custom_message Custom message explaining what's needed
     * @return void
     * 
     * @version 1.0
     */
    public static function development_pending_notice($feature_name, $custom_message = '') {
        $title = sprintf(__('Development Pending: %s', 'tradepress'), $feature_name);
        
        if (empty($custom_message)) {
            $message = __('This feature requires additional development to connect with real data sources and calculation engines.', 'tradepress');
        } else {
            $message = $custom_message;
        }
        
        self::warning($title, $message, true);
    }
}

endif;