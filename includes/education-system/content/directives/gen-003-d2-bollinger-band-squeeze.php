<?php
/**
 * Lesson Content: D2 Bollinger Band Squeeze
 */
return [
    'lesson_id' => 'GEN-003',
    'parent_id' => 'GEN-004',
    'title' => 'Advanced Strategy: The Bollinger Band Squeeze',
    'directive_id' => 'D2',
    'module' => 'Module 10: Key Indicator Deep Dive - Volatility & Trend',
    'complexity' => 'Advanced',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'This lesson covers an advanced strategy derived from Bollinger Bands. It assumes you understand the basics of Bollinger Bands from lesson GEN-004. The Bollinger Band Squeeze is a powerful signal that volatility is about to increase dramatically. It is often called "the calm before the storm".'
        ],
        [
            'type' => 'header',
            'data' => 'What is a Bollinger Band Squeeze?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'A squeeze occurs when volatility falls to a very low level, causing the upper and lower bands to move closer together, "squeezing" the price action. This period of low volatility is often followed by a period of high volatility and a significant price move in either direction.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Identify a Squeeze'
        ],
        [
            'type' => 'paragraph',
            'data' => 'You can identify a squeeze visually by noticing the bands are the narrowest they have been in a long time. Some traders use an additional indicator called Bollinger Band Width to measure the distance between the bands numerically, but a visual check is often sufficient.'
        ],
        [
            'type' => 'header',
            'data' => 'Trading the Breakout'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The squeeze itself does not predict the direction of the breakout. It only signals that a breakout is likely. Traders must wait for the price to break decisively above the upper band (for a long trade) or below the lower band (for a short trade), often confirmed by a surge in volume.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock you are watching has been trading sideways for 3 weeks. You apply Bollinger Bands and notice the bands are extremely narrow, almost flat. The price is coiling in a tight range. You are anticipating a big move, but you are unsure of the direction.',
                '**Question:** What is the correct trading plan here? Do you buy or sell in anticipation? What is the specific event you are waiting for?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The correct plan is to remain neutral and patient. Do not try to guess the direction. Your plan should be to place alerts just outside the upper and lower bands. You are waiting for a candle to close decisively outside the bands, accompanied by a noticeable increase in volume. That is your signal to enter a trade in the direction of the breakout, with a stop-loss placed just inside the breakout zone.'
        ]
    ]
];
