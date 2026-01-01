<?php
/**
 * Lesson Content: Introduction to the Average Directional Index (ADX)
 */
return [
    // -- METADATA --
    'lesson_id' => 'GEN-002',
    'parent_id' => null,
    'title' => 'Introduction to the Average Directional Index (ADX)',
    'directive_id' => 'D1',
    'module' => 'Module 10: Key Indicator Deep Dive - Volatility & Trend',
    'complexity' => 'Intermediate',
    'status' => 'Drafting',

    // -- LESSON CONTENT --
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'Let\'s explore a unique and powerful indicator: the ADX. Before we start, it\'s crucial to adjust your mindset. Most indicators try to tell you which direction the price is going. The ADX does not. Its only job is to measure the **strength** of the trend. Your mental model for ADX should be a "trend-o-meter". Is the trend powerful and healthy, or is it weak and running out of steam? This is the only question ADX answers.'
        ],
        [
            'type' => 'header',
            'data' => 'What is the Average Directional Index (ADX)?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The ADX is a trend strength indicator composed of three lines: the main ADX line, the Positive Directional Indicator (+DI), and the Negative Directional Indicator (-DI). Together, they help traders gauge both the strength and direction of a market trend.'
        ],
        [
            'type' => 'header',
            'data' => 'How to Read the ADX'
        ],
        [
            'type' => 'subheader',
            'data' => 'The ADX Line: The Trend\'s Horsepower'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The main ADX line is a number between 0 and 100 that measures the strength of the trend, regardless of whether it\'s an uptrend or downtrend.'
        ],
        [
            'type' => 'list',
            'data' => [
                '**ADX below 20-25:** Indicates a weak or non-existent trend. The market is likely in a sideways "ranging" period. Trend-following strategies will likely fail here.',
                '**ADX rising above 25:** A trend may be starting or strengthening. It\'s time to pay attention.',
                '**ADX above 40:** A strong, established trend is in place.'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'The +DI and -DI Lines: Who\'s in Control?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'These two lines determine the trend\'s direction. When the **+DI line is above the -DI line**, buyers have more strength (uptrend). When the **-DI line is above the +DI line**, sellers have more strength (downtrend). The crossover of these lines can signal a potential shift in momentum.'
        ],
        [
            'type' => 'header',
            'data' => 'Putting It All Together: The Complete Signal'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The true power of ADX comes from combining the lines. A strong, confirmed uptrend occurs when the **+DI is above the -DI** AND the **ADX line is rising and above 25**. A strong, confirmed downtrend occurs when the **-DI is above the +DI** AND the **ADX line is rising and above 25**.'
        ],
        [
            'type' => 'image',
            'data' => [
                'url' => '[PLACEHOLDER: /education/assets/gen-002-adx-uptrend.png]',
                'caption' => 'A chart showing a stock in a strong uptrend. Note how the ADX line is above 40 and the +DI line is clearly above the -DI line.'
            ]
        ],
        [
            'type' => 'header',
            'data' => 'Scenario & Question'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** You see a stock that has been falling for weeks. You are considering opening a short position to profit from the fall. You look at the ADX indicator and see that while the -DI line is far above the +DI line (confirming bears are in control), the main ADX line itself is falling and has just crossed below 25.',
                '**Question:** What does the falling ADX line tell you about the strength of this downtrend? Does this information support your decision to open a new short position?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'This is a classic ADX signal that requires careful thought. The falling ADX line, even though the price is still dropping, indicates that the momentum or strength of the downtrend is fading. It\'s like a car that\'s still rolling downhill but has taken its foot off the gas. This does **not** support opening a new short position. In fact, it warns that the trend is exhausting itself and may be entering a consolidation phase or even preparing for a reversal. It\'s a signal to be cautious, not aggressive.'
        ],
        [
            'type' => 'header',
            'data' => 'Summary'
        ],
        [
            'type' => 'list',
            'data' => [
                'ADX measures trend **strength**, not direction.',
                '+DI and -DI determine trend **direction**.',
                'A reading above 25 indicates a strengthening trend.',
                'A reading below 25 indicates a weak or ranging market.',
                'Always use the lines together for a complete picture.'
            ]
        ]
    ]
];
