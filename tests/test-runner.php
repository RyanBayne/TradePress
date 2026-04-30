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
        ],
        'feature_status' => [
            'title' => 'Feature Status',
            'description' => 'Current implementation and readiness status across plugin features'
        ],
        'ui_crawl' => [
            'title' => 'UI Crawl',
            'description' => 'Visit admin tabs one by one and report page-level errors'
        ],
        'trading212' => [
            'title' => 'Trading212 API',
            'description' => 'Live endpoint tests for the Trading212 integration — runs against your configured API key'
        ]
    ];
    
    /**
     *   C On St Ru Ct.
     *
     * @version 1.0.0
     */
    public function __construct() {
        add_action('wp_ajax_tradepress_run_tests', [$this, 'ajax_run_tests']);
        add_action('wp_ajax_tradepress_discover_tests', [$this, 'ajax_discover_tests']);
        add_action('wp_ajax_tradepress_ui_crawl_log', [$this, 'ajax_ui_crawl_log']);
        add_action('admin_menu', [$this, 'add_test_menu'], 999);
        
        // Load test framework
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'tests/framework/class-test-registry.php';
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'tests/framework/class-test-case.php';
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'tests/framework/class-test-results.php';
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'tests/framework/class-test-utils.php';
    }
    /**
     * Add test runner to admin menu (only in developer mode)
      *
      * @version 1.0.0
     */
    public function add_test_menu() {
        if ( tradepress_is_developer_mode() ) {
            add_submenu_page(
                'TradePress',
                'Testing',
                'Testing',
                'manage_options',
                'tradepress-tests',
                [$this, 'render_test_page']
            );
        }
    }
    
    /**
     * Render test runner page
      *
      * @version 1.0.0
     */
    public function render_test_page() {
        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'active';
        
        if (!isset($this->tabs[$current_tab])) {
            $current_tab = 'active';
        }
        
        echo '<div class="wrap tradepress-tests">';
        echo '<h1>' . esc_html__('TradePress Testing', 'tradepress') . '</h1>';
        
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
     * Localize Testing page script data.
     *
     * Script/style handles are enqueued by the central asset queue.
      *
      * @version 1.0.0
     */
    private function enqueue_test_assets() {
        if ( ! wp_script_is( 'tradepress-test-runner', 'enqueued' ) && ! wp_script_is( 'tradepress-test-runner', 'registered' ) ) {
            return;
        }
        
        wp_localize_script('tradepress-test-runner', 'tradePressTests', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tradepress_tests'),
            'i18n' => [
                'run' => __('Run', 'tradepress'),
                'running' => __('Running...', 'tradepress'),
                'runPhase3' => __('Run Phase 3 Tests', 'tradepress'),
                'error' => __('Error', 'tradepress'),
                'ajaxError' => __('AJAX request failed', 'tradepress'),
                'resultsCleared' => __('Test results cleared.', 'tradepress'),
                'testResults' => __('Test Results', 'tradepress'),
                'overallStatus' => __('Overall Status', 'tradepress'),
                'executionTime' => __('Execution Time', 'tradepress'),
                'requirementsSatisfied' => __('Requirements Satisfied', 'tradepress'),
                'individualResults' => __('Individual Test Results', 'tradepress'),
                'requirement' => __('Requirement', 'tradepress'),
                'performanceSummary' => __('Performance Summary', 'tradepress'),
                'apiCallsSaved' => __('API Calls Saved', 'tradepress'),
                'cacheHitRate' => __('Cache Hit Rate', 'tradepress'),
                'memoryUsage' => __('Memory Usage', 'tradepress'),
                'recommendations' => __('Recommendations', 'tradepress'),
                'discovering' => __('Discovering tests...', 'tradepress'),
                'discoverTests' => __('Discover Tests', 'tradepress'),
                'discoveryComplete' => __('Discovery complete. Reloading results...', 'tradepress')
            ]
        ]);
    }
    
    /**
     * Render Active Tests tab
      *
      * @version 1.0.0
     */
    private function render_active_tab() {
        $active_tests = TradePress_Test_Registry::get_tests(['status' => 'active']);
        
        echo '<h2>' . esc_html($this->tabs['active']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['active']['description']) . '</p>';
        echo '<p><button type="button" id="discover-tests" class="button button-secondary">' . esc_html__( 'Discover Tests', 'tradepress' ) . '</button></p>';
        echo '<div id="discover-tests-result" style="margin: 10px 0;"></div>';
        
        $this->render_test_table($active_tests);
    }
    
    /**
     * Render Standard Tests tab
      *
      * @version 1.0.0
     */
    private function render_standard_tab() {
        $standard_tests = TradePress_Test_Registry::get_tests(['category' => 'standard']);
        
        echo '<h2>' . esc_html($this->tabs['standard']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['standard']['description']) . '</p>';
        
        $this->render_test_table($standard_tests);
    }
    
    /**
     * Render Bug Investigation tab
      *
      * @version 1.0.0
     */
    private function render_bugs_tab() {
        $bug_tests = TradePress_Test_Registry::get_tests(['category' => 'bugs']);
        
        echo '<h2>' . esc_html($this->tabs['bugs']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['bugs']['description']) . '</p>';
        
        $this->render_test_table($bug_tests);
    }
    
    /**
     * Render Performance Tests tab
      *
      * @version 1.0.0
     */
    private function render_performance_tab() {
        $perf_tests = TradePress_Test_Registry::get_tests(['category' => 'performance']);
        
        echo '<h2>' . esc_html($this->tabs['performance']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['performance']['description']) . '</p>';
        
        $this->render_test_table($perf_tests);
    }
    
    /**
     * Render Phase 3 Tests tab
      *
      * @version 1.0.0
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
     * Render Feature Status tab using the existing implementation view.
      *
      * @version 1.0.0
     */
    private function render_feature_status_tab() {
        echo '<h2>' . esc_html($this->tabs['feature_status']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['feature_status']['description']) . '</p>';

        $feature_status_view = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/feature-status.php';
        if ( ! file_exists( $feature_status_view ) ) {
            echo '<div class="notice notice-warning"><p>' . esc_html__( 'Feature status view is not available.', 'tradepress' ) . '</p></div>';
            return;
        }

        require_once $feature_status_view;

        $feature_status_class = 'TradePress_Admin_Development_Feature_Status';
        if ( class_exists( $feature_status_class ) && is_callable( array( $feature_status_class, 'output' ) ) ) {
            call_user_func( array( $feature_status_class, 'output' ) );
            return;
        }

        echo '<div class="notice notice-warning"><p>' . esc_html__( 'Feature status class could not be loaded.', 'tradepress' ) . '</p></div>';
    }

    /**
     * Render Trading212 API Tests tab.
     *
     * @version 1.0.0
     */
    private function render_trading212_tab() {
        $api_settings  = get_option( 'tradepress_api_settings', array() );
        $settings_key  = isset( $api_settings['trading212_api_key'] ) ? trim( (string) $api_settings['trading212_api_key'] ) : '';
        $live_key      = trim( (string) get_option( 'tradepress_trading212_api_key', '' ) );
        $paper_key     = trim( (string) get_option( 'tradepress_trading212_paper_api_key', '' ) );
        if ( '' === $live_key ) {
            $live_key = trim( (string) get_option( 'TradePress_api_trading212_realmoney_apikey', '' ) );
        }
        if ( '' === $paper_key ) {
            $paper_key = trim( (string) get_option( 'TradePress_api_trading212_papermoney_apikey', '' ) );
        }

        $environment = isset( $api_settings['trading212_environment'] ) ? (string) $api_settings['trading212_environment'] : '';
        if ( '' !== $environment && '' === $settings_key && ( '' !== $live_key || '' !== $paper_key ) ) {
            $environment = '';
        }
        if ( '' === $environment ) {
            $legacy_environment = (string) get_option( 'tradepress_trading212_environment', '' );
            if ( in_array( $legacy_environment, array( 'demo', 'live' ), true ) ) {
                $environment = $legacy_environment;
            }
        }
        if ( '' === $environment ) {
            $trading_mode = (string) get_option( 'TradePress_api_trading212_trading_mode', '' );
            if ( 'live' === $trading_mode ) {
                $environment = 'live';
            } elseif ( 'paper' === $trading_mode ) {
                $environment = 'demo';
            }
        }
        if ( '' === $environment ) {
            $environment = ( '' !== $live_key && '' === $paper_key ) ? 'live' : 'demo';
        }

        $api_key = '' !== $settings_key ? $settings_key : ( 'live' === $environment ? $live_key : $paper_key );
        if ( '' === $api_key ) {
            $api_key = '' !== $live_key ? $live_key : $paper_key;
        }
        $has_key = '' !== $api_key;

        echo '<h2>' . esc_html( $this->tabs['trading212']['title'] ) . '</h2>';
        echo '<p class="description">' . esc_html( $this->tabs['trading212']['description'] ) . '</p>';

        if ( ! $has_key ) {
            echo '<div class="notice notice-warning inline"><p>';
            echo esc_html__( 'No Trading212 API key is configured. Add your key in Trading Platforms settings before running these tests.', 'tradepress' );
            echo '</p></div>';
        }

        echo '<div class="tradepress-t212-tests">';

        echo '<div style="margin: 12px 0; padding: 10px 14px; background: #f0f0f1; border-left: 4px solid #72aee6; font-size: 13px;">';
        echo '<strong>' . esc_html__( 'Environment:', 'tradepress' ) . '</strong> ';
        echo '<code>' . esc_html( $environment ) . '</code>';
        if ( 'live' === $environment ) {
            echo ' &mdash; <span style="color:#d63638;">' . esc_html__( 'Live environment — read-only tests only, no orders will be placed.', 'tradepress' ) . '</span>';
        }
        echo '</div>';

        echo '<div class="test-controls" style="margin: 20px 0;">';
        echo '<button id="run-trading212-tests" class="button button-primary"' . ( $has_key ? '' : ' disabled' ) . '>';
        echo esc_html__( 'Run Trading212 Tests', 'tradepress' );
        echo '</button> ';
        echo '<button id="clear-trading212-results" class="button">';
        echo esc_html__( 'Clear Results', 'tradepress' );
        echo '</button>';
        echo '</div>';

        echo '<div id="trading212-test-results" style="margin-top: 20px;">';
        echo '<p><em>' . esc_html__( 'Click "Run Trading212 Tests" to execute all read-only endpoint tests against your configured API key.', 'tradepress' ) . '</em></p>';
        echo '</div>';
        echo '</div>';

        // Inline JS — scoped to this tab, no separate file needed.
        ?>
        <style>
        .t212-test-group { margin: 20px 0; }
        .t212-test-group h3 { margin: 0 0 8px; font-size: 14px; }
        .t212-test-row { display: flex; align-items: flex-start; gap: 12px; padding: 10px 14px; border-bottom: 1px solid #e0e0e0; font-size: 13px; }
        .t212-test-row:last-child { border-bottom: none; }
        .t212-test-row.pass { background: #f0fff4; }
        .t212-test-row.fail { background: #fff0f0; }
        .t212-test-row.skip { background: #fffbe6; }
        .t212-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; min-width: 42px; text-align: center; }
        .t212-badge.pass { background: #d1fae5; color: #065f46; }
        .t212-badge.fail { background: #fee2e2; color: #991b1b; }
        .t212-badge.skip { background: #fef9c3; color: #713f12; }
        .t212-endpoint { font-family: monospace; color: #444; flex: 1; }
        .t212-detail { font-size: 12px; color: #666; flex: 2; word-break: break-all; }
        .t212-summary { padding: 12px 14px; font-weight: 600; border-top: 2px solid #ccc; margin-top: 4px; }
        </style>
        <script>
        (function($) {
            $('#run-trading212-tests').on('click', function() {
                var $btn = $(this);
                var $results = $('#trading212-test-results');
                $btn.prop('disabled', true).text('<?php echo esc_js( __( 'Running\u2026', 'tradepress' ) ); ?>');
                $results.html('<p><em><?php echo esc_js( __( 'Running tests\u2026', 'tradepress' ) ); ?></em></p>');

                $.ajax({
                    url: tradePressTests.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'tradepress_run_tests',
                        nonce: tradePressTests.nonce,
                        test_suite: 'trading212'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            $results.html(renderTrading212Results(response.data));
                        } else {
                            var msg = (response.data) ? response.data : '<?php echo esc_js( __( 'Unknown error', 'tradepress' ) ); ?>';
                            $results.html('<div class="notice notice-error"><p>' + $('<span>').text(msg).html() + '</p></div>');
                        }
                    },
                    error: function() {
                        $results.html('<div class="notice notice-error"><p><?php echo esc_js( __( 'AJAX request failed.', 'tradepress' ) ); ?></p></div>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run Trading212 Tests', 'tradepress' ) ); ?>');
                    }
                });
            });

            $('#clear-trading212-results').on('click', function() {
                $('#trading212-test-results').html('<p><em><?php echo esc_js( __( 'Click "Run Trading212 Tests" to execute all read-only endpoint tests against your configured API key.', 'tradepress' ) ); ?></em></p>');
            });

            function renderTrading212Results(data) {
                if (!data || !data.tests) {
                    return '<p><?php echo esc_js( __( 'No test data returned.', 'tradepress' ) ); ?></p>';
                }
                var html = '';
                var passCount = 0, failCount = 0, skipCount = 0;

                // Group by category
                var groups = {};
                $.each(data.tests, function(i, test) {
                    var group = test.group || 'General';
                    if (!groups[group]) groups[group] = [];
                    groups[group].push(test);
                });

                $.each(groups, function(groupName, tests) {
                    html += '<div class="t212-test-group"><h3>' + $('<span>').text(groupName).html() + '</h3><div style="border:1px solid #e0e0e0;">';
                    $.each(tests, function(i, test) {
                        var status = test.status || 'fail';
                        if (status === 'pass') passCount++;
                        else if (status === 'skip') skipCount++;
                        else failCount++;

                        html += '<div class="t212-test-row ' + status + '">';
                        html += '<span class="t212-badge ' + status + '">' + status.toUpperCase() + '</span>';
                        html += '<span class="t212-endpoint">' + $('<span>').text(test.endpoint || test.name).html() + '</span>';
                        html += '<span class="t212-detail">' + $('<span>').text(test.message || '').html() + '</span>';
                        html += '</div>';
                    });
                    html += '</div></div>';
                });

                var totalCount = passCount + failCount + skipCount;
                var summaryColor = (failCount === 0) ? '#0a7c42' : '#d63638';
                html += '<div class="t212-summary" style="color:' + summaryColor + ';">';
                html += '<?php echo esc_js( __( 'Results:', 'tradepress' ) ); ?> ' + passCount + ' <?php echo esc_js( __( 'passed', 'tradepress' ) ); ?>, ' + failCount + ' <?php echo esc_js( __( 'failed', 'tradepress' ) ); ?>, ' + skipCount + ' <?php echo esc_js( __( 'skipped', 'tradepress' ) ); ?> (<?php echo esc_js( __( 'of', 'tradepress' ) ); ?> ' + totalCount + ')';
                if (data.environment) {
                    html += ' &mdash; Environment: <code>' + $('<span>').text(data.environment).html() + '</code>';
                }
                html += '</div>';
                return html;
            }
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Render UI Crawl tab.
      *
      * @version 1.0.0
     */
    private function render_ui_crawl_tab() {        $seed_urls = $this->get_ui_crawl_seed_urls();

        echo '<section class="tradepress-ui-crawl">';
        echo '<div class="tradepress-ui-crawl__hero">';
        echo '<div class="tradepress-ui-crawl__hero-copy">';
        echo '<h2>' . esc_html($this->tabs['ui_crawl']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['ui_crawl']['description']) . '</p>';
        echo '<p class="description">' . esc_html__( 'This scan checks each discovered tab URL for HTTP failures and common PHP/WordPress error signals in page HTML.', 'tradepress' ) . '</p>';
        echo '</div>';
        echo '<div class="tradepress-ui-crawl__meta">';
        echo '<div class="tradepress-ui-crawl__meta-card">';
        echo '<span class="tradepress-ui-crawl__meta-label">' . esc_html__( 'Seed URLs', 'tradepress' ) . '</span>';
        echo '<strong class="tradepress-ui-crawl__meta-value">' . esc_html( count( $seed_urls ) ) . '</strong>';
        echo '</div>';
        echo '<div class="tradepress-ui-crawl__meta-card">';
        echo '<span class="tradepress-ui-crawl__meta-label">' . esc_html__( 'Mode', 'tradepress' ) . '</span>';
        echo '<strong class="tradepress-ui-crawl__meta-value">' . esc_html__( 'HTML signal scan', 'tradepress' ) . '</strong>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="tradepress-ui-crawl__actions">';
        echo '<button id="run-ui-crawl" class="button button-primary">' . esc_html__( 'Run UI Crawl', 'tradepress' ) . '</button>';
        echo '<button id="clear-ui-crawl" class="button">' . esc_html__( 'Clear Results', 'tradepress' ) . '</button>';
        echo '</div>';

        echo '<div class="tradepress-ui-crawl__panels">';
        echo '<div class="tradepress-ui-crawl__panel tradepress-ui-crawl__panel--summary">';
        echo '<h3>' . esc_html__( 'Run Summary', 'tradepress' ) . '</h3>';
        echo '<div id="ui-crawl-summary" class="tradepress-ui-crawl__summary"></div>';
        echo '</div>';
        echo '<div class="tradepress-ui-crawl__panel tradepress-ui-crawl__panel--results">';
        echo '<h3>' . esc_html__( 'URL Results', 'tradepress' ) . '</h3>';
        echo '<div id="ui-crawl-results" class="tradepress-ui-crawl__results"></div>';
        echo '</div>';
        echo '</div>';

        $ui_crawl_payload = array(
            'seeds'  => $seed_urls,
            'config' => array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'tradepress_tests' ),
            ),
        );

        echo '<div id="tradepress-ui-crawl-data" data-ui-crawl="' . esc_attr( wp_json_encode( $ui_crawl_payload ) ) . '"></div>';
        echo '</section>';
    }

    /**
     * AJAX logger for UI crawl failures.
      *
      * @version 1.0.0
     */
    public function ajax_ui_crawl_log() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'tradepress_tests')) {
            wp_send_json_error('Security check failed');
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        if ( ! function_exists( 'tradepress_trace_log' ) ) {
            $bugnet_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/bugnet-system/functions.tradepress-bugnet.php';
            if ( file_exists( $bugnet_file ) ) {
                require_once $bugnet_file;
            }
        }

        $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
        $status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '';
        $error_message = isset($_POST['error_message']) ? sanitize_text_field(wp_unslash($_POST['error_message'])) : '';

        $signals_raw = isset($_POST['signals']) ? wp_unslash($_POST['signals']) : '[]';
        $signals = json_decode($signals_raw, true);
        if (!is_array($signals)) {
            $signals = array();
        }

        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log(
                'UI Crawl Failure',
                array(
                    'url' => $url,
                    'status' => $status,
                    'signals' => $signals,
                    'error' => $error_message,
                )
            );
        }

        wp_send_json_success(array('logged' => true));
    }

    /**
     * Seed admin URLs used to discover all tab links.
     *
     * @return array
      * @version 1.0.0
     */
    private function get_ui_crawl_seed_urls() {
        $is_dev_mode = function_exists( 'tradepress_is_developer_mode' ) ? tradepress_is_developer_mode() : false;

        $seed_urls = array(
            admin_url( 'admin.php?page=TradePress' ),
            admin_url( 'admin.php?page=tradepress_data' ),
            admin_url( 'admin.php?page=tradepress_watchlists' ),
            admin_url( 'admin.php?page=tradepress_automation' ),
            admin_url( 'admin.php?page=tradepress_research' ),
            admin_url( 'admin.php?page=tradepress_trading' ),
            admin_url( 'admin.php?page=tradepress_platforms' ),
            admin_url( 'admin.php?page=tradepress-tests' ),
        );

        if ( $is_dev_mode ) {
            $seed_urls[] = admin_url( 'admin.php?page=tradepress_development' );
            $seed_urls[] = admin_url( 'admin.php?page=tradepress_focus' );
            $seed_urls[] = admin_url( 'admin.php?page=tradepress_analysis' );
            $seed_urls[] = admin_url( 'admin.php?page=tradepress_scoring_directives' );
        }

        return array_values( array_unique( $seed_urls ) );
    }
    
    /**
     * Render test table
      *
      * @version 1.0.0
      *
      * @param mixed $tests
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
     * Get status badge HTML.
     *
     * @param string $status Test status value.
     * @return string Badge markup.
      * @version 1.0.0
     */
    private function get_status_badge($status) {
        $status = sanitize_key( (string) $status );

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
      *
      * @version 1.0.0
     */
    public function ajax_run_tests() {
        $raw_nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

        if ( ! wp_verify_nonce( $raw_nonce, 'tradepress_tests' ) ) {
            wp_send_json_error( 'Security check failed' );
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
            return;
        }

        $test_suite = sanitize_text_field( wp_unslash( $_POST['test_suite'] ?? '' ) );
        $test_id    = isset( $_POST['test_id'] ) ? absint( wp_unslash( $_POST['test_id'] ) ) : 0;

        $this->trace( 'Test Runner: ajax_run_tests called', array( 'suite' => $test_suite, 'test_id' => $test_id ) );

        try {
            if ( $test_id > 0 ) {
                $result = $this->execute_registered_file_test( $test_id );
                wp_send_json_success( $result );
                return;
            }

            switch ( $test_suite ) {
                case 'phase3':
                    $query_register_path = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
                    $test_file_path      = plugin_dir_path( __FILE__ ) . 'recent-call-register-tests.php';

                    if ( ! file_exists( $query_register_path ) ) {
                        throw new Exception( 'Query register file not found: ' . $query_register_path );
                    }
                    require_once $query_register_path;

                    if ( ! file_exists( $test_file_path ) ) {
                        throw new Exception( 'Test file not found: ' . $test_file_path );
                    }
                    require_once $test_file_path;

                    if ( ! class_exists( 'TradePress_Call_Register' ) ) {
                        throw new Exception( 'TradePress_Call_Register class not found' );
                    }

                    if ( ! class_exists( 'TradePress_Recent_Call_Register_Tests' ) ) {
                        throw new Exception( 'TradePress_Recent_Call_Register_Tests class not found' );
                    }

                    $tester  = new TradePress_Recent_Call_Register_Tests();
                    $results = $tester->run_phase3_tests();
                    break;

                case 'trading212':
                    $api_test_file = plugin_dir_path( __FILE__ ) . 'integration/trading212/trading212-api-tests.php';

                    if ( ! file_exists( $api_test_file ) ) {
                        throw new Exception( 'Trading212 test file not found: ' . $api_test_file );
                    }

                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/trading212/trading212-api.php';
                    require_once $api_test_file;

                    if ( ! class_exists( 'TradePress_Trading212_API_Tests' ) ) {
                        throw new Exception( 'TradePress_Trading212_API_Tests class not found' );
                    }

                    $tester  = new TradePress_Trading212_API_Tests();
                    $results = $tester->run_all_tests();
                    break;

                default:
                    throw new Exception( 'Unknown test suite: ' . $test_suite );
            }

            wp_send_json_success( $results );

        } catch ( Exception $e ) {
            $this->trace( 'Test Runner: ajax_run_tests error', array( 'error' => $e->getMessage() ) );
            wp_send_json_error( 'Test execution failed: ' . $e->getMessage() );
        }
    }

    /**
     * AJAX handler for discovering and registering tests from configured directories.
      *
      * @version 1.0.0
     */
    public function ajax_discover_tests() {
        $raw_nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

        if ( ! wp_verify_nonce( $raw_nonce, 'tradepress_tests' ) ) {
            wp_send_json_error( 'Security check failed' );
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
            return;
        }

        $directory = sanitize_text_field( wp_unslash( $_POST['directory'] ?? '' ) );
        $summary   = TradePress_Test_Registry::discover_tests( $directory );

        $this->trace( 'Test Runner: discovery complete', array( 'directory' => $directory, 'summary' => $summary ) );

        wp_send_json_success( $summary );
    }

    /**
     * Execute one registered file-based test by ID.
     *
     * @param int $test_id
     * @return array
     * @throws Exception
      * @version 1.0.0
     */
    private function execute_registered_file_test( $test_id ) {
        $test = TradePress_Test_Registry::get_test( $test_id );
        if ( ! $test ) {
            throw new Exception( 'Test not found: ' . $test_id );
        }

        if ( 'file' !== $test->test_type ) {
            throw new Exception( 'Only file tests are supported in this endpoint.' );
        }

        $class_name = (string) $test->class_name;
        $file_path  = TRADEPRESS_PLUGIN_DIR_PATH . ltrim( (string) $test->file_path, '/' );

        if ( ! class_exists( $class_name, false ) ) {
            TradePress_Test_Registry::autoload_registered_test_class( $class_name );
        }

        if ( ! class_exists( $class_name, false ) ) {
            if ( ! file_exists( $file_path ) ) {
                throw new Exception( 'Test file not found: ' . $test->file_path );
            }
            require_once $file_path;
        }

        if ( ! class_exists( $class_name, false ) ) {
            throw new Exception( 'Test class not found: ' . $class_name );
        }

        $instance = new $class_name( (int) $test->test_id );
        if ( ! $instance instanceof TradePress_Test_Case ) {
            throw new Exception( 'Test class must extend TradePress_Test_Case' );
        }

        $result = $instance->run_all_tests();
        $fresh  = TradePress_Test_Registry::get_test( $test_id );

        return array(
            'test_id' => (int) $test->test_id,
            'status' => ( ! empty( $result['passed'] ) ? 'passed' : 'failed' ),
            'success_rate' => $fresh ? (float) $fresh->success_rate : null,
            'run_count' => $fresh ? (int) $fresh->run_count : null,
            'results' => $result,
        );
    }

    /**
     * Trace helper with lazy BugNet load.
     *
     * @param string $message
     * @param array  $context
      * @version 1.0.0
     */
    private function trace( $message, $context = array() ) {
        if ( ! function_exists( 'tradepress_trace_log' ) ) {
            $bugnet_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/bugnet-system/functions.tradepress-bugnet.php';
            if ( file_exists( $bugnet_file ) ) {
                require_once $bugnet_file;
            }
        }

        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log( $message, $context );
        }
    }

    /**
     * Run tests programmatically (for AI or automated testing)
      *
      * @version 1.0.0
      *
      * @param string $test_suite
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
