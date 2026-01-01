<?php
/**
 * TradePress Form Fields
 *
 * Generic form field handling functions for use throughout the plugin.
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Form_Fields' ) ) :

/**
 * TradePress_Form_Fields Class.
 */
class TradePress_Form_Fields {

    /**
     * Get a setting from the settings API.
     *
     * @param mixed $option_name
     * @return string
     */
    public static function get_option( $option_name, $default = '' ) {
        // Array value
        if ( strstr( $option_name, '[' ) ) {

            parse_str( $option_name, $option_array );

            // Option name is first key
            $option_name = current( array_keys( $option_array ) );

            // Get value
            $option_values = get_option( $option_name, '' );

            $key = key( $option_array[ $option_name ] );

            if ( isset( $option_values[ $key ] ) ) {
                $option_value = $option_values[ $key ];
            } else {
                $option_value = null;
            }

        // Single value
        } else {
            $option_value = get_option( $option_name, null );
        }

        if ( is_array( $option_value ) ) {
            $option_value = array_map( 'stripslashes', $option_value );
        } elseif ( ! is_null( $option_value ) ) {
            $option_value = stripslashes( $option_value );
        }

        return $option_value === null ? $default : $option_value;
    }

    /**
     * Output admin fields.
     *
     * Loops though the TradePress options array and outputs each field.
     *
     * @param array $options Opens array to output
     */
    public static function output_fields( $options ) {
                            
        foreach ( $options as $value ) {
            if ( ! isset( $value['type'] ) ) {
                continue;
            }
            if ( ! isset( $value['id'] ) ) {
                $value['id'] = '';
            }
            if ( ! isset( $value['title'] ) ) {
                $value['title'] = isset( $value['name'] ) ? $value['name'] : '';
            }
            if ( ! isset( $value['class'] ) ) {
                $value['class'] = '';
            }
            if ( ! isset( $value['css'] ) ) {
                $value['css'] = '';
            }
            if ( ! isset( $value['default'] ) ) {
                $value['default'] = '';
            }
            if ( ! isset( $value['desc'] ) ) {
                $value['desc'] = '';
            }
            if ( ! isset( $value['desc_tip'] ) ) {
                $value['desc_tip'] = false;
            }
            if ( ! isset( $value['placeholder'] ) ) {
                $value['placeholder'] = '';
            }

            // Custom attribute handling
            $custom_attributes = array();

            if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
                foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
                    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }

            // Description handling
            $field_description = self::get_field_description( $value );
            extract( $field_description );

            // Switch based on type
            switch ( $value['type'] ) {

                // Section Titles
                case 'title':
                    if ( ! empty( $value['title'] ) ) {
                        echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
                    }
                    if ( ! empty( $value['desc'] ) ) {
                        echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
                    }
                    echo '<table class="form-table">'. "\n\n";
                    if ( ! empty( $value['id'] ) ) {
                        do_action( 'TradePress_settings_' . sanitize_title( $value['id'] ) );
                    }
                    break;

                // Section Ends
                case 'sectionend':
                    if ( ! empty( $value['id'] ) ) {
                        do_action( 'TradePress_settings_' . sanitize_title( $value['id'] ) . '_end' );
                    }
                    echo '</table>';
                    if ( ! empty( $value['id'] ) ) {
                        do_action( 'TradePress_settings_' . sanitize_title( $value['id'] ) . '_after' );
                    }
                    break;
                
                // Standard text inputs and subtypes like 'number'
                case 'text':

                    $option_value = self::get_option( $value['id'], $value['default'] );

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo $tooltip_html; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                            <input
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="text"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                value="<?php echo esc_attr( $option_value ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                <?php echo implode( ' ', $custom_attributes ); ?>
                                <?php if( isset( $value['readonly'] ) ) { echo 'readonly'; } ?>                                
                                /> <?php echo $description; ?>
                        </td>
                    </tr><?php
                    break;                
                case 'hidden':
                    $option_value = self::get_option( $value['id'], $value['default'] );
                    ?><input type="hidden" name="<?php echo esc_attr( $value['id'] ); ?>" value="<?php echo esc_attr( $option_value ); ?>" /><?php 
                    break;
                case 'email':
                case 'number':
                case 'color' :
                case 'password' :

                    $type         = $value['type'];
                    $option_value = self::get_option( $value['id'], $value['default'] );

                    if ( $value['type'] == 'color' ) {
                        $type = 'text';
                        $value['class'] .= 'colorpick';
                        $description .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>';
                    }

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo $tooltip_html; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                            <?php
                            if ( 'color' == $value['type'] ) {
                                echo '<span class="colorpickpreview" style="background: ' . esc_attr( $option_value ) . ';"></span>';
                            }
                            ?>
                            <input
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="<?php echo esc_attr( $type ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                value="<?php echo esc_attr( $option_value ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                <?php echo implode( ' ', $custom_attributes ); ?>
                                /> <?php echo $description; ?>
                        </td>
                    </tr><?php
                    break;

                // Textarea
                case 'textarea':

                    $option_value = self::get_option( $value['id'], $value['default'] );

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo $tooltip_html; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                            <?php echo $description; ?>
                            <textarea
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                <?php echo implode( ' ', $custom_attributes ); ?>
                                ><?php echo esc_textarea( $option_value );  ?></textarea>
                        </td>
                    </tr><?php
                    break;

                // Select boxes
                case 'select' :
                case 'multiselect' :

                    $option_value = self::get_option( $value['id'], $value['default'] );

                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo $tooltip_html; ?>
                        </th>
                        <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
                            <select
                                name="<?php echo esc_attr( $value['id'] ); ?><?php if ( $value['type'] == 'multiselect' ) echo '[]'; ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                <?php echo implode( ' ', $custom_attributes ); ?>
                                <?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
                                >
                                <?php
                                    foreach ( $value['options'] as $key => $val ) {
                                        ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php

                                            if ( is_array( $option_value ) ) {
                                                selected( in_array( $key, $option_value ), true );
                                            } else {
                                                selected( $option_value, $key );
                                            }

                                        ?>><?php echo $val ?></option>
                                        <?php
                                    }
                                ?>
                            </select> <?php echo $description; ?>
                        </td>
                    </tr><?php
                    break;

                // Radio inputs
                case 'radio' :
                    $option_value = self::get_option( $value['id'], $value['default'] );
                    ?><tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                            <?php echo $tooltip_html; ?>
                        </th>
                        <td class="forminp forminp-checkbox">
                            <fieldset>
                                <?php echo $description; ?>
                                <ul>
                                <?php
                                    foreach ( $value['options'] as $key => $val ) {
                                        ?>
                                        <li>
                                            <input
                                                name="<?php echo $value['id']; ?>"
                                                value="<?php echo $key; ?>"
                                                type="radio"
                                                id="<?php echo esc_attr( $value['id'] . $key ); ?>"
                                                <?php echo implode( ' ', $custom_attributes ); ?>
                                                <?php checked( $key, $option_value ); ?>
                                                /> <?php echo $val ?>
                                        </li>
                                        <?php
                                    }
                                ?>
                                </ul>
                            </fieldset>
                        </td>
                    </tr><?php
                    break;

                // Checkbox input
                case 'scopecheckbox' :
                case 'scopecheckboxpublic' :
                case 'checkbox' :
                
                    $option_value = self::get_option( $value['id'], $value['default'] );
                    
                    $visbility_class = array();

                    if ( ! isset( $value['hide_if_checked'] ) ) {
                        $value['hide_if_checked'] = false;
                    }
                    if ( ! isset( $value['show_if_checked'] ) ) {
                        $value['show_if_checked'] = false;
                    }
                    if ( 'yes' == $value['hide_if_checked'] || 'yes' == $value['show_if_checked'] ) {
                        $visbility_class[] = 'hidden_option';
                    }
                    if ( 'option' == $value['hide_if_checked'] ) {
                        $visbility_class[] = 'hide_options_if_checked';
                    }
                    if ( 'option' == $value['show_if_checked'] ) {
                        $visbility_class[] = 'show_options_if_checked';
                    }

                    if ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
                        ?>
                            <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
                                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
                                <td class="forminp forminp-checkbox">
                                    <fieldset>
                        <?php
                    } else {
                        ?>
                            <fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
                        <?php
                    }

                    if ( ! empty( $value['title'] ) ) {
                        ?>
                            <legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
                        <?php
                    }
                    ?>  
                    
                    <?php if( $value['type'] == 'scopecheckbox' ) { echo TradePress_scopecheckbox_required_icon( $value['scope'] ); } ?>
                    <?php if( $value['type'] == 'scopecheckboxpublic' ) { echo TradePress_scopecheckboxpublic_required_icon( $value['scope'] ); } ?>
                    
                        <label for="<?php echo $value['id'] ?>">
                            <input
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="checkbox"
                                class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
                                value="1"
                                <?php checked( $option_value, 'yes'); ?>
                                <?php echo implode( ' ', $custom_attributes ); ?>
                            /> 
                            
                            <?php echo $description ?>
                        </label>                          
                        <?php echo $tooltip_html; ?>
                    <?php

                    if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
                                    ?>
                                    </fieldset>
                                </td>
                            </tr>
                        <?php
                    } else {
                        ?>
                            </fieldset>
                        <?php
                    }
                    break;

                // Default: run an action
                default:
                    do_action( 'TradePress_admin_field_' . $value['type'], $value );
                    break;
            }
        }
    }

    /**
     * Helper function to get the formated description and tip HTML for a
     * given form field. Plugins can call this when implementing their own custom
     * settings types.
     *
     * @param  array $value The form field value array
     * @return array The description and tip as a 2 element array
     * 
     * @version 1.0
     */
    public static function get_field_description( $value ) {
        $description  = ' ';
        $tooltip_html = ' ';

        if ( true === $value['desc_tip'] ) {
            $tooltip_html = $value['desc'];
        } elseif ( ! empty( $value['desc_tip'] ) ) {
            $description  = $value['desc'];
            $tooltip_html = $value['desc_tip'];
        } elseif ( ! empty( $value['desc'] ) ) {
            $description  = $value['desc'];
        }

        if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {
            $description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
        } elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {
            $description = wp_kses_post( $description );
        } elseif ( $description ) {
            $description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
        }

        if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ) ) ) {
            $tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
        } elseif ( $tooltip_html ) {
            $tooltip_html = TradePress_help_tip( $tooltip_html );
        }

        return array(
            'description'  => $description,
            'tooltip_html' => $tooltip_html
        );
    }

    /**
     * Save admin fields.
     *
     * Loops though the TradePress options array and outputs each field.
     *
     * @param array $options Options array to output
     * @return bool
     */
    public static function save_fields( $options ) {
        
        if ( empty( $_POST ) ) {      
            return false;
        }

        // Options to update will be stored here and saved later.
        $update_options = array();

        // Loop options and get values to save.
        foreach ( $options as $key => $option ) {
            
            if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
                continue;
            }

            // Get posted value.
            if ( strstr( $option['id'], '[' ) ) {
                parse_str( $option['id'], $option_name_array );
                $option_name  = current( array_keys( $option_name_array ) );
                $setting_name = key( $option_name_array[ $option_name ] );
                $raw_value    = isset( $_POST[ $option_name ][ $setting_name ] ) ? wp_unslash( $_POST[ $option_name ][ $setting_name ] ) : null;
            } else {
                $option_name  = $option['id'];
                $setting_name = '';
                $raw_value    = isset( $_POST[ $option['id'] ] ) ? wp_unslash( $_POST[ $option['id'] ] ) : null;
            }

            // Format the value based on option type.
            switch ( $option['type'] ) {
                case 'scopecheckbox' :
                case 'scopecheckboxpublic' :
                case 'checkbox' :
                    $value = is_null( $raw_value ) ? 'no' : 'yes';
                    break;
                case 'textarea' :
                    $value = wp_kses_post( trim( $raw_value ) );
                    break;
                case 'multiselect' :
                case 'multi_select_countries' :
                    $value = array_filter( array_map( 'TradePress_clean', (array) $raw_value ) );
                    break;
                case 'image_width' :
                    $value = array();
                    if ( isset( $raw_value['width'] ) ) {
                        $value['width']  = TradePress_clean( $raw_value['width'] );
                        $value['height'] = TradePress_clean( $raw_value['height'] );
                        $value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
                    } else {
                        $value['width']  = $option['default']['width'];
                        $value['height'] = $option['default']['height'];
                        $value['crop']   = $option['default']['crop'];
                    }
                    break;
                default :
                    $value = TradePress_clean( $raw_value );
                    break;
            }

            /**
             * Sanitize the value of an option.
             */
            $value = apply_filters( 'TradePress_admin_settings_sanitize_option', $value, $option, $raw_value );

            /**
             * Sanitize the value of an option by option name.
             */
            $value = apply_filters( "TradePress_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

            if ( is_null( $value ) ) {
                continue;
            }

            // Check if option is an array and handle that differently to single values.
            if ( $option_name && $setting_name ) {
                if ( ! isset( $update_options[ $option_name ] ) ) {
                    $update_options[ $option_name ] = get_option( $option_name, array() );
                }
                if ( ! is_array( $update_options[ $option_name ] ) ) {
                    $update_options[ $option_name ] = array();
                }
                $update_options[ $option_name ][ $setting_name ] = $value;
            } else {
                $update_options[ $option_name ] = $value;
            }
        }

        // Save all options in our array.
        foreach ( $update_options as $name => $value ) {
            update_option( $name, $value );
        }

        return true;
    }
}

endif;