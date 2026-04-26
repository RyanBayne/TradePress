<?php
/**
 * TradePress Tests Page - Tabbed Interface
 * 
 * Main controller for the Tests page with tabbed navigation
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Admin_Tests_Page {
    
    private $plugin_name;
    private $version;
    
    public function __construct($plugin_name = 'tradepress', $version = '1.0.0') {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Main output method for the Tests page
     */
    public function output() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'phase3';
        
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('TradePress Tests', 'tradepress') . '</h1>';
        
        // Tab navigation
        $this->render_tab_navigation($active_tab);
        
        // Tab content
        $this->render_tab_content($active_tab);
        
        echo '</div>';
    }
    
    /**
     * Render tab navigation
     */
    private function render_tab_navigation($active_tab) {
        $tabs = $this->get_tabs();
        
        echo '<nav class="nav-tab-wrapper">';
        foreach ($tabs as $tab_key => $tab_data) {
            $class = ($active_tab === $tab_key) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $url = admin_url('admin.php?page=tradepress-tests&tab=' . $tab_key);
            echo '<a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">';
            echo esc_html($tab_data['title']);
            echo '</a>';
        }
        echo '</nav>';
    }
    
    /**
     * Render tab content
     */
    private function render_tab_content($active_tab) {
        echo '<div class="tab-content">';
        
        switch ($active_tab) {
            case 'phase3':
                $this->render_phase3_tab();
                break;
                
            default:
                $this->render_phase3_tab();
                break;
        }
        
        echo '</div>';
    }
    
    /**
     * Get available tabs
     */
    private function get_tabs() {
        return [
            'phase3' => [
                'title' => __('Phase 3 Tests', 'tradepress'),
                'description' => __('Recent Call Register testing suite', 'tradepress')
            ]
            // Future tabs will be added here
        ];
    }
    
    /**
     * Render Phase 3 tab content
     */
    private function render_phase3_tab() {
        ?>
        <div class="test-tab-content">
            <h2><?php esc_html_e('Recent Call Register Tests', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Comprehensive testing suite for the Recent Call Register system, validating API call deduplication, platform-aware caching, and cross-feature integration.', 'tradepress'); ?></p>
            
            <div class="test-controls" style="margin: 20px 0;">
                <button id="run-phase3-tests" class="button button-primary">
                    <?php esc_html_e('Run Phase 3 Tests', 'tradepress'); ?>
                </button>
                <button id="clear-test-results" class="button">
                    <?php esc_html_e('Clear Results', 'tradepress'); ?>
                </button>
            </div>
            
            <div id="test-results" style="margin-top: 20px;">
                <p><em><?php esc_html_e('Click "Run Phase 3 Tests" to execute the Recent Call Register test suite.', 'tradepress'); ?></em></p>
            </div>
        </div>
        
        <style>
        .test-tab-content {
            padding: 20px 0;
        }
        .test-result {
            margin: 10px 0;
            padding: 15px;
            border-left: 4px solid #ccc;
            background: #f9f9f9;
        }
        .test-result.passed {
            border-left-color: #46b450;
            background: #f0fff4;
        }
        .test-result.failed {
            border-left-color: #dc3232;
            background: #fff0f0;
        }
        .test-result.error {
            border-left-color: #ffb900;
            background: #fffbf0;
        }
        .test-result.warning {
            border-left-color: #ff8c00;
            background: #fff8f0;
        }
        .test-details {
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
            background: #fff;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .performance-summary {
            background: #e8f4fd;
            border: 1px solid #0073aa;
            padding: 15px;
            margin: 20px 0;
        }
        .recommendations {
            background: #fff2cc;
            border: 1px solid #d4a017;
            padding: 15px;
            margin: 20px 0;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#run-phase3-tests').click(function() {
                var button = $(this);
                button.prop('disabled', true).text('<?php esc_html_e('Running Tests...', 'tradepress'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tradepress_run_tests',
                        test_suite: 'phase3',
                        nonce: '<?php echo wp_create_nonce('tradepress_tests'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            displayTestResults(response.data);
                        } else {
                            $('#test-results').html('<div class="notice notice-error"><p><?php esc_html_e('Error:', 'tradepress'); ?> ' + response.data + '</p></div>');
                        }
                    },
                    error: function() {
                        $('#test-results').html('<div class="notice notice-error"><p><?php esc_html_e('AJAX request failed', 'tradepress'); ?></p></div>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php esc_html_e('Run Phase 3 Tests', 'tradepress'); ?>');
                    }
                });
            });
            
            $('#clear-test-results').click(function() {
                $('#test-results').html('<p><em><?php esc_html_e('Test results cleared.', 'tradepress'); ?></em></p>');
            });
            
            function displayTestResults(results) {
                var html = '<h2><?php esc_html_e('Test Results', 'tradepress'); ?></h2>';
                
                // Overall status
                html += '<div class="test-result ' + results.overall_status + '">';
                html += '<h3><?php esc_html_e('Overall Status:', 'tradepress'); ?> ' + results.overall_status.toUpperCase() + '</h3>';
                html += '<p><?php esc_html_e('Execution Time:', 'tradepress'); ?> ' + results.execution_time + '</p>';
                html += '<p><?php esc_html_e('Requirements Satisfied:', 'tradepress'); ?> ' + results.requirements_satisfied.length + '</p>';
                html += '</div>';
                
                // Individual tests
                html += '<h3><?php esc_html_e('Individual Test Results', 'tradepress'); ?></h3>';
                results.tests.forEach(function(test) {
                    html += '<div class="test-result ' + test.status + '">';
                    html += '<h4>' + test.name + ' (' + test.status.toUpperCase() + ')</h4>';
                    html += '<p><strong><?php esc_html_e('Requirement:', 'tradepress'); ?></strong> ' + test.requirement + '</p>';
                    html += '<p><strong><?php esc_html_e('Execution Time:', 'tradepress'); ?></strong> ' + test.execution_time_ms + 'ms</p>';
                    
                    if (test.details && test.details.length > 0) {
                        html += '<div class="test-details">';
                        test.details.forEach(function(detail) {
                            html += detail + '<br>';
                        });
                        html += '</div>';
                    }
                    html += '</div>';
                });
                
                // Performance summary
                html += '<div class="performance-summary">';
                html += '<h3><?php esc_html_e('Performance Summary', 'tradepress'); ?></h3>';
                html += '<p><strong><?php esc_html_e('API Calls Saved:', 'tradepress'); ?></strong> ' + results.performance_summary.api_calls_saved + '</p>';
                html += '<p><strong><?php esc_html_e('Cache Hit Rate:', 'tradepress'); ?></strong> ' + (results.performance_summary.cache_hit_rate * 100) + '%</p>';
                html += '<p><strong><?php esc_html_e('Memory Usage:', 'tradepress'); ?></strong> ' + results.performance_summary.memory_usage_mb + 'MB</p>';
                html += '</div>';
                
                // Recommendations
                if (results.recommendations && results.recommendations.length > 0) {
                    html += '<div class="recommendations">';
                    html += '<h3><?php esc_html_e('Recommendations', 'tradepress'); ?></h3>';
                    html += '<ul>';
                    results.recommendations.forEach(function(rec) {
                        html += '<li>' + rec + '</li>';
                    });
                    html += '</ul>';
                    html += '</div>';
                }
                
                // AI-readable JSON (collapsible)
                html += '<div style="margin-top: 20px;">';
                html += '<button type="button" onclick="toggleAIResults()" class="button"><?php esc_html_e('Show/Hide AI-Readable Results', 'tradepress'); ?></button>';
                html += '<div id="ai-results" style="display: none; margin-top: 10px;">';
                html += '<textarea readonly style="width: 100%; height: 300px; font-family: monospace; font-size: 11px;">';
                html += JSON.stringify(results, null, 2);
                html += '</textarea>';
                html += '</div>';
                html += '</div>';
                
                $('#test-results').html(html);
            }
        });
        
        function toggleAIResults() {
            var div = document.getElementById('ai-results');
            div.style.display = div.style.display === 'none' ? 'block' : 'none';
        }
        </script>
        <?php
    }
}