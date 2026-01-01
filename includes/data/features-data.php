<?php
/**
 * TradePress Features Data
 *
 * Centralized class for accessing all TradePress feature data.
 * Integrates with the feature-loader to provide a unified interface for accessing feature definitions.
 *
 * @package TradePress\Includes\Data
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the feature loader if not already included
if (!function_exists('tradepress_get_all_development_data')) {
    if (defined('TRADEPRESS_ROADMAP_DIR') && file_exists(TRADEPRESS_ROADMAP_DIR . 'feature-loader.php')) {
        require_once TRADEPRESS_ROADMAP_DIR . 'feature-loader.php';
    }
}

/**
 * TradePress_Features_Data Class
 */
class TradePress_Features_Data {
    
    /**
     * Get all features data
     *
     * @return array All feature data organized by category
     */
    public static function get_features_data() {
        // Get all development data from feature loader
        $development_data = tradepress_get_all_development_data();
        
        // Transform into UI-compatible format
        $ui_data = array();
        
        // Define page categories with their display labels
        $page_categories = array(
            'dashboard' => array('label' => __('Dashboard', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-dashboard')),
            'settings' => array('label' => __('Settings', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-settings')),
            'trading_platforms' => array('label' => __('Trading Platforms', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-tradingplatforms')),
            'social_platforms' => array('label' => __('Social Platforms', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-social')),
            'debug' => array('label' => __('Debug', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-debug')),
            'automation' => array('label' => __('Automation', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-automation')),
            'research' => array('label' => __('Research', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-research')),
            'sandbox' => array('label' => __('Sandbox', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-sandbox')),
            'development' => array('label' => __('Development', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-development')),
            'trading' => array('label' => __('Trading', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-trading')),
            'watchlists' => array('label' => __('Watchlists', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-watchlists')),
            'data' => array('label' => __('Data', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-data')),
            'analysis' => array('label' => __('Analysis', 'tradepress'), 'url' => admin_url('admin.php?page=tradepress-analysis')),
        );
        
        // Define system categories with their display labels
        $system_categories = array(
            'api_handling' => array('label' => __('API Handling', 'tradepress')),
            'database_management' => array('label' => __('Database Management', 'tradepress')),
            'trading_strategies' => array('label' => __('Trading Strategies', 'tradepress')),
            'scoring_algorithm' => array('label' => __('Scoring Algorithm', 'tradepress')),
            'cron_scheduling' => array('label' => __('CRON Scheduling', 'tradepress')),
            'user_management' => array('label' => __('User Management', 'tradepress')),
            'integrations' => array('label' => __('Integrations', 'tradepress')),
            'ui_components' => array('label' => __('UI Components', 'tradepress')),
            'development_ui' => array('label' => __('Development UI', 'tradepress')),
            'technical_architecture' => array('label' => __('Technical Architecture', 'tradepress')),
            'scoring_implementation' => array('label' => __('Scoring Implementation', 'tradepress')),
            'data_optimization_implementation' => array('label' => __('Data Optimization', 'tradepress')),
        'data_freshness' => array('label' => __('Data Freshness Framework', 'tradepress')),
            'strategy_implementation' => array('label' => __('Strategy Implementation', 'tradepress')),
            'symbols_management' => array('label' => __('Symbols Management', 'tradepress')),
            'documentation' => array('label' => __('Documentation', 'tradepress')),
            'advanced_trading' => array('label' => __('Advanced Trading', 'tradepress')),
            'forecast_implementation' => array('label' => __('Forecast Implementation', 'tradepress')),
            'forecast_management' => array('label' => __('Forecast Management', 'tradepress')),
            'plugin_modes' => array('label' => __('Plugin Modes', 'tradepress')),
            'api_integrations' => array('label' => __('API Integrations', 'tradepress')),
            'education_academy' => array('label' => __('Education Academy', 'tradepress')),
            'daasg_system' => array('label' => __('DAASG System', 'tradepress')),
        );
        
        // Process page categories
        foreach ($page_categories as $page_id => $page_info) {
            if (!isset($development_data[$page_id]) || empty($development_data[$page_id])) {
                continue;
            }
            
            $ui_data[$page_id] = array(
                'label' => $page_info['label'],
                'url' => $page_info['url'],
                'tabs' => array()
            );
            
            // Create a default tab for this page
            $ui_data[$page_id]['tabs']['default'] = array(
                'label' => $page_info['label'] . ' ' . __('Features', 'tradepress'),
                'enabled' => true,
                'abilities' => array()
            );
            
            // Add all features to the default tab
            foreach ($development_data[$page_id] as $feature_id => $feature) {
                $ui_data[$page_id]['tabs']['default']['abilities'][$feature_id] = array(
                    'label' => isset($feature['title']) ? $feature['title'] : $feature_id,
                    'status' => isset($feature['status']) ? $feature['status'] : 'planned',
                    'enabled' => isset($feature['enabled']) ? $feature['enabled'] : false,
                    'version' => isset($feature['version']) ? $feature['version'] : '1.0.0'
                );
            }
        }
        
        // Process system categories
        foreach ($system_categories as $system_id => $system_info) {
            if (!isset($development_data[$system_id]) || empty($development_data[$system_id])) {
                continue;
            }
            
            $ui_data[$system_id] = array(
                'label' => $system_info['label'],
                'tabs' => array()
            );
            
            // Create a default tab for this system
            $ui_data[$system_id]['tabs']['default'] = array(
                'label' => $system_info['label'] . ' ' . __('Features', 'tradepress'),
                'enabled' => true,
                'abilities' => array()
            );
            
            // Add all features to the default tab
            foreach ($development_data[$system_id] as $feature_id => $feature) {
                $ui_data[$system_id]['tabs']['default']['abilities'][$feature_id] = array(
                    'label' => isset($feature['title']) ? $feature['title'] : $feature_id,
                    'status' => isset($feature['status']) ? $feature['status'] : 'planned',
                    'enabled' => isset($feature['enabled']) ? $feature['enabled'] : false,
                    'version' => isset($feature['version']) ? $feature['version'] : '1.0.0'
                );
            }
        }
        
        return $ui_data;
    }
}
