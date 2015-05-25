<?php

class gcms_pilzNewsletter_manager
{
    private $databaseManager;
    private $newsletterCreator;
    private $newsletterFormPrinterAndReader;
    private $emailSender;

    function __construct()
    {
        $this->databaseManager = new gcms_pilzNewsletter_databaseManager();
        $this->newsletterCreator = new gcms_pilzNewsletter_newsletterCreator();
        $this->newsletterFormPrinterAndReader = new gcms_pilzNewsletter_formPrinterAndReader($this->databaseManager);
        $this->emailSender = new gcms_pilzNewsletter_emailSender($this->databaseManager, $this->newsletterFormPrinterAndReader);

        add_shortcode('newsletterHTMLPrint', array($this, 'handleNewsletterHTMLPrintShortCode'));

    }

    function sendNewsletterForDebugging()
    {
        //to debug - trigger newsletter
        do_action('myCustomPilzEvent_newNewsletterStuffToSend');
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

    function triggerNewsletter()
    {
        $this->emailSender->sendNewsletter();
    }

    function getRandomNumber()
    {
        return rand(0, 99999);
    }

    function handleNewsletterHTMLPrintShortCode()
    {
        ob_start();

        // registration form sent => add email address to newsletteraspirants and send email to confirm registration for user
        if($this->newsletterFormPrinterAndReader->formToSubscribeForNewsletterSent())
        {
            $this->sendNewsletterForDebugging();

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
            $randomNumberFromConfirmationEmailLink = $this->newsletterFormPrinterAndReader->getRandomNumberFromConfirmationLinkFromEmail();

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
        // unsubscribe from newsletter link in normal newsletter email clicked => remove email address from newsletterrecipients
        else if($this->newsletterFormPrinterAndReader->unsubscribeLinkFromEmailClicked())
        {
            $emailAddressToUnsubscribe = $this->newsletterFormPrinterAndReader->getEmailAddressToUnsubscribe();
            $randomNumberToVerifyUnsubscribeSentFromForm = $this->newsletterFormPrinterAndReader->getRandomNumberToVerifyUnsubscribe();

            if($this->databaseManager->isEmailAddressToUnsubscribeMatchingWithRandomNumberToVerifyUnsubscribe($emailAddressToUnsubscribe, $randomNumberToVerifyUnsubscribeSentFromForm))
            {
                $this->databaseManager->deleteRecipient($emailAddressToUnsubscribe);
                $this->newsletterFormPrinterAndReader->printSuccessfullUnsubscribeHTML();
            }
            else
            {
                $this->newsletterFormPrinterAndReader->printUnsuccessfullUnsubscribeHTML();
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