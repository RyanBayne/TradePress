<?php
/**
 * TradePress Development and Feature Tracking
 *
 * Central repository for development information, feature tracking, and implementation
 * guidance for both developers and AI assistants. This file centralizes information
 * previously spread across multiple .md files into a single, programmatically 
 * accessible structure.
 *
 * @package TradePress
 *
 * USAGE THROUGHOUT THE PLUGIN:
 * ---------------------------
 * The feature data defined in this file is used by multiple components:
 * 1. Feature Status Overview - Displays all features and their implementation status
 * 2. Development Roadmap - Uses phase and priority for development timeline planning
 * 3. Feature Management Settings - Controls feature enabling/disabling in Settings > Features
 * 4. Plugin Initialization - Conditionally loads features based on 'enabled' status
 * 5. Dependency Management - Checks required dependencies before activating features
 * 6. Admin Notices - Shows warnings about missing dependencies or recommended features
 * 7. Completion Reports - Generates statistics on implementation progress
 * 8. Documentation Generator - Auto-generates documentation from feature metadata
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include all feature files
// Roadmap files moved to Google Drive for project management
define('TRADEPRESS_ROADMAP_DIR', 'G:/My Drive/Project Management/Live/TradePress Project/roadmap/');

if (is_dir(TRADEPRESS_ROADMAP_DIR)) {
    require_once TRADEPRESS_ROADMAP_DIR . 'features/dashboard-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/settings-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/trading-platforms-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/social-platforms-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/debug-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/automation-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/research-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/sandbox-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/development-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/trading-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/watchlists-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/data-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/analysis-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/api-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/database-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/trading-strategies-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/scoring-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/cron-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/user-management-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/integration-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/ui-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/technical-architecture.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/scoring-implementation.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/data-strategy-implementation.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/symbols-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/documentation-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/advanced-trading-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/forecast-implementation.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/forecast-management-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/plugin-modes-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/api-integrations-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/education-academy-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/daasg-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/trend-calculator-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/frontend-features.php';
    require_once TRADEPRESS_ROADMAP_DIR . 'features/community-features.php';
}

/**
 * Class TradePress_Development
 * 
 * Central repository for development data, feature tracking, and implementation guidance.
 * Organizes features by pages and systems to provide comprehensive development information.
 * 
 * This file will eventually consolidate data from:
 * - admin/page/development/feature-status.php
 * 
 * The goal is to bring our tracking of features and AI guidance into one central file
 * that can be accessed programmatically from various parts of the plugin.
 * 
 * DEVELOPMENT GUIDELINES:
 * ----------------------
 * 1. FEATURE STRUCTURE:
 *    Each feature should have the following structure:
 *    - 'title': Human-readable name of the feature
 *    - 'description': Brief explanation of what the feature does
 *    - 'status': Current implementation status:
 *        * 'planned' - Not yet started
 *        * 'in_progress' - Currently being worked on
 *        * 'demo' - Placeholder/mock implementation
 *        * 'live' - Fully implemented, tested, and production-ready feature
 *        * 'deferred' - Postponed to a later date
 *    - 'priority': Importance within its phase (1-5, where 1 is highest)
 *    - 'phase': Development phase (1-6) for scheduling purposes
 *    - 'enabled': Whether the feature is currently enabled
 *    - 'version': Version in which the feature was introduced
 *    - 'files': Array of related file paths
 *    - 'notes': Additional implementation details or considerations
 *    - 'dependencies': Array of feature slugs that must be completed first
 *    - 'ai_guidance': Specific guidance for AI assistants
 */
class TradePress_Development {

    /**
     * Get all development data
     * 
     * @return array All development data combined from all methods
     */
    public static function get_all_data() {
        $all_data = array();
        
        // Page features
        $all_data['dashboard'] = self::get_dashboard_features();
        $all_data['settings'] = self::get_settings_features();
        $all_data['trading_platforms'] = self::get_trading_platforms_features();
        $all_data['social_platforms'] = self::get_social_platforms_features();
        $all_data['debug'] = self::get_debug_features();
        $all_data['automation'] = self::get_automation_features();
        $all_data['research'] = self::get_research_features();
        $all_data['sandbox'] = self::get_sandbox_features();
        $all_data['development'] = self::get_development_features();
        $all_data['trading'] = self::get_trading_features();
        $all_data['watchlists'] = self::get_watchlists_features();
        $all_data['data'] = self::get_data_features();
        $all_data['analysis'] = self::get_analysis_features();
        
        // System features
        $all_data['api_handling'] = self::get_api_features();
        $all_data['database_management'] = self::get_database_features();
        $all_data['trading_strategies'] = self::get_trading_strategies();
        $all_data['scoring_algorithm'] = self::get_scoring_features();
        $all_data['cron_scheduling'] = self::get_cron_features();
        $all_data['user_management'] = self::get_user_management_features();
        $all_data['integrations'] = self::get_integration_features();
        $all_data['ui_components'] = self::get_ui_features();
        
        // Implementation plan features
        $all_data['scoring_implementation'] = self::get_scoring_implementation();
        $all_data['data_optimization_implementation'] = self::get_data_optimization_implementation();
        $all_data['strategy_implementation'] = self::get_strategy_implementation();
        
        // Additional systems
        $all_data['symbols_management'] = self::get_symbols_features();
        $all_data['documentation'] = self::get_documentation_features();
        $all_data['advanced_trading'] = self::get_advanced_trading_features();
        $all_data['forecast_implementation'] = self::get_forecast_implementation();
        $all_data['forecast_management'] = self::get_forecast_management_features();
        $all_data['plugin_modes'] = self::get_plugin_modes_features();
        $all_data['api_integrations'] = self::get_api_integrations_features();
        $all_data['education_academy'] = self::get_education_academy_features();
        $all_data['daasg_system'] = self::get_daasg_features();
        $all_data['trend_calculator'] = self::get_trend_calculator_features();
        $all_data['frontend'] = self::get_frontend_features(); // Added new data
        $all_data['community'] = self::get_community_features();
        
        return $all_data;
    }

    /**
     * Get feature data in a format compatible with the admin UI
     * 
     * This method transforms the feature data from get_all_data() into a format
     * that can be used directly by the feature status tab in the admin UI.
     * 
     * @return array Feature data organized into 'pages' and 'systems' structures
     */
    public static function get_ui_compatible_feature_data() {
        $all_data = self::get_all_data();
        
        // Debug logging if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $data_categories = array_keys($all_data);
            $total_features = 0;
            foreach ($all_data as $category => $features) {
                $total_features += count($features);
            }
            error_log('TradePress_Development::get_ui_compatible_feature_data - Categories: ' . implode(', ', $data_categories));
            error_log('TradePress_Development::get_ui_compatible_feature_data - Total features: ' . $total_features);
        }
        
        $ui_data = array(
            'pages' => array(),
            'systems' => array()
        );
        
        // Process pages
        $page_categories = array(
            'dashboard', 'settings', 'trading_platforms', 'social_platforms', 'debug', 
            'automation', 'research', 'sandbox', 'development', 'trading', 
            'watchlists', 'data', 'analysis'
        );
        
        foreach ($page_categories as $page_id) {
            if (!isset($all_data[$page_id]) || empty($all_data[$page_id])) {
                continue;
            }
            
            $features = $all_data[$page_id];
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Processing page: ' . $page_id . ' with ' . count($features) . ' features');
            }
            
            // Create page entry
            $ui_data['pages'][$page_id] = array(
                'label' => ucfirst(str_replace('_', ' ', $page_id)),
                'tabs' => array()
            );
            
            // Group features by category for tabs
            $tabs = array();
            
            // Default tab for features without a specific category
            $default_tab_id = 'general';
            if (!isset($tabs[$default_tab_id])) {
                $tabs[$default_tab_id] = array(
                    'label' => 'General',
                    'enabled' => true,
                    'abilities' => array()
                );
            }
            
            foreach ($features as $feature_id => $feature) {
                // Determine which tab this feature belongs to (can be customized based on your needs)
                $category_parts = explode('_', $feature_id);
                $tab_id = !empty($category_parts[0]) ? $category_parts[0] : $default_tab_id;
                
                // Create tab if it doesn't exist
                if (!isset($tabs[$tab_id])) {
                    $tabs[$tab_id] = array(
                        'label' => ucfirst(str_replace('_', ' ', $tab_id)),
                        'enabled' => true,
                        'abilities' => array()
                    );
                }
                
                // Add feature as an ability
                $tabs[$tab_id]['abilities'][$feature_id] = array(
                    'label' => isset($feature['description']) ? $feature['description'] : $feature['title'],
                    'status' => isset($feature['status']) ? $feature['status'] : 'demo',
                    'enabled' => isset($feature['enabled']) ? $feature['enabled'] : true,
                    'version' => isset($feature['version']) ? $feature['version'] : '1.0.0'
                );
            }
            
            $ui_data['pages'][$page_id]['tabs'] = $tabs;
        }
        
        // Process systems
        $system_categories = array(
            'api_handling', 'database_management', 'trading_strategies', 
            'scoring_algorithm', 'cron_scheduling', 'user_management', 'integrations', 'ui_components',
            'symbols_management', 'documentation', 'advanced_trading'
        );
        
        foreach ($system_categories as $system_id) {
            if (!isset($all_data[$system_id]) || empty($all_data[$system_id])) {
                continue;
            }
            
            $features = $all_data[$system_id];
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Processing system: ' . $system_id . ' with ' . count($features) . ' features');
            }
            
            // Create system entry
            $ui_data['systems'][$system_id] = array(
                'label' => ucfirst(str_replace('_', ' ', $system_id)),
                'features' => array()
            );
            
            // Add features
            foreach ($features as $feature_id => $feature) {
                $ui_data['systems'][$system_id]['features'][$feature_id] = array(
                    'label' => isset($feature['title']) ? $feature['title'] : ucfirst(str_replace('_', ' ', $feature_id)),
                    'description' => isset($feature['description']) ? $feature['description'] : '',
                    'status' => isset($feature['status']) ? $feature['status'] : 'demo',
                    'enabled' => isset($feature['enabled']) ? $feature['enabled'] : true,
                    'version' => isset($feature['version']) ? $feature['version'] : '1.0.0'
                );
            }
        }
        
        // Debug logging - output the structure we're returning
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $page_count = isset($ui_data['pages']) ? count($ui_data['pages']) : 0;
            $system_count = isset($ui_data['systems']) ? count($ui_data['systems']) : 0;
            error_log("UI data structure: Pages: {$page_count}, Systems: {$system_count}");
        }
        
        return $ui_data;
    }

    /**
     * Get dashboard tabs for UI compatibility
     * 
     * @return array Dashboard tabs
     */
    private static function get_dashboard_tabs() {
        $tabs = array(
            'overview' => array(
                'label' => __('Overview', 'tradepress'),
                'enabled' => true,
                'abilities' => array(),
            ),
            'at_a_glance' => array(
                'label' => __('At a Glance', 'tradepress'),
                'enabled' => true,
                'abilities' => array(),
            ),
            'market_data' => array(
                'label' => __('Market Data', 'tradepress'),
                'enabled' => true,
                'abilities' => array(),
            ),
            'signals' => array(
                'label' => __('Signals', 'tradepress'),
                'enabled' => true,
                'abilities' => array(),
            ),
        );
        
        // Add dashboard features to appropriate tabs
        $dashboard_features = self::get_dashboard_features();
        foreach ($dashboard_features as $key => $feature) {
            $tab_key = $key === 'market_overview' ? 'market_data' : 
                      ($key === 'recent_signals' ? 'signals' : 
                      ($key === 'at_a_glance' ? 'at_a_glance' : 'overview'));
            
            $tabs[$tab_key]['abilities'][$key] = array(
                'label' => $feature['title'],
                'status' => $feature['status'],
                'enabled' => $feature['enabled'],
                'version' => $feature['version'],
            );
        }
        
        return $tabs;
    }

    /**
     * Generate tab data for a specific page
     *
     * @param string $page_key The page key
     * @return array Tab data structure
     */
    private static function generate_tab_data($page_key) {
        $tabs = array();
        $features = self::{"get_{$page_key}_features"}();
        
        if (empty($features)) {
            // If no features exist, return at least one tab to prevent errors
            return array(
                'general' => array(
                    'label' => __('General', 'tradepress'),
                    'enabled' => true,
                    'abilities' => array(),
                )
            );
        }
        
        // Group features by logical tabs
        foreach ($features as $key => $feature) {
            // Get a simplified tab key from feature key
            $tab_parts = explode('_', $key);
            $tab_key = isset($tab_parts[0]) ? $tab_parts[0] : 'general';
            
            // Initialize tab if it doesn't exist
            if (!isset($tabs[$tab_key])) {
                $tabs[$tab_key] = array(
                    'label' => ucfirst(str_replace('_', ' ', $tab_key)),
                    'enabled' => true,
                    'abilities' => array(),
                );
            }
            
            // Add feature to tab
            $tabs[$tab_key]['abilities'][$key] = array(
                'label' => isset($feature['title']) ? $feature['title'] : ucfirst(str_replace('_', ' ', $key)),
                'status' => isset($feature['status']) ? $feature['status'] : 'planned',
                'enabled' => isset($feature['enabled']) ? $feature['enabled'] : true,
                'version' => isset($feature['version']) ? $feature['version'] : '1.0.0',
            );
        }
        
        // Ensure we have at least one tab
        if (empty($tabs)) {
            $tabs['general'] = array(
                'label' => __('General', 'tradepress'),
                'enabled' => true,
                'abilities' => array()
            );
        }
        
        return $tabs;
    }
    
    /**
     * Convert system features to the expected format
     *
     * @param string $system_key The system key
     * @return array Formatted system features
     */
    private static function convert_system_features($system_key) {
        $formatted_features = array();
        $features = self::{"get_{$system_key}_features"}();
        
        if (empty($features)) {
            return array();
        }
        
        foreach ($features as $key => $feature) {
            $formatted_features[$key] = array(
                'label' => isset($feature['title']) ? $feature['title'] : $key,
                'status' => isset($feature['status']) ? $feature['status'] : 'planned',
                'enabled' => isset($feature['enabled']) ? $feature['enabled'] : true,
                'version' => isset($feature['version']) ? $feature['version'] : '1.0.0',
            );
        }
        
        return $formatted_features;
    }

    /**
     * Get features for the Dashboard page
     *
     * @return array Array of dashboard features with status and implementation details
     */
    public static function get_dashboard_features() {
        return tradepress_get_dashboard_features();
    }
    
    /**
     * Get features for the Settings page
     *
     * @return array Array of settings features with status and implementation details
     */
    public static function get_settings_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_settings_features')) {
            return tradepress_get_settings_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for the Trading Platforms page
     *
     * @return array Array of trading platform features with status and implementation details
     */
    public static function get_trading_platforms_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_trading_platforms_features')) {
            return tradepress_get_trading_platforms_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for the Social Platforms page
     *
     * @return array Array of social platform features with status and implementation details
     */
    public static function get_social_platforms_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_social_platforms_features')) {
            return tradepress_get_social_platforms_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for the Debug page
     *
     * @return array Array of debug features with status and implementation details
     */
    public static function get_debug_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_debug_features')) {
            return tradepress_get_debug_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for the Automation page
     *
     * @return array Array of automation features with status and implementation details
     */
    public static function get_automation_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_automation_features')) {
            return tradepress_get_automation_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for the Research page
     *
     * @return array Array of research features with status and implementation details
     */
    public static function get_research_features() {
        return tradepress_get_research_features();
    }
    
    /**
     * Get features for the Sandbox page
     *
     * @return array Array of sandbox features with status and implementation details
     */
    public static function get_sandbox_features() {
        return tradepress_get_sandbox_features();
    }
    
    /**
     * Get features for the Development page
     *
     * @return array Array of development features with status and implementation details
     */
    public static function get_development_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_development_features')) {
            return tradepress_get_development_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for the Trading page
     *
     * @return array Array of trading features with status and implementation details
     */
    public static function get_trading_features() {
        return tradepress_get_trading_features();
    }
    
    /**
     * Get features for the Watchlists page
     *
     * @return array Array of watchlist features with status and implementation details
     */
    public static function get_watchlists_features() {
        return tradepress_get_watchlists_features();
    }
    
    /**
     * Get features for the Data page
     *
     * @return array Array of data features with status and implementation details
     */
    public static function get_data_features() {
        return tradepress_get_data_features();
    }
    
    /**
     * Get features for the Analysis page
     *
     * @return array Array of analysis features with status and implementation details
     */
    public static function get_analysis_features() {
        return tradepress_get_analysis_features();
    }
    
    /**
     * Get features for API handling
     *
     * @return array Array of API features with status and implementation details
     */
    public static function get_api_features() {
        return tradepress_get_api_features();
    }
    
    /**
     * Get features for Database management
     *
     * @return array Array of database features with status and implementation details
     */
    public static function get_database_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_database_features')) {
            return tradepress_get_database_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for Trading Strategies
     *
     * @return array Array of trading strategy features with status and implementation details
     */
    public static function get_trading_strategies() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_trading_strategies')) {
            return tradepress_get_trading_strategies();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for Scoring Algorithm and directives
     *
     * @return array Array of scoring algorithm features with status and implementation details
     */
    public static function get_scoring_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_scoring_features')) {
            return tradepress_get_scoring_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for CRON/Scheduling
     *
     * @return array Array of CRON features with status and implementation details
     */
    public static function get_cron_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_cron_features')) {
            return tradepress_get_cron_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for User Management
     *
     * @return array Array of user management features with status and implementation details
     */
    public static function get_user_management_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_user_management_features')) {
            return tradepress_get_user_management_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for External Integrations
     *
     * @return array Array of integration features with status and implementation details
     */
    public static function get_integration_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_integration_features')) {
            return tradepress_get_integration_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for UI Components
     *
     * @return array Array of UI features with status and implementation details
     */
    public static function get_ui_features() {
        return tradepress_get_ui_features();
    }

    /**
     * Get features for Phase 1 Scoring Implementation Plan
     *
     * @return array Array of scoring implementation features with status and implementation details
     */
    public static function get_scoring_implementation() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_scoring_implementation')) {
            return tradepress_get_scoring_implementation();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for Phase 2 Data Optimization Implementation Plan
     *
     * @return array Array of data optimization implementation features with status and details
     */
    public static function get_data_optimization_implementation() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_data_optimization_implementation')) {
            return tradepress_get_data_optimization_implementation();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for Phase 3 Strategy System Implementation Plan
     *
     * @return array Array of strategy implementation features with status and details
     */
    public static function get_strategy_implementation() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_strategy_implementation')) {
            return tradepress_get_strategy_implementation();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for Symbol Management
     *
     * @return array Array of symbol management features with status and implementation details
     */
    public static function get_symbols_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_symbols_features')) {
            return tradepress_get_symbols_features();
        }
        return array(); // Empty array as fallback
    }
    
    /**
     * Get features for Documentation
     *
     * @return array Array of documentation features with status and implementation details
     */
    public static function get_documentation_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_documentation_features')) {
            return tradepress_get_documentation_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for Advanced Trading Automation
     *
     * @return array Array of advanced trading automation features with status and implementation details
     */
    public static function get_advanced_trading_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_advanced_trading_features')) {
            return tradepress_get_advanced_trading_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for Market Forecast Implementation
     *
     * @return array Array of market forecast implementation features with status and implementation details
     */
    public static function get_forecast_implementation() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_forecast_implementation')) {
            return tradepress_get_forecast_implementation();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for Forecast Management
     *
     * @return array Array of forecast management features with status and implementation details
     */
    public static function get_forecast_management_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_forecast_management_features')) {
            return tradepress_get_forecast_management_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for Plugin Modes System
     *
     * @return array Array of plugin modes features with status and implementation details
     */
    public static function get_plugin_modes_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_plugin_modes_features')) {
            return tradepress_get_plugin_modes_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for API Integrations
     *
     * @return array Array of API integration features with status and implementation details
     */
    public static function get_api_integrations_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_api_integrations_features')) {
            return tradepress_get_api_integrations_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for the Trading Academy educational system
     *
     * @return array Array of education academy features with status and implementation details
     */
    public static function get_education_academy_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_education_academy_features')) {
            return tradepress_get_education_academy_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for the DAASG (Drawn Analysis Algorithmic Signal Generator) system
     *
     * @return array Array of DAASG features with status and implementation details
     */
    public static function get_daasg_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_daasg_features')) {
            return tradepress_get_daasg_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for Trend Calculator system
     *
     * @return array Array of trend calculator features with status and implementation details
     */
    public static function get_trend_calculator_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_trend_calculator_features')) {
            return tradepress_get_trend_calculator_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for the Frontend system
     *
     * @return array Array of frontend features with status and implementation details
     */
    public static function get_frontend_features() {
        // Call the external function if it exists
        if (function_exists('tradepress_get_frontend_features')) {
            return tradepress_get_frontend_features();
        }
        return array(); // Empty array as fallback
    }

    /**
     * Get features for Community Engagement
     *
     * @return array Array of community features with status and implementation details
     */
    public static function get_community_features() {
        if (function_exists('tradepress_get_community_features')) {
            return tradepress_get_community_features();
        }
        return array();
    }
}