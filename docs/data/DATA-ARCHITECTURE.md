# TradePress Data Architecture and Flow

This document outlines the data architecture of TradePress, explaining how data flows through the system from external sources to the frontend. It serves as a guide for developers and AI to understand the system's design principles and to ensure consistent implementation of new features.

## System Overview

TradePress operates on a multi-layered architecture to manage financial data:

1. **External Data Sources Layer**: APIs, web scraping, and other external data sources
2. **API Service Layer**: Standardized classes for communicating with external data providers
3. **Queue Processing Layer**: Background queue system for reliable API request handling
4. **Normalization Layer**: Data adapters that transform raw API responses into standardized formats
5. **Storage Layer**: Database tables and WordPress post meta for persisting data
6. **Business Logic Layer**: Classes that process and analyze data
7. **Presentation Layer**: User interfaces that display data to users

The data flow is described in the sections below. A repository-local diagram can be added later when the visual asset exists in plugin docs.

## Data Flow Patterns

### Queue-Based Data Flow (Current Architecture)

1. User requests data (e.g., views a symbol page)
2. System checks if data exists in database and is fresh
3. If data is missing or stale:
   - Request is added to background processing queue
   - Queue system handles API calls asynchronously
   - Data is normalized through appropriate adapters
   - Normalized data is stored in the database
4. Data is retrieved from database and displayed to the user
5. UI polls database for updates without blocking

### Background Processing System

1. **Queue Management**: `TradePress_Data_Import_Process` extends `TradePress_Background_Processing`
2. **Priority System**: Critical vs non-critical requests with different queue priorities
3. **Self-Perpetuating Loop**: Background process maintains continuous operation
4. **Health Monitoring**: CRON healthcheck restarts failed processes
5. **Rate Limit Coordination**: Queue system respects API rate limits across providers

### Scheduled Updates (CRON)

1. WordPress CRON triggers scheduled update jobs
2. System identifies data that needs refreshing based on timestamps
3. Requests are queued for background processing (not direct API calls)
4. Background process handles actual API requests in batches
5. Data is normalized and stored in the database
6. Update status and timestamps are recorded

## Key Components

### 1. Main Loader (`loader.php`)

The `loader.php` file serves as the entry point for the entire plugin and is responsible for:

- Loading required files and classes
- Initializing core components
- Registering hooks and actions
- Managing plugin lifecycle

This file also contains the main `WordPressTradePress` class which:
- Defines constants
- Sets up admin and frontend hooks
- Loads system-specific code
- Initializes symbols from posts

### 2. Asset Management (`assets-loader-original.php`)

This class is responsible for loading scripts and styles needed for the admin interface:

- Registers and enqueues admin UI styles
- Loads JavaScript for interactive elements
- Manages conditional loading based on current screen
- Localizes scripts with PHP variables

Assets should not fetch data directly but instead call appropriate endpoints registered in AJAX handlers.

### 3. Queue Processing Architecture

#### Background Processing System

**TradePress_Data_Import_Process** (`includes/data-import-process.php`)
- Extends `TradePress_Background_Processing` for reliable queue handling
- Processes API requests asynchronously to prevent AJAX timeouts
- Implements retry logic and error handling
- Maintains continuous operation through self-dispatch loops
- Integrates with WordPress CRON for health monitoring

**Queue Database Schema** (`includes/queue-schema.php`)
- Stores queued API requests with priority levels
- Tracks processing status and retry attempts
- Enables queue management and monitoring
- Supports cancellation of pending requests

### 4. API Architecture

#### Financial API Service (`class.tradepress-financial-api-service.php`)

This is the base class for all financial API integrations:

- Handles authentication and API credentials
- Manages request formatting and parameters
- Processes raw API responses
- Handles errors and rate limiting
- Logs API calls for tracking and debugging
- **Now integrates with queue system for background processing**

**Important**: This class handles the mechanics of API communication but does not normalize data or determine when/what data to fetch. All API calls are now routed through the background queue system.

#### API Adapter (`class.tradepress-api-adapter.php`)

This abstract class provides standardization across different data sources:

- Transforms API-specific response formats into standardized TradePress formats
- Ensures consistent data structure regardless of source
- Maps fields from different providers to a common schema
- Handles special cases for each provider
- Provides fallback data where needed

**Important**: Adapters ensure that the rest of the system works with consistent data structures regardless of source.

### 4. Symbol Management

#### Symbol Class (`class.tradepress-symbol.php`)

Manages individual symbol data:

- Loads symbol information from database
- Aggregates data from multiple tables
- Updates symbol data from APIs
- Provides accessor methods for symbol properties

#### Symbols Manager (`class.tradepress-symbols.php`)

Handles operations across multiple symbols:

- Provides registry for symbol objects
- Retrieves symbols based on various criteria
- Manages symbol creation and initialization
- Serves as the main entry point for symbol data access

### 5. Database Layer

#### Tables Installation (`tables-installation.php`)

Creates and updates database tables for:

- Symbol data
- Price history
- Support/resistance levels
- Scoring data
- API call tracking
- Trading records

#### Database Functions (`functions.tradepress-database.php`)

Provides utility functions for:

- Inserting, updating, and deleting records
- Querying data efficiently
- Managing database transactions
- Handling data migrations

## Data Update Mechanisms

### On-Demand Updates (Queue-Based)

Data can be updated on-demand in the following scenarios:

1. **User Views Symbol**: When a user views a symbol that has stale or missing data, the system adds a request to the background queue.
2. **Manual Update Buttons**: Admin UI provides buttons that add items to queue (not direct API calls).
3. **Database Query Triggers**: Certain queries will automatically queue required data if missing.

Implementation principles:

- Check for existing data before queuing API calls
- Use appropriate timestamp comparisons for staleness
- **All API requests go through background queue system**
- **UI polling updates status without blocking user interface**
- Queue priority system ensures critical requests are processed first
- Background process handles actual API calls and stores results

### Scheduled Updates (Queue-Enhanced CRON)

TradePress uses WordPress CRON with background queue integration:

1. **Daily Price Updates**: CRON jobs add price update requests to queue for all tracked symbols.
2. **Weekly Fundamentals**: Fundamental data requests are queued with lower priority.
3. **Earnings Calendar**: Updates are queued around earnings season with high priority.
4. **Batch Processing**: Large symbol sets are split into individual queue items.
5. **Process Health Monitoring**: CRON monitors and restarts failed background processes.

Implementation principles:

- **CRON adds items to queue rather than making direct API calls**
- **Background process handles actual API requests continuously**
- Split large jobs into individual queue items for better control
- Record progress through queue status tracking
- Handle API rate limits at the queue processing level
- Log success/failure of both queue operations and API calls
- **Self-perpetuating background loops maintain continuous operation**

## Adding New Data Sources

When implementing a new data source:

1. Create a service class extending `TradePress_Financial_API_Service`
2. Create an adapter class extending `TradePress_API_Adapter`
3. Register the new service in the API directory
4. Create appropriate database tables if needed
5. Implement data refresh mechanisms in the appropriate controllers

## Guidelines for Implementation

### 1. Fetching Data (Queue-Based Architecture)

- **Never** make direct API calls from views, templates, or AJAX handlers
- **All API requests must go through the background queue system**
- Always use the symbol or appropriate model classes to fetch data
- Check for cached/stored data before queuing new API calls
- Use queue priority system for urgent vs routine requests
- UI should poll database for updates, not trigger API calls directly

### 2. Storing Data

- Always normalize data before storing
- Store in appropriate custom tables, not just post meta
- Include timestamps for freshness checking
- Use transactions for multi-table updates

### 3. Displaying Data

- Templates should request data from model classes, not APIs
- Handle missing data gracefully with appropriate fallbacks
- Provide visual indicators for data freshness
- Include "update now" options for stale data

## Object Registry

TradePress uses an object registry (`class.tradepress-object-registry.php`) to manage globally accessible objects:

- Symbol objects are stored in the registry for reuse
- API service instances can be accessed across the plugin
- Prevents duplicate instantiation of resource-intensive objects
- Maintains state across different parts of the system

Example usage:
```php
// Store object
TradePress_Object_Registry::add('symbol_AAPL', $symbol_object);

// Retrieve object
$symbol = TradePress_Object_Registry::get('symbol_AAPL');
```

## Queue System Architecture

### Background Processing Components

**TradePress_Data_Import_Process** - Main queue processor
- Extends `TradePress_Background_Processing` for WordPress integration
- Handles API requests asynchronously to prevent timeouts
- Implements self-perpetuating loops for continuous operation
- Integrates with CRON for health monitoring and restart capability

**Queue Database Schema** - Persistent queue storage
- `tradepress_queue` table stores pending API requests
- Priority system (critical, high, normal, low)
- Status tracking (pending, processing, completed, failed)
- Retry logic with exponential backoff

**Multi-Provider Coordination** - API provider management
- Alpha Vantage, Alpaca, and Finnhub integration
- Provider fallback system when primary source unavailable
- Rate limit coordination across all providers
- Cross-provider data validation and normalization

### Queue Processing Flow

```
User Action → Queue Item → Background Process → API Call → Database → UI Update
     ↓              ↓              ↓              ↓           ↓         ↓
  No Blocking    Priority      Continuous     Rate Limit   Storage   Polling
                 System        Operation      Respect       Update    Display
```

### Key Architectural Principles

1. **No API calls in AJAX handlers** - Use background queue system only
2. **Database as communication layer** - Processes communicate via database
3. **Queue-first approach** - All external API requests go through background queues
4. **UI polling, not pushing** - Interface reads from database, doesn't trigger API calls
5. **Self-perpetuating loops** - Background processes maintain continuous operation
6. **CRON as safety net** - Healthcheck restarts failed processes
7. **Priority-based processing** - Critical requests processed before routine updates
8. **Graceful degradation** - System continues operating when API providers unavailable

## Error Handling and Logging

All data operations should:

1. Use appropriate error handling with queue retry mechanisms
2. Log significant events and errors through queue system
3. Provide user feedback for failures via database polling
4. Include exponential backoff retry mechanisms in queue processing

## Development Workflow

When developing new features:

1. Begin with database schema design if needed
2. Implement model classes to handle the data
3. Create or update API services and adapters
4. Implement update mechanisms (on-demand and scheduled)
5. Finally, create or modify views to display the data

Following this sequence ensures that all data handling is properly encapsulated before being exposed to the UI.

## Testing New Data Sources

Before integrating a new data source:

1. Manually test API responses to understand structure
2. Create a normalization plan for mapping fields
3. Create an adapter test suite to verify field mapping works correctly
4. Implement proper error handling for API-specific issues
5. Set up appropriate rate limit handling
6. Document the data source in this architecture document

## Transient Caching Strategy

The plugin uses WordPress transients as a primary caching mechanism to minimize database queries and external API requests. This section outlines the comprehensive caching approach for different data types.

### Data Volatility Classification

TradePress classifies data based on its volatility to determine appropriate caching strategies:

1. **Static Data** (rarely changes)
   - Symbol metadata (name, description, sector)
   - Historical data (past price data)
   - Exchange information
   - Instrument specifications
   - *Cache Duration: 24+ hours*

2. **Semi-Dynamic Data** (changes daily/infrequently)
   - End-of-day price data
   - Daily company fundamentals
   - Weekly/monthly score history
   - Historical price patterns
   - *Cache Duration: 1-24 hours*

3. **Dynamic Data** (changes frequently)
   - Current price information
   - Real-time trading signals
   - Intraday volatility metrics
   - Social media sentiment
   - *Cache Duration: 1-60 minutes*

4. **Real-Time Data** (requires live updates)
   - Active trading signals
   - Market emergency alerts
   - Critical price movements
   - *Cache Duration: None or very short (< 1 minute)*

### Caching Decision Algorithm

The system uses a multi-factor algorithm to determine if data should be cached and for how long:

```
function determineTransientExpiration(data_type, symbol_importance, update_frequency, user_settings) {
    // Base expiration by data type
    switch(data_type) {
        case 'static': base_expiration = 86400; // 24 hours
        case 'semi_dynamic': base_expiration = 3600; // 1 hour
        case 'dynamic': base_expiration = 300; // 5 minutes
        case 'real_time': base_expiration = 60; // 1 minute
    }
    
    // Adjust by symbol importance (high importance = fresher data)
    $importance_factor = 1;
    switch(symbol_importance) {
        case 'high': importance_factor = 0.5; // Half the cache time
        case 'medium': importance_factor = 1; // Standard cache time
        case 'low': importance_factor = 1.5; // Extend cache time 50%
    }
    
    // Adjust by market hours
    $market_factor = isMarketOpen() ? 0.7 : 2; // Shorter during market hours
    
    // Adjust by user settings
    $settings_factor = user_settings['data_freshness'] === 'maximum' ? 0.5 : 1;
    
    // Calculate final expiration
    $expiration = base_expiration * importance_factor * market_factor * settings_factor;
    
    return $expiration;
}
```

### Implementation Patterns

1. **API Response Caching**

All external API responses are cached using transients with dynamic keys that include:
- Endpoint path
- Query parameters (hashed for length)
- API version 
- User-specific context if applicable

```php
// Example implementation
function get_api_data($endpoint, $params) {
    // Generate cache key
    $cache_key = 'tradepress_api_' . md5($endpoint . serialize($params));
    
    // Try to get cached data first
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // If not in cache, make the API request
    $response = make_api_request($endpoint, $params);
    
    if (!is_wp_error($response)) {
        // Determine appropriate cache duration based on endpoint
        $expiration = determine_cache_duration($endpoint, $params);
        
        // Store in cache
        set_transient($cache_key, $response, $expiration);
    }
    
    return $response;
}
```

2. **Graduated Caching Strategy**

For complex data that requires multiple API calls or heavy processing:

- **Level 1**: Cache raw API responses (lowest level)
- **Level 2**: Cache normalized/processed data (middle level)
- **Level 3**: Cache view-ready data with UI components (highest level)

This approach allows invalidating specific cache levels without affecting others:

```php
// Example of level 2 cache usage
function get_symbol_score_components($symbol_id) {
    // Check for processed data cache first (Level 2)
    $cache_key = 'tradepress_score_components_' . $symbol_id;
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // If not in L2 cache, try to build from L1 cache
    $raw_data = get_raw_score_data($symbol_id); // Uses L1 cache
    
    if ($raw_data) {
        $processed_data = process_score_components($raw_data);
        
        // Store in L2 cache for 1 hour
        set_transient($cache_key, $processed_data, HOUR_IN_SECONDS);
        return $processed_data;
    }
    
    return false;
}
```

3. **Adaptive Cache Invalidation**

The system uses intelligent cache invalidation strategies:

- **Time-based**: Standard expiration based on data volatility
- **Event-based**: Invalidate related caches when data changes
- **Dependency-based**: Invalidate caches when dependencies change
- **Bulk invalidation**: Clear category of caches during major updates

Example implementation:
```php
/**
 * Invalidate related caches when a symbol is updated
 */
function invalidate_symbol_caches($symbol_id) {
    global $wpdb;
    
    // Get symbol ticker from ID
    $symbol = get_symbol_ticker($symbol_id);
    
    // Delete direct symbol caches
    delete_transient('tradepress_symbol_data_' . $symbol_id);
    delete_transient('tradepress_symbol_score_' . $symbol_id);
    
    // Find and delete pattern-matching transients
    $pattern = '_transient_tradepress_*_' . $symbol . '_*';
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '{$pattern}'");
    
    // Invalidate aggregate caches that might include this symbol
    delete_transient('tradepress_market_overview');
    delete_transient('tradepress_top_scored_symbols');
}
```

4. **Cache Warming**

The system uses strategic cache warming to maintain performance:

- **Scheduled warming**: CRON jobs pre-populate critical caches
- **Priority-based warming**: High-priority symbols are cached first
- **User-triggered warming**: First user action triggers background caching
- **Predictive warming**: System predicts needed data based on usage patterns

### Cache Management

1. **Size Monitoring**

The system monitors transient cache size to prevent database bloat:

```php
function monitor_transient_cache_size() {
    global $wpdb;
    
    // Get count and size of transients
    $result = $wpdb->get_row("
        SELECT COUNT(*) as count, SUM(LENGTH(option_value)) as size 
        FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_tradepress_%'
    ");
    
    // Log metrics
    update_option('tradepress_cache_metrics', [
        'count' => $result->count,
        'size' => $result->size,
        'measured_at' => time()
    ]);
    
    // Alert if exceeds thresholds
    if ($result->count > 1000 || $result->size > 5000000) { // 5MB
        trigger_cache_cleanup();
    }
}
```

2. **Cache Pruning Strategy**

When cache size exceeds thresholds:

- Least recently used (LRU) caches are pruned first
- Low-priority symbol caches are pruned before high-priority ones
- Raw data caches (Level 1) are pruned before derived data caches
- Expired transients are cleaned up on a regular schedule

### Market Hours Awareness

The caching system is market hours aware:

- During market hours: Shorter cache durations for price-sensitive data
- Outside market hours: Longer cache durations 
- Market holidays: Extended cache times for most data types
- Pre/post market: Adjusted durations based on volatility

### API Rate Limit Integration

The caching system integrates with API rate limits:

- Dynamically extends cache duration when approaching rate limits
- Maintains a "rate limit reserve" for high-priority requests
- Uses longer caches for expensive API endpoints
- Implements fallback mechanisms when rate limits are exceeded

### Implementing Cache Logic in Code

When implementing new features that fetch data, follow this pattern:

```php
/**
 * Get data with caching logic
 */
function get_cached_data($identifier, $fetch_callback, $expiration = 3600) {
    // Create a unique cache key
    $cache_key = 'tradepress_' . md5($identifier);
    
    // Try to get from cache
    $data = get_transient($cache_key);
    
    // If not cached or cache is expired
    if ($data === false) {
        // Call the fetch callback which gets fresh data
        $data = call_user_func($fetch_callback);
        
        // If we got valid data, cache it
        if ($data && !is_wp_error($data)) {
            set_transient($cache_key, $data, $expiration);
        }
    }
    
    return $data;
}

// Usage example
$symbol_data = get_cached_data(
    'symbol_full_' . $symbol_id,
    function() use ($symbol_id) {
        return fetch_complete_symbol_data($symbol_id);
    },
    HOUR_IN_SECONDS
);
```

By following these caching principles, TradePress minimizes unnecessary API calls and database queries while maintaining data freshness appropriate to each data type's volatility.
