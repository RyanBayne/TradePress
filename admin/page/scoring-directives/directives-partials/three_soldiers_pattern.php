<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
$directive = $all_directives['three_soldiers_pattern'];
?>
<div class="directive-section">
<div class="section-header">
<h3><?php echo esc_html( $directive['name'] ); ?></h3>
</div>
<div class="section-content">
<p><?php echo esc_html( $directive['description'] ); ?></p>
<div class="setting-group">
<label><?php esc_html_e( 'Weight:', 'tradepress' ); ?></label>
<div class="setting-control">
<input type="number" value="<?php echo esc_attr( $directive['weight'] ?? 16 ); ?>" min="0" max="100">
</div>
</div>
</div>
</div>
