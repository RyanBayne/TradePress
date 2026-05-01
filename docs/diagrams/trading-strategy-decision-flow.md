# Trading Strategy Decision Flow — Diagram Source

## Purpose

This file contains Mermaid diagram source for the Trading Strategy decision flow. It is the source of truth for the Diagrams tab until video walkthroughs are produced.

---

## Full Decision Flow

```mermaid
flowchart TD
    START([Evaluate Trading Strategy\nfor Symbol]) --> HAS_GROUPS{Rule-threshold\ngroups configured?}

    HAS_GROUPS -- Yes --> CHECK_GROUPS[Evaluate each group\nagainst its own pass threshold]
    CHECK_GROUPS --> GROUPS_PASS{All required\ngroups passed?}
    GROUPS_PASS -- No --> NO_TRADE_GROUPS([DO NOT TRADE\nGroup threshold not met])
    GROUPS_PASS -- Yes --> HAS_SCORE

    HAS_GROUPS -- No --> HAS_SCORE{Scoring Strategy\nattached?}

    HAS_SCORE -- Yes --> COMPUTE[Compute current score\nusing linked Scoring Strategy]
    COMPUTE --> SCORE_MET{Score >= minimum\nrequired?}
    SCORE_MET -- No --> NO_TRADE_SCORE([DO NOT TRADE\nScore below minimum])
    SCORE_MET -- Yes --> MOMENTUM_GATE{Momentum gate\nenabled?}
    MOMENTUM_GATE -- Yes --> SCORE_SUSTAINED{Score sustained for\nrequired N periods?}
    SCORE_SUSTAINED -- No --> NO_TRADE_MOMENTUM([DO NOT TRADE\nScore not yet sustained\nwait for confirmation])
    SCORE_SUSTAINED -- Yes --> HAS_HARD_GATES
    MOMENTUM_GATE -- No --> HAS_HARD_GATES

    HAS_SCORE -- No --> HAS_HARD_GATES{Hard-gate indicators\nconfigured?}

    HAS_HARD_GATES -- Yes --> CHECK_HARD[Evaluate each\nhard-gate indicator]
    CHECK_HARD --> HARD_PASS{ALL hard-gate\nindicators passed?}
    HARD_PASS -- No --> NO_TRADE_HARD([DO NOT TRADE\nHard gate blocked\nnon-negotiable condition failed])
    HARD_PASS -- Yes --> TRADE

    HAS_HARD_GATES -- No --> TRADE([TRIGGER TRADE\nAll active conditions passed])

    style NO_TRADE_GROUPS fill:#c0392b,color:#fff
    style NO_TRADE_SCORE fill:#c0392b,color:#fff
    style NO_TRADE_MOMENTUM fill:#e67e22,color:#fff
    style NO_TRADE_HARD fill:#c0392b,color:#fff
    style TRADE fill:#27ae60,color:#fff
    style START fill:#2980b9,color:#fff
```

---

## Simplified overview (for intro training)

```mermaid
flowchart LR
    MANUAL[Manual Trading\nDecision] -->|Uses| SCORING[Scoring Directives\nRanks quality\nProduces a score]
    AUTO[Automated Trading\nDecision] -->|Uses| STRATEGY[Trading Strategy\nRule-threshold trigger\nEnough conditions met?]
    SCORING -->|Can feed into| STRATEGY
    STRATEGY -->|Optionally requires| HARDGATE[Hard-gate Indicators\nAbsolute requirements\nBlock regardless of score]
```

---

## Group structure diagram (for grouped indicators training)

```mermaid
flowchart TD
    STRATEGY[Trading Strategy\nRequires 2 of 3 groups to pass]
    STRATEGY --> GA[Group A — Momentum\nRequires 3 of 5]
    STRATEGY --> GB[Group B — Volume\nRequires 2 of 3]
    STRATEGY --> GC[Group C — Trend\nRequires 2 of 2]

    GA --> A1[RSI]
    GA --> A2[MACD]
    GA --> A3[Stochastic]
    GA --> A4[Williams %R]
    GA --> A5[CCI]

    GB --> B1[Volume Spike]
    GB --> B2[OBV]
    GB --> B3[Money Flow Index]

    GC --> C1[MA Crossover]
    GC --> C2[ADX]
```

---

## Score + hard gate combination (for Level 3 training)

```mermaid
flowchart TD
    EVAL[Evaluate symbol] --> SCORE_CHECK{Score >= 55?\nMax possible: 82}
    SCORE_CHECK -- No --> BLOCKED1([Blocked — score too low])
    SCORE_CHECK -- Yes --> HARD_CHECK{Volume > 20-day avg?}
    HARD_CHECK -- No --> BLOCKED2([Blocked — hard gate failed\nno exceptions])
    HARD_CHECK -- Yes --> TRIGGER([Trade triggered])

    style BLOCKED1 fill:#c0392b,color:#fff
    style BLOCKED2 fill:#c0392b,color:#fff
    style TRIGGER fill:#27ae60,color:#fff
```

---

## Usage note

These diagrams are intended to be rendered in the admin Diagrams tab using the existing Mermaid render pipeline. When interactive training is implemented (pointer-guided walkthroughs), these diagrams will become reference visuals that the pointer system links back to at each step.
