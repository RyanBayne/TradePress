        <!-- Quick Actions Bar -->
        <div class="api-quick-actions">
            <div class="quick-actions-row">
                <button type="button" class="button quick-action-button" data-target="api-status-view">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php esc_html_e('API Status', 'tradepress'); ?>
                </button>
                
                <button type="button" class="button quick-action-button" data-target="api-settings-section">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('API Configuration', 'tradepress'); ?>
                </button>
                
                <button type="button" class="button quick-action-button" data-target="data-explorer-section">
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e('Data Explorer', 'tradepress'); ?>
                </button>
                
                <button type="button" class="button quick-action-button" data-target="available-endpoints">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php esc_html_e('View Endpoints', 'tradepress'); ?>
                </button>
                
                <?php if (!empty($documentation_links)): ?>
                <a href="<?php echo esc_url($documentation_links[0]['url']); ?>" target="_blank" class="button quick-action-button">
                    <span class="dashicons dashicons-external"></span>
                    <?php esc_html_e('Documentation', 'tradepress'); ?>
                </a>
                <?php endif; ?>
                
                <button type="button" class="button quick-action-button clear-cache" data-api="<?php echo esc_attr($api_id); ?>">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Clear Cache', 'tradepress'); ?>
                </button>
            </div>
        </div>