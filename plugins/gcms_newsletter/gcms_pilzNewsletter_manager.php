<?php

class gcms_pilzNewsletter_manager
{
    private $databaseManager;
    private $subscriber;
    private $unsubscriber;
    private $newsletterCreator;
    private $newsletterSender;

    function __construct()
    {
        $this->databaseManager = new gcms_pilzNewsletter_databaseManager();
        $this->subscriber = new gcms_pilzNewsletter_subscriber();
        $this->unsubscriber = new gcms_pilzNewsletter_unsubscriber();
        $this->newsletterCreator = new gcms_pilzNewsletter_newsletterCreator();
        $this->newsletterSender = new gcms_pilzNewsletter_newsletterSender();
    }

    function initializePlugin()
    {
        $this->databaseManager->initialize();
        $this->subscriber->initialize();
        $this->newsletterSender->initialize();





        //$this->doTestStuff();
    }

    function finalizePlugin()
    {
        $this->newsletterSender->finalize();
    }

    function doTestStuff()
    {
        $this->newsletterSender->sendNewsletter("asdjf");
    }
}

?>