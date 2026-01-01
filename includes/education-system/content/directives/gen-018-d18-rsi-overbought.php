<?php
/**
 * Lesson Content: D18 RSI Overbought
 */
return [
    'lesson_id' => 'GEN-018',
    'parent_id' => 'GEN-017',
    'title' => 'Signal: RSI Overbought',
    'directive_id' => 'D18',
    'module' => 'Module 9: Key Indicator Deep Dive - Momentum',
    'complexity' => 'Intermediate',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'This lesson focuses on a specific signal from the RSI: the "Overbought" reading. Understanding this signal is key to managing risk and timing exits.'
        ],
        [
            'type' => 'header',
            'data' => 'What Does Overbought Mean?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'An overbought reading (typically RSI > 70) does not automatically mean "sell". It simply means that the stock has experienced a rapid price increase and its upward momentum may be getting exhausted. It is a warning sign that the trend might be due for a pause or a pullback.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Use the Overbought Signal'
        ],
        [
            'type' => 'list',
            'data' => [
                '**For Taking Profits:** If you are already in a long position, an overbought RSI can be a good signal to consider taking some profits off the table, especially if it coincides with a resistance level.',
                '**For Initiating a Short:** Shorting a stock just because it is overbought is extremely risky, as strong stocks can stay overbought for a long time. A disciplined trader waits for the RSI to cross back down below 70, and for price to show signs of weakness, before considering a short position.'
            ]
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock you hold has just broken out to new all-time highs. The move is very strong, and the RSI is now at 85. You are tempted to sell and lock in your profit, but you are also worried you might miss out on more gains.',
                '**Question:** What is a disciplined, non-emotional way to manage this position, using the overbought RSI signal?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'A disciplined approach would be to use a scaling-out strategy. You could sell a portion of your position (e.g., 1/3 or 1/2) to lock in some profit, acknowledging the overbought reading. Then, you can hold the rest of the position with a trailing stop-loss to continue participating in any further upside. This approach balances the fear of giving back profits with the fear of missing out on more gains.'
        ]
    ]
];
