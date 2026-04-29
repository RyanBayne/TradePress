<?php
/**
 * Test utility helpers for the TradePress testing framework.
 *
 * Provides static helpers for data generation, comparison, timing, and
 * common assertion patterns used by TradePress_Test_Case subclasses.
 *
 * @package TradePress/Testing
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Test_Utils' ) ) :

/**
 * Class TradePress_Test_Utils
 *
 * A collection of static helpers available to all test cases.
 */
class TradePress_Test_Utils {

    // -------------------------------------------------------------------------
    // Timing helpers
    // -------------------------------------------------------------------------

    /**
     * Timers keyed by label.
     *
     * @var float[]
     */
    private static $timers = array();

    /**
     * Start a named timer.
     *
     * @param string $label Unique label for this timer.
      * @version 1.0.0
     */
    public static function start_timer( $label = 'default' ) {
        self::$timers[ $label ] = microtime( true );
    }

    /**
     * Stop a named timer and return elapsed milliseconds.
     *
     * @param string $label The label passed to start_timer().
     * @return float Elapsed time in milliseconds, or 0.0 if timer was not started.
      * @version 1.0.0
     */
    public static function stop_timer( $label = 'default' ) {
        if ( ! isset( self::$timers[ $label ] ) ) {
            return 0.0;
        }
        $elapsed = ( microtime( true ) - self::$timers[ $label ] ) * 1000;
        unset( self::$timers[ $label ] );
        return round( $elapsed, 3 );
    }

    // -------------------------------------------------------------------------
    // Data generation helpers
    // -------------------------------------------------------------------------

    /**
     * Generate a random stock ticker symbol (for test fixture data).
     *
     * @param int $length Number of characters (default 4).
     * @return string Upper-case alphabetic string.
      * @version 1.0.0
     */
    public static function random_ticker( $length = 4 ) {
        $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ticker = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $ticker .= $chars[ wp_rand( 0, 25 ) ];
        }
        return $ticker;
    }

    /**
     * Generate a minimal OHLCV candle array for a given ticker and date.
     *
     * @param string $ticker Symbol, e.g. "AAPL".
     * @param string $date   MySQL date string "YYYY-MM-DD".
     * @param float  $close  Closing price (open/high/low/volume derived from it).
     * @return array Candle data keyed by open, high, low, close, volume, date, ticker.
      * @version 1.0.0
     */
    public static function make_candle( $ticker, $date, $close = 100.0 ) {
        $close  = (float) $close;
        $spread = $close * 0.02;

        return array(
            'ticker' => strtoupper( $ticker ),
            'date'   => $date,
            'open'   => round( $close - ( $spread / 2 ), 4 ),
            'high'   => round( $close + $spread,          4 ),
            'low'    => round( $close - $spread,           4 ),
            'close'  => round( $close,                    4 ),
            'volume' => wp_rand( 100000, 10000000 ),
        );
    }

    /**
     * Generate a sequence of OHLCV candles for backtesting fixtures.
     *
     * @param string $ticker     Symbol.
     * @param string $start_date Start date "YYYY-MM-DD".
     * @param int    $days       Number of candles to generate.
     * @param float  $seed_price Starting close price.
     * @return array Indexed array of candle arrays.
      * @version 1.0.0
     */
    public static function make_candle_series( $ticker, $start_date, $days = 30, $seed_price = 100.0 ) {
        $candles = array();
        $price   = (float) $seed_price;
        $date    = strtotime( $start_date );

        for ( $i = 0; $i < $days; $i++ ) {
            $change  = $price * ( ( wp_rand( -300, 300 ) / 10000 ) ); // ±3%
            $price   = max( 0.01, $price + $change );
            $candles[] = self::make_candle( $ticker, gmdate( 'Y-m-d', $date ), $price );
            $date   += DAY_IN_SECONDS;
        }

        return $candles;
    }

    // -------------------------------------------------------------------------
    // Deep-comparison helpers
    // -------------------------------------------------------------------------

    /**
     * Deep-compare two arrays, returning true only when every key and value match.
     *
     * @param array $a First array.
     * @param array $b Second array.
     * @return bool True when arrays are identical (recursive).
      * @version 1.0.0
     */
    public static function arrays_equal( array $a, array $b ) {
        if ( count( $a ) !== count( $b ) ) {
            return false;
        }
        foreach ( $a as $key => $value ) {
            if ( ! array_key_exists( $key, $b ) ) {
                return false;
            }
            if ( is_array( $value ) && is_array( $b[ $key ] ) ) {
                if ( ! self::arrays_equal( $value, $b[ $key ] ) ) {
                    return false;
                }
            } elseif ( $value !== $b[ $key ] ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return a human-readable diff between two scalars or arrays.
     *
     * @param mixed $expected Expected value.
     * @param mixed $actual   Actual value.
     * @return string Diff description.
      * @version 1.0.0
     */
    public static function diff( $expected, $actual ) {
        $e_str = is_scalar( $expected ) ? (string) $expected : wp_json_encode( $expected );
        $a_str = is_scalar( $actual )   ? (string) $actual   : wp_json_encode( $actual );
        return "Expected: {$e_str} | Got: {$a_str}";
    }

    // -------------------------------------------------------------------------
    // WordPress-specific helpers
    // -------------------------------------------------------------------------

    /**
     * Check whether a WordPress database table exists.
     *
     * @param string $table_name Full table name (including prefix).
     * @return bool
      * @version 1.0.0
     */
    public static function table_exists( $table_name ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is safe: constructed from $wpdb->prefix + known constant.
        $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
        return $exists === $table_name;
    }

    /**
     * Check whether a WordPress option exists (is set to any value).
     *
     * @param string $option_name Option name.
     * @return bool
      * @version 1.0.0
     */
    public static function option_exists( $option_name ) {
        return get_option( $option_name, '__NOT_SET__' ) !== '__NOT_SET__';
    }

    /**
     * Assert that a function is callable, returning a descriptive error string
     * on failure or an empty string on success.
     *
     * @param string $function_name PHP function name.
     * @return string Empty string on success, error message on failure.
      * @version 1.0.0
     */
    public static function assert_function_exists( $function_name ) {
        if ( function_exists( $function_name ) ) {
            return '';
        }
        return "Expected function '{$function_name}' to exist but it was not found.";
    }

    /**
     * Assert that a class is defined.
     *
     * @param string $class_name PHP class name.
     * @return string Empty string on success, error message on failure.
      * @version 1.0.0
     */
    public static function assert_class_exists( $class_name ) {
        if ( class_exists( $class_name ) ) {
            return '';
        }
        return "Expected class '{$class_name}' to exist but it was not found.";
    }

    // -------------------------------------------------------------------------
    // Number helpers
    // -------------------------------------------------------------------------

    /**
     * Check whether a float value is within an inclusive tolerance band.
     *
     * @param float $expected  Target value.
     * @param float $actual    Value under test.
     * @param float $tolerance Maximum allowed absolute difference.
     * @return bool
      * @version 1.0.0
     */
    public static function floats_near( $expected, $actual, $tolerance = 0.0001 ) {
        return abs( (float) $expected - (float) $actual ) <= (float) $tolerance;
    }

    /**
     * Return true when a value is a finite, non-NaN number.
     *
     * @param mixed $value Value to check.
     * @return bool
      * @version 1.0.0
     */
    public static function is_valid_number( $value ) {
        return is_numeric( $value ) && is_finite( (float) $value );
    }
}

endif;
