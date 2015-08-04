jQuery( document ).ready( function( $ ) {	
	$( '.delete_feed' ).click( function( event ) {
		event.preventDefault();
		$.post( ajaxurl, {
			action: 'deleteiterablefeed',
			id: $( this ).data( 'id' )
		}, function( response ) {
			console.log( response );
		} ).error( function() {
			alert( 'Error: unable to delete feed.' );
		} );

		$( this ).parents( 'tr' ).fadeOut();
	} );
} );
