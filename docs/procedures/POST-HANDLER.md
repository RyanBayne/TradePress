# TradePress POST Handler System

This document provides guidance on how to use the centralized POST handling system in TradePress to process form submissions securely and consistently.

## Overview

The TradePress POST Handler system provides a centralized way to process form submissions in the admin area. It handles security checks, validation, and dispatching to appropriate handlers, ensuring a consistent approach to form processing across the plugin.

## Benefits

- **Security**: Built-in nonce verification and capability checks
- **Organization**: Centralized handling of all form submissions
- **Consistency**: Standard approach to form processing and error handling
- **Redirection**: Automatic handling of redirects and notices after form submission

## How to Use

### 1. Creating a Form

Use the helper functions to create a secure form:

```php
<?php
// Start a form that will be handled by the 'save_settings' action
echo tradepress_form_open('save_settings');
?>

<!-- Form fields -->
<div class="form-field">
    <label for="field1">Setting 1</label>
    <input type="text" id="field1" name="field1" value="">
</div>

<div class="form-field">
    <label for="field2">Setting 2</label>
    <input type="text" id="field2" name="field2" value="">
</div>

<button type="submit" class="button button-primary">Save Settings</button>

</form>
```

### 2. Registering a Handler

Register a handler function for your form's action:

```php
/**
 * Register handler for the save_settings action
 */
function my_plugin_init() {
    tradepress_register_post_handler('save_settings', 'my_save_settings_handler', 'manage_options');
}
add_action('init', 'my_plugin_init');

/**
 * Handle the settings form submission
 */
function my_save_settings_handler($post_data) {
    // Sanitize inputs
    $field1 = isset($post_data['field1']) ? sanitize_text_field($post_data['field1']) : '';
    $field2 = isset($post_data['field2']) ? sanitize_text_field($post_data['field2']) : '';
    
    // Process the data
    update_option('my_setting_field1', $field1);
    update_option('my_setting_field2', $field2);
    
    // Return success with redirect
    return tradepress_post_success('Settings saved successfully!');
}
```

### 3. Handling Results

The system automatically handles redirects and displays notifications, but you can also access the result data if needed:

```php
$result = tradepress_get_post_result();
if ($result) {
    // Custom handling of the result data
    echo 'Processing complete with result: ' . esc_html(print_r($result, true));
}
```

## API Reference

### Form Creation

- `tradepress_form_open($action, $form_action = '', $attributes = array())` - Creates a form opening tag with the necessary attributes
- `tradepress_nonce_field($action, $referer = true, $echo = true)` - Adds the necessary hidden fields to a form

### Handler Registration

- `tradepress_register_post_handler($action, $callback, $capability = '')` - Registers a handler for a specific action

### Response Helpers

- `tradepress_post_success($message, $redirect_url = '')` - Creates a success response
- `tradepress_post_error($message, $redirect_url = '')` - Creates an error response
- `tradepress_get_post_result()` - Gets the result data after processing

## Security Considerations

The POST Handler system implements several security measures:

1. Nonce verification to prevent CSRF attacks
2. Capability checks to ensure proper permissions
3. Input sanitization (must be implemented in handlers)
4. Validation of actions and handlers

## Example Use Cases

### Settings Form

```php
<?php echo tradepress_form_open('save_api_settings'); ?>
    <h2>API Settings</h2>
    
    <table class="form-table">
        <tr>
            <th><label for="api_key">API Key</label></th>
            <td>
                <input type="text" id="api_key" name="api_key" 
                       value="<?php echo esc_attr(get_option('my_api_key')); ?>">
            </td>
        </tr>
        <tr>
            <th><label for="api_secret">API Secret</label></th>
            <td>
                <input type="password" id="api_secret" name="api_secret" 
                       value="<?php echo esc_attr(get_option('my_api_secret')); ?>">
            </td>
        </tr>
    </table>
    
    <p class="submit">
        <button type="submit" class="button button-primary">Save Settings</button>
    </p>
</form>
```

### Registration Handler

```php
tradepress_register_post_handler('save_api_settings', function($post_data) {
    // Sanitize inputs
    $api_key = isset($post_data['api_key']) ? sanitize_text_field($post_data['api_key']) : '';
    $api_secret = isset($post_data['api_secret']) ? sanitize_text_field($post_data['api_secret']) : '';
    
    // Validate inputs
    if (empty($api_key)) {
        return tradepress_post_error('API Key is required.');
    }
    
    // Save settings
    update_option('my_api_key', $api_key);
    update_option('my_api_secret', $api_secret);
    
    // Test API connection
    $test_result = my_test_api_connection($api_key, $api_secret);
    
    if (is_wp_error($test_result)) {
        return tradepress_post_error('Settings saved, but API connection failed: ' . $test_result->get_error_message());
    }
    
    return tradepress_post_success('API settings saved and connection verified successfully.');
}, 'manage_options');
```

## Best Practices

1. Always sanitize and validate all user inputs in your handler
2. Use capability checks to restrict access to sensitive operations
3. Return clear success or error messages for good user experience
4. Group related settings in a single form and handler
5. Use descriptive action names that reflect what the form does
