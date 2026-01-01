<?php
/**
 * Lesson Content: D24 Volume Weighted Average Price (VWAP)
 */
return [
    'lesson_id' => 'GEN-024',
    'parent_id' => 'GEN-022',
    'title' => 'VWAP: The Institutional Benchmark',
    'directive_id' => 'D24',
    'module' => 'Module 10: Key Indicator Deep Dive - Volatility & Trend',
    'complexity' => 'Advanced',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'The Volume Weighted Average Price (VWAP) is a benchmark used primarily by day traders and institutional investors. It represents the true average price of a stock over a given period, taking into account the volume of shares traded at each specific price point.'
        ],
        [
            'type' => 'header',
            'data' => 'How is VWAP Different from a Moving Average?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'A simple moving average is based only on price. VWAP is based on both price and volume. This means that price levels where a high volume of trading occurred have a greater impact on the VWAP line. It is typically used on intraday charts (like the 1-minute or 5-minute) and resets at the beginning of each trading day.'
        ],
        [
            'type' => 'header',
            'data' => 'How Traders Use VWAP'
        ],
        [
            'type' => 'list',
            'data' => [
                '**As a Benchmark:** Institutions often judge their trade executions by whether they bought below the VWAP or sold above it. For this reason, VWAP acts as a magnet for price.',
                '**As Support/Resistance:** Like a moving average, the VWAP line can act as a dynamic level of support or resistance during the trading day.',
                '**As a Trend Filter:** Many day traders will only take long trades when the price is above the VWAP and short trades when the price is below it.'
            ]
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** It is midday. A stock you are watching has been in a strong uptrend all morning and is trading well above its VWAP line. It begins to pull back.',
                '**Question:** As a day trader looking to buy this stock, where is the most logical place to look for a potential entry?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The most logical place to look for an entry is at or near the VWAP line. Because so many institutional and algorithmic traders use VWAP as a benchmark for value, there is often a concentration of buying interest at that level. A pullback to the VWAP in a stock that is in a clear intraday uptrend represents a classic, high-probability "buy the dip" setup for a day trader.'
        ]
    ]
];
