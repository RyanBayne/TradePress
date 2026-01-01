<?php
/**
 * TradePress Script Assets Registry
 *
 * Contains the registry for all JavaScript assets.
 *
 * @package TradePress/Assets
 * @since 1.0.0
 * @created 2024-12-21
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

return array(
    // Admin Scripts
    'admin' => array(
        'admin-common' => array(
            'path' => 'js/admin-common.js',
            'purpose' => 'Common admin functionality and utilities',
            'pages' => array('all'),
            'dependencies' => array('jquery')
        ),
        'admin-trading-page' => array(
            'path' => 'js/admin-trading-page.js',
            'purpose' => 'Trading page admin functionality',
            'pages' => array('trading'),
            'dependencies' => array('jquery', 'admin-common')
        ),
        'admin-technical-analysis' => array(
            'path' => 'js/admin-technical-analysis.js',
            'purpose' => 'Technical analysis tools and charts',
            'pages' => array('research'),
            'dependencies' => array('jquery')
        ),
        'admin-sandbox-experiments' => array(
            'path' => 'js/admin-sandbox-experiments.js',
            'purpose' => 'Sandbox experimental features',
            'pages' => array('sandbox'),
            'dependencies' => array('jquery')
        ),
        'admin-sandbox-database' => array(
            'path' => 'js/admin-sandbox-database.js',
            'purpose' => 'Database sandbox tools',
            'pages' => array('sandbox'),
            'dependencies' => array('jquery')
        ),
        'admin-fundamental-analysis' => array(
            'path' => 'js/admin-fundamental-analysis.js',
            'purpose' => 'Fundamental analysis tools',
            'pages' => array('research'),
            'dependencies' => array('jquery')
        ),
        'admin-features' => array(
            'path' => 'js/admin-features.js',
            'purpose' => 'Feature management interface',
            'pages' => array('features'),
            'dependencies' => array('jquery')
        ),
        'admin-database' => array(
            'path' => 'js/admin-database.js',
            'purpose' => 'Database management tools',
            'pages' => array('database'),
            'dependencies' => array('jquery')
        ),
        'admin-bot' => array(
            'path' => 'js/admin-bot.js',
            'purpose' => 'Bot management and controls',
            'pages' => array('automation'),
            'dependencies' => array('jquery')
        ),
        'admin-automation' => array(
            'path' => 'assets/js/admin-automation.js',
            'purpose' => 'Automation dashboard controls',
            'pages' => array('automation'),
            'dependencies' => array('jquery')
        ),
        'admin-api-tab' => array(
            'path' => 'js/admin-api-tab.js',
            'purpose' => 'API settings and testing interface',
            'pages' => array('api'),
            'dependencies' => array('jquery')
        ),
        'admin-trading-platforms' => array(
            'path' => 'js/admin-trading-platforms.js',
            'purpose' => 'Trading platforms page functionality and AJAX calls',
            'pages' => array('trading-platforms'),
            'dependencies' => array('jquery')
        )
    ),
    
    // Feature Scripts
    'features' => array(
        'alert-decoder' => array(
            'path' => 'js/alert-decoder.js',
            'purpose' => 'Alert decoding and parsing functionality',
            'pages' => array('alert-decoder'),
            'dependencies' => array('jquery')
        ),
        'ajax-diagnostics' => array(
            'path' => 'js/ajax-diagnostics.js',
            'purpose' => 'AJAX diagnostic tools',
            'pages' => array('sandbox'),
            'dependencies' => array('jquery')
        ),
        'ajax-test' => array(
            'path' => 'js/ajax-test.js',
            'purpose' => 'AJAX testing functionality',
            'pages' => array('sandbox'),
            'dependencies' => array('jquery')
        ),
        'api-tab' => array(
            'path' => 'js/api-tab.js',
            'purpose' => 'API tab navigation and controls',
            'pages' => array('api'),
            'dependencies' => array('jquery')
        ),
        'directive-test' => array(
            'path' => 'js/directive-test.js',
            'purpose' => 'Directive testing and scoring',
            'pages' => array('sandbox'),
            'dependencies' => array('jquery')
        ),
        'education-pattern-quiz' => array(
            'path' => 'js/education-pattern-quiz.js',
            'purpose' => 'Educational pattern recognition quiz',
            'pages' => array('education'),
            'dependencies' => array('jquery')
        ),
        'features' => array(
            'path' => 'js/features.js',
            'purpose' => 'Feature toggle and management',
            'pages' => array('features'),
            'dependencies' => array('jquery')
        ),
        'features-management' => array(
            'path' => 'js/features-management.js',
            'purpose' => 'Advanced feature management',
            'pages' => array('features'),
            'dependencies' => array('jquery', 'features')
        ),
        'shortcodes' => array(
            'path' => 'js/shortcodes.js',
            'purpose' => 'Shortcode management and preview',
            'pages' => array('shortcodes'),
            'dependencies' => array('jquery')
        ),
        'stock-vip' => array(
            'path' => 'js/stock-vip.js',
            'purpose' => 'Stock VIP alerts and premium features',
            'pages' => array('stock-vip'),
            'dependencies' => array('jquery')
        ),
        'ui-library' => array(
            'path' => 'js/ui-library.js',
            'purpose' => 'UI Library interactive functionality',
            'pages' => array('development'),
            'dependencies' => array('jquery')
        ),
        'ui-library-animations' => array(
            'path' => 'js/components/ui-library-animations.js',
            'purpose' => 'Animation interactions for UI Library showcase',
            'pages' => array('development'),
            'dependencies' => array('jquery')
        )
    ),
    
    // Task Management Scripts
    'tasks' => array(
        'tradepress-current-task' => array(
            'path' => 'js/tradepress-current-task.js',
            'purpose' => 'Current task display and management',
            'pages' => array('current-task'),
            'dependencies' => array('jquery')
        ),
        'tradepress-development-tabs' => array(
            'path' => 'js/tradepress-development-tabs.js',
            'purpose' => 'Development tab navigation',
            'pages' => array('development'),
            'dependencies' => array('jquery')
        ),
        'tradepress-earnings-tab' => array(
            'path' => 'js/tradepress-earnings-tab.js',
            'purpose' => 'Earnings calendar tab functionality',
            'pages' => array('earnings'),
            'dependencies' => array('jquery')
        ),
        'tradepress-tables-tab' => array(
            'path' => 'js/tradepress-tables-tab.js',
            'purpose' => 'Database tables tab management',
            'pages' => array('database'),
            'dependencies' => array('jquery')
        ),
        'tradepress-tasks' => array(
            'path' => 'js/tradepress-tasks.js',
            'purpose' => 'Task list management and filtering',
            'pages' => array('tasks'),
            'dependencies' => array('jquery')
        )
    ),
    
    // Widget Scripts
    'widgets' => array(
        'tradingview-widget' => array(
            'path' => 'js/tradingview-widget.js',
            'purpose' => 'TradingView widget integration',
            'pages' => array('research', 'trading'),
            'dependencies' => array()
        )
    ),
    
    // Setup Scripts
    'setup' => array(
        'twitchpress-setup' => array(
            'path' => 'js/twitchpress-setup.js',
            'purpose' => 'Plugin setup wizard functionality',
            'pages' => array('setup'),
            'dependencies' => array('jquery')
        ),
        'twitchpress-faq' => array(
            'path' => 'js/twitchpress-faq.js',
            'purpose' => 'FAQ accordion and help system',
            'pages' => array('faq'),
            'dependencies' => array('jquery')
        ),
        'twitchpress-enhanced-select' => array(
            'path' => 'js/twitchpress-enhanced-select.js',
            'purpose' => 'Enhanced select dropdown functionality',
            'pages' => array('all'),
            'dependencies' => array('jquery', 'select2')
        )
    ),
    
    // Third Party Libraries
    'libraries' => array(
        'select2' => array(
            'path' => 'js/select2/select2.js',
            'purpose' => 'Select2 dropdown enhancement library',
            'pages' => array('all'),
            'dependencies' => array('jquery')
        ),
        'jquery-blockui' => array(
            'path' => 'js/jquery-blockui/jquery.blockUI.js',
            'purpose' => 'jQuery BlockUI for loading states',
            'pages' => array('all'),
            'dependencies' => array('jquery')
        )
    )
);
