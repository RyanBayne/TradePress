<?php
/**
 * TradePress API Directory
 * 
 * Central repository of all API providers supported by TradePress
 * 
 * @package TradePress
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TradePress_API_Directory {
    
    /**
     * Get all API providers (financial and non-financial)
     * 
     * @return array All API providers
     */
    public static function get_all_providers() {
        return array_merge(
            self::get_financial_providers(),
            self::get_non_financial_providers()
        );
    }
    
    /**
     * Get financial API providers
     * 
     * @return array Financial API providers
     */
    public static function get_financial_providers() {

        return array(
            'trading212' => array(
                'name' => 'Trading 212',
                'logo_url' => 'https://cdn.brandfetch.io/idljYeMpMH/theme/dark/logo.svg?c=1dxbfHSJFAPEGdCLU4o5B',
                'icon_url' => 'https://cdn.brandfetch.io/idljYeMpMH/w/400/h/400/theme/dark/icon.jpeg?c=1dxbfHSJFAPEGdCLU4o5B',
                'description' => 'Trading 212 is a London based fintech company that democratises the financial markets with free, smart and easy to use apps, enabling anyone to invest.',
                'url' => 'https://www.trading212.com/',
                'api_doc_url' => 'https://t212public-api-docs.redoc.ly/',
                'class_path' => 'trading212/trading212-api.php',
                'class_name' => 'TradePress_Trading212_API',
                'has_sandbox' => true,
                'auth_type' => 'oauth',
                'api_type' => 'trading', // API provides trading capabilities
                'features' => array(
                    'market_data' => true,
                    'trading' => true,
                    'account_management' => true,
                    'portfolio_tracking' => true,
                    'paper_trading' => true
                )
            ),
            'alpaca' => array(
                'name' => 'Alpaca',
                'logo_url' => 'https://cdn.brandfetch.io/id3ddNjt-I/theme/dark/logo.svg?c=1dxbfHSJFAPEGdCLU4o5B',
                'icon_url' => 'https://cdn.brandfetch.io/id3ddNjt-I/w/400/h/400/theme/dark/icon.jpeg?c=1dxbfHSJFAPEGdCLU4o5B',
                'description' => 'Commission-free stock trading API for individuals and businesses.',
                'url' => 'https://alpaca.markets/',
                'api_doc_url' => 'https://alpaca.markets/docs/api-documentation/',
                'class_path' => 'alpaca/alpaca-api.php',
                'class_name' => 'TradePress_Alpaca_API',
                'has_sandbox' => true,
                'auth_type' => 'api_key',
                'api_type' => 'trading', // API provides trading capabilities
                'features' => array(
                    'market_data' => true,
                    'trading' => true,
                    'account_management' => true,
                    'portfolio_tracking' => true,
                    'paper_trading' => true
                )
            ),
            'interactive_brokers' => array(
                'name' => 'Interactive Brokers',
                'icon' => 'interactive_brokers.png',
                'logo_url' => 'https://cdn.brandfetch.io/idcABCQwX-/theme/dark/logo.svg?c=1dxbfHSJFAPEGdCLU4o5B',
                'icon_url' => 'https://cdn.brandfetch.io/idcABCQwX-/w/400/h/400/theme/dark/icon.jpeg?c=1dxbfHSJFAPEGdCLU4o5B',
                'description' => 'Global electronic trading platform with advanced tools.',
                'url' => 'https://www.interactivebrokers.com/',
                'api_doc_url' => 'https://interactivebrokers.github.io/tws-api/',
                'class_path' => 'ibkr/ibkr-api.php',
                'class_name' => 'TradePress_IBKR_API',
                'has_sandbox' => true,
                'auth_type' => 'oauth',
                'api_type' => 'trading', // API provides trading capabilities
                'features' => array(
                    'market_data' => true,
                    'trading' => true,
                    'account_management' => true,
                    'portfolio_tracking' => true,
                    'paper_trading' => true
                )
            ),
            'etoro' => array(
                'name' => 'eToro',
                'icon' => 'etoro.png',
                'logo_url' => 'https://cdn.brandfetch.io/idFGQktZu-/theme/dark/logo.svg?c=0tptOg8WbvVTRPzZlFIbI',
                'icon_url' => 'https://cdn.brandfetch.io/idFGQktZu-/w/400/h/400/theme/dark/icon.jpeg?c=0tptOg8WbvVTRPzZlFIbI',
                'description' => 'Social trading and multi-asset brokerage company.',
                'url' => 'https://www.etoro.com/',
                'api_doc_url' => 'https://developers.etoro.com/',
                'class_path' => 'etoro/etoro-api.php',
                'class_name' => 'TradePress_Etoro_API',
                'has_sandbox' => false,
                'auth_type' => 'oauth',
                'api_type' => 'trading', // API provides trading capabilities
                'features' => array(
                    'market_data' => true,
                    'trading' => true,
                    'account_management' => true,
                    'portfolio_tracking' => true,
                    'social_trading' => true
                )
            ),
            'fidelity' => array(
                'name' => 'Fidelity',
                'icon' => 'fidelity.png',
                'logo_url' => 'https://cdn.brandfetch.io/idI5o-FgGG/theme/dark/logo.svg?c=37_uDmCWV9s-Cf_OAsTVD',
                'icon_url' => 'https://cdn.brandfetch.io/idI5o-FgGG/w/400/h/400/theme/dark/icon.jpeg?c=37_uDmCWV9s-Cf_OAsTVD',
                'description' => 'Large financial services provider.',
                'url' => 'https://www.fidelity.com/',
                'api_doc_url' => 'https://developer.fidelity.com/',
                'class_path' => 'fidelity/fidelity-api.php',
                'class_name' => 'TradePress_Fidelity_API',
                'has_sandbox' => true,
                'auth_type' => 'api_key',
                'api_type' => 'trading', // API provides trading capabilities
                'features' => array(
                    'market_data' => true,
                    'trading' => true,
                    'account_management' => true,
                    'portfolio_tracking' => true,
                    'retirement_accounts' => true
                )
            ),
            'polygon' => array(
                'name' => 'Polygon',
                'icon' => 'polygon.png',
                'logo_url' => 'https://cdn.brandfetch.io/idq4xE_gZr/theme/dark/logo.svg?c=2xUxcxEoqnKrUeFSQnbgO',
                'icon_url' => 'https://cdn.brandfetch.io/idq4xE_gZr/w/400/h/400/theme/dark/icon.jpeg?c=2xUxcxEoqnKrUeFSQnbgO',
                'description' => 'Real-time and historical market data API.',
                'url' => 'https://polygon.io/',
                'api_doc_url' => 'https://polygon.io/docs/',
                'class_path' => 'polygon/polygon-api.php',
                'class_name' => 'TradePress_Polygon_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'real_time' => true,
                    'historical_data' => true,
                    'options_data' => true,
                    'news' => true
                )
            ),
            'tradier' => array(
                'name' => 'Tradier',
                'icon' => 'tradier.png',
                'logo_url' => 'https://cdn.brandfetch.io/idOoBQaCAK/theme/dark/logo.svg?c=2Qfm6QYM0WB2bRgYwM_2C',
                'icon_url' => 'https://cdn.brandfetch.io/idOoBQaCAK/w/400/h/400/theme/dark/icon.jpeg?c=2Qfm6QYM0WB2bRgYwM_2C',
                'description' => 'Brokerage API for trading and market data.',
                'url' => 'https://developer.tradier.com/',
                'api_doc_url' => 'https://developer.tradier.com/documentation',
                'class_path' => 'tradier/tradier-api.php',
                'class_name' => 'TradePress_Tradier_API',
                'has_sandbox' => true,
                'auth_type' => 'oauth',
                'api_type' => 'trading', // API provides trading capabilities
                'features' => array(
                    'market_data' => true,
                    'trading' => true,
                    'account_management' => true,
                    'options_trading' => true
                )
            ),
            'twelvedata' => array(
                'name' => 'Twelve Data',
                'icon' => 'twelvedata.png',
                'logo_url' => 'https://cdn.brandfetch.io/idFx1d3FLO/theme/dark/logo.svg?c=1K0lVp4gYmgYjmR28uRxO',
                'icon_url' => 'https://cdn.brandfetch.io/idFx1d3FLO/w/400/h/400/theme/dark/icon.jpeg?c=1K0lVp4gYmgYjmR28uRxO',
                'description' => 'Financial data API for stocks, forex, and cryptocurrencies.',
                'url' => 'https://twelvedata.com/',
                'api_doc_url' => 'https://twelvedata.com/docs',
                'class_path' => 'twelvedata/twelvedata-api.php',
                'class_name' => 'TradePress_TwelveData_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'real_time' => true,
                    'historical_data' => true,
                    'technical_indicators' => true,
                    'forex' => true,
                    'crypto' => true
                )
            ),
            'iexcloud' => array(
                'name' => 'IEX Cloud',
                'icon' => 'iexcloud.png',
                'logo_url' => 'https://cdn.brandfetch.io/id70OMeZZE/theme/dark/logo.svg?c=0QNP1nHI5NB5-FZkh0Q-Z',
                'icon_url' => 'https://cdn.brandfetch.io/id70OMeZZE/w/400/h/400/theme/dark/icon.jpeg?c=0QNP1nHI5NB5-FZkh0Q-Z',
                'description' => 'Financial data platform and API.',
                'url' => 'https://iexcloud.io/',
                'api_doc_url' => 'https://iexcloud.io/docs/api/',
                'class_path' => 'iexcloud/iexcloud-api.php',
                'class_name' => 'TradePress_IEXCloud_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'real_time' => true,
                    'historical_data' => true,
                    'fundamentals' => true,
                    'news' => true,
                    'analyst_ratings' => true
                )
            ),
            'alphavantage' => array(
                'name' => 'Alpha Vantage',
                'icon' => 'alphavantage.png',
                'logo_url' => 'https://cdn.brandfetch.io/id7uTkOd1G/theme/dark/logo.svg?c=1KmbvCDYOhm7QjwB_nAwt',
                'icon_url' => 'https://cdn.brandfetch.io/id7uTkOd1G/w/400/h/400/theme/dark/icon.jpeg?c=1KmbvCDYOhm7QjwB_nAwt',
                'description' => 'Financial data API for stocks, forex, and cryptocurrencies.',
                'url' => 'https://www.alphavantage.co/',
                'api_doc_url' => 'https://www.alphavantage.co/documentation/',
                'class_path' => 'alphavantage/alphavantage-api.php',
                'class_name' => 'TradePress_AlphaVantage_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'historical_data' => true,
                    'technical_indicators' => true,
                    'fundamentals' => true,
                    'forex' => true,
                    'crypto' => true,
                    'economic_indicators' => true
                )
            ),
            'alltick' => array(
                'name' => 'AllTick',
                'icon' => 'alltick.png',
                'logo_url' => 'https://www.alltick.co/alltick.png', // No Brandfetch entry, keeping original URL
                'icon_url' => 'https://www.alltick.co/alltick-icon.jpg', // No Brandfetch entry, keeping original URL
                'description' => 'Real-time and historical market data API.',
                'url' => 'https://www.alltick.co/',
                'api_doc_url' => 'https://www.alltick.co/api',
                'class_path' => 'alltick/alltick-api.php',
                'class_name' => 'TradePress_AllTick_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'real_time' => true,
                    'historical_data' => true
                )
            ),
            'eodhd' => array(
                'name' => 'EOD Historical Data',
                'icon' => 'eodhd.png',
                'logo_url' => 'https://eodhistoricaldata.com/img/logo.png', // No Brandfetch entry, keeping original URL
                'icon_url' => 'https://eodhistoricaldata.com/img/favicon.png', // No Brandfetch entry, keeping original URL
                'description' => 'Historical data API for stocks, forex, and cryptocurrencies.',
                'url' => 'https://eodhistoricaldata.com/',
                'api_doc_url' => 'https://eodhistoricaldata.com/api-docs/',
                'class_path' => 'eodhd/eodhd-api.php',
                'class_name' => 'TradePress_EODHD_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'historical_data' => true,
                    'fundamentals' => true,
                    'screener' => true,
                    'news' => true
                )
            ),
            'finnhub' => array(
                'name' => 'Finnhub',
                'icon' => 'finnhub.png',
                'logo_url' => 'https://cdn.brandfetch.io/idyFTHa8Zo/theme/dark/logo.svg?c=30rrbvP67fZ3DX2FgZfC4',
                'icon_url' => 'https://cdn.brandfetch.io/idyFTHa8Zo/w/400/h/400/theme/dark/icon.jpeg?c=30rrbvP67fZ3DX2FgZfC4',
                'description' => 'Financial data API for stocks, forex, and cryptocurrencies.',
                'url' => 'https://finnhub.io/',
                'api_doc_url' => 'https://finnhub.io/docs/api',
                'class_path' => 'finnhub/finnhub-api.php',
                'class_name' => 'TradePress_Finnhub_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'real_time' => true,
                    'historical_data' => true,
                    'fundamentals' => true, 
                    'news' => true,
                    'sentiment' => true,
                    'technical_indicators' => true,
                    'economic_calendar' => true,
                    'institutional_data' => true,
                    'websocket_support' => true,
                    'webhook_support' => true
                )
            ),
            'fmp' => array(
                'name' => 'Financial Modeling Prep',
                'icon' => 'fmp.png',
                'logo_url' => 'https://financialmodelingprep.com/assets/images/logo.png', // No Brandfetch entry, keeping original URL
                'icon_url' => 'https://financialmodelingprep.com/assets/images/favicon.png', // No Brandfetch entry, keeping original URL
                'description' => 'Financial data and APIs for investors and developers.',
                'url' => 'https://financialmodelingprep.com/',
                'api_doc_url' => 'https://financialmodelingprep.com/developer/docs/',
                'class_path' => 'fmp/fmp-api.php',
                'class_name' => 'TradePress_FMP_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'historical_data' => true,
                    'fundamentals' => true,
                    'financial_statements' => true,
                    'analyst_estimates' => true,
                    'price_targets' => true
                )
            ),
            'intrinio' => array(
                'name' => 'Intrinio',
                'icon' => 'intrinio.png',
                'logo_url' => 'https://cdn.brandfetch.io/idvN3BAZ7q/theme/dark/logo.svg?c=2AWI_qAzgkWtjzMbDiuLc',
                'icon_url' => 'https://cdn.brandfetch.io/idvN3BAZ7q/w/400/h/400/theme/dark/icon.jpeg?c=2AWI_qAzgkWtjzMbDiuLc',
                'description' => 'Financial data API with coverage of stocks, options, and more.',
                'url' => 'https://intrinio.com/',
                'api_doc_url' => 'https://docs.intrinio.com/',
                'class_path' => 'intrinio/intrinio-api.php',
                'class_name' => 'TradePress_Intrinio_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'historical_data' => true,
                    'fundamentals' => true,
                    'options_data' => true,
                    'company_data' => true
                )
            ),
            'marketstack' => array(
                'name' => 'MarketStack',
                'icon' => 'marketstack.png',
                'logo_url' => 'https://marketstack.com/site_images/marketstack-logo.svg', // No Brandfetch entry, keeping original URL
                'icon_url' => 'https://marketstack.com/site_images/favicon.png', // No Brandfetch entry, keeping original URL
                'description' => 'Real-time and historical market data API.',
                'url' => 'https://marketstack.com/',
                'api_doc_url' => 'https://marketstack.com/documentation',
                'class_path' => 'marketstack/marketstack-api.php',
                'class_name' => 'TradePress_MarketStack_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'historical_data' => true,
                    'eod_data' => true
                )
            ),
            'tradingview' => array(
                'name' => 'TradingView',
                'icon' => 'tradingview.png',
                'logo_url' => 'https://cdn.brandfetch.io/idvIx_kMiS/theme/dark/logo.svg?c=06U22mvMGGQRRhDX8SJJY',
                'icon_url' => 'https://cdn.brandfetch.io/idvIx_kMiS/w/400/h/400/theme/dark/icon.jpeg?c=06U22mvMGGQRRhDX8SJJY',
                'description' => 'Financial visualization platform and social network.',
                'url' => 'https://www.tradingview.com/',
                'api_doc_url' => 'https://www.tradingview.com/brokerage-integration/',
                'class_path' => 'tradingview/tradingview-api.php',
                'class_name' => 'TradePress_TradingView_API',
                'has_sandbox' => false,
                'auth_type' => 'api_key',
                'api_type' => 'data_only', // API provides only data, no trading
                'features' => array(
                    'market_data' => true,
                    'charting' => true,
                    'technical_indicators' => true,
                    'social' => true,
                    'ideas' => true
                )
            ),
            'tradingapi' => array(
                'name' => 'Trading API',
                'icon' => 'tradingapi.png',
                'logo_url' => 'https://via.placeholder.com/200x100?text=TradingAPI', // Generic, keeping original placeholder
                'icon_url' => 'https://via.placeholder.com/32x32?text=TA', // Generic, keeping original placeholder
                'description' => 'API for trading stocks, options, and more.',
                'url' => 'https://tradingapi.com/',
                'api_doc_url' => 'https://tradingapi.com/docs',
                'class_path' => 'tradingapi/tradingapi-api.php',
                'class_name' => 'TradePress_TradingAPI_API',
                'has_sandbox' => true,
                'auth_type' => 'api_key',
                'api_type' => 'trading', // API provides trading capabilities
                'features' => array(
                    'market_data' => true,
                    'trading' => true,
                    'account_management' => true
                )
            ),
            'webull' => array(
                'name' => 'WeBull',
                'icon' => 'webull.png',
                'logo_url' => 'https://cdn.brandfetch.io/id3ZoTKY8M/theme/dark/logo.svg?c=2Nfn9NyC9f7nCMa-sMvM5',
                'icon_url' => 'https://cdn.brandfetch.io/id3ZoTKY8M/w/400/h/400/theme/dark/icon.jpeg?c=2Nfn9NyC9f7nCMa-sMvM5',
                'description' => 'Commission-free online broker with advanced trading tools.',
                'url' => 'https://www.webull.com/',
                'api_doc_url' => 'https://www.webull.com/api',
                'class_path' => 'webull/webull-api.php',
                'class_name' => 'TradePress_WeBull_API',
                'has_sandbox' => true,
                'auth_type' => 'oauth',
                'api_type' => 'trading', // API provides trading capabilities
                'features' => array(
                    'market_data' => true,
                    'trading' => true,
                    'account_management' => true,
                    'paper_trading' => true,
                    'options_trading' => true
                )
            )
        );
    }
    
    /**
     * Get non-financial API providers (social platforms, etc.)
     * 
     * @return array Non-financial API providers
     */
    public static function get_non_financial_providers() {
        return array(
            'telegram' => array(
                'name' => 'Telegram',
                'icon' => 'telegram.png',
                'description' => 'Cloud-based instant messaging service.',
                'url' => 'https://telegram.org/',
                'api_doc_url' => 'https://core.telegram.org/bots/api',
                'class_path' => 'telegram/telegram-api.php',
                'class_name' => 'TradePress_Telegram_API',
                'has_sandbox' => false,
                'auth_type' => 'bot_token',
                'api_type' => 'messaging', // API provides messaging capabilities
                'features' => array(
                    'messaging' => true,
                    'notifications' => true,
                    'automation' => true
                )
            ),
            'discord' => array(
                'name' => 'Discord',
                'icon' => 'discord.png',
                'description' => 'Voice, video and text communication service.',
                'url' => 'https://discord.com/',
                'api_doc_url' => 'https://discord.com/developers/docs/intro',
                'class_path' => 'discord/discord-api.php',
                'class_name' => 'TRADEPRESS_DISCORD_API',
                'has_sandbox' => false,
                'auth_type' => 'bot_token',
                'api_type' => 'messaging', // API provides messaging capabilities
                'features' => array(
                    'messaging' => true,
                    'notifications' => true,
                    'voice' => true,
                    'server_management' => true
                )
            )
        );
    }
    
    /**
     * Get API provider details by ID
     * 
     * @param string $provider_id The provider ID to look up
     * @return array|false Provider details or false if not found
     */
    public static function get_provider($provider_id) {
        $all_providers = self::get_all_providers();
        
        return isset($all_providers[$provider_id]) ? $all_providers[$provider_id] : false;
    }
}

/**
 * Remove Yahoo Finance from the financial providers list
 *
 * @param array $providers The array of financial providers
 * @return array Modified array of providers with Yahoo Finance removed
 */
function tradepress_remove_yahoofinance_provider($providers) {
    if (isset($providers['yahoofinance'])) {
        unset($providers['yahoofinance']);
    }
    return $providers;
}
add_filter('tradepress_financial_providers', 'tradepress_remove_yahoofinance_provider', 20);

