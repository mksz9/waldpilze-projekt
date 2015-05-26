<?php

class gcms_pilzNewsletter_adminPage
{
    const postParameter_sendNewsletter = 'sendNewsletter';

    private $emailSender;

    function __construct($emailSender)
    {
        $this->emailSender = $emailSender;

        if ($this->isNewsletterTriggered())
        {
            $this->emailSender->sendNewsletter();
            $this->printSuccessfullNewsletterAlertBox();
        }

        add_menu_page('Pilz-Newsletter Plugin Page', 'Pilz-Newsletter Administration', 'manage_options', 'pilz-newsletter-plugin', array($this, 'initPilzNewsletterAdminPage'));
    }


    function isNewsletterTriggered()
    {
        return $this->isSendNewsletterPostParameterSet();
    }


    function isSendNewsletterPostParameterSet()
    {
        return isset($_POST[self::postParameter_sendNewsletter]);
    }

    function printSuccessfullNewsletterAlertBox()
    {
        echo '<script language="javascript">';
        echo 'alert("newsletter successfully sent")';
        echo '</script>';
    }

    function initPilzNewsletterAdminPage()
    {
        echo "<h1>Pilz-Newsletter-Administration</h1>";
        echo "<p>sending the newsletter sends the last 3 posts to all recipients</p>";
        echo '<form method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        echo '<button type="submit" name="' . self::postParameter_sendNewsletter . '">Newsletter versenden</button>';
        echo '</form>';
    }
}


?>