<?php
/**
 * Example unit test — validates core framework assumptions and plugin utilities.
 *
 * This test file serves as a reference implementation showing how to write
 * unit tests using TradePress_Test_Case. Register it via the Testing admin tab
 * (discover tests) or by calling TradePress_Test_Registry::register_test().
 *
 * @package TradePress/Testing/Unit
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Example_Test' ) ) :

/**
 * Class TradePress_Example_Test
 *
 * Tests core plugin functions, DB table presence, and framework utilities.
 * All public methods whose names begin with "test" are run automatically
 * by TradePress_Test_Case::run_all_tests().
 */
class TradePress_Example_Test extends TradePress_Test_Case {

    /**
     * Static metadata used by TradePress_Test_Registry::discover_tests()
     * to auto-register this test file.
     *
     * @var array
     */
    public static $test_metadata = array(
        'title'          => 'Example Unit Test',
        'description'    => 'Validates core plugin functions, DB tables, and test utilities. Use as a reference for writing new unit tests.',
        'category'       => 'standard',
        'priority_level' => 1,
    );

    // -------------------------------------------------------------------------
    // Test methods
    // -------------------------------------------------------------------------

    /**
     * Verify that tradepress_is_developer_mode() exists and returns a boolean.
      *
      * @version 1.0.0
     */
    public function test_developer_mode_function_exists_and_returns_bool() {
        $error = TradePress_Test_Utils::assert_function_exists( 'tradepress_is_developer_mode' );
        $this->assertEquals( '', $error, 'tradepress_is_developer_mode() must exist' );

        if ( '' === $error ) {
            $result = tradepress_is_developer_mode();
            $this->assertTrue(
                is_bool( $result ),
                'tradepress_is_developer_mode() must return a boolean, got: ' . gettype( $result )
            );
        }
    }

    /**
     * Verify that tradepress_trace_log() exists and is callable.
      *
      * @version 1.0.0
     */
    public function test_trace_log_function_exists() {
        $error = TradePress_Test_Utils::assert_function_exists( 'tradepress_trace_log' );
        $this->assertEquals( '', $error, 'tradepress_trace_log() must be defined' );
    }

    /**
     * Verify that the three testing DB tables exist.
      *
      * @version 1.0.0
     */
    public function test_testing_db_tables_exist() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'tradepress_tests',
            $wpdb->prefix . 'tradepress_test_runs',
            $wpdb->prefix . 'tradepress_test_faults',
        );

        foreach ( $tables as $table ) {
            $this->assertTrue(
                TradePress_Test_Utils::table_exists( $table ),
                "Required DB table is missing: {$table}"
            );
        }
    }

    /**
     * Verify TradePress_Test_Utils::arrays_equal() behaves correctly.
      *
      * @version 1.0.0
     */
    public function test_utils_arrays_equal() {
        // Identical arrays.
        $this->assertTrue(
            TradePress_Test_Utils::arrays_equal( array( 'a' => 1, 'b' => 2 ), array( 'a' => 1, 'b' => 2 ) ),
            'arrays_equal() should return true for identical arrays'
        );

        // Different values.
        $this->assertFalse(
            TradePress_Test_Utils::arrays_equal( array( 'a' => 1 ), array( 'a' => 99 ) ),
            'arrays_equal() should return false when values differ'
        );

        // Different counts.
        $this->assertFalse(
            TradePress_Test_Utils::arrays_equal( array( 1, 2, 3 ), array( 1, 2 ) ),
            'arrays_equal() should return false when counts differ'
        );
    }

    /**
     * Verify TradePress_Test_Utils::is_valid_number() correctly identifies finite numbers.
      *
      * @version 1.0.0
     */
    public function test_utils_is_valid_number() {
        $this->assertTrue( TradePress_Test_Utils::is_valid_number( 42 ),     'Integer 42 should be valid' );
        $this->assertTrue( TradePress_Test_Utils::is_valid_number( 3.14 ),   'Float 3.14 should be valid' );
        $this->assertTrue( TradePress_Test_Utils::is_valid_number( '7.5' ),  'Numeric string should be valid' );
        $this->assertFalse( TradePress_Test_Utils::is_valid_number( 'abc' ), 'Non-numeric string must not be valid' );
        $this->assertFalse( TradePress_Test_Utils::is_valid_number( INF ),   'INF must not be valid' );
        $this->assertFalse( TradePress_Test_Utils::is_valid_number( NAN ),   'NAN must not be valid' );
    }

    /**
     * Verify TradePress_Test_Utils::floats_near() tolerance checks.
      *
      * @version 1.0.0
     */
    public function test_utils_floats_near() {
        $this->assertTrue(
            TradePress_Test_Utils::floats_near( 1.0, 1.00005, 0.0001 ),
            'Values within tolerance should be considered near'
        );
        $this->assertFalse(
            TradePress_Test_Utils::floats_near( 1.0, 1.5, 0.0001 ),
            'Values outside tolerance should not be considered near'
        );
    }

    /**
     * Verify that TradePress_Test_Results::get_global_summary() returns the expected keys.
      *
      * @version 1.0.0
     */
    public function test_results_global_summary_structure() {
        $summary = TradePress_Test_Results::get_global_summary();

        $required_keys = array( 'total_tests', 'total_runs', 'total_passed', 'total_failed', 'overall_rate', 'last_run_date' );

        foreach ( $required_keys as $key ) {
            $this->assertTrue(
                array_key_exists( $key, $summary ),
                "get_global_summary() result is missing key: {$key}"
            );
        }

        $this->assertTrue(
            is_int( $summary['total_tests'] ),
            'total_tests must be an integer'
        );
        $this->assertTrue(
            is_float( $summary['overall_rate'] ) || is_int( $summary['overall_rate'] ),
            'overall_rate must be numeric'
        );
    }

    /**
     * Verify make_candle() produces a structurally correct OHLCV array.
      *
      * @version 1.0.0
     */
    public function test_utils_make_candle_structure() {
        $candle = TradePress_Test_Utils::make_candle( 'TEST', '2024-01-15', 150.0 );

        foreach ( array( 'ticker', 'date', 'open', 'high', 'low', 'close', 'volume' ) as $key ) {
            $this->assertTrue(
                array_key_exists( $key, $candle ),
                "make_candle() result is missing key: {$key}"
            );
        }

        $this->assertEquals( 'TEST', $candle['ticker'], 'Ticker should match input' );
        $this->assertEquals( '2024-01-15', $candle['date'], 'Date should match input' );
        $this->assertEquals( 150.0, $candle['close'], 'Close should match the seed price' );

        $this->assertTrue(
            $candle['high'] >= $candle['close'],
            'High must be >= close'
        );
        $this->assertTrue(
            $candle['low'] <= $candle['close'],
            'Low must be <= close'
        );
    }
}

endif;
