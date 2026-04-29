# AI CURRENT PROJECT: BugNet System Implementation

## Coordination Update — 2026-04-29

Release-prep documentation has been updated while this BugNet/Advisor project remains in the file for historical context.

Before continuing Advisor, Focus, Research, Analysis, Trading, or any UI work:

- Read `START.md`
- Read `SPRINT.md`
- Check `ROADMAP.md` → UI/UX Sprint, Release Readiness Triage, Demo Content Removal Inventory
- Check `roadmap/admin-ui-status-index.md`
- Check `wp-content/plugins/tradepress/docs/procedures/demo-content-inventory.md`
- Check `wp-content/plugins/tradepress/docs/procedures/demo-data-and-developer-mode.md`
- Check `wp-content/plugins/tradepress/docs/procedures/STYLES.md` before UI changes
- Check `wp-content/plugins/tradepress/docs/data/DATA-ARCHITECTURE.md` before API, import, storage, or data-display changes
- Check `wp-content/plugins/tradepress/docs/data/DATA-FRESHNESS-FRAMEWORK.md` before changing refresh/staleness logic
- Check `wp-content/plugins/tradepress/docs/data/DATABASE-META-TABLES-PLAN.md` before adding or changing storage

Current product direction:

- UI cleanup is release-priority work.
- User-visible demo/mock/random/sample market output should be phased out.
- Demo-backed views should use imported/live data, show a clear empty state, remain Developer Mode-only, move to an extension, or be removed.
- All API calls must still go through the queued data flow.

---

## Current Focus: Debugging and Monitoring System

**Priority:** Phase 1, Week 1
**Purpose:** Systematic debugging system to support all development tasks
**Status:** Initial Implementation Complete - Ready for Testing

## Completed Tasks:
- [x] Created primary BugNet class with logging levels and output types
- [x] Added BugNet settings tab with output controls and depth level
- [x] Added BugNet controls to TradePress Developers toolbar
- [x] Created helper functions for easy debugging access
- [x] Added BugNet logging to advisor form submission
- [x] Fixed advisor form submission using admin-post.php handlers
- [x] Resolved duplicate function declaration errors
- [x] Implemented proper session management for advisor steps

## Next Phase: Focus Advisor Tab Debugging
**Status:** Form validation working, investigating blank screen on submission

## Project Overview

Implementing the Focus Advisor tab as a step-by-step wizard that guides users through comprehensive investment analysis. This system will carry selected stocks forward through each step, building toward automated decision-making while providing immediate value.

## Core Architecture

### Session Management
**Approach:** WordPress transients for step data persistence
- `tradepress_advisor_session_{user_id}` - Main session data
- `tradepress_advisor_step_{user_id}_{step}` - Individual step results
- 24-hour expiration with manual clear option

### Navigation System
**Progressive Access:** Users can only access completed steps
- Step 1 (Mode Selection): Always accessible
- Steps 2-6: Unlocked after previous step completion
- Visual indicators for completed/locked/current steps

## Required Classes and Functions

### Core Advisor Classes

1. **`TradePress_Advisor_Session`** (`includes/advisor/advisor-session.php`)
   - Session state management and data persistence
   - Methods:
     - `start_session($mode)` - Initialize new advisor session
     - `get_session_data()` - Retrieve current session state
     - `update_step_data($step, $data)` - Save step results
     - `get_step_data($step)` - Retrieve step results
     - `clear_session()` - Reset advisor session
     - `get_selected_symbols()` - Get symbols carried through steps

2. **`TradePress_Advisor_Controller`** (`includes/advisor/advisor-controller.php`)
   - Main controller for advisor workflow
   - Methods:
     - `render_step($step_number)` - Display specific step interface
     - `process_step_submission($step, $data)` - Handle step form submissions
     - `validate_step_access($step)` - Check if user can access step
     - `get_progress_status()` - Return completion status for all steps
     - `calculate_final_recommendations()` - Generate final investment advice

3. **`TradePress_Advisor_Mode_Handler`** (`includes/advisor/advisor-mode-handler.php`)
   - Handles different advisor modes (Invest, Day Trade, etc.)
   - Methods:
     - `get_available_modes()` - List all advisor modes
     - `set_mode($mode)` - Configure advisor for specific mode
     - `get_mode_steps($mode)` - Get steps for selected mode
     - `get_mode_settings($mode)` - Get mode-specific configurations

### Step-Specific Classes

4. **`TradePress_Advisor_Earnings_Step`** (`includes/advisor/steps/earnings-step.php`)
   - Step 2: Earnings calendar integration
   - Methods:
     - `get_earnings_opportunities()` - Fetch curated earnings list
     - `filter_by_user_symbols()` - Prioritize user's symbols
     - `process_symbol_selection($symbols)` - Handle user selections

5. **`TradePress_Advisor_News_Step`** (`includes/advisor/steps/class.tradepress-news-step.php`)
   - Step 3: News analysis and sentiment
   - Methods:
     - `get_news_for_symbols($symbols)` - Fetch news for selected stocks
     - `analyze_sentiment($news_items)` - Basic sentiment analysis
     - `suggest_additional_symbols()` - Recommend based on positive news

6. **`TradePress_Advisor_Forecasts_Step`** (`includes/advisor/steps/class.tradepress-forecasts-step.php`)
   - Step 4: Price targets and analyst forecasts
   - Methods:
     - `get_price_targets($symbols)` - Fetch analyst price targets
     - `calculate_upside_potential($symbol, $current_price, $target)` - Calculate potential returns
     - `format_forecast_data($data)` - Format for display

7. **`TradePress_Advisor_Economic_Step`** (`includes/advisor/steps/class.tradepress-economic-step.php`)
   - Step 5: Economic impact assessment
   - Methods:
     - `get_relevant_indicators($symbols)` - Find applicable economic data
     - `assess_sector_impact($symbols)` - Analyze sector-specific factors
     - `get_policy_impacts($symbols)` - Identify policy effects

8. **`TradePress_Advisor_Technical_Step`** (`includes/advisor/steps/class.tradepress-technical-step.php`)
   - Step 6: Support & resistance analysis
   - Methods:
     - `get_support_resistance($symbols)` - Calculate technical levels
     - `assess_entry_timing($symbol)` - Determine optimal entry points
     - `calculate_risk_reward($symbol)` - Risk/reward analysis

### Data Integration Classes

9. **`TradePress_Advisor_Data_Fetcher`** (`includes/advisor/advisor-data-fetcher.php`)
   - Coordinates data fetching across all APIs
   - Methods:
     - `fetch_earnings_data()` - Get earnings calendar data
     - `fetch_news_data($symbols)` - Get news for symbols
     - `fetch_analyst_data($symbols)` - Get price targets and estimates
     - `fetch_economic_data()` - Get relevant economic indicators
     - `fetch_technical_data($symbols)` - Get technical analysis data

10. **`TradePress_Advisor_API_Coordinator`** (`includes/advisor/advisor-api-coordinator.php`)
    - Manages API calls and rate limiting for advisor
    - Methods:
      - `queue_api_request($endpoint, $params)` - Queue API calls
      - `process_api_queue()` - Execute queued requests
      - `get_cached_data($key)` - Retrieve cached API responses
      - `cache_api_response($key, $data)` - Cache API responses

## Implementation Steps

### Week 1: Core Infrastructure
- [x] Create advisor tab in Focus page
- [x] Implement session management system
- [x] Build step navigation with progressive access
- [x] Create basic wizard interface

### Week 2: Step Implementation
- [x] Implement Mode Selection (Step 1)
- [x] Build Earnings Opportunities (Step 2)
- [x] Create News Analysis (Step 3)
- [x] Add basic data fetching capabilities

### Week 3: Advanced Steps
- [ ] Implement Price Forecasts (Step 4)
- [ ] Build Economic Impact (Step 5)
- [ ] Create Technical Analysis (Step 6)
- [ ] Add final recommendations system

## File Structure

```
admin/page/focus/view/advisor.php - Main advisor interface
includes/advisor/
  ├── advisor-session.php
  ├── advisor-controller.php
  ├── advisor-mode-handler.php
  ├── advisor-data-fetcher.php
  ├── advisor-api-coordinator.php
  └── steps/
      ├── earnings-step.php
      ├── news-step.php
      ├── forecasts-step.php
      ├── economic-step.php
      └── technical-step.php
```

## Success Criteria

- [x] Advisor tab accessible from Focus page
- [x] Step navigation with progressive access works
- [x] Session data persists between steps
- [x] Mode selection affects available steps
- [ ] Selected symbols carry forward through all steps
- [ ] Each step fetches real data (no demo content)
- [ ] Final recommendations generated from all steps
- [x] Session can be cleared and restarted
- [ ] API calls properly queued and rate-limited
- [ ] Error handling for failed API calls

## API Integration Requirements

**No Demo Content:** All steps must fetch real data from trading platforms and data providers:

- **Earnings Data:** Alpha Vantage EARNINGS_CALENDAR endpoint
- **News Data:** Alpha Vantage NEWS_SENTIMENT or alternative news API
- **Price Targets:** Financial Modeling Prep or IEX Cloud
- **Economic Data:** FRED API or Alpha Vantage economic indicators
- **Technical Data:** Alpha Vantage technical indicators or custom calculations

## Next Phase Preview

After completing basic Advisor tab:
- [ ] Add AI-powered recommendations to each step
- [ ] Implement alternative advisor modes (Day Trade, Portfolio Review)
- [ ] Create starring system for priority stock management
- [ ] Build automated decision-making based on advisor logic

## Current Priority Tasks

### Immediate (This Week)
- [x] Complete Step 2 (Earnings Opportunities) implementation
- [x] Complete Step 3 (News Analysis) with sentiment analysis
- [x] Test symbol selection and carry-forward through steps
- [x] Add news-based additional opportunities feature
- [ ] Implement proper error handling for API failures

### Next Week
- [ ] Replace demo data with real news API integration
- [ ] Implement Step 4 (Price Forecasts) with analyst data
- [ ] Implement Step 5 (Economic Impact) analysis
- [ ] Create comprehensive testing for all completed steps

## Notes

- Use WordPress transients for session management (24-hour expiration)
- Follow existing plugin patterns for API integration
- Ensure all API calls go through proper rate limiting
- Implement comprehensive error handling for API failures
- Create clear visual indicators for step progress
- Allow users to modify selections in previous steps
