# Directive Testing Procedure & Standard Architecture

## Testing Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    DIRECTIVE TESTING PROCEDURE                  │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
│   User Triggers  │───▶│  Directive Test  │───▶│   Test Results   │
│   Test Command   │    │     Handler      │    │   & Logging      │
└──────────────────┘    └──────────────────┘    └──────────────────┘
         │                        │                        │
         │                        ▼                        │
         │              ┌──────────────────┐               │
         │              │  Get Test Symbol │               │
         │              │  (NVDA/Random)   │               │
         │              └──────────────────┘               │
         │                        │                        │
         │                        ▼                        │
         │              ┌──────────────────┐               │
         │              │ Check Call Cache │               │
         │              │  (30min window)  │               │
         │              └──────────────────┘               │
         │                        │                        │
         │                        ▼                        │
         │              ┌──────────────────┐               │
         │              │  Directive Only  │               │
         │              │   Fetches Data   │               │
         │              └──────────────────┘               │
         │                        │                        │
         │                        ▼                        │
         │              ┌──────────────────┐               │
         │              │ Process & Score  │               │
         │              │   Technical      │               │
         │              │   Indicators     │               │
         │              └──────────────────┘               │
         │                        │                        │
         └────────────────────────┼────────────────────────┘
                                  ▼
                        ┌──────────────────┐
                        │  Log Results to  │
                        │  directives.log  │
                        └──────────────────┘
```

## Standard Directive Architecture

### ❌ WRONG APPROACH (Causes Duplicate API Calls)
```
Directive Handler ──┐
                   ├──▶ Alpha Vantage API ──▶ Database Insert
Directive Class ───┘
```

### ✅ CORRECT APPROACH (Single API Call)
```
Directive Handler ──▶ Directive Class ──▶ Alpha Vantage API ──▶ Database Insert
                                      │
                                      └──▶ Call Register Cache (30min)
```

## Implementation Standards

### 1. Directive Handler Responsibilities
- **Get test symbol** (NVDA or random based on settings)
- **Pass symbol to directive** 
- **Log test results**
- **NO API CALLS** - Let directive handle its own data

### 2. Directive Class Responsibilities  
- **Check Call Register cache** (30-minute window)
- **Fetch fresh data if needed** from Alpha Vantage
- **Process technical indicators**
- **Return calculated scores**
- **Handle own caching logic**

### 3. Testing Command Structure
```bash
php test-directive.php [directive-name]
```

### 4. Required Directive Methods
```php
class DirectiveName extends ScoringDirectiveBase {
    public function fetch_fresh_data($symbol) {
        // Check cache first
        // Make API call if needed
        // Return processed data
    }
    
    public function calculate_score($symbol_data) {
        // Process indicators
        // Return score array
    }
}
```

## Directives to Check for Double API Calls

Based on DEVELOPMENT-NOTES.md, these directives need verification:

### 🔄 READY FOR TESTING
- **D17 RSI** - ✅ Fixed (no longer has double API calls)
- **D22 Volume** - ⚠️ Need to check
- **D4 CCI** - ⚠️ Need to check  
- **D10 MACD** - ⚠️ Need to check

### 🔄 BACKUP DIRECTIVES
- **D3 Bollinger Bands** - ⚠️ Need to check
- **D7 EMA** - ⚠️ Need to check

## Testing Checklist

For each directive, verify:

1. **Single API Call Pattern**
   - [ ] Directive handler does NOT make API calls
   - [ ] Only directive class fetches data
   - [ ] Call Register caching is used

2. **Proper Method Structure**
   - [ ] `fetch_fresh_data()` method exists and is public
   - [ ] Cache checking before API calls
   - [ ] Proper error handling

3. **Test Command Works**
   - [ ] `php test-directive.php [name]` executes
   - [ ] Results logged to directives.log
   - [ ] No duplicate API call notices

4. **Data Processing**
   - [ ] Technical indicators calculated correctly
   - [ ] Score array returned with proper structure
   - [ ] Edge cases handled (missing data, etc.)

## Analysis Results

### ✅ DIRECTIVES WITH CORRECT ARCHITECTURE
- **D4 CCI** - Has `fetch_fresh_cci_data()` method and uses Call Register caching
- **D10 MACD** - Has `fetch_fresh_macd_data()` method and uses Technical Indicator Cache

### ❌ DIRECTIVES WITH MISSING METHODS
- **D22 Volume** - No `fetch_fresh_data()` method, relies on handler API calls
- **D3 Bollinger Bands** - No `fetch_fresh_data()` method, relies on handler API calls  
- **D7 EMA** - No `fetch_fresh_data()` method, relies on handler API calls

### 🔧 DIRECTIVE HANDLER ISSUES
The directive handler is making duplicate API calls for:
- CCI (handler makes API call + directive makes its own)
- MACD (handler makes API call + directive makes its own)
- Bollinger Bands (handler makes API call, directive expects it)
- EMA (handler makes API call, directive expects it)
- Volume (handler gets volume from quote, directive expects it)

## ✅ FIXES COMPLETED

### 1. ✅ Updated Directive Handler
- ✅ Removed API calls for CCI and MACD (directives handle their own)
- ✅ Removed API calls for Volume, Bollinger Bands, and EMA
- ✅ Updated test data extraction to use scores array

### 2. ✅ Updated Volume Directive
- ✅ Added `fetch_fresh_volume_data()` method
- ✅ Updated `calculate_score()` to fetch data when not provided
- ✅ Returns volume data in scores array

### 3. ✅ Updated Bollinger Bands Directive  
- ✅ Added `fetch_fresh_bollinger_data()` method
- ✅ Implemented Call Register caching (30 minutes)
- ✅ Updated `calculate_score()` to fetch data when not provided
- ✅ Returns band values in scores array

### 4. ✅ Updated EMA Directive
- ✅ Added `fetch_fresh_ema_data()` method  
- ✅ Implemented Call Register caching (30 minutes)
- ✅ Updated `calculate_score()` to fetch data when not provided
- ✅ Returns EMA value in scores array

## 🎯 STANDARD ARCHITECTURE ACHIEVED

All directives now follow the standard pattern:
1. **Directive Handler** - Gets test symbol, passes to directive, logs results
2. **Directive Class** - Checks cache, fetches fresh data if needed, calculates scores
3. **Call Register** - Provides 30-minute caching for all API calls
4. **Single API Call** - No more duplicate calls between handler and directive

## 📋 TESTING READY

All 5 directives for Alpha Release are now ready for testing:
- **D1 ADX** ✅ (Already tested)
- **D17 RSI** ✅ (Already tested) 
- **D22 Volume** ✅ (Fixed - ready for testing)
- **D4 CCI** ✅ (Fixed - ready for testing)
- **D10 MACD** ✅ (Fixed - ready for testing)

Run tests with:
```bash
php test-directive.php volume
php test-directive.php cci  
php test-directive.php macd
```