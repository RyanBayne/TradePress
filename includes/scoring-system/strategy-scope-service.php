<?php
/**
 * Strategy symbol scope service.
 *
 * Stores and validates the symbols/watchlists a scoring strategy is intended to use.
 *
 * Scoring strategies use this as applicability metadata. SEES can show ranking
 * warnings from it. Trading strategies decide whether advisory metadata becomes
 * an execution guard.
 *
 * @package TradePress/ScoringSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service for strategy symbol scope metadata.
 */
class TradePress_Strategy_Scope_Service {

	/**
	 * Get the option key for a strategy scope.
	 *
	 * @param int $strategy_id Strategy ID.
	 * @return string
	 */
	private static function option_key( $strategy_id ) {
		return 'tradepress_strategy_scope_' . absint( $strategy_id );
	}

	/**
	 * Normalize a raw symbol list.
	 *
	 * @param string|array $symbols Raw symbols.
	 * @return array
	 */
	public static function normalize_symbols( $symbols ) {
		if ( is_string( $symbols ) ) {
			$symbols = preg_split( '/[\s,]+/', $symbols );
		}

		if ( ! is_array( $symbols ) ) {
			return array();
		}

		$normalized = array();
		foreach ( $symbols as $symbol ) {
			$symbol = strtoupper( trim( sanitize_text_field( (string) $symbol ) ) );
			$symbol = preg_replace( '/[^A-Z0-9._\-\/=:]/', '', $symbol );

			if ( '' !== $symbol ) {
				$normalized[] = $symbol;
			}
		}

		return array_values( array_unique( $normalized ) );
	}

	/**
	 * Normalize watchlist IDs.
	 *
	 * @param string|array $watchlist_ids Raw watchlist IDs.
	 * @return array
	 */
	public static function normalize_watchlist_ids( $watchlist_ids ) {
		if ( is_string( $watchlist_ids ) ) {
			$watchlist_ids = preg_split( '/[\s,]+/', $watchlist_ids );
		}

		if ( ! is_array( $watchlist_ids ) ) {
			return array();
		}

		$normalized = array();
		foreach ( $watchlist_ids as $watchlist_id ) {
			$watchlist_id = sanitize_key( (string) $watchlist_id );
			if ( '' !== $watchlist_id ) {
				$normalized[] = $watchlist_id;
			}
		}

		return array_values( array_unique( $normalized ) );
	}

	/**
	 * Normalize a scope payload.
	 *
	 * @param array $scope Raw scope.
	 * @return array
	 */
	public static function normalize_scope( $scope ) {
		if ( ! is_array( $scope ) ) {
			$scope = array();
		}

		$scope_mode = isset( $scope['scope_mode'] ) ? sanitize_key( (string) $scope['scope_mode'] ) : 'advisory';
		if ( ! in_array( $scope_mode, array( 'advisory', 'enforced' ), true ) ) {
			$scope_mode = 'advisory';
		}

		$manual_symbols = isset( $scope['manual_symbols'] ) ? self::normalize_symbols( $scope['manual_symbols'] ) : array();
		$watchlist_ids  = isset( $scope['watchlist_ids'] ) ? self::normalize_watchlist_ids( $scope['watchlist_ids'] ) : array();
		$has_scope      = ! empty( $manual_symbols ) || ! empty( $watchlist_ids );

		return array(
			'scope_mode'     => $scope_mode,
			'manual_symbols' => $manual_symbols,
			'watchlist_ids'  => $watchlist_ids,
			'has_scope'      => $has_scope,
			'summary'        => self::build_summary( $manual_symbols, $watchlist_ids, $scope_mode ),
		);
	}

	/**
	 * Get the stored scope for a strategy.
	 *
	 * @param int $strategy_id Strategy ID.
	 * @return array
	 */
	public static function get_scope( $strategy_id ) {
		$scope = get_option( self::option_key( $strategy_id ), array() );
		return self::normalize_scope( is_array( $scope ) ? $scope : array() );
	}

	/**
	 * Save a strategy scope.
	 *
	 * @param int   $strategy_id Strategy ID.
	 * @param array $scope Raw scope.
	 * @return array
	 */
	public static function save_scope( $strategy_id, $scope ) {
		$normalized = self::normalize_scope( $scope );

		if ( empty( $normalized['has_scope'] ) ) {
			delete_option( self::option_key( $strategy_id ) );
			return $normalized;
		}

		update_option(
			self::option_key( $strategy_id ),
			array(
				'scope_mode'     => $normalized['scope_mode'],
				'manual_symbols' => $normalized['manual_symbols'],
				'watchlist_ids'  => $normalized['watchlist_ids'],
			),
			false
		);

		return $normalized;
	}

	/**
	 * Delete a strategy scope.
	 *
	 * @param int $strategy_id Strategy ID.
	 * @return void
	 */
	public static function delete_scope( $strategy_id ) {
		delete_option( self::option_key( $strategy_id ) );
	}

	/**
	 * Resolve watchlist symbols through extension points.
	 *
	 * @param array $watchlist_ids Watchlist IDs.
	 * @return array
	 */
	public static function resolve_watchlist_symbols( $watchlist_ids ) {
		$symbols = array();

		foreach ( $watchlist_ids as $watchlist_id ) {
			/**
			 * Filter symbols for a strategy-scoped watchlist.
			 *
			 * @param array  $symbols      Symbols for the watchlist.
			 * @param string $watchlist_id Watchlist identifier.
			 */
			$watchlist_symbols = apply_filters( 'tradepress_strategy_scope_watchlist_symbols', array(), $watchlist_id );
			$symbols           = array_merge( $symbols, self::normalize_symbols( $watchlist_symbols ) );
		}

		return array_values( array_unique( $symbols ) );
	}

	/**
	 * Resolve all symbols currently allowed by a strategy scope.
	 *
	 * @param array $scope Normalized scope.
	 * @return array
	 */
	public static function resolve_symbols( $scope ) {
		$scope = self::normalize_scope( $scope );
		return array_values(
			array_unique(
				array_merge(
					$scope['manual_symbols'],
					self::resolve_watchlist_symbols( $scope['watchlist_ids'] )
				)
			)
		);
	}

	/**
	 * Validate a symbol against a strategy scope.
	 *
	 * The returned `allowed` flag means "allowed for the requested validation
	 * context", not "the scoring strategy may calculate a score". Scoring/SEES
	 * ranking should treat out-of-scope findings as advisory unless a trading
	 * strategy explicitly asks for enforcement.
	 *
	 * @param int    $strategy_id Strategy ID.
	 * @param string $symbol Symbol.
	 * @param string $context Validation context: scoring, sees, or trading.
	 * @return array
	 */
	public static function validate_symbol( $strategy_id, $symbol, $context = 'scoring' ) {
		$scope           = self::get_scope( $strategy_id );
		$normalized      = self::normalize_symbols( array( $symbol ) );
		$symbol          = isset( $normalized[0] ) ? $normalized[0] : '';
		$resolved_symbols = self::resolve_symbols( $scope );
		$messages        = array();
		$context         = sanitize_key( (string) $context );
		$can_enforce     = 'trading' === $context;

		if ( empty( $scope['has_scope'] ) ) {
			return array(
				'allowed'          => true,
				'status'           => 'unscoped',
				'symbol'           => $symbol,
				'scope'            => $scope,
				'resolved_symbols' => $resolved_symbols,
				'messages'         => array( __( 'Strategy has no symbol scope.', 'tradepress' ) ),
			);
		}

		if ( ! empty( $scope['watchlist_ids'] ) && empty( $resolved_symbols ) && empty( $scope['manual_symbols'] ) ) {
			$messages[] = __( 'Watchlist symbols are not available yet; manual symbols are required for enforcement in this build.', 'tradepress' );
		}

		$in_scope = '' !== $symbol && in_array( $symbol, $resolved_symbols, true );
		if ( $in_scope ) {
			$status     = 'in_scope';
			$allowed    = true;
			$messages[] = __( 'Symbol is inside the strategy scope.', 'tradepress' );
		} elseif ( 'enforced' === $scope['scope_mode'] && $can_enforce ) {
			$status     = 'out_of_scope_blocked';
			$allowed    = false;
			$messages[] = __( 'Symbol is outside the enforced trading scope.', 'tradepress' );
		} elseif ( 'enforced' === $scope['scope_mode'] ) {
			$status     = 'out_of_scope_enforcement_recommended';
			$allowed    = true;
			$messages[] = __( 'Symbol is outside the intended scope; enforcement is reserved for trading strategy execution.', 'tradepress' );
		} else {
			$status     = 'out_of_scope_advisory';
			$allowed    = true;
			$messages[] = __( 'Symbol is outside the advisory strategy scope.', 'tradepress' );
		}

		return array(
			'allowed'          => $allowed,
			'status'           => $status,
			'symbol'           => $symbol,
			'scope'            => $scope,
			'resolved_symbols' => $resolved_symbols,
			'messages'         => $messages,
		);
	}

	/**
	 * Build a short human-readable scope summary.
	 *
	 * @param array  $manual_symbols Manual symbols.
	 * @param array  $watchlist_ids Watchlist IDs.
	 * @param string $scope_mode Scope mode.
	 * @return string
	 */
	private static function build_summary( $manual_symbols, $watchlist_ids, $scope_mode ) {
		if ( empty( $manual_symbols ) && empty( $watchlist_ids ) ) {
			return __( 'No symbol scope', 'tradepress' );
		}

		$parts = array();
		if ( ! empty( $manual_symbols ) ) {
			$parts[] = sprintf(
				/* translators: %d: number of manual symbols */
				_n( '%d manual symbol', '%d manual symbols', count( $manual_symbols ), 'tradepress' ),
				count( $manual_symbols )
			);
		}

		if ( ! empty( $watchlist_ids ) ) {
			$parts[] = sprintf(
				/* translators: %d: number of watchlists */
				_n( '%d watchlist', '%d watchlists', count( $watchlist_ids ), 'tradepress' ),
				count( $watchlist_ids )
			);
		}

		return sprintf(
			/* translators: 1: scope mode, 2: scope parts */
			__( '%1$s: %2$s', 'tradepress' ),
			ucfirst( $scope_mode ),
			implode( ', ', $parts )
		);
	}
}
