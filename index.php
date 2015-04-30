<?php
/*
Plugin Name: Wordpress Iterable Add-On
Plugin URI: http://www.imge.com
Description: Iterable integration for Wordpress.
Version: 4.1.1
Author: Chris Lewis
Author URI: http://www.imge.com
*/

define( 'VERSION', '4.1.1' );

require_once( dirname( __FILE__ ) . '/data.php' );
require_once( dirname( __FILE__ ) . '/iterable.php' );


if( is_admin() ) {
    require_once( dirname( __FILE__ ) . '/Update/BFIGitHubPluginUploader.php' );
    new BFIGithubPluginUpdater( __FILE__, 'cdlewis', 'Iterable-Wordpress' );
}

add_action( 'admin_init', function() {
    // settings
    register_setting( 'iterable-settings', 'api_key' );
    register_setting( 'iterable-settings', 'listwise_key' );
    register_setting( 'iterable-message-channels', 'message_channels' );
    register_setting( 'iterable-campaigns', 'campaigns' );
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
    add_submenu_page( 'iterable', 'Campaigns', 'Campaigns', 'manage_options', 'iterable_campaigns', function() {
        $iterable = new Iterable( get_option( 'api_key' ) );
        require_once( dirname( __FILE__ ) . '/templates/campaigns.php' );
    } );
    add_submenu_page( 'iterable', 'Settings', 'Settings', 'manage_options', 'iterable_settings', function() {
        require_once( dirname( __FILE__ ) . '/templates/settings.php' );
    } );
} );

/* Campaigns */

add_filter( 'cron_schedules', function( $interval ) {
    $interval[ 'minutes_10' ] = array(
        'interval' => 10 * 60,
        'display' => 'Once Every 10 Minutes'
    );
    return $interval;
} );

register_activation_hook( __FILE__, function() {
    if( !wp_next_scheduled( 'iterablecampaignshook' ) ) {
        wp_schedule_event( time(), 'minutes_10', 'iterablecampaignshook' );
    }
} );

add_action( 'iterablecampaignshook', function() {
    trigger_error( 'Iterable Campaigns Hook: Start >> ' . time(), E_USER_WARNING );
    $campaigns = json_decode( get_option( 'campaigns' ), true );
    $one_hour = 60 * 60;
    $one_day = $one_hour * 24;
    if( $campaigns ) {
        $changed = false;
        foreach( $campaigns as &$c ) {
            // make sure times are ints
            $c[ 'last_send' ] = intval( $c[ 'last_send' ] );

            date_default_timezone_set( get_option( 'timezone_string' ) );
            $send_time = strtotime( $c[ 'send_at' ] );

            // Has this happened already today?
            if( isset( $c[ 'last_send' ] ) && date( 'd', time() ) == date( 'd', $c[ 'last_send' ] ) ) {
                trigger_error( 'Send has already occurred today', E_USER_WARNING );
                continue;
            }

            if( is_numeric( $c[ 'suppression_list_ids' ] ) ) {
                $c[ 'suppression_list_ids' ] = array( $c[ 'suppression_list_ids' ] );
            } else {
                $c[ 'suppression_list_ids' ] = false;
            }

            // Is it time for the send today?
            if( time() >= $send_time - $one_hour && time() <= $send_time ) {
                trigger_error( 'Trigger send time for ' . date( 'Y-m-d H:i:s', $send_time ), E_USER_WARNING );
                $iterable = new Iterable( get_option( 'api_key' ) );
                trigger_error( 'send_at ' . $c[ 'send_at' ] . ' send time ' . $send_time, E_USER_WARNING );
                $result = $iterable->campaigns_create(
                    $c[ 'name' ],
                    $c[ 'list_id' ],
                    $c[ 'template_id' ],
                    $c[ 'suppression_list_ids' ],
                    gmdate( 'Y-m-d H:i:s', $send_time )
                );

                if( !$result[ 'success' ] ) {
                    trigger_error( 'Error sending campaign' . print_r( $result, true ), E_USER_WARNING );
                }

                trigger_error( 'iterable campaigns create >>> ' . print_r( $result, true ), E_USER_WARNING );

                $c[ 'last_send' ] = time();
                $changed = true;
            }  else {
                trigger_error( 'Not send time yet >> ' . print_r( $c, true ) . ' >> ' . $send_time . ' >> ' . time(), E_USER_WARNING );
            }
if( $changed ) {
                update_option( 'campaigns', json_encode( $campaigns ) );
            }
        }
        unset( $c );
    } else {
        trigger_error( 'Campaigns is null', E_USER_WARNING );
    }
} );

register_deactivation_hook( __FILE__, function() {
    wp_clear_scheduled_hook( 'iterablecampaignshook' );
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
    unset( $_REQUEST[ 'subscribers' ] );
    $list_id = $_REQUEST[ 'iterablelist' ];
    $resubscribe = $_REQUEST[ 'resubscribe' ] === 'true';

    // make sure override is valid
    $override = array( 'email' => false );
    if( isset( $_REQUEST[ 'override' ] ) && is_array( $_REQUEST[ 'override' ] ) ) {
        $override = $_REQUEST[ 'override' ];
    }

    $all_users_req = $iterable->export_csv( 'user', 'All', false, false, false, array_keys( $override ) );
    if( !$all_users_req ) {
        trigger_error( print_r( $all_users, true ), E_USER_WARNING );
        echo 'failure (all_users)';
        die();
    }

    // convert to hashtable
    $all_users = explode( PHP_EOL, $all_users_req[ 'content' ] );
    unset( $all_users_req ); // not taking any chances with php gc
    $header = str_getcsv( array_shift( $all_users ) );
    $email_hashtable = array();
    foreach( $all_users as $index => $user ) {
        $entry = array_combine( $header, str_getcsv( $user ) );
        if( $entry === false ) {
            trigger_error( $user, E_USER_WARNING );
        }

        if( $entry !== false ) {
            $email = $entry[ 'email' ];
            unset( $entry[ 'email' ] );

            $email_hashtable[ $entry[ 'email' ] ] = gzcompress( json_encode( $entry ) );
        }

        unset( $user, $all_users[ $index ] );
    }

    // unset duplicated subscriber information
    foreach( $subscribers as &$subscriber ) {
        if( !isset( $email_hashtable[ $subscriber[ 'email' ] ] ) ) {
            continue;
        }

        $match = json_decode( gzuncompress( $email_hashtable[ $subscriber[ 'email' ] ] ), true );
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
