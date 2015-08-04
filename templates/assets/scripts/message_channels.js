jQuery( document ).ready( function( $ ) {
    try {
        var channels = JSON.parse( $( '.message_channels' ).val() );
    } catch( e ) {
        console.log( 'Error: unable to parse channels data, starting fresh.' );
        var channels = '';
    }
    if( _.isArray( channels ) ) {
        $( '#channel_body' ).html( _.template( $( '.message_channel_template' ).html() )( { message_channels: channels } ) );
    }

    $( '.add_new_channel' ).click( function( event ) {
        event.preventDefault();
        $( '#channel_body' ).append( _.template( $( '.message_channel_template' ).html() )( {
            message_channels: [ { name: '', id: '' } ]
        } ) );
    } );

    $( 'body' ).on( 'click', '.delete_channel', function( event ) {
        event.preventDefault();
        $( this ).parents( 'tr' ).remove();
    } );

    $( '.channel_form' ).submit( function( event ) {
        var result = [];
        $( '.channel' ).each( function() {
            result.push( { name: $( this ).find( '.name' ).val(), id: $( this ).find( '.id' ).val() } );
        } );
        $( '.message_channels' ).val( JSON.stringify( result ) );
    } );
} );
