<?php
/**
 * TradePress AI Integration - Directive Analysis System
 *
 * @package TradePress/AI
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_AI_Directive_Analyzer {
    
    /**
     * Analyze directive implementation completeness
     */
    public static function analyze_directive($directive_id) {
        $analysis = array(
            'directive_id' => $directive_id,
            'files' => array(),
            'implementation' => array(),
            'status' => 'unknown',
            'recommendations' => array()
        );
        
        // Check class file
        $class_file = TRADEPRESS_PLUGIN_DIR_PATH . "includes/scoring-system/directives/{$directive_id}.php";
        $analysis['files']['class'] = file_exists($class_file);
        
        // Check config form
        $config_file = TRADEPRESS_PLUGIN_DIR_PATH . "admin/page/scoring-directives/directives-partials/{$directive_id}.php";
        $analysis['files']['config'] = file_exists($config_file);
        
        // Check class implementation
        if ($analysis['files']['class']) {
            require_once $class_file;
            $class_name = 'TradePress_Scoring_Directive_' . str_replace('-', '_', ucwords($directive_id, '-'));
            
            $analysis['implementation']['class_exists'] = class_exists($class_name);
            
            if ($analysis['implementation']['class_exists']) {
                $instance = new $class_name();
                $analysis['implementation']['has_calculate'] = method_exists($instance, 'calculate_score');
                $analysis['implementation']['has_explanation'] = method_exists($instance, 'get_explanation');
                $analysis['implementation']['has_max_score'] = method_exists($instance, 'get_max_score');
            }
        }
        
        // Determine status
        $analysis['status'] = self::determine_status($analysis);
        
        // Generate recommendations
        $analysis['recommendations'] = self::generate_recommendations($analysis);
        
        return $analysis;
    }
    
    /**
     * Determine directive status
     */
    private static function determine_status($analysis) {
        if (!$analysis['files']['class']) {
            return 'missing';
        }
        
        if (!$analysis['implementation']['class_exists']) {
            return 'broken';
        }
        
        $required_methods = array('has_calculate', 'has_explanation', 'has_max_score');
        $has_all_methods = true;
        
        foreach ($required_methods as $method) {
            if (!isset($analysis['implementation'][$method]) || !$analysis['implementation'][$method]) {
                $has_all_methods = false;
                break;
            }
        }
        
        if ($has_all_methods && $analysis['files']['config']) {
            return 'complete';
        } elseif ($has_all_methods) {
            return 'needs_config';
        } else {
            return 'incomplete';
        }
    }
    
    /**
     * Generate recommendations
     */
    private static function generate_recommendations($analysis) {
        $recommendations = array();
        
        if (!$analysis['files']['class']) {
            $recommendations[] = "Create class file: {$analysis['directive_id']}.php";
        }
        
        if (!$analysis['files']['config']) {
            $recommendations[] = "Create config form: {$analysis['directive_id']}.php";
        }
        
        if (isset($analysis['implementation']['class_exists']) && !$analysis['implementation']['class_exists']) {
            $recommendations[] = "Fix class naming or syntax errors";
        }
        
        if (isset($analysis['implementation']['has_calculate']) && !$analysis['implementation']['has_calculate']) {
            $recommendations[] = "Implement calculate_score() method";
        }
        
        if (isset($analysis['implementation']['has_explanation']) && !$analysis['implementation']['has_explanation']) {
            $recommendations[] = "Implement get_explanation() method";
        }
        
        if (isset($analysis['implementation']['has_max_score']) && !$analysis['implementation']['has_max_score']) {
            $recommendations[] = "Implement get_max_score() method";
        }
        
        return $recommendations;
    }
}