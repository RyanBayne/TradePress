<?php
/**
 * TradePress Directive Testing System
 *
 * Handles testing of individual scoring directives with data validation
 *
 * @package TradePress/Scoring
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/data/class-data-query.php';

/**
 * TradePress_Directive_Tester Class
 */
class TradePress_Directive_Tester {

    /**
     * Test a directive against a symbol
     *
     * @param string $directive_id Directive ID
     * @param string $symbol Symbol ticker (default: NVDA)
     * @param bool $force_fresh Force fresh data import
     * @return array Test results
     */
    public static function test_directive($directive_id, $symbol = 'NVDA', $force_fresh = true) {
        $start_time = microtime(true);
        
        // Get directive definition
        $directive = self::get_directive_definition($directive_id);
        if (!$directive) {
            return self::error_result('Directive not found: ' . $directive_id);
        }
        
        // Get required data types for this directive
        $required_data = self::get_required_data_types($directive_id);
        
        // Fetch symbol data
        $symbol_data = TradePress_Data_Query::get_symbol_data($symbol, $required_data, $force_fresh);
        
        // Execute directive test
        $score = self::execute_directive_test($directive_id, $symbol, $symbol_data);
        
        $execution_time = round((microtime(true) - $start_time) * 1000, 2);
        
        return array(
            'success' => true,
            'directive_id' => $directive_id,
            'symbol' => $symbol,
            'score' => $score,
            'execution_time' => $execution_time . 'ms',
            'data_status' => self::get_data_status_summary($symbol, $required_data),
            'message' => self::get_test_message($directive_id, $score),
            'data_analysis' => self::get_data_analysis($symbol_data)
        );
    }

    /**
     * Execute directive test logic
     *
     * @param string $directive_id Directive ID
     * @param string $symbol Symbol ticker
     * @param array $symbol_data Symbol data
     * @return int Score result
     */
    private static function execute_directive_test($directive_id, $symbol, $symbol_data) {
        switch ($directive_id) {
            case 'isa_reset':
                return self::test_isa_reset_directive($symbol_data);
                
            case 'rsi_oversold':
                return self::test_rsi_oversold_directive($symbol_data);
                
            case 'earnings_proximity':
                return self::test_earnings_proximity_directive($symbol_data);
                
            default:
                // Demo scoring for unknown directives
                return rand(0, 100);
        }
    }

    /**
     * Test ISA Reset directive
     */
    private static function test_isa_reset_directive($symbol_data) {
        $current_date = current_time('Y-m-d');
        $isa_reset_date = date('Y') . '-04-06';
        
        $days_to_reset = (strtotime($isa_reset_date) - strtotime($current_date)) / 86400;
        
        // Score based on proximity to ISA reset
        if (abs($days_to_reset) <= 7) {
            return 15; // High score near reset
        } elseif (abs($days_to_reset) <= 30) {
            return 8;  // Medium score within month
        }
        
        return 0; // No score outside period
    }

    /**
     * Test RSI Oversold directive
     */
    private static function test_rsi_oversold_directive($symbol_data) {
        // Demo RSI calculation
        $rsi = isset($symbol_data['technical']['rsi']) ? $symbol_data['technical']['rsi'] : rand(20, 80);
        
        if ($rsi < 30) {
            return 20; // Strong oversold signal
        } elseif ($rsi < 40) {
            return 10; // Moderate oversold signal
        }
        
        return 0;
    }

    /**
     * Test Earnings Proximity directive
     */
    private static function test_earnings_proximity_directive($symbol_data) {
        if (empty($symbol_data['earnings'])) {
            return 0;
        }
        
        $next_earnings = $symbol_data['earnings'][0];
        $days_to_earnings = (strtotime($next_earnings['date']) - current_time('timestamp')) / 86400;
        
        if ($days_to_earnings <= 7 && $days_to_earnings >= 0) {
            return 12; // Score for upcoming earnings
        }
        
        return 0;
    }

    /**
     * Get required data types for directive
     */
    private static function get_required_data_types($directive_id) {
        $requirements = array(
            'isa_reset' => array('price_data'),
            'rsi_oversold' => array('price_data', 'technical'),
            'earnings_proximity' => array('earnings'),
            'macd_crossover' => array('price_data', 'technical'),
            'volume_spike' => array('price_data')
        );
        
        return isset($requirements[$directive_id]) ? $requirements[$directive_id] : array('price_data');
    }

    /**
     * Get directive definition
     */
    private static function get_directive_definition($directive_id) {
        if (function_exists('tradepress_get_directive_by_id')) {
            return tradepress_get_directive_by_id($directive_id);
        }
        
        // Fallback definitions
        $definitions = array(
            'isa_reset' => array('name' => 'ISA Reset', 'active' => true),
            'rsi_oversold' => array('name' => 'RSI Oversold', 'active' => true),
            'earnings_proximity' => array('name' => 'Earnings Proximity', 'active' => true)
        );
        
        return isset($definitions[$directive_id]) ? $definitions[$directive_id] : null;
    }

    /**
     * Get data status summary
     */
    private static function get_data_status_summary($symbol, $required_data) {
        $status = array();
        
        foreach ($required_data as $type) {
            $status[$type] = TradePress_Data_Query::get_data_status($symbol, $type);
        }
        
        return $status;
    }

    /**
     * Get test message based on score
     */
    private static function get_test_message($directive_id, $score) {
        if ($score > 15) {
            return "Strong signal detected - directive scoring high";
        } elseif ($score > 5) {
            return "Moderate signal detected - directive scoring medium";
        } else {
            return "No significant signal - directive not triggered";
        }
    }

    /**
     * Get data analysis for display
     */
    private static function get_data_analysis($symbol_data) {
        $analysis = array();
        
        foreach ($symbol_data as $type => $data) {
            if ($data) {
                switch ($type) {
                    case 'price_data':
                        $analysis['Current Price'] = '$' . (isset($data['close']) ? $data['close'] : 'N/A');
                        $analysis['Volume'] = isset($data['volume']) ? number_format($data['volume']) : 'N/A';
                        break;
                        
                    case 'earnings':
                        $analysis['Next Earnings'] = !empty($data) ? $data[0]['date'] : 'N/A';
                        break;
                        
                    case 'fundamentals':
                        $analysis['Market Cap'] = isset($data['market_cap']) ? $data['market_cap'] : 'N/A';
                        break;
                }
            }
        }
        
        return $analysis;
    }

    /**
     * Return error result
     */
    private static function error_result($message) {
        return array(
            'success' => false,
            'error' => $message,
            'score' => 0
        );
    }
}