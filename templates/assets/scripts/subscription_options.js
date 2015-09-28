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

    function get_hash( variable ) {
        var query = window.location.hash;
        query = query.replace( '#', '' );
        var pairs = query.split( '&' );
        for( var i = 0; i < pairs.length; i++ ) {
            var pair = pairs[ i ].split( '=' );
            if( decodeURIComponent( pair[ 0 ] ) === variable ) {
                return decodeURIComponent( pair[ 1 ] );
            }
        }
        return '';
    }

    // Get user data
    var query_email = get_parameter( 'email' );
    var hash_email = get_hash( 'email' );
    var cookie_email = $.cookie( 'iterableEndUserId' );

    if( query_email !== '' ) {
        email = query_email;
    } else if( hash_email !== '' ) {
        email = hash_email;
    } else if( cookie_email != undefined ) {
        email = cookie_email;
    } else {
        $( '.subscription_options' ).html(
            _.template( $( '#error_box' ).html() )( {
                email: atob( $( '#fallback' ).val() ),
                website: encodeURIComponent( document.URL ),
            } )
        );
        return;
    }

    $( '#email' ).val( email );

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
            $( '.nochannels_message' ).show();
        } else {
            $( '.nochannels_message' ).hide();
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
