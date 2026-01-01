<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( 'TradePress_Install_Tables' ) ) :

class TradePress_Install_Tables {
    
    var $installation_type = 'update';
    
    /**
     * Get all tables with descriptions
     * 
     * @return array Table names with descriptions
     */
    public function get_tables_with_descriptions() {
        return array(
            // Core tables
            'tradepress_calls' => 'API call logs and tracking',
            'tradepress_errors' => 'Error logging and debugging', 
            'tradepress_endpoints' => 'API endpoint configurations',
            'tradepress_meta' => 'Plugin metadata storage',
            
            // Symbol tables
            'tradepress_symbols' => 'Stock symbols and company data',
            'tradepress_price_levels' => 'Support and resistance price levels',
            'tradepress_price_history' => 'Historical price data storage',
            'tradepress_price_data' => 'OHLCV price data from API imports',
            
            // Meta tables
            'tradepress_symbol_meta' => 'Symbol metadata and attributes',
            'tradepress_api_calls' => 'API call tracking and performance',
            'tradepress_data_sources_meta' => 'Data source configurations',
            'tradepress_cache_meta' => 'Cache management and expiration',
            'tradepress_cron_meta' => 'Scheduled job management',
            
            // Scoring tables
            'tradepress_symbol_scores' => 'Symbol scoring history',
            'tradepress_directive_scores' => 'Detailed directive scoring',
            'tradepress_strategies' => 'Trading strategies and rules',
            'tradepress_strategy_symbols' => 'Strategy-symbol associations',
            'tradepress_score_analysis' => 'Score analysis and performance',
            
            // Strategy Management tables
            'tradepress_scoring_strategies' => 'Scoring strategy definitions',
            'tradepress_scoring_strategy_categories' => 'Strategy category management',
            'tradepress_scoring_strategy_directives' => 'Strategy-directive relationships',
            'tradepress_scoring_strategy_tests' => 'Strategy testing results',
            'tradepress_scoring_strategy_performance' => 'Strategy performance tracking',
            'tradepress_scoring_strategy_versions' => 'Strategy version control',
            'tradepress_scoring_strategy_backtest_results' => 'Backtesting results storage',
            
            // Trading bot tables
            'tradepress_trades' => 'Trading history and records',
            'tradepress_algorithm_runs' => 'Algorithm execution tracking',
            
            // Prediction tables
            'tradepress_prediction_sources' => 'Price prediction sources',
            'tradepress_price_predictions' => 'Price prediction data',
            'tradepress_source_performance' => 'Prediction source accuracy',
            
            // Social alerts tables
            'tradepress_social_alerts' => 'Social media trading alerts',
            'tradepress_alert_outcomes' => 'Alert performance tracking',
            'tradepress_alert_source_metrics' => 'Alert source reliability',
            
            // Research tables
            'tradepress_research_sources' => 'Research data sources',
            'tradepress_research' => 'Research reports and analysis',
            
            // Testing System tables
            'tradepress_tests' => 'Test definitions and metadata',
            'tradepress_test_runs' => 'Test execution history and results',
            'tradepress_test_faults' => 'Bug tracking and test failures',
            
            // Logs table
            'tradepress_logs' => 'System logging and debugging',
            
            // E-Learning System tables
            'tradepress_courses' => 'Course definitions and metadata',
            'tradepress_steps' => 'Course step content and structure',
            'tradepress_user_journal' => 'User responses and progress tracking'
        );
    }
    
    /**
     * Get table names only
     * 
     * @return array Table names
     */
    public function get_tables() {
        return array_keys($this->get_tables_with_descriptions());
    }
        
    public function __construct() {
        if ( ! defined( 'TradePress_TABLES_INSTALLING' ) ) {
            define( 'TradePress_TABLES_INSTALLING', true );
        }  
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );       
    }
    
    /**
     * Main method to create all database tables
     * 
     * @return bool True on successful installation
     */
    public function create_tables() {
        global $wpdb;
        
        // Direct execution SQL approach for critical tables
        // This ensures they are created properly even if dbDelta has issues
        
        // Create core tables
        $this->primary_tables();
        
        // Create additional tables
        $this->create_symbol_tables();
        $this->create_meta_tables();
        $this->create_scoring_tables();
        $this->create_bot_tables();
        $this->create_prediction_tables();
        $this->create_social_alerts_tables();
        $this->create_research_tables();
        $this->create_testing_tables();
        $this->create_logs_table();
        $this->create_elearning_tables();
        
        // Insert pre-installation data
        $this->insert_research_sources_data();
        
        // Create strategy management tables
        $this->create_enhanced_scoring_strategies_tables();
        $this->insert_scoring_strategies_data();
        $this->insert_sample_strategies();
        
        // Log the installation
        $this->log_table_installation();
        
        // Return list of created tables for verification
        return true;
    }
    
    /**
     * Legacy method for compatibility - maps to create_tables()
     * 
     * @return bool True on successful installation
     */
    public function install() {
        return $this->create_tables();
    }
    
    public function update() {
        $this->create_tables();
    }
    
    /**
     * Log the table installation
     */
    private function log_table_installation() {
        update_option('tradepress_db_version', TRADEPRESS_VERSION);
        update_option('tradepress_db_last_updated', current_time('mysql'));
        
        // Save list of tables for reference
        update_option('tradepress_tables_list', $this->get_tables());
    }
                                                    
    /**
    * Create the core tables for TradePress
    * 
    * @version 1.0
    */
    public function primary_tables() {
        global $wpdb, $charset_collate;
        
        // Create activity table - Direct SQL approach
        $table_name = $wpdb->prefix . "tradepress_calls";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            entryid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            callid bigint(20) unsigned DEFAULT '0',
            service varchar(50) NOT NULL,
            type varchar(50) NOT NULL,
            status varchar(50) NOT NULL,
            file varchar(500), 
            `function` varchar(125) NOT NULL,
            line bigint(20),
            wpuserid bigint(20) unsigned DEFAULT '0',
            timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            description longtext,
            outcome longtext,
            life bigint(20) NOT NULL DEFAULT '86400',
            PRIMARY KEY (entryid)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Create errors table
        $table_name = $wpdb->prefix . "tradepress_errors";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            errorid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            entryid bigint(20) unsigned,
            code varchar(50),
            error varchar(250),
            line bigint(20),
            `function` varchar(125),
            file varchar(500),
            timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (errorid)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Create endpoints table
        $table_name = $wpdb->prefix . "tradepress_endpoints";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            endpointid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            entryid bigint(20) unsigned,
            service varchar(50) NOT NULL,
            endpoint varchar(500) NOT NULL,
            parameters longtext NOT NULL, 
            firstuse timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            lastuse timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            counter bigint(20) NOT NULL DEFAULT '1',
            PRIMARY KEY (endpointid)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Create meta table
        $table_name = $wpdb->prefix . "tradepress_meta";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            metaid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            entryid bigint(20) unsigned,
            metakey varchar(50) NOT NULL,
            metavalue longtext NOT NULL,
            timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
            expiry DATETIME,
            PRIMARY KEY (metaid)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create symbol-related tables
     */
    public function create_symbol_tables() {
        global $wpdb, $charset_collate;
        
        // Primary symbols table
        $table_name = $wpdb->prefix . "tradepress_symbols";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            symbol varchar(20) NOT NULL,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            exchange varchar(50) DEFAULT NULL,
            type varchar(50) DEFAULT NULL,
            sector varchar(100) DEFAULT NULL,
            industry varchar(100) DEFAULT NULL,
            current_price decimal(20,8) DEFAULT NULL,
            volume bigint(20) DEFAULT NULL,
            market_cap decimal(20,2) DEFAULT NULL,
            price_updated datetime DEFAULT NULL,
            forecast_low decimal(20,8) DEFAULT NULL,
            forecast_medium decimal(20,8) DEFAULT NULL,
            forecast_high decimal(20,8) DEFAULT NULL,
            current_score int(11) DEFAULT NULL,
            support_level_long decimal(20,8) DEFAULT NULL,
            resistance_level_long decimal(20,8) DEFAULT NULL,
            active tinyint(1) NOT NULL DEFAULT 1,
            post_id bigint(20) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY symbol (symbol),
            KEY exchange (exchange),
            KEY type (type),
            KEY sector (sector),
            KEY industry (industry),
            KEY current_score (current_score),
            KEY active (active),
            KEY post_id (post_id)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Support and resistance levels (multiple per symbol)
        $table_name = $wpdb->prefix . "tradepress_price_levels";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            symbol_id bigint(20) unsigned NOT NULL,
            level_type varchar(20) NOT NULL,
            timeframe varchar(20) NOT NULL,
            price_level decimal(20,8) NOT NULL,
            strength int(11) DEFAULT 5,
            confirmed_count int(11) DEFAULT 0,
            last_test_date datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY symbol_id (symbol_id),
            KEY level_type (level_type),
            KEY timeframe (timeframe)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Historical price data
        $table_name = $wpdb->prefix . "tradepress_price_history";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            symbol varchar(10) NOT NULL,
            date date NOT NULL,
            open decimal(10,4) DEFAULT NULL,
            high decimal(10,4) DEFAULT NULL,
            low decimal(10,4) DEFAULT NULL,
            close decimal(10,4) DEFAULT NULL,
            volume bigint(20) DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY symbol (symbol),
            KEY date (date)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Price data table for API imports (OHLCV data)
        $table_name = $wpdb->prefix . "tradepress_price_data";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            symbol varchar(20) NOT NULL,
            date date NOT NULL,
            open decimal(20,4) DEFAULT NULL,
            high decimal(20,4) DEFAULT NULL,
            low decimal(20,4) DEFAULT NULL,
            close decimal(20,4) DEFAULT NULL,
            volume bigint(20) DEFAULT NULL,
            source varchar(50) DEFAULT NULL,
            timeframe varchar(20) DEFAULT '1Day',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY symbol_date_source (symbol, date, source, timeframe),
            KEY symbol (symbol),
            KEY date (date),
            KEY source (source)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create essential meta tables for trading data
     */
    public function create_meta_tables() {
        global $wpdb, $charset_collate;
        
        // Symbol metadata table
        $table_name = $wpdb->prefix . "tradepress_symbol_meta";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            symbol_id bigint(20) NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            source varchar(50) DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY symbol_id (symbol_id),
            KEY meta_key (meta_key),
            KEY source (source)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // API calls tracking table
        $table_name = $wpdb->prefix . "tradepress_api_calls";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            provider varchar(50) NOT NULL,
            endpoint varchar(255) NOT NULL,
            call_time datetime DEFAULT CURRENT_TIMESTAMP,
            response_code int(3) DEFAULT NULL,
            response_time_ms int(5) DEFAULT NULL,
            cache_hit tinyint(1) DEFAULT 0,
            error_message text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY provider (provider),
            KEY call_time (call_time),
            KEY endpoint (endpoint)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Data sources configuration table
        $table_name = $wpdb->prefix . "tradepress_data_sources_meta";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            source_name varchar(100) NOT NULL,
            source_type enum('api','file','manual') DEFAULT 'api',
            config_key varchar(255) NOT NULL,
            config_value longtext,
            is_active tinyint(1) DEFAULT 1,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY source_name (source_name),
            KEY config_key (config_key),
            KEY is_active (is_active)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Cache metadata table
        $table_name = $wpdb->prefix . "tradepress_cache_meta";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            cache_group varchar(100) NOT NULL,
            data_type varchar(50) NOT NULL,
            symbol_id bigint(20) DEFAULT NULL,
            expires_at datetime NOT NULL,
            invalidation_triggers text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY cache_key (cache_key(191)),
            KEY cache_group (cache_group),
            KEY data_type (data_type),
            KEY expires_at (expires_at),
            KEY symbol_id (symbol_id)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // CRON job metadata table
        $table_name = $wpdb->prefix . "tradepress_cron_meta";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            job_name varchar(100) NOT NULL,
            job_status enum('active','paused','error','completed') DEFAULT 'active',
            last_run datetime DEFAULT NULL,
            next_run datetime DEFAULT NULL,
            run_count int(10) DEFAULT 0,
            error_count int(10) DEFAULT 0,
            last_error text DEFAULT NULL,
            config_data longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY job_name (job_name),
            KEY job_status (job_status),
            KEY next_run (next_run)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create scoring-related tables to track scoring history and analysis
     */
    public function create_scoring_tables() {
        global $wpdb, $charset_collate;
        
        // Table for storing symbol scores
        $table_name = $wpdb->prefix . "tradepress_symbol_scores";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            symbol_id bigint(20) unsigned NOT NULL,
            symbol varchar(20) NOT NULL,
            score int(11) NOT NULL,
            previous_score int(11) DEFAULT NULL,
            components longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            notes text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY symbol_id (symbol_id),
            KEY symbol (symbol),
            KEY score (score),
            KEY created_at (created_at)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Table for storing detailed directive scores
        $table_name = $wpdb->prefix . "tradepress_directive_scores";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            score_id bigint(20) unsigned NOT NULL,
            directive_id varchar(100) NOT NULL,
            directive_name varchar(255) NOT NULL,
            raw_score decimal(10,4) NOT NULL,
            weighted_score decimal(10,4) NOT NULL,
            weight decimal(10,4) NOT NULL,
            details longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY score_id (score_id),
            KEY directive_id (directive_id)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Table for storing strategy definitions
        $table_name = $wpdb->prefix . "tradepress_strategies";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            strategy_name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            time_horizon varchar(50) DEFAULT NULL,
            directives longtext NOT NULL,
            config longtext DEFAULT NULL,
            creator_id bigint(20) DEFAULT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            performance_data longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY strategy_name (strategy_name(191)),
            KEY is_active (is_active),
            KEY time_horizon (time_horizon)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Table for associating symbols with specific strategies
        $table_name = $wpdb->prefix . "tradepress_strategy_symbols";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            strategy_id bigint(20) unsigned NOT NULL,
            symbol_id bigint(20) unsigned NOT NULL,
            is_approved tinyint(1) NOT NULL DEFAULT 1,
            priority int(11) DEFAULT 5,
            notes text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY strategy_symbol (strategy_id, symbol_id),
            KEY strategy_id (strategy_id),
            KEY symbol_id (symbol_id),
            KEY is_approved (is_approved),
            KEY priority (priority)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Table for score analysis and comparison
        $table_name = $wpdb->prefix . "tradepress_score_analysis";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            symbol_id bigint(20) unsigned NOT NULL,
            strategy_id bigint(20) unsigned NOT NULL,
            analysis_date datetime NOT NULL,
            start_date datetime NOT NULL,
            end_date datetime NOT NULL,
            avg_score decimal(10,4) DEFAULT NULL,
            max_score decimal(10,4) DEFAULT NULL,
            min_score decimal(10,4) DEFAULT NULL,
            price_correlation decimal(10,4) DEFAULT NULL,
            success_rate decimal(10,4) DEFAULT NULL,
            avg_profit_per_signal decimal(10,4) DEFAULT NULL,
            analysis_data longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY symbol_strategy_dates (symbol_id, strategy_id, start_date, end_date),
            KEY symbol_id (symbol_id),
            KEY strategy_id (strategy_id),
            KEY analysis_date (analysis_date)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create trading bot related tables
     */
    public function create_bot_tables() {
        global $wpdb, $charset_collate;
        
        // Table for storing trades
        $table_name = $wpdb->prefix . "tradepress_trades";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            symbol_id bigint(20) unsigned NOT NULL,
            symbol varchar(20) NOT NULL,
            action varchar(20) NOT NULL,
            quantity decimal(20,8) NOT NULL,
            price decimal(20,8) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            score int(11) NOT NULL,
            datetime datetime NOT NULL,
            result decimal(20,8) DEFAULT NULL,
            notes text DEFAULT NULL,
            meta longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY symbol (symbol),
            KEY action (action),
            KEY status (status),
            KEY datetime (datetime)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Table for storing algorithm runs
        $table_name = $wpdb->prefix . "tradepress_algorithm_runs";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            start_time datetime NOT NULL,
            end_time datetime DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'running',
            symbols_processed int(11) DEFAULT 0,
            api_calls int(11) DEFAULT 0,
            scores_generated int(11) DEFAULT 0,
            trade_signals int(11) DEFAULT 0,
            run_type varchar(20) NOT NULL DEFAULT 'manual',
            notes text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY start_time (start_time),
            KEY status (status),
            KEY run_type (run_type)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create logs table
     */
    public function create_logs_table() {
        global $wpdb, $charset_collate;
        
        $table_name = $wpdb->prefix . "tradepress_logs";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            timestamp DATETIME NOT NULL,
            level VARCHAR(20) NOT NULL,
            category VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            context LONGTEXT,
            PRIMARY KEY (id),
            KEY level (level),
            KEY category (category),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create the prediction tracking tables
     */
    public function create_prediction_tables() {
        global $wpdb, $charset_collate;

        // Prediction Sources Table
        $table_name = $wpdb->prefix . "tradepress_prediction_sources";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            source_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            source_name varchar(255) NOT NULL,
            source_url varchar(2048) DEFAULT NULL,
            source_type varchar(50) NOT NULL DEFAULT 'web',
            scraping_config longtext DEFAULT NULL,
            api_config longtext DEFAULT NULL,
            active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (source_id),
            KEY source_name (source_name(191)),
            KEY source_type (source_type),
            KEY active (active)
        ) $charset_collate;";
        $wpdb->query($sql);

        // Price Predictions Table
        $table_name = $wpdb->prefix . "tradepress_price_predictions";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            prediction_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            source_id bigint(20) UNSIGNED NOT NULL,
            symbol varchar(20) NOT NULL,
            prediction_date datetime NOT NULL,
            target_date datetime NOT NULL,
            price_prediction decimal(16,6) NOT NULL,
            price_low decimal(16,6) DEFAULT NULL,
            price_high decimal(16,6) DEFAULT NULL,
            confidence_percentage decimal(5,2) DEFAULT NULL,
            actual_price decimal(16,6) DEFAULT NULL,
            accuracy_percentage decimal(7,4) DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (prediction_id),
            KEY source_id (source_id),
            KEY symbol (symbol),
            KEY prediction_date (prediction_date),
            KEY target_date (target_date)
        ) $charset_collate;";
        $wpdb->query($sql);

        // Source Performance Table
        $table_name = $wpdb->prefix . "tradepress_source_performance";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            performance_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            source_id bigint(20) UNSIGNED NOT NULL,
            symbol varchar(20) DEFAULT NULL,
            time_horizon varchar(50) NOT NULL,
            prediction_count int(11) NOT NULL DEFAULT 0,
            average_accuracy decimal(7,4) DEFAULT NULL,
            success_rate decimal(7,4) DEFAULT NULL,
            average_error decimal(7,4) DEFAULT NULL,
            last_evaluated datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (performance_id),
            UNIQUE KEY source_symbol_horizon (source_id, symbol, time_horizon)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create social media alerts and signals tables
     */
    public function create_social_alerts_tables() {
        global $wpdb, $charset_collate;

        // Main social alerts table
        $table_name = $wpdb->prefix . "tradepress_social_alerts";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            alert_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            platform varchar(50) NOT NULL COMMENT 'Platform like Discord, X.com, etc',
            source varchar(100) NOT NULL COMMENT 'Specific source like Stock VIP',
            author varchar(255) DEFAULT NULL COMMENT 'Author of the message/post',
            message_date datetime DEFAULT NULL COMMENT 'When the message was originally posted',
            raw_message longtext NOT NULL COMMENT 'Original unprocessed message',
            processed_status varchar(20) DEFAULT 'new' COMMENT 'Status: new, processed, failed',
            symbols text DEFAULT NULL COMMENT 'Identified stock symbols',
            detected_action varchar(50) DEFAULT NULL COMMENT 'Buy, sell, etc',
            price_target decimal(20,8) DEFAULT NULL COMMENT 'Target price if specified',
            stop_loss decimal(20,8) DEFAULT NULL COMMENT 'Stop loss if specified',
            sentiment varchar(20) DEFAULT NULL COMMENT 'Positive, negative, neutral',
            confidence_score decimal(5,2) DEFAULT NULL COMMENT 'Confidence in signal 0-100',
            processing_notes longtext DEFAULT NULL COMMENT 'Notes from processing',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (alert_id),
            KEY platform (platform),
            KEY source (source),
            KEY processed_status (processed_status),
            KEY message_date (message_date),
            KEY created_at (created_at)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Trade Alerts: Alert to score/trade correlation table
        $table_name = $wpdb->prefix . "tradepress_alert_outcomes";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            outcome_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            alert_id bigint(20) unsigned NOT NULL,
            symbol varchar(20) NOT NULL,
            entry_price decimal(20,8) DEFAULT NULL,
            exit_price decimal(20,8) DEFAULT NULL,
            entry_date datetime DEFAULT NULL,
            exit_date datetime DEFAULT NULL,
            profit_loss decimal(20,8) DEFAULT NULL,
            profit_loss_percent decimal(10,4) DEFAULT NULL,
            outcome_status varchar(20) DEFAULT 'pending' COMMENT 'pending, successful, failed',
            trade_executed tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether a trade was executed',
            trade_id bigint(20) unsigned DEFAULT NULL COMMENT 'Reference to trade if executed',
            notes text DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (outcome_id),
            KEY alert_id (alert_id),
            KEY symbol (symbol),
            KEY outcome_status (outcome_status),
            KEY trade_executed (trade_executed),
            KEY trade_id (trade_id)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Source reliability metrics table
        $table_name = $wpdb->prefix . "tradepress_alert_source_metrics";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            metric_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            platform varchar(50) NOT NULL,
            source varchar(100) NOT NULL,
            total_alerts int(11) NOT NULL DEFAULT 0,
            successful_alerts int(11) NOT NULL DEFAULT 0,
            failed_alerts int(11) NOT NULL DEFAULT 0,
            avg_profit_percent decimal(10,4) DEFAULT NULL,
            success_rate decimal(5,2) DEFAULT NULL,
            avg_hold_time_hours decimal(10,2) DEFAULT NULL,
            last_alert_date datetime DEFAULT NULL,
            last_evaluated datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            notes text DEFAULT NULL,
            PRIMARY KEY (metric_id),
            UNIQUE KEY platform_source (platform, source),
            KEY success_rate (success_rate),
            KEY last_alert_date (last_alert_date)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create research tables for storing reports from various sources
     */
    public function create_research_tables() {
        global $wpdb, $charset_collate;
        
        // Research sources table
        $table_name = $wpdb->prefix . "tradepress_research_sources";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            source_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(50) NOT NULL,
            url text NOT NULL,
            credentials text,
            settings longtext,
            last_fetch datetime DEFAULT NULL,
            status varchar(50) DEFAULT 'active',
            created datetime DEFAULT CURRENT_TIMESTAMP,
            description text,
            reliability_score decimal(5,2) DEFAULT NULL,
            PRIMARY KEY (source_id),
            KEY type (type),
            KEY status (status)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Check if description column exists, add it if it doesn't
        $check_column = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'description'",
            DB_NAME,
            $table_name
        ));
        
        if(empty($check_column)) {
            $wpdb->query($wpdb->prepare("ALTER TABLE %s ADD COLUMN description text AFTER created", $table_name));
        }
        
        // Research data table
        $table_name = $wpdb->prefix . "tradepress_research";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            source_id bigint(20) unsigned NOT NULL,
            symbol varchar(20) DEFAULT NULL,
            report_date datetime NOT NULL,
            report_type varchar(50) NOT NULL,
            title varchar(255) DEFAULT NULL,
            content longtext NOT NULL,
            metadata longtext,
            sentiment varchar(20) DEFAULT NULL,
            signal_type varchar(50) DEFAULT NULL,
            price_target decimal(20,8) DEFAULT NULL,
            confidence_score decimal(5,2) DEFAULT NULL,
            verified tinyint(1) DEFAULT 0,
            processed tinyint(1) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY source_id (source_id),
            KEY symbol (symbol),
            KEY report_date (report_date),
            KEY report_type (report_type),
            KEY sentiment (sentiment),
            KEY verified (verified),
            KEY processed (processed)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Insert pre-installation data for research sources
     */
    public function insert_research_sources_data() {
        global $wpdb;
        
        $sources_table = $wpdb->prefix . "tradepress_research_sources";
        
        // Check if table exists and has data
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM %s", $sources_table));
        
        if ($count == 0) {
            // Verify description column exists before inserting
            $description_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'description'",
                DB_NAME,
                $sources_table
            ));
            
            // Insert Earnings Whispers source
            $earnings_whispers = array(
                'name' => 'Earnings Whispers',
                'type' => 'email',
                'url' => 'https://www.earningswhispers.com/',
                'credentials' => serialize(array('api_key' => '', 'api_secret' => '')),
                'settings' => serialize(array(
                    'email_folder' => 'INBOX',
                    'email_subject_filter' => 'Earnings Whisper',
                    'email_from_filter' => 'report@earningswhispers.com',
                    'auto_fetch' => true,
                    'fetch_frequency' => 'hourly'
                )),
                'status' => 'active'
            );
            
            // Only add description if the column exists
            if ($description_exists) {
                $earnings_whispers['description'] = 'Earnings Whispers email reports for trading signals based on earnings expectations vs. actual results';
            }
            
            $earnings_whispers['reliability_score'] = 85.00;
            
            $wpdb->insert($sources_table, $earnings_whispers);
            
            // Insert StockVIP Free Alerts source
            $stockvip_alerts = array(
                'name' => 'StockVIP Free Alerts',
                'type' => 'discord',
                'url' => 'https://discord.gg/stockvip',
                'credentials' => serialize(array('token' => '', 'channel_id' => '')),
                'settings' => serialize(array(
                    'channel_name' => 'free-alerts',
                    'webhook_url' => '',
                    'auto_fetch' => true,
                    'fetch_frequency' => 'realtime'
                )),
                'status' => 'active'
            );
            
            // Only add description if the column exists
            if ($description_exists) {
                $stockvip_alerts['description'] = 'Free trading alerts from StockVIP Discord community';
            }
            
            $stockvip_alerts['reliability_score'] = 72.50;
            
            $wpdb->insert($sources_table, $stockvip_alerts);
        }
    }
    
    /**
     * Create enhanced scoring strategies tables
     */
    public function create_enhanced_scoring_strategies_tables() {
        // Load and execute the enhanced scoring strategies schema
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/installation/scoring-strategies-schema.php';
        TradePress_Scoring_Strategies_Schema::create_tables();
    }
    
    /**
     * Insert default scoring strategies data
     */
    public function insert_scoring_strategies_data() {
        // Load and execute default data insertion
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/installation/scoring-strategies-schema.php';
        TradePress_Scoring_Strategies_Schema::insert_default_categories();
    }
    
    /**
     * Insert sample strategies
     */
    public function insert_sample_strategies() {
        // Load and execute sample strategies creation
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/installation/sample-strategies.php';
        TradePress_Sample_Strategies::create_sample_strategies();
    }

    /**
     * Create testing system tables
     */
    public function create_testing_tables() {
        global $wpdb, $charset_collate;
        
        // Main tests table
        $table_name = $wpdb->prefix . "tradepress_tests";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            test_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            category varchar(50) NOT NULL DEFAULT 'standard',
            priority_level tinyint(1) NOT NULL DEFAULT 3,
            status varchar(20) NOT NULL DEFAULT 'active',
            test_type varchar(50) NOT NULL DEFAULT 'file',
            file_path varchar(500) DEFAULT NULL,
            class_name varchar(255) DEFAULT NULL,
            method_name varchar(255) DEFAULT NULL,
            test_data longtext DEFAULT NULL,
            expected_result longtext DEFAULT NULL,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_run datetime DEFAULT NULL,
            run_count int(11) DEFAULT 0,
            success_rate decimal(5,2) DEFAULT NULL,
            avg_execution_time decimal(10,4) DEFAULT NULL,
            github_issue_url varchar(500) DEFAULT NULL,
            notes text DEFAULT NULL,
            PRIMARY KEY (test_id),
            KEY category (category),
            KEY status (status),
            KEY test_type (test_type),
            KEY priority_level (priority_level),
            KEY created_by (created_by),
            KEY last_run (last_run)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Test runs table
        $table_name = $wpdb->prefix . "tradepress_test_runs";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            run_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            test_id bigint(20) unsigned NOT NULL,
            run_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) NOT NULL DEFAULT 'pending',
            execution_time decimal(10,4) DEFAULT NULL,
            memory_usage int(11) DEFAULT NULL,
            input_data longtext,
            output_data longtext,
            error_message text DEFAULT NULL,
            stack_trace longtext DEFAULT NULL,
            run_by bigint(20) unsigned NOT NULL,
            environment varchar(50) DEFAULT NULL,
            version varchar(50) DEFAULT NULL,
            PRIMARY KEY (run_id),
            KEY test_id (test_id),
            KEY status (status),
            KEY run_date (run_date),
            KEY run_by (run_by)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Test faults table
        $table_name = $wpdb->prefix . "tradepress_test_faults";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            fault_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            test_id bigint(20) unsigned NOT NULL,
            run_id bigint(20) unsigned DEFAULT NULL,
            detected_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            severity varchar(20) NOT NULL DEFAULT 'medium',
            title varchar(255) NOT NULL,
            description text,
            steps_to_reproduce text,
            expected_behavior text,
            actual_behavior text,
            github_issue_id varchar(100) DEFAULT NULL,
            github_issue_url varchar(500) DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'open',
            assigned_to bigint(20) unsigned DEFAULT NULL,
            resolved_date datetime DEFAULT NULL,
            resolution_notes text DEFAULT NULL,
            created_by bigint(20) unsigned NOT NULL,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (fault_id),
            KEY test_id (test_id),
            KEY run_id (run_id),
            KEY severity (severity),
            KEY status (status),
            KEY assigned_to (assigned_to),
            KEY created_by (created_by)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
    
    /**
     * Create e-learning system tables
     */
    public function create_elearning_tables() {
        global $wpdb, $charset_collate;
        
        // Courses table
        $table_name = $wpdb->prefix . "tradepress_courses";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            order_index int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY order_index (order_index)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // Steps table
        $table_name = $wpdb->prefix . "tradepress_steps";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            course_id bigint(20) unsigned NOT NULL,
            title varchar(255) NOT NULL,
            content longtext DEFAULT NULL,
            prompts longtext DEFAULT NULL,
            tasks longtext DEFAULT NULL,
            order_index int(11) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY status (status),
            KEY order_index (order_index)
        ) $charset_collate;";
        $wpdb->query($sql);
        
        // User journal table
        $table_name = $wpdb->prefix . "tradepress_user_journal";
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            step_id bigint(20) unsigned NOT NULL,
            prompt_id varchar(100) NOT NULL,
            response longtext NOT NULL,
            task_completed tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_step_prompt (user_id, step_id, prompt_id),
            KEY user_id (user_id),
            KEY step_id (step_id),
            KEY task_completed (task_completed)
        ) $charset_collate;";
        $wpdb->query($sql);
    }
}

endif;