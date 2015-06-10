<?php

function filtered_user_fields( $iterable ) {
    $results = $iterable->user_fields();
    $supress = json_decode( get_option( 'iterable-supress-fields' ) );
    if( $results[ 'success' ] && is_array( $supress ) ) {
        $results[ 'content' ] = array_diff( $results[ 'content' ], $supress );
    } else {
        die();
    }
    return $results;
}
