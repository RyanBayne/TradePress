<?php
/**
 * Class TradePress_Prediction_Scraper
 * 
 * Base class for scraping predictions from websites
 * 
 * @package TradePress\Predictions\Scrapers
 * @version 1.0.0
 * @created 2025-04-14
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class TradePress_Prediction_Scraper {
    /**
     * @var int Source ID
     */
    protected $source_id;
    
    /**
     * @var array Source configuration
     */
    protected $config;
    
    /**
     * @var SimpleHtmlDom Instance
     */
    protected $html_parser;
    
    /**
     * Constructor
     * 
     * @param int $source_id Source ID
     * @param array $config Optional config override
     */
    public function __construct($source_id, $config = null) {
        $this->source_id = $source_id;
        
        // Load config from database if not provided
        if (null === $config) {
            $this->load_config();
        } else {
            $this->config = $config;
        }
        
        // Initialize HTML parser
        $this->html_parser = new SimpleHtmlDom();
    }
    
    /**
     * Load configuration from database
     */
    protected function load_config() {
        global $wpdb;
        $table = $wpdb->prefix . 'tradepress_prediction_sources';
        
        $source = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE source_id = %d", $this->source_id),
            ARRAY_A
        );
        
        if ($source) {
            $this->config = json_decode($source['scraping_config'], true);
        } else {
            $this->config = [];
        }
    }
    
    /**
     * Check if scraping is allowed for the source
     * 
     * @return bool Whether scraping is allowed
     */
    public function is_scraping_allowed() {
        // Check robots.txt and other restrictions
        return $this->check_robots_txt() && $this->check_last_scrape_time();
    }
    
    /**
     * Check robots.txt for the source URL
     * 
     * @return bool Whether allowed by robots.txt
     */
    protected function check_robots_txt() {
        // Implementation to check robots.txt
    }
    
    /**
     * Check if enough time has passed since last scrape
     * 
     * @return bool Whether enough time has passed
     */
    protected function check_last_scrape_time() {
        // Implementation to prevent too frequent requests
    }
    
    /**
     * Record the scraping activity
     * 
     * @param string $status Status of the scrape
     * @param string $notes Additional notes
     * @return bool Success status
     */
    protected function log_scrape($status, $notes = '') {
        // Implementation to log scraping activity
    }
    
    /**
     * Fetch predictions for a symbol
     * 
     * @param string $symbol Symbol to fetch predictions for
     * @return array|WP_Error Predictions or error
     */
    abstract public function fetch_predictions($symbol);
}