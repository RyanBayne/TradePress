<?php
/**
 * UI Library Working Notes Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.6
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Working Notes', 'tradepress'); ?></h3>
    <p><?php esc_html_e('Components for capturing and displaying notes and documentation during the trading process.', 'tradepress'); ?></p>
    
    <div class="notes-showcase">
        <!-- Basic Notes Editor -->
        <div class="component-demo">
            <h4><?php esc_html_e('Basic Notes Editor', 'tradepress'); ?></h4>
            <div class="working-notes-container">
                <div class="working-notes-header">
                    <h5 class="working-notes-title"><?php esc_html_e('Trading Notes', 'tradepress'); ?></h5>
                </div>
                <div class="working-notes-editor">
                    <textarea class="working-notes-textarea" placeholder="<?php esc_attr_e('Enter your trading notes here...', 'tradepress'); ?>"></textarea>
                </div>
                <div class="working-notes-footer">
                    <div class="working-notes-meta">
                        <?php esc_html_e('Last edited: Today at 10:45 AM', 'tradepress'); ?>
                    </div>
                    <div class="working-notes-buttons">
                        <button class="tp-button tp-button-secondary"><?php esc_html_e('Discard', 'tradepress'); ?></button>
                        <button class="tp-button tp-button-primary"><?php esc_html_e('Save Notes', 'tradepress'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Rich Text Editor -->
        <div class="component-demo">
            <h4><?php esc_html_e('Rich Text Editor', 'tradepress'); ?></h4>
            <div class="working-notes-container">
                <div class="working-notes-header">
                    <h5 class="working-notes-title"><?php esc_html_e('Strategy Documentation', 'tradepress'); ?></h5>
                    <div class="working-notes-controls">
                        <button class="tp-button tp-button-small tp-button-secondary"><?php esc_html_e('History', 'tradepress'); ?></button>
                    </div>
                </div>
                <div class="working-notes-rich-editor">
                    <div class="working-notes-toolbar">
                        <button class="editor-tool-button" title="<?php esc_attr_e('Bold', 'tradepress'); ?>">
                            <span class="dashicons dashicons-editor-bold"></span>
                        </button>
                        <button class="editor-tool-button" title="<?php esc_attr_e('Italic', 'tradepress'); ?>">
                            <span class="dashicons dashicons-editor-italic"></span>
                        </button>
                        <button class="editor-tool-button" title="<?php esc_attr_e('Bulleted List', 'tradepress'); ?>">
                            <span class="dashicons dashicons-editor-ul"></span>
                        </button>
                        <button class="editor-tool-button" title="<?php esc_attr_e('Numbered List', 'tradepress'); ?>">
                            <span class="dashicons dashicons-editor-ol"></span>
                        </button>
                        <span class="editor-tool-separator"></span>
                        <button class="editor-tool-button" title="<?php esc_attr_e('Insert Link', 'tradepress'); ?>">
                            <span class="dashicons dashicons-admin-links"></span>
                        </button>
                        <button class="editor-tool-button" title="<?php esc_attr_e('Insert Image', 'tradepress'); ?>">
                            <span class="dashicons dashicons-format-image"></span>
                        </button>
                        <button class="editor-tool-button" title="<?php esc_attr_e('Insert Code', 'tradepress'); ?>">
                            <span class="dashicons dashicons-editor-code"></span>
                        </button>
                    </div>
                    <div class="working-notes-content" contenteditable="true">
                        <h3><?php esc_html_e('Moving Average Crossover Strategy', 'tradepress'); ?></h3>
                        <p><?php esc_html_e('This strategy uses the crossing of two moving averages to generate trading signals.', 'tradepress'); ?></p>
                        <ul>
                            <li><?php esc_html_e('Buy when the fast MA crosses above the slow MA', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Sell when the fast MA crosses below the slow MA', 'tradepress'); ?></li>
                        </ul>
                        <p><?php esc_html_e('Parameters to consider:', 'tradepress'); ?></p>
                        <ol>
                            <li><?php esc_html_e('Fast MA period (e.g., 20)', 'tradepress'); ?></li>
                            <li><?php esc_html_e('Slow MA period (e.g., 50)', 'tradepress'); ?></li>
                            <li><?php esc_html_e('MA type (EMA often works better than SMA)', 'tradepress'); ?></li>
                        </ol>
                    </div>
                </div>
                <div class="working-notes-footer">
                    <div class="working-notes-meta">
                        <?php esc_html_e('Last edited by: John Doe • 2 hours ago', 'tradepress'); ?>
                    </div>
                    <div class="working-notes-buttons">
                        <button class="tp-button tp-button-primary"><?php esc_html_e('Save Changes', 'tradepress'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Read-Only Notes Display -->
        <div class="component-demo">
            <h4><?php esc_html_e('Read-Only Notes Display', 'tradepress'); ?></h4>
            <div class="working-notes-display">
                <h3><?php esc_html_e('Trade Analysis - AAPL', 'tradepress'); ?></h3>
                <p><strong><?php esc_html_e('Entry Point:', 'tradepress'); ?></strong> <?php esc_html_e('$150.25 on 2023-05-15', 'tradepress'); ?></p>
                <p><strong><?php esc_html_e('Exit Point:', 'tradepress'); ?></strong> <?php esc_html_e('$165.75 on 2023-06-02', 'tradepress'); ?></p>
                <p><strong><?php esc_html_e('Profit/Loss:', 'tradepress'); ?></strong> <span style="color:green">+10.32%</span></p>
                
                <h4><?php esc_html_e('Notes', 'tradepress'); ?></h4>
                <p><?php esc_html_e('Entered after earnings beat and breakout from consolidation pattern. Market conditions were favorable with overall bullish sentiment in tech sector.', 'tradepress'); ?></p>
                
                <h4><?php esc_html_e('Lessons Learned', 'tradepress'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Could have entered earlier when first signs of accumulation appeared', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Should have taken partial profits at resistance level around $162', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Position sizing was appropriate for account size', 'tradepress'); ?></li>
                </ul>
                
                <blockquote>
                    <?php esc_html_e('Remember to always check the overall market context before entering trades in individual stocks.', 'tradepress'); ?>
                </blockquote>
            </div>
        </div>
        
        <!-- Notes with Tags -->
        <div class="component-demo">
            <h4><?php esc_html_e('Notes with Tags', 'tradepress'); ?></h4>
            <div class="working-notes-container">
                <div class="working-notes-header">
                    <h5 class="working-notes-title"><?php esc_html_e('Trading Journal Entry', 'tradepress'); ?></h5>
                </div>
                <div class="working-notes-editor">
                    <div class="working-notes-tags">
                        <span class="working-notes-tag-label"><?php esc_html_e('Tags:', 'tradepress'); ?></span>
                        <div class="working-notes-tag"><?php esc_html_e('Breakout', 'tradepress'); ?> <span class="working-notes-tag-remove">×</span></div>
                        <div class="working-notes-tag"><?php esc_html_e('Swing Trade', 'tradepress'); ?> <span class="working-notes-tag-remove">×</span></div>
                        <div class="working-notes-tag"><?php esc_html_e('Technology', 'tradepress'); ?> <span class="working-notes-tag-remove">×</span></div>
                        <input type="text" class="working-notes-tag-input" placeholder="<?php esc_attr_e('Add tag...', 'tradepress'); ?>" />
                    </div>
                    <textarea class="working-notes-textarea" placeholder="<?php esc_attr_e('Enter your journal entry...', 'tradepress'); ?>"></textarea>
                </div>
                <div class="working-notes-footer">
                    <div class="working-notes-buttons">
                        <button class="tp-button tp-button-secondary"><?php esc_html_e('Cancel', 'tradepress'); ?></button>
                        <button class="tp-button tp-button-primary"><?php esc_html_e('Save Entry', 'tradepress'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
    // Add interactive demo script
    $notes_script = "
        jQuery(document).ready(function($) {
            // Rich text editor buttons
            $('.editor-tool-button').on('click', function() {
                $(this).toggleClass('active');
            });
            
            // Tag removal
            $('.working-notes-tag-remove').on('click', function() {
                $(this).parent().fadeOut(200, function() {
                    $(this).remove();
                });
            });
            
            // Tag addition
            $('.working-notes-tag-input').on('keypress', function(e) {
                if (e.which === 13 && $(this).val().trim() !== '') {
                    e.preventDefault();
                    var tagText = $(this).val().trim();
                    var newTag = $('<div class=\"working-notes-tag\">' + tagText + ' <span class=\"working-notes-tag-remove\">×</span></div>');
                    
                    $(this).before(newTag);
                    $(this).val('');
                    
                    // Bind remove event to new tag
                    newTag.find('.working-notes-tag-remove').on('click', function() {
                        $(this).parent().fadeOut(200, function() {
                            $(this).remove();
                        });
                    });
                }
            });
            
            // Save button click simulation
            $('.working-notes-buttons .tradepress-button-primary').on('click', function() {
                alert('Notes saved successfully!');
            });
        });
    ";
    
    wp_add_inline_script('jquery', $notes_script);
    ?>
