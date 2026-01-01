<?php
/**
 * Background Updater
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    TradePress_Background_Updater
 * @version  1.0.0
 * @package  TradePress/Classes
 * @category Class
 * @author   Ryan Bayne
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * TradePress_Background_Updater Class.
 */
class TradePress_Background_Updater extends TradePress_Background_Processing {

    /**
     * @var string
     */
    protected $action = 'TradePress_updater';

    /**
     * Dispatch updater.
     *
     * Updater will still run via cron job if this fails for any reason.
     */
    public function dispatch() {
        $dispatched = parent::dispatch();
        if ( is_wp_error( $dispatched ) ) {
            //$logger->add    
        }
    }
                         
    /**
     * Handle cron healthcheck
     *
     * Restart the background process if not already running
     * and data exists in the queue.
     */
    public function handle_cron_healthcheck() {
        // Log healthcheck for monitoring
        error_log('TradePress Background Updater: Healthcheck running');
        
        if ( $this->is_process_running() ) {
            // Background process already running.
            error_log('TradePress Background Updater: Process already running, skipping');
            return;
        }

        if ( $this->is_queue_empty() ) {
            // No data to process.
            error_log('TradePress Background Updater: Queue empty, clearing scheduled event');
            $this->clear_scheduled_event();
            return;
        }

        error_log('TradePress Background Updater: Starting background process');
        $this->handle();
    }

    /**
     * Schedule fallback event.
     */
    protected function schedule_event() {
        if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
            // Dynamic delay based on system load
            $delay = $this->get_optimal_schedule_delay();
            wp_schedule_event( time() + $delay, $this->cron_interval_identifier, $this->cron_hook_identifier );
        }
    }
    
    /**
     * Get optimal schedule delay for updater
     *
     * @return int
     */
    protected function get_optimal_schedule_delay() {
        // Check if this is during peak hours (9 AM - 5 PM)
        $current_hour = (int) date('H');
        $is_peak_hours = ($current_hour >= 9 && $current_hour <= 17);
        
        // Shorter delay during off-peak hours
        if (!$is_peak_hours) {
            return 5; // 5 seconds during off-peak
        }
        
        // Longer delay during peak hours to reduce server load
        return 30; // 30 seconds during peak hours
    }

    /**
     * Is the updater running?
     * @return boolean
     */
    public function is_updating() {
        return false === $this->is_queue_empty();
    }
                         
    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param string $callback Update callback function
     * @return mixed
     */
    protected function task( $callback ) {
        if ( ! defined( 'TradePress_UPDATING' ) ) {
            define( 'TradePress_UPDATING', true );
        }

        include_once( 'TradePress-update-functions.php' );

        // Validate callback is safe before execution
        if ( is_callable( $callback ) && is_string( $callback ) && strpos( $callback, 'TradePress_' ) === 0 ) {
            //$logger->add( 'TradePress_db_updates', sprintf( 'Running %s callback', $callback ) );
            call_user_func( $callback );
            //$logger->add( 'TradePress_db_updates', sprintf( 'Finished %s callback', $callback ) );
        } else {
            //$logger->add( 'TradePress_db_updates', sprintf( 'Could not find %s callback', $callback ) );
        }

        return false;
    }
                                 
    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete() {          
        //$logger->add( 'TradePress_db_updates', 'Data update complete' );
        TradePress_update_db_version();
        parent::complete();
    }
}
