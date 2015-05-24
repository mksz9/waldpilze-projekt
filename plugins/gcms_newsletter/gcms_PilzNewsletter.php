<?php
    /**
     * Plugin Name: Pilz-Newsletter
     * Description: Erweiterung f�r Pilz-Website zum verschicken von aktuellen Pilzinformationen
     * Version: 0.1
     * Author: Patrick Sippl
     */




/*add_filter('cron_schedules', 'addNewIntervalToSchedules');

function addNewIntervalToSchedules($schedules)
{
    $schedules['minutes_1'] = array('interval'=>10, 'display'=>'Every 10 seconds');
    return $schedules;
}


add_action('periodicalSendPilzNewsletterHook', 'abc');



wp_schedule_event(time(), 'minutes_1', 'periodicalSendPilzNewsletterHook');



function abc()
{
    //error_log("myNewErrorlog");
}*/



if (!class_exists('gcms_pilzNewsletter'))
{
    class gcms_pilzNewsletter
    {
        private $newsletterManager;

        function __construct()
        {
            $this->doIncludes();

            $this->newsletterManager = new gcms_pilzNewsletter_manager();

            register_activation_hook( __FILE__, array($this, 'initializePlugin'));
            register_deactivation_hook( __FILE__, array($this, 'finalizePlugin'));

            add_action('myCustomPilzEvent_newNewsletterStuffToSend', array($this, 'triggerNewsletter'));

        }

        function doIncludes()
        {
            include_once('gcms_pilzNewsletter_manager.php');
            include_once('gcms_pilzNewsletter_databaseManager.php');
            include_once('gcms_pilzNewsletter_unsubscriber.php');
            include_once('gcms_pilzNewsletter_newsletterCreator.php');
            include_once('gcms_pilzNewsletter_newsletterData.php');
            include_once('gcms_pilzNewsletter_emailSender.php');
            include_once('gcms_pilzNewsletter_formPrinterAndReader.php');

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }

        function initializePlugin()
        {
            $this->newsletterManager->initializePlugin();
        }

        function finalizePlugin()
        {
            $this->newsletterManager->finalizePlugin();
        }

        function triggerNewsletter()
        {
            $this->newsletterManager->triggerNewsletter();
        }
    }

    $gcms_pilzNewsletter = new gcms_pilzNewsletter();
}

 
?>