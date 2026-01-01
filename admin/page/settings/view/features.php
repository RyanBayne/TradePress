<?php
/**
 * TradePress Features Settings
 *
 * @package TradePress/Admin
 * @version 1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include the features data class if not already included
if ( ! class_exists( 'TradePress_Features_Data' ) ) {
    require_once TRADEPRESS_PLUGIN_DIR . 'includes/data/features-data.php';
}

/**
 * TradePress_Admin_Settings_Features Class
 * 
 * Note: This tab has been removed from the Settings page.
 * The functionality is now available in the Development area under Feature Status.
 */
class TradePress_Admin_Settings_Features {
    /**
     * Setting page ID
     * 
     * @var string
     */
    protected $id;

    /**
     * Setting page label
     * 
     * @var string
     */
    protected $label;
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'features';
        $this->label = __( 'Features', 'tradepress' );

        // Tab registration removed - no longer appears in Settings
        // add_filter( 'TradePress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        
        // Keep these hooks in case we need to access this page directly
        add_action( 'TradePress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
        
        // Enqueue styles and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue necessary styles and scripts
     */
    public function enqueue_assets($hook) {
        // Only load on the right pages
        if (strpos($hook, 'page_tradepress-settings') !== false || strpos($hook, 'page_TradePress') !== false) {
            // Load jQuery - WordPress core already includes it
            wp_enqueue_script('jquery');
            
            // Ensure dashicons are loaded
            wp_enqueue_style('dashicons');
            
            // Load the features-specific CSS
            wp_enqueue_style(
                'tradepress-features-styles',
                TRADEPRESS_PLUGIN_URL . 'css/admin-features.css',
                array(),
                defined('TRADEPRESS_VERSION') ? TRADEPRESS_VERSION : time()
            );
            
            // Add inline styles for any missing elements
            $inline_styles = '
                .features-controls {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 20px;
                    background: #f9f9f9;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                }
                
                .features-stats {
                    display: flex;
                    flex-wrap: wrap;
                    margin-bottom: 20px;
                    background: #fff;
                    border: 1px solid #e5e5e5;
                    padding: 15px;
                }
                
                .stat-box {
                    margin-right: 30px;
                    margin-bottom: 10px;
                }
                
                .stat-label {
                    font-weight: bold;
                    margin-right: 5px;
                }
            ';
            wp_add_inline_style('tradepress-features-styles', $inline_styles);
            
            // Add simple inline script using the Sandbox approach - direct click handlers
            wp_add_inline_script('jquery', '
                jQuery(document).ready(function($) {
                    console.log("Features page script initialized");
                    
                    // Handle accordion toggling
                    $(".feature-page-header").click(function() {
                        // Get the content element to toggle
                        var $content = $(this).next(".feature-page-content");
                        
                        // Toggle the content with animation
                        $content.slideToggle();
                        
                        // Toggle the icon
                        var $icon = $(this).find(".toggle-section");
                        $icon.toggleClass("dashicons-arrow-down-alt2 dashicons-arrow-up-alt2");
                        
                        return false; // Prevent event bubbling
                    });
                    
                    // Handle expand all button
                    $(".expand-all").click(function(e) {
                        e.preventDefault();
                        $(".feature-page-content").slideDown();
                        $(".toggle-section").removeClass("dashicons-arrow-down-alt2").addClass("dashicons-arrow-up-alt2");
                    });
                    
                    // Handle collapse all button
                    $(".collapse-all").click(function(e) {
                        e.preventDefault();
                        $(".feature-page-content").slideUp();
                        $(".toggle-section").removeClass("dashicons-arrow-up-alt2").addClass("dashicons-arrow-down-alt2");
                    });
                    
                    // Feature status filtering
                    $("#feature-status-filter").change(function() {
                        var status = $(this).val();
                        if (status === "all") {
                            $(".feature-row").show();
                        } else {
                            $(".feature-row").hide();
                            $(".feature-row." + status + "-status").show();
                        }
                    });
                    
                    // Update stats function
                    function updateStats() {
                        var totalFeatures = $(".feature-row").length;
                        var liveFeatures = $(".feature-row.live-status").length;
                        var demoFeatures = $(".feature-row.demo-status").length;
                        var completionRate = Math.round((liveFeatures / totalFeatures) * 100);
                        
                        $("#total-features").text(totalFeatures);
                        $("#live-features").text(liveFeatures);
                        $("#demo-features").text(demoFeatures);
                        $("#completion-rate").text(completionRate + "%");
                    }
                    
                    // Run initial stats update
                    updateStats();
                    
                    // Update stats when status changes
                    $(".feature-mode select").change(function() {
                        var row = $(this).closest("tr");
                        row.removeClass("live-status demo-status");
                        row.addClass($(this).val() + "-status");
                        updateStats();
                    });
                });
            ');
        }
    }

    /**
     * Get feature page structure data
     * 
     * @return array Array of pages and their features
     */
    protected function get_features_data() {
        return TradePress_Features_Data::get_features_data();
    }

    /**
     * Output the settings
     */
    public function output() {
        $features = $this->get_features_data();
        ?>
        <div class="tradepress-features-settings">
            <div class="tradepress-features-header">
                <h2><?php esc_html_e('Features Management', 'tradepress'); ?></h2>
                <p class="description"><?php esc_html_e('Manage TradePress features and track development progress.', 'tradepress'); ?></p>
            </div>
            <form method="post" id="mainform" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('tradepress-save-features-settings', 'tradepress_features_nonce'); ?>
                <div class="features-controls">
                    <div class="feature-filter">
                        <label for="feature-status-filter"><?php esc_html_e('Filter by Status:', 'tradepress'); ?></label>
                        <select id="feature-status-filter">
                            <option value="all"><?php esc_html_e('All Features', 'tradepress'); ?></option>
                            <option value="live"><?php esc_html_e('Live Features', 'tradepress'); ?></option>
                            <option value="demo"><?php esc_html_e('Demo Features', 'tradepress'); ?></option>
                        </select>
                    </div>
                    <div class="bulk-actions">
                        <a href="#" class="expand-all"><?php esc_html_e('Expand All', 'tradepress'); ?></a> | 
                        <a href="#" class="collapse-all"><?php esc_html_e('Collapse All', 'tradepress'); ?></a>
                    </div>
                </div>
                
                <div class="features-stats">
                    <div class="stat-box">
                        <span class="stat-label"><?php esc_html_e('Total Features:', 'tradepress'); ?></span>
                        <span class="stat-value" id="total-features">0</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-label"><?php esc_html_e('Live Features:', 'tradepress'); ?></span>
                        <span class="stat-value" id="live-features">0</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-label"><?php esc_html_e('Demo Features:', 'tradepress'); ?></span>
                        <span class="stat-value" id="demo-features">0</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-label"><?php esc_html_e('Completion Rate:', 'tradepress'); ?></span>
                        <span class="stat-value" id="completion-rate">0%</span>
                    </div>
                </div>
                
                <div class="features-list">
                <?php 
                // Display features by page
                foreach ($features as $page_id => $page) : 
                ?>
                    <div class="feature-page">
                        <div class="feature-page-header">
                            <h3><?php echo esc_html($page['label']); ?></h3>
                            <span class="toggle-section dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="feature-page-content" style="display: none;">
                            <?php if (!empty($page['tabs'])) : ?>
                                <table class="widefat features-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Tab/Feature', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Status', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Enabled', 'tradepress'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($page['tabs'] as $tab_id => $tab) : ?>
                                        <tr class="tab-header">
                                            <td colspan="3">
                                                <strong><?php echo esc_html($tab['label']); ?></strong>
                                                <input type="hidden" name="features[<?php echo esc_attr($page_id); ?>][tabs][<?php echo esc_attr($tab_id); ?>][label]" value="<?php echo esc_attr($tab['label']); ?>">
                                                <label class="tab-toggle">
                                                    <input type="checkbox" 
                                                           name="features[<?php echo esc_attr($page_id); ?>][tabs][<?php echo esc_attr($tab_id); ?>][enabled]" 
                                                           value="1" 
                                                           <?php checked(isset($tab['enabled']) ? $tab['enabled'] : false); ?>>
                                                    <?php esc_html_e('Enable Tab', 'tradepress'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                        <?php if (!empty($tab['abilities'])) : ?>
                                            <?php foreach ($tab['abilities'] as $ability_id => $ability) : 
                                                $status_class = isset($ability['status']) && $ability['status'] == 'live' ? 'live-status' : 'demo-status';
                                            ?>
                                                <tr class="feature-row <?php echo esc_attr($status_class); ?>">
                                                    <td>
                                                        <?php echo esc_html($ability['label']); ?>
                                                        <input type="hidden" name="features[<?php echo esc_attr($page_id); ?>][tabs][<?php echo esc_attr($tab_id); ?>][abilities][<?php echo esc_attr($ability_id); ?>][label]" value="<?php echo esc_attr($ability['label']); ?>">
                                                    </td>
                                                    <td class="feature-mode">
                                                        <select name="features[<?php echo esc_attr($page_id); ?>][tabs][<?php echo esc_attr($tab_id); ?>][abilities][<?php echo esc_attr($ability_id); ?>][status]">
                                                            <option value="live" <?php selected(isset($ability['status']) && $ability['status'] == 'live'); ?>><?php esc_html_e('Live', 'tradepress'); ?></option>
                                                            <option value="demo" <?php selected(isset($ability['status']) && $ability['status'] == 'demo'); ?>><?php esc_html_e('Demo', 'tradepress'); ?></option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" 
                                                                   name="features[<?php echo esc_attr($page_id); ?>][tabs][<?php echo esc_attr($tab_id); ?>][abilities][<?php echo esc_attr($ability_id); ?>][enabled]" 
                                                                   value="1" 
                                                                   <?php checked(isset($ability['enabled']) ? $ability['enabled'] : false); ?>>
                                                            <?php esc_html_e('Enabled', 'tradepress'); ?>
                                                        </label>
                                                        <?php if (isset($ability['version'])) : ?>
                                                            <span class="version"><?php echo esc_html(sprintf(__('v%s', 'tradepress'), $ability['version'])); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
                
                <input type="hidden" name="save" value="save_features">
            </form>
        </div>
        <?php
    }

    /**
     * Save settings
     */
    public function save() {
        if (!isset($_POST['save']) || $_POST['save'] !== 'save_features') {
            return;
        }

        if (!wp_verify_nonce($_POST['tradepress_features_nonce'], 'tradepress-save-features-settings')) {
            wp_die(__('Action failed. Please refresh the page and retry.', 'tradepress'));
        }

        // Get existing feature data
        $features = TradePress_Features_Data::get_features_data();

        // Get submitted data
        $submitted_features = isset($_POST['features']) ? $_POST['features'] : array();
        
        // Loop through each page
        foreach ($features as $page_id => &$page) {
            if (isset($submitted_features[$page_id]['tabs'])) {
                foreach ($page['tabs'] as $tab_id => &$tab) {
                    // Update tab enabled status
                    if (isset($submitted_features[$page_id]['tabs'][$tab_id]['enabled'])) {
                        $tab['enabled'] = true;
                    } else {
                        $tab['enabled'] = false;
                    }

                    // Update abilities
                    if (isset($submitted_features[$page_id]['tabs'][$tab_id]['abilities'])) {
                        foreach ($tab['abilities'] as $ability_id => &$ability) {
                            // Update ability status
                            if (isset($submitted_features[$page_id]['tabs'][$tab_id]['abilities'][$ability_id]['status'])) {
                                $ability['status'] = sanitize_text_field(
                                    $submitted_features[$page_id]['tabs'][$tab_id]['abilities'][$ability_id]['status']
                                );
                            }

                            // Update ability version
                            if (isset($submitted_features[$page_id]['tabs'][$tab_id]['abilities'][$ability_id]['version'])) {
                                $ability['version'] = sanitize_text_field(
                                    $submitted_features[$page_id]['tabs'][$tab_id]['abilities'][$ability_id]['version']
                                );
                            }
                            
                            // Update ability enabled status
                            if (isset($submitted_features[$page_id]['tabs'][$tab_id]['abilities'][$ability_id]['enabled'])) {
                                $ability['enabled'] = true;
                            } else {
                                $ability['enabled'] = false;
                            }
                        }
                    }
                }
            }
        }
        
        // Save updated features data
        TradePress_Features_Data::save_features_data($features);
        
        // Display success message
        add_settings_error(
            'tradepress_features_settings',
            'tradepress_features_settings_saved',
            __('Features settings saved successfully.', 'tradepress'),
            'success'
        );
    }
}

return new TradePress_Admin_Settings_Features();
