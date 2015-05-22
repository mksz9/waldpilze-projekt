<?php

class gcms_pilzNewsletter_emailSender
{
    private $databaseManager;

    function __construct($databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    function initialize()
    {
        $this->startScheduledSending();
    }

    function finalize()
    {
        $this->stopScheduledSending();
    }

    function sendNewsletter()
    {
        foreach($this->databaseManager->getAllNewsletterRecipients() as $recipient)
        {
            // DOO
            //echo $recipient->email;
        }


        wp_mail('Patrick.Sippl@t-online.de', 'mySubject', $this->getCurrentContentToSend());
    }

    function sendRegistrationConfirmationEmail($emailAddress, $randomNumber)
    {
        wp_mail($emailAddress, 'Confirm your newsletter registration', $this->generateFullURLWithRandomNumberParameter($randomNumber));
    }

    function  generateFullURLWithRandomNumberParameter($randomNumber)
    {
        return "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'&randomNumber='.$randomNumber;
    }

    function getCurrentContentToSend()
    {
        return "myCurrentContentToSend";
    }






    function startScheduledSending()
    {
        //$this->addScheduledIntervalToWpSchedules();


        add_action('periodicalSendPilzNewsletterHook', array($this, 'sendNewsletter'));



        wp_schedule_event(time(), 'minutes_1', 'periodicalSendPilzNewsletterHook');

    }

    function addScheduledIntervalToWpSchedules()
    {
        add_filter('cron_schedules', array($this, 'addNewIntervalToSchedules'));
    }

    function addNewIntervalToSchedules($schedules)
    {
        $schedules['minutes_1'] = array('interval'=>10, 'display'=>'Every 10 seconds');
        return $schedules;
    }

    function  stopScheduledSending()
    {
        wp_clear_scheduled_hook('periodicalSendPilzNewsletterHook');
    }
}

?>