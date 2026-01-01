<?php
/**
 * Setup Wizard - Folders Step
 */

if (!defined('ABSPATH')) {
    exit;
}

$upload_dir = wp_upload_dir();

// Define items to check
$items_to_check = array(
    array(
        'type' => 'Folder',
        'path' => $upload_dir['basedir'] . '/tradepress_uploads',
        'description' => 'Main uploads directory for TradePress files'
    )
);
?>

<h1><?php _e('Folders &amp; Files', 'tradepress'); ?></h1>

<p><?php _e('These are the folders and files that TradePress needs to function properly. Status indicators show whether each item exists.', 'tradepress'); ?></p>

<form method="post">
    <table class="tradepress-setup-extensions" cellspacing="0">
        <thead>
            <tr>
                <th class="extension-name"><?php _e('Status', 'tradepress'); ?></th>
                <th class="extension-name"><?php _e('Type', 'tradepress'); ?></th>
                <th class="extension-description"><?php _e('Path', 'tradepress'); ?></th>
                <th class="extension-description"><?php _e('Description', 'tradepress'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items_to_check as $item) : 
                $exists = file_exists($item['path']);
                $status_icon = $exists ? '✅' : '❌';
                $status_text = $exists ? __('Exists', 'tradepress') : __('Missing', 'tradepress');
                $status_class = $exists ? 'status-success' : 'status-error';
            ?>
            <tr>
                <td class="status-cell <?php echo $status_class; ?>">
                    <span class="status-icon"><?php echo $status_icon; ?></span>
                    <span class="status-text"><?php echo $status_text; ?></span>
                </td>
                <td class="type-cell"><?php echo esc_html($item['type']); ?></td>
                <td class="path-cell"><code><?php echo esc_html($item['path']); ?></code></td>
                <td class="description-cell"><?php echo esc_html($item['description']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p class="tradepress-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Create Missing Items & Continue', 'tradepress'); ?>" name="save_step" />
        <?php wp_nonce_field('tradepress-setup'); ?>
    </p>
</form>

<style>
.status-cell {
    text-align: center;
    font-weight: 600;
}
.status-success {
    color: #28a745;
}
.status-error {
    color: #dc3545;
}
.status-icon {
    font-size: 16px;
    margin-right: 5px;
}
.path-cell code {
    background: var(--tp-bg-tertiary);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
}
.type-cell {
    font-weight: 500;
}
.description-cell {
    color: var(--tp-text-secondary);
    font-size: 13px;
}
</style>