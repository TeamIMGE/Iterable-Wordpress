jQuery( document ).ready( function( $ ) {
    if( $( '.subscription_options' ).length <= 0 ) {
        return;
    }

    function get_parameter( name ) {
        name = name.replace( /[\[]/, '\\[' ).replace( /[\]]/, '\\]' );
        var regex = new RegExp( '[\\?&]' + name + '=([^&#]*)' ),
        results = regex.exec( location.search );
        return results === null ? '' : decodeURIComponent( results[ 1 ].replace( /\+/g, ' ' ) );
    }

    // Get user data

    var email = get_parameter( 'email' );
    if( email === '' ) {
        email = $.cookie( 'iterableEndUserId' );
    }
    if( !email ) {
        $( '.subscription_container.all_sends' ).html( '<h2>Error: Email not found</h2>' );
        return;
    } else {
        $( '#email' ).val( email );
    }

    $.get( $( '.subscription_options form' ).attr( 'action' ), {
        action: 'getchannels',
        email: decodeURIComponent( email )
    }, function( unsubscribed ) {
        $( '.subscription_container.all_sends' ).html(
            _.template( $( '#optin_box' ).html() )( {
                message_channels: all_channels,
                unsubscribed: JSON.parse( unsubscribed ),
            } )
        );

        $( '.fa-spin.loading' ).remove();
        $( '.checkbox input' ).first().trigger( 'change' );
    } );

    $( 'body' ).on( 'change', '.checkbox input', function( event ) {
        console.log( 'changed' );
        // update list of subscribed message channels
        var subscribe_list = $( '.subscription_options input:checked' ).map( function() {
            return $( this ).parents( 'div' ).data( 'name' );
        } ).get().join( ', ' );

        if( subscribe_list === '' ) {
            $( '.subscription_options' ).addClass( 'show_koala' );
        } else {
            $( '.subscription_options' ).removeClass( 'show_koala' );
        }

        $( '.subscribed_sends' ).html( subscribe_list );
    } );

    $( '.subscription_options > form' ).submit( function( event ) {
        console.log( 'form submit' );
        event.preventDefault();
        var unsub_ids = $( '.subscription_options input[type="checkbox"]:not(:checked)' ).map( function() {
            return parseInt( $( this ).val() );
        } ).get()
        $.post( $( this ).attr( 'action' ), {
            action: 'updatechannel',
            email: $( '#email' ).val(),
            ids: unsub_ids
        }, function( result ) {
            $( '#save_changes' ).removeClass( 'btn-primary' ).addClass( 'btn-success' ).html( 'Saved! <i class="fa fa-check"></i>' );
            setTimeout( function() {
                $( '#save_changes' ).removeClass( 'btn-success' ).addClass( 'btn-primary' ).html( 'Save Changes' );
            }, 5000 );
            console.log( result );
        } ).fail( function() {
            console.log( 'failure' );
        } );
    } );

    $( '#save_changes' ).click( function( event ) {
        event.preventDefault();
        $( '.subscription_options > form' ).trigger( 'submit' );
    } );

    $( '.btn-unsubscribe-all' ).click( function( event ) {
        // uncheck everything
        $( '.subscription_options input[type="checkbox"]' ).prop( 'checked', false );

        // trigger save
        $( '.subscription_options > form' ).trigger( 'submit' );
    } );
} );
