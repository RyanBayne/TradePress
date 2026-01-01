<?php
/**
 * TradePress Class Scanner
 * 
 * Scans plugin files to discover and analyze PHP classes
 *
 * @package TradePress/Includes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Class_Scanner {
    
    /**
     * Get all plugin classes
     */
    public static function get_plugin_classes() {
        $classes = array();
        $plugin_dir = TRADEPRESS_PLUGIN_DIR_PATH;
        
        // Get all PHP files
        $files = self::get_php_files($plugin_dir);
        
        foreach ($files as $file) {
            $file_classes = self::extract_classes_from_file($file);
            $classes = array_merge($classes, $file_classes);
        }
        
        return $classes;
    }
    
    /**
     * Get all PHP files recursively
     */
    private static function get_php_files($dir) {
        $files = array();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Extract classes from a PHP file
     */
    private static function extract_classes_from_file($file) {
        $classes = array();
        $content = file_get_contents($file);
        
        // Use token parsing for accurate class detection
        $tokens = token_get_all($content);
        $class_token = false;
        
        for ($i = 0; $i < count($tokens); $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_CLASS) {
                $class_token = true;
                continue;
            }
            
            if ($class_token && is_array($tokens[$i]) && $tokens[$i][0] === T_STRING) {
                $class_name = $tokens[$i][1];
                $classes[] = array(
                    'name' => $class_name,
                    'file' => str_replace(TRADEPRESS_PLUGIN_DIR_PATH, '', $file),
                    'full_path' => $file,
                    'methods' => self::get_class_methods($class_name, $file),
                    'description' => self::get_class_description($content, $class_name)
                );
                $class_token = false;
            }
        }
        
        return $classes;
    }
    
    /**
     * Get class methods using reflection
     */
    private static function get_class_methods($class_name, $file) {
        $methods = array();
        
        // Include the file to make class available
        if (file_exists($file)) {
            include_once $file;
        }
        
        if (class_exists($class_name)) {
            try {
                $reflection = new ReflectionClass($class_name);
                $class_methods = $reflection->getMethods();
                
                foreach ($class_methods as $method) {
                    if ($method->getDeclaringClass()->getName() === $class_name) {
                        $methods[] = array(
                            'name' => $method->getName(),
                            'visibility' => $method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private'),
                            'static' => $method->isStatic(),
                            'parameters' => self::get_method_parameters($method),
                            'docblock' => $method->getDocComment()
                        );
                    }
                }
            } catch (Exception $e) {
                // Class couldn't be reflected
            }
        }
        
        return $methods;
    }
    
    /**
     * Get method parameters
     */
    private static function get_method_parameters($method) {
        $parameters = array();
        
        foreach ($method->getParameters() as $param) {
            $parameters[] = array(
                'name' => $param->getName(),
                'optional' => $param->isOptional(),
                'default' => $param->isOptional() && $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
            );
        }
        
        return $parameters;
    }
    
    /**
     * Extract class description from docblock
     */
    private static function get_class_description($content, $class_name) {
        // Look for docblock before class declaration
        $pattern = '/\/\*\*.*?\*\/\s*(?:final\s+|abstract\s+)?class\s+' . preg_quote($class_name) . '/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $docblock = $matches[0];
            // Extract description from docblock
            if (preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)(?:\n\s*\*\s*@|\n\s*\*\/)/s', $docblock, $desc_matches)) {
                return trim(str_replace('*', '', $desc_matches[1]));
            }
        }
        
        return 'No description available';
    }
}