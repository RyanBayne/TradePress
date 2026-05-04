<?php
/**
 * TradePress - Admin Trading Area
 *
 * Handles the Trading tabs including Portfolio, Trade History, Manual Trading, SEES, and Calculators.
 * The Trading Strategy Creator tool allows users to define conditions for automated trading actions.
 *
 * Trading Strategy System:
 * - Uses directives to set specific trading requirements and conditions
 * - Implements a rule-based approach with strict conditions (not primarily score-based)
 * - Can utilize scoring data from the Scoring Directives system as one optional factor
 * - Supports conditional pairs (OR logic) where either condition can trigger an action
 * - May result in no trade action if strict conditions are not met (unlike scoring which always produces results)
 *
 * Boundary: scoring strategies calculate weighted scores, SEES ranks symbols
 * from those scores, and trading strategies own execution gates such as hard
 * thresholds, scope enforcement, open-position checks, and auto-trading stops.
 *
 * Database Dependencies:
 * - tradepress_trading_strategies: Stores strategy configurations and metadata
 * - tradepress_trading_rules: Stores individual rule definitions for strategies
 * - tradepress_trading_actions: Records actions taken by strategies
 * - tradepress_trading_executions: Logs execution history of strategies
 * - tradepress_directives: May reference directives when used as conditions
 *
 * Related Development Tasks:
 * - Implement conditional pairs functionality (see DEVELOPMENT-NOTES.md "Trading Strategy Pairs")
 * - Add strategy execution engine (see DEVELOPMENT-ROADMAP.md "Trading Automation")
 * - Create strategy performance tracking (see DEVELOPMENT-ROADMAP.md "Strategy Analytics")
 * - Build paper trading simulation for strategy testing
 *
 * @author   TradePress
 * @category Admin
 * @package  TradePress/Admin
 * @since    1.0.0
 * @version  1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/trading/trading-page.php';

// Action hooks for tab content.
// Existing tabs (assuming they will be created or have placeholders).
add_action( 'tradepress_trading_area_portfolio_tab_content', 'tradepress_display_portfolio_tab_content' );
add_action( 'tradepress_trading_area_trade-history_tab_content', 'tradepress_display_trade_history_tab_content' );
add_action( 'tradepress_trading_area_manual-trade_tab_content', 'tradepress_display_manual_trade_tab_content' );

// New SEES tabs.
add_action( 'tradepress_trading_area_sees-demo_tab_content', 'tradepress_display_sees_demo_tab_content' );
add_action( 'tradepress_trading_area_sees-diagnostics_tab_content', 'tradepress_display_sees_diagnostics_tab_content' );
add_action( 'tradepress_trading_area_sees-ready_tab_content', 'tradepress_display_sees_ready_tab_content' );
add_action( 'tradepress_trading_area_sees-pro_tab_content', 'tradepress_display_sees_pro_tab_content' );

// Calculators tab.
add_action( 'tradepress_trading_area_calculators_tab_content', 'tradepress_display_calculators_tab_content' );

// Trading Strategies tab (merged).
add_action( 'tradepress_trading_area_trading-strategies_tab_content', 'tradepress_display_trading_strategies_tab_content' );

// AJAX handler for SEES Demo data.
add_action( 'wp_ajax_tradepress_fetch_sees_demo_data', 'tradepress_ajax_fetch_sees_demo_data' );
add_action( 'wp_ajax_tradepress_fetch_sees_diagnostic_trace', 'tradepress_ajax_fetch_sees_diagnostic_trace' );
add_action( 'wp_ajax_tradepress_fetch_sees_strategy_options', 'tradepress_ajax_fetch_sees_strategy_options' );

if ( ! function_exists( 'tradepress_ajax_fetch_sees_demo_data' ) ) {
	/**
	 * AJAX handler to fetch SEES demo data.
	 *
	 * @version 1.0.0
	 */
	function tradepress_ajax_fetch_sees_demo_data() {
		check_ajax_referer( 'tradepress_fetch_sees_demo_data_nonce', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to access this data.', 'tradepress' ), 403 );
		}

		if ( ! function_exists( 'tradepress_can_access_development_views' ) || ! tradepress_can_access_development_views() ) {
			wp_send_json_error( __( 'SEES Demo data is available only when Developer Mode is enabled.', 'tradepress' ), 403 );
		}

		$trace_mode   = isset( $_POST['trace_mode'] ) ? tradepress_normalize_sees_trace_mode( wp_unslash( $_POST['trace_mode'] ) ) : 'scoring';
		$strategy_id  = isset( $_POST['strategy_id'] ) ? absint( wp_unslash( $_POST['strategy_id'] ) ) : 0;
		$strategy_set = false;

		if ( $strategy_id > 0 ) {
			if ( ! class_exists( 'TradePress_Scoring_Strategies_DB' ) ) {
				$db_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';
				if ( file_exists( $db_file ) ) {
					require_once $db_file;
				}
			}

			$strategy_set = class_exists( 'TradePress_Scoring_Strategies_DB' );
		}

		$symbols_data_file = TRADEPRESS_PLUGIN_DIR . 'includes/data/symbols-data.php';
		if ( ! file_exists( $symbols_data_file ) ) {
			wp_send_json_error( __( 'Symbols data file not found.', 'tradepress' ), 500 );
		}

		require_once $symbols_data_file;

		if ( ! function_exists( 'tradepress_get_test_stock_symbols' ) || ! function_exists( 'tradepress_get_test_company_details' ) ) {
			wp_send_json_error( __( 'Required data functions are missing.', 'tradepress' ), 500 );
		}

		$symbols_data         = tradepress_get_test_stock_symbols();
		$company_details_all  = tradepress_get_test_company_details();
		$sees_data            = array();
		$flattened_symbols    = array();
		$demo_symbols_limit   = 25;
		$generated_rows_count = 0;
		$strategy_directives  = array();

		if ( $strategy_set ) {
			$strategy_directives = TradePress_Scoring_Strategies_DB::get_strategy_directives( $strategy_id );
		}

		foreach ( $symbols_data as $category => $symbol_list ) {
			if ( 'global_markets' === $category ) {
				foreach ( $symbol_list as $symbol_data ) {
					if ( isset( $symbol_data['symbol'] ) && is_string( $symbol_data['symbol'] ) ) {
						$flattened_symbols[] = $symbol_data['symbol'];
					}
				}
			} else {
				$flattened_symbols = array_merge( $flattened_symbols, $symbol_list );
			}
		}

		foreach ( $flattened_symbols as $symbol ) {
			if ( $generated_rows_count >= $demo_symbols_limit ) {
				break;
			}

			if ( ! is_string( $symbol ) || '' === $symbol ) {
				continue;
			}

			$details = isset( $company_details_all[ $symbol ] ) ? $company_details_all[ $symbol ] : array();

			if ( empty( $details['name'] ) ) {
				continue;
			}

			$industry = isset( $details['industry'] ) ? strtolower( (string) $details['industry'] ) : '';
			if ( in_array( $industry, array( 'etf', 'etp' ), true ) ) {
				continue;
			}

			$score = wp_rand( 30, 95 );
			if ( ! empty( $strategy_directives ) ) {
				$steps = tradepress_build_sees_diagnostic_steps( $strategy_directives, $symbol, $trace_mode );
				$score = 0;
				foreach ( $steps as $step ) {
					$score += isset( $step['weighted_score'] ) ? (float) $step['weighted_score'] : 0;
				}
				$score = round( $score, 2 );
			}

			$price          = isset( $details['avg_volume'] ) ? (float) ( ( (float) $details['avg_volume'] / 1000000 ) + wp_rand( 50, 250 ) ) : (float) wp_rand( 10, 500 ) * ( wp_rand( 80, 120 ) / 100 );
			$change_percent = wp_rand( -1000, 1000 ) / 100;

			$sees_data[] = array(
				'symbol'         => $symbol,
				'name'           => isset( $details['name'] ) ? $details['name'] : 'N/A',
				'industry'       => isset( $details['industry'] ) ? $details['industry'] : 'N/A',
				'score'          => $score,
				'price'          => number_format( $price, 2, '.', '' ),
				'change_percent' => number_format( $change_percent, 2, '.', '' ),
			);

			++$generated_rows_count;
		}

		wp_send_json_success( $sees_data, 200 );
	}
}

if ( ! function_exists( 'tradepress_normalize_sees_trace_mode' ) ) {
	/**
	 * Normalize requested trace mode.
	 *
	 * @param string $trace_mode Raw trace mode.
	 * @return string
	 */
	function tradepress_normalize_sees_trace_mode( $trace_mode ) {
		$normalized = sanitize_key( (string) $trace_mode );
		return in_array( $normalized, array( 'scoring', 'trading' ), true ) ? $normalized : 'scoring';
	}
}

if ( ! function_exists( 'tradepress_verify_sees_diagnostics_ajax_nonce' ) ) {
	/**
	 * Verify the SEES Diagnostics nonce and return JSON on failure.
	 *
	 * @return void
	 */
	function tradepress_verify_sees_diagnostics_ajax_nonce() {
		if ( ! check_ajax_referer( 'tradepress_fetch_sees_diagnostic_trace_nonce', '_ajax_nonce', false ) ) {
			wp_send_json_error( __( 'Invalid or expired SEES diagnostics request.', 'tradepress' ), 403 );
		}
	}
}

if ( ! function_exists( 'tradepress_get_sees_strategy_options' ) ) {
	/**
	 * Get strategy options for diagnostics by mode.
	 *
	 * @param string $trace_mode Trace mode.
	 * @return array
	 */
	function tradepress_get_sees_strategy_options( $trace_mode ) {
		$trace_mode = tradepress_normalize_sees_trace_mode( $trace_mode );

		if ( ! class_exists( 'TradePress_Scoring_Strategies_DB' ) ) {
			$db_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';
			if ( file_exists( $db_file ) ) {
				require_once $db_file;
			}
		}

		if ( ! class_exists( 'TradePress_Scoring_Strategies_DB' ) ) {
			return array();
		}

		$strategies = TradePress_Scoring_Strategies_DB::get_strategies(
			array(
				'status' => 'all',
				'limit'  => 100,
			)
		);

		$options = array();
		foreach ( $strategies as $strategy ) {
			$type = isset( $strategy->type ) ? sanitize_key( (string) $strategy->type ) : 'scoring';

			if ( 'scoring' === $trace_mode && ! in_array( $type, array( 'scoring', 'hybrid' ), true ) ) {
				continue;
			}

			if ( 'trading' === $trace_mode && ! in_array( $type, array( 'trading', 'hybrid' ), true ) ) {
				continue;
			}

			$component_count = isset( $strategy->id ) ? count( TradePress_Scoring_Strategies_DB::get_strategy_directives( (int) $strategy->id, true ) ) : 0;

			$options[] = array(
				'id'                  => isset( $strategy->id ) ? (int) $strategy->id : 0,
				'name'                => isset( $strategy->name ) ? (string) $strategy->name : '',
				'type'                => $type,
				'min_score_threshold' => isset( $strategy->min_score_threshold ) ? (float) $strategy->min_score_threshold : 50.0,
				'total_weight'        => isset( $strategy->total_weight ) ? (float) $strategy->total_weight : 100.0,
				'component_count'     => $component_count,
				'storage_source'      => 'tradepress_scoring_strategies',
			);
		}

		if ( 'trading' === $trace_mode && empty( $options ) ) {
			foreach ( $strategies as $strategy ) {
				$component_count = isset( $strategy->id ) ? count( TradePress_Scoring_Strategies_DB::get_strategy_directives( (int) $strategy->id, true ) ) : 0;

				$options[] = array(
					'id'                  => isset( $strategy->id ) ? (int) $strategy->id : 0,
					'name'                => isset( $strategy->name ) ? (string) $strategy->name . ' (transitional)' : '',
					'type'                => isset( $strategy->type ) ? sanitize_key( (string) $strategy->type ) : 'scoring',
					'min_score_threshold' => isset( $strategy->min_score_threshold ) ? (float) $strategy->min_score_threshold : 50.0,
					'total_weight'        => isset( $strategy->total_weight ) ? (float) $strategy->total_weight : 100.0,
					'component_count'     => $component_count,
					'storage_source'      => 'tradepress_scoring_strategies_transitional',
				);
			}
		}

		return array_values( array_filter( $options ) );
	}
}

if ( ! function_exists( 'tradepress_ajax_fetch_sees_strategy_options' ) ) {
	/**
	 * AJAX handler to fetch strategy options for SEES diagnostics.
	 */
	function tradepress_ajax_fetch_sees_strategy_options() {
		tradepress_verify_sees_diagnostics_ajax_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to access this data.', 'tradepress' ), 403 );
		}

		if ( ! function_exists( 'tradepress_can_access_development_views' ) || ! tradepress_can_access_development_views() ) {
			wp_send_json_error( __( 'SEES diagnostics are available only when Developer Mode is enabled.', 'tradepress' ), 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by tradepress_verify_sees_diagnostics_ajax_nonce().
		$trace_mode = isset( $_POST['trace_mode'] ) ? tradepress_normalize_sees_trace_mode( wp_unslash( $_POST['trace_mode'] ) ) : 'scoring';
		$options    = tradepress_get_sees_strategy_options( $trace_mode );

		wp_send_json_success( $options, 200 );
	}
}

if ( ! function_exists( 'tradepress_build_sees_diagnostic_steps' ) ) {
	/**
	 * Build deterministic diagnostic steps from stored strategy directives.
	 *
	 * @param array  $strategy_directives Strategy directive rows.
	 * @param string $symbol Symbol being evaluated.
	 * @param string $trace_mode Trace mode.
	 * @return array
	 */
	function tradepress_build_sees_diagnostic_steps( $strategy_directives, $symbol, $trace_mode ) {
		$steps = array();

		$loader_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-loader.php';
		if ( ! function_exists( 'tradepress_get_directive_by_id' ) && file_exists( $loader_file ) ) {
			require_once $loader_file;
		}

		$directive_count = max( 1, count( $strategy_directives ) );

		foreach ( $strategy_directives as $index => $directive_row ) {
			$directive_id   = isset( $directive_row->directive_id ) ? (string) $directive_row->directive_id : '';
			$directive_name = isset( $directive_row->directive_name ) ? (string) $directive_row->directive_name : '';
			$directive_def  = function_exists( 'tradepress_get_directive_by_id' ) ? tradepress_get_directive_by_id( $directive_id ) : null;
			$indicator_key  = is_array( $directive_def ) && ! empty( $directive_def['technical_indicator'] ) ? sanitize_key( $directive_def['technical_indicator'] ) : '';
			$db_is_active   = ! isset( $directive_row->is_active ) || 1 === (int) $directive_row->is_active;
			$def_is_active  = ! is_array( $directive_def ) || ! isset( $directive_def['active'] ) || (bool) $directive_def['active'];
			$has_definition = is_array( $directive_def );
			$component_ok   = $db_is_active && $def_is_active && $has_definition;
			$warning        = '';

			if ( ! $has_definition ) {
				$warning = 'Directive definition missing from registry/configured directives.';
			} elseif ( ! $db_is_active ) {
				$warning = 'Strategy component is disabled in strategy storage.';
			} elseif ( ! $def_is_active ) {
				$warning = 'Directive is currently inactive in configuration.';
			}

			$label = $directive_name;
			if ( '' === $label && is_array( $directive_def ) && ! empty( $directive_def['name'] ) ) {
				$label = (string) $directive_def['name'];
			}
			if ( '' === $label ) {
				$label = ucwords( str_replace( '_', ' ', $directive_id ) );
			}

			$description = '';
			if ( is_array( $directive_def ) && ! empty( $directive_def['description'] ) ) {
				$description = (string) $directive_def['description'];
			}

			$weight_percent = isset( $directive_row->weight ) ? (float) $directive_row->weight : ( 100 / $directive_count );
			$weight         = max( 0.0, min( 1.0, $weight_percent / 100 ) );

			$seed_value   = abs( crc32( $symbol . '|' . $directive_id . '|' . $trace_mode ) );
			$raw_input    = round( ( $seed_value % 10000 ) / 100, 2 );
			$score        = round( max( 0.0, min( 100.0, 25 + ( ( $seed_value % 7600 ) / 100 ) ) ), 2 );
			$threshold    = 'trading' === $trace_mode ? 60.0 : 50.0;
			$passed       = $component_ok && $score >= $threshold;
			$weighted     = $component_ok ? round( $score * $weight, 2 ) : 0.0;
			$max_weighted = round( 100 * $weight, 2 );
			$formula_text = $component_ok
				? round( $weighted, 2 ) . ' = ' . round( $score, 2 ) . ' * ' . round( $weight, 4 )
				: '0 = blocked component';
			$entity_label = 'trading' === $trace_mode ? 'indicator' : 'directive';
			$code_path    = 'trading' === $trace_mode && '' !== $indicator_key
				? $indicator_key . ' -> evaluate_indicator_gate'
				: 'tradepress_get_directive_by_id() -> evaluate_' . $entity_label;

			$steps[] = array(
				'id'                  => $directive_id,
				'key'                 => $directive_id,
				'label'               => $label,
				'description'         => $description,
				'component_type'      => $entity_label,
				'component_source'    => 'tradepress_strategy_directives',
				'component_available' => $has_definition,
				'component_active'    => $db_is_active && $def_is_active,
				'warning'             => $warning,
				'input_value'         => $raw_input,
				'score'               => $score,
				'weight'              => $weight,
				'weighted_score'      => $weighted,
				'max_weighted_score'  => $max_weighted,
				'formula_text'        => $formula_text,
				'threshold'           => $threshold,
				'passed'              => $passed,
				'code_path'           => $code_path,
				'next_action'         => $component_ok
					? ( $passed ? 'Proceed to next ' . $entity_label : 'Keep evaluating; may block trigger at strategy gate' )
					: 'Blocked until component is available and active',
			);
		}

		return $steps;
	}
}

if ( ! function_exists( 'tradepress_ajax_fetch_sees_diagnostic_trace' ) ) {
	/**
	 * AJAX handler to fetch SEES diagnostic trace for a single symbol.
	 *
	 * @version 1.1.0
	 */
	function tradepress_ajax_fetch_sees_diagnostic_trace() {
		tradepress_verify_sees_diagnostics_ajax_nonce();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to access this data.', 'tradepress' ), 403 );
		}

		if ( ! function_exists( 'tradepress_can_access_development_views' ) || ! tradepress_can_access_development_views() ) {
			wp_send_json_error( __( 'SEES diagnostics are available only when Developer Mode is enabled.', 'tradepress' ), 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by tradepress_verify_sees_diagnostics_ajax_nonce().
		$raw_symbol = isset( $_POST['symbol'] ) ? sanitize_text_field( wp_unslash( $_POST['symbol'] ) ) : '';
		$symbol     = strtoupper( $raw_symbol );

		if ( '' === $symbol ) {
			wp_send_json_error( __( 'A symbol is required.', 'tradepress' ), 400 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by tradepress_verify_sees_diagnostics_ajax_nonce().
		$trace_mode = isset( $_POST['trace_mode'] ) ? tradepress_normalize_sees_trace_mode( wp_unslash( $_POST['trace_mode'] ) ) : 'scoring';
		$options    = tradepress_get_sees_strategy_options( $trace_mode );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by tradepress_verify_sees_diagnostics_ajax_nonce().
		$raw_id = isset( $_POST['strategy_id'] ) ? absint( wp_unslash( $_POST['strategy_id'] ) ) : 0;

		if ( 0 === $raw_id && ! empty( $options ) ) {
			$raw_id = (int) $options[0]['id'];
		}

		$symbols_data_file = TRADEPRESS_PLUGIN_DIR . 'includes/data/symbols-data.php';
		if ( ! file_exists( $symbols_data_file ) ) {
			wp_send_json_error( __( 'Symbols data file not found.', 'tradepress' ), 500 );
		}

		require_once $symbols_data_file;

		if ( ! function_exists( 'tradepress_get_test_company_details' ) ) {
			wp_send_json_error( __( 'Required data functions are missing.', 'tradepress' ), 500 );
		}

		$company_details_all = tradepress_get_test_company_details();
		$details             = isset( $company_details_all[ $symbol ] ) ? $company_details_all[ $symbol ] : array();
		$name                = isset( $details['name'] ) ? (string) $details['name'] : $symbol;
		$industry            = isset( $details['industry'] ) ? (string) $details['industry'] : 'N/A';

		if ( ! class_exists( 'TradePress_Scoring_Strategies_DB' ) ) {
			$db_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';
			if ( file_exists( $db_file ) ) {
				require_once $db_file;
			}
		}

		if ( ! class_exists( 'TradePress_Scoring_Strategies_DB' ) ) {
			wp_send_json_error( __( 'Strategy storage is unavailable.', 'tradepress' ), 500 );
		}

		if ( ! class_exists( 'TradePress_Strategy_Scope_Service' ) ) {
			$scope_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/strategy-scope-service.php';
			if ( file_exists( $scope_file ) ) {
				require_once $scope_file;
			}
		}

		$process = array(
			array(
				'label'     => 'Resolve trace mode',
				'code_path' => 'tradepress_normalize_sees_trace_mode()',
				'passed'    => true,
			),
			array(
				'label'     => 'Load strategy options for selected mode',
				'code_path' => 'tradepress_get_sees_strategy_options()',
				'passed'    => ! empty( $options ),
			),
		);

		$strategy  = $raw_id > 0 ? TradePress_Scoring_Strategies_DB::get_strategy( $raw_id ) : null;
		$process[] = array(
			'label'     => 'Load selected strategy',
			'code_path' => 'TradePress_Scoring_Strategies_DB::get_strategy()',
			'passed'    => (bool) $strategy,
		);

		if ( ! $strategy ) {
			$decision_branch_details = array(
				array(
					'gate'      => 'strategy-selection',
					'status'    => 'failed',
					'reason'    => 'No strategy selected for trace mode.',
					'code_path' => 'TradePress_Scoring_Strategies_DB::get_strategy()',
				),
			);

			wp_send_json_success(
				array(
					'symbol'                  => $symbol,
					'name'                    => $name,
					'industry'                => $industry,
					'score'                   => 0,
					'decision'                => 'Stopped: Strategy not selected',
					'decision_state'          => 'stopped',
					'trace_mode'              => $trace_mode,
					'strategy_id'             => 0,
					'strategy_name'           => '',
					'strategy_type'           => '',
					'strategy_status'         => '',
					'strategy_storage'        => 'tradepress_scoring_strategies',
					'strategy_scope'          => null,
					'scope_validation'        => null,
					'component_count'         => 0,
					'passed_count'            => 0,
					'component_warning_count' => 0,
					'minimum_threshold'       => 0,
					'max_possible_score'      => 0,
					'score_percent_of_max'    => 0,
					'threshold_distance'      => 0,
					'distance_to_threshold'   => 0,
					'decision_branch_details' => $decision_branch_details,
					'next_function'           => 'Select a strategy with active components',
					'generatedAt'             => current_time( 'mysql' ),
					'process'                 => $process,
					'steps'                   => array(),
				),
				200
			);
		}

		$scope_validation = class_exists( 'TradePress_Strategy_Scope_Service' )
			? TradePress_Strategy_Scope_Service::validate_symbol( (int) $strategy->id, $symbol, $trace_mode )
			: array(
				'allowed'          => true,
				'status'           => 'scope_service_unavailable',
				'symbol'           => $symbol,
				'scope'            => null,
				'resolved_symbols' => array(),
				'messages'         => array( __( 'Strategy scope service is unavailable.', 'tradepress' ) ),
			);

		$process[] = array(
			'label'     => 'Validate strategy symbol scope',
			'code_path' => 'TradePress_Strategy_Scope_Service::validate_symbol()',
			'passed'    => ! empty( $scope_validation['allowed'] ),
		);

		if ( 'trading' === $trace_mode && empty( $scope_validation['allowed'] ) ) {
			$decision_branch_details = array(
				array(
					'gate'      => 'symbol-scope',
					'status'    => 'failed',
					'reason'    => ! empty( $scope_validation['messages'] ) ? implode( ' ', $scope_validation['messages'] ) : 'Symbol is outside the enforced trading scope.',
					'code_path' => 'TradePress_Strategy_Scope_Service::validate_symbol()',
				),
			);

			wp_send_json_success(
				array(
					'symbol'                  => $symbol,
					'name'                    => $name,
					'industry'                => $industry,
					'score'                   => 0,
					'decision'                => 'Stopped: symbol outside enforced trading scope',
					'decision_state'          => 'stopped',
					'trace_mode'              => $trace_mode,
					'strategy_id'             => isset( $strategy->id ) ? (int) $strategy->id : 0,
					'strategy_name'           => isset( $strategy->name ) ? (string) $strategy->name : '',
					'strategy_type'           => isset( $strategy->type ) ? (string) $strategy->type : 'scoring',
					'strategy_status'         => isset( $strategy->status ) ? (string) $strategy->status : '',
					'strategy_storage'        => 'tradepress_scoring_strategies',
					'strategy_scope'          => isset( $scope_validation['scope'] ) ? $scope_validation['scope'] : null,
					'scope_validation'        => $scope_validation,
					'component_count'         => 0,
					'passed_count'            => 0,
					'component_warning_count' => 0,
					'minimum_threshold'       => isset( $strategy->min_score_threshold ) ? (float) $strategy->min_score_threshold : 50.0,
					'max_possible_score'      => 0,
					'score_percent_of_max'    => 0,
					'threshold_distance'      => 0,
					'distance_to_threshold'   => 0,
					'decision_branch_details' => $decision_branch_details,
					'next_function'           => 'Return stop decision to diagnostics panel',
					'generatedAt'             => current_time( 'mysql' ),
					'process'                 => $process,
					'steps'                   => array(),
				),
				200
			);
		}

		$strategy_directives = TradePress_Scoring_Strategies_DB::get_strategy_directives( (int) $strategy->id, true );
		$process[]           = array(
			'label'     => 'Load strategy components',
			'code_path' => 'TradePress_Scoring_Strategies_DB::get_strategy_directives()',
			'passed'    => ! empty( $strategy_directives ),
		);

		$steps     = tradepress_build_sees_diagnostic_steps( $strategy_directives, $symbol, $trace_mode );
		$process[] = array(
			'label'     => 'Evaluate strategy pipeline',
			'code_path' => 'tradepress_build_sees_diagnostic_steps()',
			'passed'    => ! empty( $steps ),
		);

		$total_score             = 0.0;
		$total_max_score         = 0.0;
		$passed_count            = 0;
		$component_warning_count = 0;
		$steps_count             = count( $steps );
		$min_threshold           = isset( $strategy->min_score_threshold ) ? (float) $strategy->min_score_threshold : 50.0;
		$decision_branch_details = array();

		foreach ( $steps as $step ) {
			$total_score     += isset( $step['weighted_score'] ) ? (float) $step['weighted_score'] : 0.0;
			$total_max_score += isset( $step['max_weighted_score'] ) ? (float) $step['max_weighted_score'] : 0.0;
			if ( ! empty( $step['passed'] ) ) {
				++$passed_count;
			}
			if ( ! empty( $step['warning'] ) ) {
				++$component_warning_count;
			}
		}

		$total_score           = round( $total_score, 2 );
		$total_max_score       = round( $total_max_score, 2 );
		$decision              = 'Stopped: No components to evaluate';
		$score_percent_of_max  = $total_max_score > 0 ? round( ( $total_score / $total_max_score ) * 100, 2 ) : 0.0;
		$distance_to_threshold = round( $total_score - $min_threshold, 2 );

		if ( $component_warning_count > 0 ) {
			$decision_branch_details[] = array(
				'gate'      => 'component-health',
				'status'    => 'warning',
				'reason'    => sprintf( '%d strategy component(s) are missing or inactive.', $component_warning_count ),
				'code_path' => 'tradepress_build_sees_diagnostic_steps()',
			);
		}

		if ( isset( $scope_validation['status'] ) && in_array( $scope_validation['status'], array( 'out_of_scope_advisory', 'out_of_scope_enforcement_recommended' ), true ) ) {
			$decision_branch_details[] = array(
				'gate'      => 'symbol-scope',
				'status'    => 'warning',
				'reason'    => ! empty( $scope_validation['messages'] ) ? implode( ' ', $scope_validation['messages'] ) : 'Symbol is outside the intended strategy scope.',
				'code_path' => 'TradePress_Strategy_Scope_Service::validate_symbol()',
			);
		}

		if ( 'scoring' === $trace_mode ) {
			if ( $total_score >= ( $min_threshold + 15 ) ) {
				$decision                  = 'High score: continue to next decision function';
				$decision_branch_details[] = array(
					'gate'      => 'score-threshold',
					'status'    => 'passed',
					'reason'    => 'Score is comfortably above minimum threshold.',
					'code_path' => 'scoring strategy decision branch',
				);
			} elseif ( $total_score >= $min_threshold ) {
				$decision                  = 'Low qualified score: continue with caution';
				$decision_branch_details[] = array(
					'gate'      => 'score-threshold',
					'status'    => 'passed',
					'reason'    => 'Score met minimum threshold but did not exceed high-confidence buffer.',
					'code_path' => 'scoring strategy decision branch',
				);
			} else {
				$decision                  = 'Below suggested trading threshold: continue ranking';
				$decision_branch_details[] = array(
					'gate'      => 'score-threshold',
					'status'    => 'warning',
					'reason'    => 'Score is below the scoring strategy suggested trading threshold; scoring and SEES ranking continue.',
					'code_path' => 'scoring strategy advisory branch',
				);
			}
		} elseif ( $steps_count > 0 ) {
			$required_pass_count = max( 1, (int) ceil( $steps_count * 0.60 ) );
			if ( $passed_count >= $required_pass_count ) {
				$decision                  = 'Rule threshold met: continue to trigger function';
				$decision_branch_details[] = array(
					'gate'      => 'indicator-threshold',
					'status'    => 'passed',
					'reason'    => sprintf( 'Passed %d/%d components (required: %d).', $passed_count, $steps_count, $required_pass_count ),
					'code_path' => 'trading strategy threshold branch',
				);
			} else {
				$decision                  = 'Stopped: required indicator threshold not met';
				$decision_branch_details[] = array(
					'gate'      => 'indicator-threshold',
					'status'    => 'failed',
					'reason'    => sprintf( 'Passed %d/%d components (required: %d).', $passed_count, $steps_count, $required_pass_count ),
					'code_path' => 'trading strategy threshold branch',
				);
			}
		}

		$process[] = array(
			'label'     => 'Apply decision gate',
			'code_path' => 'scoring/trading strategy decision branch',
			'passed'    => ( false === strpos( $decision, 'Stopped:' ) ),
		);

		$decision_state = false === strpos( $decision, 'Stopped:' ) ? 'continued' : 'stopped';
		$next_function  = 'continued' === $decision_state
			? ( 'trading' === $trace_mode ? 'tradepress_execute_trading_strategy_gate()' : 'tradepress_rank_scoring_strategy_result()' )
			: 'Return stop decision to diagnostics panel';

		wp_send_json_success(
			array(
				'symbol'                  => $symbol,
				'name'                    => $name,
				'industry'                => $industry,
				'score'                   => $total_score,
				'decision'                => $decision,
				'decision_state'          => $decision_state,
				'trace_mode'              => $trace_mode,
				'strategy_id'             => isset( $strategy->id ) ? (int) $strategy->id : 0,
				'strategy_name'           => isset( $strategy->name ) ? (string) $strategy->name : '',
				'strategy_type'           => isset( $strategy->type ) ? (string) $strategy->type : 'scoring',
				'strategy_status'         => isset( $strategy->status ) ? (string) $strategy->status : '',
				'strategy_storage'        => 'tradepress_scoring_strategies',
				'strategy_scope'          => isset( $scope_validation['scope'] ) ? $scope_validation['scope'] : null,
				'scope_validation'        => $scope_validation,
				'component_count'         => $steps_count,
				'passed_count'            => $passed_count,
				'component_warning_count' => $component_warning_count,
				'minimum_threshold'       => $min_threshold,
				'max_possible_score'      => $total_max_score,
				'score_percent_of_max'    => $score_percent_of_max,
				'threshold_distance'      => $distance_to_threshold,
				'distance_to_threshold'   => $distance_to_threshold,
				'decision_branch_details' => $decision_branch_details,
				'next_function'           => $next_function,
				'generatedAt'             => current_time( 'mysql' ),
				'process'                 => $process,
				'steps'                   => $steps,
			),
			200
		);
	}
}

// Placeholder functions for existing tabs (if not already defined elsewhere).
/**
 * Display portfolio tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_portfolio_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/portfolio.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'Portfolio tab content view file not found.', 'tradepress' ) . '</p>';
	}
}

/**
 * Display trade history tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_trade_history_tab_content() {
	// Example: include 'views/trade-history.php'.
	echo '<p>' . esc_html__( 'Trade History tab content to be added.', 'tradepress' ) . '</p>';
}

/**
 * Display manual trade tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_manual_trade_tab_content() {
	// Example: include 'views/manual-trade.php'.
	echo '<p>' . esc_html__( 'Manual Trading tab content to be added.', 'tradepress' ) . '</p>';
}

// Functions to display content for the new SEES tabs.
/**
 * Display sees demo tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_sees_demo_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/sees-demo.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'SEES Demo tab content view file not found.', 'tradepress' ) . '</p>';
	}
}

/**
 * Display sees diagnostics tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_sees_diagnostics_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/sees-diagnostics.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'SEES Diagnostics tab content view file not found.', 'tradepress' ) . '</p>';
	}
}

/**
 * Display sees ready tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_sees_ready_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/sees-ready.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'SEES Ready tab content view file not found.', 'tradepress' ) . '</p>';
	}
}

/**
 * Display sees pro tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_sees_pro_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/sees-pro.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'SEES Pro tab content view file not found.', 'tradepress' ) . '</p>';
	}
}


// Calculators tab content.
/**
 * Display calculators tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_calculators_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/calculators.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'Calculators tab content view file not found.', 'tradepress' ) . '</p>';
	}
}

/**
 * Trading Strategies tab content with sub-tabs
 *
 * Displays the interface for creating and managing trading strategies with sub-navigation.
 *
 * @version 1.0.0
 */
function tradepress_display_trading_strategies_tab_content() {
	// Get current sub-tab.
	$sub_tab = isset( $_GET['sub_tab'] ) ? sanitize_key( wp_unslash( $_GET['sub_tab'] ) ) : 'create'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- sub_tab is display-only navigation, no state change.

	// Sub-tab navigation.
	$sub_tabs = array(
		'create'  => __( 'Create Trading Strategy', 'tradepress' ),
		'custom'  => __( 'My Custom Strategies', 'tradepress' ),
		'builtin' => __( 'Built-in Strategies', 'tradepress' ),
	);

	echo '<div class="tradepress-sub-tabs">';
	echo '<ul class="subsubsub">';
	$count = 0;
	foreach ( $sub_tabs as $key => $label ) {
		$active_class = ( $sub_tab === $key ) ? ' current' : '';
		$url          = admin_url( 'admin.php?page=tradepress_trading&tab=trading-strategies&sub_tab=' . $key );
		$separator    = ( $count > 0 ) ? ' | ' : '';
		echo '<li><a href="' . esc_url( $url ) . '" class="' . esc_attr( $active_class ) . '">' . esc_html( $label ) . '</a>' . esc_html( $separator ) . '</li>';
		++$count;
	}
	echo '</ul>';

	echo '<div class="sub-tab-content">';
	switch ( $sub_tab ) {
		case 'create':
			$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/create_strategy.php';
			if ( file_exists( $view_file ) ) {
				include_once $view_file;
			} else {
				echo '<p>' . esc_html__( 'Create Strategy view file not found.', 'tradepress' ) . '</p>';
			}
			break;
		case 'custom':
			$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/trading-strategies.php';
			if ( file_exists( $view_file ) ) {
				include_once $view_file;
			} else {
				echo '<p>' . esc_html__( 'Custom Strategies view file not found.', 'tradepress' ) . '</p>';
			}
			break;
		case 'builtin':
			tradepress_display_builtin_strategies();
			break;
		default:
			echo '<p>' . esc_html__( 'Invalid sub-tab.', 'tradepress' ) . '</p>';
	}
	echo '</div>';
	echo '</div>';
}

/**
 * Display built-in trading strategies
 *
 * @version 1.0.0
 */
function tradepress_display_builtin_strategies() {
	$strategies = array(
		array(
			'name'            => 'Conviction Growth Strategy',
			'type'            => 'Custom (TradePress)',
			'trading_style'   => 'Growth/Position Trading',
			'risk_level'      => 'Medium-High',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'SEES Scoring (75+), Earnings Calendar, Resistance Levels, Volume Analysis',
			'principles'      => 'High-conviction entries with aggressive profit-taking near resistance. Strategic earnings timing with confidence to hold through volatility. Target: 60% annual returns.',
			'risk_management' => 'Mental stops, position sizing by conviction level, max 5-8 positions, hold through setbacks if fundamentals intact',
		),
		array(
			'name'            => 'SEES Score-Based Strategy',
			'type'            => 'Custom (TradePress)',
			'trading_style'   => 'Swing/Position Trading',
			'risk_level'      => 'Medium',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'SEES Scoring System, Technical Analysis, Volume',
			'principles'      => 'Uses TradePress SEES scoring to identify high-probability trades. Combines fundamental analysis with technical indicators.',
			'risk_management' => 'Position sizing based on score confidence, stop-loss at 8-12%, take profit at 15-25%',
		),
		array(
			'name'            => 'Mean Reversion',
			'type'            => 'Classic',
			'trading_style'   => 'Swing Trading',
			'risk_level'      => 'Medium-High',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'Bollinger Bands, RSI, Moving Averages, Standard Deviation',
			'principles'      => 'Buy when price is below statistical average, sell when above. Works best in ranging markets.',
			'risk_management' => 'Tight stops for outlier events, position sizing based on volatility',
		),
		array(
			'name'            => 'Earnings Whispers',
			'type'            => 'Event-Based',
			'trading_style'   => 'Event Trading',
			'risk_level'      => 'High',
			'monitoring'      => 'Pre-earnings',
			'status'          => 'Planning',
			'indicators'      => 'Whisper vs Consensus, Revenue Growth, Sentiment Analysis',
			'principles'      => 'Trade based on earnings expectations vs whisper numbers. Focus on high-conviction beats.',
			'risk_management' => 'Exit before earnings if momentum shifts, limit position size due to volatility',
		),
		array(
			'name'            => 'Analyst Adjustments (Zacks-like)',
			'type'            => 'Fundamental',
			'trading_style'   => 'Position Trading',
			'risk_level'      => 'Medium',
			'monitoring'      => 'Weekly',
			'status'          => 'Planning',
			'indicators'      => 'Estimate Revisions, Analyst Ratings, Earnings Momentum',
			'principles'      => 'Follow analyst estimate momentum and rating changes. Buy on positive revisions.',
			'risk_management' => 'Diversified positions, stop-loss on negative revision trends',
		),
		array(
			'name'            => 'Resistance Level Breakouts',
			'type'            => 'Technical',
			'trading_style'   => 'Momentum Trading',
			'risk_level'      => 'Medium-High',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'Support/Resistance Levels, Volume, Price Action',
			'principles'      => 'Buy on confirmed breakouts above resistance with volume confirmation.',
			'risk_management' => 'Stop below breakout level, trail stops on momentum moves',
		),
		array(
			'name'            => 'VIX-Based Market Timing',
			'type'            => 'Market Timing',
			'trading_style'   => 'Market Timing',
			'risk_level'      => 'Medium',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'VIX Levels, VIX/VXV Ratio, Market Sentiment',
			'principles'      => 'Use VIX extremes to time market entries/exits. High VIX = opportunity, Low VIX = caution.',
			'risk_management' => 'Adjust position sizes based on volatility regime',
		),
		array(
			'name'            => 'S&P 500 Sector Rotation',
			'type'            => 'Sector Strategy',
			'trading_style'   => 'Position Trading',
			'risk_level'      => 'Medium',
			'monitoring'      => 'Weekly',
			'status'          => 'Planning',
			'indicators'      => 'Sector Performance, Economic Indicators, Yield Curve',
			'principles'      => 'Rotate between sectors based on economic cycle and relative performance.',
			'risk_management' => 'Diversification across sectors, rebalancing based on momentum',
		),
		array(
			'name'            => 'Adaptive Risk Monitor',
			'type'            => 'Risk Management',
			'trading_style'   => 'All Styles',
			'risk_level'      => 'Low',
			'monitoring'      => 'Real-time',
			'status'          => 'Planning',
			'indicators'      => 'Portfolio Beta, Correlation, Drawdown Metrics',
			'principles'      => 'Dynamically adjust position sizes and risk based on market conditions and portfolio metrics.',
			'risk_management' => 'Core risk management system - adjusts all other strategies',
		),
	);

	echo '<div class="tradepress-builtin-strategies">';
	echo '<div class="strategies-header">';
	echo '<h3>' . esc_html__( 'Built-in Trading Strategies', 'tradepress' ) . ' <span class="tp-demo-feature-marker dashicons dashicons-warning" title="' . esc_attr__( 'Static planning reference', 'tradepress' ) . '" aria-label="' . esc_attr__( 'Static planning reference', 'tradepress' ) . '"></span></h3>';
	echo '<p>' . esc_html__( 'These strategies are planning references only. They are not active trading automation and do not read live provider data from this view.', 'tradepress' ) . '</p>';
	echo '</div>';

	echo '<div class="tradepress-data-status-panel" data-mode="dev-only-demo" data-health="not_applicable">';
	echo '<h3>' . esc_html__( 'Built-in Strategy Status', 'tradepress' ) . '</h3>';
	echo '<table class="widefat fixed striped"><tbody>';
	echo '<tr><th scope="row">' . esc_html__( 'Data mode', 'tradepress' ) . '</th><td>' . esc_html__( 'Dev-only Demo', 'tradepress' ) . '</td></tr>';
	echo '<tr><th scope="row">' . esc_html__( 'Source of truth', 'tradepress' ) . '</th><td>' . esc_html__( 'Static planning definitions in the Trading tab controller', 'tradepress' ) . '</td></tr>';
	echo '<tr><th scope="row">' . esc_html__( 'Provider', 'tradepress' ) . '</th><td>' . esc_html__( 'Not applicable', 'tradepress' ) . '</td></tr>';
	echo '<tr><th scope="row">' . esc_html__( 'Execution state', 'tradepress' ) . '</th><td>' . esc_html__( 'Planning reference only; not executable trading automation', 'tradepress' ) . '</td></tr>';
	echo '</tbody></table>';
	echo '</div>';

	echo '<div class="tp-phase-panel tp-phase-panel-demo" role="note">';
	echo '<div class="tp-phase-panel-header">';
	echo '<span class="tp-phase-panel-icon dashicons dashicons-warning" aria-hidden="true"></span>';
	echo '<strong>' . esc_html__( 'Static data: strategy planning references', 'tradepress' ) . '</strong>';
	echo '</div>';
	echo '<p>' . esc_html__( 'These rows are hard-coded planning definitions. They are useful for shaping the product model, but they are not saved strategy records and they cannot run against live market data from this view.', 'tradepress' ) . '</p>';
	echo '<p class="tp-phase-next-step">' . esc_html__( 'Next live-data step: convert approved built-in strategy definitions into installable strategy templates backed by the final trading strategy schema and testing workflow.', 'tradepress' ) . '</p>';
	echo '</div>';

	echo '<div class="strategies-table-container">';
	echo '<table class="wp-list-table widefat fixed striped strategies-table">';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="strategy-name">' . esc_html__( 'Strategy Name', 'tradepress' ) . '</th>';
	echo '<th class="strategy-type">' . esc_html__( 'Type', 'tradepress' ) . '</th>';
	echo '<th class="strategy-style">' . esc_html__( 'Trading Style', 'tradepress' ) . '</th>';
	echo '<th class="strategy-risk">' . esc_html__( 'Risk Level', 'tradepress' ) . '</th>';
	echo '<th class="strategy-status">' . esc_html__( 'Status', 'tradepress' ) . '</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	foreach ( $strategies as $strategy ) {
		$status_class = strtolower( str_replace( ' ', '-', $strategy['status'] ) );
		$risk_class   = strtolower( str_replace( '-', '', $strategy['risk_level'] ) );

		echo '<tr class="strategy-row" data-strategy="' . esc_attr( sanitize_title( $strategy['name'] ) ) . '">';
		echo '<td class="strategy-name"><strong>' . esc_html( $strategy['name'] ) . '</strong></td>';
		echo '<td class="strategy-type">' . esc_html( $strategy['type'] ) . '</td>';
		echo '<td class="strategy-style">' . esc_html( $strategy['trading_style'] ) . '</td>';
		echo '<td class="strategy-risk risk-' . esc_attr( $risk_class ) . '">' . esc_html( $strategy['risk_level'] ) . '</td>';
		echo '<td class="strategy-status status-' . esc_attr( $status_class ) . '">' . esc_html( $strategy['status'] ) . '</td>';
		echo '</tr>';

		echo '<tr class="strategy-details" id="details-' . esc_attr( sanitize_title( $strategy['name'] ) ) . '" style="display: none;">';
		echo '<td colspan="5">';
		echo '<div class="strategy-detail-content">';

		echo '<div class="detail-section">';
		echo '<h4>' . esc_html__( 'Indicators/Components', 'tradepress' ) . '</h4>';
		echo '<p>' . esc_html( $strategy['indicators'] ) . '</p>';
		echo '</div>';

		echo '<div class="detail-section">';
		echo '<h4>' . esc_html__( 'Strategy Principles', 'tradepress' ) . '</h4>';
		echo '<p>' . esc_html( $strategy['principles'] ) . '</p>';
		echo '</div>';

		echo '<div class="detail-section">';
		echo '<h4>' . esc_html__( 'Risk Management', 'tradepress' ) . '</h4>';
		echo '<p>' . esc_html( $strategy['risk_management'] ) . '</p>';
		echo '</div>';

		echo '<div class="detail-section monitoring-info">';
		echo '<strong>' . esc_html__( 'Monitoring Frequency:', 'tradepress' ) . '</strong> ' . esc_html( $strategy['monitoring'] );
		echo '</div>';

		echo '</div>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	echo '</div>';
}
