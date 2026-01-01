<?php
/**
 * ISA Reset Directive Configuration Partial
 * 
 * @package TradePress/Admin/Directives/Partials
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

        <!-- Right Column: Active Directive Details -->
        <div class="directive-details-container">
            
            <div class="scoring-directives-sections">
                <!-- ISA Reset Directive Section -->
                <div class="directive-section isa-reset-section" id="directive-isa_reset">
                    <div class="section-header">
                        <h3>
                            <span class="construction-icon dashicons dashicons-admin-tools" title="<?php esc_attr_e('Under Construction', 'tradepress'); ?>"></span>
                            <?php esc_html_e('ISA Reset Directive', 'tradepress'); ?>
                        </h3>
                    </div>
                    
                    <div class="section-content">
                        <div class="directive-description">
                            <p><?php esc_html_e('Add additional scoring points to symbols shortly before and after the ISA (Individual Savings Account) reset period. This can help identify investment opportunities during this annual financial event.', 'tradepress'); ?></p>
                            
                            <div class="isa-info-box">
                                <h4><?php esc_html_e('About ISA Reset', 'tradepress'); ?></h4>
                                <p><?php esc_html_e('The ISA allowance resets on April 6th each year, which coincides with the UK tax year. During this period, investors often make significant contributions to their ISAs before the previous year\'s allowance expires, and then again as they utilize their new allowance.', 'tradepress'); ?></p>
                                <p><?php esc_html_e('This increased investment activity can lead to price movements and trading opportunities, particularly in popular retail stocks and investment trusts. The directive automatically increases scores for applicable symbols during this period to highlight potential trading opportunities.', 'tradepress'); ?></p>
                                <p><strong><?php esc_html_e('Next ISA Reset Date:', 'tradepress'); ?></strong> <?php echo date('F j, Y', strtotime('April 6, ' . (date('n') >= 4 && date('j') > 5 ? date('Y') + 1 : date('Y')))); ?></p>
                            </div>
                        </div>
                        
                        <div class="directive-settings">
                            <div class="setting-group">
                                <label for="isa-days-before"><?php esc_html_e('Days Before Reset:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" id="isa-days-before" min="0" max="30" value="<?php echo esc_attr(isset($isa_reset_directive['days_before']) ? $isa_reset_directive['days_before'] : 3); ?>">
                                    <p class="setting-description"><?php esc_html_e('Number of days before the ISA reset to apply scoring impact.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label for="isa-days-after"><?php esc_html_e('Days After Reset:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" id="isa-days-after" min="0" max="30" value="<?php echo esc_attr(isset($isa_reset_directive['days_after']) ? $isa_reset_directive['days_after'] : 3); ?>">
                                    <p class="setting-description"><?php esc_html_e('Number of days after the ISA reset to apply scoring impact.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="setting-group">
                                <label for="isa-score-impact"><?php esc_html_e('Score Impact:', 'tradepress'); ?></label>
                                <div class="setting-control">
                                    <input type="number" id="isa-score-impact" min="0" max="50" value="<?php echo esc_attr(isset($isa_reset_directive['score_impact']) ? $isa_reset_directive['score_impact'] : 10); ?>">
                                    <p class="setting-description"><?php esc_html_e('Points to add to the symbol score during the ISA reset period.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="directive-actions">
                                <button type="button" class="button button-primary" id="save-isa-directive">
                                    <?php esc_html_e('Save ISA Reset Directive', 'tradepress'); ?>
                                </button>
                                <span class="directive-status" id="isa-status"></span>
                            </div>
                        </div>
                    </div>
                </div>