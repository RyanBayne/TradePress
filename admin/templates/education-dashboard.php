<?php
/**
 * Template for the Trading Academy admin dashboard
 *
 * @package TradePress\Admin\Templates
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="wrap tradepress-academy-dashboard">
	<h1><?php esc_html_e( 'Trading Academy Dashboard', 'tradepress' ); ?></h1>
	
	<div class="academy-admin-notice notice notice-info">
		<p><?php esc_html_e( 'Welcome to the TradePress Trading Academy admin dashboard. From here you can manage all aspects of your trading education system.', 'tradepress' ); ?></p>
	</div>
	
	<div class="academy-dashboard-stats">
		<div class="stats-box">
			<h3><?php esc_html_e( 'Academy Statistics', 'tradepress' ); ?></h3>
			<div class="stats-grid">
				<div class="stat-item">
					<span class="stat-number">-</span>
					<span class="stat-label"><?php esc_html_e( 'Total Students', 'tradepress' ); ?></span>
				</div>
				<div class="stat-item">
					<span class="stat-number">-</span>
					<span class="stat-label"><?php esc_html_e( 'Active Lessons', 'tradepress' ); ?></span>
				</div>
				<div class="stat-item">
					<span class="stat-number">-</span>
					<span class="stat-label"><?php esc_html_e( 'Completion Rate', 'tradepress' ); ?></span>
				</div>
				<div class="stat-item">
					<span class="stat-number">-</span>
					<span class="stat-label"><?php esc_html_e( 'Quizzes Taken', 'tradepress' ); ?></span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="academy-dashboard-quick-links">
		<h3><?php esc_html_e( 'Quick Links', 'tradepress' ); ?></h3>
		<div class="quick-links-grid">
			<a href="#" class="quick-link-card">
				<span class="dashicons dashicons-welcome-learn-more"></span>
				<span class="quick-link-label"><?php esc_html_e( 'Manage Lessons', 'tradepress' ); ?></span>
			</a>
			<a href="#" class="quick-link-card">
				<span class="dashicons dashicons-groups"></span>
				<span class="quick-link-label"><?php esc_html_e( 'Student Management', 'tradepress' ); ?></span>
			</a>
			<a href="#" class="quick-link-card">
				<span class="dashicons dashicons-chart-bar"></span>
				<span class="quick-link-label"><?php esc_html_e( 'Reports', 'tradepress' ); ?></span>
			</a>
			<a href="#" class="quick-link-card">
				<span class="dashicons dashicons-admin-generic"></span>
				<span class="quick-link-label"><?php esc_html_e( 'Academy Settings', 'tradepress' ); ?></span>
			</a>
		</div>
	</div>
	
	<div class="academy-dashboard-recent">
		<div class="recent-activity">
			<h3><?php esc_html_e( 'Recent Activity', 'tradepress' ); ?></h3>
			<div class="activity-placeholder">
				<p><?php esc_html_e( 'Once the Trading Academy is active, recent student activity will appear here.', 'tradepress' ); ?></p>
			</div>
		</div>
	</div>
</div>
