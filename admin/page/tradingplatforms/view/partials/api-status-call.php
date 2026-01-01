<div class="api-status-column">
                    <div class="status-box">
                        <div class="status-header">
                            <h3><?php esc_html_e('Latest API Call', 'tradepress'); ?></h3>
                            <div class="debug-panel-actions">
                                <button type="button" class="button refresh-debug-info" data-api="<?php echo esc_attr($api_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('refresh-debug-info')); ?>">
                                    <span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh', 'tradepress'); ?>
                                </button>
                            </div>
                        </div>
                        <div id="api-call-info-container">
                            <?php 
                            // Get latest API call details
                            if (class_exists('TradePress_AJAX')) {
                                $api_call = TradePress_AJAX::get_latest_api_call($api_id);
                                
                                if ($api_call): ?>
                                    <div class="api-call-details">
                                        <table class="widefat">
                                            <tr>
                                                <th>API Identifier:</th>
                                                <td><?php echo esc_html($api_call['api_id']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Endpoint:</th>
                                                <td><?php echo esc_html($api_call['endpoint']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Method:</th>
                                                <td><?php echo esc_html($api_call['method']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Timestamp:</th>
                                                <td><?php echo esc_html($api_call['timestamp']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Request Data:</th>
                                                <td><pre><?php echo esc_html(print_r($api_call['request_data'], true)); ?></pre></td>
                                            </tr>
                                            <tr>
                                                <th>Response:</th>
                                                <td>
                                                    <div class="api-response">
                                                        <pre><?php echo esc_html(is_string($api_call['response']) ? $api_call['response'] : print_r($api_call['response'], true)); ?></pre>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="api-info-message">
                                        <p><?php echo sprintf(esc_html__('No recent API calls for %s have been cached. Make an API call with testing mode enabled to see results here.', 'tradepress'), esc_html($api_id)); ?></p>
                                    </div>
                                <?php endif;
                            } else { ?>
                                <div class="api-info-message">
                                    <p><?php esc_html_e('API call tracking system not available.', 'tradepress'); ?></p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>