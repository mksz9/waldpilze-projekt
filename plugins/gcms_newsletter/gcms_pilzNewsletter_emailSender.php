<?php

class gcms_pilzNewsletter_emailSender
{
    private $databaseManager;
    private $formPrinterAndReader;

    function __construct($databaseManager, $formPrinterAndReader)
    {
        $this->databaseManager = $databaseManager;
        $this->formPrinterAndReader = $formPrinterAndReader;



        add_action('newPilzContent', array($this, 'sendNewsletter'));
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

    function setEmailContentTypeToPlain()
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
        $this->setEmailContentTypeToHTML();
        $mailSuccess = false;
        if($this->isValidEmailAddress($emailAddress))
        {
            $mailSuccess = wp_mail($emailAddress, 'Confirm your newsletter registration', 'To confirm you newsletter registration please click on the following link<br>' . $this->formPrinterAndReader->generateURLWithRandomNumberParameteToVerifyAspirant($randomNumber));
        }
        return $mailSuccess;
    }

    function isValidEmailAddress($emailAddress)
    {
        return filter_var($emailAddress, FILTER_VALIDATE_EMAIL);
    }

    function getContentToSend($recipientEmailAddress)
    {
        $content = '<h1>PilzReminder</h1><br><p>There is new content on our pilz-Site. Visit us again <a href="'.home_url().'">here</a></p>'.$this->getUnsubscribeContentForEmail($recipientEmailAddress);
        return $content;
    }

    function getUnsubscribeContentForEmail($recipientEmailAddress)
    {
        return '<br><a href="'.$this->formPrinterAndReader->generateUnsubscribeURLForEmail($recipientEmailAddress).'">Click here to unsubscribe this email address from our newsletter!</a><br>';
    }
}

?>