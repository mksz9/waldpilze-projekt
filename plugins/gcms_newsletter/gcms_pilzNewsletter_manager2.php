<?php

class gcms_pilzNewsletter_manager
{
    private $databaseManager;
    private $newsletterCreator;
    private $newsletterSender;
    private $newsletterFormPrinterAndReader;

    function __construct()
    {
        //$this->databaseManager = new gcms_pilzNewsletter_databaseManager();
        //$this->newsletterCreator = new gcms_pilzNewsletter_newsletterCreator();
        //$this->newsletterSender = new gcms_pilzNewsletter_newsletterSender();
        //$this->newsletterFormPrinterAndReader = new gcms_pilzNewsletter_formPrinterAndReader();

        //add_shortcode('newsletterHTMLPrint', array($this, 'handleNewsletterHTMLPrintShortCode'));
    }

    function initializePlugin()
    {
        //$this->databaseManager->initialize();
        //$this->newsletterSender->initialize();
    }

    function finalizePlugin()
    {
        //$this->newsletterSender->finalize();
    }

    function handleNewsletterHTMLPrintShortCode()
    {
        ob_start();

        if($this->newsletterFormPrinterAndReader->formToSubscribeForNewsletterSent())
        {
            $newMailAddressToSubscribe = $this->newsletterFormPrinterAndReader->getNewSubscribedMailAdressForNewsletter();
            $this->databaseManager->insertNewEmailAdressForNewsletter($newMailAddressToSubscribe);
            $this->newsletterFormPrinterAndReader->printSuccessfullySubscribedForNewsletterHTML();
        }
        else
        {
            $this->newsletterFormPrinterAndReader->printSubscribeForNewsletterHTML();

        }

        return ob_get_clean();
    }

}


?>



