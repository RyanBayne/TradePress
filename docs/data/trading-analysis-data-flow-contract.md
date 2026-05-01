# Trading and Analysis Data-Flow Contract

Status: Sprint 1 contract slice, 2026-05-01

This document records data-flow decisions for Trading and Analysis read-only/admin surfaces as they are reviewed for release readiness.

## Contract

Request -> read stored data or user input -> queue refresh if stale and provider-backed -> render state from storage or explicit user input.

No Trading or Analysis view should call external APIs during page rendering. Provider-backed views must use stored rows/options/transients and queue refresh work through the existing import queue.

## Data-Flow Decisions

| View | Data purpose | Source of truth | Primary provider | Fallback provider | Unsuitable providers | Freshness SLA | Queue trigger threshold | UI data mode |
|---|---|---|---|---|---|---|---|---|
| Trading > Calculators | User-entered trading arithmetic | User-entered form inputs in the browser | Not applicable | Not applicable | All external providers are unsuitable for this pure calculator surface | Not applicable | None; no queued refresh required | Live for the active calculator; Coming Soon for unfinished calculator panels |
| Trading > Trading Strategies > Create Trading Strategy | Dev-only rule-threshold strategy design (decision trigger configuration) | `TradePress_Scoring_Directives_Registry` for available indicator definitions; strategy-level rule settings from the builder form | Not applicable during design | Not applicable | External market-data providers and broker APIs are unsuitable in the builder render path | Not applicable for design state | None from the view; future save/test execution must use dedicated trading strategy handlers and data freshness gates | Dev-only Demo |
| Trading > Trading Strategies > My Custom Strategies | Dev-only review of stored trading-rule drafts | Transitional storage currently reads `tradepress_scoring_strategies` and related scoring tables until dedicated trading-strategy persistence is split out | Not applicable for list rendering | Not applicable | External market-data providers and broker APIs are unsuitable in the list render path | Stored draft/reference context | None from the view; testing/execution must be separate and queued/gated | Dev-only Demo or Empty |
| Trading > Trading Strategies > Built-in Strategies | Static planning reference list | Static array in `admin/page/trading/trading-tabs.php` | Not applicable | Not applicable | External providers are unsuitable; rows are not live trading strategies | Not applicable | None | Dev-only Demo |
| Trading > SEES Demo | Dev-only scoring/demo review | Bundled test symbol metadata generated through Developer Mode AJAX only | Not selected | Not applicable | External market-data providers and broker APIs are unsuitable in the demo render/AJAX path | Not applicable for demo state | None; future release-facing SEES output must read stored scoring results | Dev-only Demo |
| Trading > SEES Diagnostics | Dev-only strategy trace workspace | Stored scoring strategy records/components; bundled test symbol metadata for symbol cards | Not selected | Not applicable | External market-data providers and broker APIs are unsuitable in the diagnostics render/AJAX path | Not applicable for demo state | None; future release-facing diagnostics must read stored scoring/trading execution results | Dev-only Demo |
| Analysis > Recently Analysed Symbols | Dev-only audit/list of previously scored symbols | `tradepress_symbol_scores` | Not applicable for list rendering | Not applicable | External market-data providers are unsuitable in the list render path | Stored score/reference context | None from the view | Cached or Empty |
| Analysis > Support & Resistance | Dev-only placeholder for future support/resistance analysis | No stored OHLC source connected to the view yet | Not selected | Not selected | Direct provider/analyzer execution during render is unsuitable | Not applicable until stored OHLC data is connected | None from the view; future analysis must read stored candles and queue calculation/import work | Dev-only Demo |
| Analysis > Volatility Analysis | Dev-only placeholder for future volatility analysis | No stored volatility/OHLC source connected to the view yet | Not selected | Not selected | Direct provider/analyzer execution during render is unsuitable | Not applicable until stored OHLC or volatility data is connected | None from the view; future analysis must read stored candles and queue calculation/import work | Dev-only Demo |

## View State Rules

## SEES Diagnostics Trace Payload Contract

SEES Diagnostics is a Developer Mode-only trace workspace. It explains how a selected stored strategy would be evaluated for a bundled diagnostic symbol. It is not a live market-data or trade-execution path.

The trace payload returned by `tradepress_ajax_fetch_sees_diagnostic_trace()` is the canonical contract for Sprint 2 UI rendering and Sprint 3 verification.

### Payload Fields

| Field | Required | Meaning |
|---|---|---|
| `trace_mode` | Yes | Either `scoring` or `trading`. `scoring` traces stored scoring directives. `trading` is transitional and traces stored strategy components until dedicated trading strategy persistence exists. |
| `symbol`, `name`, `industry` | Yes | Diagnostic symbol context. Current symbol cards use bundled test metadata only. |
| `strategy_id`, `strategy_name`, `strategy_type`, `strategy_status`, `strategy_storage` | Yes | Selected stored strategy metadata. `strategy_storage` is currently `tradepress_scoring_strategies`. |
| `component_count` | Yes | Count of strategy components included in the trace after loading stored strategy directives. |
| `passed_count` | Yes | Count of trace steps where the component evaluation passed. |
| `component_warning_count` | Yes | Count of trace steps carrying a `warning` because the component is missing, disabled, or unavailable. |
| `score` | Yes | Raw weighted total for the selected strategy trace. It is not globally normalised to `0..100`. |
| `max_possible_score` | Yes | Sum of each trace step's `max_weighted_score`. This is the selected strategy's reachable maximum for the current component stack. |
| `score_percent_of_max` | Yes | Derived display value: `(score / max_possible_score) * 100`, or `0` when `max_possible_score` is `0`. |
| `minimum_threshold` | Yes | Strategy minimum raw score threshold. For trading mode this is retained as strategy metadata even when the immediate decision branch uses a component pass count. |
| `threshold_distance` | Yes | Canonical threshold distance: `score - minimum_threshold`. Positive and zero values mean the score gate is met. Negative values mean the score is short by that amount. |
| `distance_to_threshold` | Compatibility | Backwards-compatible alias for `threshold_distance` while existing UI code migrates. New code should read `threshold_distance`. |
| `decision` | Yes | Human-readable outcome string for the diagnostics panel. |
| `decision_state` | Yes | Machine-readable top-level state: `continued` or `stopped`. |
| `decision_branch_details` | Yes | Ordered list of branch/gate results that explain warnings, stops, and continuation. |
| `next_function` | Yes | Next intended system function or the stop-return target. It is explanatory in diagnostics and must not execute trades. |
| `process` | Yes | Ordered checkpoint list showing the high-level trace pipeline and whether each checkpoint passed. |
| `steps` | Yes | Ordered component-level trace records. Empty when no strategy or no components can be evaluated. |
| `generatedAt` | Yes | WordPress-local timestamp for the generated trace. |

### Branch Details

Each item in `decision_branch_details` must include:

| Field | Meaning |
|---|---|
| `gate` | Stable gate key such as `strategy-selection`, `component-health`, `score-threshold`, or `indicator-threshold`. |
| `status` | One of `passed`, `failed`, or `warning`. |
| `reason` | Short user-readable reason for the branch result. |
| `code_path` | Function or logical branch that produced the result. |

Branch status semantics:

- `passed`: the gate allowed evaluation to continue.
- `failed`: the gate blocked the trace and should produce `decision_state: stopped`.
- `warning`: the trace found degraded or incomplete component state. A warning does not automatically stop the trace unless a later required gate fails, but the warning count must remain visible.

Top-level `decision_state` is intentionally narrower than branch `status`:

- `continued`: the selected branch would proceed to the next scoring/trading decision function.
- `stopped`: the selected branch returned a stop decision to the diagnostics panel.

### Component Warning Semantics

Warnings are component-health findings, not hidden failures. A trace step must set `warning` when a stored strategy component cannot be evaluated as a healthy active component, including:

- The component exists in strategy storage but is inactive.
- The directive/indicator definition is unavailable in the current registry.
- The registry definition is disabled.

When a step has a warning:

- `component_available` and `component_active` must identify the underlying state.
- `weighted_score` must not contribute positive score for blocked components.
- `component_warning_count` must increment.
- `decision_branch_details` must include a `component-health` item with `status: warning`.

### Step Records

Each entry in `steps` should identify the real stored strategy component rather than a placeholder label. Required step fields are:

| Field | Meaning |
|---|---|
| `id`, `label`, `description` | Component identity shown in the trace. |
| `component_type` | `directive` in scoring mode or `indicator` in trading mode. |
| `component_source` | Storage source for the component, currently `tradepress_strategy_directives`. |
| `component_available`, `component_active` | Boolean component-health flags. |
| `code_path` | Registry/directive path or transitional trading strategy indicator path. |
| `input_value`, `score`, `weight`, `weighted_score`, `max_weighted_score`, `threshold` | Numeric evaluation values. |
| `passed` | Boolean component pass result. |
| `formula_text` | Human-readable score calculation summary. |
| `warning` | Empty or a component-health warning string. |
| `next_action` | Short explanation of whether the component continues or is blocked. |

### Mode Rules

- `scoring` mode uses the selected scoring strategy's stored components and score threshold. High score, low qualified score, and below-threshold branches are expressed through `score-threshold` branch details.
- `trading` mode currently uses transitional stored scoring strategy components until dedicated trading strategy tables exist. The immediate branch uses component pass count, with `indicator-threshold` branch details.
- Neither mode may call external providers or execute trade actions from the diagnostics render/AJAX path.

## Dual-Algorithm Boundary

TradePress keeps two algorithm families with different responsibilities:

1. **Scoring Directives algorithm (optimization/ranking):** Produces weighted, strategy-aware scores to rank opportunity quality. It is continuous and can demand near-optimal conditions before a high score appears.
2. **Trading Strategy algorithm (rule-threshold trigger):** Evaluates whether enough configured rules are currently within acceptable bounds (for example `3 of 5` or `60%`) and may require multi-period confirmation before triggering.

Design constraints for current core scope:

- Trading Strategy must not be positioned as a duplicate scoring engine.
- Strategy-level precision controls (for example trend-of-change gating and wait-for-better-entry logic) are optional complexity and should be designed now but can remain non-blocking for core release behavior.
- If score is used by Trading Strategy, it is an input signal, not a replacement for explicit rule-threshold configuration.

- `Live`: calculator output is produced immediately from user-entered inputs.
- `Coming Soon`: calculator panel is present but not implemented.
- Runtime health is `not_applicable` for pure calculators because no provider, queue, or stored market data dependency is involved.

## Validation Notes

- `admin/page/trading/view/calculators.php` renders forms and static state only.
- `assets/js/tradepress-calculators.js` calculates the Averaging Down result client-side from form inputs.
- No external API provider, queue trigger, or stored market-data read exists in the Trading > Calculators render path.
- `admin/page/trading/view/create_strategy.php` renders a rule-threshold strategy builder from registry/static directive definitions only; it does not execute scoring loops or trading actions.
- `admin/page/trading/view/trading-strategies.php` reads transitional stored drafts from scoring strategy tables and presents them as dev-only trading-rule strategy records until dedicated persistence is separated.
- `admin/page/trading/trading-tabs.php::tradepress_display_builtin_strategies()` renders static planning definitions only.
- `assets/js/trading-create-strategy.js` and `assets/js/trading-strategies.js` remain client-side design helpers; they do not execute provider calls or trading actions.
- `admin/page/trading/view/sees-demo.php` is labelled Dev-only Demo and does not use provider data.
- `admin/page/trading/trading-tabs.php` now requires Developer Mode for SEES demo and diagnostic AJAX handlers, not only `manage_options`.
- `admin/page/trading/view/sees-diagnostics.php` is labelled Dev-only Demo and reports its transitional storage boundary.
- `assets/js/sees-diagnostics.js` displays selected strategy metadata, component counts, decision state, and next function from the AJAX trace response.
- `assets/js/sees-diagnostics.js` now reads `threshold_distance` as the canonical score-threshold distance, keeps `distance_to_threshold` as fallback compatibility, labels branch statuses, distinguishes component warnings from hard failures, and supports copying the trace payload for verification evidence.
- Create Scoring Strategies now exposes `Minimum Score Threshold` and saves it as `min_score_threshold`; this value is the source of `minimum_threshold` in SEES Diagnostics scoring-mode traces.
- Manual continued-path evidence captured: strategy `SEES UI Testing`, 4 components, score `79.36 / 100.00`, threshold distance `+29.36`, decision continued to `tradepress_rank_scoring_strategy_result()`.
- `admin/page/analysis/view/recent-symbols.php` reads `tradepress_symbol_scores` only and renders Cached or Empty state.
- `admin/page/analysis/view/support-resistance.php` no longer instantiates `TradePress_Financial_API_Service` or `SupportResistanceLevels` during render; it validates explicit user input and reports that stored OHLC-backed analysis is not connected yet.
- `admin/page/analysis/view/volatility-analysis.php` renders a dev-only placeholder/status panel only; no provider or analyzer is called during render.

## Config-Path Contract (Trading Platforms tabs)

The Trading Platforms page exposes API management, provider comparisons, and enable/disable switches for configured broker and market-data APIs. Confirmed by source inspection (AGENT-2 Option B audit, 2026-05-01):

| View | Data purpose | Source of truth | Provider call in render path | Queue trigger | Freshness SLA | UI data mode |
|---|---|---|---|---|---|---|
| Trading Platforms > Overview | Landing page for platform integrations | Static/stored provider directory listing via `TradePress_API_Directory::get_all_providers()` | None | None | Not applicable | Config/Live |
| Trading Platforms > API Management | Stored API key and endpoint configuration per provider | WordPress options via `get_option()` per provider slug | None — credentials are read from storage; no API test call during render | None | Not applicable; configuration surface | Config/Live |
| Trading Platforms > API Switches | Enable/disable per-provider service flags | WordPress options (service enable/disable switches) | None | None | Not applicable | Config/Live |
| Trading Platforms > Provider Comparisons | Static comparison grid of supported providers and capability tiers | Static/hardcoded comparison data in the comparisons view or provider directory | None | None | Not applicable | Config/Live (static) |

**Rules:**
- Trading Platforms tabs must not call external provider APIs in their render path.
- Live credential testing (if implemented) must use an explicit AJAX action, never page render.
- All provider API calls must go through the background queue processor.
