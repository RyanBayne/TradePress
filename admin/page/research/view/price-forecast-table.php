<?php
/**
 * The Price Forecast Table List Table Class.
 *
 * @link       https://example.com
 * @since      1.0.0
 * @package    TradePress
 * @subpackage TradePress/admin/page
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { 
    die;
}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class for rendering the price forecast table on the Research page
 */
class TradePress_Price_Forecast_Table extends WP_List_Table {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => 'forecast',
            'plural'   => 'forecasts',
            'ajax'     => false,
        ) );
    }

    /**
     * Get column information
     *
     * @return array Columns
     */
    public function get_columns() {
        $columns = array(
            'symbol'      => __( 'Symbol', 'tradepress' ),
            'name'        => __( 'Company Name', 'tradepress' ),
            'current'     => __( 'Current Price', 'tradepress' ),
            'forecast_1m' => __( '1 Month', 'tradepress' ),
            'forecast_3m' => __( '3 Month', 'tradepress' ),
            'forecast_6m' => __( '6 Month', 'tradepress' ),
            'forecast_1y' => __( '1 Year', 'tradepress' ),
            'confidence'  => __( 'Confidence', 'tradepress' ),
            'updated'     => __( 'Last Updated', 'tradepress' ),
        );
        
        return $columns;
    }
    
    /**
     * Define which columns are sortable
     *
     * @return array Sortable columns
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'symbol'      => array( 'symbol', true ),
            'name'        => array( 'name', false ),
            'current'     => array( 'current', false ),
            'confidence'  => array( 'confidence', false ),
            'updated'     => array( 'updated', true ),
        );
        
        return $sortable_columns;
    }
    
    /**
     * Get default sort column
     *
     * @return string Default sort column
     */
    protected function get_default_primary_column_name() {
        return 'symbol';
    }

    /**
     * Prepare items for table
     */
    public function prepare_items() {
        // Get the user's screen option for items per page
        $user = get_current_user_id();
        $screen = get_current_screen();
        $option = $screen ? $screen->get_option('per_page', 'option') : 'tradepress_price_forecasts_per_page';
        $per_page = get_user_meta($user, $option, true);
        
        // If the option doesn't exist or is empty, use the default
        if (empty($per_page) || !is_numeric($per_page)) {
            $per_page = 10; // Default value
        }
        
        $current_page = $this->get_pagenum();
        
        // Get search term
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        
        // Get sort parameters
        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'symbol';
        $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'asc';
        
        // Get data from source
        $data = $this->get_forecast_data($search, $orderby, $order);
        
        // Slice data for pagination
        $data_slice = array_slice($data, ($current_page - 1) * $per_page, $per_page);
        
        // Set pagination arguments
        $this->set_pagination_args(array(
            'total_items' => count($data),
            'per_page'    => $per_page,
            'total_pages' => ceil(count($data) / $per_page),
        ));
        
        // Set items
        $this->items = $data_slice;
    }

    /**
     * Default column renderer
     *
     * @param array  $item        Item being displayed
     * @param string $column_name Column being displayed
     * @return string Column content
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'current':
                return '$' . number_format( $item[ $column_name ], 2 );
                
            case 'forecast_1m':
            case 'forecast_3m':
            case 'forecast_6m':
            case 'forecast_1y':
                $forecast = $item[ $column_name ];
                $style = $this->get_confidence_color_style( $forecast['confidence'] );
                return '<span class="price-forecast" style="' . $style . '">$' . number_format( $forecast['price'], 2 ) . '</span>';
                
            case 'confidence':
                $style = $this->get_confidence_color_style( $item[ $column_name ] );
                return '<div class="confidence-indicator" style="' . $style . '">' . number_format( $item[ $column_name ], 1 ) . '%</div>';
                
            case 'updated':
                return human_time_diff( strtotime( $item[ $column_name ] ), current_time( 'timestamp' ) ) . ' ago';
                
            default:
                return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
        }
    }
    
    /**
     * Render the symbol column with actions
     *
     * @param array $item Item being displayed
     * @return string Column content
     */
    public function column_symbol( $item ) {
        $symbol = $item['symbol'];
        
        // Build row actions
        $actions = array(
            'view' => sprintf(
                '<a href="%s">%s</a>',
                esc_url( add_query_arg( array( 'symbol' => $symbol ), admin_url( 'admin.php?page=tradepress_research&tab=price_forecast' ) ) ),
                __( 'View Details', 'tradepress' )
            ),
            'refresh' => sprintf(
                '<a href="%s">%s</a>',
                wp_nonce_url( add_query_arg( array( 'action' => 'refresh_forecast', 'symbol' => $symbol ), admin_url( 'admin.php?page=tradepress_research&tab=price_forecast' ) ), 'refresh_forecast_' . $symbol ),
                __( 'Refresh', 'tradepress' )
            ),
        );
        
        // Return the symbol column with actions
        return sprintf(
            '<strong><a class="row-symbol" href="%s">%s</a></strong> %s',
            esc_url( add_query_arg( array( 'symbol' => $symbol ), admin_url( 'admin.php?page=tradepress_research&tab=price_forecast' ) ) ),
            $symbol,
            $this->row_actions( $actions )
        );
    }

    /**
     * Generate color style based on confidence
     *
     * @param float $confidence 0-100 confidence score
     * @return string CSS style
     */
    private function get_confidence_color_style( $confidence ) {
        // Normalize confidence to 0-1 range
        $normalized = max( 0, min( 100, $confidence ) ) / 100;
        
        // Calculate color components (red to yellow to green)
        if ( $normalized < 0.5 ) {
            // Red to yellow
            $r = 255;
            $g = round( 255 * ( $normalized * 2 ) );
            $b = 0;
        } else {
            // Yellow to green
            $r = round( 255 * ( 1 - ( $normalized - 0.5 ) * 2 ) );
            $g = 255;
            $b = 0;
        }
        
        // Return the background-color style with semi-transparent background
        return sprintf(
            'background-color: rgba(%d, %d, %d, 0.3); padding: 3px 8px; border-radius: 4px; display: inline-block;',
            $r, $g, $b
        );
    }
    
    /**
     * Get price forecast data
     *
     * @param string $search  Search term
     * @param string $orderby Order by column
     * @param string $order   Order direction
     * @return array Forecast data
     */
    private function get_forecast_data( $search = '', $orderby = 'symbol', $order = 'asc' ) {
        // In a real implementation, you would fetch this data from your database
        // For demo purposes, we'll generate some mock data
        
        // Demo symbols
        $symbols = array(
            'AAPL' => 'Apple Inc.',
            'MSFT' => 'Microsoft Corporation',
            'GOOG' => 'Alphabet Inc.',
            'AMZN' => 'Amazon.com, Inc.',
            'META' => 'Meta Platforms, Inc.',
            'TSLA' => 'Tesla, Inc.',
            'NVDA' => 'NVIDIA Corporation',
            'AMD'  => 'Advanced Micro Devices, Inc.',
            'INTC' => 'Intel Corporation',
            'IBM'  => 'International Business Machines Corporation',
            'ORCL' => 'Oracle Corporation',
            'CRM'  => 'Salesforce, Inc.',
            'ADBE' => 'Adobe Inc.',
            'CSCO' => 'Cisco Systems, Inc.',
            'NFLX' => 'Netflix, Inc.',
        );
        
        // Filter by search term if provided
        if ( ! empty( $search ) ) {
            $filtered_symbols = array();
            foreach ( $symbols as $symbol => $name ) {
                if ( stripos( $symbol, $search ) !== false || stripos( $name, $search ) !== false ) {
                    $filtered_symbols[ $symbol ] = $name;
                }
            }
            $symbols = $filtered_symbols;
        }
        
        // Generate data for each symbol
        $data = array();
        foreach ( $symbols as $symbol => $name ) {
            // Generate current price between $10 and $1000
            $current_price = mt_rand( 100, 10000 ) / 10;
            
            // Generate confidence score between 50 and 95
            $confidence = mt_rand( 500, 950 ) / 10;
            
            // Generate last updated timestamp within the past week
            $updated = date( 'Y-m-d H:i:s', strtotime( '-' . mt_rand( 1, 168 ) . ' hours' ) );
            
            // Create forecasts for different time periods
            $forecasts = array(
                'forecast_1m' => array(
                    'price' => $current_price * ( 1 + mt_rand( -50, 100 ) / 1000 ),
                    'confidence' => mt_rand( 600, 900 ) / 10,
                ),
                'forecast_3m' => array(
                    'price' => $current_price * ( 1 + mt_rand( -100, 200 ) / 1000 ),
                    'confidence' => mt_rand( 550, 850 ) / 10,
                ),
                'forecast_6m' => array(
                    'price' => $current_price * ( 1 + mt_rand( -150, 300 ) / 1000 ),
                    'confidence' => mt_rand( 500, 800 ) / 10,
                ),
                'forecast_1y' => array(
                    'price' => $current_price * ( 1 + mt_rand( -200, 400 ) / 1000 ),
                    'confidence' => mt_rand( 450, 750 ) / 10,
                ),
            );
            
            // Add to data array
            $data[] = array(
                'symbol'      => $symbol,
                'name'        => $name,
                'current'     => $current_price,
                'forecast_1m' => $forecasts['forecast_1m'],
                'forecast_3m' => $forecasts['forecast_3m'],
                'forecast_6m' => $forecasts['forecast_6m'],
                'forecast_1y' => $forecasts['forecast_1y'],
                'confidence'  => $confidence,
                'updated'     => $updated,
            );
        }
        
        // Sort data
        usort( $data, function( $a, $b ) use ( $orderby, $order ) {
            $result = 0;
            
            if ( $orderby === 'current' || $orderby === 'confidence' ) {
                // Numeric comparison for numeric columns
                $result = $a[ $orderby ] <=> $b[ $orderby ];
            } elseif ( $orderby === 'updated' ) {
                // Date comparison for updated column
                $result = strtotime( $a[ $orderby ] ) <=> strtotime( $b[ $orderby ] );
            } else {
                // String comparison for other columns
                $result = strcasecmp( $a[ $orderby ], $b[ $orderby ] );
            }
            
            // Apply sort order
            return ( $order === 'asc' ) ? $result : -$result;
        } );
        
        return $data;
    }
    
    /**
     * Message to be displayed when there are no items
     */
    public function no_items() {
        _e( 'No price forecasts found.', 'tradepress' );
    }
}