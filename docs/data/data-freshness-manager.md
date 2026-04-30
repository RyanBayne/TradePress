# Data Freshness Manager

## Relationship To Canonical Data Docs

This file describes the Data Freshness Manager implementation and its interaction with the Recent Call Register. The governing freshness policy is `wp-content/plugins/tradepress/docs/data/DATA-FRESHNESS-FRAMEWORK.md`. The governing data-flow rule remains `wp-content/plugins/tradepress/docs/data/DATA-ARCHITECTURE.md`: page requests should read stored data and queue refreshes rather than calling providers inline.

## Overview
The Data Freshness Manager serves as the central coordinator for validating data freshness before algorithm execution. It acts as a gatekeeper layer ensuring data quality and preventing unnecessary API calls through intelligent caching.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    TradePress Plugin Features                   │
├─────────────────┬─────────────────┬─────────────────┬───────────┤
│ Scoring         │ Test Directive  │ Price Updates   │ Earnings  │
│ Algorithms      │ Functions       │ System          │ Calendar  │
└─────────┬───────┴─────────┬───────┴─────────┬───────┴─────┬─────┘
          │                 │                 │             │
          ▼                 ▼                 ▼             ▼
┌─────────────────────────────────────────────────────────────────┐
│              Data Freshness Manager (Gatekeeper)               │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ 1. Validate Request Parameters                          │    │
│  │ 2. Check Recent Call Register for Cached Data          │    │
│  │ 3. Determine if Fresh API Call is Needed               │    │
│  │ 4. Route to API Handler or Return Cached Data          │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────┬───────────────────────┬───────────────────┘
                      │                       │
          ┌───────────▼──────────┐           │
          │ Recent Call Register │           │
          │ (Caching Layer)      │           │
          │                      │           │
          │ Platform-Aware Keys: │           │
          │ • alphavantage.quote │           │
          │ • finnhub.quote      │           │
          │ • earnings.calendar  │           │
          │                      │           │
          │ Hourly Rotation:     │           │
          │ • 2024092217_calls   │           │
          │ • 2024092218_calls   │           │
          └──────────────────────┘           │
                                             │
                      ┌─────────────────────▼─────────────────────┐
                      │            API Handlers                   │
                      │ ┌─────────────────┬─────────────────────┐ │
                      │ │ Alpha Vantage   │ Finnhub API         │ │
                      │ │ API Handler     │ Handler             │ │
                      │ └─────────────────┴─────────────────────┘ │
                      └───────────────────────────────────────────┘
```

## Key Components

### 1. Request Validation
- **Symbol Format**: Ensures proper symbol formatting (e.g., "AAPL", "MSFT")
- **Platform Specification**: Validates API platform selection
- **Parameter Completeness**: Checks required parameters are present

### 2. Cache Integration
- **Platform-Aware Keys**: Different platforms get separate cache entries
- **Time-Based Expiration**: Configurable cache duration (default: 2 hours)
- **Cross-Feature Sharing**: Multiple features can benefit from single API calls

### 3. Decision Logic
```php
if (cache_exists && !cache_expired) {
    return cached_data;
} else {
    make_api_call();
    store_in_cache();
    return fresh_data;
}
```

## Technical Implementation

### Core Methods

#### `validate_data_freshness($symbol, $platform, $data_type)`
- **Purpose**: Main entry point for data freshness validation
- **Parameters**: 
  - `$symbol`: Stock symbol (e.g., "AAPL")
  - `$platform`: API platform ("alphavantage", "finnhub")
  - `$data_type`: Type of data ("quote", "earnings", "news")
- **Returns**: Array with validation results and recommendations

#### `should_fetch_fresh_data($cache_key)`
- **Purpose**: Determines if fresh API call is needed
- **Logic**: Checks cache existence and expiration
- **Returns**: Boolean decision

#### `get_cache_duration($data_type)`
- **Purpose**: Returns appropriate cache duration for data type
- **Durations**:
  - Quotes: 2 hours (market hours), 8 hours (after hours)
  - Earnings: 24 hours
  - News: 1 hour

## Integration Points

### With Recent Call Register
```php
$cache_key = $this->recent_call_register->generate_cache_key($platform, $endpoint, $params);
$cached_data = $this->recent_call_register->get_cached_call($cache_key);
```

### With API Handlers
```php
if (!$this->should_fetch_fresh_data($cache_key)) {
    return $cached_data;
}
$fresh_data = $this->api_handler->make_call($endpoint, $params);
$this->recent_call_register->store_call_result($cache_key, $fresh_data);
```

## Configuration Options

### Cache Durations (in seconds)
- `quote_cache_duration`: 7200 (2 hours)
- `earnings_cache_duration`: 86400 (24 hours)
- `news_cache_duration`: 3600 (1 hour)

### Platform Settings
- `default_platform`: "alphavantage"
- `fallback_enabled`: true
- `timeout_duration`: 30 seconds

## Error Handling

### Cache Failures
- **Scenario**: Cache system unavailable
- **Response**: Proceed with API call, log warning
- **Fallback**: Direct API access without caching

### API Failures
- **Scenario**: External API returns error
- **Response**: Return cached data if available
- **Logging**: Record failure for monitoring

### Data Validation Failures
- **Scenario**: Invalid symbol or parameters
- **Response**: Return error without API call
- **Prevention**: Early validation prevents wasted calls

## Performance Benefits

### API Call Reduction
- **Before**: Each feature makes independent API calls
- **After**: Shared cache reduces calls by ~70%
- **Example**: 10 features requesting AAPL quote = 1 API call instead of 10

### Response Time Improvement
- **Cache Hit**: ~5ms response time
- **API Call**: ~500-2000ms response time
- **Overall**: 95% faster for cached requests

## Monitoring & Debugging

### Available Logs
- Cache hit/miss ratios
- API call frequency
- Data freshness validation results
- Error rates by platform

### Debug Information
```php
// Enable debug mode
define('TRADEPRESS_DATA_FRESHNESS_DEBUG', true);

// View cache statistics
$stats = $data_freshness_manager->get_cache_statistics();
```

## Developer Notes

### Adding New Data Types
1. Define cache duration in `get_cache_duration()`
2. Add validation rules in `validate_data_freshness()`
3. Update platform-specific handling if needed

### Platform Integration
1. Register platform in Recent Call Register
2. Implement platform-specific cache key generation
3. Add error handling for platform-specific responses

### Testing
- Use `tests/recent-call-register-tests.php` for validation
- Run Phase 3 tests to verify functionality
- Monitor cache performance in production

## User Perspective

### What Users See
- **Faster Response Times**: Cached data loads instantly
- **Reduced API Costs**: Fewer external API calls
- **Reliable Data**: Automatic fallback to cached data during API outages
- **Transparent Operation**: System works behind the scenes

### When Fresh Data is Fetched
- First request for a symbol/platform combination
- Cache expiration (based on data type)
- Manual refresh requests
- Cache system failures

This system ensures optimal performance while maintaining data accuracy across all TradePress features.
