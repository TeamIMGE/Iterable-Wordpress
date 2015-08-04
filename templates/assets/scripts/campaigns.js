jQuery( document ).ready( function( $ ) {
    try {
        var campaigns = JSON.parse( $( '.campaigns' ).val() );
    } catch( e ) {
        console.log( 'Error: unable to parse channels data, starting fresh.' );
        var campaigns = '';
    }
    if( _.isArray( campaigns ) ) {
        $( '#campaigns_body' ).html( _.template( $( '.campaigns_template' ).html() )( { campaigns: campaigns } ) );
    }

    $( '.add_new_campaign' ).click( function( event ) {
        event.preventDefault();
        $( '#campaigns_body' ).append( _.template( $( '.campaigns_template' ).html() )( {
            campaigns: [ {} ]
        } ) );
    } );

    $( 'body' ).on( 'click', '.delete_campaign', function( event ) {
        event.preventDefault();
        $( this ).parents( 'tr' ).remove();
    } );

    $( '.campaign_form' ).submit( function( event ) {
        var result = [];
        $( '.campaign' ).each( function() {
            result.push( {
                name: $( this ).find( '.name' ).val(),
                list_id: parseInt( $( this ).find( '.list_id' ).val() ),
                template_id: parseInt( $( this ).find( '.template_id' ).val() ),
                suppression_list_ids: $( this ).find( '.suppression_list_ids' ).val(),
                send_at: $( this ).find( '.send_at' ).val(),
                last_send: $( this ).find( '.last_send' ).val(),
            } );
        } );
        $( '.campaigns' ).val( JSON.stringify( result ) );
    } );
} );
