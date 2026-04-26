<?php
/**
 * Developer Flow Logger - Detailed decision tracking
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Developer_Flow_Logger {
    
    private static $flow_steps = array();
    private static $current_context = '';
    
    /**
     * Start a new flow context
     */
    public static function start_flow($context, $description = '') {
        if (get_option('tradepress_developer_mode') !== 'yes') {
            return;
        }
        
        self::$current_context = $context;
        self::$flow_steps = array();
        
        self::log_step('FLOW_START', $description ?: "Starting {$context} flow", array(
            'timestamp' => current_time('mysql'),
            'context' => $context
        ));
    }
    
    /**
     * Log a decision point
     */
    public static function log_decision($decision, $result, $reason = '', $data = array()) {
        if (get_option('tradepress_developer_mode') !== 'yes') {
            return;
        }
        
        self::log_step('DECISION', "{$decision} → {$result}", array_merge($data, array(
            'decision' => $decision,
            'result' => $result,
            'reason' => $reason
        )));
    }
    
    /**
     * Log an action
     */
    public static function log_action($action, $details = '', $data = array()) {
        if (get_option('tradepress_developer_mode') !== 'yes') {
            return;
        }
        
        self::log_step('ACTION', "{$action}: {$details}", array_merge($data, array(
            'action' => $action
        )));
    }
    
    /**
     * Log a cache operation
     */
    public static function log_cache($operation, $key, $result, $data = array()) {
        if (get_option('tradepress_developer_mode') !== 'yes') {
            return;
        }
        
        self::log_step('CACHE', "{$operation} {$key} → {$result}", array_merge($data, array(
            'operation' => $operation,
            'cache_key' => $key,
            'result' => $result
        )));
    }
    
    /**
     * Log API operation
     */
    public static function log_api($provider, $endpoint, $result, $data = array()) {
        if (get_option('tradepress_developer_mode') !== 'yes') {
            return;
        }
        
        self::log_step('API', "{$provider} {$endpoint} → {$result}", array_merge($data, array(
            'provider' => $provider,
            'endpoint' => $endpoint,
            'result' => $result
        )));
    }
    
    /**
     * End flow and display results
     */
    public static function end_flow($final_result = '', $error = null) {
        if (get_option('tradepress_developer_mode') !== 'yes') {
            return;
        }
        
        self::log_step('FLOW_END', $final_result ?: 'Flow completed', array(
            'final_result' => $final_result,
            'error' => $error,
            'total_steps' => count(self::$flow_steps)
        ));
        
        self::display_flow_breakdown();
    }
    
    /**
     * Log individual step
     */
    private static function log_step($type, $message, $data = array()) {
        self::$flow_steps[] = array(
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'timestamp' => microtime(true),
            'memory' => memory_get_usage()
        );
    }
    
    /**
     * Display comprehensive flow breakdown
     */
    private static function display_flow_breakdown() {
        if (empty(self::$flow_steps)) {
            return;
        }
        
        $start_time = self::$flow_steps[0]['timestamp'];
        
        $html = '<div class="tradepress-flow-breakdown">';
        $html .= '<h3>🔍 Developer Flow Breakdown: ' . esc_html(self::$current_context) . '</h3>';
        $html .= '<div class="flow-summary">';
        $html .= '<span class="flow-stat">Steps: ' . count(self::$flow_steps) . '</span>';
        $html .= '<span class="flow-stat">Duration: ' . number_format((end(self::$flow_steps)['timestamp'] - $start_time) * 1000, 2) . 'ms</span>';
        $html .= '</div>';
        
        $html .= '<div class="flow-steps">';
        
        foreach (self::$flow_steps as $index => $step) {
            $elapsed = number_format(($step['timestamp'] - $start_time) * 1000, 2);
            $type_class = strtolower($step['type']);
            
            $html .= '<div class="flow-step step-' . $type_class . '">';
            $html .= '<div class="step-header">';
            $html .= '<span class="step-number">' . ($index + 1) . '</span>';
            $html .= '<span class="step-type">' . $step['type'] . '</span>';
            $html .= '<span class="step-time">+' . $elapsed . 'ms</span>';
            $html .= '</div>';
            $html .= '<div class="step-message">' . esc_html($step['message']) . '</div>';
            
            if (!empty($step['data'])) {
                $html .= '<div class="step-data">';
                foreach ($step['data'] as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value, JSON_PRETTY_PRINT);
                    }
                    $html .= '<div class="data-item"><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<style>
        .tradepress-flow-breakdown {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
        }
        .flow-summary {
            margin-bottom: 15px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 3px;
        }
        .flow-stat {
            margin-right: 15px;
            font-weight: bold;
        }
        .flow-steps {
            max-height: 500px;
            overflow-y: auto;
        }
        .flow-step {
            margin-bottom: 10px;
            padding: 8px;
            border-left: 4px solid #6c757d;
            background: white;
        }
        .step-flow_start { border-left-color: #28a745; }
        .step-decision { border-left-color: #ffc107; }
        .step-action { border-left-color: #007bff; }
        .step-cache { border-left-color: #17a2b8; }
        .step-api { border-left-color: #fd7e14; }
        .step-flow_end { border-left-color: #dc3545; }
        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .step-number {
            background: #6c757d;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 8px;
            min-width: 20px;
            text-align: center;
        }
        .step-type {
            font-weight: bold;
            margin-right: 10px;
        }
        .step-time {
            margin-left: auto;
            color: #6c757d;
        }
        .step-message {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .step-data {
            background: #f8f9fa;
            padding: 5px;
            border-radius: 3px;
        }
        .data-item {
            margin-bottom: 2px;
        }
        </style>';
        
        // Display the breakdown
        echo $html;
        
        // Reset for next flow
        self::$flow_steps = array();
        self::$current_context = '';
        
        // Also add to developer notices if available
        if (class_exists('TradePress_Developer_Notices')) {
            TradePress_Developer_Notices::database_notice('FLOW', 'developer_breakdown', array('context' => self::$current_context), true);
        }
    }
}