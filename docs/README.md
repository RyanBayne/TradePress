# TradePress Developer Documentation

This folder contains developer-facing documentation that should live with the plugin code.

Repository docs are the source of truth for implementation decisions, testing rules, data contracts, architecture, and AI collaborator guidance. User-facing documentation should be drafted separately for the GitHub Wiki.

## Current Sections

| Folder | Purpose |
|---|---|
| `systems/` | Core system architecture and behaviour contracts |
| `procedures/` | Development procedures, guardrails, and checklists |

## Important Files

| File | Use |
|---|---|
| `systems/SCORING-SYSTEM.md` | Scoring architecture, raw score semantics, strategy maximums, and percentage display rules |
| `systems/DIRECTIVE-TESTING-STRATEGY.md` | Directive testing strategy, score-contract testing, fixture strategy, and sanity runners |
| `procedures/implementation-guardrail-checklist.md` | Required checklist before queue, provider, data-flow, freshness, or health-state changes |
| `MIGRATION-MAP.md` | Tracks which documentation has moved into the plugin repo and what remains in project docs |

## Packaging

Developer docs may ship with the open-source plugin if they are accurate, non-secret, and useful to contributors. Before public packaging, audit this folder for secrets, obsolete instructions, unresolved private security findings, and private commercial planning.
