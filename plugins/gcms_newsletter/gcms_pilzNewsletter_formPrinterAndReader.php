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

        function printEmailToConfirmNewsletterRegistrationSentHTML()
        {
            echo '<p>Email to confirm newsletter registration was sent to the following Email-Address: ' . $this->getNewSubscribedMailAdressForNewsletter() . '</p>';
        }

        function printSuccessfullySubscribedForNewsletterHTML()
        {
            echo '<p>successfully subscribed for newsletter</p>';
        }

        function printUnsuccessfullNewsletterConfirmationHTML()
        {
            echo '<p>Could not confirm newsletter registration</p>';
        }

        function  printEmailAddressIsAlreadyRecipientHTML()
        {
            echo '<p>This email address is already registered for newsletter</p>';
        }

        function printEmailIsAlreadyAspirantHTML()
        {
            echo '<p>Newsletter confirmation was already sent to the folloginw email address: '.$this->getNewSubscribedMailAdressForNewsletter().'<br>New confirmation email was sent.</p>';
        }

        function getNewSubscribedMailAdressForNewsletter()
        {
            return $_POST[self::input_email_name];
        }

        function formToSubscribeForNewsletterSent()
        {
            return isset($_POST[self::input_submit_name]);
        }

        function getRandomNumberFromConfirmationLinkFromEmailLink()
        {
            return $_GET['randomNumber'];
        }

        function confirmationLinkFromEmailClicked()
        {
            return isset($_GET['randomNumber']);
        }
    }

?>