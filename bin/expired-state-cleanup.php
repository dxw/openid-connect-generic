#!/usr/bin/env php
<?php

if (php_sapi_name() !== 'cli') {
    exit(1);
}

if (count($argv) !== 2) {
    echo "Usage: php expired-state-cleanup.php /path/to/wp-load.php\n";
    exit(1);
}

// WP boilerplate
define('WP_INSTALLING', true);
include_once $argv[1];
// End WP boilerplate

$open_id_settings = get_option('openid_connect_generic_settings');
$expiration_time_in_seconds = 180;
if(isset($open_id_settings['state_time_limit']) && (int)$open_id_settings['state_time_limit'] > 0) {
    $expiration_time_in_seconds = (int)$open_id_settings['state_time_limit'];
}

error_log('OpenID Connect Generic Client: Starting expired state cleanup, cleaning tokens older than ' . $expiration_time_in_seconds . ' seconds');

$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE \"openid-connect-generic-state-%\"", OBJECT );

foreach($results as $result) {
    if ( isset( $result->option_value ) && (maybe_unserialize($result->option_value) + $expiration_time_in_seconds) < time() ) {
        delete_option( $result->option_name );
    }
}


error_log('OpenID Connect Generic Client: Expired state cleanup complete');

exit(0);
