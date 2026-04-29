# Documentation Migration Map

Last updated: 2026-04-29

## Policy

Developer and AI-critical documentation should live in the plugin repository when it directly affects code, architecture, tests, release gates, data contracts, or implementation rules.

User documentation should be copied or rewritten into GitHub Wiki seed pages, then the local source should move under the user-documentation area in `TradePress-Documentation`.

Planning and business documentation can remain in `TradePress-Documentation`.

## First Batch Moved To Plugin Repo

| Original location | New plugin location | Classification | Status |
|---|---|---|---|
| `TradePress-Documentation/docs/systems/SCORING-SYSTEM.md` | `wp-content/plugins/tradepress/docs/systems/SCORING-SYSTEM.md` | Developer / system contract | Moved |
| `TradePress-Documentation/docs/systems/DIRECTIVE-TESTING-STRATEGY.md` | `wp-content/plugins/tradepress/docs/systems/DIRECTIVE-TESTING-STRATEGY.md` | Developer / testing contract | Moved |
| `TradePress-Documentation/docs/procedures/implementation-guardrail-checklist.md` | `wp-content/plugins/tradepress/docs/procedures/implementation-guardrail-checklist.md` | Developer / procedure | Moved |

Short pointer stubs remain at the original locations during migration so existing links continue to guide readers to the canonical files.

## Candidate Next Batch

| Source | Suggested destination | Notes |
|---|---|---|
| `TradePress-Documentation/docs/DATA-ARCHITECTURE.md` | `docs/architecture/DATA-ARCHITECTURE.md` or `docs/data/DATA-ARCHITECTURE.md` | High priority; code-critical data-flow contract |
| `TradePress-Documentation/docs/DATA-FRESHNESS-FRAMEWORK.md` | `docs/data/DATA-FRESHNESS-FRAMEWORK.md` | High priority; freshness and queue decisions |
| `TradePress-Documentation/docs/DATABASE-META-TABLES-PLAN.md` | `docs/data/DATABASE-META-TABLES-PLAN.md` | Storage planning and schema decisions |
| `TradePress-Documentation/docs/AI/AI-GUIDANCE.md` | `docs/ai/AI-GUIDANCE.md` | AI collaborator rules |
| `TradePress-Documentation/docs/AI/AI-CURRENT-PROJECT.md` | `docs/ai/AI-CURRENT-PROJECT.md` | Current AI focus; may remain external if very session-oriented |
| `TradePress-Documentation/docs/TESTING-SYSTEM.md` | `docs/testing/TESTING-SYSTEM.md` | Testing/BugNet reference |

## Link Migration Rule

When a document is moved:

1. Move the canonical content into `wp-content/plugins/tradepress/docs/`.
2. Leave a short pointer stub at the old location.
3. Update `START.md`, `SPRINT.md`, and high-traffic roadmap references.
4. Defer broad link cleanup to small batches to avoid noisy documentation churn.
