<?php
/**
 * TradePress Test Data Functions
 *
 * Functions for generating test data for the TradePress plugin.
 *
 * @package TradePress/Functions
 * @version 1.2.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get test stock symbols organized by priority and category
 *
 * @return array Array of test stock symbols organized by category
 */
function tradepress_get_test_stock_symbols() {
    return array(
        'previously_trading' => array(
            'NVDA', 'RBLX', 'NVAX', 'TTD', 'AMC', 'PLTR', 'IONQ', 'PYPL', 'CYNGN', 'MU',
            'AVGO', 'ACHR', 'JOBY', 'HOOD', 'SPR', 'CRWD', 'BME', 'EL', 'LCID', 'QUBT',
            'ASTS', 'COREWEAVE', 'GBIO', 'BA.L', 'BA', 'HIMS', 'QBTS', 'SOUN', 'LBTC',
            'MAIA', 'AMD', 'QQ.L', 'TSM', 'GWAV', 'ARM', 'QCOM', 'REE', 'VUZI', 'LUCY',
            'SIFY', 'BMW', 'KIA', 'RKLB', 'BABA', 'SPOT', 'AXON', 'PONY', 'LMT', 'PDD'
        ),
        'other_stocks' => array(
            'AMRQ', 'ASML', 'VIST', 'HOVR', 'EVTL', 'UPB', 'SOFI', 'LUNR', 'FCEL', 'SGBX',
            'TWLO', 'UBER', 'QQ', 'BAB', 'AIR', 'NUKK', 'FDX', 'NFLX', 'DUOL', 'METC',
            'ATLX', 'LHX', 'SPAI', 'LIDR', 'GEV', 'SNOW', 'VOO', 'GE', 'BRK-B', 'CRM',
            'DKS', 'CASY', 'ULTA', 'CAVA', 'PFE', 'AMSC', 'MSTR'
        ),
        'strategy_stocks' => array(
            'TSLA', 'AMZN', 'BLK', 'VIX', 'SBUX', 'SAP', 'SEI', 'GE', 'GS'
        ),
        'meme_stocks' => array(
            'GME'
        ),
        'global_markets' => array(
            'TECH100' => array('symbol' => 'TECH100', 'name' => 'US Tech 100', 'cfd_only' => true),
            'EUR/USD' => array('symbol' => 'EUR/USD', 'name' => 'EUR/USD', 'cfd_only' => true),
            'USA500' => array('symbol' => 'USA500', 'name' => 'USA 500', 'cfd_only' => true),
            'GER40' => array('symbol' => 'GER40', 'name' => 'Germany 40', 'cfd_only' => true),
            'USA30' => array('symbol' => 'USA30', 'name' => 'USA 30', 'cfd_only' => true),
            'BTC' => array('symbol' => 'BTC', 'name' => 'Bitcoin', 'cfd_only' => true),
            'GBP/USD' => array('symbol' => 'GBP/USD', 'name' => 'GBP/USD', 'cfd_only' => true),
            'UK100' => array('symbol' => 'UK100', 'name' => 'UK100', 'cfd_only' => true),
            'EU50' => array('symbol' => 'EU50', 'name' => 'EU50', 'cfd_only' => true),
            'USD/JPY' => array('symbol' => 'USD/JPY', 'name' => 'USD/JPY', 'cfd_only' => true)
        )
    );
}

/**
 * Get all symbols as a flat array (for backward compatibility)
 *
 * @return array Array of all test stock symbols
 */
function tradepress_get_all_test_symbols() {
    $symbols = tradepress_get_test_stock_symbols();
    $flat_symbols = array();
    
    foreach ($symbols as $category => $symbol_list) {
        if ($category === 'global_markets') {
            foreach ($symbol_list as $symbol_data) {
                $flat_symbols[] = $symbol_data['symbol'];
            }
        } else {
            $flat_symbols = array_merge($flat_symbols, $symbol_list);
        }
    }
    
    return array_unique($flat_symbols);
}

/**
 * Get symbols by category
 *
 * @param string $category Category name
 * @return array Array of symbols in the specified category
 */
function tradepress_get_symbols_by_category($category) {
    $symbols = tradepress_get_test_stock_symbols();
    if (!isset($symbols[$category])) {
        return array();
    }
    
    if ($category === 'global_markets') {
        return $symbols[$category];
    }
    
    return $symbols[$category];
}

/**
 * Get highest priority symbols (Previously Trading Stocks)
 *
 * @return array Array of highest priority symbols
 */
function tradepress_get_priority_symbols() {
    $symbols = tradepress_get_test_stock_symbols();
    return $symbols['previously_trading'];
}

/**
 * Get strategy symbols (for built-in strategies)
 *
 * @return array Array of symbols for built-in strategies
 */
function tradepress_get_strategy_symbols() {
    $symbols = tradepress_get_test_stock_symbols();
    return array_merge($symbols['strategy_stocks'], array('NVDA', 'SGBX'));
}

/**
 * Get meme stock symbols (for social media monitoring)
 *
 * @return array Array of meme stock symbols
 */
function tradepress_get_meme_symbols() {
    $symbols = tradepress_get_test_stock_symbols();
    return $symbols['meme_stocks'];
}

/**
 * Get global markets symbols (CFD instruments)
 *
 * @return array Array of global markets symbols with CFD indicators
 */
function tradepress_get_global_markets() {
    $symbols = tradepress_get_test_stock_symbols();
    return $symbols['global_markets'];
}

/**
 * Check if a symbol is CFD only
 *
 * @param string $symbol Symbol to check
 * @return bool True if CFD only, false otherwise
 */
function tradepress_is_cfd_symbol($symbol) {
    $global_markets = tradepress_get_global_markets();
    return isset($global_markets[$symbol]) && $global_markets[$symbol]['cfd_only'];
}

/**
 * Get a list of test foreign exchange currency pairs
 *
 * @return array Array of forex pairs
 */
function tradepress_get_test_forex_pairs() {
    return array(
        'EUR/USD', 'USD/JPY', 'GBP/USD', 'USD/CHF', 'AUD/USD',
        'USD/CAD', 'NZD/USD', 'EUR/GBP', 'EUR/JPY', 'GBP/JPY'
    );
}

/**
 * Get detailed information about test stock companies
 *
 * @return array Array of company details indexed by symbol
 */
function tradepress_get_test_company_details() {
    return array(
        // Tech Giants & AI
        'NVDA' => array(
            'name' => 'NVIDIA Corporation',
            'sector' => 'Technology',
            'industry' => 'Semiconductors',
            'exchange' => 'NASDAQ',
            'country' => 'United States',
            'founded' => '1993',
            'employees' => 29600,
            'market_cap_category' => 'Mega Cap',
            'dividend_yield' => 0.03,
            'beta' => 1.70,
            'avg_volume' => 42900000,
            'description' => 'Designs and manufactures computer graphics processors, chipsets, and related multimedia software, focusing heavily on AI and data center solutions.',
            'products' => array('GeForce GPUs', 'CUDA', 'Tensor Cores', 'DGX Systems', 'NVIDIA DRIVE', 'Jetson', 'Omniverse'),
            'primary_competitors' => array('AMD', 'INTC', 'QCOM', 'GOOGL', 'MSFT')
        ),
        'PLTR' => array(
            'name' => 'Palantir Technologies Inc.',
            'sector' => 'Technology',
            'industry' => 'Software—Infrastructure',
            'exchange' => 'NYSE',
            'country' => 'United States',
            'founded' => '2003',
            'employees' => 3838,
            'market_cap_category' => 'Large Cap',
            'dividend_yield' => 0.0,
            'beta' => 2.73,
            'avg_volume' => 59700000,
            'description' => 'Builds and deploys software platforms for data analysis, particularly for government and enterprise clients.',
            'products' => array('Foundry', 'Gotham', 'Apollo', 'Artificial Intelligence Platform (AIP)'),
            'primary_competitors' => array('SNOW', 'CRM', 'MSFT', 'C3.ai')
        ),

        // Electric Vehicles & Transportation
        'TSLA' => array(
            'name' => 'Tesla, Inc.',
            'sector' => 'Consumer Cyclical',
            'industry' => 'Auto Manufacturers',
            'exchange' => 'NASDAQ',
            'country' => 'United States',
            'founded' => '2003',
            'employees' => 140473,
            'market_cap_category' => 'Mega Cap',
            'dividend_yield' => 0.0,
            'beta' => 2.00,
            'avg_volume' => 108200000,
            'description' => 'Designs, develops, manufactures, sells, and leases electric vehicles, energy generation, and storage systems.',
            'products' => array('Model S', 'Model 3', 'Model X', 'Model Y', 'Cybertruck', 'Powerwall', 'Megapack', 'Solar Roof', 'Supercharger Network', 'FSD'),
            'primary_competitors' => array('BYD', 'Volkswagen', 'GM', 'F', 'LCID', 'RIVN')
        ),
        'LCID' => array(
            'name' => 'Lucid Group, Inc.',
            'sector' => 'Consumer Cyclical',
            'industry' => 'Auto Manufacturers',
            'exchange' => 'NASDAQ',
            'country' => 'United States',
            'founded' => '2007',
            'employees' => 6500,
            'market_cap_category' => 'Mid Cap',
            'dividend_yield' => 0.0,
            'beta' => 1.15,
            'avg_volume' => 31400000,
            'description' => 'Designs, engineers, and builds luxury electric vehicles, EV powertrains, and battery systems.',
            'products' => array('Lucid Air', 'Lucid Gravity (Upcoming)'),
            'primary_competitors' => array('TSLA', 'Mercedes-Benz', 'BMW', 'Porsche', 'RIVN')
        ),

        // Aerospace & Defense
        'LMT' => array(
            'name' => 'Lockheed Martin Corporation',
            'sector' => 'Industrials',
            'industry' => 'Aerospace & Defense',
            'exchange' => 'NYSE',
            'country' => 'United States',
            'founded' => '1995',
            'employees' => 116000,
            'market_cap_category' => 'Large Cap',
            'dividend_yield' => 2.85,
            'beta' => 0.75,
            'avg_volume' => 1200000,
            'description' => 'Researches, designs, develops, manufactures, integrates, and sustains technology systems, products, and services worldwide.',
            'products' => array('F-35 Lightning II', 'Aegis Combat System', 'THAAD', 'Orion Spacecraft', 'Missiles and Fire Control'),
            'primary_competitors' => array('BA', 'RTX', 'NOC', 'GD')
        ),

        // Fintech & Financial Services
        'PYPL' => array(
            'name' => 'PayPal Holdings, Inc.',
            'sector' => 'Financial Services',
            'industry' => 'Credit Services',
            'exchange' => 'NASDAQ',
            'country' => 'United States',
            'founded' => '1998',
            'employees' => 27200,
            'market_cap_category' => 'Large Cap',
            'dividend_yield' => 0.0,
            'beta' => 1.15,
            'avg_volume' => 15900000,
            'description' => 'Operates a technology platform and digital payments system that enables digital and mobile payments for consumers and merchants worldwide.',
            'products' => array('PayPal', 'Venmo', 'Xoom', 'Braintree', 'Hyperwallet', 'PayPal Credit'),
            'primary_competitors' => array('SQ', 'V', 'MA', 'Stripe', 'Adyen')
        ),

        // Quantum Computing & Advanced Tech
        'IONQ' => array(
            'name' => 'IonQ, Inc.',
            'sector' => 'Technology',
            'industry' => 'Computer Hardware',
            'exchange' => 'NYSE',
            'country' => 'United States',
            'founded' => '2015',
            'employees' => 200,
            'market_cap_category' => 'Mid Cap',
            'dividend_yield' => 0.0,
            'beta' => 2.1,
            'avg_volume' => 10000000,
            'description' => 'Develops trapped-ion quantum computing systems.',
            'products' => array('IonQ Aria', 'IonQ Forte', 'Quantum Computing as a Service'),
            'primary_competitors' => array('IBM', 'GOOGL', 'MSFT', 'Rigetti', 'Quantinuum')
        ),

        // Space & Satellite
        'ASTS' => array(
            'name' => 'AST SpaceMobile, Inc.',
            'sector' => 'Communication Services',
            'industry' => 'Telecom Services',
            'exchange' => 'NASDAQ',
            'country' => 'United States',
            'founded' => '2017',
            'employees' => 600,
            'market_cap_category' => 'Mid Cap',
            'dividend_yield' => 0.0,
            'beta' => 1.8,
            'avg_volume' => 15000000,
            'description' => 'Building a space-based cellular broadband network designed to connect directly to standard mobile phones.',
            'products' => array('SpaceMobile Network', 'BlueWalker 3 (Test Satellite)', 'BlueBird Satellites'),
            'primary_competitors' => array('Starlink (SpaceX)', 'Iridium (IRDM)', 'Globalstar (GSAT)', 'Lynk Global')
        ),

        // Biotechnology & Healthcare
        'HIMS' => array(
            'name' => 'Hims & Hers Health, Inc.',
            'sector' => 'Healthcare',
            'industry' => 'Health Information Services',
            'exchange' => 'NYSE',
            'country' => 'United States',
            'founded' => '2017',
            'employees' => 900,
            'market_cap_category' => 'Mid Cap',
            'dividend_yield' => 0.0,
            'beta' => 1.5,
            'avg_volume' => 8000000,
            'description' => 'Operates a multi-specialty telehealth platform connecting consumers to licensed healthcare professionals.',
            'products' => array('Telehealth Services (Men\'s/Women\'s Health)', 'Prescription Medications', 'OTC Products'),
            'primary_competitors' => array('TDOC', 'AMWL', 'Ro')
        ),

        // Gaming & Entertainment
        'RBLX' => array(
            'name' => 'Roblox Corporation',
            'sector' => 'Communication Services',
            'industry' => 'Electronic Gaming & Multimedia',
            'exchange' => 'NYSE',
            'country' => 'United States',
            'founded' => '2004',
            'employees' => 2100,
            'market_cap_category' => 'Large Cap',
            'dividend_yield' => 0.0,
            'beta' => 1.8,
            'avg_volume' => 25000000,
            'description' => 'Operates an online entertainment platform that allows users to program games and play games created by other users.',
            'products' => array('Roblox Platform', 'Robux Virtual Currency', 'Developer Exchange'),
            'primary_competitors' => array('MSFT (Minecraft)', 'Epic Games', 'Unity (U)')
        ),

        // E-commerce & Retail
        'AMZN' => array(
            'name' => 'Amazon.com, Inc.',
            'sector' => 'Consumer Cyclical',
            'industry' => 'Internet Retail',
            'exchange' => 'NASDAQ',
            'country' => 'United States',
            'founded' => '1994',
            'employees' => 1525000,
            'market_cap_category' => 'Mega Cap',
            'dividend_yield' => 0.0,
            'beta' => 1.14,
            'avg_volume' => 52300000,
            'description' => 'Engages in the retail sale of consumer products and subscriptions through online and physical stores worldwide. Also a major provider of cloud computing services.',
            'products' => array('Amazon Marketplace', 'AWS', 'Prime', 'Alexa', 'Kindle', 'Amazon Studios'),
            'primary_competitors' => array('WMT', 'MSFT', 'GOOGL', 'BABA')
        ),

        // Semiconductors
        'TSM' => array(
            'name' => 'Taiwan Semiconductor Manufacturing Company Limited',
            'sector' => 'Technology',
            'industry' => 'Semiconductors',
            'exchange' => 'NYSE',
            'country' => 'Taiwan',
            'founded' => '1987',
            'employees' => 73090,
            'market_cap_category' => 'Mega Cap',
            'dividend_yield' => 1.04,
            'beta' => 1.01,
            'avg_volume' => 9800000,
            'description' => 'The world\'s largest dedicated independent semiconductor foundry, manufacturing chips designed by other companies.',
            'products' => array('Advanced Node Manufacturing (7nm, 5nm, 3nm, etc.)', 'Specialty Technologies', 'Wafer Manufacturing'),
            'primary_competitors' => array('INTC', 'Samsung Electronics', 'GlobalFoundries', 'SMIC')
        ),
        'AVGO' => array(
            'name' => 'Broadcom Inc.',
            'sector' => 'Technology',
            'industry' => 'Semiconductors',
            'exchange' => 'NASDAQ',
            'country' => 'United States',
            'founded' => '1961',
            'employees' => 20000,
            'market_cap_category' => 'Mega Cap',
            'dividend_yield' => 1.27,
            'beta' => 1.15,
            'avg_volume' => 2500000,
            'description' => 'Designs, develops, and supplies a broad range of semiconductor and infrastructure software solutions.',
            'products' => array('Networking Chips', 'Storage Adapters', 'Wireless Chips', 'VMware Software', 'Mainframe Software'),
            'primary_competitors' => array('QCOM', 'NVDA', 'MRVL', 'MSFT', 'CSCO')
        ),

        // ETFs & Indices
        'VOO' => array(
            'name' => 'Vanguard S&P 500 ETF',
            'sector' => 'Financial',
            'industry' => 'ETF',
            'exchange' => 'NYSE Arca',
            'country' => 'United States',
            'founded' => '2010',
            'employees' => 0,
            'market_cap_category' => 'ETF',
            'dividend_yield' => 1.38,
            'beta' => 1.0,
            'avg_volume' => 4500000,
            'description' => 'Tracks the S&P 500 Index, representing 500 of the largest U.S. companies. Known for its low expense ratio.',
            'products' => array(),
            'primary_competitors' => array('SPY', 'IVV')
        ),
        'VIX' => array(
            'name' => 'CBOE Volatility Index',
            'sector' => 'Financial',
            'industry' => 'Index',
            'exchange' => 'CBOE',
            'country' => 'United States',
            'founded' => '1993',
            'employees' => 0,
            'market_cap_category' => 'Index',
            'dividend_yield' => 0.0,
            'beta' => 0.0,
            'avg_volume' => 0,
            'description' => 'A real-time market index representing the market\'s expectation of 30-day forward-looking volatility derived from S&P 500 index options.',
            'products' => array(),
            'primary_competitors' => array()
        ),

        // Consumer & Retail
        'SBUX' => array(
            'name' => 'Starbucks Corporation',
            'sector' => 'Consumer Cyclical',
            'industry' => 'Restaurants',
            'exchange' => 'NASDAQ',
            'country' => 'United States',
            'founded' => '1971',
            'employees' => 383000,
            'market_cap_category' => 'Large Cap',
            'dividend_yield' => 2.1,
            'beta' => 0.79,
            'avg_volume' => 7500000,
            'description' => 'Operates as a roaster, marketer, and retailer of specialty coffee worldwide.',
            'products' => array('Coffee', 'Tea', 'Food Items', 'Licensed Products', 'Mobile App'),
            'primary_competitors' => array('Dunkin\' (DNKN)', 'McDonald\'s (MCD)', 'Tim Hortons', 'Costa Coffee')
        )
    );
}