<?php
/**
 * TradePress Setup Wizard Page
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TradePress/Admin/Pages
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_Setup_Wizard_Page' ) ) :

/**
 * TradePress_Admin_Setup_Wizard_Page Class
 */
class TradePress_Admin_Setup_Wizard_Page {

    /** @var string Current Step */
    private $step = '';

    /** @var array Steps for the setup wizard */
    private $steps = array();

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menus' ) );
        add_action( 'admin_init', array( $this, 'setup_wizard' ) );
        add_action( 'wp_ajax_tradepress_test_apis', array( $this, 'ajax_test_apis' ) );
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        // Check if setup is complete
        $setup_complete = get_option('tradepress_setup_complete', false);
        
        if (!$setup_complete) {
            // Add to main plugin menu
            add_submenu_page(
                'TradePress',
                __('Setup Wizard', 'tradepress'),
                __('Setup Wizard', 'tradepress'),
                'manage_options',
                'tradepress-setup-wizard',
                array($this, 'output')
            );
        }
    }

    /**
     * Main output method following TradePress pattern
     */
    public function output() {
        echo '<div class="wrap">';
        echo '<h1>' . __('TradePress Setup Wizard', 'tradepress') . '</h1>';
        
        echo '<div class="tradepress-wizard-container">';
        $this->setup_wizard_steps();
        echo '<div class="tradepress-wizard-content">';
        $this->setup_wizard_content();
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Initialize wizard when on wizard page
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'tradepress-setup-wizard' !== $_GET['page'] ) {
            return;
        }
        
        // Enqueue setup wizard styles
        wp_enqueue_style( 'tradepress-setup', TRADEPRESS_PLUGIN_URL . '/assets/css/pages/setup.css', array( 'dashicons' ), TRADEPRESS_VERSION );
        wp_enqueue_style( 'tradepress-setup-wizard', TRADEPRESS_PLUGIN_URL . '/assets/css/setup-wizard.css', array(), TRADEPRESS_VERSION );
        
        $this->steps = array(
            'introduction' => array(
                'name'    => __( 'Introduction', 'tradepress' ),
                'view'    => array( $this, 'setup_introduction' ),
                'handler' => array( $this, 'setup_introduction_save' )
            ),
            'watchlist' => array(
                'name'    => __( 'Watchlist', 'tradepress' ),
                'view'    => array( $this, 'setup_watchlist' ),
                'handler' => array( $this, 'setup_watchlist_save' )
            ),
            'folders' => array(
                'name'    => __( 'Files', 'tradepress' ),
                'view'    => array( $this, 'setup_folders' ),
                'handler' => array( $this, 'setup_folders_save' )
            ),
            'database' => array(
                'name'    => __( 'Database', 'tradepress' ),
                'view'    => array( $this, 'setup_database' ),
                'handler' => array( $this, 'setup_database_save' )
            ),
            'improvement' => array(
                'name'    => __( 'Options', 'tradepress' ),
                'view'    => array( $this, 'setup_improvement' ),
                'handler' => array( $this, 'setup_improvement_save' )
            ),
            'ready' => array(
                'name'    => __( 'Ready!', 'tradepress' ),
                'view'    => array( $this, 'setup_ready' ),
                'handler' => ''
            )
        );
        
        $step_keys = array_keys( $this->steps );
        $this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : $step_keys[0];

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }
    }

    /**
     * Output the steps progress
     */
    public function setup_wizard_steps() {
        if (empty($this->steps)) return;
        
        $step_keys = array_keys( $this->steps );
        $current_index = array_search( $this->step, $step_keys, true );
        
        ?>
        <div class="tradepress-wizard-sidebar">
            <?php 
            $step_number = 1;
            foreach ( $this->steps as $step_key => $step ) : 
                $step_index = array_search( $step_key, $step_keys, true );
                $classes = array('wizard-step-tab');
                
                if ( $step_key === $this->step ) {
                    $classes[] = 'active';
                } elseif ( $current_index > $step_index ) {
                    $classes[] = 'completed';
                } elseif ( $current_index < $step_index ) {
                    $classes[] = 'disabled';
                }
            ?>
                <div class="<?php echo implode(' ', $classes); ?>">
                    <div class="step-number"><?php echo $step_number; ?></div>
                    <div class="step-label"><?php echo esc_html( $step['name'] ); ?></div>
                </div>
            <?php 
                $step_number++;
            endforeach; 
            ?>
        </div>
        <?php
    }

    /**
     * Output the content for the current step
     */
    public function setup_wizard_content() {
        if( !isset( $this->steps[ $this->step ]['view'] ) ) {
            echo '<h2>' . __( 'Invalid Step!', 'tradepress' ) . '</h2>';
            echo '<p>' . __( 'You have attempted to visit a setup step that does not exist.', 'tradepress' ) . '</p>';
        } else {
            call_user_func( $this->steps[ $this->step ]['view'] );
        }
    }

    /**
     * Get next step link
     */
    public function get_next_step_link() {
        $keys = array_keys( $this->steps );
        $current_index = array_search( $this->step, $keys, true );
        return add_query_arg( 'step', $keys[ $current_index + 1 ] );
    }

    /**
     * Introduction step
     */
    public function setup_introduction() {
        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/setup-wizard/partials/introduction.php';
    }

    /**
     * Save introduction step
     */
    public function setup_introduction_save() {
        check_admin_referer('tradepress-setup');
        
        // Save developer mode setting
        $developer_mode = isset($_POST['tradepress_developer_mode']) ? 1 : 0;
        update_option('tradepress_developer_mode', $developer_mode);
        
        // Save API keys
        if (isset($_POST['tradepress_alpaca_api_key'])) {
            update_option('tradepress_alpaca_api_key', sanitize_text_field($_POST['tradepress_alpaca_api_key']));
        }
        
        if (isset($_POST['tradepress_alpaca_secret_key'])) {
            update_option('tradepress_alpaca_secret_key', sanitize_text_field($_POST['tradepress_alpaca_secret_key']));
        }
        
        if (isset($_POST['tradepress_alpha_vantage_api_key'])) {
            update_option('tradepress_alpha_vantage_api_key', sanitize_text_field($_POST['tradepress_alpha_vantage_api_key']));
        }
        
        // Handle mass credentials input for developer mode
        if ($developer_mode && isset($_POST['tradepress_mass_credentials']) && !empty($_POST['tradepress_mass_credentials'])) {
            $mass_credentials = sanitize_textarea_field($_POST['tradepress_mass_credentials']);
            $lines = explode("\n", $mass_credentials);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $parts = explode(':', $line);
                if (count($parts) >= 2) {
                    $api_name = trim($parts[0]);
                    $api_key = trim($parts[1]);
                    $api_secret = isset($parts[2]) ? trim($parts[2]) : '';
                    
                    switch ($api_name) {
                        case 'alpaca':
                            update_option('tradepress_alpaca_api_key', $api_key);
                            if (!empty($api_secret)) {
                                update_option('tradepress_alpaca_secret_key', $api_secret);
                            }
                            break;
                        case 'alpha_vantage':
                            update_option('tradepress_alpha_vantage_api_key', $api_key);
                            break;
                    }
                }
            }
        }
        
        // Test APIs and store results
        $api_test_results = $this->perform_api_tests();
        set_transient('tradepress_api_test_results', $api_test_results, 300);
        
        wp_safe_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * Watchlist step
     */
    public function setup_watchlist() {
        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/setup-wizard/partials/watchlist.php';
    }

    /**
     * Save watchlist step
     */
    public function setup_watchlist_save() {
        check_admin_referer('tradepress-setup');
        wp_safe_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * Folders step
     */
    public function setup_folders() {
        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/setup-wizard/partials/folders.php';
    }

    /**
     * Save folders step
     */
    public function setup_folders_save() {
        check_admin_referer('tradepress-setup');
        
        $upload_dir = wp_upload_dir();
        $folders_to_create = array(
            $upload_dir['basedir'] . '/tradepress_uploads'
        );
        
        $results = array();
        foreach ($folders_to_create as $folder_path) {
            if (file_exists($folder_path)) {
                $results[] = '✅ Already exists: ' . $folder_path;
            } else {
                if (wp_mkdir_p($folder_path)) {
                    $results[] = '✅ Created: ' . $folder_path;
                } else {
                    $results[] = '❌ Failed to create: ' . $folder_path;
                }
            }
        }
        
        set_transient('tradepress_folder_results', $results, 300);
        
        wp_safe_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * Database step
     */
    public function setup_database() {
        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/setup-wizard/partials/database.php';
    }

    /**
     * Save database step
     */
    public function setup_database_save() {
        check_admin_referer('tradepress-setup');
        
        // Install/update core database tables
        require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/installation/tables-installation.php');
        $tables = new TradePress_Install_Tables();
        $tables->create_tables();
        
        // Store success message
        set_transient('tradepress_database_results', '✅ Database tables have been created successfully.', 300);
        
        wp_safe_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * Improvement step
     */
    public function setup_improvement() {
        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/setup-wizard/partials/improvement.php';
    }

    /**
     * Save improvement step
     */
    public function setup_improvement_save() {
        check_admin_referer('tradepress-setup');
        
        // Save improvement options
        if (isset($_POST['TradePress_install_samples']) && $_POST['TradePress_install_samples'] == 'yes') {
            // Install sample data if requested
            update_option('tradepress_install_samples', 'yes');
        }
        
        if (isset($_POST['TradePress_feedback_data'])) {
            update_option('TradePress_feedback_data', 1);
        } else {
            delete_option('TradePress_feedback_data');
        }
        
        if (isset($_POST['TradePress_feedback_prompt'])) {
            update_option('TradePress_feedback_prompt', 1);
        } else {
            delete_option('TradePress_feedback_prompt');
        }
        
        wp_safe_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * Ready step
     */
    public function setup_ready() {
        // Mark setup as complete when reaching ready step
        update_option('tradepress_setup_complete', true);
        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/setup-wizard/partials/ready.php';
    }

    /**
     * AJAX handler for API testing
     */
    public function ajax_test_apis() {
        check_ajax_referer('tradepress_api_test', 'nonce');
        wp_send_json(array('html' => 'API test placeholder'));
    }
    
    /**
     * Perform API tests
     */
    private function perform_api_tests() {
        $test_symbol = 'AAPL';
        
        $alpaca_key = get_option('tradepress_alpaca_api_key', '');
        $alpaca_secret = get_option('tradepress_alpaca_secret_key', '');
        $alpha_key = get_option('tradepress_alpha_vantage_api_key', '');
        
        $results = array();
        
        // Test Alpaca API
        if (!empty($alpaca_key) && !empty($alpaca_secret)) {
            $results['alpaca'] = $this->test_alpaca_api($test_symbol, $alpaca_key, $alpaca_secret);
        } else {
            $results['alpaca'] = array('status' => 'skipped', 'message' => 'API keys not configured');
        }
        
        // Test Alpha Vantage API
        if (!empty($alpha_key)) {
            $results['alpha_vantage'] = $this->test_alpha_vantage_api($test_symbol, $alpha_key);
        } else {
            $results['alpha_vantage'] = array('status' => 'skipped', 'message' => 'API key not configured');
        }
        
        return $this->format_api_test_results($test_symbol, $results);
    }
    
    private function test_alpaca_api($symbol, $key, $secret) {
        $url = 'https://paper-api.alpaca.markets/v2/assets/' . $symbol;
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'APCA-API-KEY-ID' => $key,
                'APCA-API-SECRET-KEY' => $secret
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            return array('status' => 'error', 'message' => $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code === 200) {
            $data = json_decode($body, true);
            return array('status' => 'success', 'data' => $data);
        } else {
            return array('status' => 'error', 'message' => 'HTTP ' . $code . ': ' . $body);
        }
    }
    
    private function test_alpha_vantage_api($symbol, $key) {
        $url = 'https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=' . $symbol . '&apikey=' . $key;
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return array('status' => 'error', 'message' => $response->get_error_message());
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code === 200) {
            $data = json_decode($body, true);
            if (isset($data['Global Quote'])) {
                return array('status' => 'success', 'data' => $data['Global Quote']);
            } else {
                return array('status' => 'error', 'message' => 'Invalid response format');
            }
        } else {
            return array('status' => 'error', 'message' => 'HTTP ' . $code);
        }
    }
    
    private function format_api_test_results($symbol, $results) {
        $html = '<div class="tp-notice tp-notice-info">';
        $html .= '<div class="tp-notice-icon">🔍</div>';
        $html .= '<div><strong>API Connection Test Results</strong><br>';
        $html .= '<small>Test Symbol: ' . esc_html($symbol) . '</small><br><br>';
        
        foreach ($results as $api => $result) {
            $api_name = $api === 'alpaca' ? 'Alpaca (Paper Trading)' : 'Alpha Vantage (Market Data)';
            
            $status_icon = $result['status'] === 'success' ? '✅' : ($result['status'] === 'error' ? '❌' : '⚠️');
            $html .= '<div style="margin: 8px 0; padding: 8px 12px; background: var(--tp-bg-tertiary); border-radius: 6px;">';
            $html .= '<strong>' . $api_name . ':</strong> ' . $status_icon . ' ' . ucfirst($result['status']);
            
            if ($result['status'] === 'success' && isset($result['data'])) {
                if ($api === 'alpaca' && isset($result['data']['name'])) {
                    $html .= '<br><small style="color: var(--tp-text-muted);">Asset Found: ' . esc_html($result['data']['name']) . '</small>';
                } elseif ($api === 'alpha_vantage' && isset($result['data']['05. price'])) {
                    $html .= '<br><small style="color: var(--tp-text-muted);">Current Price: $' . esc_html($result['data']['05. price']) . '</small>';
                }
            } elseif (isset($result['message'])) {
                $html .= '<br><small style="color: var(--tp-text-muted);">' . esc_html($result['message']) . '</small>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
}

endif;

// Initialize the class
new TradePress_Admin_Setup_Wizard_Page();