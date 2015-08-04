jQuery( document ).ready( function( $ ) {
    try {
        var workflows = JSON.parse( $( '.workflows' ).val() );
    } catch( e ) {
        console.log( 'Error: unable to parse channels data, starting fresh.' );
        var workflows = '';
    }
    if( _.isArray( workflows ) ) {
        $( '#workflows_body' ).html( _.template( $( '.workflows_template' ).html() )( { workflows: workflows } ) );
    }

    $( '.add_new_workflow' ).click( function( event ) {
        event.preventDefault();
        $( '#workflows_body' ).append( _.template( $( '.workflows_template' ).html() )( {
            workflows: [ {} ]
        } ) );
    } );

    $( 'body' ).on( 'click', '.delete_workflow', function( event ) {
        event.preventDefault();
        $( this ).parents( 'tr' ).remove();
    } );

    $( '.workflows_form' ).submit( function( event ) {
        var result = [];
        $( '.workflow' ).each( function() {
            result.push( {
                workflow_id: parseInt( $( this ).find( '.workflow_id' ).val() ),
                list_id: parseInt( $( this ).find( '.list_id' ).val() ),
            } );
        } );
        $( '.workflows' ).val( JSON.stringify( result ) );
    } );
} );
