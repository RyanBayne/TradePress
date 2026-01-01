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

== Disclaimer ==

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
Translator needed to localize the Channel Solution for Twitch.

== Changelog == 
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
environment for WTG Task Manager - possibly an environment that triggers faults with the new version of this software. This is common
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



