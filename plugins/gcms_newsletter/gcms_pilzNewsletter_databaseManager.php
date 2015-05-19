<?php

    class gcms_pilzNewsletter_databaseManager
    {
        function initialize()
        {
            $this->createRecipientsDatabase();
        }

        function getDbPrefix()
        {
            global $wpdb;
            return $wpdb->prefix;
        }

        function getDbCharsetCollate()
        {
            global $wpdb;
            return $wpdb->get_charset_collate();
        }

        function getTableName()
        {
            return $this->getDbPrefix()."newsletterRecipients";
        }

        function createRecipientsDatabase()
        {
            $charset_collate = $this->getDbCharsetCollate();
            $tableName = $this->getTableName();

            $sqlCreateQuery = "CREATE TABLE $tableName (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                email text NOT NULL,
                UNIQUE KEY id (id)
                ) $charset_collate;";

            dbDelta($sqlCreateQuery);
        }

        function insertNewEmailAddressForNewsletter($newEmailAddress)
        {
            global $wpdb;
            $wpdb->insert($this->getTableName(), array('email' => $newEmailAddress));
        }

        function deleteEmailAddressFromNewsletter($emailAddressToDelete) // funktioniert, wird aber noch nicht verwendet
        {
            global $wpdb;
            $wpdb->delete($this->getTableName(), array('email' => $emailAddressToDelete));
        }

        function getAllNewsletterRecipients()
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM " . $this->getTableName());
        }

        function fireSQLQuery($query)
        {
            dbDelta($query);
        }
    }

?>