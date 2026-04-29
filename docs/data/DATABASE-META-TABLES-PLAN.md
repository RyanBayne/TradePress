# TradePress Meta Tables Implementation Plan

## Overview
This document outlines the meta tables needed for TradePress to avoid overloading WordPress core meta tables with trading-specific data.

## Phase 1: Core Meta Tables (Immediate Need)

### 1. Symbol Meta Tables
```sql
-- Core symbol metadata
CREATE TABLE tradepress_symbol_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    symbol_id bigint(20) NOT NULL,
    meta_key varchar(255) NOT NULL,
    meta_value longtext,
    source varchar(50) DEFAULT NULL,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY symbol_id (symbol_id),
    KEY meta_key (meta_key),
    KEY source (source),
    FOREIGN KEY (symbol_id) REFERENCES tradepress_symbols(id) ON DELETE CASCADE
);

-- Fundamental data storage
CREATE TABLE tradepress_symbol_fundamentals (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    symbol_id bigint(20) NOT NULL,
    metric_name varchar(100) NOT NULL,
    metric_value decimal(20,4) DEFAULT NULL,
    metric_text varchar(500) DEFAULT NULL,
    period varchar(20) DEFAULT NULL, -- quarterly, annual, ttm
    report_date date DEFAULT NULL,
    source varchar(50) DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY symbol_id (symbol_id),
    KEY metric_name (metric_name),
    KEY report_date (report_date),
    FOREIGN KEY (symbol_id) REFERENCES tradepress_symbols(id) ON DELETE CASCADE
);

-- Technical indicators storage
CREATE TABLE tradepress_symbol_technical (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    symbol_id bigint(20) NOT NULL,
    indicator_name varchar(50) NOT NULL,
    indicator_value decimal(20,4) DEFAULT NULL,
    timeframe varchar(20) DEFAULT NULL, -- 1d, 1w, 1m, etc.
    calculation_date date DEFAULT NULL,
    source varchar(50) DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY symbol_id (symbol_id),
    KEY indicator_name (indicator_name),
    KEY calculation_date (calculation_date),
    FOREIGN KEY (symbol_id) REFERENCES tradepress_symbols(id) ON DELETE CASCADE
);
```

### 2. API Management Meta Tables
```sql
-- API call tracking for rate limiting
CREATE TABLE tradepress_api_calls (
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
);

-- Data source configurations
CREATE TABLE tradepress_data_sources_meta (
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
);

-- Encrypted API credentials storage
CREATE TABLE tradepress_api_credentials (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    provider varchar(50) NOT NULL,
    credential_type varchar(50) NOT NULL, -- api_key, secret, token
    encrypted_value longtext NOT NULL,
    is_active tinyint(1) DEFAULT 1,
    expires_at datetime DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY provider_type (provider, credential_type),
    KEY is_active (is_active)
);
```

### 3. Cache Management Meta Table
```sql
-- Cache metadata for intelligent invalidation
CREATE TABLE tradepress_cache_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    cache_key varchar(255) NOT NULL,
    cache_group varchar(100) NOT NULL,
    data_type varchar(50) NOT NULL, -- price, fundamental, news, etc.
    symbol_id bigint(20) DEFAULT NULL,
    expires_at datetime NOT NULL,
    invalidation_triggers text DEFAULT NULL, -- JSON array of triggers
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY cache_key (cache_key),
    KEY cache_group (cache_group),
    KEY data_type (data_type),
    KEY expires_at (expires_at),
    KEY symbol_id (symbol_id)
);
```

## Phase 2: Advanced Meta Tables (After Core Implementation)

### 4. Strategy Meta Tables
```sql
-- Strategy parameters and configurations
CREATE TABLE tradepress_strategy_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    strategy_id bigint(20) NOT NULL,
    parameter_name varchar(100) NOT NULL,
    parameter_value longtext,
    parameter_type varchar(50) DEFAULT 'string', -- string, number, boolean, json
    is_required tinyint(1) DEFAULT 0,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY strategy_id (strategy_id),
    KEY parameter_name (parameter_name)
);

-- Strategy performance tracking
CREATE TABLE tradepress_strategy_performance (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    strategy_id bigint(20) NOT NULL,
    metric_name varchar(100) NOT NULL,
    metric_value decimal(20,4) DEFAULT NULL,
    period_start date DEFAULT NULL,
    period_end date DEFAULT NULL,
    symbol_id bigint(20) DEFAULT NULL, -- NULL for overall performance
    calculated_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY strategy_id (strategy_id),
    KEY metric_name (metric_name),
    KEY period_start (period_start),
    KEY symbol_id (symbol_id)
);
```

### 5. User Activity Meta Tables
```sql
-- User trading preferences
CREATE TABLE tradepress_user_preferences (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    preference_key varchar(255) NOT NULL,
    preference_value longtext,
    preference_type varchar(50) DEFAULT 'string',
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY preference_key (preference_key)
);

-- Watchlist metadata
CREATE TABLE tradepress_watchlist_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    watchlist_id bigint(20) NOT NULL,
    meta_key varchar(255) NOT NULL,
    meta_value longtext,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY watchlist_id (watchlist_id),
    KEY meta_key (meta_key)
);
```

### 6. Automation Meta Tables
```sql
-- CRON job metadata and status
CREATE TABLE tradepress_cron_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    job_name varchar(100) NOT NULL,
    job_status enum('active','paused','error','completed') DEFAULT 'active',
    last_run datetime DEFAULT NULL,
    next_run datetime DEFAULT NULL,
    run_count int(10) DEFAULT 0,
    error_count int(10) DEFAULT 0,
    last_error text DEFAULT NULL,
    config_data longtext DEFAULT NULL, -- JSON configuration
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY job_name (job_name),
    KEY job_status (job_status),
    KEY next_run (next_run)
);

-- Background process metadata
CREATE TABLE tradepress_process_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    process_name varchar(100) NOT NULL,
    process_type varchar(50) NOT NULL, -- import, export, calculation, etc.
    status enum('pending','running','completed','failed') DEFAULT 'pending',
    progress_percent int(3) DEFAULT 0,
    total_items int(10) DEFAULT 0,
    processed_items int(10) DEFAULT 0,
    error_message text DEFAULT NULL,
    started_at datetime DEFAULT NULL,
    completed_at datetime DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY process_name (process_name),
    KEY status (status),
    KEY created_at (created_at)
);
```

## Phase 3: Market Data Meta Tables (Future Enhancement)

### 7. Market Context Meta Tables
```sql
-- Market session information
CREATE TABLE tradepress_market_sessions (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    exchange varchar(50) NOT NULL,
    session_date date NOT NULL,
    session_type enum('regular','pre','post','holiday') DEFAULT 'regular',
    open_time time DEFAULT NULL,
    close_time time DEFAULT NULL,
    is_trading_day tinyint(1) DEFAULT 1,
    notes text DEFAULT NULL,
    PRIMARY KEY (id),
    KEY exchange (exchange),
    KEY session_date (session_date),
    KEY is_trading_day (is_trading_day)
);

-- News sentiment metadata
CREATE TABLE tradepress_news_meta (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    news_id bigint(20) NOT NULL,
    sentiment_score decimal(5,2) DEFAULT NULL, -- -1.00 to 1.00
    relevance_score decimal(5,2) DEFAULT NULL, -- 0.00 to 1.00
    symbol_mentions text DEFAULT NULL, -- JSON array of mentioned symbols
    keywords text DEFAULT NULL, -- JSON array of keywords
    source_reliability decimal(3,2) DEFAULT NULL, -- 0.00 to 1.00
    analyzed_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY news_id (news_id),
    KEY sentiment_score (sentiment_score),
    KEY analyzed_at (analyzed_at)
);
```

## Implementation Priority

1. **Immediate (Week 1-2)**: Symbol meta tables and API tracking
2. **Short-term (Week 3-4)**: Cache management and data source configuration
3. **Medium-term (Month 2)**: Strategy and user preference tables
4. **Long-term (Month 3+)**: Market context and advanced analytics tables

## Benefits of This Approach

- **Performance**: Dedicated tables with proper indexing for trading data
- **Scalability**: Can handle large volumes of financial data efficiently
- **Flexibility**: Easy to add new meta fields without schema changes
- **WordPress Compatibility**: Keeps WordPress meta tables clean
- **Data Integrity**: Foreign key relationships ensure data consistency

## Next Steps

1. Create database migration scripts for Phase 1 tables
2. Update `TradePress_DB_Schema` class to include meta tables
3. Create corresponding model classes for each meta table
4. Implement meta data access methods in core classes
5. Add meta tables to the Tables tab display

This foundation will support robust data import, API management, and trading strategy implementation.