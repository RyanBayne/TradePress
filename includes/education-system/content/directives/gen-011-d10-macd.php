<?php
/**
 * Lesson Content: D10 MACD
 */
return [
    'lesson_id' => 'GEN-011',
    'parent_id' => 'GEN-008',
    'title' => 'MACD: The Trend and Momentum Indicator',
    'directive_id' => 'D10',
    'module' => 'Module 9: Key Indicator Deep Dive - Momentum',
    'complexity' => 'Intermediate',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'The Moving Average Convergence Divergence (MACD) is a favorite among traders because it combines trend-following and momentum into one powerful indicator. It helps identify the direction and strength of a trend.'
        ],
        [
            'type' => 'header',
            'data' => 'The Components of the MACD'
        ],
        [
            'type' => 'list',
            'data' => [
                '**The MACD Line:** The result of subtracting the 26-period EMA from the 12-period EMA. It measures short-term momentum.',
                '**The Signal Line:** A 9-period EMA of the MACD line. It acts as a slower, smoother version of the MACD line.',
                '**The Histogram:** The bars on the indicator, which represent the distance between the MACD line and the Signal line.'
            ]
        ],
        [
            'type' => 'header',
            'data' => 'How to Use the MACD'
        ],
        [
            'type' => 'subheader',
            'data' => '1. Crossovers'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The most common signal is the crossover. When the MACD line crosses above the Signal line, it is a bullish signal. When it crosses below, it is a bearish signal. The histogram moving from negative to positive (or vice-versa) visualizes this event.'
        ],
        [
            'type' => 'subheader',
            'data' => '2. The Zero Line'
        ],
        [
            'type' => 'paragraph',
            'data' => 'When the MACD line is above the zero line, it indicates that the short-term average is above the long-term average, suggesting positive upward momentum. When it is below zero, it suggests downward momentum. A bullish crossover that occurs while the MACD is already above the zero line is a particularly strong confirmation of an uptrend.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock is in a strong uptrend, with the MACD line well above the zero line. The stock pulls back for a few days, and you see the MACD line dip down and briefly cross below its Signal line. A few days later, it crosses back above the Signal line.',
                '**Question:** What does this crossover signal, and why is its location (above the zero line) significant?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'This is a bullish MACD crossover that acts as a buy signal for a trend-continuation trade. Its location is highly significant. Because it occurred while the MACD was above the zero line, it indicates that the overall long-term momentum remains positive. This is not a reversal signal from a downtrend, but rather a signal that a brief pullback is over and the primary uptrend is likely to resume.'
        ]
    ]
];
