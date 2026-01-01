<!-- Service Overview -->
    <div class="service-overview">
        <div class="service-logo">
            <img src="<?php echo esc_url($api_logo_url); ?>" alt="<?php echo esc_attr($api_name . ' Logo'); ?>">
        </div>
        <div class="service-details">
            <div class="service-meta">
                <span class="service-version"><?php esc_html_e('API Version:', 'tradepress'); ?> <?php echo esc_html($api_version); ?></span>
                <form method="post" id="tradepress-<?php echo esc_attr($api_id); ?>-operational-toggle" class="service-status-form">
                    <?php wp_nonce_field('tradepress_' . $api_id . '_api_settings', 'tradepress_' . $api_id . '_operational_nonce'); ?>
                    <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                    <input type="hidden" name="TradePress_switch_<?php echo esc_attr($api_id); ?>_api_services" 
                           value="<?php echo ($api_enabled === 'yes') ? 'no' : 'yes'; ?>" id="operational-value-<?php echo esc_attr($api_id); ?>">
                    <button type="submit" name="toggle_operational_status" class="service-status-toggle-button <?php echo $api_enabled === 'yes' ? 'operational' : 'disabled'; ?>">
                        <?php echo $api_enabled === 'yes' ? esc_html__('Operational', 'tradepress') : esc_html__('Disabled', 'tradepress'); ?>
                    </button>
                </form>
                
                <?php 
                // Check if this is a data-only API (no trading capabilities)
                $is_data_only_api = isset($provider['api_type']) && $provider['api_type'] === 'data_only'; 
                
                // Only show trading mode toggle if this is not a data-only API
                if (!$is_data_only_api): 
                ?>
                <form method="post" id="tradepress-<?php echo esc_attr($api_id); ?>-trading-mode-toggle" class="trading-mode-button-form">
                    <?php wp_nonce_field('tradepress_' . $api_id . '_api_settings', 'tradepress_' . $api_id . '_trading_mode_nonce'); ?>
                    <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                    <input type="hidden" name="TradePress_api_<?php echo esc_attr($api_id); ?>_trading_mode" 
                           value="<?php echo $trading_mode === 'live' ? 'paper' : 'live'; ?>" id="trading-mode-value-<?php echo esc_attr($api_id); ?>">
                    <button type="submit" name="toggle_trading_mode" class="trading-mode-toggle-button <?php echo esc_attr($trading_mode); ?>">
                        <?php echo $trading_mode === 'live' ? esc_html__('Live Trading', 'tradepress') : esc_html__('Paper Trading', 'tradepress'); ?>
                    </button>
                </form>
                <?php endif; ?>
                
                <form method="post" id="tradepress-<?php echo esc_attr($api_id); ?>-demo-mode-toggle" class="demo-mode-button-form">
                    <?php wp_nonce_field('tradepress_' . $api_id . '_api_settings', 'tradepress_' . $api_id . '_demo_mode_nonce'); ?>
                    <input type="hidden" name="api_id" value="<?php echo esc_attr($api_id); ?>">
                    <input type="hidden" name="TradePress_switch_<?php echo esc_attr($api_id); ?>_demo_mode" 
                           value="<?php echo ($demo_mode === 'yes') ? 'no' : 'yes'; ?>" id="demo-mode-value-<?php echo esc_attr($api_id); ?>">
                    <button type="submit" name="toggle_demo_mode" class="demo-mode-toggle-button <?php echo $demo_mode === 'yes' ? 'demo-on' : 'demo-off'; ?>">
                        <?php echo $demo_mode === 'yes' ? esc_html__('Demo Mode On', 'tradepress') : esc_html__('Demo Mode Off', 'tradepress'); ?>
                    </button>
                </form>
            </div>
            
            <div class="api-service-info">
                <!-- Display API version -->
                <div class="api-info-item">
                    <span class="info-label"><?php esc_html_e('Version:', 'tradepress'); ?></span>
                    <span class="info-value"><?php echo esc_html($api_version); ?></span>
                </div>
                
                <!-- Display environment (demo/live) -->
                <div class="api-info-item">
                    <span class="info-label"><?php esc_html_e('Dev Environment:', 'tradepress'); ?></span>
                    <span class="info-value <?php echo $is_demo_mode ? 'demo-env' : 'live-env'; ?>">
                        <?php echo $is_demo_mode ? esc_html__('Demo', 'tradepress') : esc_html__('Live', 'tradepress'); ?>
                    </span>
                </div>
                
                <?php if ($is_data_only_api): ?>
                <!-- Display API type for data-only APIs -->
                <div class="api-info-item">
                    <span class="info-label"><?php esc_html_e('API Type:', 'tradepress'); ?></span>
                    <span class="info-value data-only-api">
                        <?php esc_html_e('Data Only', 'tradepress'); ?>
                    </span>
                </div>
                <!-- Display data access level if available -->
                <div class="api-info-item">
                    <span class="info-label"><?php esc_html_e('Access Level:', 'tradepress'); ?></span>
                    <span class="info-value">
                        <?php echo get_option('TradePress_switch_' . $api_id . '_api_premium', 'no') === 'yes' ? 
                            esc_html__('Premium', 'tradepress') : esc_html__('Standard', 'tradepress'); ?>
                    </span>
                </div>
                <?php else: ?>
                <!-- Display trading mode (paper/live) only for trading APIs -->
                <div class="api-info-item">
                    <span class="info-label"><?php esc_html_e('Trading Mode:', 'tradepress'); ?></span>
                    <span class="info-value <?php echo $trading_mode === 'live' ? 'live-trading' : 'paper-trading'; ?>">
                        <?php echo $trading_mode === 'live' ? esc_html__('Live Trading', 'tradepress') : esc_html__('Paper Trading', 'tradepress'); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>