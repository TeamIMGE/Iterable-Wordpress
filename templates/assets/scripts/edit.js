jQuery( document ).ready( function( $ ) {
	// Get relevant gravityform feed data
	$( '#gravityform' ).change( function( event ) {
		$( 'body' ).trigger( 'data_invalidated' );
		$.get( ajaxurl, { action: 'gravityformfieldsbyid', id: $( this ).val() }, function( result ) {
			result = JSON.parse( result );

			// replace fields info
			window.gravityform_fields = [];
			for( var i = 0; i < result.length; i++ ) {
				var item = { id: result[ i ].id, name: result[ i ].label }; 	

				if( result[ i ].type === 'checkbox' ) {
					var choices = [];	
					$( result[ i ].choices ).each( function( index ) {
						var id = result[ i ].id + '.' + ( index + 1 );
						var name = result[ i ].label + ' - ' + this.text;
						choices.push( { id: id, name: name } );
					} );
					item.choices = choices;
                    window.gravityform_fields.push( item );
				}

				if( _.isArray( result[ i ].inputs ) ) {
					$( result[ i ].inputs ).each( function( index ) {
                        window.gravityform_fields.push( {
                            id: this.id,
                            name: result[ i ].label + ' - ' + this.label
                        } );
					} );
				} else {
				    window.gravityform_fields.push( item );
                }
			}

			// notify everyone of change
			$( 'body' ).trigger( 'data_change' );
		} );
	} );

	// Refresh UI
	$( 'body' ).on( 'data_change', function( event ) {
		var list_fields = $( '<select></select>' );
		$( '#map' ).html( _.template( $( '.gravityform_field_template' ).html(), {
			gravityform_fields: window.gravityform_fields,
            iterable_fields: window.iterable_fields
		} ) );

		// match to feed data if it's relevant
		if( typeof( feed ) !== 'undefined' && feed.form_id === $( '#gravityform' ).val() ) {
			for( key in feed.meta.fields ) {
				var row = $( '#map tr[data-id="' + key + '"]' );	
				row.find( '.iterable_field' ).val( feed.meta.fields[ key ] );

                if( feed.meta.override ) { // not present in older versions
                    row.find( '.override_field' ).prop( 'checked', feed.meta.override[ feed.meta.fields[ key ] ] );
                }
			}
		} else { // otherwise guess
			$( '.gravityform_field' ).each( function() {
				var gf_name = $( this ).children().first().text().toLowerCase().replace( ' ', '' );
				$( this ).find( '.iterable_field option' ).each( function() {
					if( gf_name == $( this ).val().toLowerCase().replace( ' ', '' ) ) {
						$( this ).attr( 'selected', 'selected' );
					}
				} );
			} );
		}

		// update fields for 'only subscribe when checked'
		var html = '<option></option>';
		$( window.gravityform_fields ).each( function() {
			if( typeof( this.choices ) !== 'undefined' ) {
				$( this.choices ).each( function() {
					html += '<option value="' + this.id + '">' + this.name + '</option>';
				} );
			}
		} );	
		$( '#require_checked' ).html( html );
		if( typeof( window.feed ) !== 'undefined' && typeof( window.feed.meta.require_checked ) !== 'undefined' ) {
			$( '#require_checked' ).val( window.feed.meta.require_checked );
		}
	} );

	$( 'body' ).on( 'data_invalidated', function( event ) {
		$( '#map' ).html( '<tr><td colspan="2" style="text-align: center; font-size: 40px;"><i class="fa fa-cog fa-spin"></i></td></tr>' );
	} );

	// Handle Saving
	$( '#save_feed' ).click( function( event ) {
		event.preventDefault();
		$( this ).prop( 'disabled', true );
		$( this ).val( 'Saving...' );

		// create map fields structure
		var map_fields = {};
        var map_override = {};
		$( '#map .gravityform_field' ).each( function() {
			var key = $( this ).data( 'id' ); 
			var value = $( this ).find( '.iterable_field' ).val();
            var override = $( this ).find( '.override_field' ).prop( 'checked' );

			if( value !== "" ) { 
				map_fields[ key ] = value;
                map_override[ value ] = override; // value being the iterable field name
			}
		} );

		$.post( ajaxurl, {
			action: 'saveiterablefeed',
			feed_id: $( '#feed_id' ).val(),
			iterablelist: $( '#iterablelist' ).val(),
			iterablelist_name: $( '#iterablelist option:selected' ).text(),
			gravityform: $( '#gravityform' ).val(),	
			gravityform_name: $( '#gravityform option:selected' ).text(),
			map_fields: JSON.stringify( map_fields ),
            map_override: JSON.stringify( map_override ),
			resubscribe: $( '#resubscribe' ).attr( 'checked' ) === "checked",
			listwise: $( '#listwise' ).attr( 'checked' ) === "checked",
			require_checked: $( '#require_checked' ).val()
		}, function( response ) {
			if( !isNaN( response ) ) {
                if( response !== $( '#feed_id' ).val() ) {
                    window.location = document.URL + '&id=' + response;
                }
			}
		} ).error( function( response ) {
			alert( 'Unable to save ' + response );
		} ).always( function() {
			$( '#save_feed' ).prop( 'disabled', false );
			$( '#save_feed' ).val( 'Save' );
		} );
	} );

	// Populate form with initial data
	if( typeof( feed ) !== 'undefined' ) {
		$( '#iterablelist' ).val( feed.meta.iterablelist );
		$( '#gravityform' ).val( feed.form_id ).trigger( 'change' );
		if( feed.meta.resubscribe === "false" ) {
			$( '#resubscribe' ).attr( 'checked', false );
		} else {
			$( '#resubscribe' ).attr( 'checked', true );
		}
		if( feed.meta.listwise === "false" ) {
			$( '#listwise' ).attr( 'checked', false );
		} else {
			$( '#listwise' ).attr( 'checked', true );
		}
	}
} );
