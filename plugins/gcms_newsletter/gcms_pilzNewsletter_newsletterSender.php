<?php

class gcms_pilzNewsletter_newsletterSender
{
    function sendNewsletter($newsletterData)
    {
        wp_mail('Patrick.Sippl@t-online.de', 'mySubject', 'myMessage');
    }
}

?>