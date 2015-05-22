<?php

    class gcms_pilzNewsletter_databaseManager
    {
        function initialize()
        {
            $this->createRecipientsTable();
            $this->createAspirantsTable();
        }

        function  finalize()
        {
            $this->dropAspirantsTable();
            $this->dropRecipientsTable();
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

        function getRecipientsTableName()
        {
            return $this->getDbPrefix()."newsletterRecipients";
        }

        function getAspirantsTableName()
        {
            return $this->getDbPrefix()."newsletterAspirants";
        }

        function createAspirantsTable()
        {
            $charset_collate = $this->getDbCharsetCollate();
            $tableName = $this->getAspirantsTableName();

            $sqlCreateQuery = "CREATE TABLE $tableName (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                email text NOT NULL,
                randomNumber mediumint(9),
                UNIQUE KEY id (id)
                ) $charset_collate;";

            dbDelta($sqlCreateQuery);
        }

        function createRecipientsTable()
        {
            $charset_collate = $this->getDbCharsetCollate();
            $tableName = $this->getRecipientsTableName();

            $sqlCreateQuery = "CREATE TABLE $tableName (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                email text NOT NULL,
                UNIQUE KEY id (id)
                ) $charset_collate;";

            dbDelta($sqlCreateQuery);
        }

        function dropAspirantsTable()
        {
            global $wpdb;
            $wpdb->query('DROP TABLE '.$this->getAspirantsTableName());
        }

        function dropRecipientsTable()
        {
            global $wpdb;
            $wpdb->query('DROP TABLE '.$this->getRecipientsTableName());
        }


        function insertNewRecipient($emailAddress)
        {
            echo $emailAddress;
            global $wpdb;
            $wpdb->insert($this->getRecipientsTableName(), array('email' => $emailAddress));
        }

        function isEmailAddressAlreadyAspirant($emailAddress)
        {
            global $wpdb;
            $result = $wpdb->query('SELECT * FROM '.$this->getAspirantsTableName().' WHERE email=\''.$emailAddress.'\'');
            return $result >= 1;
        }

        function isEmailAddressAlreadyRecipient($emailAddress)
        {
            global $wpdb;
            $result = $wpdb->query('SELECT * FROM '.$this->getRecipientsTableName().' WHERE email=\''.$emailAddress.'\'');
            return $result >= 1;
        }

        function insertNewAspirant($aspirantEmailAddress, $randomNumber)
        {
            global $wpdb;
            $wpdb->insert($this->getAspirantsTableName(), array('email' => $aspirantEmailAddress, 'randomNumber' => $randomNumber));
        }

        function updateAspirant($aspirantEmailAddress, $newRandomNumber)
        {
            $updateAspirantQuery = 'UPDATE '.$this->getAspirantsTableName().' SET randomNumber=\''.$newRandomNumber.'\' WHERE email=\''.$aspirantEmailAddress.'\'';
            global $wpdb;
            $wpdb->query($wpdb->prepare($updateAspirantQuery));
        }

        function deleteRecipient($emailAddressToDelete) // funktioniert, wird aber noch nicht verwendet
        {
            global $wpdb;
            $wpdb->delete($this->getRecipientsTableName(), array('email' => $emailAddressToDelete));
        }

        function deleteAspirant($emailAddressToDelete)
        {
            global $wpdb;
            $wpdb->delete($this->getAspirantsTableName(), array('email' => $emailAddressToDelete));
        }

        function getAllNewsletterRecipients()
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM " . $this->getRecipientsTableName());
        }

        function isRandomNumberFromConfirmationEmailInAspirantTable($randomNumber)
        {
            global $wpdb;
            $result = $wpdb->query('SELECT * FROM '.$this->getAspirantsTableName().' WHERE randomNumber='.$randomNumber);
            return $result >= 1;
        }

        function getEmailAddressForRandomNumberFromConfirmationEmail($randomNumber)
        {
            global $wpdb;
            $result = $wpdb->get_row('SELECT email FROM '.$this->getAspirantsTableName().' WHERE randomNumber=\''.$randomNumber.'\'');
            return $result->email;
        }
    }

?>