<?php
// uninstall.php

    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        exit();
    }

    if( !WP_UNINSTALL_PLUGIN ){
        exit();
    }

    $sqlDropTableQuery = "DROP TABLE wp_newsletterrecipients";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sqlDropTableQuery );

?>