<?php
/**
 * Lesson Content: D17 Relative Strength Index (RSI)
 */
return [
    'lesson_id' => 'GEN-017',
    'parent_id' => null,
    'title' => 'RSI: Measuring Momentum and Extremes',
    'directive_id' => 'D17',
    'module' => 'Module 9: Key Indicator Deep Dive - Momentum',
    'complexity' => 'Intermediate',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'The Relative Strength Index (RSI) is one of the most popular momentum oscillators. Its primary purpose is to identify overbought and oversold conditions, helping traders spot potential price reversals.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Read the RSI'
        ],
        [
            'type' => 'paragraph',
            'data' => 'RSI is measured on a scale of 0 to 100. The key levels to watch are 70 and 30.'
        ],
        [
            'type' => 'list',
            'data' => [
                '**Overbought (RSI > 70):** When the RSI moves above 70, the stock is considered to be overbought. This means it has risen quickly and may be due for a pullback or consolidation.',
                '**Oversold (RSI < 30):** When the RSI moves below 30, the stock is considered to be oversold. This means it has fallen quickly and may be due for a bounce.'
            ]
        ],
        [
            'type' => 'header',
            'data' => 'The Mental Model: A Rubber Band'
        ],
        [
            'type' => 'paragraph',
            'data' => 'Think of the price as being attached to a rubber band. The RSI tells you how far that rubber band has been stretched. An overbought reading means the band is stretched to the upside; an oversold reading means it is stretched to the downside. Eventually, the band is likely to snap back toward the middle.'
        ],
        [
            'type' => 'paragraph',
            'data' => '**Crucial Caveat:** In a very strong trend, a stock can remain overbought or oversold for a long time. RSI is most effective in ranging or choppy markets. Using it to short a stock in a powerful uptrend is a dangerous strategy.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock has been falling for a week and its RSI has just dropped to 25. You are thinking of buying it, expecting a bounce.',
                '**Question:** Is the RSI reading of 25 a complete buy signal on its own? What other factor would give you much more confidence in this trade?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'No, the RSI reading alone is not a complete signal. It only tells you the stock has fallen fast. To add confidence, you should look for confirmation from price action. For example, if the stock is also at a major, known support level (like a previous low or a long-term moving average), the combination of being oversold AND at a support level makes for a much higher-probability trade. An indicator signal combined with a price action signal is always more powerful.'
        ]
    ]
];
