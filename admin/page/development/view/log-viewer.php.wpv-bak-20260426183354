<?php
/**
 * TradePress Sandbox - Log Viewer Tab
 *
 * @package TradePress
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$log_directory = TRADEPRESS_PLUGIN_DIR_PATH . 'logs/';
$log_files = glob($log_directory . '*.log');

?>
<div class="sandbox-section">
    <h2>Log Viewer</h2>
    
    <?php if (empty($log_files)) : ?>
        <div class="notice notice-info inline">
            <p>No log files found.</p>
        </div>
    <?php else : ?>
        <div class="sandbox-panel">
            <label for="log-file-select">Select Log File:</label>
            <select id="log-file-select">
                <?php foreach ($log_files as $log_file) : ?>
                    <option value="<?php echo basename($log_file); ?>"><?php echo basename($log_file); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="button" id="load-log-file">Load Log</button>
        </div>
        
        <div id="log-content">
            <div class="notice notice-info inline">
                <p>Select a log file above and click "Load Log" to view its contents.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
