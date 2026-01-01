<?php
/**
 * TradePress Alpha Vantage API Endpoints
 *
 * Defines endpoints and parameters for the Alpha Vantage market data service
 *
 * @package TradePress
 * @subpackage API\AlphaVantage
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Alpha Vantage API Endpoints class
 */
class TradePress_AlphaVantage_Endpoints {
    
    /**
     * Get all available endpoints
     *
     * @return array Array of available endpoints with their configurations
     */
    public static function get_endpoints() {
        return array(
            'TIME_SERIES_INTRADAY' => array(
                'function' => 'TIME_SERIES_INTRADAY',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('outputsize', 'datatype'),
                'description' => 'Intraday time series data',
                'intervals' => array('1min', '5min', '15min', '30min', '60min'),
            ),
            'TIME_SERIES_DAILY' => array(
                'function' => 'TIME_SERIES_DAILY',
                'required_params' => array('symbol'),
                'optional_params' => array('outputsize', 'datatype'),
                'description' => 'Daily time series data',
            ),
            'TIME_SERIES_DAILY_ADJUSTED' => array(
                'function' => 'TIME_SERIES_DAILY_ADJUSTED',
                'required_params' => array('symbol'),
                'optional_params' => array('outputsize', 'datatype'),
                'description' => 'Daily adjusted time series data',
            ),
            'TIME_SERIES_WEEKLY' => array(
                'function' => 'TIME_SERIES_WEEKLY',
                'required_params' => array('symbol'),
                'optional_params' => array('datatype'),
                'description' => 'Weekly time series data',
            ),
            'TIME_SERIES_WEEKLY_ADJUSTED' => array(
                'function' => 'TIME_SERIES_WEEKLY_ADJUSTED',
                'required_params' => array('symbol'),
                'optional_params' => array('datatype'),
                'description' => 'Weekly adjusted time series data',
            ),
            'TIME_SERIES_MONTHLY' => array(
                'function' => 'TIME_SERIES_MONTHLY',
                'required_params' => array('symbol'),
                'optional_params' => array('datatype'),
                'description' => 'Monthly time series data',
            ),
            'TIME_SERIES_MONTHLY_ADJUSTED' => array(
                'function' => 'TIME_SERIES_MONTHLY_ADJUSTED',
                'required_params' => array('symbol'),
                'optional_params' => array('datatype'),
                'description' => 'Monthly adjusted time series data',
            ),
            'GLOBAL_QUOTE' => array(
                'function' => 'GLOBAL_QUOTE',
                'required_params' => array('symbol'),
                'optional_params' => array('datatype'),
                'description' => 'Global quote for a symbol',
            ),
            'SYMBOL_SEARCH' => array(
                'function' => 'SYMBOL_SEARCH',
                'required_params' => array('keywords'),
                'optional_params' => array('datatype'),
                'description' => 'Search for symbols',
            ),
            'OVERVIEW' => array(
                'function' => 'OVERVIEW',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Company information and fundamental data',
            ),
            'INCOME_STATEMENT' => array(
                'function' => 'INCOME_STATEMENT',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Annual and quarterly income statements',
            ),
            'BALANCE_SHEET' => array(
                'function' => 'BALANCE_SHEET',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Annual and quarterly balance sheets',
            ),
            'CASH_FLOW' => array(
                'function' => 'CASH_FLOW',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Annual and quarterly cash flows',
            ),
            'EARNINGS_CALENDAR' => array(
                'function' => 'EARNINGS_CALENDAR',
                'required_params' => array(),
                'optional_params' => array('symbol', 'horizon'),
                'description' => 'Upcoming earnings announcements',
                'horizons' => array('3month', '6month', '12month'),
                'default_horizon' => '3month',
                'version' => '2',
                'category' => 'Fundamental Data',
            ),
            'EARNINGS' => array(
                'function' => 'EARNINGS',
                'required_params' => array('symbol'),
                'optional_params' => array(),
                'description' => 'Historical quarterly earnings data for a specified company',
                'version' => '2',
                'category' => 'Fundamental Data',
            ),
            'TIME_SERIES_INTRADAY_EXTENDED' => array(
                'function' => 'TIME_SERIES_INTRADAY_EXTENDED',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('slice'),
                'description' => 'Extended intraday time series data',
                'intervals' => array('1min', '5min', '15min', '30min', '60min'),
                'category' => 'Core Stock APIs',
            ),
            'MARKET_STATUS' => array(
                'function' => 'MARKET_STATUS',
                'required_params' => array(),
                'optional_params' => array(),
                'description' => 'Overview of market status, including primary exchanges',
                'category' => 'Core Stock APIs',
            ),
            'NEWS_SENTIMENT' => array(
                'function' => 'NEWS_SENTIMENT',
                'required_params' => array(),
                'optional_params' => array('tickers', 'topics', 'time_from', 'time_to', 'sort', 'limit'),
                'description' => 'Live and historical news sentiment scores for stocks',
                'category' => 'Alpha Intelligence',
            ),
            'TOP_GAINERS_LOSERS' => array(
                'function' => 'TOP_GAINERS_LOSERS',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Top N gainers, losers, and most actively traded U.S. stocks',
                'category' => 'Alpha Intelligence',
            ),
            'LISTING_STATUS' => array(
                'function' => 'LISTING_STATUS',
                'required_params' => array(),
                'optional_params' => array('date', 'state', 'datatype'),
                'description' => 'List of active and delisted stocks',
                'category' => 'Fundamental Data',
            ),
            'IPO_CALENDAR' => array(
                'function' => 'IPO_CALENDAR',
                'required_params' => array(),
                'optional_params' => array('horizon'),
                'description' => 'Upcoming IPO calendar data',
                'horizons' => array('3month', '6month', '12month'),
                'default_horizon' => '3month',
                'category' => 'Fundamental Data',
            ),
            'CURRENCY_EXCHANGE_RATE' => array(
                'function' => 'CURRENCY_EXCHANGE_RATE',
                'required_params' => array('from_currency', 'to_currency'),
                'optional_params' => array('datatype'),
                'description' => 'Realtime exchange rate for any pair of digital or physical currency',
                'category' => 'Forex',
            ),
            'FX_INTRADAY' => array(
                'function' => 'FX_INTRADAY',
                'required_params' => array('from_symbol', 'to_symbol', 'interval'),
                'optional_params' => array('outputsize', 'datatype'),
                'description' => 'Intraday time series for a forex pair',
                'intervals' => array('1min', '5min', '15min', '30min', '60min'),
                'category' => 'Forex',
            ),
            'FX_DAILY' => array(
                'function' => 'FX_DAILY',
                'required_params' => array('from_symbol', 'to_symbol'),
                'optional_params' => array('outputsize', 'datatype'),
                'description' => 'Daily time series for a forex pair',
                'category' => 'Forex',
            ),
            'FX_WEEKLY' => array(
                'function' => 'FX_WEEKLY',
                'required_params' => array('from_symbol', 'to_symbol'),
                'optional_params' => array('datatype'),
                'description' => 'Weekly time series for a forex pair',
                'category' => 'Forex',
            ),
            'FX_MONTHLY' => array(
                'function' => 'FX_MONTHLY',
                'required_params' => array('from_symbol', 'to_symbol'),
                'optional_params' => array('datatype'),
                'description' => 'Monthly time series for a forex pair',
                'category' => 'Forex',
            ),
            'CRYPTO_INTRADAY' => array(
                'function' => 'CRYPTO_INTRADAY',
                'required_params' => array('symbol', 'market', 'interval'),
                'optional_params' => array('outputsize', 'datatype'),
                'description' => 'Intraday time series for a cryptocurrency',
                'intervals' => array('1min', '5min', '15min', '30min', '60min'),
                'category' => 'Cryptocurrency',
            ),
            'DIGITAL_CURRENCY_DAILY' => array(
                'function' => 'DIGITAL_CURRENCY_DAILY',
                'required_params' => array('symbol', 'market'),
                'optional_params' => array('datatype'),
                'description' => 'Daily time series for a cryptocurrency',
                'category' => 'Cryptocurrency',
            ),
            'DIGITAL_CURRENCY_WEEKLY' => array(
                'function' => 'DIGITAL_CURRENCY_WEEKLY',
                'required_params' => array('symbol', 'market'),
                'optional_params' => array('datatype'),
                'description' => 'Weekly time series for a cryptocurrency',
                'category' => 'Cryptocurrency',
            ),
            'DIGITAL_CURRENCY_MONTHLY' => array(
                'function' => 'DIGITAL_CURRENCY_MONTHLY',
                'required_params' => array('symbol', 'market'),
                'optional_params' => array('datatype'),
                'description' => 'Monthly time series for a cryptocurrency',
                'category' => 'Cryptocurrency',
            ),
            'CRYPTO_RATING' => array(
                'function' => 'CRYPTO_RATING',
                'required_params' => array('symbol'),
                'optional_params' => array('datatype'),
                'description' => 'Cryptocurrency health scores/ratings',
                'category' => 'Cryptocurrency',
            ),
            'WTI' => array(
                'function' => 'WTI',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'West Texas Intermediate (WTI) crude oil prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'BRENT' => array(
                'function' => 'BRENT',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Brent crude oil prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'NATURAL_GAS' => array(
                'function' => 'NATURAL_GAS',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Natural gas prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'COPPER' => array(
                'function' => 'COPPER',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Copper prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'ALUMINUM' => array(
                'function' => 'ALUMINUM',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Aluminum prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'WHEAT' => array(
                'function' => 'WHEAT',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Wheat prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'CORN' => array(
                'function' => 'CORN',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Corn prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'COTTON' => array(
                'function' => 'COTTON',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Cotton prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'SUGAR' => array(
                'function' => 'SUGAR',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Sugar prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'COFFEE' => array(
                'function' => 'COFFEE',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Coffee prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'ALL_COMMODITIES' => array(
                'function' => 'ALL_COMMODITIES',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Various commodities prices',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Commodities',
            ),
            'REAL_GDP' => array(
                'function' => 'REAL_GDP',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Real Gross Domestic Product (GDP) data',
                'intervals' => array('annual', 'quarterly'),
                'default_interval' => 'annual',
                'category' => 'Economic Indicators',
            ),
            'REAL_GDP_PER_CAPITA' => array(
                'function' => 'REAL_GDP_PER_CAPITA',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Real GDP per capita data',
                'category' => 'Economic Indicators',
            ),
            'TREASURY_YIELD' => array(
                'function' => 'TREASURY_YIELD',
                'required_params' => array('interval'),
                'optional_params' => array('maturity', 'datatype'),
                'description' => 'Treasury yield data',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'maturities' => array('3month', '2year', '5year', '7year', '10year', '30year'),
                'category' => 'Economic Indicators',
            ),
            'FEDERAL_FUNDS_RATE' => array(
                'function' => 'FEDERAL_FUNDS_RATE',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Federal funds interest rate data',
                'intervals' => array('daily', 'weekly', 'monthly'),
                'default_interval' => 'monthly',
                'category' => 'Economic Indicators',
            ),
            'CPI' => array(
                'function' => 'CPI',
                'required_params' => array(),
                'optional_params' => array('interval', 'datatype'),
                'description' => 'Consumer Price Index (CPI) data',
                'intervals' => array('monthly', 'semiannual'),
                'default_interval' => 'monthly',
                'category' => 'Economic Indicators',
            ),
            'INFLATION' => array(
                'function' => 'INFLATION',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Inflation data',
                'category' => 'Economic Indicators',
            ),
            'INFLATION_EXPECTATION' => array(
                'function' => 'INFLATION_EXPECTATION',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Inflation expectation data',
                'category' => 'Economic Indicators',
            ),
            'CONSUMER_SENTIMENT' => array(
                'function' => 'CONSUMER_SENTIMENT',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Consumer sentiment data',
                'category' => 'Economic Indicators',
            ),
            'RETAIL_SALES' => array(
                'function' => 'RETAIL_SALES',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Retail sales data',
                'category' => 'Economic Indicators',
            ),
            'DURABLES' => array(
                'function' => 'DURABLES',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Durable Goods Orders data',
                'category' => 'Economic Indicators',
            ),
            'UNEMPLOYMENT' => array(
                'function' => 'UNEMPLOYMENT',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Unemployment Rate data',
                'category' => 'Economic Indicators',
            ),
            'NONFARM_PAYROLL' => array(
                'function' => 'NONFARM_PAYROLL',
                'required_params' => array(),
                'optional_params' => array('datatype'),
                'description' => 'Nonfarm Payroll data',
                'category' => 'Economic Indicators',
            ),
            'SMA' => array(
                'function' => 'SMA',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Simple Moving Average',
                'category' => 'Technical Indicators',
            ),
            'EMA' => array(
                'function' => 'EMA',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Exponential Moving Average',
                'category' => 'Technical Indicators',
            ),
            'WMA' => array(
                'function' => 'WMA',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Weighted Moving Average',
                'category' => 'Technical Indicators',
            ),
            'DEMA' => array(
                'function' => 'DEMA',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Double Exponential Moving Average',
                'category' => 'Technical Indicators',
            ),
            'TEMA' => array(
                'function' => 'TEMA',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Triple Exponential Moving Average',
                'category' => 'Technical Indicators',
            ),
            'TRIMA' => array(
                'function' => 'TRIMA',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Triangular Moving Average',
                'category' => 'Technical Indicators',
            ),
            'KAMA' => array(
                'function' => 'KAMA',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Kaufman Adaptive Moving Average',
                'category' => 'Technical Indicators',
            ),
            'MAMA' => array(
                'function' => 'MAMA',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('fastlimit', 'slowlimit', 'datatype'),
                'description' => 'MESA Adaptive Moving Average',
                'category' => 'Technical Indicators',
            ),
            'VWAP' => array(
                'function' => 'VWAP',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('datatype'),
                'description' => 'Volume Weighted Average Price',
                'category' => 'Technical Indicators',
            ),
            'T3' => array(
                'function' => 'T3',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('vfactor', 'datatype'),
                'description' => 'Triple Exponential Moving Average (T3)',
                'category' => 'Technical Indicators',
            ),
            'BBANDS' => array(
                'function' => 'BBANDS',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('nbdevup', 'nbdevdn', 'matype', 'datatype'),
                'description' => 'Bollinger Bands',
                'category' => 'Technical Indicators',
            ),
            'MIDPOINT' => array(
                'function' => 'MIDPOINT',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'MidPoint over period',
                'category' => 'Technical Indicators',
            ),
            'MIDPRICE' => array(
                'function' => 'MIDPRICE',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Midpoint Price over period (uses high/low)',
                'category' => 'Technical Indicators',
            ),
            'SAR' => array(
                'function' => 'SAR',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('acceleration', 'maximum', 'datatype'),
                'description' => 'Parabolic SAR',
                'category' => 'Technical Indicators',
            ),
            'SAREXT' => array(
                'function' => 'SAREXT',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('startvalue', 'offsetonreverse', 'accelerationinitlong', 'accelerationlong', 'accelerationmaxlong', 'accelerationinitshort', 'accelerationshort', 'accelerationmaxshort', 'datatype'),
                'description' => 'Parabolic SAR - Extended',
                'category' => 'Technical Indicators',
            ),
            // Momentum Indicators
            'MACD' => array(
                'function' => 'MACD',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('fastperiod', 'slowperiod', 'signalperiod', 'datatype'),
                'description' => 'Moving Average Convergence/Divergence',
                'category' => 'Technical Indicators',
            ),
            'MACDEXT' => array(
                'function' => 'MACDEXT',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('fastperiod', 'slowperiod', 'signalperiod', 'fastmatype', 'slowmatype', 'signalmatype', 'datatype'),
                'description' => 'MACD with controllable MA type',
                'category' => 'Technical Indicators',
            ),
            'STOCH' => array(
                'function' => 'STOCH',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('fastkperiod', 'slowkperiod', 'slowdperiod', 'slowkmatype', 'slowdmatype', 'datatype'),
                'description' => 'Stochastic Oscillator',
                'category' => 'Technical Indicators',
            ),
            'STOCHF' => array(
                'function' => 'STOCHF',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('fastkperiod', 'fastdperiod', 'fastdmatype', 'datatype'),
                'description' => 'Stochastic Fast',
                'category' => 'Technical Indicators',
            ),
            'RSI' => array(
                'function' => 'RSI',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Relative Strength Index',
                'category' => 'Technical Indicators',
            ),
            'STOCHRSI' => array(
                'function' => 'STOCHRSI',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('fastkperiod', 'fastdperiod', 'fastdmatype', 'datatype'),
                'description' => 'Stochastic Relative Strength Index',
                'category' => 'Technical Indicators',
            ),
            'WILLR' => array(
                'function' => 'WILLR',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => "Williams' %%R",
                'category' => 'Technical Indicators',
            ),
            'ADX' => array(
                'function' => 'ADX',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Average Directional Movement Index',
                'category' => 'Technical Indicators',
            ),
            'ADXR' => array(
                'function' => 'ADXR',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Average Directional Movement Index Rating',
                'category' => 'Technical Indicators',
            ),
            'APO' => array(
                'function' => 'APO',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('fastperiod', 'slowperiod', 'matype', 'datatype'),
                'description' => 'Absolute Price Oscillator',
                'category' => 'Technical Indicators',
            ),
            'PPO' => array(
                'function' => 'PPO',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('fastperiod', 'slowperiod', 'matype', 'datatype'),
                'description' => 'Percentage Price Oscillator',
                'category' => 'Technical Indicators',
            ),
            'MOM' => array(
                'function' => 'MOM',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Momentum',
                'category' => 'Technical Indicators',
            ),
            'BOP' => array(
                'function' => 'BOP',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('datatype'),
                'description' => 'Balance Of Power',
                'category' => 'Technical Indicators',
            ),
            'CCI' => array(
                'function' => 'CCI',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Commodity Channel Index',
                'category' => 'Technical Indicators',
            ),
            'CMO' => array(
                'function' => 'CMO',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Chande Momentum Oscillator',
                'category' => 'Technical Indicators',
            ),
            'ROC' => array(
                'function' => 'ROC',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Rate of change',
                'category' => 'Technical Indicators',
            ),
            'ROCR' => array(
                'function' => 'ROCR',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Rate of change ratio',
                'category' => 'Technical Indicators',
            ),
            'AROON' => array(
                'function' => 'AROON',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Aroon',
                'category' => 'Technical Indicators',
            ),
            'AROONOSC' => array(
                'function' => 'AROONOSC',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Aroon Oscillator',
                'category' => 'Technical Indicators',
            ),
            'MFI' => array(
                'function' => 'MFI',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Money Flow Index',
                'category' => 'Technical Indicators',
            ),
            'TRIX' => array(
                'function' => 'TRIX',
                'required_params' => array('symbol', 'interval', 'time_period', 'series_type'),
                'optional__params' => array('datatype'),
                'description' => '1-day ROC of a Triple Smooth EMA',
                'category' => 'Technical Indicators',
            ),
            'ULTOSC' => array(
                'function' => 'ULTOSC',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('timeperiod1', 'timeperiod2', 'timeperiod3', 'datatype'),
                'description' => 'Ultimate Oscillator',
                'category' => 'Technical Indicators',
            ),
            'DX' => array(
                'function' => 'DX',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Directional Movement Index',
                'category' => 'Technical Indicators',
            ),
            'MINUS_DI' => array(
                'function' => 'MINUS_DI',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Minus Directional Indicator',
                'category' => 'Technical Indicators',
            ),
            'PLUS_DI' => array(
                'function' => 'PLUS_DI',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Plus Directional Indicator',
                'category' => 'Technical Indicators',
            ),
            'MINUS_DM' => array(
                'function' => 'MINUS_DM',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Minus Directional Movement',
                'category' => 'Technical Indicators',
            ),
            'PLUS_DM' => array(
                'function' => 'PLUS_DM',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Plus Directional Movement',
                'category' => 'Technical Indicators',
            ),
            'OBV' => array(
                'function' => 'OBV',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('datatype'),
                'description' => 'On Balance Volume',
                'category' => 'Technical Indicators',
            ),
            'AD' => array(
                'function' => 'AD',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('datatype'),
                'description' => 'Chaikin A/D Line',
                'category' => 'Technical Indicators',
            ),
            'ADOSC' => array(
                'function' => 'ADOSC',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('fastperiod', 'slowperiod', 'datatype'),
                'description' => 'Chaikin A/D Oscillator',
                'category' => 'Technical Indicators',
            ),
            'TRANGE' => array(
                'function' => 'TRANGE',
                'required_params' => array('symbol', 'interval'),
                'optional_params' => array('datatype'),
                'description' => 'True Range',
                'category' => 'Technical Indicators',
            ),
            'ATR' => array(
                'function' => 'ATR',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Average True Range',
                'category' => 'Technical Indicators',
            ),
            'NATR' => array(
                'function' => 'NATR',
                'required_params' => array('symbol', 'interval', 'time_period'),
                'optional_params' => array('datatype'),
                'description' => 'Normalized Average True Range',
                'category' => 'Technical Indicators',
            ),
            'HT_TRENDLINE' => array(
                'function' => 'HT_TRENDLINE',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Hilbert Transform - Instantaneous Trendline',
                'category' => 'Technical Indicators',
            ),
            'HT_SINE' => array(
                'function' => 'HT_SINE',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Hilbert Transform - SineWave',
                'category' => 'Technical Indicators',
            ),
            'HT_TRENDMODE' => array(
                'function' => 'HT_TRENDMODE',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Hilbert Transform - Trend vs Cycle Mode',
                'category' => 'Technical Indicators',
            ),
            'HT_DCPERIOD' => array(
                'function' => 'HT_DCPERIOD',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Hilbert Transform - Dominant Cycle Period',
                'category' => 'Technical Indicators',
            ),
            'HT_DCPHASE' => array(
                'function' => 'HT_DCPHASE',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Hilbert Transform - Dominant Cycle Phase',
                'category' => 'Technical Indicators',
            ),
            'HT_PHASOR' => array(
                'function' => 'HT_PHASOR',
                'required_params' => array('symbol', 'interval', 'series_type'),
                'optional_params' => array('datatype'),
                'description' => 'Hilbert Transform - Phasor Components',
                'category' => 'Technical Indicators',
            ),
        );
    }
    
    /**
     * Get endpoint configuration by name
     *
     * @param string $endpoint_name The name of the endpoint
     * @return array|false Endpoint configuration or false if not found
     */
    public static function get_endpoint($endpoint_name) {
        $endpoints = self::get_endpoints();
        return isset($endpoints[$endpoint_name]) ? $endpoints[$endpoint_name] : false;
    }
    
    /**
     * Get endpoint URL
     *
     * @param string $endpoint_name The name of the endpoint
     * @param array $params Parameters to include in the URL
     * @param string $base_url Base API URL
     * @return string Complete endpoint URL
     */
    public static function get_endpoint_url($endpoint_name, $params = array(), $base_url = '') {
        $endpoint = self::get_endpoint($endpoint_name);
        
        if (!$endpoint) {
            return '';
        }
        
        // Use the provided base_url or get from options
        if (empty($base_url)) {
            $base_url = 'https://www.alphavantage.co/query';
        }
        
        // Start building query parameters
        $query_params = array();
        
        // Add the function parameter
        $query_params['function'] = $endpoint['function'];
        
        // Add API key from options
        $api_key = get_option('tradepress_alphavantage_api_key', '');
        $query_params['apikey'] = $api_key;
        
        // Add required parameters
        foreach ($endpoint['required_params'] as $param) {
            if (isset($params[$param])) {
                $query_params[$param] = $params[$param];
            } else {
                // Missing required parameter
                return '';
            }
        }
        
        // Add optional parameters
        foreach ($endpoint['optional_params'] as $param) {
            if (isset($params[$param])) {
                $query_params[$param] = $params[$param];
            }
        }
        
        // Build the URL
        $url = $base_url . '?' . http_build_query($query_params);
        
        return $url;
    }
}
