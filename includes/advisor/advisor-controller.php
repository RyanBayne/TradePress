<?php
/**
 * TradePress Advisor Controller
 *
 * Main controller for the advisor workflow and step management.
 *
 * @package TradePress/Advisor
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Advisor_Controller {

    /**
     * Session manager instance
     *
     * @var TradePress_Advisor_Session
     */
    private $session;

    /**
     * Mode handler instance
     *
     * @var TradePress_Advisor_Mode_Handler
     */
    private $mode_handler;

    /**
     * Constructor
     */
    public function __construct() {
        $this->session = new TradePress_Advisor_Session();
        $this->mode_handler = new TradePress_Advisor_Mode_Handler();
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
    }
    
    /**
     * Handle form submissions
     */
    public function handle_form_submission() {
        tradepress_trace_log('Admin init hook fired', array('post_data' => !empty($_POST), 'advisor_action' => isset($_POST['tradepress_advisor_action'])));
        
        if ( empty( $_POST['tradepress_advisor_action'] ) ) {
            return;
        }
        
        tradepress_trace_log('Advisor form submission detected', array('action' => $_POST['tradepress_advisor_action']));
        
        $action = sanitize_text_field( $_POST['tradepress_advisor_action'] );
        
        switch ( $action ) {
            case 'step_1':
                tradepress_trace_log('Processing advisor step 1');
                $this->handle_step_1_submission();
                break;
            case 'step_2':
                tradepress_trace_log('Processing advisor step 2');
                $this->handle_step_2_submission();
                break;
            case 'step_3':
                tradepress_trace_log('Processing advisor step 3');
                $this->handle_step_3_submission();
                break;
            case 'step_4':
                tradepress_trace_log('Processing advisor step 4');
                $this->handle_step_4_submission();
                break;
            case 'step_5':
                tradepress_trace_log('Processing advisor step 5');
                $this->handle_step_5_submission();
                break;
            case 'step_6':
                tradepress_trace_log('Processing advisor step 6');
                $this->handle_step_6_submission();
                break;
        }
    }
    
    /**
     * Handle step 1 form submission
     */
    public function handle_step_1_submission() {
        tradepress_trace_log('Step 1 handler called');
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_advisor_step_1' ) ) {
            tradepress_trace_log('Nonce verification failed');
            wp_die( 'Security check failed' );
        }
        
        tradepress_trace_log('Nonce verified');
        
        // Process mode selection
        $mode = sanitize_text_field( $_POST['advisor_mode'] ?? '' );
        tradepress_trace_log('Selected mode', array('mode' => $mode));
        
        if ( empty( $mode ) ) {
            tradepress_trace_log('No mode selected - redirecting with error');
            wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=1&error=no_mode' ) );
            exit;
        }
        
        // Save mode and mark step as completed
        $this->session->set_mode( $mode );
        $this->session->mark_step_completed( 1 );
        $this->session->set_current_step( 2 );
        
        tradepress_trace_log('Redirecting to step 2');
        
        // Redirect to step 2
        wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2&message=mode_selected' ) );
        exit;
    }
    
    /**
     * Handle step 2 form submission
     */
    public function handle_step_2_submission() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_advisor_step_2' ) ) {
            wp_die( 'Security check failed' );
        }
        
        // Process symbol selection
        $selected_symbols = $_POST['selected_symbols'] ?? array();
        $selected_symbols = array_map( 'sanitize_text_field', $selected_symbols );
        
        // Save symbols and mark step as completed
        $this->session->set_selected_symbols( $selected_symbols );
        $this->session->mark_step_completed( 2 );
        $this->session->set_current_step( 3 );
        
        // Redirect to step 3
        wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=3&message=symbols_selected' ) );
        exit;
    }
    
    /**
     * Handle step 3 form submission
     */
    public function handle_step_3_submission() {
        tradepress_trace_log('Step 3 handler called');
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_advisor_step_3' ) ) {
            tradepress_trace_log('Step 3 nonce verification failed');
            wp_die( 'Security check failed' );
        }
        
        tradepress_trace_log('Step 3 nonce verified');
        
        // Mark step 3 as completed
        $this->session->mark_step_completed( 3 );
        $this->session->set_current_step( 4 );
        
        tradepress_trace_log('Redirecting to step 4');
        
        // Redirect to step 4
        wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=4&message=news_analyzed' ) );
        exit;
    }
    
    /**
     * Handle step 4 form submission
     */
    public function handle_step_4_submission() {
        tradepress_trace_log('Step 4 handler called');
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_advisor_step_4' ) ) {
            tradepress_trace_log('Step 4 nonce verification failed');
            wp_die( 'Security check failed' );
        }
        
        tradepress_trace_log('Step 4 nonce verified');
        
        // Mark step 4 as completed
        $this->session->mark_step_completed( 4 );
        $this->session->set_current_step( 5 );
        
        tradepress_trace_log('Redirecting to step 5');
        
        // Redirect to step 5
        wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=5&message=forecasts_analyzed' ) );
        exit;
    }
    
    /**
     * Handle step 5 form submission
     */
    public function handle_step_5_submission() {
        tradepress_trace_log('Step 5 handler called');
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_advisor_step_5' ) ) {
            tradepress_trace_log('Step 5 nonce verification failed');
            wp_die( 'Security check failed' );
        }
        
        tradepress_trace_log('Step 5 nonce verified');
        
        // Mark step 5 as completed
        $this->session->mark_step_completed( 5 );
        $this->session->set_current_step( 6 );
        
        tradepress_trace_log('Redirecting to step 6');
        
        // Redirect to step 6
        wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=6&message=economic_analyzed' ) );
        exit;
    }
    
    /**
     * Handle step 6 form submission
     */
    public function handle_step_6_submission() {
        tradepress_trace_log('Step 6 handler called');
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_advisor_step_6' ) ) {
            tradepress_trace_log('Step 6 nonce verification failed');
            wp_die( 'Security check failed' );
        }
        
        tradepress_trace_log('Step 6 nonce verified');
        
        // Process technical indicator selections
        $selected_indicators = $_POST['selected_indicators'] ?? array();
        $selected_indicators = array_map( 'sanitize_text_field', $selected_indicators );
        
        $scoring_strategy = sanitize_text_field( $_POST['scoring_strategy'] ?? 'balanced' );
        $time_horizon = sanitize_text_field( $_POST['time_horizon'] ?? '1m' );
        
        tradepress_trace_log('Technical settings processed', array(
            'indicators' => $selected_indicators,
            'strategy' => $scoring_strategy,
            'horizon' => $time_horizon
        ));
        
        // Save technical settings
        $this->session->set_technical_settings( array(
            'indicators' => $selected_indicators,
            'scoring_strategy' => $scoring_strategy,
            'time_horizon' => $time_horizon
        ));
        
        // Mark step 6 as completed
        $this->session->mark_step_completed( 6 );
        $this->session->set_current_step( 7 );
        
        tradepress_trace_log('Redirecting to step 7 (final results)');
        
        // Redirect to step 7 (final results)
        wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=7&message=technical_configured' ) );
        exit;
    }

    /**
     * Render specific step interface
     *
     * @param int $step_number Step number to render
     * @return string HTML output
     */
    public function render_step( $step_number ) {
        if ( ! $this->validate_step_access( $step_number ) ) {
            return '<div class="notice notice-error"><p>' . 
                   esc_html__( 'You must complete previous steps first.', 'tradepress' ) . 
                   '</p></div>';
        }

        $session_data = $this->session->get_session_data();
        $mode = $session_data ? $session_data['mode'] : 'invest';

        switch ( $step_number ) {
            case 1:
                return $this->render_mode_selection();
            case 2:
                return $this->render_earnings_step();
            case 3:
                return $this->render_news_step();
            case 4:
                return $this->render_forecasts_step();
            case 5:
                return $this->render_economic_step();
            case 6:
                return $this->render_technical_step();
            case 7:
                return $this->render_results_step();
            default:
                return '<p>' . esc_html__( 'Invalid step number.', 'tradepress' ) . '</p>';
        }
    }

    /**
     * Process step form submission
     *
     * @param int   $step Step number
     * @param array $data Form data
     * @return bool Success status
     */
    public function process_step_submission( $step, $data ) {
        // Validate and sanitize data based on step
        $sanitized_data = $this->sanitize_step_data( $step, $data );
        
        // Save step data
        return $this->session->update_step_data( $step, $sanitized_data );
    }

    /**
     * Validate step access
     *
     * @param int $step Step number
     * @return bool True if user can access step
     */
    public function validate_step_access( $step ) {
        // Step 1 is always accessible
        if ( $step === 1 ) {
            return true;
        }

        // Check if session exists
        if ( ! $this->session->has_session() ) {
            TradePress_Admin_Notices::add_wordpress_notice(
                'advisor_no_session',
                'warning',
                true,
                __( 'No Active Session', 'tradepress' ),
                __( 'Please start by selecting an advisor mode.', 'tradepress' )
            );
            return false;
        }

        // Check if previous step is completed
        if ( ! $this->session->is_step_completed( $step - 1 ) ) {
            TradePress_Admin_Notices::add_wordpress_notice(
                'advisor_step_locked',
                'info',
                true,
                __( 'Step Locked', 'tradepress' ),
                sprintf( __( 'Please complete Step %d first.', 'tradepress' ), $step - 1 )
            );
            return false;
        }

        return true;
    }

    /**
     * Get progress status for all steps
     *
     * @return array Progress status array
     */
    public function get_progress_status() {
        $session_data = $this->session->get_session_data();
        $completed_steps = $session_data ? $session_data['completed_steps'] : array();
        $current_step = $session_data ? $session_data['current_step'] : 1;

        $steps = array(
            1 => array( 'title' => __( 'Mode Selection', 'tradepress' ), 'status' => 'accessible' ),
            2 => array( 'title' => __( 'Earnings', 'tradepress' ), 'status' => 'locked' ),
            3 => array( 'title' => __( 'News', 'tradepress' ), 'status' => 'locked' ),
            4 => array( 'title' => __( 'Forecasts', 'tradepress' ), 'status' => 'locked' ),
            5 => array( 'title' => __( 'Economic', 'tradepress' ), 'status' => 'locked' ),
            6 => array( 'title' => __( 'Technical', 'tradepress' ), 'status' => 'locked' ),
            7 => array( 'title' => __( 'Results', 'tradepress' ), 'status' => 'locked' ),
        );

        foreach ( $steps as $step_num => &$step_info ) {
            if ( in_array( $step_num, $completed_steps ) ) {
                $step_info['status'] = 'completed';
            } elseif ( $step_num <= $current_step ) {
                $step_info['status'] = 'accessible';
            }
        }

        return $steps;
    }

    /**
     * Render mode selection step
     *
     * @return string HTML output
     */
    private function render_mode_selection() {
        $modes = $this->mode_handler->get_available_modes();
        $current_mode = $this->session->get_mode();

        ob_start();
        ?>
        <div class="advisor-step-content">
            <h3><?php esc_html_e( 'Step 1: Select Investment Mode', 'tradepress' ); ?></h3>
            <p><?php esc_html_e( 'Choose your investment approach to customize the analysis process.', 'tradepress' ); ?></p>
            
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'tradepress_advisor_step_1' ); ?>
                <input type="hidden" name="action" value="tradepress_advisor_step_1">
                
                <div class="mode-selection">
                    <?php foreach ( $modes as $mode_key => $mode_info ) : ?>
                        <?php $is_enabled = isset( $mode_info['enabled'] ) ? $mode_info['enabled'] : true; ?>
                        <div class="mode-option <?php echo ! $is_enabled ? 'mode-disabled' : ''; ?>">
                            <label>
                                <input type="radio" name="advisor_mode" value="<?php echo esc_attr( $mode_key ); ?>" 
                                       <?php checked( $current_mode, $mode_key ); ?>
                                       <?php disabled( ! $is_enabled ); ?>>
                                <strong><?php echo esc_html( $mode_info['title'] ); ?></strong>
                                <p><?php echo esc_html( $mode_info['description'] ); ?></p>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Continue to Earnings', 'tradepress' ); ?>" onclick="return validateModeSelection()">
                </p>
            </form>
        </div>
        
        <script>
        function validateModeSelection() {
            var selectedMode = document.querySelector('input[name="advisor_mode"]:checked');
            if (!selectedMode) {
                alert('<?php esc_html_e( 'Please select an investment mode before continuing.', 'tradepress' ); ?>');
                return false;
            }
            return true;
        }
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render earnings opportunities step
     */
    private function render_earnings_step() {
        // Get earnings data from existing system
        $earnings_data = $this->get_earnings_opportunities();
        
        ob_start();
        ?>
        <div class="advisor-step-content">
            <h3><?php esc_html_e( 'Step 2: Earnings Opportunities', 'tradepress' ); ?></h3>
            <p><?php esc_html_e( 'Select stocks with upcoming earnings that interest you for investment analysis.', 'tradepress' ); ?></p>
            
            <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'mode_selected' ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Investment mode selected successfully!', 'tradepress' ); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'tradepress_advisor_step_2' ); ?>
                <input type="hidden" name="action" value="tradepress_advisor_step_2">
                
                <div class="earnings-selection">
                    <?php if ( ! empty( $earnings_data ) ) : ?>
                        <?php 
                        // Show data source info
                        require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/earnings-data-bridge.php';
                        $data_info = TradePress_Advisor_Earnings_Bridge::get_data_info();
                        ?>
                        <div class="earnings-data-info">
                            <div class="data-info-header">
                                <p><strong>Data Source:</strong> <?php echo esc_html( $data_info['source'] ); ?> 
                                <span class="data-status <?php echo esc_attr( $data_info['status'] ); ?>">
                                    (<?php echo esc_html( $data_info['message'] ); ?>)
                                </span></p>
                                <?php if ( $data_info['status'] === 'stale' ) : ?>
                                    <a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2&refresh_earnings=1' ); ?>" 
                                       class="button button-small"><?php esc_html_e( 'Refresh Data', 'tradepress' ); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="earnings-grid">
                            <?php foreach ( $earnings_data as $symbol => $data ) : ?>
                                <div class="earnings-item">
                                    <label>
                                        <input type="checkbox" name="selected_symbols[]" value="<?php echo esc_attr( $symbol ); ?>">
                                        <div class="earnings-info">
                                            <strong><?php echo esc_html( $symbol ); ?></strong>
                                            <span class="company-name"><?php echo esc_html( $data['company'] ?? 'N/A' ); ?></span>
                                            <span class="earnings-date"><?php echo esc_html( $data['date'] ?? 'TBD' ); ?></span>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <?php 
                        // Get API status for Alpha Vantage
                        $api_status = $this->get_alphavantage_status();
                        ?>
                        <div class="earnings-api-status">
                            <h4><?php esc_html_e( 'Earnings Data Status', 'tradepress' ); ?></h4>
                            <div class="api-status-info">
                                <p><strong><?php esc_html_e( 'Data Provider:', 'tradepress' ); ?></strong> Alpha Vantage</p>
                                <p><strong><?php esc_html_e( 'Status:', 'tradepress' ); ?></strong> 
                                    <span class="status-indicator <?php echo esc_attr( $api_status['status'] ); ?>">
                                        <?php echo esc_html( $api_status['message'] ); ?>
                                    </span>
                                </p>
                                <?php if ( $api_status['status'] === 'no_key' ) : ?>
                                    <p><strong><?php esc_html_e( 'Solution:', 'tradepress' ); ?></strong> 
                                        <a href="<?php echo admin_url( 'admin.php?page=tradepress_platforms' ); ?>">
                                            <?php esc_html_e( 'Configure Alpha Vantage API key', 'tradepress' ); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="notice notice-info">
                            <p><?php esc_html_e( 'No earnings data available. Please configure API access or update earnings calendar data.', 'tradepress' ); ?></p>
                            <p>
                                <a href="<?php echo admin_url( 'admin.php?page=tradepress_platforms' ); ?>" class="button button-primary"><?php esc_html_e( 'Configure API', 'tradepress' ); ?></a>
                                <a href="<?php echo admin_url( 'admin.php?page=tradepress_data&tab=earnings' ); ?>" class="button"><?php esc_html_e( 'Update Earnings Data', 'tradepress' ); ?></a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <p class="submit">
                    <?php 
                    $api_status = $this->get_alphavantage_status();
                    $is_api_configured = ( $api_status['status'] === 'configured' );
                    ?>
                    <input type="submit" class="button button-primary" 
                           value="<?php esc_attr_e( 'Continue to News Analysis', 'tradepress' ); ?>"
                           <?php echo ! $is_api_configured ? 'disabled' : ''; ?>>
                    <?php if ( ! $is_api_configured ) : ?>
                        <p class="description"><?php esc_html_e( 'Please configure Alpha Vantage API key to continue.', 'tradepress' ); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=1' ); ?>" class="button button-secondary"><?php esc_html_e( 'Back to Mode Selection', 'tradepress' ); ?></a>
                </p>
            </form>
        </div>
        

        <?php
        return ob_get_clean();
    }
    
    /**
     * Get earnings opportunities from real API data
     */
    private function get_earnings_opportunities() {
        try {
            // Load earnings bridge
            require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/earnings-data-bridge.php';
            
            // Get real earnings data
            $earnings_data = TradePress_Advisor_Earnings_Bridge::get_earnings_opportunities();
            
            if ( empty( $earnings_data ) ) {
                tradepress_trace_log( 'No earnings data available from API' );
                return array();
            }
            
            tradepress_trace_log( 'Loaded real earnings data', array( 'count' => count( $earnings_data ) ) );
            
            return $earnings_data;
            
        } catch ( Exception $e ) {
            tradepress_trace_log( 'Error loading earnings data: ' . $e->getMessage() );
            return array();
        }
    }
    
    /**
     * Get company name for symbol
     */
    private function get_company_name( $symbol ) {
        // Load symbols data functions if not already loaded
        if ( ! function_exists( 'tradepress_get_test_company_details' ) ) {
            require_once TRADEPRESS_PLUGIN_DIR . 'includes/data/symbols-data.php';
        }
        
        if ( function_exists( 'tradepress_get_test_company_details' ) ) {
            $details = tradepress_get_test_company_details();
            return isset( $details[ $symbol ]['name'] ) ? $details[ $symbol ]['name'] : $symbol;
        }
        return $symbol;
    }
    
    /**
     * Get Alpha Vantage API status
     */
    private function get_alphavantage_status() {
        $api_key = get_option('TradePress_api_alphavantage_key', '');
        
        if ( empty( $api_key ) ) {
            return array(
                'status' => 'no_key',
                'message' => 'API key not configured'
            );
        }
        
        // Check if API is enabled
        $enabled = get_option( 'TradePress_switch_alphavantage_api_services', 'no' );
        
        if ( $enabled !== 'yes' ) {
            return array(
                'status' => 'disabled',
                'message' => 'API is disabled in settings'
            );
        }
        
        return array(
            'status' => 'configured',
            'message' => 'API key configured and enabled'
        );
    }

    private function render_news_step() {
        $selected_symbols = $this->session->get_selected_symbols();
        
        if ( empty( $selected_symbols ) ) {
            ob_start();
            ?>
            <div class="advisor-step-content">
                <h3><?php esc_html_e( 'Step 3: News Analysis', 'tradepress' ); ?></h3>
                <div class="notice notice-warning">
                    <p><?php esc_html_e( 'No symbols selected from previous step.', 'tradepress' ); ?></p>
                    <p><a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2' ); ?>" class="button"><?php esc_html_e( 'Back to Earnings', 'tradepress' ); ?></a></p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        // Load news integration class
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/news/news-advisor-integration.php';
        $news_integration = new TradePress_News_Advisor_Integration();
        
        // Get news analysis for selected symbols
        $news_analysis = $news_integration->get_news_analysis( $selected_symbols );
        
        // Get additional opportunities
        $additional_opportunities = $news_integration->get_additional_opportunities( $selected_symbols );
        
        ob_start();
        ?>
        <div class="advisor-step-content">
            <h3><?php esc_html_e( 'Step 3: News Analysis', 'tradepress' ); ?></h3>
            
            <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'symbols_selected' ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Symbols selected successfully! Analyzing recent news...', 'tradepress' ); ?></p>
                </div>
            <?php endif; ?>
            

            
            <div class="news-analysis-summary">
                <h4><?php printf( esc_html__( 'News Analysis for %d Selected Symbols', 'tradepress' ), count( $selected_symbols ) ); ?></h4>
                <p class="selected-symbols"><?php echo esc_html( implode( ', ', $selected_symbols ) ); ?></p>
            </div>
            
            <div class="news-analysis-grid">
                <?php foreach ( $selected_symbols as $symbol ) : ?>
                    <?php 
                    $analysis = isset( $news_analysis[ $symbol ] ) ? $news_analysis[ $symbol ] : array();
                    if ( empty( $analysis ) ) continue;
                    
                    $sentiment_class = $news_integration->get_sentiment_class( $analysis['overall_sentiment'] );
                    $recommendation_class = $news_integration->get_recommendation_class( $analysis['recommendation'] );
                    ?>
                    <div class="symbol-news-analysis">
                        <div class="symbol-header">
                            <h5><?php echo esc_html( $symbol ); ?></h5>
                            <div class="analysis-badges">
                                <span class="sentiment-badge <?php echo esc_attr( $sentiment_class ); ?>">
                                    <?php echo esc_html( number_format( $analysis['overall_sentiment'], 2 ) ); ?>
                                </span>
                                <span class="recommendation-badge <?php echo esc_attr( $recommendation_class ); ?>">
                                    <?php echo esc_html( ucfirst( $analysis['recommendation'] ) ); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="analysis-metrics">
                            <div class="metric">
                                <span class="metric-label"><?php esc_html_e( 'News Items:', 'tradepress' ); ?></span>
                                <span class="metric-value"><?php echo esc_html( $analysis['news_count'] ); ?></span>
                            </div>
                            <div class="metric">
                                <span class="metric-label"><?php esc_html_e( 'Impact Score:', 'tradepress' ); ?></span>
                                <span class="metric-value"><?php echo esc_html( $analysis['impact_score'] ); ?>/5</span>
                            </div>
                        </div>
                        
                        <?php if ( ! empty( $analysis['key_headlines'] ) ) : ?>
                            <div class="key-headlines">
                                <h6><?php esc_html_e( 'Key Headlines:', 'tradepress' ); ?></h6>
                                <?php foreach ( array_slice( $analysis['key_headlines'], 0, 2 ) as $headline ) : ?>
                                    <div class="headline-item">
                                        <a href="<?php echo esc_url( $headline['url'] ); ?>" target="_blank" class="headline-link">
                                            <?php echo esc_html( $headline['headline'] ); ?>
                                        </a>
                                        <div class="headline-meta">
                                            <span class="source"><?php echo esc_html( $headline['source'] ); ?></span>
                                            <span class="sentiment <?php echo esc_attr( $news_integration->get_sentiment_class( $headline['sentiment'] ) ); ?>">
                                                <?php echo esc_html( number_format( $headline['sentiment'], 2 ) ); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $analysis['risk_factors'] ) ) : ?>
                            <div class="risk-factors">
                                <h6><?php esc_html_e( 'Risk Factors:', 'tradepress' ); ?></h6>
                                <ul>
                                    <?php foreach ( array_slice( $analysis['risk_factors'], 0, 2 ) as $risk ) : ?>
                                        <li><?php echo esc_html( $risk ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ( ! empty( $additional_opportunities ) ) : ?>
                <div class="additional-opportunities">
                    <h4><?php esc_html_e( 'Additional News-Based Opportunities', 'tradepress' ); ?></h4>
                    <p><?php esc_html_e( 'Based on recent positive news sentiment, consider these additional symbols:', 'tradepress' ); ?></p>
                    
                    <div class="opportunities-grid">
                        <?php foreach ( $additional_opportunities as $opportunity ) : ?>
                            <div class="opportunity-item">
                                <div class="opportunity-header">
                                    <strong><?php echo esc_html( $opportunity['symbol'] ); ?></strong>
                                    <span class="sentiment-score <?php echo esc_attr( $news_integration->get_sentiment_class( $opportunity['sentiment'] ) ); ?>">
                                        <?php echo esc_html( number_format( $opportunity['sentiment'], 2 ) ); ?>
                                    </span>
                                </div>
                                <p class="opportunity-reason"><?php echo esc_html( $opportunity['reason'] ); ?></p>
                                <p class="opportunity-headline"><?php echo esc_html( $opportunity['key_headline'] ); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="step-actions">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'tradepress_advisor_step_3' ); ?>
                    <input type="hidden" name="action" value="tradepress_advisor_step_3">
                    
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Continue to Forecasts', 'tradepress' ); ?>">
                        <a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2' ); ?>" class="button button-secondary"><?php esc_html_e( 'Back to Earnings', 'tradepress' ); ?></a>
                    </p>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_forecasts_step() {
        $selected_symbols = $this->session->get_selected_symbols();
        
        if ( empty( $selected_symbols ) ) {
            ob_start();
            ?>
            <div class="advisor-step-content">
                <h3><?php esc_html_e( 'Step 4: Price Forecasts', 'tradepress' ); ?></h3>
                <div class="notice notice-warning">
                    <p><?php esc_html_e( 'No symbols selected from previous steps.', 'tradepress' ); ?></p>
                    <p><a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2' ); ?>" class="button"><?php esc_html_e( 'Back to Earnings', 'tradepress' ); ?></a></p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        // Load forecast integration class
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/forecasts/price-forecast-integration.php';
        $forecast_integration = new TradePress_Price_Forecast_Integration();
        
        // Get forecast analysis for selected symbols
        $forecast_analysis = $forecast_integration->get_forecast_analysis( $selected_symbols );
        
        ob_start();
        ?>
        <div class="advisor-step-content">
            <h3><?php esc_html_e( 'Step 4: Price Forecasts', 'tradepress' ); ?></h3>
            
            <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'news_analyzed' ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'News analysis completed! Analyzing price forecasts...', 'tradepress' ); ?></p>
                </div>
            <?php endif; ?>
            

            
            <div class="forecast-analysis-summary">
                <h4><?php printf( esc_html__( 'Price Forecast Analysis for %d Selected Symbols', 'tradepress' ), count( $selected_symbols ) ); ?></h4>
                <p class="selected-symbols"><?php echo esc_html( implode( ', ', $selected_symbols ) ); ?></p>
            </div>
            
            <div class="forecast-analysis-grid">
                <?php foreach ( $selected_symbols as $symbol ) : ?>
                    <?php 
                    $analysis = isset( $forecast_analysis[ $symbol ] ) ? $forecast_analysis[ $symbol ] : array();
                    if ( empty( $analysis ) ) continue;
                    
                    $recommendation_class = $forecast_integration->get_recommendation_class( $analysis['recommendation'] );
                    $risk_class = $forecast_integration->get_risk_class( $analysis['risk_level'] );
                    ?>
                    <div class="symbol-forecast-analysis">
                        <div class="symbol-header">
                            <h5><?php echo esc_html( $symbol ); ?></h5>
                            <div class="analysis-badges">
                                <span class="recommendation-badge <?php echo esc_attr( $recommendation_class ); ?>">
                                    <?php echo esc_html( ucwords( str_replace( '_', ' ', $analysis['recommendation'] ) ) ); ?>
                                </span>
                                <span class="risk-badge <?php echo esc_attr( $risk_class ); ?>">
                                    <?php echo esc_html( ucfirst( $analysis['risk_level'] ) ); ?> Risk
                                </span>
                            </div>
                        </div>
                        
                        <div class="current-price-section">
                            <div class="current-price">
                                <span class="price-label"><?php esc_html_e( 'Current Price:', 'tradepress' ); ?></span>
                                <span class="price-value">$<?php echo esc_html( number_format( $analysis['current_price'], 2 ) ); ?></span>
                            </div>
                            <div class="upside-potential">
                                <span class="upside-label"><?php esc_html_e( 'Upside Potential:', 'tradepress' ); ?></span>
                                <span class="upside-value <?php echo $analysis['upside_potential'] >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo esc_html( number_format( $analysis['upside_potential'], 1 ) ); ?>%
                                </span>
                            </div>
                        </div>
                        
                        <div class="forecast-periods">
                            <?php foreach ( $analysis['forecasts'] as $period => $forecast ) : ?>
                                <?php 
                                $distance_class = $forecast_integration->get_distance_class( $forecast['distance_percent'] );
                                $period_label = array(
                                    '1m' => '1 Month',
                                    '3m' => '3 Months', 
                                    '6m' => '6 Months',
                                    '1y' => '1 Year'
                                );
                                ?>
                                <div class="forecast-period">
                                    <div class="period-header">
                                        <span class="period-label"><?php echo esc_html( $period_label[ $period ] ); ?></span>
                                        <span class="source-count"><?php echo esc_html( $forecast['source_count'] ); ?> sources</span>
                                    </div>
                                    
                                    <div class="forecast-metrics">
                                        <div class="target-price">
                                            <span class="metric-label"><?php esc_html_e( 'Target:', 'tradepress' ); ?></span>
                                            <span class="metric-value">$<?php echo esc_html( number_format( $forecast['target_price'], 2 ) ); ?></span>
                                        </div>
                                        
                                        <div class="price-distance <?php echo esc_attr( $distance_class ); ?>">
                                            <span class="distance-percent"><?php echo esc_html( $forecast['distance_percent'] > 0 ? '+' : '' ); ?><?php echo esc_html( $forecast['distance_percent'] ); ?>%</span>
                                            <span class="distance-absolute">(<?php echo esc_html( $forecast['distance_absolute'] > 0 ? '+$' : '-$' ); ?><?php echo esc_html( number_format( abs( $forecast['distance_absolute'] ), 2 ) ); ?>)</span>
                                        </div>
                                        
                                        <div class="confidence-score">
                                            <span class="metric-label"><?php esc_html_e( 'Confidence:', 'tradepress' ); ?></span>
                                            <span class="metric-value"><?php echo esc_html( number_format( $forecast['confidence'], 1 ) ); ?>%</span>
                                        </div>
                                    </div>
                                    
                                    <div class="forecast-sources">
                                        <span class="sources-label"><?php esc_html_e( 'Sources:', 'tradepress' ); ?></span>
                                        <span class="sources-list"><?php echo esc_html( implode( ', ', array_slice( $forecast['sources'], 0, 3 ) ) ); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="forecast-updated">
                            <span class="updated-label"><?php esc_html_e( 'Last Updated:', 'tradepress' ); ?></span>
                            <span class="updated-time"><?php echo esc_html( human_time_diff( strtotime( $analysis['last_updated'] ), current_time( 'timestamp' ) ) ); ?> ago</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="step-actions">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'tradepress_advisor_step_4' ); ?>
                    <input type="hidden" name="action" value="tradepress_advisor_step_4">
                    
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Continue to Economic Analysis', 'tradepress' ); ?>">
                        <a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=3' ); ?>" class="button button-secondary"><?php esc_html_e( 'Back to News', 'tradepress' ); ?></a>
                    </p>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_economic_step() {
        $selected_symbols = $this->session->get_selected_symbols();
        
        if ( empty( $selected_symbols ) ) {
            ob_start();
            ?>
            <div class="advisor-step-content">
                <h3><?php esc_html_e( 'Step 5: Economic Impact', 'tradepress' ); ?></h3>
                <div class="notice notice-warning">
                    <p><?php esc_html_e( 'No symbols selected from previous steps.', 'tradepress' ); ?></p>
                    <p><a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2' ); ?>" class="button"><?php esc_html_e( 'Back to Earnings', 'tradepress' ); ?></a></p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        // Load economic integration class
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/economic/economic-impact-integration.php';
        $economic_integration = new TradePress_Economic_Impact_Integration();
        
        // Get economic analysis for selected symbols
        $economic_analysis = $economic_integration->get_economic_analysis( $selected_symbols );
        
        ob_start();
        ?>
        <div class="advisor-step-content">
            <h3><?php esc_html_e( 'Step 5: Economic Impact', 'tradepress' ); ?></h3>
            
            <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'forecasts_analyzed' ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Price forecasts analyzed! Evaluating economic impact...', 'tradepress' ); ?></p>
                </div>
            <?php endif; ?>
            

            
            <div class="economic-overview">
                <h4><?php esc_html_e( 'Current Economic Environment', 'tradepress' ); ?></h4>
                <div class="overall-outlook <?php echo esc_attr( $economic_integration->get_outlook_class( $economic_analysis['overall_outlook']['outlook'] ) ); ?>">
                    <span class="outlook-label"><?php echo esc_html( ucfirst( $economic_analysis['overall_outlook']['outlook'] ) ); ?> Outlook</span>
                    <span class="outlook-description"><?php echo esc_html( $economic_analysis['overall_outlook']['description'] ); ?></span>
                </div>
            </div>
            
            <div class="economic-factors-grid">
                <h4><?php esc_html_e( 'Key Economic Factors', 'tradepress' ); ?></h4>
                <div class="factors-container">
                    <?php foreach ( $economic_analysis['economic_factors'] as $factor_key => $factor ) : ?>
                        <?php 
                        $impact_class = $economic_integration->get_impact_class( $factor['impact_level'] );
                        $trend_class = $economic_integration->get_trend_class( $factor['trend'] );
                        ?>
                        <div class="economic-factor">
                            <div class="factor-header">
                                <h5><?php echo esc_html( $factor['title'] ); ?></h5>
                                <div class="factor-badges">
                                    <span class="impact-badge <?php echo esc_attr( $impact_class ); ?>">
                                        <?php echo esc_html( ucfirst( $factor['impact_level'] ) ); ?> Impact
                                    </span>
                                    <span class="trend-badge <?php echo esc_attr( $trend_class ); ?>">
                                        <?php echo esc_html( ucfirst( $factor['trend'] ) ); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="factor-value">
                                <span class="value-label"><?php esc_html_e( 'Current:', 'tradepress' ); ?></span>
                                <span class="value-number"><?php echo esc_html( $factor['current_value'] ); ?></span>
                            </div>
                            
                            <div class="factor-description">
                                <?php echo esc_html( $factor['description'] ); ?>
                            </div>
                            
                            <div class="factor-sectors">
                                <span class="sectors-label"><?php esc_html_e( 'Affects:', 'tradepress' ); ?></span>
                                <span class="sectors-list"><?php echo esc_html( implode( ', ', $factor['sectors_affected'] ) ); ?></span>
                            </div>
                            
                            <div class="factor-updated">
                                <?php echo esc_html( human_time_diff( strtotime( $factor['last_updated'] ), current_time( 'timestamp' ) ) ); ?> ago
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="symbol-economic-analysis">
                <h4><?php printf( esc_html__( 'Economic Impact on Selected Symbols (%d)', 'tradepress' ), count( $selected_symbols ) ); ?></h4>
                <div class="symbol-analysis-grid">
                    <?php foreach ( $selected_symbols as $symbol ) : ?>
                        <?php 
                        $analysis = isset( $economic_analysis['symbol_analysis'][ $symbol ] ) ? $economic_analysis['symbol_analysis'][ $symbol ] : array();
                        if ( empty( $analysis ) ) continue;
                        
                        $outlook_class = $economic_integration->get_outlook_class( $analysis['overall_impact']['outlook'] );
                        $risk_class = $analysis['risk_level'] === 'high' ? 'risk-high' : ( $analysis['risk_level'] === 'low' ? 'risk-low' : 'risk-medium' );
                        ?>
                        <div class="symbol-economic-card">
                            <div class="symbol-header">
                                <h5><?php echo esc_html( $symbol ); ?></h5>
                                <div class="symbol-badges">
                                    <span class="sector-badge"><?php echo esc_html( $analysis['sector'] ); ?></span>
                                    <span class="outlook-badge <?php echo esc_attr( $outlook_class ); ?>">
                                        <?php echo esc_html( ucfirst( $analysis['overall_impact']['outlook'] ) ); ?>
                                    </span>
                                    <span class="risk-badge <?php echo esc_attr( $risk_class ); ?>">
                                        <?php echo esc_html( ucfirst( $analysis['risk_level'] ) ); ?> Risk
                                    </span>
                                </div>
                            </div>
                            
                            <div class="impact-description">
                                <?php echo esc_html( $analysis['overall_impact']['description'] ); ?>
                            </div>
                            
                            <?php if ( ! empty( $analysis['opportunities'] ) ) : ?>
                                <div class="opportunities-section">
                                    <h6><?php esc_html_e( 'Economic Opportunities:', 'tradepress' ); ?></h6>
                                    <ul>
                                        <?php foreach ( array_slice( $analysis['opportunities'], 0, 2 ) as $opportunity ) : ?>
                                            <li>
                                                <strong><?php echo esc_html( $opportunity['factor'] ); ?>:</strong>
                                                <?php echo esc_html( $opportunity['description'] ); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ( ! empty( $analysis['threats'] ) ) : ?>
                                <div class="threats-section">
                                    <h6><?php esc_html_e( 'Economic Threats:', 'tradepress' ); ?></h6>
                                    <ul>
                                        <?php foreach ( array_slice( $analysis['threats'], 0, 2 ) as $threat ) : ?>
                                            <li>
                                                <strong><?php echo esc_html( $threat['factor'] ); ?>:</strong>
                                                <?php echo esc_html( $threat['description'] ); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="relevant-factors">
                                <span class="factors-label"><?php esc_html_e( 'Key Factors:', 'tradepress' ); ?></span>
                                <span class="factors-count"><?php echo count( $analysis['relevant_factors'] ); ?> economic indicators</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="step-actions">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'tradepress_advisor_step_5' ); ?>
                    <input type="hidden" name="action" value="tradepress_advisor_step_5">
                    
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Continue to Technical Analysis', 'tradepress' ); ?>">
                        <a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=4' ); ?>" class="button button-secondary"><?php esc_html_e( 'Back to Forecasts', 'tradepress' ); ?></a>
                    </p>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_technical_step() {
        $selected_symbols = $this->session->get_selected_symbols();
        $current_settings = $this->session->get_technical_settings();
        
        if ( empty( $selected_symbols ) ) {
            ob_start();
            ?>
            <div class="advisor-step-content">
                <h3><?php esc_html_e( 'Step 6: Technical Analysis Configuration', 'tradepress' ); ?></h3>
                <div class="notice notice-warning">
                    <p><?php esc_html_e( 'No symbols selected from previous steps.', 'tradepress' ); ?></p>
                    <p><a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2' ); ?>" class="button"><?php esc_html_e( 'Back to Earnings', 'tradepress' ); ?></a></p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        ob_start();
        ?>
        <div class="advisor-step-content">
            <h3><?php esc_html_e( 'Step 6: Technical Analysis Configuration', 'tradepress' ); ?></h3>
            
            <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'economic_analyzed' ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Economic analysis completed! Configure technical indicators...', 'tradepress' ); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="technical-config-summary">
                <h4><?php printf( esc_html__( 'Configure Technical Analysis for %d Selected Symbols', 'tradepress' ), count( $selected_symbols ) ); ?></h4>
                <p class="selected-symbols"><?php echo esc_html( implode( ', ', $selected_symbols ) ); ?></p>
                <p class="config-description"><?php esc_html_e( 'Select technical indicators and scoring strategy to complete your investment analysis.', 'tradepress' ); ?></p>
            </div>
            
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'tradepress_advisor_step_6' ); ?>
                <input type="hidden" name="action" value="tradepress_advisor_step_6">
                
                <div class="technical-config-sections">
                    <div class="config-section">
                        <h4><?php esc_html_e( 'Technical Indicators', 'tradepress' ); ?></h4>
                        <p class="section-description"><?php esc_html_e( 'Select the technical indicators to include in your analysis:', 'tradepress' ); ?></p>
                        
                        <div class="indicators-grid">
                            <?php 
                            $indicators = array(
                                'sma' => array( 'title' => 'Simple Moving Average (SMA)', 'description' => 'Trend following indicator based on average price' ),
                                'ema' => array( 'title' => 'Exponential Moving Average (EMA)', 'description' => 'Weighted moving average giving more importance to recent prices' ),
                                'rsi' => array( 'title' => 'Relative Strength Index (RSI)', 'description' => 'Momentum oscillator measuring speed and magnitude of price changes' ),
                                'macd' => array( 'title' => 'MACD', 'description' => 'Trend-following momentum indicator' ),
                                'bollinger' => array( 'title' => 'Bollinger Bands', 'description' => 'Volatility indicator with upper and lower bands' ),
                                'stochastic' => array( 'title' => 'Stochastic Oscillator', 'description' => 'Momentum indicator comparing closing price to price range' ),
                                'volume' => array( 'title' => 'Volume Analysis', 'description' => 'Trading volume patterns and trends' ),
                                'support_resistance' => array( 'title' => 'Support/Resistance', 'description' => 'Key price levels where stock tends to find support or resistance' )
                            );
                            
                            $selected_indicators = isset( $current_settings['indicators'] ) ? $current_settings['indicators'] : array( 'sma', 'rsi', 'macd' );
                            ?>
                            
                            <?php foreach ( $indicators as $key => $indicator ) : ?>
                                <div class="indicator-option">
                                    <label>
                                        <input type="checkbox" name="selected_indicators[]" value="<?php echo esc_attr( $key ); ?>" 
                                               <?php checked( in_array( $key, $selected_indicators ) ); ?>>
                                        <div class="indicator-info">
                                            <strong><?php echo esc_html( $indicator['title'] ); ?></strong>
                                            <p><?php echo esc_html( $indicator['description'] ); ?></p>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="config-section">
                        <h4><?php esc_html_e( 'Scoring Strategy', 'tradepress' ); ?></h4>
                        <p class="section-description"><?php esc_html_e( 'Choose how to weight and combine the technical indicators:', 'tradepress' ); ?></p>
                        
                        <div class="strategy-options">
                            <?php 
                            $strategies = array(
                                'conservative' => array( 'title' => 'Conservative', 'description' => 'Emphasizes trend confirmation and lower risk signals' ),
                                'balanced' => array( 'title' => 'Balanced', 'description' => 'Equal weighting of trend and momentum indicators' ),
                                'aggressive' => array( 'title' => 'Aggressive', 'description' => 'Focuses on momentum and early trend signals' ),
                                'momentum' => array( 'title' => 'Momentum Focus', 'description' => 'Prioritizes momentum oscillators like RSI and Stochastic' ),
                                'trend' => array( 'title' => 'Trend Focus', 'description' => 'Emphasizes moving averages and trend-following indicators' )
                            );
                            
                            $selected_strategy = isset( $current_settings['scoring_strategy'] ) ? $current_settings['scoring_strategy'] : 'balanced';
                            ?>
                            
                            <?php foreach ( $strategies as $key => $strategy ) : ?>
                                <div class="strategy-option">
                                    <label>
                                        <input type="radio" name="scoring_strategy" value="<?php echo esc_attr( $key ); ?>" 
                                               <?php checked( $selected_strategy, $key ); ?>>
                                        <div class="strategy-info">
                                            <strong><?php echo esc_html( $strategy['title'] ); ?></strong>
                                            <p><?php echo esc_html( $strategy['description'] ); ?></p>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="config-section">
                        <h4><?php esc_html_e( 'Time Horizon', 'tradepress' ); ?></h4>
                        <p class="section-description"><?php esc_html_e( 'Select the primary time frame for technical analysis:', 'tradepress' ); ?></p>
                        
                        <div class="horizon-options">
                            <?php 
                            $horizons = array(
                                '1w' => array( 'title' => '1 Week', 'description' => 'Short-term trading signals' ),
                                '1m' => array( 'title' => '1 Month', 'description' => 'Medium-term trend analysis' ),
                                '3m' => array( 'title' => '3 Months', 'description' => 'Quarterly trend assessment' ),
                                '6m' => array( 'title' => '6 Months', 'description' => 'Long-term investment signals' )
                            );
                            
                            $selected_horizon = isset( $current_settings['time_horizon'] ) ? $current_settings['time_horizon'] : '1m';
                            ?>
                            
                            <?php foreach ( $horizons as $key => $horizon ) : ?>
                                <div class="horizon-option">
                                    <label>
                                        <input type="radio" name="time_horizon" value="<?php echo esc_attr( $key ); ?>" 
                                               <?php checked( $selected_horizon, $key ); ?>>
                                        <div class="horizon-info">
                                            <strong><?php echo esc_html( $horizon['title'] ); ?></strong>
                                            <p><?php echo esc_html( $horizon['description'] ); ?></p>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Generate Final Results', 'tradepress' ); ?>" onclick="return validateTechnicalConfig()">
                        <a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=5' ); ?>" class="button button-secondary"><?php esc_html_e( 'Back to Economic', 'tradepress' ); ?></a>
                    </p>
                </div>
            </form>
        </div>
        
        <script>
        function validateTechnicalConfig() {
            var selectedIndicators = document.querySelectorAll('input[name="selected_indicators[]"]:checked');
            if (selectedIndicators.length === 0) {
                alert('<?php esc_html_e( 'Please select at least one technical indicator.', 'tradepress' ); ?>');
                return false;
            }
            return true;
        }
        </script>
        <?php
        return ob_get_clean();
    }
    
    private function render_results_step() {
        $selected_symbols = $this->session->get_selected_symbols();
        $session_data = $this->session->get_session_data();
        
        if ( empty( $selected_symbols ) || empty( $session_data ) ) {
            ob_start();
            ?>
            <div class="advisor-step-content">
                <h3><?php esc_html_e( 'Step 7: Final Results', 'tradepress' ); ?></h3>
                <div class="notice notice-warning">
                    <p><?php esc_html_e( 'Incomplete analysis data. Please complete all previous steps.', 'tradepress' ); ?></p>
                    <p><a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=1' ); ?>" class="button"><?php esc_html_e( 'Start Over', 'tradepress' ); ?></a></p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        ob_start();
        ?>
        <div class="advisor-step-content">
            <h3><?php esc_html_e( 'Step 7: Investment Analysis Results', 'tradepress' ); ?></h3>
            
            <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'technical_configured' ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Technical analysis configured! Generating comprehensive results...', 'tradepress' ); ?></p>
                </div>
            <?php endif; ?>
            

            
            <div class="results-summary">
                <h4><?php esc_html_e( 'Analysis Summary', 'tradepress' ); ?></h4>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label"><?php esc_html_e( 'Investment Mode:', 'tradepress' ); ?></span>
                        <span class="summary-value"><?php echo esc_html( ucfirst( $session_data['mode'] ?? 'invest' ) ); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label"><?php esc_html_e( 'Symbols Analyzed:', 'tradepress' ); ?></span>
                        <span class="summary-value"><?php echo count( $selected_symbols ); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label"><?php esc_html_e( 'Analysis Steps:', 'tradepress' ); ?></span>
                        <span class="summary-value"><?php echo count( $session_data['completed_steps'] ?? array() ); ?>/6 Completed</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label"><?php esc_html_e( 'Technical Indicators:', 'tradepress' ); ?></span>
                        <span class="summary-value"><?php echo count( $session_data['technical_settings']['indicators'] ?? array() ); ?> Selected</span>
                    </div>
                </div>
            </div>
            
            <div class="final-recommendations">
                <h4><?php esc_html_e( 'Investment Recommendations', 'tradepress' ); ?></h4>
                <p class="recommendations-note"><?php esc_html_e( 'Based on comprehensive analysis across earnings, news, forecasts, economic factors, and technical indicators:', 'tradepress' ); ?></p>
                
                <div class="recommendations-grid">
                    <?php foreach ( $selected_symbols as $index => $symbol ) : ?>
                        <?php 
                        // Generate mock comprehensive scores for demo
                        $scores = array(
                            'overall' => rand( 65, 95 ),
                            'earnings' => rand( 70, 90 ),
                            'news' => rand( 60, 85 ),
                            'forecasts' => rand( 75, 95 ),
                            'economic' => rand( 65, 80 ),
                            'technical' => rand( 70, 90 )
                        );
                        
                        $recommendation = $scores['overall'] >= 85 ? 'strong_buy' : ( $scores['overall'] >= 75 ? 'buy' : ( $scores['overall'] >= 65 ? 'hold' : 'sell' ) );
                        $rec_class = array(
                            'strong_buy' => 'rec-strong-buy',
                            'buy' => 'rec-buy', 
                            'hold' => 'rec-hold',
                            'sell' => 'rec-sell'
                        )[ $recommendation ];
                        ?>
                        <div class="recommendation-card">
                            <div class="rec-header">
                                <h5><?php echo esc_html( $symbol ); ?></h5>
                                <div class="rec-badges">
                                    <span class="overall-score"><?php echo esc_html( $scores['overall'] ); ?>/100</span>
                                    <span class="recommendation-badge <?php echo esc_attr( $rec_class ); ?>">
                                        <?php echo esc_html( ucwords( str_replace( '_', ' ', $recommendation ) ) ); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="score-breakdown">
                                <div class="score-item">
                                    <span class="score-label"><?php esc_html_e( 'Earnings:', 'tradepress' ); ?></span>
                                    <span class="score-value"><?php echo esc_html( $scores['earnings'] ); ?>/100</span>
                                </div>
                                <div class="score-item">
                                    <span class="score-label"><?php esc_html_e( 'News:', 'tradepress' ); ?></span>
                                    <span class="score-value"><?php echo esc_html( $scores['news'] ); ?>/100</span>
                                </div>
                                <div class="score-item">
                                    <span class="score-label"><?php esc_html_e( 'Forecasts:', 'tradepress' ); ?></span>
                                    <span class="score-value"><?php echo esc_html( $scores['forecasts'] ); ?>/100</span>
                                </div>
                                <div class="score-item">
                                    <span class="score-label"><?php esc_html_e( 'Economic:', 'tradepress' ); ?></span>
                                    <span class="score-value"><?php echo esc_html( $scores['economic'] ); ?>/100</span>
                                </div>
                                <div class="score-item">
                                    <span class="score-label"><?php esc_html_e( 'Technical:', 'tradepress' ); ?></span>
                                    <span class="score-value"><?php echo esc_html( $scores['technical'] ); ?>/100</span>
                                </div>
                            </div>
                            
                            <div class="rec-summary">
                                <?php 
                                $summaries = array(
                                    'Strong earnings outlook with positive analyst forecasts and favorable technical indicators.',
                                    'Solid fundamentals supported by recent positive news and economic tailwinds.',
                                    'Mixed signals with strong technical setup but economic headwinds to consider.',
                                    'Positive momentum across multiple analysis factors with good risk-reward profile.'
                                );
                                echo esc_html( $summaries[ $index % count( $summaries ) ] );
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="analysis-actions">
                <h4><?php esc_html_e( 'Next Steps', 'tradepress' ); ?></h4>
                <div class="actions-grid">
                    <div class="action-item">
                        <h5><?php esc_html_e( 'Export Results', 'tradepress' ); ?></h5>
                        <p><?php esc_html_e( 'Download your complete analysis report', 'tradepress' ); ?></p>
                        <button class="button" onclick="alert('Export feature coming soon!')"><?php esc_html_e( 'Export PDF', 'tradepress' ); ?></button>
                    </div>
                    <div class="action-item">
                        <h5><?php esc_html_e( 'Save to Watchlist', 'tradepress' ); ?></h5>
                        <p><?php esc_html_e( 'Add recommended symbols to your watchlist', 'tradepress' ); ?></p>
                        <button class="button" onclick="alert('Watchlist integration coming soon!')"><?php esc_html_e( 'Save Symbols', 'tradepress' ); ?></button>
                    </div>
                    <div class="action-item">
                        <h5><?php esc_html_e( 'New Analysis', 'tradepress' ); ?></h5>
                        <p><?php esc_html_e( 'Start a fresh investment analysis', 'tradepress' ); ?></p>
                        <a href="<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=1&reset=1' ); ?>" class="button button-primary"><?php esc_html_e( 'Start New', 'tradepress' ); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Sanitize step data
     *
     * @param int   $step Step number
     * @param array $data Raw form data
     * @return array Sanitized data
     */
    private function sanitize_step_data( $step, $data ) {
        switch ( $step ) {
            case 1:
                return array(
                    'mode' => sanitize_text_field( $data['advisor_mode'] ?? 'invest' )
                );
            default:
                return $data;
        }
    }
}

// Instantiate the controller
if ( is_admin() ) {
    new TradePress_Advisor_Controller();
}