window.csv_data = false;
jQuery( document ).ready( function( $ ) {
    $( '#csv' ).change( function( event ) {
        window.csv_data = false;
        event.preventDefault();
        $( '#csv' ).parse( {
            config: {
                header: true,
                complete: function( results, file ) {
                    window.csv_data = results;
                    window.csv_fields = window.csv_data.meta.fields ;
                    $( '#map' ).html( _.template( $( '.csv_field_template' ).html() ) );
                }
            },
            error: function( error, file, input_element, reason ) {
                console.log( 'fatal error', error );
            }
        } );
    } );
    $( '#import_list' ).click( function( event ) { 
        event.preventDefault();

        // input validation
        $( '.import-response-message' ).remove();
        var errors = [];
        if( $( '#csv' ).val() === "" ) {
            errors.push( 'You must add a csv file.' );
        } else if( !window.csv_data ) {
            errors.push( 'Unable to read csv file.' );
        }
        if( $( '#iterablelist' ).val() === '' ) {
            errors.push( 'You must select an interable list.' );
        }

        // create tables to track mapping between iterable columns and
        // csv columns as well as whether either should be overridden
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

        // a user must select a least one mapping
        if( Object.keys( map_fields ).length === 0 ) {
           errors.push( 'You have no selected any map fields' ); 
        }

        // Are we in a position to continue importing?
        if( errors.length > 0 ) {
            $( '#import_list' ).after( _.template( $( '.error_template' ).html() )( {
                message: 'Import could not continue due to the following errors:',
                errors: errors
            } ) );
            return;
        } else { // begin import process
            $( '#import_list' ).prop( 'disabled', true );

            var subscribers = [];

            $( window.csv_data.data ).each( function() {
                // create subscriber object
                var subscriber = { dataFields: {} };
                var record = this;
                for( key in map_fields ) {
                    if( map_fields[ key ] === 'email' ) { // special column
                        subscriber.email = record[ key ];
                    } else if( record[ key ] !== '' ) {
                        subscriber.dataFields[ map_fields[ key ] ] = record[ key ];
                    }
                }

                // add in attribution data
                if( $( '#source' ).val() !== '' ) {
                    subscriber.dataFields.Source = $( '#source' ).val();
                    map_override.Source = false;
                }
                if( $( '#campaign' ).val() !== '' ) {
                    subscriber.dataFields.Campaign = $( '#campaign' ).val();
                    map_override.Campaign = false;
                }
                if( $( '#medium' ).val() !== '' ) {
                    subscriber.dataFields.Medium = $( '#medium' ).val();
                    map_override.Medium = false;
                }

                subscribers.push( subscriber );
            } );

            $( '#import_list' ).val( 'Importing...' );

            $.ajax( {
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'subscribe',
                    resubscribe: $( '#resubscribe' ).attr( 'checked' ) === "checked",
                    subscribers: JSON.stringify( subscribers ),
                    iterablelist: $( '#iterablelist' ).val(),
                    override: map_override,
                }
            } ).done( function( data ) {
                messages = {
                    'success': { class: 'updated', text: 'Import Succeeded' },
                    'failure (all_users)': { class: 'error', text: 'Unable to download list of users from Iterable. Probably worth trying again.' },
                    'fail (list_subscribe)': { class: 'error', text: 'Unable to subscribe users to Iterable list.' },
                    'zero_subscribers': { class: 'error', text: 'No new subscribers to add to list.' },
                    'generic_failure': { class: 'error', text: 'Import failed. Reasons unknown.' },
                };

                if( typeof( messages[ data ] ) === 'undefined' ) {
                    data = 'generic_failure';
                }

                $( '#import_list' ).after( '<div style="margin-top: 20px;" class="' + messages[ data ].class + ' import-response-message"><p>' + messages[ data ].text + '</p></div>' );
            } ).fail( function() {
                    $( '#import_list' ).after( '<div style="margin-top: 20px;" class="error import-response-message"><p>Import Failed</p></div>' );                    
            } ).always( function() {
                $( '#import_list' ).val( 'Import' ).prop( 'disabled', false );
            } );
        }
    } );
} );
