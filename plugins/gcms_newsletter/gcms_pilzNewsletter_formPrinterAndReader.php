<?php

    class gcms_pilzNewsletter_formPrinterAndReader
    {
        const input_submit_name = 'form_subscribeForNewsletter_submit';
        const input_email_name = 'form_subscribeForNewsletter_email';

        function printSubscribeForNewsletterHTML()
        {
            echo '<form method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
            echo '<h1>Enter your Email for PilzNewsletter-Registration</h1>';
            echo '<input type="text" name="' . self::input_email_name . '" maxlength="30">';
            echo '<button type="reset">Eingaben zurücksetzen</button>';
            echo '<button type="submit" name="' . self::input_submit_name . '">Eingaben absenden</button>';
            echo '</form>';
        }

        function printSuccessfullySubscribedForNewsletterHTML()
        {
            echo '<p>successfully subscribed for newsletter with the following Email-Address: ' . $this->getNewSubscribedMailAdressForNewsletter() . '</p>';
        }

        function getNewSubscribedMailAdressForNewsletter()
        {
            return $_POST[self::input_email_name];
        }

        function formToSubscribeForNewsletterSent()
        {
            return isset($_POST[self::input_submit_name]);
        }
    }

?>