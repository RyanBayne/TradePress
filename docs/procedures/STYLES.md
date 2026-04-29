# TradePress Procedure - Moving In-line Styles
Contains rules for creating styles and the procedure to move them from .php files to .css files.

## Overview
Follow this procedure when moving in-line styles from .php files to .css files.

## Overview
This procedure ensures consistent WordPress best practices when refactoring inline styles and scripts to external files in TradePress. It follows the plugin's asset management system and maintains code organization.

## Critical Rules (From AI.md)
❌ **DO NOT** add CSS styles to PHP files  
❌ **DO NOT** create new CSS/JS files without explicit permission  
❌ **DO NOT** add `<style>` or `<script>` tags in .php files  
✅ **USE** existing assets in `wp-content/plugins/TradePress/assets`  
✅ **CHECK** existing components first  
✅ **ENQUEUE** assets properly using WordPress hooks  

## Step 1: Identify Inline Styles

### 1.1 Locate Inline Styles
Search for the following patterns in .php files:
```php
// Look for these patterns:
<style>...</style>
echo '<style>...</style>';
wp_add_inline_style(...)
style="..."
```

### 1.2 Document Current Styles
Create a list of:
- File containing inline styles
- Purpose of the styles
- Page/view where styles are used
- Dependencies (if any)

## Step 2: Determine Target CSS File

### 2.1 Check Existing CSS Files
First, check if appropriate CSS file already exists:
```
assets/css/pages/[page-name].css
assets/css/components/[component-name].css
assets/css/layouts/[layout-name].css
```

### 2.2 CSS File Selection Priority
1. **Page-specific styles** → `assets/css/pages/[page].css`
2. **Component styles** → `assets/css/components/[component].css`
3. **Layout styles** → `assets/css/layouts/[layout].css`
4. **Base styles** → `assets/css/base/[type].css`

### 2.3 File Naming Convention
- Use kebab-case: `direct-api-test.css`
- Be specific: `development-current-task.css`
- Include purpose: `dashboard-widgets.css`

## Step 3: Move Styles to CSS File

### 3.1 Extract and Clean Styles
```css
/* Add header comment */
/**
 * [Page/Component Name] Styles
 * 
 * @package TradePress/CSS/[Category]
 * @since 1.0.0
 */

/* Original inline styles (cleaned) */
.your-styles-here {
    /* ... */
}
```

### 3.2 Organize CSS Structure
```css
/* Page/Component specific styles */
.main-container {
    /* ... */
}

/* Sub-components */
.sub-component {
    /* ... */
}

/* Responsive styles */
@media (max-width: 768px) {
    /* ... */
}
```

## Step 4: Register CSS File

### 4.1 For UI Library Components
Add to `assets/manage-assets.php` (style-assets.php):
```php
'your-component' => array(
    'path' => 'css/components/your-component.css',
    'dependencies' => array(),
    'purpose' => 'Styles for your component',
    'pages' => array('ui-library', 'development')
),
```

### 4.2 For Admin Pages
Add to `admin/assets-loader-original.php`:
```php
// Register the CSS file
wp_register_style(
    'tradepress-your-page',
    TRADEPRESS_PLUGIN_URL . 'assets/css/pages/your-page.css',
    array(),
    TRADEPRESS_VERSION
);

// Enqueue conditionally
if (isset($_GET['page']) && $_GET['page'] === 'your_page') {
    wp_enqueue_style('tradepress-your-page');
}
```

## Step 5: Update PHP File

### 5.1 Remove Inline Styles
```php
// Remove these patterns:
// ❌ <style>...</style>
// ❌ echo '<style>...</style>';
// ❌ wp_add_inline_style(...)
// ❌ style="..." attributes where possible
```

### 5.2 Ensure CSS Classes
```php
// Ensure HTML has proper CSS classes
<div class="your-component">
    <div class="sub-element">
        <!-- Content -->
    </div>
</div>
```

### 5.3 Add Asset Loading (if needed)
```php
// Only if not handled by admin/assets-loader-original.php
wp_enqueue_style(
    'tradepress-your-component',
    TRADEPRESS_PLUGIN_URL . 'assets/css/components/your-component.css',
    array(),
    TRADEPRESS_VERSION
);
```

## Step 6: Test Implementation

### 6.1 Verify CSS Loading
1. Check browser dev tools for CSS file loading
2. Verify styles are applied correctly
3. Test responsive behavior
4. Check for conflicts with other styles

### 6.2 Validate Asset Registration
```php
// Check if style is registered
if (wp_style_is('tradepress-your-component', 'registered')) {
    // Style is registered
}

// Check if style is enqueued
if (wp_style_is('tradepress-your-component', 'enqueued')) {
    // Style is enqueued and will be loaded
}
```

## Step 7: Documentation Updates

### 7.1 Update Asset Documentation
Add entry to asset management documentation:
```markdown
- **your-component.css**: Styles for [component description]
  - Location: `assets/css/components/your-component.css`
  - Dependencies: None
  - Used on: [page names]
```

### 7.2 Update File Headers
```php
/**
 * [File Description]
 * 
 * Related CSS: assets/css/[category]/[file].css
 * Related JS: assets/js/[file].js (if applicable)
 * 
 * @package TradePress/[Category]
 * @since 1.0.0
 */
```

## Common Patterns and Examples

### Example 1: Page-Specific Styles
```php
// Before (in development/current-task.php):
echo '<style>
.current-task-container { margin: 20px; }
.task-status { color: green; }
</style>';

// After:
// 1. Move to assets/css/pages/development-current-task.css
// 2. Register in admin/assets-loader-original.php
// 3. Remove inline style from PHP
```

### Example 2: Component Styles
```php
// Before (in various files):
echo '<style>
.api-status-box { border: 1px solid #ddd; }
.status-active { color: green; }
</style>';

// After:
// 1. Move to assets/css/components/api-status.css
// 2. Register in manage-assets.php
// 3. Use across multiple pages
```

## Asset Management Integration

### For UI Library Components
Use the centralized asset management system:
```php
// In manage-assets.php
$this->assets['css']['components']['your-component'] = array(
    'path' => 'css/components/your-component.css',
    'dependencies' => array(),
    'purpose' => 'Component description',
    'pages' => array('ui-library', 'development')
);
```

### For Admin Pages
Use the admin assets system:
```php
// In admin/assets-loader-original.php
wp_register_style('handle', $url, $deps, $version);
wp_enqueue_style('handle'); // When appropriate
```

## Quality Checklist

- [ ] Inline styles completely removed from PHP files
- [ ] CSS file properly organized with comments
- [ ] Styles registered in appropriate asset management system
- [ ] Conditional loading implemented correctly
- [ ] No CSS conflicts with existing styles
- [ ] Responsive behavior maintained
- [ ] Browser compatibility verified
- [ ] Documentation updated

## Troubleshooting

### CSS Not Loading
1. Check if style is registered: `wp_style_is('handle', 'registered')`
2. Check if style is enqueued: `wp_style_is('handle', 'enqueued')`
3. Verify file path is correct
4. Check for PHP errors in asset registration

### Styles Not Applied
1. Check CSS selector specificity
2. Verify HTML classes match CSS classes
3. Check for conflicting styles
4. Inspect element in browser dev tools

### Performance Issues
1. Minimize number of CSS files per page
2. Use existing CSS files when possible
3. Implement proper conditional loading
4. Consider CSS minification for production

## Best Practices Summary

1. **Always use external CSS files** - Never add inline styles
2. **Follow existing patterns** - Use the established asset management system
3. **Organize logically** - Group related styles together
4. **Document thoroughly** - Include proper comments and documentation
5. **Test extensively** - Verify functionality across different scenarios
6. **Maintain consistency** - Follow existing naming conventions and structure
