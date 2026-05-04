# TradePress Scoring System Documentation

## Overview
The TradePress scoring system provides a quantitative measure of trading potential for stock symbols. Each symbol receives a score that represents its potential based on various technical and fundamental indicators. This document explains how the scoring system works and how to interpret the scores displayed in the admin interface.

## Algorithm Boundary: Scoring vs Trading Strategy

TradePress intentionally separates two algorithm families:

1. **Scoring Directives algorithm (this document):**
    - Produces weighted scores for ranking and optimization.
    - Can demand high-quality confluence before signaling a strong opportunity.
    - Outputs are suited to ranking, filtering, and confidence comparisons.

2. **Trading Strategy algorithm (separate subsystem):**
    - Uses threshold-rule logic (for example, how many configured indicators must pass now).
    - Can trigger when conditions are acceptable, not necessarily optimal.
    - Supports optional confirmation windows to reduce early entries.

Important: Trading Strategy should not become a duplicate scoring engine. It may reference scores as an input, but score optimization and rule-threshold execution remain distinct responsibilities.

### Ownership rules for thresholds and symbol scope

Scoring strategies may store advisory trading context, but they do not execute trades and should not be treated as hard execution gates.

| Concern | Owner | Notes |
|---|---|---|
| Directive selection and weights | Scoring Strategy | Defines how the score is calculated. |
| Maximum possible strategy score | Scoring Strategy / SEES Diagnostics | Derived from the active directive stack and used to help choose sensible thresholds. |
| Suggested trading threshold | Scoring Strategy metadata | Advisory only. It helps a user document an intended trading target after reviewing the strategy's reachable score. |
| Symbol applicability/scope | Scoring Strategy metadata | Advisory context for SEES ranking and diagnostics. It identifies where the scoring formula was designed to apply. |
| Ranking/filtering symbols | SEES | SEES orders symbols from scores and may show advisory scope or threshold warnings. |
| Hard threshold enforcement | Trading Strategy | Automated trade execution decides whether a score threshold blocks a trade. |
| Hard symbol-scope enforcement | Trading Strategy | Scope can become an execution guard only in a trading strategy context, with open-position and auto-trading safety checks. |

Implementation note: existing storage uses `min_score_threshold` for the suggested threshold because the field already exists in scoring strategy persistence. New UI and service code should describe it as a suggested trading threshold, not as a scoring requirement.

### Scope guidance

- Core/free scope: clear rule-threshold controls, minimum-required condition controls, confirmation controls.
- Optional advanced precision: trend-of-change and wait-for-better-entry gating should be designed early but can remain optional complexity until premium extension phases.

## Score Components
The scoring algorithm evaluates multiple factors to generate a comprehensive score:

1. **Price Movement** (20% weight) - Evaluates recent price changes
2. **Volume** (15% weight) - Analyzes trading volume patterns
3. **RSI** (25% weight) - Considers Relative Strength Index values
4. **MACD** (25% weight) - Evaluates Moving Average Convergence Divergence
5. **Moving Averages** (15% weight) - Compares short and long-term moving averages

Additional modifiers can be applied through custom filters. Trading Strategy integration should consume score outputs as inputs without replacing the scoring algorithm's ranking role.

## Earnings-Based Scoring Directives (HIGH PRIORITY)

Based on analysis of effective market strategies, the following earnings-based scoring directives are prioritized for implementation:

1. **Earnings Event Proximity** (HIGH PRIORITY)
   - Adjusts score based on proximity to upcoming earnings announcements
   - Increases sensitivity as earnings date approaches
   - Accounts for historical volatility during earnings periods
   - Implements graduated scoring weight (higher weight as date approaches)

2. **Earnings Surprise Analysis** (HIGH PRIORITY)
   - Compares actual earnings vs. expectations ("earnings whisper")
   - Calculates Post-earnings Announcement Drift (PEAD) probability
   - Evaluates divergence between official estimates and market sentiment
   - Scores magnitude and direction of potential surprise

3. **Pre/Post-Earnings Behavior** (HIGH PRIORITY)
   - Analyzes symbol's historical price movement before/after earnings
   - Identifies patterns in Earnings Announcement Premium (EAP)
   - Tracks post-earnings volatility and directional bias
   - Creates symbol-specific earnings behavior profile

4. **Analyst Sentiment Integration** (HIGH PRIORITY)
   - Tracks changes in analyst consensus and price targets
   - Measures sentiment divergence against technical indicators
   - Evaluates analyst accuracy history for specific symbols
   - Compares official estimates with "whisper numbers"

These earnings-based directives will be weighted and integrated with existing technical indicators to create a more comprehensive scoring system that accounts for these critical market catalysts.

## Understanding the Score Display
In the Symbols admin table, each stock displays the following scoring information:

### Raw Score Value
The primary score is a raw strategy score. It must not be assumed to be a universal `0-100` percentage.

Many current directives use a familiar `0-100` scale, but the scoring system also supports directives and strategies with different maximum scores. A strategy total is the sum or weighted combination of its active directive scores, and that strategy may define its own maximum possible total.

This matters because:

- A directive may declare `get_max_score()` as `25`, `105`, `175`, `375`, or another value depending on configuration.
- A strategy using several directives can legitimately exceed `100` raw points.
- The UI may transform `raw_score / strategy_max_score` into a percentage for display, but that percentage is derived, not the canonical score.
- Tests must validate scores against the directive or strategy contract, not against a hard-coded global `0-100` rule.

### Legacy 0-100 Display
Some current admin views still display scores as if they are from 0 to 100, color-coded for quick visual assessment:
- **Red** (0-29): Low potential
- **Orange** (30-49): Moderate potential
- **Yellow** (50-69): Average potential
- **Green** (70-89): Good potential
- **Dark Green** (90-100): Excellent potential

Treat this as legacy/default display behaviour until the strategy-aware score display is fully implemented.

### Percentage Indicator
The percentage value represents how high the score is relative to the maximum possible score within the selected trading strategy. This is crucial because:

- A score of 70 might be excellent in one strategy but merely average in another
- The percentage helps you understand how close the symbol is to achieving the maximum potential score
- Different trading strategies have different maximum possible scores

For example, if a symbol has a score of 75 with an 85% percentage, this means:
- The raw score is 75 out of 100
- The maximum possible score for this symbol in the current strategy is approximately 88
- The symbol has achieved 85% of its maximum potential in the strategy

## Practical Application
When analyzing symbols:

1. **Look at the Score First** - This gives you a baseline assessment (0-100)
2. **Check the Percentage** - This tells you how optimized the stock is for your current strategy
3. **Consider Both Together** - High scores with high percentages represent the strongest candidates

## Customizing Score Display
Developers can modify the score display by:
- Adjusting the color thresholds in `render_score_indicator()` method
- Implementing custom strategy-based scoring by extending the `TradePress_Scoring_Algorithm` class
- Using the `tradepress_modify_symbol_score` filter to apply custom adjustments

## Implementation Notes
In production environments, strategy-specific maximum scores are calculated based on the actual strategy parameters. For development and testing, a simulated maximum score is generated to demonstrate the percentage functionality.

### Core Scoring Algorithm Structure:
- Algorithm Structure
    - Load a default strategy
        - Add a column of checkboxes to Scoring Directives tab for selecting directives as part of the default ❌
    - Will load all ACTIVE strategies (a profile of selected scoring directives) before beginning the loop/polling ❌
        - We will need a table of saved strategies for deleting or duplicating for creating an altered version, no editing will be allowed for a strategy ❌
        - Strategies will get a unique number, basically a counter which indicates how many strategies have been created can be used ❌
        - Strategies can be disabled on this admin table ❌
    - Create an array of all symbols ❌
        - if all strategies have exclusion symbols then do not use the default (active) symbols (in symbols post type) ❌
        - The array should have a priority value set by the user (1-100) ❌
        - Priority will be used depending on the time it takes to get through all symbols ❌
        - We'll need to determine a suitable time for re-checking a symbols status, and return to the higher priority symbols if that time is reached ❌
        - If priority causes a return to the beginning of the array, all stocks that aren't checked will be put on a paused status to prevent trading them ❌
        - That does not apply to symbols with existing positions opened by the plugin (risk management must always be active for open positions) ❌
    - API Call Results ❌
        - Create an array to hold standardised stock market data so that consecutive scoring directives can reuse the data rather than repeat API calls ❌
        - We should also store API call results in transient, create settings to switch transients off/on and also a setting for the time to hold transients ❌
            keeping in mind the longer the time the less accurate scoring would be but it may help to reduce unneeded calls on endpoints that don't change often ❌
        - Let's create a directory of endpoints with a column of data to indicate the change frequency ❌
    - Create an array of all scoring directives from all strategies ❌
        - Remove inactive scoring directives ❌
    - Increase the counter for the number of sessions run (I think the automation view already does this) ❌
    - Begin the endless loop (aka polling) ❌
        - Count the loops (I think the automation view already does this) ❌
        - The counter will act as an ID linking all the results together along with the Symbol ❌
        - Database table will be required for storing results, make them temporary (48 hours) ❌
        - Create an admin setting to extend the lifetime of entries ❌
    - Select the next symbol in the queue (strategy may have specific symbols attached to it, they take priority) ❌
        - Attaching symbols to a strategy needs to be a feature ❌
        - Add settings for strategy to only include selected symbols and not the default of ALL active symbols ❌
        - Needs to be a post-meta value for Symbols post-type to indicate if the symbol is active or not, add a checkbox for this to the post type edit screen ❌
    - Then loop through all the scoring directives (easiest approach might be to use the directives slug/name in an eval() line to call the directive method) ❌
        - Results of a directive API call (actual data) gets stored in database (not sure about table name right now) ❌
        - The determined result of calculations that the plugin does, and further algorithmns built into the directive, are stored in the database ❌
        - The score generated by the directive is then assigned to each of the active strategies (their totals will differ if they have different directives) ❌
            - Also store the score in the database ❌
        - Main Settings will have Scoring Directive switches that override everything else - if a strategy uses an inactive strategy it will be skipped ❌
            - This is to be able to quickly disable directives that are considered inaffective or problematic at any giving time ❌
    - Score total ❌
        - The score will be stored in database (custom table) ❌
    - Trading ❌
        - Trading will not happen within this loop ❌
Okay, this looks like a solid foundation for your core scoring loop. Based on your outline, here are some questions and points for clarification that arise:


- Asynchronous Operation: The scoring and trading algorithms will run asynchronously, initially triggered by user action and eventually by cron jobs. ❌
- Scoring System:
    - Employ a scoring system to rank securities based on potential profitability. ✅
    - Adaptable to different trading styles (CFD, Investment, etc.). ❌
    - User-Configurable: Highly flexible and customizable via a detailed admin interface. ❌
    - API Integration: All API calls should pass through the same class to ensure data consistency. ✅
    - API Call Management: Track API calls and manage limits; switch APIs automatically when limits are reached. ❌
- Data Management:
    - Ensure data is persistent with timestamps at every point of integration. ❌
    - Add a caution indicator if news hasn't been updated for a specified period of time. ❌
    - Prune data as the dataset increases to avoid impacting effeciency. ❌
    - Move News to a separate database table. ❌
- Error Handling:
    - Log errors to `debug.log` and the BugNet class. ✅
    - Use admin notices for persistent or high-priority errors. ✅
    - Implement retry logic for API calls. ❌
    - Cycle through alternative APIs after multiple failures. ❌
    - Flag unused code with comments for future cleanup. ❌
- Testing:
    - Implement rigorous testing procedures to ensure stability. ❌
    - Log and monitor data flow from the various API. ❌
    - Log and monitor scoring and the algorithm. ❌
- System Limitations
    - Establish a risk by time system i.e. the more time that passes the less reliable a score is ❌
    - Also count the algorithmn time to complete scoring for a symbol and predict how many can be done before reaching the considered limit before 
    having to return to the first symbol and re-check it's status. ❌

### Scoring Algorithm:
- Core Component:  The central element of the project, driving all security recommendations. ✅
- Complexity: Incorporate multiple chart indicators, custom settings, and global market changes. ❌
- Trading Styles:  Adaptable to different trading styles (CFD, Investment) through user-configurable options. ❌
- Future-Proofing: Design should anticipate features like automatic trading. ✅
- Class-Based Implementation: Encapsulate the algorithm within a dedicated class (`class.tradepress-scoring-algorithm.php`). ✅
- Long/Short Positions: Consider positive and negative values. ❌
- User-Configurable: Allow users to enable/disable parameters. ❌
- Parameters:
    - **Standard Indicators:** ✅
        1.  Moving Averages (various periods) ✅
        2.  Relative Strength Index (RSI) ✅
        3.  Bollinger Bands ✅
        4.  MACD (Moving Average Convergence Divergence) ✅
        5.  Stochastic Oscillator ✅
        6.  Average True Range (ATR) ✅
        7.  On-Balance Volume (OBV) ✅
        8.  Commodity Channel Index (CCI) ✅
        9.  Fibonacci Retracement Levels ❌
        10. Parabolic SAR ✅
    - **Additional Logic:** ❌
        1.  **Earnings Reports:** Score based on upcoming earnings reports and analyst expectations. ❌
        2.  **News Sentiment:** Analyze financial news for positive/negative sentiment towards a security. ❌
        3.  **Discord/Chat Analysis:** Scrape and analyze chat data for trending stock tips. ❌
        4.  **Analyst Ratings:** Incorporate buy/sell/hold recommendations from analysts. ❌
        5.  **Volume Analysis:** Track trading volume to identify potential breakouts or reversals. ✅
        6.  **Volatility:** Measure the volatility of a security and factor it into risk assessment. ✅
        7.  **Economic Calendar:** Incorporate economic events that may impact security prices. ❌
        8.  **Insider Trading:** Track insider buying/selling activity. ❌
        9.  **Sector Performance:** Compare a security's performance against its sector. ❌
        10. **Web Scraping:** Extract data from financial websites to gain insights not available through APIs. ❌
        11. **Consensus Building:** Aggregate user input to determine a consensus view. ❌
        12. **Best/Worst Month Rule:** Adjust scores based on historical performance per month. ❌
        13. **Dynamic Rule Threshholds:** Allow rules to adjust based on custom factors. ❌
        14. **Proximity of Data:** Use timestamp data to create dynamic rules based on how recently data was made available. ❌
        15. **Thresholds:** Implement various thresholds to trigger actions based on scoring or other factors. ❌
        16. **Security Exclusion:** Allow users to exclude specific securities from the algorithm. ❌
        17. **Maximum Symbols:** Allow the user to define a maximum number of symbols to be processed to maintain effeciency. ❌
        18. **Symbol Selection:** The ability to use specific criteria for selecting which symbols to process. ❌
        19. **Average Scoring Threshold:** Avoid processing low scoring security symbols. ❌
- Long/Short Positions:
    - Long Positions: Identify securities likely to increase in price (higher scores = higher likelihood). ❌
    - Short Positions: Identify securities likely to decrease in price (higher scores = higher likelihood). ❌
    - Reverse Indicators: Use indicators and news in reverse to identify short positions. ❌
    - User Choice: Allow users to select their desired position type. ❌
- Symbol Specific Timeframe: Allow the user to set a specific timeframe per security in addition to a global setting. ❌
- Admin Control:
    - Activate/deactivate parameters. ❌
    - Create/delete/edit rules for each parameter. ❌
    - Create new rule sets. ❌

## Scoring System Discussion/Notes (to be processed into technical specifications)

  Deep Dive into Trading Algorithm Scoring Strategy
  Thank you for sharing such detailed insights into your vision for the scoring system. This level of complexity is fascinating and represents a much more sophisticated approach than what I initially implemented.

  Current vs. Intended Weighting System
  You're right that the current weighting implementation might not fully align with your vision. The current system uses simple percentage weights that just determine how much influence each directive has on the final score. Your vision seems to be for:

  Gradient-based scoring within each directive (not just binary conditions)
  Dynamic weighting where points are awarded proportionally to how close a condition is to ideal
  Layered weighting where the directive itself has importance, and then conditions within that directive have their own significance
  Profile-Based Scoring
  The concept of profile-based scoring makes perfect sense. Since different strategies would prioritize different indicators, having scores tied to specific profiles provides proper context. This approach allows for:

  Comparing the same stock across different strategies
  Backtesting various profiles to determine most effective approaches
  Gradually evolving strategies based on performance data
  Long vs. Short Strategies
  This is an excellent question. There are two potential approaches:

  Option 1: Unified Algorithm (-100 to +100 scale)
  Use the same algorithm with a scale where negative values indicate short opportunities
  Base directives apply to both (volume, price action, etc.)
  Specific directives push the score positive (long signals) or negative (short signals)
  Near-zero indicates neutral positions
  Advantages: Efficient, comparative, consistent baseline
  Option 2: Separate Algorithms
  One algorithm focused on finding long opportunities (0-100 scale)
  Another specifically tuned for short opportunities (0-100 scale, higher = better short)
  Advantages: More specialized, potentially clearer thresholds, simpler to understand
  I personally favor Option 1 (unified) as it better represents the spectrum of market positions and allows for easier comparison between opportunities.

  Advanced Weighting Considerations
  The approach you described with analytic predictions and temporary dips shows the depth of complexity needed:

  Proportional Scoring: Adding points per percentage deviation from a target
  Conditional Multipliers: Extra points during specific market conditions (like dips)
  Context Awareness: Distinguishing between dips and crashes
  This requires directives that aren't just static measurements but also consider:

  Market context
  Historical patterns
  Relative movements
  Time-based factors
  Implementation Path Forward
  Given this sophistication, I would suggest:

  First implement the basic framework with simpler directives
  Add the profile system as a container for directive configurations
  Enhance directives to support gradient scoring
  Implement unified scale for long/short signals
  Add contextual awareness capabilities
  Would you prefer to implement the more complex system directly, or start with simpler building blocks and evolve toward your complete vision?

  I understand your approach preferences. Here's what I've processed:

  We'll build from simple to complex, evolving toward your full vision
  Using a unified algorithm for both long/short positions (-100 to +100 scale)
  Prioritizing long/buying functionality and portfolio investment features
  Allowing scores to exceed 100 with sufficient active directives
  Implementing score decay over time:
  Faster decay for shorter trading periods
  Configurable decay rates based on chart timeframes (minutes, hours)
  Tied to profile strategy and time horizons
  Supporting rapid profit-taking strategies
  This time-based decay adds an important dimension to your scoring system, making it more dynamic and aligned with different trading timeframes.

