# Trading Strategy — Training Scenarios

## Purpose

This document is the source material for user training. Each scenario demonstrates a real-world use case for TradePress trading strategies, presented in plain language before introducing any technical detail. These scenarios are intended to become:

- Written walkthrough guides in the plugin guide
- Worked examples in the admin training section
- Scripts for video walkthroughs
- Interactive pointer-guided tours in the admin UI (see roadmap)

---

## Before you start: Two systems, two purposes

TradePress gives you two separate systems for evaluating trades. Understanding the difference is important before building anything.

**Scoring Directives** help you understand the *quality* of a trade opportunity. They produce a score. A high score means conditions are as good as they can be right now. You can use this score to make manual decisions — "I'll only consider trades with a score above 60 today."

**Trading Strategies** help you automate action. They evaluate a set of rules and decide whether to trigger a trade. A trading strategy does not require a perfect score; it requires enough conditions to be acceptable. You define what "enough" means.

You can use them separately, or combine them.

---

## Scenario A — The Basics: A rules-only strategy

**Who this is for:** Someone who wants simple automated signals without worrying about scoring.

**The situation:** You follow 5 technical indicators regularly. You do not need all 5 to agree before you trade — you just need the majority to be pointing the right way.

**What you build:**
- A Trading Strategy with 5 indicators: RSI, MACD, Volume Spike, Moving Average Crossover, Bollinger Band position.
- Threshold: **3 of 5 must pass**.

**How it behaves:**
When TradePress evaluates this strategy, it checks each indicator. If 3 or more are in an acceptable state, the strategy triggers.

**When this is useful:** Quick market entries where you trust your indicators but do not need everything aligned perfectly. Good for active traders who want speed over precision.

**What to watch out for:** Lower thresholds increase frequency of signals. A 3-of-5 rule will trigger more often than a 4-of-5 rule. More signals is not always better — some will be weaker quality.

---

## Scenario B — Grouped indicators: Requiring different types of confirmation

**Who this is for:** Someone who wants signals that confirm across multiple categories, not just any 3 of 5.

**The problem with Scenario A:** In Scenario A, it is technically possible for 3 momentum indicators to pass while both volume indicators fail. You would still get a trigger — but you are trading on momentum alone without volume backing. Some traders consider that risky.

**The situation:** You want at least 2 momentum signals AND at least 1 volume signal before entering a trade.

**What you build:**
- A Trading Strategy with two groups.
- **Group A — Momentum** (requires 2 of 3): RSI, MACD, Stochastic.
- **Group B — Volume** (requires 1 of 2): Volume Spike, OBV.
- Strategy threshold: **Both groups must pass**.

**How it behaves:**
The strategy checks Group A and Group B separately. Group A passes if 2 of 3 momentum indicators are acceptable. Group B passes if 1 of 2 volume indicators is acceptable. The trade triggers only when both groups pass.

**What this gives you:** Confirmation that you have both momentum and volume supporting the move, not just one type of signal dominating.

**Extending this further:** You can add a third group (for example Trend confirmation) and require 2 of 3 groups to pass overall. This gives you even broader required agreement.

---

## Scenario C — Adding a score gate: Better quality thresholds

**Who this is for:** Someone who already has a tuned Scoring Strategy and wants to use it to filter trade triggers.

**The situation:** You have spent time configuring a Scoring Strategy with 6 directives. The maximum achievable score for your configuration is 82. You have noticed that trades where the score was below 55 tend to perform worse. You want to block low-quality triggers.

**What you build:**
- A Trading Strategy with your scoring strategy attached.
- Minimum score: **55** (with the system displaying: *Max achievable: 82*).

**How it behaves:**
When TradePress evaluates the symbol, it first computes the current score using your Scoring Strategy. If the score is below 55, the trade does not trigger, regardless of anything else. If the score is 55 or above, the trade may proceed.

**What to understand about max possible score:** TradePress does not normalise scores to 100. The maximum depends on your Scoring Strategy's weights and active directives. If your maximum is 82, a score of 55 represents roughly 67% of the best possible conditions. Knowing the maximum is critical when setting your minimum.

**When this is useful:** You want automated trading but with quality filtering. The strategy will only trigger when market conditions are genuinely above a baseline you have decided is acceptable.

---

## Scenario D — Momentum-assisted waiting: Don't enter on a spike

**Who this is for:** An intermediate user who has been burned by entering on a brief score spike that quickly reversed.

**The situation:** You are using the score gate from Scenario C. The score sometimes crosses your minimum of 55 briefly — for one evaluation period — and then drops back. You have entered on those brief crossings and regretted it. You want the score to be sustained before you act.

**What you build:**
- Same as Scenario C.
- Additionally: enable the **momentum gate** with a confirmation window of **3 periods**.

**How it behaves:**
The score must reach 55 AND remain at or above 55 for 3 consecutive evaluation periods. A single crossing does not trigger. The trade triggers only after the condition has been stable for the required number of periods.

**What this gives you:** Confidence that the score is not a transient spike but a sustained improvement in conditions. You will miss some fast-moving opportunities, but you will also avoid more false entries.

**Trade-off:** Momentum gating introduces delay. In very fast-moving markets, this may mean the best entry has already passed by the time the condition is confirmed. This is an advanced control — use it when you have evidence that brief crossings have cost you.

---

## Scenario E — Hard-gate indicators: Non-negotiable conditions

**Who this is for:** Someone who has a condition they absolutely require regardless of what the score says.

**The situation:** You use a score gate and it works well. But you have one condition that you consider non-negotiable: volume must be above the 20-day average. You have experienced trades where the score was high but volume was absent — the moves were thin and reversed quickly. You want volume to be a hard requirement, not just one of many weighted inputs.

**What you build:**
- A Trading Strategy with a scoring strategy attached (minimum score 55, max 82).
- **Hard-gate indicator:** Volume Confirmation (volume above 20-day average).

**How it behaves:**
TradePress evaluates the score first. If the score meets the minimum, it then checks the hard-gate indicator. If volume is not confirmed, the trade does not trigger — regardless of how high the score is. A score of 80 with no volume confirmation will be blocked.

**What this gives you:** An absolute safeguard for conditions you have decided are dealbreakers. The score optimises entry quality across many variables; the hard gate protects against the specific failure mode you most want to avoid.

**Extending this further:** You can add multiple hard-gate indicators. Each one is an absolute requirement. Think of them as conditions you would never trade without, no matter how good everything else looks.

---

## Scenario F — Full complexity: Combining everything

**Who this is for:** An advanced user who wants the most complete protection before a trade executes.

**The situation:** You want automated trading but with layered safeguards: grouped rule-threshold conditions, a minimum quality score, a momentum confirmation, and one absolute requirement.

**What you build:**
A 10-indicator strategy with:

- **Group A — Momentum** (requires 3 of 5): RSI, MACD, Stochastic, Williams %R, CCI
- **Group B — Volume** (requires 2 of 3): Volume Spike, OBV, Money Flow Index
- Strategy threshold: both groups must pass.
- **Scoring Strategy attached** — minimum score 60 (max achievable 82).
- **Momentum gate** — 2-period confirmation.
- **Hard-gate indicator** — RSI above 50 (must pass, no exceptions).

**How it behaves:**
All of the following must be true before a trade triggers:

1. Group A: 3 of 5 momentum indicators are in acceptable state.
2. Group B: 2 of 3 volume indicators are in acceptable state.
3. Current score is 60 or above for at least 2 consecutive periods.
4. RSI is above 50 right now.

Any single condition failing blocks the trade.

**When this is useful:** When you want maximum confidence before any automated action. The trade-off is frequency — highly layered strategies will trigger less often. But when they do trigger, the convergence of conditions gives you the strongest possible signal that multiple independent systems agree the conditions are right.

---

## Reading the Decision Flow

The following describes the evaluation order. A detailed visual diagram of this flow is available in the admin Diagrams tab.

```
For each symbol being evaluated:

1. Are rule-threshold groups configured?
   If yes → each group must meet its own pass threshold
   If any group fails → DO NOT TRADE

2. Is a Scoring Strategy attached?
   If yes → compute current score
   If score < minimum → DO NOT TRADE
   If momentum gate is enabled → score must be sustained for N periods
   If not sustained → DO NOT TRADE

3. Are hard-gate indicators configured?
   If yes → each hard-gate indicator must pass
   If any fails → DO NOT TRADE (absolute block, no exceptions)

4. All active conditions passed → TRIGGER TRADE
```

---

## Choosing your complexity level

| Your situation | Start with |
|---|---|
| New to automated trading, want simple signals | Scenario A — rules-only |
| Want to ensure different signal types agree | Scenario B — grouped indicators |
| Already use scoring, want quality filtering | Scenario C — score gate |
| Been burned by brief spikes | Scenario D — momentum gate |
| Have a condition you will never trade without | Scenario E — hard-gate |
| Want maximum convergence before triggering | Scenario F — full complexity |

You can always start simple and layer complexity over time. Trading Strategies are versioned and saved — you can adjust thresholds and gates as you learn how your configured strategy behaves in practice.
