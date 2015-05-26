<?php

class gcms_pilzNewsletter_emailSender
{
    private $databaseManager;
    private $formPrinterAndReader;

    function __construct($databaseManager, $formPrinterAndReader)
    {
        $this->databaseManager = $databaseManager;
        $this->formPrinterAndReader = $formPrinterAndReader;
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
        $this->setEmailContentTypeToHTML();

        foreach($this->databaseManager->getAllNewsletterRecipients() as $recipient)
        {
            $recipientEmailAddress = $recipient->email;
            wp_mail($recipientEmailAddress, 'Pilz-Newsletter', $this->getContentToSend($recipientEmailAddress));
        }
    }

    function  setEmailContentTypeToPlain()
    {
        add_filter( 'wp_mail_content_type', function( $content_type ) {
            return 'text/plain';
        });
    }

    function setEmailContentTypeToHTML()
    {
        add_filter( 'wp_mail_content_type', function( $content_type ) {
            return 'text/html';
        });
    }

    function sendRegistrationConfirmationEmail($emailAddress, $randomNumber)
    {
        $this->setEmailContentTypeToPlain();
        $mailSuccess = wp_mail($emailAddress, 'Confirm your newsletter registration', $this->formPrinterAndReader->generateURLWithRandomNumberParameteToVerifyAspirant($randomNumber));
        return $mailSuccess;
    }

    function getContentToSend($recipientEmailAddress)
    {
        $content = '<h1>myCurrentContentToSend</h1><br><p>mySecondLine</p>'.$this->getUnsubscribeContentForEmail($recipientEmailAddress);
        return $content;
    }

    function getUnsubscribeContentForEmail($recipientEmailAddress)
    {
        return '<br><a href="'.$this->formPrinterAndReader->generateUnsubscribeURLForEmail($recipientEmailAddress).'">Click here to unsubscribe this email address from our newsletter!</a><br>';
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