<?php
/**
 * Example integration test for the TradePress testing framework.
 *
 * @package TradePress/Testing/Integration
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Example_Integration_Test' ) ) :

class TradePress_Example_Integration_Test extends TradePress_Test_Case {

    /**
     * Metadata used during discovery/registration.
     *
     * @var array
     */
    public static $test_metadata = array(
        'title' => 'Example Integration Test',
        'description' => 'Validates registration/discovery integration against live plugin structures.',
        'category' => 'standard',
        'priority_level' => 2,
    );

    /**
     * Ensure plugin constants required by the test framework exist.
      *
      * @version 1.0.0
     */
    public function test_framework_constants_exist() {
        $this->assertTrue( defined( 'TRADEPRESS_PLUGIN_DIR_PATH' ), 'TRADEPRESS_PLUGIN_DIR_PATH must be defined.' );
        $this->assertTrue( defined( 'TRADEPRESS_VERSION' ), 'TRADEPRESS_VERSION must be defined.' );
    }

    /**
     * Ensure the configured discovery directories include unit and integration folders.
      *
      * @version 1.0.0
     */
    public function test_discovery_directories_include_core_paths() {
        $directories = TradePress_Test_Registry::get_discovery_directories();

        $this->assertTrue( is_array( $directories ), 'Discovery directories should be an array.' );
        $this->assertTrue( in_array( 'tests/unit', $directories, true ), 'tests/unit should be included in discovery paths.' );
        $this->assertTrue( in_array( 'tests/integration', $directories, true ), 'tests/integration should be included in discovery paths.' );
    }

    /**
     * Ensure discovery returns a valid summary structure.
      *
      * @version 1.0.0
     */
    public function test_discovery_summary_shape() {
        $summary = TradePress_Test_Registry::discover_tests();

        $required_keys = array( 'directories', 'files_scanned', 'registered', 'updated', 'skipped', 'errors' );

        foreach ( $required_keys as $key ) {
            $this->assertTrue(
                array_key_exists( $key, $summary ),
                sprintf( 'Discovery summary should include key: %s', $key )
            );
        }

        $this->assertTrue( is_array( $summary['directories'] ), 'directories must be an array.' );
        $this->assertTrue( is_array( $summary['errors'] ), 'errors must be an array.' );
        $this->assertTrue( is_int( $summary['files_scanned'] ), 'files_scanned must be an integer.' );
    }

    /**
     * Ensure discovered classes can be autoloaded via the registry lookup.
      *
      * @version 1.0.0
     */
    public function test_registered_class_autoload() {
        $loaded = TradePress_Test_Registry::autoload_registered_test_class( 'TradePress_Example_Test' );

        $this->assertTrue( is_bool( $loaded ), 'autoload_registered_test_class should return a boolean.' );
        $this->assertTrue( class_exists( 'TradePress_Example_Test' ), 'TradePress_Example_Test should be available after autoload attempt.' );
    }
}

endif;
