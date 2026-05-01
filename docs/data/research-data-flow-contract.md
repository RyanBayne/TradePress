# Research Data Flow Contract

Last updated: 2026-04-30

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

## View State Map

| State | News Feed | Earnings Calendar |
|---|---|---|
| Missing provider key | Show `Empty` with no-provider guidance for Alpha Vantage or Alpaca. Do not show demo records. | Show `Empty` with Alpha Vantage key guidance. Do not show mock records. |
| Provider key present but service disabled | Show `Empty` with provider-disabled guidance. | Show `Empty` with provider-disabled guidance once the panel distinguishes disabled service from missing key. |
| No stored records | Show `Queued` when Alpaca or Alpha Vantage is enabled/configured and `fetch_news` is queued; otherwise show `Empty` with configuration guidance. | Show `Queued` when Alpha Vantage is enabled/configured and `fetch_earnings` is queued; otherwise show `Empty` with configuration guidance. |
| Stored records fresh | Show `Live` after news storage exists. | Show `Live`. |
| Stored records older than ideal target but within SLA | Show `Cached`. | Show `Cached`. |
| Stored records stale beyond SLA | Show `Queued` if a refresh item is pending/running; otherwise show `Cached` with warning until queue-trigger wiring is complete. | Show `Queued` if a refresh item is pending/running; otherwise show `Cached` with warning until queue-trigger wiring is complete. |
| Provider/import failure | Show data mode from storage and runtime health `failed`; do not substitute demo data. | Show data mode from storage and runtime health `failed`; do not substitute mock data. |

## Open Implementation Gaps

1. News Feed currently uses option-backed alpha storage; migrate to a dedicated news table during the schema pass.
2. Provider status panels should expose the standard data mode label separately from provider health across all Research views.

## Validation Notes

Validated by source inspection on 2026-04-30:

- `admin/page/research/view/news-feed.php` does not call provider classes or `wp_remote_*` in the tab render path.
- `admin/page/research/view/earnings.php` reads stored options for earnings data and does not call provider classes or `wp_remote_*` in the tab render path.
- `includes/data-import-process.php` already contains the queued `fetch_earnings` action and stores normalised records in `tradepress_earnings_data`.
- `admin/page/research/view/earnings.php` queues `fetch_earnings` through `TradePress_Data_Import_Process::queue_data_fetch()` only after reading stored data and checking duplicate queue state.
- `admin/page/research/view/news-feed.php` queues `fetch_news` through `TradePress_Data_Import_Process::queue_data_fetch()` only after reading stored data and checking duplicate queue state.
- `includes/data-import-process.php` contains the queued `fetch_news` action and stores normalised records in `tradepress_news_data`.
- `TradePress_Data_Import_Process::queue_data_fetch()` schedules `tradepress_process_data_import_queue` so database-backed queue rows are processed by WP-Cron instead of relying on page-request API calls.
