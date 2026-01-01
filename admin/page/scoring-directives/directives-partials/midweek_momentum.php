<?php
/**
 * Midweek Momentum Directive Configuration
 * D38 - Scores Tuesday-Thursday volume and volatility strength patterns
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="directive-config-container" data-directive="midweek_momentum">
    <div class="directive-header">
        <h4>Midweek Momentum (D38)</h4>
        <span class="directive-category">Temporal Patterns</span>
    </div>
    
    <div class="directive-description">
        <p>Analyzes Tuesday-Thursday institutional trading patterns when professional traders are most active.</p>
    </div>
    
    <div class="directive-settings">
        <div class="setting-group">
            <label>Volume Threshold</label>
            <select name="volume_threshold">
                <option value="1.2">20% above average</option>
                <option value="1.5" selected>50% above average</option>
                <option value="2.0">100% above average</option>
            </select>
        </div>
        
        <div class="setting-group">
            <label>Analysis Period</label>
            <select name="analysis_period">
                <option value="14">2 weeks</option>
                <option value="28" selected>4 weeks</option>
                <option value="56">8 weeks</option>
            </select>
        </div>
    </div>
    
    <div class="directive-tips">
        <h5>Trading Tips</h5>
        <ul>
            <li>Most effective for large-cap stocks (AAPL, MSFT) where institutions dominate</li>
            <li>Strong midweek volume often indicates institutional accumulation</li>
            <li>Avoid during holiday-shortened weeks</li>
            <li>Combine with other momentum indicators for confirmation</li>
        </ul>
    </div>
    
    <div class="directive-indicators">
        <div class="indicator positive">
            <span class="indicator-label">Strong Signal:</span>
            <span>Midweek volume >50% above average + positive momentum</span>
        </div>
        <div class="indicator neutral">
            <span class="indicator-label">Moderate Signal:</span>
            <span>Midweek volume >20% above average</span>
        </div>
        <div class="indicator negative">
            <span class="indicator-label">Weak Signal:</span>
            <span>Below-average midweek activity</span>
        </div>
    </div>
</div>