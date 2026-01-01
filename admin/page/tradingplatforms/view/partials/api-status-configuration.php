<div class="api-status-column">
                    <div class="status-box">
                        <h3><?php esc_html_e('Configuration', 'tradepress'); ?></h3>
                        
                        <?php 
                        // Determine API type
                        $provider = TradePress_API_Directory::get_provider($api_id);
                        $api_type = isset($provider['api_type']) ? $provider['api_type'] : 'trading';
                        
                        // Show different sidebar content based on API type
                        if ($api_type === 'data_only') {
                            // Data-only API sidebar content
                        ?>
                        <div class="api-type-indicator data-only">
                            <span class="dashicons dashicons-chart-area"></span>
                            <div class="api-type-text">
                                <strong><?php esc_html_e('Data-Only API', 'tradepress'); ?></strong>
                                <p><?php esc_html_e('This API provides market data but does not offer trading capabilities.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="api-features">
                            <h4><?php esc_html_e('Available Features', 'tradepress'); ?></h4>
                            <ul>
                                <?php if (isset($provider['features'])): ?>
                                    <?php foreach ($provider['features'] as $feature => $supported): ?>
                                        <?php if ($supported): ?>
                                        <li>
                                            <span class="dashicons dashicons-yes"></span>
                                            <?php echo esc_html(ucwords(str_replace('_', ' ', $feature))); ?>
                                        </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <div class="key-status-container">
                            <h4><?php esc_html_e('API Key Status', 'tradepress'); ?></h4>
                            <?php 
                            $api_key = get_option('tradepress_' . $api_id . '_api_key', '');
                            if (empty($api_key)): 
                            ?>
                                <div class="key-status missing-key">
                                    <span class="dashicons dashicons-warning"></span> 
                                    <?php esc_html_e('API Key not configured', 'tradepress'); ?>
                                </div>
                            <?php else: ?>
                                <div class="key-status valid-key">
                                    <span class="dashicons dashicons-yes"></span> 
                                    <?php esc_html_e('API Key configured', 'tradepress'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="test-credentials-buttons">
                            <button type="button" class="button test-api-connection" data-api="<?php echo esc_attr($api_id); ?>" data-nonce="<?php echo wp_create_nonce('test-api-connection'); ?>">
                                <span class="dashicons dashicons-yes"></span> 
                                <?php esc_html_e('Test API Connection', 'tradepress'); ?>
                            </button>
                        </div>
                        <?php } else { ?>
                        <div class="api-credentials-container">
                            <!-- Live Trading Column -->
                            <div class="api-credentials-column">
                                <h4><?php esc_html_e('Live Trading', 'tradepress'); ?></h4>
                                
                                <div class="key-status-container">
                                    <h5><?php esc_html_e('API Key Status', 'tradepress'); ?></h5>
                                    <?php 
                                    $real_api_key = get_option('TradePress_api_' . $api_id . '_realmoney_apikey', '');
                                    if (empty($real_api_key)): 
                                    ?>
                                        <div class="key-status missing-key">
                                            <span class="dashicons dashicons-warning"></span> 
                                            <?php esc_html_e('API Key not configured', 'tradepress'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="key-status valid-key">
                                            <span class="dashicons dashicons-yes"></span> 
                                            <?php esc_html_e('API Key configured', 'tradepress'); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h5><?php esc_html_e('API Secret Status', 'tradepress'); ?></h5>
                                    <?php 
                                    $real_api_secret = get_option('TradePress_api_' . $api_id . '_realmoney_secretkey', '');
                                    if (empty($real_api_secret)): ?>
                                        <div class="key-status missing-key">
                                            <span class="dashicons dashicons-warning"></span> 
                                            <?php esc_html_e('API Secret not configured', 'tradepress'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="key-status valid-key">
                                            <span class="dashicons dashicons-yes"></span> 
                                            <?php esc_html_e('API Secret configured', 'tradepress'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="test-credentials-buttons">
                                    <button type="button" class="button test-api-credentials" data-api="<?php echo esc_attr($api_id); ?>" data-mode="real" data-nonce="<?php echo wp_create_nonce('test-api-credentials'); ?>">
                                        <span class="dashicons dashicons-yes"></span> 
                                        <?php esc_html_e('Test Live Trading', 'tradepress'); ?>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Paper Trading Column -->
                            <div class="api-credentials-column">
                                <h4><?php esc_html_e('Paper Trading', 'tradepress'); ?></h4>
                                
                                <div class="key-status-container">
                                    <h5><?php esc_html_e('API Key Status', 'tradepress'); ?></h5>
                                    <?php 
                                    $paper_api_key = get_option('TradePress_api_' . $api_id . '_papermoney_apikey', '');
                                    if (empty($paper_api_key)): 
                                    ?>
                                        <div class="key-status missing-key">
                                            <span class="dashicons dashicons-warning"></span> 
                                            <?php esc_html_e('API Key not configured', 'tradepress'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="key-status valid-key">
                                            <span class="dashicons dashicons-yes"></span> 
                                            <?php esc_html_e('API Key configured', 'tradepress'); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h5><?php esc_html_e('API Secret Status', 'tradepress'); ?></h5>
                                    <?php 
                                    $paper_api_secret = get_option('TradePress_api_' . $api_id . '_papermoney_secretkey', '');
                                    if (empty($paper_api_secret)): ?>
                                        <div class="key-status missing-key">
                                            <span class="dashicons dashicons-warning"></span> 
                                            <?php esc_html_e('API Secret not configured', 'tradepress'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="key-status valid-key">
                                            <span class="dashicons dashicons-yes"></span> 
                                            <?php esc_html_e('API Secret configured', 'tradepress'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="test-credentials-buttons">
                                    <button type="button" class="button test-api-credentials" data-api="<?php echo esc_attr($api_id); ?>" data-mode="paper" data-nonce="<?php echo wp_create_nonce('test-api-credentials'); ?>">
                                        <span class="dashicons dashicons-yes"></span> 
                                        <?php esc_html_e('Test Paper Trading', 'tradepress'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        
                        <div id="api-test-results-<?php echo esc_attr($api_id); ?>" class="api-test-results"></div>
                    </div>
                </div>