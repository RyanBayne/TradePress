<?php
/**
 * TradePress Endpoint Tester
 *
 * Handles testing of API endpoints for different trading platforms.
 *
 * @package TradePress
 * @subpackage admin/page/TradingPlatforms
 * @version 1.1.4
 * @since 1.0.0
 * @created 2025-05-05 13:15:00
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TradePress_Endpoint_Tester {
	/**
	 * Constructor
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_tradepress_test_endpoint', array( $this, 'ajax_test_endpoint' ) );

		// Process standard POST requests for endpoint testing (non-Ajax method)
		if ( isset( $_POST['tradepress_test_endpoint'] ) ) {
			// Log to debug.log for debugging purposes
			if ( function_exists( 'error_log' ) ) {
			}

			// Check for valid nonce - important fix: look for any field that starts with "tradepress_test_endpoint_nonce_"
			$nonce_found = false;
			foreach ( $_POST as $key => $value ) {
				if ( strpos( $key, 'tradepress_test_endpoint_nonce_' ) === 0 ) {
					$nonce_found = true;
					// Verify this nonce against our standard action
					if ( wp_verify_nonce( $value, 'tradepress_test_endpoint_nonce' ) ) {
						// Process the test request directly (not through Ajax)
						$this->process_test_request();
					}
					break;
				}
			}

			// Log if no valid nonce was found
			if ( ! $nonce_found && function_exists( 'error_log' ) ) {
			}
		}
	}

	/**
	 * AJAX handler for testing endpoints
	 *
	 * @version 1.0.0
	 */
	public function ajax_test_endpoint() {
		// Verify nonce
		check_ajax_referer( 'tradepress_test_endpoint_nonce', 'nonce' );

		// Check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'tradepress' ) ) );
		}

		// Get request parameters
		$endpoint_id = isset( $_POST['endpoint'] ) ? sanitize_text_field( $_POST['endpoint'] ) : '';
		$platform_id = isset( $_POST['platform'] ) ? sanitize_text_field( $_POST['platform'] ) : '';

		// Validate input
		if ( empty( $endpoint_id ) || empty( $platform_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing required parameters.', 'tradepress' ) ) );
		}

		// Get the endpoint details
		$endpoint_details = $this->get_endpoint_details( $endpoint_id, $platform_id );

		// Test the endpoint
		$result = $this->test_endpoint( $endpoint_details, $platform_id );

		if ( is_wp_error( $result ) ) {
			// Format error report for display
			$error_report = $this->generate_error_report( $endpoint_details, $result, $platform_id );
			wp_send_json_error( array( 'message' => $error_report ) );
		} else {
			// Return successful response
			wp_send_json_success(
				array(
					'message'  => __( 'Endpoint test completed successfully!', 'tradepress' ),
					'data'     => $result,
					'endpoint' => $endpoint_id,
					'platform' => $platform_id,
				)
			);
		}
	}

	/**
	 * Process a test request directly (non-Ajax)
	 *
	 * @version 1.0.0
	 */
	private function process_test_request() {
		// Get parameters from POST data
		$endpoint_id  = isset( $_POST['endpoint'] ) ? sanitize_text_field( $_POST['endpoint'] ) : '';
		$endpoint_key = isset( $_POST['endpoint_key'] ) ? sanitize_text_field( $_POST['endpoint_key'] ) : '';
		$platform_id  = isset( $_POST['platform'] ) ? sanitize_text_field( $_POST['platform'] ) : '';

		if ( empty( $endpoint_id ) || empty( $platform_id ) ) {
			// Log the error for debugging
			if ( function_exists( 'error_log' ) ) {
			}
			return;
		}

		// Get the endpoint details
		$endpoint_details = $this->get_endpoint_details( $endpoint_id, $platform_id );

		// Test the endpoint
		$test_result = $this->test_endpoint( $endpoint_details, $platform_id );

		// Store results in transient for the page to display
		$transient_key = 'tradepress_endpoint_test_' . md5( $platform_id . '_' . $endpoint_id );

		$environment = 'Live';

		if ( is_wp_error( $test_result ) ) {
			// Generate enhanced error report
			$detailed_error = $this->generate_error_report( $endpoint_details, $test_result, $platform_id );
			$error_data     = $test_result->get_error_data();

			$results = array(
				'success'         => false,
				'message'         => $test_result->get_error_message(),
				'error_code'      => $test_result->get_error_code(),
				'raw_response'    => $error_data,
				'error_report'    => $detailed_error,
				'platform'        => $platform_id,
				'endpoint'        => $endpoint_id,
				'endpoint_key'    => $endpoint_key,
				'timestamp'       => current_time( 'mysql' ),
				'debug_timestamp' => microtime( true ),
				'status_code'     => is_array( $error_data ) && isset( $error_data['status_code'] ) ? $error_data['status_code'] : $test_result->get_error_code(),
				'environment'     => $environment,
			);
		} else {
			$results = array(
				'success'         => true,
				'message'         => __( 'Endpoint test completed successfully!', 'tradepress' ),
				'data'            => $test_result,
				'raw_response'    => isset( $test_result['raw_response'] ) ? $test_result['raw_response'] : $test_result,
				'platform'        => $platform_id,
				'endpoint'        => $endpoint_id,
				'endpoint_key'    => $endpoint_key,
				'timestamp'       => current_time( 'mysql' ),
				'debug_timestamp' => microtime( true ),
				'status_code'     => isset( $test_result['status_code'] ) ? $test_result['status_code'] : 200,
				'environment'     => $environment,
			);
		}

		// Save the results to transients for retrieval on page reload
		set_transient( $transient_key, $results, HOUR_IN_SECONDS );

		// Also store a reference for which test was last run so the page knows which results to show
		$last_test = array(
			'platform'     => $platform_id,
			'endpoint'     => $endpoint_id,
			'endpoint_key' => $endpoint_key,
			'timestamp'    => current_time( 'mysql' ),
		);
		set_transient( 'tradepress_last_endpoint_test', $last_test, HOUR_IN_SECONDS );

		// Log successful test for debugging
		if ( function_exists( 'error_log' ) ) {
		}
	}

	/**
	 * Get endpoint details by ID and platform
	 *
	 * @param string $endpoint_id The endpoint ID
	 * @param string $platform_id The platform ID
	 * @return array|false Endpoint details or false if not found
	 * @version 1.0.0
	 */
	private function get_endpoint_details( $endpoint_id, $platform_id ) {
		$registry_endpoint = $this->get_registry_endpoint( $endpoint_id, $platform_id );

		if ( $registry_endpoint ) {
			return $this->normalize_registry_endpoint( $endpoint_id, $platform_id, $registry_endpoint );
		}

		return array(
			'id'          => $endpoint_id,
			'platform'    => $platform_id,
			'method'      => 'GET',
			'path'        => "/{$endpoint_id}",
			'description' => __( 'API Endpoint', 'tradepress' ),
			'parameters'  => array(),
			'version'     => 'v1',
			'source'      => 'fallback',
		);
	}

	/**
	 * Get an endpoint definition from the platform endpoint registry.
	 *
	 * @param string $endpoint_id Endpoint key.
	 * @param string $platform_id Platform key.
	 * @return array|false
	 */
	private function get_registry_endpoint( $endpoint_id, $platform_id ) {
		$registry = $this->get_endpoint_registry( $platform_id );

		if ( empty( $registry['class'] ) || ! class_exists( $registry['class'] ) ) {
			return false;
		}

		if ( method_exists( $registry['class'], 'get_endpoint' ) ) {
			$endpoint = call_user_func( array( $registry['class'], 'get_endpoint' ), $endpoint_id );
			if ( $endpoint ) {
				return $endpoint;
			}
		}

		if ( method_exists( $registry['class'], 'get_endpoints' ) ) {
			$endpoints = call_user_func( array( $registry['class'], 'get_endpoints' ) );
			if ( is_array( $endpoints ) && isset( $endpoints[ $endpoint_id ] ) ) {
				return $endpoints[ $endpoint_id ];
			}
		}

		return false;
	}

	/**
	 * Resolve endpoint registry class and file for a platform.
	 *
	 * @param string $platform_id Platform key.
	 * @return array
	 */
	private function get_endpoint_registry( $platform_id ) {
		$registries = array(
			'alltick'            => array( 'class' => 'TradePress_AllTick_Endpoints', 'file' => 'api/alltick/alltick-endpoints.php' ),
			'alpaca'             => array( 'class' => 'TradePress_Alpaca_Endpoints', 'file' => 'api/alpaca/alpaca-endpoints.php' ),
			'alphavantage'       => array( 'class' => 'TradePress_AlphaVantage_Endpoints', 'file' => 'api/alphavantage/alphavantage-endpoints.php' ),
			'eodhistoricaldata'  => array( 'class' => 'TradePress_EODHistoricalData_Endpoints', 'file' => 'api/eodhistoricaldata/eodhistoricaldata-endpoints.php' ),
			'eodhd'              => array( 'class' => 'TradePress_EODHD_Endpoints', 'file' => 'api/eodhd/eodhd-endpoints.php' ),
			'finnhub'            => array( 'class' => 'TradePress_Finnhub_Endpoints', 'file' => 'api/finnhub/finnhub-endpoints.php' ),
			'fmp'                => array( 'class' => 'TradePress_FMP_Endpoints', 'file' => 'api/fmp/fmp-endpoints.php' ),
			'fred'               => array( 'class' => 'TradePress_FRED_Endpoints', 'file' => 'api/fred/fred-endpoints.php' ),
			'ibkr'               => array( 'class' => 'TradePress_IBKR_Endpoints', 'file' => 'api/ibkr/ibkr-endpoints.php' ),
			'iexcloud'           => array( 'class' => 'TradePress_IEXCloud_Endpoints', 'file' => 'api/iexcloud/iexcloud-endpoints.php' ),
			'marketstack'        => array( 'class' => 'TradePress_MarketStack_Endpoints', 'file' => 'api/marketstack/marketstack-endpoints.php' ),
			'polygon'            => array( 'class' => 'TradePress_Polygon_Endpoints', 'file' => 'api/polygon/polygon-endpoints.php' ),
			'quandl'             => array( 'class' => 'TradePress_Quandl_Endpoints', 'file' => 'api/quandl/quandl-endpoints.php' ),
			'tradier'            => array( 'class' => 'TradePress_Tradier_Endpoints', 'file' => 'api/tradier/tradier-endpoints.php' ),
			'trading212'         => array( 'class' => 'TradePress_Trading212_Endpoints', 'file' => 'api/trading212/trading212-endpoints.php' ),
			'tradingview'        => array( 'class' => 'TradePress_TradingView_Endpoints', 'file' => 'api/tradingview/tradingview-endpoints.php' ),
			'twelvedata'         => array( 'class' => 'TradePress_TwelveData_Endpoints', 'file' => 'api/twelvedata/twelvedata-endpoints.php' ),
			'webull'             => array( 'class' => 'TradePress_WeBull_Endpoints', 'file' => 'api/webull/webull-endpoints.php' ),
			'yahoo'              => array( 'class' => 'TradePress_Yahoo_Endpoints', 'file' => 'api/yahoo/yahoo-endpoints.php' ),
		);

		if ( empty( $registries[ $platform_id ] ) ) {
			return array();
		}

		$registry = $registries[ $platform_id ];
		if ( ! class_exists( $registry['class'] ) && ! empty( $registry['file'] ) ) {
			$file = TRADEPRESS_PLUGIN_DIR_PATH . $registry['file'];
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}

		return $registry;
	}

	/**
	 * Normalize provider endpoint definitions to the tester contract.
	 *
	 * @param string $endpoint_id Endpoint key.
	 * @param string $platform_id Platform key.
	 * @param array  $definition Registry endpoint definition.
	 * @return array
	 */
	private function normalize_registry_endpoint( $endpoint_id, $platform_id, $definition ) {
		$parameters = array();

		if ( isset( $definition['parameters'] ) && is_array( $definition['parameters'] ) ) {
			$parameters = $definition['parameters'];
		} elseif ( isset( $definition['required_params'] ) || isset( $definition['optional_params'] ) ) {
			$required = isset( $definition['required_params'] ) && is_array( $definition['required_params'] ) ? $definition['required_params'] : array();
			$optional = isset( $definition['optional_params'] ) && is_array( $definition['optional_params'] ) ? $definition['optional_params'] : array();
			foreach ( array_merge( $required, $optional ) as $parameter_name ) {
				$parameters[ $parameter_name ] = array(
					'required' => in_array( $parameter_name, $required, true ),
				);
			}
		}

		return array(
			'id'             => $endpoint_id,
			'platform'       => $platform_id,
			'method'         => isset( $definition['method'] ) ? strtoupper( $definition['method'] ) : 'GET',
			'path'           => isset( $definition['endpoint'] ) ? $definition['endpoint'] : '',
			'function'       => isset( $definition['function'] ) ? $definition['function'] : $endpoint_id,
			'description'    => isset( $definition['description'] ) ? $definition['description'] : __( 'API Endpoint', 'tradepress' ),
			'parameters'     => $parameters,
			'version'        => isset( $definition['version'] ) ? $definition['version'] : 'v1',
			'rate_limit'     => isset( $definition['rate_limit'] ) ? $definition['rate_limit'] : '',
			'scopes'         => isset( $definition['scopes'] ) ? $definition['scopes'] : array(),
			'base_url'       => isset( $definition['base_url'] ) ? $definition['base_url'] : '',
			'source'         => 'registry',
			'raw_definition' => $definition,
		);
	}

	/**
	 * Test an endpoint
	 *
	 * @param array  $endpoint Endpoint details
	 * @param string $platform_id The platform ID
	 * @return array|WP_Error Test result or error
	 * @version 1.0.0
	 */
	public function test_endpoint( $endpoint, $platform_id ) {
		// Get provider details to determine API type
		$provider_info = $this->get_platform_info( $platform_id );
		$api_type      = isset( $provider_info['api_type'] ) ? $provider_info['api_type'] : 'trading';

		// Verify API configuration
		$config_check = $this->verify_api_configuration( $platform_id, $api_type );

		// Check if we have valid credentials
		if ( ! $config_check['configured'] ) {
			$message = sprintf(
				/* translators: %s: string value */
				__( 'API credentials are not configured. Missing: %s', 'tradepress' ),
				implode( ', ', $config_check['missing'] )
			);

			// Add more detailed information for troubleshooting
			$details = array(
				'platform'            => $platform_id,
				'api_type'            => $api_type,
				'missing_credentials' => $config_check['missing'],
				'option_names'        => $config_check['option_names'],
			);

			if ( ! empty( $config_check['suggestions'] ) ) {
				$details['suggestions'] = $config_check['suggestions'];
			}

			if ( ! empty( $config_check['settings_page'] ) ) {
				$details['settings_page'] = $config_check['settings_page'];
			}

			return new WP_Error(
				'missing_credentials',
				$message,
				$details
			);
		}

		// Get API credentials
		$api_credentials = $this->get_api_credentials( $platform_id, $api_type );

		return $this->make_api_request( $platform_id, $endpoint, $api_credentials );
	}

	/**
	 * Verify API configuration is complete
	 *
	 * @param string $platform_id The platform ID
	 * @param string $api_type The API type (trading or data_only)
	 * @return array Configuration status
	 * @version 1.0.0
	 */
	private function verify_api_configuration( $platform_id, $api_type ) {
		$result = array(
			'configured'   => false,
			'missing'      => array(),
			'option_names' => array(),
			'suggestions'  => array(),
		);

		// Check configuration based on API type
		if ( $api_type === 'trading' ) {
			if ( $platform_id === 'trading212' ) {
				$trading_mode = get_option( "TradePress_api_{$platform_id}_trading_mode", 'paper' );
				$api_key      = $this->get_trading212_api_key( $trading_mode );

				$result['option_names'][] = $trading_mode === 'live' ? 'tradepress_trading212_api_key' : 'tradepress_trading212_paper_api_key';
				$result['option_names'][] = $trading_mode === 'live' ? "TradePress_api_{$platform_id}_realmoney_apikey" : "TradePress_api_{$platform_id}_papermoney_apikey";

				if ( empty( $api_key ) ) {
					$result['missing'][] = $trading_mode === 'live' ? 'Live API Key' : 'Demo API Key';
				}

				$result['configured'] = empty( $result['missing'] );
				return $result;
			}

			$trading_mode = get_option( "TradePress_api_{$platform_id}_trading_mode", 'paper' );

			if ( $trading_mode === 'paper' ) {
				$api_key    = get_option( "TradePress_api_{$platform_id}_papermoney_apikey", '' );
				$api_secret = get_option( "TradePress_api_{$platform_id}_papermoney_secretkey", '' );

				$result['option_names'][] = "TradePress_api_{$platform_id}_papermoney_apikey";
				$result['option_names'][] = "TradePress_api_{$platform_id}_papermoney_secretkey";

				if ( empty( $api_key ) ) {
					$result['missing'][] = 'Paper Trading API Key';
				}

				if ( empty( $api_secret ) ) {
					$result['missing'][] = 'Paper Trading Secret Key';
				}
			} else {
				$api_key    = get_option( "TradePress_api_{$platform_id}_realmoney_apikey", '' );
				$api_secret = get_option( "TradePress_api_{$platform_id}_realmoney_secretkey", '' );

				$result['option_names'][] = "TradePress_api_{$platform_id}_realmoney_apikey";
				$result['option_names'][] = "TradePress_api_{$platform_id}_realmoney_secretkey";

				if ( empty( $api_key ) ) {
					$result['missing'][] = 'Live Trading API Key';
				}

				if ( empty( $api_secret ) ) {
					$result['missing'][] = 'Live Trading Secret Key';
				}
			}

			$result['settings_page'] = admin_url( 'admin.php?page=tradepress_platforms&tab=' . $platform_id );
			/* translators: %s: string value */
			$result['suggestions'][] = sprintf( __( 'Configure API credentials in %s settings', 'tradepress' ), $platform_id );

		} elseif ( $api_type === 'data_only' ) {
			// For data-only APIs, check for API key in multiple possible option formats
			$option_names = array(
				"TradePress_api_{$platform_id}_key",
				"TradePress_{$platform_id}_api_key",
				"tradepress_{$platform_id}_api_key",
				"TradePress_api_{$platform_id}_apikey",
				'TradePress_alphavantage_api_key', // Special case for Alpha Vantage
			);

			$api_key = '';
			foreach ( $option_names as $option_name ) {
				$option_value = get_option( $option_name, '' );
				if ( ! empty( $option_value ) ) {
					$api_key = $option_value;
					break;
				}
				$result['option_names'][] = $option_name;
			}

			if ( empty( $api_key ) ) {
				$result['missing'][]     = 'API Key';
				$result['settings_page'] = admin_url( 'admin.php?page=tradepress_platforms&tab=' . $platform_id );
				/* translators: %s: string value */
				$result['suggestions'][] = sprintf( __( 'Configure API credentials in %s settings', 'tradepress' ), $platform_id );
			}
		}

		// If no missing credentials, then configuration is good
		$result['configured'] = empty( $result['missing'] );

		return $result;
	}

	/**
	 * Get API credentials for a platform
	 *
	 * @param string $platform_id The platform ID
	 * @param string $api_type The API type (trading or data_only)
	 * @return array API credentials
	 * @version 1.0.0
	 */
	private function get_api_credentials( $platform_id, $api_type ) {
		$credentials = array(
			'api_key'    => '',
			'api_secret' => '',
			'mode'       => 'paper',
		);

		// Get trading mode for trading platforms
		if ( $api_type === 'trading' ) {
			$trading_mode        = get_option( "TradePress_api_{$platform_id}_trading_mode", 'paper' );
			$credentials['mode'] = $trading_mode;

			if ( $platform_id === 'trading212' ) {
				$credentials['api_key'] = $this->get_trading212_api_key( $trading_mode );
				return $credentials;
			}

			// Get API keys based on trading mode
			if ( $trading_mode === 'paper' ) {
				$credentials['api_key']    = get_option( "TradePress_api_{$platform_id}_papermoney_apikey", '' );
				$credentials['api_secret'] = get_option( "TradePress_api_{$platform_id}_papermoney_secretkey", '' );
			} else {
				$credentials['api_key']    = get_option( "TradePress_api_{$platform_id}_realmoney_apikey", '' );
				$credentials['api_secret'] = get_option( "TradePress_api_{$platform_id}_realmoney_secretkey", '' );
			}
		}
		// For data-only APIs, there's usually just one API key setting
		elseif ( $api_type === 'data_only' ) {
			// Different platforms may store API keys in different option names
			switch ( $platform_id ) {
				case 'alphavantage':
					// Check both possible option name formats for Alpha Vantage API key
					$credentials['api_key'] = get_option( 'tradepress_api_alphavantage_key', '' );
					if ( empty( $credentials['api_key'] ) ) {
						// Try alternative option name format
						$credentials['api_key'] = get_option( 'TradePress_alphavantage_api_key', '' );
					}
					if ( empty( $credentials['api_key'] ) ) {
						// Try another common format
						$credentials['api_key'] = get_option( 'tradepress_alphavantage_api_key', '' );
					}
					break;
				case 'twelvedata':
					$credentials['api_key'] = get_option( 'TradePress_api_twelvedata_key', '' );
					break;
				case 'finnhub':
					$credentials['api_key'] = get_option( 'TradePress_api_finnhub_key', '' );
					break;
				default:
					// Generic fallback for other data APIs
					$credentials['api_key']    = get_option( "TradePress_api_{$platform_id}_key", '' );
					$credentials['api_secret'] = get_option( "TradePress_api_{$platform_id}_secret", '' );

					// Try alternative option name formats if the primary option is empty
					if ( empty( $credentials['api_key'] ) ) {
						$credentials['api_key'] = get_option( "TradePress_{$platform_id}_api_key", '' );
					}
					if ( empty( $credentials['api_key'] ) ) {
						$credentials['api_key'] = get_option( "tradepress_{$platform_id}_api_key", '' );
					}
					break;
			}
		}

		return $credentials;
	}

	/**
	 * Get the configured Trading212 API key for the current environment.
	 *
	 * @param string $trading_mode Current trading mode.
	 * @return string
	 */
	private function get_trading212_api_key( $trading_mode ) {
		$api_settings = get_option( 'tradepress_api_settings', array() );
		$api_key      = '';

		if ( $trading_mode === 'live' ) {
			$api_key = trim( (string) get_option( 'tradepress_trading212_api_key', '' ) );
			if ( '' === $api_key ) {
				$api_key = trim( (string) get_option( 'TradePress_api_trading212_realmoney_apikey', '' ) );
			}
		} else {
			$api_key = trim( (string) get_option( 'tradepress_trading212_paper_api_key', '' ) );
			if ( '' === $api_key ) {
				$api_key = trim( (string) get_option( 'TradePress_api_trading212_papermoney_apikey', '' ) );
			}
		}

		if ( '' === $api_key && isset( $api_settings['trading212_api_key'] ) ) {
			$api_key = trim( (string) $api_settings['trading212_api_key'] );
		}

		return $api_key;
	}

	/**
	 * Get platform information
	 *
	 * @param string $platform_id The platform ID
	 * @return array Platform information
	 * @version 1.0.0
	 */
	private function get_platform_info( $platform_id ) {
		// Default platform info
		$platform_info = array(
			'api_type'               => 'trading',
			'name'                   => ucfirst( $platform_id ),
			'supports_paper_trading' => true,
		);

		// Data-only APIs
		$data_only_apis = array( 'alphavantage', 'twelvedata', 'finnhub', 'iexcloud', 'marketstack', 'fmp' );
		if ( in_array( $platform_id, $data_only_apis ) ) {
			$platform_info['api_type']               = 'data_only';
			$platform_info['supports_paper_trading'] = false;
		}

		// Trading platforms may have specific settings
		switch ( $platform_id ) {
			case 'alphavantage':
				$platform_info['name'] = 'Alpha Vantage';
				break;
			case 'iexcloud':
				$platform_info['name'] = 'IEX Cloud';
				break;
		}

		// Try to get platform info from API Directory class if it exists
		if ( class_exists( 'TradePress_API_Directory' ) ) {
			$provider = TradePress_API_Directory::get_provider( $platform_id );
			if ( ! empty( $provider ) ) {
				$platform_info = wp_parse_args( $provider, $platform_info );
			}
		}

		return $platform_info;
	}

	/**
	 * Make an actual API request
	 *
	 * @param string $platform_id The platform ID
	 * @param array  $endpoint The endpoint details
	 * @param array  $credentials API credentials
	 * @return array|WP_Error API response or error
	 * @version 1.0.0
	 */
	private function make_api_request( $platform_id, $endpoint, $credentials ) {
		// Check if credentials are available
		if ( empty( $credentials['api_key'] ) ) {
			return new WP_Error(
				'missing_credentials',
				__( 'API credentials are not configured', 'tradepress' )
			);
		}

		$method = isset( $endpoint['method'] ) ? strtoupper( $endpoint['method'] ) : 'GET';

		if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) ) {
			return new WP_Error(
				'unsafe_endpoint_method',
				sprintf(
					/* translators: %s: HTTP method */
					__( 'Live endpoint test skipped because %s endpoints can modify broker data.', 'tradepress' ),
					$method
				),
				array(
					'status_code' => 'skipped',
					'method'      => $method,
					'endpoint'    => $endpoint,
				)
			);
		}

		// Build the API URL based on platform and endpoint
		$api_url = $this->build_api_url( $platform_id, $endpoint, $credentials );

		if ( empty( $api_url ) ) {
			return new WP_Error(
				'invalid_endpoint_url',
				__( 'Unable to build a valid URL for this endpoint.', 'tradepress' ),
				array(
					'status_code' => 'not_available',
					'endpoint'    => $endpoint,
				)
			);
		}

		// Make the API request
		$response = wp_remote_request(
			$api_url,
			array(
				'method'  => $method,
				'timeout' => 15,
				'headers' => $this->get_request_headers( $platform_id, $credentials ),
			)
		);

		// Handle API response
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		// Check for HTTP errors
		if ( $response_code >= 400 ) {
			return new WP_Error(
				'api_error',
				/* translators: %s: error message */
				sprintf( __( 'API error: %s', 'tradepress' ), $response_code ),
				array(
					'status_code' => $response_code,
					'url'         => $this->redact_url( $api_url ),
					'body'        => $response_body,
				)
			);
		}

		// Parse JSON response
		$data = json_decode( $response_body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error(
				'invalid_response',
				__( 'Invalid API response format', 'tradepress' ),
				array(
					'status_code' => $response_code,
					'url'         => $this->redact_url( $api_url ),
					'body'        => $response_body,
				)
			);
		}

		return array(
			'status_code'  => $response_code,
			'request_url'  => $this->redact_url( $api_url ),
			'method'       => $method,
			'endpoint'     => isset( $endpoint['path'] ) ? $endpoint['path'] : $endpoint['id'],
			'data'         => $data,
			'raw_response' => $data,
		);
	}

	/**
	 * Build API URL based on platform and endpoint
	 *
	 * @param string $platform_id The platform ID
	 * @param array  $endpoint The endpoint details
	 * @param array  $credentials API credentials
	 * @return string The API URL
	 * @version 1.0.0
	 */
	private function build_api_url( $platform_id, $endpoint, $credentials ) {
		$params = $this->get_test_parameters( $endpoint );

		// Base URLs for different platforms
		$base_urls = array(
			'alphavantage' => 'https://www.alphavantage.co/query',
			'alpaca'       => $credentials['mode'] === 'paper'
				? 'https://paper-api.alpaca.markets/v2'
				: 'https://api.alpaca.markets/v2',
			'twelvedata'   => 'https://api.twelvedata.com',
			'finnhub'      => 'https://finnhub.io/api/v1',
			'trading212'   => $credentials['mode'] === 'live'
				? 'https://live.trading212.com'
				: 'https://demo.trading212.com',
		);

		$base_url = isset( $endpoint['base_url'] ) && $endpoint['base_url'] ? $endpoint['base_url'] : ( isset( $base_urls[ $platform_id ] ) ? $base_urls[ $platform_id ] : '' );

		// For Alpha Vantage, we need to construct the URL with function and API key
		if ( $platform_id === 'alphavantage' ) {
			return add_query_arg(
				array_merge(
					array(
						'function' => isset( $endpoint['function'] ) ? $endpoint['function'] : $endpoint['id'],
						'apikey'   => $credentials['api_key'],
					),
					$params
				),
				$base_url
			);
		}

		// For other platforms, combine base URL with endpoint path
		$path = isset( $endpoint['path'] ) ? $endpoint['path'] : '';
		$path = $this->replace_path_parameters( $path, $params );
		$url  = trailingslashit( $base_url ) . ltrim( $path, '/' );

		if ( isset( $endpoint['method'] ) && strtoupper( $endpoint['method'] ) === 'GET' ) {
			$path_params = $this->get_path_parameter_names( isset( $endpoint['path'] ) ? $endpoint['path'] : '' );
			$query_args  = array();
			foreach ( $params as $key => $value ) {
				if ( ! in_array( $key, $path_params, true ) ) {
					$query_args[ $key ] = $value;
				}
			}
			if ( $query_args ) {
				$url = add_query_arg( $query_args, $url );
			}
		}

		return $url;
	}

	/**
	 * Build safe test parameters from endpoint metadata.
	 *
	 * @param array $endpoint Endpoint details.
	 * @return array
	 */
	private function get_test_parameters( $endpoint ) {
		$params     = array();
		$parameters = isset( $endpoint['parameters'] ) && is_array( $endpoint['parameters'] ) ? $endpoint['parameters'] : array();

		foreach ( $parameters as $name => $definition ) {
			if ( is_int( $name ) ) {
				$name       = $definition;
				$definition = array();
			}

			if ( is_array( $definition ) && array_key_exists( 'example', $definition ) ) {
				$params[ $name ] = $definition['example'];
			} elseif ( is_array( $definition ) && array_key_exists( 'default', $definition ) ) {
				$params[ $name ] = $definition['default'];
			} elseif ( in_array( $name, array( 'symbol', 'ticker', 'keywords' ), true ) ) {
				$params[ $name ] = $name === 'ticker' ? 'AAPL_US_EQ' : 'MSFT';
			} elseif ( $name === 'interval' ) {
				$params[ $name ] = '5min';
			} elseif ( in_array( $name, array( 'limit', 'pageSize' ), true ) ) {
				$params[ $name ] = 20;
			} elseif ( in_array( $name, array( 'id', 'reportId', 'orderId' ), true ) ) {
				$params[ $name ] = 1;
			}
		}

		return $params;
	}

	/**
	 * Replace path placeholders with test values.
	 *
	 * @param string $path Endpoint path.
	 * @param array  $params Test parameters.
	 * @return string
	 */
	private function replace_path_parameters( $path, $params ) {
		foreach ( $this->get_path_parameter_names( $path ) as $name ) {
			$value = isset( $params[ $name ] ) ? $params[ $name ] : 1;
			$path  = str_replace( '{' . $name . '}', rawurlencode( (string) $value ), $path );
		}

		return $path;
	}

	/**
	 * Get path parameter names from an endpoint path.
	 *
	 * @param string $path Endpoint path.
	 * @return array
	 */
	private function get_path_parameter_names( $path ) {
		if ( ! preg_match_all( '/\{([^}]+)\}/', $path, $matches ) ) {
			return array();
		}

		return $matches[1];
	}

	/**
	 * Get request headers for API call
	 *
	 * @param string $platform_id The platform ID
	 * @param array  $credentials API credentials
	 * @return array Request headers
	 * @version 1.0.0
	 */
	private function get_request_headers( $platform_id, $credentials ) {
		$headers = array();

		switch ( $platform_id ) {
			case 'alpaca':
				$headers['APCA-API-KEY-ID']     = $credentials['api_key'];
				$headers['APCA-API-SECRET-KEY'] = $credentials['api_secret'];
				break;

			case 'trading212':
				$headers['Authorization'] = $credentials['api_key'];
				break;

			case 'finnhub':
				$headers['X-Finnhub-Token'] = $credentials['api_key'];
				break;

			case 'iexcloud':
				// IEX Cloud uses token in URL, not headers
				break;
		}

		return $headers;
	}

	/**
	 * Redact secrets from URLs before storing test output.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	private function redact_url( $url ) {
		return preg_replace( '/([?&](?:apikey|api_key|token)=)[^&]+/i', '$1REDACTED', $url );
	}

	/**
	 * Generate a mock API response for testing
	 *
	 * @param array $endpoint Endpoint details
	 * @return array Mock response
	 * @version 1.0.0
	 */
	private function generate_mock_response( $endpoint ) {
		// Generate a mock response based on the endpoint
		$timestamp = date( 'Y-m-d\TH:i:s.000\Z' );

		switch ( $endpoint['platform'] ) {
			case 'alphavantage':
				return array(
					'Meta Data'          => array(
						'1. Information'    => 'Intraday (5min) open, high, low, close prices and volume',
						'2. Symbol'         => 'MSFT',
						'3. Last Refreshed' => $timestamp,
						'4. Interval'       => '5min',
						'5. Output Size'    => 'Compact',
						'6. Time Zone'      => 'US/Eastern',
					),
					'Time Series (5min)' => array(
						$timestamp => array(
							'1. open'   => '333.9999',
							'2. high'   => '321.9999',
							'3. low'    => '123.9999',
							'4. close'  => '666.9999',
							'5. volume' => '12345',
						),
					),
				);

			case 'alpaca':
				return array(
					'id'             => md5( time() ),
					'symbol'         => 'AAPL',
					'exchange'       => 'NASDAQ',
					'class'          => 'us_equity',
					'status'         => 'active',
					'tradable'       => true,
					'marginable'     => true,
					'shortable'      => true,
					'easy_to_borrow' => true,
				);

			default:
				// Generic response
				return array(
					'status'    => 'success',
					'timestamp' => time(),
					'data'      => array(
						'message' => 'Mock response for ' . $endpoint['id'],
					),
				);
		}
	}

	/**
	 * Generate an error report for a failed API test
	 *
	 * @param array    $endpoint Endpoint details
	 * @param WP_Error $error The error
	 * @param string   $platform_id The platform ID
	 * @return string Formatted error report
	 * @version 1.0.0
	 */
	private function generate_error_report( $endpoint, $error, $platform_id ) {
		// Format platform display name
		$platform_info = $this->get_platform_info( $platform_id );
		$platform_name = $platform_info['name'];

		// Get trading mode based on platform type
		$trading_mode = 'live';
		if ( $platform_info['api_type'] === 'trading' && $platform_info['supports_paper_trading'] ) {
			$trading_mode = get_option( "TradePress_api_{$platform_id}_trading_mode", 'paper' );
		} elseif ( $platform_info['api_type'] === 'data_only' ) {
			$trading_mode = 'paper'; // Default for display purposes
		}

		$environment = 'Live';
		$error_code  = $error->get_error_code();
		$status_text = in_array( $error_code, array( 'api_key_required', 'missing_credentials' ), true )
			? __( 'Requires API Key', 'tradepress' )
			: __( 'Connection Error', 'tradepress' );

		// Build the error report
		$report  = "### API Test Error Report ###\n";
		$report .= 'Platform: ' . esc_html( $platform_name ) . "\n";
		$report .= 'Endpoint: ' . esc_html( $endpoint['id'] ) . "\n";
		$report .= 'Status: ' . $status_text . "\n";
		$report .= 'Environment: ' . $environment . "\n";
		$report .= 'Trading Mode: ' . esc_html( $trading_mode ) . "\n";
		$report .= 'API Version: ' . ( isset( $endpoint['version'] ) ? $endpoint['version'] : 'v2' ) . "\n";
		$report .= 'Time: ' . current_time( 'd/m/Y, H:i:s' ) . "\n";
		$report .= 'Error: ' . $error->get_error_message() . ' [DEBUG: ' . date( 'Y-m-d\TH:i:s', time() ) . "+00:00]\n\n";

		// Add additional error details
		$report .= "Error Details: \n";
		if ( $error->get_error_data() ) {
			$error_data = $error->get_error_data();
			if ( is_string( $error_data ) ) {
				$raw_response = $error_data;
			} else {
				$raw_response = json_encode( $error_data, JSON_PRETTY_PRINT );
			}
		} else {
			$raw_response = '';
		}
		$report .= 'Raw Response Size: ' . strlen( $raw_response ) . " characters\n";
		$report .= '            ' . wp_html_excerpt( $raw_response, 1000, '...' ) . "\n";

		// Add diagnostic file and class information
		$report .= "\nDiagnostic Information for AI Analysis:\n";
		$report .= "Current Class: TradePress_Endpoint_Tester\n";
		$report .= "Current File: admin/page/tradingplatforms/endpoint-tester.php\n";

		// Add API configuration files
		$report .= "\nAPI Configuration Files:\n";
		if ( $platform_info['api_type'] === 'data_only' ) {
			$report .= "- admin/page/tradingplatforms/view/partials/config-data-only.php\n";
			$report .= "- includes/api/{$platform_id}/{$platform_id}-direct.php\n";
		} else {
			$report .= "- admin/page/tradingplatforms/view/template.api-tab.php\n";
			$report .= "- includes/api/{$platform_id}/{$platform_id}-api.php\n";
		}

		// Add settings information
		$report .= "\nRelated Settings Options:\n";
		if ( $platform_info['api_type'] === 'data_only' ) {
			$report .= "- TradePress_api_{$platform_id}_key: " . ( empty( get_option( "TradePress_api_{$platform_id}_key" ) ) ? 'Not configured' : 'Configured' ) . "\n";
		} elseif ( $trading_mode === 'paper' ) {
				$report .= "- TradePress_api_{$platform_id}_papermoney_apikey: " . ( empty( get_option( "TradePress_api_{$platform_id}_papermoney_apikey" ) ) ? 'Not configured' : 'Configured' ) . "\n";
				$report .= "- TradePress_api_{$platform_id}_papermoney_secretkey: " . ( empty( get_option( "TradePress_api_{$platform_id}_papermoney_secretkey" ) ) ? 'Not configured' : 'Configured' ) . "\n";
		} else {
			$report .= "- TradePress_api_{$platform_id}_realmoney_apikey: " . ( empty( get_option( "TradePress_api_{$platform_id}_realmoney_apikey" ) ) ? 'Not configured' : 'Configured' ) . "\n";
			$report .= "- TradePress_api_{$platform_id}_realmoney_secretkey: " . ( empty( get_option( "TradePress_api_{$platform_id}_realmoney_secretkey" ) ) ? 'Not configured' : 'Configured' ) . "\n";
		}

		// Get simplified stack trace (focused on plugin files)
		$stack_trace = $this->get_simplified_stack_trace();
		if ( ! empty( $stack_trace ) ) {
			$report .= "\nStack Trace (Plugin Files Only):\n";
			$report .= $stack_trace;
		}

		// Add API request parameters (if available)
		$request_params = $this->get_api_request_params( $endpoint, $platform_id );
		if ( ! empty( $request_params ) ) {
			$report .= "\nAPI Request Parameters:\n";
			$report .= $request_params;
		}

		// Add AI analysis suggestions
		$report .= "\nAI Debugging Suggestions:\n";

		// Different suggestions based on error types
		if ( strpos( $error->get_error_message(), 'credentials' ) !== false ) {
			$report .= "1. Examine the credential retrieval in get_api_credentials() method\n";
			$report .= "2. Check if the platform requires special credential handling\n";
			$report .= "3. Verify proper option names for storing {$platform_id} credentials\n";
			$report .= '4. Review platform_info API type detection: ' . $platform_info['api_type'] . "\n";
		} elseif ( strpos( $error->get_error_message(), 'connection' ) !== false ) {
			$report .= "1. Check build_api_url() for proper URL construction\n";
			$report .= "2. Review the get_request_headers() method for proper header setup\n";
			$report .= "3. Examine network connectivity issues or firewall settings\n";
		} else {
			$report .= "1. Check the test_endpoint() method implementation\n";
			$report .= "2. Review error handling in make_api_request()\n";
			$report .= "3. Examine the API response format requirements\n";
		}

		// Add common troubleshooting steps
		$report .= "\nTroubleshooting Steps:\n";
		switch ( $platform_id ) {
			case 'alphavantage':
				$report .= "1. Verify that your Alpha Vantage API key is configured properly\n";
				$report .= "2. Check if you've exceeded your API call limits (typically 5 calls per minute, 500 per day for free tier)\n";
				$report .= "3. Visit Data API settings to configure your Alpha Vantage API key\n";
				$report .= "4. For TIME_SERIES_INTRADAY, make sure required parameters (symbol, interval) are valid\n";
				break;
			case 'alpaca':
				$report .= "1. Check that both API key ID and secret key are correct\n";
				$report .= "2. Verify you're using the correct URL (paper/live trading)\n";
				$report .= "3. Ensure your account has the necessary permissions\n";
				$report .= "4. Try switching between paper and live trading modes\n";
				break;
			default:
				$report .= "1. Verify that your API credentials are configured correctly\n";
				$report .= "2. Check if you've exceeded your API rate limits\n";
				$report .= "3. Ensure the API service is operational\n";
				$report .= "4. Check network connectivity to the API service\n";
		}

		return $report;
	}

	/**
	 * Get a simplified stack trace focused on plugin files
	 *
	 * @return string Formatted stack trace
	 * @version 1.0.0
	 */
	private function get_simplified_stack_trace() {
		$trace        = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$plugin_trace = array();
		$plugin_path  = plugin_dir_path( dirname( dirname( __DIR__ ) ) );

		foreach ( $trace as $i => $call ) {
			if ( isset( $call['file'] ) && strpos( $call['file'], $plugin_path ) !== false ) {
				$rel_path       = str_replace( $plugin_path, '', $call['file'] );
				$plugin_trace[] = "#{$i}: {$rel_path}:{$call['line']} - " .
					( isset( $call['class'] ) ? $call['class'] . $call['type'] : '' ) .
					$call['function'] . '()';

				// Limit to 5 entries for readability
				if ( count( $plugin_trace ) >= 5 ) {
					break;
				}
			}
		}

		return ! empty( $plugin_trace ) ? implode( "\n", $plugin_trace ) : '';
	}

	/**
	 * Get API request parameters for diagnostics
	 *
	 * @param array  $endpoint The endpoint details
	 * @param string $platform_id The platform ID
	 * @return string Formatted request parameters
	 * @version 1.0.0
	 */
	private function get_api_request_params( $endpoint, $platform_id ) {
		$params = array();

		// Get credentials for current mode to build mock request
		$provider_info   = $this->get_platform_info( $platform_id );
		$api_credentials = $this->get_api_credentials( $platform_id, $provider_info['api_type'] );

		// For Alpha Vantage, show typical request parameters
		if ( $platform_id === 'alphavantage' ) {
			$params[] = 'function: ' . $endpoint['id'];
			$params[] = 'symbol: MSFT (default test symbol)';
			$params[] = 'interval: 5min (default test interval)';
			$params[] = 'apikey: ' . ( empty( $api_credentials['api_key'] ) ? 'NOT CONFIGURED' : '[API KEY CONFIGURED]' );
			$params[] = 'URL: ' . $this->build_api_url( $platform_id, $endpoint, $api_credentials );
		}
		// For Alpaca, show typical headers and endpoint
		elseif ( $platform_id === 'alpaca' ) {
			$params[] = 'Headers:';
			$params[] = '  APCA-API-KEY-ID: ' . ( empty( $api_credentials['api_key'] ) ? 'NOT CONFIGURED' : '[API KEY CONFIGURED]' );
			$params[] = '  APCA-API-SECRET-KEY: ' . ( empty( $api_credentials['api_secret'] ) ? 'NOT CONFIGURED' : '[API SECRET CONFIGURED]' );
			$params[] = 'URL: ' . $this->build_api_url( $platform_id, $endpoint, $api_credentials );
		}
		// Generic parameters
		else {
			$params[] = 'URL: ' . $this->build_api_url( $platform_id, $endpoint, $api_credentials );
			$params[] = 'API Key Status: ' . ( empty( $api_credentials['api_key'] ) ? 'NOT CONFIGURED' : 'CONFIGURED' );
		}

		return ! empty( $params ) ? implode( "\n", $params ) : '';
	}
}

// Initialize the endpoint tester
new TradePress_Endpoint_Tester();
