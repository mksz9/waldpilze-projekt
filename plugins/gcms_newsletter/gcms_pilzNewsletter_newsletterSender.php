<?php

class gcms_pilzNewsletter_newsletterSender
{
    function initialize()
    {
        $this->startScheduledSending();
    }

    function finalize()
    {
        $this->stopScheduledSending();
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

    function sendNewsletter()
    {
        //echo "heyho";
        error_log("myPeriodicalEvent");

        wp_mail('Patrick.Sippl@t-online.de', 'mySubject', $this->getCurrentContentToSend());
    }

    function getCurrentContentToSend()
    {
        return "myCurrentContentToSend";
    }
}

?>