<?php
/**
 * TradePress Advisor Earnings Data Bridge
 *
 * Bridges the advisor system with real earnings data from APIs and database.
 *
 * @package TradePress/Advisor
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Advisor_Earnings_Bridge {
    
    /**
     * Maximum age for earnings data (in seconds) - 24 hours
     */
    const MAX_DATA_AGE = 86400;
    
    /**
     * Get earnings opportunities for advisor
     *
     * @param bool $force_refresh Force API refresh
     * @return array Earnings data or empty array
     */
    public static function get_earnings_opportunities( $force_refresh = false ) {
        // Load the earnings calendar model
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/models/earnings-calendar.php';
        
        // Check if we need to refresh data
        if ( self::needs_data_refresh() || $force_refresh ) {
            tradepress_trace_log( 'Earnings data refresh needed, importing from API' );
            
            $import_success = TradePress_Earnings_Calendar::refresh_earnings_data();
            
            if ( ! $import_success ) {
                tradepress_trace_log( 'Earnings data import failed' );
                return array();
            }
            
            tradepress_trace_log( 'Earnings data imported successfully' );
        }
        
        // Get upcoming earnings (next 14 days for advisor)
        $earnings_data = TradePress_Earnings_Calendar::get_upcoming_earnings( 14 );
        
        if ( empty( $earnings_data ) ) {
            tradepress_trace_log( 'No earnings data available' );
            return array();
        }
        
        // Format for advisor use
        $formatted_data = self::format_for_advisor( $earnings_data );
        
        tradepress_trace_log( 'Earnings opportunities retrieved', array( 'count' => count( $formatted_data ) ) );
        
        return $formatted_data;
    }
    
    /**
     * Check if earnings data needs refresh
     *
     * @return bool True if refresh needed
     */
    public static function needs_data_refresh() {
        $last_update = TradePress_Earnings_Calendar::get_last_update_time();
        
        if ( ! $last_update ) {
            return true; // Never updated
        }
        
        $age = time() - $last_update;
        return $age > self::MAX_DATA_AGE;
    }
    
    /**
     * Get data freshness info
     *
     * @return array Data age and source info
     */
    public static function get_data_info() {
        $last_update = TradePress_Earnings_Calendar::get_last_update_time();
        $source = TradePress_Earnings_Calendar::get_data_source_name();
        
        if ( ! $last_update ) {
            return array(
                'status' => 'no_data',
                'message' => 'No earnings data available',
                'source' => 'None'
            );
        }
        
        $age = time() - $last_update;
        $age_hours = round( $age / 3600, 1 );
        
        if ( $age > self::MAX_DATA_AGE ) {
            return array(
                'status' => 'stale',
                'message' => sprintf( 'Data is %s hours old (refresh needed)', $age_hours ),
                'source' => $source,
                'last_update' => $last_update
            );
        }
        
        return array(
            'status' => 'fresh',
            'message' => sprintf( 'Data is %s hours old', $age_hours ),
            'source' => $source,
            'last_update' => $last_update
        );
    }
    
    /**
     * Format earnings data for advisor display
     *
     * @param array $earnings_data Raw earnings data from database
     * @return array Formatted data
     */
    private static function format_for_advisor( $earnings_data ) {
        $formatted = array();
        
        foreach ( $earnings_data as $item ) {
            $symbol = $item['symbol'];
            
            $formatted[ $symbol ] = array(
                'company' => $item['company_name'],
                'date' => date( 'M j, Y', strtotime( $item['report_date'] ) ),
                'time' => self::format_report_time( $item['report_time'] ),
                'eps_estimate' => $item['eps_estimate'],
                'fiscal_quarter' => $item['fiscal_quarter'],
                'raw_date' => $item['report_date']
            );
        }
        
        // Sort by date
        uasort( $formatted, function( $a, $b ) {
            return strtotime( $a['raw_date'] ) - strtotime( $b['raw_date'] );
        });
        
        return $formatted;
    }
    
    /**
     * Format report time for display
     *
     * @param string $report_time Raw report time from API
     * @return string Formatted time
     */
    private static function format_report_time( $report_time ) {
        switch ( strtoupper( $report_time ) ) {
            case 'BMO':
                return 'Before Market Open';
            case 'AMC':
                return 'After Market Close';
            case 'DMT':
                return 'During Market Hours';
            default:
                return $report_time;
        }
    }
    
    /**
     * Force refresh earnings data
     *
     * @return bool Success status
     */
    public static function force_refresh() {
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/models/earnings-calendar.php';
        
        tradepress_trace_log( 'Force refreshing earnings data' );
        
        $success = TradePress_Earnings_Calendar::refresh_earnings_data();
        
        if ( $success ) {
            tradepress_trace_log( 'Earnings data force refresh successful' );
        } else {
            tradepress_trace_log( 'Earnings data force refresh failed' );
        }
        
        return $success;
    }
}