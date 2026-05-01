# Research Data Flow Contract

Last updated: 2026-05-01

This document records Agent Sprint 1 decisions for release-facing Research views. It is the hand-off contract for view implementation and release verification.

## Contract

Every release-facing Research view must follow this path:

`Request -> read stored data -> queue refresh if stale or missing -> render state from storage`

No Research view may call an external provider directly while rendering a tab. Provider calls belong in `TradePress_Data_Import_Process` and the queue tables managed by `TradePress_Queue_Schema`.

Allowed data-mode labels:

- `Live` - stored data was imported recently enough for the view purpose.
- `Cached` - stored data exists and is usable, but is older than the ideal freshness target.
- `Queued` - stored data is missing or stale and an update request is queued or running.
- `Empty` - no stored data is available and no queued update can be started from current configuration.
- `Dev-only Demo` - developer-only fixture/demo output that must never appear to regular users as market data.

Runtime health is separate from data mode and should use `healthy`, `warning`, `failed`, `unknown`, or `not_applicable`.

## Provider Suitability Matrix

| View | Data purpose | Primary provider | Fallback provider | Unsuitable providers | Freshness SLA | Queue trigger threshold | Current storage source | UI data modes | Request-path API validation |
|---|---|---|---|---|---|---|---|---|---|
| Research > News Feed | In-session research and sentiment context. | Alpaca News for market-news coverage when enabled. | Alpha Vantage `NEWS_SENTIMENT` for symbol and topic sentiment when enabled. | Trading212 for news; broker-only or social providers until a normalised news import exists. | 60 minutes during active research. | Queue when no stored news exists, when last import is older than 30 minutes, or when provider configuration changes. Duplicate queue items are blocked by `TradePress_Queue_Schema::has_active_item()`. | WordPress option `tradepress_news_data`; import timestamp `tradepress_news_last_imported`. | Live/Cached when stored records match filter; Empty when records are missing and provider setup is incomplete; Queued when import queue status is pending/running; Dev-only Demo remains disabled for regular users. | `admin/page/research/view/news-feed.php::get_live_feed_items()` reads stored option data only and contains no provider/API call. |
| Research > Earnings Calendar | Upcoming earnings research context. | Alpha Vantage `EARNINGS_CALENDAR`. | None in free core for alpha; fallback remains undecided until another earnings-capable provider is approved. | Trading212 and Alpaca for earnings calendar data. | 24 hours. | Queue when no stored earnings exist, when last import is older than 12 hours, or when the scheduled earnings import is enabled but missed. Duplicate queue items are blocked by `TradePress_Queue_Schema::has_active_item()`. | WordPress options `tradepress_earnings_data` and fallback `tradepress_earnings_calendar_data`. | Live/Cached when stored records match filter; Empty when records are missing; Queued when import queue status is pending/running; Dev-only Demo remains disabled for regular users. | `admin/page/research/view/earnings.php::tradepress_fetch_earnings_calendar_data()` reads options only and contains no provider/API call. |
| Research > Economic Calendar | Macroeconomic event research context (interest rate decisions, GDP releases, employment reports, central bank meetings). | Financial Modeling Prep (FMP) `economic_calendar` endpoint. | None approved for free core at alpha; TradingView widget is a candidate secondary but not yet wired. | Alpha Vantage (economic indicators endpoint has different data shape and does not map cleanly to calendar events); Alpaca (no macro calendar endpoint); Trading212 (no macro calendar). | 24 hours. | Queue when no stored data exists or last import (`tradepress_economic_calendar_last_imported`) is older than 12 hours and FMP is configured and enabled. Duplicate queue items are blocked by `TradePress_Queue_Schema::has_active_item()`. | WordPress options `tradepress_economic_calendar_data`, `tradepress_economic_calendar_last_imported`, `tradepress_economic_calendar_data_source`. | `queue_pending` (highest priority empty state when a queue item is active); `no-provider` when FMP key missing; `provider-disabled` when FMP disabled; `no-import` when provider ready but no import completed; Live/Cached when stored records exist. Dev-only Demo disabled for regular users. | `admin/page/research/view/economic-calendar.php::tradepress_fetch_economic_calendar_data()` reads stored option only; `tradepress_economic_calendar_maybe_queue_refresh()` calls `queue_data_fetch()` only, never a direct provider/API call. |
| Research > Social Networks | Social integration configuration and future signal-source setup. | None for the current render path. | None approved until a queue-backed social import exists. | Discord, Twitter, and StockTwits live APIs during page render; broker/data-provider APIs. | Not applicable for the current configuration state. | None from the view. Future connection tests/imports must use explicit actions or queue-backed handlers, not render-time diagnostics. | Saved options such as `TradePress_switch_*_social_services`, `TradePress_social_*_apikey`, and Discord option keys. | Dev-only Demo configuration state; no live social records. | `admin/page/research/research-tabs.php::load_social_networks_tab()` and the included settings/switch views read options only after the Discord diagnostic call was removed from render. |

## View State Map

| State | News Feed | Earnings Calendar | Economic Calendar | Social Networks |
|---|---|---|---|---|
| Missing provider key | Show `Empty` with no-provider guidance for Alpha Vantage or Alpaca. Do not show demo records. | Show `Empty` with Alpha Vantage key guidance. Do not show mock records. | Show `no-provider` empty state with FMP key guidance. | Show saved configuration state only; do not call social APIs. |
| Provider key present but service disabled | Show `Empty` with provider-disabled guidance. | Show `Empty` with provider-disabled guidance once the panel distinguishes disabled service from missing key. | Show `provider-disabled` empty state. | Show saved switch state only; do not call social APIs. |
| No stored records | Show `Queued` when Alpaca or Alpha Vantage is enabled/configured and `fetch_news` is queued; otherwise show `Empty` with configuration guidance. | Show `Queued` when Alpha Vantage is enabled/configured and `fetch_earnings` is queued; otherwise show `Empty` with configuration guidance. | Show `queue_pending` when FMP ready and `fetch_economic_calendar` is queued; otherwise show `no-import` empty state. | Not applicable until a queue-backed social import exists. |
| Stored records fresh | Show `Live` after news storage exists. | Show `Live`. | Show `Live`. | Not applicable. |
| Stored records older than ideal target but within SLA | Show `Cached`. | Show `Cached`. | Show `Cached`. | Not applicable. |
| Stored records stale beyond SLA | Show `Queued` if a refresh item is pending/running; otherwise show `Cached` with warning until queue-trigger wiring is complete. | Show `Queued` if a refresh item is pending/running; otherwise show `Cached` with warning until queue-trigger wiring is complete. | Show `queue_pending` if `fetch_economic_calendar` is queued/running; otherwise show `Cached` with warning. | Not applicable. |
| Provider/import failure | Show data mode from storage and runtime health `failed`; do not substitute demo data. | Show data mode from storage and runtime health `failed`; do not substitute mock data. | Show data mode from storage and runtime health `failed`; do not substitute demo data. | Show saved configuration state and require explicit/queued diagnostics later. |

## Config-Path Contract (Social Networks tab)

The Research > Social Networks tab is a configuration settings surface, not a data-display view. It renders stored API key/token options for Twitter and StockTwits and saves settings to WordPress options via nonce-verified POST forms.

- **Data purpose:** Social platform API credential storage and service enable/disable.
- **Source of truth:** WordPress options (`TradePress_social_twitter_apikey`, `TradePress_switch_twitter_social_services`, etc.).
- **Provider call in render path:** None. The tab reads stored credentials only.
- **Queue trigger:** None required; no import data is fetched from this view.
- **Freshness SLA:** Not applicable; these are configuration settings, not imported data.
- **UI data mode:** Dev-only Demo until a real social import/source contract exists.
- **Release direction:** Dev-only for now; return to normal users only after a queue-backed social import/source contract exists.

## Open Implementation Gaps

1. News Feed currently uses option-backed alpha storage; migrate to a dedicated news table during the schema pass.
2. Provider status panels should expose the standard data mode label separately from provider health across all Research views.
3. Economic Calendar FMP fallback provider (TradingView widget or alternative) remains undecided for alpha; document chosen fallback before beta.

## Validation Notes

Validated by source inspection on 2026-04-30:

- `admin/page/research/view/news-feed.php` does not call provider classes or `wp_remote_*` in the tab render path.
- `admin/page/research/view/earnings.php` reads stored options for earnings data and does not call provider classes or `wp_remote_*` in the tab render path.
- `includes/data-import-process.php` already contains the queued `fetch_earnings` action and stores normalised records in `tradepress_earnings_data`.
- `admin/page/research/view/earnings.php` queues `fetch_earnings` through `TradePress_Data_Import_Process::queue_data_fetch()` only after reading stored data and checking duplicate queue state.
- `admin/page/research/view/news-feed.php` queues `fetch_news` through `TradePress_Data_Import_Process::queue_data_fetch()` only after reading stored data and checking duplicate queue state.
- `includes/data-import-process.php` contains the queued `fetch_news` action and stores normalised records in `tradepress_news_data`.
- `TradePress_Data_Import_Process::queue_data_fetch()` schedules `tradepress_process_data_import_queue` so database-backed queue rows are processed by WP-Cron instead of relying on page-request API calls.

Validated by source inspection on 2026-05-01 (Economic Calendar wired end-to-end):

- `admin/page/research/view/economic-calendar.php::tradepress_fetch_economic_calendar_data()` reads `get_option('tradepress_economic_calendar_data')` only; no provider class or `wp_remote_*` call exists in the render path.
- `tradepress_economic_calendar_maybe_queue_refresh()` calls `TradePress_Data_Import_Process::queue_data_fetch('fetch_economic_calendar')` only after reading stored timestamp and checking `TradePress_Queue_Schema::has_active_item('data_import', 'fetch_economic_calendar')`.
- `includes/data-import-process.php` contains the queued `fetch_economic_calendar` case; `fetch_economic_calendar_data()` uses `TradePress_API_Factory` and stores normalised records in `tradepress_economic_calendar_data`, `tradepress_economic_calendar_last_imported`, and `tradepress_economic_calendar_data_source`.
- `api/fmp/fmp-api.php::get_economic_calendar($from, $to)` is called only from within the background queue processor, never from a page-render path.
- PHP lint passed for all three modified files (economic-calendar.php, data-import-process.php, fmp-api.php).
- `admin/page/research/view/social-networks/settings/discord-settings.php` no longer calls Discord diagnostics during render; it reports saved option state only.
