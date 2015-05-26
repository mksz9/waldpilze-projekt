<?php

    class gcms_pilzNewsletter_formPrinterAndReader
    {
        const input_submit_name = 'form_subscribeForNewsletter_submit';
        const input_email_name = 'form_subscribeForNewsletter_email';

        const getParameter_randomNumberToVerifyAspirant_name = 'rand1';
        const getParameter_randomNumberToVerifyUnsubscribe_name = 'rand2';
        const getParameter_emailToUnsubscribe_name = 'email';

        private $databaseManager;

        function __construct($databaseManager)
        {
            $this->databaseManager = $databaseManager;
        }

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
            echo '<p>successfully subscribed for newsletter!</p>';
        }

        function printUnsuccessfullNewsletterConfirmationHTML()
        {
            echo '<p>Could not confirm newsletter registration. Maybe you clicked not the latest confirmation email.</p>';
        }

        function  printEmailAddressIsAlreadyRecipientHTML()
        {
            echo '<p>This email address is already registered for newsletter</p>';
        }

        function printEmailIsAlreadyAspirantHTML()
        {
            echo '<p>Newsletter confirmation was already sent to the following email address: '.$this->getNewSubscribedMailAdressForNewsletter().'<br>New confirmation email was sent.</p>';
        }

        function printSuccessfullUnsubscribeHTML()
        {
            echo '<p>you successfully unsubscribed the following email address from our newsletter: '.$this->getEmailAddressToUnsubscribe();
        }

        function printUnsuccessfullUnsubscribeHTML()
        {
            echo '<p>we couldnt unsubscribe the following email address from our newsletter: '.$this->getEmailAddressToUnsubscribe().'<br>you are not authorized</p>';
        }

        function printUnsuccessfullRegistrationHTML()
        {
            echo '<p>We couldnt send the email to complete your registration to the entered email address: '.$this->getNewSubscribedMailAdressForNewsletter().'</p>';
        }

        function getNewSubscribedMailAdressForNewsletter()
        {
            return $_POST[self::input_email_name];
        }

        function formToSubscribeForNewsletterSent()
        {
            return isset($_POST[self::input_submit_name]);
        }

        function getRandomNumberFromConfirmationLinkFromEmail()
        {
            return $_GET[self::getParameter_randomNumberToVerifyAspirant_name];
        }

        function confirmationLinkFromEmailClicked()
        {
            return isset($_GET[self::getParameter_randomNumberToVerifyAspirant_name]);
        }

        function getEmailAddressToUnsubscribe()
        {
            return $_GET[self::getParameter_emailToUnsubscribe_name];
        }

        function getRandomNumberToVerifyUnsubscribe()
        {
            return $_GET[self::getParameter_randomNumberToVerifyUnsubscribe_name];
        }

        function unsubscribeLinkFromEmailClicked()
        {
            return isset($_GET[self::getParameter_emailToUnsubscribe_name]) && isset($_GET[self::getParameter_randomNumberToVerifyUnsubscribe_name]);
        }

        function generateUnsubscribeURLForEmail($recipientEmailAddress)
        {
            $randomNumberToVerifyUnsubscribe = $this->databaseManager->getRandomNumberToVerifyUnsubscribeForEmailAddressFromDatabase($recipientEmailAddress);
            return $this->getURL().'&'.self::getParameter_emailToUnsubscribe_name.'='.$recipientEmailAddress.'&'.self::getParameter_randomNumberToVerifyUnsubscribe_name.'='.$randomNumberToVerifyUnsubscribe;
        }

        function getURL()
        {
            return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        }

        function generateURLWithRandomNumberParameteToVerifyAspirant($randomNumber)
        {
            return $this->getURL().'&'.self::getParameter_randomNumberToVerifyAspirant_name.'='.$randomNumber;
        }
    }

?>