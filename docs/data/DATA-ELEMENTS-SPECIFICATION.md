# TradePress Data Elements Specification

## Relationship To Canonical Data Docs

This file describes the Data Elements admin/system specification. For overall data flow, queued API access, storage layers, and source-of-truth rules, use `wp-content/plugins/tradepress/docs/data/DATA-ARCHITECTURE.md`. For freshness/staleness decisions, use `wp-content/plugins/tradepress/docs/data/DATA-FRESHNESS-FRAMEWORK.md`. For schema and meta-table decisions, use `wp-content/plugins/tradepress/docs/data/DATABASE-META-TABLES-PLAN.md`.

## Overview
The Data Elements system provides comprehensive management of all data groups required by TradePress, including their sources, import schedules, database storage, and usage tracking.

## Core Concepts

### Data Elements
A **Data Element** represents a group of related data that serves specific purposes within the plugin. Each element has:

- **Name & Description**: Human-readable identification
- **Uses**: List of features that consume this data (scoring directives, technical indicators, charts, etc.)
- **Database Table**: Primary storage location
- **Data Sources**: Primary, secondary, and additional sources
- **Import Frequency**: How often data should be refreshed
- **Import Function**: PHP function responsible for data import

### Data Element Structure
```php
'element_id' => array(
    'name' => 'Human Readable Name',
    'description' => 'Detailed description of the data',
    'uses' => array('Scoring Directives', 'Technical Indicators', 'Charts'),
    'database_table' => 'tradepress_table_name',
    'primary_source' => 'Alpha Vantage',
    'secondary_source' => 'Polygon', // Optional
    'additional_sources' => array('IEX Cloud', 'Finnhub'), // Optional
    'frequency' => 'Real-time / 1 minute / Hourly / Daily / Weekly',
    'import_function' => 'tradepress_import_function_name',
    'dependencies' => array('other_element_id'), // Optional
    'quality_requirements' => array(
        'accuracy' => 'high',
        'completeness' => 'required',
        'timeliness' => 'critical'
    )
)
```

## Implementation Plan

### Phase 1: Foundation (COMPLETED)
- [x] Create Data Elements tab structure
- [x] Basic data element configuration
- [x] Visual table layout and styling
- [x] Status monitoring system

### Phase 2: Data Discovery (CURRENT)
- [ ] Analyze existing codebase to identify all data requirements
- [ ] Map data usage across scoring directives
- [ ] Map data usage across technical indicators
- [ ] Map data usage across charts and calculations
- [ ] Expand data elements configuration with real requirements
- [ ] Document database table requirements

### Phase 3: Data Validation System
- [ ] Create data validation procedures
- [ ] Implement data quality checks
- [ ] Add data completeness verification
- [ ] Create data freshness monitoring

### Phase 4: Import System (FUTURE)
- [ ] Create standardized import functions using POST/GET via plugin listener
- [ ] Implement CRON scheduling system
- [ ] Add import logging and monitoring
- [ ] Create manual import triggers (non-AJAX)

**Note:** Manual import functionality will be implemented using WordPress admin_post hooks through the plugin listener system, not AJAX with API calls.

## Data Element Categories

### Market Data
- **Stock Prices**: Real-time and historical price data
- **Volume Data**: Trading volume information
- **Market Indices**: S&P 500, NASDAQ, etc.
- **Sector Performance**: Industry group performance

### Fundamental Data
- **Company Fundamentals**: Financial statements, ratios
- **Earnings Calendar**: Upcoming earnings dates
- **Analyst Estimates**: Price targets, recommendations
- **Corporate Actions**: Splits, dividends, mergers

### Technical Data
- **Technical Indicators**: RSI, MACD, Moving Averages
- **Support/Resistance Levels**: Key price levels
- **Chart Patterns**: Breakouts, trends
- **Volume Indicators**: Volume-based signals

### Alternative Data
- **News Sentiment**: Market news analysis
- **Social Media Sentiment**: Twitter, Reddit sentiment
- **Economic Indicators**: GDP, inflation, employment
- **Options Data**: Options chains, implied volatility

## Database Integration

### Table Naming Convention
- Primary tables: `tradepress_{data_type}`
- Meta tables: `tradepress_{data_type}_meta`
- Historical tables: `tradepress_{data_type}_history`

### Data Relationships
- All data elements should link to symbols via `symbol_id`
- Timestamps should use UTC timezone
- Data quality scores should be stored with each record

## Import Function Standards

### Function Naming
```php
function tradepress_import_{element_id}($options = array()) {
    // Implementation
}
```

### Required Parameters
- `$options['force']`: Force import regardless of schedule
- `$options['symbol']`: Import specific symbol only
- `$options['limit']`: Limit number of records

### Return Format
```php
return array(
    'success' => true/false,
    'message' => 'Status message',
    'records_imported' => 123,
    'errors' => array(),
    'execution_time' => 1.23
);
```

## Manual Import Features

### Individual Element Import
- Import specific data element on demand
- Override normal scheduling
- Useful for testing and troubleshooting

### Bulk Import Operations
- Import all elements at once
- Useful for initial setup or recovery
- Progress tracking and error reporting

## Development Guidelines

### Adding New Data Elements
1. Define element in configuration array
2. Create import function following standards
3. Add database table if needed
4. Update documentation
5. Add unit tests

### Best Practices
- Always validate input data
- Log import activities
- Handle API rate limits gracefully
- Implement proper error handling
- Use transactions for data consistency
