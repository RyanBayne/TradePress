<?php
/**
 * Lesson Content: D3 Bollinger Bands
 */
return [
    'lesson_id' => 'GEN-004',
    'parent_id' => null,
    'title' => 'Introduction to Bollinger Bands',
    'directive_id' => 'D3',
    'module' => 'Module 10: Key Indicator Deep Dive - Volatility & Trend',
    'complexity' => 'Intermediate',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'Bollinger Bands are a versatile tool that helps us understand market volatility. The key mental model for this indicator is "mean reversion". Prices tend to revert to the average over time, and Bollinger Bands help us visualize when prices have moved too far from that average and might be due for a pullback.'
        ],
        [
            'type' => 'header',
            'data' => 'What are Bollinger Bands?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'They consist of three lines: a Simple Moving Average (SMA) in the middle, and an upper and lower band. The bands are typically set at two standard deviations away from the middle SMA. Standard deviation is a measure of volatility, so the bands automatically widen when volatility is high and narrow when volatility is low.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Use Bollinger Bands'
        ],
        [
            'type' => 'subheader',
            'data' => '1. Identifying Overbought/Oversold Conditions'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The primary use is to identify extremes. When the price touches the upper band, it is considered relatively expensive or "overbought" and may be due for a pullback. When it touches the lower band, it is considered relatively cheap or "oversold" and may be due for a bounce.'
        ],
        [
            'type' => 'subheader',
            'data' => '2. Trend Following'
        ],
        [
            'type' => 'paragraph',
            'data' => 'In a strong trend, the price can "walk the band", consistently touching or running along the upper band (in an uptrend) or lower band (in a downtrend). In this case, touching the band is a sign of strength, not a reversal signal.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock is in a sideways, ranging market (not a strong trend). The price has just touched the upper Bollinger Band for the third time in a month, and each previous time it has fallen back to the middle SMA line.',
                '**Question:** Does this signal a high-probability shorting opportunity? What is the primary risk to this mean-reversion strategy?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'In a ranging market, this is a classic mean-reversion setup and presents a high-probability shorting opportunity. The expectation is that the price will revert to the mean (the middle SMA). The primary risk is that this time is different, and the stock breaks out of its range into a strong uptrend. A disciplined trader would enter a short position but place a stop-loss just above the upper band to protect against a breakout.'
        ]
    ]
];
