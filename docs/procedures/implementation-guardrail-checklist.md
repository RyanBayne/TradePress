# Implementation Guardrail Checklist (No-Duplication)

Last updated: 2026-04-29

Use this one-page checklist before coding any provider-status, data-flow, queue, caching, or data-freshness change.

## Goal

Prevent duplicate classes/systems and force changes through existing architecture first.

## 1) Stop Conditions (Do Not Code Yet)

If any item below is true, pause and resolve first:

- You have not checked whether a class/system already exists for the same concern.
- You are about to create a new core class for queueing, freshness, provider selection, or adapters.
- You are about to make external API calls from page render or controller request path.
- You cannot state where the UI reads data from (DB table, option, post meta, transient).

## 2) Existing Systems to Reuse First

Use and extend these before creating anything new:

- Queue execution: `TradePress_Background_Processing`
  - File: `wp-content/plugins/tradepress/includes/class.background-process.php`
- Data import queue process: `TradePress_Data_Import_Process`
  - File: `wp-content/plugins/tradepress/includes/data-import-process.php`
- Freshness gate: `TradePress_Data_Freshness_Manager`
  - File: `wp-content/plugins/tradepress/includes/data-freshness-manager.php`
- Queue storage schema: `TradePress_Queue_Schema`
  - File: `wp-content/plugins/tradepress/includes/queue-schema.php`
- Provider creation/selection: `TradePress_API_Factory`
  - File: `wp-content/plugins/tradepress/api/api-factory.php`
- Provider usage/rate-limit fallback: `TradePress_API_Usage_Tracker`
  - File: `wp-content/plugins/tradepress/includes/api-usage-tracker.php`
- API normalization layers:
  - `TradePress_API_Adapter` (`wp-content/plugins/tradepress/api/api-adapter.php`)
  - `TradePress_API_Data_Adapter` (`wp-content/plugins/tradepress/includes/api-data-adapter.php`)

## 3) Explicit No-Duplication Rules

- Do not add a new queue manager class while `TradePress_Background_Processing`, `TradePress_Data_Import_Process`, and `TradePress_Queue_Schema` are active.
- Do not add a new freshness orchestrator while `TradePress_Data_Freshness_Manager` is active.
- Do not add a third adapter pattern unless a concrete gap is documented against both existing adapter layers.
- Prefer adding methods to existing classes over creating sibling classes with overlapping responsibilities.

## 4) Data-Flow Contract (Must Pass)

For each changed view/path:

1. Request reads stored data first.
2. If stale/missing, queue refresh.
3. No inline external API calls in render path.
4. UI shows state from stored/queue status.

Allowed UI states:

- Live
- Cached
- Queued
- Empty
- Dev-only Demo

Runtime health is separate from data mode. When a feature, provider, calculation, or dependency is anomalous, expose a health badge/state as well:

- healthy
- warning
- failed
- unknown
- not_applicable

Do not present `failed` health as a normal empty state, and do not use demo/random data to hide a failure.

If a failed feature is a scoring directive used by an active scoring or trading strategy, the strategy must auto-pause until the directive recovers, is removed/replaced, or a deliberate safe fallback is configured.

## 5) Minimal Pre-Change Audit (Required)

Complete this before implementation:

1. Search for existing class usage in codebase.
2. Identify current source of truth for the view data.
3. Define freshness SLA and queue trigger threshold.
4. Confirm primary and fallback provider for the view purpose.
5. Confirm demo fallback behavior for regular users (must not appear as real data).

## 6) Allowed vs Not Allowed Changes

Allowed now:

- Add provider-purpose mapping to existing config/logic.
- Add freshness thresholds to existing manager logic.
- Improve UI mode messaging and status indicators.
- Add queue-trigger checks in existing request handlers.

Not allowed now (without architecture review):

- New core queue/freshness frameworks.
- New global adapter framework.
- Parallel provider-selection subsystem separate from API Factory/Usage Tracker.

## 7) Documentation Sync (Same Change Set)

When implementation changes behavior, update in the same session:

- `START.md` (if workflow guidance changes)
- `SPRINT.md` (current task status and notes)
- `ROADMAP.md` (release-prep contract if affected)
- `roadmap/admin-ui-status-index.md` (if tab visibility or data mode changes)

## 8) Quick Sign-Off Block

Do not move to next task until all are true:

- [ ] No duplicated class/system introduced
- [ ] Data-flow contract holds (DB read -> queue if stale -> render from DB)
- [ ] Provider primary/fallback decision documented
- [ ] Freshness SLA + queue trigger documented
- [ ] UI state is explicit (Live/Cached/Queued/Empty/Dev-only Demo)
- [ ] Runtime health is explicit when applicable (healthy/warning/failed/unknown/not_applicable)
- [ ] Strategies using failed directives auto-pause or have an explicit safe fallback
- [ ] Relevant docs synchronized
