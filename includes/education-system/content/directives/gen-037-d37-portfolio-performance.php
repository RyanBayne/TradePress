<?php
/**
 * Educational Content: Portfolio Performance Directive (D37)
 *
 * Explains how the Portfolio Performance directive works and why it's unique to Alpaca
 */
return array(
	// -- METADATA --
	'lesson_id'    => 'GEN-037',
	'parent_id'    => null,
	'title'        => 'Portfolio Performance Analysis: Using Your Real Trading Data',
	'directive_id' => 'D37',
	'module'       => 'Module 4: Advanced Directives',
	'complexity'   => 'Intermediate',
	'status'       => 'Active',

	// -- LESSON CONTENT --
	'content'      => array(
		array(
			'type' => 'paragraph',
			'data' => 'The Portfolio Performance directive represents a breakthrough in personalized trading analysis. Unlike traditional technical indicators that only look at individual stock data, this directive analyzes YOUR actual portfolio performance and trading behavior to inform future decisions.',
		),
		array(
			'type' => 'header',
			'data' => 'What Makes This Directive Unique',
		),
		array(
			'type' => 'paragraph',
			'data' => 'This directive is **only possible with Alpaca integration** because it requires access to your real trading account data. Alpha Vantage and other market data providers cannot access your personal portfolio information - they only provide market data for individual stocks.',
		),
		array(
			'type' => 'list',
			'data' => array(
				'**Portfolio Momentum**: Tracks your account\'s recent performance trends',
				'**Watchlist Analysis**: Identifies stocks you\'ve marked as high-conviction plays',
				'**Risk Tolerance Assessment**: Analyzes your portfolio diversification patterns',
				'**Personal Conviction Scoring**: Higher scores for stocks in your active watchlists',
			),
		),
		array(
			'type' => 'header',
			'data' => 'How Portfolio Momentum Works',
		),
		array(
			'type' => 'paragraph',
			'data' => 'The directive analyzes your portfolio\'s equity curve over the past week. If your portfolio is gaining momentum (positive performance), it suggests your current stock selection process is working well. This creates a positive feedback loop:',
		),
		array(
			'type' => 'code_block',
			'data' => 'Portfolio Momentum = (Current Equity - Previous Equity) / Previous Equity\n\nStrong Momentum (>2%): +40 points\nPositive Momentum (>0%): +20 points\nNegative Momentum (<-2%): -20 points',
		),
		array(
			'type' => 'header',
			'data' => 'Watchlist Conviction Factor',
		),
		array(
			'type' => 'paragraph',
			'data' => 'When you add a stock to your Alpaca watchlist, you\'re signaling higher conviction in that opportunity. The directive rewards this by adding +30 points to stocks in your watchlists. This reflects the psychological reality that you\'re more likely to succeed with stocks you\'ve researched and marked as interesting.',
		),
		array(
			'type' => 'header',
			'data' => 'Risk Tolerance Assessment',
		),
		array(
			'type' => 'paragraph',
			'data' => 'The directive examines your portfolio diversification. A concentrated portfolio (low diversification) suggests higher risk tolerance, which can be positive for aggressive growth strategies. This adds context to your trading personality that pure technical analysis cannot provide.',
		),
		array(
			'type' => 'header',
			'data' => 'Practical Applications',
		),
		array(
			'type' => 'list',
			'data' => array(
				'**Hot Hand Detection**: When your portfolio is performing well, the directive boosts scores for new opportunities',
				'**Conviction Validation**: Stocks in your watchlists get priority scoring',
				'**Risk Alignment**: Recommendations align with your demonstrated risk tolerance',
				'**Performance Feedback**: Poor portfolio performance may suggest strategy adjustment needed',
			),
		),
		array(
			'type' => 'header',
			'data' => 'API Requirements & Cost',
		),
		array(
			'type' => 'paragraph',
			'data' => 'This directive uses Alpaca\'s account endpoints, which are **completely free** and don\'t count against market data limits:',
		),
		array(
			'type' => 'code_block',
			'data' => 'Required Alpaca Endpoints:\n- /v2/account/portfolio/history (FREE)\n- /v2/watchlists (FREE)\n\nNo market data API calls required!\nNo additional costs beyond Alpaca account access.',
		),
		array(
			'type' => 'header',
			'data' => 'Why This Matters for Your Trading',
		),
		array(
			'type' => 'paragraph',
			'data' => 'Traditional technical analysis treats all traders the same. But your personal trading history, risk tolerance, and conviction levels are crucial factors in your success. This directive bridges the gap between impersonal market data and your personal trading psychology.',
		),
		array(
			'type' => 'summary',
			'data' => array(
				'**Unique to Alpaca**: Requires real account data that other APIs cannot provide',
				'**Zero Cost**: Uses free account endpoints, no market data charges',
				'**Personal Context**: Incorporates your actual trading behavior and preferences',
				'**Momentum Feedback**: Rewards successful portfolio performance with higher scores',
				'**Conviction Weighting**: Prioritizes stocks in your watchlists',
			),
		),
	),
);
