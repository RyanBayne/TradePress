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

    <!-- Textarea -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Textarea', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-textarea"><?php esc_html_e('Description', 'tradepress'); ?></label>
                <textarea id="demo-textarea" class="tradepress-form-textarea" rows="4" placeholder="<?php esc_attr_e('Enter detailed description...', 'tradepress'); ?>"></textarea>
            </div>
        </div>
    </div>

    <!-- Select Dropdowns -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Select Dropdowns', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-select"><?php esc_html_e('Single Select', 'tradepress'); ?></label>
                <select id="demo-select" class="tradepress-form-select">
                    <option value=""><?php esc_html_e('Choose option...', 'tradepress'); ?></option>
                    <option value="option1"><?php esc_html_e('Option 1', 'tradepress'); ?></option>
                    <option value="option2"><?php esc_html_e('Option 2', 'tradepress'); ?></option>
                    <option value="option3"><?php esc_html_e('Option 3', 'tradepress'); ?></option>
                </select>
            </div>
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-multiselect"><?php esc_html_e('Multi Select', 'tradepress'); ?></label>
                <select id="demo-multiselect" class="tradepress-form-select tradepress-form-select-multiple" multiple size="4">
                    <option value="apple"><?php esc_html_e('Apple', 'tradepress'); ?></option>
                    <option value="banana" selected><?php esc_html_e('Banana', 'tradepress'); ?></option>
                    <option value="cherry"><?php esc_html_e('Cherry', 'tradepress'); ?></option>
                    <option value="date" selected><?php esc_html_e('Date', 'tradepress'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Checkbox and Radio Groups -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Checkbox and Radio Groups', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <div class="tradepress-form-row">
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
            <div class="tradepress-form-row">
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

    <!-- Search Input -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Search Input', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-search-input"><?php esc_html_e('Search', 'tradepress'); ?></label>
                <div class="tradepress-search-wrapper">
                    <input type="search" id="demo-search-input" class="tradepress-form-input tradepress-search-input" placeholder="<?php esc_attr_e('Search...', 'tradepress'); ?>">
                    <span class="tradepress-search-icon dashicons dashicons-search"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- File Upload -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('File Upload', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <div class="tradepress-form-row">
                <label class="tradepress-form-label" for="demo-file-input"><?php esc_html_e('File Upload', 'tradepress'); ?></label>
                <input type="file" id="demo-file-input" class="tradepress-form-file">
                <p class="tradepress-form-description"><?php esc_html_e('Choose a file to upload (max 2MB)', 'tradepress'); ?></p>
            </div>
        </div>
    </div>

    <!-- Form Layouts -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Form Layouts', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <!-- Horizontal Layout -->
            <div class="tradepress-form-layout tradepress-form-layout-horizontal">
                <h5><?php esc_html_e('Horizontal Layout', 'tradepress'); ?></h5>
                <div class="tradepress-form-row tradepress-form-row-horizontal">
                    <label class="tradepress-form-label tradepress-form-label-horizontal" for="demo-horizontal-1"><?php esc_html_e('First Name:', 'tradepress'); ?></label>
                    <input type="text" id="demo-horizontal-1" class="tradepress-form-input">
                </div>
                <div class="tradepress-form-row tradepress-form-row-horizontal">
                    <label class="tradepress-form-label tradepress-form-label-horizontal" for="demo-horizontal-2"><?php esc_html_e('Last Name:', 'tradepress'); ?></label>
                    <input type="text" id="demo-horizontal-2" class="tradepress-form-input">
                </div>
            </div>

            <!-- Inline Layout -->
            <div class="tradepress-form-layout tradepress-form-layout-inline">
                <h5><?php esc_html_e('Inline Layout', 'tradepress'); ?></h5>
                <div class="tradepress-form-row tradepress-form-row-inline">
                    <label class="tradepress-form-label tradepress-form-label-inline" for="demo-inline-1"><?php esc_html_e('City:', 'tradepress'); ?></label>
                    <input type="text" id="demo-inline-1" class="tradepress-form-input tradepress-form-input-inline">
                    <label class="tradepress-form-label tradepress-form-label-inline" for="demo-inline-2"><?php esc_html_e('State:', 'tradepress'); ?></label>
                    <select id="demo-inline-2" class="tradepress-form-select tradepress-form-select-inline">
                        <option value=""><?php esc_html_e('Select...', 'tradepress'); ?></option>
                        <option value="ca"><?php esc_html_e('California', 'tradepress'); ?></option>
                        <option value="ny"><?php esc_html_e('New York', 'tradepress'); ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Contact Form -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Simple Contact Form', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <form method="post" action="" class="tradepress-demo-form">
                <?php wp_nonce_field('tradepress_ui_contact_form'); ?>
                <input type="hidden" name="tradepress_form_action" value="contact_form">
                
                <div class="tradepress-form-row">
                    <label class="tradepress-form-label" for="contact-name"><?php esc_html_e('Name *', 'tradepress'); ?></label>
                    <input type="text" id="contact-name" name="contact_name" class="tradepress-form-input" required>
                </div>
                <div class="tradepress-form-row">
                    <label class="tradepress-form-label" for="contact-email"><?php esc_html_e('Email *', 'tradepress'); ?></label>
                    <input type="email" id="contact-email" name="contact_email" class="tradepress-form-input" required>
                </div>
                <div class="tradepress-form-row">
                    <label class="tradepress-form-label" for="contact-message"><?php esc_html_e('Message *', 'tradepress'); ?></label>
                    <textarea id="contact-message" name="contact_message" class="tradepress-form-textarea" rows="4" required></textarea>
                </div>
                <div class="tradepress-form-actions">
                    <button type="submit" class="tp-button tp-button-primary"><?php esc_html_e('Send Message', 'tradepress'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Trading Settings Form -->
    <div class="tradepress-component-group">
        <h4><?php esc_html_e('Trading Settings Form', 'tradepress'); ?></h4>
        <div class="tradepress-form-showcase">
            <form method="post" action="" class="tradepress-demo-form">
                <?php wp_nonce_field('tradepress_ui_trading_settings'); ?>
                <input type="hidden" name="tradepress_form_action" value="trading_settings">
                
                <div class="tradepress-form-row">
                    <label class="tradepress-form-label" for="risk-level"><?php esc_html_e('Risk Level', 'tradepress'); ?></label>
                    <select id="risk-level" name="risk_level" class="tradepress-form-select">
                        <option value="low"><?php esc_html_e('Low Risk', 'tradepress'); ?></option>
                        <option value="medium" selected><?php esc_html_e('Medium Risk', 'tradepress'); ?></option>
                        <option value="high"><?php esc_html_e('High Risk', 'tradepress'); ?></option>
                    </select>
                </div>
                <div class="tradepress-form-row">
                    <label class="tradepress-form-label" for="max-investment"><?php esc_html_e('Max Investment ($)', 'tradepress'); ?></label>
                    <input type="number" id="max-investment" name="max_investment" class="tradepress-form-input" min="100" max="100000" value="5000">
                </div>
                <div class="tradepress-form-row">
                    <fieldset class="tradepress-form-fieldset">
                        <legend class="tradepress-form-legend"><?php esc_html_e('Trading Preferences', 'tradepress'); ?></legend>
                        <div class="tradepress-form-checkbox-group">
                            <label class="tradepress-form-checkbox-label">
                                <input type="checkbox" name="preferences[]" value="day_trading" class="tradepress-form-checkbox">
                                <span class="tradepress-form-checkbox-text"><?php esc_html_e('Day Trading', 'tradepress'); ?></span>
                            </label>
                            <label class="tradepress-form-checkbox-label">
                                <input type="checkbox" name="preferences[]" value="swing_trading" class="tradepress-form-checkbox" checked>
                                <span class="tradepress-form-checkbox-text"><?php esc_html_e('Swing Trading', 'tradepress'); ?></span>
                            </label>
                            <label class="tradepress-form-checkbox-label">
                                <input type="checkbox" name="preferences[]" value="long_term" class="tradepress-form-checkbox">
                                <span class="tradepress-form-checkbox-text"><?php esc_html_e('Long-term Investment', 'tradepress'); ?></span>
                            </label>
                        </div>
                    </fieldset>
                </div>
                <div class="tradepress-form-actions">
                    <button type="submit" class="tp-button tp-button-primary"><?php esc_html_e('Save Settings', 'tradepress'); ?></button>
                    <button type="reset" class="tp-button tp-button-secondary"><?php esc_html_e('Reset', 'tradepress'); ?></button>
                </div>
            </form>
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
