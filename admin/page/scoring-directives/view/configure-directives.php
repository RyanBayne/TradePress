<?php
/**
 * TradePress - Configure Directives Tab (New Clean Version)
 * 
 * @package TradePress/Admin/Directives
 * @version 2.0.0
 * @created 2024-12-16
 * 
 * @todo COMPLETED: Added strategy validation framework with placeholder functions
 *       - Added tradepress_get_directive_strategies() placeholder
 *       - Added tradepress_validate_directive_disable() validation
 *       - Added Strategies column to directive table
 *       - Integration ready for when strategy management is implemented
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load directives data
if (!function_exists('tradepress_get_all_directives')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-loader.php';
}

// Load directive handler
require_once dirname(__FILE__) . '/../directive-handler.php';

// Enqueue new CSS
wp_enqueue_style('tradepress-configure-directives', TRADEPRESS_PLUGIN_URL . 'assets/css/pages/configure-directives.css', array(), TRADEPRESS_VERSION);

// Dequeue any conflicting JavaScript
wp_dequeue_script('tradepress-scoring-directives');
wp_deregister_script('tradepress-scoring-directives');
wp_dequeue_script('tradepress-directive-testing');
wp_deregister_script('tradepress-directive-testing');

// Add inline CSS to force visibility and style new sections
wp_add_inline_style('tradepress-configure-directives', '
.directive-section { display: block !important; visibility: visible !important; }
.position-monitoring-section, .hard-limits-section { 
    margin-top: 20px; 
    padding: 15px; 
    border: 1px solid #ddd; 
    border-radius: 4px; 
    background: #f9f9f9; 
}
.position-monitoring-section h4, .hard-limits-section h4 { 
    margin: 0 0 15px 0; 
    color: #0073aa; 
    font-size: 14px; 
}
.position-monitoring-config, .hard-limit-config { 
    margin-top: 15px; 
    padding-top: 15px; 
    border-top: 1px solid #ddd; 
}
.directive-test-notice { 
    background: #f0f8ff; 
    border-left: 4px solid #0073aa; 
    padding: 12px; 
    margin: 8px 0; 
}
.directive-test-notice .notice-header { 
    font-size: 16px; 
    margin-bottom: 8px; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}
.directive-test-notice .price { 
    color: #0073aa; 
    font-weight: bold; 
}
.directive-test-notice .notice-details { 
    margin: 8px 0; 
    display: flex; 
    flex-wrap: wrap; 
    gap: 15px; 
}
.directive-test-notice .detail-item { 
    font-size: 13px; 
}
.directive-test-notice .detail-item em { 
    color: #666; 
    font-size: 12px; 
}
.directive-test-notice .notice-scores { 
    margin-top: 8px; 
    padding-top: 8px; 
    border-top: 1px solid #ddd; 
}
.directive-test-notice .score-item { 
    font-size: 14px; 
    margin-right: 20px; 
}
.directive-test-notice .score-change.positive { 
    color: #46b450; 
}
.directive-test-notice .score-change.negative { 
    color: #dc3232; 
}
.directive-test-notice .score-change.neutral { 
    color: #666; 
}
.directive-test-notice.error-notice { 
    background: #ffeaea; 
    border-left-color: #dc3232; 
}
');

// Load enhanced object registry functions
if (!function_exists('tradepress_get_symbol')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/object-registry-enhanced.php';
}

// Log all POST requests to this page
if (!empty($_POST)) {
    error_log('[' . date('Y-m-d H:i:s') . '] POST received: ' . print_r($_POST, true), 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
}



// Handle test directive form submission
if (isset($_POST['action']) && $_POST['action'] === 'test_directive') {
    // Log user action
    error_log('[' . date('Y-m-d H:i:s') . '] User ' . get_current_user_id() . ' clicked Test button for directive: ' . ($_POST['directive_id'] ?? 'unknown'), 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
    
    if (wp_verify_nonce($_POST['test_nonce'], 'tradepress_test_directive') && current_user_can('manage_options')) {
        $directive_id = sanitize_text_field($_POST['directive_id']);
        $directive_code = sanitize_text_field($_POST['directive_code'] ?? '');
        
        // Log developer mode status
        $dev_mode = get_option('tradepress_developer_mode', 'no');
        error_log('[' . date('Y-m-d H:i:s') . '] Developer mode status: ' . $dev_mode, 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
        error_log('[' . date('Y-m-d H:i:s') . '] is_admin(): ' . (is_admin() ? 'true' : 'false'), 3, TRADEPRESS_PLUGIN_DIR_PATH . 'trace.log');
        
        // Get selected symbol and trading mode from form
        $test_symbol = sanitize_text_field($_POST['test_symbol'] ?? 'AAPL');
        $trading_mode = sanitize_text_field($_POST['trading_mode'] ?? 'long');
        
        // Update last used timestamp before testing
        $option_key = 'tradepress_directive_' . $directive_id;
        $current_config = get_option($option_key, array());
        $current_config['last_used'] = current_time('mysql');
        update_option($option_key, $current_config);
        
        // Use the directive handler for testing
        $test_result = TradePress_Directive_Handler::test_directive($directive_id, $test_symbol, $trading_mode, $directive_code);
        
        if ($test_result['success']) {
            // Create enhanced directive test notice
            $notice_html = TradePress_Directive_Handler::create_directive_test_notice($directive_id, $test_result['test_data'], $trading_mode, $test_result['directive_code'] ?? null);
            add_settings_error('tradepress_directives', 'directive_test_success', $notice_html, 'updated');
        } else {
            // Create enhanced error notice based on error type
            $error_type = $test_result['error_type'] ?? 'general';
            
            $error_html = '<div class="directive-test-notice error-notice">';
            
            // Add directive code if available
            $directive_code_display = '';
            if (!empty($test_result['directive_code'])) {
                $directive_code_display = '<code style="background: #0073aa; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; margin-right: 8px;">' . esc_html($test_result['directive_code']) . '</code>';
            }
            
            if ($error_type === 'rate_limit') {
                $error_html .= '<div class="notice-header">' . $directive_code_display . '<strong>⚠️ API Limit Reached</strong></div>';
                $error_html .= '<div class="notice-details">';
                $error_html .= '<span class="detail-item">Alpha Vantage free tier allows 25 API calls per day</span>';
                $error_html .= '<span class="detail-item"><strong>Solution:</strong> Try again tomorrow or <a href="https://www.alphavantage.co/premium/" target="_blank">upgrade to premium</a></span>';
                $error_html .= '</div>';
            } else {
                $error_html .= '<div class="notice-header">' . $directive_code_display . '<strong>Test Failed</strong></div>';
                $error_html .= '<div class="notice-details">';
                $error_html .= '<span class="detail-item"><strong>Error:</strong> ' . esc_html($test_result['message']) . '</span>';
                $error_html .= '</div>';
            }
            
            $error_html .= '</div>';
            add_settings_error('tradepress_directives', 'directive_test_failed', $error_html, 'error');
        }
        
        // Keep the directive selected
        $_GET['configure'] = $directive_id;
    }
}

// Handle save configuration
if (isset($_POST['action']) && $_POST['action'] === 'save_directive') {
    $directive_id = sanitize_text_field($_POST['directive_id']);
    
    // Debug output
    error_log('Save directive called for: ' . $directive_id);
    error_log('POST data: ' . print_r($_POST, true));
    
    $result = TradePress_Directive_Handler::save_configuration($directive_id, $_POST);
    
    error_log('Save result: ' . print_r($result, true));
    
    if ($result['success']) {
        $message = __('Configuration saved successfully.', 'tradepress');
        
        if (!empty($result['changes'])) {
            $message .= ' Changes: ' . implode(', ', $result['changes']);
        }
        
        add_settings_error('tradepress_directives', 'directive_saved', $message, 'updated');
        
        // Add warnings as separate notices
        if (!empty($result['warnings'])) {
            foreach ($result['warnings'] as $warning) {
                add_settings_error('tradepress_directives', 'directive_warning_' . uniqid(), '⚠️ ' . $warning, 'notice-warning');
            }
        }
    } else {
        add_settings_error('tradepress_directives', 'directive_save_failed', $result['message'], 'error');
    }
    
    // Don't redirect immediately - let notices display first
    $_GET['configure'] = $directive_id; // Keep the directive selected
}

// Handle feature issue submission
if (isset($_POST['action']) && $_POST['action'] === 'report_feature_issue') {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/feedback/class-feature-feedback-system.php';
    
    $result = TradePress_Feature_Feedback_System::handle_issue_submission($_POST);
    
    if ($result['success']) {
        add_settings_error('tradepress_directives', 'feature_issue_reported', $result['message'], 'updated');
    } else {
        add_settings_error('tradepress_directives', 'feature_issue_failed', $result['message'], 'error');
    }
    
    // Redirect back to where user came from
    $return_url = get_transient('tradepress_feedback_return_' . get_current_user_id());
    delete_transient('tradepress_feedback_return_' . get_current_user_id());
    
    if ($return_url) {
        wp_redirect($return_url);
        exit;
    }
}

// Handle form submission
if (isset($_POST['action']) && $_POST['action'] === 'toggle_directive') {
    if (wp_verify_nonce($_POST['toggle_nonce'], 'tradepress_toggle_directive') && current_user_can('manage_options')) {
        $directive_id = sanitize_text_field($_POST['directive_id']);
        $new_state = (bool)intval($_POST['new_state']);
        
        // Check composite directive dependencies when enabling
        if ($new_state && isset($all_directives[$directive_id]['components'])) {
            $missing_components = array();
            foreach ($all_directives[$directive_id]['components'] as $component_id) {
                if (!isset($all_directives[$component_id]) || !$all_directives[$component_id]['active']) {
                    $component_name = isset($all_directives[$component_id]) ? $all_directives[$component_id]['name'] : ucfirst(str_replace('_', ' ', $component_id));
                    $missing_components[] = $component_name;
                }
            }
            
            if (!empty($missing_components)) {
                $message = sprintf(
                    __('Cannot enable %s. Required components not active: %s', 'tradepress'),
                    $all_directives[$directive_id]['name'],
                    implode(', ', $missing_components)
                );
                add_settings_error('tradepress_directives', 'composite_dependencies_missing', $message, 'error');
                set_transient('settings_errors', get_settings_errors(), 30);
                wp_redirect(admin_url('admin.php?page=tradepress_scoring_directives&tab=configure_directives'));
                exit;
            }
        }
        
        // TODO: Add strategy validation when disabling directives
        if (!$new_state) {
            $validation = tradepress_validate_directive_disable($directive_id);
            if (!$validation['success']) {
                add_settings_error('tradepress_directives', 'directive_disable_blocked', $validation['message'], 'error');
                set_transient('settings_errors', get_settings_errors(), 30);
                wp_redirect(admin_url('admin.php?page=tradepress_scoring_directives&tab=configure_directives'));
                exit;
            }
        }
        
        // Get current directives
        $configured_directives = get_option('tradepress_scoring_directives', array());
        
        // Update the directive state
        if (!isset($configured_directives[$directive_id])) {
            $configured_directives[$directive_id] = array();
        }
        $configured_directives[$directive_id]['active'] = $new_state;
        
        // Save back to options
        update_option('tradepress_scoring_directives', $configured_directives);
        
        // Add success notice
        $directive_name = $all_directives[$directive_id]['name'] ?? ucfirst(str_replace('_', ' ', $directive_id));
        $message = $new_state 
            ? sprintf(__('%s directive has been enabled.', 'tradepress'), $directive_name)
            : sprintf(__('%s directive has been disabled.', 'tradepress'), $directive_name);
        
        add_settings_error('tradepress_directives', 'directive_toggled', $message, 'updated');
        set_transient('settings_errors', get_settings_errors(), 30);
        
        // Redirect to configure the directive if it was enabled
        $redirect_url = admin_url('admin.php?page=tradepress_scoring_directives&tab=configure_directives');
        if ($new_state) {
            $redirect_url = add_query_arg('configure', $directive_id, $redirect_url);
        }
        wp_redirect($redirect_url);
        exit;
    }
}

// Get all directives
$all_directives = tradepress_get_all_directives();

/**
 * TODO: Strategy Management Integration
 * 
 * The following functions need to be implemented when strategy management is ready:
 * 
 * 1. tradepress_get_directive_strategies($directive_id)
 *    - Returns array of strategies using this directive
 *    - Format: [['id' => 1, 'name' => 'Strategy Name'], ...]
 * 
 * 2. Strategy validation before directive disable
 *    - Check if directive is used by active strategies
 *    - Prevent disable if strategies depend on it
 * 
 * 3. Strategy page integration
 *    - Link to admin.php?page=tradepress_strategies&strategy=ID
 *    - Show directive usage within strategy configuration
 * 
 * Files to create/modify:
 * - includes/strategy-management/strategy-directive-mapper.php
 * - admin/page/strategies/ (strategy management pages)
 * - Database tables for strategy-directive relationships
 */

/**
 * Placeholder function to get strategies using a directive
 * 
 * @param string $directive_id The directive ID
 * @return array Array of strategies using this directive
 */
function tradepress_get_directive_strategies($directive_id) {
    // TODO: Implement actual database lookup when strategy management is ready
    // This should query the strategy-directive relationship table
    
    // For now, return empty array (shows "None")
    return array();
    
    // Example of what this should return when implemented:
    /*
    return array(
        array('id' => 1, 'name' => 'Conservative Growth'),
        array('id' => 3, 'name' => 'Momentum Trading')
    );
    */
}

/**
 * Placeholder function to validate directive can be disabled
 * 
 * @param string $directive_id The directive ID
 * @return array Validation result with success/message
 */
function tradepress_validate_directive_disable($directive_id) {
    // TODO: Implement when strategy management is ready
    // Check if directive is used by any active strategies
    
    $strategies = tradepress_get_directive_strategies($directive_id);
    
    if (!empty($strategies)) {
        $strategy_names = array_column($strategies, 'name');
        return array(
            'success' => false,
            'message' => sprintf(
                __('Cannot disable directive. It is currently used by: %s', 'tradepress'),
                implode(', ', $strategy_names)
            )
        );
    }
    
    return array('success' => true, 'message' => '');
}
?>

<div class="configure-directives-container">
    <?php settings_errors('tradepress_directives'); ?>
    
    <div class="directives-layout">
        <!-- Left Column: Directives Table -->
        <div class="directives-table-container">
            <div class="tablenav top">
                <div class="alignleft actions">
                    <input type="search" id="directive-search-input" name="s" value="<?php echo esc_attr(isset($_GET['s']) ? $_GET['s'] : ''); ?>" placeholder="<?php esc_attr_e('Search directives...', 'tradepress'); ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Directives', 'tradepress'); ?>">
                </div>
            </div>
            <div class="wp-list-table widefat fixed striped">
                <?php
                // Handle sorting
                $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'name';
                $order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';
                
                // Default sort order preferences (what users likely want first)
                $default_orders = array(
                    'status' => 'desc', // Active first
                    'impact' => 'desc', // High impact first
                    'weight' => 'desc', // Higher weight first
                    'last_used' => 'desc' // Most recent first
                );
                
                // If no explicit order and this is the first sort on this column, use preferred default
                if (!isset($_GET['order']) && isset($default_orders[$orderby])) {
                    $order = $default_orders[$orderby];
                }
                
                // Sort directives
                uasort($all_directives, function($a, $b) use ($orderby, $order, $all_directives) {
                    $val_a = '';
                    $val_b = '';
                    
                    switch ($orderby) {
                        case 'name':
                            $val_a = $a['name'];
                            $val_b = $b['name'];
                            break;
                        case 'status':
                            $val_a = $a['active'] ? 1 : 0;
                            $val_b = $b['active'] ? 1 : 0;
                            break;
                        case 'impact':
                            $impact_order = array('low' => 1, 'medium' => 2, 'high' => 3);
                            $val_a = $impact_order[$a['impact'] ?? 'low'];
                            $val_b = $impact_order[$b['impact'] ?? 'low'];
                            break;
                        case 'weight':
                            $val_a = $a['weight'] ?? 10;
                            $val_b = $b['weight'] ?? 10;
                            break;
                        case 'last_used':
                            // Find directive IDs for these directives
                            $directive_id_a = array_search($a, $all_directives);
                            $directive_id_b = array_search($b, $all_directives);
                            $config_a = get_option('tradepress_directive_' . $directive_id_a, array());
                            $config_b = get_option('tradepress_directive_' . $directive_id_b, array());
                            $val_a = !empty($config_a['last_used']) ? strtotime($config_a['last_used']) : 0;
                            $val_b = !empty($config_b['last_used']) ? strtotime($config_b['last_used']) : 0;
                            break;
                    }
                    
                    if ($val_a == $val_b) return 0;
                    $result = $val_a < $val_b ? -1 : 1;
                    return $order === 'desc' ? -$result : $result;
                });
                
                function get_sort_url($column) {
                    global $orderby, $order;
                    // If clicking the same column, toggle order
                    if ($orderby === $column) {
                        $new_order = ($order === 'asc') ? 'desc' : 'asc';
                    } else {
                        // New column - use smart defaults
                        $defaults = array('status' => 'desc', 'impact' => 'desc', 'weight' => 'desc', 'last_used' => 'desc');
                        $new_order = isset($defaults[$column]) ? $defaults[$column] : 'asc';
                    }
                    return add_query_arg(array('orderby' => $column, 'order' => $new_order));
                }
                
                function get_sort_class($column) {
                    global $orderby, $order;
                    if ($orderby === $column) {
                        return $order === 'asc' ? 'sorted asc' : 'sorted desc';
                    }
                    return 'sortable';
                }
                ?>
                
                <div class="table-header" style="display: flex; background: #f1f1f1; padding: 12px 15px; font-weight: 600; border-bottom: 1px solid #c3c4c7;">
                    <?php if (get_option('tradepress_developer_mode', 'no') === 'yes') : ?>
                    <div style="flex: 0.5;"><?php _e('Code', 'tradepress'); ?></div>
                    <?php endif; ?>
                    <div style="flex: 2;">
                        <a href="<?php echo esc_url(get_sort_url('name')); ?>" class="<?php echo get_sort_class('name'); ?>">
                            <?php _e('Directive Name', 'tradepress'); ?>
                        </a>
                    </div>
                    <div style="flex: 1;">
                        <a href="<?php echo esc_url(get_sort_url('status')); ?>" class="<?php echo get_sort_class('status'); ?>">
                            <?php _e('Status', 'tradepress'); ?>
                        </a>
                    </div>
                    <div style="flex: 1;"><?php _e('Dev Status', 'tradepress'); ?></div>
                    <div style="flex: 1;">
                        <a href="<?php echo esc_url(get_sort_url('impact')); ?>" class="<?php echo get_sort_class('impact'); ?>">
                            <?php _e('Impact', 'tradepress'); ?>
                        </a>
                    </div>
                    <div style="flex: 1;">
                        <a href="<?php echo esc_url(get_sort_url('weight')); ?>" class="<?php echo get_sort_class('weight'); ?>">
                            <?php _e('Weight', 'tradepress'); ?>
                        </a>
                    </div>
                    <div style="flex: 1;">
                        <a href="<?php echo esc_url(get_sort_url('last_used')); ?>" class="<?php echo get_sort_class('last_used'); ?>">
                            <?php _e('Last Used', 'tradepress'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="tradepress-compact-table">
                <?php 
                // Handle search filtering
                $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
                $filtered_directives = $all_directives;
                
                if (!empty($search_term)) {
                    $filtered_directives = array_filter($all_directives, function($directive, $id) use ($search_term) {
                        return stripos($directive['name'], $search_term) !== false || 
                               stripos($directive['description'] ?? '', $search_term) !== false ||
                               stripos($directive['code'] ?? '', $search_term) !== false;
                    }, ARRAY_FILTER_USE_BOTH);
                }
                
                foreach ($filtered_directives as $directive_id => $directive): ?>
                    <div class="accordion-row">
                        <div class="accordion-header">
                            <?php if (get_option('tradepress_developer_mode', 'no') === 'yes') : ?>
                            <div style="flex: 0.5;">
                                <code style="background: #0073aa; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold;"><?php echo esc_html($directive['code'] ?? ''); ?></code>
                            </div>
                            <?php endif; ?>
                            <div style="flex: 2;">
                                <strong><?php echo esc_html($directive['name']); ?></strong>
                            </div>
                            <div style="flex: 1;">
                                <span class="status-badge <?php echo $directive['active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $directive['active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            <div style="flex: 1;">
                                <?php 
                                $dev_status = $directive['development_status'] ?? 'development';
                                $status_class = $dev_status === 'tested' ? 'status-active' : 'status-inactive';
                                ?>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo esc_html(ucfirst($dev_status)); ?>
                                </span>
                            </div>
                            <div style="flex: 1;">
                                <span class="impact-badge impact-<?php echo esc_attr($directive['impact'] ?? 'low'); ?>">
                                    <?php echo esc_html(ucfirst($directive['impact'] ?? 'low')); ?>
                                </span>
                            </div>
                            <div style="flex: 1;"><?php echo esc_html($directive['weight'] ?? 10); ?></div>
                            <div style="flex: 1;">
                                <?php 
                                $saved_config = get_option('tradepress_directive_' . $directive_id, array());
                                if (!empty($saved_config['last_used'])) {
                                    echo esc_html(human_time_diff(strtotime($saved_config['last_used']), current_time('timestamp')) . ' ago');
                                } else {
                                    echo '<span style="color: #666;">' . esc_html__('Never', 'tradepress') . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <?php if (isset($directive['components']) && !empty($directive['components'])) : ?>
                        <div class="directive-components-row" style="display: flex; padding: 8px 15px; background: #f9f9f9; border-top: 1px solid #e0e0e0; align-items: center;">
                            <div style="flex: 2; font-size: 12px; color: #666; font-weight: 500;">
                                <?php esc_html_e('Required Components:', 'tradepress'); ?>
                            </div>
                            <div style="flex: 6; display: flex; gap: 4px; flex-wrap: wrap;">
                                <?php foreach ($directive['components'] as $component_id) : ?>
                                    <?php 
                                    $component_directive = isset($all_directives[$component_id]) ? $all_directives[$component_id] : null;
                                    $is_active = $component_directive && $component_directive['active'];
                                    $code = $component_directive['code'] ?? strtoupper(substr($component_id, 0, 3));
                                    $opacity = $is_active ? '1' : '0.4';
                                    $bg_color = $is_active ? '#0073aa' : '#999';
                                    ?>
                                    <code style="background: <?php echo $bg_color; ?>; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; opacity: <?php echo $opacity; ?>; cursor: help;" title="<?php echo esc_attr($component_directive['name'] ?? ucfirst(str_replace('_', ' ', $component_id))); ?> - <?php echo $is_active ? 'Active' : 'Inactive'; ?>"><?php echo esc_html($code); ?></code>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="accordion-content">
                            <div class="directive-meta">
                                <div>
                                    <strong>Description:</strong><br>
                                    <?php echo esc_html($directive['description'] ?? 'No description available'); ?>
                                </div>
                                <div>
                                    <strong>Bullish:</strong><br>
                                    <?php echo esc_html($directive['bullish'] ?? 'Not specified'); ?>
                                </div>
                                <div>
                                    <strong>Bearish:</strong><br>
                                    <?php echo esc_html($directive['bearish'] ?? 'Not specified'); ?>
                                </div>
                            </div>
                            
                            <?php if (isset($directive['tip'])): ?>
                                <div class="directive-tip-box">
                                    <h4><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e('Usage Tip', 'tradepress'); ?></h4>
                                    <p class="directive-tip-content"><?php echo esc_html($directive['tip']); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="directive-actions">
                                <a href="<?php echo esc_url(add_query_arg('configure', $directive_id)); ?>" class="button button-primary">
                                    <?php esc_html_e('Configure', 'tradepress'); ?>
                                </a>
                                <button type="button" class="button view-api-call" data-directive="<?php echo esc_attr($directive_id); ?>">
                                    <?php esc_html_e('API Call', 'tradepress'); ?>
                                </button>
                                <button type="button" class="button view-api-response" data-directive="<?php echo esc_attr($directive_id); ?>">
                                    <?php esc_html_e('Response', 'tradepress'); ?>
                                </button>
                                <button type="button" class="button view-directive-outcome" data-directive="<?php echo esc_attr($directive_id); ?>">
                                    <?php esc_html_e('Outcome', 'tradepress'); ?>
                                </button>
                                <form method="post" style="display: inline;" class="directive-test-form" data-directive="<?php echo esc_attr($directive_id); ?>">
                                    <?php wp_nonce_field('tradepress_test_directive', 'test_nonce'); ?>
                                    <input type="hidden" name="action" value="test_directive">
                                    <input type="hidden" name="directive_id" value="<?php echo esc_attr($directive_id); ?>">
                                    <input type="hidden" name="directive_code" value="<?php echo esc_attr($directive['code'] ?? ''); ?>">
                                    <input type="hidden" name="test_symbol" value="<?php 
                                        // Use symbol from settings
                                        if (!class_exists('TradePress_AI_Directive_Tester')) {
                                            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-tester.php';
                                        }
                                        echo esc_attr(TradePress_AI_Directive_Tester::get_test_symbol('NVDA')); 
                                    ?>">
                                    <input type="hidden" name="trading_mode" value="long">
                                    <button type="submit" class="button">
                                        <?php esc_html_e('Test', 'tradepress'); ?>
                                    </button>
                                </form>
                                <form method="post" style="display: inline;">
                                    <?php wp_nonce_field('tradepress_toggle_directive', 'toggle_nonce'); ?>
                                    <input type="hidden" name="action" value="toggle_directive">
                                    <input type="hidden" name="directive_id" value="<?php echo esc_attr($directive_id); ?>">
                                    <input type="hidden" name="new_state" value="<?php echo $directive['active'] ? '0' : '1'; ?>">
                                    <?php 
                                    $can_enable = true;
                                    $disabled_reason = '';
                                    
                                    // Check if this is a composite directive with inactive components
                                    if (!$directive['active'] && isset($directive['components'])) {
                                        $missing_components = array();
                                        foreach ($directive['components'] as $component_id) {
                                            if (!isset($all_directives[$component_id]) || !$all_directives[$component_id]['active']) {
                                                $component_name = isset($all_directives[$component_id]) ? $all_directives[$component_id]['name'] : ucfirst(str_replace('_', ' ', $component_id));
                                                $missing_components[] = $component_name;
                                            }
                                        }
                                        if (!empty($missing_components)) {
                                            $can_enable = false;
                                            $disabled_reason = 'Required components not active: ' . implode(', ', $missing_components);
                                        }
                                    }
                                    ?>
                                    <button type="submit" class="button" <?php echo !$can_enable ? 'disabled title="' . esc_attr($disabled_reason) . '"' : ''; ?>>
                                        <?php echo $directive['active'] ? esc_html__('Disable', 'tradepress') : esc_html__('Enable', 'tradepress'); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Column: Stacked Containers -->
        <div class="directive-right-column">
            <!-- Configuration Container -->
            <div class="directive-details-container">
            <?php 
            $configure_directive = isset($_GET['configure']) ? sanitize_text_field($_GET['configure']) : 'rsi';
            if (isset($all_directives[$configure_directive])) {
                $directive = $all_directives[$configure_directive];
            } else {
                $directive = array('name' => 'Unknown Directive', 'active' => false, 'description' => 'Directive not found');
            }
            
            // Load saved configuration
            $saved_config = get_option('tradepress_directive_' . $configure_directive, array());
            ?>
            
            <div class="directive-section">
                <div class="section-header">
                    <h3>
                        <span class="construction-icon dashicons dashicons-admin-tools" title="<?php esc_attr_e('Under Construction', 'tradepress'); ?>"></span>
                        <?php if (get_option('tradepress_developer_mode', 'no') === 'yes' && !empty($directive['code'])) : ?>
                            <code style="background: #0073aa; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; margin-right: 8px;"><?php echo esc_html($directive['code']); ?></code>
                        <?php endif; ?>
                        <?php echo esc_html($directive['name']); ?>
                    </h3>
                </div>
                
                <div class="section-content">
                    <?php if (!$directive['active']) : ?>
                        <div class="directive-disabled-message">
                            <div class="notice notice-warning inline">
                                <p><strong><?php esc_html_e('Directive Disabled', 'tradepress'); ?></strong></p>
                                <p><?php esc_html_e('This directive needs to be enabled before it can be configured. Use the Enable button in the Actions column to activate it.', 'tradepress'); ?></p>
                            </div>
                        </div>
                    <?php else : ?>

                        
                        <div class="directive-description">
                            <p><?php echo esc_html($directive['description']); ?></p>
                        </div>
                        
                        <form method="post">
                            <?php wp_nonce_field('tradepress_save_directive', 'save_nonce'); ?>
                            <input type="hidden" name="action" value="save_directive">
                            <input type="hidden" name="directive_id" value="<?php echo esc_attr($configure_directive); ?>">
                            
                            <div class="directive-settings">
                                <div class="setting-group">
                                    <label><?php esc_html_e('Weight:', 'tradepress'); ?></label>
                                    <div class="setting-control">
                                        <input type="number" name="weight" value="<?php echo esc_attr($saved_config['weight'] ?? $directive['weight'] ?? 10); ?>" min="0" max="100" title="<?php esc_attr_e('Importance of this directive in the overall strategy it is used in.', 'tradepress'); ?>">
                                    </div>
                                </div>
                                
                                <?php if ($configure_directive === 'rsi') : ?>
                                <div class="setting-group">
                                    <label><?php esc_html_e('RSI Period:', 'tradepress'); ?></label>
                                    <div class="setting-control">
                                        <input type="number" name="period" value="<?php echo esc_attr($saved_config['period'] ?? 14); ?>" min="1" max="50" title="<?php esc_attr_e('Number of periods to calculate RSI (typically 14).', 'tradepress'); ?>">
                                    </div>
                                </div>
                                
                                <div class="setting-group">
                                    <label><?php esc_html_e('Base Multiplier:', 'tradepress'); ?></label>
                                    <div class="setting-control">
                                        <input type="number" name="base_multiplier" value="<?php echo esc_attr($saved_config['base_multiplier'] ?? 0.5); ?>" min="0.1" max="2.0" step="0.1" title="<?php esc_attr_e('Controls overall scoring sensitivity (0.1 = low, 2.0 = high).', 'tradepress'); ?>">
                                    </div>
                                </div>
                                
                                
                                <div class="setting-group">
                                    <label><?php esc_html_e('Bonus Tier 1 (Moderate):', 'tradepress'); ?></label>
                                    <div class="setting-control">
                                        <input type="number" name="bonus_tier_1" value="<?php echo esc_attr($saved_config['bonus_tier_1'] ?? 20); ?>" min="0" max="50" title="<?php esc_attr_e('Bonus points for RSI ≤30 (long) or ≥70 (short).', 'tradepress'); ?>">
                                    </div>
                                </div>
                                
                                <div class="setting-group">
                                    <label><?php esc_html_e('Bonus Tier 2 (Strong):', 'tradepress'); ?></label>
                                    <div class="setting-control">
                                        <input type="number" name="bonus_tier_2" value="<?php echo esc_attr($saved_config['bonus_tier_2'] ?? 40); ?>" min="0" max="100" title="<?php esc_attr_e('Additional bonus for RSI ≤20 (long) or ≥80 (short).', 'tradepress'); ?>">
                                    </div>
                                </div>
                                
                                <div class="setting-group">
                                    <label><?php esc_html_e('Bonus Tier 3 (Extreme):', 'tradepress'); ?></label>
                                    <div class="setting-control">
                                        <input type="number" name="bonus_tier_3" value="<?php echo esc_attr($saved_config['bonus_tier_3'] ?? 80); ?>" min="0" max="200" title="<?php esc_attr_e('Additional bonus for RSI ≤10 (long) or ≥90 (short).', 'tradepress'); ?>">
                                    </div>
                                </div>
                                
                                <div class="setting-group">
                                    <label><?php esc_html_e('Minimum Score:', 'tradepress'); ?></label>
                                    <div class="setting-control">
                                        <input type="number" name="min_score" value="<?php echo esc_attr($saved_config['min_score'] ?? 0); ?>" min="0" max="100" step="0.1" title="<?php esc_attr_e('Minimum score to return regardless of RSI value.', 'tradepress'); ?>">
                                    </div>
                                </div>
                                

                            <?php elseif ($configure_directive === 'isa_reset') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Days Before Reset:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="days_before" value="<?php echo esc_attr($saved_config['days_before'] ?? 3); ?>" min="0" max="30">
                                    <p class="setting-description"><?php esc_html_e('Number of days before the ISA reset to apply scoring impact.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Days After Reset:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="days_after" value="<?php echo esc_attr($saved_config['days_after'] ?? 3); ?>" min="0" max="30">
                                    <p class="setting-description"><?php esc_html_e('Number of days after the ISA reset to apply scoring impact.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Score Impact:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="score_impact" value="<?php echo esc_attr($saved_config['score_impact'] ?? 10); ?>" min="0" max="50">
                                    <p class="setting-description"><?php esc_html_e('Points to add to the symbol score during the ISA reset period.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="isa-info-box">
                                <h4><?php esc_html_e('About ISA Reset', 'tradepress'); ?></h4>
                                <p><?php esc_html_e('The ISA allowance resets on April 6th each year. This directive increases scores during this period to highlight potential trading opportunities.', 'tradepress'); ?></p>
                                <p><strong><?php esc_html_e('Next ISA Reset Date:', 'tradepress'); ?></strong> <?php echo date('F j, Y', strtotime('April 6, ' . (date('n') >= 4 && date('j') > 5 ? date('Y') + 1 : date('Y')))); ?></p>
                            </div>
                            
                            <?php elseif ($configure_directive === 'volume') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Base Multiplier:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="base_multiplier" value="<?php echo esc_attr($saved_config['base_multiplier'] ?? 1.0); ?>" min="0.1" max="3.0" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Controls overall scoring sensitivity (0.1 = low, 3.0 = high).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('High Volume Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="high_volume_bonus" value="<?php echo esc_attr($saved_config['high_volume_bonus'] ?? 25); ?>" min="0" max="50">
                                    <p class="setting-description"><?php esc_html_e('Bonus points when volume is ≥1.5x average.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Volume Surge Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="surge_bonus" value="<?php echo esc_attr($saved_config['surge_bonus'] ?? 50); ?>" min="0" max="100">
                                    <p class="setting-description"><?php esc_html_e('Additional bonus when volume is ≥3.0x average.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Minimum Score:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="min_score" value="<?php echo esc_attr($saved_config['min_score'] ?? 0); ?>" min="0" max="100" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Minimum score to return regardless of volume ratio.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'moving_averages') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Short MA Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="short_period" value="<?php echo esc_attr($saved_config['short_period'] ?? 20); ?>" min="5" max="50">
                                    <p class="setting-description"><?php esc_html_e('Short-term moving average period (typically 20).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Long MA Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="long_period" value="<?php echo esc_attr($saved_config['long_period'] ?? 50); ?>" min="30" max="200">
                                    <p class="setting-description"><?php esc_html_e('Long-term moving average period (typically 50).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('MA Type:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <select name="ma_type">
                                        <option value="SMA" <?php selected($saved_config['ma_type'] ?? 'SMA', 'SMA'); ?>>Simple Moving Average (SMA)</option>
                                        <option value="EMA" <?php selected($saved_config['ma_type'] ?? 'SMA', 'EMA'); ?>>Exponential Moving Average (EMA)</option>
                                    </select>
                                    <p class="setting-description"><?php esc_html_e('Type of moving average calculation.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Trend Alignment Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="alignment_bonus" value="<?php echo esc_attr($saved_config['alignment_bonus'] ?? 25); ?>" min="10" max="50">
                                    <p class="setting-description"><?php esc_html_e('Bonus when price is above both MAs (bullish alignment).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'mfi') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="period" value="<?php echo esc_attr($saved_config['period'] ?? 14); ?>" min="5" max="30">
                                    <p class="setting-description"><?php esc_html_e('Number of periods for MFI calculation (typically 14).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Oversold Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="oversold" value="<?php echo esc_attr($saved_config['oversold'] ?? 20); ?>" min="10" max="30">
                                    <p class="setting-description"><?php esc_html_e('MFI value indicating oversold condition (typically 20).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Overbought Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="overbought" value="<?php echo esc_attr($saved_config['overbought'] ?? 80); ?>" min="70" max="90">
                                    <p class="setting-description"><?php esc_html_e('MFI value indicating overbought condition (typically 80).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'macd') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Fast Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="fast_period" value="<?php echo esc_attr($saved_config['fast_period'] ?? 12); ?>" min="5" max="30">
                                    <p class="setting-description"><?php esc_html_e('Fast EMA period for MACD calculation (typically 12).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Slow Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="slow_period" value="<?php echo esc_attr($saved_config['slow_period'] ?? 26); ?>" min="15" max="50">
                                    <p class="setting-description"><?php esc_html_e('Slow EMA period for MACD calculation (typically 26).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Signal Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="signal_period" value="<?php echo esc_attr($saved_config['signal_period'] ?? 9); ?>" min="5" max="20">
                                    <p class="setting-description"><?php esc_html_e('Signal line EMA period (typically 9).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Crossover Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="crossover_bonus" value="<?php echo esc_attr($saved_config['crossover_bonus'] ?? 30); ?>" min="10" max="50">
                                    <p class="setting-description"><?php esc_html_e('Bonus points for bullish MACD crossover.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'ema') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('EMA Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="period" value="<?php echo esc_attr($saved_config['period'] ?? 21); ?>" min="5" max="200">
                                    <p class="setting-description"><?php esc_html_e('Number of periods for EMA calculation (typically 21).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Trend Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="trend_bonus" value="<?php echo esc_attr($saved_config['trend_bonus'] ?? 20); ?>" min="5" max="50">
                                    <p class="setting-description"><?php esc_html_e('Bonus points when price is above EMA (bullish trend).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Distance Multiplier:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="distance_multiplier" value="<?php echo esc_attr($saved_config['distance_multiplier'] ?? 1.0); ?>" min="0.1" max="3.0" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Multiplier based on distance from EMA (1.0 = standard).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'cci') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="period" value="<?php echo esc_attr($saved_config['period'] ?? 20); ?>" min="5" max="50">
                                    <p class="setting-description"><?php esc_html_e('Number of periods for CCI calculation (typically 20).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Oversold Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="oversold" value="<?php echo esc_attr($saved_config['oversold'] ?? -100); ?>" min="-200" max="-50">
                                    <p class="setting-description"><?php esc_html_e('CCI value indicating oversold condition (typically -100).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Overbought Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="overbought" value="<?php echo esc_attr($saved_config['overbought'] ?? 100); ?>" min="50" max="200">
                                    <p class="setting-description"><?php esc_html_e('CCI value indicating overbought condition (typically 100).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'adx') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="period" value="<?php echo esc_attr($saved_config['period'] ?? 14); ?>" min="5" max="30">
                                    <p class="setting-description"><?php esc_html_e('Number of periods for ADX calculation (typically 14).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Strong Trend Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="strong_trend" value="<?php echo esc_attr($saved_config['strong_trend'] ?? 25); ?>" min="15" max="40">
                                    <p class="setting-description"><?php esc_html_e('ADX value indicating strong trend (typically 25).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Very Strong Trend Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="very_strong_trend" value="<?php echo esc_attr($saved_config['very_strong_trend'] ?? 40); ?>" min="30" max="60">
                                    <p class="setting-description"><?php esc_html_e('ADX value indicating very strong trend (typically 40).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Position Monitoring Section -->
                            <div class="position-monitoring-section">
                                <h4><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Position Monitoring', 'tradepress'); ?></h4>
                                <div class="setting-group">
                                    <label>
                                        <input type="checkbox" name="enable_position_monitoring" value="1" <?php checked($saved_config['enable_position_monitoring'] ?? false, true); ?>>
                                        <?php esc_html_e('Enable for existing positions', 'tradepress'); ?>
                                    </label>
                                    <p class="setting-description"><?php esc_html_e('Monitor existing positions using this directive logic.', 'tradepress'); ?></p>
                                </div>
                                
                                <div class="position-monitoring-config" style="<?php echo empty($saved_config['enable_position_monitoring']) ? 'display:none;' : ''; ?>">
                                    <div class="setting-group">
                                        <label><?php esc_html_e('Check Frequency:', 'tradepress'); ?></label>
                                        <div class="setting-control">
                                            <select name="check_frequency">
                                                <option value="1" <?php selected($saved_config['check_frequency'] ?? 15, 1); ?>><?php esc_html_e('Every minute', 'tradepress'); ?></option>
                                                <option value="5" <?php selected($saved_config['check_frequency'] ?? 15, 5); ?>><?php esc_html_e('Every 5 minutes', 'tradepress'); ?></option>
                                                <option value="15" <?php selected($saved_config['check_frequency'] ?? 15, 15); ?>><?php esc_html_e('Every 15 minutes', 'tradepress'); ?></option>
                                                <option value="60" <?php selected($saved_config['check_frequency'] ?? 15, 60); ?>><?php esc_html_e('Every hour', 'tradepress'); ?></option>
                                            </select>
                                            <p class="setting-description"><?php esc_html_e('How often to check existing positions.', 'tradepress'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="setting-group">
                                        <label><?php esc_html_e('Alert Threshold:', 'tradepress'); ?></label>
                                        <div class="setting-control">
                                            <input type="number" name="position_alert_threshold" value="<?php echo esc_attr($saved_config['position_alert_threshold'] ?? 20); ?>" min="10" max="50">
                                            <p class="setting-description"><?php esc_html_e('ADX value that triggers position alerts (trend weakening).', 'tradepress'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="setting-group">
                                        <label><?php esc_html_e('Action on Trigger:', 'tradepress'); ?></label>
                                        <div class="setting-control">
                                            <select name="trigger_action">
                                                <option value="alert" <?php selected($saved_config['trigger_action'] ?? 'alert', 'alert'); ?>><?php esc_html_e('Alert only', 'tradepress'); ?></option>
                                                <option value="reduce" <?php selected($saved_config['trigger_action'] ?? 'alert', 'reduce'); ?>><?php esc_html_e('Reduce position 50%', 'tradepress'); ?></option>
                                                <option value="close" <?php selected($saved_config['trigger_action'] ?? 'alert', 'close'); ?>><?php esc_html_e('Close position', 'tradepress'); ?></option>
                                                <option value="hedge" <?php selected($saved_config['trigger_action'] ?? 'alert', 'hedge'); ?>><?php esc_html_e('Add hedge position', 'tradepress'); ?></option>
                                            </select>
                                            <p class="setting-description"><?php esc_html_e('Action to take when alert threshold is reached.', 'tradepress'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hard Limits Section -->
                            <div class="hard-limits-section">
                                <h4><span class="dashicons dashicons-shield"></span> <?php esc_html_e('Trade Blocking (Hard Limits)', 'tradepress'); ?></h4>
                                <div class="setting-group">
                                    <label>
                                        <input type="checkbox" name="enable_hard_limit" value="1" <?php checked($saved_config['enable_hard_limit'] ?? false, true); ?>>
                                        <?php esc_html_e('Enable trade blocking for this directive', 'tradepress'); ?>
                                    </label>
                                    <p class="setting-description"><?php esc_html_e('Block trades when ADX indicates unfavorable conditions.', 'tradepress'); ?></p>
                                </div>
                                
                                <div class="hard-limit-config" style="<?php echo empty($saved_config['enable_hard_limit']) ? 'display:none;' : ''; ?>">
                                    <div class="setting-group">
                                        <label><?php esc_html_e('Block Threshold:', 'tradepress'); ?></label>
                                        <div class="setting-control">
                                            <input type="number" name="hard_limit_threshold" value="<?php echo esc_attr($saved_config['hard_limit_threshold'] ?? 15); ?>" min="5" max="30">
                                            <p class="setting-description"><?php esc_html_e('ADX value below which to block new trades (weak trend).', 'tradepress'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="setting-group">
                                        <label><?php esc_html_e('Block Duration:', 'tradepress'); ?></label>
                                        <div class="setting-control">
                                            <select name="block_duration">
                                                <option value="15" <?php selected($saved_config['block_duration'] ?? 60, 15); ?>><?php esc_html_e('15 minutes', 'tradepress'); ?></option>
                                                <option value="60" <?php selected($saved_config['block_duration'] ?? 60, 60); ?>><?php esc_html_e('1 hour', 'tradepress'); ?></option>
                                                <option value="240" <?php selected($saved_config['block_duration'] ?? 60, 240); ?>><?php esc_html_e('4 hours', 'tradepress'); ?></option>
                                                <option value="1440" <?php selected($saved_config['block_duration'] ?? 60, 1440); ?>><?php esc_html_e('24 hours', 'tradepress'); ?></option>
                                            </select>
                                            <p class="setting-description"><?php esc_html_e('How long to block trades after threshold is reached.', 'tradepress'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="setting-group">
                                        <label><?php esc_html_e('Block Scope:', 'tradepress'); ?></label>
                                        <div class="setting-control">
                                            <select name="block_scope">
                                                <option value="symbol" <?php selected($saved_config['block_scope'] ?? 'symbol', 'symbol'); ?>><?php esc_html_e('This symbol only', 'tradepress'); ?></option>
                                                <option value="portfolio" <?php selected($saved_config['block_scope'] ?? 'symbol', 'portfolio'); ?>><?php esc_html_e('Entire portfolio', 'tradepress'); ?></option>
                                            </select>
                                            <p class="setting-description"><?php esc_html_e('Scope of the trade block when triggered.', 'tradepress'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'bollinger_bands') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="period" value="<?php echo esc_attr($saved_config['period'] ?? 20); ?>" min="5" max="50">
                                    <p class="setting-description"><?php esc_html_e('Number of periods for moving average calculation (typically 20).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Standard Deviations:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="std_dev" value="<?php echo esc_attr($saved_config['std_dev'] ?? 2.0); ?>" min="1.0" max="3.0" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Number of standard deviations for band calculation (typically 2.0).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Squeeze Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="squeeze_threshold" value="<?php echo esc_attr($saved_config['squeeze_threshold'] ?? 0.1); ?>" min="0.05" max="0.3" step="0.01">
                                    <p class="setting-description"><?php esc_html_e('Band width threshold for squeeze detection (0.1 = 10% of price).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'news_sentiment_positive') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Sentiment Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="sentiment_threshold" value="<?php echo esc_attr($saved_config['sentiment_threshold'] ?? 0.6); ?>" min="0.1" max="1.0" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Minimum sentiment score to trigger positive signal (0.6 = 60% positive).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Lookback Days:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="lookback_days" value="<?php echo esc_attr($saved_config['lookback_days'] ?? 7); ?>" min="1" max="30">
                                    <p class="setting-description"><?php esc_html_e('Number of days to analyze news sentiment (typically 7).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Strong Sentiment Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="strong_sentiment_bonus" value="<?php echo esc_attr($saved_config['strong_sentiment_bonus'] ?? 25); ?>" min="10" max="50">
                                    <p class="setting-description"><?php esc_html_e('Bonus points for very positive sentiment (≥0.8).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('News Volume Multiplier:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="volume_multiplier" value="<?php echo esc_attr($saved_config['volume_multiplier'] ?? 1.5); ?>" min="1.0" max="3.0" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Score multiplier based on news volume (1.5 = 50% boost for high news volume).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'obv') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Trend Confirmation Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="trend_bonus" value="<?php echo esc_attr($saved_config['trend_bonus'] ?? 20); ?>" min="10" max="50">
                                    <p class="setting-description"><?php esc_html_e('Bonus points when OBV trend matches price trend.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Divergence Penalty:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="divergence_penalty" value="<?php echo esc_attr($saved_config['divergence_penalty'] ?? 15); ?>" min="5" max="30">
                                    <p class="setting-description"><?php esc_html_e('Points deducted when OBV diverges from price trend.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Volume Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="volume_threshold" value="<?php echo esc_attr($saved_config['volume_threshold'] ?? 1.2); ?>" min="1.0" max="3.0" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Minimum volume ratio for OBV calculation (1.2 = 20% above average).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'price_above_sma_50') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('SMA Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="sma_period" value="<?php echo esc_attr($saved_config['sma_period'] ?? 50); ?>" min="20" max="200">
                                    <p class="setting-description"><?php esc_html_e('Number of periods for SMA calculation (typically 50).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Distance Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="distance_bonus" value="<?php echo esc_attr($saved_config['distance_bonus'] ?? 15); ?>" min="5" max="30">
                                    <p class="setting-description"><?php esc_html_e('Bonus points based on distance above SMA.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Proximity Penalty:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="proximity_penalty" value="<?php echo esc_attr($saved_config['proximity_penalty'] ?? 10); ?>" min="5" max="25">
                                    <p class="setting-description"><?php esc_html_e('Points deducted when price is below SMA.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'rsi') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('RSI Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="period" value="<?php echo esc_attr($saved_config['period'] ?? 14); ?>" min="5" max="50">
                                    <p class="setting-description"><?php esc_html_e('Number of periods for RSI calculation (typically 14).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Oversold Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="oversold" value="<?php echo esc_attr($saved_config['oversold'] ?? 30); ?>" min="10" max="40">
                                    <p class="setting-description"><?php esc_html_e('RSI value indicating oversold condition (typically 30).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Overbought Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="overbought" value="<?php echo esc_attr($saved_config['overbought'] ?? 70); ?>" min="60" max="90">
                                    <p class="setting-description"><?php esc_html_e('RSI value indicating overbought condition (typically 70).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Extreme Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="extreme_bonus" value="<?php echo esc_attr($saved_config['extreme_bonus'] ?? 25); ?>" min="10" max="50">
                                    <p class="setting-description"><?php esc_html_e('Bonus points for extreme RSI values (<20 or >80).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            
                            <?php elseif ($configure_directive === 'rsi_overbought') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('RSI Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="period" value="<?php echo esc_attr($saved_config['period'] ?? 14); ?>" min="5" max="50">
                                    <p class="setting-description"><?php esc_html_e('Number of periods for RSI calculation (typically 14).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Overbought Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="overbought_threshold" value="<?php echo esc_attr($saved_config['overbought_threshold'] ?? 70); ?>" min="60" max="85">
                                    <p class="setting-description"><?php esc_html_e('RSI value indicating overbought condition (typically 70).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Extreme Overbought Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="extreme_overbought_bonus" value="<?php echo esc_attr($saved_config['extreme_overbought_bonus'] ?? 20); ?>" min="10" max="40">
                                    <p class="setting-description"><?php esc_html_e('Additional points when RSI > 80 (extreme overbought).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'support_resistance_levels') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Proximity Percentage:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="proximity_percent" value="<?php echo esc_attr($saved_config['proximity_percent'] ?? 1.0); ?>" min="0.1" max="5.0" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Percentage range for grouping nearby levels into zones (1.0 = 1%).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('High Confluence Minimum:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="highly_overlapped_min_methods" value="<?php echo esc_attr($saved_config['highly_overlapped_min_methods'] ?? 4); ?>" min="2" max="6">
                                    <p class="setting-description"><?php esc_html_e('Minimum methods required for high confluence zones (stronger levels).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Medium Confluence Minimum:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="well_overlapped_min_methods" value="<?php echo esc_attr($saved_config['well_overlapped_min_methods'] ?? 2); ?>" min="1" max="4">
                                    <p class="setting-description"><?php esc_html_e('Minimum methods required for medium confluence zones.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Fibonacci Lookback Days:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="fib_lookback_days" value="<?php echo esc_attr($saved_config['fib_lookback_days'] ?? 252); ?>" min="30" max="500">
                                    <p class="setting-description"><?php esc_html_e('Days to look back for Fibonacci retracement calculations (252 = 1 year).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Swing Lookback Period:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="swing_lookback" value="<?php echo esc_attr($saved_config['swing_lookback'] ?? 20); ?>" min="5" max="50">
                                    <p class="setting-description"><?php esc_html_e('Period for identifying swing highs and lows.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <?php elseif ($configure_directive === 'dividend_yield_attractive') : ?>
                            <div class="setting-group">
                                <label><?php esc_html_e('Minimum Yield Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="min_yield_threshold" value="<?php echo esc_attr($saved_config['min_yield_threshold'] ?? 2.0); ?>" min="0.5" max="10.0" step="0.1">
                                    <p class="setting-description"><?php esc_html_e('Minimum dividend yield percentage to consider attractive (2.0 = 2%).', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Sector Comparison Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="sector_comparison_bonus" value="<?php echo esc_attr($saved_config['sector_comparison_bonus'] ?? 20); ?>" min="5" max="50">
                                    <p class="setting-description"><?php esc_html_e('Bonus points when yield is above sector average.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('High Yield Bonus:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="high_yield_bonus" value="<?php echo esc_attr($saved_config['high_yield_bonus'] ?? 30); ?>" min="10" max="50">
                                    <p class="setting-description"><?php esc_html_e('Additional bonus for yields above 5%.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Yield Trap Penalty:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="yield_trap_penalty" value="<?php echo esc_attr($saved_config['yield_trap_penalty'] ?? 25); ?>" min="10" max="50">
                                    <p class="setting-description"><?php esc_html_e('Penalty for extremely high yields (>8%) that may indicate dividend cuts.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label><?php esc_html_e('Payout Ratio Threshold:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" name="payout_ratio_threshold" value="<?php echo esc_attr($saved_config['payout_ratio_threshold'] ?? 80); ?>" min="50" max="100">
                                    <p class="setting-description"><?php esc_html_e('Maximum acceptable payout ratio percentage for sustainability.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            

                            
                                <div class="directive-actions">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e('Save Configuration', 'tradepress'); ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                    

                </div>
            </div>
            </div>
            
            <!-- Testing & Symbols Section -->
            <?php if ($directive['active']) : ?>
            <div class="directive-details-container">
                <div class="directive-section">
                    <div class="section-header">
                        <h3><?php esc_html_e('Testing & Symbols', 'tradepress'); ?></h3>
                    </div>
                
                <div class="section-content">
                    <div class="directive-description" id="tradepress-testing-pointer-target">
                        <p><?php esc_html_e('Select a symbol and test the directive configuration with real market data.', 'tradepress'); ?></p>
                    </div>                    
                    <?php 
                    // Get symbols from custom post type
                    $symbols_query = new WP_Query(array(
                        'post_type' => 'symbols',
                        'post_status' => 'publish',
                        'posts_per_page' => 20,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ));
                    
                    $has_symbols = $symbols_query->have_posts();
                    ?>
                    <input type="hidden" name="trading_mode" value="long" form="test-form">
                    
                    <div class="symbols-section" style="margin-bottom: 15px;">
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;"><?php esc_html_e('Symbols in Use:', 'tradepress'); ?></label>
                        <?php if ($has_symbols) : ?>
                            <select name="test_symbol" style="width: 100%; padding: 5px;" form="test-form">
                                <?php while ($symbols_query->have_posts()) : $symbols_query->the_post(); ?>
                                    <?php 
                                    $ticker = get_post_meta(get_the_ID(), '_tradepress_ticker', true);
                                    $display_text = !empty($ticker) ? $ticker . ' - ' . get_the_title() : get_the_title();
                                    ?>
                                    <option value="<?php echo esc_attr(!empty($ticker) ? $ticker : get_the_title()); ?>"><?php echo esc_html($display_text); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <p style="margin: 5px 0; font-size: 12px; color: #666;">
                                <?php printf(esc_html__('%d symbols installed', 'tradepress'), $symbols_query->found_posts); ?>
                            </p>
                        <?php else : ?>
                            <p style="margin: 5px 0; color: #999; font-style: italic;">
                                <?php esc_html_e('No symbols installed', 'tradepress'); ?>
                            </p>
                        <?php endif; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                    
                    <form method="post" id="test-form">
                        <?php wp_nonce_field('tradepress_test_directive', 'test_nonce'); ?>
                        <input type="hidden" name="action" value="test_directive">
                        <input type="hidden" name="directive_id" value="<?php echo esc_attr($configure_directive); ?>">
                        
                        <div class="directive-actions">
                            <button type="submit" class="button" <?php echo !$has_symbols ? 'disabled' : ''; ?>>
                                <?php esc_html_e('Test', 'tradepress'); ?>
                            </button>
                        </div>
                    </form>
                        
                    <?php if (!$has_symbols) : ?>
                        <p style="margin: 10px 0 0 0; font-size: 12px;">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_symbols')); ?>">
                                <?php esc_html_e('Install symbol posts to enable testing', 'tradepress'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            </div>
            <?php endif; ?>
            
            <!-- Data Requirements Section -->
            <?php if ($directive['active']) : ?>
            <?php 
            // Load data requirements system
            if (!class_exists('TradePress_Directive_Data_Requirements')) {
                require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directive-data-requirements.php';
            }
            
            // Render data requirements container
            TradePress_Directive_Data_Requirements::render_requirements_container($configure_directive, $directive['name']);
            ?>
            <?php endif; ?>
            
            <!-- Scoring Information Section -->
            <?php if ($directive['active'] && in_array($configure_directive, ['adx', 'isa_reset', 'moving_averages', 'mfi', 'macd', 'ema', 'cci', 'bollinger_bands', 'rsi', 'volume', 'support_resistance_levels', 'news_sentiment_positive', 'obv', 'price_above_sma_50', 'rsi_overbought', 'dividend_yield_attractive', 'friday_positioning', 'monday_effect', 'volume_rhythm', 'institutional_timing', 'midweek_momentum', 'intraday_u_pattern', 'time_based_support'])) : ?>
            <div class="directive-details-container">
                <div class="directive-section">
                    <div class="section-header">
                        <h3><?php esc_html_e('Scoring Information', 'tradepress'); ?></h3>
                    </div>
                    
                    <div class="section-content">
                        <?php if ($configure_directive === 'rsi') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span id="rsi-max-score"><?php 
                                    $base_mult = $saved_config['base_multiplier'] ?? 0.5;
                                    $tier1 = $saved_config['bonus_tier_1'] ?? 20;
                                    $tier2 = $saved_config['bonus_tier_2'] ?? 40;
                                    $tier3 = $saved_config['bonus_tier_3'] ?? 80;
                                    echo round((100 * $base_mult) + $tier1 + $tier2 + $tier3, 1);
                                ?></span>
                                <p class="setting-description"><?php esc_html_e('Calculated maximum score with current settings.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 points (neutral starting position)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Oversold Bonus:', 'tradepress'); ?></label>
                                <span>+30 points when RSI ≤ <?php echo $saved_config['oversold'] ?? 30; ?></span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Overbought Penalty:', 'tradepress'); ?></label>
                                <span>-20 points when RSI ≥ <?php echo $saved_config['overbought'] ?? 70; ?></span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Extreme Bonus/Penalty:', 'tradepress'); ?></label>
                                <span>±<?php echo $saved_config['extreme_bonus'] ?? 25; ?> points for RSI ≤20 or ≥80</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Extreme Oversold (RSI = 15):', 'tradepress'); ?></label>
                                <span><?php 
                                    $extreme_bonus = $saved_config['extreme_bonus'] ?? 25;
                                    $example_score = 50 + 30 + $extreme_bonus;
                                    echo "50 + 30 + {$extreme_bonus} = {$example_score} points";
                                ?></span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Oversold (RSI = 25):', 'tradepress'); ?></label>
                                <span>50 + 30 = 80 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Overbought (RSI = 75):', 'tradepress'); ?></label>
                                <span>50 - 20 = 30 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Extreme Overbought (RSI = 85):', 'tradepress'); ?></label>
                                <span><?php 
                                    $extreme_bonus = $saved_config['extreme_bonus'] ?? 25;
                                    $example_score = 50 - 20 - $extreme_bonus;
                                    echo "50 - 20 - {$extreme_bonus} = {$example_score} points";
                                ?></span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'rsi_overbought') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span id="rsi-overbought-max-score"><?php 
                                    $extreme_bonus = $saved_config['extreme_overbought_bonus'] ?? 20;
                                    echo round(50 + 30 + $extreme_bonus, 1);
                                ?></span>
                                <p class="setting-description"><?php esc_html_e('Calculated maximum score with current settings.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Condition:', 'tradepress'); ?></label>
                                <span>No score if RSI < <?php echo $saved_config['overbought_threshold'] ?? 70; ?></span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Overbought Base:', 'tradepress'); ?></label>
                                <span>50 points when RSI ≥ <?php echo $saved_config['overbought_threshold'] ?? 70; ?></span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Intensity Bonus:', 'tradepress'); ?></label>
                                <span>Up to +30 points based on overbought intensity</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Extreme Bonus:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['extreme_overbought_bonus'] ?? 20; ?> points when RSI ≥ 80</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Moderate Overbought (RSI = 72):', 'tradepress'); ?></label>
                                <span>50 + (intensity bonus) ≈ 52 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Strong Overbought (RSI = 78):', 'tradepress'); ?></label>
                                <span>50 + (intensity bonus) ≈ 74 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Extreme Overbought (RSI = 85):', 'tradepress'); ?></label>
                                <span><?php 
                                    $extreme_bonus = $saved_config['extreme_overbought_bonus'] ?? 20;
                                    $example_score = 50 + 30 + $extreme_bonus;
                                    echo "50 + 30 + {$extreme_bonus} = {$example_score} points";
                                ?></span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'volume') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span id="volume-max-score"><?php 
                                    $base_mult = $saved_config['base_multiplier'] ?? 1.0;
                                    $high_bonus = $saved_config['high_volume_bonus'] ?? 25;
                                    $surge_bonus = $saved_config['surge_bonus'] ?? 50;
                                    // Max base assuming 10x volume ratio
                                    $max_base = 50 + (10 * 25 * $base_mult);
                                    echo $max_base + $high_bonus + $surge_bonus;
                                ?></span>
                                <p class="setting-description"><?php esc_html_e('Calculated maximum score with current settings.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 + ((volume_ratio - 1) × 25 × multiplier)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('High Volume Bonus:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['high_volume_bonus'] ?? 25; ?> points when volume ≥1.5x average</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Volume Surge Bonus:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['surge_bonus'] ?? 50; ?> additional points when volume ≥3.0x average</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('2x Average Volume:', 'tradepress'); ?></label>
                                <span><?php 
                                    $base_mult = $saved_config['base_multiplier'] ?? 1.0;
                                    $high_bonus = $saved_config['high_volume_bonus'] ?? 25;
                                    $example_score = 50 + (1 * 25 * $base_mult) + $high_bonus;
                                    echo "50 + (1 × 25 × {$base_mult}) + {$high_bonus} = {$example_score} points";
                                ?></span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('4x Average Volume:', 'tradepress'); ?></label>
                                <span><?php 
                                    $base_mult = $saved_config['base_multiplier'] ?? 1.0;
                                    $high_bonus = $saved_config['high_volume_bonus'] ?? 25;
                                    $surge_bonus = $saved_config['surge_bonus'] ?? 50;
                                    $example_score = 50 + (3 * 25 * $base_mult) + $high_bonus + $surge_bonus;
                                    echo "50 + (3 × 25 × {$base_mult}) + {$high_bonus} + {$surge_bonus} = {$example_score} points";
                                ?></span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'support_resistance_levels') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span id="sr-max-score">100</span>
                                <p class="setting-description"><?php esc_html_e('Fixed maximum score based on proximity to support/resistance levels.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 points (neutral starting position)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Near Support:', 'tradepress'); ?></label>
                                <span>+15 points when within 2% of strong support level</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Far from Resistance:', 'tradepress'); ?></label>
                                <span>+20 points when >5% below nearest resistance</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Above Resistance:', 'tradepress'); ?></label>
                                <span>-15 points when price breaks above resistance</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Analysis Methods', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Technical Methods:', 'tradepress'); ?></label>
                                <span>Swing Highs/Lows, Moving Averages, Fibonacci, Pivot Points, Psychological Levels</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Confluence Zones:', 'tradepress'); ?></label>
                                <span>High: <?php echo $saved_config['highly_overlapped_min_methods'] ?? 4; ?>+ methods | Medium: <?php echo $saved_config['well_overlapped_min_methods'] ?? 2; ?>+ methods</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'news_sentiment_positive') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span id="sentiment-max-score"><?php 
                                    $strong_bonus = $saved_config['strong_sentiment_bonus'] ?? 25;
                                    $volume_mult = $saved_config['volume_multiplier'] ?? 1.5;
                                    echo round((100 + $strong_bonus) * $volume_mult, 1);
                                ?></span>
                                <p class="setting-description"><?php esc_html_e('Calculated maximum score with current settings.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>(sentiment - threshold) / (1.0 - threshold) × 100</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Strong Sentiment Bonus:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['strong_sentiment_bonus'] ?? 25; ?> points when sentiment ≥ 0.8</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('News Volume Multiplier:', 'tradepress'); ?></label>
                                <span>×<?php echo $saved_config['volume_multiplier'] ?? 1.5; ?> when news count ≥ 5 articles</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Moderate Positive (0.7 sentiment, 3 articles):', 'tradepress'); ?></label>
                                <span><?php 
                                    $threshold = $saved_config['sentiment_threshold'] ?? 0.6;
                                    $example_score = (0.7 - $threshold) / (1.0 - $threshold) * 100;
                                    echo "(0.7 - {$threshold}) / 0.4 × 100 = " . round($example_score, 1) . " points";
                                ?></span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Strong Positive (0.85 sentiment, 8 articles):', 'tradepress'); ?></label>
                                <span><?php 
                                    $threshold = $saved_config['sentiment_threshold'] ?? 0.6;
                                    $strong_bonus = $saved_config['strong_sentiment_bonus'] ?? 25;
                                    $volume_mult = $saved_config['volume_multiplier'] ?? 1.5;
                                    $base_score = (0.85 - $threshold) / (1.0 - $threshold) * 100;
                                    $final_score = ($base_score + $strong_bonus) * $volume_mult;
                                    echo "((" . round($base_score, 1) . " + {$strong_bonus}) × {$volume_mult}) = " . round($final_score, 1) . " points";
                                ?></span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'obv') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span id="obv-max-score"><?php 
                                    $trend_bonus = $saved_config['trend_bonus'] ?? 20;
                                    echo round((50 + $trend_bonus) * 1.2, 1);
                                ?></span>
                                <p class="setting-description"><?php esc_html_e('Calculated maximum score with current settings.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 points (neutral starting position)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Trend Confirmation:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['trend_bonus'] ?? 20; ?> points when OBV matches price trend</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Divergence Penalty:', 'tradepress'); ?></label>
                                <span>-<?php echo $saved_config['divergence_penalty'] ?? 15; ?> points when OBV diverges from price</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Volume Boost:', 'tradepress'); ?></label>
                                <span>×1.2 when volume ≥ <?php echo $saved_config['volume_threshold'] ?? 1.2; ?>x average</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Trend Confirmation (normal volume):', 'tradepress'); ?></label>
                                <span><?php 
                                    $trend_bonus = $saved_config['trend_bonus'] ?? 20;
                                    $example_score = 50 + $trend_bonus;
                                    echo "50 + {$trend_bonus} = {$example_score} points";
                                ?></span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Trend Confirmation (high volume):', 'tradepress'); ?></label>
                                <span><?php 
                                    $trend_bonus = $saved_config['trend_bonus'] ?? 20;
                                    $example_score = (50 + $trend_bonus) * 1.2;
                                    echo "(50 + {$trend_bonus}) × 1.2 = " . round($example_score, 1) . " points";
                                ?></span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Divergence (normal volume):', 'tradepress'); ?></label>
                                <span><?php 
                                    $divergence_penalty = $saved_config['divergence_penalty'] ?? 15;
                                    $example_score = 50 - $divergence_penalty;
                                    echo "50 - {$divergence_penalty} = {$example_score} points";
                                ?></span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'price_above_sma_50') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span id="sma-max-score"><?php 
                                    $distance_bonus = $saved_config['distance_bonus'] ?? 15;
                                    echo round(50 + $distance_bonus + ($distance_bonus * 0.5), 1);
                                ?></span>
                                <p class="setting-description"><?php esc_html_e('Calculated maximum score with current settings.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 points (neutral starting position)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Above SMA Bonus:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['distance_bonus'] ?? 15; ?> points when price > SMA</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Below SMA Penalty:', 'tradepress'); ?></label>
                                <span>-<?php echo $saved_config['proximity_penalty'] ?? 10; ?> points when price < SMA</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Distance Multiplier:', 'tradepress'); ?></label>
                                <span>+50% bonus/penalty when >5% distance from SMA</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Price 2% above SMA:', 'tradepress'); ?></label>
                                <span><?php 
                                    $distance_bonus = $saved_config['distance_bonus'] ?? 15;
                                    $example_score = 50 + $distance_bonus;
                                    echo "50 + {$distance_bonus} = {$example_score} points";
                                ?></span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Price 8% above SMA:', 'tradepress'); ?></label>
                                <span><?php 
                                    $distance_bonus = $saved_config['distance_bonus'] ?? 15;
                                    $extra_bonus = round($distance_bonus * 0.5, 1);
                                    $example_score = 50 + $distance_bonus + $extra_bonus;
                                    echo "50 + {$distance_bonus} + {$extra_bonus} = {$example_score} points";
                                ?></span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Price 3% below SMA:', 'tradepress'); ?></label>
                                <span><?php 
                                    $proximity_penalty = $saved_config['proximity_penalty'] ?? 10;
                                    $example_score = 50 - $proximity_penalty;
                                    echo "50 - {$proximity_penalty} = {$example_score} points";
                                ?></span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'adx') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Fixed maximum score based on trend strength.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Weak Trend (ADX < 25):', 'tradepress'); ?></label>
                                <span>20-40 points (proportional to ADX value)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Strong Trend (ADX ≥ 25):', 'tradepress'); ?></label>
                                <span>50-80 points based on trend strength</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Very Strong Trend (ADX ≥ 40):', 'tradepress'); ?></label>
                                <span>80-100 points for extreme trend strength</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('ADX = 15 (Weak):', 'tradepress'); ?></label>
                                <span>30 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('ADX = 30 (Strong):', 'tradepress'); ?></label>
                                <span>65 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('ADX = 50 (Very Strong):', 'tradepress'); ?></label>
                                <span>90 points</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'isa_reset') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span><?php echo $saved_config['score_impact'] ?? 10; ?></span>
                                <p class="setting-description"><?php esc_html_e('Fixed bonus points during ISA reset period.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Outside ISA Period:', 'tradepress'); ?></label>
                                <span>0 points (no impact)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('ISA Reset Period:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['score_impact'] ?? 10; ?> points bonus</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Active Period:', 'tradepress'); ?></label>
                                <span><?php echo $saved_config['days_before'] ?? 3; ?> days before + <?php echo $saved_config['days_after'] ?? 3; ?> days after April 6th</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Calendar Impact', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Next ISA Reset:', 'tradepress'); ?></label>
                                <span><?php echo date('F j, Y', strtotime('April 6, ' . (date('n') >= 4 && date('j') > 5 ? date('Y') + 1 : date('Y')))); ?></span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Active Dates:', 'tradepress'); ?></label>
                                <span>April <?php echo 6 - ($saved_config['days_before'] ?? 3); ?> - April <?php echo 6 + ($saved_config['days_after'] ?? 3); ?></span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'moving_averages') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span><?php echo 50 + ($saved_config['alignment_bonus'] ?? 25); ?></span>
                                <p class="setting-description"><?php esc_html_e('Base score plus alignment bonus.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 points (neutral position)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Bullish Alignment:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['alignment_bonus'] ?? 25; ?> points when price > short MA > long MA</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('MA Periods:', 'tradepress'); ?></label>
                                <span>Short: <?php echo $saved_config['short_period'] ?? 20; ?> | Long: <?php echo $saved_config['long_period'] ?? 50; ?> (<?php echo $saved_config['ma_type'] ?? 'SMA'; ?>)</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Perfect Alignment:', 'tradepress'); ?></label>
                                <span><?php echo 50 + ($saved_config['alignment_bonus'] ?? 25); ?> points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Neutral/Mixed:', 'tradepress'); ?></label>
                                <span>50 points</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'mfi') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score for extreme oversold conditions.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Oversold (MFI ≤ 20):', 'tradepress'); ?></label>
                                <span>70-100 points (higher score for lower MFI)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Neutral (20 < MFI < 80):', 'tradepress'); ?></label>
                                <span>30-70 points (proportional)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Overbought (MFI ≥ 80):', 'tradepress'); ?></label>
                                <span>0-30 points (penalty for overbought)</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('MFI = 15 (Oversold):', 'tradepress'); ?></label>
                                <span>85 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('MFI = 50 (Neutral):', 'tradepress'); ?></label>
                                <span>50 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('MFI = 85 (Overbought):', 'tradepress'); ?></label>
                                <span>25 points</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'macd') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span><?php echo 50 + ($saved_config['crossover_bonus'] ?? 30); ?></span>
                                <p class="setting-description"><?php esc_html_e('Base score plus crossover bonus.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 points (neutral position)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Bullish Crossover:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['crossover_bonus'] ?? 30; ?> points when MACD crosses above signal</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('MACD Parameters:', 'tradepress'); ?></label>
                                <span>Fast: <?php echo $saved_config['fast_period'] ?? 12; ?> | Slow: <?php echo $saved_config['slow_period'] ?? 26; ?> | Signal: <?php echo $saved_config['signal_period'] ?? 9; ?></span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Bullish Crossover:', 'tradepress'); ?></label>
                                <span><?php echo 50 + ($saved_config['crossover_bonus'] ?? 30); ?> points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('No Clear Signal:', 'tradepress'); ?></label>
                                <span>50 points</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'ema') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span><?php echo round(50 + ($saved_config['trend_bonus'] ?? 20) * ($saved_config['distance_multiplier'] ?? 1.0) * 1.5, 1); ?></span>
                                <p class="setting-description"><?php esc_html_e('Base plus trend bonus with distance multiplier.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 points (neutral position)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Above EMA Bonus:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['trend_bonus'] ?? 20; ?> points when price > EMA</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Distance Multiplier:', 'tradepress'); ?></label>
                                <span>×<?php echo $saved_config['distance_multiplier'] ?? 1.0; ?> based on distance from EMA</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Price 2% above EMA:', 'tradepress'); ?></label>
                                <span><?php echo 50 + ($saved_config['trend_bonus'] ?? 20); ?> points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Price 5% above EMA:', 'tradepress'); ?></label>
                                <span><?php echo round(50 + ($saved_config['trend_bonus'] ?? 20) * ($saved_config['distance_multiplier'] ?? 1.0) * 1.3, 1); ?> points</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'cci') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score for extreme oversold conditions.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Oversold (CCI ≤ -100):', 'tradepress'); ?></label>
                                <span>70-100 points (higher score for lower CCI)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Neutral (-100 < CCI < 100):', 'tradepress'); ?></label>
                                <span>30-70 points (proportional)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Overbought (CCI ≥ 100):', 'tradepress'); ?></label>
                                <span>0-30 points (penalty for overbought)</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('CCI = -150 (Oversold):', 'tradepress'); ?></label>
                                <span>85 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('CCI = 0 (Neutral):', 'tradepress'); ?></label>
                                <span>50 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('CCI = 150 (Overbought):', 'tradepress'); ?></label>
                                <span>25 points</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'bollinger_bands') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score for optimal band conditions.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Near Lower Band:', 'tradepress'); ?></label>
                                <span>70-100 points (oversold opportunity)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Band Squeeze:', 'tradepress'); ?></label>
                                <span>+20 points bonus for low volatility</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Near Upper Band:', 'tradepress'); ?></label>
                                <span>0-30 points (overbought warning)</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Price at Lower Band + Squeeze:', 'tradepress'); ?></label>
                                <span>90 + 20 = 110 points (capped at 100)</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Price in Middle:', 'tradepress'); ?></label>
                                <span>50 points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Price at Upper Band:', 'tradepress'); ?></label>
                                <span>20 points</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'dividend_yield_attractive') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span><?php 
                                    $sector_bonus = $saved_config['sector_comparison_bonus'] ?? 20;
                                    $high_yield_bonus = $saved_config['high_yield_bonus'] ?? 30;
                                    echo 50 + $sector_bonus + $high_yield_bonus;
                                ?></span>
                                <p class="setting-description"><?php esc_html_e('Base score plus sector and high yield bonuses.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Formula', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Base Score:', 'tradepress'); ?></label>
                                <span>50 points (neutral position)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Above Sector Average:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['sector_comparison_bonus'] ?? 20; ?> points when yield > sector average</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('High Yield Bonus:', 'tradepress'); ?></label>
                                <span>+<?php echo $saved_config['high_yield_bonus'] ?? 30; ?> points when yield > 5%</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Yield Trap Penalty:', 'tradepress'); ?></label>
                                <span>-<?php echo $saved_config['yield_trap_penalty'] ?? 25; ?> points when yield > 8% or payout ratio > <?php echo $saved_config['payout_ratio_threshold'] ?? 80; ?>%</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Example Scenarios', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Quality Dividend (4% yield, 60% payout):', 'tradepress'); ?></label>
                                <span><?php echo 50 + ($saved_config['sector_comparison_bonus'] ?? 20); ?> points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('High Yield (6% yield, 70% payout):', 'tradepress'); ?></label>
                                <span><?php echo 50 + ($saved_config['sector_comparison_bonus'] ?? 20) + ($saved_config['high_yield_bonus'] ?? 30); ?> points</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Yield Trap (10% yield, 95% payout):', 'tradepress'); ?></label>
                                <span><?php echo 50 - ($saved_config['yield_trap_penalty'] ?? 25); ?> points</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'institutional_timing') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score based on institutional timing patterns.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('End-of-Period Buying:', 'tradepress'); ?></label>
                                <span>+40 points for month/quarter-end accumulation patterns</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Rebalancing Flows:', 'tradepress'); ?></label>
                                <span>+30 points for positive institutional rebalancing</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Tax-Loss Selling:', 'tradepress'); ?></label>
                                <span>-25 points during December tax-loss selling period</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('January Effect:', 'tradepress'); ?></label>
                                <span>+35 points for January recovery patterns</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Analysis Focus', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Calendar Effects:', 'tradepress'); ?></label>
                                <span>Month-end, quarter-end, year-end rebalancing patterns</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Best Suited For:', 'tradepress'); ?></label>
                                <span>Large-cap index components (AAPL, MSFT) with heavy institutional ownership</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Seasonal Patterns:', 'tradepress'); ?></label>
                                <span>December selling, January effect, pension fund flows</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Update Frequency:', 'tradepress'); ?></label>
                                <span>Monthly/Quarterly analysis</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'time_based_support') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score based on VWAP and time-sensitive support levels.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Above VWAP:', 'tradepress'); ?></label>
                                <span>+30 points when price trades above VWAP</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Near VWAP Support:', 'tradepress'); ?></label>
                                <span>+25 points when price within 1% of VWAP support</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Time-Based Levels:', 'tradepress'); ?></label>
                                <span>+20 points for confluence with hourly/daily pivots</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Volume Confirmation:', 'tradepress'); ?></label>
                                <span>+25 points when supported by above-average volume</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Analysis Focus', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('VWAP Analysis:', 'tradepress'); ?></label>
                                <span>Volume-weighted average price as institutional benchmark</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Best Suited For:', 'tradepress'); ?></label>
                                <span>High-volume day trading stocks (SPY, QQQ, TSLA)</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Data Requirements:', 'tradepress'); ?></label>
                                <span>Real-time intraday data (expensive API calls)</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Update Frequency:', 'tradepress'); ?></label>
                                <span>Hourly during market hours</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Cost Optimization:', 'tradepress'); ?></label>
                                <span>Consider webhook implementation for 95% API cost reduction</span>
                            </div>
                        </div>
                        <?php elseif ($configure_directive === 'friday_positioning') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score based on Friday positioning patterns.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Strong Friday Close:', 'tradepress'); ?></label>
                                <span>70-100 points for institutional accumulation patterns</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Weak Friday Close:', 'tradepress'); ?></label>
                                <span>20-40 points for weekend risk reduction patterns</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Volume Confirmation:', 'tradepress'); ?></label>
                                <span>+20 points bonus when supported by volume</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Analysis Focus', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Institutional Behavior:', 'tradepress'); ?></label>
                                <span>End-of-week positioning and weekend risk management</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Best Suited For:', 'tradepress'); ?></label>
                                <span>Large-cap stocks (JPM, BAC) with institutional dominance</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Update Frequency:', 'tradepress'); ?></label>
                                <span>Weekly analysis (Friday close)</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'midweek_momentum') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>150</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score based on midweek momentum patterns.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Volume Strength (>20% above avg):', 'tradepress'); ?></label>
                                <span>+30 points for strong midweek volume</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Volatility Patterns (>10% higher):', 'tradepress'); ?></label>
                                <span>+25 points for higher midweek volatility</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Price Momentum (>1% positive):', 'tradepress'); ?></label>
                                <span>+35 points for positive midweek returns</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Slight Momentum (>0% positive):', 'tradepress'); ?></label>
                                <span>+15 points for slight midweek momentum</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Analysis Focus', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Institutional Activity:', 'tradepress'); ?></label>
                                <span>Tuesday-Thursday professional trader patterns</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Best Suited For:', 'tradepress'); ?></label>
                                <span>Large-cap stocks (AAPL, MSFT) with institutional dominance</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Analysis Period:', 'tradepress'); ?></label>
                                <span>4 weeks of midweek performance data</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Update Frequency:', 'tradepress'); ?></label>
                                <span>Daily updates Tuesday-Thursday</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'volume_rhythm') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score based on volume rhythm patterns.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('High Volume on Up Days:', 'tradepress'); ?></label>
                                <span>+40 points for institutional accumulation patterns</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Low Volume on Down Days:', 'tradepress'); ?></label>
                                <span>+25 points for lack of selling pressure</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Volume Anomalies:', 'tradepress'); ?></label>
                                <span>+20 points for unusual day-of-week patterns</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Distribution Patterns:', 'tradepress'); ?></label>
                                <span>-30 points for high volume on down days</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Analysis Focus', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Pattern Detection:', 'tradepress'); ?></label>
                                <span>Day-of-week volume and institutional trading rhythms</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Best Suited For:', 'tradepress'); ?></label>
                                <span>High-volume stocks (SPY, QQQ) with clear institutional patterns</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Key Insights:', 'tradepress'); ?></label>
                                <span>Monday/Friday rebalancing, Wednesday low participation</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Update Frequency:', 'tradepress'); ?></label>
                                <span>Weekly pattern analysis</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'intraday_u_pattern') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score for optimal U-pattern detection.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Morning Volume Surge (>1.5x avg):', 'tradepress'); ?></label>
                                <span>+25 points for strong morning activity</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Afternoon Recovery (>1% gain):', 'tradepress'); ?></label>
                                <span>+35 points for afternoon momentum</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Classic U-Pattern (>0.7 strength):', 'tradepress'); ?></label>
                                <span>+40 points for confirmed pattern</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Pattern Requirements', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Data Requirements:', 'tradepress'); ?></label>
                                <span>Requires expensive intraday data (1min/5min candles)</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('API Platforms:', 'tradepress'); ?></label>
                                <span>Finnhub (Candles) or Alpaca (Real-time Bars)</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Best Suited For:', 'tradepress'); ?></label>
                                <span>High-volume day trading stocks (SPY, QQQ, TSLA)</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Cost Optimization:', 'tradepress'); ?></label>
                                <span>Consider webhook implementation for 95% API cost reduction</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Current Status:', 'tradepress'); ?></label>
                                <span>Placeholder implementation - requires intraday data feeds</span>
                            </div>
                        </div>
                        
                        <?php elseif ($configure_directive === 'monday_effect') : ?>
                        <div class="setting-group">
                            <label><?php esc_html_e('Maximum Possible Score:', 'tradepress'); ?></label>
                            <div class="setting-control">
                                <span>100</span>
                                <p class="setting-description"><?php esc_html_e('Maximum score based on Monday effect patterns.', 'tradepress'); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-conditions">
                            <h4><?php esc_html_e('Scoring Logic', 'tradepress'); ?></h4>
                            <div class="condition-group">
                                <label><?php esc_html_e('Strong Monday Weakness (<-0.5%):', 'tradepress'); ?></label>
                                <span>-30 points (Monday Effect detected)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Moderate Monday Weakness (<-0.2%):', 'tradepress'); ?></label>
                                <span>-18 points (Mild Monday Effect)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Positive Monday Bias (>0.3%):', 'tradepress'); ?></label>
                                <span>+24 points (Contrarian opportunity)</span>
                            </div>
                            <div class="condition-group">
                                <label><?php esc_html_e('Win Rate Adjustments:', 'tradepress'); ?></label>
                                <span>±15 points based on Monday success rate</span>
                            </div>
                        </div>
                        
                        <div class="directive-examples">
                            <h4><?php esc_html_e('Analysis Focus', 'tradepress'); ?></h4>
                            <div class="example-group">
                                <label><?php esc_html_e('Market Phenomenon:', 'tradepress'); ?></label>
                                <span>Weekend news accumulation and sentiment shifts</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Best Suited For:', 'tradepress'); ?></label>
                                <span>Retail-heavy stocks (TSLA, AMC) and quality names (AAPL, MSFT)</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Analysis Period:', 'tradepress'); ?></label>
                                <span>12 weeks of Monday performance data</span>
                            </div>
                            <div class="example-group">
                                <label><?php esc_html_e('Update Frequency:', 'tradepress'); ?></label>
                                <span>Weekly analysis (Monday morning)</span>
                            </div>
                        </div>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            


        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle API Call button
    $('.view-api-call').on('click', function() {
        var directive = $(this).data('directive');
        
        // AJAX call to get recent API calls for this directive
        $.post(ajaxurl, {
            action: 'tradepress_get_api_calls',
            directive: directive,
            nonce: '<?php echo wp_create_nonce('tradepress_api_calls'); ?>'
        })
        .done(function(response) {
            if (response.success) {
                showApiCallModal(directive, response.data);
            } else {
                alert('No recent API calls found for ' + directive + ' directive.');
            }
        })
        .fail(function(xhr, status, error) {
            console.log('AJAX Error:', status, error);
            alert('Error loading API calls: ' + error);
        });
    });
    
    function showApiCallModal(directive, calls) {
        var modal = '<div id="api-call-modal" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 10000; max-width: 80%; max-height: 80%; overflow-y: auto;">';
        modal += '<h3>Recent API Calls - ' + directive.toUpperCase() + '</h3>';
        modal += '<div style="margin-bottom: 15px;">';
        
        if (calls.length === 0) {
            modal += '<p>No recent API calls found.</p>';
        } else {
            calls.forEach(function(call, index) {
                modal += '<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 4px;">';
                modal += '<strong>Call #' + (index + 1) + '</strong><br>';
                modal += '<strong>Platform:</strong> ' + call.platform + '<br>';
                modal += '<strong>Method:</strong> ' + call.method + '<br>';
                modal += '<strong>Parameters:</strong> ' + JSON.stringify(call.parameters) + '<br>';
                modal += '<strong>Timestamp:</strong> ' + call.timestamp + '<br>';
                modal += '<strong>Age:</strong> ' + call.age_minutes + ' minutes ago';
                modal += '</div>';
            });
        }
        
        modal += '</div>';
        modal += '<button onclick="closeApiCallModal()" class="button">Close</button>';
        modal += '</div>';
        modal += '<div id="api-call-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;" onclick="closeApiCallModal()"></div>';
        
        $('body').append(modal);
    }
    
    function showApiResponseModal(directive, calls) {
        var modal = '<div id="api-response-modal" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 10000; max-width: 90%; max-height: 80%; overflow-y: auto;">';
        modal += '<h3>Recent API Responses - ' + directive.toUpperCase() + '</h3>';
        modal += '<div style="margin-bottom: 15px;">';
        
        if (calls.length === 0) {
            modal += '<p>No recent API responses found.</p>';
        } else {
            calls.forEach(function(call, index) {
                modal += '<div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 4px;">';
                modal += '<strong>Response #' + (index + 1) + ' (' + call.platform + ' - ' + call.method + ')</strong><br>';
                modal += '<strong>Timestamp:</strong> ' + call.timestamp + ' (' + call.age_minutes + ' minutes ago)<br><br>';
                modal += '<strong>Response Data:</strong><br>';
                modal += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow-y: auto;">' + JSON.stringify(call.response, null, 2) + '</pre>';
                modal += '</div>';
            });
        }
        
        modal += '</div>';
        modal += '<button onclick="closeApiResponseModal()" class="button">Close</button>';
        modal += '</div>';
        modal += '<div id="api-response-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;" onclick="closeApiResponseModal()"></div>';
        
        $('body').append(modal);
    }
    
    window.closeApiCallModal = function() {
        $('#api-call-modal, #api-call-overlay').remove();
    };
    
    window.closeApiResponseModal = function() {
        $('#api-response-modal, #api-response-overlay').remove();
    };
    
    function showDirectiveOutcomeModal(directive, data) {
        var modal = '<div id="directive-outcome-modal" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 10000; max-width: 90%; max-height: 80%; overflow-y: auto;">';
        modal += '<h3>Directive Outcome - ' + directive.toUpperCase() + '</h3>';
        modal += '<div style="margin-bottom: 15px;">';
        
        // Show explanation
        modal += '<h4>How This Directive Works:</h4>';
        modal += '<pre style="background: #f9f9f9; padding: 15px; border-radius: 4px; white-space: pre-wrap; font-family: monospace; font-size: 13px; line-height: 1.4;">' + data.explanation + '</pre>';
        
        // Show outcome
        modal += '<h4>Sample Calculation Results:</h4>';
        modal += '<div style="background: #f0f8ff; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">';
        modal += '<strong>Symbol:</strong> ' + data.symbol + '<br>';
        modal += '<strong>Trading Mode:</strong> ' + data.trading_mode + '<br>';
        modal += '<strong>Score:</strong> ' + data.outcome.score + '<br>';
        if (data.outcome.rsi_value) {
            modal += '<strong>RSI Value:</strong> ' + data.outcome.rsi_value + '<br>';
        }
        if (data.outcome.signal_type) {
            modal += '<strong>Signal Type:</strong> ' + data.outcome.signal_type.replace('_', ' ') + '<br>';
        }
        modal += '</div>';
        
        modal += '</div>';
        modal += '<button onclick="closeDirectiveOutcomeModal()" class="button">Close</button>';
        modal += '</div>';
        modal += '<div id="directive-outcome-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;" onclick="closeDirectiveOutcomeModal()"></div>';
        
        $('body').append(modal);
    }
    
    window.closeDirectiveOutcomeModal = function() {
        $('#directive-outcome-modal, #directive-outcome-overlay').remove();
    };
    
    // Handle Response button
    $('.view-api-response').on('click', function() {
        var directive = $(this).data('directive');
        
        // AJAX call to get recent API calls for this directive
        $.post(ajaxurl, {
            action: 'tradepress_get_api_calls',
            directive: directive,
            nonce: '<?php echo wp_create_nonce('tradepress_api_calls'); ?>'
        })
        .done(function(response) {
            if (response.success) {
                showApiResponseModal(directive, response.data);
            } else {
                alert('No recent API responses found for ' + directive + ' directive.');
            }
        })
        .fail(function(xhr, status, error) {
            console.log('AJAX Error:', status, error);
            alert('Error loading API responses: ' + error);
        });
    });
    
    // Handle Outcome button
    $('.view-directive-outcome').on('click', function() {
        var directive = $(this).data('directive');
        var symbol = $('select[name="test_symbol"]').val() || 'AAPL';
        var trading_mode = $('select[name="trading_mode"]').val() || 'long';
        
        $.post(ajaxurl, {
            action: 'tradepress_get_directive_outcome',
            directive: directive,
            symbol: symbol,
            trading_mode: trading_mode,
            nonce: '<?php echo wp_create_nonce('tradepress_directive_outcome'); ?>'
        })
        .done(function(response) {
            if (response.success) {
                showDirectiveOutcomeModal(directive, response.data);
            } else {
                alert('Error loading directive outcome: ' + response.data);
            }
        })
        .fail(function(xhr, status, error) {
            alert('Error loading directive outcome: ' + error);
        });
    });
    
    // Check for selected directive from URL and open accordion
    var urlParams = new URLSearchParams(window.location.search);
    var selectedDirective = urlParams.get('configure');
    
    if (selectedDirective) {
        $('.accordion-row').each(function() {
            var configureLink = $(this).find('a[href*="configure=' + selectedDirective + '"]');
            if (configureLink.length > 0) {
                $(this).find('.accordion-content').addClass('active').show();
                $(this).find('.accordion-header').addClass('active');
            }
        });
    }
    
    // Search functionality
    $('#directive-search-input').on('keyup', function(e) {
        if (e.keyCode === 13) { // Enter key
            performSearch();
        }
    });
    
    $('#search-submit').on('click', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    function performSearch() {
        var searchTerm = $('#directive-search-input').val();
        var url = new URL(window.location);
        if (searchTerm) {
            url.searchParams.set('s', searchTerm);
        } else {
            url.searchParams.delete('s');
        }
        window.location.href = url.toString();
    }
    
    // Position monitoring toggle
    $('input[name="enable_position_monitoring"]').on('change', function() {
        var $config = $(this).closest('.position-monitoring-section').find('.position-monitoring-config');
        if ($(this).is(':checked')) {
            $config.slideDown();
        } else {
            $config.slideUp();
        }
    });
    
    // Hard limits toggle
    $('input[name="enable_hard_limit"]').on('change', function() {
        var $config = $(this).closest('.hard-limits-section').find('.hard-limit-config');
        if ($(this).is(':checked')) {
            $config.slideDown();
        } else {
            $config.slideUp();
        }
    });
    
    // Accordion functionality with URL update
    $('.accordion-header').on('click', function() {
        var $content = $(this).next('.accordion-content');
        var isActive = $content.hasClass('active');
        
        // Extract directive key from configure link
        var configureLink = $(this).closest('.accordion-row').find('a[href*="configure="]');
        var directiveKey = '';
        if (configureLink.length > 0) {
            var href = configureLink.attr('href');
            var match = href.match(/configure=([^&]+)/);
            if (match) {
                directiveKey = match[1];
            }
        }
        
        // Close all accordions
        $('.accordion-content').removeClass('active').slideUp();
        $('.accordion-header').removeClass('active');
        
        // Open clicked accordion if it wasn't active and update URL
        if (!isActive && directiveKey) {
            $content.addClass('active').slideDown();
            $(this).addClass('active');
            
            // Update URL with configure parameter
            var url = new URL(window.location);
            url.searchParams.set('configure', directiveKey);
            window.location.href = url.toString();
        }
    });
});
</script>

