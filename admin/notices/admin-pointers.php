<?php  
/**
 * TradePress - Pointers
 *
 * Manage multiple step tutorial like process using WP core points.  
 *
 * @author   Ryan Bayne
 * @category Support
 * @package  TradePress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
                      
if ( ! class_exists( 'TradePress_Admin_Pointers' ) ) :

/**
 * TradePress_Admin_Pointers Class.
 */
class TradePress_Admin_Pointers {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'setup_pointers_for_screen' ) );
    }

    /**
     * Setup pointers for screen.
     */
    public function setup_pointers_for_screen() {    
        if ( ! $screen = get_current_screen() ) {
            return;
        }
             
        switch ( $screen->id ) {
            case 'plugins_page_TradePress' :
                $this->create_tables_tutorial();
            break;
            case 'tradepress_page_tradepress_scoring_directives' :
                $this->directive_configuration_pointer();
            break;
            case 'tradepress_page_tradepress_automation' :
                $this->automation_api_selection_pointer();
            break;
            case 'tradepress_page_tradepress_development' :
                if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'pointers' ) {
                    $this->pointers_page_automatic_pointer();
                }
            break;
        }
    }

    /**
     * Create left positioned header pointer - reusable method
     */
    public function create_left_header_pointer($target, $title, $content, $enable_focus = false) {
        return array(
            'target' => $target,
            'options' => array(
                'content' => '<h3>' . esc_html($title) . '</h3><p>' . esc_html($content) . '</p>',
                'position' => array(
                    'edge' => 'right',
                    'align' => 'top'
                ),
                'pointerClass' => 'wp-pointer-right tradepress-left-header-pointer',
                'width' => 320
            ),
            'focus' => $enable_focus
        );
    }

    /**
     * Directive configuration pointer.
     */
    public function directive_configuration_pointer() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Check if pointer has been dismissed
        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        if ( in_array( 'tradepress_directive_config', $dismissed ) ) {
            return;
        }
        
        $pointers = array(
            'pointers' => array(
                'tradepress_directive_config' => $this->create_left_header_pointer(
                    '.directive-details-container',
                    'Directive Configuration',
                    'Configure the selected directive default settings. These settings will be copied into strategies, then they can be adjusted to suit the strategy. Changing the values below will not have any affect on existing strategies.',
                    false
                )
            )
        );

        $this->enqueue_pointers( $pointers );
    }

    /**
     * Automation API selection pointer.
     */
    public function automation_api_selection_pointer() {
        // Disable automatic pointer - using manual test button instead
        return;
    }

    /**
     * Automatic pointer for the Pointers testing page
     */
    public function pointers_page_automatic_pointer() {
        // Check if pointer has been dismissed
        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        if ( in_array( 'tradepress_automatic_focus_test', $dismissed ) ) {
            return;
        }
        
        $pointers = array(
            'pointers' => array(
                'tradepress_automatic_focus_test' => array(
                    'target' => '#target-1',
                    'options' => array(
                        'content' => '<h3>' . esc_html__('Automatic Focus Test', 'tradepress') . '</h3>' .
                                   '<p>' . esc_html__('This pointer appears automatically with focus overlay when the page loads. It demonstrates how automatic pointers can work with focus effects.', 'tradepress') . '</p>',
                        'position' => array(
                            'edge' => 'left',
                            'align' => 'center'
                        ),
                        'pointerClass' => 'wp-pointer-left tradepress-left-header-pointer',
                        'width' => 320
                    ),
                    'focus' => true
                )
            )
        );

        $this->enqueue_pointers( $pointers );
    }

    /**
     * Pointers example with proper dismissal tracking.
     */
    public function create_tables_tutorial() {
        if ( ! isset( $_GET['TradePresstutorial'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Check dismissed pointers
        $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        
        // Build chain of non-dismissed pointers
        $pointers = array();
        
        if ( ! in_array( 'tradepress_tutorial_first', $dismissed ) ) {
            $pointers['tradepress_tutorial_first'] = array(
                'target' => '#contextual-help-link',
                'next' => 'tradepress_tutorial_second',
                'options' => array(
                    'content' => '<h3>' . esc_html__( 'Help and Information Tab', 'tradepress' ) . '</h3>' .
                    '<p>' . esc_html__( 'Over here is your first step to getting help. You will also find information about supporting the project as a developer or donator.', 'tradepress' ) . '</p>',
                    'position' => array(
                        'edge' => 'top',
                        'align' => 'right'
                    )
                )
            );
        }
        
        if ( ! in_array( 'tradepress_tutorial_second', $dismissed ) ) {
            $pointers['tradepress_tutorial_second'] = array(
                'target' => '#contextual-help-link',
                'options' => array(
                    'content' => '<h3>' . esc_html__( 'Second Step', 'tradepress' ) . '</h3>' .
                    '<p>' . esc_html__( 'Second content in the tutorial.', 'tradepress' ) . '</p>',
                    'position' => array(
                        'edge' => 'bottom',
                        'align' => 'middle'
                    )
                )
            );
        }
        
        if ( ! empty( $pointers ) ) {
            $this->enqueue_pointers( array( 'pointers' => $pointers ) );
        }
    }

    /**
     * Enqueue pointers and add script to page.
     * @param array $pointers
     */
    public function enqueue_pointers( $pointers ) {
        if ( empty( $pointers['pointers'] ) ) {
            return;
        }
        
        $pointers_json = wp_json_encode( $pointers );
        TradePress_enqueue_js( "
            jQuery( function( $ ) {
                var TradePress_pointers = {$pointers_json};

                setTimeout( init_TradePress_pointers, 800 );

                function init_TradePress_pointers() {
                    jQuery.each( TradePress_pointers.pointers, function( i ) {
                        show_TradePress_pointer(i);
                        return false;
                    });
                }

                function show_TradePress_pointer( id ) {
                    var pointer = TradePress_pointers.pointers[ id ];
                    if ( !pointer ) return;
                    
                    var \$target = \$( pointer.target );
                    
                    var options = \$.extend( pointer.options, {
                        close: function() {
                            // Save dismissed state to WordPress user meta
                            \$.post( ajaxurl, {
                                pointer: id,
                                action: 'dismiss-wp-pointer'
                            });
                        }
                    } );
                    
                    \$target.pointer( options ).pointer( 'open' );

                    if ( pointer.next_trigger ) {
                        var \$nextTarget = \$( pointer.next_trigger.target );
                        var currentPointer = \$target.data('pointer');
                        
                        \$nextTarget.on( pointer.next_trigger.event, function() {
                            if (currentPointer) {
                                setTimeout( function() { 
                                    currentPointer.element.pointer('close');
                                }, 400 );
                            }
                        });
                    }
                }
            });
        " );
    }
}

endif;

// Included by class TradePress_Admin() by action hook "init"...
new TradePress_Admin_Pointers();