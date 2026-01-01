<?php
/**
 * TradePress Automation Admin Area
 *
 * @package TradePress/Admin/Views
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin_Automation_Page Class
 */
class TradePress_Admin_Automation_Page {
    /**
     * Current active tab.
     *
     * @var string
     */
    private $active_tab = 'dashboard';

    /**
     * Constructor.
     */
    public function __construct() {
        // Set the active tab based on URL parameter
        if (isset($_GET['tab']) && !empty($_GET['tab'])) {
            $this->active_tab = sanitize_text_field($_GET['tab']);
        }
        
        // Debug the active tab value
        echo "<!-- Active Tab: " . esc_html($this->active_tab) . " -->";
    }

    /**
     * Output the Automation area.
     */
    public static function output() {
        // Create an instance of the class instead of using static method
        $instance = new self();
        $instance->render_output();
    }

    /**
     * Render the output of the automation area.
     */
    public function render_output() {
        $tabs = $this->get_tabs();
        ?>
        <div class="wrap tradepress-admin">
            <h1>
                <?php 
                echo esc_html__('TradePress Automation', 'tradepress');
                if (isset($tabs[$this->active_tab])) {
                    echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
                    echo esc_html($tabs[$this->active_tab]);
                }
                ?>
            </h1>
            
            <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                <?php foreach ($tabs as $tab_id => $tab_name) : 
                    $active = ($this->active_tab === $tab_id) ? 'nav-tab-active' : '';
                    $url = admin_url('admin.php?page=tradepress_automation&tab=' . $tab_id);
                ?>
                    <a href="<?php echo esc_url($url); ?>" class="nav-tab <?php echo esc_attr($active); ?>"><?php echo esc_html($tab_name); ?></a>
                <?php endforeach; ?>
            </nav>
            
            <div class="tradepress-automation-content">
                <?php 
                    // Load the active tab content
                    $this->load_tab_content($this->active_tab);
                    
                    // Enqueue automation scripts
                    $this->enqueue_automation_scripts();
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get tabs for the automation area.
     *
     * @return array
     */
    public function get_tabs() {
        $tabs = array(
            'dashboard' => __( 'Dashboard', 'tradepress' ),
            'data_import' => __( 'Data Import', 'tradepress' ),
            'algorithm' => __( 'Algorithm', 'tradepress' ),
            'signals' => __( 'Signals', 'tradepress' ),
            'trading' => __( 'Trading', 'tradepress' ),
            'cron' => __( 'CRON Jobs', 'tradepress' ),
            'settings' => __( 'Settings', 'tradepress' ),
        );
        
        return apply_filters( 'tradepress_automation_tabs', $tabs );
    }

    /**
     * Load tab content based on the active tab.
     *
     * @param string $tab Tab to load.
     */
    private function load_tab_content( $tab ) {
        switch ( $tab ) {
            case 'dashboard':
                $this->dashboard_tab();
                break;
            case 'data_import':
                $this->data_import_tab();
                break;
            case 'algorithm':
                $this->algorithm_tab();
                break;
            case 'signals':
                $this->signals_tab();
                break;
            case 'trading':
                $this->trading_tab();
                break;
            case 'cron':
                $this->cron_tab();
                break;            
            case 'settings':
                $this->settings_tab();
                break;
            default:
                $this->dashboard_tab();
                break;
        }
    }

    /**
     * Dashboard tab content - centralized automation control
     */
    private function dashboard_tab() {
        // Get status of both background processes
        $data_import_status = get_option('tradepress_data_import_status', 'stopped');
        $data_import_start_time = get_option('tradepress_data_import_start_time', 0);
        $scoring_status = get_option('tradepress_scoring_process_status', 'stopped');
        $scoring_start_time = get_option('tradepress_scoring_process_start_time', 0);
        
        // Calculate runtimes
        $data_import_runtime = '00:00:00';
        if ($data_import_status === 'running' && $data_import_start_time) {
            $elapsed = current_time('timestamp') - $data_import_start_time;
            $hours = floor($elapsed / 3600);
            $minutes = floor(($elapsed % 3600) / 60);
            $seconds = $elapsed % 60;
            $data_import_runtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        $scoring_runtime = '00:00:00';
        if ($scoring_status === 'running' && $scoring_start_time) {
            $elapsed = current_time('timestamp') - $scoring_start_time;
            $hours = floor($elapsed / 3600);
            $minutes = floor(($elapsed % 3600) / 60);
            $seconds = $elapsed % 60;
            $scoring_runtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        // Get metrics
        $symbols_processed = get_option('tradepress_scoring_symbols_processed', 0);
        $scores_generated = get_option('tradepress_scoring_scores_generated', 0);
        $signals_generated = get_option('tradepress_scoring_signals_generated', 0);
        $rankings_updated = get_option('tradepress_scoring_rankings_updated', 0);
        ?>
        <div class="tab-content" id="dashboard">
            <div class="automation-section">
                <h3><?php esc_html_e('Automation Dashboard', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Control all trading automation components from this central dashboard.', 'tradepress'); ?></p>
                
                <div class="dashboard-controls">
                    <button class="button button-primary start-all-button" id="start-all-automation">
                        <?php esc_html_e('Start All Automation', 'tradepress'); ?>
                    </button>
                    <button class="button button-secondary stop-all-button" id="stop-all-automation">
                        <?php esc_html_e('Stop All Automation', 'tradepress'); ?>
                    </button>
                </div>
                
                <div class="components-grid">
                    <!-- Data Import Process Component -->
                    <div class="component-card">
                        <h4><?php esc_html_e('Data Import Process', 'tradepress'); ?></h4>
                        <div class="status-indicator <?php echo ($data_import_status === 'running') ? 'status-running' : 'status-stopped'; ?>">
                            <span class="status-dot"></span>
                            <span class="status-text">
                                <?php echo ($data_import_status === 'running') ? esc_html__('Running', 'tradepress') : esc_html__('Stopped', 'tradepress'); ?>
                            </span>
                        </div>
                        <div class="component-metrics">
                            <div class="metric-item">
                                <span class="metric-label"><?php esc_html_e('Runtime', 'tradepress'); ?></span>
                                <span class="metric-value" id="dashboard-data-import-runtime"><?php echo esc_html($data_import_runtime); ?></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label"><?php esc_html_e('Last Update', 'tradepress'); ?></span>
                                <span class="metric-value">
                                    <?php 
                                    $last_update = get_option('tradepress_data_import_last_run', 0);
                                    echo $last_update ? human_time_diff($last_update, current_time('timestamp')) . ' ago' : 'Never';
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="data-import-services">
                            <h5><?php esc_html_e('Active Data Services', 'tradepress'); ?></h5>
                            <ul class="service-list">
                                <li><span class="service-type"><?php esc_html_e('Symbol Data', 'tradepress'); ?></span> - <span class="service-source"><?php esc_html_e('Alpaca API', 'tradepress'); ?></span></li>
                                <li><span class="service-type"><?php esc_html_e('Price Data', 'tradepress'); ?></span> - <span class="service-source"><?php esc_html_e('Alpha Vantage', 'tradepress'); ?></span></li>
                                <li><span class="service-type"><?php esc_html_e('Earnings Calendar', 'tradepress'); ?></span> - <span class="service-source"><?php esc_html_e('Alpha Vantage', 'tradepress'); ?></span></li>
                                <li><span class="service-type"><?php esc_html_e('Company Info', 'tradepress'); ?></span> - <span class="service-source"><?php esc_html_e('Alpha Vantage', 'tradepress'); ?></span></li>
                            </ul>
                        </div>
                        <div class="component-controls">
                            <button class="button <?php echo ($data_import_status === 'running') ? 'button-secondary' : 'button-primary'; ?> toggle-component-button" 
                                    data-component="data_import" 
                                    data-action="<?php echo ($data_import_status === 'running') ? 'stop' : 'start'; ?>">
                                <?php echo ($data_import_status === 'running') ? esc_html__('Stop Data Import', 'tradepress') : esc_html__('Start Data Import', 'tradepress'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Scoring Algorithm Component -->
                    <div class="component-card">
                        <h4><?php esc_html_e('Scoring Algorithm', 'tradepress'); ?></h4>
                        <div class="status-indicator <?php echo ($scoring_status === 'running') ? 'status-running' : 'status-stopped'; ?>">
                            <span class="status-dot"></span>
                            <span class="status-text">
                                <?php echo ($scoring_status === 'running') ? esc_html__('Running', 'tradepress') : esc_html__('Stopped', 'tradepress'); ?>
                            </span>
                        </div>
                        <div class="component-metrics">
                            <div class="metric-item">
                                <span class="metric-label"><?php esc_html_e('Runtime', 'tradepress'); ?></span>
                                <span class="metric-value" id="dashboard-scoring-runtime"><?php echo esc_html($scoring_runtime); ?></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label"><?php esc_html_e('Symbols Processed', 'tradepress'); ?></span>
                                <span class="metric-value" id="dashboard-symbols-processed"><?php echo esc_html($symbols_processed); ?></span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label"><?php esc_html_e('Scores Generated', 'tradepress'); ?></span>
                                <span class="metric-value" id="dashboard-scores-generated"><?php echo esc_html($scores_generated); ?></span>
                            </div>
                        </div>
                        <div class="component-controls">
                            <button class="button <?php echo ($scoring_status === 'running') ? 'button-secondary' : 'button-primary'; ?> toggle-component-button" 
                                    data-component="scoring" 
                                    data-action="<?php echo ($scoring_status === 'running') ? 'stop' : 'start'; ?>">
                                <?php echo ($scoring_status === 'running') ? esc_html__('Stop Scoring', 'tradepress') : esc_html__('Start Scoring', 'tradepress'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Process Coordination Status -->
                    <div class="component-card coordination-card">
                        <h4><?php esc_html_e('Process Coordination', 'tradepress'); ?></h4>
                        <div class="coordination-status">
                            <?php 
                            $both_running = ($data_import_status === 'running' && $scoring_status === 'running');
                            $coordination_class = $both_running ? 'status-coordinated' : 'status-independent';
                            $coordination_text = $both_running ? __('Coordinated', 'tradepress') : __('Independent', 'tradepress');
                            ?>
                            <div class="status-indicator <?php echo esc_attr($coordination_class); ?>">
                                <span class="status-dot"></span>
                                <span class="status-text"><?php echo esc_html($coordination_text); ?></span>
                            </div>
                        </div>
                        <div class="component-metrics">
                            <div class="metric-item">
                                <span class="metric-label"><?php esc_html_e('Data Freshness', 'tradepress'); ?></span>
                                <span class="metric-value">
                                    <?php 
                                    $data_age = get_option('tradepress_data_import_last_run', 0);
                                    if ($data_age) {
                                        $age_minutes = (current_time('timestamp') - $data_age) / 60;
                                        if ($age_minutes < 5) {
                                            echo '<span class="freshness-good">Fresh</span>';
                                        } elseif ($age_minutes < 30) {
                                            echo '<span class="freshness-ok">Recent</span>';
                                        } else {
                                            echo '<span class="freshness-stale">Stale</span>';
                                        }
                                    } else {
                                        echo '<span class="freshness-none">No Data</span>';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="metric-item">
                                <span class="metric-label"><?php esc_html_e('Process Health', 'tradepress'); ?></span>
                                <span class="metric-value">
                                    <?php 
                                    $health_score = 0;
                                    if ($data_import_status === 'running') $health_score += 50;
                                    if ($scoring_status === 'running') $health_score += 50;
                                    echo esc_html($health_score . '%');
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="component-controls">
                            <button class="button button-primary start-all-processes" id="start-all-processes">
                                <?php esc_html_e('Start Both Processes', 'tradepress'); ?>
                            </button>
                            <button class="button button-secondary stop-all-processes" id="stop-all-processes">
                                <?php esc_html_e('Stop Both Processes', 'tradepress'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            

        </div>
        <?php
    }
    
    /**
     * Data Import tab content
     */
    private function data_import_tab() {
        // Get data import status
        $import_status = get_option('tradepress_data_import_status', 'stopped');
        $start_time = get_option('tradepress_data_import_start_time', 0);
        $last_run = get_option('tradepress_data_import_last_run', 0);
        
        // Calculate runtime
        $runtime = '00:00:00';
        if ($import_status === 'running' && $start_time) {
            $elapsed = current_time('timestamp') - $start_time;
            $hours = floor($elapsed / 3600);
            $minutes = floor(($elapsed % 3600) / 60);
            $seconds = $elapsed % 60;
            $runtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        // Get data freshness
        $earnings_last_update = get_option('tradepress_earnings_last_update', 0);
        $market_status_last_update = get_option('tradepress_market_status_last_update', 0);
        
        $status_class = ($import_status === 'running') ? 'status-running' : 'status-stopped';
        $status_text = ($import_status === 'running') ? __('Running', 'tradepress') : __('Stopped', 'tradepress');
        ?>
        <div class="tab-content" id="data-import">
            <div class="automation-section">
                <h3><?php esc_html_e('Background Data Import', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Control and monitor the background data import process that fetches API data continuously.', 'tradepress'); ?></p>
                
                <div class="status-card">
                    <div class="status-indicator <?php echo esc_attr($status_class); ?>">
                        <span class="status-dot"></span>
                        <span class="status-text"><?php echo esc_html($status_text); ?></span>
                    </div>
                    
                    <div class="runtime-display">
                        <span class="runtime-label"><?php esc_html_e('Runtime:', 'tradepress'); ?></span>
                        <span id="data-import-runtime"><?php echo esc_html($runtime); ?></span>
                    </div>
                    
                    <div class="status-actions">
                        <button class="button button-primary start-data-import-button" 
                                data-action="<?php echo ($import_status === 'running') ? 'stop' : 'start'; ?>">
                            <?php echo ($import_status === 'running') ? esc_html__('Stop Data Import', 'tradepress') : esc_html__('Start Data Import', 'tradepress'); ?>
                        </button>
                        
                        <select id="import-type-select">
                            <option value="all"><?php esc_html_e('All Data Types', 'tradepress'); ?></option>
                            <option value="earnings"><?php esc_html_e('Earnings Calendar Only', 'tradepress'); ?></option>
                            <option value="prices"><?php esc_html_e('Price Data Only', 'tradepress'); ?></option>
                            <option value="market_status"><?php esc_html_e('Market Status Only', 'tradepress'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('Available Data Import Services', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Data import services ready and configured for automated updates.', 'tradepress'); ?></p>
                
                <div class="data-services-grid">
                    <div class="service-item active">
                        <span class="service-status">✅</span>
                        <span class="service-type"><?php esc_html_e('Symbol Data', 'tradepress'); ?></span>
                        <span class="service-source"><?php esc_html_e('Alpaca API', 'tradepress'); ?></span>
                    </div>
                    <div class="service-item active">
                        <span class="service-status">✅</span>
                        <span class="service-type"><?php esc_html_e('Price Data', 'tradepress'); ?></span>
                        <span class="service-source"><?php esc_html_e('Alpha Vantage', 'tradepress'); ?></span>
                    </div>
                    <div class="service-item active">
                        <span class="service-status">✅</span>
                        <span class="service-type"><?php esc_html_e('Earnings Calendar', 'tradepress'); ?></span>
                        <span class="service-source"><?php esc_html_e('Alpha Vantage', 'tradepress'); ?></span>
                    </div>
                    <div class="service-item active">
                        <span class="service-status">✅</span>
                        <span class="service-type"><?php esc_html_e('Company Info', 'tradepress'); ?></span>
                        <span class="service-source"><?php esc_html_e('Alpha Vantage', 'tradepress'); ?></span>
                    </div>
                    <div class="service-item pending">
                        <span class="service-status">⏳</span>
                        <span class="service-type"><?php esc_html_e('News Data', 'tradepress'); ?></span>
                        <span class="service-source"><?php esc_html_e('Planned', 'tradepress'); ?></span>
                    </div>
                    <div class="service-item pending">
                        <span class="service-status">⏳</span>
                        <span class="service-type"><?php esc_html_e('Social Sentiment', 'tradepress'); ?></span>
                        <span class="service-source"><?php esc_html_e('Planned', 'tradepress'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('Data Freshness', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Monitor when different data types were last updated.', 'tradepress'); ?></p>
                
                <div class="data-freshness-grid">
                    <div class="freshness-item">
                        <span class="freshness-label"><?php esc_html_e('Earnings Calendar', 'tradepress'); ?></span>
                        <span class="freshness-value">
                            <?php 
                            if ($earnings_last_update) {
                                echo date('Y-m-d H:i:s', $earnings_last_update);
                                echo '<br><small>(' . human_time_diff($earnings_last_update, current_time('timestamp')) . ' ago)</small>';
                            } else {
                                esc_html_e('Never updated', 'tradepress');
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="freshness-item">
                        <span class="freshness-label"><?php esc_html_e('Market Status', 'tradepress'); ?></span>
                        <span class="freshness-value">
                            <?php 
                            if ($market_status_last_update) {
                                echo date('Y-m-d H:i:s', $market_status_last_update);
                                echo '<br><small>(' . human_time_diff($market_status_last_update, current_time('timestamp')) . ' ago)</small>';
                            } else {
                                esc_html_e('Never updated', 'tradepress');
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="freshness-item">
                        <span class="freshness-label"><?php esc_html_e('Last Import Run', 'tradepress'); ?></span>
                        <span class="freshness-value">
                            <?php 
                            if ($last_run) {
                                echo date('Y-m-d H:i:s', $last_run);
                                echo '<br><small>(' . human_time_diff($last_run, current_time('timestamp')) . ' ago)</small>';
                            } else {
                                esc_html_e('Never run', 'tradepress');
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Algorithm tab content
     */
    private function algorithm_tab() {
        // Get algorithm status
        $is_running = TradePress_Admin_Automation_Controller::is_algorithm_running();
        $runtime = TradePress_Admin_Automation_Controller::get_algorithm_runtime();
        $status_class = $is_running ? 'status-running' : 'status-stopped';
        $status_text = $is_running ? __('Running', 'tradepress') : __('Stopped', 'tradepress');
        $button_text = $is_running ? __('Stop Algorithm', 'tradepress') : __('Start Algorithm', 'tradepress');
        $button_class = $is_running ? 'button-stop' : 'button-start';
        
        // Get metrics
        $symbols_processed = get_option('tradepress_symbols_processed', 0);
        $scores_generated = get_option('tradepress_scores_generated', 0);
        $trade_signals = get_option('tradepress_trade_signals', 0);
        ?>
        <div class="tab-content" id="algorithm">
            <div class="automation-section">
                <h3><?php esc_html_e('Algorithm Status', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Control and monitor the scoring algorithm.', 'tradepress'); ?></p>
                
                <div class="status-card">
                    <div class="status-indicator <?php echo esc_attr($status_class); ?>">
                        <span class="status-dot"></span>
                        <span class="status-text"><?php echo esc_html($status_text); ?></span>
                    </div>
                    
                    <div class="runtime-display">
                        <span class="runtime-label"><?php esc_html_e('Runtime:', 'tradepress'); ?></span>
                        <span id="algorithm-runtime"><?php echo esc_html($runtime); ?></span>
                    </div>
                    
                    <div class="status-actions">
                        <button class="button button-primary start-algorithm-button <?php echo esc_attr($button_class); ?>"
                                data-action="<?php echo $is_running ? 'stop' : 'start'; ?>">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('Algorithm Metrics', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Real-time statistics from the algorithm.', 'tradepress'); ?></p>
                
                <div class="metrics-grid">
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Symbols Processed', 'tradepress'); ?></span>
                        <span id="symbols-processed" class="metric-value"><?php echo esc_html($symbols_processed); ?></span>
                    </div>
                    
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Scores Generated', 'tradepress'); ?></span>
                        <span id="scores-generated" class="metric-value"><?php echo esc_html($scores_generated); ?></span>
                    </div>
                    
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Trade Signals', 'tradepress'); ?></span>
                        <span id="trade-signals" class="metric-value"><?php echo esc_html($trade_signals); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Signals tab content
     */
    private function signals_tab() {
        // Get signals status
        $is_running = TradePress_Admin_Automation_Controller::is_signals_running();
        $runtime = TradePress_Admin_Automation_Controller::get_signals_runtime();
        $status_class = $is_running ? 'status-running' : 'status-stopped';
        $status_text = $is_running ? __('Running', 'tradepress') : __('Stopped', 'tradepress');
        $button_text = $is_running ? __('Stop Signals', 'tradepress') : __('Start Signals', 'tradepress');
        $button_class = $is_running ? 'button-stop' : 'button-start';
        
        // Get metrics
        $signals_generated = get_option('tradepress_signals_generated', 0);
        $signals_processed = get_option('tradepress_signals_processed', 0);
        $active_signals = get_option('tradepress_active_signals', 0);
        ?>
        <div class="tab-content" id="signals">
            <div class="automation-section">
                <h3><?php esc_html_e('Trading Signals Status', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Control and monitor the trading signals generation system.', 'tradepress'); ?></p>
                
                <div class="status-card">
                    <div class="status-indicator <?php echo esc_attr($status_class); ?>">
                        <span class="status-dot"></span>
                        <span class="status-text"><?php echo esc_html($status_text); ?></span>
                    </div>
                    
                    <div class="runtime-display">
                        <span class="runtime-label"><?php esc_html_e('Runtime:', 'tradepress'); ?></span>
                        <span id="signals-runtime"><?php echo esc_html($runtime); ?></span>
                    </div>
                    
                    <div class="status-actions">
                        <button class="button button-primary start-signals-button <?php echo esc_attr($button_class); ?>"
                                data-action="<?php echo $is_running ? 'stop' : 'start'; ?>">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('Signals Metrics', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Real-time statistics from the signals system.', 'tradepress'); ?></p>
                
                <div class="metrics-grid">
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Signals Generated', 'tradepress'); ?></span>
                        <span id="signals-generated" class="metric-value"><?php echo esc_html($signals_generated); ?></span>
                    </div>
                    
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Signals Processed', 'tradepress'); ?></span>
                        <span id="signals-processed" class="metric-value"><?php echo esc_html($signals_processed); ?></span>
                    </div>
                    
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Active Signals', 'tradepress'); ?></span>
                        <span id="active-signals" class="metric-value"><?php echo esc_html($active_signals); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Trading tab content
     */
    private function trading_tab() {
        // Get trading status
        $is_running = TradePress_Admin_Automation_Controller::is_trading_running();
        $runtime = TradePress_Admin_Automation_Controller::get_trading_runtime();
        $status_class = $is_running ? 'status-running' : 'status-stopped';
        $status_text = $is_running ? __('Running', 'tradepress') : __('Stopped', 'tradepress');
        $button_text = $is_running ? __('Stop Trading', 'tradepress') : __('Start Trading', 'tradepress');
        $button_class = $is_running ? 'button-stop' : 'button-start';
        
        // Get metrics
        $trades_executed = get_option('tradepress_trades_executed', 0);
        $successful_trades = get_option('tradepress_successful_trades', 0);
        $total_profit_loss = get_option('tradepress_total_profit_loss', 0);
        ?>
        <div class="tab-content" id="trading">
            <div class="automation-section">
                <h3><?php esc_html_e('Trading Bot Status', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Control and monitor the automated trading system.', 'tradepress'); ?></p>
                
                <div class="status-card">
                    <div class="status-indicator <?php echo esc_attr($status_class); ?>">
                        <span class="status-dot"></span>
                        <span class="status-text"><?php echo esc_html($status_text); ?></span>
                    </div>
                    
                    <div class="runtime-display">
                        <span class="runtime-label"><?php esc_html_e('Runtime:', 'tradepress'); ?></span>
                        <span id="trading-runtime"><?php echo esc_html($runtime); ?></span>
                    </div>
                    
                    <div class="status-actions">
                        <button class="button button-primary start-trading-button <?php echo esc_attr($button_class); ?>"
                                data-action="<?php echo $is_running ? 'stop' : 'start'; ?>">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('Trading Metrics', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Real-time statistics from the trading system.', 'tradepress'); ?></p>
                
                <div class="metrics-grid">
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Trades Executed', 'tradepress'); ?></span>
                        <span id="trades-executed" class="metric-value"><?php echo esc_html($trades_executed); ?></span>
                    </div>
                    
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Successful Trades', 'tradepress'); ?></span>
                        <span id="successful-trades" class="metric-value"><?php echo esc_html($successful_trades); ?></span>
                    </div>
                    
                    <div class="metric-item">
                        <span class="metric-label"><?php esc_html_e('Total P&L', 'tradepress'); ?></span>
                        <span id="total-profit-loss" class="metric-value"><?php echo esc_html($total_profit_loss); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * CRON tab content
     */
    private function cron_tab() {
        // Get WordPress cron array
        $cron_jobs = _get_cron_array();
        $schedules = wp_get_schedules();
        $now = time();
        
        // Filter for TradePress specific jobs
        $tradepress_jobs = array();
        if (!empty($cron_jobs)) {
            foreach ($cron_jobs as $timestamp => $jobs) {
                foreach ($jobs as $hook => $job_data) {
                    if (strpos($hook, 'tradepress') !== false) {
                        foreach ($job_data as $hash => $data) {
                            $schedule = isset($data['schedule']) ? $data['schedule'] : false;
                            $tradepress_jobs[] = array(
                                'timestamp' => $timestamp,
                                'hook' => $hook,
                                'schedule' => $schedule,
                                'interval' => isset($data['interval']) ? $data['interval'] : 0,
                                'args' => isset($data['args']) ? $data['args'] : array(),
                                'human_time' => human_time_diff($now, $timestamp),
                                'schedule_display' => $schedule ? (isset($schedules[$schedule]['display']) ? $schedules[$schedule]['display'] : $schedule) : __('One-time', 'tradepress'),
                                'next_run' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp)
                            );
                        }
                    }
                }
            }
        }
        
        // Sort jobs by next run time (timestamp)
        usort($tradepress_jobs, function($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });
        
        // Get TradePress CRON settings
        $earnings_cron_enabled = get_option('tradepress_earnings_cron_enabled', false);
        $earnings_cron_interval = get_option('tradepress_earnings_cron_interval', 'daily');
        $earnings_last_updated = get_option('tradepress_earnings_last_updated', 0);
        $earnings_data_source = get_option('tradepress_earnings_data_source', 'Not Available');
        
        // List of standard CRON jobs (from AUTOMATION.md)
        $standard_cron_jobs = array(
            array(
                'name' => __('Earnings Calendar CRON Job', 'tradepress'),
                'desc' => __('Fetches upcoming earnings events', 'tradepress'),
                'frequency' => __('Daily', 'tradepress'),
                'notes' => __('Already implemented', 'tradepress'),
            ),
            array(
                'name' => __('Market Status CRON Job', 'tradepress'),
                'desc' => __('Updates market open/close status', 'tradepress'),
                'frequency' => __('Every 5-15 min', 'tradepress'),
                'notes' => __('Trading hours, holidays', 'tradepress'),
            ),
            array(
                'name' => __('Symbol List CRON Job', 'tradepress'),
                'desc' => __('Updates list of tradable symbols/instruments', 'tradepress'),
                'frequency' => __('Daily or Weekly', 'tradepress'),
                'notes' => __('Trading212, Alpaca, Polygon, etc.', 'tradepress'),
            ),
            array(
                'name' => __('Price Quotes CRON Job', 'tradepress'),
                'desc' => __('Updates latest price quotes for watchlists', 'tradepress'),
                'frequency' => __('Every 1-5 min', 'tradepress'),
                'notes' => __('Rate limit sensitive', 'tradepress'),
            ),
            array(
                'name' => __('Historical Prices CRON Job', 'tradepress'),
                'desc' => __('Updates OHLCV historical price data', 'tradepress'),
                'frequency' => __('Hourly or Daily', 'tradepress'),
                'notes' => __('For backtesting, charts', 'tradepress'),
            ),
            array(
                'name' => __('Fundamentals CRON Job', 'tradepress'),
                'desc' => __('Updates company fundamentals', 'tradepress'),
                'frequency' => __('Daily or Weekly', 'tradepress'),
                'notes' => __('Earnings, ratios, etc.', 'tradepress'),
            ),
            array(
                'name' => __('News Headlines CRON Job', 'tradepress'),
                'desc' => __('Fetches latest news for tracked symbols', 'tradepress'),
                'frequency' => __('Every 15-30 min', 'tradepress'),
                'notes' => __('News APIs, sentiment', 'tradepress'),
            ),
            array(
                'name' => __('Analyst Ratings CRON Job', 'tradepress'),
                'desc' => __('Updates analyst ratings/targets', 'tradepress'),
                'frequency' => __('Daily', 'tradepress'),
                'notes' => __('Where supported', 'tradepress'),
            ),
            array(
                'name' => __('Dividends CRON Job', 'tradepress'),
                'desc' => __('Updates dividend events and history', 'tradepress'),
                'frequency' => __('Daily or Weekly', 'tradepress'),
                'notes' => __('For income strategies', 'tradepress'),
            ),
            array(
                'name' => __('Economic Calendar CRON Job', 'tradepress'),
                'desc' => __('Updates macroeconomic events', 'tradepress'),
                'frequency' => __('Daily', 'tradepress'),
                'notes' => __('For macro strategies', 'tradepress'),
            ),
            array(
                'name' => __('Options Chain CRON Job', 'tradepress'),
                'desc' => __('Updates options data for supported symbols', 'tradepress'),
                'frequency' => __('Every 15-60 min', 'tradepress'),
                'notes' => __('Only if options trading enabled', 'tradepress'),
            ),
            array(
                'name' => __('Portfolio Positions CRON Job', 'tradepress'),
                'desc' => __('Syncs open positions from broker APIs', 'tradepress'),
                'frequency' => __('Every 5-15 min', 'tradepress'),
                'notes' => __('For live trading accounts', 'tradepress'),
            ),
            array(
                'name' => __('Orders Status CRON Job', 'tradepress'),
                'desc' => __('Updates order status/history', 'tradepress'),
                'frequency' => __('Every 5-15 min', 'tradepress'),
                'notes' => __('For automation reliability', 'tradepress'),
            ),
            array(
                'name' => __('Watchlist Sync CRON Job', 'tradepress'),
                'desc' => __('Syncs user watchlists with broker/platform', 'tradepress'),
                'frequency' => __('Hourly or Daily', 'tradepress'),
                'notes' => __('For multi-platform users', 'tradepress'),
            ),
            array(
                'name' => __('Social Sentiment CRON Job', 'tradepress'),
                'desc' => __('Updates social sentiment data', 'tradepress'),
                'frequency' => __('Hourly or Daily', 'tradepress'),
                'notes' => __('Twitter, TradingView, etc.', 'tradepress'),
            ),
            array(
                'name' => __('Alerts/Signals CRON Job', 'tradepress'),
                'desc' => __('Processes and delivers trading alerts', 'tradepress'),
                'frequency' => __('Every 1-5 min', 'tradepress'),
                'notes' => __('For automation and notifications', 'tradepress'),
            ),
        );
        ?>
        <div class="tab-content" id="cron">
            <div class="automation-section">
                <h2><?php esc_html_e('Standard TradePress CRON Jobs', 'tradepress'); ?></h2>
                <div class="tp-accordion tradepress-cron-accordion">
                    <?php foreach ($standard_cron_jobs as $job): ?>
                        <div class="tp-accordion-item">
                            <div class="tp-accordion-header">
                                <span class="dashicons dashicons-clock"></span>
                                <strong><?php echo esc_html($job['name']); ?></strong>
                            </div>
                            <div class="tp-accordion-panel">
                                <table class="form-table">
                                    <tr>
                                        <th><?php esc_html_e('Description', 'tradepress'); ?></th>
                                        <td><?php echo esc_html($job['desc']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php esc_html_e('Typical Frequency', 'tradepress'); ?></th>
                                        <td><?php echo esc_html($job['frequency']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><?php esc_html_e('Notes / API Sources', 'tradepress'); ?></th>
                                        <td><?php echo esc_html($job['notes']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('Earnings Calendar CRON Job', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Configure automatic updates for earnings calendar data from Alpha Vantage.', 'tradepress'); ?></p>
                
                <div class="earnings-cron-manager">
                    <div class="earnings-cron-status">
                        <h4><?php esc_html_e('Current Status', 'tradepress'); ?></h4>
                        
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e('Data Source', 'tradepress'); ?></th>
                                <td><?php echo esc_html($earnings_data_source); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Last Updated', 'tradepress'); ?></th>
                                <td>
                                    <?php 
                                    if ($earnings_last_updated) {
                                        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $earnings_last_updated);
                                        echo ' (' . human_time_diff($earnings_last_updated, time()) . ' ' . __('ago', 'tradepress') . ')';
                                    } else {
                                        esc_html_e('Never', 'tradepress');
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('CRON Status', 'tradepress'); ?></th>
                                <td>
                                    <?php
                                    if (wp_next_scheduled('tradepress_fetch_earnings_calendar')) {
                                        echo '<span class="cron-status enabled">' . esc_html__('Enabled', 'tradepress') . '</span>';
                                    } else {
                                        echo '<span class="cron-status disabled">' . esc_html__('Disabled', 'tradepress') . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Next Run', 'tradepress'); ?></th>
                                <td>
                                    <?php
                                    $next_run = wp_next_scheduled('tradepress_fetch_earnings_calendar');
                                    if ($next_run) {
                                        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_run);
                                        echo ' (' . human_time_diff(time(), $next_run) . ' ' . __('from now', 'tradepress') . ')';
                                    } else {
                                        esc_html_e('Not scheduled', 'tradepress');
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="earnings-cron-actions">
                        <h4><?php esc_html_e('Actions', 'tradepress'); ?></h4>
                        
                        <div class="cron-action-buttons">
                            <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'run_earnings_calendar', 'page' => 'tradepress_automation', 'tab' => 'cron'), admin_url('admin.php')), 'tradepress_run_cron')); ?>" class="button button-primary">
                                <span class="dashicons dashicons-update"></span>
                                <?php esc_html_e('Run Earnings Calendar Update Now', 'tradepress'); ?>
                            </a>
                            
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="inline-form">
                                <?php wp_nonce_field('tradepress_earnings_cron_settings', 'tradepress_earnings_cron_nonce'); ?>
                                <input type="hidden" name="action" value="tradepress_update_earnings_cron">
                                
                                <?php if (wp_next_scheduled('tradepress_fetch_earnings_calendar')): ?>
                                    <button type="submit" name="tradepress_disable_earnings_cron" class="button">
                                        <span class="dashicons dashicons-no-alt"></span>
                                        <?php esc_html_e('Disable Scheduled Updates', 'tradepress'); ?>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="tradepress_enable_earnings_cron" class="button">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php esc_html_e('Enable Scheduled Updates', 'tradepress'); ?>
                                    </button>
                                    
                                    <select name="tradepress_earnings_cron_interval" class="schedule-interval">
                                        <option value="hourly" <?php selected($earnings_cron_interval, 'hourly'); ?>><?php esc_html_e('Hourly', 'tradepress'); ?></option>
                                        <option value="twicedaily" <?php selected($earnings_cron_interval, 'twicedaily'); ?>><?php esc_html_e('Twice Daily', 'tradepress'); ?></option>
                                        <option value="daily" <?php selected($earnings_cron_interval, 'daily'); ?>><?php esc_html_e('Daily', 'tradepress'); ?></option>
                                        <option value="weekly" <?php selected($earnings_cron_interval, 'weekly'); ?>><?php esc_html_e('Weekly', 'tradepress'); ?></option>
                                    </select>
                                <?php endif; ?>
                            </form>
                        </div>
                        
                        <p class="description cron-description">
                            <?php esc_html_e('Note: Alpha Vantage has rate limits that restrict the number of API calls per minute and per day. Choose appropriate schedule intervals to avoid API limits.', 'tradepress'); ?>
                        </p>
                    </div>
                    
                    <div class="api-credentials">
                        <h4><?php esc_html_e('API Configuration', 'tradepress'); ?></h4>
                        
                        <?php
                        $alpha_vantage_key = get_option('tradepress_alphavantage_api_key', '');
                        $key_status = !empty($alpha_vantage_key) ? 'configured' : 'missing';
                        ?>
                        
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e('Alpha Vantage API Key', 'tradepress'); ?></th>
                                <td>
                                    <?php if ($key_status === 'configured'): ?>
                                        <span class="api-status configured">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php esc_html_e('Configured', 'tradepress'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="api-status missing">
                                            <span class="dashicons dashicons-warning"></span>
                                            <?php esc_html_e('Missing', 'tradepress'); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress-settings&tab=api')); ?>" class="button button-secondary">
                                        <?php 
                                        if ($key_status === 'configured') {
                                            esc_html_e('Update API Key', 'tradepress');
                                        } else {
                                            esc_html_e('Configure API Key', 'tradepress');
                                        }
                                        ?>
                                    </a>
                                </td>
                            </tr>
                        </table>
                        
                        <?php if ($key_status === 'missing'): ?>
                            <div class="notice notice-warning inline">
                                <p><?php esc_html_e('Earnings calendar updates require a valid Alpha Vantage API key. Please configure your API key to use this feature.', 'tradepress'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('All TradePress CRON Jobs', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Overview of all scheduled TradePress tasks in WordPress.', 'tradepress'); ?></p>
                
                <div class="all-cron-jobs-list">
                    <table class="widefat fixed striped">
                        <thead>
                            <tr>
                                <th class="column-job"><?php esc_html_e('Job Name', 'tradepress'); ?></th>
                                <th class="column-schedule"><?php esc_html_e('Schedule', 'tradepress'); ?></th>
                                <th class="column-next-run"><?php esc_html_e('Next Run', 'tradepress'); ?></th>
                                <th class="column-actions"><?php esc_html_e('Actions', 'tradepress'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tradepress_jobs)): ?>
                                <tr>
                                    <td colspan="4"><?php esc_html_e('No TradePress CRON jobs scheduled.', 'tradepress'); ?></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tradepress_jobs as $job): ?>
                                    <tr>
                                        <td><?php echo esc_html($job['hook']); ?></td>
                                        <td><?php echo esc_html($job['schedule_display']); ?></td>
                                        <td>
                                            <?php echo esc_html($job['next_run']); ?>
                                            <br>
                                            <small><?php printf(esc_html__('(%s from now)', 'tradepress'), $job['human_time']); ?></small>
                                        </td>
                                        <td>
                                            <!-- Placeholder for future action buttons -->
                                            <?php if ($job['hook'] === 'tradepress_fetch_earnings_calendar'): ?>
                                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'run_earnings_calendar', 'page' => 'tradepress_automation', 'tab' => 'cron'), admin_url('admin.php')), 'tradepress_run_cron')); ?>" class="button button-small">
                                                    <?php esc_html_e('Run Now', 'tradepress'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div class="cron-global-actions">
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'clear_all_tradepress_crons', 'page' => 'tradepress_automation', 'tab' => 'cron'), admin_url('admin.php')), 'tradepress_clear_cron')); ?>" class="button clear-cron-button" onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all scheduled TradePress CRON jobs? This cannot be undone.', 'tradepress'); ?>');">
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_html_e('Clear All TradePress CRON Jobs', 'tradepress'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('WordPress CRON System Info', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Information about the WordPress CRON system.', 'tradepress'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('WordPress CRON System', 'tradepress'); ?></th>
                        <td>
                            <?php if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON): ?>
                                <span class="dashicons dashicons-warning"></span>
                                <?php esc_html_e('WordPress CRON is disabled (DISABLE_WP_CRON is set to true). You need to set up a system cron job to trigger WordPress scheduled tasks.', 'tradepress'); ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('WordPress CRON is enabled and working normally.', 'tradepress'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('CRON URL', 'tradepress'); ?></th>
                        <td>
                            <code><?php echo site_url('wp-cron.php?doing_wp_cron'); ?></code>
                            <p class="description">
                                <?php esc_html_e('If WordPress CRON is disabled, use this URL in your system\'s cron job to trigger WordPress scheduled tasks.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Sample crontab entry', 'tradepress'); ?></th>
                        <td>
                            <code>*/5 * * * * wget -q -O - <?php echo site_url('wp-cron.php?doing_wp_cron'); ?> >/dev/null 2>&1</code>
                            <p class="description">
                                <?php esc_html_e('This will run WordPress cron every 5 minutes.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Settings tab content
     */
    private function settings_tab() {
        ?>
        <div class="tab-content" id="settings">
            <div class="automation-section">
                <h3><?php esc_html_e('Automation Settings', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Configure global settings for the automation system.', 'tradepress'); ?></p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('save_automation_settings', 'automation_settings_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('API Selection', 'tradepress'); ?></th>
                            <td>
                                <select name="api_provider">
                                    <option value="alphavantage"><?php esc_html_e('Alpha Vantage', 'tradepress'); ?></option>
                                    <option value="finnhub"><?php esc_html_e('Finnhub', 'tradepress'); ?></option>
                                    <option value="iex"><?php esc_html_e('IEX Cloud', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Select the primary API provider for market data.', 'tradepress'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Trading Mode', 'tradepress'); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><?php esc_html_e('Trading Mode', 'tradepress'); ?></legend>
                                    <label>
                                        <input type="radio" name="trading_mode" value="paper" checked />
                                        <?php esc_html_e('Paper Trading (Simulation)', 'tradepress'); ?>
                                    </label><br />
                                    <label>
                                        <input type="radio" name="trading_mode" value="live" />
                                        <?php esc_html_e('Live Trading (Real Money)', 'tradepress'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('Warning: Live trading will use real money!', 'tradepress'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'tradepress'); ?>" />
                    </p>
                </form>
            </div>
            
            <div class="automation-section">
                <h3><?php esc_html_e('Pointer Test', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Test the pointer functionality on this page.', 'tradepress'); ?></p>
                
                <button type="button" class="button button-secondary" id="test-api-pointer">
                    <?php esc_html_e('Test API Selection Pointer', 'tradepress'); ?>
                </button>
            </div>
        </div>
        

        <?php
    }
    
    /**
     * Enqueue automation scripts and styles
     */
    private function enqueue_automation_scripts() {
        // Scripts and styles are handled by assets-loader-original.php
        // Just ensure localization is available
        wp_localize_script('jquery', 'tradepressScoringAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tradepress_admin')
        ));
        
        // Add inline script for symbol testing
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.test-symbol-fetch').on('click', function() {
                var symbol = $(this).data('symbol');
                var $results = $('#symbol-test-results');
                var $output = $('#test-output');
                
                $results.show();
                $output.text('Testing symbol fetch for ' + symbol + '...');
                
                $.ajax({
                    url: tradepressScoringAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'tradepress_test_symbol_fetch',
                        symbol: symbol,
                        nonce: tradepressScoringAjax.nonce
                    },
                    success: function(response) {
                        $output.text(JSON.stringify(response, null, 2));
                    },
                    error: function() {
                        $output.text('Error: Could not fetch symbol data');
                    }
                });
            });
            
            $('.manual-symbol-update').on('click', function() {
                var symbol = $(this).data('symbol');
                var $results = $('#symbol-test-results');
                var $output = $('#test-output');
                
                $results.show();
                $output.text('Updating symbol ' + symbol + '...');
                
                $.ajax({
                    url: tradepressScoringAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'tradepress_update_symbols_manual',
                        symbol: symbol,
                        nonce: tradepressScoringAjax.nonce
                    },
                    success: function(response) {
                        $output.text(JSON.stringify(response, null, 2));
                    },
                    error: function() {
                        $output.text('Error: Could not update symbol');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
?>