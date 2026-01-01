<?php
/**
 * Lesson Content: D12 Money Flow Index (MFI)
 */
return [
    'lesson_id' => 'GEN-013',
    'parent_id' => 'GEN-017',
    'title' => 'MFI: The Volume-Weighted RSI',
    'directive_id' => 'D12',
    'module' => 'Module 9: Key Indicator Deep Dive - Momentum',
    'complexity' => 'Advanced',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'The Money Flow Index (MFI) is often called the volume-weighted RSI. While the RSI only looks at price, the MFI incorporates both price and volume data. This can provide a more robust signal, as it measures the amount of money (volume) flowing into or out of a stock.'
        ],
        [
            'type' => 'header',
            'data' => 'How MFI Differs from RSI'
        ],
        [
            'type' => 'paragraph',
            'data' => 'By including volume, MFI can help confirm a trend. If a stock is rising and MFI is also rising, it shows that the move is supported by significant buying pressure (money flowing in). If the price is rising but MFI is falling, it suggests the move is on low volume and may not be sustainable. This is a bearish divergence.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Use MFI'
        ],
        [
            'type' => 'list',
            'data' => [
                '**Overbought/Oversold:** Like RSI, MFI is an oscillator with a 0-100 range. Readings above 80 are considered overbought, and readings below 20 are considered oversold.',
                '**Divergence:** This is the most powerful signal from the MFI. A bearish divergence (higher price, lower MFI) warns of a potential top. A bullish divergence (lower price, higher MFI) suggests a potential bottom.'
            ]
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock has just dropped to a new 52-week low, and there is panic in the market. You look at the RSI, and it is also at a new low. However, you look at the MFI and notice that it has formed a higher low, meaning it did not confirm the new low in price.',
                '**Question:** What does the MFI\'s failure to make a new low indicate? Why might it be showing a different picture than the RSI?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        'type' => 'paragraph',
        'data' => 'This is a bullish divergence and a powerful one. The MFI is showing a different picture because it includes volume. This divergence suggests that while the price dropped, the selling was not on heavy volume. It indicates that the selling pressure is exhausting, and "smart money" may be quietly accumulating shares at these low prices. It\'s a strong signal that a bottom may be forming.'
    ]
];
