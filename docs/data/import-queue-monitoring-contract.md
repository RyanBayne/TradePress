# Import Queue Monitoring Data-Flow Contract

Status: Sprint 1 contract slice, 2026-05-01

This document records the release-facing contract for Data and Automation views that monitor the background import queue.

## Contract

Request -> read stored queue/options -> queue refresh on explicit admin action or stale target view -> render state from storage.

Automation monitoring pages must not call external APIs during rendering. They may enqueue work through `TradePress_Data_Import_Process::queue_data_fetch()`, which writes to the `tradepress_queue` table and schedules `tradepress_process_data_import_queue`.

## Data-Flow Decisions

| View | Data purpose | Source of truth | Primary provider | Fallback provider | Unsuitable providers | Freshness SLA | Queue trigger threshold | UI data mode |
|---|---|---|---|---|---|---|---|---|
| Automation > Data Import | Queue observability and manual queued refresh | `tradepress_queue` rows plus `tradepress_data_import_*` options | Depends on queued item type: News uses Alpaca, Earnings uses Alpha Vantage, Economic Calendar uses FMP, market status uses Alpha Vantage, prices use configured market-data provider path | News falls back to Alpha Vantage when Alpaca is unavailable; other item types use their existing queue handlers | Direct page-render provider calls are unsuitable | Monitoring state: current request; imported data: inherited per item type | Explicit admin action queues missing non-active items immediately; target views keep their own stale-data triggers | Queued, Cached, Empty |
| Data > Sources | Data source configuration and explicit connection testing | `tradepress_research_sources`; explicit AJAX test response for source-test button | Not provider-selecting during render | Not applicable | Automatic provider/API calls during render are unsuitable; explicit admin source tests may call the submitted URL only from AJAX | Configuration state only | None for page render; source test is a user-triggered admin AJAX diagnostic, not an import queue | Config/Live |
| Data > API Activity | API-call audit trail | `tradepress_calls` logging table | Not provider-selecting; reads recorded API log rows | Not applicable | Any provider call from this view is unsuitable | Audit/reference context: stored logs only | None; this view must not queue provider data | Cached or Empty |

## Queue Status Rules

- `Queued`: one or more `data_import` queue rows are pending or processing.
- `Cached`: no active queue rows and the last import run completed.
- `Empty`: no active queue rows and no completed import evidence is stored.
- Runtime health is separate from data mode:
  - `healthy`: queue rows are pending/processing or the last run completed.
  - `warning`: failed queue rows exist.
  - `unknown`: no queue or import evidence exists.

## Validation Notes

- `admin/page/automation/automation-tabs.php` reads queue summaries through `TradePress_Queue_Schema::get_queue_summary()`.
- `admin/page/automation/automation-controller.php` queues manual data imports through `TradePress_Data_Import_Process::queue_data_fetch()`.
- `includes/data-import-process.php` processes database-backed queue rows from WP-Cron via `tradepress_process_data_import_queue`.
- `admin/page/data/view/manage-sources.php` may call remote URLs only from the explicit `tradepress_test_source` admin AJAX action; the Sources page render path reads/stores source configuration only.
- No external API provider is called by the Automation > Data Import render path.

Updated 2026-05-02: `fetch_economic_calendar` added to the queue action map in `automation-controller.php`; the `economic_calendar` import type is now selectable in the Data Import tab UI and is included in the `all` import group. The Data Freshness section now shows Economic Calendar (`tradepress_economic_calendar_last_imported`) and News Feed (`tradepress_news_last_imported`) freshness rows alongside Earnings Calendar and Market Status. PHP lint passed for automation-tabs.php and automation-controller.php.
