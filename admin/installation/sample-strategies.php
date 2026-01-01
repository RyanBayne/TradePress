<?php
/**
 * TradePress - Sample Strategies Installation
 * 
 * Creates sample strategies for demonstration
 *
 * @package TradePress/Admin/Installation
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Sample_Strategies {
    
    /**
     * Create sample strategies
     */
    public static function create_sample_strategies() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';
        
        // Check if sample strategies already exist
        $existing = TradePress_Scoring_Strategies_DB::get_strategies(array('limit' => 1));
        if (!empty($existing)) {
            return; // Sample strategies already exist
        }
        
        // Create Conservative Growth Strategy
        self::create_conservative_growth_strategy();
        
        // Create Momentum Trading Strategy  
        self::create_momentum_trading_strategy();
        
        // Create Value + Technical Strategy
        self::create_value_technical_strategy();
    }
    
    /**
     * Create Conservative Growth Strategy
     */
    private static function create_conservative_growth_strategy() {
        $strategy_data = array(
            'name' => 'Conservative Growth',
            'description' => 'Low-risk strategy focusing on stable growth with RSI and trend confirmation',
            'category' => 'conservative-growth',
            'status' => 'active',
            'risk_level' => 'low',
            'time_horizon' => 'long',
            'total_weight' => 100.00,
            'creator_id' => 1
        );
        
        $strategy_id = TradePress_Scoring_Strategies_DB::create_strategy($strategy_data);
        
        if (!is_wp_error($strategy_id)) {
            // Add directives
            $directives = array(
                array('directive_id' => 'rsi', 'directive_name' => 'RSI', 'weight' => 30.00, 'sort_order' => 1),
                array('directive_id' => 'price_above_sma_50', 'directive_name' => 'Price Above SMA 50', 'weight' => 25.00, 'sort_order' => 2),
                array('directive_id' => 'volume', 'directive_name' => 'Volume Analysis', 'weight' => 20.00, 'sort_order' => 3),
                array('directive_id' => 'news_sentiment_positive', 'directive_name' => 'News Sentiment', 'weight' => 15.00, 'sort_order' => 4),
                array('directive_id' => 'isa_reset', 'directive_name' => 'ISA Reset', 'weight' => 10.00, 'sort_order' => 5)
            );
            
            foreach ($directives as $directive) {
                TradePress_Scoring_Strategies_DB::add_strategy_directive($strategy_id, $directive);
            }
        }
    }
    
    /**
     * Create Momentum Trading Strategy
     */
    private static function create_momentum_trading_strategy() {
        $strategy_data = array(
            'name' => 'Momentum Trading',
            'description' => 'High-momentum strategy with technical indicators and volume confirmation',
            'category' => 'momentum-trading',
            'status' => 'draft',
            'risk_level' => 'medium',
            'time_horizon' => 'short',
            'total_weight' => 100.00,
            'creator_id' => 1
        );
        
        $strategy_id = TradePress_Scoring_Strategies_DB::create_strategy($strategy_data);
        
        if (!is_wp_error($strategy_id)) {
            // Add directives (using available ones)
            $directives = array(
                array('directive_id' => 'rsi', 'directive_name' => 'RSI', 'weight' => 25.00, 'sort_order' => 1),
                array('directive_id' => 'volume', 'directive_name' => 'Volume Analysis', 'weight' => 25.00, 'sort_order' => 2),
                array('directive_id' => 'price_above_sma_50', 'directive_name' => 'Price Above SMA 50', 'weight' => 20.00, 'sort_order' => 3),
                array('directive_id' => 'obv', 'directive_name' => 'On-Balance Volume', 'weight' => 15.00, 'sort_order' => 4),
                array('directive_id' => 'rsi_overbought', 'directive_name' => 'RSI Overbought', 'weight' => 15.00, 'sort_order' => 5)
            );
            
            foreach ($directives as $directive) {
                TradePress_Scoring_Strategies_DB::add_strategy_directive($strategy_id, $directive);
            }
        }
    }
    
    /**
     * Create Value + Technical Strategy
     */
    private static function create_value_technical_strategy() {
        $strategy_data = array(
            'name' => 'Value + Technical',
            'description' => 'Combines value investing principles with technical analysis for entry timing',
            'category' => 'value-investing',
            'status' => 'active',
            'risk_level' => 'low',
            'time_horizon' => 'long',
            'total_weight' => 100.00,
            'creator_id' => 1
        );
        
        $strategy_id = TradePress_Scoring_Strategies_DB::create_strategy($strategy_data);
        
        if (!is_wp_error($strategy_id)) {
            // Add directives
            $directives = array(
                array('directive_id' => 'support_resistance_levels', 'directive_name' => 'Support/Resistance Levels', 'weight' => 30.00, 'sort_order' => 1),
                array('directive_id' => 'rsi', 'directive_name' => 'RSI', 'weight' => 25.00, 'sort_order' => 2),
                array('directive_id' => 'obv', 'directive_name' => 'On-Balance Volume', 'weight' => 20.00, 'sort_order' => 3),
                array('directive_id' => 'news_sentiment_positive', 'directive_name' => 'News Sentiment', 'weight' => 15.00, 'sort_order' => 4),
                array('directive_id' => 'price_above_sma_50', 'directive_name' => 'Price Above SMA 50', 'weight' => 10.00, 'sort_order' => 5)
            );
            
            foreach ($directives as $directive) {
                TradePress_Scoring_Strategies_DB::add_strategy_directive($strategy_id, $directive);
            }
        }
    }
}