<?php
/**
 * Class TradePress_Prediction_Accuracy
 * 
 * Handles accuracy calculations and analysis for predictions
 * 
 * @package TradePress\Predictions
 * @version 1.0.0
 * @created 2025-04-14
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Prediction_Accuracy {
    /**
     * Calculate accuracy for a single prediction
     * 
     * @param float $predicted_price The predicted price
     * @param float $actual_price The actual price
     * @return float Accuracy percentage
     */
    public function calculate_single_accuracy($predicted_price, $actual_price) {
        if ($actual_price == 0) {
            return 0; // Avoid division by zero
        }
        
        $difference = abs($predicted_price - $actual_price);
        $percentage_diff = ($difference / $actual_price) * 100;
        
        // Convert to accuracy percentage (100% - error%)
        $accuracy = 100 - $percentage_diff;
        
        // Ensure we don't return negative accuracy
        return max(0, $accuracy);
    }
    
    /**
     * Calculate accuracy for predictions that provide a range
     * 
     * @param float $predicted_low Low end of prediction range
     * @param float $predicted_high High end of prediction range
     * @param float $actual_price The actual price
     * @return float Accuracy percentage
     */
    public function calculate_range_accuracy($predicted_low, $predicted_high, $actual_price) {
        // Implementation for range-based predictions
    }
    
    /**
     * Calculate source performance metrics
     * 
     * @param int $source_id Source ID
     * @param string|null $symbol Optional symbol to limit analysis
     * @param string $time_horizon Time horizon to analyze
     * @return array Performance metrics
     */
    public function calculate_source_performance($source_id, $symbol = null, $time_horizon = 'all') {
        // Implementation to calculate overall source performance
    }
    
    /**
     * Compare performance between sources
     * 
     * @param array $source_ids Source IDs to compare
     * @param string|null $symbol Optional symbol to limit comparison
     * @param string $time_horizon Time horizon to analyze
     * @return array Comparative performance data
     */
    public function compare_sources($source_ids, $symbol = null, $time_horizon = 'all') {
        // Implementation to compare sources
    }
}