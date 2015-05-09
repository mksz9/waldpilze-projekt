<?php

class gcms_pilzNewsletter_databaseManager
{

    function initialize()
    {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $this->createRecipientsDatabase();
    }

    function createRecipientsDatabase()
    {
        global $wpdb;
        $tableName = $wpdb->prefix."newsletterRecipients";
        $charset_collate = $wpdb->get_charset_collate();

        $sqlCreateQuery = "CREATE TABLE $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email text NOT NULL,
            UNIQUE KEY id (id)
            ) $charset_collate;";

        $this->fireSQLQuery($sqlCreateQuery);
    }

    function fireSQLQuery($query)
    {
        dbDelta( $query );
    }
}

?>