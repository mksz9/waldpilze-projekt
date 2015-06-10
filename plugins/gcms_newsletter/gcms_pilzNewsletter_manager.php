<?php

class gcms_pilzNewsletter_manager
{
    private $databaseManager;
    private $newsletterFormPrinterAndReader;
    private $emailSender;
    private $captcha;
    private $unsubscribeSiteManager;

    function __construct()
    {
        $this->databaseManager = new gcms_pilzNewsletter_databaseManager();
        $this->captcha = new gcms_pilzNewsletter_captcha();
        $this->unsubscribeSiteManager = new gcms_pilzNewsletter_unsubscribeSiteManager();
        $this->newsletterFormPrinterAndReader = new gcms_pilzNewsletter_formPrinterAndReader($this->databaseManager, $this->captcha, $this->unsubscribeSiteManager);
        $this->emailSender = new gcms_pilzNewsletter_emailSender($this->databaseManager, $this->newsletterFormPrinterAndReader);

        if(is_admin())
        {
            new gcms_pilzNewsletter_adminPage($this->emailSender, $this->databaseManager);
        }

        add_shortcode('newsletterHTMLPrint', array($this, 'handleNewsletterHTMLPrintShortCode'));


        if($this->newsletterFormPrinterAndReader->unsubscribeLinkFromEmailClicked())
        {
            $this->doUnsubscribe();
        }

        //echo $this->newsletterFormPrinterAndReader->generateUnsubscribeURLForEmail('Patrick.Sippl@t-online.de');
    }

    function initializePlugin()
    {
        $this->databaseManager->initialize();
        $this->unsubscribeSiteManager->addUnsubscribeInfoPageToWordpressPages();
    }

    function finalizePlugin()
    {
        $this->databaseManager->finalize();
    }

    function triggerNewsletter()
    {
        $this->emailSender->sendNewsletter();
    }

    function getRandomNumber()
    {
        return rand(0, 99999);
    }

    function doUnsubscribe()
    {
        $emailAddressToUnsubscribe = $this->newsletterFormPrinterAndReader->getEmailAddressToUnsubscribe(); // get email address from get parameter from clicked unsubscribe link in a newsletter
        $randomNumberToVerifyUnsubscribeSentFromForm = $this->newsletterFormPrinterAndReader->getRandomNumberToVerifyUnsubscribe(); // get the random number from clicked unsubscribe link in a newsletter

        if($this->databaseManager->isEmailAddressToUnsubscribeMatchingWithRandomNumberToVerifyUnsubscribe($emailAddressToUnsubscribe, $randomNumberToVerifyUnsubscribeSentFromForm)) // compare the random number from the unsubscribe link in a newsletter email with the random number (belonging to the email address) saved in the newsletter-recipients-table so not everyone can unsubscribe another email address
        {
            $this->databaseManager->deleteRecipient($emailAddressToUnsubscribe); // delete email address from newsletter-recipients-table
            $this->newsletterFormPrinterAndReader->printSuccessfullUnsubscribeHTML();
        }
        else
        {
            $this->newsletterFormPrinterAndReader->printUnsuccessfullUnsubscribeHTML();
        }

    }

    function handleNewsletterHTMLPrintShortCode()
    {
        ob_start();

        // registration form sent => add email address to newsletteraspirants and send email to confirm registration for user
        if($this->newsletterFormPrinterAndReader->formToSubscribeForNewsletterSent())
        {
			if($this->captcha->isCaptchaPluginActive() && !$this->newsletterFormPrinterAndReader->isCaptchaValid())
            {
                echo 'Invalid captcha';
                return; //no more actions because of security
            }

            $emailAddressToSubscribe = $this->newsletterFormPrinterAndReader->getNewSubscribedMailAdressForNewsletter(); //entered email address in input field

            if($this->databaseManager->isEmailAddressAlreadyRecipient($emailAddressToSubscribe)) //entered email address is already a recipient
            {
                $this->newsletterFormPrinterAndReader->printEmailAddressIsAlreadyRecipientHTML();
            }
            // new confirmation email and new random number in db if entered email address is already an aspirant (aspirant != recipient!!!!)
            else if($this->databaseManager->isEmailAddressAlreadyAspirant($emailAddressToSubscribe))
            {
                $newRandomNumber = $this->getRandomNumber(); // random number to identify email address when link in confirmation email was clicked
                $this->databaseManager->updateAspirant($emailAddressToSubscribe, $newRandomNumber); // update random number of email address in newsletter-aspirants-table in database
                $this->emailSender->sendRegistrationConfirmationEmail($emailAddressToSubscribe, $newRandomNumber); // send registration email to entered email address, user has to click link in email to complete registration
                $this->newsletterFormPrinterAndReader->printEmailIsAlreadyAspirantHTML();
            }
            // email not known yet, standard process for registration
            else
            {
                $randomNumber = $this->getRandomNumber(); // random number to identify email address when link in confirmation email was clicked
                $this->databaseManager->insertNewAspirant($emailAddressToSubscribe, $randomNumber); // write entered email address with random number in newsletter-aspirants-table in database
                $registrationMailSuccess = $this->emailSender->sendRegistrationConfirmationEmail($emailAddressToSubscribe, $randomNumber); // send registration email to entered email address, user has to click link in email to complete registration
                if($registrationMailSuccess == true)
                {
                    $this->newsletterFormPrinterAndReader->printEmailToConfirmNewsletterRegistrationSentHTML();
                }
                else
                {
                    $this->databaseManager->deleteAspirant($emailAddressToSubscribe); // delete entered email address from aspriantstable again because registration mail failed
                    $this->newsletterFormPrinterAndReader->printUnsuccessfullRegistrationHTML();
                }
            }
        }
        // confirmation link in confirmation email clicked => add email address to newsletterrecipients
        else if($this->newsletterFormPrinterAndReader->confirmationLinkFromEmailClicked())
        {
            $randomNumberFromConfirmationEmailLink = $this->newsletterFormPrinterAndReader->getRandomNumberFromConfirmationLinkFromEmail(); // get random number from get parameter from link in registration email

            if($this->databaseManager->isRandomNumberFromConfirmationEmailInAspirantTable($randomNumberFromConfirmationEmailLink)) // check if the random number is in newsletter-aspirants-table
            {
                $recipientEmailAddress = $this->databaseManager->getEmailAddressForRandomNumberFromConfirmationEmail($randomNumberFromConfirmationEmailLink); // get the email address to the random number out of the newsletter-aspirants-table in database
                $this->databaseManager->insertNewRecipient($recipientEmailAddress); // add email address to recipients-table
                $this->databaseManager->deleteAspirant($recipientEmailAddress); // delete email address from aspirants-table
                $this->newsletterFormPrinterAndReader->printSuccessfullySubscribedForNewsletterHTML();
            }
            else
            {
                $this->newsletterFormPrinterAndReader->printUnsuccessfullNewsletterConfirmationHTML();
            }
        }
        // unsubscribe from newsletter link in normal newsletter email clicked => remove email address from newsletterrecipients
//        else if($this->newsletterFormPrinterAndReader->unsubscribeLinkFromEmailClicked())
//        {
//            $emailAddressToUnsubscribe = $this->newsletterFormPrinterAndReader->getEmailAddressToUnsubscribe(); // get email address from get parameter from clicked unsubscribe link in a newsletter
//            $randomNumberToVerifyUnsubscribeSentFromForm = $this->newsletterFormPrinterAndReader->getRandomNumberToVerifyUnsubscribe(); // get the random number from clicked unsubscribe link in a newsletter
//
//            if($this->databaseManager->isEmailAddressToUnsubscribeMatchingWithRandomNumberToVerifyUnsubscribe($emailAddressToUnsubscribe, $randomNumberToVerifyUnsubscribeSentFromForm)) // compare the random number from the unsubscribe link in a newsletter email with the random number (belonging to the email address) saved in the newsletter-recipients-table so not everyone can unsubscribe another email address
//            {
//                $this->databaseManager->deleteRecipient($emailAddressToUnsubscribe); // delete email address from newsletter-recipients-table
//                $this->newsletterFormPrinterAndReader->printSuccessfullUnsubscribeHTML();
//            }
//            else
//            {
//                $this->newsletterFormPrinterAndReader->printUnsuccessfullUnsubscribeHTML();
//            }
//        }
        else
        {
            $this->newsletterFormPrinterAndReader->printSubscribeForNewsletterHTML();
        }

        return ob_get_clean();
    }

}


?>