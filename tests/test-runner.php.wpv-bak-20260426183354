<?php
/**
 * TradePress Test Runner
 * 
 * Simple interface for executing tests and displaying results
 * Can be accessed via admin menu or direct URL for development
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Test_Runner {
    
    /**
     * Available test tabs
     */
    private $tabs = [
        'active' => [
            'title' => 'Active Tests',
            'description' => 'Currently active and running test suites'
        ],
        'standard' => [
            'title' => 'Standard Tests',
            'description' => 'Core functionality test suites'
        ],
        'bugs' => [
            'title' => 'Bug Investigation',
            'description' => 'Tests related to bug reports and fixes'
        ],
        'performance' => [
            'title' => 'Performance Tests',
            'description' => 'System performance and optimization tests'
        ],
        'phase3' => [
            'title' => 'Phase 3 Tests',
            'description' => 'Recent Call Register testing suite'
        ]
    ];
    
    public function __construct() {
        add_action('wp_ajax_tradepress_run_tests', [$this, 'ajax_run_tests']);
        add_action('admin_menu', [$this, 'add_test_menu'], 999);
        
        // Load test framework
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'tests/framework/class-test-registry.php';
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'tests/framework/class-test-case.php';
    }
    
    /**
     * Add test runner to admin menu (only in developer mode)
     */
    public function add_test_menu() {
        if (get_option('tradepress_developer_mode', false)) {
            add_submenu_page(
                'TradePress',
                'Test Runner',
                'Tests',
                'manage_options',
                'tradepress-tests',
                [$this, 'render_test_page']
            );
        }
    }
    
    /**
     * Render test runner page
     */
    public function render_test_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'active';
        
        if (!isset($this->tabs[$current_tab])) {
            $current_tab = 'active';
        }
        
        echo '<div class="wrap tradepress-tests">';
        echo '<h1>' . esc_html__('TradePress Tests', 'tradepress') . '</h1>';
        
        // Tab navigation
        echo '<nav class="nav-tab-wrapper">';
        foreach ($this->tabs as $tab_id => $tab) {
            $class = ($current_tab === $tab_id) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $url = admin_url('admin.php?page=tradepress-tests&tab=' . $tab_id);
            echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">';
            echo esc_html($tab['title']);
            echo '</a>';
        }
        echo '</nav>';
        
        // Tab content
        echo '<div class="test-tab-content">';
        $method = 'render_' . $current_tab . '_tab';
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->render_active_tab(); // Default
        }
        echo '</div>';
        
        echo '</div>';
        
        $this->enqueue_test_assets();
    }
    
    /**
     * Enqueue necessary assets
     */
    private function enqueue_test_assets() {
        wp_enqueue_style(
            'tradepress-test-styles',
            TRADEPRESS_PLUGIN_URL . 'assets/css/test-runner.css',
            [],
            TRADEPRESS_VERSION
        );
        
        wp_enqueue_script(
            'tradepress-test-runner',
            TRADEPRESS_PLUGIN_URL . 'assets/js/test-runner.js',
            ['jquery'],
            TRADEPRESS_VERSION,
            true
        );
        
        wp_localize_script('tradepress-test-runner', 'tradePressTests', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tradepress_tests')
        ]);
    }
    
    /**
     * Render Active Tests tab
     */
    private function render_active_tab() {
        $active_tests = TradePress_Test_Registry::get_tests(['status' => 'active']);
        
        echo '<h2>' . esc_html($this->tabs['active']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['active']['description']) . '</p>';
        
        $this->render_test_table($active_tests);
    }
    
    /**
     * Render Standard Tests tab
     */
    private function render_standard_tab() {
        $standard_tests = TradePress_Test_Registry::get_tests(['category' => 'standard']);
        
        echo '<h2>' . esc_html($this->tabs['standard']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['standard']['description']) . '</p>';
        
        $this->render_test_table($standard_tests);
    }
    
    /**
     * Render Bug Investigation tab
     */
    private function render_bugs_tab() {
        $bug_tests = TradePress_Test_Registry::get_tests(['category' => 'bugs']);
        
        echo '<h2>' . esc_html($this->tabs['bugs']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['bugs']['description']) . '</p>';
        
        $this->render_test_table($bug_tests);
    }
    
    /**
     * Render Performance Tests tab
     */
    private function render_performance_tab() {
        $perf_tests = TradePress_Test_Registry::get_tests(['category' => 'performance']);
        
        echo '<h2>' . esc_html($this->tabs['performance']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['performance']['description']) . '</p>';
        
        $this->render_test_table($perf_tests);
    }
    
    /**
     * Render Phase 3 Tests tab
     */
    private function render_phase3_tab() {
        echo '<h2>' . esc_html($this->tabs['phase3']['title']) . '</h2>';
        echo '<p class="description">' . esc_html__('Comprehensive testing suite for the Recent Call Register system, validating API call deduplication, platform-aware caching, and cross-feature integration.', 'tradepress') . '</p>';
        
        echo '<div class="test-controls" style="margin: 20px 0;">';
        echo '<button id="run-phase3-tests" class="button button-primary">';
        echo esc_html__('Run Phase 3 Tests', 'tradepress');
        echo '</button>';
        echo '<button id="clear-test-results" class="button">';
        echo esc_html__('Clear Results', 'tradepress');
        echo '</button>';
        echo '</div>';
        
        echo '<div id="test-results" style="margin-top: 20px;">';
        echo '<p><em>' . esc_html__('Click "Run Phase 3 Tests" to execute the Recent Call Register test suite.', 'tradepress') . '</em></p>';
        echo '</div>';
    }
    
    /**
     * Render test table
     */
    private function render_test_table($tests) {
        if (empty($tests)) {
            echo '<p class="no-tests">' . esc_html__('No tests found.', 'tradepress') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">' . esc_html__('Test', 'tradepress') . '</th>';
        echo '<th scope="col">' . esc_html__('Description', 'tradepress') . '</th>';
        echo '<th scope="col">' . esc_html__('Status', 'tradepress') . '</th>';
        echo '<th scope="col">' . esc_html__('Last Run', 'tradepress') . '</th>';
        echo '<th scope="col">' . esc_html__('Success Rate', 'tradepress') . '</th>';
        echo '<th scope="col">' . esc_html__('Actions', 'tradepress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($tests as $test) {
            echo '<tr data-test-id="' . esc_attr($test->test_id) . '">';
            echo '<td class="test-title">' . esc_html($test->title) . '</td>';
            echo '<td class="test-description">' . esc_html($test->description) . '</td>';
            echo '<td class="test-status">' . $this->get_status_badge($test->status) . '</td>';
            echo '<td class="test-last-run">' . ($test->last_run ? esc_html(human_time_diff(strtotime($test->last_run))) : '-') . '</td>';
            echo '<td class="test-success-rate">' . ($test->success_rate ? round($test->success_rate, 1) . '%' : '-') . '</td>';
            echo '<td class="test-actions">';
            echo '<button class="button run-test" data-test-id="' . esc_attr($test->test_id) . '">' . esc_html__('Run', 'tradepress') . '</button>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Get status badge HTML
     */
    private function get_status_badge($status) {
        $status_classes = [
            'active' => 'status-active',
            'passed' => 'status-passed',
            'failed' => 'status-failed',
            'error' => 'status-error',
            'pending' => 'status-pending'
        ];
        
        $class = isset($status_classes[$status]) ? $status_classes[$status] : 'status-unknown';
        return '<span class="status-badge ' . esc_attr($class) . '">' . esc_html($status) . '</span>';
    }
    
    /**
     * AJAX handler for running tests
     */
    public function ajax_run_tests() {
        // Log the start of AJAX request
        error_log('TradePress Test Runner: AJAX request started');
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_tests')) {
            error_log('TradePress Test Runner: Security check failed');
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!current_user_can('manage_options')) {
            error_log('TradePress Test Runner: Insufficient permissions');
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        $test_suite = sanitize_text_field($_POST['test_suite']);
        error_log('TradePress Test Runner: Running test suite: ' . $test_suite);
        
        try {
            switch ($test_suite) {
                case 'phase3':
                    // Load dependencies
                    $query_register_path = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
                    $test_file_path = plugin_dir_path(__FILE__) . 'recent-call-register-tests.php';
                    
                    error_log('TradePress Test Runner: Loading ' . $query_register_path);
                    if (!file_exists($query_register_path)) {
                        throw new Exception('Query register file not found: ' . $query_register_path);
                    }
                    require_once $query_register_path;
                    
                    error_log('TradePress Test Runner: Loading ' . $test_file_path);
                    if (!file_exists($test_file_path)) {
                        throw new Exception('Test file not found: ' . $test_file_path);
                    }
                    require_once $test_file_path;
                    
                    if (!class_exists('TradePress_Call_Register')) {
                        throw new Exception('TradePress_Call_Register class not found');
                    }
                    
                    if (!class_exists('TradePress_Recent_Call_Register_Tests')) {
                        throw new Exception('TradePress_Recent_Call_Register_Tests class not found');
                    }
                    
                    error_log('TradePress Test Runner: Creating test instance');
                    $tester = new TradePress_Recent_Call_Register_Tests();
                    
                    error_log('TradePress Test Runner: Running tests');
                    $results = $tester->run_phase3_tests();
                    
                    error_log('TradePress Test Runner: Tests completed successfully');
                    break;
                    
                default:
                    throw new Exception('Unknown test suite: ' . $test_suite);
            }
            
            wp_send_json_success($results);
            
        } catch (Exception $e) {
            error_log('TradePress Test Runner: Error - ' . $e->getMessage());
            wp_send_json_error('Test execution failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Run tests programmatically (for AI or automated testing)
     */
    public static function run_tests_programmatically($test_suite = 'phase3') {
        switch ($test_suite) {
            case 'phase3':
                // Load dependencies
                require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
                require_once plugin_dir_path(__FILE__) . 'recent-call-register-tests.php';
                $tester = new TradePress_Recent_Call_Register_Tests();
                return $tester->run_phase3_tests();
                
            default:
                return [
                    'error' => 'Unknown test suite: ' . $test_suite,
                    'status' => 'error'
                ];
        }
    }
}

// Initialize test runner
new TradePress_Test_Runner();