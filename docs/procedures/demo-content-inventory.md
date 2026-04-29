# Demo Content Inventory

Last reviewed: 2026-04-29

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
| Analysis | Recently Analysed Symbols | `admin/page/analysis/view/recent-symbols.php` shows demo notice/sample data. | Use imported analysis data or hide page. |
| Analysis | Support & Resistance | `get_demo_level_results()` in `admin/page/analysis/view/support-resistance.php`. | Use OHLC-derived levels or hide page. |
| Analysis | Volatility Analysis | Calls `TradePress_Volatility_Tools::calculate_demo_*()`. | Use OHLC-derived volatility/ATR/beta or hide page. |
| Research | Economic Calendar | `tradepress_get_demo_economic_events()` in `admin/page/research/view/economic-calendar.php`. | Replace with Alpha Vantage/imported calendar or hide tab. |
| Research | Earnings Calendar | `tradepress_get_mock_earnings_data()` remains in `admin/page/research/view/earnings.php` (dev-only), and a provider/status panel now shows Alpha Vantage key status, CRON schedule state, and last import timestamp. Real-mode render now reads stored options only (no inline API fetch). | Continue with queued earnings import integration and replace remaining card/table randomised fields with stored analytics. |
| Research | News Feed | `get_demo_feed_items()` remains in `admin/page/research/view/news-feed.php` (dev-only), and a provider/status panel now shows Alpha Vantage + Alpaca support, key/config state, and import status/last import. | Implement queued news import + stored news rendering path so regular users move from empty state to live/cached items. |
| Research | Sector Rotation | `tradepress_get_demo_sector_data()` and random demo data. | Use imported sector/industry price history or hide tab. |
| Research | Price Forecast | Mock forecast data and demo warning partial. | Hide unless real forecast provider/data exists. |
| Research | Market Correlations | Timeframe-based randomisation. | Use stored price series correlation or hide tab. |
| Watchlists | User Watchlists | View states that sample data is shown. | Replace with actual watchlist/symbol data. |
| Trading | SEES Demo | Randomised demo view and AJAX-generated mock score/price/change. | Replace with price-only scoring from imported free-provider data or hide. |
| Trading | Trading Strategies | Sample strategies for demonstration purposes. | Extension or hide until real strategy builder is ready. |
| Trading | Create Strategy | Demo-mode branch. | Extension or hide until real strategy workflow exists. |
| Trading Platforms | API Efficiency | Random service availability in template. | Hide pending real metrics. |
| Trading Platforms | Provider template | Demo mode can randomly determine service availability. | Replace with real health checks or remove demo mode. |
| Data | Manage Sources test connection | Returns sample data for custom sources. | Keep only as a labelled developer test or replace with real validation. |
| Development | Current Task / GitHub tasks | Demo task/sample issues fallback. | Acceptable only on developer-only page. |

## System-Level Demo Data

| System | Evidence | Current recommendation |
|---|---|---|
| API adapters for extension providers | Several providers return `get_demo_data()` when demo mode is enabled. | Extension-bound providers should not expose demo data in core. |
| Trading212 shortcode | Frontend can display demo Trading212 data. | Remove user-visible demo mode; show "Requires API Key" instead. |
| API call debug cache | `TRADEPRESS_TESTING` can generate sample API call data. | Keep dev-only and never expose to regular users. |
| Advisor/news/forecast integrations | Demo news and forecast helpers. | Hide Advisor/Focus until live data exists. |
| Scoring directives | Some directives use mock news/volatility/demo calculations. | Classify each directive by live data dependency before release. |
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
