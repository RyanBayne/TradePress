<?php
/**
 * Lesson Content: Introduction to Simple Moving Averages (SMA)
 * 
 * Note: This content is structured as a PHP array to be easily imported into a database.
 * The structure follows the principles outlined in the ELEARNING-CONTENT-PLAN.md document.
 */
return [
    // -- METADATA --
    'lesson_id' => 'GEN-001',
    'parent_id' => null,
    'title' => 'Introduction to Simple Moving Averages (SMA)',
    'directive_id' => 'D13',
    'module' => 'Module 6: Introduction to Technical Analysis',
    'complexity' => 'Beginner',
    'status' => 'Drafting',

    // -- LESSON CONTENT --
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'Welcome to your first lesson on technical indicators! Before we dive in, let\'s get into the right mindset. An indicator is not a crystal ball; it is a tool to help you understand the story that price is telling. It provides context, not certainty. Our goal is to use these tools to build a case for a trade, not to find a magic button that predicts the future. Patience and discipline are your most valuable assets.'
        ],
        [
            'type' => 'header',
            'data' => 'What is a Simple Moving Average (SMA)?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The Simple Moving Average (SMA) is one of the most fundamental and widely used technical indicators. Its job is to smooth out price data to create a single flowing line, making it easier to identify the underlying trend direction.'
        ],
        [
            'type' => 'paragraph',
            'data' => 'Think of it like this: instead of looking at the chaotic, jagged up-and-down movements of a stock each day, the SMA gives you a clearer, cleaner picture of its general direction over a specific period.'
        ],
        [
            'type' => 'header',
            'data' => 'How It Works'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The calculation is simple, as the name implies. A 20-day SMA, for example, is the sum of the closing prices for the last 20 days, divided by 20. Tomorrow, the oldest day is dropped, and the new day is added, so the average "moves" over time.'
        ],
        [
            'type' => 'list',
            'data' => [
                '**Short-term SMAs** (like 10 or 20-day) react quickly to price changes and are useful for short-term trading.',
                '**Long-term SMAs** (like 50, 100, or 200-day) are slower to react and are used to identify long-term trends.',
            ]
        ],
        [
            'type' => 'header',
            'data' => 'How to Use the SMA in Trading'
        ],
        [
            'type' => 'subheader',
            'data' => '1. Identifying the Trend'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The most basic use of the SMA is to determine the trend. If the price is consistently trading above the SMA, it suggests an uptrend. If the price is consistently below the SMA, it suggests a downtrend.'
        ],
        [
            'type' => 'subheader',
            'data' => '2. Dynamic Support and Resistance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'In an uptrend, the SMA line can often act as a \"dynamic\" level of support. You will often see the price pull back to the SMA and then \"bounce\" off it to continue the trend. The opposite is true in a downtrend, where the SMA can act as resistance.'
        ],
        [
            'type' => 'header',
            'data' => 'Examples'
        ],
        [
            'type' => 'image',
            'data' => [
                'url' => '[PLACEHOLDER: /education/assets/gen-001-sma-support.png]',
                'caption' => 'A real-world chart showing the price of a stock in an uptrend, repeatedly finding support at its 50-day SMA.'
            ]
        ],
        [
            'type' => 'paragraph',
            'data' => '**Real-World Example:** During the strong bull market of 2017, the price of Bitcoin (BTC) stayed above its 50-day SMA for most of the year. Investors who used this as a simple guide to stay in the trend did very well, while a decisive cross below it in early 2018 signaled the start of a major downtrend.'
        ],
        [
            'type' => 'header',
            'data' => 'Scenario & Question'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** You are looking at a stock that has been in a clear uptrend for several months. It has been using the 50-day SMA as a solid support level, bouncing off it three times. Today, following some negative market news, the stock price has dropped sharply and closed 5% below the 50-day SMA.',
                '**Question:** What is your immediate emotional reaction? Based on this single event, should you sell your position immediately? What is the most disciplined, patient next step?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'Your immediate reaction might be fear or panic, leading to an impulse to sell. This is a normal response to seeing a pattern break. However, a disciplined trader pauses. A single close below a moving average, even a sharp one, is a warning sign, not a definitive sell signal. The patient next step is to wait for more information. Does the price continue to fall the next day? Or does it quickly reclaim the 50-day SMA, proving the break was a false alarm (a \"shakeout\")? The disciplined approach is to watch for confirmation on the next 1-2 candles before abandoning a long-term trend.'
        ],
        [
            'type' => 'header',
            'data' => 'Summary'
        ],
        [
            'type' => 'list',
            'data' => [
                'The SMA smooths price action to help identify the trend.',
                'It can act as dynamic support or resistance.',
                'It is a reactive tool, not a predictive one.',
                'Always wait for confirmation; a single break is not a complete signal.'
            ]
        ]
    ]
];
