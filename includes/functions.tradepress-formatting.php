<?php
/**
 * TradePress Formatting Functions
 *
 * Functions for formatting data and handling edge cases.
 *
 * @package TradePress/Functions
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Safely format a number, handling null values gracefully
 *
 * @param mixed $number Number to format (can be null)
 * @param int $decimals Number of decimal points
 * @param string $decimal_separator Decimal separator
 * @param string $thousands_separator Thousands separator
 * @return string Formatted number
 */
function tradepress_number_format($number, $decimals = 0, $decimal_separator = '.', $thousands_separator = ',') {
    // Handle null, empty strings, or other non-numeric values
    if ($number === null || $number === '' || !is_numeric($number)) {
        return '0';
    }
    
    // Now we can safely use number_format
    return number_format((float)$number, $decimals, $decimal_separator, $thousands_separator);
}

/**
 * Format a price with currency symbol
 *
 * @since 1.0.0
 * @param mixed $price The price to format
 * @param string $currency_symbol Currency symbol
 * @return string Formatted price or empty string if input is invalid
 */
function tradepress_price_format($price, $currency_symbol = '$') {
    if ($price === null || $price === '') {
        return '';
    }
    
    if (is_numeric($price)) {
        return $currency_symbol . tradepress_number_format($price);
    }
    
    return (string)$price;
}

/**
 * Format a percentage value
 *
 * @since 1.0.0
 * @param mixed $value The value to format as percentage
 * @param int $decimals Number of decimal points
 * @return string Formatted percentage or empty string if input is invalid
 */
function tradepress_percentage_format($value, $decimals = 2) {
    if ($value === null || $value === '') {
        return '';
    }
    
    if (is_numeric($value)) {
        return tradepress_number_format($value, $decimals) . '%';
    }
    
    return (string)$value;
}

// =============================================================================
// LEGACY FORMATTING FUNCTIONS (from functions/functions.tradepress-formatting.php)
// =============================================================================

/**
 * Find the middle of a string and split it there.
 * 
 * @param string $string
 * @param mixed $ret
 * @return mixed 
 * @version 1.0
 */
function TradePress_string_half( string $string, $ret = null ) {        
    $a = array();
    $splitstring1 = substr( $string, 0, floor( strlen( $string ) / 2 ) );
    $splitstring2 = substr( $string, floor (strlen( $string ) / 2 ) );

    if ( substr( $splitstring1, 0, -1 ) != ' ' AND substr( $splitstring2, 0, 1 ) != ' ' )
    {
        $middle = strlen( $splitstring1 ) + strpos( $splitstring2, ' ' ) + 1;
    }
    else
    {
        $middle = strrpos( substr( $string, 0, floor( strlen( $string ) / 2) ), ' ' ) + 1;    
    }

    if( $ret == 1 )
    {
        $string1 = substr( $string, 0, $middle );
        return $string1;
    }
    elseif( $ret == 2 )
    {
        $string2 = substr( $string, $middle );
        return $string2;    
    }
    
    $a[] = $string1;
    $a[] = $string2;
                    
    return $a;
}
     
/**
 * Normalize postcodes.
 *
 * Remove spaces and convert characters to uppercase.
 *
 * @param string $postcode
 * @return string Sanitized postcode.
 */
function TradePress_normalize_postcode( string $postcode ) {          
    return preg_replace( '/[\s\-]/', '', trim( strtoupper( $postcode ) ) );
}

/**
 * Format phone number.
 *
 * @param mixed $tel
 * @return string
 */
function TradePress_format_phone_number( $tel ) {            
    return str_replace( '.', '-', $tel );
}

/**
 * Make a string lowercase.
 * Try to use mb_strtolower() when available.
 *
 * @param string $string
 * @return string
 */
function TradePress_strtolower( string $string ) {                    
    return function_exists( 'mb_strtolower' ) ? mb_strtolower( $string ) : strtolower( $string );
}

/**
 * Trim a string and append a suffix.
 * 
 * @param string $string
 * @param integer $chars
 * @param string $suffix
 * @return string
 */
function TradePress_trim_string( $string, $chars = 200, $suffix = '...' ) {      
    if ( strlen( $string ) > $chars ) {
        if ( function_exists( 'mb_substr' ) ) {
            $string = mb_substr( $string, 0, ( $chars - mb_strlen( $suffix ) ) ) . $suffix;
        } else {
            $string = substr( $string, 0, ( $chars - strlen( $suffix ) ) ) . $suffix;
        }
    }
    return $string;
}     

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * 
 * Non-scalar values are ignored.
 * @param string|array $var
 * @return string|array
 */
function TradePress_clean( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'TradePress_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }
}

/**
 * Pass a template and replace {{placeholders}} with data. 
 * 
 * @param mixed $replacements
 * @param mixed $template
 * @return string
 * @version 1.0
 */
function TradePress_parse_template($replacements, $template) 
{
    return preg_replace_callback('/{{(.+?)}}/', function($matches) use ($replacements) 
    {
        return $replacements[$matches[1]];
    }, $template);
}
