                <div class="api-status-column">
                    <div class="status-box">
                        <div class="status-header">
                            <h3><?php esc_html_e('API Status Overview', 'tradepress'); ?></h3>
                            <div class="status-actions">
                                <a href="#" class="button-refresh" title="<?php esc_attr_e('Refresh Status', 'tradepress'); ?>">
                                    <span class="dashicons dashicons-update"></span>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Local Status -->
                        <div class="status-indicator">
                            <?php
                            if ($demo_mode === 'yes') {
                                $status_color = get_status_color($local_status['status']);
                                $status_message = $local_status['message'];
                            } else {
                                // Try to get real status from API or database
                                try {
                                    $real_local_status = tradepress_get_real_local_status($api_id);
                                    $status_color = get_status_color($real_local_status['status']);
                                    $status_message = $real_local_status['message'];
                                } catch (Exception $e) {
                                    $status_color = 'status-red';
                                    $status_message = '<span class="error-status"><span class="dashicons dashicons-warning"></span> ' . 
                                                     esc_html__('Error: ', 'tradepress') . esc_html($e->getMessage()) . '</span>';
                                }
                            }
                            ?>
                            <div class="status-dot <?php echo esc_attr($status_color); ?>"></div>
                            <div>
                                <strong><?php esc_html_e('Local Status:', 'tradepress'); ?></strong>
                                <?php echo $status_message; ?>
                            </div>
                        </div>
                        
                        <!-- Service Status -->
                        <div class="status-indicator">
                            <div class="status-dot <?php 
                            if ($demo_mode === 'yes') {
                                echo esc_attr(get_status_color($service_status['status']));
                            } else {
                                try {
                                    $real_service_status = tradepress_get_real_service_status($api_id);
                                    echo esc_attr(get_status_color($real_service_status['status']));
                                } catch (Exception $e) {
                                    echo 'error';
                                }
                            }
                            ?>"></div>
                            <div>
                                <strong><?php esc_html_e('Service Status:', 'tradepress'); ?></strong>
                                <?php 
                                if ($demo_mode === 'yes') {
                                    echo esc_html($service_status['message']);
                                    ?>
                                    <div class="status-updated">
                                        <small><?php esc_html_e('Last Updated:', 'tradepress'); ?> <?php echo esc_html($service_status['last_updated']); ?></small>
                                    </div>
                                    <?php
                                } else {
                                    try {
                                        $real_service_status = tradepress_get_real_service_status($api_id);
                                        echo esc_html($real_service_status['message']);
                                        ?>
                                        <div class="status-updated">
                                            <small><?php esc_html_e('Last Updated:', 'tradepress'); ?> <?php echo esc_html($real_service_status['last_updated']); ?></small>
                                        </div>
                                        <?php
                                    } catch (Exception $e) {
                                        echo '<span class="error-status"><span class="dashicons dashicons-warning"></span> ';
                                        echo esc_html__('Error: ', 'tradepress') . esc_html($e->getMessage());
                                        echo '</span>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        
                        <?php if ($demo_mode === 'yes' || !empty(tradepress_get_real_rate_limits($api_id))): ?>
                        <!-- Rate Limiting -->
                        <h4><?php esc_html_e('Rate Limiting', 'tradepress'); ?></h4>
                        
                        <?php 
                        $rate_limit_data = $demo_mode === 'yes' ? $rate_limits : tradepress_get_real_rate_limits($api_id);
                        
                        if ($demo_mode === 'no' && empty($rate_limit_data)) {
                            echo '<div class="error-status"><span class="dashicons dashicons-warning"></span> ';
                            echo esc_html__('Error: Unable to retrieve rate limit data', 'tradepress');
                            echo '</div>';
                        } else {
                            if (isset($rate_limit_data['daily_quota'])): 
                        ?>
                        <div>
                            <strong><?php esc_html_e('Daily Usage:', 'tradepress'); ?></strong>
                            <span><?php echo esc_html($rate_limit_data['daily_used']); ?> / <?php echo esc_html($rate_limit_data['daily_quota']); ?></span>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar" style="width: <?php echo esc_attr(($rate_limit_data['daily_used'] / $rate_limit_data['daily_quota']) * 100); ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($rate_limit_data['hourly_quota'])): ?>
                        <div>
                            <strong><?php esc_html_e('Hourly Usage:', 'tradepress'); ?></strong>
                            <span><?php echo esc_html($rate_limit_data['hourly_used']); ?> / <?php echo esc_html($rate_limit_data['hourly_quota']); ?></span>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar" style="width: <?php echo esc_attr(($rate_limit_data['hourly_used'] / $rate_limit_data['hourly_quota']) * 100); ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($rate_limit_data['minute_quota'])): ?>
                        <div>
                            <strong><?php esc_html_e('Per Minute Usage:', 'tradepress'); ?></strong>
                            <span><?php echo esc_html($rate_limit_data['minute_used']); ?> / <?php echo esc_html($rate_limit_data['minute_quota']); ?></span>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar" style="width: <?php echo esc_attr(($rate_limit_data['minute_used'] / $rate_limit_data['minute_quota']) * 100); ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($rate_limit_data['reset_time'])): ?>
                        <div>
                            <small>
                                <?php esc_html_e('Quota resets at:', 'tradepress'); ?> 
                                <?php echo esc_html($rate_limit_data['reset_time']); ?>
                            </small>
                        </div>
                        <?php 
                               endif;
                           }
                        ?>
                        <?php endif; ?>
                    </div>
                </div>