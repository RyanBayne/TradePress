<?php
/**
 * Lesson Content: D15 On-Balance Volume (OBV)
 */
return [
    'lesson_id' => 'GEN-015',
    'parent_id' => null,
    'title' => 'OBV: Tracking Smart Money',
    'directive_id' => 'D15',
    'module' => 'Module 10: Key Indicator Deep Dive - Volatility & Trend',
    'complexity' => 'Advanced',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'On-Balance Volume (OBV) is a simple but powerful cumulative indicator that uses volume flow to predict changes in stock price. The core idea is that volume precedes price, and OBV can help you see what the "smart money" is doing.'
        ],
        [
            'type' => 'header',
            'data' => 'How OBV is Calculated'
        ],
        [
            'type' => 'paragraph',
            'data' => 'OBV is a running total. If the price closes higher today than yesterday, all of today\'s volume is added to the OBV. If the price closes lower, all of today\'s volume is subtracted. The actual value of the OBV isn\'t important; its direction is.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Use OBV'
        ],
        [
            'type' => 'subheader',
            'data' => '1. Trend Confirmation'
        ],
        [
            'type' => 'paragraph',
            'data' => 'If the price is making new highs and the OBV is also making new highs, the trend is confirmed by volume and is considered strong. If the price is rising but the OBV is flat or falling, it suggests a lack of buying pressure, and the trend may be weak.'
        ],
        [
            'type' => 'subheader',
            'data' => '2. Divergence'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The most powerful OBV signal is divergence. A bearish divergence occurs when the price makes a new high, but the OBV fails to make a new high. This warns that volume is not supporting the new high and a reversal may be coming. A bullish divergence is the opposite.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A stock has been in a range for months, trading sideways. During this time, you notice that the price is flat, but the OBV line has been steadily climbing higher.',
                '**Question:** What does the rising OBV during a period of flat price indicate? What might this be a sign of?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'This is a classic sign of **accumulation**. It suggests that large, institutional investors ("smart money") are quietly buying shares. On up days, the volume is slightly higher, and on down days, the volume is slightly lower, causing the OBV to trend up over time. This is often a precursor to a strong bullish breakout, as it shows that big players are building a position before the price starts to move.'
        ]
    ]
];
