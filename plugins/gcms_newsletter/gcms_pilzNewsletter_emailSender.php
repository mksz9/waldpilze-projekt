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
            wp_mail($recipientEmailAddress, __('new mushroom: ', 'gcms_newsletter').$newPost->post_title, $this->getContentWithNewPilzToSend($recipientEmailAddress, $newPost->guid));
        }
    }

    function sendReminder()
    {
        $this->setEmailContentTypeToHTML();
        foreach($this->databaseManager->getAllNewsletterRecipients() as $recipient)
        {
            $recipientEmailAddress = $recipient->email;
            wp_mail($recipientEmailAddress, __('new mushroom-content', 'gcms_newsletter'), $this->getReminderContentToSend($recipientEmailAddress));
        }
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
            $mailSuccess = wp_mail($emailAddress, __('Confirm your newsletter registration', 'gcms_newsletter'), __('To confirm you newsletter registration please click on the following link', 'gcms_newsletter').'<br>' . $this->formPrinterAndReader->generateURLWithRandomNumberParameteToVerifyAspirant($randomNumber));
        }
        return $mailSuccess;
    }

    function isValidEmailAddress($emailAddress)
    {
        return filter_var($emailAddress, FILTER_VALIDATE_EMAIL);
    }

    function getContentWithNewPilzToSend($recipientEmailAddress, $newPilzURL)
    {
        $content = '<p>'. __('Dear user, ', 'gcms_newsletter').'</p><br><p>' . __('there is a new pilz on our pilz-site. See the new pilz', 'gcms_newsletter').' <a href="'.$newPilzURL.'">' . __('here', 'gcms_newsletter') . '</a></p>'.$this->getUnsubscribeContentForEmail($recipientEmailAddress);
        return $content;
    }

    function getReminderContentToSend($recipientEmailAddress)
    {
        $content = '<p>'. __('Dear user, ', 'gcms_newsletter'). '</p><br><p>' . __('there is new pilz-content for you to see ', 'gcms_newsletter') . '<a href="'.home_url().'">'.  __('here', 'gcms_newsletter') .'</a></p>'.$this->getUnsubscribeContentForEmail($recipientEmailAddress);
        return $content;
    }

    function getUnsubscribeContentForEmail($recipientEmailAddress)
    {
        return '<br><a href="'.$this->formPrinterAndReader->generateUnsubscribeURLForEmail($recipientEmailAddress).'">' . __('Click here to unsubscribe this email address from our newsletter!', 'gcms_newsletter') . '</a><br>';
    }
}

?>
