<?php
/**
 * Lesson Content: D20 Stochastic Oscillator
 */
return [
    'lesson_id' => 'GEN-020',
    'parent_id' => null,
    'title' => 'Stochastic: Pinpointing Trend Exhaustion',
    'directive_id' => 'D20',
    'module' => 'Module 9: Key Indicator Deep Dive - Momentum',
    'complexity' => 'Intermediate',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'The Stochastic Oscillator is a momentum indicator that compares a particular closing price of a stock to a range of its prices over a certain period of time. The key mental model is that in an uptrend, prices tend to close near their highs, and in a downtrend, they tend to close near their lows. When this pattern starts to falter, the Stochastic can signal a potential change.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Read the Stochastic Oscillator'
        ],
        [
            'type' => 'paragraph',
            'data' => 'It consists of two lines, %K (the faster line) and %D (the slower line), and is measured on a scale of 0-100. The key levels are 80 (overbought) and 20 (oversold).'
        ],
        [
            'type' => 'list',
            'data' => [
                '**Bullish Signal:** When both lines are below 20 and the faster %K line crosses above the slower %D line, it signals that the downward momentum is fading.',
                '**Bearish Signal:** When both lines are above 80 and the %K line crosses below the %D line, it signals that the upward momentum is fading.'
            ]
        ],
        [
            'type' => 'header',
            'data' => 'Stochastic vs. RSI'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The Stochastic is generally faster and more sensitive than the RSI, providing more signals. This makes it popular with day traders but also more prone to generating false signals in choppy markets. It is often best used in ranging markets rather than strong trending markets.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock is in a powerful, sustained uptrend, rising day after day. You notice the Stochastic Oscillator has been above the 80 level for three straight weeks.',
                '**Question:** Does this mean the stock is a good shorting candidate? Why is the Stochastic (and RSI) less reliable in this specific market condition?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'No, this is a poor shorting candidate. This is the classic mistake traders make with oscillators. In a strongly trending market, these indicators can become "embedded" and stay in the overbought (or oversold) territory for extended periods. In this context, the overbought reading is actually a sign of trend strength, not a reversal. Oscillators like the Stochastic are most effective for identifying pullbacks or reversals when a security is in a sideways range, not a powerful trend.'
        ]
    ]
];
