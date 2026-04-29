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
     * Return directories that should be scanned for file-based tests.
     *
     * @return array
      * @version 1.0.0
     */
    public static function get_discovery_directories() {
        $directories = array(
            'tests/unit',
            'tests/integration',
        );

        /**
         * Filter directories scanned by test discovery.
         *
         * @param array $directories Relative plugin directories.
         */
        $directories = apply_filters( 'tradepress_test_discovery_directories', $directories );

        if ( ! is_array( $directories ) ) {
            return array( 'tests/unit' );
        }

        $clean = array();
        foreach ( $directories as $directory ) {
            if ( ! is_string( $directory ) || '' === trim( $directory ) ) {
                continue;
            }
            $clean[] = trim( str_replace( '\\', '/', $directory ), '/' );
        }

        return array_values( array_unique( $clean ) );
    }

    /**
     * Register a new test
      *
      * @version 1.0.0
      *
      * @param mixed $args
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
      *
      * @version 1.0.0
      *
      * @param array $args
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
      *
      * @version 1.0.0
      *
      * @param mixed $test_id
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
      *
      * @version 1.0.0
      *
      * @param mixed $test_id
      * @param mixed $status
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
      *
      * @version 1.0.0
      *
      * @param mixed $test_id
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
      *
      * @version 1.0.0
      *
      * @param string $directory
     */
    public static function discover_tests($directory = '') {
        global $wpdb;

        $directories = ! empty( $directory ) ? array( trim( $directory, '/' ) ) : self::get_discovery_directories();

        $summary = array(
            'directories' => $directories,
            'files_scanned' => 0,
            'registered' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => array(),
        );

        foreach ( $directories as $scan_dir ) {
            $path = TRADEPRESS_PLUGIN_DIR_PATH . $scan_dir;

            if ( ! is_dir( $path ) ) {
                $summary['errors'][] = sprintf( 'Directory does not exist: %s', $scan_dir );
                continue;
            }

            $files = glob( trailingslashit( $path ) . '*.php' );
            if ( ! is_array( $files ) ) {
                continue;
            }

            foreach ( $files as $file ) {
                $summary['files_scanned']++;

                $rel_path = str_replace( TRADEPRESS_PLUGIN_DIR_PATH, '', $file );
                $rel_path = str_replace( '\\', '/', $rel_path );

                $metadata = self::extract_test_metadata( $file, $rel_path );
                if ( is_wp_error( $metadata ) ) {
                    $summary['errors'][] = $metadata->get_error_message();
                    continue;
                }

                $exists = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT test_id FROM {$wpdb->prefix}tradepress_tests WHERE file_path = %s OR class_name = %s LIMIT 1",
                        $metadata['file_path'],
                        $metadata['class_name']
                    )
                );

                if ( $exists ) {
                    $updated = $wpdb->update(
                        $wpdb->prefix . 'tradepress_tests',
                        array(
                            'title' => $metadata['title'],
                            'description' => $metadata['description'],
                            'category' => $metadata['category'],
                            'priority_level' => $metadata['priority_level'],
                            'status' => 'active',
                            'test_type' => 'file',
                            'file_path' => $metadata['file_path'],
                            'class_name' => $metadata['class_name'],
                        ),
                        array( 'test_id' => (int) $exists->test_id ),
                        array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' ),
                        array( '%d' )
                    );

                    if ( false === $updated ) {
                        $summary['errors'][] = sprintf( 'Failed updating test metadata for %s', $metadata['class_name'] );
                    } elseif ( 0 === (int) $updated ) {
                        $summary['skipped']++;
                    } else {
                        $summary['updated']++;
                    }

                    continue;
                }

                $result = self::register_test(
                    array(
                        'title' => $metadata['title'],
                        'description' => $metadata['description'],
                        'category' => $metadata['category'],
                        'priority_level' => $metadata['priority_level'],
                        'status' => 'active',
                        'test_type' => 'file',
                        'file_path' => $metadata['file_path'],
                        'class_name' => $metadata['class_name'],
                    )
                );

                if ( is_wp_error( $result ) ) {
                    $summary['errors'][] = sprintf( 'Failed registering %s: %s', $metadata['class_name'], $result->get_error_message() );
                } else {
                    $summary['registered']++;
                }
            }
        }

        return $summary;
    }

    /**
     * Attempt to load a class from registered file-based tests.
     *
     * @param string $class_name
     * @return bool
      * @version 1.0.0
     */
    public static function autoload_registered_test_class( $class_name ) {
        global $wpdb;

        if ( empty( $class_name ) || class_exists( $class_name, false ) ) {
            return true;
        }

        $test = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT file_path FROM {$wpdb->prefix}tradepress_tests WHERE class_name = %s AND test_type = 'file' LIMIT 1",
                $class_name
            )
        );

        if ( ! $test || empty( $test->file_path ) ) {
            return false;
        }

        $absolute_path = TRADEPRESS_PLUGIN_DIR_PATH . ltrim( $test->file_path, '/' );
        if ( ! file_exists( $absolute_path ) ) {
            return false;
        }

        require_once $absolute_path;
        return class_exists( $class_name, false );
    }

    /**
     * Parse test class metadata from a test file.
     *
     * @param string $file_path Absolute path.
     * @param string $relative_path Relative path.
     * @return array|WP_Error
      * @version 1.0.0
     */
    private static function extract_test_metadata( $file_path, $relative_path ) {
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'missing_test_file', sprintf( 'Test file missing: %s', $relative_path ) );
        }

        $classes_before = get_declared_classes();
        require_once $file_path;
        $classes_after = get_declared_classes();

        $new_classes = array_diff( $classes_after, $classes_before );
        $candidate = '';

        foreach ( $new_classes as $class_name ) {
            if ( is_subclass_of( $class_name, 'TradePress_Test_Case' ) ) {
                $candidate = $class_name;
                break;
            }
        }

        if ( '' === $candidate ) {
            foreach ( $classes_after as $class_name ) {
                if ( is_subclass_of( $class_name, 'TradePress_Test_Case' ) ) {
                    $ref = new ReflectionClass( $class_name );
                    if ( $ref->getFileName() === $file_path ) {
                        $candidate = $class_name;
                        break;
                    }
                }
            }
        }

        if ( '' === $candidate ) {
            return new WP_Error( 'invalid_test_class', sprintf( 'No TradePress_Test_Case subclass found in %s', $relative_path ) );
        }

        $title = $candidate;
        $description = sprintf( 'Auto-discovered test in %s', $relative_path );
        $category = 'standard';
        $priority_level = 3;

        if ( property_exists( $candidate, 'test_metadata' ) ) {
            $metadata = $candidate::$test_metadata;
            if ( is_array( $metadata ) ) {
                $title = ! empty( $metadata['title'] ) ? sanitize_text_field( $metadata['title'] ) : $title;
                $description = ! empty( $metadata['description'] ) ? sanitize_textarea_field( $metadata['description'] ) : $description;
                $category = ! empty( $metadata['category'] ) ? sanitize_key( $metadata['category'] ) : $category;
                $priority_level = isset( $metadata['priority_level'] ) ? absint( $metadata['priority_level'] ) : $priority_level;
            }
        }

        return array(
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'priority_level' => max( 1, min( 5, (int) $priority_level ) ),
            'file_path' => ltrim( $relative_path, '/' ),
            'class_name' => $candidate,
        );
    }
}

endif;
