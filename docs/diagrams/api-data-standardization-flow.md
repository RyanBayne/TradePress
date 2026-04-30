# API Data Standardization Flow

## Architecture Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   100+ Future   │    │   D17 RSI       │    │   Database      │
│   Directives    │    │   Directive     │    │   Storage       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         └───────────────────────┼───────────────────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │  API Data Adapter       │
                    │  (Standardization)      │
                    │                         │
                    │  • standardize_rsi()    │
                    │  • standardize_quote()  │
                    │  • standardize_macd()   │
                    │  • standardize_cci()    │
                    └─────────────────────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │  API Factory            │
                    │  (Provider Selection)   │
                    │                         │
                    │  • Rate limit tracking  │
                    │  • Automatic fallback   │
                    │  • Cooling periods      │
                    └─────────────────────────┘
                                 │
                ┌────────────────┼────────────────┐
                │                │                │
                ▼                ▼                ▼
    ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
    │ Alpha Vantage   │ │    Finnhub      │ │   Future APIs   │
    │                 │ │                 │ │                 │
    │ • RSI format A  │ │ • RSI format B  │ │ • RSI format C  │
    │ • Quote format A│ │ • Quote format B│ │ • Quote format C│
    └─────────────────┘ └─────────────────┘ └─────────────────┘
```

## Data Flow Process

### 1. Directive Request
```
D17 RSI Directive → "I need RSI data for NVDA"
```

### 2. API Factory Selection
```
API Factory → Check rate limits → Select best provider → Return API instance
```

### 3. Provider-Specific API Call
```
if (finnhub) → api.get_rsi(symbol, period)
if (alphavantage) → api.make_request('RSI', params)
```

### 4. Data Standardization
```
Raw API Response → API Data Adapter → Standardized Format
```

### 5. Universal Data Format
```json
{
  "rsi_value": 45.67,
  "timestamp": "2025-01-20T16:00:00Z",
  "provider": "finnhub",
  "symbol": "NVDA",
  "period": 14
}
```

## Benefits

### ✅ Eliminates Code Duplication
- **Before**: Each directive handles provider parsing
- **After**: Single adapter handles all parsing

### ✅ Scalable Architecture  
- **100+ Directives**: All use same adapter
- **10+ API Providers**: All parsed consistently

### ✅ Database-Ready Format
- Standardized data structure
- Ready for caching and storage
- Consistent across all providers

### ✅ Future-Proof Design
- New providers: Add parsing method to adapter
- New directives: Use existing standardized data
- No directive code changes needed

## Implementation Strategy

### Phase 1: Core Indicators (Current)
```
✅ RSI standardization
🔄 CCI standardization  
🔄 MACD standardization
🔄 Quote standardization
```

### Phase 2: Extended Coverage
```
📋 ADX standardization
📋 Bollinger Bands standardization
📋 Volume standardization
📋 EMA standardization
```

### Phase 3: Advanced Features
```
📋 Historical data standardization
📋 Real-time data standardization
📋 News sentiment standardization
📋 Fundamental data standardization
```

## Code Architecture

### Adapter Pattern Implementation
```php
class TradePress_API_Data_Adapter {
    public static function standardize_rsi_data($raw_data, $provider_id) {
        switch ($provider_id) {
            case 'alphavantage': return self::parse_alphavantage_rsi($raw_data);
            case 'finnhub': return self::parse_finnhub_rsi($raw_data);
            case 'future_provider': return self::parse_future_rsi($raw_data);
        }
    }
}
```

### Directive Usage
```php
// All directives use same pattern
$raw_data = $api->get_rsi($symbol, $period);
$standardized = TradePress_API_Data_Adapter::standardize_rsi_data($raw_data, $provider_id);
// $standardized is always same format regardless of provider
```

This architecture ensures clean separation of concerns and eliminates the code duplication you were concerned about.