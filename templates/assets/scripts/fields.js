jQuery( document ).ready( function( $ ) {
    if( typeof( iterable_fields ) === 'undefined' ) {
        $( '.fields_form' ).html( '<div class="error"><p><b>Iterable Error:</b> Unable to retrieve list of user fields. Check your API key.</p></div>' );
        return;
    }
    iterable_fields.sort();

    try {
        var fields = JSON.parse( $( '.fields' ).val() );
    } catch( e ) {
        console.log( 'Error: unable to parse channels data, starting fresh.' );
        var fields = [];
    }

    var fields_hashmap = {};
    for( var i = 0; i < fields.length; i++ ) {
        fields_hashmap[ fields[ i ] ] = true;
    }
    fields = fields_hashmap;

    for( var i = 0; i < iterable_fields.length; i++ ) {
        var name = iterable_fields[ i ];
        var hide = false;
        if( typeof( fields[ name ] ) !== 'undefined' ) {
            hide = true;
        }
        iterable_fields[ i ] = {
            name: name,
            hide: hide,
        };
    }

    $( '#field_body' ).html( _.template( $( '#field_template' ).html() )( {
        fields: iterable_fields,
    } ) );

    $( '.fields_form' ).submit( function( event ) {
        var result = $( '.fields_form input.field:not(:checked)' ).map( function() {
            return $( this ).val();
        } ).get();
        $( '.fields' ).val( JSON.stringify( result ) );
    } );
} );
