<?php
/**
 * TradePress API Adapter
 * 
 * The API Adapter class serves as an intermediate layer between the TradePress application
 * and various API services. Its primary responsibility is to normalize data between
 * different API formats and provide a consistent interface for the rest of the application.
 *
 * ARCHITECTURE NOTE:
 * - This class works together with TradePress_Financial_API_Service in the following way:
 *   - Financial API Service: Handles the API-specific implementation details including
 *     endpoints, authentication, and direct API communication
 *   - API Adapter: Normalizes data between different API formats, providing a consistent
 *     interface for the rest of the application regardless of the underlying API
 *
 * When adding new API integrations:
 * 1. Create a specific API service class extending TradePress_Financial_API_Service
 * 2. Create a corresponding adapter class extending this TradePress_API_Adapter
 * 3. Implement the required methods in both classes
 * 
 * @package TradePress/API
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

abstract class TradePress_API_Adapter {
    /**
     * The API service instance
     * @var TradePress_Financial_API_Service
     */
    protected $service;
    
    /**
     * Constructor
     * 
     * @param TradePress_Financial_API_Service $service The API service
     */
    public function __construct($service) {
        $this->service = $service;
    }
    
    /**
     * Get a quote for a symbol
     * 
     * @param string $symbol The ticker symbol
     * @return array|WP_Error Normalized quote data or error
     */
    public function get_quote($symbol) {
        // This method should be implemented by child classes
        return new WP_Error('not_implemented', __('Method not implemented', 'tradepress'));
    }
    
    /**
     * Get historical price data for a symbol
     * 
     * @param string $symbol The ticker symbol
     * @param string $interval Time interval (e.g. 'daily', '1min')
     * @param int $limit Number of data points to return
     * @return array|WP_Error Normalized historical data or error
     */
    public function get_historical_data($symbol, $interval = 'daily', $limit = 100) {
        // This method should be implemented by child classes
        return new WP_Error('not_implemented', __('Method not implemented', 'tradepress'));
    }
    
    /**
     * Get company profile for a symbol
     * 
     * @param string $symbol The ticker symbol
     * @return array|WP_Error Normalized company data or error
     */
    public function get_company_profile($symbol) {
        // This method should be implemented by child classes
        return new WP_Error('not_implemented', __('Method not implemented', 'tradepress'));
    }
    
    /**
     * Get financial statements for a symbol
     * 
     * @param string $symbol The ticker symbol
     * @param string $report_type Type of report (income, balance, cash)
     * @param string $period Period (annual, quarterly)
     * @return array|WP_Error Normalized financial data or error
     */
    public function get_financial_statements($symbol, $report_type = 'income', $period = 'annual') {
        // This method should be implemented by child classes
        return new WP_Error('not_implemented', __('Method not implemented', 'tradepress'));
    }
    
    /**
     * Search for symbols
     * 
     * @param string $query Search query
     * @return array|WP_Error Normalized search results or error
     */
    public function search_symbols($query) {
        // This method should be implemented by child classes
        return new WP_Error('not_implemented', __('Method not implemented', 'tradepress'));
    }
    
    /**
     * Get market news
     * 
     * @param string $symbol Optional symbol to filter by
     * @param int $limit Number of news items to return
     * @return array|WP_Error Normalized news data or error
     */
    public function get_news($symbol = '', $limit = 10) {
        // This method should be implemented by child classes
        return new WP_Error('not_implemented', __('Method not implemented', 'tradepress'));
    }
    
    /**
     * Get earnings calendar
     * 
     * @param string $from Start date in YYYY-MM-DD format
     * @param string $to End date in YYYY-MM-DD format
     * @param string $symbol Optional symbol to filter by
     * @return array|WP_Error Normalized earnings data or error
     */
    public function get_earnings_calendar($from = '', $to = '', $symbol = '') {
        // This method should be implemented by child classes
        return new WP_Error('not_implemented', __('Method not implemented', 'tradepress'));
    }
    
    /**
     * Standardize a quote response to a consistent format
     * 
     * @param array $data Raw quote data
     * @return array Normalized quote data
     */
    protected function normalize_quote_data($data) {
        // Default normalized structure
        $normalized = array(
            'symbol' => '',
            'price' => 0,
            'change' => 0,
            'change_percent' => 0,
            'volume' => 0,
            'average_volume' => 0,
            'previous_close' => 0,
            'open' => 0,
            'high' => 0,
            'low' => 0,
            'market_cap' => 0,
            'pe_ratio' => null,
            'dividend_yield' => null,
            'timestamp' => current_time('timestamp')
        );
        
        // Child classes should implement mapping from API-specific format to normalized format
        return $normalized;
    }
    
    /**
     * Standardize historical price data to a consistent format
     * 
     * @param array $data Raw historical price data
     * @return array Normalized historical data
     */
    protected function normalize_historical_data($data) {
        $normalized = array();
        
        // Child classes should implement mapping from API-specific format to normalized format
        return $normalized;
    }
    
    /**
     * Standardize company profile data to a consistent format
     * 
     * @param array $data Raw company profile data
     * @return array Normalized company profile
     */
    protected function normalize_company_data($data) {
        // Default normalized structure
        $normalized = array(
            'symbol' => '',
            'name' => '',
            'exchange' => '',
            'currency' => '',
            'industry' => '',
            'sector' => '',
            'website' => '',
            'description' => '',
            'ceo' => '',
            'employees' => 0,
            'address' => '',
            'phone' => '',
            'country' => '',
            'ipo_date' => ''
        );
        
        // Child classes should implement mapping from API-specific format to normalized format
        return $normalized;
    }
    
    /**
     * Standardize financial statement data to a consistent format
     * 
     * @param array $data Raw financial statement data
     * @param string $report_type Type of report
     * @return array Normalized financial data
     */
    protected function normalize_financial_data($data, $report_type) {
        $normalized = array();
        
        // Child classes should implement mapping from API-specific format to normalized format
        return $normalized;
    }
    
    /**
     * Standardize news data to a consistent format
     * 
     * @param array $data Raw news data
     * @return array Normalized news data
     */
    protected function normalize_news_data($data) {
        $normalized = array();
        
        // Child classes should implement mapping from API-specific format to normalized format
        return $normalized;
    }
    
    /**
     * Standardize earnings calendar data to a consistent format
     * 
     * @param array $data Raw earnings data
     * @return array Normalized earnings data
     */
    protected function normalize_earnings_data($data) {
        $normalized = array();
        
        // Child classes should implement mapping from API-specific format to normalized format
        return $normalized;
    }
    
    /**
     * Get the API service instance
     * 
     * @return TradePress_Financial_API_Service The API service
     */
    public function get_service() {
        return $this->service;
    }
}
