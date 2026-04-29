# Demo Data and Developer Mode Policy

TradePress may keep demo, mock, or randomly generated data during alpha, but those surfaces must be treated as development-only unless they are explicitly designed as public examples.

## Release Rule

When Developer Mode is off:

- Demo-only views must be hidden.
- Random/mock-data tabs must be hidden.
- Unfinished trading, automation, advisor, AI, and SEES views must be hidden.
- Public views must either use live/configured data or show a clear empty state such as "Requires API Key", "No Data", or "Coming Soon".

When Developer Mode is on:

- Demo-only and diagnostic views may be visible.
- Views using generated data must label that state in the UI.
- Developers may access unfinished workflows for testing, but these views are not part of beta scope until backed by real data.

## Code Convention

Use these helpers from `functions.php` when adding or reviewing admin tabs:

- `tradepress_can_access_development_views()` checks the Developer Mode and capability gate.
- `tradepress_filter_development_tabs( $tabs, $development_tab_ids )` removes development-only tabs for regular users.

Tab controllers should define all known tabs, apply extension filters, then remove development-only tabs. This prevents direct URLs or extension filters from accidentally exposing demo workflows.

## Examples

Development-only until live data exists:

- Automation dashboard, algorithm, signals, trading, and settings.
- Trading strategies, portfolio, trade history, manual trading, SEES Demo, SEES Ready, and SEES Pro.
- Non-free data provider tabs beyond Trading212, Alpaca, and Alpha Vantage.
- API efficiency or diagnostic pages that report simulated status.

Always acceptable for regular users:

- Configuration pages that do not pretend to show live data.
- Calculators that work from user-entered values.
- Empty states that clearly state what data or API key is required.
