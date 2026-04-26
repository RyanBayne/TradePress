<?php
/**
 * TradePress - Scoring Directives Algorithm Configuration Tab
 *
 * This view visualizes the execution flow and structure of the scoring algorithm.
 * It provides a dynamic representation of the algorithm's execution steps, showing
 * which methods are called, in what order, and with what results during execution.
 *
 * The visualization includes:
 * - A flowchart of algorithm execution phases and decision branches
 * - Performance metrics for each step of the algorithm
 * - Visual indicators for active/inactive branches based on current strategies
 * - Real-time data overlays showing the most recent algorithm execution
 * - Decision point visualization to highlight algorithmic branching
 *
 * This is one of the most complex visualizations in the plugin, intended to give users
 * insight into how the scoring system processes data and makes decisions based on
 * configured strategies and directives.
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 1.0.0
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue nonce for AJAX calls
wp_localize_script('jquery', 'tradepressScoringAjax', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('tradepress_automation_nonce')
));
?>
    <div class="tradepress-algorithm-config">
        <div class="algorithm-section">
            <h3><?php esc_html_e('Scoring Algorithm Process', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Control and monitor the background scoring algorithm that processes database data to generate symbol scores.', 'tradepress'); ?></p>
            
            <?php
            // Get scoring process status
            $scoring_status = get_option('tradepress_scoring_process_status', 'stopped');
            $start_time = get_option('tradepress_scoring_process_start_time', 0);
            $last_run = get_option('tradepress_scoring_process_last_run', 0);
            
            // Calculate runtime
            $runtime = '00:00:00';
            if ($scoring_status === 'running' && $start_time) {
                $elapsed = current_time('timestamp') - $start_time;
                $hours = floor($elapsed / 3600);
                $minutes = floor(($elapsed % 3600) / 60);
                $seconds = $elapsed % 60;
                $runtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }
            
            $status_class = ($scoring_status === 'running') ? 'status-running' : 'status-stopped';
            $status_text = ($scoring_status === 'running') ? __('Running', 'tradepress') : __('Stopped', 'tradepress');
            ?>
            
            <div class="scoring-process-card">
                <div class="status-indicator <?php echo esc_attr($status_class); ?>">
                    <span class="status-dot"></span>
                    <span class="status-text"><?php echo esc_html($status_text); ?></span>
                </div>
                
                <div class="runtime-display">
                    <span class="runtime-label"><?php esc_html_e('Runtime:', 'tradepress'); ?></span>
                    <span id="scoring-process-runtime"><?php echo esc_html($runtime); ?></span>
                </div>
                
                <div class="process-actions">
                    <button class="button button-primary start-scoring-process-button" 
                            data-action="<?php echo ($scoring_status === 'running') ? 'stop' : 'start'; ?>">
                        <?php echo ($scoring_status === 'running') ? esc_html__('Stop Scoring Process', 'tradepress') : esc_html__('Start Scoring Process', 'tradepress'); ?>
                    </button>
                    
                    <select id="scoring-process-type-select">
                        <option value="all"><?php esc_html_e('Full Process (Scores + Signals + Rankings)', 'tradepress'); ?></option>
                        <option value="scores"><?php esc_html_e('Calculate Scores Only', 'tradepress'); ?></option>
                        <option value="signals"><?php esc_html_e('Generate Signals Only', 'tradepress'); ?></option>
                        <option value="rankings"><?php esc_html_e('Update Rankings Only', 'tradepress'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="algorithm-section">
            <h3><?php esc_html_e('Processing Metrics', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Real-time statistics from the scoring algorithm background process.', 'tradepress'); ?></p>
            
            <?php
            $symbols_processed = get_option('tradepress_symbols_processed', 0);
            $scores_generated = get_option('tradepress_scores_generated', 0);
            $signals_generated = get_option('tradepress_trade_signals', 0);
            $rankings_last_update = get_option('tradepress_rankings_last_update', 0);
            ?>
            
            <div class="scoring-metrics-grid">
                <div class="metric-item">
                    <span class="metric-label"><?php esc_html_e('Symbols Processed', 'tradepress'); ?></span>
                    <span class="metric-value" id="scoring-symbols-processed"><?php echo esc_html($symbols_processed); ?></span>
                </div>
                
                <div class="metric-item">
                    <span class="metric-label"><?php esc_html_e('Scores Generated', 'tradepress'); ?></span>
                    <span class="metric-value" id="scoring-scores-generated"><?php echo esc_html($scores_generated); ?></span>
                </div>
                
                <div class="metric-item">
                    <span class="metric-label"><?php esc_html_e('Trading Signals', 'tradepress'); ?></span>
                    <span class="metric-value" id="scoring-signals-generated"><?php echo esc_html($signals_generated); ?></span>
                </div>
                
                <div class="metric-item">
                    <span class="metric-label"><?php esc_html_e('Rankings Last Updated', 'tradepress'); ?></span>
                    <span class="metric-value">
                        <?php 
                        if ($rankings_last_update) {
                            echo date('Y-m-d H:i:s', $rankings_last_update);
                        } else {
                            esc_html_e('Never', 'tradepress');
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="algorithm-section">
            <h3><?php esc_html_e('Process Architecture', 'tradepress'); ?></h3>
            <div class="architecture-info">
                <ul>
                    <li><?php esc_html_e('Uses database data only (no API calls)', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Processes data imported by background data import system', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Runs independently of user interface', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Continuous operation until manually stopped', 'tradepress'); ?></li>
                    <li><?php esc_html_e('Queue-based processing with automatic retry', 'tradepress'); ?></li>
                </ul>
            </div>
        </div>
    </div>

