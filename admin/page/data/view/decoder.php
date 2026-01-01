<?php
/**
 * TradePress Data Decoder Tab
 *
 * Provides the Decoder tab for the Data page.
 *
 * @package TradePress\Admin\DataTabs
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Data_Decoder_Tab {

    /**
     * Output the Decoder tab content.
     */
    public static function output() {
        // Check for form submission results
        $decoder_processed_status = isset($_GET['decoder_processed']) ? sanitize_text_field($_GET['decoder_processed']) : '';
        $decoded_data_key = isset($_GET['decoded_data_key']) ? sanitize_key($_GET['decoded_data_key']) : '';
        $decoded_content_raw = isset($_GET['decoded_content']) ? esc_textarea(urldecode($_GET['decoded_content'])) : ''; // For raw display
        $decoder_error = isset($_GET['decoder_error']) ? sanitize_text_field(urldecode($_GET['decoder_error'])) : '';
        $decoder_info = isset($_GET['decoder_info']) ? sanitize_text_field(urldecode($_GET['decoder_info'])) : '';

        if ($decoder_processed_status === 'true' && !empty($decoded_data_key)) {
            $parsed_data = get_transient($decoded_data_key);
            if ($parsed_data) {
                delete_transient($decoded_data_key);
                echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__('Data processed successfully. Found %d companies. Companies ranked by opportunity score.', 'tradepress'), count($parsed_data)) . '</p></div>';
                
                echo '<div class="tradepress-decoded-data-wrapper">';
                foreach ($parsed_data as $index => $company_data) {
                    echo '<div class="tradepress-decoded-data-card">';
                    
                    // Display company header with score
                    $score_class = '';
                    if ($company_data['total_score'] >= 70) $score_class = 'score-high';
                    elseif ($company_data['total_score'] >= 50) $score_class = 'score-medium';
                    else $score_class = 'score-low';
                    
                    echo '<div class="company-header">';
                    echo '<h3 class="decoded-data-title">' . sprintf(esc_html__('Rank #%d: %s (%s)', 'tradepress'), $index + 1, esc_html($company_data['company_name'] ?? 'Unknown'), esc_html($company_data['ticker_symbol'])) . '</h3>';
                    echo '<div class="opportunity-score ' . $score_class . '">';
                    echo '<span class="score-label">Opportunity Score:</span>';
                    echo '<span class="score-value">' . esc_html($company_data['total_score']) . '/100</span>';
                    echo '</div>';
                    echo '</div>';
                    
                    // Display recommendation
                    echo '<div class="recommendation">';
                    echo '<strong>Recommendation:</strong> ' . esc_html($company_data['recommendation']);
                    echo '<span class="confidence">Confidence: ' . esc_html($company_data['confidence_level']) . '</span>';
                    echo '</div>';
                    
                    // Display score breakdown
                    if (!empty($company_data['score_breakdown'])) {
                        echo '<div class="score-breakdown">';
                        echo '<h4>Score Breakdown:</h4>';
                        echo '<div class="score-components">';
                        foreach ($company_data['score_breakdown'] as $component => $score) {
                            $component_name = ucwords(str_replace('_', ' ', $component));
                            echo '<div class="score-component">';
                            echo '<span class="component-name">' . esc_html($component_name) . ':</span>';
                            echo '<span class="component-score">' . esc_html($score) . '/100</span>';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    
                    // Display risk factors if any
                    if (!empty($company_data['risk_factors'])) {
                        echo '<div class="risk-factors">';
                        echo '<h4>Risk Factors:</h4>';
                        echo '<ul>';
                        foreach ($company_data['risk_factors'] as $risk) {
                            echo '<li>' . esc_html($risk) . '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                    
                    // Original data in collapsible section
                    echo '<details class="raw-data-section">';
                    echo '<summary>View Raw Data</summary>';
                    echo '<div class="decoded-data-details"><dl>';
                    foreach ($company_data as $key => $value) {
                        if (!in_array($key, ['total_score', 'score_breakdown', 'confidence_level', 'recommendation', 'risk_factors']) && $value !== 'N/A' && !empty($value)) {
                            echo '<dt>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</dt>';
                            echo '<dd>' . nl2br(esc_html($value)) . '</dd>';
                        }
                    }
                    echo '</dl></div>';
                    echo '</details>';
                    
                    echo '</div>';
                }
                echo '</div>';

            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Could not retrieve decoded data. It may have expired or the link is invalid.', 'tradepress') . '</p></div>';
            }
        } elseif ($decoder_processed_status === 'info' && !empty($decoder_info)) {
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($decoder_info) . '</p></div>';
            if (!empty($decoded_content_raw)) {
                echo '<h4>' . esc_html__('Submitted Raw Content:', 'tradepress') . '</h4>';
                echo '<div class="tradepress-raw-content-box"><pre>' . $decoded_content_raw . '</pre></div>';
            }
        } elseif (!empty($decoder_error)) {
            echo '<div class="notice notice-error is-dismissible"><p>' .
                 esc_html(__('Error processing decoder form:', 'tradepress')) . ' ' . esc_html($decoder_error) .
                 '</p></div>';
            if ($decoder_processed_status === 'false' && !empty($decoded_content_raw)) {
                 echo '<h4>' . esc_html__('Submitted Raw Content:', 'tradepress') . '</h4>';
                 echo '<div class="tradepress-raw-content-box"><pre>' . $decoded_content_raw . '</pre></div>';
            }
        }
        
        ?>
        <div class="tradepress-decoder-tab">
            <?php self::render_decoder_tab(); ?>
        </div>
        <?php
    }

    /**
     * Render the Decoder tab form.
     */
    public static function render_decoder_tab() {
        ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Data Decoder', 'tradepress' ); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="tradepress_decode_data_action">
                <?php wp_nonce_field('decode_data_nonce_action', 'decode_data_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="decoder_source"><?php esc_html_e( 'Select Source:', 'tradepress' ); ?></label></th>
                        <td>
                            <select name="decoder_source" id="decoder_source">
                                <option value="earnings_whispers">Earnings Whispers Email</option>
                                <!-- Could add more options later -->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="decoder_content"><?php esc_html_e( 'Report Content:', 'tradepress' ); ?></label></th>
                        <td>
                            <textarea name="decoder_content" id="decoder_content" rows="10" cols="70"></textarea>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Decode', 'tradepress' ); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Handle Data Decoder form submission.
     * This method is hooked to 'admin_post_tradepress_decode_data_action'.
     *
     * @return void
     */
    public static function handle_decode_data_submission() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'tradepress'));
        }

        check_admin_referer('decode_data_nonce_action', 'decode_data_nonce');

        $decoder_source = isset($_POST['decoder_source']) ? sanitize_text_field($_POST['decoder_source']) : '';
        $content_to_parse = isset($_POST['decoder_content']) ? stripslashes($_POST['decoder_content']) : '';
        
        $args = array(
            'page' => 'tradepress_data',
            'tab' => 'decoder',
        );

        if (empty($content_to_parse)) {
            $args['decoder_error'] = urlencode(__('Decoder content cannot be empty.', 'tradepress'));
            $args['decoder_processed'] = 'false';
            $args['decoded_content'] = '';
        } elseif ($decoder_source === 'earnings_whispers') {
            $parsed_data = self::parse_earnings_whispers_email($content_to_parse);

            if ($parsed_data && count($parsed_data) > 0) {
                // Score the companies
                require_once TRADEPRESS_PLUGIN_DIR . 'includes/scoring-system/earnings-whisper-scorer.php';
                $scorer = new TradePress_Earnings_Whisper_Scorer();
                $scored_companies = $scorer->score_and_rank_companies($parsed_data);
                
                $transient_key = 'tradepress_decoded_' . uniqid();
                set_transient($transient_key, $scored_companies, 10 * MINUTE_IN_SECONDS);
                
                $args['decoded_data_key'] = $transient_key;
                $args['decoder_processed'] = 'true';
            } else {
                $args['decoder_error'] = urlencode(__('Failed to parse Earnings Whispers content. Please check the format or ensure the email contains recognizable company data.', 'tradepress'));
                $args['decoder_processed'] = 'false';
                $args['decoded_content'] = urlencode($content_to_parse);
            }
        } else {
            $args['decoder_info'] = urlencode(sprintf(__('Selected source "%s" is not yet supported for automated parsing. Displaying raw content.', 'tradepress'), esc_html($decoder_source)));
            $args['decoder_processed'] = 'info';
            $args['decoded_content'] = urlencode($content_to_parse);
        }
        
        $redirect_url = add_query_arg($args, admin_url('admin.php'));

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Parse complete Earnings Whispers email content.
     *
     * @param string $content The full email content to parse.
     * @return array Array of extracted company data or empty array on failure.
     */
    private static function parse_earnings_whispers_email($content) {
        $companies = [];
        
        // Split content into individual company sections
        // Look for patterns that indicate the start of a new company entry
        $company_sections = self::extract_company_sections($content);
        
        foreach ($company_sections as $section) {
            $company_data = self::parse_single_company_section($section);
            if ($company_data && !empty($company_data['ticker_symbol']) && $company_data['ticker_symbol'] !== 'N/A') {
                $companies[] = $company_data;
            }
        }
        
        return $companies;
    }
    
    /**
     * Extract individual company sections from the email content.
     *
     * @param string $content The full email content.
     * @return array Array of company section strings.
     */
    private static function extract_company_sections($content) {
        $sections = [];
        
        // Clean up content - remove email headers and footers
        $content = self::clean_email_content($content);
        
        // Split by company patterns - looking for company headers
        // Pattern matches company logos/names followed by ticker symbols in parentheses
        $pattern = '/(?=Logo for |[A-Z][a-zA-Z\s&\.,\'-]+ \([A-Z]{1,5}\) is confirmed|[A-Z][a-zA-Z\s&\.,\'-]+ \([A-Z]{1,5}\) reported)/';
        $raw_sections = preg_split($pattern, $content, -1, PREG_SPLIT_NO_EMPTY);
        
        // Filter and clean sections
        foreach ($raw_sections as $section) {
            $section = trim($section);
            if (strlen($section) > 100 && (
                strpos($section, 'is confirmed to report') !== false ||
                strpos($section, 'reported earnings of') !== false ||
                strpos($section, 'consensus earnings estimate') !== false ||
                strpos($section, 'Earnings Whisper') !== false
            )) {
                $sections[] = $section;
            }
        }
        
        return $sections;
    }
    
    /**
     * Clean email content by removing headers, footers, and ads.
     *
     * @param string $content Raw email content.
     * @return string Cleaned content.
     */
    private static function clean_email_content($content) {
        // Remove email headers
        $content = preg_replace('/^.*?Skip to content/s', '', $content);
        $content = preg_replace('/^.*?Having trouble viewing email\?.*?\n/m', '', $content);
        
        // Remove promotional content and ads
        $content = preg_replace('/This tech company grew.*?today\./s', '', $content);
        $content = preg_replace('/8\+ Possible Stocks for AI Rally.*?current share price is now before the round closes\./s', '', $content);
        $content = preg_replace('/Join 41,000\+ shareholders.*?invest\.modemobile\.com/s', '', $content);
        
        // Remove footer content
        $content = preg_replace('/You have received this email.*$/s', '', $content);
        $content = preg_replace('/Unsubscribe.*?Earnings Whispers ®$/s', '', $content);
        
        // Remove chart references and extra whitespace
        $content = preg_replace('/Chart for [^\n]+/m', '', $content);
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
        
        return trim($content);
    }
    
    /**
     * Parse a single company section for all relevant data.
     *
     * @param string $section The company section text.
     * @return array|false Extracted data or false on failure.
     */
    private static function parse_single_company_section($section) {
        $extracted_data = [];
        
        // Determine if this is upcoming earnings or past results
        $is_past_results = strpos($section, 'reported earnings of') !== false;
        
        if ($is_past_results) {
            return self::parse_past_earnings_results($section);
        } else {
            return self::parse_upcoming_earnings($section);
        }
    }
    
    /**
     * Parse upcoming earnings announcement data.
     *
     * @param string $section The company section text.
     * @return array Extracted data.
     */
    private static function parse_upcoming_earnings($section) {
        $extracted_data = ['report_type' => 'upcoming_earnings'];
        
        $patterns = [
            'company_and_ticker' => '/(?:Logo for )?(.+?)\s*\(([A-Z0-9\.]+)\)\s*is confirmed to report earnings/',
            'report_date_time' => '/report earnings on (.+? at approximately .+? ET)\./',
            'consensus_eps' => '/consensus earnings estimate is (.+? per share)/',
            'consensus_revenue' => '/on revenue of (\$[0-9\.]+\s*(?:billion|million|thousand|trillion)?)/',
            'revenue_growth_yoy' => '/representing ([\d\.]+% year-over-year revenue (?:growth|decline))/', // Enhanced to capture declines
            'earnings_whisper_number' => '/Earnings Whisper ® number is (.+? per share)/',
            'company_guidance_earnings' => '/company\'s guidance was for earnings of (.+? per share)/',
            'company_guidance_revenue' => '/guidance was for .*? on revenue of (\$[0-9\.]+\s*(?:billion|million|thousand|trillion)? to \$[0-9\.]+\s*(?:billion|million|thousand|trillion)?)\./',
            'investor_sentiment' => '/Investors are (\w+) going into the company\'s earnings release/',
            'expecting_beat_percentage' => '/with ([\d\.]+% expecting a beat)/',
            'short_interest_change' => '/Short interest has ((?:increased|decreased|changed|remained unchanged).*?)(?:\ssince|\swhile)/',
            'stock_performance_since_last_earnings' => '/stock has drifted ((?:lower|higher|up|down).*?)(?:\sfrom|\sto)/',
            // Updated to capture 200DMA details separately
            'stock_vs_200dma_details' => '/to be ([\d\.]+%) (above|below) its 200 day moving average of (\$[\d\.]+)/',
            'earnings_estimates_revision' => '/Overall earnings estimates have been (revised (?:higher|lower))/', // Enhanced to capture revision direction
            'options_pricing_move' => '/Option Traders are pricing in a ([\d\.]+% move) on earnings/',
            'avg_stock_move_recent_quarters' => '/stock has averaged a ([\d\.]+% move) in recent quarters/',
            'notable_options_activity' => '/notable (buying|selling) of ([\d,]+ contracts of the \$[\d\.]+\s*(?:call|put) expiring on .+?\.)/',
        ];
        
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $section, $matches)) {
                if ($key === 'company_and_ticker') {
                    $extracted_data['company_name'] = trim($matches[1]);
                    $extracted_data['ticker_symbol'] = trim($matches[2]);
                } elseif ($key === 'notable_options_activity') {
                    $extracted_data['options_activity_type'] = trim($matches[1]);
                    $extracted_data['options_activity_details'] = trim($matches[2]);
                } elseif ($key === 'stock_vs_200dma_details') {
                    $extracted_data['stock_vs_200dma_percentage'] = trim($matches[1]);
                    $extracted_data['stock_vs_200dma_direction'] = trim($matches[2]);
                    $extracted_data['stock_200dma_value'] = trim($matches[3]);
                } else {
                    $extracted_data[$key] = trim($matches[1]);
                }
            } else {
                // Set defaults for missing data
                if ($key === 'company_and_ticker') {
                    $extracted_data['company_name'] = 'N/A';
                    $extracted_data['ticker_symbol'] = 'N/A';
                } elseif ($key === 'notable_options_activity') {
                    $extracted_data['options_activity_type'] = 'N/A';
                    $extracted_data['options_activity_details'] = 'N/A';
                } elseif ($key === 'stock_vs_200dma_details') {
                    $extracted_data['stock_vs_200dma_percentage'] = 'N/A';
                    $extracted_data['stock_vs_200dma_direction'] = 'N/A';
                    $extracted_data['stock_200dma_value'] = 'N/A';
                } else {
                    $extracted_data[$key] = 'N/A';
                }
            }
        }
        
        return $extracted_data['ticker_symbol'] !== 'N/A' ? $extracted_data : false;
    }
    
    /**
     * Parse past earnings results data.
     *
     * @param string $section The company section text.
     * @return array Extracted data.
     */
    private static function parse_past_earnings_results($section) {
        $extracted_data = ['report_type' => 'past_results'];
        
        $patterns = [
            'company_and_ticker' => '/(?:Logo for )?(.+?)\s*\(([A-Z0-9\.]+)\)\s*reported earnings/',
            'reported_eps' => '/reported earnings of (.+? per share)/',
            'reported_revenue' => '/on revenue of (\$[0-9\.]+\s*(?:billion|million|thousand|trillion)?)/',
            'reporting_period' => '/for the (?:fiscal )?(.+?) ended (.+?)\./',
            'consensus_eps' => '/consensus earnings estimate was (.+? per share)/',
            'consensus_revenue' => '/consensus.*?on revenue of (\$[0-9\.]+\s*(?:billion|million|thousand|trillion)?)/',
            'earnings_whisper_number' => '/Earnings Whisper number was (.+? per share)/',
            'beat_miss_percentage' => '/company (?:beat|missed) expectations by ([\d\.]+%)/',
            'revenue_growth_yoy' => '/revenue (?:grew|fell) ([\d\.]+%) (?:on a )?year-over-year/',
            'forward_guidance_eps' => '/expects (?:fiscal )?(?:\d{4} )?(?:earnings of |.+?earnings of )(.+? per share)/',
            'forward_guidance_revenue' => '/expects (?:fiscal )?(?:\d{4} )?.*?revenue of (\$[0-9\.]+\s*(?:billion|million|thousand|trillion)?)/',
            'ceo_quote' => '/"([^"]+)"[^"]*(?:said|stated)[^"]*(?:CEO|chief executive|president)/',
        ];
        
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $section, $matches)) {
                if ($key === 'company_and_ticker') {
                    $extracted_data['company_name'] = trim($matches[1]);
                    $extracted_data['ticker_symbol'] = trim($matches[2]);
                } elseif ($key === 'reporting_period') {
                    $extracted_data['fiscal_period'] = trim($matches[1]);
                    $extracted_data['period_end_date'] = trim($matches[2]);
                } else {
                    $extracted_data[$key] = trim($matches[1]);
                }
            } else {
                if ($key === 'company_and_ticker') {
                    $extracted_data['company_name'] = 'N/A';
                    $extracted_data['ticker_symbol'] = 'N/A';
                } elseif ($key === 'reporting_period') {
                    $extracted_data['fiscal_period'] = 'N/A';
                    $extracted_data['period_end_date'] = 'N/A';
                } else {
                    $extracted_data[$key] = 'N/A';
                }
            }
        }
        
        return $extracted_data['ticker_symbol'] !== 'N/A' ? $extracted_data : false;
    }
}