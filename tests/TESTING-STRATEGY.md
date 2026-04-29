# TradePress Testing Strategy & Framework

## Overview: AI-Assisted Development Testing

This testing framework is designed to provide clear, measurable outputs that both developers and AI can use to verify requirements are met. Each test produces structured results that can be programmatically analyzed.

## Core Testing Principles

### 1. Output-Driven Testing
- Every test produces structured output (JSON/array format)
- Results include pass/fail status, metrics, and diagnostic data
- AI can parse results to verify requirement completion

### 2. Requirement Traceability
- Each test maps directly to specific requirements
- Test names follow requirement naming convention
- Results indicate which requirements are satisfied

### 3. Progressive Testing Levels
- **Unit Tests**: Individual function/method validation
- **Integration Tests**: Cross-component functionality
- **System Tests**: End-to-end workflow validation
- **Performance Tests**: Speed, memory, API call efficiency

## One-Command Sanity Suite

Use this first before changing scoring, indicators, directives, fixtures, or test infrastructure:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-sanity-suite.php"
```

The suite runs:

- `tests/run-fixture-sanity.php`
- `tests/run-indicator-sanity.php`
- `tests/run-directive-sanity.php --summary`

Expected successful summary:

```json
{
  "status": "passed",
  "suites_run": 3,
  "suites_passed": 3,
  "suites_failed": 0
}
```

If this fails, fix the failing suite before making broader scoring changes. The child suite summaries are included in the JSON output so humans and AI sessions can see where to focus next.

This suite is also wired into GitHub Actions as `.github/workflows/sanity-suite.yml`.

## Current Deterministic Fixture Slice

The first package-safe fixture is `tests/fixtures/canonical-24h-v1.json`.

This is a small synthetic dataset, not downloaded market data. It contains 24 hourly candles each for `NVDA`, `BTCUSD`, and `GBPUSD`, giving the project a stable surface for early correctness checks without provider access, licensing risk, or live API calls.

Run the standalone sanity runner from the plugin root:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-fixture-sanity.php"
```

The runner returns JSON for developers and AI sessions. It currently verifies:

- fixture JSON decoding
- top-level metadata and package-safety contract
- symbol list to candle-key consistency
- 24 candles per symbol
- timestamp ordering
- OHLCV numeric and range invariants
- RSI output is numeric and within `0..100`

This fixture is only a starter correctness slice. Full directive regression testing still needs the larger canonical dataset and approved baselines described in the directive testing strategy.

## Current Indicator Sanity Runner

The first standalone indicator maths runner is `tests/run-indicator-sanity.php`.

Run it from the plugin root:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-indicator-sanity.php"
```

It currently checks known-value cases for:

- EMA
- RSI
- MACD
- Bollinger Bands
- Stochastic
- ADX
- OBV
- VWAP
- MFI
- CCI

The runner may return `passed_with_warnings` when all hard checks pass but a formula edge case needs review. Zero-range and flat-price edge cases are hard checks where the expected neutral behaviour is known. Examples: RSI flat prices should return `50`, CCI flat prices should return `0`, ADX flat prices should return zero trend strength, and Stochastic flat prices should return neutral `K/D` values.

## Current Directive Sanity Runner

The first standalone directive contract runner is `tests/run-directive-sanity.php`.

Run it from the plugin root:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-directive-sanity.php"
```

For a shorter report, use:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-directive-sanity.php" --summary
```

This runner does not assume that every score is a `0..100` percentage. It validates scores against each directive's `get_max_score()` contract when available. Strategy totals may have a different maximum and may later be transformed into a percentage for display.

The runner currently checks:

- directive file discovery
- direct compatibility with `TradePress_Scoring_Directive_Base`
- required method presence: `calculate_score()`, `get_max_score()`, `get_explanation()`
- class load and instantiation
- positive numeric max-score contract
- rich sample input score extraction
- minimal input safety
- score values do not fall below zero or exceed the directive's declared max score

Files using old base classes or incomplete contracts are reported as `skipped`, not treated as passing. Files that fatal, throw, or return no score are reported as `failed`.

## Phase 3 Testing Implementation

### Recent Call Register Testing Suite

#### Test 1: Platform-Aware Caching
```php
/**
 * Requirement: Different platforms must have separate cache entries
 * Expected: alphavantage.get_quote(AAPL) ≠ finnhub.get_quote(AAPL)
 */
function test_platform_aware_caching() {
    $results = [
        'test_name' => 'platform_aware_caching',
        'requirement' => 'Separate cache entries per platform',
        'status' => 'pending',
        'details' => []
    ];
    
    // Test implementation here
    
    return $results;
}
```

#### Test 2: API Call Deduplication
```php
/**
 * Requirement: Multiple identical calls within 2-hour window use cache
 * Expected: Only 1 API call for multiple identical requests
 */
function test_api_call_deduplication() {
    $results = [
        'test_name' => 'api_call_deduplication',
        'requirement' => 'Single API call for duplicate requests',
        'metrics' => [
            'api_calls_made' => 0,
            'cache_hits' => 0,
            'expected_calls' => 1,
            'actual_calls' => 0
        ],
        'status' => 'pending'
    ];
    
    // Test implementation here
    
    return $results;
}
```

#### Test 3: Cross-Feature Integration
```php
/**
 * Requirement: Features can benefit from other features' API calls
 * Expected: Feature A's call benefits Feature B without duplicate API request
 */
function test_cross_feature_integration() {
    $results = [
        'test_name' => 'cross_feature_integration',
        'requirement' => 'Shared caching across plugin features',
        'features_tested' => ['directive_handler', 'data_freshness_manager'],
        'status' => 'pending'
    ];
    
    // Test implementation here
    
    return $results;
}
```

## Long-Term Testing Framework

### Testing Infrastructure

#### 1. Test Runner Class
```php
class TradePress_Test_Runner {
    private $results = [];
    
    public function run_test_suite($suite_name) {
        // Execute all tests in suite
        // Collect structured results
        // Generate summary report
    }
    
    public function get_results_for_ai() {
        // Return JSON-formatted results for AI parsing
    }
}
```

#### 2. Requirement Validator
```php
class TradePress_Requirement_Validator {
    public function validate_requirement($requirement_id, $test_results) {
        // Check if requirement is satisfied by test results
        // Return pass/fail with detailed reasoning
    }
}
```

#### 3. Performance Monitor
```php
class TradePress_Performance_Monitor {
    public function track_api_calls($feature, $endpoint) {
        // Monitor API call patterns
        // Detect inefficiencies
        // Generate optimization recommendations
    }
}
```

### Test Categories & Standards

#### Unit Tests
- **Naming**: `test_{class}_{method}_{scenario}`
- **Output**: Pass/fail, execution time, memory usage
- **Coverage**: All public methods, edge cases

#### Integration Tests
- **Naming**: `test_integration_{feature1}_{feature2}_{scenario}`
- **Output**: Data flow validation, API call counts, cache hit rates
- **Coverage**: Cross-component interactions

#### System Tests
- **Naming**: `test_system_{workflow}_{scenario}`
- **Output**: End-to-end success, performance metrics, user experience validation
- **Coverage**: Complete user workflows

#### Performance Tests
- **Naming**: `test_performance_{feature}_{metric}`
- **Output**: Execution time, memory usage, API call efficiency
- **Coverage**: Critical performance paths

### AI-Readable Test Results Format

```json
{
    "test_suite": "recent_call_register_phase3",
    "execution_time": "2024-01-15T10:30:00Z",
    "overall_status": "passed",
    "requirements_satisfied": [
        "platform_aware_caching",
        "api_call_deduplication",
        "cross_feature_integration"
    ],
    "tests": [
        {
            "name": "test_platform_aware_caching",
            "status": "passed",
            "requirement": "platform_aware_caching",
            "metrics": {
                "cache_entries_created": 2,
                "expected_entries": 2,
                "platform_separation": true
            },
            "execution_time_ms": 45
        }
    ],
    "performance_summary": {
        "api_calls_saved": 15,
        "cache_hit_rate": 0.85,
        "memory_usage_mb": 2.3
    },
    "recommendations": [
        "All Phase 3 requirements satisfied",
        "Ready for Phase 4 implementation"
    ]
}
```

## Implementation Plan

### Immediate (Phase 3)
1. Create test files for Recent Call Register
2. Implement structured result format
3. Test platform-aware caching
4. Validate API call deduplication
5. Verify cross-feature integration

### Short-term (Next 2 weeks)
1. Establish test runner infrastructure
2. Create requirement validation system
3. Implement performance monitoring
4. Build AI result parsing tools

### Long-term (Ongoing)
1. Expand test coverage to all features
2. Integrate with development workflow
3. Create automated test execution
4. Build regression test suite

## Benefits of This Approach

### For Developers
- Clear pass/fail criteria for each requirement
- Performance metrics to identify bottlenecks
- Structured debugging information
- Regression prevention

### For AI
- Parseable test results for requirement verification
- Quantitative metrics for optimization decisions
- Clear success criteria for feature completion
- Automated validation of implementation quality

### For Project Management
- Objective progress measurement
- Risk identification through test failures
- Quality assurance through comprehensive coverage
- Documentation of system capabilities

This framework transforms testing from a manual verification process into an automated quality assurance system that both human developers and AI can rely on for accurate project status assessment.
