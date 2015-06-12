<?php

class gcms_pilzNewsletter_emailSender
{
    private $databaseManager;
    private $formPrinterAndReader;

    function __construct($databaseManager, $formPrinterAndReader)
    {
        $this->databaseManager = $databaseManager;
        $this->formPrinterAndReader = $formPrinterAndReader;


        add_action('publish_pilze', array($this, 'sendNewsletter'));
    }

    function sendNewsletter($post_ID)
    {
        $newPost = get_post($post_ID);

        $this->setEmailContentTypeToHTML();

        foreach($this->databaseManager->getAllNewsletterRecipients() as $recipient)
        {
            $recipientEmailAddress = $recipient->email;
            wp_mail($recipientEmailAddress, 'new Pilz: '.$newPost->post_title, $this->getContentWithNewPilzToSend($recipientEmailAddress, $newPost->guid));
        }
    }

    function sendReminder()
    {
        $this->setEmailContentTypeToHTML();

        foreach($this->databaseManager->getAllNewsletterRecipients() as $recipient)
        {
            $recipientEmailAddress = $recipient->email;
            wp_mail($recipientEmailAddress, 'new pilz-content', $this->getReminderContentToSend($recipientEmailAddress));
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

    function getContentWithNewPilzToSend($recipientEmailAddress, $newPilzURL)
    {
        $content = '<p>Dear user, </p><br><p>there is a new pilz on our pilz-site. See the new pilz <a href="'.$newPilzURL.'">here</a></p>'.$this->getUnsubscribeContentForEmail($recipientEmailAddress);
        return $content;
    }

    function getReminderContentToSend($recipientEmailAddress)
    {
        $content = '<p>Dear user, </p><br><p>there is new pilz-content for you to see under <a href="'.home_url().'">here</a></p>'.$this->getUnsubscribeContentForEmail($recipientEmailAddress);
        return $content;
    }

    function getUnsubscribeContentForEmail($recipientEmailAddress)
    {
        return '<br><a href="'.$this->formPrinterAndReader->generateUnsubscribeURLForEmail($recipientEmailAddress).'">Click here to unsubscribe this email address from our newsletter!</a><br>';
    }
}

?>