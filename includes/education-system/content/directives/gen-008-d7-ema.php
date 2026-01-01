<?php
/**
 * Lesson Content: D7 Exponential Moving Average (EMA)
 */
return [
    'lesson_id' => 'GEN-008',
    'parent_id' => 'GEN-001',
    'title' => 'EMA: The Faster Moving Average',
    'directive_id' => 'D7',
    'module' => 'Module 6: Introduction to Technical Analysis',
    'complexity' => 'Beginner',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'This lesson builds on our knowledge of the Simple Moving Average (SMA). The Exponential Moving Average (EMA) is another type of moving average, but it has a key difference: it places more weight on recent prices, making it react more quickly to new information.'
        ],
        [
            'type' => 'header',
            'data' => 'SMA vs. EMA: What\'s the Difference?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'Imagine an SMA as a group photo where everyone has equal importance. An EMA is like a group photo where the people in the front row are in sharper focus. The most recent trading days have a greater impact on the average, so the EMA line "hugs" the price action more closely than an SMA of the same period.'
        ],
        [
            'type' => 'header',
            'data' => 'Why Use an EMA?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'Because of its faster reaction time, the EMA is favored by short-term traders, such as swing traders and day traders. It can provide earlier signals of a trend change or momentum shift. However, this sensitivity also means it can be more prone to giving false signals in choppy, sideways markets.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** You are a swing trader looking to catch short-term trends. You see a stock starting to move up strongly. You want to use a moving average to help you decide on an entry point on a pullback.',
                '**Question:** Would an EMA or an SMA be more suitable for your trading style, and why?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'For a short-term swing trader, an EMA (such as the 9-period or 21-period EMA) would be more suitable. Because the EMA reacts more quickly to price, it will provide a more responsive level of dynamic support for a fast-moving stock. An SMA might be too slow, and the price might not pull back far enough to meet it before continuing its trend.'
        ]
    ]
];
