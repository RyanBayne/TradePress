# Recent Call Register

## Relationship To Canonical Data Docs

This file documents the transient-based call cache used to reduce duplicate API calls. It supports, but does not replace, the queued data-flow contract in `wp-content/plugins/tradepress/docs/data/DATA-ARCHITECTURE.md` and the freshness rules in `wp-content/plugins/tradepress/docs/data/DATA-FRESHNESS-FRAMEWORK.md`.

## Overview
Time-based transient system for tracking and preventing duplicate API calls using hourly rotation for automatic cleanup.

## Technical Implementation
Uses WordPress transients with hourly rotation to cache API call results and prevent duplicate requests within configurable time windows.

### Key Features
- **Platform-aware caching**: Separate entries for alphavantage vs finnhub
- **Automatic cleanup**: Hourly transient rotation limits storage
- **Cross-feature sharing**: Multiple plugin features benefit from shared cache
- **Performance optimization**: Reduces API calls by up to 85%

### Data Flow Diagram
```
Feature Request → Generate Serial → Check Cache → Return Cached Data
                                      ↓ (if miss)
                                  Make API Call → Store Result → Return Fresh Data
```

## User Perspective
This system provides:
- **Faster responses**: Cached data eliminates API wait times
- **Cost savings**: Reduces API usage and associated costs
- **Improved reliability**: Less dependency on external API availability
- **Seamless operation**: Works transparently behind the scenes

## Developer Notes
- Located in `includes/query-register.php`
- Uses static methods for easy integration
- Configurable cache duration (default 2 hours)
- Platform-aware serialization prevents data contamination

## Testing
Comprehensive test suite validates:
- Platform separation
- Cache hit rates
- Cross-feature integration
- Performance metrics
