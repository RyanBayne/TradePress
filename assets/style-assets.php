<?php
/**
 * TradePress Style Assets Registry
 *
 * Contains the registry for all CSS assets.
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
    // CSS Files - Components
    'components' => array(
        'accordion' => array(
            'path' => 'css/components/accordion.css',
            'purpose' => 'Accordion UI components for collapsible content sections',
            'pages' => array('alert-decoder', 'features', 'faq'),
            'dependencies' => array()
        ),
        'alerts' => array(
            'path' => 'css/components/alerts.css',
            'purpose' => 'Alert boxes, notifications, and info panels',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'badges' => array(
            'path' => 'css/components/badges.css',
            'purpose' => 'Status badges, indicators, and small info displays',
            'pages' => array('earnings', 'research', 'stock-vip'),
            'dependencies' => array()
        ),
        'buttons' => array(
            'path' => 'css/components/buttons.css',
            'purpose' => 'Button styles for actions and controls',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'cards' => array(
            'path' => 'css/components/cards.css',
            'purpose' => 'Card UI elements for content organization',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'code-blocks' => array(
            'path' => 'css/components/code-blocks.css',
            'purpose' => 'Code display, syntax highlighting, and copy functionality',
            'pages' => array('shortcodes', 'sandbox', 'api'),
            'dependencies' => array()
        ),
        'content-sections' => array(
            'path' => 'css/components/content-sections.css',
            'purpose' => 'Reusable content sections for task details and descriptions',
            'pages' => array('tasks', 'current-task'),
            'dependencies' => array()
        ),
        'controls' => array(
            'path' => 'css/components/controls.css',
            'purpose' => 'UI controls like filters, pagination, action buttons',
            'pages' => array('stock-vip', 'earnings', 'research'),
            'dependencies' => array()
        ),
        'data-explorer' => array(
            'path' => 'css/components/data-explorer.css',
            'purpose' => 'Data exploration and API response display',
            'pages' => array('database', 'api'),
            'dependencies' => array()
        ),
        'data-filters' => array(
            'path' => 'css/components/data-filters.css',
            'purpose' => 'Data filtering and table controls',
            'pages' => array('database', 'earnings', 'research'),
            'dependencies' => array()
        ),
        'diagnostics' => array(
            'path' => 'css/components/diagnostics.css',
            'purpose' => 'Diagnostic tools, testing, and debugging interfaces',
            'pages' => array('sandbox'),
            'dependencies' => array()
        ),
        'experiments' => array(
            'path' => 'css/components/experiments.css',
            'purpose' => 'Experimental features and testing tools',
            'pages' => array('sandbox'),
            'dependencies' => array()
        ),
        'filters' => array(
            'path' => 'css/components/filters.css',
            'purpose' => 'Filter controls, groups, and filter bars',
            'pages' => array('tasks', 'database', 'earnings'),
            'dependencies' => array()
        ),
        'form-controls' => array(
            'path' => 'css/components/form-controls.css',
            'purpose' => 'Special form controls beyond basic inputs',
            'pages' => array('automation', 'trading'),
            'dependencies' => array()
        ),
        'forms' => array(
            'path' => 'css/components/forms.css',
            'purpose' => 'Form elements, inputs, and form layouts',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'forms-wizard' => array(
            'path' => 'css/components/forms-wizard.css',
            'purpose' => 'Extended form styles for wizards and setup screens',
            'pages' => array('setup'),
            'dependencies' => array('forms')
        ),
        'indicators' => array(
            'path' => 'css/components/indicators.css',
            'purpose' => 'Technical and market indicators, signals, interpretations',
            'pages' => array('research', 'trading'),
            'dependencies' => array()
        ),
        'lists' => array(
            'path' => 'css/components/lists.css',
            'purpose' => 'List types including task lists and subtasks',
            'pages' => array('tasks', 'current-task'),
            'dependencies' => array()
        ),
        'log-viewer' => array(
            'path' => 'css/components/log-viewer.css',
            'purpose' => 'Log viewing and code display components',
            'pages' => array('sandbox', 'automation'),
            'dependencies' => array()
        ),
        'meta-data' => array(
            'path' => 'css/components/meta-data.css',
            'purpose' => 'Meta information display for tasks and assignments',
            'pages' => array('tasks', 'current-task'),
            'dependencies' => array()
        ),
        'metrics' => array(
            'path' => 'css/components/metrics.css',
            'purpose' => 'Metrics display, stats, and data visualizations',
            'pages' => array('automation', 'trading', 'earnings', 'research'),
            'dependencies' => array()
        ),
        'modals' => array(
            'path' => 'css/components/modals.css',
            'purpose' => 'Modal dialogs and popup windows',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'notices' => array(
            'path' => 'css/components/notices.css',
            'purpose' => 'WordPress-style admin notices and alerts',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'status' => array(
            'path' => 'css/components/status.css',
            'purpose' => 'Status indicators, badges, and dots',
            'pages' => array('automation', 'tasks', 'features'),
            'dependencies' => array()
        ),
        'status-messages' => array(
            'path' => 'css/components/status-messages.css',
            'purpose' => 'Status messages and feedback displays',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'steps' => array(
            'path' => 'css/components/steps.css',
            'purpose' => 'Step indicators for wizard and progress flows',
            'pages' => array('setup'),
            'dependencies' => array()
        ),
        'switches' => array(
            'path' => 'css/components/switches.css',
            'purpose' => 'Toggle switches and binary controls',
            'pages' => array('features', 'automation'),
            'dependencies' => array()
        ),
        'tables' => array(
            'path' => 'css/components/tables.css',
            'purpose' => 'Data tables, sortable columns, and table layouts',
            'pages' => array('database', 'earnings', 'tasks'),
            'dependencies' => array()
        ),
        'task-items' => array(
            'path' => 'css/components/task-items.css',
            'purpose' => 'Individual task item display and interactions',
            'pages' => array('tasks', 'current-task'),
            'dependencies' => array()
        ),
        'animations' => array(
            'path' => 'css/components/animations.css',
            'purpose' => 'CSS animations and transitions',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'candlesticks' => array(
            'path' => 'css/components/candlesticks.css',
            'purpose' => 'Candlestick chart styles and financial data visualization',
            'pages' => array('research', 'trading', 'development'),
            'dependencies' => array('charts')
        ),
        'data-analysis' => array(
            'path' => 'css/components/data-analysis.css',
            'purpose' => 'Data analysis components, KPI dashboards, and analytics metrics',
            'pages' => array('database', 'research', 'trading', 'development'),
            'dependencies' => array('metrics', 'charts')
        ),
        'heatmaps' => array(
            'path' => 'css/components/heatmaps.css',
            'purpose' => 'Heatmap visualizations and color-coded data displays',
            'pages' => array('research', 'trading', 'development'),
            'dependencies' => array('charts')
        ),
        'pagination' => array(
            'path' => 'css/components/pagination.css',
            'purpose' => 'Pagination controls and page navigation',
            'pages' => array('database', 'tasks', 'development'),
            'dependencies' => array()
        ),
        'progress' => array(
            'path' => 'css/components/progress.css',
            'purpose' => 'Progress bars, loading indicators, and completion status',
            'pages' => array('automation', 'tasks', 'development'),
            'dependencies' => array()
        ),
        'status-indicators' => array(
            'path' => 'css/components/status-indicators.css',
            'purpose' => 'Extended status indicators and visual feedback elements',
            'pages' => array('automation', 'tasks', 'trading', 'development'),
            'dependencies' => array('status')
        ),
        'task-details' => array(
            'path' => 'css/components/task-details.css',
            'purpose' => 'Detailed task view components and expanded information displays',
            'pages' => array('tasks', 'current-task', 'development'),
            'dependencies' => array('task-items', 'meta-data')
        ),
        'task-selection' => array(
            'path' => 'css/components/task-selection.css',
            'purpose' => 'Task selection interfaces and multi-select components',
            'pages' => array('tasks', 'development'),
            'dependencies' => array('task-items')
        ),
        'tooltips' => array(
            'path' => 'css/components/tooltips.css',
            'purpose' => 'Tooltip components and hover information displays',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'working-notes' => array(
            'path' => 'css/components/working-notes.css',
            'purpose' => 'Working notes, annotations, and temporary content displays',
            'pages' => array('tasks', 'development'),
            'dependencies' => array()
        )
    ),
    
    // CSS Files - Layouts
    'layouts' => array(
        'admin' => array(
            'path' => 'css/layouts/admin.css',
            'purpose' => 'WordPress admin layout adaptations',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'api' => array(
            'path' => 'css/layouts/api.css',
            'purpose' => 'API documentation and testing layouts',
            'pages' => array('api'),
            'dependencies' => array()
        ),
        'automation' => array(
            'path' => 'css/layouts/automation.css',
            'purpose' => 'Automation dashboard and control layouts',
            'pages' => array('automation'),
            'dependencies' => array()
        ),
        'database' => array(
            'path' => 'css/layouts/database.css',
            'purpose' => 'Database management and table layouts',
            'pages' => array('database'),
            'dependencies' => array()
        ),
        'features' => array(
            'path' => 'css/layouts/features.css',
            'purpose' => 'Feature management grid and card layouts',
            'pages' => array('features'),
            'dependencies' => array()
        ),
        'grids' => array(
            'path' => 'css/layouts/grids.css',
            'purpose' => 'CSS Grid layouts for various components',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'research' => array(
            'path' => 'css/layouts/research.css',
            'purpose' => 'Research page layouts and chart arrangements',
            'pages' => array('research'),
            'dependencies' => array()
        ),
        'responsive' => array(
            'path' => 'css/layouts/responsive.css',
            'purpose' => 'Responsive design breakpoints and mobile layouts',
            'pages' => array('all'),
            'dependencies' => array()
        ),
        'shortcodes' => array(
            'path' => 'css/layouts/shortcodes.css',
            'purpose' => 'Shortcode display and documentation layouts',
            'pages' => array('shortcodes'),
            'dependencies' => array()
        ),
        'tabs' => array(
            'path' => 'css/layouts/tabs.css',
            'purpose' => 'Tab navigation and content layouts',
            'pages' => array('all'),
            'dependencies' => array()
        )
    ),
    
    // CSS Files - Pages
    'pages' => array(
        'alert-decoder' => array(
            'path' => 'css/pages/alert-decoder.css',
            'purpose' => 'Alert decoder tool specific styles',
            'pages' => array('alert-decoder'),
            'dependencies' => array('accordion', 'forms')
        ),


        'discord-settings' => array(
            'path' => 'css/pages/discord-settings.css',
            'purpose' => 'Discord integration settings page styles',
            'pages' => array('discord-settings'),
            'dependencies' => array('forms', 'cards')
        ),
        'earnings' => array(
            'path' => 'css/pages/earnings.css',
            'purpose' => 'Earnings calendar and analysis page styles',
            'pages' => array('earnings'),
            'dependencies' => array('cards', 'badges', 'tables')
        ),
        'research-earnings-tab' => array(
            'path' => 'css/pages/research-earnings-tab.css',
            'purpose' => 'Research page earnings tab specific styles',
            'pages' => array('research'),
            'dependencies' => array('cards', 'badges', 'tables')
        ),
        'research-news-feed' => array(
            'path' => 'css/pages/research-news-feed.css',
            'purpose' => 'Research page news feed tab specific styles',
            'pages' => array('research'),
            'dependencies' => array('cards', 'lists')
        ),
        'research-social-networks' => array(
            'path' => 'css/pages/research-social-networks.css',
            'purpose' => 'Research page social networks tab specific styles',
            'pages' => array('research'),
            'dependencies' => array('cards', 'forms', 'status')
        ),


        'sandbox' => array(
            'path' => 'css/pages/sandbox.css',
            'purpose' => 'Sandbox testing and experiments page styles',
            'pages' => array('sandbox'),
            'dependencies' => array('experiments', 'diagnostics')
        ),
        'setup' => array(
            'path' => 'css/pages/setup.css',
            'purpose' => 'Plugin setup wizard page styles',
            'pages' => array('setup'),
            'dependencies' => array('forms-wizard', 'steps', 'progress')
        ),

        'stockvip' => array(
            'path' => 'css/pages/stockvip.css',
            'purpose' => 'Stock VIP alerts and premium features page styles',
            'pages' => array('stock-vip'),
            'dependencies' => array('cards', 'controls', 'badges')
        ),
        'tasks' => array(
            'path' => 'css/pages/tasks.css',
            'purpose' => 'Task management page styles',
            'pages' => array('tasks'),
            'dependencies' => array('lists', 'filters', 'task-items')
        ),
        'trading' => array(
            'path' => 'css/pages/trading.css',
            'purpose' => 'Trading dashboard and controls page styles',
            'pages' => array('trading'),
            'dependencies' => array('indicators', 'forms', 'metrics')
        ),
        'ui-library' => array(
            'path' => 'css/pages/ui-library.css',
            'purpose' => 'UI Library development page with component showcase and design system elements',
            'pages' => array('development'),
            'dependencies' => array(
                'buttons', 'forms', 'modals', 'status', 'animations',
                'charts', 'data-analysis', 'filters', 'controls', 
                'pagination', 'progress', 'tooltips', 'candlesticks',
                'heatmaps', 'status-indicators', 'task-details'
            )
        ),
        'watchlists-active-symbols' => array(
            'path' => 'css/pages/watchlists-active-symbols.css',
            'purpose' => 'Styles for the active symbols view in the watchlists section',
            'pages' => array('watchlists'),
            'dependencies' => array('tables', 'modals', 'badges')
        ),
        'development' => array(
            'path' => 'css/pages/development.css',
            'purpose' => 'Main development page styles and layout',
            'pages' => array('development'),
            'dependencies' => array('tabs', 'cards')
        ),
        'development-current-task' => array(
            'path' => 'css/pages/development-current-task.css',
            'purpose' => 'Current Task tab specific styles for task management and display',
            'pages' => array('development'),
            'dependencies' => array('task-details', 'forms', 'status-indicators')
        ),
        'development-tasks' => array(
            'path' => 'css/pages/development-tasks.css',
            'purpose' => 'Tasks tab styles for task listing and management',
            'pages' => array('development'),
            'dependencies' => array('task-items', 'filters', 'lists')
        ),
        'development-assets' => array(
            'path' => 'css/pages/development-assets.css',
            'purpose' => 'Assets tab styles for asset tracking and management',
            'pages' => array('development'),
            'dependencies' => array('tables', 'data-analysis')
        )
    ),
    
    // CSS Files - Main & Utilities
    'main' => array(
        'path' => 'css/main.css',
        'purpose' => 'Main stylesheet with base styles and imports',
        'pages' => array('all'),
        'dependencies' => array('variables', 'reset', 'typography')
    ),
    'variables' => array(
        'path' => 'css/base/variables.css',
        'purpose' => 'CSS custom properties and global variables',
        'pages' => array('all'),
        'dependencies' => array()
    ),
    'reset' => array(
        'path' => 'css/base/reset.css',
        'purpose' => 'CSS reset and normalization styles',
        'pages' => array('all'),
        'dependencies' => array()
    ),
    'typography' => array(
        'path' => 'css/base/typography.css',            'purpose' => 'Typography styles for headings and text elements',
            'pages' => array('all'),
            'dependencies' => array('variables')
        )
);
