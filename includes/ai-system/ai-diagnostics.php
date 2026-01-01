<?php
/**
 * TradePress AI Diagnostics
 *
 * This class provides advanced diagnostic capabilities for the
 * AI assistant to proactively identify issues and suggest solutions.
 *
 * @package TradePress\AI
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TradePress_AI_Diagnostics
 * 
 * Provides advanced diagnostic capabilities for the AI Assistant,
 * including automated testing, log analysis, and context-aware
 * system checks.
 *
 * @since 1.0.0
 */
class TradePress_AI_Diagnostics {
    
    /**
     * Context manager instance
     *
     * @var TradePress_AI_Context_Manager
     */
    private $context_manager;
    
    /**
     * Constructor
     *
     * @param TradePress_AI_Context_Manager $context_manager Context manager instance
     */
    public function __construct($context_manager = null) {
        if ($context_manager) {
            $this->context_manager = $context_manager;
        } else {
            // Create a new context manager if none provided
            $this->context_manager = new TradePress_AI_Context_Manager();
        }
    }
    
    /**
     * Run automated diagnostics based on current context
     *
     * @return array Diagnostic results
     */
    public function run_automated_diagnostics() {
        $results = array();
        
        // Get current context
        $context = $this->context_manager->get_context();
        
        // Run general system diagnostics
        $system_diagnostics = $this->check_system_health();
        $results = array_merge($results, $system_diagnostics);
        
        // Run context-specific diagnostics
        if (!empty($context['current_feature'])) {
            $feature = $context['current_feature'];
            
            switch ($feature) {
                case 'API Integration':
                    $api_diagnostics = $this->check_api_health();
                    $results = array_merge($results, $api_diagnostics);
                    break;
                case 'Trading':
                    $trading_diagnostics = $this->check_trading_functionality();
                    $results = array_merge($results, $trading_diagnostics);
                    break;
                case 'AI Assistant':
                    $ai_diagnostics = $this->check_ai_functionality();
                    $results = array_merge($results, $ai_diagnostics);
                    break;
            }
        }
        
        // Run diagnostics based on file path
        if (!empty($context['current_file'])) {
            $file_diagnostics = $this->check_file_related_systems($context['current_file']);
            $results = array_merge($results, $file_diagnostics);
        }
        
        return $results;
    }
    
    /**
     * Check system health
     *
     * @return array Diagnostic results
     */
    private function check_system_health() {
        $results = array();
        
        // Check PHP version
        $php_version = phpversion();
        if (version_compare($php_version, '7.4', '<')) {
            $results[] = array(
                'type' => 'warning',
                'component' => 'system',
                'message' => "PHP version {$php_version} is below the recommended version 7.4",
                'severity' => 'medium',
                'suggestion' => 'Consider upgrading PHP to version 7.4 or higher for better performance and security'
            );
        } else {
            $results[] = array(
                'type' => 'info',
                'component' => 'system',
                'message' => "PHP version {$php_version} meets requirements",
                'severity' => 'low'
            );
        }
        
        // Check WordPress version
        $wp_version = get_bloginfo('version');
        if (version_compare($wp_version, '5.8', '<')) {
            $results[] = array(
                'type' => 'warning',
                'component' => 'system',
                'message' => "WordPress version {$wp_version} is below the recommended version 5.8",
                'severity' => 'medium',
                'suggestion' => 'Consider upgrading WordPress to the latest version for better compatibility and security'
            );
        } else {
            $results[] = array(
                'type' => 'info',
                'component' => 'system',
                'message' => "WordPress version {$wp_version} meets requirements",
                'severity' => 'low'
            );
        }
        
        // Check for active plugins that might conflict
        $active_plugins = get_option('active_plugins');
        $potentially_conflicting = array(
            'another-stock-plugin/another-stock-plugin.php',
            'trading-bot-manager/trading-bot-manager.php'
        );
        
        $found_conflicts = array_intersect($active_plugins, $potentially_conflicting);
        if (!empty($found_conflicts)) {
            $results[] = array(
                'type' => 'warning',
                'component' => 'system',
                'message' => "Found potentially conflicting plugins: " . implode(', ', $found_conflicts),
                'severity' => 'medium',
                'suggestion' => 'These plugins might conflict with TradePress. Consider disabling them if you encounter issues'
            );
        }
        
        // Memory limit check
        $memory_limit = ini_get('memory_limit');
        $numeric_memory_limit = $this->return_bytes($memory_limit);
        if ($numeric_memory_limit < 134217728) { // 128MB
            $results[] = array(
                'type' => 'warning',
                'component' => 'system',
                'message' => "Memory limit {$memory_limit} is below the recommended 128M",
                'severity' => 'medium',
                'suggestion' => 'Consider increasing the memory limit in your wp-config.php file: define("WP_MEMORY_LIMIT", "128M");'
            );
        }
        
        return $results;
    }
    
    /**
     * Convert memory limit string to bytes
     *
     * @param string $size_str Size string like '128M'
     * @return int Size in bytes
     */
    private function return_bytes($size_str) {
        switch (substr($size_str, -1)) {
            case 'M': case 'm': return (int)$size_str * 1048576;
            case 'K': case 'k': return (int)$size_str * 1024;
            case 'G': case 'g': return (int)$size_str * 1073741824;
            default: return (int)$size_str;
        }
    }
    
    /**
     * Check API health
     *
     * @return array Diagnostic results
     */
    private function check_api_health() {
        $results = array();
        
        // Check if API keys are configured
        if (function_exists('tradepress_get_api_key')) {
            $api_key = tradepress_get_api_key('alphavantage');
            if (empty($api_key)) {
                $results[] = array(
                    'type' => 'error',
                    'component' => 'api',
                    'message' => 'Alpha Vantage API key is not configured',
                    'severity' => 'high',
                    'suggestion' => 'Configure the Alpha Vantage API key in Settings > Trading Platforms > Alpha Vantage'
                );
            }
        }
        
        // Check API connectivity (simplified placeholder)
        if (class_exists('TradePress_API_Manager')) {
            $api_status = array(
                'alphavantage' => false,
                'polygon' => false
            );
            
            $results[] = array(
                'type' => 'info',
                'component' => 'api',
                'message' => 'API connectivity check would be performed here',
                'severity' => 'low'
            );
        }
        
        return $results;
    }
    
    /**
     * Check trading functionality
     *
     * @return array Diagnostic results
     */
    private function check_trading_functionality() {
        // Placeholder implementation
        return array(
            array(
                'type' => 'info',
                'component' => 'trading',
                'message' => 'Trading functionality check would be performed here',
                'severity' => 'low'
            )
        );
    }
    
    /**
     * Check AI functionality
     *
     * @return array Diagnostic results
     */
    private function check_ai_functionality() {
        // Placeholder implementation
        return array(
            array(
                'type' => 'info',
                'component' => 'ai',
                'message' => 'AI functionality check would be performed here',
                'severity' => 'low'
            )
        );
    }
    
    /**
     * Check systems related to a specific file
     *
     * @param string $file_path Path to the file
     * @return array Diagnostic results
     */
    private function check_file_related_systems($file_path) {
        $results = array();
        
        // Determine what systems to check based on file path
        if (strpos($file_path, 'api') !== false) {
            // Check API-related systems
            $results[] = array(
                'type' => 'info',
                'component' => 'api',
                'message' => 'API-specific diagnostics would be performed here',
                'severity' => 'low'
            );
        } elseif (strpos($file_path, 'trading') !== false) {
            // Check trading-related systems
            $results[] = array(
                'type' => 'info',
                'component' => 'trading',
                'message' => 'Trading-specific diagnostics would be performed here',
                'severity' => 'low'
            );
        }
        
        return $results;
    }
    
    /**
     * Analyze error logs for patterns
     *
     * @return array Log analysis results
     */
    public function analyze_error_logs() {
        $results = array();
        
        // This would be implemented to read and analyze error logs
        // For now, a placeholder implementation
        $results[] = array(
            'type' => 'info',
            'component' => 'logs',
            'message' => 'Log analysis would be performed here',
            'severity' => 'low'
        );
        
        return $results;
    }
    
    /**
     * Run specific diagnostic checks
     *
     * @param string $check_type Type of check to run
     * @param array $params Parameters for the check
     * @return array Check results
     */
    public function run_specific_diagnostic($check_type, $params = array()) {
        switch ($check_type) {
            case 'api_endpoint':
                return $this->check_api_endpoint($params['endpoint'] ?? '');
                
            case 'database_tables':
                return $this->check_database_tables();
                
            case 'file_permissions':
                return $this->check_file_permissions($params['directory'] ?? '');
                
            default:
                return array(
                    'error' => 'Unknown diagnostic check type'
                );
        }
    }
    
    /**
     * Check a specific API endpoint
     *
     * @param string $endpoint Endpoint to check
     * @return array Check results
     */
    private function check_api_endpoint($endpoint) {
        // Placeholder implementation
        return array(
            'endpoint' => $endpoint,
            'status' => 'This would check the endpoint status'
        );
    }
    
    /**
     * Check database tables
     *
     * @return array Check results
     */
    private function check_database_tables() {
        global $wpdb;
        $results = array();
        
        // List of expected tables
        $expected_tables = array(
            $wpdb->prefix . 'tradepress_scores',
            $wpdb->prefix . 'tradepress_symbols',
            $wpdb->prefix . 'tradepress_history'
        );
        
        foreach ($expected_tables as $table) {
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
            
            if ($table_exists) {
                $results[] = array(
                    'table' => $table,
                    'exists' => true,
                    'status' => 'OK'
                );
            } else {
                $results[] = array(
                    'table' => $table,
                    'exists' => false,
                    'status' => 'Missing',
                    'suggestion' => 'Run the database installation routine in Settings > Database Management'
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Check file permissions
     *
     * @param string $directory Directory to check
     * @return array Check results
     */
    private function check_file_permissions($directory) {
        // Placeholder implementation
        return array(
            'directory' => $directory,
            'status' => 'This would check file permissions'
        );
    }
}
