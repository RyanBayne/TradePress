<?php
/**
 * TradePress Earnings Whisper Scorer
 *
 * Scores companies based on parsed earnings whisper data to identify trading opportunities.
 *
 * @package TradePress\Scoring
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Earnings_Whisper_Scorer {
    
    /**
     * Maximum possible score a company can achieve
     */
    const MAX_SCORE = 100;
    
    /**
     * Scoring weights for different data components
     */
    private $weights = [
        // Upcoming earnings weights
        'whisper_vs_consensus' => 25,     // How much whisper differs from consensus
        'investor_sentiment' => 20,       // Bullish/bearish sentiment percentage
        'earnings_revision_trend' => 15,  // Recent estimate revisions
        'options_activity' => 15,         // Unusual options activity
        'stock_momentum' => 10,           // Recent stock performance
        'short_interest_change' => 10,    // Changes in short interest
        'guidance_vs_consensus' => 5,     // Company guidance vs consensus
        
        // Past results weights
        'beat_miss_magnitude' => 30,      // How much they beat/missed by
        'revenue_growth' => 25,           // Year-over-year revenue growth
        'guidance_quality' => 20,         // Forward guidance strength
        'market_reaction_potential' => 15, // Historical reaction patterns
        'earnings_consistency' => 10,     // Track record of beats/misses
    ];
    
    /**
     * Score a single company based on parsed earnings data
     *
     * @param array $company_data Parsed company data from earnings whisper
     * @return array Score breakdown and total
     */
    public function score_company($company_data) {
        $score_breakdown = [];
        $total_score = 0;
        $max_possible = 0;
        
        if ($company_data['report_type'] === 'upcoming_earnings') {
            return $this->score_upcoming_earnings($company_data);
        } else {
            return $this->score_past_results($company_data);
        }
    }
    
    /**
     * Score upcoming earnings announcements
     */
    private function score_upcoming_earnings($data) {
        $scores = [];
        $total_score = 0;
        $max_possible = 0;
        
        // 1. Whisper vs Consensus Analysis (25 points)
        if ($data['earnings_whisper_number'] !== 'N/A' && $data['consensus_eps'] !== 'N/A') {
            $whisper_score = $this->calculate_whisper_vs_consensus_score(
                $data['earnings_whisper_number'], 
                $data['consensus_eps']
            );
            $scores['whisper_vs_consensus'] = $whisper_score;
            $total_score += $whisper_score * ($this->weights['whisper_vs_consensus'] / 100);
            $max_possible += $this->weights['whisper_vs_consensus'];
        }
        
        // 2. Investor Sentiment (20 points)
        if ($data['expecting_beat_percentage'] !== 'N/A') {
            $sentiment_score = $this->calculate_sentiment_score($data['expecting_beat_percentage']);
            $scores['investor_sentiment'] = $sentiment_score;
            $total_score += $sentiment_score * ($this->weights['investor_sentiment'] / 100);
            $max_possible += $this->weights['investor_sentiment'];
        }
        
        // 3. Earnings Revision Trend (15 points)
        if ($data['earnings_estimates_revision'] !== 'N/A') {
            $revision_score = $this->calculate_revision_score($data['earnings_estimates_revision']);
            $scores['earnings_revision_trend'] = $revision_score;
            $total_score += $revision_score * ($this->weights['earnings_revision_trend'] / 100);
            $max_possible += $this->weights['earnings_revision_trend'];
        }
        
        // 4. Options Activity (15 points)
        if ($data['options_activity_type'] !== 'N/A') {
            $options_score = $this->calculate_options_activity_score(
                $data['options_activity_type'], 
                $data['options_activity_details']
            );
            $scores['options_activity'] = $options_score;
            $total_score += $options_score * ($this->weights['options_activity'] / 100);
            $max_possible += $this->weights['options_activity'];
        }
        
        // 5. Stock Momentum (10 points)
        if ($data['stock_performance_since_last_earnings'] !== 'N/A') {
            $momentum_score = $this->calculate_momentum_score($data['stock_performance_since_last_earnings']);
            $scores['stock_momentum'] = $momentum_score;
            $total_score += $momentum_score * ($this->weights['stock_momentum'] / 100);
            $max_possible += $this->weights['stock_momentum'];
        }
        
        // 6. Short Interest Change (10 points)
        if ($data['short_interest_change'] !== 'N/A') {
            $short_score = $this->calculate_short_interest_score($data['short_interest_change']);
            $scores['short_interest_change'] = $short_score;
            $total_score += $short_score * ($this->weights['short_interest_change'] / 100);
            $max_possible += $this->weights['short_interest_change'];
        }
        
        // 7. Guidance vs Consensus (5 points)
        if ($data['company_guidance_earnings'] !== 'N/A' && $data['consensus_eps'] !== 'N/A') {
            $guidance_score = $this->calculate_guidance_score(
                $data['company_guidance_earnings'], 
                $data['consensus_eps']
            );
            $scores['guidance_vs_consensus'] = $guidance_score;
            $total_score += $guidance_score * ($this->weights['guidance_vs_consensus'] / 100);
            $max_possible += $this->weights['guidance_vs_consensus'];
        }
        
        // Calculate final score as percentage of maximum possible
        $final_score = $max_possible > 0 ? ($total_score / $max_possible) * self::MAX_SCORE : 0;
        
        return [
            'total_score' => round($final_score, 2),
            'max_possible_score' => $max_possible,
            'score_breakdown' => $scores,
            'confidence_level' => $this->calculate_confidence_level($scores),
            'recommendation' => $this->generate_recommendation($final_score, $scores),
            'risk_factors' => $this->identify_risk_factors($data, $scores)
        ];
    }
    
    /**
     * Score past earnings results
     */
    private function score_past_results($data) {
        $scores = [];
        $total_score = 0;
        $max_possible = 0;
        
        // 1. Beat/Miss Magnitude (30 points)
        if ($data['beat_miss_percentage'] !== 'N/A') {
            $beat_score = $this->calculate_beat_miss_score($data['beat_miss_percentage']);
            $scores['beat_miss_magnitude'] = $beat_score;
            $total_score += $beat_score * ($this->weights['beat_miss_magnitude'] / 100);
            $max_possible += $this->weights['beat_miss_magnitude'];
        }
        
        // 2. Revenue Growth (25 points)
        if ($data['revenue_growth_yoy'] !== 'N/A') {
            $growth_score = $this->calculate_revenue_growth_score($data['revenue_growth_yoy']);
            $scores['revenue_growth'] = $growth_score;
            $total_score += $growth_score * ($this->weights['revenue_growth'] / 100);
            $max_possible += $this->weights['revenue_growth'];
        }
        
        // 3. Forward Guidance Quality (20 points)
        if ($data['forward_guidance_eps'] !== 'N/A') {
            $guidance_score = $this->calculate_forward_guidance_score(
                $data['forward_guidance_eps'], 
                $data['forward_guidance_revenue']
            );
            $scores['guidance_quality'] = $guidance_score;
            $total_score += $guidance_score * ($this->weights['guidance_quality'] / 100);
            $max_possible += $this->weights['guidance_quality'];
        }
        
        // Calculate final score
        $final_score = $max_possible > 0 ? ($total_score / $max_possible) * self::MAX_SCORE : 0;
        
        return [
            'total_score' => round($final_score, 2),
            'max_possible_score' => $max_possible,
            'score_breakdown' => $scores,
            'confidence_level' => $this->calculate_confidence_level($scores),
            'recommendation' => $this->generate_recommendation($final_score, $scores),
            'risk_factors' => $this->identify_risk_factors($data, $scores)
        ];
    }
    
    /**
     * Calculate score based on whisper vs consensus difference
     */
    private function calculate_whisper_vs_consensus_score($whisper, $consensus) {
        // Extract numeric values
        $whisper_val = $this->extract_numeric_value($whisper);
        $consensus_val = $this->extract_numeric_value($consensus);
        
        if ($whisper_val === null || $consensus_val === null || $consensus_val == 0) {
            return 50; // Neutral score if can't calculate
        }
        
        $difference_pct = (($whisper_val - $consensus_val) / abs($consensus_val)) * 100;
        
        // Positive whisper difference is bullish
        if ($difference_pct > 5) return 90;      // Strong positive divergence
        if ($difference_pct > 2) return 75;      // Moderate positive divergence
        if ($difference_pct > 0) return 60;      // Slight positive divergence
        if ($difference_pct > -2) return 50;     // Neutral
        if ($difference_pct > -5) return 40;     // Slight negative divergence
        return 25;                               // Strong negative divergence
    }
    
    /**
     * Calculate sentiment score from expecting beat percentage
     */
    private function calculate_sentiment_score($beat_percentage) {
        $percentage = floatval(str_replace('%', '', $beat_percentage));
        
        if ($percentage >= 75) return 90;        // Very bullish
        if ($percentage >= 60) return 75;        // Bullish
        if ($percentage >= 45) return 60;        // Slightly bullish
        if ($percentage >= 35) return 50;        // Neutral
        if ($percentage >= 25) return 40;        // Slightly bearish
        return 25;                               // Bearish
    }
    
    /**
     * Calculate revision trend score
     */
    private function calculate_revision_score($revision_text) {
        $revision_lower = strtolower($revision_text);
        
        if (strpos($revision_lower, 'revised higher') !== false) {
            return 80; // Positive revisions
        } elseif (strpos($revision_lower, 'revised lower') !== false) {
            return 30; // Negative revisions
        }
        
        return 50; // Neutral or unclear
    }
    
    /**
     * Calculate options activity score
     */
    private function calculate_options_activity_score($activity_type, $activity_details) {
        $activity_lower = strtolower($activity_type);
        
        // Extract if it's calls or puts
        $is_calls = strpos(strtolower($activity_details), 'call') !== false;
        $is_puts = strpos(strtolower($activity_details), 'put') !== false;
        
        if ($activity_lower === 'buying') {
            return $is_calls ? 80 : 40; // Call buying bullish, put buying bearish
        } elseif ($activity_lower === 'selling') {
            return $is_calls ? 40 : 70; // Call selling bearish, put selling bullish
        }
        
        return 50; // Neutral
    }
    
    /**
     * Calculate momentum score from stock performance text
     */
    private function calculate_momentum_score($performance_text) {
        $performance_lower = strtolower($performance_text);
        
        if (strpos($performance_lower, 'higher') !== false || strpos($performance_lower, 'up') !== false) {
            return 70; // Positive momentum
        } elseif (strpos($performance_lower, 'lower') !== false || strpos($performance_lower, 'down') !== false) {
            return 40; // Negative momentum
        }
        
        return 50; // Neutral
    }
    
    /**
     * Additional helper methods for scoring components
     */
    private function calculate_short_interest_score($short_change) {
        $change_lower = strtolower($short_change);
        
        if (strpos($change_lower, 'decreased') !== false) {
            return 70; // Decreasing short interest is bullish
        } elseif (strpos($change_lower, 'increased') !== false) {
            return 40; // Increasing short interest is bearish
        }
        
        return 50; // Neutral
    }
    
    private function calculate_guidance_score($guidance, $consensus) {
        // Compare company guidance to consensus
        $guidance_val = $this->extract_numeric_value($guidance);
        $consensus_val = $this->extract_numeric_value($consensus);
        
        if ($guidance_val === null || $consensus_val === null) {
            return 50;
        }
        
        if ($guidance_val > $consensus_val) return 75; // Guidance above consensus
        if ($guidance_val < $consensus_val) return 35; // Guidance below consensus
        return 50; // Guidance matches consensus
    }
    
    private function calculate_beat_miss_score($beat_miss_pct) {
        $percentage = floatval(str_replace('%', '', $beat_miss_pct));
        
        if ($percentage > 10) return 90;         // Large beat
        if ($percentage > 5) return 75;          // Moderate beat
        if ($percentage > 0) return 60;          // Small beat
        if ($percentage > -5) return 40;         // Small miss
        return 20;                               // Large miss
    }
    
    private function calculate_revenue_growth_score($growth_text) {
        // Extract percentage from growth text
        preg_match('/(\d+\.?\d*)%/', $growth_text, $matches);
        if (!empty($matches[1])) {
            $growth_pct = floatval($matches[1]);
            
            // Check if it's growth or decline
            $is_decline = strpos(strtolower($growth_text), 'decline') !== false || 
                         strpos(strtolower($growth_text), 'fell') !== false;
            
            if ($is_decline) $growth_pct = -$growth_pct;
            
            if ($growth_pct > 20) return 90;     // High growth
            if ($growth_pct > 10) return 75;     // Good growth
            if ($growth_pct > 5) return 60;      // Moderate growth
            if ($growth_pct > 0) return 55;      // Slight growth
            if ($growth_pct > -5) return 45;     // Slight decline
            if ($growth_pct > -10) return 30;    // Moderate decline
            return 20;                           // Large decline
        }
        
        return 50; // Neutral if can't parse
    }
    
    private function calculate_forward_guidance_score($eps_guidance, $revenue_guidance) {
        // This would need more sophisticated logic based on guidance quality
        // For now, return moderate score if guidance is provided
        if ($eps_guidance !== 'N/A' || $revenue_guidance !== 'N/A') {
            return 65; // Providing guidance is generally positive
        }
        return 40; // No guidance provided
    }
    
    /**
     * Extract numeric value from text (e.g., "$2.50 per share" -> 2.50)
     */
    private function extract_numeric_value($text) {
        // Remove currency symbols and extract number
        preg_match('/[\-\+]?\d+\.?\d*/', str_replace(['$', '€', '£'], '', $text), $matches);
        return !empty($matches[0]) ? floatval($matches[0]) : null;
    }
    
    /**
     * Calculate confidence level based on available data points
     */
    private function calculate_confidence_level($scores) {
        $total_components = count($this->weights);
        $available_components = count($scores);
        
        $confidence = ($available_components / $total_components) * 100;
        
        if ($confidence >= 80) return 'High';
        if ($confidence >= 60) return 'Medium';
        if ($confidence >= 40) return 'Low';
        return 'Very Low';
    }
    
    /**
     * Generate trading recommendation based on score
     */
    private function generate_recommendation($score, $scores) {
        if ($score >= 75) {
            return 'Strong Buy - High earnings opportunity potential';
        } elseif ($score >= 60) {
            return 'Buy - Moderate earnings opportunity potential';
        } elseif ($score >= 45) {
            return 'Hold - Mixed signals, monitor closely';
        } elseif ($score >= 30) {
            return 'Avoid - Negative indicators present';
        } else {
            return 'Strong Avoid - Multiple risk factors identified';
        }
    }
    
    /**
     * Identify specific risk factors
     */
    private function identify_risk_factors($data, $scores) {
        $risks = [];
        
        // Check for negative indicators
        if (isset($scores['whisper_vs_consensus']) && $scores['whisper_vs_consensus'] < 40) {
            $risks[] = 'Whisper numbers below consensus expectations';
        }
        
        if (isset($scores['investor_sentiment']) && $scores['investor_sentiment'] < 40) {
            $risks[] = 'Bearish investor sentiment';
        }
        
        if (isset($scores['earnings_revision_trend']) && $scores['earnings_revision_trend'] < 40) {
            $risks[] = 'Recent downward earnings revisions';
        }
        
        // Add more risk factor checks...
        
        return $risks;
    }
    
    /**
     * Batch score multiple companies and rank them
     */
    public function score_and_rank_companies($companies_data) {
        $scored_companies = [];
        
        foreach ($companies_data as $company) {
            $score_result = $this->score_company($company);
            $scored_companies[] = array_merge($company, $score_result);
        }
        
        // Sort by total score descending
        usort($scored_companies, function($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });
        
        return $scored_companies;
    }
}
