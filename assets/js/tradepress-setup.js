/*global TradePress_setup_params */
jQuery( function( $ ) {

    $( '.button-next' ).on( 'click', function() {
        $('.tradepress-setup-content').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
        return true;
    } );

    $( '.TradePress-wizard-plugin-extensions' ).on( 'change', '.TradePress-wizard-extension-enable input', function() {
        if ( $( this ).is( ':checked' ) ) {
            $( this ).closest( 'li' ).addClass( 'checked' );
        } else {
            $( this ).closest( 'li' ).removeClass( 'checked' );
        }
    } );

    $( '.TradePress-wizard-plugin-extensions' ).on( 'click', 'li.TradePress-wizard-extension', function() {
        var $enabled = $( this ).find( '.TradePress-wizard-extension-enable input' );

        $enabled.prop( 'checked', ! $enabled.prop( 'checked' ) ).change();
    } );

    $( '.TradePress-wizard-plugin-extensions' ).on( 'click', 'li.TradePress-wizard-extension table, li.TradePress-wizard-extension a', function( e ) {
        e.stopPropagation();
    } );
} );
