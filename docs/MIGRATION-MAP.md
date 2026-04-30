# Documentation Migration Map

Last updated: 2026-04-29

## Policy

Developer and AI-critical documentation should live in the plugin repository when it directly affects code, architecture, tests, release gates, data contracts, or implementation rules.

User documentation should be copied or rewritten into GitHub Wiki seed pages, then the local source should move under the user-documentation area in `TradePress-Documentation`.

Planning and business documentation can remain in `TradePress-Documentation`.

## Moved To Plugin Repo

| Original location | New plugin location | Classification | Status |
|---|---|---|---|
| `TradePress-Documentation/docs/systems/SCORING-SYSTEM.md` | `wp-content/plugins/tradepress/docs/systems/SCORING-SYSTEM.md` | Developer / system contract | Moved |
| `TradePress-Documentation/docs/systems/DIRECTIVE-TESTING-STRATEGY.md` | `wp-content/plugins/tradepress/docs/systems/DIRECTIVE-TESTING-STRATEGY.md` | Developer / testing contract | Moved |
| `TradePress-Documentation/docs/procedures/implementation-guardrail-checklist.md` | `wp-content/plugins/tradepress/docs/procedures/implementation-guardrail-checklist.md` | Developer / procedure | Moved |
| `TradePress-Documentation/docs/DATA-ARCHITECTURE.md` | `wp-content/plugins/tradepress/docs/data/DATA-ARCHITECTURE.md` | Developer / data contract | Moved |
| `TradePress-Documentation/docs/DATA-FRESHNESS-FRAMEWORK.md` | `wp-content/plugins/tradepress/docs/data/DATA-FRESHNESS-FRAMEWORK.md` | Developer / freshness contract | Moved |
| `TradePress-Documentation/docs/DATABASE-META-TABLES-PLAN.md` | `wp-content/plugins/tradepress/docs/data/DATABASE-META-TABLES-PLAN.md` | Developer / storage planning | Moved |
| `TradePress-Documentation/docs/AI/AI-GUIDANCE.md` | `wp-content/plugins/tradepress/docs/ai/AI-GUIDANCE.md` | Developer / AI collaboration | Moved |
| `TradePress-Documentation/docs/AI/AI-CURRENT-PROJECT.md` | `wp-content/plugins/tradepress/docs/ai/AI-CURRENT-PROJECT.md` | Developer / AI coordination | Moved |
| `TradePress-Documentation/docs/TESTING-SYSTEM.md` | `wp-content/plugins/tradepress/docs/testing/TESTING-SYSTEM.md` | Developer / testing plan | Moved |
| `TradePress-Documentation/docs/POST-HANDLER.md` | `wp-content/plugins/tradepress/docs/procedures/POST-HANDLER.md` | Developer / secure admin POST procedure | Moved |
| `TradePress-Documentation/docs/procedures/STYLES.md` | `wp-content/plugins/tradepress/docs/procedures/STYLES.md` | Developer / UI cleanup procedure | Moved |
| `TradePress-Documentation/docs/procedures/demo-content-inventory.md` | `wp-content/plugins/tradepress/docs/procedures/demo-content-inventory.md` | Developer / release readiness inventory | Moved |
| `TradePress-Documentation/docs/procedures/demo-data-and-developer-mode.md` | `wp-content/plugins/tradepress/docs/procedures/demo-data-and-developer-mode.md` | Developer / demo visibility policy | Moved |
| `TradePress-Documentation/docs/TRADING212-API-TESTING.md` | `wp-content/plugins/tradepress/docs/testing/TRADING212-API-TESTING.md` | Developer / provider testing | Moved |
| `TradePress-Documentation/docs/diagrams/api-data-standardization-flow.md` | `wp-content/plugins/tradepress/docs/diagrams/api-data-standardization-flow.md` | Developer / architecture diagram | Moved |
| `TradePress-Documentation/docs/diagrams/directive-testing-procedure.md` | `wp-content/plugins/tradepress/docs/diagrams/directive-testing-procedure.md` | Developer / testing diagram | Moved |
| `TradePress-Documentation/docs/DATA-ELEMENTS-SPECIFICATION.md` | `wp-content/plugins/tradepress/docs/data/DATA-ELEMENTS-SPECIFICATION.md` | Developer / data contract | Moved |
| `TradePress-Documentation/docs/features/data_freshness_manager.md` | `wp-content/plugins/tradepress/docs/data/data-freshness-manager.md` | Developer / data freshness implementation | Moved |
| `TradePress-Documentation/docs/features/recent_call_register.md` | `wp-content/plugins/tradepress/docs/data/recent-call-register.md` | Developer / API cache implementation | Moved |
| `TradePress-Documentation/docs/procedures/VERSION-CHANGES.md` | `wp-content/plugins/tradepress/docs/release/VERSION-CHANGES.md` | Developer / release procedure | Moved |

Short pointer stubs remain at the original locations during migration so existing links continue to guide readers to the canonical files.

Migration note: during the data-doc batch, Google Drive denied overwrite/rename access for two old source files after the plugin copies were created. During the AI/testing batch, Google Drive or another process also locked the old AI/testing files. During the release-procedure batch, old `POST-HANDLER.md` and `STYLES.md` were locked; the demo inventory and demo policy old paths were converted to pointer stubs. During the testing/provider batch, old `TRADING212-API-TESTING.md` was locked; the old diagram paths were converted to pointer stubs. Treat the plugin paths above as canonical even if an old source file temporarily remains readable in `TradePress-Documentation/docs/`.

## Audited Remaining Documentation

The remaining documentation should be handled by classification, not by moving files blindly.

### Recommended Developer-Doc Move Queue

These files directly affect implementation, architecture, testing, release gates, or AI/code behaviour. Move them into the plugin repo in small batches.

| Source | Suggested destination | Notes |
|---|---|---|
### Keep In Project Management Docs

These are roadmap, sprint, strategy, or progress-planning documents. They can reference plugin docs, but should not become canonical plugin implementation docs unless rewritten.

| Source | Classification | Reason / Action |
|---|---|---|
| `TradePress-Documentation/docs/ADVISOR-INTEGRATION-STRATEGY.md` | Planning / strategy | Keep external; it defines product direction and development philosophy. |
| `TradePress-Documentation/docs/FOCUS-PAGES-ADVISOR-ROADMAP.md` | Planning / roadmap | Keep external until an implementation spec is extracted. |
| `TradePress-Documentation/docs/FEATURE-FEEDBACK-ROADMAP.md` | Planning / roadmap with implementation rules | Keep external for now; extract runtime health contract into plugin docs when implementation begins. |
| `TradePress-Documentation/docs/POSITION-MONITORING-SYSTEM.md` | Future system design | Keep external until position monitoring enters active implementation. |
| `TradePress-Documentation/docs/TRADING212-IMPLEMENTATION-PROGRESS.md` | Progress tracking | Keep external; replace with GitHub issues or sprint notes over time. |
| `TradePress-Documentation/docs/DIRECTIVE-ANALYSIS.md` | Historical directive planning | Keep external or archive; current directive contract lives in plugin docs and sanity tests. |
| `TradePress-Documentation/docs/DIRECTIVE-STATUS-UPDATE.md` | Historical directive status | Keep external or archive; current status should come from tests/roadmap issues. |
| `TradePress-Documentation/docs/systems/MACHINE-LEARNING.md` | Future roadmap / research | Keep external until ML work becomes active. |
| `TradePress-Documentation/docs/systems/ASYNCHRONOUS-PROCESSING.md` | Empty placeholder | Do not move. Archive or delete after confirming no content is needed. Queue/background-processing guidance should be rebuilt from current code and `DATA-ARCHITECTURE.md`. |
| `TradePress-Documentation/docs/systems/ALGORITHM-EXECUTION.md` | Stale future planning | Do not move as-is. It lists mostly unimplemented automation ideas and does not reflect current strategy health/auto-pause rules. Rewrite from current automation/trading code if needed. |
| `TradePress-Documentation/docs/systems/TECHNICAL-ANALYSIS.md` | Future methodology planning | Do not move as-is. It contains broad unimplemented analysis ideas. Current technical indicator implementation is covered by `SCORING-SYSTEM.md`, `DIRECTIVE-TESTING-STRATEGY.md`, and sanity tests. |
| `TradePress-Documentation/docs/systems/TEMPLATE-SYSTEM.md` | Stale frontend/template planning | Do not move as-is. It is useful as historical context, but current UI/template guidance should be derived from active theme/template files and `STYLES.md`. |
| `TradePress-Documentation/docs/strategies/*.md` | Strategy research / product planning | Keep external; later extract user-friendly pages to Wiki and executable contracts to plugin docs. |
| `TradePress-Documentation/docs/projects/*.md` | Project implementation notes | Keep external unless a stable developer procedure should be extracted. |
| `TradePress-Documentation/docs/procedures/END-SESSION.md` | Project workflow | Keep external; this is a collaboration/session procedure, not plugin implementation. |
| `TradePress-Documentation/docs/procedures/github-wiki-plan.md` | Documentation migration planning | Keep external until Wiki migration is complete. |
| `TradePress-Documentation/docs/procedures/MOVING-INLINE-STYLES.md` | Empty placeholder | Archive or delete after confirming no useful content is expected. |

### Wiki Seed / User Documentation Queue

These should be rewritten or copied into GitHub Wiki seed pages rather than shipped as developer docs.

| Source | Suggested destination | Notes |
|---|---|---|
| `TradePress-Documentation/docs/education/ELEARNING-CONTENT-PLAN.md` | GitHub Wiki / Education | User-facing education plan. |
| `TradePress-Documentation/docs/education/EDUCATION-SYSTEM-NOTES.md` | GitHub Wiki / Education or planning docs | Mixed user education and system planning; split before publishing. |
| `TradePress-Documentation/docs/education/SUPPORT-RESISTANCE-ANALYSIS.md` | GitHub Wiki / Trading Concepts | Likely useful as user education after editing for clarity. |
| `TradePress-Documentation/docs/features/market_overview.md` | GitHub Wiki / Feature explanations | User/admin feature explanation, unless converted into implementation spec. |
| `TradePress-Documentation/docs/systems/user/USER-PROFILES.md` | GitHub Wiki / Admin guides or planning docs | User-facing profile concept; review before publishing. |
| `TradePress-Documentation/docs/wiki-seed/*.md` | GitHub Wiki | Already staged as Wiki seed pages. Publish once GitHub Wiki is enabled. |

### Existing Canonical Stubs / Locked Old Sources

These old paths have already been migrated or should be treated as non-canonical.

| Source | Canonical location | Status |
|---|---|---|
| `TradePress-Documentation/docs/DATA-ARCHITECTURE.md` | `wp-content/plugins/tradepress/docs/data/DATA-ARCHITECTURE.md` | Old Google Drive file locked during stub replacement. |
| `TradePress-Documentation/docs/DATA-FRESHNESS-FRAMEWORK.md` | `wp-content/plugins/tradepress/docs/data/DATA-FRESHNESS-FRAMEWORK.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/DATABASE-META-TABLES-PLAN.md` | `wp-content/plugins/tradepress/docs/data/DATABASE-META-TABLES-PLAN.md` | Old Google Drive file locked during stub replacement. |
| `TradePress-Documentation/docs/AI/AI-GUIDANCE.md` | `wp-content/plugins/tradepress/docs/ai/AI-GUIDANCE.md` | Old file locked during stub replacement. |
| `TradePress-Documentation/docs/AI/AI-CURRENT-PROJECT.md` | `wp-content/plugins/tradepress/docs/ai/AI-CURRENT-PROJECT.md` | Old file locked during stub replacement. |
| `TradePress-Documentation/docs/TESTING-SYSTEM.md` | `wp-content/plugins/tradepress/docs/testing/TESTING-SYSTEM.md` | Old file locked during stub replacement. |
| `TradePress-Documentation/docs/POST-HANDLER.md` | `wp-content/plugins/tradepress/docs/procedures/POST-HANDLER.md` | Old file locked during stub replacement. |
| `TradePress-Documentation/docs/procedures/STYLES.md` | `wp-content/plugins/tradepress/docs/procedures/STYLES.md` | Old file locked during stub replacement. |
| `TradePress-Documentation/docs/procedures/demo-content-inventory.md` | `wp-content/plugins/tradepress/docs/procedures/demo-content-inventory.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/procedures/demo-data-and-developer-mode.md` | `wp-content/plugins/tradepress/docs/procedures/demo-data-and-developer-mode.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/TRADING212-API-TESTING.md` | `wp-content/plugins/tradepress/docs/testing/TRADING212-API-TESTING.md` | Old file locked during stub replacement. |
| `TradePress-Documentation/docs/diagrams/api-data-standardization-flow.md` | `wp-content/plugins/tradepress/docs/diagrams/api-data-standardization-flow.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/diagrams/directive-testing-procedure.md` | `wp-content/plugins/tradepress/docs/diagrams/directive-testing-procedure.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/DATA-ELEMENTS-SPECIFICATION.md` | `wp-content/plugins/tradepress/docs/data/DATA-ELEMENTS-SPECIFICATION.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/features/data_freshness_manager.md` | `wp-content/plugins/tradepress/docs/data/data-freshness-manager.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/features/recent_call_register.md` | `wp-content/plugins/tradepress/docs/data/recent-call-register.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/procedures/VERSION-CHANGES.md` | `wp-content/plugins/tradepress/docs/release/VERSION-CHANGES.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/systems/SCORING-SYSTEM.md` | `wp-content/plugins/tradepress/docs/systems/SCORING-SYSTEM.md` | Old path is a pointer stub. |
| `TradePress-Documentation/docs/systems/DIRECTIVE-TESTING-STRATEGY.md` | `wp-content/plugins/tradepress/docs/systems/DIRECTIVE-TESTING-STRATEGY.md` | Old path is a pointer stub. |

## Recommended Next Batches

1. **Wiki seed expansion**
   Convert education and user-facing feature docs into `docs/wiki-seed/` pages, then publish to the GitHub Wiki once enabled.

## Link Migration Rule

When a document is moved:

1. Move the canonical content into `wp-content/plugins/tradepress/docs/`.
2. Leave a short pointer stub at the old location.
3. Update `START.md`, `SPRINT.md`, and high-traffic roadmap references.
4. Defer broad link cleanup to small batches to avoid noisy documentation churn.
