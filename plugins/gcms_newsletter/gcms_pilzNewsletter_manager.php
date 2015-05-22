<?php

class gcms_pilzNewsletter_manager
{
    private $databaseManager;
    private $newsletterCreator;
    private $emailSender;
    private $newsletterFormPrinterAndReader;

    function __construct()
    {
        $this->databaseManager = new gcms_pilzNewsletter_databaseManager();
        $this->newsletterCreator = new gcms_pilzNewsletter_newsletterCreator();
        $this->emailSender = new gcms_pilzNewsletter_emailSender($this->databaseManager);
        $this->newsletterFormPrinterAndReader = new gcms_pilzNewsletter_formPrinterAndReader();

        add_shortcode('newsletterHTMLPrint', array($this, 'handleNewsletterHTMLPrintShortCode'));
    }

    function initializePlugin()
    {
        $this->databaseManager->initialize();
        $this->emailSender->initialize();
    }

    function finalizePlugin()
    {
        $this->databaseManager->finalize();
        $this->emailSender->finalize();
    }


    function getRandomNumber()
    {
        return 23049;
    }

    function handleNewsletterHTMLPrintShortCode()
    {
        //global $wpdb;
        //print_r($wpdb->get_results('SELECT * FROM wp_newsletterrecipients'));


        ob_start();

        // registration form sent => add email address to newsletteraspirants and send email to confirm registration for user
        if($this->newsletterFormPrinterAndReader->formToSubscribeForNewsletterSent())
        {
            $emailAddressToSubscribe = $this->newsletterFormPrinterAndReader->getNewSubscribedMailAdressForNewsletter();

            if($this->databaseManager->isEmailAddressAlreadyRecipient($emailAddressToSubscribe))
            {
                $this->newsletterFormPrinterAndReader->printEmailAddressIsAlreadyRecipientHTML();
            }
            // new confirmation email, new random number in db
            else if($this->databaseManager->isEmailAddressAlreadyAspirant($emailAddressToSubscribe))
            {
                $newRandomNumber = $this->getRandomNumber();
                $this->databaseManager->updateAspirant($emailAddressToSubscribe, $newRandomNumber);
                $this->emailSender->sendRegistrationConfirmationEmail($emailAddressToSubscribe, $newRandomNumber);
                $this->newsletterFormPrinterAndReader->printEmailIsAlreadyAspirantHTML();
            }
            // email not known yet, standard process for registration
            else
            {
                $randomNumber = $this->getRandomNumber();
                $this->databaseManager->insertNewAspirant($emailAddressToSubscribe, $randomNumber);
                $this->emailSender->sendRegistrationConfirmationEmail($emailAddressToSubscribe, $randomNumber);
                $this->newsletterFormPrinterAndReader->printEmailToConfirmNewsletterRegistrationSentHTML();
            }
        }
        // confirmation link in confirmation email clicked => add email address to newsletterrecipients
        else if($this->newsletterFormPrinterAndReader->confirmationLinkFromEmailClicked())
        {
            $randomNumberFromConfirmationEmailLink = $this->newsletterFormPrinterAndReader->getRandomNumberFromConfirmationLinkFromEmailLink();

            if($this->databaseManager->isRandomNumberFromConfirmationEmailInAspirantTable($randomNumberFromConfirmationEmailLink))
            {
                $recipientEmailAddress = $this->databaseManager->getEmailAddressForRandomNumberFromConfirmationEmail($randomNumberFromConfirmationEmailLink);
                $this->databaseManager->insertNewRecipient($recipientEmailAddress);
                $this->databaseManager->deleteAspirant($recipientEmailAddress);
                $this->newsletterFormPrinterAndReader->printSuccessfullySubscribedForNewsletterHTML();
            }
            else
            {
                $this->newsletterFormPrinterAndReader->printUnsuccessfullNewsletterConfirmationHTML();
            }
        }
        else
        {
            $this->newsletterFormPrinterAndReader->printSubscribeForNewsletterHTML();
        }

        return ob_get_clean();
    }

}


?>