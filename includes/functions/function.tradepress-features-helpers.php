<?php
/**
 * TradePress Feature Helpers
 *
 * Helper functions for accessing feature data and mode information
 *
 * @package TradePress/Functions
 * @version 1.0.0
 * @created 2023-06-25 17:45:00
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get the tab mode information
 * 
 * @param string $page_id The page ID in the features system
 * @param string $tab_id The tab ID in the features system
 * @return array Array containing mode, status, and whether the tab is enabled
 */
function tradepress_get_tab_mode($page_id, $tab_id) {
    $features = get_option('tradepress_features_data', array());
    
    $result = array(
        'mode' => 'demo',
        'status' => 'WIP',
        'enabled' => true,
        'version' => '0.1.0'
    );
    
    if (isset($features[$page_id]['tabs'][$tab_id])) {
        $tab = $features[$page_id]['tabs'][$tab_id];
        $result['enabled'] = isset($tab['enabled']) ? (bool)$tab['enabled'] : true;
        
        // Find the first ability to determine mode (assuming all abilities in a tab have the same mode)
        if (!empty($tab['abilities'])) {
            $first_ability = reset($tab['abilities']);
            $result['mode'] = isset($first_ability['status']) ? $first_ability['status'] : 'demo';
            $result['version'] = isset($first_ability['version']) ? $first_ability['version'] : '0.1.0';
            $result['status'] = ($result['mode'] === 'live' && version_compare($result['version'], '1.0.0', '>=')) ? 'Ready' : 'WIP';
        }
    }
    
    return $result;
}

/**
 * Check if a tab is enabled
 * 
 * @param string $page_id The page ID in the features system
 * @param string $tab_id The tab ID in the features system
 * @return bool Whether the tab is enabled
 */
function tradepress_is_tab_enabled($page_id, $tab_id) {
    $tab_data = tradepress_get_tab_mode($page_id, $tab_id);
    return $tab_data['enabled'];
}

/**
 * Get a visual indicator for the current tab mode
 *
 * @param string $page_id The page ID in the features system
 * @param string $tab_id The tab ID in the features system
 * @return string HTML for the mode indicator
 */
function tradepress_get_tab_mode_indicator($page_id, $tab_id) {
    $tab_data = tradepress_get_tab_mode($page_id, $tab_id);
    
    $mode_class = ($tab_data['mode'] === 'live') ? 'live-mode' : 'demo-mode';
    $status_class = ($tab_data['status'] === 'Ready') ? 'ready-status' : 'wip-status';
    
    $indicator = '<div class="tradepress-tab-indicator">';
    $indicator .= '<span class="tab-mode ' . $mode_class . '">' . ucfirst($tab_data['mode']) . '</span>';
    $indicator .= '<span class="tab-status ' . $status_class . '">' . $tab_data['status'] . '</span>';
    $indicator .= '<span class="tab-version">v' . $tab_data['version'] . '</span>';
    $indicator .= '</div>';
    
    return $indicator;
}

/**
 * Add CSS for the tab mode indicators
 */
function tradepress_add_tab_mode_indicator_styles() {
    ?>
    <style>
        .tradepress-tab-indicator {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-left: 15px;
            font-size: 12px;
        }
        
        .tab-mode, .tab-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .tab-mode.live-mode {
            background-color: #e7f7ed;
            color: #46b450;
            border: 1px solid #c8e6d0;
        }
        
        .tab-mode.demo-mode {
            background-color: #fef8e8;
            color: #f0c33c;
            border: 1px solid #f8e6b8;
        }
        
        .tab-status.ready-status {
            background-color: #e7f7ed;
            color: #46b450;
            border: 1px solid #c8e6d0;
        }
        
        .tab-status.wip-status {
            background-color: #f6f7f7;
            color: #72777c;
            border: 1px solid #ddd;
        }
        
        .tab-version {
            color: #72777c;
            font-size: 11px;
        }
        
        .demo-mode-notice {
            background-color: #fef8e8;
            border-left: 4px solid #f0c33c;
            padding: 10px 12px;
            margin-bottom: 20px;
        }
        
        .tradepress-tab-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .tradepress-tab-header h2 {
            margin: 0;
        }
    </style>
    <?php
}
add_action('admin_head', 'tradepress_add_tab_mode_indicator_styles');
