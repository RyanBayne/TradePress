<?php
/**
 * Add the default content to the help tab.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TradePress/Admin
 * @version     1.0.0
 */
          
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

if ( ! class_exists( 'TradePress_Admin_Help', false ) ) :
           
class TradePress_Admin_Help {

    /**
     * Add Contextual help tabs.
     * 
     * @version 1.0
     */
    public function add_tabs() {
        $screen = get_current_screen();
                                       
        if ( ! $screen ) {             
            return;
        }
        
        // Debug: Log current screen ID
        error_log('Help tab screen ID: ' . $screen->id);
        
        // Check if this is a TradePress admin page
        if ( strpos( $screen->id, 'tradepress' ) === false ) {
            return;
        }                                                                                                                                            
        
        $page = empty( $_GET['page'] ) ? '' : sanitize_title( $_GET['page'] );
        $tab  = empty( $_GET['tab'] )  ? '' : sanitize_title( $_GET['tab'] );
          
        /**
        * This is the right side sidebar, usually displaying a list of links. 
        * 
        * @var {WP_Screen|WP_Screen}
        * 
        * @version 2.0
        */
        $screen->set_help_sidebar(
            apply_filters( 'TradePress_set_help_sidebar',
                '<p><strong>' . __( 'For more information:', 'tradepress' ) . '</strong></p>' .
                '<p><a href="https://github.com/ryanbayne/TradePress" target="_blank">' . __( 'GitHub', 'tradepress' ) . '</a></p>' .
                '<p><a href="https://TradePress.wordpress.com" target="_blank">' . __( 'Blog', 'tradepress' ) . '</a></p>'.
                '<p><a href="https://discord.gg/ScrhXPE" target="_blank">' . __( 'Discord', 'tradepress' ) . '</a></p>' .
                '<p><a href="https://twitch.tv/lolindark1" target="_blank">' . __( 'My Twitch', 'tradepress' ) . '</a></p>' . 
                '<p><a href="https://dev.twitch.tv/dashboard/apps" target="_blank">' . __( 'Twitch Dev Apps', 'tradepress' ) . '</a></p>' . 
                '<p><a href="https://dev.twitch.tv/docs/api/reference/" target="_blank">' . __( 'Twitch Dev Docs', 'tradepress' ) . '</a></p>' .            
                '<p><a href="https://www.patreon.com/TradePress" target="_blank">' . __( 'Patron Pledges', 'tradepress' ) . '</a></p>'
            )
        );
                
        $screen->add_help_tab( 
            apply_filters( 'TradePress_help_tab_support', 
                array(
                    'id'        => 'TradePress_support_tab',
                    'title'     => __( 'Help &amp; Support', 'tradepress' ),
                    'content'   => '<h2>' . __( 'Help &amp; Support', 'tradepress' ) . '</h2>' . 
                    '<p><a href="https://github.com/RyanBayne/TradePress/issues" class="button button-primary">' . __( 'Bugs', 'tradepress' ) . '</a> </p>' . 
                    //'<h2>' . __( 'Pointers Tutorial', 'tradepress' ) . '</h2>' .
                    //'<p>' . __( 'The plugin will explain some features using WordPress pointers.', 'tradepress' ) . '</p>' .
                    //'<p><a href="' . admin_url( 'admin.php?page=TradePress&amp;TradePresstutorial=normal' ) . '" class="button button-primary">' . __( 'Start Tutorial', 'tradepress' ) . '</a></p>' .
                    '<h2>' . __( 'Report A Bug', 'tradepress' ) . '</h2>' .
                    '<p>You could save a lot of people a lot of time by reporting issues. Tell the developers and community what has gone wrong by creating a ticket. Please explain what you were doing, what you expected from your actions and what actually happened. Screenshots and short videos are often a big help as the evidence saves us time, we will give you cookies in return.</p>' .  
                    self::get_contextual_feedback_form(),
                )
            ) 
        );
        
   
        
        ########################################################################
        #                                                                      #
        #                          CONTRIBUTION TAB                            #
        #                                                                      #
        ########################################################################                                   
        $screen->add_help_tab( 
            apply_filters( 'TradePress_help_tab_contribute',
                array(
                    'id'        => 'TradePress_contribute_tab',
                    'title'     => __( 'Contribute', 'tradepress' ),
                    'content'   => '<h2>' . __( 'Everyone Can Contribute', 'tradepress' ) . '</h2>' .
                    '<p>' . __( 'You can contribute in many ways and by doing so you will help the project thrive.' ) . '</p>' .
                    '<p><a href="' . TRADEPRESS_DONATE . '" class="button button-primary">' . __( 'Donate', 'tradepress' ) . '</a> <a href="' . TRADEPRESS_GITHUB . '/wiki" class="button button-primary">' . __( 'Update Wiki', 'tradepress' ) . '</a> <a href="' . TRADEPRESS_GITHUB . '/issues" class="button button-primary">' . __( 'Fix Bugs', 'tradepress' ) . '</a></p>',
                ) 
            ) 
        );

        $screen->add_help_tab( array(
            'id'        => 'TradePress_newsletter_tab',
            'title'     => __( 'News Mail', 'tradepress' ),
            'content'   => '<h2>' . __( 'Annual News', 'tradepress' ) . '</h2>' .
            '<p>' . __( 'Mailchip is used to manage the projects newsletter subscribers list.' ) . '</p>' .
            '<p>' . '<!-- Begin MailChimp Signup Form -->
                <link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
                <style type="text/css">         
                    #mc_embed_signup{background:#f6fbfd; clear:left; font:14px Helvetica,Arial,sans-serif; }
                    /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
                       We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
                </style>
                <div id="mc_embed_signup">
                <form action="//webtechglobal.us9.list-manage.com/subscribe/post?u=99272fe1772de14ff2be02fe6&amp;id=b9058458e5" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <div id="mc_embed_signup_scroll">
                    <h2>TradePress News by Email</h2>
                <div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
                <div class="mc-field-group">
                    <label for="mce-EMAIL">Email Address  <span class="asterisk">*</span>
                </label>
                    <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                </div>
                <div class="mc-field-group">
                    <label for="mce-FNAME">First Name </label>
                    <input type="text" value="" name="FNAME" class="" id="mce-FNAME">
                </div>
                <div class="mc-field-group">
                    <label for="mce-LNAME">Last Name </label>
                    <input type="text" value="" name="LNAME" class="" id="mce-LNAME">
                </div>
                <p>Powered by <a href="http://eepurl.com/2W_2n" title="MailChimp - email marketing made easy and fun">MailChimp</a></p>
                    <div id="mce-responses" class="clear">
                        <div class="response" id="mce-error-response" style="display:none"></div>
                        <div class="response" id="mce-success-response" style="display:none"></div>
                    </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_99272fe1772de14ff2be02fe6_b9058458e5" tabindex="-1" value=""></div>
                    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                    </div>
                </form>
                </div>
                <script type=\'text/javascript\' src=\'//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js\'></script><script type=\'text/javascript\'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]=\'EMAIL\';ftypes[0]=\'email\';fnames[1]=\'FNAME\';ftypes[1]=\'text\';fnames[2]=\'LNAME\';ftypes[2]=\'text\';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
                <!--End mc_embed_signup-->' . '</p>',
        ) );
        
        $screen->add_help_tab( 
            apply_filters( 'TradePress_help_tab_credits', 
                array(
                    'id'        => 'TradePress_credits_tab',
                    'title'     => __( 'Credits', 'tradepress' ),
                    'content'   => '<h2>' . __( 'Credits', 'tradepress' ) . '</h2>' .
                    '<p>Please do not remove credits from the plugin. You may edit them or give credit somewhere else in your project.</p>' . 
                    '<h4>' . __( 'Automattic - This plugins core is largely based on their WooCommerce plugin.' ) . '</h4>' .
                    '<h4>' . __( 'Brian at WPMUDEV - our discussion led to this project and entirely new approach in my development.' ) . '</h4>' . 
                    '<h4>' . __( 'Ignacio Cruz at WPMUDEV - has giving us a good approach to handling shortcodes.' ) . '</h4>' .
                    '<h4>' . __( 'Ashley Rich (A5shleyRich) - author of a crucial piece of the puzzle, related to asynchronous background tasks.' ) . '</h4>' .
                    '<h4>' . __( 'Igor Vaynberg - thank you for an elegant solution to searching within a menu.' ) . '</h4>',
                    '<h4>' . __( 'Nookyyy - a constant supporter who is building Nookyyy.com using TradePress.' ) . '</h4>'
                )
            ) 
        );
                    
        $screen->add_help_tab( array(
            'id'        => 'TradePress_faq_tab',
            'title'     => __( 'FAQ', 'tradepress' ),
            'content'   => '',
            'callback'  => array( $this, 'faq' ),
        ) );     
                    
        $screen->add_help_tab( array(
            'id'        => 'TradePress_app_status_tab',
            'title'     => __( 'Twitch App Status', 'tradepress' ),
            'content'   => '',
            'callback'  => array( $this, 'app_status' ),
        ) );
                
        $screen->add_help_tab( array(
            'id'        => 'TradePress_testing_tab',
            'title'     => __( 'Testing', 'tradepress' ),
            'content'   => '',
            'callback'  => array( $this, 'testing' ),
        ) );
                        
        $screen->add_help_tab( array(
            'id'        => 'TradePress_development_tab',
            'title'     => __( 'Development', 'tradepress' ),
            'content'   => '',
            'callback'  => array( $this, 'development' ),
        ) );
        
        // Add Developer Mode tab if enabled
        if (get_option('tradepress_developer_mode', false)) {
            $screen->add_help_tab( array(
                'id'        => 'TradePress_developer_mode_tab',
                'title'     => __( 'Developer Mode', 'tradepress' ),
                'content'   => '<h2>' . __( 'Developer Mode Active', 'tradepress' ) . '</h2>' .
                              '<p>' . __( 'Developer mode is currently enabled. This provides additional debugging information and developer tools.', 'tradepress' ) . '</p>' .
                              '<p><strong>' . __( 'Current Page:', 'tradepress' ) . '</strong> ' . $screen->id . '</p>' .
                              '<p><strong>' . __( 'Page Parameters:', 'tradepress' ) . '</strong> ' . http_build_query($_GET) . '</p>',
            ) );
        }
        
        // Add Demo Mode tab if enabled
        if (function_exists('is_demo_mode') && is_demo_mode()) {
            $screen->add_help_tab( array(
                'id'        => 'TradePress_demo_mode_tab',
                'title'     => __( 'Demo Mode', 'tradepress' ),
                'content'   => '<h2>' . __( 'Demo Mode Active', 'tradepress' ) . '</h2>' .
                              '<p>' . __( 'Demo mode is currently enabled. All actions are simulated and no real data is affected.', 'tradepress' ) . '</p>' .
                              '<p>' . __( 'This is a safe environment for testing features without affecting live data.', 'tradepress' ) . '</p>',
            ) );
        }
              
    }
    

    
    /**
    * FAQ menu uses script to display a selected answer.
    * 
    * @version 1.2
    */
    public function faq() {
        $questions = array(
            0 => __( '-- Select a question --', 'tradepress' ),
            1 => __( 'Can I create my own extensions?', 'tradepress' ),
            2 => __( 'How much would it cost for a custom extension?', 'tradepress' ),
            3 => __( 'Does the plugin support Twitch API version 6?', 'tradepress' ),
        );  
        
        $questions = apply_filters( 'TradePress_faq', $questions );
        ?>


        <p>
            <ul id="faq-index">
                <?php foreach ( $questions as $question_index => $question ): ?>
                    <li data-answer="<?php echo $question_index; ?>"><a href="#q<?php echo $question_index; ?>"><?php echo $question; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </p>
        
        <ul class="faq-answers">
            <li class="faq-answer" id='q1'>
                <?php _e('Yes, if you have experience with PHP and WordPress you can create an extension for TradePress. You can submit your extension to the WordPress.org repository for the community to use or keep it private or sell it as a premium extension. Please invite me to the projects GitHub for support.', 'tradepress');?>
            </li>
            <li class="faq-answer" id='q2'>
                <p> <?php _e('You can hire me to create a new extension from as little as $30.00 and if you make the extension available to the WordPress community I will charge 50% less. I will also put from free hours into improving it which I cannot do if you request a private extension.', 'tradepress');?> </p>
            </li>        
            <li class="faq-answer" id='q3'>
                <p> <?php _e('Twitch API version 6 is being added to TradePress but it will not be ready for testing until April 2018.', 'tradepress');?> </p>
            </li>        
        </ul>                     
             
        <script>
            jQuery( document).ready( function( $ ) {
                var selectedQuestion = '';

                function selectQuestion() {
                    var q = $( '#' + $(this).val() );
                    if ( selectedQuestion.length ) {
                        selectedQuestion.hide();
                    }
                    q.show();
                    selectedQuestion = q;
                }

                var faqAnswers = $('.faq-answer');
                var faqIndex = $('#faq-index');
                faqAnswers.hide();
                faqIndex.hide();

                var indexSelector = $('<select/>')
                    .attr( 'id', 'question-selector' )
                    .addClass( 'widefat' );
                var questions = faqIndex.find( 'li' );
                var advancedGroup = false;
                questions.each( function () {
                    var self = $(this);
                    var answer = self.data('answer');
                    var text = self.text();
                    var option;

                    if ( answer === 39 ) {
                        advancedGroup = $( '<optgroup />' )
                            .attr( 'label', "<?php _e( 'Advanced: This part of FAQ requires some knowledge about HTML, PHP and/or WordPress coding.', 'tradepress' ); ?>" );

                        indexSelector.append( advancedGroup );
                    }

                    if ( answer !== '' && text !== '' ) {
                        option = $( '<option/>' )
                            .val( 'q' + answer )
                            .text( text );
                        if ( advancedGroup ) {
                            advancedGroup.append( option );
                        }
                        else {
                            indexSelector.append( option );
                        }

                    }

                });

                faqIndex.after( indexSelector );
                indexSelector.before(
                    $('<label />')
                        .attr( 'for', 'question-selector' )
                        .text( "<?php _e( 'Select a question', 'tradepress' ); ?>" )
                        .addClass( 'screen-reader-text' )
                );

                indexSelector.change( selectQuestion );
            });
        </script>        

        <?php 
    }
          
    /**
    * Displays Twitch application status. 
    * 
    * This focuses on the services main Twitch application credentials only.
    * 
    * @author Ryan Bayne
    * @version 3.1
    */
    public function app_status() {
        $set_app_status = TradePress_get_app_status();
        
        // Ensure the Twitch API application has been setup...
        if( $set_app_status[0] !== 1 ) {
            echo '<h3>' . __( 'Welcome to TradePress', 'tradepress' ) . '</h3>';
            echo $set_app_status[1];             
            echo '<p><a href="' . admin_url( 'index.php?page=tradepress-setup' ) . '" class="button button-primary">' . __( 'Setup wizard', 'tradepress' ) . '</a></p>';           
            return;    
        }                                  
                    
        // Check for existing cache.
        $cache = get_transient( 'TradePresshelptabappstatus' );
        if( $cache )                                                                                          
        {
            _e( '<p>You are viewing cached data that is up to 120 seconds old. Refresh again soon to get the latest data.</p>', 'tradepress' );
            print $cache;                                                                                              
            return;
        }                                                                                                          
        else
        {
            // No existing cache found, so test Twitch API, generate output, cache output, output output!
            _e( '<p>You are viewing real-time data on this request (not cached). The data will be cached for 120 seconds.</p>', 'tradepress' );  
        }
        
        // Define variables. 
        $overall_result = true;
        $channel_display_name = __( 'Not Found', 'tradepress' );
        $channel_status = __( 'Not Found', 'tradepress' );
        $channel_game = __( 'Not Found', 'tradepress' );
        $current_user_id = get_current_user_id();
                                          
        $output = '<h2>' . __( 'Application Credentials', 'tradepress' ) . '</h2>';
        $output .= '<p>Old App ID Method: ' . TradePress_get_main_client_id() . '</p>';
        $output .= '<p>New App ID Method: ' . TradePress_get_app_id() . '</p>';
        $output .= '<p>App Redirect: ' . TradePress_get_app_redirect() . '</p>';

        // Test Get Application Token
        $output .= '<h2>' . __( 'Test: Get Application Token', 'tradepress' ) . '</h2>';

        if( TradePress_get_app_token() )
        {
            $output .= __( 'Result: Token Exists!' ); 
        }
        else
        { 
            $output .= __( 'Result: No Application Token Found' ); 
            $overall_result = false; 
        }

        if( !$overall_result ) {
            $output .= '<h3>' . __( 'Overall Result: Not Ready!', 'tradepress' ) . '</h3>';
        } else {
            $output .= '<h3>' . __( 'Overall Result: Ready!', 'tradepress' ) . '</h3>';            
        }

        // Avoid making these requests for every admin page request. 
        set_transient( 'TradePresshelptabappstatus', $output, 120 );

        print $output;    
        
        print sprintf( __( 'Please check Twitch.tv status %s before creating fault reports.' ), '<a target="_blank" href="https://twitchstatus.com/">here</a>' );   
    }
    
    public function testing() {
        $tool_action_nonce = wp_create_nonce( 'tool_action' );
        
        ob_start();
        echo '<h3>Test New Features</h3>';
        echo '<p>' . __( 'Do not test on live sites.', 'tradepress' ) . '</p>';
        
        // New Test
        echo '<h2>' . __( 'Embed Everything Shortcode: Default Videos', 'tradepress' ) . '</h2>';
        echo '<p>' . __( 'Test the ability to display a channels default videos when the stream is offline.', 'tradepress' ) . '</p>';
        echo '<p>[TradePress_embed_everything defaultcontent="videos"]</p>';
        
        // New Test
        echo '<h2>' . __( 'Embed Everything Shortcode: Default Video', 'tradepress' ) . '</h2>';
        echo '<p>' . __( 'Test the ability to display a specific video when the stream is offline.', 'tradepress' ) . '</p>';
        echo '<p>[TradePress_embed_everything defaultcontent="video" videoid="1040648073"]</p>';
                
        // New Test
        echo '<h2>' . __( 'Authorize Bot Channel', 'tradepress' ) . '</h2>';
        echo '<p>' . __( 'Logout of your main Twitch account on Twitch.tv before using this feature.', 'tradepress' ) . '</p>';
        echo '<p><a href="' . admin_url( 'admin.php?page=TradePress_tools&_wpnonce=' . $tool_action_nonce . '&toolname=tool_authorize_bot_channel' ) . '" class="button button-primary">' . __( 'Connect to Twitch', 'tradepress' ) . '</a></p>';
        
        // New Test
        echo '<h2>' . __( 'YouTube (Google API) Setup Wizard', 'tradepress' ) . '</h2>';
        echo '<p>' . __( 'Add a set of Google API credentials created for requesting YouTube data.', 'tradepress' ) . '</p>';
        echo '<p><a href="' . admin_url( 'index.php?page=tradepress-setup-youtube' ) . '" class="button button-primary">' . __( 'YouTube Setup Wizard', 'tradepress' ) . '</a></p>';
        echo '<p><a href="' . admin_url( 'admin.php?page=TradePress_tools&_wpnonce=' . $tool_action_nonce . '&toolname=tool_google_api_test' ) . '" class="button button-primary">' . __( 'Test Google API (YouTube)', 'tradepress' ) . '</a></p>';
        
        // New Test
        echo '<h2>' . __( 'Follower Only Shortcode', 'tradepress' ) . '</h2>';
        echo '<p>Hide content from visitors unless they follow your main channel on Twitch.tv - please test and monitor occasionally over 24 hours before live use.</p>';
        echo '<p>[TradePress_followers_only]Gated content goes here.[/TradePress_followers_only]</p>';
        
        // New Test
        echo '<h2>' . __( 'Team Roster Shortcode', 'tradepress' ) . '</h2>';
        echo '<p>Hide content from .</p>';
        echo '<p>[TradePress_shortcodes shortcode="team_roster" team_id="team_id" team_name="TEST" style="horizontal"]</p>';

        ob_end_flush();
    }    
    
    public function development() {
        ob_start();
        echo '<h3>Development Area</h3>';
        echo '<p>' . __( 'Do not use these features on live sites. Feel free to test features 
        but do not feedback faults.', 'tradepress' ) . '</p>';
        
        // New Test
        echo '<h2>' . __( 'Raffle Entry Shortcode', 'tradepress' ) . '</h2>';
        echo '<p>This is the first feature within the giveaways system. It will display a button for quick raffle entry.</p>';
        echo '<p>[TradePress_raffle_entry_button]Gated content goes here.[/TradePress_raffle_entry_button]</p>';
        
        ob_end_flush();                
    }
    
    /**
     * Get contextual feedback form based on current page/tab
     * 
     * @return string HTML form
     */
    private static function get_contextual_feedback_form() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/feedback/class-feature-feedback-system.php';
        
        $page = empty($_GET['page']) ? '' : sanitize_title($_GET['page']);
        $tab = empty($_GET['tab']) ? '' : sanitize_title($_GET['tab']);
        $configure = empty($_GET['configure']) ? '' : sanitize_text_field($_GET['configure']);
        
        // Determine context and feature details
        $feature_type = 'general';
        $feature_id = $page;
        $context_label = 'Feature';
        
        if ($page === 'tradepress_scoring_directives' && !empty($configure)) {
            $feature_type = 'directive';
            $feature_id = $configure;
            $context_label = 'Directive';
        } elseif (!empty($tab)) {
            $feature_type = 'page_tab';
            $feature_id = $page . '_' . $tab;
            $context_label = 'Page Tab';
        }
        
        return TradePress_Feature_Feedback_System::render_issue_form($feature_type, $feature_id, array(
            'context_label' => $context_label,
            'show_priority' => true,
            'show_disable_option' => false
        ));
    }

    /**
     * Add help tabs for the Social Platforms page.
     * 
     * This is a specialized method specifically for the Social Platforms admin page.
     * 
     * @param WP_Screen $screen The current screen object
     * @return void
     * 
     * @todo The help tab is currently not showing, there are plans to fix this and improve the Help tab for all views
     */
    public function add_social_platforms_help_tabs($screen) {
        // Remove existing help tabs to prevent duplicates
        $screen->remove_help_tabs();
        
        // Add the sidebar first
        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'tradepress') . '</strong></p>' .
            '<p><a href="https://tradepress.io/docs/social-platforms/" target="_blank">' . __('Documentation on Social Platforms', 'tradepress') . '</a></p>' .
            '<p><a href="https://tradepress.io/support/" target="_blank">' . __('Support', 'tradepress') . '</a></p>' .
            '<p><a href="https://discord.com/developers/docs/intro" target="_blank">' . __('Discord API Documentation', 'tradepress') . '</a></p>' .
            '<p><a href="https://developer.twitter.com/en/docs" target="_blank">' . __('Twitter API Documentation', 'tradepress') . '</a></p>'
        );
        
        // Overview tab
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_overview_help',
            'title'   => __('Overview', 'tradepress'),
            'content' => '<h2>' . __('Social Platforms Overview', 'tradepress') . '</h2>' .
                        '<p>' . __('The Social Platforms page allows you to configure and manage integrations with various social media platforms. You can connect your TradePress installation to services like Discord, Twitter, and more to enable automatic posting, notifications, and other social media features.', 'tradepress') . '</p>' .
                        '<p>' . __('Use the tabs below to navigate between different sections of the Social Platforms configuration.', 'tradepress') . '</p>'
        ));
        
        // Dashboard tab help
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_dashboard_help',
            'title'   => __('Dashboard', 'tradepress'),
            'content' => '<h2>' . __('Social Platforms Dashboard', 'tradepress') . '</h2>' .
                        '<p>' . __('The Dashboard tab provides an overview of all your connected social platforms with status information and quick actions.', 'tradepress') . '</p>'
        ));
        
        // Discord tab help
        $screen->add_help_tab(array(
            'id'      => 'tradepress_social_discord_help',
            'title'   => __('Discord', 'tradepress'),
            'content' => '<h2>' . __('Discord Integration', 'tradepress') . '</h2>' .
                        '<p>' . __('The Discord tab allows you to configure and manage your Discord bot integration.', 'tradepress') . '</p>'
        ));
    }
}

endif;

$class = new TradePress_Admin_Help();

add_action( 'current_screen', array( $class, 'add_tabs' ), 50 );

unset( $class );