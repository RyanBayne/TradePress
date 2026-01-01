<?php
/**
 * Symbol Functions
 *
 * Functions for handling stock symbol parsing, linking and display.
 *
 * @package TradePress
 * @subpackage Includes
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse text and convert stock symbols to clickable links.
 * 
 * Recognizes formats:
 * - $SYMBOL (e.g., $AAPL)
 * - ^INDEX (e.g., ^DJI)
 * - SYMBOL: (e.g., MSFT:)
 * - [SYMBOL] (e.g., [GOOGL])
 * 
 * @param string $text The text to parse for symbols
 * @param array $options Optional. Array of options for link generation
 *     @type string $class CSS class to add to links (default: 'tradepress-symbol-link')
 *     @type string $target Link target (default: '_self')
 *     @type string $page Target page ('analysis', 'data', 'chart', etc.) (default: 'analysis')
 * @return string Text with symbols converted to links
 */
function tradepress_parse_symbol_links($text, $options = array()) {
    // Default options
    $defaults = array(
        'class' => 'tradepress-symbol-link',
        'target' => '_self',
        'page' => 'analysis'
    );
    $options = wp_parse_args($options, $defaults);
    
    // Patterns to match different symbol formats
    $patterns = array(
        '/\$([A-Z]{1,6})/', // $AAPL format
        '/\^([A-Z]{1,6})/', // ^DJI format
        '/\b([A-Z]{1,6}):/', // MSFT: format
        '/\[([A-Z]{1,6})\]/' // [GOOGL] format
    );
    
    // Build the URL template once
    $url_template = admin_url('admin.php?page=tradepress-' . esc_attr($options['page']) . '&symbol=%s');
    
    // Process each pattern
    foreach ($patterns as $pattern) {
        $text = preg_replace_callback($pattern, function($matches) use ($url_template, $options) {
            $symbol = $matches[1];
            $url = sprintf($url_template, urlencode($symbol));
            
            // Build link with proper escaping
            return sprintf(
                '<a href="%s" class="%s" target="%s" title="%s">%s</a>',
                esc_url($url),
                esc_attr($options['class']),
                esc_attr($options['target']),
                sprintf(esc_attr__('View %s analysis', 'tradepress'), $symbol),
                esc_html($matches[0])
            );
        }, $text);
    }
    
    return $text;
}

/**
 * Get the URL for a symbol's page.
 * 
 * @param string $symbol Stock symbol
 * @param string $page Page type ('analysis', 'data', 'chart', etc.)
 * @return string URL for the symbol page
 */
function tradepress_get_symbol_url($symbol, $page = 'analysis') {
    return esc_url(
        admin_url(
            sprintf(
                'admin.php?page=tradepress-%s&symbol=%s',
                sanitize_key($page),
                urlencode($symbol)
            )
        )
    );
}

/**
 * Generate a symbol link HTML.
 * 
 * @param string $symbol Stock symbol
 * @param array $options Optional. Link generation options
 * @return string HTML link element
 */
function tradepress_get_symbol_link($symbol, $options = array()) {
    $defaults = array(
        'class' => 'tradepress-symbol-link',
        'target' => '_self',
        'page' => 'analysis',
        'text' => $symbol, // Allow custom text instead of symbol
        'prefix' => '$'    // Symbol prefix ($, ^, etc.)
    );
    $options = wp_parse_args($options, $defaults);
    
    $url = tradepress_get_symbol_url($symbol, $options['page']);
    $display_text = esc_html($options['prefix'] . $options['text']);
    
    return sprintf(
        '<a href="%s" class="%s" target="%s" title="%s">%s</a>',
        $url,
        esc_attr($options['class']),
        esc_attr($options['target']),
        sprintf(esc_attr__('View %s analysis', 'tradepress'), $symbol),
        $display_text
    );
}

/**
 * Validate if a given string is a valid stock symbol
 * 
 * @param string $symbol Symbol to validate
 * @param array $options Validation options
 * @return bool True if valid, false otherwise
 */
function tradepress_is_valid_symbol($symbol, $options = array()) {
    $defaults = array(
        'min_length' => 1,
        'max_length' => 6,
        'allow_numbers' => false,
        'exchange_suffix' => true
    );
    $options = wp_parse_args($options, $defaults);
    
    // Remove any exchange suffix for base symbol validation
    if ($options['exchange_suffix']) {
        $symbol = explode('.', $symbol)[0];
    }
    
    // Basic length check
    $length = strlen($symbol);
    if ($length < $options['min_length'] || $length > $options['max_length']) {
        return false;
    }
    
    // Character validation
    if ($options['allow_numbers']) {
        return (bool) preg_match('/^[A-Z0-9]+$/', $symbol);
    }
    
    return (bool) preg_match('/^[A-Z]+$/', $symbol);
}

/**
 * Handle exchange-specific symbols
 * 
 * @param string $symbol Symbol possibly with exchange
 * @param array $options Display options
 * @return string Formatted symbol
 */
function tradepress_parse_symbol_with_exchange($symbol, $options = array()) {
    $defaults = array(
        'show_exchange' => true,
        'exchange_format' => 'suffix', // 'suffix' or 'parentheses'
        'exchanges' => array(
            'US' => 'NYSE/NASDAQ',
            'L' => 'London',
            'TO' => 'Toronto',
            'HK' => 'Hong Kong'
        )
    );
    $options = wp_parse_args($options, $defaults);
    
    // Split symbol and exchange
    $parts = explode('.', $symbol);
    if (count($parts) !== 2) {
        return $symbol; // No exchange suffix
    }
    
    list($base_symbol, $exchange) = $parts;
    
    if (!$options['show_exchange']) {
        return $base_symbol;
    }
    
    $exchange_name = isset($options['exchanges'][$exchange]) ? $options['exchanges'][$exchange] : $exchange;
    
    if ($options['exchange_format'] === 'parentheses') {
        return sprintf('%s (%s)', $base_symbol, $exchange_name);
    }
    
    return sprintf('%s.%s', $base_symbol, $exchange);
}

/**
 * Standardize symbol format based on type
 * 
 * @param string $symbol Symbol to format
 * @param string $type Symbol type (stock, crypto, forex, index)
 * @param array $options Formatting options
 * @return string Formatted symbol
 */
function tradepress_format_symbol($symbol, $type = 'stock', $options = array()) {
    $defaults = array(
        'prefixes' => array(
            'stock' => '$',
            'crypto' => '#',
            'forex' => '@',
            'index' => '^'
        ),
        'force_uppercase' => true
    );
    $options = wp_parse_args($options, $defaults);
    
    // Clean the symbol
    $symbol = trim($symbol);
    if ($options['force_uppercase']) {
        $symbol = strtoupper($symbol);
    }
    
    // Add prefix if not already present
    $prefix = isset($options['prefixes'][$type]) ? $options['prefixes'][$type] : '';
    if ($prefix && strpos($symbol, $prefix) !== 0) {
        $symbol = $prefix . $symbol;
    }
    
    return $symbol;
}

/**
 * Generate enhanced symbol links with rich data
 * 
 * @param string $symbol Stock symbol
 * @param array $options Display options
 * @return string HTML for enhanced symbol link
 */
function tradepress_get_enhanced_symbol_link($symbol, $options = array()) {
    $defaults = array(
        'show_price' => true,
        'show_change' => true,
        'show_chart' => true,
        'cache_timeout' => 300, // 5 minutes
        'class' => 'tradepress-symbol-link enhanced'
    );
    $options = wp_parse_args($options, $defaults);
    
    // Try to get cached data first
    $cache_key = 'tradepress_symbol_data_' . $symbol;
    $data = get_transient($cache_key);
    
    if (false === $data) {
        // In a real implementation, this would fetch from your data service
        // For now, return basic link with symbol only
        return tradepress_get_symbol_link($symbol, $options);
    }
    
    $price_class = floatval($data['change']) >= 0 ? 'positive' : 'negative';
    $change_arrow = floatval($data['change']) >= 0 ? '↑' : '↓';
    
    $html = sprintf(
        '<a href="%s" class="%s" data-symbol="%s">
            <span class="symbol">%s</span>
            %s
            %s
            %s
        </a>',
        tradepress_get_symbol_url($symbol),
        esc_attr($options['class']),
        esc_attr($symbol),
        esc_html($symbol),
        $options['show_price'] ? sprintf('<span class="price">$%.2f</span>', $data['price']) : '',
        $options['show_change'] ? sprintf('<span class="change %s">%s %.2f%%</span>', $price_class, $change_arrow, $data['change']) : '',
        $options['show_chart'] ? '<span class="mini-chart">[Chart Placeholder]</span>' : ''
    );
    
    return $html;
}

/**
 * Handle groups of related symbols
 * 
 * @param array $symbols Array of related symbols
 * @param array $options Display options
 * @return string HTML for symbol group
 */
function tradepress_parse_symbol_group($symbols, $options = array()) {
    $defaults = array(
        'type' => 'inline', // inline, list, table
        'show_separator' => true,
        'separator' => ', ',
        'class' => 'tradepress-symbol-group'
    );
    $options = wp_parse_args($options, $defaults);
    
    if (!is_array($symbols)) {
        return '';
    }
    
    $formatted_symbols = array();
    foreach ($symbols as $symbol) {
        $formatted_symbols[] = tradepress_get_symbol_link($symbol);
    }
    
    if ($options['type'] === 'list') {
        return sprintf(
            '<ul class="%s">%s</ul>',
            esc_attr($options['class']),
            '<li>' . implode('</li><li>', $formatted_symbols) . '</li>'
        );
    }
    
    if ($options['type'] === 'table') {
        // Table implementation would go here
        return '[Table format not implemented]';
    }
    
    // Default inline format
    return sprintf(
        '<span class="%s">%s</span>',
        esc_attr($options['class']),
        implode($options['separator'], $formatted_symbols)
    );
}

/**
 * Smart detection of symbol context
 * 
 * @param string $text Text to parse for symbol context
 * @param array $options Parsing options
 * @return array Parsed context data
 */
function tradepress_parse_symbol_context($text, $options = array()) {
    $defaults = array(
        'detect_price' => true,
        'detect_signals' => true,
        'detect_position' => true
    );
    $options = wp_parse_args($options, $defaults);
    
    $context = array(
        'symbols' => array(),
        'prices' => array(),
        'signals' => array(),
        'positions' => array()
    );
    
    // Price detection
    if ($options['detect_price']) {
        preg_match_all('/([A-Z]{1,6})\s*[@at]*\s*\$?\s*(\d+\.?\d*)/', $text, $price_matches);
        for ($i = 0; $i < count($price_matches[0]); $i++) {
            $context['prices'][] = array(
                'symbol' => $price_matches[1][$i],
                'price' => floatval($price_matches[2][$i])
            );
        }
    }
    
    // Signal detection
    if ($options['detect_signals']) {
        preg_match_all('/(buy|sell)\s+([A-Z]{1,6})/i', $text, $signal_matches);
        for ($i = 0; $i < count($signal_matches[0]); $i++) {
            $context['signals'][] = array(
                'action' => strtolower($signal_matches[1][$i]),
                'symbol' => $signal_matches[2][$i]
            );
        }
    }
    
    // Position detection
    if ($options['detect_position']) {
        preg_match_all('/(long|short)\s+([A-Z]{1,6})\s*(\d+)?\s*(shares|contracts)?/i', $text, $position_matches);
        for ($i = 0; $i < count($position_matches[0]); $i++) {
            $context['positions'][] = array(
                'type' => strtolower($position_matches[1][$i]),
                'symbol' => $position_matches[2][$i],
                'quantity' => $position_matches[3][$i] ? intval($position_matches[3][$i]) : null,
                'unit' => $position_matches[4][$i] ? strtolower($position_matches[4][$i]) : 'shares'
            );
        }
    }
    
    return $context;
}

/**
 * Symbol search and suggestion helper
 * 
 * @param string $search Search term
 * @param array $options Search options
 * @return array Matching symbols
 */
function tradepress_find_symbol($search, $options = array()) {
    $defaults = array(
        'fuzzy_match' => true,
        'include_company' => true,
        'max_results' => 10,
        'min_length' => 2
    );
    $options = wp_parse_args($options, $defaults);
    
    if (strlen($search) < $options['min_length']) {
        return array();
    }
    
    // In a real implementation, this would search your symbol database
    // For now, return empty array
    return array();
}

/**
 * Add custom actions to symbol links
 * 
 * @param string $symbol Stock symbol
 * @param array $actions Custom actions
 * @return string Modified symbol link HTML
 */
function tradepress_add_symbol_actions($symbol, $actions = array()) {
    $default_actions = array(
        'add_to_watchlist' => array(
            'label' => __('Add to Watchlist', 'tradepress'),
            'url' => '#',
            'class' => 'watchlist-action'
        ),
        'quick_analysis' => array(
            'label' => __('Quick Analysis', 'tradepress'),
            'url' => '#',
            'class' => 'analysis-action'
        )
    );
    
    $actions = wp_parse_args($actions, $default_actions);
    
    $menu_items = array();
    foreach ($actions as $key => $action) {
        $menu_items[] = sprintf(
            '<li><a href="%s" class="%s" data-symbol="%s">%s</a></li>',
            esc_url($action['url']),
            esc_attr($action['class']),
            esc_attr($symbol),
            esc_html($action['label'])
        );
    }
    
    return sprintf(
        '<div class="symbol-with-actions">
            %s
            <ul class="symbol-actions">%s</ul>
        </div>',
        tradepress_get_symbol_link($symbol),
        implode('', $menu_items)
    );
}