<?php
/**
 * TradePress - Scoring Strategies Database Manager
 * 
 * Handles all database operations for scoring strategies
 *
 * @package TradePress/ScoringSystem
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Strategies_DB {
    
    /**
     * Get strategy by ID
     */
    public static function get_strategy($strategy_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_scoring_strategies';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $strategy_id));
    }
    
    /**
     * Get all strategies with optional filters
     */
    public static function get_strategies($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => 'all',
            'category' => 'all',
            'creator_id' => null,
            'is_public' => null,
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table = $wpdb->prefix . 'tradepress_scoring_strategies';
        $where_clauses = array('1=1');
        $where_values = array();
        
        if ($args['status'] !== 'all') {
            $where_clauses[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        if ($args['category'] !== 'all') {
            $where_clauses[] = 'category = %s';
            $where_values[] = $args['category'];
        }
        
        if ($args['creator_id']) {
            $where_clauses[] = 'creator_id = %d';
            $where_values[] = $args['creator_id'];
        }
        
        if ($args['is_public'] !== null) {
            $where_clauses[] = 'is_public = %d';
            $where_values[] = $args['is_public'] ? 1 : 0;
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $order_sql = sprintf('ORDER BY %s %s', $args['orderby'], $args['order']);
        $limit_sql = sprintf('LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
        
        $sql = "SELECT * FROM $table WHERE $where_sql $order_sql $limit_sql";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($sql, $where_values));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    /**
     * Create new strategy
     */
    public static function create_strategy($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_scoring_strategies';
        
        // Generate slug from name
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = sanitize_title($data['name']);
        }
        
        // Set creator if not specified
        if (empty($data['creator_id'])) {
            $data['creator_id'] = get_current_user_id();
        }
        
        $result = $wpdb->insert($table, $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create strategy: ' . $wpdb->last_error);
        }
        
        $strategy_id = $wpdb->insert_id;
        
        // Create initial version
        self::create_strategy_version($strategy_id, $data, 'created');
        
        return $strategy_id;
    }
    
    /**
     * Update strategy
     */
    public static function update_strategy($strategy_id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_scoring_strategies';
        
        // Get current strategy for version tracking
        $current_strategy = self::get_strategy($strategy_id);
        if (!$current_strategy) {
            return new WP_Error('not_found', 'Strategy not found');
        }
        
        $result = $wpdb->update($table, $data, array('id' => $strategy_id));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update strategy: ' . $wpdb->last_error);
        }
        
        // Create version record
        self::create_strategy_version($strategy_id, $data, 'updated');
        
        return true;
    }
    
    /**
     * Delete strategy
     */
    public static function delete_strategy($strategy_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_scoring_strategies';
        
        // Archive instead of delete to preserve history
        $result = $wpdb->update(
            $table,
            array('status' => 'archived'),
            array('id' => $strategy_id)
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to archive strategy: ' . $wpdb->last_error);
        }
        
        return true;
    }
    
    /**
     * Add directive to strategy
     */
    public static function add_strategy_directive($strategy_id, $directive_data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_directives';
        
        $directive_data['strategy_id'] = $strategy_id;
        
        $result = $wpdb->insert($table, $directive_data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to add directive: ' . $wpdb->last_error);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get strategy directives
     */
    public static function get_strategy_directives($strategy_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_directives';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE strategy_id = %d AND is_active = 1 ORDER BY sort_order ASC",
            $strategy_id
        ));
    }
    
    /**
     * Update directive weight
     */
    public static function update_directive_weight($strategy_directive_id, $weight) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_directives';
        
        $result = $wpdb->update(
            $table,
            array('weight' => $weight),
            array('id' => $strategy_directive_id)
        );
        
        return $result !== false;
    }
    
    /**
     * Remove directive from strategy
     */
    public static function remove_strategy_directive($strategy_directive_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_directives';
        
        // Soft delete by setting inactive
        $result = $wpdb->update(
            $table,
            array('is_active' => 0),
            array('id' => $strategy_directive_id)
        );
        
        return $result !== false;
    }
    
    /**
     * Save directive configuration override
     */
    public static function save_directive_config($strategy_directive_id, $config_key, $config_value, $config_type = 'string') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_directive_configs';
        
        // Check if config already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE strategy_directive_id = %d AND config_key = %s",
            $strategy_directive_id,
            $config_key
        ));
        
        $data = array(
            'config_value' => $config_value,
            'config_type' => $config_type,
            'overrides_global' => 1
        );
        
        if ($existing) {
            $result = $wpdb->update($table, $data, array('id' => $existing->id));
        } else {
            $data['strategy_directive_id'] = $strategy_directive_id;
            $data['config_key'] = $config_key;
            $result = $wpdb->insert($table, $data);
        }
        
        return $result !== false;
    }
    
    /**
     * Get directive configurations for strategy
     */
    public static function get_directive_configs($strategy_directive_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_directive_configs';
        
        $configs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE strategy_directive_id = %d",
            $strategy_directive_id
        ));
        
        // Convert to key-value array
        $config_array = array();
        foreach ($configs as $config) {
            $config_array[$config->config_key] = $config->config_value;
        }
        
        return $config_array;
    }
    
    /**
     * Save strategy test result
     */
    public static function save_test_result($test_data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_tests';
        
        if (empty($test_data['user_id'])) {
            $test_data['user_id'] = get_current_user_id();
        }
        
        $result = $wpdb->insert($table, $test_data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to save test result: ' . $wpdb->last_error);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get strategy test results
     */
    public static function get_test_results($strategy_id, $limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_tests';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE strategy_id = %d ORDER BY test_date DESC LIMIT %d",
            $strategy_id,
            $limit
        ));
    }
    
    /**
     * Create strategy version record
     */
    private static function create_strategy_version($strategy_id, $strategy_data, $change_type = 'updated') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_versions';
        
        // Get current directives
        $directives = self::get_strategy_directives($strategy_id);
        
        $version_data = array(
            'strategy_id' => $strategy_id,
            'strategy_data' => json_encode($strategy_data),
            'directives_data' => json_encode($directives),
            'change_type' => $change_type,
            'created_by' => get_current_user_id()
        );
        
        return $wpdb->insert($table, $version_data);
    }
    
    /**
     * Get strategy categories
     */
    public static function get_categories() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_strategy_categories';
        
        return $wpdb->get_results(
            "SELECT * FROM $table WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
        );
    }
    
    /**
     * Update strategy performance metrics
     */
    public static function update_performance_metrics($strategy_id) {
        global $wpdb;
        
        // Calculate performance from test results
        $tests_table = $wpdb->prefix . 'tradepress_strategy_tests';
        $strategy_table = $wpdb->prefix . 'tradepress_scoring_strategies';
        
        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_tests,
                COUNT(CASE WHEN test_status = 'completed' THEN 1 END) as successful_tests,
                AVG(CASE WHEN test_status = 'completed' THEN total_score END) as avg_score,
                MAX(test_date) as last_test_date
            FROM $tests_table 
            WHERE strategy_id = %d
        ", $strategy_id));
        
        if ($stats && $stats->total_tests > 0) {
            $success_rate = ($stats->successful_tests / $stats->total_tests) * 100;
            
            $wpdb->update(
                $strategy_table,
                array(
                    'total_tests' => $stats->total_tests,
                    'successful_tests' => $stats->successful_tests,
                    'avg_score' => $stats->avg_score,
                    'success_rate' => $success_rate,
                    'last_test_date' => $stats->last_test_date
                ),
                array('id' => $strategy_id)
            );
        }
    }
    
    /**
     * Get strategy statistics
     */
    public static function get_strategy_stats() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_scoring_strategies';
        
        return $wpdb->get_row("
            SELECT 
                COUNT(*) as total_strategies,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_strategies,
                COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_strategies,
                AVG(success_rate) as avg_success_rate,
                AVG(avg_score) as overall_avg_score
            FROM $table
        ");
    }
}