<?php
/**
 * AI Diagram Analysis AJAX Handler
 *
 * @package TradePress/Admin/Ajax
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle AI diagram analysis request
 */
function tradepress_handle_ai_diagram_analysis() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'tradepress_ai_analysis')) {
        wp_die('Security check failed');
    }
    
    $current_diagram = sanitize_text_field($_POST['current_diagram']);
    
    // Perform analysis based on current diagram and actual code
    $analysis_results = tradepress_analyze_system_architecture($current_diagram);
    
    wp_send_json_success($analysis_results);
}
add_action('wp_ajax_tradepress_ai_diagram_analysis', 'tradepress_handle_ai_diagram_analysis');

/**
 * Analyze system architecture against diagrams
 */
function tradepress_analyze_system_architecture($diagram_type) {
    $plugin_path = plugin_dir_path(__FILE__) . '../../../';
    $analysis = array(
        'immediate_issues' => array(),
        'optimizations' => array(),
        'architecture_suggestions' => array(),
        'summary' => ''
    );
    
    // Analyze based on diagram type
    switch ($diagram_type) {
        case 'api-management-flow':
            $analysis = analyze_api_management_system($plugin_path);
            break;
            
        case 'bugnet-logging':
            $analysis = analyze_logging_system($plugin_path);
            break;
            
        case 'error-propagation':
            $analysis = analyze_error_handling($plugin_path);
            break;
            
        case 'cache-strategy':
            $analysis = analyze_cache_system($plugin_path);
            break;
            
        case 'database-schema':
            $analysis = analyze_database_structure($plugin_path);
            break;
            
        default:
            $analysis = analyze_general_architecture($plugin_path, $diagram_type);
            break;
    }
    
    return $analysis;
}

/**
 * Analyze API Management System
 */
function analyze_api_management_system($plugin_path) {
    $issues = array();
    $optimizations = array();
    $suggestions = array();
    
    // Check for dual testing implementation
    $api_management_file = $plugin_path . 'admin/page/tradingplatforms/view/view.api_management.php';
    if (file_exists($api_management_file)) {
        $content = file_get_contents($api_management_file);
        
        // Check for proper cache implementation
        if (strpos($content, 'Query Test') !== false && strpos($content, 'Call Test') !== false) {
            $optimizations[] = array(
                'title' => 'Dual Testing System Implemented',
                'description' => 'Good: Both Call Test and Query Test buttons are implemented for proper API testing.',
                'priority' => 'low',
                'expected_benefit' => 'Proper rate limit protection and cache utilization'
            );
        } else {
            $issues[] = array(
                'title' => 'Missing Dual Testing System',
                'description' => 'The API management should have both Call Test (direct) and Query Test (cache-first) options.',
                'priority' => 'high',
                'code_suggestion' => 'Add separate buttons for Call Test and Query Test with different cache behaviors'
            );
        }
        
        // Check for rate limiting
        if (strpos($content, 'rate') === false) {
            $issues[] = array(
                'title' => 'Rate Limiting Not Visible',
                'description' => 'Rate limiting checks should be more prominent in the API testing interface.',
                'priority' => 'medium'
            );
        }
    }
    
    // Check for API safety protocols
    $alpaca_file = $plugin_path . 'api/alpaca/alpaca-api.php';
    if (file_exists($alpaca_file)) {
        $content = file_get_contents($alpaca_file);
        
        if (strpos($content, 'paper') !== false && strpos($content, 'live') !== false) {
            $optimizations[] = array(
                'title' => 'Trading Mode Detection Present',
                'description' => 'Good: Code shows awareness of paper vs live trading modes.',
                'priority' => 'low',
                'expected_benefit' => 'Prevents accidental live trading during testing'
            );
        }
    }
    
    return array(
        'immediate_issues' => $issues,
        'optimizations' => $optimizations,
        'architecture_suggestions' => $suggestions,
        'summary' => 'API Management system shows ' . count($optimizations) . ' good practices and ' . count($issues) . ' areas for improvement.'
    );
}

/**
 * Analyze Logging System
 */
function analyze_logging_system($plugin_path) {
    $issues = array();
    $optimizations = array();
    
    // Check BugNet implementation
    $bugnet_file = $plugin_path . 'includes/bugnet-system/bugnet.php';
    if (file_exists($bugnet_file)) {
        $content = file_get_contents($bugnet_file);
        
        if (strpos($content, 'hook') !== false) {
            $optimizations[] = array(
                'title' => 'Hook System Implemented',
                'description' => 'Good: BugNet uses hooks for modular log file registration.',
                'priority' => 'low',
                'expected_benefit' => 'Supports future plugin separation and modularity'
            );
        }
        
        if (strpos($content, 'developer_mode') !== false) {
            $optimizations[] = array(
                'title' => 'Developer Mode Support',
                'description' => 'Good: Logging system adapts based on developer mode setting.',
                'priority' => 'low',
                'expected_benefit' => 'Appropriate logging detail for different user types'
            );
        }
    } else {
        $issues[] = array(
            'title' => 'BugNet System File Missing',
            'description' => 'The core BugNet logging system file is not found.',
            'priority' => 'high'
        );
    }
    
    return array(
        'immediate_issues' => $issues,
        'optimizations' => $optimizations,
        'architecture_suggestions' => array(),
        'summary' => 'Logging system analysis complete with ' . count($issues) . ' issues found.'
    );
}

/**
 * Analyze Error Handling
 */
function analyze_error_handling($plugin_path) {
    $issues = array();
    $optimizations = array();
    
    // Check debug.log for patterns
    $debug_log = WP_CONTENT_DIR . '/debug.log';
    if (file_exists($debug_log)) {
        $log_content = file_get_contents($debug_log);
        
        // Check for repeated database errors
        $elementor_errors = substr_count($log_content, '_elementor_conditions');
        if ($elementor_errors > 10) {
            $issues[] = array(
                'title' => 'Repeated Elementor Query Errors',
                'description' => 'Found ' . $elementor_errors . ' repeated database errors for Elementor conditions. This suggests a caching or query optimization issue.',
                'priority' => 'high',
                'code_suggestion' => 'Add caching for Elementor condition queries or disable if not needed'
            );
        }
        
        // Check for missing file errors
        if (strpos($log_content, 'algorithm-debugger') !== false) {
            $issues[] = array(
                'title' => 'Missing Algorithm Debugger File',
                'description' => 'The system is trying to load algorithm-debugger.php which does not exist.',
                'priority' => 'medium',
                'code_suggestion' => 'Either create the missing file or remove the require_once statement'
            );
        }
        
        // Check for database connection issues
        $db_errors = substr_count($log_content, 'WordPress database error');
        if ($db_errors > 5) {
            $issues[] = array(
                'title' => 'Multiple Database Errors',
                'description' => 'Found ' . $db_errors . ' database errors. This indicates potential connection or query issues.',
                'priority' => 'high'
            );
        }
    }
    
    return array(
        'immediate_issues' => $issues,
        'optimizations' => $optimizations,
        'architecture_suggestions' => array(),
        'summary' => 'Error analysis found ' . count($issues) . ' issues that need immediate attention.'
    );
}

/**
 * Analyze Cache System
 */
function analyze_cache_system($plugin_path) {
    $issues = array();
    $optimizations = array();
    $suggestions = array();
    
    // Check for cache implementation in API calls
    $api_files = glob($plugin_path . 'api/*/');
    $cache_implementations = 0;
    
    foreach ($api_files as $api_dir) {
        $api_file = $api_dir . basename($api_dir) . '-api.php';
        if (file_exists($api_file)) {
            $content = file_get_contents($api_file);
            if (strpos($content, 'transient') !== false || strpos($content, 'cache') !== false) {
                $cache_implementations++;
            }
        }
    }
    
    if ($cache_implementations > 0) {
        $optimizations[] = array(
            'title' => 'Cache Implementation Found',
            'description' => 'Found caching in ' . $cache_implementations . ' API implementations.',
            'priority' => 'low',
            'expected_benefit' => 'Reduced API calls and improved performance'
        );
    } else {
        $issues[] = array(
            'title' => 'Limited Cache Implementation',
            'description' => 'Few API files show evidence of caching implementation.',
            'priority' => 'medium'
        );
    }
    
    $suggestions[] = array(
        'title' => 'Implement Multi-Level Caching',
        'description' => 'Consider implementing object cache, transients, and database caching layers as shown in the diagram.',
        'priority' => 'medium'
    );
    
    return array(
        'immediate_issues' => $issues,
        'optimizations' => $optimizations,
        'architecture_suggestions' => $suggestions,
        'summary' => 'Cache system shows room for improvement with multi-level caching strategy.'
    );
}

/**
 * Analyze Database Structure
 */
function analyze_database_structure($plugin_path) {
    global $wpdb;
    
    $issues = array();
    $optimizations = array();
    
    // Check if custom tables exist
    $tables_to_check = array(
        $wpdb->prefix . 'tradepress_symbols',
        $wpdb->prefix . 'tradepress_symbol_meta',
        $wpdb->prefix . 'tradepress_price_data'
    );
    
    foreach ($tables_to_check as $table) {
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
        if ($table_exists) {
            $optimizations[] = array(
                'title' => 'Custom Table: ' . basename($table),
                'description' => 'Good: Custom table exists for optimized data storage.',
                'priority' => 'low',
                'expected_benefit' => 'Better performance than wp_posts for structured data'
            );
        } else {
            $issues[] = array(
                'title' => 'Missing Table: ' . basename($table),
                'description' => 'Expected custom table is missing from database.',
                'priority' => 'medium'
            );
        }
    }
    
    return array(
        'immediate_issues' => $issues,
        'optimizations' => $optimizations,
        'architecture_suggestions' => array(),
        'summary' => 'Database structure analysis shows ' . count($optimizations) . ' properly implemented tables.'
    );
}

/**
 * General architecture analysis
 */
function analyze_general_architecture($plugin_path, $diagram_type) {
    return array(
        'immediate_issues' => array(),
        'optimizations' => array(
            array(
                'title' => 'Comprehensive Documentation',
                'description' => 'Good: System has detailed diagram documentation for ' . $diagram_type . '.',
                'priority' => 'low',
                'expected_benefit' => 'Easier maintenance and future development'
            )
        ),
        'architecture_suggestions' => array(
            array(
                'title' => 'Continue Documentation',
                'description' => 'Keep diagrams updated as systems evolve to maintain their value.',
                'priority' => 'low'
            )
        ),
        'summary' => 'General architecture analysis complete for ' . $diagram_type . ' diagram.'
    );
}