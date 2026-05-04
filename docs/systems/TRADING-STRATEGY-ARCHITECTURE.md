# Trading Strategy Architecture

## Purpose

This document defines the complete decision model for TradePress trading strategies. It covers three levels of increasing complexity that the strategy builder UI must support, their execution logic, and the relationship to the Scoring Directives system.

---

## Two Independent Systems

TradePress separates two decision systems that can be used independently or together:

| System | Role | When to use |
|---|---|---|
| **Scoring Directives** | Ranks and optimises trade quality. Produces a weighted score. | Manual trading decisions, monitoring, identifying near-ideal entries |
| **Trading Strategies** | Decides whether to trigger an automated trade. Uses threshold/rule logic. | Automated execution when acceptable (not necessarily optimal) conditions are met |

A user can rely on Scoring alone for manual decisions. They can use Trading Strategies alone to automate based on rules. Or they can combine them, using a Scoring Strategy as one of the inputs into a Trading Strategy.

### Enforcement ownership

Scoring Strategies can recommend a score threshold and intended symbol scope, but those settings are advisory metadata until a Trading Strategy consumes them.

Trading Strategies own enforcement decisions:

- Whether a scoring threshold blocks a trade.
- Whether a symbol outside the scoring strategy's intended scope blocks a trade.
- Whether an active auto-trading run must be stopped before strategy/watchlist changes.
- Whether open CFD positions require close/reduce/hold handling before a strategy is halted.
- Whether a watchlist mutation is advisory, strong-warning, or blocked by user confirmation.

SEES owns ranking and diagnostics. It can surface warnings, show distance from a suggested threshold, and explain scope mismatch, but SEES should not be the authority that executes or halts trades.

---

## Level 1 — Rule-Threshold Strategy (Indicator-Only)

### What it is

The simplest form. The user selects a set of indicators and defines how many must be in an acceptable state before a trade triggers.

### Execution model

```
Strategy defines:
  - N total indicators
  - Minimum required to pass (count or %)

Trade triggers when:
  passed_count >= minimum_required
```

### Example

> RSI, MACD, Volume Spike, Bollinger Band, Moving Average Crossover are all configured.
> Strategy requires 3 of 5 to be in acceptable state.
> RSI passes, MACD passes, Volume Spike passes → trade triggers.

### Grouped indicator sets (sub-groups)

Indicators can be organised into **named groups** within a strategy. Each group has its own pass requirement. The group result (pass/fail) is then treated as a single condition in the parent strategy.

```
Strategy (requires 2 of 3 groups to pass):
  ├── Group A "Momentum" (requires 3 of 5 indicators)
  │     RSI, MACD, Stochastic, Williams %R, CCI
  ├── Group B "Volume" (requires 2 of 3 indicators)
  │     Volume Spike, OBV, Money Flow Index
  └── Group C "Trend" (requires 2 of 2 indicators)
        Moving Average Crossover, ADX

Group A passes if ≥3 of its 5 indicators pass.
Group B passes if ≥2 of its 3 indicators pass.
Group C passes if both indicators pass.
Trade triggers if ≥2 of 3 groups pass.
```

This allows a user to say "I need at least 3 momentum signals AND at least 2 volume signals" without requiring every single indicator to be individually flagged in the strategy-wide count.

**UI design requirement:** The strategy builder must support drag-into-group behaviour, group pass thresholds, and a parent strategy threshold that counts groups as its units.

---

## Level 2 — Scoring Strategy Integration

### What it is

The user attaches a saved Scoring Strategy to a Trading Strategy. The scored result becomes a gate: the trade will only trigger if the score meets or exceeds a defined minimum.

### Execution model

```
Trade triggers when:
  current_score >= user_defined_minimum_score
  AND (no other indicators configured, OR all required indicators also pass)
```

### Score maximum awareness

TradePress scores are **not** normalised to 100. The maximum achievable score depends on the weights and directives active in the linked Scoring Strategy. The UI must:

1. Display the maximum possible score for the selected Scoring Strategy next to the minimum threshold input.
2. Warn the user if they set a minimum that is impossible to reach given the current strategy configuration.
3. Treat the scoring strategy's saved suggested threshold as a starting recommendation, not as an inherited hard gate unless the user explicitly enables it in the Trading Strategy.

**Example:** A Scoring Strategy with 4 directives may have a maximum achievable score of 74. Telling the user "minimum score: 60 (max achievable: 74)" is critical context.

### Momentum-assisted wait (optional, advanced)

By default, the trade triggers the moment the score crosses the minimum. For better entry quality, the user can optionally enable a momentum gate:

> "Wait for the score to be rising — do not trigger on the first crossing. Wait until the score has been stable or increasing for N periods before executing."

This uses the direction-of-change of the score over time, not just its absolute value. It reduces entries into a score that briefly spikes and then falls.

**Scope note:** Momentum gating is optional advanced precision. It must be designed now, but is a non-blocking premium candidate for the core release.

---

## Level 3 — Mixed: Scoring Strategy + Hard-Gate Indicators

### What it is

The most complex configuration. A user attaches a Scoring Strategy with a minimum score, and **also** adds individual indicators as hard requirements. A hard-gate indicator is a non-negotiable: no matter what the score is, the trade will not trigger unless that specific indicator passes.

### Execution model

```
Trade triggers when:
  current_score >= minimum_score
  AND hard_gate_indicator_1 passes
  AND hard_gate_indicator_2 passes
  (AND any additional rule-threshold groups pass, if configured)
```

### Practical meaning

The score represents the overall quality of the opportunity. The hard-gate indicators represent conditions that the user considers non-negotiable, regardless of whether other signals compensate.

**Example:**

> Scoring Strategy minimum: 55 (max possible: 74)
> Hard-gate: "Volume must be above 20-day average"
>
> Even if the score reaches 70, if volume is below the 20-day average, no trade executes.
> The volume indicator acts as an absolute blocker regardless of score.

### UI design requirement

When a Scoring Strategy is attached to a Trading Strategy, the user must be able to:

1. Set a minimum score threshold (with max possible score displayed).
2. Add any number of individual hard-gate indicators.
3. Choose whether to enforce the scoring strategy's intended symbol scope for this trading strategy.
4. Optionally enable the momentum gate on the score (Level 2 option).
5. Each hard-gate indicator should be visually distinct from standard rule-threshold indicators to make the execution logic immediately clear.

---

## Decision Flow Summary

```
START: Evaluate Trading Strategy

├── Is a Scoring Strategy attached?
│     YES → Get current score for symbol
│           ├── Enforce scoring strategy symbol scope?
│           │     YES → Symbol inside intended scope?
│           │           NO → DO NOT TRADE
│           │           YES → proceed
│           ├── Score < minimum? → DO NOT TRADE
│           └── Score >= minimum
│                 ├── Momentum gate enabled?
│                 │     YES → Score direction rising/stable for N periods?
│                 │           NO → DO NOT TRADE (wait)
│                 │           YES → proceed
│                 │     NO → proceed
│
├── Are hard-gate indicators configured?
│     YES → ALL hard-gate indicators pass?
│           NO → DO NOT TRADE (absolute block)
│           YES → proceed
│
├── Are rule-threshold indicators or groups configured?
│     YES → Do enough indicators/groups pass the threshold?
│           NO → DO NOT TRADE
│           YES → proceed
│
└── All active gates passed → TRIGGER TRADE
```

---

## Use-Case Scenarios

### Scenario A — Simple rules-only strategy

A beginner user wants automatic alerts when 3 of their 5 watched indicators align. No score involved. They configure 5 indicators, set threshold to 3, and the strategy triggers whenever 3 are acceptable.

### Scenario B — Score-gated automated trading

An intermediate user has tuned a Scoring Strategy with 6 directives producing a max score of 82. They attach it to a Trading Strategy with minimum score 65. They use the strategy as a quality filter: trades only happen when the market conditions are considered at least 79% as good as they can be.

### Scenario C — Score with momentum wait

Same as Scenario B, but the user has been burned by entering on a brief spike. They enable the momentum gate with a 3-period confirmation. The trade now only triggers after the score has been above 65 for at least 3 consecutive evaluation periods.

### Scenario D — Score + non-negotiable volume requirement

Same scoring setup, but the user adds Volume Confirmation as a hard-gate indicator. They know from experience that trades without volume backing often reverse. Even a perfect score will not trigger if volume is not present.

### Scenario E — Full mixed complexity

User builds a 10-indicator strategy with two indicator groups (Momentum group: 3 of 5, Volume group: 2 of 3) plus a Scoring Strategy minimum, plus a single hard-gate indicator for RSI above 50. The trade triggers only when:

1. Momentum group passes (3 of 5 momentum indicators acceptable)
2. Volume group passes (2 of 3 volume indicators acceptable)
3. Score >= minimum (scoring gate clears)
4. RSI > 50 (hard gate passes, regardless of score or group results)

---

## Training Requirements

These systems require structured user education. The following training assets are needed (see roadmap for scheduling):

1. **Plugin Guide — Concepts section:** Define "Scoring" vs "Trading Strategy" in plain language with the manual/automatic framing.
2. **Worked examples:** Scenarios A–E above should become guided walkthroughs in the plugin guide.
3. **Diagrams:** The Decision Flow above should be rendered as a visual flowchart in the admin Diagrams tab.
4. **Interactive pointer training:** Step-by-step in-admin guidance that walks users through building each strategy type using visual pointers (see roadmap).
5. **Video walkthroughs:** Placeholder video embeds in the guide until videos are produced.

---

## Roadmap Dependencies

See ROADMAP.md or GitHub Issues for the following feature tasks that must be built before Level 2 and Level 3 are usable:

- Trading Strategy persistence layer (dedicated tables, not piggy-backed on scoring strategy tables)
- Scoring Strategy score computation on-demand for strategy evaluation
- Hard-gate indicator configuration in strategy builder UI
- Group-based indicator organisation in strategy builder UI
- Score maximum calculation exposed to UI
- Momentum gate implementation and per-strategy toggle
- Training system: pointer-based in-admin guided walkthroughs
