<?php
/**
 * TradePress Test Data Functions
 *
 * Functions for generating and managing test data for TradePress.
 *
 * @package TradePress/Functions
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if global demo mode is enabled
 * 
 * @return bool True if in demo mode, false otherwise
 */
function is_demo_mode() {
    // Check for TRADEPRESS_DEMO_MODE constant first
    if (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) { 
        return true;
    }
    
    // Check for demo mode option using the correct option name
    $global_demo_mode = get_option('tradepress_demo_mode', 'yes');
    if ($global_demo_mode === 'yes') { ;
        return true;
    }
    
    return false;
}

/**
 * Generate test price data for a symbol
 *
 * @param string $symbol The stock symbol
 * @return array Price data
 */
function tradepress_generate_test_price_data($symbol) {
    // Generate random price between 10 and 500
    $base_price = mt_rand(1000, 50000) / 100;

    // Generate random daily change between -5% and +5%
    $change_percent = (mt_rand(-500, 500) / 100);
    $previous_close = $base_price / (1 + ($change_percent / 100));

    return array(
        'symbol' => $symbol,
        'price' => $base_price,
        'previous_close' => round($previous_close, 2),
        'change' => round($base_price - $previous_close, 2),
        'change_percent' => $change_percent,
        'volume' => mt_rand(100000, 10000000),
        'avg_volume' => mt_rand(200000, 5000000),
        'market_cap' => round($base_price * mt_rand(10000000, 100000000) / 1000000000, 2) . 'B',
        'pe_ratio' => (mt_rand(500, 5000) / 100),
    );
}

/**
 * Generate test technical indicators for a symbol
 *
 * @param string $symbol The stock symbol
 * @return array Technical indicators
 */
function tradepress_generate_test_technical_data($symbol) {
    return array(
        'rsi' => mt_rand(10, 90),
        'macd' => (mt_rand(-2000, 2000) / 1000),
        'macd_signal' => (mt_rand(-2000, 2000) / 1000),
        'macd_histogram' => (mt_rand(-1000, 1000) / 1000),
        'moving_averages' => array(
            'sma_20' => mt_rand(8000, 55000) / 100,
            'sma_50' => mt_rand(8000, 55000) / 100,
            'sma_200' => mt_rand(8000, 55000) / 100,
            'ema_12' => mt_rand(8000, 55000) / 100,
            'ema_26' => mt_rand(8000, 55000) / 100,
        ),
        'bollinger_bands' => array(
            'upper' => mt_rand(12000, 60000) / 100,
            'middle' => mt_rand(10000, 50000) / 100,
            'lower' => mt_rand(8000, 40000) / 100,
        ),
    );
}

/**
 * Generate a complete test dataset for a symbol
 *
 * @param string $symbol The stock symbol
 * @return array Complete symbol data
 */
function tradepress_generate_complete_test_data($symbol) {
    return array(
        'price' => tradepress_generate_test_price_data($symbol),
        'technical' => tradepress_generate_test_technical_data($symbol),
    );
}

/**
 * Generate financial ratios for a stock symbol
 *
 * @param string $symbol The stock symbol
 * @return array Financial ratios
 */
function tradepress_generate_financial_ratios($symbol) {
    // Get company details if available
    $company_details = tradepress_get_test_company_details();

    // Default values
    $sector = 'Technology';
    $market_cap_category = 'Large Cap';
    $beta = mt_rand(75, 200) / 100;

    // Use company details if available
    if (isset($company_details[$symbol])) {
        $details = $company_details[$symbol]; // Use a temporary variable
        $sector = isset($details['sector']) ? $details['sector'] : 'Technology';
        $market_cap_category = isset($details['market_cap_category']) ? $details['market_cap_category'] : 'Large Cap';
        $beta = isset($details['beta']) ? $details['beta'] : $beta;
    }

    // Adjust P/E ratio based on sector and market cap
    $pe_base = 20;

    if ($sector == 'Technology') {
        $pe_base = 30;
    } elseif ($sector == 'Consumer Cyclical') {
        $pe_base = 25;
    } elseif ($sector == 'Financial Services') {
        $pe_base = 15;
    } elseif ($sector == 'Healthcare') {
        $pe_base = 28;
    } elseif ($sector == 'Industrials') {
        $pe_base = 18;
    } elseif ($sector == 'Energy') {
        $pe_base = 12;
    } elseif ($sector == 'Consumer Defensive') {
        $pe_base = 22;
    }

    if ($market_cap_category == 'Mega Cap') {
        $pe_base += 5;
    } elseif ($market_cap_category == 'Small Cap') {
        $pe_base -= 5;
    } elseif ($market_cap_category == 'ETF' || $market_cap_category == 'Index' || $market_cap_category == 'ETP') {
        // Ratios are not typically applicable to ETFs/Indices/ETPs in the same way
        return array(
            'pe_ratio' => null,
            'eps' => null,
            'forward_pe' => null,
            'peg_ratio' => null,
            'pb_ratio' => null,
            'ps_ratio' => null,
            'roe' => null,
            'roa' => null,
            'debt_to_equity' => null,
            'current_ratio' => null,
            'quick_ratio' => null,
            'beta' => isset($details['beta']) ? $details['beta'] : 1.0, // Use ETF beta if available
            'dividend_yield' => isset($details['dividend_yield']) ? $details['dividend_yield'] . '%' : '0.0%'
        );
    }

    // Generate ratios with some randomness
    $pe_ratio = $pe_base + mt_rand(-800, 800) / 100;

    // Generate other ratios based on P/E
    return array(
        'pe_ratio' => max(5, round($pe_ratio, 2)),
        'eps' => round(mt_rand(100, 1000) / 100, 2),
        'forward_pe' => max(4, round($pe_ratio - mt_rand(100, 300) / 100, 2)),
        'peg_ratio' => round(mt_rand(50, 300) / 100, 2),
        'pb_ratio' => round(mt_rand(200, 800) / 100, 2),
        'ps_ratio' => round(mt_rand(100, 1000) / 100, 2),
        'roe' => round(mt_rand(500, 3000) / 100, 2) . '%',
        'roa' => round(mt_rand(300, 1500) / 100, 2) . '%',
        'debt_to_equity' => round(mt_rand(20, 150) / 100, 2),
        'current_ratio' => round(mt_rand(100, 300) / 100, 2),
        'quick_ratio' => round(mt_rand(80, 200) / 100, 2),
        'beta' => round($beta, 2),
        'dividend_yield' => round(mt_rand(0, 500) / 100, 2) . '%'
    );
}


/**
 * Generate earnings history for a symbol
 *
 * @param string $symbol The stock symbol
 * @return array Earnings history
 */
function tradepress_generate_earnings_history($symbol) {
    $quarters = array();
    $base_eps = mt_rand(50, 500) / 100;

    // Generate last 8 quarters
    for ($i = 0; $i < 8; $i++) {
        $quarter_date = date('Y-m-d', strtotime("-" . (3 * $i) . " months"));
        $quarter_num = date('n', strtotime($quarter_date));
        $fiscal_quarter = 'Q' . ceil($quarter_num / 3);
        $fiscal_year = date('Y', strtotime($quarter_date));

        // Add some variation to EPS
        $eps_variation = mt_rand(-20, 30) / 100;
        $current_eps = $base_eps * (1 + $eps_variation);

        // Estimate
        $estimate_variation = mt_rand(-15, 15) / 100;
        $eps_estimate = $current_eps * (1 + $estimate_variation);

        // Surprise
        $surprise = $current_eps - $eps_estimate;
        $surprise_percent = ($eps_estimate != 0) ? ($surprise / abs($eps_estimate)) * 100 : 0;

        // Revenue
        $base_revenue = mt_rand(100, 10000); // In Millions
        $revenue_variation = mt_rand(-10, 20) / 100;
        $current_revenue = $base_revenue * (1 + $revenue_variation);
        $revenue_estimate_variation = mt_rand(-8, 8) / 100;
        $revenue_estimate = $current_revenue * (1 + $revenue_estimate_variation);


        $quarters[] = array(
            'date' => $quarter_date,
            'fiscal_quarter' => $fiscal_quarter,
            'fiscal_year' => $fiscal_year,
            'eps_estimate' => round($eps_estimate, 2),
            'eps_actual' => round($current_eps, 2),
            'surprise' => round($surprise, 2),
            'surprise_percent' => round($surprise_percent, 2) . '%',
            'revenue_estimate' => round($revenue_estimate * 1000000), // Store as full number
            'revenue_actual' => round($current_revenue * 1000000) // Store as full number
        );

        // Adjust base EPS for growth trend
        $base_eps *= (1 + (mt_rand(-5, 15) / 100));
    }

    // Sort by date ascending
    usort($quarters, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });

    return $quarters;
}

/**
 * Generate test stock data
 *
 * @param string $symbol Stock symbol
 * @return array Stock data
 */
function tradepress_generate_test_stock_data( $symbol = 'AAPL' ) {
    // Base prices for common test symbols
    $base_prices = array(
        'AAPL' => 185.92,
        'MSFT' => 376.17,
        'NVDA' => 950.02,
        'TSLA' => 223.31,
        'GOOG' => 156.98,
        'AMZN' => 180.35,
        'META' => 487.95,
        'NFLX' => 657.31,
    );
    
    // Use default price if symbol not found
    $base_price = isset( $base_prices[$symbol] ) ? $base_prices[$symbol] : 100.00;
    
    // Generate random price movement (-3% to +3%)
    $change_pct = mt_rand(-300, 300) / 100;
    $price_change = $base_price * ($change_pct / 100);
    $current_price = $base_price + $price_change;
    
    // Generate random volume
    $volume = mt_rand(1000000, 50000000);
    
    // Generate random day range
    $day_low = $current_price * (1 - (mt_rand(50, 200) / 10000));
    $day_high = $current_price * (1 + (mt_rand(50, 200) / 10000));
    
    // Generate random 52 week range
    $year_low = $current_price * (1 - (mt_rand(1000, 3000) / 10000));
    $year_high = $current_price * (1 + (mt_rand(1000, 3000) / 10000));
    
    // Return stock data
    return array(
        'symbol' => $symbol,
        'price' => $current_price,
        'change' => $price_change,
        'change_pct' => $change_pct,
        'volume' => $volume,
        'day_low' => $day_low,
        'day_high' => $day_high,
        'year_low' => $year_low,
        'year_high' => $year_high,
        'market_cap' => $current_price * mt_rand(1000000, 2000000000),
        'pe_ratio' => mt_rand(10, 40) + (mt_rand(0, 99) / 100),
        'dividend_yield' => mt_rand(0, 400) / 100,
        'eps' => mt_rand(1, 20) + (mt_rand(0, 99) / 100),
        'last_updated' => current_time('mysql'),
    );
}

/**
 * Generate test chart data
 *
 * @param string $symbol Stock symbol
 * @param string $period Chart period (1d, 5d, 1m, 6m, 1y, 5y)
 * @return array Chart data
 */
function tradepress_generate_test_chart_data( $symbol = 'AAPL', $period = '1m' ) {
    // Base price from test data
    $stock_data = tradepress_generate_test_stock_data( $symbol );
    $base_price = $stock_data['price'];
    
    // Determine number of data points and interval based on period
    switch ( $period ) {
        case '1d':
            $data_points = 390; // 6.5 hours x 60 minutes
            $interval = 1; // 1 minute
            $start_date = strtotime('today');
            break;
        case '5d':
            $data_points = 5 * 390; // 5 days of minute data
            $interval = 5; // 5 minutes
            $start_date = strtotime('-5 days');
            break;
        case '1m':
            $data_points = 20; // 20 trading days
            $interval = 'day';
            $start_date = strtotime('-1 month');
            break;
        case '6m':
            $data_points = 130; // ~130 trading days in 6 months
            $interval = 'day';
            $start_date = strtotime('-6 months');
            break;
        case '1y':
            $data_points = 52; // 52 weeks
            $interval = 'week';
            $start_date = strtotime('-1 year');
            break;
        case '5y':
            $data_points = 60; // 60 months
            $interval = 'month';
            $start_date = strtotime('-5 years');
            break;
        default:
            $data_points = 30; // 1 month default
            $interval = 'day';
            $start_date = strtotime('-1 month');
            break;
    }
    
    // Generate chart data
    $chart_data = array();
    $current_price = $base_price * 0.9; // Start at 90% of current price for a general uptrend
    $volatility = 0.02; // 2% daily volatility
    
    for ( $i = 0; $i < $data_points; $i++ ) {
        // Random price movement based on volatility
        $random_change = mt_rand(-100, 100) / 100 * $volatility;
        $current_price = $current_price * (1 + $random_change);
        
        // Ensure price stays positive
        $current_price = max($current_price, 0.01);
        
        // Calculate date based on interval
        if ($interval == 'day') {
            $date = $start_date + ($i * 86400); // 86400 seconds in a day
        } elseif ($interval == 'week') {
            $date = $start_date + ($i * 604800); // 604800 seconds in a week
        } elseif ($interval == 'month') {
            $date = strtotime('+' . $i . ' months', $start_date);
        } else {
            // Interval in minutes
            $date = $start_date + ($i * $interval * 60);
        }
        
        // Skip weekends for daily data
        if ($interval == 'day' || is_numeric($interval)) {
            $day_of_week = date('N', $date);
            if ($day_of_week > 5) { // 6 = Saturday, 7 = Sunday
                continue;
            }
        }
        
        // Add data point
        $chart_data[] = array(
            'date' => date('Y-m-d H:i:s', $date),
            'price' => round($current_price, 2),
            'volume' => mt_rand(100000, 10000000),
        );
    }
    
    // Make sure the last price is close to the current price
    if (!empty($chart_data)) {
        $last_index = count($chart_data) - 1;
        $chart_data[$last_index]['price'] = $base_price;
    }
    
    return $chart_data;
}

/**
 * Generate test technical indicators data
 *
 * @param string $symbol Stock symbol
 * @return array Technical indicators data
 */
function tradepress_generate_test_technical_indicators( $symbol = 'AAPL' ) {
    // Get stock data for base values
    $stock_data = tradepress_generate_test_stock_data( $symbol );
    $price = $stock_data['price'];
    
    // Generate RSI (0-100)
    $rsi = mt_rand(20, 80);
    $rsi_signal = '';
    
    if ( $rsi > 70 ) {
        $rsi_signal = 'overbought';
    } elseif ( $rsi < 30 ) {
        $rsi_signal = 'oversold';
    } else {
        $rsi_signal = 'neutral';
    }
    
    // Generate MACD
    $macd_line = mt_rand(-500, 500) / 100;
    $signal_line = $macd_line + (mt_rand(-200, 200) / 100);
    $histogram = $macd_line - $signal_line;
    
    $macd_signal = '';
    if ( $macd_line > $signal_line ) {
        $macd_signal = 'bullish';
    } elseif ( $macd_line < $signal_line ) {
        $macd_signal = 'bearish';
    } else {
        $macd_signal = 'neutral';
    }
    
    // Generate Bollinger Bands
    $middle_band = $price;
    $upper_band = $price * (1 + (mt_rand(150, 250) / 10000));
    $lower_band = $price * (1 - (mt_rand(150, 250) / 10000));
    
    $bollinger_signal = '';
    if ( $price > $upper_band ) {
        $bollinger_signal = 'overbought';
    } elseif ( $price < $lower_band ) {
        $bollinger_signal = 'oversold';
    } else {
        $bollinger_signal = 'neutral';
    }
    
    // Generate moving averages
    $sma_50 = $price * (1 + (mt_rand(-200, 200) / 10000));
    $sma_200 = $price * (1 + (mt_rand(-400, 400) / 10000));
    
    $ma_signal = '';
    if ($price > $sma_50 && $price > $sma_200) {
        $ma_signal = 'strong bullish';
    } elseif ($price > $sma_50 && $price < $sma_200) {
        $ma_signal = 'neutral';
    } elseif ($price < $sma_50 && $price > $sma_200) {
        $ma_signal = 'neutral';
    } else {
        $ma_signal = 'bearish';
    }
    
    // Return indicators data
    return array(
        'symbol' => $symbol,
        'rsi' => array(
            'value' => $rsi,
            'signal' => $rsi_signal,
        ),
        'macd' => array(
            'line' => $macd_line,
            'signal' => $signal_line,
            'histogram' => $histogram,
            'indicator' => $macd_signal,
        ),
        'bollinger' => array(
            'upper' => $upper_band,
            'middle' => $middle_band,
            'lower' => $lower_band,
            'signal' => $bollinger_signal,
        ),
        'moving_averages' => array(
            'sma_50' => $sma_50,
            'sma_200' => $sma_200,
            'signal' => $ma_signal,
        ),
        'last_updated' => current_time('mysql'),
    );
}

/**
 * Generate test company info
 *
 * @param string $symbol Stock symbol
 * @return array Company information
 */
function tradepress_generate_test_company_info( $symbol = 'AAPL' ) {
    // Company data for common test symbols
    $companies = array(
        'AAPL' => array(
            'name' => 'Apple Inc.',
            'sector' => 'Technology',
            'industry' => 'Consumer Electronics',
            'description' => 'Apple Inc. designs, manufactures, and markets smartphones, personal computers, tablets, wearables, and accessories worldwide.',
            'employees' => 164000,
            'founded' => 1976,
            'ceo' => 'Tim Cook',
            'headquarters' => 'Cupertino, California, USA',
            'website' => 'https://www.apple.com',
        ),
        'MSFT' => array(
            'name' => 'Microsoft Corporation',
            'sector' => 'Technology',
            'industry' => 'Software—Infrastructure',
            'description' => 'Microsoft Corporation develops, licenses, and supports software, services, devices, and solutions worldwide.',
            'employees' => 221000,
            'founded' => 1975,
            'ceo' => 'Satya Nadella',
            'headquarters' => 'Redmond, Washington, USA',
            'website' => 'https://www.microsoft.com',
        ),
        'NVDA' => array(
            'name' => 'NVIDIA Corporation',
            'sector' => 'Technology',
            'industry' => 'Semiconductors',
            'description' => 'NVIDIA Corporation provides graphics, and compute and networking solutions in the United States, Taiwan, China, and internationally.',
            'employees' => 26196,
            'founded' => 1993,
            'ceo' => 'Jensen Huang',
            'headquarters' => 'Santa Clara, California, USA',
            'website' => 'https://www.nvidia.com',
        ),
        'TSLA' => array(
            'name' => 'Tesla, Inc.',
            'sector' => 'Consumer Cyclical',
            'industry' => 'Auto Manufacturers',
            'description' => 'Tesla, Inc. designs, develops, manufactures, sells, and leases electric vehicles and energy generation and storage systems.',
            'employees' => 127855,
            'founded' => 2003,
            'ceo' => 'Elon Musk',
            'headquarters' => 'Austin, Texas, USA',
            'website' => 'https://www.tesla.com',
        ),
    );
    
    // Return company info if available, or generate generic info
    if ( isset( $companies[$symbol] ) ) {
        return array_merge(
            array('symbol' => $symbol),
            $companies[$symbol]
        );
    } else {
        return array(
            'symbol' => $symbol,
            'name' => $symbol . ' Corporation',
            'sector' => 'Unknown',
            'industry' => 'Unknown',
            'description' => 'No detailed information available for this company.',
            'employees' => mt_rand(1000, 100000),
            'founded' => mt_rand(1950, 2010),
            'ceo' => 'Unknown',
            'headquarters' => 'Unknown',
            'website' => 'https://www.example.com',
        );
    }
}

/**
 * Get test data for recent symbols
 *
 * @param int $count Number of symbols to return
 * @return array Recent symbols data
 */
function tradepress_get_test_recent_symbols_data( $count = 5 ) {
    $symbols = array('AAPL', 'MSFT', 'NVDA', 'TSLA', 'GOOG', 'AMZN', 'META', 'NFLX');
    $recent_symbols = array();
    
    // Shuffle to get random selection
    shuffle($symbols);
    $symbols = array_slice($symbols, 0, $count);
    
    foreach ($symbols as $symbol) {
        $stock_data = tradepress_generate_test_stock_data($symbol);
        $company_info = tradepress_generate_test_company_info($symbol);
        
        $recent_symbols[$symbol] = array(
            'symbol' => $symbol,
            'company_name' => $company_info['name'],
            'price' => $stock_data['price'],
            'change' => $stock_data['change'],
            'change_percent' => $stock_data['change_pct'],
            'is_positive' => $stock_data['change_pct'] >= 0,
            'timestamp' => current_time('mysql'),
            'thumbnail' => plugins_url('/assets/images/stocks/' . strtolower($symbol) . '.png', TRADEPRESS_PLUGIN_FILE),
        );
    }
    
    return $recent_symbols;
}

/**
 * Get test market movers data
 *
 * @return array Market movers data
 */
function tradepress_get_test_market_movers() {
    $gainers = array();
    $losers = array();
    $volume = array();
    
    $symbols = array('AAPL', 'MSFT', 'NVDA', 'TSLA', 'GOOG', 'AMZN', 'META', 'NFLX', 'BAC', 'JPM', 'XOM', 'CVX', 'PFE', 'JNJ');
    
    // Generate data for each symbol
    foreach ($symbols as $symbol) {
        $stock_data = tradepress_generate_test_stock_data($symbol);
        
        // Sort into categories
        if ($stock_data['change_pct'] > 0) {
            $gainers[] = array(
                'symbol' => $symbol,
                'price' => $stock_data['price'],
                'change_pct' => $stock_data['change_pct'],
            );
        } else {
            $losers[] = array(
                'symbol' => $symbol,
                'price' => $stock_data['price'],
                'change_pct' => $stock_data['change_pct'],
            );
        }
        
        $volume[] = array(
            'symbol' => $symbol,
            'price' => $stock_data['price'],
            'volume' => $stock_data['volume'],
        );
    }
    
    // Sort gainers by percent change (descending)
    usort($gainers, function($a, $b) {
        return $b['change_pct'] <=> $a['change_pct'];
    });
    
    // Sort losers by percent change (ascending)
    usort($losers, function($a, $b) {
        return $a['change_pct'] <=> $b['change_pct'];
    });
    
    // Sort volume by volume (descending)
    usort($volume, function($a, $b) {
        return $b['volume'] <=> $a['volume'];
    });
    
    // Return top 5 in each category
    return array(
        'gainers' => array_slice($gainers, 0, 5),
        'losers' => array_slice($losers, 0, 5),
        'volume' => array_slice($volume, 0, 5),
    );
}

/**
 * Get test recent trades data
 *
 * @param int $count Number of trades to return
 * @return array Recent trades data
 */
function tradepress_get_test_recent_trades( $count = 5 ) {
    $symbols = array('AAPL', 'MSFT', 'NVDA', 'TSLA', 'GOOG', 'AMZN', 'META', 'NFLX');
    $trade_types = array('buy', 'sell');
    $trades = array();
    
    for ($i = 0; $i < $count; $i++) {
        $symbol = $symbols[array_rand($symbols)];
        $type = $trade_types[array_rand($trade_types)];
        $stock_data = tradepress_generate_test_stock_data($symbol);
        
        $timestamp = strtotime('-' . mt_rand(1, 14) . ' days');
        
        $trades[] = array(
            'date' => date('Y-m-d H:i:s', $timestamp),
            'symbol' => $symbol,
            'type' => $type,
            'price' => $stock_data['price'],
            'quantity' => mt_rand(1, 50),
            'total' => $stock_data['price'] * mt_rand(1, 50),
            'status' => 'completed',
        );
    }
    
    // Sort by date (newest first)
    usort($trades, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return $trades;
}

/**
 * Get test watchlist data
 *
 * @param int $count Number of watchlist items
 * @return array Watchlist data
 */
function tradepress_get_test_watchlist( $count = 5 ) {
    $symbols = array('AAPL', 'MSFT', 'NVDA', 'TSLA', 'GOOG', 'AMZN', 'META', 'NFLX');
    $notes = array(
        'Watching for breakout above resistance level.',
        'Earnings report coming next week.',
        'Looking for entry point on next pullback.',
        'Dividend expected next month.',
        'Tracking for possible swing trade.',
        'Monitoring after recent analyst upgrade.',
        'Long-term hold, accumulating on dips.',
        'Waiting for technical confirmation.',
    );
    
    // Shuffle to get random selection
    shuffle($symbols);
    $symbols = array_slice($symbols, 0, $count);
    
    $watchlist = array();
    
    foreach ($symbols as $symbol) {
        $stock_data = tradepress_generate_test_stock_data($symbol);
        
        $watchlist[] = array(
            'symbol' => $symbol,
            'price' => $stock_data['price'],
            'change_pct' => $stock_data['change_pct'],
            'notes' => $notes[array_rand($notes)],
            'added_date' => date('Y-m-d H:i:s', strtotime('-' . mt_rand(1, 60) . ' days')),
            'alert_price' => $stock_data['price'] * (mt_rand(80, 120) / 100),
        );
    }
    
    return $watchlist;
}
