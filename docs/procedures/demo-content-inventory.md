# Demo Content Inventory

Last reviewed: 2026-04-30

This inventory tracks TradePress views and systems that still use demo, mock, sample, or randomly generated data. The product direction is to phase out user-visible demo content. Each item should be resolved by using imported/live data, replacing the view with a clear empty state, moving it to an extension, or removing it from the free core until live data exists.

## Decision States

| State | Meaning |
|---|---|
| Needs decision | Product choice still required. |
| Use imported data | Keep the feature, but replace generated data with stored/imported data. |
| Hide pending live data | Keep code available for development, but do not show to regular users. |
| Remove from core | Delete or move out of the free plugin. |
| Extension | Move to a paid extension after core beta is stable. |

## Admin View Inventory

| Area | View / tab | Evidence | Current recommendation |
|---|---|---|---|
| Automation | Dashboard | `admin/page/automation/automation-tabs.php` dashboard remains dev-only; `automation-controller.php` now also blocks dashboard metrics and bulk/scoring controls unless Developer Mode access is allowed. Older `automation-interface.php` contains simulated activity but is not the active menu renderer. | Keep hidden pending genuine live process/status dashboard. |
| Automation | Algorithm / Signals / Trading / Settings | Roadmap marks incomplete or extension-bound; controller endpoints for algorithm/signals/trading/scoring controls are now Developer Mode-only. | Hide pending extension/live implementation. |
| Automation | CRON Jobs | `admin/page/automation/automation-tabs.php` `cron_tab()` uses runtime WordPress scheduler data (`_get_cron_array()`, `wp_next_scheduled()`, options). Batch 1 updated empty-state labels for missing API key and empty scheduled jobs list. Static "Standard TradePress CRON Jobs" accordion remains conceptual and is tracked in GitHub issue [#72](https://github.com/RyanBayne/TradePress/issues/72). | Keep tab visible; continue refactor to runtime-derived cards and explicit states only. |
| Analysis | Recently Analysed Symbols | `admin/page/analysis/view/recent-symbols.php` — entire view was hardcoded demo data (AAPL, MSFT, NVDA, TSLA, AMZN, META, GOOGL, AMD, INTC, QCOM). No live data path exists. Batch 2 (2026-04-29): added mode guard so non-demo mode renders `In Development` empty state and returns early. Follow-up tracked in GitHub issue [#77](https://github.com/RyanBayne/TradePress/issues/77). | Implement live data path from stored symbol/analysis records; demo rendering retained for dev preview only. |
| Analysis | Support & Resistance | `get_demo_level_results()` in `admin/page/analysis/view/support-resistance.php`. Live path (`SupportResistanceLevels` class) exists but had two silent fallbacks to demo data (classes unavailable; bad result). Batch 3 (2026-04-30): both fallbacks replaced with explicit `In Development` / `No Data` empty states. Tracked in GitHub issue [#80](https://github.com/RyanBayne/TradePress/issues/80). | Stabilise OHLC import so live path reliably returns data; then remove `get_demo_level_results()`. |
| Analysis | Volatility Analysis | `tradepress_volatility_analysis_tab_content()` in `admin/page/analysis/view/volatility-analysis.php` called `calculate_demo_*()` unconditionally — `$is_demo` was set but never used to gate calls. Batch 3 (2026-04-30): added `tradepress_get_tab_mode()` guard; non-demo mode now shows `In Development` and returns early. Tracked in GitHub issue [#80](https://github.com/RyanBayne/TradePress/issues/80). | Implement live volatility path from stored OHLC data; then remove demo calculation calls. |
| Research | Economic Calendar | `tradepress_get_demo_economic_events()` remains in `admin/page/research/view/economic-calendar.php` for Developer Mode preview only. Batch 4 (2026-04-30): added `tradepress_get_tab_mode()` + `can_show_demo` guard and asset-loader guard. Follow-up batch (2026-04-30): regular users now keep the filter shell plus provider/import status, read stored `tradepress_economic_calendar_data` only, and see an explicit empty state instead of demo events. Tracked in [#81](https://github.com/RyanBayne/TradePress/issues/81). | Implement queued economic-calendar import/storage integration for FMP/TradingView or another selected provider; then remove demo call. |
| Research | Earnings Calendar | `tradepress_get_mock_earnings_data()` remains in `admin/page/research/view/earnings.php` (dev-only), and a provider/status panel now shows Alpha Vantage key status, CRON schedule state, and last import timestamp. Real-mode render now reads stored options only (no inline API fetch). | Continue with queued earnings import integration and replace remaining card/table randomised fields with stored analytics. |
| Research | News Feed | `get_demo_feed_items()` remains in `admin/page/research/view/news-feed.php` (dev-only), and a provider/status panel now shows Alpha Vantage + Alpaca support, key/config state, and import status/last import. | Implement queued news import + stored news rendering path so regular users move from empty state to live/cached items. |
| Research | Sector Rotation | `tradepress_get_demo_sector_data()`, `get_demo_top_industries()`, `get_demo_bottom_industries()` in `admin/page/research/view/sector-rotation.php`. No mode gate. Batch 4 (2026-04-30): added `tradepress_get_tab_mode()` + `can_show_demo` guard. Tracked in [#81](https://github.com/RyanBayne/TradePress/issues/81). | Use imported sector/industry price history; remove demo calls. |
| Research | Price Forecast | Mock forecast data and demo warning partial. | Hide unless real forecast provider/data exists. |
| Research | Market Correlations | `tradepress_generate_demo_correlations()` in `admin/page/research/view/market-correlations.php`. No mode gate. Batch 4 (2026-04-30): added `tradepress_get_tab_mode()` + `can_show_demo` guard. Tracked in [#81](https://github.com/RyanBayne/TradePress/issues/81). | Use stored price series correlation; remove demo call. |
| Watchlists | User Watchlists | View states that sample data is shown. | Replace with actual watchlist/symbol data. |
| Trading | SEES Demo | Randomised demo view and AJAX-generated mock score/price/change. | Replace with price-only scoring from imported free-provider data or hide. |
| Trading | Trading Strategies | Sample strategies for demonstration purposes. | Extension or hide until real strategy builder is ready. |
| Trading | Create Strategy | Demo-mode branch. | Extension or hide until real strategy workflow exists. |
| Scoring Directives | Demo Mode tab / notice | `admin/page/scoring-directives/scoring-directives-tabs.php` added the Demo tab and notice whenever Demo Mode was enabled. Batch 8 (2026-04-30): now requires Demo Mode plus Developer Mode access, so regular users do not see demo-only scoring UI. | Keep developer-only until public demo content is fully phased out. |
| Scoring Directives | Activity Logs | `admin/page/scoring-directives/view/logs.php` rendered hardcoded sample log rows and fake pagination totals. Batch 8 (2026-04-30): replaced sample rows with real `wp-content/scoring.log` entries and an explicit empty state when no log exists. | Keep visible with real logs only; improve parser/filtering later if needed. |
| Trading Platforms | API Efficiency | `rand()` calls in all 16 `view.*.php` files generated random endpoint status (`active`/`maintenance`) and fake usage counts on every page load. `mt_rand()` in `template.api-tab.php` helpers randomly showed fake rate limit counts and fake service disruptions. Batch 6 (2026-04-30): replaced all `rand()`/`mt_rand()` — endpoints default to `active`, usage counts set to 0, rate-limit `used` fields set to `null`, service status returns `unknown` pending real ping. Tracked in [#83](https://github.com/RyanBayne/TradePress/issues/83). | Implement real API call count tracking from response headers; implement service status page pings per provider. |
| Trading Platforms | Provider template | Demo mode can randomly determine service availability. Hardcoded `$local_status`/`$service_status`/`$rate_limits` in individual views used only when per-provider demo mode toggle is enabled. Random values removed in Batch 6 — static demo arrays remain but are opt-in only. | Replace static demo arrays with real API health checks once service status endpoints are integrated. |
| Data | Manage Sources test connection | Returns sample data for custom sources. | Keep only as a labelled developer test or replace with real validation. |
| Development | Current Task / GitHub tasks | Demo task/sample issues fallback. | Acceptable only on developer-only page. |

## System-Level Demo Data

| System | Evidence | Current recommendation |
|---|---|---|
| Recent Symbols tracker | `includes/utils/recent-symbols-tracker.php` `get_recent_symbols_data()` generated random fake prices for every symbol unconditionally. Batch 4 (2026-04-30): returns `data_status: no_data` placeholder unless in demo+dev mode. Tracked in [#81](https://github.com/RyanBayne/TradePress/issues/81). | Implement live read path from stored symbol data once import pipeline exists. |
| Forecast integration | `includes/forecasts/price-forecast-integration.php` `get_symbol_forecasts()` always called `get_demo_forecast_for_symbol()`. Batch 4 (2026-04-30): returns empty array unless in demo+dev mode. Tracked in [#81](https://github.com/RyanBayne/TradePress/issues/81). | Implement live forecast read from stored data. |
| News advisor integration | `includes/news/news-advisor-integration.php` `get_symbol_news()` and `get_additional_opportunities()` always used demo news + hardcoded trending symbols. Batch 4 (2026-04-30): both return empty unless in demo+dev mode. Tracked in [#81](https://github.com/RyanBayne/TradePress/issues/81). | Implement live news read from stored/imported news items. |
| API adapters (eToro, Fidelity, Trading212, TradingView, Webull) | `make_request()` in etoro, fidelity, trading212, tradingview and six methods in webull called `get_demo_data()` whenever credentials were absent. Batch 5 (2026-04-30): replaced with `WP_Error('api_key_required', ...)`. `TRADEPRESS_DEMO_MODE` constant path preserved. Tracked in [#82](https://github.com/RyanBayne/TradePress/issues/82). | Update Trading Platforms UI to detect `WP_Error('api_key_required')` from adapters and render `Requires API Key` empty state. |
| Trading212 shortcode | Frontend can display demo Trading212 data. | Remove user-visible demo mode; show "Requires API Key" instead. |
| API call debug cache | `TRADEPRESS_TESTING` can generate sample API call data. | Keep dev-only and never expose to regular users. |
| Advisor/news/forecast integrations | Demo news and forecast helpers. | Hide Advisor/Focus until live data exists. |
| Scoring directives | `includes/scoring-system/directives/news-sentiment-positive.php` `get_news_sentiment()` returned hardcoded mock sentiment scores for AAPL/TSLA/MSFT/GOOGL/AMZN (and a 0.55 fallback for all others) unconditionally on every scoring run, plus `usleep(100000)` delay. Batch 7 (2026-04-30): added demo+dev gate; mock data only in demo mode; live path returns `null` → score 0; `usleep` removed. Other directives (`volatility-tools.php` demo methods) only called from `volatility-analysis.php` which is already gated. Tracked in [#84](https://github.com/RyanBayne/TradePress/issues/84). | Implement real news sentiment read from stored/imported news items. |
| Symbol scoring | `posts/post-type-symbols.php` uses random max possible score. | Replace with real scoring metadata or remove display. |
| Installation/sample data | Setup can install mock price/technical data. | Make optional developer seed data only, never default public content. |

## Working Rule

For each item, choose one:

1. Replace with imported/live data.
2. Replace with a precise empty state.
3. Hide behind Developer Mode while work continues.
4. Move to an extension.
5. Remove from the free core.

Do not leave demo data visible to regular users as a substitute for a real feature.

## Kick-Off Micro-Batch (Small Start)

Scope this first pass to one tab only so progress is visible and low-risk.

### Batch 3: Analysis > Support & Resistance + Volatility Analysis tabs

- Objective: Eliminate silent demo fallbacks that could expose fake data to non-demo users.
- Source paths: `admin/page/analysis/view/support-resistance.php`, `admin/page/analysis/view/volatility-analysis.php`

#### Checklist

- [x] Identify all render files and data sources.
- [x] Classify each displayed block as live, cached, queued, empty, or demo.
- [x] Replace any silent demo fallbacks with one of the approved empty states.
- [x] Create one GitHub issue for the highest-impact follow-up using the new issue templates.
- [x] Record outcome in this inventory with evidence path(s).

#### Batch 3 Outcome (2026-04-30)

- **Support & Resistance**: Live path (`SupportResistanceLevels` + `TradePress_Financial_API_Service`) exists and is correct for demo mode. Two fallback paths silently called `get_demo_level_results()` in non-demo mode: (1) when required classes are unavailable, (2) when the live analysis returned a non-array. Both replaced with explicit empty states (`In Development` / `No Data`). Demo path unchanged.
- **Volatility Analysis**: `tradepress_volatility_analysis_tab_content()` had no mode gate — `$is_demo` was set but ignored; `calculate_demo_*()` always called. Added `tradepress_get_tab_mode()` guard at function top; non-demo mode now returns early with `In Development` state.
- Follow-up issue: [#80](https://github.com/RyanBayne/TradePress/issues/80).

### Batch 2: Analysis > Recently Analysed Symbols tab

- Objective: Classify data usage and apply correct empty-state handling.
- Source path: `admin/page/analysis/view/recent-symbols.php`

#### Checklist

- [x] Identify all view render files and data sources.
- [x] Classify each displayed block as live, cached, queued, empty, or demo.
- [x] Replace any generic placeholder messages with one of the approved empty states.
- [x] Create one GitHub issue for the highest-impact follow-up using the new issue templates.
- [x] Record outcome in this inventory with evidence path(s).

#### Batch 2 Outcome (2026-04-29)

- Scope audited: `admin/page/analysis/view/recent-symbols.php` (full file, 964 lines).
- Classification summary:
	- Demo only: Entire `$symbols` array (~460 lines) — 10 hardcoded US tech stocks (AAPL, MSFT, NVDA, TSLA, AMZN, META, GOOGL, AMD, INTC, QCOM) with static prices, scores, indicators, and analysis text dated 2023-06-25.
	- Demo only: Three helper functions (`getPortfolioAdvice()`, `getDayTradingAdvice()`, `getIntradayAdvice()`) operate only on the hardcoded score/signal fields.
	- Demo only: All three rendered sections (Portfolio, Day Trading, Intraday) — no live data path exists.
- Empty-state fix applied: Added mode guard after the demo-mode notice. Non-demo mode now renders `In Development` label and returns early. Demo mode retains full preview rendering.
- Follow-up issue: [#77](https://github.com/RyanBayne/TradePress/issues/77).

### Batch 1: Automation > CRON Jobs tab

- Objective: Confirm the CRON Jobs tab uses real scheduler/state data and no demo placeholders.
- Source path: `admin/page/automation/`
- Initial task size: 30-60 minute audit plus one focused follow-up issue.

#### Checklist

- [x] Identify all CRON Jobs tab render files and data sources.
- [x] Classify each displayed block as live, cached, queued, empty, or demo.
- [x] Replace any generic placeholder messages with one of the approved empty states.
- [x] Create one GitHub issue for the highest-impact follow-up using the new issue templates.
- [x] Record outcome in this inventory with evidence path(s).

#### Batch 1 Outcome (2026-04-29)

- Scope audited: `admin/page/automation/automation-tabs.php` (`cron_tab()`).
- Classification summary:
	- Live: Scheduled jobs table, next run times, WP CRON status, CRON URL/system info.
	- Cached/Options-backed: Earnings CRON settings and API key configuration state.
	- Empty state: No scheduled TradePress jobs now uses explicit `No Data` label.
	- Requires key state: Missing Alpha Vantage now uses explicit `Requires API Key` label.
	- Conceptual/non-runtime: "Standard TradePress CRON Jobs" accordion remains static reference content.
- Follow-up issue: [#72](https://github.com/RyanBayne/TradePress/issues/72).
