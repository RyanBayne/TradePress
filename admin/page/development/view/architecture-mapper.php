<?php
/**
 * TradePress Architecture Mapper
 * 
 * Generates visual tree maps of the plugin architecture including systems,
 * classes, methods, files, and relationships for development guidance.
 * 
 * @package TradePress/Development
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Architecture_Mapper {
    
    /**
     * Get the complete architecture map
     * 
     * @return array Architecture tree structure
     */
    public static function get_architecture_map() {
        return array(
            'core_systems' => array(
                'name' => 'Core Systems',
                'description' => 'Foundational systems that power the TradePress plugin',
                'type' => 'system',
                'children' => array(
                    'data_system' => array(
                        'name' => 'Data System',
                        'description' => 'Core data management, processing, and synchronization',
                        'type' => 'system',
                        'file' => 'includes/data-manager.php',
                        'key_systems' => array('API System', 'Database System'),
                        'key_classes' => array('TradePress_Data_Manager', 'TradePress_Data_Sync'),
                        'tables' => array(
                            'tradepress_data_queue' => 'Manages data synchronization queue',
                            'tradepress_data_cache' => 'Temporary data storage and caching',
                            'tradepress_data_logs' => 'Data operation logging'
                        ),
                        'children' => array(
                            'freshness_manager' => array(
                                'name' => 'Data Freshness Manager',
                                'type' => 'component',
                                'purpose' => 'Ensures data quality and timeliness across all systems',
                                'features' => array('Data validation', 'Freshness checks', 'Cache management', 'Update scheduling'),
                                'supporting_classes' => array('TradePress_Data_Validator', 'TradePress_Cache_Manager'),
                                'file' => 'includes/data-freshness-manager.php',
                                'implementation_status' => 'Core implemented with Recent Call Register'
                            ),
                            'background_processor' => array(
                                'name' => 'Background Processing System',
                                'type' => 'component',
                                'purpose' => 'Handles asynchronous tasks and data processing',
                                'features' => array('Queue management', 'Process monitoring', 'Error handling', 'Task scheduling'),
                                'supporting_classes' => array('TradePress_Background_Process', 'TradePress_Task_Queue'),
                                'file' => 'includes/background-processor.php',
                                'implementation_status' => 'Core implemented with process monitoring'
                            ),
                            'scoring_system' => array(
                                'name' => 'Scoring System',
                                'type' => 'component',
                                'purpose' => 'Implements trading algorithms and scoring directives',
                                'features' => array(
                                    'Directive processing',
                                    'Score calculation',
                                    'Technical analysis',
                                    'Performance monitoring'
                                ),
                                'supporting_classes' => array(
                                    'TradePress_Scoring_Algorithm',
                                    'TradePress_Directive_Handler',
                                    'TradePress_Technical_Indicators'
                                ),
                                'file' => 'includes/scoring/scoring-algorithm.php',
                                'implementation_status' => 'Active development - Phase 3',
                                'tables' => array(
                                    'tradepress_scoring_results' => 'Stores calculated scores',
                                    'tradepress_score_history' => 'Historical score tracking',
                                    'tradepress_technical_indicators' => 'Technical analysis data'
                                )
                            )
                        )
                    ),
                    'development_page' => array(
                        'name' => 'Development',
                        'description' => 'Development tools, architecture maps, debugging utilities',
                        'type' => 'page',
                        'file' => 'admin/page/development/development-tabs.php',
                        'key_systems' => array('Logging System', 'GitHub Integration'),
                        'key_classes' => array('TradePress_Architecture_Mapper', 'TradePress_Logger'),
                        'css_files' => array('development-assets.css', 'duplicate-checker.css'),
                        'js_files' => array('tradepress-development-tabs.js', 'duplicate-checker.js', 'development-assets.js'),
                        'children' => array(
                            'architecture_tab' => array(
                                'name' => 'Architecture Map',
                                'type' => 'tab',
                                'purpose' => 'Visual guide to plugin architecture for developers and AI',
                                'features' => array('System overview', 'Class relationships', 'Implementation guidance'),
                                'supporting_classes' => array('TradePress_Architecture_Mapper')
                            ),
                            'current_task_tab' => array(
                                'name' => 'Current Task',
                                'type' => 'tab',
                                'purpose' => 'Track current development tasks and progress',
                                'features' => array('Task management', 'Progress tracking'),
                                'supporting_classes' => array('TradePress_Admin_Development_Current_Task')
                            )
                        )
                    ),
                    'data_page' => array(
                        'name' => 'Data',
                        'description' => 'Data import, management, and analysis tools',
                        'type' => 'page',
                        'key_systems' => array('Data System', 'API System'),
                        'key_classes' => array('TradePress_Symbols', 'TradePress_Data_Import_Process'),
                        'children' => array(
                            'symbols_tab' => array(
                                'name' => 'Symbols',
                                'type' => 'tab',
                                'purpose' => 'Manage stock symbols, import data, track symbol usage',
                                'features' => array('Symbol management', 'Data import', 'Usage tracking'),
                                'supporting_classes' => array('TradePress_Symbols', 'TradePress_Symbol')
                            )
                        )
                    ),
                    'trading_page' => array(
                        'name' => 'Trading',
                        'description' => 'Trading strategies, portfolio management, order execution',
                        'type' => 'page',
                        'key_systems' => array('API System', 'Trading System'),
                        'key_classes' => array('TradePress_Trading_Algo', 'TradePress_Base_API'),
                        'children' => array(
                            'strategies_tab' => array(
                                'name' => 'Strategies',
                                'type' => 'tab',
                                'purpose' => 'Create and manage trading strategies',
                                'features' => array('Strategy creation', 'Backtesting', 'Performance tracking'),
                                'supporting_classes' => array('TradePress_Trading_Algo')
                            )
                        )
                    ),
                    'analysis_page' => array(
                        'name' => 'Analysis',
                        'description' => 'Technical analysis, scoring algorithms, market research',
                        'type' => 'page',
                        'key_systems' => array('Scoring System', 'Data System'),
                        'key_classes' => array('TradePress_Scoring_Algorithm', 'TradePress_Technical_Indicators'),
                        'children' => array(
                            'scoring_directives_tab' => array(
                                'name' => 'Scoring Directives',
                                'type' => 'tab',
                                'purpose' => 'Configure and test scoring directives with real API data',
                                'features' => array('Directive configuration', 'Real-time testing', 'API integration', 'Data freshness validation'),
                                'supporting_classes' => array('TradePress_Directive_Handler', 'TradePress_Data_Freshness_Manager'),
                                'implementation_status' => 'Test button now uses real API data instead of dummy data'
                            )
                        )
                    ),
                    'automation_page' => array(
                        'name' => 'Automation',
                        'description' => 'Automated trading, cron jobs, background processes',
                        'type' => 'page',
                        'key_systems' => array('Automation System', 'API System'),
                        'key_classes' => array('TradePress_Background_Process', 'TradePress_Cron'),
                        'css_files' => array('admin-automation.css'),
                        'js_files' => array('admin-automation.js', 'cron-tab.js'),
                        'children' => array(
                            'cron_tab' => array(
                                'name' => 'Cron Jobs',
                                'type' => 'tab',
                                'purpose' => 'Manage scheduled tasks and background processes',
                                'features' => array('Job scheduling', 'Status monitoring', 'Log viewing'),
                                'supporting_classes' => array('TradePress_Background_Process')
                            )
                        )
                    ),
                    'research_page' => array(
                        'name' => 'Research',
                        'description' => 'Market research, earnings data, economic indicators',
                        'type' => 'page',
                        'key_systems' => array('Data System', 'API System'),
                        'key_classes' => array('TradePress_Earnings_Calendar', 'TradePress_Research'),
                        'children' => array(
                            'earnings_tab' => array(
                                'name' => 'Earnings',
                                'type' => 'tab',
                                'purpose' => 'Track earnings announcements and analyze impact',
                                'features' => array('Earnings calendar', 'Historical data', 'Impact analysis'),
                                'supporting_classes' => array('TradePress_Earnings_Calendar')
                            )
                        )
                    ),
                    'symbols_page' => array(
                        'name' => 'Symbols',
                        'description' => 'Symbol management, watchlists, symbol data',
                        'type' => 'page',
                        'key_systems' => array('Symbols System', 'Data System'),
                        'key_classes' => array('TradePress_Symbols', 'TradePress_Symbol'),
                        'children' => array(
                            'watchlists_tab' => array(
                                'name' => 'Watchlists',
                                'type' => 'tab',
                                'purpose' => 'Create and manage symbol watchlists',
                                'features' => array('List creation', 'Symbol tracking', 'Performance monitoring'),
                                'supporting_classes' => array('TradePress_Symbols')
                            )
                        )
                    ),
                    'settings_page' => array(
                        'name' => 'Settings',
                        'description' => 'Plugin configuration, user preferences, system settings',
                        'type' => 'page',
                        'key_systems' => array('Admin System'),
                        'key_classes' => array('TradePress_Admin', 'TradePress_Settings'),
                        'children' => array(
                            'general_tab' => array(
                                'name' => 'General',
                                'type' => 'tab',
                                'purpose' => 'Configure general plugin settings and preferences',
                                'features' => array('Plugin options', 'User preferences', 'System configuration'),
                                'supporting_classes' => array('TradePress_Settings')
                            )
                        )
                    ),
                    'socialplatforms_page' => array(
                        'name' => 'Social Platforms',
                        'description' => 'Social media integration, Discord, Telegram notifications',
                        'type' => 'page',
                        'key_systems' => array('API System', 'Notification System'),
                        'key_classes' => array('TRADEPRESS_DISCORD_API', 'TradePress_Telegram_API'),
                        'js_files' => array('socialplatforms-discord-settings.js', 'socialplatforms-stocktwits.js'),
                        'children' => array(
                            'discord_tab' => array(
                                'name' => 'Discord',
                                'type' => 'tab',
                                'purpose' => 'Configure Discord bot integration and notifications',
                                'features' => array('Bot configuration', 'Channel management', 'Alert setup'),
                                'supporting_classes' => array('TRADEPRESS_DISCORD_API', 'TRADEPRESS_DISCORD_Connection_Manager'),
                                'js_files' => array('socialplatforms-discord-settings.js')
                            )
                        )
                    ),
                    'scoring_directives_page' => array(
                        'name' => 'Scoring Directives',
                        'description' => 'Configure scoring algorithms and technical indicators',
                        'type' => 'page',
                        'key_systems' => array('Scoring System'),
                        'key_classes' => array('TradePress_Scoring_Directive_Base', 'TradePress_Technical_Indicators'),
                        'js_files' => array('tradepress-scoring-directives.js'),
                        'children' => array(
                            'directives_tab' => array(
                                'name' => 'Directives',
                                'type' => 'tab',
                                'purpose' => 'Manage and configure scoring directives',
                                'features' => array('Directive management', 'Parameter tuning', 'Performance testing'),
                                'supporting_classes' => array('TradePress_Scoring_Directive_Base'),
                                'js_files' => array('tradepress-scoring-directives.js')
                            )
                        )
                    )
                )
            ),
            'data_freshness_framework' => array(
                'name' => 'Data Freshness Framework',
                'description' => 'Gatekeeper layer ensuring data quality and freshness before algorithm execution',
                'type' => 'system',
                'implementation_status' => 'Core implemented with Recent Call Register integration',
                'children' => array(
                    'call_register' => array(
                        'name' => 'Recent Call Register',
                        'file' => 'includes/query-register.php',
                        'type' => 'class',
                        'methods' => array('generate_serial', 'get_current_transient_key', 'check_recent_call', 'register_call', 'get_cached_result', 'cache_result'),
                        'purpose' => 'Time-based transient system preventing duplicate API calls across all plugin features',
                        'usage_pattern' => 'ALL API calls should use Call Register for caching and deduplication',
                        'implementation_status' => 'Fully implemented - Phase 1 & 2 complete',
                        'key_features' => array(
                            'Platform-aware caching' => 'alphavantage.get_quote(AAPL) vs finnhub.get_quote(AAPL)',
                            'Hourly transient rotation' => 'Automatic cleanup every hour',
                            'Cross-feature sharing' => 'One API call shared by all features',
                            'Serial-based deduplication' => 'md5(platform + method + parameters)'
                        ),
                        'transient_format' => 'tradepress_call_register_YYYYMMDDHH',
                        'integration_status' => 'Integrated with Directive Handler and Data Freshness Manager'
                    ),
                    'freshness_manager' => array(
                        'name' => 'Data Freshness Manager',
                        'file' => 'includes/data-freshness-manager.php',
                        'type' => 'class',
                        'methods' => array('validate_data_freshness', 'ensure_data_freshness', 'trigger_data_update', 'needs_update'),
                        'purpose' => 'Central coordinator for all data freshness validation with API integration',
                        'usage_pattern' => 'ALL algorithms must validate data through this class before execution',
                        'implementation_status' => 'Updated to use Recent Call Register for caching',
                        'use_cases' => array(
                            'cfd_trading' => '1-5 minute freshness',
                            'swing_trading' => '1-2 hour freshness', 
                            'scoring_algorithms' => '30 minute to 24 hour freshness',
                            'test_button' => 'Always fresh for manual testing'
                        )
                    ),
                    'object_registry_integration' => array(
                        'name' => 'Object Registry Integration',
                        'file' => 'includes/object-registry-enhanced.php',
                        'type' => 'enhancement',
                        'methods' => array('tradepress_build_symbol_data', 'tradepress_get_symbol', 'tradepress_get_fresh_symbol'),
                        'purpose' => 'Builds symbol objects with fresh API data automatically',
                        'usage_pattern' => 'Use tradepress_get_symbol() or tradepress_get_fresh_symbol() throughout plugin',
                        'implementation_status' => 'Implemented with automatic freshness checking'
                    ),
                    'test_integration' => array(
                        'name' => 'Test Button Integration',
                        'file' => 'admin/page/scoring-directives/directive-handler.php',
                        'type' => 'feature',
                        'methods' => array('test_directive', 'test_rsi_directive'),
                        'purpose' => 'Real API testing with Call Register caching for directive testing',
                        'implementation_status' => 'Updated to use Recent Call Register - eliminates duplicate API calls',
                        'caching_behavior' => 'Multiple test clicks within 10 minutes use cached data'
                    )
                )
            ),
            'api_system' => array(
                'name' => 'API System',
                'description' => 'Unified API integration framework with provider management',
                'type' => 'system',
                'implementation_status' => 'Core implemented with provider framework',
                'children' => array(
                    'base_classes' => array(
                        'name' => 'Base Classes',
                        'type' => 'category',
                        'children' => array(
                            'TradePress_Base_API' => array(
                                'name' => 'TradePress_Base_API',
                                'file' => 'api/base-api.php',
                                'type' => 'class',
                                'methods' => array('test_connection', 'get_quote', 'make_request', 'validate_credentials'),
                                'required_methods' => array('test_connection', 'get_quote'),
                                'extends' => 'TradePress_Financial_API_Service',
                                'usage_pattern' => 'All API providers MUST extend this class',
                                'implementation_example' => 'class MyAPI extends TradePress_Base_API { public function test_connection() { ... } }'
                            ),
                            'TradePress_Financial_API_Service' => array(
                                'name' => 'TradePress_Financial_API_Service',
                                'file' => 'api/financial-api-service.php',
                                'type' => 'class',
                                'methods' => array('make_request', 'get_auth_header', 'update_rate_limits'),
                                'provides' => 'Core HTTP request handling, authentication, rate limiting',
                                'usage_pattern' => 'Extended by TradePress_Base_API - do not extend directly'
                            ),
                            'TradePress_API_Adapter' => array(
                                'name' => 'TradePress_API_Adapter',
                                'file' => 'api/api-adapter.php',
                                'type' => 'class',
                                'methods' => array('normalize_quote_data', 'normalize_historical_data'),
                                'usage_pattern' => 'Create adapter for each API to normalize data formats'
                            )
                        )
                    ),
                    'api_providers' => array(
                        'name' => 'API Providers',
                        'type' => 'category',
                        'children' => array(
                            'alpaca' => array(
                                'name' => 'Alpaca API',
                                'file' => 'api/alpaca/alpaca-api.php',
                                'type' => 'provider',
                                'api_type' => 'trading',
                                'methods' => array('get_account', 'get_positions', 'place_order', 'get_bars'),
                                'extends' => 'TradePress_Base_API',
                                'credentials' => array('api_key', 'api_secret'),
                                'modes' => array('paper', 'live'),
                                'implementation_status' => 'Updated to use base class with usage tracking'
                            ),
                            'alphavantage' => array(
                                'name' => 'Alpha Vantage API',
                                'file' => 'api/alphavantage/alphavantage-api.php',
                                'type' => 'provider',
                                'api_type' => 'data_only',
                                'methods' => array('get_global_quote', 'get_earnings_calendar'),
                                'extends' => 'TradePress_Base_API',
                                'credentials' => array('api_key'),
                                'implementation_status' => 'Integrated with Usage Tracker and Fallback System',
                                'freshness_integration' => 'Used by test buttons and symbol data building'
                            ),
                            'finnhub' => array(
                                'name' => 'Finnhub API',
                                'file' => 'api/finnhub/finnhub-api.php',
                                'type' => 'provider',
                                'api_type' => 'data_only',
                                'methods' => array('get_quote', 'get_company_profile'),
                                'extends' => 'TradePress_Base_API',
                                'credentials' => array('api_key'),
                                'implementation_status' => 'Fallback provider for Alpha Vantage rate limits'
                            )
                        )
                    ),
                    'usage_tracking' => array(
                        'name' => 'API Usage Tracking & Fallback System',
                        'type' => 'system',
                        'file' => 'includes/api-usage-tracker.php',
                        'description' => 'Intelligent API switching when rate limits are detected',
                        'methods' => array('track_call', 'mark_rate_limited', 'is_likely_rate_limited', 'get_best_api_for_data'),
                        'features' => array(
                            'Daily usage tracking per provider',
                            'Automatic rate limit detection',
                            'Intelligent API fallback selection',
                            'Developer notices for API switching',
                            'Usage statistics and reporting'
                        ),
                        'integration_points' => array(
                            'TradePress_Base_API' => 'Automatic usage tracking on all API calls',
                            'TradePress_API_Factory' => 'Fallback logic when creating API instances',
                            'Developer Notices' => 'Real-time notifications of API switching'
                        ),
                        'fallback_priority' => array(
                            'quote' => 'alphavantage → finnhub → alpaca',
                            'technical_indicators' => 'alphavantage → finnhub',
                            'news' => 'finnhub → alphavantage',
                            'fundamentals' => 'alphavantage → finnhub'
                        ),
                        'implementation_status' => 'Fully implemented with automatic fallback'
                    ),
                    'factory' => array(
                        'name' => 'API Factory',
                        'file' => 'api/api-factory.php',
                        'type' => 'factory',
                        'methods' => array('create', 'create_from_settings', 'test_all_apis', 'create_with_fallback'),
                        'usage_pattern' => 'ALWAYS use factory to create API instances',
                        'example' => '$api = TradePress_API_Factory::create("alpaca", $args);',
                        'validates' => 'Ensures APIs extend TradePress_Base_API',
                        'fallback_integration' => 'Integrates with Usage Tracker for intelligent API selection',
                        'implementation_status' => 'Enhanced with fallback logic and usage tracking'
                    ),
                    'directory' => array(
                        'name' => 'API Directory',
                        'file' => 'api/api-directory.php',
                        'type' => 'registry',
                        'methods' => array('get_all_providers', 'get_provider')
                    )
                )
            ),
            'logging_system' => array(
                'name' => 'Logging System',
                'description' => 'Centralized logging and monitoring',
                'type' => 'system',
                'children' => array(
                    'TradePress_API_Logging' => array(
                        'name' => 'API Logging',
                        'file' => 'includes/api-logging.php',
                        'type' => 'class',
                        'methods' => array('log_call', 'track_endpoint', 'log_error', 'add_meta'),
                        'tables' => array('tradepress_calls', 'tradepress_endpoints', 'tradepress_errors', 'tradepress_meta'),
                        'usage_pattern' => 'Automatically used by TradePress_Base_API',
                        'call_flow' => '1. log_call() 2. track_endpoint() 3. log_error() if needed 4. add_meta()'
                    ),
                    'TradePress_Logger' => array(
                        'name' => 'General Logger',
                        'file' => 'logging/logger.php',
                        'type' => 'class',
                        'methods' => array('error', 'warning', 'info', 'debug'),
                        'tables' => array('tradepress_logs')
                    )
                )
            ),
            'system_interactions' => array(
                'name' => 'System Interactions',
                'description' => 'Cross-system dependencies and data flows',
                'type' => 'system_map',
                'children' => array(
                    'data_flows' => array(
                        'name' => 'Data Flows',
                        'type' => 'flow',
                        'description' => 'Primary data paths between systems',
                        'flows' => array(
                            'api_to_data' => 'API System → Data System (Raw data ingestion)',
                            'data_to_scoring' => 'Data System → Scoring System (Analysis input)',
                            'scoring_to_trading' => 'Scoring System → Trading System (Decision signals)'
                        )
                    ),
                    'dependencies' => array(
                        'name' => 'System Dependencies',
                        'type' => 'dependency_map',
                        'description' => 'Critical system dependencies',
                        'dependencies' => array(
                            'data_system' => array('API System', 'Background Processing'),
                            'scoring_system' => array('Data System', 'Technical Indicators'),
                            'trading_system' => array('Scoring System', 'Risk Management')
                        )
                    )
                )
            ),
            'admin_system' => array(
                'name' => 'Admin System',
                'description' => 'WordPress admin interface and pages',
                'type' => 'system',
                'children' => array(
                    'trading_platforms' => array(
                        'name' => 'Trading Platforms Page',
                        'type' => 'page',
                        'children' => array(
                            'main_page' => array(
                                'name' => 'Main Page',
                                'file' => 'admin/page/tradingplatforms/tradingplatforms.php',
                                'type' => 'view'
                            ),
                            'api_tabs' => array(
                                'name' => 'API Tabs',
                                'file' => 'admin/page/tradingplatforms/view/template.api-tab.php',
                                'type' => 'template',
                                'partials' => array(
                                    'api-service-overview.php',
                                    'api-status-overview.php',
                                    'config-data-only.php',
                                    'quick-actions.php'
                                )
                            )
                        )
                    ),
                    'development_page' => array(
                        'name' => 'Development Page',
                        'type' => 'page',
                        'children' => array(
                            'architecture_mapper' => array(
                                'name' => 'Architecture Mapper',
                                'file' => 'admin/page/development/architecture-mapper.php',
                                'type' => 'class'
                            )
                        )
                    )
                )
            ),
            'data_system' => array(
                'name' => 'Data System',
                'description' => 'Data management and processing',
                'type' => 'system',
                'children' => array(
                    'symbols' => array(
                        'name' => 'Symbol Management',
                        'file' => 'classes/symbols.php',
                        'type' => 'class'
                    ),
                    'scoring' => array(
                        'name' => 'Scoring System',
                        'file' => 'includes/scoring-system/scoring-algorithm.php',
                        'type' => 'class'
                    )
                )
            )
        );
    }
    
    /**
     * Render the architecture tree as HTML
     * 
     * @return string HTML tree structure
     */
    public static function render_tree() {
        $map = self::get_architecture_map();
        $html = '<div class="tradepress-architecture-tree">';
        $html .= self::render_tree_node($map, 0);
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render a single tree node
     * 
     * @param array $nodes Tree nodes
     * @param int $level Nesting level
     * @return string HTML for nodes
     */
    private static function render_tree_node($nodes, $level = 0) {
        $html = '';
        
        foreach ($nodes as $key => $node) {
            $indent = str_repeat('  ', $level);
            $type_class = isset($node['type']) ? 'tree-' . $node['type'] : 'tree-item';
            
            $html .= "<div class='tree-node {$type_class}' data-level='{$level}'>";
            $html .= "<div class='tree-item-header'>";
            
            // Add expand/collapse icon if has children
            if (isset($node['children'])) {
                $html .= "<span class='tree-toggle'>▼</span>";
            } else {
                $html .= "<span class='tree-spacer'></span>";
            }
            
            // Add type icon
            $icon = self::get_type_icon($node['type'] ?? 'item');
            $html .= "<span class='tree-icon'>{$icon}</span>";
            
            // Add name
            $html .= "<span class='tree-name'>{$node['name']}</span>";
            
            // Add file path if available
            if (isset($node['file'])) {
                $html .= "<span class='tree-file'>{$node['file']}</span>";
            }
            
            // Add API type badge if available
            if (isset($node['api_type'])) {
                $html .= "<span class='tree-badge api-type-{$node['api_type']}'>{$node['api_type']}</span>";
            }
            
            $html .= "</div>";
            
            // Add description if available
            if (isset($node['description'])) {
                $html .= "<div class='tree-description'>{$node['description']}</div>";
            }
            
            // Add methods if available
            if (isset($node['methods'])) {
                $html .= "<div class='tree-methods'>";
                $html .= "<strong>Methods:</strong> " . implode(', ', $node['methods']);
                $html .= "</div>";
            }
            
            // Add required methods if available
            if (isset($node['required_methods'])) {
                $html .= "<div class='tree-required-methods'>";
                $html .= "<strong>Required Methods:</strong> " . implode(', ', $node['required_methods']);
                $html .= "</div>";
            }
            
            // Add usage pattern if available
            if (isset($node['usage_pattern'])) {
                $html .= "<div class='tree-usage-pattern'>";
                $html .= "<strong>Usage:</strong> {$node['usage_pattern']}";
                $html .= "</div>";
            }
            
            // Add implementation example if available
            if (isset($node['implementation_example'])) {
                $html .= "<div class='tree-implementation-example'>";
                $html .= "<strong>Example:</strong> <code>{$node['implementation_example']}</code>";
                $html .= "</div>";
            }
            
            // Add extends information if available
            if (isset($node['extends'])) {
                $html .= "<div class='tree-extends'>";
                $html .= "<strong>Extends:</strong> {$node['extends']}";
                $html .= "</div>";
            }
            
            // Add credentials if available
            if (isset($node['credentials'])) {
                $html .= "<div class='tree-credentials'>";
                $html .= "<strong>Credentials:</strong> " . implode(', ', $node['credentials']);
                $html .= "</div>";
            }
            
            // Add implementation status if available
            if (isset($node['implementation_status'])) {
                $status_class = strpos($node['implementation_status'], 'Updated') !== false ? 'status-good' : 'status-needs-work';
                $html .= "<div class='tree-implementation-status {$status_class}'>";
                $html .= "<strong>Status:</strong> {$node['implementation_status']}";
                $html .= "</div>";
            }
            
            // Add tables if available
            if (isset($node['tables'])) {
                $html .= "<div class='tree-tables'>";
                $html .= "<strong>Tables:</strong> " . implode(', ', $node['tables']);
                $html .= "</div>";
            }
            
            // Add partials if available
            if (isset($node['partials'])) {
                $html .= "<div class='tree-partials'>";
                $html .= "<strong>Partials:</strong> " . implode(', ', $node['partials']);
                $html .= "</div>";
            }
            
            // Add key systems if available
            if (isset($node['key_systems'])) {
                $html .= "<div class='tree-key-systems'>";
                $html .= "<strong>Key Systems:</strong> " . implode(', ', $node['key_systems']);
                $html .= "</div>";
            }
            
            // Add key classes if available
            if (isset($node['key_classes'])) {
                $html .= "<div class='tree-key-classes'>";
                $html .= "<strong>Key Classes:</strong> " . implode(', ', $node['key_classes']);
                $html .= "</div>";
            }
            
            // Add purpose if available
            if (isset($node['purpose'])) {
                $html .= "<div class='tree-purpose'>";
                $html .= "<strong>Purpose:</strong> {$node['purpose']}";
                $html .= "</div>";
            }
            
            // Add features if available
            if (isset($node['features'])) {
                $html .= "<div class='tree-features'>";
                $html .= "<strong>Features:</strong> " . implode(', ', $node['features']);
                $html .= "</div>";
            }
            
            // Add supporting classes if available
            if (isset($node['supporting_classes'])) {
                $html .= "<div class='tree-supporting-classes'>";
                $html .= "<strong>Supporting Classes:</strong> " . implode(', ', $node['supporting_classes']);
                $html .= "</div>";
            }
            
            // Add CSS files if available
            if (isset($node['css_files'])) {
                $html .= "<div class='tree-css-files'>";
                $html .= "<strong>CSS Files:</strong> " . implode(', ', $node['css_files']);
                $html .= "</div>";
            }
            
            // Add JS files if available
            if (isset($node['js_files'])) {
                $html .= "<div class='tree-js-files'>";
                $html .= "<strong>JS Files:</strong> " . implode(', ', $node['js_files']);
                $html .= "</div>";
            }
            
            // Render children
            if (isset($node['children'])) {
                $html .= "<div class='tree-children'>";
                $html .= self::render_tree_node($node['children'], $level + 1);
                $html .= "</div>";
            }
            
            $html .= "</div>";
        }
        
        return $html;
    }
    
    /**
     * Get icon for node type
     * 
     * @param string $type Node type
     * @return string Icon HTML
     */
    private static function get_type_icon($type) {
        $icons = array(
            'system' => '🏗️',
            'class' => '📦',
            'category' => '📁',
            'provider' => '🔌',
            'factory' => '🏭',
            'registry' => '📋',
            'page' => '📄',
            'tab' => '📑',
            'view' => '👁️',
            'template' => '📝',
            'item' => '📄'
        );
        
        return $icons[$type] ?? '📄';
    }
    
    /**
     * Get CSS styles for the tree
     * 
     * @return string CSS styles
     */
    public static function get_tree_styles() {
        return "
        .tradepress-architecture-tree {
            font-family: 'Courier New', monospace;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
        }
        
        .tree-node {
            margin: 2px 0;
            border-left: 2px solid transparent;
        }
        
        .tree-system { border-left-color: #007cba; }
        .tree-class { border-left-color: #00a32a; }
        .tree-category { border-left-color: #dba617; }
        .tree-provider { border-left-color: #d63638; }
        .tree-factory { border-left-color: #8c44c4; }
        .tree-registry { border-left-color: #00a0d2; }
        .tree-page { border-left-color: #ff6900; }
        .tree-tab { border-left-color: #ff9500; }
        .tree-view { border-left-color: #826eb4; }
        .tree-template { border-left-color: #c9356e; }
        
        .tree-item-header {
            display: flex;
            align-items: center;
            padding: 4px 8px;
            cursor: pointer;
            border-radius: 3px;
        }
        
        .tree-item-header:hover {
            background: #e8f4f8;
        }
        
        .tree-toggle {
            width: 16px;
            font-size: 12px;
            cursor: pointer;
            user-select: none;
        }
        
        .tree-spacer {
            width: 16px;
        }
        
        .tree-icon {
            margin: 0 8px;
            font-size: 16px;
        }
        
        .tree-name {
            font-weight: bold;
            color: #333;
        }
        
        .tree-file {
            margin-left: 12px;
            color: #666;
            font-size: 12px;
            font-style: italic;
        }
        
        .tree-badge {
            margin-left: 8px;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .api-type-trading {
            background: #d4edda;
            color: #155724;
        }
        
        .api-type-data_only {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .tree-description {
            margin: 4px 0 4px 40px;
            color: #666;
            font-size: 13px;
            font-style: italic;
        }
        
        .tree-methods, .tree-tables, .tree-partials, .tree-required-methods, 
        .tree-usage-pattern, .tree-implementation-example, .tree-extends, 
        .tree-credentials, .tree-implementation-status, .tree-key-systems,
        .tree-key-classes, .tree-purpose, .tree-features, .tree-supporting-classes,
        .tree-css-files, .tree-js-files {
            margin: 4px 0 4px 40px;
            font-size: 12px;
            color: #555;
        }
        
        .tree-css-files {
            color: #8c44c4;
        }
        
        .tree-js-files {
            color: #d63638;
        }
        
        .tree-key-systems {
            color: #007cba;
            font-weight: bold;
        }
        
        .tree-key-classes {
            color: #00a32a;
        }
        
        .tree-purpose {
            color: #8c44c4;
            font-style: italic;
        }
        
        .tree-features {
            color: #d63638;
        }
        
        .tree-supporting-classes {
            color: #00a0d2;
        }
        
        .tree-required-methods {
            color: #d63638;
            font-weight: bold;
        }
        
        .tree-usage-pattern {
            color: #007cba;
            font-style: italic;
        }
        
        .tree-implementation-example {
            background: #f0f0f0;
            padding: 4px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        .tree-extends {
            color: #00a32a;
        }
        
        .status-good {
            color: #00a32a;
        }
        
        .status-needs-work {
            color: #d63638;
        }
        
        .tree-children {
            margin-left: 20px;
        }
        
        .tree-node[data-level='0'] {
            margin: 10px 0;
            padding: 8px;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        ";
    }
    
    /**
     * Get JavaScript for tree interaction
     * 
     * @return string JavaScript code
     */
    public static function get_tree_scripts() {
        return "
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.tree-toggle');
            
            toggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const node = this.closest('.tree-node');
                    const children = node.querySelector('.tree-children');
                    
                    if (children) {
                        if (children.style.display === 'none') {
                            children.style.display = 'block';
                            this.textContent = '▼';
                        } else {
                            children.style.display = 'none';
                            this.textContent = '▶';
                        }
                    }
                });
            });
        });
        ";
    }
}