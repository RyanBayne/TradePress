<?php
/**
 * TradePress Analysis - Recent Symbols tab view.
 *
 * @package TradePress/Admin/Analysis
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tradepress_get_tab_mode' ) ) {
	require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/functions/function.tradepress-features-helpers.php';
}

$tab_mode = tradepress_get_tab_mode( 'analysis', 'recent_symbols' );

if ( ! $tab_mode['enabled'] ) {
	echo '<div class="notice notice-warning"><p>' . esc_html__( 'This tab is currently disabled in Features settings.', 'tradepress' ) . '</p></div>';
	return;
}

global $wpdb;

$scores_table = $wpdb->prefix . 'tradepress_symbol_scores';
$table_exists  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $scores_table ) ) );

if ( $table_exists !== $scores_table ) {
	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'No Analysis Data', 'tradepress' ) . '</strong> - ' . esc_html__( 'Recent symbol analysis will appear here after the scoring tables are installed and populated.', 'tradepress' ) . '</p></div>';
	return;
}

$recent_scores = $wpdb->get_results(
	"SELECT score_id.id, score_id.symbol_id, score_id.symbol, score_id.score, score_id.created_at, posts.post_title
	FROM {$scores_table} score_id
	LEFT JOIN {$wpdb->posts} posts ON posts.ID = score_id.symbol_id
	ORDER BY score_id.created_at DESC
	LIMIT 20",
	ARRAY_A
);

?>

<div class="tradepress-analysis-recent-symbols">
	<h2><?php esc_html_e( 'Recent Symbol Analysis', 'tradepress' ); ?></h2>

	<div class="tradepress-data-status-panel" data-mode="cached" data-health="not_applicable">
		<h3><?php esc_html_e( 'Recent Analysis Data Status', 'tradepress' ); ?></h3>
		<table class="widefat fixed striped">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Data mode', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Cached or Empty', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Source of truth', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Stored scoring rows in the tradepress_symbol_scores table', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Provider', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Not selected in this render path', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Queue behavior', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'No queue trigger from this view; scoring/import processes populate the table separately', 'tradepress' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<?php if ( empty( $recent_scores ) ) : ?>
		<div class="notice notice-info">
			<p><strong><?php esc_html_e( 'Not Scored', 'tradepress' ); ?></strong> - <?php esc_html_e( 'No real symbol scores have been generated yet. Run a scoring strategy to populate this view.', 'tradepress' ); ?></p>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Symbol', 'tradepress' ); ?></th>
					<th><?php esc_html_e( 'Name', 'tradepress' ); ?></th>
					<th><?php esc_html_e( 'Score', 'tradepress' ); ?></th>
					<th><?php esc_html_e( 'Scored At', 'tradepress' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent_scores as $score_row ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $score_row['symbol'] ); ?></strong></td>
						<td><?php echo esc_html( $score_row['post_title'] ?: __( 'Unknown', 'tradepress' ) ); ?></td>
						<td><?php echo esc_html( $score_row['score'] ); ?></td>
						<td><?php echo esc_html( $score_row['created_at'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
