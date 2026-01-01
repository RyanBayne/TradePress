<?php
/**
 * API Data Adapter - Standardizes data from different API providers
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_API_Data_Adapter {
    
    /**
     * Standardize RSI data from any API provider
     */
    public static function standardize_rsi_data($raw_data, $provider_id) {
        switch ($provider_id) {
            case 'alphavantage':
                return self::parse_alphavantage_rsi($raw_data);
            case 'finnhub':
                return self::parse_finnhub_rsi($raw_data);
            default:
                return new WP_Error('unsupported_provider', "RSI parsing not supported for {$provider_id}");
        }
    }
    
    /**
     * Standardize quote data from any API provider
     */
    public static function standardize_quote_data($raw_data, $provider_id) {
        switch ($provider_id) {
            case 'alphavantage':
                return self::parse_alphavantage_quote($raw_data);
            case 'finnhub':
                return self::parse_finnhub_quote($raw_data);
            default:
                return new WP_Error('unsupported_provider', "Quote parsing not supported for {$provider_id}");
        }
    }
    
    /**
     * Parse Alpha Vantage RSI response
     */
    private static function parse_alphavantage_rsi($data) {
        // Check for error messages first
        if (isset($data['Error Message'])) {
            return new WP_Error('alphavantage_error', $data['Error Message']);
        }
        
        if (isset($data['Note'])) {
            return new WP_Error('alphavantage_rate_limit', $data['Note']);
        }
        
        $technical_analysis = $data['Technical Analysis: RSI'] ?? array();
        
        if (empty($technical_analysis)) {
            return new WP_Error('no_rsi_data', 'No Technical Analysis: RSI key found in response. Available keys: ' . implode(', ', array_keys($data)));
        }
        
        $latest_date = array_key_first($technical_analysis);
        $rsi_value = $technical_analysis[$latest_date]['RSI'] ?? null;
        
        if ($rsi_value === null) {
            return new WP_Error('no_rsi_value', 'No RSI value found for date: ' . $latest_date);
        }
        
        return (float) $rsi_value;
    }
    
    /**
     * Parse Finnhub RSI response
     */
    private static function parse_finnhub_rsi($data) {
        if (isset($data['rsi']) && !empty($data['rsi'])) {
            return (float) end($data['rsi']);
        }
        return null;
    }
    
    /**
     * Parse Alpha Vantage quote response
     */
    private static function parse_alphavantage_quote($data) {
        $quote = $data['Global Quote'] ?? array();
        
        return array(
            'price' => (float) ($quote['05. price'] ?? 0),
            'open' => (float) ($quote['02. open'] ?? 0),
            'high' => (float) ($quote['03. high'] ?? 0),
            'low' => (float) ($quote['04. low'] ?? 0),
            'previous_close' => (float) ($quote['08. previous close'] ?? 0),
            'change' => (float) ($quote['09. change'] ?? 0),
            'change_percent' => (float) str_replace('%', '', $quote['10. change percent'] ?? '0')
        );
    }
    
    /**
     * Parse Finnhub quote response
     */
    private static function parse_finnhub_quote($data) {
        return array(
            'price' => (float) ($data['c'] ?? 0),
            'open' => (float) ($data['o'] ?? 0),
            'high' => (float) ($data['h'] ?? 0),
            'low' => (float) ($data['l'] ?? 0),
            'previous_close' => (float) ($data['pc'] ?? 0),
            'change' => (float) ($data['d'] ?? 0),
            'change_percent' => (float) ($data['dp'] ?? 0)
        );
    }
}