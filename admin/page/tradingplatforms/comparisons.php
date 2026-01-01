<?php
/**
 * TradePress Trading Platforms Comparisons Tab
 * 
 * Displays comparison information about different trading platforms and APIs
 *
 * @package TradePress
 * @subpackage admin/page/TradingPlatforms
 * @version 1.0.0
 * @since 1.0.0
 * @created 2025-04-26 21:45:00
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the API Comparisons tab content
 */
function tradepress_trading_platforms_comparisons_tab() {
    // Check if demo mode is active
    $is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;

    // Get all financial API providers from the directory
    require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
    $providers = TradePress_API_Directory::get_financial_providers();
    
    // Define the comparison categories and fields
    $comparison_categories = array(
        'data_offerings' => array(
            'title' => __('Data Offerings', 'tradepress'),
            'fields' => array(
                'real_time_data' => __('Real-Time Data', 'tradepress'),
                'historical_data' => __('Historical Data', 'tradepress'),
                'tick_data' => __('Tick-by-Tick Data', 'tradepress'),
                'minute_data' => __('Minute Data', 'tradepress'),
                'eod_data' => __('End-of-Day Data', 'tradepress'),
                'fundamental_data' => __('Fundamental Data', 'tradepress'),
                'options_data' => __('Options Data', 'tradepress'),
                'forex_data' => __('Forex Data', 'tradepress'),
                'crypto_data' => __('Crypto Data', 'tradepress')
            )
        ),
        'data_delivery' => array(
            'title' => __('Data Delivery', 'tradepress'),
            'fields' => array(
                'rest_api' => __('REST API', 'tradepress'),
                'websocket_streaming' => __('WebSocket Streaming', 'tradepress'),
                'webhook_support' => __('Webhook Support', 'tradepress'),
                'csv_downloads' => __('CSV Downloads', 'tradepress'),
                'bulk_data_feeds' => __('Bulk Data Feeds', 'tradepress'),
                'polling_frequency' => __('Polling Frequency', 'tradepress'),
                'data_latency' => __('Data Latency', 'tradepress')
            )
        ),
        'rate_limits' => array(
            'title' => __('Rate Limits', 'tradepress'),
            'fields' => array(
                'free_rate_limit' => __('Free Tier Rate Limit', 'tradepress'),
                'paid_rate_limit' => __('Paid Tier Rate Limit', 'tradepress')
            )
        ),
        'trading_features' => array(
            'title' => __('Trading Features', 'tradepress'),
            'fields' => array(
                'paper_trading' => __('Paper Trading', 'tradepress'),
                'real_trading' => __('Real Trading', 'tradepress'),
                'market_orders' => __('Market Orders', 'tradepress'),
                'limit_orders' => __('Limit Orders', 'tradepress'),
                'stop_orders' => __('Stop Orders', 'tradepress'),
                'trailing_stops' => __('Trailing Stops', 'tradepress'),
                'fractional_shares' => __('Fractional Shares', 'tradepress'),
                'order_cancellation' => __('Order Cancellation', 'tradepress')
            )
        ),
        'cost_structure' => array(
            'title' => __('Cost Structure', 'tradepress'),
            'fields' => array(
                'free_tier' => __('Free Tier Available', 'tradepress'),
                'free_tier_limitations' => __('Free Tier Limitations', 'tradepress'),
                'paid_tiers' => __('Paid Tier Pricing', 'tradepress'),
                'commission_fees' => __('Commission Fees', 'tradepress')
            )
        ),
        'technical_analysis' => array(
            'title' => __('Technical Analysis', 'tradepress'),
            'fields' => array(
                'technical_indicators' => __('Technical Indicators', 'tradepress'),
                'aggregate_indicators' => __('Aggregate Indicators', 'tradepress'),
                'candlestick_patterns' => __('Candlestick Patterns', 'tradepress'),
                'support_resistance' => __('Support/Resistance Levels', 'tradepress'),
                'news_sentiment' => __('News Sentiment Analysis', 'tradepress')
            )
        ),
        'technical_aspects' => array(
            'title' => __('Technical Aspects', 'tradepress'),
            'fields' => array(
                'authentication' => __('Authentication Method', 'tradepress'),
                'api_documentation' => __('API Documentation Quality', 'tradepress'),
                'sdk_availability' => __('SDK Availability', 'tradepress'),
                'webhook_support' => __('Webhook Support', 'tradepress')
            )
        )
    );
    
    // Demo comparison data - in a real implementation this would come from a database
    $comparison_data = array(
        'trading212' => array(
            'real_time_data' => array('value' => 'Yes', 'notes' => 'Real-time quotes for funded accounts'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Historical price data available'),
            'tick_data' => array('value' => 'No', 'notes' => 'Not available via API'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute-level data available'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data available'),
            'fundamental_data' => array('value' => 'Limited', 'notes' => 'Basic company info only'),
            'options_data' => array('value' => 'No', 'notes' => 'Options not supported'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'FX pairs available'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Crypto CFDs available'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'RESTful API endpoints'),
            'websocket_streaming' => array('value' => 'No', 'notes' => 'Not currently available'),
            'polling_frequency' => array('value' => 'Rate limited', 'notes' => 'Subject to API limits'),
            'data_latency' => array('value' => '~1-2 seconds', 'notes' => 'Near real-time'),
            'free_rate_limit' => array('value' => 'No free tier', 'notes' => 'Requires funded account'),
            'paid_rate_limit' => array('value' => '60 requests/min', 'notes' => 'Per API key'),
            'paper_trading' => array('value' => 'Yes', 'notes' => 'Demo account available'),
            'real_trading' => array('value' => 'Yes', 'notes' => 'Live trading supported'),
            'market_orders' => array('value' => 'Yes', 'notes' => 'Market orders supported'),
            'limit_orders' => array('value' => 'Yes', 'notes' => 'Limit orders supported'),
            'stop_orders' => array('value' => 'Yes', 'notes' => 'Stop loss orders'),
            'trailing_stops' => array('value' => 'No', 'notes' => 'Not available via API'),
            'fractional_shares' => array('value' => 'Yes', 'notes' => 'Fractional shares supported'),
            'order_cancellation' => array('value' => 'Yes', 'notes' => 'Pending orders can be cancelled'),
            'free_tier' => array('value' => 'No', 'notes' => 'Requires funded account'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'No free tier available'),
            'paid_tiers' => array('value' => 'Free trading', 'notes' => 'No monthly fees'),
            'commission_fees' => array('value' => 'None', 'notes' => 'Commission-free trading'),
            'authentication' => array('value' => 'API Key', 'notes' => 'API key authentication'),
            'api_documentation' => array('value' => 'Good', 'notes' => 'Clear documentation with examples'),
            'sdk_availability' => array('value' => 'No', 'notes' => 'No official SDKs'),
            'webhook_support' => array('value' => 'No', 'notes' => 'Not currently supported')
        ),
        'interactive_brokers' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Market data subscriptions required'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'Yes (Paid)', 'notes' => 'Level II data available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute bars available'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data included'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Comprehensive fundamental data'),
            'options_data' => array('value' => 'Yes', 'notes' => 'Full options chain data'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'FX pairs and rates'),
            'crypto_data' => array('value' => 'Limited', 'notes' => 'Select crypto products'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Client Portal Web API'),
            'websocket_streaming' => array('value' => 'Yes', 'notes' => 'TWS API streaming'),
            'polling_frequency' => array('value' => 'Real-time', 'notes' => 'Streaming data available'),
            'data_latency' => array('value' => '<100ms', 'notes' => 'Professional-grade latency'),
            'free_rate_limit' => array('value' => 'No free tier', 'notes' => 'Requires funded account'),
            'paid_rate_limit' => array('value' => 'High', 'notes' => 'Professional limits'),
            'paper_trading' => array('value' => 'Yes', 'notes' => 'Full paper trading account'),
            'real_trading' => array('value' => 'Yes', 'notes' => 'Global markets access'),
            'market_orders' => array('value' => 'Yes', 'notes' => 'All order types supported'),
            'limit_orders' => array('value' => 'Yes', 'notes' => 'Advanced order types'),
            'stop_orders' => array('value' => 'Yes', 'notes' => 'Stop and stop-limit orders'),
            'trailing_stops' => array('value' => 'Yes', 'notes' => 'Trailing stop orders'),
            'fractional_shares' => array('value' => 'Limited', 'notes' => 'Select US stocks only'),
            'order_cancellation' => array('value' => 'Yes', 'notes' => 'Full order management'),
            'free_tier' => array('value' => 'No', 'notes' => 'Requires account minimum'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'No free tier'),
            'paid_tiers' => array('value' => '$0.005/share', 'notes' => 'Tiered commission structure'),
            'commission_fees' => array('value' => 'Yes', 'notes' => 'Per-share/contract fees'),
            'authentication' => array('value' => 'OAuth 2.0', 'notes' => 'Secure OAuth authentication'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Comprehensive documentation'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'Limited', 'notes' => 'Event notifications available')
        ),
        'etoro' => array(
            'real_time_data' => array('value' => 'Yes', 'notes' => 'Real-time prices for platform users'),
            'historical_data' => array('value' => 'Limited', 'notes' => 'Basic historical data available'),
            'tick_data' => array('value' => 'No', 'notes' => 'Not available via API'),
            'minute_data' => array('value' => 'Limited', 'notes' => 'Basic minute data only'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day pricing available'),
            'fundamental_data' => array('value' => 'Limited', 'notes' => 'Basic company information'),
            'options_data' => array('value' => 'No', 'notes' => 'CFDs only, no options'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'Extensive FX pairs'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Major cryptocurrencies'),
            'rest_api' => array('value' => 'Limited', 'notes' => 'Restricted API access'),
            'websocket_streaming' => array('value' => 'No', 'notes' => 'Not publicly available'),
            'polling_frequency' => array('value' => 'Limited', 'notes' => 'Restricted access'),
            'data_latency' => array('value' => '~5-15 seconds', 'notes' => 'Consumer-grade latency'),
            'free_rate_limit' => array('value' => 'No public API', 'notes' => 'API not publicly available'),
            'paid_rate_limit' => array('value' => 'Restricted', 'notes' => 'Limited partner access'),
            'paper_trading' => array('value' => 'Yes', 'notes' => 'Virtual portfolio available'),
            'real_trading' => array('value' => 'Yes', 'notes' => 'CFD and stock trading'),
            'market_orders' => array('value' => 'Yes', 'notes' => 'Market orders supported'),
            'limit_orders' => array('value' => 'Yes', 'notes' => 'Limit orders available'),
            'stop_orders' => array('value' => 'Yes', 'notes' => 'Stop loss orders'),
            'trailing_stops' => array('value' => 'No', 'notes' => 'Not available'),
            'fractional_shares' => array('value' => 'Yes', 'notes' => 'Fractional investing available'),
            'order_cancellation' => array('value' => 'Yes', 'notes' => 'Order management available'),
            'free_tier' => array('value' => 'No', 'notes' => 'Requires account funding'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'No free API access'),
            'paid_tiers' => array('value' => 'Spread-based', 'notes' => 'No commission, spread costs'),
            'commission_fees' => array('value' => 'None', 'notes' => 'Spread-based pricing'),
            'authentication' => array('value' => 'Proprietary', 'notes' => 'Custom authentication system'),
            'api_documentation' => array('value' => 'Limited', 'notes' => 'Restricted documentation'),
            'sdk_availability' => array('value' => 'No', 'notes' => 'No public SDKs'),
            'webhook_support' => array('value' => 'No', 'notes' => 'Not available')
        ),
        'fidelity' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time quotes for account holders'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Not available via API'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Intraday data available'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data included'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Company fundamentals available'),
            'options_data' => array('value' => 'Yes', 'notes' => 'Options chains and data'),
            'forex_data' => array('value' => 'Limited', 'notes' => 'Limited FX offerings'),
            'crypto_data' => array('value' => 'No', 'notes' => 'Crypto not supported'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'RESTful API available'),
            'websocket_streaming' => array('value' => 'Limited', 'notes' => 'Limited streaming capabilities'),
            'polling_frequency' => array('value' => 'Rate limited', 'notes' => 'Subject to API limits'),
            'data_latency' => array('value' => '~1-3 seconds', 'notes' => 'Near real-time for account holders'),
            'free_rate_limit' => array('value' => 'No free tier', 'notes' => 'Requires Fidelity account'),
            'paid_rate_limit' => array('value' => '120 requests/min', 'notes' => 'Standard rate limits'),
            'paper_trading' => array('value' => 'Limited', 'notes' => 'Virtual trading available'),
            'real_trading' => array('value' => 'Yes', 'notes' => 'Full trading capabilities'),
            'market_orders' => array('value' => 'Yes', 'notes' => 'Market orders supported'),
            'limit_orders' => array('value' => 'Yes', 'notes' => 'Limit orders available'),
            'stop_orders' => array('value' => 'Yes', 'notes' => 'Stop loss orders'),
            'trailing_stops' => array('value' => 'Yes', 'notes' => 'Trailing stop orders'),
            'fractional_shares' => array('value' => 'Yes', 'notes' => 'Fractional shares supported'),
            'order_cancellation' => array('value' => 'Yes', 'notes' => 'Order management available'),
            'free_tier' => array('value' => 'No', 'notes' => 'Requires Fidelity brokerage account'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'No free tier'),
            'paid_tiers' => array('value' => 'Free trades', 'notes' => 'No commission on stocks/ETFs'),
            'commission_fees' => array('value' => 'None', 'notes' => 'Commission-free stock trading'),
            'authentication' => array('value' => 'OAuth 2.0', 'notes' => 'OAuth authentication required'),
            'api_documentation' => array('value' => 'Good', 'notes' => 'Comprehensive developer docs'),
            'sdk_availability' => array('value' => 'Limited', 'notes' => 'Some language support'),
            'webhook_support' => array('value' => 'No', 'notes' => 'Webhooks not supported')
        ),
        'polygon' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time data with paid plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'Yes (Paid)', 'notes' => 'Tick-level data available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute aggregates available'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'Daily aggregates included'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Company financials and details'),
            'options_data' => array('value' => 'Yes (Paid)', 'notes' => 'Options contracts and chains'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'FX real-time and historical'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Crypto aggregates and trades'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Comprehensive REST API'),
            'websocket_streaming' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time WebSocket feeds'),
            'polling_frequency' => array('value' => 'High', 'notes' => 'Based on subscription tier'),
            'data_latency' => array('value' => '<100ms', 'notes' => 'Low latency for paid tiers'),
            'free_rate_limit' => array('value' => '5 requests/min', 'notes' => 'Very limited free tier'),
            'paid_rate_limit' => array('value' => 'Up to 1000/min', 'notes' => 'Varies by plan'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'Yes', 'notes' => 'Very limited free tier'),
            'free_tier_limitations' => array('value' => '5 requests/min, 2 years historical', 'notes' => 'Severe limitations'),
            'paid_tiers' => array('value' => '$99-$399/mo', 'notes' => 'Professional data plans'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Data subscription model'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Simple API key authentication'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Comprehensive and well-organized'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'Yes', 'notes' => 'WebSocket streaming available')
        ),
        'tradier' => array(
            'real_time_data' => array('value' => 'Yes', 'notes' => 'Real-time quotes included'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Historical price data available'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Intraday minute data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data included'),
            'fundamental_data' => array('value' => 'Limited', 'notes' => 'Basic company information'),
            'options_data' => array('value' => 'Yes', 'notes' => 'Full options chains and data'),
            'forex_data' => array('value' => 'No', 'notes' => 'Forex not supported'),
            'crypto_data' => array('value' => 'No', 'notes' => 'Crypto not supported'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Comprehensive REST API'),
            'websocket_streaming' => array('value' => 'Yes', 'notes' => 'Real-time streaming available'),
            'polling_frequency' => array('value' => 'High', 'notes' => 'Professional rate limits'),
            'data_latency' => array('value' => '~200ms', 'notes' => 'Near real-time data'),
            'free_rate_limit' => array('value' => 'No free tier', 'notes' => 'Requires brokerage account'),
            'paid_rate_limit' => array('value' => '120 requests/min', 'notes' => 'Standard API limits'),
            'paper_trading' => array('value' => 'Yes', 'notes' => 'Sandbox environment available'),
            'real_trading' => array('value' => 'Yes', 'notes' => 'Full brokerage capabilities'),
            'market_orders' => array('value' => 'Yes', 'notes' => 'Market orders supported'),
            'limit_orders' => array('value' => 'Yes', 'notes' => 'Limit orders available'),
            'stop_orders' => array('value' => 'Yes', 'notes' => 'Stop orders supported'),
            'trailing_stops' => array('value' => 'Yes', 'notes' => 'Trailing stop orders'),
            'fractional_shares' => array('value' => 'No', 'notes' => 'Whole shares only'),
            'order_cancellation' => array('value' => 'Yes', 'notes' => 'Full order management'),
            'free_tier' => array('value' => 'No', 'notes' => 'Requires funded brokerage account'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'No free tier'),
            'paid_tiers' => array('value' => '$0.35/contract', 'notes' => 'Per-contract options pricing'),
            'commission_fees' => array('value' => 'Yes', 'notes' => 'Options: $0.35/contract, Stocks: varies'),
            'authentication' => array('value' => 'OAuth 2.0', 'notes' => 'OAuth authentication required'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Comprehensive developer docs'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'Yes', 'notes' => 'Account event webhooks')
        ),
        'twelvedata' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time data with paid plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute-level intraday data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data included'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Company profiles and financials'),
            'options_data' => array('value' => 'No', 'notes' => 'Options data not available'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'Comprehensive FX pairs'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Major cryptocurrencies'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Comprehensive REST API'),
            'websocket_streaming' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time WebSocket feeds'),
            'polling_frequency' => array('value' => 'High', 'notes' => 'Based on subscription plan'),
            'data_latency' => array('value' => '~1-5 seconds', 'notes' => 'Near real-time for paid plans'),
            'free_rate_limit' => array('value' => '8 requests/min', 'notes' => 'Free tier available'),
            'paid_rate_limit' => array('value' => 'Up to 5000/min', 'notes' => 'Varies by plan'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'Yes', 'notes' => 'Basic free tier available'),
            'free_tier_limitations' => array('value' => '8 requests/min, delayed data', 'notes' => 'Limited functionality'),
            'paid_tiers' => array('value' => '$8-$149/mo', 'notes' => 'Multiple subscription tiers'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Subscription-based pricing'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Simple API key authentication'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Clear and comprehensive docs'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'Yes', 'notes' => 'WebSocket streaming available')
        ),
        'iexcloud' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time data with paid plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Intraday minute data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data included'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Company stats and financials'),
            'options_data' => array('value' => 'Limited', 'notes' => 'Basic options data only'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'Major currency pairs'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Major cryptocurrencies'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Comprehensive REST API'),
            'websocket_streaming' => array('value' => 'Yes (Paid)', 'notes' => 'SSE streaming available'),
            'polling_frequency' => array('value' => 'High', 'notes' => 'Based on subscription'),
            'data_latency' => array('value' => '~15ms', 'notes' => 'Very low latency'),
            'free_rate_limit' => array('value' => '100 requests/day', 'notes' => 'Very limited free tier'),
            'paid_rate_limit' => array('value' => 'Up to 1M/month', 'notes' => 'Message-based pricing'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'Yes', 'notes' => 'Very limited free tier'),
            'free_tier_limitations' => array('value' => '100 requests/day, delayed data', 'notes' => 'Extremely limited'),
            'paid_tiers' => array('value' => '$0.0005-$0.05/message', 'notes' => 'Pay-per-use pricing'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Message-based pricing'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Token-based authentication'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Clear and comprehensive'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'Yes', 'notes' => 'SSE streaming available')
        ),
        'alphavantage' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time with premium plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Intraday minute data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'Daily time series data'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Company overview and financials'),
            'options_data' => array('value' => 'No', 'notes' => 'Options data not available'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'Comprehensive FX pairs'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Major cryptocurrencies'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Simple REST API'),
            'websocket_streaming' => array('value' => 'No', 'notes' => 'No streaming available'),
            'polling_frequency' => array('value' => 'Rate limited', 'notes' => 'Based on subscription'),
            'data_latency' => array('value' => '~15 minutes', 'notes' => 'Delayed data on free tier'),
            'free_rate_limit' => array('value' => '25 requests/day', 'notes' => 'Very limited free tier'),
            'paid_rate_limit' => array('value' => '75-1200/min', 'notes' => 'Varies by plan'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'Yes', 'notes' => 'Very limited free tier'),
            'free_tier_limitations' => array('value' => '25 requests/day, 15min delay', 'notes' => 'Severe limitations'),
            'paid_tiers' => array('value' => '$25-$1200/mo', 'notes' => 'Multiple subscription tiers'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Subscription-based pricing'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Simple API key authentication'),
            'api_documentation' => array('value' => 'Good', 'notes' => 'Clear documentation with examples'),
            'sdk_availability' => array('value' => 'Limited', 'notes' => 'Community SDKs available'),
            'webhook_support' => array('value' => 'No', 'notes' => 'No webhook support'),
            'technical_indicators' => array('value' => 'Yes', 'notes' => 'Comprehensive technical indicators: RSI, MACD, CCI, ADX, Bollinger Bands, EMA, SMA, etc.'),
            'aggregate_indicators' => array('value' => 'No', 'notes' => 'Individual indicators only, no aggregate analysis'),
            'candlestick_patterns' => array('value' => 'No', 'notes' => 'No pattern recognition'),
            'support_resistance' => array('value' => 'No', 'notes' => 'No support/resistance analysis'),
            'news_sentiment' => array('value' => 'Yes', 'notes' => 'News sentiment data available')
        ),
        'alltick' => array(
            'real_time_data' => array('value' => 'Yes', 'notes' => 'Real-time tick data available'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Historical tick and bar data'),
            'tick_data' => array('value' => 'Yes', 'notes' => 'Tick-by-tick data available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute bar data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'Daily bar data'),
            'fundamental_data' => array('value' => 'Limited', 'notes' => 'Basic instrument info only'),
            'options_data' => array('value' => 'Yes', 'notes' => 'Options tick data'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'FX tick data'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Crypto tick data'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'REST API for queries'),
            'websocket_streaming' => array('value' => 'Yes', 'notes' => 'Real-time WebSocket feeds'),
            'polling_frequency' => array('value' => 'Real-time', 'notes' => 'Streaming tick data'),
            'data_latency' => array('value' => '<50ms', 'notes' => 'Very low latency'),
            'free_rate_limit' => array('value' => 'No free tier', 'notes' => 'Paid service only'),
            'paid_rate_limit' => array('value' => 'High', 'notes' => 'Professional limits'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'No', 'notes' => 'No free tier available'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'Paid service only'),
            'paid_tiers' => array('value' => 'Custom pricing', 'notes' => 'Enterprise pricing model'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Subscription-based'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Token-based authentication'),
            'api_documentation' => array('value' => 'Limited', 'notes' => 'Basic documentation available'),
            'sdk_availability' => array('value' => 'Limited', 'notes' => 'Some language support'),
            'webhook_support' => array('value' => 'Yes', 'notes' => 'WebSocket streaming')
        ),
        'eodhd' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time data with premium plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data (their specialty)'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Intraday minute data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data (core offering)'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Comprehensive fundamentals'),
            'options_data' => array('value' => 'Yes', 'notes' => 'Options historical data'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'FX historical data'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Crypto historical data'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Comprehensive REST API'),
            'websocket_streaming' => array('value' => 'No', 'notes' => 'No streaming available'),
            'polling_frequency' => array('value' => 'Rate limited', 'notes' => 'Based on subscription'),
            'data_latency' => array('value' => '~15 minutes', 'notes' => 'Delayed data on free tier'),
            'free_rate_limit' => array('value' => '20 requests/day', 'notes' => 'Very limited free tier'),
            'paid_rate_limit' => array('value' => '100K-1M/day', 'notes' => 'High daily limits'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'Yes', 'notes' => 'Limited free tier'),
            'free_tier_limitations' => array('value' => '20 requests/day, delayed data', 'notes' => 'Very restrictive'),
            'paid_tiers' => array('value' => '$19.99-$79.99/mo', 'notes' => 'Affordable pricing tiers'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Subscription-based pricing'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Simple token authentication'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Comprehensive documentation'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'No', 'notes' => 'No webhook support')
        ),
        'finnhub' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time data with paid plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Historical price data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute resolution data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'Daily candles available'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Company profiles, metrics, earnings, news'),
            'options_data' => array('value' => 'No', 'notes' => 'Options data not available'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'FX rates and data'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Crypto exchanges data'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Comprehensive REST API'),
            'websocket_streaming' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time WebSocket feeds'),
            'polling_frequency' => array('value' => 'High', 'notes' => 'Based on subscription'),
            'data_latency' => array('value' => '~1 second', 'notes' => 'Near real-time'),
            'free_rate_limit' => array('value' => '60 requests/min', 'notes' => 'Generous free tier'),
            'paid_rate_limit' => array('value' => '300-600/min', 'notes' => 'Higher limits with paid plans'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'Yes', 'notes' => 'Generous free tier'),
            'free_tier_limitations' => array('value' => '60 requests/min, delayed data', 'notes' => 'Good free access'),
            'paid_tiers' => array('value' => '$7.99-$99.99/mo', 'notes' => 'Affordable pricing'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Subscription-based pricing'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Simple token authentication'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Clear and comprehensive'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'Yes', 'notes' => 'WebSocket streaming available'),
            'technical_indicators' => array('value' => 'No', 'notes' => 'No built-in technical indicators'),
            'aggregate_indicators' => array('value' => 'Yes', 'notes' => 'Aggregate technical analysis with buy/sell signals, includes ADX'),
            'candlestick_patterns' => array('value' => 'Yes', 'notes' => 'Candlestick pattern detection and recognition'),
            'support_resistance' => array('value' => 'Yes', 'notes' => 'Support and resistance level identification'),
            'news_sentiment' => array('value' => 'Yes', 'notes' => 'News sentiment analysis and buzz metrics')
        ),
        'fmp' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time quotes with paid plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Intraday minute data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'Daily historical data'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Comprehensive fundamentals (specialty)'),
            'options_data' => array('value' => 'Limited', 'notes' => 'Basic options data'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'FX rates available'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Crypto prices'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Comprehensive REST API'),
            'websocket_streaming' => array('value' => 'No', 'notes' => 'No streaming available'),
            'polling_frequency' => array('value' => 'Rate limited', 'notes' => 'Based on subscription'),
            'data_latency' => array('value' => '~15 minutes', 'notes' => 'Delayed on free tier'),
            'free_rate_limit' => array('value' => '250 requests/day', 'notes' => 'Decent free tier'),
            'paid_rate_limit' => array('value' => '300-10K/day', 'notes' => 'Daily limits vary by plan'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'Yes', 'notes' => 'Good free tier'),
            'free_tier_limitations' => array('value' => '250 requests/day, delayed data', 'notes' => 'Reasonable limits'),
            'paid_tiers' => array('value' => '$14-$399/mo', 'notes' => 'Multiple pricing tiers'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Subscription-based pricing'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Simple API key authentication'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Comprehensive with examples'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'No', 'notes' => 'No webhook support')
        ),
        'intrinio' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time data with premium plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Intraday minute bars'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'Daily price data'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Comprehensive fundamentals'),
            'options_data' => array('value' => 'Yes', 'notes' => 'Options prices and Greeks'),
            'forex_data' => array('value' => 'Limited', 'notes' => 'Basic FX data'),
            'crypto_data' => array('value' => 'No', 'notes' => 'Crypto not supported'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Professional REST API'),
            'websocket_streaming' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time streaming feeds'),
            'polling_frequency' => array('value' => 'High', 'notes' => 'Professional limits'),
            'data_latency' => array('value' => '~100ms', 'notes' => 'Low latency feeds'),
            'free_rate_limit' => array('value' => 'No free tier', 'notes' => 'Enterprise-focused'),
            'paid_rate_limit' => array('value' => 'High', 'notes' => 'Professional rate limits'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'No', 'notes' => 'No free tier available'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'Enterprise pricing only'),
            'paid_tiers' => array('value' => 'Custom pricing', 'notes' => 'Enterprise sales model'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Subscription-based'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Secure API authentication'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Professional documentation'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple language SDKs'),
            'webhook_support' => array('value' => 'Yes', 'notes' => 'Real-time streaming available')
        ),
        'marketstack' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time data with paid plans'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Intraday minute data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data (core offering)'),
            'fundamental_data' => array('value' => 'Limited', 'notes' => 'Basic company information'),
            'options_data' => array('value' => 'No', 'notes' => 'Options data not available'),
            'forex_data' => array('value' => 'No', 'notes' => 'Forex not supported'),
            'crypto_data' => array('value' => 'No', 'notes' => 'Crypto not supported'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Simple REST API'),
            'websocket_streaming' => array('value' => 'No', 'notes' => 'No streaming available'),
            'polling_frequency' => array('value' => 'Rate limited', 'notes' => 'Based on subscription'),
            'data_latency' => array('value' => '~15 minutes', 'notes' => 'Delayed on free tier'),
            'free_rate_limit' => array('value' => '1000 requests/month', 'notes' => 'Limited free tier'),
            'paid_rate_limit' => array('value' => '10K-1M/month', 'notes' => 'Monthly limits'),
            'paper_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'real_trading' => array('value' => 'No', 'notes' => 'Data-only provider'),
            'market_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'limit_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'stop_orders' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'trailing_stops' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'fractional_shares' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'order_cancellation' => array('value' => 'N/A', 'notes' => 'Data-only provider'),
            'free_tier' => array('value' => 'Yes', 'notes' => 'Basic free tier'),
            'free_tier_limitations' => array('value' => '1000 requests/month, delayed data', 'notes' => 'Very limited'),
            'paid_tiers' => array('value' => '$9.99-$99.99/mo', 'notes' => 'Affordable pricing'),
            'commission_fees' => array('value' => 'N/A', 'notes' => 'Subscription-based pricing'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Simple API key authentication'),
            'api_documentation' => array('value' => 'Good', 'notes' => 'Clear documentation'),
            'sdk_availability' => array('value' => 'Limited', 'notes' => 'Basic SDK support'),
            'webhook_support' => array('value' => 'No', 'notes' => 'No webhook support')
        ),
        'tradingview' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time data with premium subscriptions'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Extensive historical data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available via API'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute-level chart data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'Daily chart data'),
            'fundamental_data' => array('value' => 'Limited', 'notes' => 'Basic company info'),
            'options_data' => array('value' => 'Limited', 'notes' => 'Basic options data'),
            'forex_data' => array('value' => 'Yes', 'notes' => 'Comprehensive FX data'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Major crypto exchanges'),
            'rest_api' => array('value' => 'Limited', 'notes' => 'Charting Library API only'),
            'websocket_streaming' => array('value' => 'Yes (Paid)', 'notes' => 'Real-time feeds for partners'),
            'polling_frequency' => array('value' => 'Limited', 'notes' => 'Restricted API access'),
            'data_latency' => array('value' => '~1 second', 'notes' => 'Near real-time for premium'),
            'free_rate_limit' => array('value' => 'No public API', 'notes' => 'No free public API'),
            'paid_rate_limit' => array('value' => 'Partner only', 'notes' => 'Requires partnership agreement'),
            'paper_trading' => array('value' => 'Yes', 'notes' => 'Paper trading available'),
            'real_trading' => array('value' => 'Yes (Partners)', 'notes' => 'Via broker integrations'),
            'market_orders' => array('value' => 'Yes (Partners)', 'notes' => 'Through integrated brokers'),
            'limit_orders' => array('value' => 'Yes (Partners)', 'notes' => 'Through integrated brokers'),
            'stop_orders' => array('value' => 'Yes (Partners)', 'notes' => 'Through integrated brokers'),
            'trailing_stops' => array('value' => 'Yes (Partners)', 'notes' => 'Through integrated brokers'),
            'fractional_shares' => array('value' => 'Depends on broker', 'notes' => 'Varies by integrated broker'),
            'order_cancellation' => array('value' => 'Yes (Partners)', 'notes' => 'Through integrated brokers'),
            'free_tier' => array('value' => 'No', 'notes' => 'No free API access'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'Partnership required'),
            'paid_tiers' => array('value' => 'Custom partnership', 'notes' => 'Enterprise partnership model'),
            'commission_fees' => array('value' => 'Depends on broker', 'notes' => 'Varies by integrated broker'),
            'authentication' => array('value' => 'OAuth 2.0', 'notes' => 'Partner authentication'),
            'api_documentation' => array('value' => 'Limited', 'notes' => 'Partner-only documentation'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Charting Library SDK'),
            'webhook_support' => array('value' => 'Yes (Partners)', 'notes' => 'For integrated brokers')
        ),
        'tradingapi' => array(
            'real_time_data' => array('value' => 'Yes', 'notes' => 'Real-time market data'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Historical price data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute-level data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data'),
            'fundamental_data' => array('value' => 'Limited', 'notes' => 'Basic company information'),
            'options_data' => array('value' => 'Yes', 'notes' => 'Options trading data'),
            'forex_data' => array('value' => 'No', 'notes' => 'Forex not supported'),
            'crypto_data' => array('value' => 'No', 'notes' => 'Crypto not supported'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'RESTful API endpoints'),
            'websocket_streaming' => array('value' => 'Limited', 'notes' => 'Basic streaming available'),
            'polling_frequency' => array('value' => 'Rate limited', 'notes' => 'Standard rate limits'),
            'data_latency' => array('value' => '~2-5 seconds', 'notes' => 'Near real-time'),
            'free_rate_limit' => array('value' => 'No free tier', 'notes' => 'Paid service only'),
            'paid_rate_limit' => array('value' => '100-500/min', 'notes' => 'Varies by plan'),
            'paper_trading' => array('value' => 'Yes', 'notes' => 'Sandbox environment'),
            'real_trading' => array('value' => 'Yes', 'notes' => 'Live trading supported'),
            'market_orders' => array('value' => 'Yes', 'notes' => 'Market orders supported'),
            'limit_orders' => array('value' => 'Yes', 'notes' => 'Limit orders available'),
            'stop_orders' => array('value' => 'Yes', 'notes' => 'Stop orders supported'),
            'trailing_stops' => array('value' => 'Limited', 'notes' => 'Basic trailing stops'),
            'fractional_shares' => array('value' => 'No', 'notes' => 'Whole shares only'),
            'order_cancellation' => array('value' => 'Yes', 'notes' => 'Order management available'),
            'free_tier' => array('value' => 'No', 'notes' => 'No free tier available'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'Paid service only'),
            'paid_tiers' => array('value' => 'Custom pricing', 'notes' => 'Contact for pricing'),
            'commission_fees' => array('value' => 'Yes', 'notes' => 'Per-trade commissions'),
            'authentication' => array('value' => 'API Key', 'notes' => 'API key authentication'),
            'api_documentation' => array('value' => 'Limited', 'notes' => 'Basic documentation available'),
            'sdk_availability' => array('value' => 'No', 'notes' => 'No official SDKs'),
            'webhook_support' => array('value' => 'Limited', 'notes' => 'Basic webhook support')
        ),
        'webull' => array(
            'real_time_data' => array('value' => 'Yes', 'notes' => 'Real-time quotes for account holders'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Historical price data'),
            'tick_data' => array('value' => 'No', 'notes' => 'Tick data not available'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Minute-level data'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'End-of-day data'),
            'fundamental_data' => array('value' => 'Yes', 'notes' => 'Company fundamentals and news'),
            'options_data' => array('value' => 'Yes', 'notes' => 'Options chains and data'),
            'forex_data' => array('value' => 'Limited', 'notes' => 'Basic FX data'),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Crypto trading data'),
            'rest_api' => array('value' => 'Limited', 'notes' => 'Unofficial/reverse-engineered APIs'),
            'websocket_streaming' => array('value' => 'Limited', 'notes' => 'Unofficial streaming'),
            'polling_frequency' => array('value' => 'Limited', 'notes' => 'Unofficial API limits'),
            'data_latency' => array('value' => '~1-3 seconds', 'notes' => 'Near real-time'),
            'free_rate_limit' => array('value' => 'No official API', 'notes' => 'Unofficial APIs only'),
            'paid_rate_limit' => array('value' => 'No official API', 'notes' => 'No official paid API'),
            'paper_trading' => array('value' => 'Yes', 'notes' => 'Paper trading account available'),
            'real_trading' => array('value' => 'Yes', 'notes' => 'Live trading (app/web only)'),
            'market_orders' => array('value' => 'Yes', 'notes' => 'Market orders supported'),
            'limit_orders' => array('value' => 'Yes', 'notes' => 'Limit orders available'),
            'stop_orders' => array('value' => 'Yes', 'notes' => 'Stop orders supported'),
            'trailing_stops' => array('value' => 'Yes', 'notes' => 'Trailing stop orders'),
            'fractional_shares' => array('value' => 'Yes', 'notes' => 'Fractional shares supported'),
            'order_cancellation' => array('value' => 'Yes', 'notes' => 'Order management available'),
            'free_tier' => array('value' => 'No official API', 'notes' => 'No official API available'),
            'free_tier_limitations' => array('value' => 'N/A', 'notes' => 'No official API'),
            'paid_tiers' => array('value' => 'Commission-free', 'notes' => 'No API fees (no official API)'),
            'commission_fees' => array('value' => 'None', 'notes' => 'Commission-free trading'),
            'authentication' => array('value' => 'Unofficial', 'notes' => 'Reverse-engineered auth'),
            'api_documentation' => array('value' => 'None', 'notes' => 'No official documentation'),
            'sdk_availability' => array('value' => 'No', 'notes' => 'No official SDKs'),
            'webhook_support' => array('value' => 'No', 'notes' => 'No webhook support')
        ),
        'alpaca' => array(
            'real_time_data' => array('value' => 'Yes (Paid)', 'notes' => 'Free with funded account'),
            'historical_data' => array('value' => 'Yes', 'notes' => 'Free tier available'),
            'tick_data' => array('value' => 'Yes (Paid)', 'notes' => 'Via Data API subscription'),
            'minute_data' => array('value' => 'Yes', 'notes' => 'Free tier available'),
            'eod_data' => array('value' => 'Yes', 'notes' => 'Free tier available'),
            'fundamental_data' => array('value' => 'Limited', 'notes' => 'Basic company info only'),
            'options_data' => array('value' => 'Yes (Paid)', 'notes' => 'Via Market Data subscription'),
            'forex_data' => array('value' => 'No', 'notes' => ''),
            'crypto_data' => array('value' => 'Yes', 'notes' => 'Limited selection'),
            'rest_api' => array('value' => 'Yes', 'notes' => 'Comprehensive endpoints'),
            'websocket_streaming' => array('value' => 'Yes', 'notes' => 'For real-time data'),
            'polling_frequency' => array('value' => 'Unlimited', 'notes' => 'Subject to rate limits'),
            'data_latency' => array('value' => '~500ms', 'notes' => 'For real-time data'),
            'free_rate_limit' => array('value' => '200 requests/min', 'notes' => 'For market data'),
            'paid_rate_limit' => array('value' => 'Higher', 'notes' => 'Varies by subscription'),
            'paper_trading' => array('value' => 'Yes', 'notes' => 'Full-featured'),
            'real_trading' => array('value' => 'Yes', 'notes' => 'With funded account'),
            'market_orders' => array('value' => 'Yes', 'notes' => ''),
            'limit_orders' => array('value' => 'Yes', 'notes' => ''),
            'stop_orders' => array('value' => 'Yes', 'notes' => ''),
            'trailing_stops' => array('value' => 'Yes', 'notes' => ''),
            'fractional_shares' => array('value' => 'Yes', 'notes' => ''),
            'order_cancellation' => array('value' => 'Yes', 'notes' => ''),
            'free_tier' => array('value' => 'Yes', 'notes' => 'With limitations'),
            'free_tier_limitations' => array('value' => 'Delayed data, rate limits', 'notes' => ''),
            'paid_tiers' => array('value' => '$9-$99/mo', 'notes' => 'Based on plan level'),
            'commission_fees' => array('value' => 'None', 'notes' => 'Commission-free trading'),
            'authentication' => array('value' => 'API Key', 'notes' => 'Key ID & Secret'),
            'api_documentation' => array('value' => 'Excellent', 'notes' => 'Clear and comprehensive'),
            'sdk_availability' => array('value' => 'Yes', 'notes' => 'Multiple languages'),
            'webhook_support' => array('value' => 'Yes', 'notes' => 'For account activities')
        )
    );
    
    // Placeholder keys for providers we don't have detailed info for yet
    foreach ($providers as $provider_id => $provider) {
        if (!isset($comparison_data[$provider_id])) {
            $comparison_data[$provider_id] = array();
            
            // Set all fields to "Data Pending"
            foreach ($comparison_categories as $category_id => $category) {
                foreach ($category['fields'] as $field_id => $field_name) {
                    $comparison_data[$provider_id][$field_id] = array(
                        'value' => 'Data Pending',
                        'notes' => 'Information being compiled'
                    );
                }
            }
        }
    }
    
    // Get all provider IDs to show by default (instead of just a few selected ones)
    $default_providers = array_keys($providers);
    
    ?>
    <div class="tradepress-api-comparisons-container">
        <?php 
        TradePress_Admin_Notices::development_progress_notice(
            'API Provider Comparisons',
            array(),
            'header-right'
        );
        ?>
        
        <div class="api-comparisons-description">
            <div class="comparison-controls">
                <div class="section-toggles">
                    <h4><?php esc_html_e('Show/Hide Sections:', 'tradepress'); ?></h4>
                    <div class="button-group">
                        <?php foreach ($comparison_categories as $category_id => $category): ?>
                            <button type="button" class="button section-toggle active" data-section="<?php echo esc_attr($category_id); ?>">
                                <?php echo esc_html($category['title']); ?>
                            </button>
                        <?php endforeach; ?>
                        <button type="button" class="button section-toggle active" data-section="api_capability_matrix">
                            <?php esc_html_e('API Capability Matrix', 'tradepress'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="provider-filters">
                    <h4><?php esc_html_e('Provider Filters:', 'tradepress'); ?></h4>
                    <div class="button-group">
                        <button type="button" class="button filter-button" id="filter-active-only">
                            <?php esc_html_e('Active Only', 'tradepress'); ?>
                        </button>
                        <button type="button" class="button filter-button" id="filter-data-only">
                            <?php esc_html_e('Data Only', 'tradepress'); ?>
                        </button>
                        <button type="button" class="button filter-button" id="filter-trading-only">
                            <?php esc_html_e('Trading Only', 'tradepress'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php foreach ($comparison_categories as $category_id => $category): ?>
            <div class="comparison-category-section" id="section-<?php echo esc_attr($category_id); ?>">
                <h3 class="comparison-category-title"><?php echo esc_html($category['title']); ?></h3>
                
                <div class="comparison-table-wrapper">
                    <table class="widefat comparison-table">
                        <thead>
                            <tr>
                                <th class="feature-column"><?php esc_html_e('Feature', 'tradepress'); ?></th>
                                <?php 
                                // Show all providers by default
                                foreach ($providers as $provider_id => $provider):
                                ?>
                                    <th class="provider-column provider-<?php echo esc_attr($provider_id); ?>">
                                        <?php if (!empty($provider['icon'])): ?>
                                            <img src="<?php echo esc_url(TRADEPRESS_PLUGIN_URL . 'admin/assets/images/providers/' . $provider['icon']); ?>" alt="<?php echo esc_attr($provider['name']); ?>" class="provider-icon" />
                                        <?php endif; ?>
                                        <?php echo esc_html($provider['name']); ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category['fields'] as $field_id => $field_name): ?>
                                <tr>
                                    <td class="feature-name"><?php echo esc_html($field_name); ?></td>
                                    <?php 
                                    foreach ($providers as $provider_id => $provider):
                                        $field_data = isset($comparison_data[$provider_id][$field_id]) ? $comparison_data[$provider_id][$field_id] : array('value' => 'N/A', 'notes' => '');
                                        $value = $field_data['value'];
                                        $notes = $field_data['notes'];
                                        
                                        // Apply styling based on the value
                                        $value_class = '';
                                        if (stripos($value, 'yes') !== false) {
                                            $value_class = 'value-positive';
                                        } elseif (stripos($value, 'no') !== false) {
                                            $value_class = 'value-negative';
                                        } elseif (stripos($value, 'limited') !== false) {
                                            $value_class = 'value-limited';
                                        } elseif (stripos($value, 'n/a') !== false) {
                                            $value_class = 'value-na';
                                        } elseif (stripos($value, 'pending') !== false) {
                                            $value_class = 'value-pending';
                                        }
                                    ?>
                                        <td class="provider-value provider-<?php echo esc_attr($provider_id); ?>">
                                            <div class="value <?php echo esc_attr($value_class); ?>"><?php echo esc_html($value); ?></div>
                                            <?php if (!empty($notes)): ?>
                                                <div class="notes"><?php echo esc_html($notes); ?></div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- API Capability Matrix Section -->
        <div class="comparison-category-section" id="section-api_capability_matrix">
            <h3 class="comparison-category-title"><?php esc_html_e('API Capability Matrix - Directive Data Requirements', 'tradepress'); ?></h3>
            
            <div class="comparison-table-wrapper">
                <table class="widefat comparison-table">
                    <thead>
                        <tr>
                            <th class="feature-column"><?php esc_html_e('Directive', 'tradepress'); ?></th>
                            <?php foreach ($providers as $provider_id => $provider): ?>
                                <th class="provider-column provider-<?php echo esc_attr($provider_id); ?>">
                                    <?php if (!empty($provider['icon'])): ?>
                                        <img src="<?php echo esc_url(TRADEPRESS_PLUGIN_URL . 'admin/assets/images/providers/' . $provider['icon']); ?>" alt="<?php echo esc_attr($provider['name']); ?>" class="provider-icon" />
                                    <?php endif; ?>
                                    <?php echo esc_html($provider['name']); ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/api-capability-matrix.php';
                        $matrix = TradePress_API_Capability_Matrix::get_matrix();
                        
                        // Build directive capability matrix from the new system
                        $capability_matrix = array();
                        $alpha_directives = array('adx', 'rsi', 'volume', 'cci', 'macd');
                        
                        foreach ($alpha_directives as $directive_id) {
                            $capability_matrix[$directive_id] = array(
                                'code' => strtoupper($directive_id),
                                'name' => ucfirst(str_replace('_', ' ', $directive_id)),
                                'status' => 'Alpha Release',
                                'providers' => array()
                            );
                            
                            // Get platforms that support this directive's data needs
                            $data_needs = array($directive_id, 'quote'); // Most directives need their indicator + quote data
                            
                            foreach ($providers as $provider_id => $provider) {
                                $supported = false;
                                $endpoints = array();
                                
                                foreach ($data_needs as $data_type) {
                                    if (TradePress_API_Capability_Matrix::platform_supports($provider_id, $data_type)) {
                                        $supported = true;
                                        $endpoints[] = $data_type;
                                    }
                                }
                                
                                $capability_matrix[$directive_id]['providers'][$provider_id] = array(
                                    'supported' => $supported,
                                    'status' => $supported ? 'full' : 'none',
                                    'notes' => $supported ? 'Supports required data types' : 'Missing required data',
                                    'endpoints' => $endpoints
                                );
                            }
                        }
                        
                        // Use only alpha directives for now
                        $ordered_directives = array_keys($capability_matrix);
                        
                        foreach ($ordered_directives as $directive_key): 
                            if (!isset($capability_matrix[$directive_key])) continue;
                            $directive = $capability_matrix[$directive_key];
                            $is_alpha = in_array($directive_key, $alpha_directives);
                        ?>
                            <tr class="<?php echo $is_alpha ? 'alpha-directive' : ''; ?>">
                                <td class="feature-name">
                                    <strong><?php echo esc_html($directive['code'] . ' - ' . $directive['name']); ?></strong>
                                    <div class="directive-status"><?php echo esc_html($directive['status']); ?></div>
                                    <?php if ($is_alpha): ?>
                                        <div class="alpha-badge">ALPHA RELEASE</div>
                                    <?php endif; ?>
                                </td>
                                <?php foreach ($providers as $provider_id => $provider): 
                                    $capability = isset($directive['providers'][$provider_id]) ? $directive['providers'][$provider_id] : array('supported' => false, 'status' => 'unknown', 'notes' => 'No data');
                                    
                                    $value_class = 'value-negative';
                                    $display_value = '❌ Not Supported';
                                    
                                    if ($capability['supported']) {
                                        if ($capability['status'] === 'full') {
                                            $value_class = 'value-positive';
                                            $display_value = '✅ Full Support';
                                        } elseif ($capability['status'] === 'partial') {
                                            $value_class = 'value-limited';
                                            $display_value = '⚠️ Partial Support';
                                        }
                                    }
                                ?>
                                    <td class="provider-value provider-<?php echo esc_attr($provider_id); ?>">
                                        <div class="value <?php echo esc_attr($value_class); ?>"><?php echo esc_html($display_value); ?></div>
                                        <div class="notes"><?php echo esc_html($capability['notes']); ?></div>
                                        <?php if (!empty($capability['endpoints'])): ?>
                                            <div class="endpoints">Endpoints: <?php echo esc_html(implode(', ', $capability['endpoints'])); ?></div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="matrix-benefits">
            <h4><?php esc_html_e('API Capability Matrix Benefits', 'tradepress'); ?></h4>
            <ul>
                <li><strong><?php esc_html_e('Zero per-directive coding', 'tradepress'); ?></strong> - <?php esc_html_e('Matrix handles all compatibility decisions automatically', 'tradepress'); ?></li>
                <li><strong><?php esc_html_e('Automatic provider selection', 'tradepress'); ?></strong> - <?php esc_html_e('System selects best API based on actual capabilities', 'tradepress'); ?></li>
                <li><strong><?php esc_html_e('Easy new API integration', 'tradepress'); ?></strong> - <?php esc_html_e('Just update matrix, no directive code changes needed', 'tradepress'); ?></li>
            </ul>
        </div>
        
        <div class="comparison-legend">
            <h4><?php esc_html_e('Legend', 'tradepress'); ?></h4>
            <ul>
                <li><span class="legend-item value-positive">Yes/Available</span> - <?php esc_html_e('Feature is available', 'tradepress'); ?></li>
                <li><span class="legend-item value-negative">No/Unavailable</span> - <?php esc_html_e('Feature is not available', 'tradepress'); ?></li>
                <li><span class="legend-item value-limited">Limited</span> - <?php esc_html_e('Feature is available with limitations', 'tradepress'); ?></li>
                <li><span class="legend-item value-na">N/A</span> - <?php esc_html_e('Feature is not applicable to this provider', 'tradepress'); ?></li>
                <li><span class="legend-item value-pending">Data Pending</span> - <?php esc_html_e('Information is still being compiled', 'tradepress'); ?></li>
            </ul>
        </div>
        
        <div class="comparison-notes">
            <h4><?php esc_html_e('Notes', 'tradepress'); ?></h4>
            <ol>
                <li><?php esc_html_e('This comparison is based on information available at the time of compilation and may change as services update their offerings.', 'tradepress'); ?></li>
                <li><?php esc_html_e('Free tier limitations and rate limits are particularly subject to change and should be verified with the provider.', 'tradepress'); ?></li>
                <li><?php esc_html_e('For the most accurate and up-to-date information, always consult the provider\'s official documentation.', 'tradepress'); ?></li>
                <li><?php esc_html_e('TradePress integration capabilities may differ from the full feature set of a provider. This comparison focuses on what\'s theoretically available from the API.', 'tradepress'); ?></li>
            </ol>
        </div>
    </div>
    
    <?php
}
?>
