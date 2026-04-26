=== TradePress ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/WordPressPlugins
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Trading, Stocks, Market, Stock Market, Crypto, Trading Algorithmn, Investing, Portfolio, Money, Finance
Requires at least: 5.4
Tested up to: 6.8.0
Stable tag: 1.0.95
Requires PHP: 5.6
                        
Stock market trading support tool (alpha phase)
                       
== Description ==
TradePress will require intense testing before considering it to be a key support in trading 
so take great care when factoring output into your decision making.

The plugins purpose is to combine multiple sources of information and bring them to one, highly customisable location.
Then use that information to support decision making in a dynamic manner, which will also be customisable. 

Eventually I intend to generate signals to suit different forms of trading, custom strategies and trading-timeframes.
This will be done using a bespoke interface and a unique approach to building an algorithm.


## About TradePress

TradePress is a sophisticated WordPress plugin designed to empower traders by systematically identifying and prioritizing investment opportunities through a data-centric approach. Its core strength lies in an advanced 'Scoring Directives' engine, which intelligently processes a comprehensive spectrum of market data—encompassing technical indicators, fundamental analysis, earnings reports, and even news sentiment. By applying these user-configurable or pre-defined directives, TradePress generates a dynamic score for each financial symbol, reflecting its potential relative to specific trading strategies. This robust, score-driven prioritization is key to helping users focus on the most promising symbols and can form the foundation for automated trading decisions, ultimately aiming to provide a significant analytical advantage in navigating the complexities of the financial markets.

# Technical / Descriptive Names:
These names are good for internal use, documentation, or for users who appreciate a more direct description of what the feature does.

TradePress is a...
Directive-Based AutoTrader (DBAT)

It ranks stocks using...
Scoring Engine Execution System (SEES)

It can provide...
Quantitative Signals

It also offers...
Directive-Driven Order Placement (DDOP)

Based on a...
Score-Driven Execution Module (SDEM)


== Disclaimer ==
The plugin will require the users own configuration so that all outcomes are based on the users
choices and understanding of both the plugin and stock market. While it would be ideal for the plugin
to support profitable trading it's primary goal is to support a traders decision making outside of the
WordPress environment. None of the features or information offered within the plugin can be considered
financial advice due to the potential for technical faults that affect information flow and data output.

You're own reasoning, analysis and risk-management must be consistent. This is how you will identify
if TradePress is reliable and help the project move towards not only generating signals, but auto-trading. 

= Links =                                                                
*   <a href="https://github.com/RyanBayne/TradePress" title="">GitHub</a>       
*   <a href="https://discord.gg/ScrhXPE" title="Chat about TradePress on Discord.">Discord Chat</a>     
*   <a href="https://www.patreon.com/WordPressPlugins" title="">Patreon Pledges</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="">PayPal Donations</a>       

= Features List = 
* Trading Platforms API Integration 
* Symbols Post Type 

= Coming Features = 
* More Trading Platform API Integration 
* Bespoke signals approach that can be displayed using shortcode 
* Customisable algorithmn using a strategy-profile approach with optional directives
* Automatic trading - initially paper/demo for the near future

= Support = 
The plugins development is fully supported. The community can support each
other on the projects <a href="https://discord.gg/ScrhXPE" title="Chat about TradePress on Discord.">Discord</a>
and you can unlock premium support by joining the projects <a href="https://www.patreon.com/WordPressPlugins" title="">Patreon</a> 
or donating via <a href="https://www.paypal.me/zypherevolved" title="">PayPal</a> or other agreed methods.

== Installation ==
= Method One =
Move folder inside the .zip file into the "wp-content/plugins/" directory if your website is stored locally. Then upload the new plugin folder using your FTP program.

= Method Two = 
Use your hosting control panels file manager to upload the plugin folder (not the .zip, only the folder inside it) to the "wp-content/plugins/" directory.

= Method Three =
In your WordPress admin click on Plugins then click on Add New. You can search for your plugin there and perform the installation easily. This method does not apply to premium plugins.

= Example Shortcodes = 
Shortcodes are in development: [TradePress_shortcodes]


== Screenshots ==
1. Custom list of plugins for bulk installation and activation.
2. Example of how the WP admin is fully used. Help tab can be available on any page.
3. Security feature that helps to detect illegal entry of administrator accounts into the database.

== Languages ==
Translator needed to localize TradePress.

== Changelog == 
= 1.0.95 =
* Faults Resolved
    - Replaced bare _e() calls with esc_html_e() in tradepress_ajax_get_api_call_details() in includes/ajax-handlers.php to satisfy WordPress.Security.EscapeOutput.UnsafePrintingFunction (E-86a150cb)
    - Replaced all bare _e() / _ex() calls with esc_html_e() / esc_html_ex() across 9 files (85 occurrences) to resolve WordPress.Security.EscapeOutput.UnsafePrintingFunction: admin/page/setup-wizard/partials/watchlist.php, admin/page/tradingplatforms/view/template.api-tab.php, admin/page/tradingplatforms/view/view.api_management.php, admin/templates/education-dashboard.php, admin/templates/settings-sees.php, api/webull/webull-admin.php, loader.php, posts/post-type-webhooks.php, toolbars/developer-mode.php
    - Resolved all 13 WordPress.Security.EscapeOutput.ExceptionNotEscaped issues: wrapped Exception message arguments with esc_html() or esc_html__() across manage-sources.php, tests/framework/class-test-runner.php, api/base-api.php
    - Resolved all 384 WordPress.Security.EscapeOutput.OutputNotEscaped issues across 64+ files: applied esc_html__() to bare __() calls, esc_attr() to CSS class variables, (int)/(float) casts to numeric output, esc_html() to plain-text function calls, esc_attr() to wp_create_nonce() in HTML attributes, esc_url() to admin_url() calls, and phpcs:ignore suppressions for HTML-returning functions and CLI test scripts
    - Fixed wp_die(__()) → wp_die(esc_html__()) across admin functions, form handlers, listener, toolbars (32 occurrences)
    - Fixed echo wp_create_nonce() → echo esc_attr(wp_create_nonce()) in hidden inputs across tradingplatforms views, webull-admin, tests-tabs (12 occurrences)
    - Fixed echo admin_url() → echo esc_url(admin_url()) in advisor-controller.php (15 occurrences)
* Feature Improvements
    - None
* Technical Notes
    - Updated @version tag on tradepress_ajax_get_api_call_details() to 1.0.95
    - Results file (.wpv-results.json) updated to reflect all resolved issues: 499 → 17 remaining (15 I18n warnings + 2 config notices)
* Configuration Advice
    - No changes required
* Database Changes
    - No Changes

= 1.0.9 = 
* Faults Resolved
    - Fixed unordered placeholders in translatable string in discord-webhook-manager.php send_trade_alert() — changed %s, %s, %s to %1$s, %2$s, %3$s to satisfy WordPress.WP.I18n.UnorderedPlaceholdersText (E-7152f7b7)
    - Fixed concatenated variable in esc_html_e() call in api-test-handler.php generate_html_report() — replaced dynamic string concatenation with a static translatable string to satisfy WordPress.WP.I18n.NonSingularStringLiteralText (E-6f068f15)
    - Added missing 'tradepress' text domain to _e() call in post-type-webhooks.php html_TradePress_post_webhooks_options() to satisfy WordPress.WP.I18n.MissingArgDomain (E-02eb4929)
* Feature Improvements
    - None
* Technical Notes
    - Updated @version tags on send_trade_alert(), generate_html_report(), and html_TradePress_post_webhooks_options() to 1.0.95
* Configuration Advice
    - No changes required
* Database Changes
    - No Changes

= 1.0.8 = 
* Faults Resolved
    - Added missing 'tradepress' text domain to __() call in TradePress_helix_httpstatus_groups() in api/functions.tradepress-api-statuses.php to satisfy WordPress.WP.I18n.MissingArgDomain (E-1744d162)
* Feature Improvements
    - None
* Technical Notes
    - Updated @version tag on TradePress_helix_httpstatus_groups() to 1.0.95
* Configuration Advice
    - No changes required
* Database Changes
    - No Changes

= 1.0.7 = 
* Faults Resolved
    - Replaced all unescaped _e() calls with esc_html_e() in admin/page/research/research-tabs.php to satisfy WordPress.Security.EscapeOutput.UnsafePrintingFunction (E-ee49f060)
    - Escaped all unescaped date(), human_time_diff(), and site_url() output in automation-tabs.php to satisfy WordPress.Security.EscapeOutput.OutputNotEscaped (E-46ee8627)
    - Removed empty string from __() translation call in settings/view/users.php Registration section to satisfy WordPress.WP.I18n.NoEmptyStrings (E-f9d58a2e)
    - Escaped all get_sort_class() output with esc_attr() in configure-directives.php to satisfy WordPress.Security.EscapeOutput.OutputNotEscaped (E-ac5f1914)
    - Replaced all _e() with esc_html_e() (or wp_kses_post(__()) for HTML content) across 5 notice files: update.php, install.php, updated.php, updating.php, custom-dismiss.php to satisfy WordPress.Security.EscapeOutput.UnsafePrintingFunction (E-85a4670e)
    - Replaced all _e() with esc_html_e() across 19 additional files to satisfy WordPress.Security.EscapeOutput.UnsafePrintingFunction: api-activity.php (32), api-endpoints.php (5), tables.php (6), all-apiactivity.php (1), endpoints.php (1), development-tabs.php (4), pointers.php (22), form-handler-example.php (2), database.php (6), extensions.php (3), folders.php (6), improvement.php (12), introduction.php (22), ready.php (9), services.php (3), general.php (1), price-forecast-table.php (1), class-mode-indicators.php (1), listtable-endpoints.php (1)
    - Wrapped 15 unescaped wp_create_nonce() calls with esc_attr() across 8 files to satisfy WordPress.Security.EscapeOutput.OutputNotEscaped
    - Wrapped 10 unescaped admin_url() calls with esc_url() across 7 files to satisfy WordPress.Security.EscapeOutput.OutputNotEscaped
    - Replaced 75 unescaped __() calls with esc_html__() in echo, printf, sprintf, wp_die, concatenation, and ternary contexts across 19 files to satisfy WordPress.Security.EscapeOutput.OutputNotEscaped
    - Escaped $args['new_version'] with esc_html() in admin-initialisation.php printf output
    - Replaced 6 htmlspecialchars() calls with esc_html() in direct-api-test.php
    - Wrapped 10 unescaped date_i18n(), human_time_diff(), and tradepress_number_format() calls with esc_html() across 5 files
    - Escaped all unescaped output in configure-directives.php Scoring Information section: 35 $saved_config values with esc_html(), 8 round() calls with esc_html(), 2 date() calls with esc_html(), 13 interpolated strings with esc_html(), 9 arithmetic expressions with esc_html(), $bg_color/$opacity with esc_attr(), $status_class with esc_attr(), style ternaries with esc_attr() (E-52345e06 and ~85 related issues)
    - Replaced 27 _e() with esc_html_e() in includes/ajax-handlers.php to satisfy WordPress.Security.EscapeOutput.UnsafePrintingFunction (E-b60e98ee)
    - Renamed 'TradePress Logo Icon.png' to 'tradepress-logo-icon.png' to remove spaces from filename per WordPress naming standards (E-2d2cb69b)
    - Replaced __() with esc_html__() in wp_die() call in automation-controller.php save_scoring_directives() (E-c0b73964)
* Feature Improvements
    - None
* Technical Notes
    - Updated load_social_networks_tab() and display_symbol_details() methods with @version 1.0.95
    - All translatable output in research tabs now uses escaped printing functions per WordPress coding standards
    - Added esc_html() around date() and human_time_diff() calls in dashboard_tab() and data_import_tab()
    - Added esc_url() around site_url() calls in cron_tab()
    - Updated @version tags on dashboard_tab, data_import_tab, and cron_tab methods
* Configuration Advice
    - No changes required
* Database Changes
    - No Changes

= 1.0.6 = 
* Faults Resolved
    - Replaced all unescaped _e() calls with esc_html_e() in automation-dashboard.php to satisfy WordPress.Security.EscapeOutput.UnsafePrintingFunction
    - Added missing 'tradepress' text domain to all __() calls in admin-help.php (add_tabs and app_status methods)
    - Fixed all unescaped output in admin-help.php: replaced _e() with esc_html_e(), __() with esc_html__(), added esc_url() for URLs, wp_kses_post() for HTML content, and esc_attr()/esc_html() for variables
    - Fixed all 30 unescaped output issues in admin/form-fields.php: added wp_kses_post() for pre-escaped HTML variables, esc_attr() for attribute contexts, esc_html() for option labels
    - Fixed mismatched text domains: replaced 'twitch-press' with 'tradepress' in admin-initialisation.php, replaced 'TradePress-login' with 'tradepress' in 35 calls in settings/view/users.php, confirmed 'text_domain' already corrected in toolbar-developers.php
    - Removed empty strings from translation functions in admin-roles.php: replaced 27 __( '', 'tradepress' ) calls with plain empty strings
    - Fixed all 19 unescaped output issues in admin/notices/admin-notices.php: escaped $title with esc_html(), $desc with wp_kses_post(), $d with esc_attr() in notice methods; wrapped pre-escaped HTML variables with wp_kses_post(); replaced __() with esc_html__() in echo contexts
    - Replaced variable text domain $this->slug with literal 'tradepress' in 14 translation calls across admin/config/admin-menus.php
    - Replaced all _e() with esc_html_e() and escaped wp_create_nonce output in admin/page/development/view/diagrams.php
    - Fixed all escaping issues in admin/page/data/view/listtable-apiactivity.php: esc_url for URLs, esc_html for database values, esc_textarea for textarea content, esc_attr for HTML attributes, esc_html__ for labels, wp_kses_post for HTML translations
    - Replaced all _e() with esc_html_e() in admin/page/development/view/feature-status.php
    - Added admin/tools/wpcs-fixer.php: reusable AJAX-based auto-fixer for UnsafePrintingFunction issues (_e to esc_html_e), enabling a Fix button on the Issue Details panel
    - Replaced all _e() with esc_html_e() in admin/page/scoring-directives/view/configure-directives.php (433 calls)
* Feature Improvements
    - None
* Technical Notes
    - All translatable output in the automation dashboard now uses escaped printing functions per WordPress coding standards
* Configuration Advice
    - No changes required
* Database Changes
    - No Changes

= 1.0.5 Released 10th July 2025 = 
* Faults Resolved
    - None
* Feature Improvements
    - Added section visibility controls to UI Library for focused development
    - Implemented section state persistence using localStorage
    - Enhanced UI Library with Show All/Hide All functionality for better workflow
* Technical Notes
    - Added data attributes to UI Library sections for programmatic control
    - Implemented jQuery-based section toggle functionality
    - Enhanced main container with structured section management
* Configuration Advice
    - Use section visibility controls in UI Library to focus on one component at a time
    - Section visibility preferences are automatically saved and restored
* Database Changes
    - No Changes

= 1.0.4 Released 10th July 2025 = 
* Faults Resolved
    - Fixed fatal error: Call to undefined method TradePress_Admin_Notices::add_notice()
    - Resolved developer toolbar "Backup Plugin" tool failure issues
    - Corrected method name mismatch in notices system (add_notice vs add_wordpress_notice)
    - Fixed toolbar action handling inconsistencies causing backup failures
* Feature Improvements
    - Enhanced developer toolbar with centralized action handling system
    - Improved backup plugin functionality with version increment automation
    - Added comprehensive error reporting for toolbar operations
    - Implemented standardized notice system across all toolbar tools
    - Enhanced backup tool with detailed success/failure messaging
* Technical Notes
    - Centralized all toolbar actions through TradePress_Toolbars class
    - Implemented consistent nonce verification for all toolbar operations
    - Added proper exception handling for backup operations with detailed error messages
    - Updated asset guidelines documentation with broken styles tracking system
    - Enhanced CSS class checker in UI Library for development debugging
* Configuration Advice
    - Developer toolbar backup tool now requires proper file permissions for backup directory
    - Beta testing and demo mode switches now provide clearer feedback messages
    - Enable WP_DEBUG for detailed toolbar operation logging
* Database Changes
    - No Changes

= 1.0.3 Released ??? = 
* Faults Resolved
    - None documented
* Feature Improvements
    - None documented
* Technical Notes
    - None documented
* Configuration Advice
    - None documented
* Database Changes
    - No Changes

= 1.0.2 Released ??? = 
* Faults Resolved
    - None
* Feature Improvements
    - None
* Technical Notes
    - None
* Configuration Advice
    - None
* Database Changes
    - None

= 1.0.1 Released 10th June 2025 = 
* Faults Resolved
    - Fixed UI Library CSS asset loading issues in development environment
    - Resolved asset manager initialization problems
* Feature Improvements
    - Added comprehensive UI Library development tool with color palette showcase
    - Implemented Asset Status Management system for monitoring CSS/JS files
    - Enhanced development environment with component demonstration capabilities
    - Added real-time asset validation and health monitoring
    - Introduced centralized asset queue system for optimized loading
* Technical Notes
    - Implemented TradePress_Asset_Queue class for intelligent asset management
    - Added context-aware asset loading based on current page/tab detection
    - Enhanced asset verification system with detailed status reporting
    - Improved development workflow with UI component library
    - Added comprehensive asset dependency management
* Configuration Advice
    - Developers should use the new UI Library (Development > UI Library) for component reference
    - Asset status can be monitored via Development > Assets tab
    - Enable WP_DEBUG for detailed asset loading information
* Database Changes
    - No Changes

= 1.0.0 = 
* Initial Release
    - Basic plugin structure and foundation
    - Trading platform integration framework
    - Symbols post type implementation
    - Core asset management system

= When To Update = 

Browse the changes log and decide if an update is required. There is nothing wrong with skipping version if it does not
help you - look for security related changes or new features that could really benefit you. If you do not see any you may want
to avoid updating. If you decide to apply the new version - do so after you have backedup your entire WordPress installation 
(files and data). Files only or data only is not a suitable backup. Every WordPress installation is different and creates a different
environment for TradePress - possibly an environment that triggers faults with the new version of this software. This is common
in software development and it is why we need to make preparations that allow reversal of major changes to our website.

== Contributors ==
List of developers and people who have supported development in a technical way... 

* None Yet 

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the TradePress plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.

Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.



