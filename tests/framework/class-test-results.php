<?php
/**
 * Test results management for the TradePress testing framework.
 *
 * Provides retrieval and summary methods for test run history, pass/fail
 * statistics, and per-test result data stored in the database.
 *
 * @package TradePress/Testing
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Test_Results' ) ) :

/**
 * Class TradePress_Test_Results
 *
 * Static helper for reading test run data from the database.
 */
class TradePress_Test_Results {

    /**
     * Retrieve all run records for a specific test.
     *
     * @param int $test_id Test ID.
     * @param int $limit   Maximum number of records to return (0 = all).
     * @return array Array of run objects, newest first.
      * @version 1.0.0
     */
    public static function get_runs_for_test( $test_id, $limit = 20 ) {
        global $wpdb;

        $test_id = absint( $test_id );
        $limit   = absint( $limit );

        $sql = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tradepress_test_runs WHERE test_id = %d ORDER BY run_date DESC",
            $test_id
        );

        if ( $limit > 0 ) {
            $sql .= $wpdb->prepare( ' LIMIT %d', $limit );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Retrieve a single run record by its ID.
     *
     * @param int $run_id Run ID.
     * @return object|null Run object or null when not found.
      * @version 1.0.0
     */
    public static function get_run( $run_id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}tradepress_test_runs WHERE run_id = %d",
                absint( $run_id )
            )
        );
    }

    /**
     * Return pass/fail/error counts for a given test.
     *
     * @param int $test_id Test ID.
     * @return array {
     *     @type int    $total  Total number of runs.
     *     @type int    $passed Runs with status "passed".
     *     @type int    $failed Runs with status "failed".
     *     @type int    $error  Runs with status "error".
     *     @type float  $success_rate Success percentage (0–100).
     *     @type float  $avg_time     Average execution time in seconds.
     * }
      * @version 1.0.0
     */
    public static function get_test_stats( $test_id ) {
        global $wpdb;

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    COUNT(*)                                                     AS total,
                    SUM( CASE WHEN status = 'passed' THEN 1 ELSE 0 END )       AS passed,
                    SUM( CASE WHEN status = 'failed' THEN 1 ELSE 0 END )       AS failed,
                    SUM( CASE WHEN status = 'error'  THEN 1 ELSE 0 END )       AS error,
                    AVG( execution_time )                                        AS avg_time
                FROM {$wpdb->prefix}tradepress_test_runs
                WHERE test_id = %d",
                absint( $test_id )
            )
        );

        $total        = isset( $row->total )  ? (int) $row->total  : 0;
        $passed       = isset( $row->passed ) ? (int) $row->passed : 0;
        $success_rate = $total > 0 ? round( ( $passed / $total ) * 100, 2 ) : 0.0;

        return array(
            'total'        => $total,
            'passed'       => $passed,
            'failed'       => isset( $row->failed ) ? (int) $row->failed : 0,
            'error'        => isset( $row->error )  ? (int) $row->error  : 0,
            'success_rate' => $success_rate,
            'avg_time'     => isset( $row->avg_time ) ? (float) $row->avg_time : 0.0,
        );
    }

    /**
     * Return a summary across all tests: total runs, pass rate, and last-run date.
     *
     * @return array {
     *     @type int    $total_tests   Number of registered tests.
     *     @type int    $total_runs    Total run records.
     *     @type int    $total_passed  Total passed runs.
     *     @type int    $total_failed  Total failed runs.
     *     @type float  $overall_rate  Overall pass percentage.
     *     @type string $last_run_date MySQL datetime of the most recent run, or empty.
     * }
      * @version 1.0.0
     */
    public static function get_global_summary() {
        global $wpdb;

        $row = $wpdb->get_row(
            "SELECT
                COUNT(*)                                                     AS total_runs,
                SUM( CASE WHEN status = 'passed' THEN 1 ELSE 0 END )       AS total_passed,
                SUM( CASE WHEN status = 'failed' THEN 1 ELSE 0 END )       AS total_failed,
                MAX( run_date )                                              AS last_run_date
            FROM {$wpdb->prefix}tradepress_test_runs"
        );

        $test_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}tradepress_tests" );
        $total_runs  = isset( $row->total_runs )  ? (int) $row->total_runs  : 0;
        $total_pass  = isset( $row->total_passed ) ? (int) $row->total_passed : 0;
        $overall     = $total_runs > 0 ? round( ( $total_pass / $total_runs ) * 100, 2 ) : 0.0;

        return array(
            'total_tests'   => $test_count,
            'total_runs'    => $total_runs,
            'total_passed'  => $total_pass,
            'total_failed'  => isset( $row->total_failed ) ? (int) $row->total_failed : 0,
            'overall_rate'  => $overall,
            'last_run_date' => isset( $row->last_run_date ) ? $row->last_run_date : '',
        );
    }

    /**
     * Fetch all fault records for a test or run.
     *
     * @param array $args {
     *     Optional query arguments.
     *     @type int    $test_id Filter by test ID.
     *     @type int    $run_id  Filter by run ID.
     *     @type string $status  Filter by status ('open', 'resolved', etc.).
     *     @type int    $limit   Maximum rows (default 50, 0 = all).
     * }
     * @return array Array of fault objects.
      * @version 1.0.0
     */
    public static function get_faults( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'test_id' => 0,
            'run_id'  => 0,
            'status'  => '',
            'limit'   => 50,
        );
        $args = wp_parse_args( $args, $defaults );

        $where  = array( '1=1' );
        $values = array();

        if ( $args['test_id'] > 0 ) {
            $where[]  = 'test_id = %d';
            $values[] = (int) $args['test_id'];
        }
        if ( $args['run_id'] > 0 ) {
            $where[]  = 'run_id = %d';
            $values[] = (int) $args['run_id'];
        }
        if ( ! empty( $args['status'] ) ) {
            $where[]  = 'status = %s';
            $values[] = sanitize_key( $args['status'] );
        }

        $sql = 'SELECT * FROM ' . $wpdb->prefix . 'tradepress_test_faults WHERE ' . implode( ' AND ', $where ) . ' ORDER BY fault_id DESC';

        if ( (int) $args['limit'] > 0 ) {
            $values[] = (int) $args['limit'];
            $sql     .= ' LIMIT %d';
        }

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare( $sql, $values );
        }

        return $wpdb->get_results( $sql );
    }

    /**
     * Record a new fault against a test run.
     *
     * @param array $fault_data {
     *     @type int    $test_id       Required.
     *     @type int    $run_id        Run that surfaced the fault.
     *     @type string $fault_type    E.g. 'assertion', 'exception', 'timeout'.
     *     @type string $message       Human-readable description.
     *     @type string $stack_trace   Optional PHP back-trace.
     *     @type string $status        'open' (default) | 'resolved' | 'wontfix'.
     * }
     * @return int|false New fault ID on success, false on failure.
      * @version 1.0.0
     */
    public static function record_fault( $fault_data ) {
        global $wpdb;

        $defaults = array(
            'test_id'     => 0,
            'run_id'      => 0,
            'fault_type'  => 'assertion',
            'message'     => '',
            'stack_trace' => '',
            'status'      => 'open',
            'created_at'  => current_time( 'mysql' ),
        );

        $data = wp_parse_args( $fault_data, $defaults );

        if ( empty( $data['test_id'] ) || empty( $data['message'] ) ) {
            return false;
        }

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'tradepress_test_faults',
            $data,
            array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
        );

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Delete all run records older than a given number of days.
     *
     * @param int $days Runs older than this many days will be removed. Default 90.
     * @return int Number of rows deleted.
      * @version 1.0.0
     */
    public static function prune_old_runs( $days = 90 ) {
        global $wpdb;

        $days   = absint( $days );
        $cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        return (int) $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}tradepress_test_runs WHERE run_date < %s",
                $cutoff
            )
        );
    }
}

endif;
