<?php
/**
 * TradePress Algorithm Debugger Page
 *
 * Provides a visual interface for inspecting and step-debugging the
 * scoring algorithm: run status, recent runs, per-step results, and
 * the ability to trigger a fresh manual run.
 *
 * @package TradePress/Admin/Pages
 * @version 1.0.95
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TradePress_Admin_Algorithm_Debugger_Page Class
 */
class TradePress_Admin_Algorithm_Debugger_Page {

	/**
	 * Steps that make up the scoring algorithm pipeline.
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Constructor — define algorithm steps.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->steps = array(
			array(
				'id'          => 'load_symbols',
				'name'        => __( 'Load Symbols', 'tradepress' ),
				'description' => __( 'Fetch symbols scheduled for scoring from the database.', 'tradepress' ),
			),
			array(
				'id'          => 'fetch_api_data',
				'name'        => __( 'Fetch API Data', 'tradepress' ),
				'description' => __( 'Retrieve market data from connected trading platform APIs.', 'tradepress' ),
			),
			array(
				'id'          => 'apply_directives',
				'name'        => __( 'Apply Scoring Directives', 'tradepress' ),
				'description' => __( 'Run each active scoring directive (RSI, VWAP, Volume, etc.) against symbol data.', 'tradepress' ),
			),
			array(
				'id'          => 'aggregate_scores',
				'name'        => __( 'Aggregate Scores', 'tradepress' ),
				'description' => __( 'Combine directive results into a composite score per symbol.', 'tradepress' ),
			),
			array(
				'id'          => 'check_thresholds',
				'name'        => __( 'Check Signal Thresholds', 'tradepress' ),
				'description' => __( 'Compare scores against the configured threshold to generate trade signals.', 'tradepress' ),
			),
			array(
				'id'          => 'persist_results',
				'name'        => __( 'Persist Results', 'tradepress' ),
				'description' => __( 'Save scores and signals to the database and update run statistics.', 'tradepress' ),
			),
		);
	}

	/**
	 * Output the algorithm debugger page.
	 *
	 * @version 1.0.0
	 */
	public function output() {
		$is_running   = (bool) get_option( 'tradepress_algorithm_running', false );
		$current_run  = (int) get_option( 'tradepress_current_run_id', 0 );
		$recent_runs  = $this->get_recent_runs( 10 );
		$global_stats = $this->get_global_stats();

		wp_enqueue_style(
			'tradepress-algorithm-debugger',
			TRADEPRESS_PLUGIN_URL . 'assets/css/algorithm-debugger.css',
			array(),
			TRADEPRESS_VERSION
		);
		?>
		<div class="tradepress-algorithm-debugger">

			<div class="algorithm-debugger-content">

				<!-- Status Banner -->
				<div class="algorithm-container">
					<div class="algorithm-header">
						<h3><?php esc_html_e( 'Scoring Algorithm Status', 'tradepress' ); ?></h3>
						<p>
							<?php if ( $is_running ) : ?>
								<span style="color:#0073aa;">&#9679; <?php esc_html_e( 'Algorithm is currently running', 'tradepress' ); ?></span>
								<?php if ( $current_run ) : ?>
									&mdash; <?php /* translators: %d: run ID */ printf( esc_html__( 'Run #%d', 'tradepress' ), $current_run ); ?>
								<?php endif; ?>
							<?php else : ?>
								<span style="color:#666;">&#9679; <?php esc_html_e( 'Algorithm is idle', 'tradepress' ); ?></span>
							<?php endif; ?>
						</p>
					</div>

					<div class="algorithm-controls">
						<?php if ( ! $is_running ) : ?>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=tradepress_run_algorithm' ), 'tradepress_run_algorithm' ) ); ?>"
								class="button button-primary">
								<?php esc_html_e( 'Run Algorithm Now', 'tradepress' ); ?>
							</a>
						<?php else : ?>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=tradepress_stop_algorithm' ), 'tradepress_stop_algorithm' ) ); ?>"
								class="button button-secondary">
								<?php esc_html_e( 'Stop Algorithm', 'tradepress' ); ?>
							</a>
						<?php endif; ?>
						<a href="<?php echo esc_url( add_query_arg( 'tab', 'algorithm_debugger' ) ); ?>" class="button">
							<?php esc_html_e( 'Refresh', 'tradepress' ); ?>
						</a>
					</div>
				</div>

				<!-- Global Stats -->
				<div class="algorithm-container">
					<div class="algorithm-header">
						<h3><?php esc_html_e( 'Lifetime Statistics', 'tradepress' ); ?></h3>
					</div>
					<table class="widefat striped" style="max-width:600px;">
						<tbody>
							<tr>
								<td><?php esc_html_e( 'Symbols Processed', 'tradepress' ); ?></td>
								<td><strong><?php echo esc_html( number_format_i18n( $global_stats['symbols_processed'] ) ); ?></strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'API Calls Made', 'tradepress' ); ?></td>
								<td><strong><?php echo esc_html( number_format_i18n( $global_stats['api_calls'] ) ); ?></strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Scores Generated', 'tradepress' ); ?></td>
								<td><strong><?php echo esc_html( number_format_i18n( $global_stats['scores_generated'] ) ); ?></strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Trade Signals Generated', 'tradepress' ); ?></td>
								<td><strong><?php echo esc_html( number_format_i18n( $global_stats['trade_signals'] ) ); ?></strong></td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Algorithm Pipeline Steps -->
				<div class="algorithm-container">
					<div class="algorithm-header">
						<h3><?php esc_html_e( 'Algorithm Pipeline', 'tradepress' ); ?></h3>
						<p><?php esc_html_e( 'The steps below are executed in order during each scoring run.', 'tradepress' ); ?></p>
					</div>
					<div class="steps-tree">
						<?php foreach ( $this->steps as $index => $step ) : ?>
							<div class="step-node" id="step-<?php echo esc_attr( $step['id'] ); ?>">
								<div class="step-header">
									<div class="step-number"><?php echo esc_html( $index + 1 ); ?></div>
									<div class="step-info">
										<p class="step-name"><?php echo esc_html( $step['name'] ); ?></p>
										<div class="step-status">
											<span><?php echo esc_html( $step['description'] ); ?></span>
										</div>
									</div>
								</div>
							</div>
							<?php if ( $index < count( $this->steps ) - 1 ) : ?>
								<div class="step-connector"></div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Recent Runs -->
				<div class="algorithm-container">
					<div class="algorithm-header">
						<h3><?php esc_html_e( 'Recent Algorithm Runs', 'tradepress' ); ?></h3>
					</div>
					<?php if ( empty( $recent_runs ) ) : ?>
						<p><?php esc_html_e( 'No algorithm runs recorded yet.', 'tradepress' ); ?></p>
					<?php else : ?>
						<table class="widefat striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Run #', 'tradepress' ); ?></th>
									<th><?php esc_html_e( 'Type', 'tradepress' ); ?></th>
									<th><?php esc_html_e( 'Started', 'tradepress' ); ?></th>
									<th><?php esc_html_e( 'Ended', 'tradepress' ); ?></th>
									<th><?php esc_html_e( 'Status', 'tradepress' ); ?></th>
									<th><?php esc_html_e( 'Symbols', 'tradepress' ); ?></th>
									<th><?php esc_html_e( 'Signals', 'tradepress' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $recent_runs as $run ) : ?>
									<tr>
										<td><?php echo esc_html( $run->id ); ?></td>
										<td><?php echo esc_html( $run->run_type ); ?></td>
										<td><?php echo esc_html( $run->start_time ); ?></td>
										<td><?php echo esc_html( $run->end_time ? $run->end_time : '—' ); ?></td>
										<td>
											<?php
											$status_map = array(
												'completed' => '<span style="color:#46b450;">&#10003; ' . esc_html__( 'Completed', 'tradepress' ) . '</span>',
												'running' => '<span style="color:#0073aa;">&#9679; ' . esc_html__( 'Running', 'tradepress' ) . '</span>',
												'error'   => '<span style="color:#dc3232;">&#10007; ' . esc_html__( 'Error', 'tradepress' ) . '</span>',
												'stopped' => '<span style="color:#b46900;">&#9632; ' . esc_html__( 'Stopped', 'tradepress' ) . '</span>',
											);
											$status_key = isset( $run->status ) ? $run->status : '';
											echo isset( $status_map[ $status_key ] )
												? wp_kses( $status_map[ $status_key ], array( 'span' => array( 'style' => array() ) ) )
												: esc_html( $status_key );
											?>
										</td>
										<td><?php echo isset( $run->symbols_processed ) ? esc_html( number_format_i18n( $run->symbols_processed ) ) : '—'; ?></td>
										<td><?php echo isset( $run->trade_signals ) ? esc_html( number_format_i18n( $run->trade_signals ) ) : '—'; ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>

			</div><!-- .algorithm-debugger-content -->
		</div><!-- .tradepress-algorithm-debugger -->
		<?php
	}

	/**
	 * Get recent algorithm runs from the database.
	 *
	 * @param  int $limit Number of rows to return.
	 * @return array        Array of run objects.
	 * @version 1.0.0
	 */
	private function get_recent_runs( $limit = 10 ) {
		global $wpdb;

		$table = $wpdb->prefix . 'tradepress_algorithm_runs';

		// Return empty array gracefully if the table doesn't exist yet.
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
			return array();
		}

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` ORDER BY id DESC LIMIT %d",  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $limit )
			)
		);
	}

	/**
	 * Get global cumulative stats stored as options.
	 *
	 * @return array
	 * @version 1.0.0
	 */
	private function get_global_stats() {
		return array(
			'symbols_processed' => (int) get_option( 'tradepress_symbols_processed', 0 ),
			'api_calls'         => (int) get_option( 'tradepress_calls', 0 ),
			'scores_generated'  => (int) get_option( 'tradepress_scores_generated', 0 ),
			'trade_signals'     => (int) get_option( 'tradepress_trade_signals', 0 ),
		);
	}
}
