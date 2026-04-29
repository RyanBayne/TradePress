# TradePress — Directive Testing Strategy

**Status:** Draft — April 2026  
**Owner:** TradePress Core  
**Related docs:** [plugin testing strategy](../../tests/TESTING-STRATEGY.md) · `TradePress-Documentation/docs/DIRECTIVE-ANALYSIS.md` · [SCORING-SYSTEM.md](SCORING-SYSTEM.md)  
**ROADMAP context:** Feeds into Phase 1 "Testing framework" milestone

---

## 1. Purpose

Scoring Directives are the most business-critical code in TradePress. A silent change in RSI, VWAP, or any custom directive logic can shift every score in the system without triggering a PHP error. This document defines a strategy for catching that class of problem early, building an auditable history of tested directive behaviour, and giving developers confidence when upgrading or replacing indicator logic.

The strategy covers:
- Standard technical indicator directives (RSI, MACD, VWAP, Bollinger Bands, etc.)
- Custom/composite directives (ISA Reset, Weekly Rhythm, Institutional Timing, etc.)
- Versioned, reproducible test runs
- JSON-persisted result history and drift detection

---

## 2. Core Design Principles

| Principle | Rationale |
|-----------|-----------|
| **Deterministic first** | Tests must produce the same result every run; no live API calls in the test path |
| **Version-linked** | Every result artifact records the directive version that produced it |
| **Regime coverage** | Each directive is tested across multiple market regimes, not just happy-path data |
| **Approachable** | Developers can run tests from the WordPress admin UI without CLI knowledge |
| **Auditable drift** | A change in output must be visible and require explicit approval, not silently pass |

---

## 3. Directive Metadata (Required for Participation in Test System)

Every directive must declare a `$test_metadata` static property or companion constant:

```php
protected static array $test_metadata = [
    'directive_id'             => 'rsi',
    'directive_version'        => '1.2.0',          // semver, increment on logic change
    'input_contract_version'   => '1.0',             // increment when expected input shape changes
    'output_contract_version'  => '1.0',             // increment when score range/shape changes
    'output_range'             => [0, 100],           // expected min/max
    'test_profile'             => 'standard',         // fast | standard | extended
    'indicator_type'           => 'standard',         // standard | custom | composite
    'invariants'               => [
        'no_nan', 'no_inf', 'within_output_range',   // always-checked assertions
    ],
    'tolerance'                => 0.01,               // floating-point delta allowed in regression
];
```

This metadata drives: test discovery, artifact labelling, drift sensitivity, and dashboard display.

### Score scale contract

Directive and strategy scores must not be treated as universal `0..100` percentages.

Current and future test runners should validate score values against explicit contracts:

- Directive output is checked against the directive's `get_max_score($config)` value when available.
- Strategy totals are checked against the strategy's own maximum possible total.
- Percentages are display transformations, usually `raw_score / strategy_max_score * 100`, not the canonical score.
- A directive using `0..100` is valid, but it is only one scoring scale.
- A strategy can legitimately exceed `100` raw points when multiple active directives contribute to the total.

Any test or UI that hard-codes `0..100` without consulting the directive or strategy contract should be treated as a correctness risk.

---

## 4. Canonical Test Dataset

All directive tests share a single **canonical dataset** so results across different directives are comparable and drift is attributable to code — not data.

### Dataset specification

| Property | Value |
|----------|-------|
| Symbols | AAPL · MSFT · TSLA · GLD · SPY |
| Source | Pre-fetched OHLCV + volume fixtures (no live API) |
| Periods | 252 trading days (1 year) per symbol |
| Granularity | Daily close data |
| Storage | `tests/fixtures/canonical-dataset-v1.json` |
| Versioning | Dataset file is versioned; results reference `dataset_id + dataset_version` |

### Market regime slices (built into fixture)

Each symbol period includes tagged sub-windows:

| Regime tag | Description |
|------------|-------------|
| `trending_up` | Sustained uptrend, ~20% move |
| `trending_down` | Sustained downtrend, ~20% decline |
| `sideways` | Low directional movement, tight range |
| `high_vol_shock` | Sharp spike, ≥ 2 std dev day |
| `low_volume` | Below-average volume days |
| `earnings_window` | 5 days around known earnings event |

When importing or regenerating fixtures, all regime tags must be preserved to maintain comparability.

### Starter synthetic fixture

The plugin now includes a smaller bootstrap fixture at:

```
tests/fixtures/canonical-24h-v1.json
```

This fixture contains synthetic 24-hour hourly OHLCV data for `NVDA`, `BTCUSD`, and `GBPUSD`. It exists to validate the fixture format, OHLCV invariants, and basic indicator sanity without live API calls or downloaded market data.

It does not replace the full 252-day canonical directive dataset above. Treat it as Layer B infrastructure groundwork only: useful for checking that the test runner and AI-readable result format work before larger directive baselines exist.

Run from the plugin root:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-fixture-sanity.php"
```

### Custom directive fixtures

Custom/composite directives (e.g. ISA Reset, Institutional Timing) may define **supplementary fixtures** that extend the canonical dataset with domain-specific inputs (e.g. calendar dates, portfolio state). These live in `tests/fixtures/custom/`.

---

## 5. Test Layers

### One-command sanity suite

Use this first for local verification before changing indicator or directive code:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-sanity-suite.php"
```

It runs the fixture, indicator, and directive sanity runners and returns one JSON result. A healthy run should report `status: passed`, `suites_run: 3`, `suites_passed: 3`, and `suites_failed: 0`.

### Layer A — Pure Unit Tests (fast)

- Validate calculation logic in isolation
- No OHLCV data required; use minimal synthetic inputs
- Assert mathematical correctness (RSI formula, MACD line calculation, etc.)
- Assert edge cases: empty array, single data point, all-zero values
- Target: < 50 ms per directive

Current bootstrap runner:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-indicator-sanity.php"
```

This runner checks known-value cases for EMA, RSI, MACD, Bollinger Bands, Stochastic, ADX, OBV, VWAP, MFI, and CCI. Zero-range and flat-price edge cases are hard checks where the expected neutral behaviour is known. Examples: RSI flat prices should return `50`, CCI flat prices should return `0`, ADX flat prices should return zero trend strength, and Stochastic flat prices should return neutral `K/D` values.

### Layer B — Fixture Integration Tests (medium)

- Run directive against canonical dataset fixture slices
- Assert expected numeric outputs within tolerance band
- Assert output range, shape, and type contract using directive/strategy score metadata, not a hard-coded global percentage range
- Assert invariants (no NaN, no out-of-range score, etc.)
- Target: < 500 ms per directive per regime slice

Current bootstrap runner:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-directive-sanity.php"
```

Use `--summary` to avoid the full per-directive output:

```powershell
& "c:\wamp64\bin\php\php8.3.14\php.exe" "tests\run-directive-sanity.php" --summary
```

This runner validates directive loading, required method presence, `get_max_score()` availability, sample score extraction, and minimal input safety. It reports legacy base-class files and incomplete contracts explicitly so migration work can be tracked.

### Layer C — Regression / Snapshot Tests (slower)

- Run full directive suite against all canonical data
- Compare output hash and stats against the stored **approved baseline**
- Flag any drift beyond tolerance as a regression candidate
- Require explicit baseline approval to update
- Target: < 5 s per directive full run

All three layers can be run from the WordPress admin UI or CLI (`php tests/run-directives.php`).

---

## 6. JSON Result Artifact Schema

Every test run produces one artifact file per directive. Files are stored in:

```
tests/results/directives/{directive_id}/{YYYY-MM-DD}_{run_id}.json
```

An index file at `tests/results/directives/index.json` tracks all runs.

### Artifact structure

```json
{
  "run_id": "d1f3a2b9-...",
  "timestamp": "2026-04-27T14:30:00Z",
  "plugin_version": "1.0.95",
  "git_commit": "abc1234",

  "directive_id": "rsi",
  "directive_version": "1.2.0",
  "input_contract_version": "1.0",
  "output_contract_version": "1.0",

  "dataset_id": "canonical",
  "dataset_version": "1",
  "regime": "trending_up",
  "symbol": "AAPL",

  "test_layer": "B",
  "status": "pass",

  "assertions": [
    { "name": "within_output_range", "pass": true },
    { "name": "no_nan",              "pass": true },
    { "name": "score_gt_60_in_uptrend", "pass": true, "expected": ">60", "actual": 74.3 }
  ],

  "output_stats": {
    "score": 74.3,
    "min": 42.1,
    "max": 89.7,
    "mean": 66.2,
    "stddev": 12.4,
    "output_hash": "sha256:e3b0c4..."
  },

  "drift_vs_baseline": {
    "baseline_run_id": "9f2a1c0e-...",
    "baseline_version": "1.1.0",
    "score_delta": 0.2,
    "hash_match": true,
    "within_tolerance": true,
    "status": "clean"
  }
}
```

### Index entry (appended per run)

```json
{
  "directive_id": "rsi",
  "directive_version": "1.2.0",
  "run_id": "d1f3a2b9-...",
  "timestamp": "2026-04-27T14:30:00Z",
  "status": "pass",
  "drift_status": "clean",
  "baseline_approved": true
}
```

---

## 7. Baseline Approval Workflow

```
1. New directive version shipped
2. Run Layer C regression test
3. System compares output hash + stats to stored baseline
         │
    ┌────┴────────────┐
    │ Hash match?      │
    └────┬────────────┘
        Yes → PASS → no action required
        No  → DRIFT DETECTED
               │
          ┌────┴─────────────────────────────────────┐
          │ Delta within tolerance (e.g. < 0.01)?     │
          └────┬─────────────────────────────────────┘
              Yes → SOFT PASS (logged, no block)
              No  → REGRESSION FLAG
                     │
               Developer reviews diff
                     │
               Approve new baseline  OR  Revert code
                     │
               Approval stored in index with:
               - approver (WP user ID)
               - approved_at timestamp
               - reason note
               - previous + new baseline run IDs
```

Approval is done through the admin UI (Scoring Directives → Testing → Baselines). Unapproved regressions block promotion to "verified" status on the Directives dashboard.

---

## 8. Fail Policy

| Condition | Classification | Effect |
|-----------|----------------|--------|
| Runtime error / exception | **Hard fail** | Test blocked; must fix before merge |
| Output out of declared range | **Hard fail** | Test blocked |
| NaN / Inf in output | **Hard fail** | Test blocked |
| Output contract shape broken | **Hard fail** | Test blocked |
| Score delta > tolerance | **Regression flag** | Requires baseline approval |
| Score delta ≤ tolerance | **Soft pass** | Logged; no block |
| Hash mismatch, delta ≤ tolerance | **Soft pass + note** | Indicates floating-point drift |
| Custom directive invariant broken | **Hard fail** | Test blocked |

---

## 9. Standard vs. Custom Directive Handling

### Standard indicator directives

Strict numeric assertions are appropriate because the expected output is well-defined:
- RSI between 0–100 ✓
- MACD line = 12-period EMA − 26-period EMA (verifiable) ✓
- Bollinger Band width ≥ 0 ✓

Layer A tests verify the formula. Layer B tests verify the score derived from the indicator value.

### Custom / composite directives

Custom directives (ISA Reset, Institutional Timing, Weekly Rhythm, Friday Positioning, etc.) cannot be tested by formula — instead test by **behavioural invariants and monotonic expectations**:

| Invariant type | Example |
|----------------|---------|
| Bounds | Score is always 0–100 |
| Directionality | Score increases when input condition strengthens |
| Independence | Directive does not read external state mid-run |
| Nullability | Gracefully returns neutral score when data is missing |
| Seasonality contract | ISA Reset scores higher in April; lower in September |

Custom directives must supply at least one Layer B fixture with expected output ranges per regime.

---

## 10. Admin UI Integration

### Scoring Directives → Testing tab (new)

| Panel | Content |
|-------|---------|
| **Run Tests** | Select directive(s) + layer, trigger run, see live output |
| **Directives Status** | Table: all directives, current version, last test date, pass/fail, drift status |
| **Baselines** | List pending regressions awaiting approval; approve/reject UI |
| **History** | Run log with filter by directive, date, status, version |
| **Fixtures** | View / refresh canonical dataset; import custom fixtures |

---

## 11. Phased Implementation Plan

### Phase 1 — Foundation (aligns with ROADMAP Phase 1 "Testing framework")

- [ ] Add `$test_metadata` to all 27 active directives
- [ ] Build canonical dataset fixture (`tests/fixtures/canonical-dataset-v1.json`) using 5 symbols × 252 days
- [ ] Tag regime windows in fixture
- [ ] Implement `TradePress_Directive_Test_Runner` class (extend existing `TradePress_Test_Runner`)
- [ ] Implement Layer A test scaffolding (unit assertions + invariants)
- [ ] Implement Layer B test execution against fixture slices
- [ ] Produce JSON result artifacts per run
- [ ] Implement `tests/results/directives/index.json` tracking
- [ ] Build admin Testing tab (run + status table only)

**Deliverable:** All directives have metadata; Layer A+B tests run; results stored as JSON.

### Phase 2 — Regression Intelligence

- [ ] Implement Layer C snapshot comparison
- [ ] Implement baseline storage and approval workflow
- [ ] Add drift detection with configurable tolerance per directive
- [ ] Build Baselines panel in admin UI
- [ ] Build History log panel with filters
- [ ] Add notification on unapproved regression (admin notice)

**Deliverable:** Any logic change in a directive is automatically flagged before going unnoticed.

### Phase 3 — Backtesting Alignment

- [ ] Reuse canonical dataset in backtesting harness
- [ ] Map directive output history to strategy performance outcomes
- [ ] Add scenario packs (earnings windows, volatility events)
- [ ] Export directive performance metrics alongside backtest results
- [ ] Visualise per-directive contribution to historical strategy P&L

**Deliverable:** Directive accuracy can be correlated with real trading outcomes.

---

## 12. Relationship to Existing Test Infrastructure

The existing `wp-content/plugins/tradepress/docs/testing/TESTING-SYSTEM.md` describes a general plugin test framework (`TradePress_Test_Registry`, `TradePress_Test_Runner`, etc.). The directive testing system **extends** that infrastructure:

- `TradePress_Directive_Test_Runner` extends `TradePress_Test_Runner`
- Directive results use the existing `tradepress_test_runs` table schema, extended with directive-specific columns
- The Scoring Directives testing admin tab is a child of the broader Testing admin section
- Fixture loading utilities are shared with any other fixture-based tests defined in the general framework

---

## 13. Open Decisions

| Decision | Options | Recommended |
|----------|---------|-------------|
| Baseline strictness | Strict (any delta fails) · Tolerant (threshold only) · Hybrid | **Hybrid** — strict for contracts/invariants, tolerant for floating-point drift |
| Fixture data source | Manually curated · Alpha Vantage snapshot · Generated synthetic | **Alpha Vantage snapshot** for standard directives; synthetic for custom |
| Fixture refresh policy | Manual only · Versioned refresh on new dataset tag | **Versioned refresh** — keep v1 locked; create v2 for major market regime updates |
| Regression block policy | Block all PRs with unapproved regressions · Warn only | **Block** for hard fails; **warn** for soft pass drift |
| History retention | Keep all · Keep last N runs | **Keep last 50 runs** per directive + all baseline-approved runs |

---

*Document created: 2026-04-27. Update `directive_version` fields in directives as changes are made. Revisit Phase thresholds after Phase 1 delivery.*
