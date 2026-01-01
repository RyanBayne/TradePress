<?php
/**
 * TradePress CRON Jobs Tab
 *
 * Displays and manages CRON jobs for TradePress
 *
 * @package TradePress
 * @subpackage admin/page/automation
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'tradepress'));
}

// Handle CRON job scheduling form submission
if (isset($_POST['schedule_tradepress_cron']) && check_admin_referer('tradepress_schedule_cron', 'tradepress_cron_nonce')) {
    $cron_job = sanitize_text_field($_POST['cron_job']);
    $recurrence = sanitize_text_field($_POST['recurrence']);
    
    if ($cron_job === 'earnings_calendar') {
        // Clear any existing schedule for this job
        $timestamp = wp_next_scheduled('tradepress_fetch_earnings_calendar');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'tradepress_fetch_earnings_calendar');
        }
        
        // Schedule new job
        if ($recurrence !== 'none') {
            wp_schedule_event(time(), $recurrence, 'tradepress_fetch_earnings_calendar');
            
            // Show success message
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__('Earnings Calendar import has been scheduled.', 'tradepress') . 
                 '</p></div>';
        } else {
            // Show info message
            echo '<div class="notice notice-info is-dismissible"><p>' . 
                 esc_html__('Earnings Calendar import schedule has been removed.', 'tradepress') . 
                 '</p></div>';
        }
    }
}

// Get all scheduled CRON events
$cron_array = _get_cron_array();
?>

<div class="tab-content" id="cron">
    <h2><?php esc_html_e('WordPress CRON Jobs', 'tradepress'); ?></h2>
    <p class="description"><?php esc_html_e('Manage scheduled tasks for TradePress and monitor all WordPress CRON jobs.', 'tradepress'); ?></p>
    
    <div class="tradepress-card">
        <h3><?php esc_html_e('Schedule TradePress CRON Jobs', 'tradepress'); ?></h3>
        <p><?php esc_html_e('Use this form to schedule automatic data import and other recurring tasks.', 'tradepress'); ?></p>
        
        <form method="post" action="">
            <?php wp_nonce_field('tradepress_schedule_cron', 'tradepress_cron_nonce'); ?>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="cron_job"><?php esc_html_e('CRON Job', 'tradepress'); ?></label></th>
                        <td>
                            <select name="cron_job" id="cron_job">
                                <option value="earnings_calendar"><?php esc_html_e('Import Earnings Calendar Data', 'tradepress'); ?></option>
                                <!-- More jobs can be added here later -->
                            </select>
                            <p class="description">
                                <?php esc_html_e('Select a job to schedule.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="recurrence"><?php esc_html_e('Frequency', 'tradepress'); ?></label></th>
                        <td>
                            <select name="recurrence" id="recurrence">
                                <option value="none"><?php esc_html_e('Disabled', 'tradepress'); ?></option>
                                <option value="hourly"><?php esc_html_e('Hourly', 'tradepress'); ?></option>
                                <option value="twicedaily"><?php esc_html_e('Twice Daily', 'tradepress'); ?></option>
                                <option value="daily"><?php esc_html_e('Daily', 'tradepress'); ?></option>
                                <option value="weekly"><?php esc_html_e('Weekly', 'tradepress'); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('How often to run this job. Set to "Disabled" to remove the scheduled job.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div id="earnings_calendar_details" class="job-details">
                <h4><?php esc_html_e('Earnings Calendar Import Details', 'tradepress'); ?></h4>
                <p><?php esc_html_e('This job will fetch earnings calendar data from Alpha Vantage using the EARNINGS_CALENDAR endpoint with a 3-month horizon parameter.', 'tradepress'); ?></p>
                <ul>
                    <li><?php esc_html_e('Data Provider: Alpha Vantage', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Endpoint: EARNINGS_CALENDAR', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Horizon: 3 months', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Data Storage: Cached for 6 hours', 'tradepress'); ?></li>
                </ul>
                
                <?php
                // Check if an API key is configured
                $api_key = get_option('tradepress_alphavantage_api_key', '');
                if (empty($api_key)):
                ?>
                <div class="notice notice-warning inline">
                    <p>
                        <?php esc_html_e('No Alpha Vantage API key configured. Please configure your API key in the API Settings.', 'tradepress'); ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_api')); ?>"><?php esc_html_e('Configure API', 'tradepress'); ?></a>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php
                // Check if job is already scheduled
                $next_run = wp_next_scheduled('tradepress_fetch_earnings_calendar');
                if ($next_run):
                    $schedule = wp_get_schedule('tradepress_fetch_earnings_calendar');
                ?>
                <div class="notice notice-info inline">
                    <p>
                        <?php 
                        echo sprintf(
                            esc_html__('This job is currently scheduled to run %1$s. Next run: %2$s', 'tradepress'),
                            '<strong>' . esc_html($schedule) . '</strong>',
                            '<strong>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_run)) . '</strong>'
                        ); 
                        ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            
            <p class="submit">
                <input type="submit" name="schedule_tradepress_cron" class="button button-primary" value="<?php esc_attr_e('Save CRON Schedule', 'tradepress'); ?>">
            </p>
        </form>
    </div>
    
    <div class="tradepress-card">
        <h3><?php esc_html_e('All WordPress CRON Jobs', 'tradepress'); ?></h3>
        <p><?php esc_html_e('This table shows all currently scheduled CRON jobs in WordPress. TradePress jobs are highlighted.', 'tradepress'); ?></p>
        
        <div id="tradepress-cron-accordion" class="tradepress-accordion">
            <div class="accordion-header">
                <h3><?php esc_html_e('View All Scheduled CRON Jobs', 'tradepress'); ?></h3>
            </div>
            <div class="accordion-content">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Hook', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Schedule', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Next Run', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Arguments', 'tradepress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($cron_array)) {
                            foreach ($cron_array as $timestamp => $crons) {
                                foreach ($crons as $hook => $events) {
                                    foreach ($events as $key => $event) {
                                        $is_tradepress = (strpos($hook, 'tradepress') !== false);
                                        $row_class = $is_tradepress ? 'tradepress-cron-job' : '';
                                        ?>
                                        <tr class="<?php echo esc_attr($row_class); ?>">
                                            <td>
                                                <?php 
                                                if ($is_tradepress) {
                                                    echo '<strong>' . esc_html($hook) . '</strong>';
                                                } else {
                                                    echo esc_html($hook);
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo isset($event['schedule']) ? esc_html($event['schedule']) : esc_html__('One-time', 'tradepress'); ?></td>
                                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp); ?></td>
                                            <td>
                                                <?php 
                                                if (!empty($event['args'])) {
                                                    echo '<code>' . esc_html(json_encode($event['args'])) . '</code>';
                                                } else {
                                                    echo '—';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                        } else {
                            echo '<tr><td colspan="4">' . esc_html__('No CRON jobs scheduled.', 'tradepress') . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="accordion-header">
                <h3><?php esc_html_e('WordPress CRON Information', 'tradepress'); ?></h3>
            </div>
            <div class="accordion-content">
                <ul>
                    <li>
                        <strong><?php esc_html_e('WP-Cron Status:', 'tradepress'); ?></strong>
                        <?php 
                        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
                            echo '<span class="cron-status disabled">' . esc_html__('Disabled', 'tradepress') . '</span>';
                            echo '<p class="description">' . esc_html__('WordPress CRON is disabled via DISABLE_WP_CRON constant. Scheduled tasks will not run automatically.', 'tradepress') . '</p>';
                        } else {
                            echo '<span class="cron-status enabled">' . esc_html__('Enabled', 'tradepress') . '</span>';
                        }
                        ?>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Alternative WP-Cron:', 'tradepress'); ?></strong>
                        <?php 
                        if (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON) {
                            echo '<span class="cron-status alternate">' . esc_html__('Enabled', 'tradepress') . '</span>';
                            echo '<p class="description">' . esc_html__('Alternative WP-Cron is enabled. This is used when the standard CRON method fails.', 'tradepress') . '</p>';
                        } else {
                            echo '<span class="cron-status standard">' . esc_html__('Disabled', 'tradepress') . '</span>';
                        }
                        ?>
                    </li>
                </ul>
            </div>
            
            <div class="accordion-header">
                <h3><?php esc_html_e('CRON Actions', 'tradepress'); ?></h3>
            </div>
            <div class="accordion-content">
                <p><?php esc_html_e('Use these buttons to manage or troubleshoot CRON jobs.', 'tradepress'); ?></p>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=tradepress_automation&tab=cron&action=run_earnings_calendar'), 'tradepress_run_cron')); ?>" class="button"><?php esc_html_e('Run Earnings Calendar Import Now', 'tradepress'); ?></a>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=tradepress_automation&tab=cron&action=clear_all_tradepress_crons'), 'tradepress_clear_cron')); ?>" class="button"><?php esc_html_e('Clear All TradePress CRON Jobs', 'tradepress'); ?></a>
            </div>
        </div>
    </div>
</div>