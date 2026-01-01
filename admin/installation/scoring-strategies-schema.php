<?php
/**
 * TradePress - Enhanced Scoring Strategies Database Schema
 * 
 * Comprehensive database tables for managing scoring strategies with relationships
 *
 * @package TradePress/Admin/Installation
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Strategies_Schema {
    
    /**
     * Create enhanced scoring strategy tables
     */
    public static function create_tables() {
        global $wpdb, $charset_collate;
        
        // Enhanced strategies table (replaces existing basic one)
        self::create_strategies_table();
        
        // Strategy directives relationship table
        self::create_strategy_directives_table();
        
        // Strategy directive configurations (per-strategy overrides)
        self::create_strategy_directive_configs_table();
        
        // Strategy test results and performance tracking
        self::create_strategy_tests_table();
        
        // Strategy performance metrics
        self::create_strategy_performance_table();
        
        // Strategy versions (for tracking changes)
        self::create_strategy_versions_table();
        
        // Strategy categories/tags
        self::create_strategy_categories_table();
        
        // Log the schema creation
        update_option('tradepress_scoring_strategies_schema_version', '2.0.0');
        update_option('tradepress_scoring_strategies_schema_created', current_time('mysql'));
    }
    
    /**
     * Enhanced strategies table
     */
    private static function create_strategies_table() {
        global $wpdb, $charset_collate;
        
        $table_name = $wpdb->prefix . "tradepress_scoring_strategies";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text DEFAULT NULL,
            category varchar(100) DEFAULT 'custom',
            template varchar(100) DEFAULT NULL,
            status enum('active','draft','archived','testing') DEFAULT 'draft',
            type enum('scoring','trading','hybrid') DEFAULT 'scoring',
            risk_level enum('low','medium','high') DEFAULT 'medium',
            time_horizon enum('intraday','short','medium','long') DEFAULT 'medium',
            
            -- Strategy Configuration
            total_weight decimal(5,2) DEFAULT 100.00,
            min_score_threshold decimal(5,2) DEFAULT 50.00,
            max_directives int(3) DEFAULT 20,
            weighting_method enum('equal','custom','dynamic') DEFAULT 'custom',
            
            -- Performance Tracking
            total_tests int(11) DEFAULT 0,
            successful_tests int(11) DEFAULT 0,
            avg_score decimal(5,2) DEFAULT NULL,
            success_rate decimal(5,2) DEFAULT NULL,
            last_test_date datetime DEFAULT NULL,
            
            -- Metadata
            creator_id bigint(20) unsigned DEFAULT NULL,
            is_public tinyint(1) DEFAULT 0,
            is_template tinyint(1) DEFAULT 0,
            template_source_id bigint(20) unsigned DEFAULT NULL,
            
            -- Timestamps
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY name (name(191)),
            KEY status (status),
            KEY category (category),
            KEY type (type),
            KEY risk_level (risk_level),
            KEY creator_id (creator_id),
            KEY is_public (is_public),
            KEY success_rate (success_rate),
            KEY created_at (created_at)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Strategy directives relationship table
     */
    private static function create_strategy_directives_table() {
        global $wpdb, $charset_collate;
        
        $table_name = $wpdb->prefix . "tradepress_strategy_directives";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            strategy_id bigint(20) unsigned NOT NULL,
            directive_id varchar(100) NOT NULL,
            directive_name varchar(255) NOT NULL,
            
            -- Weighting Configuration
            weight decimal(5,2) NOT NULL DEFAULT 10.00,
            is_required tinyint(1) DEFAULT 0,
            min_score_contribution decimal(5,2) DEFAULT 0.00,
            max_score_contribution decimal(5,2) DEFAULT 100.00,
            
            -- Conditional Logic
            conditions longtext DEFAULT NULL COMMENT 'JSON: Conditions for when this directive applies',
            multipliers longtext DEFAULT NULL COMMENT 'JSON: Dynamic multipliers based on market conditions',
            
            -- Performance Tracking
            avg_score decimal(5,2) DEFAULT NULL,
            contribution_rate decimal(5,2) DEFAULT NULL,
            last_calculated datetime DEFAULT NULL,
            
            -- Metadata
            sort_order int(3) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            notes text DEFAULT NULL,
            
            -- Timestamps
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            UNIQUE KEY strategy_directive (strategy_id, directive_id),
            KEY strategy_id (strategy_id),
            KEY directive_id (directive_id),
            KEY weight (weight),
            KEY is_required (is_required),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Strategy directive configurations (per-strategy overrides)
     */
    private static function create_strategy_directive_configs_table() {
        global $wpdb, $charset_collate;
        
        $table_name = $wpdb->prefix . "tradepress_strategy_directive_configs";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            strategy_directive_id bigint(20) unsigned NOT NULL,
            config_key varchar(100) NOT NULL,
            config_value longtext NOT NULL,
            config_type enum('string','number','boolean','json','array') DEFAULT 'string',
            
            -- Override Information
            overrides_global tinyint(1) DEFAULT 1,
            global_value longtext DEFAULT NULL COMMENT 'Original global value for reference',
            override_reason text DEFAULT NULL,
            
            -- Validation
            is_valid tinyint(1) DEFAULT 1,
            validation_errors text DEFAULT NULL,
            
            -- Timestamps
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            UNIQUE KEY strategy_directive_config (strategy_directive_id, config_key),
            KEY strategy_directive_id (strategy_directive_id),
            KEY config_key (config_key),
            KEY overrides_global (overrides_global),
            KEY is_valid (is_valid)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Strategy test results table
     */
    private static function create_strategy_tests_table() {
        global $wpdb, $charset_collate;
        
        $table_name = $wpdb->prefix . "tradepress_strategy_tests";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            strategy_id bigint(20) unsigned NOT NULL,
            test_type enum('single_symbol','portfolio','backtest','live') DEFAULT 'single_symbol',
            
            -- Test Parameters
            symbol varchar(20) DEFAULT NULL,
            symbols_tested text DEFAULT NULL COMMENT 'JSON array of symbols for portfolio tests',
            trading_mode enum('long','short','both') DEFAULT 'long',
            test_date datetime NOT NULL,
            
            -- Test Results
            total_score decimal(8,2) DEFAULT NULL,
            individual_scores longtext DEFAULT NULL COMMENT 'JSON: Individual directive scores',
            execution_time_ms int(6) DEFAULT NULL,
            api_calls_made int(4) DEFAULT NULL,
            
            -- Market Context
            market_conditions longtext DEFAULT NULL COMMENT 'JSON: Market data at test time',
            price_at_test decimal(20,8) DEFAULT NULL,
            volume_at_test bigint(20) DEFAULT NULL,
            
            -- Test Outcome
            test_status enum('completed','failed','timeout','cancelled') DEFAULT 'completed',
            error_message text DEFAULT NULL,
            warnings longtext DEFAULT NULL COMMENT 'JSON array of warnings',
            
            -- Performance Tracking
            signal_strength enum('weak','moderate','strong','very_strong') DEFAULT NULL,
            recommendation enum('strong_buy','buy','hold','sell','strong_sell') DEFAULT NULL,
            confidence_level decimal(5,2) DEFAULT NULL,
            
            -- Metadata
            test_notes text DEFAULT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            
            -- Timestamps
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            KEY strategy_id (strategy_id),
            KEY test_type (test_type),
            KEY symbol (symbol),
            KEY test_date (test_date),
            KEY test_status (test_status),
            KEY signal_strength (signal_strength),
            KEY recommendation (recommendation),
            KEY user_id (user_id)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Strategy performance metrics table
     */
    private static function create_strategy_performance_table() {
        global $wpdb, $charset_collate;
        
        $table_name = $wpdb->prefix . "tradepress_strategy_performance";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            strategy_id bigint(20) unsigned NOT NULL,
            
            -- Time Period
            period_start datetime NOT NULL,
            period_end datetime NOT NULL,
            period_type enum('daily','weekly','monthly','quarterly','yearly','custom') DEFAULT 'monthly',
            
            -- Basic Metrics
            total_tests int(11) DEFAULT 0,
            successful_tests int(11) DEFAULT 0,
            failed_tests int(11) DEFAULT 0,
            avg_score decimal(8,2) DEFAULT NULL,
            median_score decimal(8,2) DEFAULT NULL,
            min_score decimal(8,2) DEFAULT NULL,
            max_score decimal(8,2) DEFAULT NULL,
            
            -- Success Metrics
            success_rate decimal(5,2) DEFAULT NULL,
            strong_signals int(11) DEFAULT 0,
            weak_signals int(11) DEFAULT 0,
            
            -- Performance Metrics
            avg_execution_time_ms decimal(8,2) DEFAULT NULL,
            total_api_calls int(11) DEFAULT 0,
            avg_api_calls_per_test decimal(5,2) DEFAULT NULL,
            
            -- Reliability Metrics
            consistency_score decimal(5,2) DEFAULT NULL COMMENT 'How consistent the strategy performs',
            volatility_score decimal(5,2) DEFAULT NULL COMMENT 'Score volatility measure',
            
            -- Market Correlation
            market_correlation decimal(5,4) DEFAULT NULL,
            sector_performance longtext DEFAULT NULL COMMENT 'JSON: Performance by sector',
            symbol_performance longtext DEFAULT NULL COMMENT 'JSON: Performance by symbol',
            
            -- Comparative Analysis
            benchmark_comparison decimal(5,2) DEFAULT NULL,
            peer_ranking int(3) DEFAULT NULL,
            
            -- Metadata
            calculation_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            data_quality_score decimal(5,2) DEFAULT NULL,
            notes text DEFAULT NULL,
            
            PRIMARY KEY (id),
            UNIQUE KEY strategy_period (strategy_id, period_start, period_end),
            KEY strategy_id (strategy_id),
            KEY period_type (period_type),
            KEY success_rate (success_rate),
            KEY avg_score (avg_score),
            KEY calculation_date (calculation_date)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Strategy versions table (for tracking changes)
     */
    private static function create_strategy_versions_table() {
        global $wpdb, $charset_collate;
        
        $table_name = $wpdb->prefix . "tradepress_strategy_versions";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            strategy_id bigint(20) unsigned NOT NULL,
            version_number varchar(20) NOT NULL DEFAULT '1.0.0',
            
            -- Version Data (snapshot of strategy at this version)
            strategy_data longtext NOT NULL COMMENT 'JSON: Complete strategy configuration',
            directives_data longtext NOT NULL COMMENT 'JSON: All directive configurations',
            
            -- Change Information
            change_type enum('created','updated','archived','restored') DEFAULT 'updated',
            change_summary text DEFAULT NULL,
            change_details longtext DEFAULT NULL COMMENT 'JSON: Detailed changes made',
            
            -- Performance Comparison
            performance_before longtext DEFAULT NULL COMMENT 'JSON: Performance metrics before change',
            performance_after longtext DEFAULT NULL COMMENT 'JSON: Performance metrics after change',
            
            -- Metadata
            created_by bigint(20) unsigned DEFAULT NULL,
            is_active tinyint(1) DEFAULT 0,
            tags text DEFAULT NULL COMMENT 'Comma-separated tags',
            
            -- Timestamps
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            KEY strategy_id (strategy_id),
            KEY version_number (version_number),
            KEY change_type (change_type),
            KEY created_by (created_by),
            KEY is_active (is_active),
            KEY created_at (created_at)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Strategy categories table
     */
    private static function create_strategy_categories_table() {
        global $wpdb, $charset_collate;
        
        $table_name = $wpdb->prefix . "tradepress_strategy_categories";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            description text DEFAULT NULL,
            parent_id bigint(20) unsigned DEFAULT NULL,
            
            -- Category Configuration
            default_risk_level enum('low','medium','high') DEFAULT 'medium',
            default_time_horizon enum('intraday','short','medium','long') DEFAULT 'medium',
            suggested_directives text DEFAULT NULL COMMENT 'JSON: Commonly used directives',
            
            -- Display Options
            color varchar(7) DEFAULT '#0073aa',
            icon varchar(50) DEFAULT 'chart-line',
            sort_order int(3) DEFAULT 0,
            
            -- Metadata
            strategy_count int(11) DEFAULT 0,
            is_system tinyint(1) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            
            -- Timestamps
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY name (name),
            KEY parent_id (parent_id),
            KEY is_system (is_system),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Insert default strategy categories
     */
    public static function insert_default_categories() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . "tradepress_strategy_categories";
        
        // Check if categories already exist
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count > 0) {
            return; // Categories already exist
        }
        
        $categories = array(
            array(
                'name' => 'Conservative Growth',
                'slug' => 'conservative-growth',
                'description' => 'Low-risk strategies focused on steady, long-term growth',
                'default_risk_level' => 'low',
                'default_time_horizon' => 'long',
                'color' => '#2e7d32',
                'icon' => 'trending-up',
                'sort_order' => 1,
                'is_system' => 1
            ),
            array(
                'name' => 'Momentum Trading',
                'slug' => 'momentum-trading',
                'description' => 'Strategies that capitalize on strong price momentum and trends',
                'default_risk_level' => 'medium',
                'default_time_horizon' => 'short',
                'color' => '#f57c00',
                'icon' => 'rocket',
                'sort_order' => 2,
                'is_system' => 1
            ),
            array(
                'name' => 'Value Investing',
                'slug' => 'value-investing',
                'description' => 'Strategies focused on undervalued stocks with strong fundamentals',
                'default_risk_level' => 'low',
                'default_time_horizon' => 'long',
                'color' => '#0073aa',
                'icon' => 'gem',
                'sort_order' => 3,
                'is_system' => 1
            ),
            array(
                'name' => 'Technical Analysis',
                'slug' => 'technical-analysis',
                'description' => 'Strategies based on technical indicators and chart patterns',
                'default_risk_level' => 'medium',
                'default_time_horizon' => 'medium',
                'color' => '#7b1fa2',
                'icon' => 'chart-bar',
                'sort_order' => 4,
                'is_system' => 1
            ),
            array(
                'name' => 'High Frequency',
                'slug' => 'high-frequency',
                'description' => 'Fast-paced strategies for intraday and scalping opportunities',
                'default_risk_level' => 'high',
                'default_time_horizon' => 'intraday',
                'color' => '#d32f2f',
                'icon' => 'flash',
                'sort_order' => 5,
                'is_system' => 1
            ),
            array(
                'name' => 'Custom Strategies',
                'slug' => 'custom',
                'description' => 'User-created custom strategies',
                'default_risk_level' => 'medium',
                'default_time_horizon' => 'medium',
                'color' => '#666666',
                'icon' => 'cog',
                'sort_order' => 99,
                'is_system' => 0
            )
        );
        
        foreach ($categories as $category) {
            $wpdb->insert($table_name, $category);
        }
    }
    
    /**
     * Create foreign key relationships (if supported)
     */
    public static function create_foreign_keys() {
        global $wpdb;
        
        // Note: WordPress typically doesn't use foreign keys due to MyISAM compatibility
        // But we can add them for InnoDB installations
        
        $engine = $wpdb->get_var("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$wpdb->prefix}tradepress_scoring_strategies'");
        
        if (strtolower($engine) === 'innodb') {
            // Add foreign key constraints
            $constraints = array(
                "ALTER TABLE {$wpdb->prefix}tradepress_strategy_directives 
                 ADD CONSTRAINT fk_strategy_directives_strategy 
                 FOREIGN KEY (strategy_id) REFERENCES {$wpdb->prefix}tradepress_scoring_strategies(id) 
                 ON DELETE CASCADE ON UPDATE CASCADE",
                
                "ALTER TABLE {$wpdb->prefix}tradepress_strategy_directive_configs 
                 ADD CONSTRAINT fk_directive_configs_strategy_directive 
                 FOREIGN KEY (strategy_directive_id) REFERENCES {$wpdb->prefix}tradepress_strategy_directives(id) 
                 ON DELETE CASCADE ON UPDATE CASCADE",
                
                "ALTER TABLE {$wpdb->prefix}tradepress_strategy_tests 
                 ADD CONSTRAINT fk_strategy_tests_strategy 
                 FOREIGN KEY (strategy_id) REFERENCES {$wpdb->prefix}tradepress_scoring_strategies(id) 
                 ON DELETE CASCADE ON UPDATE CASCADE",
                
                "ALTER TABLE {$wpdb->prefix}tradepress_strategy_performance 
                 ADD CONSTRAINT fk_strategy_performance_strategy 
                 FOREIGN KEY (strategy_id) REFERENCES {$wpdb->prefix}tradepress_scoring_strategies(id) 
                 ON DELETE CASCADE ON UPDATE CASCADE",
                
                "ALTER TABLE {$wpdb->prefix}tradepress_strategy_versions 
                 ADD CONSTRAINT fk_strategy_versions_strategy 
                 FOREIGN KEY (strategy_id) REFERENCES {$wpdb->prefix}tradepress_scoring_strategies(id) 
                 ON DELETE CASCADE ON UPDATE CASCADE"
            );
            
            foreach ($constraints as $constraint) {
                $wpdb->query($constraint);
            }
        }
    }
    
    /**
     * Get table relationships for documentation
     */
    public static function get_table_relationships() {
        return array(
            'tradepress_scoring_strategies' => array(
                'description' => 'Main strategies table with metadata and performance tracking',
                'relationships' => array(
                    'has_many' => array(
                        'tradepress_strategy_directives',
                        'tradepress_strategy_tests', 
                        'tradepress_strategy_performance',
                        'tradepress_strategy_versions'
                    ),
                    'belongs_to' => array(
                        'wp_users' => 'creator_id'
                    )
                )
            ),
            'tradepress_strategy_directives' => array(
                'description' => 'Directives assigned to strategies with weights and configuration',
                'relationships' => array(
                    'belongs_to' => array(
                        'tradepress_scoring_strategies' => 'strategy_id'
                    ),
                    'has_many' => array(
                        'tradepress_strategy_directive_configs'
                    )
                )
            ),
            'tradepress_strategy_directive_configs' => array(
                'description' => 'Per-strategy directive configuration overrides',
                'relationships' => array(
                    'belongs_to' => array(
                        'tradepress_strategy_directives' => 'strategy_directive_id'
                    )
                )
            ),
            'tradepress_strategy_tests' => array(
                'description' => 'Individual strategy test results and performance data',
                'relationships' => array(
                    'belongs_to' => array(
                        'tradepress_scoring_strategies' => 'strategy_id',
                        'wp_users' => 'user_id'
                    )
                )
            ),
            'tradepress_strategy_performance' => array(
                'description' => 'Aggregated performance metrics over time periods',
                'relationships' => array(
                    'belongs_to' => array(
                        'tradepress_scoring_strategies' => 'strategy_id'
                    )
                )
            ),
            'tradepress_strategy_versions' => array(
                'description' => 'Version history and change tracking for strategies',
                'relationships' => array(
                    'belongs_to' => array(
                        'tradepress_scoring_strategies' => 'strategy_id',
                        'wp_users' => 'created_by'
                    )
                )
            ),
            'tradepress_strategy_categories' => array(
                'description' => 'Strategy categorization and organization',
                'relationships' => array(
                    'self_referential' => 'parent_id'
                )
            )
        );
    }
}