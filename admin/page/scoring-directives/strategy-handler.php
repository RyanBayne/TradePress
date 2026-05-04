<?php
/**
 * TradePress - Strategy Management Handler
 *
 * Handles AJAX requests and form submissions for strategy management
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles strategy management AJAX requests.
 */
class TradePress_Strategy_Handler {

	/**
	 * Initialize hooks
	 *
	 * @version 1.0.0
	 */
	public static function init() {
		add_action( 'wp_ajax_tradepress_create_strategy', array( __CLASS__, 'ajax_create_strategy' ) );
		add_action( 'wp_ajax_tradepress_update_strategy', array( __CLASS__, 'ajax_update_strategy' ) );
		add_action( 'wp_ajax_tradepress_delete_strategy', array( __CLASS__, 'ajax_delete_strategy' ) );
		add_action( 'wp_ajax_tradepress_test_strategy', array( __CLASS__, 'ajax_test_strategy' ) );
		add_action( 'wp_ajax_tradepress_get_strategies', array( __CLASS__, 'ajax_get_strategies' ) );
		add_action( 'wp_ajax_tradepress_duplicate_strategy', array( __CLASS__, 'ajax_duplicate_strategy' ) );
	}

	/**
	 * Create new strategy
	 *
	 * @version 1.0.0
	 */
	public static function ajax_create_strategy() {
		check_ajax_referer( 'tradepress_strategy_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'tradepress' ), 403 );
		}

		// Load database class.
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

		$name                = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description         = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$template            = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : '';
		$min_score_threshold = isset( $_POST['min_score_threshold'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['min_score_threshold'] ) ) : 50.0;
		$directives          = isset( $_POST['directives'] ) ? json_decode( wp_unslash( $_POST['directives'] ), true ) : array();

		if ( empty( $name ) ) {
			wp_send_json_error( __( 'Strategy name is required.', 'tradepress' ) );
		}

		if ( empty( $directives ) || ! is_array( $directives ) ) {
			wp_send_json_error( __( 'At least one directive is required.', 'tradepress' ) );
		}

		// Validate total weight.
		$total_weight = array_sum( array_column( $directives, 'weight' ) );
		if ( abs( 100 - $total_weight ) > 0.01 ) {
			wp_send_json_error( __( 'Total weight must be exactly 100%.', 'tradepress' ) );
		}

		// Determine category based on template.
		$category = 'custom';
		if ( ! empty( $template ) ) {
			$category = $template;
		}

		// Create strategy.
		$strategy_data = array(
			'name'                => $name,
			'description'         => $description,
			'category'            => $category,
			'template'            => $template,
			'status'              => 'draft',
			'total_weight'        => $total_weight,
			'min_score_threshold' => max( 0, min( 500, $min_score_threshold ) ),
			'creator_id'          => get_current_user_id(),
		);

		$strategy_id = TradePress_Scoring_Strategies_DB::create_strategy( $strategy_data );

		if ( is_wp_error( $strategy_id ) ) {
			wp_send_json_error( $strategy_id->get_error_message(), 500 );
		}

		// Add directives to strategy.
		foreach ( $directives as $directive ) {
			$directive_data = array(
				'directive_id'   => isset( $directive['id'] ) ? sanitize_text_field( $directive['id'] ) : '',
				'directive_name' => isset( $directive['name'] ) ? sanitize_text_field( $directive['name'] ) : '',
				'weight'         => isset( $directive['weight'] ) ? (float) $directive['weight'] : 0.0,
				'sort_order'     => isset( $directive['sort_order'] ) ? (int) $directive['sort_order'] : 0,
			);

			TradePress_Scoring_Strategies_DB::add_strategy_directive( $strategy_id, $directive_data );
		}

		wp_send_json_success(
			array(
				'strategy_id' => $strategy_id,
				'message'     => __( 'Strategy created successfully.', 'tradepress' ),
			)
		);
	}

	/**
	 * Update existing strategy name, description, status, minimum score threshold,
	 * and the weights of its currently assigned directives.
	 *
	 * Expects POST fields:
	 *   strategy_id         (int)
	 *   name                (string)
	 *   description         (string, optional)
	 *   status              (string: draft|active|inactive)
	 *   min_score_threshold (float)
	 *   directives          (JSON array: [{ id, weight }, ...])
	 *
	 * @version 1.0.0
	 */
	public static function ajax_update_strategy() {
		check_ajax_referer( 'tradepress_strategy_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'tradepress' ), 403 );
		}

		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

		$strategy_id         = isset( $_POST['strategy_id'] ) ? absint( wp_unslash( $_POST['strategy_id'] ) ) : 0;
		$name                = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description         = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$status              = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'draft';
		$min_score_threshold = isset( $_POST['min_score_threshold'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['min_score_threshold'] ) ) : 50.0;
		$directives          = isset( $_POST['directives'] ) ? json_decode( wp_unslash( $_POST['directives'] ), true ) : array();

		if ( ! $strategy_id ) {
			wp_send_json_error( __( 'Strategy ID is required.', 'tradepress' ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( __( 'Strategy name is required.', 'tradepress' ) );
		}

		$allowed_statuses = array( 'draft', 'active', 'inactive' );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = 'draft';
		}

		// Validate directive weights when provided.
		if ( ! empty( $directives ) && is_array( $directives ) ) {
			$total_weight = array_sum( array_column( $directives, 'weight' ) );
			if ( abs( 100 - $total_weight ) > 0.01 ) {
				wp_send_json_error( __( 'Total weight must be exactly 100%.', 'tradepress' ) );
			}
		}

		// Update the strategy record.
		$strategy_data = array(
			'name'                => $name,
			'description'         => $description,
			'status'              => $status,
			'min_score_threshold' => max( 0, min( 500, $min_score_threshold ) ),
		);

		$result = TradePress_Scoring_Strategies_DB::update_strategy( $strategy_id, $strategy_data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message(), 500 );
		}

		// Sync directive weights for existing assignments.
		if ( ! empty( $directives ) && is_array( $directives ) ) {
			$current_directives = TradePress_Scoring_Strategies_DB::get_strategy_directives( $strategy_id );

			// Build lookup: directive_id => strategy_directive row id.
			$directive_row_map = array();
			foreach ( $current_directives as $row ) {
				$directive_row_map[ $row->directive_id ] = $row->id;
			}

			foreach ( $directives as $directive ) {
				$directive_id = isset( $directive['id'] ) ? sanitize_text_field( $directive['id'] ) : '';
				$weight       = isset( $directive['weight'] ) ? (float) $directive['weight'] : 0.0;

				if ( isset( $directive_row_map[ $directive_id ] ) ) {
					TradePress_Scoring_Strategies_DB::update_directive_weight( $directive_row_map[ $directive_id ], $weight );
				}
			}
		}

		wp_send_json_success(
			array(
				'strategy_id' => $strategy_id,
				'message'     => __( 'Strategy updated successfully.', 'tradepress' ),
			)
		);
	}

	/**
	 * Test strategy with symbol
	 *
	 * @version 1.0.0
	 */
	public static function ajax_test_strategy() {
		check_ajax_referer( 'tradepress_strategy_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'tradepress' ), 403 );
		}

		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

		$strategy_id  = isset( $_POST['strategy_id'] ) ? absint( wp_unslash( $_POST['strategy_id'] ) ) : 0;
		$symbol       = isset( $_POST['symbol'] ) ? sanitize_text_field( wp_unslash( $_POST['symbol'] ) ) : '';
		$trading_mode = isset( $_POST['trading_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['trading_mode'] ) ) : '';

		if ( ! $strategy_id || ! $symbol ) {
			wp_send_json_error( __( 'Strategy ID and symbol are required.', 'tradepress' ) );
		}

		// Get strategy and directives.
		$strategy = TradePress_Scoring_Strategies_DB::get_strategy( $strategy_id );
		if ( ! $strategy ) {
			wp_send_json_error( __( 'Strategy not found.', 'tradepress' ) );
		}

		$directives = TradePress_Scoring_Strategies_DB::get_strategy_directives( $strategy_id );
		if ( empty( $directives ) ) {
			wp_send_json_error( __( 'No directives found for strategy.', 'tradepress' ) );
		}

		// Calculate strategy score.
		$test_result = self::calculate_strategy_score( $strategy, $directives, $symbol, $trading_mode );

		if ( is_wp_error( $test_result ) ) {
			wp_send_json_error( $test_result->get_error_message(), 500 );
		}

		// Save test result.
		$test_data = array(
			'strategy_id'       => $strategy_id,
			'test_type'         => 'single_symbol',
			'symbol'            => $symbol,
			'trading_mode'      => $trading_mode,
			'test_date'         => current_time( 'mysql' ),
			'total_score'       => $test_result['total_score'],
			'individual_scores' => wp_json_encode( $test_result['individual_scores'] ),
			'execution_time_ms' => $test_result['execution_time'],
			'test_status'       => 'completed',
		);

		TradePress_Scoring_Strategies_DB::save_test_result( $test_data );

		// Update strategy performance.
		TradePress_Scoring_Strategies_DB::update_performance_metrics( $strategy_id );

		wp_send_json_success( $test_result );
	}

	/**
	 * Get strategies list
	 *
	 * @version 1.0.0
	 */
	public static function ajax_get_strategies() {
		check_ajax_referer( 'tradepress_strategy_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'tradepress' ), 403 );
		}

		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

		$args = array(
			'status' => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'all',
			'limit'  => isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 50,
		);

		$strategies = TradePress_Scoring_Strategies_DB::get_strategies( $args );

		// Add directive counts.
		foreach ( $strategies as &$strategy ) {
			$directives                = TradePress_Scoring_Strategies_DB::get_strategy_directives( $strategy->id );
			$strategy->directive_count = count( $directives );
			$strategy->directives      = $directives;
		}

		wp_send_json_success( $strategies );
	}

	/**
	 * Delete strategy
	 *
	 * @version 1.0.0
	 */
	public static function ajax_delete_strategy() {
		check_ajax_referer( 'tradepress_strategy_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'tradepress' ), 403 );
		}

		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

		$strategy_id = isset( $_POST['strategy_id'] ) ? absint( wp_unslash( $_POST['strategy_id'] ) ) : 0;

		if ( ! $strategy_id ) {
			wp_send_json_error( __( 'Strategy ID is required.', 'tradepress' ) );
		}

		$result = TradePress_Scoring_Strategies_DB::delete_strategy( $strategy_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message(), 500 );
		}

		wp_send_json_success( __( 'Strategy deleted successfully.', 'tradepress' ) );
	}

	/**
	 * Duplicate strategy
	 *
	 * @version 1.0.0
	 */
	public static function ajax_duplicate_strategy() {
		check_ajax_referer( 'tradepress_strategy_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'tradepress' ), 403 );
		}

		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

		$strategy_id = isset( $_POST['strategy_id'] ) ? absint( wp_unslash( $_POST['strategy_id'] ) ) : 0;
		$strategy    = TradePress_Scoring_Strategies_DB::get_strategy( $strategy_id );

		if ( ! $strategy ) {
			wp_send_json_error( __( 'Strategy not found.', 'tradepress' ) );
		}

		// Create duplicate.
		$new_strategy_data = array(
			'name'                => $strategy->name . ' (Copy)',
			'description'         => $strategy->description,
			'category'            => $strategy->category,
			'status'              => 'draft',
			'risk_level'          => $strategy->risk_level,
			'time_horizon'        => $strategy->time_horizon,
			'total_weight'        => $strategy->total_weight,
			'min_score_threshold' => $strategy->min_score_threshold,
			'creator_id'          => get_current_user_id(),
		);

		$new_strategy_id = TradePress_Scoring_Strategies_DB::create_strategy( $new_strategy_data );

		if ( is_wp_error( $new_strategy_id ) ) {
			wp_send_json_error( $new_strategy_id->get_error_message(), 500 );
		}

		// Copy directives.
		$directives = TradePress_Scoring_Strategies_DB::get_strategy_directives( $strategy_id );
		foreach ( $directives as $directive ) {
			$directive_data = array(
				'directive_id'   => $directive->directive_id,
				'directive_name' => $directive->directive_name,
				'weight'         => $directive->weight,
				'sort_order'     => $directive->sort_order,
			);

			TradePress_Scoring_Strategies_DB::add_strategy_directive( $new_strategy_id, $directive_data );
		}

		wp_send_json_success(
			array(
				'strategy_id' => $new_strategy_id,
				'message'     => __( 'Strategy duplicated successfully.', 'tradepress' ),
			)
		);
	}

	/**
	 * Calculate strategy score for symbol
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $strategy Strategy row.
	 * @param mixed $directives Strategy directive rows.
	 * @param mixed $symbol Symbol to test.
	 * @param mixed $trading_mode Trading mode.
	 */
	private static function calculate_strategy_score( $strategy, $directives, $symbol, $trading_mode ) {
		$start_time = microtime( true );

		// Load directive handler.
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/scoring-directives/directive-handler.php';

		$individual_scores    = array();
		$total_weighted_score = 0;
		$total_weight         = 0;

		foreach ( $directives as $directive ) {
			// Test individual directive.
			$directive_result = TradePress_Directive_Handler::test_directive(
				$directive->directive_id,
				$symbol,
				$trading_mode
			);

			if ( $directive_result['success'] ) {
				$score          = $directive_result['test_data']['scores'][ $trading_mode ] ?? 0;
				$weighted_score = ( $score * $directive->weight ) / 100;

				$individual_scores[] = array(
					'directive_id'   => $directive->directive_id,
					'directive_name' => $directive->directive_name,
					'raw_score'      => $score,
					'weight'         => $directive->weight,
					'weighted_score' => $weighted_score,
				);

				$total_weighted_score += $weighted_score;
				$total_weight         += $directive->weight;
			} else {
				// Handle directive failure.
				$individual_scores[] = array(
					'directive_id'   => $directive->directive_id,
					'directive_name' => $directive->directive_name,
					'raw_score'      => 0,
					'weight'         => $directive->weight,
					'weighted_score' => 0,
					'error'          => $directive_result['message'],
				);
			}
		}

		// Calculate final score.
		$final_score = $total_weight > 0 ? ( $total_weighted_score / $total_weight ) * 100 : 0;

		$execution_time = ( microtime( true ) - $start_time ) * 1000; // Convert to milliseconds.

		return array(
			'total_score'       => round( $final_score, 2 ),
			'individual_scores' => $individual_scores,
			'execution_time'    => round( $execution_time, 2 ),
			'symbol'            => $symbol,
			'trading_mode'      => $trading_mode,
			'strategy_name'     => $strategy->name,
		);
	}
}

// Initialize the handler.
TradePress_Strategy_Handler::init();
