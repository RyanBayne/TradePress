# AI Guidance — TradePress

> This file governs how AI should behave when working on TradePress.
> Read START.md first for project context, glossary, and the key files index.
> Check `wp-content/plugins/tradepress/docs/ai/AI-CURRENT-PROJECT.md` for the active focus area and immediate task.

---

## AI Role Assignments

Each AI has a defined focus. Do not overlap unless the current project file directs it.

| AI | Focus 1 | Focus 2 |
|----|---------|---------|
| **Copilot** | Quality & Standards / Testing System | Code implementation |
| **Gemini** | Documentation & Education | Online presence |
| **Amazon Q** | Scoring Directives & Strategy | Dev tooling & GitHub |

---

## Mandatory Rules

### First 10 Minutes
At the start of every AI session:

1. Read `START.md`
2. Read `SPRINT.md`
3. Read `wp-content/plugins/tradepress/docs/ai/AI-CURRENT-PROJECT.md`
4. Check `ROADMAP.md` for the current release direction
5. Check `roadmap/admin-ui-status-index.md` before changing admin menus, tabs, or visibility
6. Check `wp-content/plugins/tradepress/docs/procedures/demo-content-inventory.md` before touching demo/mock/random/sample data
7. Check `git status` before editing; assume existing changes belong to the developer or another AI

Do not reset, flatten, or overwrite unrelated worktree changes.

If the working tree is dirty:
- Scope edits to the files required for the current task only
- Do not revert or reformat unrelated files
- If another AI changed the same file, preserve intent and make the smallest safe patch

### Code Standards
- All PHP must comply with WordPress-Core, WordPress-Docs, WordPress-Extra (PHPCS)
- British English in all documentation and comments
- No `<style>` or `<script>` tags in `.php` files — use enqueued assets only
- CSS files → `assets/css/`, enqueue in `assets/manage-assets.php` (UI Library) or `assets/assets-loader-original.php` (everything else)
- JS files → `assets/js/`, enqueue the same way
- No new CSS/JS files without confirming no existing file covers the need

### Class Naming
- Do not use `class.tradepress-` for new files — this convention is being phased out
- When editing a file that still uses it, rename the class as part of that edit (systematic rollout)

### BugNet Logging (Mandatory)
Add BugNet logging to every new function and form handler. This is not optional.

Helper functions available:
- `tradepress_debug( $data, $label )`
- `tradepress_log_user_action( $action, $context )`
- `tradepress_log_error( $message, $context )`
- `tradepress_debug_timer_start( $label )` / `tradepress_debug_timer_end( $label )`

Log: form submissions, API calls, user navigation, errors, and timing for performance-sensitive paths.

### Planning Rules
Before writing code, AI must:

1. **Search** for existing systems that cover the need — avoid duplicating classes or logic
2. **Justify** any new PHP class as functionally distinct, not just a renamed copy
3. **Deprecate** — when replacing a file/class/system, mark the old one deprecated; do not delete silently
4. **Check structure** — if a file path does not conform to the project structure, flag it
5. **Consider API surface** — new classes should be usable by other WordPress plugins where practical
6. **Add test steps** to every implementation plan for features being changed

Keep plans short and execution-first: implement in small, verifiable batches instead of broad rewrites.

### Procedures
- Check `docs/procedures/` before starting any complex task — a procedure may already exist
- Create a procedure file if the work is repeatable and none exists

---

## Release Prep Rules

### Demo Content
User-visible demo content is being phased out. Do not preserve demo/mock/random/sample data as a substitute for a real feature.

Before changing a demo-backed view:
- Check `wp-content/plugins/tradepress/docs/procedures/demo-content-inventory.md`
- Choose one outcome: imported/live data, clear empty state, Developer Mode only, extension, or removal
- Update `roadmap/admin-ui-status-index.md` if visibility changes

### Twitch Legacy Code
The project still contains inherited TwitchPress/Twitch-era code. Do not delete broad legacy systems without classification.

Migration order:
1. Inventory references
2. Classify each as delete, rename, quarantine, or keep temporarily
3. Remove public-facing leftovers first
4. Patch in small batches with syntax checks and search-count follow-up

### UI Cleanup
UI cleanup is release-priority work. Before styling admin UI:
- Check `ROADMAP.md` → UI/UX Sprint
- Check `wp-content/plugins/tradepress/docs/procedures/STYLES.md`
- Prefer existing theme-library patterns
- Keep each change scoped to one page/tab or one reusable pattern

---

## Data Flow — Critical Constraint

All API calls are queued. Never call an API directly inside a page request or WP hook.

Correct: `Request → check freshness → queue if stale → serve from DB always`
Wrong: `Request → call API → display result`

See `wp-content/plugins/tradepress/docs/data/DATA-ARCHITECTURE.md`.

---

## Testing Protocol

Every implementation must end with testing instructions for the developer.

### Required in every response that changes behaviour:

1. **Navigation path** — exact route: `WP Admin → Page → Tab → Section`
2. **What to test** — specific actions, fields, buttons
3. **Expected outcomes** — what success looks like
4. **Error indicators** — what failure looks like and how to diagnose it
5. **BugNet check** — what log entries to verify

### Validation gates
- Do not assume a test passed.
- Wait for explicit developer confirmation or a note in `wp-content/plugins/tradepress/docs/ai/AI-CURRENT-PROJECT.md` before advancing phases.

### Standard testing block format

```
## Testing Instructions

**Navigation:** WP Admin → [Page] → [Tab]

### Test scenarios
| Action | Expected result |
|--------|----------------|
| [step] | [outcome] |

### BugNet verification
- [ ] Check logs for: [expected entry]
- [ ] No unexpected errors in output

### If it fails
[Specific troubleshooting step]
```

---

## Key Files Reference

> Full index is in `START.md → Key files index`. Quick reference for common tasks:

| Task | File |
|------|------|
| Add a constant | `tradepress.php` |
| Register admin page | `admin/admin.php` |
| Enqueue asset (UI Library) | `assets/manage-assets.php` |
| Enqueue asset (other) | `assets/assets-loader-original.php` |
| Background queue logic | `includes/class-data-import-process.php` |
| Options / settings | `options.php` |
| Current AI task | `wp-content/plugins/tradepress/docs/ai/AI-CURRENT-PROJECT.md` |
| Developer scratch notes | `docs/DEVELOPMENT-NOTES.md` |
| Admin UI status | `roadmap/admin-ui-status-index.md` |
| Demo content inventory | `wp-content/plugins/tradepress/docs/procedures/demo-content-inventory.md` |
| Demo data policy | `wp-content/plugins/tradepress/docs/procedures/demo-data-and-developer-mode.md` |
| UI styling procedure | `wp-content/plugins/tradepress/docs/procedures/STYLES.md` |
