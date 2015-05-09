<?php
    /**
     * Plugin Name: Pilz-Newsletter
     * Description: Erweiterung f�r Pilz-Website zum verschicken von aktuellen Pilzinformationen
     * Version: 0.1
     * Author: Patrick Sippl
     */

    if (!class_exists('gcms_pilzNewsletter'))
    {
        class gcms_pilzNewsletter
        {
            private $newsletterManager;

            function __construct()
            {
                include_once('gcms_pilzNewsletter_manager.php');
                include_once('gcms_pilzNewsletter_databaseManager.php');
                include_once('gcms_pilzNewsletter_subscriber.php');
                include_once('gcms_pilzNewsletter_unsubscriber.php');
                include_once('gcms_pilzNewsletter_newsletterCreator.php');
                include_once('gcms_pilzNewsletter_newsletterData.php');
                include_once('gcms_pilzNewsletter_newsletterSender.php');

                $this->newsletterManager = new gcms_pilzNewsletter_manager();

                register_activation_hook( __FILE__, array($this, 'initializePlugin'));
            }

            function initializePlugin()
            {
                $this->newsletterManager->initializePlugin();
            }
        }

        $gcms_pilzNewsletter = new gcms_pilzNewsletter();
    }

 
?>