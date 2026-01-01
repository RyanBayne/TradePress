# TradePress Asset Management Guidelines

## Plugin Tabbed Views - Asset Review Status
**Management System:** All tabs except "UI Library" use `wp-content\plugins\TradePress\admin\assets-loader-original.php`
**UI Library:** Uses newer system in `wp-content/plugins/TradePress/assets/manage-assets.php`

# Migration Plan (do not action until specifically asked)
We will be replacing `assets.php` with `manage-assets.php` systematically. 

### 1. Settings Page (`tradepress-settings`)
**File:** `admin/page/settings/class.tradepress-admin-settings.php`
**Tabs:**
- [ ] General (`view/general.php`)
- [ ] Users (`view/users.php`) 
- [ ] Trading API (`view/tradingapi.php`)
- [ ] Features (`view/features.php`)
- [ ] Shortcodes (`view/shortcodes.php`)
- [ ] Database (`view/database.php`)
- [ ] SEES (`view/sees.php`)
- [ ] BugNet (`view/bugnet.php`)

### 2. Development Page (`tradepress_development`)
**File:** `admin/page/development/development-tabs.php`
**Tabs:**
- [ ] Current Task (`view/current-task.php`)
- [ ] Tasks (`view/tasks.php`)
- [ ] GitHub (`view/github.php`)
- [ ] Changes (`view/changes.php`)
- [ ] Discussion (`view/discussion.php`)
- [ ] Notes (`view/notes.php`)
- [ ] Feature Status (`view/feature-status.php`)
- [ ] AI (`view/ai.php`)
- [x] UI Library (`view/ui-library.php`) *Uses new asset system*
- [ ] jQuery UI (`view/jquery-ui.php`)
- [ ] Assets (`view/assets-loader-original.php`)
- [ ] Listener Testing (`view/listener-testing.php`)

### 3. Trading Platforms Page (`tradepress_platforms`)
**File:** `admin/page/tradingplatforms/trading-platforms-tabs.php`
**Tabs:**
- [ ] AllTick (`view/view.alltick.php`)
- [ ] Alpaca (`view/view.alpaca.php`)
- [ ] IEX Cloud (`view/view.iexcloud.php`)
- [ ] Polygon (`view/view.polygon.php`)
- [ ] Tradier (`view/view.tradier.php`)
- [ ] Finnhub (`view/view.finnhub.php`)
- [ ] Twitter (`view/view.twitter.php`)
- [ ] StockTwits (`view/view.stocktwits.php`)
- [ ] Twelve Data (`view/view.twelvedata.php`)
- [ ] Interactive Brokers (`view/view.ibkr.php`)
- [ ] TradingView (`view/view.tradingview.php`)
- [ ] Marketstack (`view/view.marketstack.php`)
- [ ] EOD Historical Data (`view/view.eodhistoricaldata.php`)
- [ ] Yahoo Finance (`view/view.yahoofinance.php`)
- [ ] Tiingo (`view/view.tiingo.php`)
- [ ] Alpha Vantage (`view/view.alphavantage.php`)
- [ ] Quandl (`view/view.quandl.php`)
- [ ] FRED (`view/view.fred.php`)
- [ ] Gemini (`view/view.gemini.php`)
- [ ] WeBull (`view/view.webull.php`)
- [ ] Trading212 (`view/view.trading212.php`)
- [ ] TradingAPI (`view/view.tradingapi.php`)
- [ ] Comparisons (`comparisons.php`)
- [ ] Platform Switches (`view/view.api_switches.php`)
- [ ] Overview (`view/view.overview.php`)
- [ ] Endpoints (`view/view.endpoints.php`)

### 4. Social Platforms Page (`tradepress_social`)
**File:** `admin/page/socialplatforms/socialplatforms-tabs.php`
**Tabs:**
- [ ] Settings (`view/settings.php`)
- [ ] Platform Switches (`view/platform_switches.php`)
- [ ] Discord (`view/discord/discord.php`)
  - [ ] Discord Stock VIP (`view/discord/stockvip/view.stock-vip.php`)
    - [ ] Alert Decoder (`stockvip/tab-alert-decoder.php`)
    - [ ] Stock Alerts (`stockvip/tab-stock-alerts.php`)
    - [ ] Channel (`stockvip/tab-channel.php`)
    - [ ] Settings (`stockvip/tab-settings.php`)
    - [ ] Statistics (`stockvip/tab-statistics.php`)
  - [ ] Discord Endpoint Tester (`view/discord/discord-endpoint-tester.php`)
- [ ] Twitter (`view/twitter.php`)
- [ ] StockTwits (`view/stocktwits.php`)

### 5. Trading Page (`tradepress_trading`)
**File:** `admin/page/trading/trading-tabs.php`
**Tabs:**
- [ ] Portfolio (`view/portfolio.php`)
- [ ] Trade History (`view/trade-history.php`)
- [ ] Manual Trading (`view/manual-trade.php`)
- [ ] Create Strategies (`view/create_strategy.php`)
- [ ] SEES Demo (`view/sees-demo.php`)
- [ ] SEES Ready (`view/sees-ready.php`)
- [ ] SEES Pro (`view/sees-pro.php`)
- [ ] Calculators (`view/calculators.php`)
- [ ] Trading Strategies (`view/trading-strategies.php`)

### 6. Watchlists Page (`tradepress_watchlists`)
**File:** `admin/page/watchlists/watchlists-tabs.php`
**Tabs:**
- [ ] Active Symbols (`view/active-symbols.php`)
- [ ] User Watchlists (`view/user-watchlists.php`)
- [ ] Create Watchlist (`view/create-watchlist.php`)

### 7. Data Page (`tradepress_data`)
**File:** `admin/page/data/data-tabs.php`
**Tabs:**
- [ ] Tables (`view/tables.php`)
- [ ] Manage Sources (`view/manage-sources.php`)
- [ ] Decoder (`view/decoder.php`)
- [ ] Endpoints (`view/endpoints.php`)
- [ ] API Activity (`view/api-activity.php`)

### 8. Analysis Page (`tradepress_analysis`)
**File:** `admin/page/analysis/analysis-tabs.php`
**Tabs:**
- [ ] Recent Symbols (`view/recent-symbols.php`)
- [ ] Performance (`view/performance.php`)
- [ ] Market Overview (`view/market-overview.php`)

### 9. Automation Page (`tradepress_automation`)
**File:** `admin/page/automation/automation-tabs.php`
**Tabs:**
- [ ] Dashboard (`view/dashboard.php`)
- [ ] Algorithm (`view/algorithm.php`)
- [ ] Signals (`view/signals.php`)
- [ ] Trading (`view/trading.php`)
- [ ] CRON Jobs (`view/cron.php`)

### 10. Research Page (`tradepress_research`)
**File:** `admin/page/research/research-tabs.php`
**Tabs:**
- [ ] Overview (`view/overview.php`)
- [ ] Earnings Calendar (`view/earnings.php`)
- [ ] Technical Analysis (`view/technical.php`)
- [ ] Fundamental Analysis (`view/fundamental.php`)
- [ ] News (`view/news.php`)
- [ ] Chatter (`view/chatter.php`)

### 11. Scoring Directives Page (`tradepress_scoring_directives`)
**File:** `admin/page/scoring-directives/scoring-directives-tabs.php`
**Tabs:**
- [ ] Overview (`view/overview.php`)
- [ ] Create (`view/create.php`)
- [ ] Manage (`view/manage.php`)

**Legend:**
- [x] = Asset status verified and working
- [ ] = Pending asset review
- ❌ = Issues found, requires fixes

**Review Priority:** Start with most frequently used tabs (Settings > Development > Trading Platforms)
**Asset System Requirements:**
- [x] CSS Variables file created (`css/base/variables.css`)
- [x] Asset queue system implemented (`queue-assets.php`)
- [x] Asset manager integration with UI Library
- [x] Color Palette section completed with proper styling
- [x] Button Components section completed with proper styling
- [x] Main container structure optimized
- [x] All component CSS files have matching HTML structures
- [x] Pagination Controls section completed with interactive functionality
- [x] Animation Showcase section completed with existing CSS classes
- [x] Working Notes section completed with editor components
- [ ] All partials created and functional
- [ ] Interactive JavaScript functionality working
- [ ] Cross-browser compatibility tested

**Recent Achievements:**
- ✅ Color palette grid now displays in compact 2-row layout
- ✅ Button components section properly integrated
- ✅ Form components section completed with validation states and layouts
- ✅ Controls and Actions section integrated with interactive functionality
- ✅ Filters and Search section completed with comprehensive filter components
- ✅ Pagination Controls section integrated with standard, compact, and infinite scroll variants
- ✅ Progress Indicators section integrated with progress bars, step indicators, and loading spinners
- ✅ Animation Showcase section refactored to use existing CSS classes only and integrated into main container
- ✅ Working Notes section completed with editor components and integrated into main container
- ✅ Accordion Components section completed with collapsible panels, tree-view, and FAQ-style accordions
- ✅ Status Indicators section completed with trading status, connection status, and performance indicators
- ✅ Data Analysis Components section completed with trading metrics, statistics tables, and KPI dashboards
- ✅ CSS variables system fully functional
- ✅ Asset queue system loading styles correctly
- ✅ Main container cleaned up and optimized

**Next Sections: Chart Visualization**
Create `admin/page/development/partials/ui-library/chart-visualization.php` with:
- Chart components for data visualization
- Trading chart patterns and indicators
- Performance and comparison charts
- Interactive chart controls

---

This document outlines the structure and best practices for managing CSS and JavaScript assets within the TradePress plugin. The asset system has been refactored to a component-based architecture to improve maintainability, performance, and developer experience.

## Asset Directory Structure

All frontend assets are located in the `/wp-content/plugins/TradePress/assets/` directory. The structure is as follows:

```
assets/
├── css/
│   ├── components/      # Reusable UI components (e.g., cards, forms, tables)
│   ├── layouts/         # Global layout styles (e.g., admin page structure, grids)
│   └── pages/           # Styles for specific admin pages or tabs
├── js/
│   ├── admin/           # Admin-specific functionality
│   ├── components/      # Scripts for specific components (e.g., accordion)
│   └── features/        # Scripts for major features
└── manage-assets.php    # The central asset registry for the plugin
```

## How to Add or Modify Assets

All assets are registered in `manage-assets.php`. Follow these steps to add or modify assets:

1.  **Identify the Correct Location**:
    *   **CSS**: Place new styles in the appropriate `css` subdirectory (`components`, `layouts`, or `pages`).
    *   **JS**: Place new scripts in the appropriate `js` subdirectory.
2.  **Register in `manage-assets.php`**: Add or update the asset's entry in the `$assets` array in `manage-assets.php`. Define its `path`, `purpose`, `pages` for conditional loading, and `dependencies`.
3.  **Follow Naming Conventions**: Use the `tp-` prefix for all new CSS classes.
4.  **Use CSS Variables**: Utilize existing CSS variables for colors, spacing, and typography to maintain consistency.
5.  **Document Components**: If creating a new component, document its HTML structure at the top of its CSS file.

## Naming Convention

TradePress uses a BEM-like methodology with a `tp-` prefix for all class names to avoid conflicts and improve readability:

- `tp-component` - Base component class (e.g., `tp-card`)
- `tp-component__element` - Element within a component (e.g., `tp-card__header`)
- `tp-component--modifier` - Modifier for specific variants (e.g., `tp-card--highlighted`)

## Component Documentation

Each component should have its HTML structure documented at the top of its CSS file. For example:

```html
<!-- Accordion Example -->
<div class="tp-accordion">
  <div class="tp-accordion-item">
    <div class="tp-accordion-header">
      <h4>Title</h4>
      <span class="tp-accordion-icon dashicons dashicons-arrow-up-alt2"></span>
    </div>
    <div class="tp-accordion-content">
      Content goes here
    </div>
  </div>
</div>
```

## CSS Variables

TradePress uses CSS variables (custom properties) for consistent styling. These are typically defined in a global stylesheet loaded on all admin pages.

- Colors: `--tp-color-*`
- Spacing: `--tp-spacing-*`
- Typography: `--tp-font-*`
- Borders: `--tp-border-*`

Example usage:
```css
.my-element {
  color: var(--tp-color-gray-500);
  margin-bottom: var(--tp-spacing-md);
  font-weight: var(--tp-font-weight-semibold);
}
```

## Legacy Class Support

For backward compatibility, some legacy class names are supported through CSS rules that map them to the new component styles. New development should always use the new `tp-` prefixed classes.

Example:
```css
/* New class */
.tradepress-accordion { /* styles */ }

/* Legacy support in the same file */
.alert-decoder-accordion {
    /* Mapped styles */
}
```

## Component List Reference

| Component | Primary Class | Description | File Location |
|-----------|---------------|-------------|--------------|
| Accordion | `.tradepress-accordion` | Collapsible content panels | components/accordion.css |
| Form | `.tradepress-form` | Form elements and layouts | components/forms.css |
| Card | `.tradepress-card` | Content containers | components/cards.css |
| Table | `.tradepress-table` | Data tables | components/tables.css |
| Grid | `.tradepress-grid` | Layout grid system | layouts/grids.css |
| Button | `.tradepress-button` | Action buttons | components/buttons.css |
| Notice | `.tradepress-notice` | Admin notices and alerts | components/notices.css |
| Badge | `.tradepress-badge` | Status and info badges | components/badges.css |


## Responsive Design

Responsive styles are handled within component or layout files using media queries. Use the existing breakpoints for consistency:

- Mobile: `max-width: 600px`
- Tablet: `max-width: 782px`
- Desktop: `min-width: 783px`


## LESSONS LEARNED FROM MIGRATION

This section is retained for historical context.

### Asset Management System Challenges

#### 1. Complex Dependency Chains
**Problem:** CSS files had circular or unclear dependencies
**Impact:** Files loaded in wrong order, causing style conflicts
**Lesson:** Need explicit dependency declaration and validation in `manage-assets.php`.

#### 2. Fallback System Complexity
**Problem:** Multiple fallback mechanisms created confusion
**Impact:** Difficult to determine which CSS file was actually loading
**Lesson:** Simplify fallback to single migration target per deprecated file.

#### 3. Page Detection Logic
**Problem:** Tab and page detection was unreliable
**Impact:** Wrong assets loaded for admin pages
**Lesson:** The new asset manager uses a `pages` array for explicit, reliable conditional loading.

#### 4. File Existence Checks
**Problem:** Asset system assumed files existed without verification
**Impact:** 404 errors and broken styling
**Lesson:** The new system includes file existence checks.

### File Structure Challenges

#### 1. Verbose Naming Impact
**Problem:** Long filenames made development slower
**Evidence:** IDE autocomplete took longer, file navigation was cumbersome
**Impact:** Reduced development velocity, increased cognitive load

#### 2. Inconsistent Patterns
**Problem:** Mixed naming conventions across different plugin areas
**Impact:** Difficult to predict file locations, reduced code maintainability

#### 3. Deep Directory Nesting
**Problem:** Files buried 4-5 levels deep in directory structure
**Impact:** Long import paths, difficult file organization