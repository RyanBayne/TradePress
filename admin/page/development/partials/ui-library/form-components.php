<?php
/**
 * UI Library Form Components Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.0
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Form Components', 'tradepress'); ?></h3>
    <p><?php esc_html_e('Standard form elements and input controls for consistent user input handling.', 'tradepress'); ?></p>

    <!-- Text Inputs -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Text Inputs', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-text-input"><?php esc_html_e('Text Input', 'tradepress'); ?></label>
                <input type="text" id="demo-text-input" class="tradepress-form-input" placeholder="<?php esc_attr_e('Enter text...', 'tradepress'); ?>">
            </div>
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-email-input"><?php esc_html_e('Email Input', 'tradepress'); ?></label>
                <input type="email" id="demo-email-input" class="tradepress-form-input" placeholder="<?php esc_attr_e('user@example.com', 'tradepress'); ?>">
            </div>
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-password-input"><?php esc_html_e('Password Input', 'tradepress'); ?></label>
                <input type="password" id="demo-password-input" class="tradepress-form-input" placeholder="<?php esc_attr_e('Enter password...', 'tradepress'); ?>">
            </div>
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-number-input"><?php esc_html_e('Number Input', 'tradepress'); ?></label>
                <input type="number" id="demo-number-input" class="tradepress-form-input tradepress-form-input-number" min="0" max="100" value="50">
            </div>
        </div>
    </div>

    <!-- Checkbox and Radio Groups -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Checkbox and Radio Groups', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase tradepress-form-options-layout">
            <div class="tradepress-form-row tradepress-form-options-column">
                <fieldset class="tradepress-form-fieldset">
                    <legend class="tradepress-form-legend"><?php esc_html_e('Checkbox Group', 'tradepress'); ?></legend>
                    <div class="tradepress-form-checkbox-group">
                        <label class="tradepress-form-checkbox-label">
                            <input type="checkbox" name="demo-checkbox[]" value="option1" checked class="tradepress-form-checkbox">
                            <span class="tradepress-form-checkbox-text"><?php esc_html_e('Option 1', 'tradepress'); ?></span>
                        </label>
                        <label class="tradepress-form-checkbox-label">
                            <input type="checkbox" name="demo-checkbox[]" value="option2" class="tradepress-form-checkbox">
                            <span class="tradepress-form-checkbox-text"><?php esc_html_e('Option 2', 'tradepress'); ?></span>
                        </label>
                        <label class="tradepress-form-checkbox-label">
                            <input type="checkbox" name="demo-checkbox[]" value="option3" checked class="tradepress-form-checkbox">
                            <span class="tradepress-form-checkbox-text"><?php esc_html_e('Option 3', 'tradepress'); ?></span>
                        </label>
                    </div>
                </fieldset>
            </div>
            <div class="tradepress-form-row tradepress-form-options-column">
                <fieldset class="tradepress-form-fieldset">
                    <legend class="tradepress-form-legend"><?php esc_html_e('Radio Group', 'tradepress'); ?></legend>
                    <div class="tradepress-form-radio-group">
                        <label class="tradepress-form-radio-label">
                            <input type="radio" name="demo-radio" value="small" checked class="tradepress-form-radio">
                            <span class="tradepress-form-radio-text"><?php esc_html_e('Small', 'tradepress'); ?></span>
                        </label>
                        <label class="tradepress-form-radio-label">
                            <input type="radio" name="demo-radio" value="medium" class="tradepress-form-radio">
                            <span class="tradepress-form-radio-text"><?php esc_html_e('Medium', 'tradepress'); ?></span>
                        </label>
                        <label class="tradepress-form-radio-label">
                            <input type="radio" name="demo-radio" value="large" class="tradepress-form-radio">
                            <span class="tradepress-form-radio-text"><?php esc_html_e('Large', 'tradepress'); ?></span>
                        </label>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <!-- Form Validation States -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Validation States', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-success-input"><?php esc_html_e('Success State', 'tradepress'); ?></label>
                <input type="text" id="demo-success-input" class="tradepress-form-input tradepress-form-input-success" value="<?php esc_attr_e('Valid input', 'tradepress'); ?>">
                <div class="tradepress-form-feedback tradepress-form-feedback-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('This field is valid', 'tradepress'); ?>
                </div>
            </div>
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-error-input"><?php esc_html_e('Error State', 'tradepress'); ?></label>
                <input type="text" id="demo-error-input" class="tradepress-form-input tradepress-form-input-error" value="<?php esc_attr_e('Invalid input', 'tradepress'); ?>">
                <div class="tradepress-form-feedback tradepress-form-feedback-error">
                    <span class="dashicons dashicons-dismiss"></span>
                    <?php esc_html_e('This field has an error', 'tradepress'); ?>
                </div>
            </div>
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-warning-input"><?php esc_html_e('Warning State', 'tradepress'); ?></label>
                <input type="text" id="demo-warning-input" class="tradepress-form-input tradepress-form-input-warning" value="<?php esc_attr_e('Warning input', 'tradepress'); ?>">
                <div class="tradepress-form-feedback tradepress-form-feedback-warning">
                    <span class="dashicons dashicons-warning"></span>
                    <?php esc_html_e('This field has a warning', 'tradepress'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ajax Validation Form -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Ajax Validation Form', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <form id="ajax-validation-form" class="tradepress-demo-form">
                <?php wp_nonce_field('tradepress_ui_ajax_validation', 'ajax_nonce'); ?>
                
                <div class="tradepress-form-row">
                    <label class="tradepress-form-label" for="username"><?php esc_html_e('Username *', 'tradepress'); ?></label>
                    <input type="text" id="username" name="username" class="tradepress-form-input" required>
                    <div id="username-feedback" class="tradepress-form-feedback" style="display:none;"></div>
                </div>
                <div class="tradepress-form-row">
                    <label class="tradepress-form-label" for="symbol-check"><?php esc_html_e('Stock Symbol *', 'tradepress'); ?></label>
                    <input type="text" id="symbol-check" name="symbol" class="tradepress-form-input" placeholder="AAPL" required>
                    <div id="symbol-feedback" class="tradepress-form-feedback" style="display:none;"></div>
                </div>
                <div class="tradepress-form-actions">
                    <button type="submit" class="tp-button tp-button-primary" id="ajax-submit-btn"><?php esc_html_e('Validate & Submit', 'tradepress'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Username validation
        $('#username').on('blur', function() {
            var username = $(this).val();
            if (username.length < 3) return;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tradepress_validate_username',
                    username: username,
                    nonce: $('#ajax_nonce').val()
                },
                success: function(response) {
                    var feedback = $('#username-feedback');
                    feedback.show();
                    
                    if (response.success) {
                        feedback.removeClass('tradepress-form-feedback-error')
                               .addClass('tradepress-form-feedback-success')
                               .html('<span class="dashicons dashicons-yes-alt"></span>' + response.data.message);
                        $('#username').removeClass('tradepress-form-input-error')
                                     .addClass('tradepress-form-input-success');
                    } else {
                        feedback.removeClass('tradepress-form-feedback-success')
                               .addClass('tradepress-form-feedback-error')
                               .html('<span class="dashicons dashicons-dismiss"></span>' + response.data.message);
                        $('#username').removeClass('tradepress-form-input-success')
                                     .addClass('tradepress-form-input-error');
                    }
                }
            });
        });
        
        // Symbol validation
        $('#symbol-check').on('blur', function() {
            var symbol = $(this).val().toUpperCase();
            if (symbol.length < 1) return;
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tradepress_validate_symbol',
                    symbol: symbol,
                    nonce: $('#ajax_nonce').val()
                },
                success: function(response) {
                    var feedback = $('#symbol-feedback');
                    feedback.show();
                    
                    if (response.success) {
                        feedback.removeClass('tradepress-form-feedback-error')
                               .addClass('tradepress-form-feedback-success')
                               .html('<span class="dashicons dashicons-yes-alt"></span>' + response.data.message);
                        $('#symbol-check').removeClass('tradepress-form-input-error')
                                         .addClass('tradepress-form-input-success');
                    } else {
                        feedback.removeClass('tradepress-form-feedback-success')
                               .addClass('tradepress-form-feedback-error')
                               .html('<span class="dashicons dashicons-dismiss"></span>' + response.data.message);
                        $('#symbol-check').removeClass('tradepress-form-input-success')
                                         .addClass('tradepress-form-input-error');
                    }
                }
            });
        });
        
        // Form submission
        $('#ajax-validation-form').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tradepress_submit_ajax_form',
                    username: $('#username').val(),
                    symbol: $('#symbol-check').val(),
                    nonce: $('#ajax_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Form submitted successfully: ' + response.data.message);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        });
    });
    </script>
</div>
