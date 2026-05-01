<?php
/**
 * Queue Database Schema
 *
 * Creates database tables for background processing queue system
 *
 * @package TradePress
 * @subpackage BackgroundProcessing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TradePress_Queue_Schema {

	/**
	 * Create queue tables
	 *
	 * @version 1.0.0
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Queue items table
		$queue_table = $wpdb->prefix . 'tradepress_queue';
		$queue_sql   = "CREATE TABLE $queue_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            queue_name varchar(50) NOT NULL,
            priority int(11) NOT NULL DEFAULT 10,
            status varchar(20) NOT NULL DEFAULT 'pending',
            item_type varchar(50) NOT NULL,
            item_data longtext NOT NULL,
            attempts int(11) NOT NULL DEFAULT 0,
            max_attempts int(11) NOT NULL DEFAULT 3,
            created_at datetime NOT NULL,
            scheduled_at datetime NOT NULL,
            started_at datetime NULL,
            completed_at datetime NULL,
            error_message text NULL,
            PRIMARY KEY (id),
            KEY queue_status (queue_name, status),
            KEY priority_scheduled (priority DESC, scheduled_at ASC),
            KEY status_attempts (status, attempts)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $queue_sql );

		// Update database version
		update_option( 'tradepress_queue_db_version', '1.0.0' );
	}

	/**
	 * Check if tables exist and create if needed
	 *
	 * @version 1.0.0
	 */
	public static function maybe_create_tables() {
		$db_version = get_option( 'tradepress_queue_db_version', '0' );

		if ( version_compare( $db_version, '1.0.0', '<' ) ) {
			self::create_tables();
		}
	}

	/**
	 * Add item to queue
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $queue_name
	 * @param mixed $item_type
	 * @param mixed $item_data
	 * @param int   $priority
	 * @param mixed $scheduled_at
	 */
	public static function add_item( $queue_name, $item_type, $item_data, $priority = 10, $scheduled_at = null ) {
		global $wpdb;

		self::maybe_create_tables();

		if ( ! $scheduled_at ) {
			$scheduled_at = current_time( 'mysql' );
		}

		$table = $wpdb->prefix . 'tradepress_queue';

		return $wpdb->insert(
			$table,
			array(
				'queue_name'   => $queue_name,
				'priority'     => $priority,
				'item_type'    => $item_type,
				'item_data'    => json_encode( $item_data ),
				'created_at'   => current_time( 'mysql' ),
				'scheduled_at' => $scheduled_at,
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Check whether an item is already pending or processing.
	 *
	 * @version 1.0.0
	 *
	 * @param string $queue_name Queue name.
	 * @param string $item_type Item type.
	 * @return bool
	 */
	public static function has_active_item( $queue_name, $item_type ) {
		global $wpdb;

		self::maybe_create_tables();

		$table = $wpdb->prefix . 'tradepress_queue';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT COUNT(*)
				FROM $table
				WHERE queue_name = %s
				AND item_type = %s
				AND status IN ( 'pending', 'processing' )
				AND attempts < max_attempts
				",
				$queue_name,
				$item_type
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (int) $count > 0;
	}

	/**
	 * Check whether a queue has any pending or processing items.
	 *
	 * @version 1.0.0
	 *
	 * @param string $queue_name Queue name.
	 * @return bool
	 */
	public static function has_active_queue( $queue_name ) {
		global $wpdb;

		self::maybe_create_tables();

		$table = $wpdb->prefix . 'tradepress_queue';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT COUNT(*)
				FROM $table
				WHERE queue_name = %s
				AND status IN ( 'pending', 'processing' )
				AND attempts < max_attempts
				",
				$queue_name
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return (int) $count > 0;
	}

	/**
	 * Get a queue status summary for monitoring views.
	 *
	 * @version 1.0.0
	 *
	 * @param string $queue_name Queue name.
	 * @return array
	 */
	public static function get_queue_summary( $queue_name ) {
		global $wpdb;

		self::maybe_create_tables();

		$table   = $wpdb->prefix . 'tradepress_queue';
		$summary = array(
			'pending'       => 0,
			'processing'    => 0,
			'completed'     => 0,
			'failed'        => 0,
			'total'         => 0,
			'active'        => 0,
			'last_created'  => '',
			'last_finished' => '',
			'last_error'    => '',
		);

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT status, COUNT(*) AS status_count
				FROM $table
				WHERE queue_name = %s
				GROUP BY status
				",
				$queue_name
			),
			ARRAY_A
		);

		foreach ( $rows as $row ) {
			$status = sanitize_key( $row['status'] );
			$count  = (int) $row['status_count'];

			if ( isset( $summary[ $status ] ) ) {
				$summary[ $status ] = $count;
			}

			$summary['total'] += $count;
		}

		$last_row = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT created_at, completed_at, error_message
				FROM $table
				WHERE queue_name = %s
				ORDER BY created_at DESC
				LIMIT 1
				",
				$queue_name
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( $last_row ) {
			$summary['last_created']  = isset( $last_row['created_at'] ) ? $last_row['created_at'] : '';
			$summary['last_finished'] = isset( $last_row['completed_at'] ) ? $last_row['completed_at'] : '';
			$summary['last_error']    = isset( $last_row['error_message'] ) ? $last_row['error_message'] : '';
		}

		$summary['active'] = $summary['pending'] + $summary['processing'];

		return $summary;
	}

	/**
	 * Get next item from queue
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $queue_name
	 */
	public static function get_next_item( $queue_name ) {
		global $wpdb;

		$table = $wpdb->prefix . 'tradepress_queue';

		return $wpdb->get_row(
			$wpdb->prepare(
				"
            SELECT * FROM $table 
            WHERE queue_name = %s 
            AND status = 'pending' 
            AND scheduled_at <= %s 
            AND attempts < max_attempts
            ORDER BY priority DESC, scheduled_at ASC 
            LIMIT 1
        ",
				$queue_name,
				current_time( 'mysql' )
			)
		);
	}
}
