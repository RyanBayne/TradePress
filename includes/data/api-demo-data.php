<?php
/**
 * API Demo Data Generator
 *
 * Provides methods for generating consistent demo data for different API tabs
 *
 * @class    TradePress_API_Demo_Data
 * @package  TradePress/API
 * @version  1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress API Demo Data class
 */
class TradePress_API_Demo_Data {

    /**
     * Generate demo status data for an API
     *
     * @param string $api_id The API identifier
     * @return array Status data
     */
    public static function get_status_data($api_id) {
        // Local status is randomly active (90% chance) or inactive
        $local_status = array(
            'status' => rand(1, 10) > 1 ? 'active' : 'inactive',
            'message' => ''
        );
        
        $local_status['message'] = $local_status['status'] === 'active' 
            ? __('Properly configured and working', 'tradepress')
            : __('Configuration issue detected', 'tradepress');
        
        // Service status has more variety
        $status_options = array('operational', 'operational', 'operational', 'disruption', 'outage');
        $status_key = array_rand($status_options);
        $service_status = array(
            'status' => $status_options[$status_key],
            'message' => '',
            'last_updated' => date('Y-m-d H:i:s', time() - rand(60, 7200)) // Random time in the last 2 hours
        );
        
        switch ($service_status['status']) {
            case 'operational':
                $service_status['message'] = __('All systems operational', 'tradepress');
                break;
            case 'disruption':
                $service_status['message'] = __('Minor service disruption reported', 'tradepress');
                break;
            case 'outage':
                $service_status['message'] = __('Service outage detected', 'tradepress');
                break;
        }
        
        return array(
            'local_status' => $local_status,
            'service_status' => $service_status
        );
    }
    
    /**
     * Generate demo rate limit data
     *
     * @param string $api_id The API identifier
     * @return array Rate limit data
     */
    public static function get_rate_limits($api_id) {
        // Different APIs have different rate limit structures
        $rate_limits = array(
            'reset_time' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        );
        
        // Daily usage
        $daily_quota = rand(5000, 50000);
        $daily_used = rand(0, $daily_quota);
        $rate_limits['daily_quota'] = $daily_quota;
        $rate_limits['daily_used'] = $daily_used;
        
        // Hourly usage
        $hourly_quota = rand(500, 5000);
        $hourly_used = rand(0, $hourly_quota);
        $rate_limits['hourly_quota'] = $hourly_quota;
        $rate_limits['hourly_used'] = $hourly_used;
        
        // Per-minute usage
        $minute_quota = rand(50, 500);
        $minute_used = rand(0, $minute_quota);
        $rate_limits['minute_quota'] = $minute_quota;
        $rate_limits['minute_used'] = $minute_used;
        
        return $rate_limits;
    }
    
    /**
     * Generate demo response data for data explorer
     *
     * @param string $api_id The API identifier
     * @param string $symbol The symbol to get data for
     * @param string $data_type The type of data to return
     * @return array Demo data
     */
    public static function get_explorer_data($api_id, $symbol, $data_type) {
        $response = array(
            'symbol' => $symbol,
            'timestamp' => date('c'),
            'api' => $api_id
        );
        
        switch ($data_type) {
            case 'quote':
                $response['data'] = self::generate_quote_data($symbol);
                break;
                
            case 'historical':
                $response['data'] = self::generate_historical_data($symbol);
                break;
                
            case 'company':
                $response['data'] = self::generate_company_data($symbol);
                break;
                
            case 'account':
                $response['data'] = self::generate_account_data($symbol);
                break;
                
            case 'positions':
                $response['data'] = self::generate_positions_data($symbol);
                break;
                
            case 'orders':
                $response['data'] = self::generate_orders_data($symbol);
                break;
                
            default:
                $response['data'] = array(
                    'message' => __('Demo data not available for this data type', 'tradepress')
                );
        }
        
        return $response;
    }
    
    /**
     * Generate quote data
     */
    private static function generate_quote_data($symbol) {
        $base_price = rand(10, 1000);
        return array(
            'price' => number_format($base_price + (rand(-100, 100) / 100), 2),
            'change' => number_format(rand(-500, 500) / 100, 2),
            'change_percent' => number_format(rand(-500, 500) / 100, 2) . '%',
            'volume' => rand(100000, 10000000),
            'high' => number_format($base_price + (rand(0, 200) / 100), 2),
            'low' => number_format($base_price - (rand(0, 200) / 100), 2),
            'open' => number_format($base_price - (rand(-100, 100) / 100), 2),
            'prev_close' => number_format($base_price - (rand(-100, 100) / 100), 2),
            'bid' => number_format($base_price - (rand(1, 10) / 100), 2),
            'ask' => number_format($base_price + (rand(1, 10) / 100), 2),
        );
    }
    
    /**
     * Generate historical data
     */
    private static function generate_historical_data($symbol) {
        $data = array();
        $base_price = rand(10, 1000);
        $current_price = $base_price;
        
        for ($i = 30; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $change = rand(-100, 100) / 100;
            $current_price += $change;
            
            // Make sure price doesn't go negative
            if ($current_price < 1) {
                $current_price = rand(100, 200) / 100;
            }
            
            $high = $current_price + (rand(10, 50) / 100);
            $low = $current_price - (rand(10, 50) / 100);
            
            // Make sure low isn't higher than current or high
            $low = min($low, $current_price);
            
            // Make sure high isn't lower than current
            $high = max($high, $current_price);
            
            $data[] = array(
                'date' => $date,
                'open' => number_format($current_price - (rand(-20, 20) / 100), 2),
                'high' => number_format($high, 2),
                'low' => number_format($low, 2),
                'close' => number_format($current_price, 2),
                'volume' => rand(100000, 10000000)
            );
        }
        
        return $data;
    }
    
    /**
     * Generate company data
     */
    private static function generate_company_data($symbol) {
        $symbol_parts = explode('.', $symbol);
        $base_name = $symbol_parts[0];
        
        $sectors = array('Technology', 'Financial Services', 'Healthcare', 'Consumer Cyclical', 'Industrials');
        $industries = array(
            'Software', 'Banking', 'Medical Devices', 'Retail', 'Manufacturing',
            'Semiconductors', 'Insurance', 'Biotechnology', 'E-Commerce', 'Aerospace'
        );
        
        return array(
            'name' => $base_name . ' ' . __('Corporation', 'tradepress'),
            'symbol' => $symbol,
            'exchange' => rand(0, 1) ? 'NASDAQ' : 'NYSE',
            'sector' => $sectors[array_rand($sectors)],
            'industry' => $industries[array_rand($industries)],
            'employees' => rand(100, 100000),
            'founded' => rand(1950, 2020),
            'headquarters' => rand(0, 1) ? 'New York, NY' : 'San Francisco, CA',
            'description' => sprintf(__('This is a sample company description for %s. This text is generated for demo purposes.', 'tradepress'), $symbol),
            'market_cap' => rand(1000, 1000000) * 1000000,
            'pe_ratio' => number_format(rand(10, 50) + (rand(0, 99) / 100), 2),
            'dividend_yield' => number_format(rand(0, 5) + (rand(0, 99) / 100), 2),
            'beta' => number_format(rand(0, 3) + (rand(0, 99) / 100), 2)
        );
    }
    
    /**
     * Generate account data (for broker APIs)
     */
    private static function generate_account_data() {
        return array(
            'id' => 'acc_' . substr(md5(rand()), 0, 10),
            'status' => 'ACTIVE',
            'currency' => 'USD',
            'buying_power' => number_format(rand(1000, 100000) + (rand(0, 99) / 100), 2),
            'cash' => number_format(rand(1000, 50000) + (rand(0, 99) / 100), 2),
            'portfolio_value' => number_format(rand(10000, 500000) + (rand(0, 99) / 100), 2),
            'equity' => number_format(rand(10000, 500000) + (rand(0, 99) / 100), 2),
            'last_maintenance_margin' => number_format(rand(1000, 10000) + (rand(0, 99) / 100), 2),
            'day_trade_count' => rand(0, 3),
            'created_at' => date('Y-m-d', strtotime('-' . rand(30, 365) . ' days'))
        );
    }
    
    /**
     * Generate positions data (for broker APIs)
     */
    private static function generate_positions_data($requested_symbol = null) {
        $symbols = array('AAPL', 'MSFT', 'GOOGL', 'AMZN', 'FB');
        $positions = array();
        
        // If a specific symbol was requested, just return that one
        if ($requested_symbol) {
            $symbol_parts = explode('.', $requested_symbol);
            $base_symbol = $symbol_parts[0];
            $qty = rand(1, 100);
            $avg_price = rand(10, 1000) + (rand(0, 99) / 100);
            $current_price = $avg_price + (rand(-100, 100) / 100);
            
            return array(
                'symbol' => $base_symbol,
                'quantity' => $qty,
                'avg_entry_price' => number_format($avg_price, 2),
                'current_price' => number_format($current_price, 2),
                'market_value' => number_format($qty * $current_price, 2),
                'cost_basis' => number_format($qty * $avg_price, 2),
                'unrealized_pl' => number_format($qty * ($current_price - $avg_price), 2),
                'unrealized_pl_percent' => number_format(100 * ($current_price - $avg_price) / $avg_price, 2) . '%',
                'side' => 'long'
            );
        }
        
        // Otherwise generate a list of positions
        foreach ($symbols as $symbol) {
            $qty = rand(1, 100);
            $avg_price = rand(10, 1000) + (rand(0, 99) / 100);
            $current_price = $avg_price + (rand(-100, 100) / 100);
            
            $positions[] = array(
                'symbol' => $symbol,
                'quantity' => $qty,
                'avg_entry_price' => number_format($avg_price, 2),
                'current_price' => number_format($current_price, 2),
                'market_value' => number_format($qty * $current_price, 2),
                'cost_basis' => number_format($qty * $avg_price, 2),
                'unrealized_pl' => number_format($qty * ($current_price - $avg_price), 2),
                'unrealized_pl_percent' => number_format(100 * ($current_price - $avg_price) / $avg_price, 2) . '%',
                'side' => 'long'
            );
        }
        
        return $positions;
    }
    
    /**
     * Generate orders data (for broker APIs)
     */
    private static function generate_orders_data() {
        $symbols = array('AAPL', 'MSFT', 'GOOGL', 'AMZN', 'FB');
        $statuses = array('open', 'filled', 'canceled', 'rejected');
        $sides = array('buy', 'sell');
        $types = array('market', 'limit', 'stop', 'stop_limit');
        
        $orders = array();
        
        for ($i = 0; $i < rand(3, 8); $i++) {
            $symbol = $symbols[array_rand($symbols)];
            $side = $sides[array_rand($sides)];
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];
            $price = rand(10, 1000) + (rand(0, 99) / 100);
            $qty = rand(1, 100);
            
            $created = date('Y-m-d H:i:s', time() - rand(0, 604800)); // Within the last week
            $updated = date('Y-m-d H:i:s', strtotime($created) + rand(0, 3600)); // Within an hour of creation
            
            $orders[] = array(
                'id' => 'ord_' . substr(md5(rand()), 0, 10),
                'symbol' => $symbol,
                'quantity' => $qty,
                'side' => $side,
                'type' => $type,
                'status' => $status,
                'submitted_at' => $created,
                'updated_at' => $updated,
                'filled_qty' => $status == 'filled' ? $qty : ($status == 'open' ? rand(0, $qty) : 0),
                'filled_avg_price' => $status == 'filled' || ($status == 'open' && rand(0, 1)) ? number_format($price + (rand(-20, 20) / 100), 2) : null,
                'limit_price' => in_array($type, array('limit', 'stop_limit')) ? number_format($price, 2) : null,
                'stop_price' => in_array($type, array('stop', 'stop_limit')) ? number_format($price + (rand(-50, 50) / 100), 2) : null,
                'time_in_force' => rand(0, 1) ? 'day' : 'gtc'
            );
        }
        
        return $orders;
    }
}
