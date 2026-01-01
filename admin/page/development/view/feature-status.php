<?php
/**
 * TradePress Development Feature Status Tab
 *
 * Provides feature status tracking and management in the Development area.
 * This tab is specifically designed to monitor the development progress of features
 * throughout the plugin, with a focus on tracking the transition from demo to live implementations.
 *
 * @package TradePress\Admin\development
 * @version 1.0.1
 * @date    2024-06-25
 * 
 * DATA CONSUMPTION:
 * ----------------
 * This feature status tab consumes data from TradePress_Development::get_ui_compatible_feature_data(),
 * which is the central repository for all feature definitions. The data structure is transformed
 * into a UI-friendly format with pages, tabs, and abilities.
 *
 * RELATED COMPONENTS:
 * -----------------
 * The feature data displayed here is also used by:
 * 1. Development Roadmap tab - For timeline visualization
 * 2. Plugin Settings > Features - For feature toggles
 * 3. TradePress initialization - For conditional feature loading
 * 4. Development Reports - For progress tracking
 * 
 * @ai-guidance
 * Feature Status Tab AI Usage Guidelines:
 * 1. PRIMARY PURPOSE: Track development progress of all plugin features
 * 2. TRANSITION TRACKING: Monitor the transition from demo/placeholder content to live functionality
 * 3. PRIORITIZATION: Use this tab to identify which features need attention for completion
 * 4. STATUS UPDATES: Update feature statuses here as development progresses
 * 5. DOCUMENTATION: Record implementation notes and decisions for each feature
 * 6. NO CONTROLS: This is an information-only view - no enable/disable functionality
 * 7. ENHANCEMENT FOCUS: Prioritize converting "Demo" features to "Live" status
 * 
 * DEVELOPMENT GUIDELINES:
 * ----------------------
 * 1. STATUS DEFINITIONS:
 *    - "Demo": Placeholder implementation or mock functionality that simulates the intended feature
 *    - "Live": Fully implemented, tested, and production-ready feature
 * 
 * 2. UPDATING STATUS:
 *    - Status data is retrieved from TradePress_Features_Data::get_features_data()
 *    - To change a feature's status, update its 'status' value in that method
 *    - Status changes should be documented in commits with appropriate notes
 * 
 * 3. ADDING NEW FEATURES:
 *    - Add new features to the appropriate page/tab section in the features_data array
 *    - Always include 'label', 'status', 'enabled', and 'version' properties
 *    - New features should typically start with 'status' => 'demo' until fully implemented
 * 
 * 4. COMPLETION STATISTICS:
 *    - Overall completion rate is calculated automatically as: (live features / total features) * 100
 *    - Page-specific completion rates help track progress in different plugin areas
 *    - These statistics are used for development planning and prioritization
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include the features data class if not already included
if ( ! class_exists( 'TradePress_Features_Data' ) ) {
    require_once TRADEPRESS_PLUGIN_DIR . 'includes/data/features-data.php';
}

class TradePress_Admin_Development_Feature_Status {
    
    /**
     * Output the Feature Status tab content
     */
    public static function output() {
        // Get features data from the existing class
        $features = self::get_features_data();
        
        // Enqueue necessary styles and scripts
        wp_enqueue_style('dashicons');
        
        // Enqueue help functionality
        self::enqueue_help_assets();
        ?>
        <div class="wrap feature-status-tab">
            <div class="completion-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Total Features:', 'tradepress'); ?></span>
                    <span class="stat-value" id="total-features">0</span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Live Features:', 'tradepress'); ?></span>
                    <span class="stat-value" id="live-features">0</span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Demo Features:', 'tradepress'); ?></span>
                    <span class="stat-value" id="demo-features">0</span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Completion Rate:', 'tradepress'); ?></span>
                    <span class="stat-value" id="completion-rate">0%</span>
                </div>
            </div>
            
            <div class="tp-accordion-controls">
                <button class="button expand-all"><?php _e('Expand All', 'tradepress'); ?></button>
                <button class="button collapse-all"><?php _e('Collapse All', 'tradepress'); ?></button>
            </div>
            
            <div class="tp-accordion-container">
                <?php 
                // Group features into sections
                $page_features = array();
                $system_features = array();
                
                foreach ($features as $page_id => $page) {
                    if (isset($page['url'])) {
                        $page_features[$page_id] = $page;
                    } else {
                        $system_features[$page_id] = $page;
                    }
                }
                
                // Output section headings
                ?>
                <h3 class="feature-section-header"><?php _e('Admin Pages', 'tradepress'); ?></h3>
                
                <?php foreach ($page_features as $page_id => $page): ?>
                <div class="tp-accordion-item">
                    <div class="tp-accordion-header">
                        <h3 class="tp-accordion-title"><?php echo esc_html($page['label']); ?></h3>
                        <span class="tp-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tp-accordion-content">
                        <?php if (!empty($page['tabs'])): ?>
                            <table class="feature-status-table">
                                <thead>
                                    <tr>
                                        <th width="40%"><?php _e('Feature', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Status', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Enabled', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Version', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Help', 'tradepress'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Count stats for this page
                                    $page_total = 0;
                                    $page_live = 0;
                                    $page_demo = 0;
                                    
                                    foreach ($page['tabs'] as $tab_id => $tab): 
                                        // Generate tab URL if available
                                        $tab_url = '';
                                        if (isset($page['url']) && isset($tab['url'])) {
                                            $tab_url = $tab['url'];
                                        } elseif (isset($page['url'])) {
                                            $tab_url = add_query_arg('tab', $tab_id, $page['url']);
                                        }
                                    ?>
                                    <tr class="feature-tab-header">
                                        <td colspan="5">
                                            <strong><?php echo esc_html($tab['label']); ?></strong>
                                            <?php if (!empty($tab_url)): ?>
                                                <a href="<?php echo esc_url($tab_url); ?>" class="feature-tab-link dashicons dashicons-external"></a>
                                            <?php endif; ?>
                                            <span class="tab-status">
                                                <?php echo isset($tab['enabled']) && $tab['enabled'] ? '(' . __('Enabled', 'tradepress') . ')' : '(' . __('Disabled', 'tradepress') . ')'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php if (!empty($tab['abilities'])): ?>
                                        <?php foreach ($tab['abilities'] as $ability_id => $ability): 
                                            $is_live = isset($ability['status']) && $ability['status'] == 'live';
                                            $status_class = $is_live ? 'status-live' : 'status-demo';
                                            $status_text = $is_live ? __('Live', 'tradepress') : __('Demo', 'tradepress');
                                            $is_enabled = isset($ability['enabled']) && $ability['enabled'];
                                            $version = isset($ability['version']) ? $ability['version'] : '1.0.0';
                                            
                                            // Check for help file
                                            $help_file = TRADEPRESS_PLUGIN_DIR . 'docs/features/' . $ability_id . '.md';
                                            $has_help = file_exists($help_file);
                                            

                                            
                                            // Update page stats
                                            $page_total++;
                                            if ($is_live) {
                                                $page_live++;
                                            } else {
                                                $page_demo++;
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html($ability['label']); ?></td>
                                            <td>
                                                <span class="feature-status-badge <?php echo esc_attr($status_class); ?>">
                                                    <?php echo esc_html($status_text); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $is_enabled ? __('Yes', 'tradepress') : __('No', 'tradepress'); ?>
                                            </td>
                                            <td>
                                                <?php echo esc_html($version); ?>
                                            </td>
                                            <td>
                                                <?php if ($has_help): ?>
                                                    <button class="button button-small feature-help-btn" data-feature="<?php echo esc_attr($ability_id); ?>">
                                                        <span class="dashicons dashicons-info"></span>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="dashicons dashicons-minus" style="color: #ccc;"></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5"><?php _e('No features defined for this tab.', 'tradepress'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($page_total > 0): ?>
                                    <tr class="page-stats-row">
                                        <td colspan="5">
                                            <strong><?php _e('Page Statistics:', 'tradepress'); ?></strong> 
                                            <?php echo sprintf(
                                                __('Total: %d, Live: %d, Demo: %d, Completion Rate: %d%%', 'tradepress'), 
                                                $page_total, 
                                                $page_live, 
                                                $page_demo, 
                                                ($page_total > 0) ? round(($page_live / $page_total) * 100) : 0
                                            ); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p><?php _e('No tabs defined for this page.', 'tradepress'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <h3 class="feature-section-header"><?php _e('System Components', 'tradepress'); ?></h3>
                
                <?php foreach ($system_features as $system_id => $system): ?>
                <div class="tp-accordion-item">
                    <div class="tp-accordion-header">
                        <h3 class="tp-accordion-title"><?php echo esc_html($system['label']); ?></h3>
                        <span class="tp-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tp-accordion-content">
                        <?php if (!empty($system['tabs'])): ?>
                            <table class="feature-status-table">
                                <thead>
                                    <tr>
                                        <th width="40%"><?php _e('Feature', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Status', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Enabled', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Version', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Help', 'tradepress'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Count stats for this system
                                    $system_total = 0;
                                    $system_live = 0;
                                    $system_demo = 0;
                                    
                                    foreach ($system['tabs'] as $tab_id => $tab): 
                                        // Generate tab URL if available
                                        $tab_url = '';
                                        if (isset($system['url']) && isset($tab['url'])) {
                                            $tab_url = $tab['url'];
                                        } elseif (isset($system['url'])) {
                                            $tab_url = add_query_arg('tab', $tab_id, $system['url']);
                                        }
                                    ?>
                                    <tr class="feature-tab-header">
                                        <td colspan="5">
                                            <strong><?php echo esc_html($tab['label']); ?></strong>
                                            <?php if (!empty($tab_url)): ?>
                                                <a href="<?php echo esc_url($tab_url); ?>" class="feature-tab-link dashicons dashicons-external"></a>
                                            <?php endif; ?>
                                            <span class="tab-status">
                                                <?php echo isset($tab['enabled']) && $tab['enabled'] ? '(' . __('Enabled', 'tradepress') . ')' : '(' . __('Disabled', 'tradepress') . ')'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php if (!empty($tab['abilities'])): ?>
                                        <?php foreach ($tab['abilities'] as $ability_id => $ability): 
                                            $is_live = isset($ability['status']) && $ability['status'] == 'live';
                                            $status_class = $is_live ? 'status-live' : 'status-demo';
                                            $status_text = $is_live ? __('Live', 'tradepress') : __('Demo', 'tradepress');
                                            $is_enabled = isset($ability['enabled']) && $ability['enabled'];
                                            $version = isset($ability['version']) ? $ability['version'] : '1.0.0';
                                            
                                            // Check for help file
                                            $help_file = TRADEPRESS_PLUGIN_DIR . 'docs/features/' . $ability_id . '.md';
                                            $has_help = file_exists($help_file);
                                            
                                            // Update system stats
                                            $system_total++;
                                            if ($is_live) {
                                                $system_live++;
                                            } else {
                                                $system_demo++;
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html($ability['label']); ?></td>
                                            <td>
                                                <span class="feature-status-badge <?php echo esc_attr($status_class); ?>">
                                                    <?php echo esc_html($status_text); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $is_enabled ? __('Yes', 'tradepress') : __('No', 'tradepress'); ?>
                                            </td>
                                            <td>
                                                <?php echo esc_html($version); ?>
                                            </td>
                                            <td>
                                                <?php if ($has_help): ?>
                                                    <button class="button button-small feature-help-btn" data-feature="<?php echo esc_attr($ability_id); ?>">
                                                        <span class="dashicons dashicons-info"></span>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="dashicons dashicons-minus" style="color: #ccc;"></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5"><?php _e('No features defined for this tab.', 'tradepress'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($system_total > 0): ?>
                                    <tr class="page-stats-row">
                                        <td colspan="5">
                                            <strong><?php _e('System Statistics:', 'tradepress'); ?></strong> 
                                            <?php echo sprintf(
                                                __('Total: %d, Live: %d, Demo: %d, Completion Rate: %d%%', 'tradepress'), 
                                                $system_total, 
                                                $system_live, 
                                                $system_demo, 
                                                ($system_total > 0) ? round(($system_live / $system_total) * 100) : 0
                                            ); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p><?php _e('No tabs defined for this system.', 'tradepress'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <h3 class="feature-section-header"><?php _e('Scoring Directives', 'tradepress'); ?></h3>
                
                <div class="tp-accordion-item">
                    <div class="tp-accordion-header">
                        <h3 class="tp-accordion-title"><?php _e('System Directives', 'tradepress'); ?></h3>
                        <span class="tp-accordion-icon dashicons dashicons-arrow-down-alt2"></span>
                    </div>
                    <div class="tp-accordion-content">
                        <?php 
                        // Load directives data
                        if (!function_exists('tradepress_get_all_system_directives')) {
                            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-register.php';
                        }
                        $directives = tradepress_get_all_system_directives();
                        
                        if (!empty($directives)): 
                        ?>
                            <table class="feature-status-table">
                                <thead>
                                    <tr>
                                        <th width="30%"><?php _e('Directive Name', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Status', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Dev Status', 'tradepress'); ?></th>
                                        <th width="10%"><?php _e('Version', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Category', 'tradepress'); ?></th>
                                        <th width="15%"><?php _e('Impact', 'tradepress'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $directive_total = 0;
                                    $directive_tested = 0;
                                    $directive_development = 0;
                                    
                                    foreach ($directives as $directive_id => $directive): 
                                        $dev_status = $directive['development_status'] ?? 'development';
                                        $is_tested = $dev_status === 'tested';
                                        $status_class = $is_tested ? 'status-live' : 'status-demo';
                                        $status_text = $is_tested ? __('Tested', 'tradepress') : __('Development', 'tradepress');
                                        $version = $directive['version'] ?? '0.1.0';
                                        $category = ucfirst($directive['category'] ?? 'technical');
                                        $impact = ucfirst($directive['impact'] ?? 'medium');
                                        
                                        $directive_total++;
                                        if ($is_tested) {
                                            $directive_tested++;
                                        } else {
                                            $directive_development++;
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($directive['name']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $directive['active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $directive['active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="feature-status-badge <?php echo esc_attr($status_class); ?>">
                                                <?php echo esc_html($status_text); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($version); ?></td>
                                        <td><?php echo esc_html($category); ?></td>
                                        <td><?php echo esc_html($impact); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <tr class="page-stats-row">
                                        <td colspan="6">
                                            <strong><?php _e('Directive Statistics:', 'tradepress'); ?></strong> 
                                            <?php echo sprintf(
                                                __('Total: %d, Tested: %d, Development: %d, Completion Rate: %d%%', 'tradepress'), 
                                                $directive_total, 
                                                $directive_tested, 
                                                $directive_development, 
                                                ($directive_total > 0) ? round(($directive_tested / $directive_total) * 100) : 0
                                            ); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p><?php _e('No directives found.', 'tradepress'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue help functionality assets
     */
    private static function enqueue_help_assets() {
        // Add inline CSS for help functionality
        wp_add_inline_style('dashicons', '
            .feature-help-btn {
                padding: 2px 6px !important;
                min-height: auto !important;
                line-height: 1 !important;
            }
            .feature-help-btn .dashicons {
                font-size: 14px;
                width: 14px;
                height: 14px;
            }
            .feature-help-modal {
                display: none;
                position: fixed;
                z-index: 100000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }
            .feature-help-content {
                background-color: #fff;
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #ccc;
                width: 80%;
                max-width: 800px;
                max-height: 80vh;
                overflow-y: auto;
                border-radius: 4px;
                position: relative;
            }
            .feature-help-close {
                position: absolute;
                top: 10px;
                right: 15px;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                color: #aaa;
            }
            .feature-help-close:hover {
                color: #000;
            }
        ');
        
        // Add inline JavaScript directly to footer
        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                // Handle help button clicks
                $(document).on("click", ".feature-help-btn", function(e) {
                    e.preventDefault();
                    var featureId = $(this).data("feature");
                    
                    $.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        type: "POST",
                        data: {
                            action: "tradepress_get_feature_help",
                            feature_id: featureId,
                            nonce: "<?php echo wp_create_nonce('tradepress_feature_help'); ?>"
                        },
                        success: function(response) {
                            if (response.success) {
                                // Create and show modal
                                var modal = $('<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:100000;">' +
                                    '<div style="background:white;margin:50px auto;padding:20px;width:80%;max-width:800px;max-height:80vh;overflow-y:auto;border-radius:5px;position:relative;">' +
                                    '<span style="position:absolute;top:10px;right:15px;font-size:28px;cursor:pointer;color:#aaa;" class="help-close">&times;</span>' +
                                    '<div>' + response.data + '</div>' +
                                    '</div></div>');
                                
                                $('body').append(modal);
                                
                                // Close on click
                                modal.on('click', function(e) {
                                    if (e.target === this || $(e.target).hasClass('help-close')) {
                                        modal.remove();
                                    }
                                });
                            } else {
                                alert("Help content not found for this feature.");
                            }
                        },
                        error: function() {
                            alert("Error loading help content.");
                        }
                    });
                });
            });
            </script>
            <?php
        });
    }
    
    /**
     * Get feature page structure data
     * 
     * @return array Array of pages and their features
     * 
     * @ai-guidance
     * This method retrieves the core feature data structure that defines all plugin features.
     * AI should analyze this data to:
     * 1. Identify features with "demo" status that need to be upgraded to "live"
     * 2. Prioritize features based on their importance and current status
     * 3. Track overall implementation completion percentages
     * 4. Suggest updates to the data structure when adding new features
     */
    protected static function get_features_data() {
        // Check if TradePress_Features_Data exists and has the get_features_data method
        if (class_exists('TradePress_Features_Data') && method_exists('TradePress_Features_Data', 'get_features_data')) {
            return TradePress_Features_Data::get_features_data();
        }
        
        // Fallback empty array if class not found
        return array();
    }
}
