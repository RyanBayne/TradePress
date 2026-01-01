<?php
/**
 * TradePress Risk Factors Registry
 *
 * Registry for storing and retrieving risk factors and thresholds
 * for different trading strategies and market conditions.
 *
 * @package TradePress
 * @subpackage Risks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Risk_Factors_Registry' ) ) :

/**
 * Risk Factors Registry Class
 */
class TradePress_Risk_Factors_Registry {
    
    /**
     * The single instance of this class
     *
     * @var TradePress_Risk_Factors_Registry
     */
    private static $instance = null;
    
    /**
     * Risk factors registry
     *
     * @var array
     */
    private $risk_factors = array();
    
    /**
     * Sector risk ratings
     *
     * @var array
     */
    private $sector_ratings = array();
    
    /**
     * Main TradePress Risk Factors Registry Instance.
     *
     * Ensures only one instance of TradePress Risk Factors Registry is loaded or can be loaded.
     *
     * @return TradePress_Risk_Factors_Registry - Main instance.
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->initialize_risk_factors();
        $this->initialize_sector_ratings();
        
        // Schedule daily refresh of risk factors
        add_action('init', array($this, 'schedule_risk_factors_refresh'));
        
        // Hook refresh action
        add_action('tradepress_refresh_risk_factors', array($this, 'refresh_risk_factors'));
    }
    
    /**
     * Initialize default risk factors
     */
    private function initialize_risk_factors() {
        // Get stored risk factors from database
        $stored_factors = get_option('tradepress_risk_factors', array());
        
        // Default risk factors
        $default_factors = array(
            'portfolio_risk' => array(
                'max_position_size_percent' => 5,
                'max_sector_exposure_percent' => 20,
                'max_correlation_threshold' => 0.8,
                'default_risk_score_threshold' => 25,
                'high_risk_score_threshold' => 50,
                'severe_risk_score_threshold' => 75,
            ),
            'volatility_thresholds' => array(
                'low_vix_threshold' => 15,
                'high_vix_threshold' => 25,
                'vix_spike_percent' => 20,
                'unusual_volume_threshold_percent' => 200,
                'unusual_price_movement_percent' => 4,
                'correlation_breakdown_threshold' => 0.3,
            ),
            'risk_response' => array(
                'moderate_stop_adjustment_percent' => 20,
                'high_stop_adjustment_percent' => 40,
                'high_position_reduction_percent' => 50,
            ),
            'risk_score_weights' => array(
                'portfolio_percentage_weight' => 0.20,
                'unrealized_loss_weight' => 0.15,
                'time_held_weight' => 0.05,
                'market_volatility_weight' => 0.15,
                'correlation_weight' => 0.10,
                'symbol_volatility_weight' => 0.10,
                'sector_risk_weight' => 0.10,
                'earnings_proximity_weight' => 0.10,
                'technical_signals_weight' => 0.05,
            ),
        );
        
        // Merge stored factors with defaults (using stored values when available)
        $this->risk_factors = array_replace_recursive($default_factors, $stored_factors);
    }
    
    /**
     * Initialize sector risk ratings
     */
    private function initialize_sector_ratings() {
        // Get stored sector ratings from database
        $stored_ratings = get_option('tradepress_sector_risk_ratings', array());
        
        // Default sector risk ratings (0-1 scale)
        $default_ratings = array(
            'Technology' => 0.7,
            'Healthcare' => 0.5,
            'Financials' => 0.6,
            'Consumer Discretionary' => 0.6,
            'Consumer Staples' => 0.3,
            'Energy' => 0.8,
            'Materials' => 0.6,
            'Industrials' => 0.5,
            'Utilities' => 0.3,
            'Real Estate' => 0.5,
            'Communication Services' => 0.6,
        );
        
        // Merge stored ratings with defaults
        $this->sector_ratings = array_merge($default_ratings, $stored_ratings);
    }
    
    /**
     * Schedule daily refresh of risk factors
     */
    public function schedule_risk_factors_refresh() {
        if (!wp_next_scheduled('tradepress_refresh_risk_factors')) {
            wp_schedule_event(time(), 'daily', 'tradepress_refresh_risk_factors');
        }
    }
    
    /**
     * Refresh risk factors based on current market conditions
     */
    public function refresh_risk_factors() {
        // Update sector risk ratings based on recent performance
        $this->update_sector_ratings();
        
        // Update volatility thresholds based on recent market data
        $this->update_volatility_thresholds();
        
        // Store updated risk factors
        update_option('tradepress_risk_factors', $this->risk_factors);
        update_option('tradepress_sector_risk_ratings', $this->sector_ratings);
    }
    
    /**
     * Update sector risk ratings based on market conditions
     */
    private function update_sector_ratings() {
        // Implementation would analyze recent sector performance and volatility
        // This is a simplified placeholder
        $market_data = new TradePress_Market_Data();
        
        // Sector ETFs to analyze
        $sector_etfs = array(
            'Technology' => 'XLK',
            'Healthcare' => 'XLV',
            'Financials' => 'XLF',
            'Consumer Discretionary' => 'XLY',
            'Consumer Staples' => 'XLP',
            'Energy' => 'XLE',
            'Materials' => 'XLB',
            'Industrials' => 'XLI',
            'Utilities' => 'XLU',
            'Real Estate' => 'XLRE',
            'Communication Services' => 'XLC',
        );
        
        foreach ($sector_etfs as $sector => $etf) {
            // Get recent volatility for sector
            $data = $market_data->get_market_data($etf, 30);
            
            if (!empty($data)) {
                // Calculate volatility (simple implementation)
                $prices = array_column($data, 'close');
                $percent_changes = array();
                
                for ($i = 1; $i < count($prices); $i++) {
                    $percent_changes[] = abs(($prices[$i-1] - $prices[$i]) / $prices[$i]) * 100;
                }
                
                $avg_volatility = array_sum($percent_changes) / count($percent_changes);
                
                // Normalize volatility to 0-1 scale
                // Assuming 5% daily volatility is maximum (scale accordingly)
                $normalized_volatility = min(1, $avg_volatility / 5);
                
                // Update sector rating (blend of existing and new rating)
                $current_rating = $this->sector_ratings[$sector];
                $this->sector_ratings[$sector] = ($current_rating * 0.7) + ($normalized_volatility * 0.3);
            }
        }
    }
    
    /**
     * Update volatility thresholds based on recent market data
     */
    private function update_volatility_thresholds() {
        // Implementation would analyze recent market volatility
        // This is a simplified placeholder
        $market_data = new TradePress_Market_Data();
        
        // Get VIX data for past 90 days
        $vix_data = $market_data->get_market_data('VIX', 90);
        
        if (!empty($vix_data)) {
            $vix_values = array_column($vix_data, 'close');
            
            // Calculate percentiles
            sort($vix_values);
            $count = count($vix_values);
            
            // 25th percentile for low threshold
            $low_index = floor($count * 0.25);
            $low_vix = $vix_values[$low_index];
            
            // 75th percentile for high threshold
            $high_index = floor($count * 0.75);
            $high_vix = $vix_values[$high_index];
            
            // Update thresholds (with some smoothing to avoid rapid changes)
            $current_low = $this->risk_factors['volatility_thresholds']['low_vix_threshold'];
            $current_high = $this->risk_factors['volatility_thresholds']['high_vix_threshold'];
            
            $this->risk_factors['volatility_thresholds']['low_vix_threshold'] = 
                round(($current_low * 0.7) + ($low_vix * 0.3));
            
            $this->risk_factors['volatility_thresholds']['high_vix_threshold'] = 
                round(($current_high * 0.7) + ($high_vix * 0.3));
        }
    }
    
    /**
     * Get risk factor
     *
     * @param string $category Risk factor category
     * @param string $factor Risk factor name
     * @return mixed Risk factor value or null if not found
     */
    public function get_risk_factor($category, $factor) {
        if (isset($this->risk_factors[$category][$factor])) {
            return $this->risk_factors[$category][$factor];
        }
        return null;
    }
    
    /**
     * Set risk factor
     *
     * @param string $category Risk factor category
     * @param string $factor Risk factor name
     * @param mixed $value Risk factor value
     * @return void
     */
    public function set_risk_factor($category, $factor, $value) {
        if (!isset($this->risk_factors[$category])) {
            $this->risk_factors[$category] = array();
        }
        
        $this->risk_factors[$category][$factor] = $value;
        
        // Update option in database
        update_option('tradepress_risk_factors', $this->risk_factors);
    }
    
    /**
     * Get all risk factors
     *
     * @return array All risk factors
     */
    public function get_all_risk_factors() {
        return $this->risk_factors;
    }
    
    /**
     * Get risk factors for a category
     *
     * @param string $category Risk factor category
     * @return array|null Risk factors for category or null if not found
     */
    public function get_category_risk_factors($category) {
        if (isset($this->risk_factors[$category])) {
            return $this->risk_factors[$category];
        }
        return null;
    }
    
    /**
     * Get sector risk rating
     *
     * @param string $sector Sector name
     * @return float Risk rating (0-1) or 0.5 if not found
     */
    public function get_sector_risk_rating($sector) {
        if (isset($this->sector_ratings[$sector])) {
            return $this->sector_ratings[$sector];
        }
        return 0.5; // Default to medium risk if sector not found
    }
    
    /**
     * Set sector risk rating
     *
     * @param string $sector Sector name
     * @param float $rating Risk rating (0-1)
     * @return void
     */
    public function set_sector_risk_rating($sector, $rating) {
        // Ensure rating is between 0 and 1
        $rating = min(1, max(0, $rating));
        
        $this->sector_ratings[$sector] = $rating;
        
        // Update option in database
        update_option('tradepress_sector_risk_ratings', $this->sector_ratings);
    }
    
    /**
     * Get all sector risk ratings
     *
     * @return array All sector risk ratings
     */
    public function get_all_sector_risk_ratings() {
        return $this->sector_ratings;
    }
}

endif; // End if class_exists check
