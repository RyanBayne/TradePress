<?php
/**
 * Directive Data Requirements Display System
 * Integrates with API Capability Matrix to show data needs and compatible platforms
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Directive_Data_Requirements {
    
    /**
     * Get data requirements for a specific directive
     */
    public static function get_directive_requirements($directive_id) {
        // Load API capability matrix
        if (!class_exists('TradePress_API_Capability_Matrix')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/api-capability-matrix.php';
        }
        
        // Map directive IDs to their data requirements
        $directive_data_map = array(
            'rsi' => array('rsi', 'quote'),
            'cci' => array('cci', 'quote'),
            'macd' => array('macd', 'quote'),
            'adx' => array('adx', 'quote'),
            'volume' => array('volume', 'quote'),
            'bollinger_bands' => array('sma', 'quote'),
            'ema' => array('ema', 'quote'),
            'mfi' => array('mfi', 'volume', 'quote'),
            'obv' => array('obv', 'volume', 'quote'),
            'moving_averages' => array('sma', 'ema', 'quote'),
            'price_above_sma_50' => array('sma', 'quote'),
            'support_resistance_levels' => array('quote', 'volume'),
            'news_sentiment_positive' => array('news', 'quote'),
            'dividend_yield_attractive' => array('fundamentals', 'quote')
        );
        
        $data_needs = $directive_data_map[$directive_id] ?? array('quote');
        
        $requirements = array();
        foreach ($data_needs as $data_type) {
            $platforms = TradePress_API_Capability_Matrix::get_platforms_for_data_type($data_type);
            $freshness = TradePress_API_Capability_Matrix::get_freshness_requirement($data_type);
            
            $requirements[] = array(
                'data_type' => $data_type,
                'platforms' => $platforms,
                'freshness_seconds' => $freshness,
                'freshness_display' => self::format_freshness($freshness)
            );
        }
        
        return $requirements;
    }
    
    /**
     * Format freshness seconds into human readable format
     */
    private static function format_freshness($seconds) {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . ' minutes';
        } else {
            return round($seconds / 3600, 1) . ' hours';
        }
    }
    
    /**
     * Render data requirements container for directive page
     */
    public static function render_requirements_container($directive_id, $directive_name) {
        $requirements = self::get_directive_requirements($directive_id);
        
        if (empty($requirements)) {
            return;
        }
        
        ?>
        <div class="directive-details-container">
            <div class="directive-section">
                <div class="section-header">
                    <h3><?php esc_html_e('Data Requirements & Compatible Platforms', 'tradepress'); ?></h3>
                </div>
                
                <div class="section-content">
                    <?php foreach ($requirements as $requirement): ?>
                    <div class="requirement-item" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <h4 style="margin: 0 0 10px 0; color: #0073aa; text-transform: uppercase; font-size: 13px;">
                            <?php echo esc_html($requirement['data_type']); ?> Data
                        </h4>
                        
                        <div class="requirement-details" style="margin-bottom: 15px;">
                            <div class="detail-row" style="display: flex; margin-bottom: 8px;">
                                <label style="flex: 0 0 120px; font-weight: 600; color: #333;">Freshness:</label>
                                <span><?php echo esc_html($requirement['freshness_display']); ?> maximum age</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 8px;">
                                <label style="flex: 0 0 120px; font-weight: 600; color: #333;">Update Frequency:</label>
                                <span><?php echo self::get_update_frequency($requirement['data_type']); ?></span>
                            </div>
                            <div class="detail-row" style="display: flex;">
                                <label style="flex: 0 0 120px; font-weight: 600; color: #333;">Cost Level:</label>
                                <span><?php echo self::get_cost_level($requirement['data_type']); ?></span>
                            </div>
                        </div>
                        
                        <div class="compatible-platforms">
                            <label style="font-weight: 600; color: #333; display: block; margin-bottom: 8px;">
                                Compatible Platforms (<?php echo count($requirement['platforms']); ?>):
                            </label>
                            <div class="platform-badges" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                <?php if (empty($requirement['platforms'])): ?>
                                    <span style="color: #999; font-style: italic;">No compatible platforms found</span>
                                <?php else: ?>
                                    <?php foreach ($requirement['platforms'] as $platform): ?>
                                        <span class="platform-badge" style="background: #0073aa; color: white; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">
                                            <?php echo esc_html(strtoupper($platform)); ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="data-lifecycle-info" style="margin-top: 20px; padding: 15px; background: #f0f8ff; border-left: 4px solid #0073aa; border-radius: 4px;">
                        <h4 style="margin: 0 0 10px 0; color: #0073aa;">Data Lifecycle for <?php echo esc_html($directive_name); ?></h4>
                        <div class="lifecycle-details">
                            <div style="margin-bottom: 8px;">
                                <strong>Market Hours:</strong> Real-time updates every 15 minutes (9:30 AM - 4:00 PM ET)
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>After Hours:</strong> Final calculation at market close (4:00 PM ET)
                            </div>
                            <div style="margin-bottom: 8px;">
                                <strong>Weekends:</strong> No updates - uses Friday's closing values
                            </div>
                            <div>
                                <strong>Cache Strategy:</strong> <?php echo self::get_cache_strategy($directive_id); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get update frequency description for data type
     */
    private static function get_update_frequency($data_type) {
        $frequencies = array(
            'rsi' => 'Every 15 minutes during market hours',
            'cci' => 'Every 15 minutes during market hours', 
            'macd' => 'Every 15 minutes during market hours',
            'adx' => 'Every 15 minutes during market hours',
            'volume' => 'Every 5 minutes during market hours',
            'quote' => 'Every 1-5 minutes during market hours',
            'sma' => 'Every 15 minutes during market hours',
            'ema' => 'Every 15 minutes during market hours',
            'mfi' => 'Every 30 minutes during market hours',
            'obv' => 'Every 15 minutes during market hours',
            'news' => 'Every 30 minutes, 24/7',
            'fundamentals' => 'Daily after market close'
        );
        
        return $frequencies[$data_type] ?? 'Every 15 minutes during market hours';
    }
    
    /**
     * Get cost level for data type
     */
    private static function get_cost_level($data_type) {
        $costs = array(
            'rsi' => 'Medium (technical indicator)',
            'cci' => 'Medium (technical indicator)',
            'macd' => 'Medium (technical indicator)', 
            'adx' => 'Medium (technical indicator)',
            'volume' => 'Low (basic market data)',
            'quote' => 'Low (basic market data)',
            'sma' => 'Medium (technical indicator)',
            'ema' => 'Medium (technical indicator)',
            'mfi' => 'High (volume + price calculation)',
            'obv' => 'Medium (volume-based indicator)',
            'news' => 'High (premium news feeds)',
            'fundamentals' => 'Medium (company data)'
        );
        
        return $costs[$data_type] ?? 'Medium';
    }
    
    /**
     * Get cache strategy for directive
     */
    private static function get_cache_strategy($directive_id) {
        $strategies = array(
            'rsi' => '30 minutes during market hours, 24 hours after close',
            'cci' => '30 minutes during market hours, 24 hours after close',
            'macd' => '30 minutes during market hours, 24 hours after close',
            'adx' => '30 minutes during market hours, 24 hours after close',
            'volume' => '15 minutes during market hours, 24 hours after close',
            'news_sentiment_positive' => '30 minutes for news, 24 hours for sentiment scores'
        );
        
        return $strategies[$directive_id] ?? '30 minutes during market hours, 24 hours after close';
    }
}