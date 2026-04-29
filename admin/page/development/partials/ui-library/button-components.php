<?php
/**
 * UI Library Button Components Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.7
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="tradepress-ui-section tradepress-ui-section--buttons">
	<h3><?php esc_html_e( 'Button Components', 'tradepress' ); ?></h3>
	<p><?php esc_html_e( 'Standard button variations for consistent UI interactions.', 'tradepress' ); ?></p>

	<!-- Primary Buttons -->
	<div class="tradepress-component-group">
		<h4><?php esc_html_e( 'Primary Buttons', 'tradepress' ); ?></h4>
		<div class="tradepress-component-showcase">
			<button class="button button-primary"><?php esc_html_e( 'Primary Button', 'tradepress' ); ?></button>
			<button class="button button-primary" disabled><?php esc_html_e( 'Disabled Primary', 'tradepress' ); ?></button>
			<button class="button button-primary button-large"><?php esc_html_e( 'Large Primary', 'tradepress' ); ?></button>
			<button class="button button-primary button-small"><?php esc_html_e( 'Small Primary', 'tradepress' ); ?></button>
		</div>
	</div>

	<!-- Secondary Buttons -->
	<div class="tradepress-component-group">
		<h4><?php esc_html_e( 'Secondary Buttons', 'tradepress' ); ?></h4>
		<div class="tradepress-component-showcase">
			<button class="button button-secondary"><?php esc_html_e( 'Secondary Button', 'tradepress' ); ?></button>
			<button class="button button-secondary" disabled><?php esc_html_e( 'Disabled Secondary', 'tradepress' ); ?></button>
			<button class="button button-secondary button-large"><?php esc_html_e( 'Large Secondary', 'tradepress' ); ?></button>
			<button class="button button-secondary button-small"><?php esc_html_e( 'Small Secondary', 'tradepress' ); ?></button>
		</div>
	</div>

	<!-- Icon Buttons -->
	<div class="tradepress-component-group tradepress-component-group--icon-buttons">
		<h4><?php esc_html_e( 'Icon Buttons', 'tradepress' ); ?></h4>
		<div class="tradepress-component-showcase">
			<button type="button" class="button button-primary tradepress-button-icon">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Add New', 'tradepress' ); ?>
			</button>
			<button type="button" class="button button-secondary tradepress-button-icon">
				<span class="dashicons dashicons-edit"></span>
				<?php esc_html_e( 'Edit', 'tradepress' ); ?>
			</button>
			<button type="button" class="button button-secondary tradepress-button-icon">
				<span class="dashicons dashicons-trash"></span>
				<?php esc_html_e( 'Delete', 'tradepress' ); ?>
			</button>
			<button type="button" class="button button-secondary tradepress-button-icon">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Download', 'tradepress' ); ?>
			</button>
		</div>
	</div>

	<!-- Button Groups -->
	<div class="tradepress-component-group tradepress-component-group--button-groups">
		<h4><?php esc_html_e( 'Button Groups', 'tradepress' ); ?></h4>
		<div class="tradepress-component-showcase">
			<div class="tradepress-ui-button-group" role="group" aria-label="<?php esc_attr_e( 'Button Group Example', 'tradepress' ); ?>">
				<button type="button" class="button button-secondary"><?php esc_html_e( 'Left', 'tradepress' ); ?></button>
				<button type="button" class="button button-secondary"><?php esc_html_e( 'Center', 'tradepress' ); ?></button>
				<button type="button" class="button button-secondary"><?php esc_html_e( 'Right', 'tradepress' ); ?></button>
			</div>
		</div>
	</div>

	<!-- Status Badge Buttons -->
	<div class="tradepress-component-group tradepress-component-group--status-badges">
		<h4><?php esc_html_e( 'Status Badge Buttons', 'tradepress' ); ?></h4>
		<div class="tradepress-component-showcase">
			<span class="status-badge status-active"><?php esc_html_e( 'Operational', 'tradepress' ); ?></span>
			<span class="status-badge status-inactive"><?php esc_html_e( 'Disabled', 'tradepress' ); ?></span>
			<span class="type-badge type-data"><?php esc_html_e( 'Data Only', 'tradepress' ); ?></span>
			<span class="type-badge type-trading"><?php esc_html_e( 'Trading', 'tradepress' ); ?></span>
			<span class="mode-badge mode-live"><?php esc_html_e( 'Live', 'tradepress' ); ?></span>
			<span class="mode-badge mode-paper"><?php esc_html_e( 'Paper', 'tradepress' ); ?></span>
			<span class="rate-limit-badge rate-normal"><?php esc_html_e( 'Normal', 'tradepress' ); ?></span>
		</div>
	</div>
</div>
