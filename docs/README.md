# TradePress Developer Documentation

This folder contains developer-facing documentation that should live with the plugin code.

Repository docs are the source of truth for implementation decisions, testing rules, data contracts, architecture, and AI collaborator guidance. User-facing documentation should be drafted separately for the GitHub Wiki.

## Current Sections

| Folder | Purpose |
|---|---|
| `ai/` | AI collaborator rules and current project context used by future coding sessions |
| `data/` | Data architecture, freshness, queueing, storage, and schema planning |
| `diagrams/` | Developer diagrams for data flow, standardisation, and testing architecture |
| `testing/` | Testing system, sanity-suite, diagnostics, and test framework notes |
| `release/` | Release, versioning, packaging, and publication procedures |
| `systems/` | Core system architecture and behaviour contracts |
| `procedures/` | Development procedures, guardrails, and checklists |

## Important Files

| File | Use |
|---|---|
| `ai/AI-GUIDANCE.md` | AI collaborator rules, first-10-minutes checklist, and worktree safety expectations |
| `ai/AI-CURRENT-PROJECT.md` | Current AI focus area and coordination notes |
| `data/DATA-ARCHITECTURE.md` | Canonical data-flow model for API, import, queue, storage, freshness, and display work |
| `data/DATA-FRESHNESS-FRAMEWORK.md` | Freshness/staleness rules for queueing and data-readiness decisions |
| `data/DATABASE-META-TABLES-PLAN.md` | Database and meta-table planning before storage changes |
| `data/DATA-ELEMENTS-SPECIFICATION.md` | Data Elements admin/system specification and data group metadata model |
| `data/data-freshness-manager.md` | Data Freshness Manager implementation notes and Recent Call Register integration |
| `data/recent-call-register.md` | Transient-based API call cache and duplicate-call prevention notes |
| `testing/TESTING-SYSTEM.md` | BugNet/testing framework plan and diagnostics reference |
| `testing/TRADING212-API-TESTING.md` | Trading212 API testing plan for demo/live provider validation |
| `diagrams/api-data-standardization-flow.md` | API data adapter and normalisation flow diagram |
| `diagrams/directive-testing-procedure.md` | Directive testing procedure and standard architecture diagram |
| `release/VERSION-CHANGES.md` | Version update and changelog procedure for release work |
| `systems/SCORING-SYSTEM.md` | Scoring architecture, raw score semantics, strategy maximums, and percentage display rules |
| `systems/DIRECTIVE-TESTING-STRATEGY.md` | Directive testing strategy, score-contract testing, fixture strategy, and sanity runners |
| `procedures/implementation-guardrail-checklist.md` | Required checklist before queue, provider, data-flow, freshness, or health-state changes |
| `procedures/POST-HANDLER.md` | Secure admin POST handler pattern and helper API |
| `procedures/STYLES.md` | UI cleanup procedure for moving inline styles and using plugin assets |
| `procedures/demo-content-inventory.md` | Source-code-backed inventory of demo/mock/random/sample data surfaces |
| `procedures/demo-data-and-developer-mode.md` | Policy for hiding demo-only and unfinished views while Developer Mode remains available |
| `MIGRATION-MAP.md` | Tracks which documentation has moved into the plugin repo and what remains in project docs |

## Packaging

Developer docs may ship with the open-source plugin if they are accurate, non-secret, and useful to contributors. Before public packaging, audit this folder for secrets, obsolete instructions, unresolved private security findings, and private commercial planning.
