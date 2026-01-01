<?php
/**
 * Lesson Content: D9 Moving Average Crossover
 */
return [
    'lesson_id' => 'GEN-010',
    'parent_id' => 'GEN-008',
    'title' => 'Strategy: The MA Crossover',
    'directive_id' => 'D9',
    'module' => 'Module 6: Introduction to Technical Analysis',
    'complexity' => 'Intermediate',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'The Moving Average Crossover is a classic trend-following strategy. It uses two moving averages—a short-period one and a long-period one—to generate buy and sell signals. The core idea is that when the faster average crosses above the slower one, it signals a potential shift to an uptrend, and vice-versa.'
        ],
        [
            'type' => 'header',
            'data' => 'The Golden Cross and the Death Cross'
        ],
        [
            'type' => 'paragraph',
            'data' => 'These are the two most famous crossover patterns, typically used by long-term investors and funds on daily charts:'
        ],
        [
            'type' => 'list',
            'data' => [
                '**The Golden Cross:** Occurs when the 50-day moving average crosses ABOVE the 200-day moving average. This is widely seen as a strong, long-term bullish signal.',
                '**The Death Cross:** Occurs when the 50-day moving average crosses BELOW the 200-day moving average. This is seen as a strong, long-term bearish signal.'
            ]
        ],
        [
            'type' => 'header',
            'data' => 'Mental Model: The Lagging Nature of Crossovers'
        ],
        [
            'type' => 'paragraph',
            'data' => 'It is crucial to understand that crossover strategies are **lagging**. The cross happens long after the new trend has already begun. The purpose of this strategy is not to catch the exact bottom or top, but to confirm that a significant, durable trend change has likely occurred. It prioritizes being right over being first.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock has been in a downtrend for a year. It bottoms out and starts to recover. The price rises 20% from its lows. You have been waiting on the sidelines. Today, you see that the 50-day MA has finally crossed above the 200-day MA, confirming a Golden Cross.',
                '**Question:** Have you missed the move? What is the psychological benefit of waiting for the Golden Cross instead of trying to buy at the exact bottom?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'No, you have not necessarily \'missed\' the move. While you didn\'t buy at the absolute cheapest price, the Golden Cross provides confirmation that the new uptrend is likely real and sustainable. The psychological benefit is immense: you are trading with the confirmed long-term trend, not trying to catch a falling knife. This increases the probability that your trade will be successful, even if it means sacrificing some initial profit for a much higher degree of certainty.'
        ]
    ]
];
