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
            'description' => 'Currently active and running test suites',
            'tab_ref' => 'TAB01'
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
            'description' => 'Recent Call Register testing suite',
            'tab_ref' => 'TAB05'
        ],
        'feature_status' => [
            'title' => 'Feature Status',
            'description' => 'Current implementation and readiness status across plugin features',
            'tab_ref' => 'TAB06'
        ],
        'ui_crawl' => [
            'title' => 'UI Crawl',
            'description' => 'Visit admin tabs one by one and report page-level errors',
            'tab_ref' => 'TAB07'
        ],
        'trading212' => [
            'title' => 'Trading212 API',
            'description' => 'Live endpoint tests for the Trading212 integration — runs against your configured API key',
            'tab_ref' => 'TAB08'
        ],
        'directives' => [
            'title' => 'Scoring Directives',
            'description' => 'Run all scoring directives against dummy data and report class, method, and calculation status',
            'tab_ref' => 'TAB09',
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
        echo '<h1>' . esc_html( $this->ref( __( 'TradePress Testing', 'tradepress' ), 'TIT01' ) ) . '</h1>';
        
        // Tab navigation
        echo '<nav class="nav-tab-wrapper">';
        foreach ($this->tabs as $tab_id => $tab) {
            $class = ($current_tab === $tab_id) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $url = admin_url('admin.php?page=tradepress-tests&tab=' . $tab_id);
            $tab_ref = isset( $tab['tab_ref'] ) ? $tab['tab_ref'] : '';
            echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">';
            echo esc_html( $this->ref( $tab['title'], $tab_ref ) );
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
     * Apply a developer reference code to UI text.
     *
     * @param string $text UI text.
     * @param string $code Reference code.
     * @return string Referenced UI text.
     */
    private function ref( $text, $code ) {
        if ( function_exists( 'tradepress_ui_reference' ) ) {
            return tradepress_ui_reference( $text, $code );
        }

        return $text;
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
        echo '<p><button type="button" id="discover-tests" class="button button-secondary">' . esc_html( $this->ref( __( 'Discover Tests', 'tradepress' ), 'BUT01' ) ) . '</button></p>';
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
        echo '<p class="description">' . esc_html__( 'Comprehensive testing suite for the Recent Call Register system, validating API call deduplication, platform-aware caching, and cross-feature integration.', 'tradepress' ) . '</p>';
        
        echo '<div class="test-controls" style="margin: 20px 0;">';
        echo '<button id="run-phase3-tests" class="button button-primary">';
        echo esc_html( $this->ref( __( 'Run Phase 3 Tests', 'tradepress' ), 'BUT02' ) );
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
     * Render Scoring Directives tab.
     *
     * @version 1.0.0
     */
    private function render_directives_tab() {
        echo '<h2>' . esc_html( $this->tabs['directives']['title'] ) . '</h2>';
        echo '<p class="description">' . esc_html( $this->tabs['directives']['description'] ) . '</p>';

        echo '<div class="test-controls" style="margin: 20px 0;">';
        echo '<button id="run-directive-tests" class="button button-primary">';
        echo esc_html( $this->ref( __( 'Run All Directive Tests', 'tradepress' ), 'BUT05' ) );
        echo '</button> ';
        echo '<button id="clear-directive-results" class="button" style="display:none">';
        echo esc_html__( 'Clear Results', 'tradepress' );
        echo '</button>';
        echo '</div>';

        echo '<div id="directive-progress" style="display:none;margin-bottom:10px;font-size:13px;color:#555;"></div>';
        echo '<div id="directive-test-results">';
        echo '<p><em>' . esc_html__( 'Click "Run All Directive Tests" to test every scoring directive against dummy data.', 'tradepress' ) . '</em></p>';
        echo '</div>';
        ?>
        <style>
        .tp-dir-table { border-collapse: collapse; width: 100%; font-size: 13px; margin-top: 4px; }
        .tp-dir-table th { background: #f0f0f1; padding: 8px 12px; text-align: left; border-bottom: 2px solid #ccc; font-size: 12px; }
        .tp-dir-table td { padding: 7px 12px; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        .tp-dir-table tr.tp-dir-pass { background: #f0fff4; }
        .tp-dir-table tr.tp-dir-fail { background: #fff5f5; }
        .tp-dir-table tr.tp-dir-ready { background: #f0f7ff; }
        .tp-dir-table tr.tp-dir-pending td { color: #999; font-style: italic; }
        .tp-dir-badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .tp-dir-badge.tested  { background: #d1fae5; color: #065f46; }
        .tp-dir-badge.ready   { background: #dbeafe; color: #1e3a5f; }
        .tp-dir-badge.development { background: #fef9c3; color: #713f12; }
        .tp-dir-badge.unknown { background: #f0f0f1; color: #666; }
        .tp-dir-badge.pending { background: #f0f0f1; color: #999; }
        .tp-dir-badge.error   { background: #fee2e2; color: #991b1b; }
        .tp-dir-tests { display: flex; flex-wrap: wrap; gap: 6px; font-size: 12px; }
        .tp-dir-tests span { white-space: nowrap; }
        .tp-dir-tests .ok   { color: #065f46; }
        .tp-dir-tests .bad  { color: #991b1b; font-weight: 600; }
        .tp-dir-issues { margin: 0; padding: 0 0 0 14px; color: #991b1b; font-size: 12px; }
        .tp-dir-summary { padding: 10px 14px; font-weight: 600; background: #f0f0f1; border-top: 2px solid #ccc; font-size: 13px; display: flex; gap: 16px; }
        .tp-dir-summary span { font-weight: normal; }
        .tp-dir-progress-bar { height: 4px; background: #e0e0e0; border-radius: 2px; margin-bottom: 8px; overflow: hidden; }
        .tp-dir-progress-bar-fill { height: 100%; background: #2271b1; border-radius: 2px; transition: width 0.2s; }
        </style>
        <script>
        (function($) {
            var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
            var nonce   = <?php echo wp_json_encode( wp_create_nonce( 'tradepress_tests' ) ); ?>;
            var counts  = { tested: 0, ready: 0, development: 0, error: 0, done: 0, total: 0 };

            function esc(str) { return $('<span>').text(String(str)).html(); }

            function badge(status) {
                return '<span class="tp-dir-badge ' + esc(status) + '">' + esc(status) + '</span>';
            }

            function renderTests(tests) {
                if (!tests || !Object.keys(tests).length) return '<em style="color:#999">—</em>';
                var out = '<div class="tp-dir-tests">';
                $.each(tests, function(name, passed) {
                    out += '<span class="' + (passed ? 'ok' : 'bad') + '">' +
                           (passed ? '&#10003;' : '&#10007;') + ' ' + esc(name.replace(/_/g,' ')) + '</span>';
                });
                return out + '</div>';
            }

            function renderIssues(issues) {
                if (!issues || !issues.length) return '<span style="color:#065f46">&#10003; None</span>';
                var out = '<ul class="tp-dir-issues">';
                $.each(issues, function(i, issue) { out += '<li>' + esc(issue) + '</li>'; });
                return out + '</ul>';
            }

            function updateSummary() {
                var $s = $('#tp-dir-summary-bar');
                $s.html(
                    '<strong><?php echo esc_js( __( 'Total:', 'tradepress' ) ); ?></strong> <span>' + counts.total + '</span>' +
                    '&nbsp;&nbsp;<strong style="color:#065f46"><?php echo esc_js( __( 'Tested:', 'tradepress' ) ); ?></strong> <span>' + counts.tested + '</span>' +
                    '&nbsp;&nbsp;<strong style="color:#1e3a5f"><?php echo esc_js( __( 'Ready:', 'tradepress' ) ); ?></strong> <span>' + counts.ready + '</span>' +
                    '&nbsp;&nbsp;<strong style="color:#713f12"><?php echo esc_js( __( 'Development:', 'tradepress' ) ); ?></strong> <span>' + counts.development + '</span>' +
                    (counts.error ? '&nbsp;&nbsp;<strong style="color:#991b1b"><?php echo esc_js( __( 'Error:', 'tradepress' ) ); ?></strong> <span>' + counts.error + '</span>' : '')
                );
            }

            function updateProgress() {
                var pct = counts.total ? Math.round((counts.done / counts.total) * 100) : 0;
                $('#tp-dir-progress-fill').css('width', pct + '%');
                $('#directive-progress').text(
                    '<?php echo esc_js( __( 'Testing', 'tradepress' ) ); ?> ' + counts.done + ' / ' + counts.total +
                    (counts.done < counts.total ? ' — <?php echo esc_js( __( 'please wait…', 'tradepress' ) ); ?>' : ' — <?php echo esc_js( __( 'complete', 'tradepress' ) ); ?>')
                );
            }

            function testDirective(id, $row) {
                return $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: { action: 'tradepress_run_tests', nonce: nonce, test_suite: 'directive_single', directive_id: id }
                }).then(function(response) {
                    counts.done++;
                    if (response.success && response.data) {
                        var r = response.data;
                        var status = r.recommended_status || 'error';
                        counts[status] = (counts[status] || 0) + 1;
                        var rowClass = 'tp-dir-' + (status === 'tested' ? 'pass' : status === 'ready' ? 'ready' : 'fail');
                        $row.removeClass('tp-dir-pending').addClass(rowClass);
                        $row.find('.tp-dir-result-cell').html(badge(status));
                        $row.find('.tp-dir-tests-cell').html(renderTests(r.tests));
                        $row.find('.tp-dir-issues-cell').html(renderIssues(r.issues));
                    } else {
                        counts.error++;
                        $row.removeClass('tp-dir-pending').addClass('tp-dir-fail');
                        $row.find('.tp-dir-result-cell').html(badge('error'));
                        $row.find('.tp-dir-tests-cell').html('');
                        $row.find('.tp-dir-issues-cell').html('<span style="color:#991b1b">' + esc(response.data || 'Request failed') + '</span>');
                    }
                    updateProgress();
                    updateSummary();
                }, function() {
                    counts.done++;
                    counts.error++;
                    $row.removeClass('tp-dir-pending').addClass('tp-dir-fail');
                    $row.find('.tp-dir-result-cell').html(badge('error'));
                    $row.find('.tp-dir-issues-cell').html('<span style="color:#991b1b">AJAX error</span>');
                    updateProgress();
                    updateSummary();
                });
            }

            $('#run-directive-tests').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php echo esc_js( __( 'Loading directives…', 'tradepress' ) ); ?>');
                $('#clear-directive-results').hide();
                $('#directive-progress').show();
                counts = { tested: 0, ready: 0, development: 0, error: 0, done: 0, total: 0 };

                // Step 1: load directive list
                $.ajax({
                    url: ajaxUrl, method: 'POST',
                    data: { action: 'tradepress_run_tests', nonce: nonce, test_suite: 'directives_list' }
                }).then(function(resp) {
                    if (!resp.success || !resp.data || !resp.data.directives) {
                        $('#directive-test-results').html('<div class="notice notice-error"><p><?php echo esc_js( __( 'Could not load directive list.', 'tradepress' ) ); ?></p></div>');
                        $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Directive Tests', 'tradepress' ) ); ?>');
                        return;
                    }
                    var directives = resp.data.directives;
                    counts.total = directives.length;

                    // Step 2: render skeleton table
                    var html = '<div class="tp-dir-progress-bar"><div id="tp-dir-progress-fill" class="tp-dir-progress-bar-fill" style="width:0%"></div></div>';
                    html += '<table class="tp-dir-table"><thead><tr>';
                    html += '<th><?php echo esc_js( __( 'Code', 'tradepress' ) ); ?></th>';
                    html += '<th><?php echo esc_js( __( 'Directive', 'tradepress' ) ); ?></th>';
                    html += '<th><?php echo esc_js( __( 'Declared', 'tradepress' ) ); ?></th>';
                    html += '<th><?php echo esc_js( __( 'Result', 'tradepress' ) ); ?></th>';
                    html += '<th><?php echo esc_js( __( 'Tests', 'tradepress' ) ); ?></th>';
                    html += '<th><?php echo esc_js( __( 'Issues', 'tradepress' ) ); ?></th>';
                    html += '</tr></thead><tbody>';
                    $.each(directives, function(i, d) {
                        html += '<tr class="tp-dir-pending" data-directive-id="' + esc(d.id) + '">';
                        html += '<td><code>' + esc(d.code) + '</code></td>';
                        html += '<td><strong>' + esc(d.name) + '</strong><br><code style="font-size:11px;color:#888">' + esc(d.id) + '</code></td>';
                        html += '<td>' + badge(d.development_status || 'unknown') + '</td>';
                        html += '<td class="tp-dir-result-cell">' + badge('pending') + '</td>';
                        html += '<td class="tp-dir-tests-cell"><em style="color:#bbb">—</em></td>';
                        html += '<td class="tp-dir-issues-cell"><em style="color:#bbb">—</em></td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    html += '<div class="tp-dir-summary" id="tp-dir-summary-bar"></div>';
                    $('#directive-test-results').html(html);
                    updateProgress();
                    updateSummary();
                    $btn.text('<?php echo esc_js( __( 'Testing…', 'tradepress' ) ); ?>');

                    // Step 3: test each directive sequentially
                    var chain = $.Deferred().resolve();
                    $.each(directives, function(i, d) {
                        chain = chain.then(function() {
                            var $row = $('tr[data-directive-id="' + d.id + '"]');
                            return testDirective(d.id, $row);
                        });
                    });
                    chain.always(function() {
                        $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Directive Tests', 'tradepress' ) ); ?>');
                        $('#clear-directive-results').show();
                    });

                }, function() {
                    $('#directive-test-results').html('<div class="notice notice-error"><p><?php echo esc_js( __( 'AJAX request failed loading directive list.', 'tradepress' ) ); ?></p></div>');
                    $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Directive Tests', 'tradepress' ) ); ?>');
                });
            });

            $('#clear-directive-results').on('click', function() {
                $(this).hide();
                $('#directive-progress').hide();
                $('#directive-test-results').html('<p><em><?php echo esc_js( __( 'Click "Run All Directive Tests" to test every scoring directive against dummy data.', 'tradepress' ) ); ?></em></p>');
            });
        }(jQuery));
        </script>
            /**
             * Render Scoring Directives tab.
             *
             * @version 1.0.0
             */
            private function render_directives_tab() {
                echo '<h2>' . esc_html( $this->tabs['directives']['title'] ) . '</h2>';
                echo '<p class="description">' . esc_html( $this->tabs['directives']['description'] ) . '</p>';

                echo '<div class="test-controls" style="margin: 20px 0;">';
                echo '<button id="run-directive-tests" class="button button-primary">';
                echo esc_html( $this->ref( __( 'Run All Directive Tests', 'tradepress' ), 'BUT05' ) );
                echo '</button> ';
                echo '<button id="clear-directive-results" class="button" style="display:none">';
                echo esc_html__( 'Clear Results', 'tradepress' );
                echo '</button>';
                echo '</div>';

                echo '<div id="directive-progress" style="display:none;margin-bottom:10px;font-size:13px;color:#555;"></div>';
                echo '<div id="directive-test-results">';
                echo '<p><em>' . esc_html__( 'Click "Run All Directive Tests" to test every scoring directive against dummy data.', 'tradepress' ) . '</em></p>';
                echo '</div>';
                ?>
                <style>
                /* ── Directive Test Table ─────────────────────────────────── */
                .tp-dir-table { border-collapse: collapse; width: 100%; font-size: 13px; margin-top: 4px; }
                .tp-dir-table th { background: #f0f0f1; padding: 8px 12px; text-align: left; border-bottom: 2px solid #ccc; font-size: 12px; }
                .tp-dir-table td { padding: 7px 12px; border-bottom: 1px solid #e0e0e0; vertical-align: middle; }
                .tp-dir-table tr.tp-dir-pass td   { background: #f0fff4; }
                .tp-dir-table tr.tp-dir-fail td   { background: #fff5f5; }
                .tp-dir-table tr.tp-dir-ready td  { background: #f0f7ff; }
                .tp-dir-table tr.tp-dir-pending td { color: #999; font-style: italic; }
                .tp-dir-data-row { cursor: default; }
                .tp-dir-data-row.is-expandable { cursor: pointer; }
                .tp-dir-data-row.is-expandable:hover td { filter: brightness(0.97); }
                .tp-dir-detail-row td { padding: 0 !important; background: #fafafa !important; border-bottom: 2px solid #c8d4e0 !important; }
                /* ── Badges ──────────────────────────────────────────────── */
                .tp-dir-badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
                .tp-dir-badge.tested      { background: #d1fae5; color: #065f46; }
                .tp-dir-badge.ready       { background: #dbeafe; color: #1e3a5f; }
                .tp-dir-badge.development { background: #fef9c3; color: #713f12; }
                .tp-dir-badge.unknown     { background: #f0f0f1; color: #666; }
                .tp-dir-badge.pending     { background: #f0f0f1; color: #999; }
                .tp-dir-badge.error       { background: #fee2e2; color: #991b1b; }
                /* ── Chevron ─────────────────────────────────────────────── */
                .tp-dir-chevron { font-size: 10px; color: #888; display: inline-block; transition: transform 0.18s; user-select: none; }
                .tp-dir-chevron.open { transform: rotate(90deg); }
                /* ── Summary / Progress ──────────────────────────────────── */
                .tp-dir-summary { padding: 10px 14px; font-weight: 600; background: #f0f0f1; border-top: 2px solid #ccc; font-size: 13px; display: flex; gap: 16px; flex-wrap: wrap; }
                .tp-dir-summary span { font-weight: normal; }
                .tp-dir-progress-bar { height: 4px; background: #e0e0e0; border-radius: 2px; margin-bottom: 8px; overflow: hidden; }
                .tp-dir-progress-bar-fill { height: 100%; background: #2271b1; border-radius: 2px; transition: width 0.2s; }
                /* ── Accordion Panel ─────────────────────────────────────── */
                .tp-dir-panel { padding: 14px 18px; display: flex; flex-direction: column; gap: 12px; }
                /* ── Step Boxes ──────────────────────────────────────────── */
                .tp-dir-steps-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 7px; }
                .tp-dir-step-box { display: flex; align-items: flex-start; gap: 8px; padding: 8px 10px; border-radius: 4px; border: 1px solid; }
                .tp-dir-step-box.step-pass { background: #f0fdf4; border-color: #bbf7d0; }
                .tp-dir-step-box.step-fail { background: #fff1f2; border-color: #fecdd3; }
                .step-icon { font-size: 14px; font-weight: 700; line-height: 1.4; flex-shrink: 0; }
                .step-pass .step-icon { color: #16a34a; }
                .step-fail .step-icon { color: #dc2626; }
                .step-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: #333; }
                .step-detail { font-size: 11px; color: #666; margin-top: 2px; word-break: break-all; }
                .step-detail code { font-size: 10px; color: #555; background: rgba(0,0,0,.04); padding: 1px 3px; border-radius: 2px; }
                /* ── Math Box ────────────────────────────────────────────── */
                .tp-dir-math-box { border: 1px solid #dde3ea; border-radius: 4px; overflow: hidden; }
                .tp-dir-math-head { background: #eef2f7; padding: 5px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #444; border-bottom: 1px solid #dde3ea; }
                .tp-dir-math-inner { display: flex; }
                .tp-dir-math-input  { flex: 1; padding: 10px 14px; border-right: 1px solid #dde3ea; min-width: 0; }
                .tp-dir-math-output { width: 200px; flex-shrink: 0; padding: 10px 14px; }
                .tp-dir-math-sub { font-size: 11px; font-weight: 600; color: #666; margin-bottom: 6px; }
                .tp-dir-input-tbl { font-size: 11px; border-collapse: collapse; width: 100%; }
                .tp-dir-input-tbl td { padding: 2px 5px; border: none; }
                .tp-dir-input-tbl td:first-child { color: #999; width: 130px; }
                .tp-dir-input-tbl td:last-child  { font-weight: 500; color: #333; font-family: monospace; font-size: 11px; }
                .tp-dir-score-display { font-size: 30px; font-weight: 700; line-height: 1.2; margin: 6px 0 2px; }
                .score-sep, .score-max { font-size: 16px; color: #aaa; font-weight: 400; }
                .score-pct { font-size: 12px; color: #888; margin-bottom: 8px; }
                .tp-dir-score-bar { height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden; }
                .tp-dir-score-bar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
                /* ── Issues ──────────────────────────────────────────────── */
                .tp-dir-panel-issues { background: #fff5f5; border: 1px solid #fecaca; border-radius: 4px; padding: 8px 12px; }
                .tp-dir-panel-issues ul { margin: 4px 0 0 16px; padding: 0; font-size: 12px; color: #991b1b; }
                </style>
                <script>
                (function($) {
                    var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
                    var nonce   = <?php echo wp_json_encode( wp_create_nonce( 'tradepress_tests' ) ); ?>;
                    var counts  = { tested: 0, ready: 0, development: 0, error: 0, done: 0, total: 0 };

                    function esc(str) { return $('<span>').text(String(str)).html(); }

                    function badge(status) {
                        return '<span class="tp-dir-badge ' + esc(status) + '">' + esc(status) + '</span>';
                    }

                    // Human-readable metadata for each test step key.
                    var stepMeta = {
                        class_file:    { label: 'Class File',        desc: function(r) { var d = r.detail || {}; return d.class_file_path ? '<code>' + esc(d.class_file_path) + '</code>' : 'File not found'; } },
                        config_form:   { label: 'Config Form',       desc: function()  { return 'Admin UI partial file'; } },
                        class_found:   { label: 'Class Declared',    desc: function(r) { var d = r.detail || {}; return d.class_name ? '<code>' + esc(d.class_name) + '</code>' : 'No class detected'; } },
                        instantiation: { label: 'Instantiation',     desc: function()  { return 'new ClassName() succeeded'; } },
                        has_calculate: { label: 'calculate_score()', desc: function()  { return 'Method exists on class'; } },
                        has_max_score: { label: 'get_max_score()',   desc: function()  { return 'Method exists on class'; } },
                        scoring_runs:  { label: 'Scoring Executes',  desc: function(r) { var d = r.detail || {}; return (d.score_value !== null && d.score_value !== undefined) ? 'Returned: <strong>' + esc(String(d.score_value)) + '</strong>' : 'Returned a valid type'; } }
                    };

                    function fmtNum(n) { return Number(n).toLocaleString(); }

                    // Build the full detail accordion panel for one directive result.
                    function renderDetailPanel(r) {
                        var detail = r.detail  || {};
                        var tests  = r.tests   || {};
                        var issues = r.issues  || [];
                        var di     = detail.dummy_input || {};

                        // ── Step boxes ─────────────────────────────────────────────
                        var stepsHtml = '<div class="tp-dir-steps-grid">';
                        $.each(tests, function(key, passed) {
                            var meta     = stepMeta[key] || { label: key.replace(/_/g, ' '), desc: function() { return ''; } };
                            var descHtml = meta.desc(r);
                            stepsHtml += '<div class="tp-dir-step-box ' + (passed ? 'step-pass' : 'step-fail') + '">';
                            stepsHtml += '<div class="step-icon">' + (passed ? '&#10003;' : '&#10007;') + '</div>';
                            stepsHtml += '<div class="step-body">';
                            stepsHtml += '<div class="step-label">' + esc(meta.label) + '</div>';
                            if (descHtml) { stepsHtml += '<div class="step-detail">' + descHtml + '</div>'; }
                            stepsHtml += '</div></div>';
                        });
                        stepsHtml += '</div>';

                        // ── Score math panel ────────────────────────────────────────
                        var mathHtml = '';
                        if (detail.score_value !== null && detail.score_value !== undefined) {
                            var pct      = detail.score_percent || 0;
                            var barColor = pct >= 75 ? '#16a34a' : pct >= 50 ? '#2271b1' : pct >= 25 ? '#b45309' : '#dc2626';
                            mathHtml += '<div class="tp-dir-math-box">';
                            mathHtml += '<div class="tp-dir-math-head">Score Calculation</div>';
                            mathHtml += '<div class="tp-dir-math-inner">';
                            // Left column — input data
                            mathHtml += '<div class="tp-dir-math-input">';
                            mathHtml += '<div class="tp-dir-math-sub">Dummy Input <em>(NVDA)</em></div>';
                            mathHtml += '<table class="tp-dir-input-tbl">';
                            if (di.price            !== undefined) { mathHtml += '<tr><td>Price</td><td>$' + esc(di.price) + '</td></tr>'; }
                            if (di.rsi              !== undefined) { mathHtml += '<tr><td>RSI (14)</td><td>' + esc(di.rsi) + '</td></tr>'; }
                            if (di.macd_histogram   !== undefined) { mathHtml += '<tr><td>MACD Histogram</td><td>' + esc(di.macd_histogram) + '</td></tr>'; }
                            if (di.macd_signal      !== undefined) { mathHtml += '<tr><td>MACD Signal</td><td>' + esc(di.macd_signal) + '</td></tr>'; }
                            if (di.volume          !== undefined) { mathHtml += '<tr><td>Volume</td><td>' + fmtNum(di.volume) + '</td></tr>'; }
                            if (di.avg_volume      !== undefined && di.volume !== undefined) {
                                var volPct = Math.round((di.volume / di.avg_volume - 1) * 100);
                                mathHtml += '<tr><td>Avg Volume</td><td>' + fmtNum(di.avg_volume) + ' <em style="color:#888">(vol ' + (volPct >= 0 ? '+' : '') + volPct + '%)</em></td></tr>';
                            }
                            if (di.sma_50          !== undefined) { mathHtml += '<tr><td>SMA 50</td><td>' + esc(di.sma_50) + '</td></tr>'; }
                            if (di.ema_20          !== undefined) { mathHtml += '<tr><td>EMA 20</td><td>' + esc(di.ema_20) + '</td></tr>'; }
                            if (di.bollinger_lower !== undefined) {
                                mathHtml += '<tr><td>Bollinger Band</td><td>' + esc(di.bollinger_lower) + ' / <strong>' + esc(di.bollinger_middle) + '</strong> / ' + esc(di.bollinger_upper) + '</td></tr>';
                            }
                            if (di.earnings_days   !== undefined) { mathHtml += '<tr><td>Earnings</td><td>' + esc(di.earnings_days) + ' days out</td></tr>'; }
                            if (di.pe_ratio        !== undefined) { mathHtml += '<tr><td>P/E Ratio</td><td>' + esc(di.pe_ratio) + '</td></tr>'; }
                            mathHtml += '</table></div>';
                            // Right column — score result
                            mathHtml += '<div class="tp-dir-math-output">';
                            mathHtml += '<div class="tp-dir-math-sub">Score Result</div>';
                            mathHtml += '<div class="tp-dir-score-display">';
                            mathHtml += '<span class="score-num" style="color:' + barColor + '">' + esc(detail.score_value) + '</span>';
                            mathHtml += '<span class="score-sep"> / </span>';
                            mathHtml += '<span class="score-max">' + esc(detail.max_score_value) + '</span>';
                            mathHtml += '</div>';
                            mathHtml += '<div class="score-pct">' + esc(pct) + '%</div>';
                            mathHtml += '<div class="tp-dir-score-bar"><div class="tp-dir-score-bar-fill" style="width:' + Math.min(pct, 100) + '%;background:' + barColor + '"></div></div>';
                            mathHtml += '</div>';
                            mathHtml += '</div></div>'; // close math-inner + math-box
                        }

                        // ── Issues ─────────────────────────────────────────────────
                        var issuesHtml = '';
                        if (issues.length) {
                            issuesHtml += '<div class="tp-dir-panel-issues">';
                            issuesHtml += '<strong style="color:#991b1b">&#9888; Issues</strong>';
                            issuesHtml += '<ul>';
                            $.each(issues, function(i, issue) { issuesHtml += '<li>' + esc(issue) + '</li>'; });
                            issuesHtml += '</ul></div>';
                        }

                        return '<div class="tp-dir-panel">' + stepsHtml + mathHtml + issuesHtml + '</div>';
                    }

                    function updateSummary() {
                        var $s = $('#tp-dir-summary-bar');
                        $s.html(
                            '<strong><?php echo esc_js( __( 'Total:', 'tradepress' ) ); ?></strong> <span>' + counts.total + '</span>' +
                            '&nbsp;&nbsp;<strong style="color:#065f46"><?php echo esc_js( __( 'Tested:', 'tradepress' ) ); ?></strong> <span>' + counts.tested + '</span>' +
                            '&nbsp;&nbsp;<strong style="color:#1e3a5f"><?php echo esc_js( __( 'Ready:', 'tradepress' ) ); ?></strong> <span>' + counts.ready + '</span>' +
                            '&nbsp;&nbsp;<strong style="color:#713f12"><?php echo esc_js( __( 'Development:', 'tradepress' ) ); ?></strong> <span>' + counts.development + '</span>' +
                            (counts.error ? '&nbsp;&nbsp;<strong style="color:#991b1b"><?php echo esc_js( __( 'Error:', 'tradepress' ) ); ?></strong> <span>' + counts.error + '</span>' : '')
                        );
                    }

                    function updateProgress() {
                        var pct = counts.total ? Math.round((counts.done / counts.total) * 100) : 0;
                        $('#tp-dir-progress-fill').css('width', pct + '%');
                        $('#directive-progress').text(
                            '<?php echo esc_js( __( 'Testing', 'tradepress' ) ); ?> ' + counts.done + ' / ' + counts.total +
                            (counts.done < counts.total ? ' — <?php echo esc_js( __( 'please wait\u2026', 'tradepress' ) ); ?>' : ' — <?php echo esc_js( __( 'complete', 'tradepress' ) ); ?>')
                        );
                    }

                    function testDirective(id, $row) {
                        return $.ajax({
                            url: ajaxUrl,
                            method: 'POST',
                            data: { action: 'tradepress_run_tests', nonce: nonce, test_suite: 'directive_single', directive_id: id }
                        }).then(function(response) {
                            counts.done++;
                            var $detailRow = $row.next('.tp-dir-detail-row');
                            if (response.success && response.data) {
                                var r      = response.data;
                                var status = r.recommended_status || 'error';
                                counts[status] = (counts[status] || 0) + 1;
                                var rowClass = 'tp-dir-' + (status === 'tested' ? 'pass' : status === 'ready' ? 'ready' : 'fail');
                                $row.removeClass('tp-dir-pending').addClass(rowClass + ' is-expandable');
                                $row.find('.tp-dir-result-cell').html(badge(status));
                                $row.find('.tp-dir-toggle-cell').html('<span class="tp-dir-chevron">&#9654;</span>');
                                $detailRow.find('.tp-dir-panel-wrap').html(renderDetailPanel(r));
                            } else {
                                var errMsg = (response && response.data) ? response.data : 'Request failed';
                                counts.error++;
                                $row.removeClass('tp-dir-pending').addClass('tp-dir-fail is-expandable');
                                $row.find('.tp-dir-result-cell').html(badge('error'));
                                $row.find('.tp-dir-toggle-cell').html('<span class="tp-dir-chevron">&#9654;</span>');
                                $detailRow.find('.tp-dir-panel-wrap').html(renderDetailPanel({ tests: {}, issues: [errMsg], detail: {} }));
                            }
                            updateProgress();
                            updateSummary();
                        }, function() {
                            counts.done++;
                            counts.error++;
                            $row.removeClass('tp-dir-pending').addClass('tp-dir-fail is-expandable');
                            $row.find('.tp-dir-result-cell').html(badge('error'));
                            $row.find('.tp-dir-toggle-cell').html('<span class="tp-dir-chevron">&#9654;</span>');
                            $row.next('.tp-dir-detail-row').find('.tp-dir-panel-wrap').html(renderDetailPanel({ tests: {}, issues: ['AJAX error'], detail: {} }));
                            updateProgress();
                            updateSummary();
                        });
                    }

                    // Accordion: clicking an expandable row toggles the detail row below it.
                    $('#directive-test-results').on('click', '.tp-dir-data-row.is-expandable', function() {
                        var $row    = $(this);
                        var $detail = $row.next('.tp-dir-detail-row');
                        var isOpen  = $detail.is(':visible');
                        $detail.toggle(!isOpen);
                        $row.find('.tp-dir-chevron').toggleClass('open', !isOpen);
                    });

                    $('#run-directive-tests').on('click', function() {
                        var $btn = $(this);
                        $btn.prop('disabled', true).text('<?php echo esc_js( __( 'Loading directives\u2026', 'tradepress' ) ); ?>');
                        $('#clear-directive-results').hide();
                        $('#directive-progress').show();
                        counts = { tested: 0, ready: 0, development: 0, error: 0, done: 0, total: 0 };

                        // Step 1: load directive list
                        $.ajax({
                            url: ajaxUrl, method: 'POST',
                            data: { action: 'tradepress_run_tests', nonce: nonce, test_suite: 'directives_list' }
                        }).then(function(resp) {
                            if (!resp.success || !resp.data || !resp.data.directives) {
                                $('#directive-test-results').html('<div class="notice notice-error"><p><?php echo esc_js( __( 'Could not load directive list.', 'tradepress' ) ); ?></p></div>');
                                $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Directive Tests', 'tradepress' ) ); ?>');
                                return;
                            }
                            var directives = resp.data.directives;
                            counts.total   = directives.length;

                            // Step 2: render skeleton table (no Tests/Issues columns — replaced by accordion)
                            var html  = '<div class="tp-dir-progress-bar"><div id="tp-dir-progress-fill" class="tp-dir-progress-bar-fill" style="width:0%"></div></div>';
                            html += '<table class="tp-dir-table"><thead><tr>';
                            html += '<th style="width:60px"><?php echo esc_js( __( 'Code', 'tradepress' ) ); ?></th>';
                            html += '<th><?php echo esc_js( __( 'Directive', 'tradepress' ) ); ?></th>';
                            html += '<th style="width:110px"><?php echo esc_js( __( 'Declared', 'tradepress' ) ); ?></th>';
                            html += '<th style="width:100px"><?php echo esc_js( __( 'Result', 'tradepress' ) ); ?></th>';
                            html += '<th style="width:26px"></th>';
                            html += '</tr></thead><tbody>';
                            $.each(directives, function(i, d) {
                                // Data row
                                html += '<tr class="tp-dir-pending tp-dir-data-row" data-directive-id="' + esc(d.id) + '">';
                                html += '<td><code>' + esc(d.code) + '</code></td>';
                                html += '<td><strong>' + esc(d.name) + '</strong><br><code style="font-size:11px;color:#888">' + esc(d.id) + '</code></td>';
                                html += '<td>' + badge(d.development_status || 'unknown') + '</td>';
                                html += '<td class="tp-dir-result-cell">' + badge('pending') + '</td>';
                                html += '<td class="tp-dir-toggle-cell" style="text-align:center"></td>';
                                html += '</tr>';
                                // Hidden detail row — filled after each directive test completes
                                html += '<tr class="tp-dir-detail-row" style="display:none"><td colspan="5"><div class="tp-dir-panel-wrap"></div></td></tr>';
                            });
                            html += '</tbody></table>';
                            html += '<div class="tp-dir-summary" id="tp-dir-summary-bar"></div>';
                            $('#directive-test-results').html(html);
                            updateProgress();
                            updateSummary();
                            $btn.text('<?php echo esc_js( __( 'Testing\u2026', 'tradepress' ) ); ?>');

                            // Step 3: test each directive sequentially, one AJAX call at a time
                            var chain = $.Deferred().resolve();
                            $.each(directives, function(i, d) {
                                chain = chain.then(function() {
                                    var $row = $('tr.tp-dir-data-row[data-directive-id="' + d.id + '"]');
                                    return testDirective(d.id, $row);
                                });
                            });
                            chain.always(function() {
                                $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Directive Tests', 'tradepress' ) ); ?>');
                                $('#clear-directive-results').show();
                            });

                        }, function() {
                            $('#directive-test-results').html('<div class="notice notice-error"><p><?php echo esc_js( __( 'AJAX request failed loading directive list.', 'tradepress' ) ); ?></p></div>');
                            $btn.prop('disabled', false).text('<?php echo esc_js( __( 'Run All Directive Tests', 'tradepress' ) ); ?>');
                        });
                    });

                    $('#clear-directive-results').on('click', function() {
                        $(this).hide();
                        $('#directive-progress').hide();
                        $('#directive-test-results').html('<p><em><?php echo esc_js( __( 'Click "Run All Directive Tests" to test every scoring directive against dummy data.', 'tradepress' ) ); ?></em></p>');
                    });
                }(jQuery));
                </script>
                <?php
            }
        <?php
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

        echo '<h2>' . esc_html($this->tabs['trading212']['title']) . '</h2>';
        echo '<p class="description">' . esc_html($this->tabs['trading212']['description']) . '</p>';

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
        echo esc_html( $this->ref( __( 'Run Trading212 Tests', 'tradepress' ), 'BUT03' ) );
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
        echo '<button id="run-ui-crawl" class="button button-primary">' . esc_html( $this->ref( __( 'Run UI Crawl', 'tradepress' ), 'BUT04' ) ) . '</button>';
        echo '<button id="clear-ui-crawl" class="button">' . esc_html__( 'Clear Results', 'tradepress' ) . '</button>';
        echo '</div>';

        echo '<div class="tradepress-ui-crawl__panels">';
        echo '<div class="tradepress-ui-crawl__panel tradepress-ui-crawl__panel--summary">';
        echo '<h3>' . esc_html( $this->ref( __( 'Run Summary', 'tradepress' ), 'PAN04' ) ) . '</h3>';
        echo '<div id="ui-crawl-summary" class="tradepress-ui-crawl__summary"></div>';
        echo '</div>';
        echo '<div class="tradepress-ui-crawl__panel tradepress-ui-crawl__panel--results">';
        echo '<h3>' . esc_html( $this->ref( __( 'URL Results', 'tradepress' ), 'PAN05' ) ) . '</h3>';
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

        $test_suite   = sanitize_text_field( wp_unslash( $_POST['test_suite'] ?? '' ) );
        $test_id      = isset( $_POST['test_id'] ) ? absint( wp_unslash( $_POST['test_id'] ) ) : 0;
        $directive_id = sanitize_key( wp_unslash( $_POST['directive_id'] ?? '' ) );

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

                case 'directives_list':
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-register.php';
                    $all_system = tradepress_get_all_system_directives();
                    $list       = array();
                    foreach ( $all_system as $dir_id => $dir_def ) {
                        $list[] = array(
                            'id'                 => $dir_id,
                            'name'               => $dir_def['name'] ?? $dir_id,
                            'code'               => $dir_def['code'] ?? '',
                            'development_status' => $dir_def['development_status'] ?? 'unknown',
                            'category'           => $dir_def['category'] ?? '',
                        );
                    }
                    $results = array( 'directives' => $list );
                    break;

                case 'directive_single':
                    if ( '' === $directive_id ) {
                        throw new Exception( 'directive_id is required' );
                    }
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-register.php';
                    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

                    // Provide stubs for dependencies that directive classes may reference
                    // but which are not available outside the full WordPress/plugin bootstrap.
                    if ( ! class_exists( 'TradePress_Call_Register' ) ) {
                        // phpcs:ignore
                        eval( '
                            class TradePress_Call_Register {
                                public static function generate_serial() { return "tp-test-serial"; }
                                public static function get_cached_result() { return false; }
                                public static function cache_result() { return true; }
                                public static function __callStatic( $name, $arguments ) { return false; }
                            }
                        ' );
                    }

                    // Do not stub TradePress_Directive_Logger.
                    // Some directives require_once the real logger file directly; stubbing the class first
                    // causes a fatal redeclare when that file is included.
                    if ( 'yes' === get_option( 'bugnet_output_directives' ) ) {
                        $directive_logger_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
                        if ( file_exists( $directive_logger_file ) ) {
                            require_once $directive_logger_file;
                        }
                    }

                    if ( ! class_exists( 'TradePress_Developer_Flow_Logger' ) ) {
                        // phpcs:ignore
                        eval( 'class TradePress_Developer_Flow_Logger {
                            public static function start_flow() {}
                            public static function log_action() {}
                            public static function log_cache() {}
                            public static function log_api() {}
                            public static function log_decision() {}
                            public static function end_flow() {}
                            public static function __callStatic( $name, $arguments ) {}
                        }' );
                    }

                    // Some directives extend TradePress_Base_Directive, which is not loaded in this test context.
                    // Provide a lightweight fallback so those classes can still be instantiated and evaluated.
                    if ( ! class_exists( 'TradePress_Base_Directive' ) ) {
                        // phpcs:ignore
                        eval( '
                            abstract class TradePress_Base_Directive extends TradePress_Scoring_Directive_Base {
                                protected $directive_id = "";
                                protected $directive_code = "";

                                public function __construct() {}

                                public function get_max_score( $config = array() ) {
                                    return isset( $this->max_score ) ? (float) $this->max_score : 100.0;
                                }

                                public function get_explanation( $config = array() ) {
                                    return "";
                                }
                            }
                        ' );
                    }

                    $all_system = tradepress_get_all_system_directives();
                    if ( ! isset( $all_system[ $directive_id ] ) ) {
                        throw new Exception( 'Unknown directive: ' . $directive_id );
                    }
                    $def = $all_system[ $directive_id ];

                    // Files use hyphens; directive IDs use underscores — try both.
                    $hyphen_id  = str_replace( '_', '-', $directive_id );
                    $dir_file   = TRADEPRESS_PLUGIN_DIR_PATH . "includes/scoring-system/directives/{$directive_id}.php";
                    $dir_file_h = TRADEPRESS_PLUGIN_DIR_PATH . "includes/scoring-system/directives/{$hyphen_id}.php";
                    if ( ! file_exists( $dir_file ) && file_exists( $dir_file_h ) ) {
                        $dir_file = $dir_file_h;
                    }

                    $result = array(
                        'id'                 => $directive_id,
                        'name'               => $def['name'] ?? $directive_id,
                        'code'               => $def['code'] ?? '',
                        'development_status' => $def['development_status'] ?? 'unknown',
                        'tests'              => array(),
                        'issues'             => array(),
                        'recommended_status' => 'development',
                        'detail'             => array(
                            'class_file_path' => str_replace( TRADEPRESS_PLUGIN_DIR_PATH, '', $dir_file ),
                            'class_name'      => '',
                            'score_value'     => null,
                            'max_score_value' => null,
                            'score_percent'   => null,
                            'dummy_input'     => array(
                                'symbol'           => 'NVDA',
                                'price'            => 450.0,
                                'rsi'              => 35.5,
                                'macd_histogram'   => 0.7,
                                'macd_signal'      => 1.8,
                                'volume'           => 50000000,
                                'avg_volume'       => 35000000,
                                'sma_50'           => 440.0,
                                'ema_20'           => 445.0,
                                'bollinger_upper'  => 460.0,
                                'bollinger_middle' => 450.0,
                                'bollinger_lower'  => 440.0,
                                'earnings_days'    => 5,
                                'pe_ratio'         => 65.5,
                            ),
                        ),
                    );

                    // Test 1: class file exists
                    $result['tests']['class_file'] = file_exists( $dir_file );
                    if ( ! $result['tests']['class_file'] ) {
                        $result['issues'][] = "Missing class file (tried: {$directive_id}.php and {$hyphen_id}.php)";
                    }

                    // Test 2: config form exists (for technical indicators)
                    if ( isset( $def['technical_indicator'] ) ) {
                        $config_file   = TRADEPRESS_PLUGIN_DIR_PATH . "admin/page/scoring-directives/directives-partials/{$directive_id}.php";
                        $config_file_h = TRADEPRESS_PLUGIN_DIR_PATH . "admin/page/scoring-directives/directives-partials/{$hyphen_id}.php";
                        $config_exists = file_exists( $config_file ) || file_exists( $config_file_h );
                        $result['tests']['config_form'] = $config_exists;
                        if ( ! $config_exists ) {
                            $result['issues'][] = "Missing config form: {$directive_id}.php";
                        }
                    }

                    // Test 3 & 4: load class, instantiate, run calculate_score
                    if ( $result['tests']['class_file'] ) {
                        $before = get_declared_classes();
                        try {
                            require_once $dir_file;
                            $after       = get_declared_classes();
                            $new_classes = array_diff( $after, $before );
                            // Find a class that extends a known base or contains 'directive'/'tradepress'
                            $found_class = '';
                            foreach ( $new_classes as $cls ) {
                                $parents = class_parents( $cls );
                                if (
                                    isset( $parents['TradePress_Scoring_Directive_Base'] ) ||
                                    isset( $parents['TradePress_Base_Directive'] ) ||
                                    false !== stripos( $cls, 'directive' ) ||
                                    false !== stripos( $cls, 'tradepress' )
                                ) {
                                    $found_class = $cls;
                                    break;
                                }
                            }
                            if ( '' === $found_class && ! empty( $new_classes ) ) {
                                $found_class = reset( $new_classes );
                            }
                            $result['tests']['class_found'] = '' !== $found_class;
                            if ( '' === $found_class ) {
                                $result['issues'][] = 'No directive class found in file after loading';
                                                        $result['detail']['class_name'] = $found_class;
                            }
                        } catch ( \Throwable $load_err ) {
                            $result['tests']['class_found'] = false;
                            $result['issues'][]             = 'File load error: ' . $load_err->getMessage();
                            $found_class                    = '';
                        }

                        if ( '' !== $found_class ) {
                            try {
                                $instance = new $found_class();
                                $result['tests']['instantiation'] = true;
                                $result['tests']['has_calculate'] = method_exists( $instance, 'calculate_score' );
                                $result['tests']['has_max_score'] = method_exists( $instance, 'get_max_score' );

                                if ( $result['tests']['has_calculate'] ) {
                                    $dummy = array(
                                        'symbol'       => 'NVDA',
                                        'price'        => 450.0,
                                        'volume'       => 50000000,
                                        'avg_volume'   => 35000000,
                                        'technical'    => array(
                                            'rsi'       => 35.5,
                                            'adx'       => array( 'adx' => 28.5, 'plus_di' => 25.2, 'minus_di' => 18.7 ),
                                            'macd'      => array( 'macd' => 2.5, 'signal' => 1.8, 'histogram' => 0.7 ),
                                            'bollinger' => array( 'upper' => 460, 'middle' => 450, 'lower' => 440 ),
                                            'ema_20'    => 445.0,
                                            'sma_50'    => 440.0,
                                            'obv'       => 1500000000,
                                        ),
                                        'fundamentals' => array( 'market_cap' => 1100000000000, 'pe_ratio' => 65.5 ),
                                        'earnings'     => array( array( 'date' => gmdate( 'Y-m-d', strtotime( '+5 days' ) ), 'estimate' => 5.25 ) ),
                                    );
                                    $score = $instance->calculate_score( $dummy );
                                    $result['tests']['scoring_runs'] = is_numeric( $score ) || is_array( $score );
                                    if ( ! $result['tests']['scoring_runs'] ) {
                                        $result['issues'][] = 'calculate_score() returned invalid type: ' . gettype( $score );
                                                                        if ( $result['tests']['scoring_runs'] ) {
                                                                            $score_num = is_array( $score ) ? ( isset( $score['score'] ) ? (float) $score['score'] : 0.0 ) : (float) $score;
                                                                            $score_max = method_exists( $instance, 'get_max_score' ) ? (float) $instance->get_max_score() : 100.0;
                                                                            $result['detail']['score_value']     = round( $score_num, 4 );
                                                                            $result['detail']['max_score_value'] = round( $score_max, 2 );
                                                                            $result['detail']['score_percent']   = $score_max > 0.0 ? round( ( $score_num / $score_max ) * 100.0, 1 ) : 0.0;
                                                                        }
                                    }
                                }
                            } catch ( \Throwable $inst_err ) {
                                $result['tests']['instantiation'] = false;
                                $result['issues'][]               = 'Instantiation error: ' . $inst_err->getMessage();
                            }
                        }
                    }

                    // Determine recommended status
                    $all_passed = ! empty( $result['tests'] ) && ! in_array( false, $result['tests'], true );
                    $pass_count = count( array_filter( $result['tests'] ) );
                    $total      = count( $result['tests'] );
                    if ( $all_passed && empty( $result['issues'] ) ) {
                        $result['recommended_status'] = 'tested';
                    } elseif ( $total > 0 && ( $pass_count / $total ) >= 0.75 ) {
                        $result['recommended_status'] = 'ready';
                    } else {
                        $result['recommended_status'] = 'development';
                    }

                    $results = $result;
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
