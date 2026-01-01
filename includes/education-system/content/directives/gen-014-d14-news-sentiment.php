<?php
/**
 * Lesson Content: D14 Positive News Sentiment
 */
return [
    'lesson_id' => 'GEN-014',
    'parent_id' => null,
    'title' => 'Sentiment Analysis: Trading the News',
    'directive_id' => 'D14',
    'module' => 'Module 8: Day Trading Fundamentals',
    'complexity' => 'Advanced',
    'status' => 'Drafting',
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'News and sentiment are powerful market drivers. This directive moves beyond pure price and volume to analyze the tone of news articles and social media, attempting to quantify market mood.'
        ],
        [
            'type' => 'header',
            'data' => 'What is Sentiment Analysis?'
        ],
        [
            'type' => 'paragraph',
            'data' => 'It is the process of using Natural Language Processing (NLP) to scan text (like news articles, tweets, or reports) and determine if the sentiment is positive, negative, or neutral. In trading, the theory is that overwhelmingly positive sentiment can drive buying pressure, and negative sentiment can drive selling pressure.'
        ],
        [
            'type' => 'header',
            'data' => 'A Contrarian Approach'
        ],
        [
            'type' => 'paragraph',
            'data' => 'While positive news can fuel a rally, extreme positive sentiment can also be a contrarian indicator. The legendary investor Warren Buffett famously advised to be "fearful when others are greedy, and greedy when others are fearful." When every news article is glowing and every retail trader is bullish, it can sometimes mean a market top is near, as there is no one left to buy.'
        ],
        [
            'type' => 'scenario',
            'data' => [
                '**Situation:** A new electric vehicle company has been in the news constantly. Every headline is positive, talking about their revolutionary technology. The stock price has gone up 500% in three months. The news sentiment score in your system is at a maximum positive reading.',
                '**Question:** From a risk management perspective, is this a good time to open a new long position? What does the extreme positive sentiment suggest?'
            ]
        ],
        [
            'type' => 'subheader',
            'data' => 'Guidance'
        ],
        [
            'type' => 'paragraph',
            'data' => 'From a risk management perspective, this is a very dangerous time to open a new long position. While the trend is up, the extreme positive sentiment suggests the trade is overcrowded and that the good news is likely already priced in. This is a state of high euphoria, which often precedes a sharp correction. A disciplined trader would see this not as an opportunity to buy, but as a signal to be cautious or even take profits on an existing position.'
        ]
    ]
];
