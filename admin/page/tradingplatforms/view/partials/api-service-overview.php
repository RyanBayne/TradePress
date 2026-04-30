<!-- Service Overview -->
	<div class="service-overview">
		<div class="service-logo">
			<?php if ( ! empty( $api_logo_url ) ) : ?>
				<img src="<?php echo esc_url( $api_logo_url ); ?>" alt="<?php echo esc_attr( $api_name ); ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
			<?php endif; ?>
			<span class="service-logo-fallback" style="<?php echo ! empty( $api_logo_url ) ? 'display:none;' : ''; ?>display:flex;align-items:center;justify-content:center;width:48px;height:48px;background:#2271b1;color:#fff;font-size:18px;font-weight:700;border-radius:8px;"><?php echo esc_html( strtoupper( substr( $api_name, 0, 2 ) ) ); ?></span>
		</div>
		<div class="service-details">
			<h3 class="service-name"><?php echo esc_html( $api_name ); ?></h3>
			<div class="service-meta">
				<form method="post" id="tradepress-<?php echo esc_attr( $api_id ); ?>-operational-toggle" class="service-status-form">
					<?php wp_nonce_field( 'tradepress_' . $api_id . '_api_settings', 'tradepress_' . $api_id . '_operational_nonce' ); ?>
					<input type="hidden" name="api_id" value="<?php echo esc_attr( $api_id ); ?>">
					<input type="hidden" name="TradePress_switch_<?php echo esc_attr( $api_id ); ?>_api_services" 
							value="<?php echo ( $api_enabled === 'yes' ) ? 'no' : 'yes'; ?>" id="operational-value-<?php echo esc_attr( $api_id ); ?>">
					<button type="submit" name="toggle_operational_status" class="service-status-toggle-button <?php echo $api_enabled === 'yes' ? 'operational' : 'disabled'; ?>">
						<?php echo $api_enabled === 'yes' ? esc_html__( 'Operational', 'tradepress' ) : esc_html__( 'Disabled', 'tradepress' ); ?>
					</button>
				</form>
				
				<?php
				// Check if this is a data-only API (no trading capabilities)
				$is_data_only_api = isset( $provider['api_type'] ) && $provider['api_type'] === 'data_only';

				// Only show trading mode toggle if this is not a data-only API
				if ( ! $is_data_only_api ) :
					?>
				<form method="post" id="tradepress-<?php echo esc_attr( $api_id ); ?>-trading-mode-toggle" class="trading-mode-button-form">
					<?php wp_nonce_field( 'tradepress_' . $api_id . '_api_settings', 'tradepress_' . $api_id . '_trading_mode_nonce' ); ?>
					<input type="hidden" name="api_id" value="<?php echo esc_attr( $api_id ); ?>">
					<input type="hidden" name="TradePress_api_<?php echo esc_attr( $api_id ); ?>_trading_mode" 
							value="<?php echo $trading_mode === 'live' ? 'paper' : 'live'; ?>" id="trading-mode-value-<?php echo esc_attr( $api_id ); ?>">
					<button type="submit" name="toggle_trading_mode" class="trading-mode-toggle-button <?php echo esc_attr( $trading_mode ); ?>">
						<?php echo $trading_mode === 'live' ? esc_html__( 'Live Trading', 'tradepress' ) : esc_html__( 'Paper Trading', 'tradepress' ); ?>
					</button>
				</form>
				<?php endif; ?>
				
			</div>
			
			<div class="api-service-info">
				<!-- Display API version -->
				<div class="api-info-item">
					<span class="info-label"><?php esc_html_e( 'Version:', 'tradepress' ); ?></span>
					<span class="info-value"><?php echo esc_html( $api_version ); ?></span>
				</div>
				
				<!-- Display environment -->
				<div class="api-info-item">
					<span class="info-label"><?php esc_html_e( 'Environment:', 'tradepress' ); ?></span>
					<span class="info-value live-env">
						<?php esc_html_e( 'Live', 'tradepress' ); ?>
					</span>
				</div>
				
				<?php if ( $is_data_only_api ) : ?>
				<!-- Display API type for data-only APIs -->
				<div class="api-info-item">
					<span class="info-label"><?php esc_html_e( 'API Type:', 'tradepress' ); ?></span>
					<span class="info-value data-only-api">
						<?php esc_html_e( 'Data Only', 'tradepress' ); ?>
					</span>
				</div>
				<!-- Display data access level if available -->
				<div class="api-info-item">
					<span class="info-label"><?php esc_html_e( 'Access Level:', 'tradepress' ); ?></span>
					<span class="info-value">
						<?php
						echo get_option( 'TradePress_switch_' . $api_id . '_api_premium', 'no' ) === 'yes' ?
							esc_html__( 'Premium', 'tradepress' ) : esc_html__( 'Standard', 'tradepress' );
						?>
					</span>
				</div>
				<?php else : ?>
				<!-- Display trading mode (paper/live) only for trading APIs -->
				<div class="api-info-item">
					<span class="info-label"><?php esc_html_e( 'Trading Mode:', 'tradepress' ); ?></span>
					<span class="info-value <?php echo $trading_mode === 'live' ? 'live-trading' : 'paper-trading'; ?>">
						<?php echo $trading_mode === 'live' ? esc_html__( 'Live Trading', 'tradepress' ) : esc_html__( 'Paper Trading', 'tradepress' ); ?>
					</span>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
