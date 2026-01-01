<?php
/**
 * Test registry class for TradePress testing framework
 *
 * @package TradePress/Testing
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('TradePress_Test_Registry')):

class TradePress_Test_Registry {
    /**
     * Register a new test
     */
    public static function register_test($args) {
        global $wpdb;
        
        $defaults = [
            'title' => '',
            'description' => '',
            'category' => 'standard',
            'priority_level' => 3,
            'status' => 'active',
            'test_type' => 'file',
            'file_path' => null,
            'class_name' => null,
            'method_name' => null,
            'test_data' => null,
            'expected_result' => null,
            'created_by' => get_current_user_id(),
            'notes' => ''
        ];
        
        $data = wp_parse_args($args, $defaults);
        
        // Ensure required fields are present
        if (empty($data['title'])) {
            return new WP_Error('missing_title', 'Test title is required');
        }
        
        // For file-based tests, validate file exists
        if ($data['test_type'] === 'file' && $data['file_path']) {
            if (!file_exists(TRADEPRESS_PLUGIN_DIR_PATH . $data['file_path'])) {
                return new WP_Error('invalid_file', 'Test file does not exist');
            }
        }
        
        // Convert arrays to JSON
        if (is_array($data['test_data'])) {
            $data['test_data'] = wp_json_encode($data['test_data']);
        }
        if (is_array($data['expected_result'])) {
            $data['expected_result'] = wp_json_encode($data['expected_result']);
        }
        
        // Insert into database
        $result = $wpdb->insert(
            $wpdb->prefix . 'tradepress_tests',
            $data,
            ['%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to register test');
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get all registered tests
     */
    public static function get_tests($args = []) {
        global $wpdb;
        
        $defaults = [
            'category' => '',
            'status' => '',
            'test_type' => '',
            'priority_level' => '',
            'orderby' => 'test_id',
            'order' => 'DESC',
            'limit' => 0,
            'offset' => 0
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $where = [];
        $where_values = [];
        
        if ($args['category']) {
            $where[] = 'category = %s';
            $where_values[] = $args['category'];
        }
        
        if ($args['status']) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        if ($args['test_type']) {
            $where[] = 'test_type = %s';
            $where_values[] = $args['test_type'];
        }
        
        if ($args['priority_level']) {
            $where[] = 'priority_level = %d';
            $where_values[] = $args['priority_level'];
        }
        
        $sql = "SELECT * FROM {$wpdb->prefix}tradepress_tests";
        
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        if ($args['limit'] > 0) {
            $sql .= " LIMIT %d, %d";
            $where_values[] = $args['offset'];
            $where_values[] = $args['limit'];
        }
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get a specific test by ID
     */
    public static function get_test($test_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tradepress_tests WHERE test_id = %d",
            $test_id
        ));
    }
    
    /**
     * Update test status
     */
    public static function update_test_status($test_id, $status) {
        global $wpdb;
        
        return $wpdb->update(
            $wpdb->prefix . 'tradepress_tests',
            ['status' => $status],
            ['test_id' => $test_id],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * Delete a test
     */
    public static function delete_test($test_id) {
        global $wpdb;
        
        // Delete test runs first
        $wpdb->delete(
            $wpdb->prefix . 'tradepress_test_runs',
            ['test_id' => $test_id],
            ['%d']
        );
        
        // Delete test faults
        $wpdb->delete(
            $wpdb->prefix . 'tradepress_test_faults',
            ['test_id' => $test_id],
            ['%d']
        );
        
        // Delete the test
        return $wpdb->delete(
            $wpdb->prefix . 'tradepress_tests',
            ['test_id' => $test_id],
            ['%d']
        );
    }
    
    /**
     * Auto-discover tests in directory
     */
    public static function discover_tests($directory = 'tests/unit') {
        $path = TRADEPRESS_PLUGIN_DIR_PATH . $directory;
        
        if (!is_dir($path)) {
            return new WP_Error('invalid_directory', 'Directory does not exist');
        }
        
        $files = glob($path . '/*.php');
        $registered = 0;
        
        foreach ($files as $file) {
            // Get relative path
            $rel_path = str_replace(TRADEPRESS_PLUGIN_DIR_PATH, '', $file);
            
            // Check if test is already registered
            global $wpdb;
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT test_id FROM {$wpdb->prefix}tradepress_tests WHERE file_path = %s",
                $rel_path
            ));
            
            if (!$exists) {
                // Parse file for test class and methods
                $content = file_get_contents($file);
                if (preg_match('/class\s+(\w+)/', $content, $matches)) {
                    $class_name = $matches[1];
                    
                    // Register the test
                    $result = self::register_test([
                        'title' => $class_name,
                        'description' => "Auto-discovered test in {$rel_path}",
                        'test_type' => 'file',
                        'file_path' => $rel_path,
                        'class_name' => $class_name
                    ]);
                    
                    if (!is_wp_error($result)) {
                        $registered++;
                    }
                }
            }
        }
        
        return $registered;
    }
}

endif;