<?php
/**
 * TradePress - Scoring Directives Logs Tab
 *
 * Monitor scoring algorithm execution, directive performance, and system activity
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$log_file    = WP_CONTENT_DIR . '/scoring.log';
$log_entries = array();
$error_count = 0;
$warning_count = 0;

if ( file_exists( $log_file ) && is_readable( $log_file ) ) {
	$log_lines = file( $log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
	$log_lines = is_array( $log_lines ) ? array_slice( array_reverse( $log_lines ), 0, 50 ) : array();

	foreach ( $log_lines as $line ) {
		$time    = '';
		$message = $line;

		if ( preg_match( '/^\[(.*?)\]\s*(.*)$/', $line, $matches ) ) {
			$time    = $matches[1];
			$message = $matches[2];
		}

		$level = 'info';
		if ( stripos( $message, 'error' ) !== false || stripos( $message, 'failed' ) !== false ) {
			$level = 'error';
		} elseif ( stripos( $message, 'warning' ) !== false || stripos( $message, 'limit' ) !== false ) {
			$level = 'warning';
		} elseif ( stripos( $message, 'debug' ) !== false ) {
			$level = 'debug';
		}

		$log_entries[] = array(
			'time'    => $time,
			'level'   => $level,
			'source'  => 'scoring',
			'message' => $message,
			'context' => basename( $log_file ),
		);

		if ( 'error' === $level ) {
			$error_count++;
		} elseif ( 'warning' === $level ) {
			$warning_count++;
		}
	}
}
?>

<div class="tradepress-logs-interface">
	<div class="logs-controls">
		<div class="log-filters">
			<select id="log-level" class="tp-select">
				<option value="all"><?php esc_html_e( 'All Levels', 'tradepress' ); ?></option>
				<option value="info"><?php esc_html_e( 'Info', 'tradepress' ); ?></option>
				<option value="warning"><?php esc_html_e( 'Warning', 'tradepress' ); ?></option>
				<option value="error"><?php esc_html_e( 'Error', 'tradepress' ); ?></option>
				<option value="debug"><?php esc_html_e( 'Debug', 'tradepress' ); ?></option>
			</select>
			
			<select id="log-source" class="tp-select">
				<option value="all"><?php esc_html_e( 'All Sources', 'tradepress' ); ?></option>
				<option value="algorithm"><?php esc_html_e( 'Algorithm', 'tradepress' ); ?></option>
				<option value="directives"><?php esc_html_e( 'Directives', 'tradepress' ); ?></option>
				<option value="api"><?php esc_html_e( 'API Calls', 'tradepress' ); ?></option>
				<option value="scoring"><?php esc_html_e( 'Scoring Engine', 'tradepress' ); ?></option>
			</select>
			
			<input type="date" id="log-date" class="tp-input" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
			
			<button type="button" class="tp-button tp-button-primary refresh-logs-btn">
				<?php esc_html_e( 'Refresh', 'tradepress' ); ?>
			</button>
			
			<button type="button" class="tp-button tp-button-secondary clear-logs-btn">
				<?php esc_html_e( 'Clear Logs', 'tradepress' ); ?>
			</button>
		</div>
	</div>
	
	<div class="logs-content">
		<div class="logs-table-container">
			<table class="widefat logs-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'tradepress' ); ?></th>
						<th><?php esc_html_e( 'Level', 'tradepress' ); ?></th>
						<th><?php esc_html_e( 'Source', 'tradepress' ); ?></th>
						<th><?php esc_html_e( 'Message', 'tradepress' ); ?></th>
						<th><?php esc_html_e( 'Context', 'tradepress' ); ?></th>
					</tr>
				</thead>
				<tbody id="logs-tbody">
					<?php if ( empty( $log_entries ) ) : ?>
						<tr>
							<td colspan="5">
								<div class="notice notice-info inline">
									<p>
										<strong><?php esc_html_e( 'No scoring logs found.', 'tradepress' ); ?></strong>
										<?php esc_html_e( 'Real scoring activity will appear here after scoring logging is enabled and a scoring run writes to scoring.log.', 'tradepress' ); ?>
									</p>
								</div>
							</td>
						</tr>
					<?php endif; ?>
					<?php foreach ( $log_entries as $log ) : ?>
						<tr class="log-entry log-<?php echo esc_attr( $log['level'] ); ?>">
							<td class="log-time"><?php echo esc_html( $log['time'] ); ?></td>
							<td class="log-level">
								<span class="level-badge level-<?php echo esc_attr( $log['level'] ); ?>">
									<?php echo esc_html( ucfirst( $log['level'] ) ); ?>
								</span>
							</td>
							<td class="log-source"><?php echo esc_html( ucfirst( $log['source'] ) ); ?></td>
							<td class="log-message"><?php echo esc_html( $log['message'] ); ?></td>
							<td class="log-context"><?php echo esc_html( $log['context'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
		<div class="logs-pagination">
			<div class="pagination-info">
				<?php
				printf(
					/* translators: %d: number of log entries shown. */
					esc_html__( 'Showing latest %d real scoring log entries', 'tradepress' ),
					count( $log_entries )
				);
				?>
			</div>
			<div class="pagination-controls">
				<button type="button" class="tp-button tp-button-outline" disabled>
					<?php esc_html_e( 'Previous', 'tradepress' ); ?>
				</button>
				<button type="button" class="tp-button tp-button-outline" disabled>
					<?php esc_html_e( 'Next', 'tradepress' ); ?>
				</button>
			</div>
		</div>
	</div>
	
	<div class="logs-statistics">
		<h4><?php esc_html_e( 'Log Summary', 'tradepress' ); ?></h4>
		<div class="stats-grid">
			<div class="stat-item">
				<span class="stat-number"><?php echo esc_html( count( $log_entries ) ); ?></span>
				<span class="stat-label"><?php esc_html_e( 'Entries Shown', 'tradepress' ); ?></span>
			</div>
			<div class="stat-item">
				<span class="stat-number"><?php echo esc_html( $error_count ); ?></span>
				<span class="stat-label"><?php esc_html_e( 'Errors', 'tradepress' ); ?></span>
			</div>
			<div class="stat-item">
				<span class="stat-number"><?php echo esc_html( $warning_count ); ?></span>
				<span class="stat-label"><?php esc_html_e( 'Warnings', 'tradepress' ); ?></span>
			</div>
			<div class="stat-item">
				<span class="stat-number"><?php echo file_exists( $log_file ) ? esc_html( size_format( filesize( $log_file ) ) ) : esc_html__( 'None', 'tradepress' ); ?></span>
				<span class="stat-label"><?php esc_html_e( 'Log File Size', 'tradepress' ); ?></span>
			</div>
		</div>
	</div>
</div>
