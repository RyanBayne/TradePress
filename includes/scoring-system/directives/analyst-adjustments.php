<?php
/**
 * Analyst Update After Price Drop Scoring Directive
 *
 * Scores symbols based on whether analysts have recently updated their outlook
 * after a significant price decline.
 *
 * @package TradePress/ScoringDirectives
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Scoring_Directive_Analyst_Post_Drop_Update Class
 */
class TradePress_Scoring_Directive_Analyst_Post_Drop_Update extends TradePress_Scoring_Directive_Base {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'analyst_post_drop_update';
        $this->name = 'Analyst Update After Price Drop';
        $this->description = 'Scores higher if analysts update ratings/forecasts shortly after a significant price drop, lower if they remain silent.';
        $this->weight = 18; // Example weight, adjust as needed
        $this->bullish_values = 'Recent update post-drop';
        $this->bearish_values = 'No recent update post-drop';
        $this->priority = 25; // Example priority

        // Default parameters - these can be overridden in settings
        $this->parameters = array(
            'drop_percentage' => array(
                'name' => 'Drop Percentage Threshold (%)',
                'description' => 'Minimum percentage drop from peak to trough to trigger analysis (e.g., 15 for -15%).',
                'type' => 'number',
                'default' => 15.0,
                'step' => 1.0
            ),
            'drop_lookback_days' => array(
                'name' => 'Drop Lookback Period (Days)',
                'description' => 'How many days back to look for a significant price drop.',
                'type' => 'number',
                'default' => 30,
                'step' => 1
            ),
            'update_recency_days' => array(
                'name' => 'Update Recency Window (Days)',
                'description' => 'How many days after the price trough an analyst update is considered "recent".',
                'type' => 'number',
                'default' => 5,
                'step' => 1
            ),
            'penalty_active' => array(
                'name' => 'Apply Penalty for No Update',
                'description' => 'If checked, significantly reduce score if no recent update occurs after a drop.',
                'type' => 'checkbox',
                'default' => true
            )
        );
    }

    /**
     * Calculate score based on analyst update timing relative to a price drop.
     *
     * @param array $symbol_data Symbol data containing price history and analyst update date.
     *                           Requires: ['price']['current'], ['price']['historical'] (array [date=>close]), ['analyst']['last_update_date'] (YYYY-MM-DD)
     * @param array $params Parameters for the directive (drop_percentage, drop_lookback_days, update_recency_days, penalty_active).
     * @return int Score from 0-100
     */
    public function calculate_score($symbol_data, $params = array()) {
        // Merge default parameters with any provided overrides
        $params = wp_parse_args($params, $this->get_default_parameters());

        // --- Configuration ---
        $drop_percentage_threshold = -abs(floatval($params['drop_percentage'])); // Ensure it's negative
        $drop_lookback_days = intval($params['drop_lookback_days']);
        $update_recency_days = intval($params['update_recency_days']);
        $apply_penalty = filter_var($params['penalty_active'], FILTER_VALIDATE_BOOLEAN);

        // --- Data Validation ---
        if (empty($symbol_data['price']['historical']) || $drop_lookback_days <= 0) {
            return 50; // Not enough data for analysis
        }
        if (empty($symbol_data['analyst']['last_update_date'])) {
            // If analyst data is missing, we can't apply the logic reliably.
            // Return neutral, or slightly lower if penalty is active? Let's go neutral for now.
             return 50;
        }

        // --- Detect Significant Drop ---
        $historical_prices = $symbol_data['price']['historical'];
        // Ensure data is sorted chronologically if needed (assuming keys are dates 'YYYY-MM-DD')
        ksort($historical_prices);

        // Get the relevant slice of historical data
        $relevant_prices = array_slice($historical_prices, -$drop_lookback_days, null, true);

        if (count($relevant_prices) < $drop_lookback_days * 0.8) { // Need sufficient data points
             return 50;
        }

        $peak_price = 0;
        $trough_price = PHP_FLOAT_MAX;
        $trough_date = null;

        foreach ($relevant_prices as $date => $price) {
            if ($price > $peak_price) {
                $peak_price = $price;
            }
            // Find the absolute lowest point in the period
            if ($price < $trough_price) {
                $trough_price = $price;
                $trough_date = $date;
            }
        }

        if ($peak_price <= 0 || $trough_price === PHP_FLOAT_MAX || $trough_date === null) {
            return 50; // Could not determine peak/trough
        }

        $percentage_drop = (($trough_price - $peak_price) / $peak_price) * 100;

        // --- Check Condition: Was there a significant drop? ---
        if ($percentage_drop > $drop_percentage_threshold) {
             // No significant drop detected in the lookback period
             return 50; // Return neutral score
        }

        // --- Significant Drop Detected - Check Analyst Update ---
        $last_analyst_update_date_str = $symbol_data['analyst']['last_update_date'];
        $last_analyst_update_ts = strtotime($last_analyst_update_date_str);
        $trough_ts = strtotime($trough_date);

        if (!$last_analyst_update_ts || !$trough_ts) {
            return 50; // Invalid date formats
        }

        $days_diff = ($last_analyst_update_ts - $trough_ts) / (60 * 60 * 24);

        // --- Scoring Logic ---
        if ($last_analyst_update_ts > $trough_ts && $days_diff <= $update_recency_days) {
            // BULLISH: Analyst update occurred *after* the trough and within the recency window.
            // Score higher the more recent the update (closer to the trough date).
            $recency_factor = max(0, ($update_recency_days - $days_diff) / $update_recency_days); // 1 for update on day 1 after trough, 0 for day Z+1
            $score = 75 + ($recency_factor * 20); // Score between 75 and 95
        } else {
            // BEARISH/NEUTRAL: Update occurred before/on trough OR is too old after the trough.
            if ($apply_penalty) {
                // Calculate how many days *past* the recency window we are without an update
                $days_overdue = max(0, $days_diff - $update_recency_days);
                // Penalize more the longer it's been without an update (capped penalty)
                $penalty_factor = min(1, $days_overdue / ( $update_recency_days * 2 )); // Max penalty after 2x recency window
                $score = 30 - ($penalty_factor * 20); // Score between 10 and 30
            } else {
                // If penalty is not active, return a neutral-ish score
                $score = 45;
            }
        }

        return max(0, min(100, round($score))); // Ensure score is between 0 and 100
    }

    /**
     * Get default parameters for the directive.
     *
     * @return array Default parameters.
     */
    private function get_default_parameters() {
        $defaults = array();
        foreach ($this->parameters as $key => $param) {
            $defaults[$key] = $param['default'];
        }
        return $defaults;
    }
}
