# AI Current Project

Last updated: 2026-05-01

## Current Focus

Release-prep work is focused on storage-first data flow, truthful developer/user UI states, scoring strategy creation, and SEES Diagnostics validation.

The old BugNet/Advisor implementation notes are historical. Do not treat them as the active task list unless the user explicitly resumes Advisor work.

## Required Reading For New Sessions

1. `START.md`
2. `ROADMAP.md`
3. The relevant `AGENT-*.md` file for the assigned owner
4. `roadmap/admin-ui-status-index.md` before changing admin menus, tabs, or visibility
5. `wp-content/plugins/tradepress/docs/data/DATA-ARCHITECTURE.md` before API, import, storage, or data-display changes
6. `wp-content/plugins/tradepress/docs/data/DATA-FRESHNESS-FRAMEWORK.md` before refresh/staleness changes
7. `wp-content/plugins/tradepress/docs/data/DATABASE-META-TABLES-PLAN.md` before storage changes
8. `wp-content/plugins/tradepress/docs/procedures/demo-content-inventory.md` and `wp-content/plugins/tradepress/docs/procedures/demo-data-and-developer-mode.md` before touching demo/mock/random/sample output
9. `wp-content/plugins/tradepress/docs/procedures/STYLES.md` before UI changes

`SPRINT.md` is now a compact archive, not the active task list.

## Active Rules

- Render paths must read stored data first.
- Missing or stale provider data must queue refresh work instead of calling APIs inline.
- Regular users must not see demo/mock/random/sample market data as if it were live/imported.
- Tabs visible only because `WP_DEVELOPMENT_MODE` is enabled should show the red spanner indicator.
- Preserve the existing Trading Strategy builder UI unless a redesign is explicitly requested.
- Keep `roadmap/admin-ui-status-index.md`, the relevant `AGENT-*.md`, and contract docs synchronized when behaviour changes.

## Current Completed Work

- SEES Diagnostics trace contract finalized with canonical score, threshold, branch, process, and step fields.
- SEES Diagnostics UI now prefers `threshold_distance`, labels branch statuses, separates warnings from hard failures, and reports copy-trace feedback.
- Continued-path trace evidence captured for HIMS using strategy `SEES UI Testing`.
- Scoring strategy tables now create on demand.
- Create Scoring Strategies supports Evenly Divide Weights, exact `100%` save validation, automatic weight balancing for new custom directives, and Minimum Score Threshold.
- Manage Scoring Strategies shows the saved minimum score threshold.

## Current Blockers / Next Evidence

1. Capture stopped-path SEES Diagnostics evidence using a high Minimum Score Threshold, for example `90`.
2. Capture warning-path SEES Diagnostics evidence using a missing/inactive/unavailable strategy component.
3. Review/harden scoring strategy and SEES AJAX handlers for nonce, capability, `wp_unslash()`, sanitization, Developer Mode gates, and escaping.
4. Continue PHPCS cleanup in targeted batches and record exact results.
