<?php
/**
 * Educational Content: Portfolio Performance Directive (D37)
 * 
 * Explains how the Portfolio Performance directive works and why it's unique to Alpaca
 */
return [
    // -- METADATA --
    'lesson_id' => 'GEN-037',
    'parent_id' => null,
    'title' => 'Portfolio Performance Analysis: Using Your Real Trading Data',
    'directive_id' => 'D37',
    'module' => 'Module 4: Advanced Directives',
    'complexity' => 'Intermediate',
    'status' => 'Active',

    // -- LESSON CONTENT --
    'content' => [
        [
            'type' => 'paragraph',
            'data' => 'The Portfolio Performance directive represents a breakthrough in personalized trading analysis. Unlike traditional technical indicators that only look at individual stock data, this directive analyzes YOUR actual portfolio performance and trading behavior to inform future decisions.'
        ],
        [
            'type' => 'header',
            'data' => 'What Makes This Directive Unique'
        ],
        [
            'type' => 'paragraph',
            'data' => 'This directive is **only possible with Alpaca integration** because it requires access to your real trading account data. Alpha Vantage and other market data providers cannot access your personal portfolio information - they only provide market data for individual stocks.'
        ],
        [
            'type' => 'list',
            'data' => [
                '**Portfolio Momentum**: Tracks your account\'s recent performance trends',
                '**Watchlist Analysis**: Identifies stocks you\'ve marked as high-conviction plays',
                '**Risk Tolerance Assessment**: Analyzes your portfolio diversification patterns',
                '**Personal Conviction Scoring**: Higher scores for stocks in your active watchlists'
            ]
        ],
        [
            'type' => 'header',
            'data' => 'How Portfolio Momentum Works'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The directive analyzes your portfolio\'s equity curve over the past week. If your portfolio is gaining momentum (positive performance), it suggests your current stock selection process is working well. This creates a positive feedback loop:'
        ],
        [
            'type' => 'code_block',
            'data' => 'Portfolio Momentum = (Current Equity - Previous Equity) / Previous Equity\n\nStrong Momentum (>2%): +40 points\nPositive Momentum (>0%): +20 points\nNegative Momentum (<-2%): -20 points'
        ],
        [
            'type' => 'header',
            'data' => 'Watchlist Conviction Factor'
        ],
        [
            'type' => 'paragraph',
            'data' => 'When you add a stock to your Alpaca watchlist, you\'re signaling higher conviction in that opportunity. The directive rewards this by adding +30 points to stocks in your watchlists. This reflects the psychological reality that you\'re more likely to succeed with stocks you\'ve researched and marked as interesting.'
        ],
        [
            'type' => 'header',
            'data' => 'Risk Tolerance Assessment'
        ],
        [
            'type' => 'paragraph',
            'data' => 'The directive examines your portfolio diversification. A concentrated portfolio (low diversification) suggests higher risk tolerance, which can be positive for aggressive growth strategies. This adds context to your trading personality that pure technical analysis cannot provide.'
        ],
        [
            'type' => 'header',
            'data' => 'Practical Applications'
        ],
        [
            'type' => 'list',
            'data' => [
                '**Hot Hand Detection**: When your portfolio is performing well, the directive boosts scores for new opportunities',
                '**Conviction Validation**: Stocks in your watchlists get priority scoring',
                '**Risk Alignment**: Recommendations align with your demonstrated risk tolerance',
                '**Performance Feedback**: Poor portfolio performance may suggest strategy adjustment needed'
            ]
        ],
        [
            'type' => 'header',
            'data' => 'API Requirements & Cost'
        ],
        [
            'type' => 'paragraph',
            'data' => 'This directive uses Alpaca\'s account endpoints, which are **completely free** and don\'t count against market data limits:'
        ],
        [
            'type' => 'code_block',
            'data' => 'Required Alpaca Endpoints:\n- /v2/account/portfolio/history (FREE)\n- /v2/watchlists (FREE)\n\nNo market data API calls required!\nNo additional costs beyond Alpaca account access.'
        ],
        [
            'type' => 'header',
            'data' => 'Why This Matters for Your Trading'
        ],
        [
            'type' => 'paragraph',
            'data' => 'Traditional technical analysis treats all traders the same. But your personal trading history, risk tolerance, and conviction levels are crucial factors in your success. This directive bridges the gap between impersonal market data and your personal trading psychology.'
        ],
        [
            'type' => 'summary',
            'data' => [
                '**Unique to Alpaca**: Requires real account data that other APIs cannot provide',
                '**Zero Cost**: Uses free account endpoints, no market data charges',
                '**Personal Context**: Incorporates your actual trading behavior and preferences',
                '**Momentum Feedback**: Rewards successful portfolio performance with higher scores',
                '**Conviction Weighting**: Prioritizes stocks in your watchlists'
            ]
        ]
    ]
];