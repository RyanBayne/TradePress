<?php
/**
 * Directive Configuration Handler
 */
class TradePress_Directive_Handler {
    

    
    public static function save_configuration($directive_id, $data) {
        // Load freshness manager if not already loaded
        if (!class_exists('TradePress_Data_Freshness_Manager')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/data-freshness-manager.php';
        }
        error_log('Handler: Starting save for ' . $directive_id);
        
        if (!wp_verify_nonce($_POST['save_nonce'], 'tradepress_save_directive') || !current_user_can('manage_options')) {
            error_log('Handler: Security check failed');
            return array('success' => false, 'message' => 'Security check failed.');
        }
        
        error_log('Handler: Security check passed');
        
        // Get current values for comparison
        $option_key = 'tradepress_directive_' . $directive_id;
        $current_data = get_option($option_key, array());
        
        $validated_data = self::validate_data($directive_id, $data);
        if ($validated_data === false) {
            return array('success' => false, 'message' => 'Validation failed.');
        }
        
        // Track changes and generate warnings
        $changes = self::track_changes($directive_id, $current_data, $validated_data);
        $warnings = self::generate_warnings($directive_id, $validated_data);
        
        update_option($option_key, $validated_data);
        
        return array(
            'success' => true,
            'changes' => $changes,
            'warnings' => $warnings
        );
    }
    
    public static function validate_data($directive_id, $data) {
        // Load schema
        if (!class_exists('TradePress_Directive_Config_Schema')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directive-config-schema.php';
        }
        
        $validated = array();
        $validated['weight'] = self::validate_range($data['weight'] ?? 10, 0, 100);
        
        $fields = TradePress_Directive_Config_Schema::get_directive_fields($directive_id);
        foreach ($fields as $field_name => $field_config) {
            $value = $data[$field_name] ?? $field_config['default'];
            $validated[$field_name] = TradePress_Directive_Config_Schema::validate_field($directive_id, $field_name, $value);
        }
        
        return $validated;
    }
    
    public static function track_changes($directive_id, $old_data, $new_data) {
        $changes = array();
        $field_names = self::get_field_names($directive_id);
        
        foreach ($new_data as $key => $new_value) {
            $old_value = $old_data[$key] ?? null;
            if ($old_value !== $new_value) {
                $field_name = $field_names[$key] ?? ucfirst(str_replace('_', ' ', $key));
                $changes[] = sprintf('%s: %s → %s', $field_name, $old_value ?? 'default', $new_value);
            }
        }
        
        return $changes;
    }
    
    public static function generate_warnings($directive_id, $data) {
        $warnings = array();
        
        switch ($directive_id) {
            case 'rsi':
                if ($data['oversold'] > 25) {
                    $warnings[] = 'RSI Oversold threshold above 25 may miss good buying opportunities.';
                }
                if ($data['overbought'] < 75) {
                    $warnings[] = 'RSI Overbought threshold below 75 may generate false sell signals in strong trends.';
                }
                if ($data['period'] < 10) {
                    $warnings[] = 'RSI period below 10 may be too sensitive and generate false signals.';
                }
                if ($data['weight'] > 50) {
                    $warnings[] = 'High weight (>50) may cause RSI to dominate other indicators.';
                }
                break;
            case 'isa':
                if ($data['days_before'] > 14) {
                    $warnings[] = 'ISA period longer than 14 days may dilute the seasonal effect.';
                }
                if ($data['score_impact'] > 25) {
                    $warnings[] = 'High score impact (>25) may cause excessive bias during ISA period.';
                }
                break;
            case 'support_resistance_levels':
                if ($data['proximity_percent'] > 3.0) {
                    $warnings[] = 'High proximity percentage (>3%) may group unrelated levels together.';
                }
                if ($data['highly_overlapped_min_methods'] < 3) {
                    $warnings[] = 'Low high confluence minimum (<3) may weaken level significance.';
                }
                if ($data['fib_lookback_days'] < 60) {
                    $warnings[] = 'Short Fibonacci lookback (<60 days) may miss important levels.';
                }
                break;
        }
        
        return $warnings;
    }
    
    public static function get_field_names($directive_id) {
        $names = array(
            'weight' => 'Weight',
            'period' => 'RSI Period',
            'oversold' => 'Oversold Threshold',
            'overbought' => 'Overbought Threshold',
            'days_before' => 'Days Before Reset',
            'days_after' => 'Days After Reset',
            'score_impact' => 'Score Impact',
            'proximity_percent' => 'Proximity Percentage',
            'highly_overlapped_min_methods' => 'High Confluence Minimum',
            'well_overlapped_min_methods' => 'Medium Confluence Minimum',
            'fib_lookback_days' => 'Fibonacci Lookback Days',
            'swing_lookback' => 'Swing Lookback Period'
        );
        
        return $names;
    }
    
    public static function validate_range($value, $min, $max) {
        $value = intval($value);
        return max($min, min($max, $value));
    }
    
    public static function test_directive($directive_id, $symbol = null, $trading_mode = 'long', $directive_code = '') {
        // Get symbol from settings if not provided
        if ($symbol === null) {
            if (!class_exists('TradePress_AI_Directive_Tester')) {
                require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-tester.php';
            }
            $symbol = TradePress_AI_Directive_Tester::get_test_symbol('AAPL');
        }
        // Use directive-specific handler if available
        $handler_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/' . $directive_id . '/' . $directive_id . '-handler.php';
        if (file_exists($handler_file)) {
            require_once $handler_file;
            $handler_class = 'TradePress_' . ucfirst($directive_id) . '_Handler';
            if (class_exists($handler_class) && method_exists($handler_class, 'test_directive')) {
                return $handler_class::test_directive($symbol, $trading_mode);
            }
        }
        
        // Fallback to generic handler (legacy RSI-based)
        // Load required classes
        if (!class_exists('TradePress_Install_Tables')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/installation/tables-installation.php';
        }
        if (!class_exists('TradePress_Technical_Indicators')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-tradepress-technical-indicators.php';
        }
        
        // Check if tables exist before attempting to create them
        global $wpdb;
        $symbols_table = $wpdb->prefix . 'tradepress_symbols';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $symbols_table));
        
        if (!$table_exists) {
            // Only create tables if they don't exist
            $installer = new TradePress_Install_Tables();
            $table_result = $installer->create_symbol_tables();
            TradePress_Developer_Notices::database_notice('CREATE', 'symbol_tables', array('action' => 'create_missing_tables'), $table_result);
        }
        
        // Load Call Register
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        // For RSI directive, skip quote data - directive handles everything
        $quote_data = array('price' => 0); // Default for directives that don't need quote data
        
        if ($directive_id !== 'rsi') {
            // Check Call Register for quote data first - avoid API calls within 10 minutes
            $cached_result = TradePress_Call_Register::get_cached_result('alphavantage', 'get_quote', array('symbol' => $symbol), 10);
            
            if ($cached_result !== false) {
                // Use cached data from Call Register
                $quote_data = $cached_result;
                TradePress_Developer_Notices::api_call_notice('call_register', 'get_quote', $symbol, array('cached' => true, 'data' => $quote_data));
            } else {
                // Make fresh API call with fallback
                $api = TradePress_API_Factory::create_from_settings(null, 'paper', 'quote');
                if (is_wp_error($api)) {
                    TradePress_Developer_Notices::api_call_notice('api_factory', 'factory_create', 'N/A', $api);
                    return array(
                        'success' => false,
                        'message' => 'No available API for quote data: ' . $api->get_error_message()
                    );
                }
                
                $quote_data = $api->get_quote($symbol);
                TradePress_Developer_Notices::api_call_notice($api->get_provider_id(), 'get_quote', $symbol, $quote_data);
                
                if (is_wp_error($quote_data)) {
                    return array(
                        'success' => false,
                        'message' => 'API call failed: ' . $quote_data->get_error_message()
                    );
                }
                
                // Only cache successful results with valid price data
                if (!is_wp_error($quote_data) && isset($quote_data['price']) && $quote_data['price'] > 0) {
                    $quote_serial = TradePress_Call_Register::cache_result($api->get_provider_id(), 'get_quote', array('symbol' => $symbol), $quote_data, 10);
                } else {
                    // Don't cache invalid data
                    $quote_data = array('price' => 0);
                }
                
                // Store serial number for API calls display
                self::store_api_serial($directive_id, $quote_serial, $api->get_provider_id(), 'get_quote', array('symbol' => $symbol));
            }
        }
        
        $config = get_option('tradepress_directive_' . $directive_id, array());
        TradePress_Developer_Notices::database_notice('SELECT', 'wp_options', array('option_name' => 'tradepress_directive_' . $directive_id), !empty($config));
        
        // Use configuration settings instead of hardcoded values
        $rsi_period = $config['period'] ?? 14;
        $oversold_threshold = $config['oversold'] ?? 30;
        $overbought_threshold = $config['overbought'] ?? 70;
        
        // Ensure API instance is available for all directives
        if (!isset($api)) {
            $api = TradePress_API_Factory::create_from_settings(null, 'paper', 'technical_indicators');
            if (is_wp_error($api)) {
                return array(
                    'success' => false,
                    'message' => 'No API available for technical indicators: ' . $api->get_error_message()
                );
            }
        }
        
        // Skip RSI processing for non-RSI directives
        if ($directive_id !== 'rsi' && $directive_id !== 'price_action') {
            $rsi = null;
        }
        
        // Don't cache failed API results that return null or error data
        $rsi = null;
        if ($directive_id === 'price_action') {
            // Only price_action directive needs RSI data from handler
            if ($cached_rsi !== false) {
                $rsi_data = $cached_rsi;
                TradePress_Developer_Notices::api_call_notice('call_register', 'rsi', $symbol, array('cached' => true, 'data' => $rsi_data));
            } else {
                $rsi_data = $api->make_request('RSI', array(
                    'symbol' => $symbol,
                    'interval' => 'daily',
                    'time_period' => (string)$rsi_period,
                    'series_type' => 'close'
                ));
                
                TradePress_Developer_Notices::api_call_notice('alphavantage', 'rsi', $symbol, $rsi_data);
                
                if (is_wp_error($rsi_data)) {
                    return array(
                        'success' => false,
                        'message' => 'RSI API call failed: ' . $rsi_data->get_error_message()
                    );
                }
                
                // Cache the RSI result for 1 hour
                $rsi_serial = TradePress_Call_Register::cache_result('alphavantage', 'rsi', array('symbol' => $symbol), $rsi_data, 60);
                
                // Store serial number for API calls display
                self::store_api_serial($directive_id, $rsi_serial, 'alphavantage', 'RSI', array('symbol' => $symbol, 'period' => $rsi_period));
            }
            
            // Extract the most recent RSI value
            if (isset($rsi_data['Technical Analysis: RSI'])) {
                $rsi_values = $rsi_data['Technical Analysis: RSI'];
                $latest_date = array_keys($rsi_values)[0]; // Get most recent date
                $rsi = (float) $rsi_values[$latest_date]['RSI'];
            }
            
            if ($rsi === null) {
                return array(
                    'success' => false,
                    'message' => 'No RSI data available from API - LIVE process halted'
                );
            }
        }
        
        // Set default RSI for non-RSI directives
        if ($rsi === null) {
            $rsi = 50; // Neutral RSI for display purposes
        }
        
        // Store RSI data in database for historical analysis
        global $wpdb;
        $symbol_meta_table = $wpdb->prefix . 'tradepress_symbol_meta';
        
        // Get or create symbol ID
        $symbols_table = $wpdb->prefix . 'tradepress_symbols';
        $symbol_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$symbols_table} WHERE symbol = %s",
            $symbol
        ));
        
        if (!$symbol_id) {
            // Create symbol record if it doesn't exist
            $wpdb->insert($symbols_table, array(
                'symbol' => $symbol,
                'name' => $symbol . ' Corporation',
                'current_price' => $quote_data['price'] ?? 0,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ));
            $symbol_id = $wpdb->insert_id;
        }
        
        // Store RSI data with configuration used
        $rsi_meta = array(
            'rsi_value' => $rsi,
            'period' => $rsi_period,
            'oversold_threshold' => $oversold_threshold,
            'overbought_threshold' => $overbought_threshold,
            'signal' => $rsi < $oversold_threshold ? 'Buy' : ($rsi > $overbought_threshold ? 'Sell' : 'Hold'),
            'data_source' => 'live_api'
        );
        
        $wpdb->insert($symbol_meta_table, array(
            'symbol_id' => $symbol_id,
            'meta_key' => 'rsi_test_data',
            'meta_value' => serialize($rsi_meta),
            'source' => 'directive_test',
            'updated_at' => current_time('mysql')
        ));
        
        TradePress_Developer_Notices::database_notice('INSERT', $symbol_meta_table, array('symbol_id' => $symbol_id, 'meta_key' => 'rsi_test_data'), $wpdb->insert_id > 0);
        
        // Calculate actual scores using appropriate directive
        if ($directive_id === 'support_resistance_levels') {
            $directive_class = 'SupportResistanceLevels';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/support-resistance-levels.php';
        } elseif ($directive_id === 'isa_reset') {
            $directive_class = 'TradePress_Scoring_Directive_ISA_RESET';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/isa-reset.php';
        } elseif ($directive_id === 'bollinger_bands') {
            $directive_class = 'TradePress_Scoring_Directive_BOLLINGER_BANDS';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/bollinger-bands.php';
        } elseif ($directive_id === 'adx') {
            $directive_class = 'TradePress_Scoring_Directive_ADX';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/adx.php';
        } elseif ($directive_id === 'cci') {
            $directive_class = 'TradePress_Scoring_Directive_CCI';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/cci.php';
        } elseif ($directive_id === 'ema') {
            $directive_class = 'TradePress_Scoring_Directive_EMA';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/ema.php';
        } elseif ($directive_id === 'macd') {
            $directive_class = 'TradePress_Scoring_Directive_MACD';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/macd.php';
        } elseif ($directive_id === 'mfi') {
            $directive_class = 'TradePress_Scoring_Directive_MFI';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/mfi.php';
        } elseif ($directive_id === 'moving_averages') {
            $directive_class = 'TradePress_Scoring_Directive_MOVING_AVERAGES';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/moving-averages.php';
        } elseif ($directive_id === 'news_sentiment_positive') {
            $directive_class = 'TradePress_News_Sentiment_Positive_Directive';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/news-sentiment-positive.php';
        } elseif ($directive_id === 'obv') {
            $directive_class = 'TradePress_Scoring_Directive_OBV';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/obv.php';
        } elseif ($directive_id === 'price_above_sma_50') {
            $directive_class = 'TradePress_Scoring_Directive_PRICE_ABOVE_SMA_50';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/price-above-sma-50.php';
        } elseif ($directive_id === 'rsi') {
            $directive_class = 'TradePress_Scoring_Directive_RSI';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/rsi.php';
        } elseif ($directive_id === 'rsi_overbought') {
            $directive_class = 'TradePress_Scoring_Directive_RSI_OVERBOUGHT';
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/rsi-overbought.php';
        } elseif ($directive_id === 'dividend_yield_attractive') {
            // D5 - This directive doesn't exist yet, return mock result
            return array(
                'success' => false,
                'message' => 'Dividend Yield directive not implemented yet - placeholder for alpha release',
                'directive_code' => $directive_code
            );
        } else {
            $directive_class = 'TradePress_Scoring_Directive_' . strtoupper($directive_id);
            $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/' . str_replace('_', '-', $directive_id) . '.php';
        }
        
        if (file_exists($directive_file)) {
            require_once $directive_file;
        }
        
        if (!class_exists($directive_class)) {
            return array('success' => false, 'message' => 'Directive class not found: ' . $directive_class, 'directive_code' => self::get_directive_code($directive_id));
        }
        
        if ($directive_id === 'support_resistance_levels') {
            // D21 requires API service - use existing API instance
            $directive_instance = new $directive_class($symbol, $api, $config);
        } else {
            $directive_instance = new $directive_class();
        }
        
        // Prepare symbol data based on directive type
        if ($directive_id === 'rsi') {
            $symbol_data = array(
                'symbol' => $symbol,
                'technical' => array('rsi' => $rsi)
            );
        } elseif ($directive_id === 'volume') {
            // Volume directive will fetch its own data - just pass symbol info
            $symbol_data = array(
                'symbol' => $symbol
            );
        } elseif ($directive_id === 'support_resistance_levels') {
            $symbol_data = array(
                'price' => $quote_data['price'] ?? 0,
                'symbol' => $symbol,
                'limited_data' => true // Flag for basic scoring
            );
        } elseif ($directive_id === 'bollinger_bands') {
            // Bollinger Bands directive will fetch its own data - just pass symbol info
            $symbol_data = array(
                'symbol' => $symbol,
                'price' => $quote_data['price'] ?? 0
            );
        } elseif ($directive_id === 'adx') {
            // ADX directive will fetch its own data - just pass symbol info
            $symbol_data = array(
                'symbol' => $symbol,
                'price' => $quote_data['price'] ?? 0
            );
        } elseif ($directive_id === 'cci') {
            // CCI directive handles its own data fetching - just pass symbol info
            $symbol_data = array(
                'symbol' => $symbol,
                'price' => $quote_data['price'] ?? 0
            );
        } elseif ($directive_id === 'ema') {
            // EMA directive will fetch its own data - just pass symbol info
            $symbol_data = array(
                'symbol' => $symbol,
                'price' => $quote_data['price'] ?? 0
            );
        } elseif ($directive_id === 'macd') {
            // MACD directive handles its own data fetching - just pass symbol info
            $symbol_data = array(
                'symbol' => $symbol,
                'price' => $quote_data['price'] ?? 0
            );
        } elseif ($directive_id === 'mfi') {
            // Get MFI data from API
            $mfi_data = $api->make_request('MFI', array(
                'symbol' => $symbol,
                'interval' => 'daily',
                'time_period' => (string)($config['period'] ?? 14)
            ));
            
            TradePress_Developer_Notices::api_call_notice('alphavantage', 'mfi', $symbol, $mfi_data);
            
            if (is_wp_error($mfi_data)) {
                return array(
                    'success' => false,
                    'message' => 'MFI API call failed: ' . $mfi_data->get_error_message()
                );
            }
            
            // Extract latest MFI value
            $mfi_value = null;
            if (isset($mfi_data['Technical Analysis: MFI'])) {
                $mfi_analysis = $mfi_data['Technical Analysis: MFI'];
                $latest_date = array_keys($mfi_analysis)[0];
                $mfi_value = (float) $mfi_analysis[$latest_date]['MFI'];
            }
            
            if ($mfi_value === null) {
                return array(
                    'success' => false,
                    'message' => 'No MFI data available from API'
                );
            }
            
            $symbol_data = array(
                'price' => $quote_data['price'] ?? 0,
                'technical' => array('mfi' => $mfi_value)
            );
        } elseif ($directive_id === 'news_sentiment_positive') {
            // News sentiment doesn't require additional API data - uses mock data
            $symbol_data = array(
                'symbol' => $symbol,
                'price' => $quote_data['price'] ?? 0
            );
        } elseif ($directive_id === 'obv') {
            // Get OBV data from API
            $obv_data = $api->make_request('OBV', array(
                'symbol' => $symbol,
                'interval' => 'daily'
            ));
            
            TradePress_Developer_Notices::api_call_notice('alphavantage', 'obv', $symbol, $obv_data);
            
            if (is_wp_error($obv_data)) {
                return array(
                    'success' => false,
                    'message' => 'OBV API call failed: ' . $obv_data->get_error_message()
                );
            }
            
            // Extract latest OBV value
            $obv_value = null;
            if (isset($obv_data['Technical Analysis: OBV'])) {
                $obv_analysis = $obv_data['Technical Analysis: OBV'];
                $latest_date = array_keys($obv_analysis)[0];
                $obv_value = (float) $obv_analysis[$latest_date]['OBV'];
            }
            
            if ($obv_value === null) {
                return array(
                    'success' => false,
                    'message' => 'No OBV data available from API'
                );
            }
            
            $symbol_data = array(
                'price' => $quote_data['price'] ?? 0,
                'volume_ratio' => 1.5, // Mock volume ratio
                'technical' => array('obv' => $obv_value)
            );
        } elseif ($directive_id === 'price_above_sma_50') {
            // Get SMA data from API
            $sma_data = $api->make_request('SMA', array(
                'symbol' => $symbol,
                'interval' => 'daily',
                'time_period' => (string)($config['sma_period'] ?? 50),
                'series_type' => 'close'
            ));
            
            TradePress_Developer_Notices::api_call_notice('alphavantage', 'sma', $symbol, $sma_data);
            
            if (is_wp_error($sma_data)) {
                return array(
                    'success' => false,
                    'message' => 'SMA API call failed: ' . $sma_data->get_error_message()
                );
            }
            
            // Extract latest SMA value
            $sma_value = null;
            if (isset($sma_data['Technical Analysis: SMA'])) {
                $sma_analysis = $sma_data['Technical Analysis: SMA'];
                $latest_date = array_keys($sma_analysis)[0];
                $sma_value = (float) $sma_analysis[$latest_date]['SMA'];
            }
            
            if ($sma_value === null) {
                return array(
                    'success' => false,
                    'message' => 'No SMA data available from API'
                );
            }
            
            $symbol_data = array(
                'price' => $quote_data['price'] ?? 0,
                'technical' => array('sma' => $sma_value)
            );
        } elseif ($directive_id === 'moving_averages') {
            // Get both short and long MA data from API
            $ma_type = $config['ma_type'] ?? 'SMA';
            $short_period = $config['short_period'] ?? 20;
            $long_period = $config['long_period'] ?? 50;
            
            $short_ma_data = $api->make_request($ma_type, array(
                'symbol' => $symbol,
                'interval' => 'daily',
                'time_period' => (string)$short_period,
                'series_type' => 'close'
            ));
            
            $long_ma_data = $api->make_request($ma_type, array(
                'symbol' => $symbol,
                'interval' => 'daily',
                'time_period' => (string)$long_period,
                'series_type' => 'close'
            ));
            
            TradePress_Developer_Notices::api_call_notice('alphavantage', strtolower($ma_type), $symbol, $short_ma_data);
            
            if (is_wp_error($short_ma_data) || is_wp_error($long_ma_data)) {
                return array(
                    'success' => false,
                    'message' => 'Moving Averages API call failed'
                );
            }
            
            // Extract latest MA values
            $ma_values = null;
            $ma_key = 'Technical Analysis: ' . $ma_type;
            
            if (isset($short_ma_data[$ma_key]) && isset($long_ma_data[$ma_key])) {
                $short_analysis = $short_ma_data[$ma_key];
                $long_analysis = $long_ma_data[$ma_key];
                
                $latest_date = array_keys($short_analysis)[0];
                $ma_values = array(
                    'short_ma' => (float) $short_analysis[$latest_date][$ma_type],
                    'long_ma' => (float) $long_analysis[$latest_date][$ma_type]
                );
            }
            
            if (!$ma_values) {
                return array(
                    'success' => false,
                    'message' => 'No Moving Averages data available from API'
                );
            }
            
            $symbol_data = array(
                'price' => $quote_data['price'] ?? 0,
                'technical' => array('moving_averages' => $ma_values)
            );
        } elseif ($directive_id === 'price_action') {
            // Get moving averages from API
            $ma_20_data = $api->make_request('SMA', array(
                'symbol' => $symbol,
                'interval' => 'daily',
                'time_period' => '20',
                'series_type' => 'close'
            ));
            
            $ma_50_data = $api->make_request('SMA', array(
                'symbol' => $symbol,
                'interval' => 'daily',
                'time_period' => '50',
                'series_type' => 'close'
            ));
            
            $ma_200_data = $api->make_request('SMA', array(
                'symbol' => $symbol,
                'interval' => 'daily',
                'time_period' => '200',
                'series_type' => 'close'
            ));
            
            // Extract latest MA values
            $ma_20 = 0;
            $ma_50 = 0;
            $ma_200 = 0;
            
            if (isset($ma_20_data['Technical Analysis: SMA'])) {
                $ma_20_values = $ma_20_data['Technical Analysis: SMA'];
                $latest_date = array_keys($ma_20_values)[0];
                $ma_20 = (float) $ma_20_values[$latest_date]['SMA'];
            }
            
            if (isset($ma_50_data['Technical Analysis: SMA'])) {
                $ma_50_values = $ma_50_data['Technical Analysis: SMA'];
                $latest_date = array_keys($ma_50_values)[0];
                $ma_50 = (float) $ma_50_values[$latest_date]['SMA'];
            }
            
            if (isset($ma_200_data['Technical Analysis: SMA'])) {
                $ma_200_values = $ma_200_data['Technical Analysis: SMA'];
                $latest_date = array_keys($ma_200_values)[0];
                $ma_200 = (float) $ma_200_values[$latest_date]['SMA'];
            }
            
            $symbol_data = array(
                'price' => $quote_data['price'] ?? 0,
                'technical' => array(
                    'ma_20' => $ma_20,
                    'ma_50' => $ma_50,
                    'ma_200' => $ma_200
                )
            );
        } else {
            $symbol_data = array('price' => $quote_data['price'] ?? 0, 'technical' => array('rsi' => $rsi));
        }
        
        $scores = array();
        if ($trading_mode === 'both') {
            $both_result = $directive_instance->calculate_score($symbol_data, 'both');
            if (is_array($both_result)) {
                $scores = $both_result;
            } else {
                $scores['long'] = $both_result;
                $scores['short'] = $both_result;
            }
        } else {
            $result = $directive_instance->calculate_score($symbol_data, $config);
            if (is_array($result) && isset($result['score'])) {
                // ISA directive returns array with 'score' key
                $scores[$trading_mode] = $result['score'];
            } elseif (is_array($result) && isset($result[$trading_mode])) {
                // Standard directive with trading mode keys
                $scores[$trading_mode] = $result[$trading_mode];
            } else {
                // Simple numeric result - handle null as calculation failure
                if ($result === null) {
                    return array(
                        'success' => false,
                        'message' => 'Score could not be calculated - insufficient data or API error',
                        'directive_code' => self::get_directive_code($directive_id)
                    );
                }
                $scores[$trading_mode] = is_numeric($result) ? $result : 0;
            }
        }
        
        // Log scoring test with configuration
        TradePress_Logging_Helper::log_scoring('RSI Test Strategy', 'test_' . $directive_id, array(
            'symbol' => $symbol,
            'rsi' => round($rsi, 2),
            'period' => $rsi_period,
            'signal' => $rsi < $oversold_threshold ? 'Buy' : ($rsi > $overbought_threshold ? 'Sell' : 'Hold'),
            'data_source' => 'live_api',
            'config_used' => $config,
            'trading_mode' => $trading_mode,
            'scores' => $scores
        ));
        
        // Prepare test data based on directive type
        $test_data = array(
            'symbol' => $symbol,
            'current_price' => $quote_data['price'] ?? 0,
            'config' => $config,
            'stored_in_db' => true,
            'trading_mode' => $trading_mode,
            'scores' => $scores
        );
        
        if ($directive_id === 'volume') {
            $test_data['volume'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? ($scores[$trading_mode]['volume'] ?? 0) : 0;
            $test_data['avg_volume'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? ($scores[$trading_mode]['avg_volume'] ?? 0) : 0;
            $test_data['volume_ratio'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['volume_ratio'] ?? 0, 2) : 0;
            $test_data['volume_signal'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? ($scores[$trading_mode]['signal'] ?? 'No signal') : 'No signal';
        } elseif ($directive_id === 'bollinger_bands') {
            $test_data['upper_band'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['upper_band'] ?? 0, 2) : 0;
            $test_data['middle_band'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['middle_band'] ?? 0, 2) : 0;
            $test_data['lower_band'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['lower_band'] ?? 0, 2) : 0;
            $test_data['band_position'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['band_position'] . '%' : 'N/A';
            $test_data['bb_signal'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['signal'] : 'No signal';
        } elseif ($directive_id === 'adx') {
            // ADX directive fetches its own data, so get values from scores array
            $test_data['adx_value'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['adx_value'] ?? 0, 2) : 0;
            $test_data['plus_di'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['plus_di'] ?? 0, 2) : 0;
            $test_data['minus_di'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['minus_di'] ?? 0, 2) : 0;
            $test_data['trend_strength'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['trend_strength'] : 'N/A';
            $test_data['adx_signal'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['signal'] : 'No signal';
        } elseif ($directive_id === 'cci') {
            $test_data['cci_value'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['cci_value'] ?? 0, 2) : 0;
            $test_data['cci_condition'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['condition'] : 'N/A';
            $test_data['cci_signal'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['signal'] : 'No signal';
        } elseif ($directive_id === 'ema') {
            $test_data['ema_value'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['ema_value'] ?? 0, 2) : 0;
            $test_data['distance_percent'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['distance_percent'] . '%' : 'N/A';
            $test_data['trend'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['trend'] : 'N/A';
            $test_data['ema_signal'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['signal'] : 'No signal';
        } elseif ($directive_id === 'macd') {
            $test_data['macd_line'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['macd_line'] ?? 0, 4) : 0;
            $test_data['signal_line'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['signal_line'] ?? 0, 4) : 0;
            $test_data['histogram'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? round($scores[$trading_mode]['histogram'] ?? 0, 4) : 0;
            $test_data['crossover_type'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['crossover_type'] : 'N/A';
            $test_data['macd_signal'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['signal'] : 'No signal';
        } elseif ($directive_id === 'mfi') {
            $test_data['mfi_value'] = round($symbol_data['technical']['mfi'], 2);
            $test_data['mfi_condition'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['condition'] : 'N/A';
            $test_data['volume_weighted'] = 'Yes';
            $test_data['mfi_signal'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['signal'] : 'No signal';
        } elseif ($directive_id === 'moving_averages') {
            $ma_data = $symbol_data['technical']['moving_averages'];
            $test_data['short_ma'] = round($ma_data['short_ma'], 2);
            $test_data['long_ma'] = round($ma_data['long_ma'], 2);
            $test_data['short_distance'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['short_distance'] . '%' : 'N/A';
            $test_data['ma_alignment'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['ma_alignment'] : 'N/A';
            $test_data['ma_signal'] = isset($scores[$trading_mode]) && is_array($scores[$trading_mode]) ? $scores[$trading_mode]['signal'] : 'No signal';
        } elseif ($directive_id === 'news_sentiment_positive') {
            $test_data['sentiment_score'] = 0.75; // Mock data
            $test_data['news_count'] = 8;
            $test_data['sentiment_signal'] = 'Positive';
            $test_data['lookback_days'] = $config['lookback_days'] ?? 7;
            $test_data['threshold'] = $config['sentiment_threshold'] ?? 0.6;
        } elseif ($directive_id === 'obv') {
            $test_data['obv_value'] = isset($symbol_data['technical']['obv']) ? round($symbol_data['technical']['obv'], 0) : 1000000;
            $test_data['obv_trend'] = 'Bullish';
            $test_data['price_trend'] = 'Bullish';
            $test_data['trend_confirmation'] = 'Yes';
            $test_data['volume_boost'] = isset($symbol_data['volume_ratio']) && $symbol_data['volume_ratio'] >= ($config['volume_threshold'] ?? 1.2) ? 'Yes' : 'No';
        } elseif ($directive_id === 'price_above_sma_50') {
            $sma_value = isset($symbol_data['technical']['sma']) ? $symbol_data['technical']['sma'] : 0;
            $current_price = $symbol_data['price'] ?? 0;
            $distance_percent = $sma_value > 0 ? (($current_price - $sma_value) / $sma_value) * 100 : 0;
            
            $test_data['sma_value'] = round($sma_value, 2);
            $test_data['distance_percent'] = round($distance_percent, 2) . '%';
            $test_data['position'] = $current_price > $sma_value ? 'Above SMA' : 'Below SMA';
            $test_data['trend_signal'] = $current_price > $sma_value ? 'Bullish' : 'Bearish';
        } elseif ($directive_id === 'rsi') {
            $test_data['rsi_value'] = round($rsi, 2);
            $test_data['rsi_condition'] = $rsi <= ($config['oversold'] ?? 30) ? 'Oversold' : ($rsi >= ($config['overbought'] ?? 70) ? 'Overbought' : 'Neutral');
            $test_data['extreme_level'] = $rsi <= 20 ? 'Extreme Oversold' : ($rsi >= 80 ? 'Extreme Overbought' : 'Normal');
            $test_data['signal_strength'] = $rsi <= 20 || $rsi >= 80 ? 'Strong' : ($rsi <= 30 || $rsi >= 70 ? 'Moderate' : 'Weak');
        } elseif ($directive_id === 'rsi_overbought') {
            $test_data['rsi_value'] = round($rsi, 2);
            $test_data['overbought_status'] = $rsi >= ($config['overbought_threshold'] ?? 70) ? 'Overbought' : 'Not Overbought';
            $test_data['extreme_level'] = $rsi >= 80 ? 'Extreme Overbought' : 'Normal Overbought';
            $test_data['pullback_risk'] = $rsi >= 80 ? 'High' : ($rsi >= 70 ? 'Moderate' : 'Low');
        } else {
            $test_data['rsi_value'] = round($rsi, 2);
            $test_data['rsi_signal'] = $rsi < $oversold_threshold ? 'Oversold (Buy)' : ($rsi > $overbought_threshold ? 'Overbought (Sell)' : 'Neutral');
            $test_data['period_used'] = $rsi_period;
            $test_data['oversold_threshold'] = $oversold_threshold;
            $test_data['overbought_threshold'] = $overbought_threshold;
            $test_data['data_source'] = 'live_api_rsi';
        }
        
        return array(
            'success' => true,
            'message' => ucfirst(str_replace('_', ' ', $directive_id)) . ' test completed with LIVE Alpha Vantage data',
            'directive_code' => $directive_code,
            'test_data' => $test_data
        );
    }
    
    /**
     * Store API call serial number for directive
     */
    private static function store_api_serial($directive_id, $serial, $platform, $method, $parameters) {
        $config = get_option('tradepress_directive_' . $directive_id, array());
        
        if (!isset($config['recent_api_serials'])) {
            $config['recent_api_serials'] = array();
        }
        
        // Add new serial (keep only last 10)
        $config['recent_api_serials'][] = array(
            'serial' => $serial,
            'platform' => $platform,
            'method' => $method,
            'parameters' => $parameters,
            'timestamp' => time()
        );
        
        // Keep only the 10 most recent
        $config['recent_api_serials'] = array_slice($config['recent_api_serials'], -10);
        
        update_option('tradepress_directive_' . $directive_id, $config);
    }
    
    /**
     * Create enhanced directive test notice
     */
    public static function create_directive_test_notice($directive_id, $test_data, $trading_mode, $directive_code = null) {
        $symbol = $test_data['symbol'] ?? 'UNKNOWN';
        $price = $test_data['current_price'] ?? 0;
        $scores = $test_data['scores'] ?? array();
        
        // Get company name if available
        $company_name = self::get_company_name($symbol);
        $header = $company_name ? "{$symbol} - {$company_name}" : $symbol;
        
        // Get previous score for comparison
        $previous_score = self::get_previous_score($directive_id, $symbol, $trading_mode);
        
        // Add directive code if available
        $directive_code_display = '';
        if ($directive_code) {
            $directive_code_display = '<code style="background: #0073aa; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; margin-right: 8px;">' . esc_html($directive_code) . '</code>';
        }
        
        $html = '<div class="notice-header">' . $directive_code_display . '<strong>' . esc_html($header) . '</strong> <span class="price">$' . number_format($price, 2) . '</span></div>';
        
        // Directive-specific information
        if ($directive_id === 'adx') {
            $adx_value = $test_data['adx_value'] ?? 'N/A';
            $signal = $test_data['adx_signal'] ?? 'No Signal';
            $trend = $test_data['trend_strength'] ?? 'Unknown';
            
            $html .= '<div class="notice-details">';
            $html .= '<span class="detail-item"><strong>ADX:</strong> ' . $adx_value . ' <em>(trend strength)</em></span>';
            $html .= '<span class="detail-item"><strong>Signal:</strong> ' . esc_html($signal) . '</span>';
            $html .= '<span class="detail-item"><strong>Trend:</strong> ' . esc_html($trend) . '</span>';
            $html .= '</div>';
        } elseif ($directive_id === 'rsi') {
            $rsi_value = $test_data['rsi_value'] ?? 'N/A';
            $condition = $test_data['rsi_condition'] ?? 'Unknown';
            
            $html .= '<div class="notice-details">';
            $html .= '<span class="detail-item"><strong>RSI:</strong> ' . $rsi_value . ' <em>(momentum indicator)</em></span>';
            $html .= '<span class="detail-item"><strong>Condition:</strong> ' . esc_html($condition) . '</span>';
            $html .= '</div>';
        } elseif ($directive_id === 'volume') {
            $volume_ratio = $test_data['volume_ratio'] ?? 'N/A';
            $volume_signal = $test_data['volume_signal'] ?? 'Unknown';
            
            $html .= '<div class="notice-details">';
            $html .= '<span class="detail-item"><strong>Volume Ratio:</strong> ' . $volume_ratio . 'x <em>(vs average)</em></span>';
            $html .= '<span class="detail-item"><strong>Volume Signal:</strong> ' . esc_html($volume_signal) . '</span>';
            $html .= '</div>';
        }
        
        // Score information
        $html .= '<div class="notice-scores">';
        if ($trading_mode === 'both' && isset($scores['long']) && isset($scores['short'])) {
            $html .= '<span class="score-item"><strong>Long Score:</strong> ' . $scores['long'] . '</span>';
            $html .= '<span class="score-item"><strong>Short Score:</strong> ' . $scores['short'] . '</span>';
        } elseif (isset($scores[$trading_mode])) {
            $current_score = $scores[$trading_mode];
            $html .= '<span class="score-item"><strong>' . ucfirst($trading_mode) . ' Score:</strong> ' . $current_score;
            
            // Show previous score comparison if available
            if ($previous_score !== null) {
                $change = $current_score - $previous_score;
                $change_class = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : 'neutral');
                $change_symbol = $change > 0 ? '+' : '';
                $html .= ' <span class="score-change ' . $change_class . '">(' . $change_symbol . $change . ' from last test)</span>';
            }
            $html .= '</span>';
        }
        $html .= '</div>';
        
        // Remove the closing div for directive-test-notice since we removed the opening one
        
        // Store current score for future comparisons
        if (isset($scores[$trading_mode])) {
            self::store_test_score($directive_id, $symbol, $trading_mode, $scores[$trading_mode]);
        }
        
        return $html;
    }
    
    /**
     * Get company name for symbol
     */
    private static function get_company_name($symbol) {
        global $wpdb;
        $symbols_table = $wpdb->prefix . 'tradepress_symbols';
        
        $name = $wpdb->get_var($wpdb->prepare(
            "SELECT name FROM {$symbols_table} WHERE symbol = %s",
            $symbol
        ));
        
        return $name && $name !== $symbol . ' Corporation' ? $name : null;
    }
    
    /**
     * Get previous test score for comparison
     */
    private static function get_previous_score($directive_id, $symbol, $trading_mode) {
        $scores = get_option('tradepress_test_scores_' . $directive_id, array());
        $key = $symbol . '_' . $trading_mode;
        
        return isset($scores[$key]) ? $scores[$key] : null;
    }
    
    /**
     * Store test score for future comparisons
     */
    private static function store_test_score($directive_id, $symbol, $trading_mode, $score) {
        $scores = get_option('tradepress_test_scores_' . $directive_id, array());
        $key = $symbol . '_' . $trading_mode;
        
        $scores[$key] = $score;
        
        // Keep only last 50 scores per directive
        if (count($scores) > 50) {
            $scores = array_slice($scores, -50, null, true);
        }
        
        update_option('tradepress_test_scores_' . $directive_id, $scores);
    }
    
    /**
     * Get directive code for display
     */
    private static function get_directive_code($directive_id) {
        $codes = array(
            'rsi' => 'D17',
            'adx' => 'D1',
            'volume' => 'D22',
            'cci' => 'D4',
            'macd' => 'D10'
        );
        
        return $codes[$directive_id] ?? strtoupper(substr($directive_id, 0, 3));
    }
}