<?php
/**
 * Lesson Content: D4 Commodity Channel Index (CCI)
 */
return [
    'lesson_id' => 'GEN-005',
    'parent_id' => null,
    'title' => 'Trading Cycles with the CCI',
    'directive_id' => 'D4',
    'module' => 'Module 9: Key Indicator Deep Dive - Momentum',
    'complexity' => 'Intermediate',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'The Commodity Channel Index (CCI) is a momentum-based oscillator used to identify cyclical trends. Don\'t let the name fool you; while it was originally designed for commodities, it is highly effective in stocks and other markets that exhibit cyclical behavior.'
        ],
        [
            'type' => 'header',
            'data' => 'What is the CCI?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The CCI measures the current price level relative to an average price level over a specific period. It is an unbounded oscillator, meaning it has no upper or lower limit, but it is typically viewed in relation to the +100 and -100 levels.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Use the CCI'
        ],
        [
            'type' => 'list',
            'data' => [
                '**Identifying New Trends:** A move above +100 can signal the start of a strong uptrend. A move below -100 can signal the start of a strong downtrend.',
                '**Overbought and Oversold:** Readings above +100 are considered overbought, and readings below -100 are considered oversold. This can be used for mean-reversion strategies, especially in ranging markets.',
                '**Divergence:** If the price is making a new high but the CCI fails to make a new high, it indicates a bearish divergence and weakening momentum. The opposite is true for bullish divergence.'
            ]
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A cyclical stock like a homebuilder has been in a downtrend for months. You notice the price has just made a new low, but the CCI, while still below -100, has formed a higher low than it did on the previous price dip.',
                '**Question:** What is this phenomenon called, and what does it suggest about the current downtrend?'
            ]
        ],
        [
            'type's' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'This is a classic bullish divergence. It suggests that the downward momentum is fading. Even though the price made a new low, the selling pressure was not as strong as before. This is not a signal to buy immediately, but it is a strong warning sign for short-sellers and a heads-up for buyers that the trend may be preparing to reverse.'
        ]
    ]
];
