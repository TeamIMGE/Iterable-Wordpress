<?php
/*
Plugin Name: Wordpress Iterable Add-On
Plugin URI: http://www.imge.com
Description: Iterable integration for Wordpress.
Version: 3.0
Author: Chris Lewis
Author URI: http://www.imge.com
*/

require_once( dirname( __FILE__ ) . '/data.php' );
require_once( dirname( __FILE__ ) . '/iterable.php' );
require_once( dirname( __FILE__ ) . '/Update/BFIGitHubPluginUploader.php' );

if( is_admin() ) {
    new BFIGithubPluginUpdater( __FILE__, 'cdlewis', 'Iterable-Wordpress' );
}

add_action( 'admin_init', function() {
    // settings
    register_setting( 'iterable-settings', 'api_key' );
    register_setting( 'iterable-settings', 'listwise_key' );
    register_setting( 'iterable-message-channels', 'message_channels' );
} );

add_action( 'admin_menu', function() {
    add_menu_page( 'Iterable', 'Iterable', 'manage_options', 'iterable', function() {
        require_once( dirname( __FILE__ ) . '/templates/list.php' );
    }, '', 19 );
    add_submenu_page( 'iterable', 'Feeds', 'Feeds', 'manage_options', 'iterable_feed', function() {
        require_once( dirname( __FILE__ ) . '/templates/list.php' );
    } );
    add_submenu_page( 'iterable', 'Add Feed', 'Add Feed', 'manage_options', 'iterable_feed_edit', function() {
        $iterable = new Iterable( get_option( 'api_key' ) );
        require_once( dirname( __FILE__ ) . '/templates/edit.php' );
    } );
    add_submenu_page( 'iterable', 'Import', 'Import', 'manage_options', 'iterable_import', function() {
        $iterable = new Iterable( get_option( 'api_key' ) );
        require_once( dirname( __FILE__ ) . '/templates/import.php' );
    } );
    add_submenu_page( 'iterable', 'Message Channels', 'Message Channels', 'manage_options', 'iterable_message_channels', function() {
        require_once( dirname( __FILE__ ) . '/templates/message_channels.php' );
    } );
    add_submenu_page( 'iterable', 'Settings', 'Settings', 'manage_options', 'iterable_settings', function() {
        require_once( dirname( __FILE__ ) . '/templates/settings.php' );
    } );
} );

/* Manage Message Channels */

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script( 'subscription_page', plugins_url( '/templates/assets/scripts/subscription_options.js', __FILE__ ), array( 'jquery' ), '', true );
} );

add_shortcode( 'subscription_options', function() {
    $iterable = new Iterable( get_option( 'api_key' ) );
    $all_channels = json_decode( get_option( 'message_channels', '[]' ) );

    $user = $iterable->user( $_REQUEST[ 'email' ] );
    $unsubscribed_ids = array();
    if( $user[ 'success' ] &&
        isset( $user[ 'content' ] ) &&
        isset( $user[ 'content' ][ 'unsubscribedMessageTypeIds' ] ) ) {
        foreach( $user[ 'content' ][ 'unsubscribedMessageTypeIds' ] as $i ) {
            $unsubscribed_ids[ $i ] = true;
        }
    }

    if( !$user[ 'success' ] ) {
        trigger_error( $user[ 'error_message' ], E_USER_WARNING );
    }

    ob_start();
    require_once( dirname( __FILE__ ) . '/templates/subscription_options.php' );
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
} );

add_action( 'wp_ajax_updatechannel', function() {
    $iterable = new Iterable( get_option( 'api_key' ) );

    // default to empty array
    $ids;
    if( !isset( $_REQUEST[ 'ids' ] ) || !is_array( $_REQUEST[ 'ids' ] ) ) {
        $ids = array();
    } else {
        $ids = $_REQUEST[ 'ids' ];
    }

    $result = $iterable->user_update_subscriptions( $_REQUEST[ 'email' ], false, false, $ids );
    if( $result[ 'success' ] ) {
        echo 'success';
    } else {
        echo 'failure';
    }
    die();
} );

/* Import Users Action */

add_action( 'wp_ajax_subscribe', function() {
    $iterable = new Iterable( get_option( 'api_key' ) );
    $subscribers = json_decode( stripslashes( $_REQUEST[ 'subscribers' ] ), true );
    $list_id = $_REQUEST[ 'iterablelist' ];
    $resubscribe = $_REQUEST[ 'resubscribe' ] === 'true';

    // make sure override is valid
    $override = array( 'email' => false );
    if( isset( $_REQUEST[ 'override' ] ) && is_array( $_REQUEST[ 'override' ] ) ) {
        $override = $_REQUEST[ 'override' ];
    }

    $all_users = $iterable->export_csv( 'user', 'All', false, false, false, array_keys( $override ) );
    if( !$all_users ) {
        trigger_error( print_r( $all_users, true ), E_USER_WARNING );
        echo 'failure (all_users)';
        die();
    }

    // convert to hashtable
    $all_users = explode( PHP_EOL, $all_users[ 'content' ] );
    $header = str_getcsv( array_shift( $all_users ) );
    $email_hashtable = array();
    foreach( $all_users as $index => &$user ) {
        $entry = array_combine( $header, str_getcsv( $user ) );
        if( $entry === false ) {
            trigger_error( $user, E_USER_WARNING );
        }

        if( $entry !== false ) {
            $email_hashtable[ $entry[ 'email' ] ] = $entry;
            unset( $email_hasbtable[ $entry[ 'email' ] ][ 'email' ] );
        }

        // save as much space as possible -- yes we've run out of memory here before
        unset( $all_users[ $index ] );
    }

    // unset duplicated subscriber information
    foreach( $subscribers as &$subscriber ) {
        if( !isset( $email_hashtable[ $subscriber[ 'email' ] ] ) ) {
            continue;
        }

        $match = $email_hashtable[ $subscriber[ 'email' ] ];
        print_r( $match );
        foreach( $subscriber[ 'dataFields' ] as $key => $value ) {
            if( $value === '' || ( isset( $match[ $key ] ) && $match[ $key ] !== '' && $override[ $key ] === 'false' ) ) {
                unset( $subscriber[ 'dataFields' ][ $key ] );
            }
        }
    }

    unset( $subscriber ); // hurray for mutal loop bugs
    unset( $email_hashtable ); // we're having memory issues!

    // send subscribers to iterable
    $response = $iterable->list_subscribe(
        $list_id,
        $subscribers,
        $resubscribe
    );

    if( $response[ 'success' ] ) {
        echo 'success';
    } else {
        trigger_error( print_r( $response, true ), E_USER_WARNING );
        echo 'fail (list_subscribe)';
    }

    die();
} );

/* Gravityforms Integration */

if( class_exists( 'GFForms' ) && class_exists( 'GFAddOn' ) ) {
    GFForms::include_addon_framework();
    class GFSimpleAddOn extends GFAddOn {
        protected $_version = '1.3';
        protected $_min_gravityforms_version = '1.7.9999';
        protected $_slug = 'iterable';
        protected $_path = 'gravityforms-iterable/index.php';
        protected $_full_path = __FILE__;
        protected $_title = 'Iterable';
        protected $_short_title = 'Iterable';
        private $iterable;

        public function init() {
            parent::init();
            $this->create_ajax_api();
            IterableData::update_table();
            $this->iterable = new Iterable( get_option( 'api_key' ) ); 
            add_action( 'gform_after_submission', array( $this, 'process_feeds' ), 10, 2 );
        }

        // check incoming data fields against existing entry for potential conflicts
        private function remove_existing_fields( $subscriber, $override ) {
            $existing_user = $this->iterable->user( $subscriber[ 'email' ] );
            if( $existing_user[ 'success' ] ) {
                foreach( $override as $key => $value ) {
                    // email is not a valid data field
                    if( $key === 'email' ) {
                        continue;
                    }

                    // override disabled and a valid non-empty field exists
                    if( !$value && isset( $existing_user[ 'content' ][ $key ] ) && $existing_user[ 'content' ][ 'key ' ] !== '' ) {
                        unset( $subscriber[ 'dataFields' ][ $key ] );
                        
                    }
                }

                // datafields is now potentially empty
                if( empty( $subscriber[ 'dataFields' ] ) ) {
                    unset( $subscriber[ 'dataFields' ] );
                }
            }

            return $subscriber;
        }

        public function process_feeds( $entry, $form ) {
            $feeds = IterableData::get_feed_by_form( $form[ 'id' ] );

            if( !$feeds ) {
                trigger_error( 'Iterable: no feeds found' );
                return;
            }

            foreach( $feeds as $feed ) {    
                // structure for subscriber data
                $subscriber = array(
                    'email' => '',
                    'dataFields' => array()
                );

                // check for required checkbox
                if( $feed[ 'meta' ][ 'require_checked' ] != '' && $entry[ $feed[ 'meta' ][ 'require_checked' ] ] == '' ) {
                    trigger_error( 'Require checked test failed for ' . $feed[ 'meta' ][ 'require_checked' ] . print_r( $entry, true ), E_USER_WARNING );
                    continue;
                }

                // email
                $flipped_meta = array_flip( $feed[ 'meta' ][ 'fields' ] );
                $subscriber[ 'email' ] = $entry[ $flipped_meta[ 'email' ] ];
                unset( $feed[ 'meta' ][ 'fields' ][ $flipped_meta[ 'email' ] ] );

                // any other fields
                if( count( $feed[ 'meta' ][ 'fields' ] ) > 0 ) {
                    foreach( $feed[ 'meta' ][ 'fields' ] as $key => $value ) {
                        $subscriber[ 'dataFields' ][ $value ] = $entry[ $key ];
                    }
                } else {
                    unset( $subscriber[ 'dataFields' ] );
                }

                $subscriber = $this->remove_existing_fields( $subscriber, $feed[ 'meta' ][ 'override' ] );

                // validate with listwise
                if( $feed[ 'meta' ][ 'listwise' ] === 'true' ) {
                    try {
                        $cleaned_email = $this->listwise_validate( $subscriber[ 'email' ] );
                        if( !$cleaned_email ) {
                            trigger_error( 'Iterable: email rejected by listwise' );
                            continue;
                        }

                        $subscriber[ 'email' ] = $cleaned_email;
                    } catch( Exception $e ) {
                        trigger_error( 'Iterable: http request failed', $e );
                    }
                }

                $response = $this->iterable->list_subscribe(
                    $feed[ 'meta' ][ 'iterablelist' ],
                    array( $subscriber ),
                    $feed[ 'meta' ][ 'resubscribe' ] == true
                );

                if( !$response[ 'success' ] ) {
                    trigger_error( 'Error subscribing user >>> ' . print_r( $response, true ), E_USER_ERROR );
                }
            }
        }

        public function listwise_validate( $email ) {
            // assume valid if there's no api key
            if( get_option( 'listwise_key', '' ) === '' ) {
                trigger_error( 'Listwise API Key not entered', E_USER_WARNING );
                return $email;
            }

            $url = 'https://api.listwisehq.com/clean/quick.php?email=%s&api_key=%s';
            $result = wp_remote_get( sprintf(
                $url,
                urlencode( $email ),
                get_option( 'listwise_key' )
            ), array( 'timeout' => 10 ) );

            // if api request fails assume the email is clean
            if( is_wp_error( $result ) ) {
                trigger_error( 'Iterable: listwise api request failed', E_USER_ERROR );
                return $email;
            }

            $body = json_decode( wp_remote_retrieve_body( $result ) );

            // email not clean
            if( isset( $body->email_status ) && $body->email_status !== 'clean' ) {
                trigger_error( 'Iterable: skipping email as unclean', E_USER_WARNING );
                return false;
            }

            // if we get here the email is clean
            return $body->email;
        }

        public function create_ajax_api() {
            $iterable = new Iterable( get_option( 'api_key' ) );

            add_action( 'wp_ajax_gravityformfieldsbyid', function() {
                $form = GFAPI::get_form( $_REQUEST[ 'id' ] );
                echo json_encode( $form[ 'fields' ] );
                die();
            } );
            add_action( 'wp_ajax_saveiterablefeed', function() {
                $map_fields = json_decode( stripslashes( $_REQUEST[ 'map_fields' ] ), true );
                $map_override = json_decode( stripslashes( $_REQUEST[ 'map_override' ] ), true );

                // store feed names in meta
                $meta[ 'fields' ] = ( $map_fields ) ? $map_fields : array();
                $meta[ 'override' ] = ( $map_override ) ? $map_override : array();
                $meta[ 'iterablelist' ] = $_REQUEST[ 'iterablelist' ];
                $meta[ 'iterablelist_name' ] = $_REQUEST[ 'iterablelist_name' ];
                $meta[ 'gravityform_name' ] = $_REQUEST[ 'gravityform_name' ];
                $meta[ 'resubscribe' ] = $_REQUEST[ 'resubscribe' ];
                $meta[ 'listwise' ] = $_REQUEST[ 'listwise' ];
                $meta[ 'require_checked' ] = $_REQUEST[ 'require_checked' ];

                echo IterableData::update_feed( $_POST[ 'feed_id' ], $_POST[ 'gravityform' ], true, $meta );
                die();
            } );
            add_action( 'wp_ajax_deleteiterablefeed', function() {
                echo IterableData::delete_feed( $_REQUEST[ 'id' ] );
                die();
            } );
        }
    }

    new GFSimpleAddOn();
} else {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p>Cannot load Iterable plugin. GravityForms is either not installed or needs to be updated.</p></div>';
    } );
}
