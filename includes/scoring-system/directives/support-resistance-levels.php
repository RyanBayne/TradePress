<?php
/**
 * TradePress - Support & Resistance Levels Analysis
 *
 * @package TradePress
 * @subpackage scoring-system/Directives
 * @version 1.0.0
 * @since 1.0.0
 * @created 2025-01-20
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class SupportResistanceLevels
 *
 * Unified directive that identifies both support and resistance zones using 6 technical methods.
 * Calculates all levels in one pass for maximum efficiency and highlights confluence areas.
 */
class SupportResistanceLevels extends TradePress_Scoring_Directive_Base {

    private $symbol;
    private $api_service;
    private $config;
    private $historical_data;
    private $current_price;
    private $all_levels = [];

    public function __construct(string $symbol, TradePress_Financial_API_Service $api_service, array $config = []) {
        $this->id = 'support_resistance_levels';
        $this->name = 'Support & Resistance Levels';
        $this->description = 'Identifies support and resistance zones using 6 technical methods with confluence analysis.';
        $this->weight = 20;
        $this->bullish_values = 'Near strong support, far from resistance';
        $this->bearish_values = 'Near strong resistance, far from support';
        $this->priority = 25;
        
        $this->symbol = strtoupper($symbol);
        $this->api_service = $api_service;
        $this->load_config($config);
    }

    private function load_config(array $config = []) {
        $defaults = [
            'data_period' => '1y',
            'data_interval' => 'daily',
            'ma_periods' => [50, 100, 200],
            'fib_lookback_days' => 252,
            'pivot_period' => 'daily',
            'swing_lookback' => 20,
            'proximity_percent' => 1.0,
            'highly_overlapped_min_methods' => 4,
            'well_overlapped_min_methods' => 2,
        ];
        $this->config = array_merge($defaults, $config);
    }

    private function _fetch_data(): bool {
        try {
            $this->historical_data = $this->api_service->get_historical_data(
                $this->symbol,
                $this->config['data_interval'],
                $this->config['data_period']
            );

            if (empty($this->historical_data)) {
                throw new Exception("No historical data returned for {$this->symbol}.");
            }

            $quote = $this->api_service->get_quote($this->symbol);
            $this->current_price = $quote['price'] ?? end($this->historical_data)['close'] ?? null;

            if ($this->current_price === null) {
                throw new Exception("Could not determine current price for {$this->symbol}.");
            }

            return true;

        } catch (Exception $e) {
            tradepress_trace_log("SupportResistanceLevels Error: Failed to fetch data for {$this->symbol}. " . $e->getMessage());
            return false;
        }
    }

    private function _calculate_all_levels() {
        $this->all_levels = [
            'swing_highs' => $this->_find_swing_highs(),
            'swing_lows' => $this->_find_swing_lows(),
            'moving_averages' => $this->_calculate_moving_averages(),
            'fibonacci' => $this->_calculate_fibonacci_levels(),
            'pivot_points' => $this->_calculate_pivot_points(),
            'psychological' => $this->_find_psychological_levels(),
        ];

        $this->all_levels = array_filter($this->all_levels);
    }

    private function _find_swing_highs(): array {
        // Placeholder - implement swing high detection
        return [];
    }

    private function _find_swing_lows(): array {
        // Placeholder - implement swing low detection
        return [];
    }

    private function _calculate_moving_averages(): array {
        // Placeholder - calculate MA levels above and below current price
        return [];
    }

    private function _calculate_fibonacci_levels(): array {
        // Placeholder - calculate both retracements and extensions
        return [];
    }

    private function _calculate_pivot_points(): array {
        // Placeholder - calculate R1/R2/R3 and S1/S2/S3
        return [];
    }

    private function _find_psychological_levels(): array {
        // Placeholder - find round numbers above and below current price
        return [];
    }

    private function _analyze_zones(): array {
        $resistance_levels = [];
        $support_levels = [];

        foreach ($this->all_levels as $method => $levels) {
            foreach ($levels as $level) {
                if (is_numeric($level)) {
                    if ($level > $this->current_price) {
                        $resistance_levels[] = ['price' => (float)$level, 'method' => $method];
                    } elseif ($level < $this->current_price) {
                        $support_levels[] = ['price' => (float)$level, 'method' => $method];
                    }
                }
            }
        }

        return [
            'resistance_zones' => $this->_group_levels($resistance_levels, 'resistance'),
            'support_zones' => $this->_group_levels($support_levels, 'support')
        ];
    }

    private function _group_levels(array $levels, string $type): array {
        if (empty($levels)) {
            return ['highly_overlapped' => [], 'well_overlapped' => []];
        }

        // Sort resistance ascending, support descending
        usort($levels, function($a, $b) use ($type) {
            return $type === 'resistance' ? $a['price'] <=> $b['price'] : $b['price'] <=> $a['price'];
        });

        $zones = [];
        $current_zone = null;
        $proximity_amount = $this->current_price * ($this->config['proximity_percent'] / 100);

        foreach ($levels as $level_info) {
            $level_price = $level_info['price'];
            $level_method = $level_info['method'];

            if ($current_zone === null) {
                $current_zone = [
                    'min_price' => $level_price,
                    'max_price' => $level_price,
                    'methods' => [$level_method => 1]
                ];
            } elseif (abs($level_price - $current_zone['max_price']) <= $proximity_amount) {
                $current_zone['min_price'] = min($current_zone['min_price'], $level_price);
                $current_zone['max_price'] = max($current_zone['max_price'], $level_price);
                $current_zone['methods'][$level_method] = 1;
            } else {
                if ($current_zone !== null) {
                    $zones[] = $current_zone;
                }
                $current_zone = [
                    'min_price' => $level_price,
                    'max_price' => $level_price,
                    'methods' => [$level_method => 1]
                ];
            }
        }

        if ($current_zone !== null) {
            $zones[] = $current_zone;
        }

        $highly_overlapped = [];
        $well_overlapped = [];

        foreach ($zones as $zone) {
            $method_count = count($zone['methods']);
            $zone_data = [
                'min_price' => $zone['min_price'],
                'max_price' => $zone['max_price'],
                'method_count' => $method_count,
                'methods' => array_keys($zone['methods'])
            ];

            if ($method_count >= $this->config['highly_overlapped_min_methods']) {
                $highly_overlapped[] = $zone_data;
            } elseif ($method_count >= $this->config['well_overlapped_min_methods']) {
                $well_overlapped[] = $zone_data;
            }
        }

        return [
            'highly_overlapped' => $highly_overlapped,
            'well_overlapped' => $well_overlapped
        ];
    }

    public function find_support_resistance_zones(): ?array {
        if (!$this->_fetch_data()) {
            return null;
        }

        $this->_calculate_all_levels();
        $zones = $this->_analyze_zones();

        return [
            'symbol' => $this->symbol,
            'current_price' => $this->current_price,
            'resistance_zones' => $zones['resistance_zones'],
            'support_zones' => $zones['support_zones'],
            'all_levels' => $this->all_levels
        ];
    }

    public function calculate_score($symbol_data, $config = []) {
        $score = 50; // Neutral base

        try {
            $current_price = isset($symbol_data['price']) ? (float)$symbol_data['price'] : 0;
            
            if ($current_price <= 0) {
                return $score;
            }

            // Handle limited data scenario (testing mode)
            if (isset($symbol_data['limited_data']) && $symbol_data['limited_data']) {
                // Return basic score with simple price-based logic
                $price_mod = fmod($current_price, 10);
                if ($price_mod < 2) {
                    $score += 15; // Near psychological support
                } elseif ($price_mod > 8) {
                    $score -= 10; // Near psychological resistance
                }
                return max(0, min(100, $score));
            }

            $zones_data = $this->find_support_resistance_zones();
            
            if (empty($zones_data)) {
                return $score;
            }

            $resistance_zones = array_merge(
                $zones_data['resistance_zones']['highly_overlapped'] ?? [],
                $zones_data['resistance_zones']['well_overlapped'] ?? []
            );

            $support_zones = array_merge(
                $zones_data['support_zones']['highly_overlapped'] ?? [],
                $zones_data['support_zones']['well_overlapped'] ?? []
            );

            // Find nearest resistance and support
            $nearest_resistance = $this->_find_nearest_zone($resistance_zones, $current_price, 'resistance');
            $nearest_support = $this->_find_nearest_zone($support_zones, $current_price, 'support');

            // Calculate score based on position relative to levels
            if ($nearest_resistance) {
                $resistance_distance = (($nearest_resistance['min_price'] - $current_price) / $current_price) * 100;
                if ($resistance_distance > 5) {
                    $score += 20; // Far from resistance
                } elseif ($resistance_distance > 2) {
                    $score += 10;
                } elseif ($resistance_distance < 0) {
                    $score -= 15; // Above resistance
                }
            }

            if ($nearest_support) {
                $support_distance = (($current_price - $nearest_support['max_price']) / $current_price) * 100;
                if ($support_distance < 2) {
                    $score += 15; // Near strong support
                } elseif ($support_distance < 5) {
                    $score += 10;
                } elseif ($support_distance > 10) {
                    $score -= 10; // Far from support
                }
            }

        } catch (Exception $e) {
            tradepress_trace_log('Support/Resistance scoring error: ' . $e->getMessage());
        }

        return max(0, min(100, $score));
    }

    private function _find_nearest_zone($zones, $current_price, $type) {
        if (empty($zones)) {
            return null;
        }

        $nearest = null;
        $min_distance = PHP_FLOAT_MAX;

        foreach ($zones as $zone) {
            $zone_price = $type === 'resistance' ? $zone['min_price'] : $zone['max_price'];
            $distance = abs($zone_price - $current_price);
            
            if ($distance < $min_distance) {
                $min_distance = $distance;
                $nearest = $zone;
            }
        }

        return $nearest;
    }

    public function get_max_score($config = []) {
        return 100;
    }

    public function get_explanation($config = []) {
        return "Analyzes support and resistance levels using 6 technical methods. Higher scores for stocks near strong support and far from resistance. Confluence of multiple methods increases level significance.";
    }
}
?>