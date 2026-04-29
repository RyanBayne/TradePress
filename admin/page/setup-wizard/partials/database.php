<?php
/**
 * Setup Wizard - Database Step`n *`n * @version 1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Display folder creation results if available
$folder_results = get_transient( 'tradepress_folder_results' );
if ( $folder_results ) {
	echo '<div class="tp-notice tp-notice-info">';
	echo '<div class="tp-notice-icon">📁</div>';
	echo '<div><strong>Folder Creation Results</strong><br>';
	foreach ( $folder_results as $result ) {
		echo esc_html( $result ) . '<br>';
	}
	echo '</div></div>';
	delete_transient( 'tradepress_folder_results' );
}

global $wpdb;

// Get table list from installation class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/installation/tables-installation.php';
$installer         = new TradePress_Install_Tables();
$tradepress_tables = $installer->get_tables_with_descriptions();

$bugnet_tables = array(
	'bugnet_issues'       => 'Issue tracking and bug reports',
	'bugnet_issues_meta'  => 'Issue metadata and details',
	'bugnet_reports'      => 'System reports and diagnostics',
	'bugnet_reports_meta' => 'Report metadata storage',
	'bugnet_wp_caches'    => 'WordPress cache management',
);

// Check which tables exist
$existing_tables = array();
$missing_tables  = array();

foreach ( array_merge( $tradepress_tables, $bugnet_tables ) as $table => $description ) {
	$table_name = $wpdb->prefix . $table;
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
		$existing_tables[ $table ] = $description;
	} else {
		$missing_tables[ $table ] = $description;
	}
}
?>

<h1><?php esc_html_e( 'Database Changes', 'tradepress' ); ?></h1>
<form method="post">
	<p><?php esc_html_e( 'TradePress requires database tables for trading data, monitoring, and debugging. BugNet will be installed automatically for trading monitoring.', 'tradepress' ); ?></p>
	
	<?php if ( ! empty( $existing_tables ) ) : ?>
		<h2>✅ <?php esc_html_e( 'Already Installed', 'tradepress' ); ?></h2>
		<ul>
			<?php foreach ( $existing_tables as $table => $description ) : ?>
				<li><strong><?php echo $wpdb->prefix . $table; ?></strong> - <?php echo esc_html( $description ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	
	<?php if ( ! empty( $missing_tables ) ) : ?>
		<h2>📋 <?php esc_html_e( 'To Be Installed', 'tradepress' ); ?></h2>
		<ul>
			<?php foreach ( $missing_tables as $table => $description ) : ?>
				<li><strong><?php echo $wpdb->prefix . $table; ?></strong> - <?php echo esc_html( $description ); ?></li>
			<?php endforeach; ?>
		</ul>
		<p><?php esc_html_e( 'The above tables will be created when you continue.', 'tradepress' ); ?></p>
	<?php else : ?>
		<p>✅ <?php esc_html_e( 'All required tables are already installed.', 'tradepress' ); ?></p>
	<?php endif; ?>

	<p class="tradepress-setup-actions step">
		<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'tradepress' ); ?>" name="save_step" />
		<?php wp_nonce_field( 'tradepress-setup' ); ?>
	</p>
</form>
