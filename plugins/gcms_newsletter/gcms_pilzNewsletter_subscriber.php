<?php

class gcms_pilzNewsletter_subscriber
{
    function initialize()
    {
        $this->startListenToSubscribers();
    }

	function startListenToSubscribers()
    {
        add_action('newSubscribe', array($this, 'subscribe'));

        //do_action('newSubscribe');
    }

    function subscribe($emailToSubscribe)
    {
        //subscribe email


        $sender = new gcms_pilzNewsletter_newsletterSender();
        $sender->sendNewsletter("heyho");

        //echo "heyho222";
    }
}

?>