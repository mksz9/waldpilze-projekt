<?php

    class gcms_pilzNewsletter_formPrinterAndReader
    {
        const input_submit_name = 'form_subscribeForNewsletter_submit';
        const input_email_name = 'form_subscribeForNewsletter_email';

        const input_captchaValue_name = 'form_subscribeForNewsletter_captchaValue';

        const getParameter_randomNumberToVerifyAspirant_name = 'rand1';
        const getParameter_randomNumberToVerifyUnsubscribe_name = 'rand2';
        const getParameter_emailToUnsubscribe_name = 'email';


        private $databaseManager;
        private $captcha;
        private $unsubscribeSiteManager;


        function __construct($databaseManager, $captcha, $unsubscribeSiteManager)
        {
            $this->databaseManager = $databaseManager;
            $this->captcha = $captcha;
            $this->unsubscribeSiteManager = $unsubscribeSiteManager;
        }

        function printSubscribeForNewsletterHTML()
        {
            ?>
                <form method="post" action="<?php esc_url($_SERVER['REQUEST_URI']) ?>">
                    <p>Enter your Email for PilzNewsletter-Registration</p>
                    <input type="text" name="<?php echo self::input_email_name ?>" maxlength="30">

                    <?php
                        if(class_exists('gcms_cap_captcha'))
                        {
                            $this->captcha->printCaptcha(self::input_captchaValue_name);
                        }
                    ?>
                    <button type="submit" name="<?php echo self::input_submit_name ?>">Eingaben absenden</button>
                </form>
            <?php
        }

        function printEmailToConfirmNewsletterRegistrationSentHTML()
        {
            ?>
                <p>Email to confirm newsletter registration was sent to the following Email-Address: <?php echo $this->getNewSubscribedMailAdressForNewsletter() ?></p>
            <?php
        }

        function printSuccessfullySubscribedForNewsletterHTML()
        {
            ?>
                <p>successfully subscribed for newsletter!</p>
            <?php
        }

        function printUnsuccessfullNewsletterConfirmationHTML()
        {
            ?>
                <p>Could not confirm newsletter registration. Maybe you clicked not the latest confirmation email.</p>
            <?php
        }

        function  printEmailAddressIsAlreadyRecipientHTML()
        {
            ?>
                <p>This email address is already registered for newsletter</p>
            <?php
        }

        function printEmailIsAlreadyAspirantHTML()
        {
            ?>
                <p>Newsletter confirmation was already sent to the following email address: <?php echo $this->getNewSubscribedMailAdressForNewsletter() ?><br>New confirmation email was sent.</p>
            <?php
        }

        function printSuccessfullUnsubscribeHTML()
        {
            ?>
                <p>you successfully unsubscribed the following email address from our newsletter: <?php echo $this->getEmailAddressToUnsubscribe() ?></p>
            <?php
        }

        function printUnsuccessfullUnsubscribeHTML()
        {
            ?>
                <p>We couldnt unsubscribe the following email address from our newsletter: <?php echo $this->getEmailAddressToUnsubscribe() ?><br>you are not authorized</p>
            <?php
        }

        function printUnsuccessfullRegistrationHTML()
        {
            ?>
                <p>We couldnt send the email to complete your registration to the entered email address: <?php echo $this->getNewSubscribedMailAdressForNewsletter() ?></p>
            <?php
        }

        function getNewSubscribedMailAdressForNewsletter()
        {
            return sanitize_text_field($_POST[self::input_email_name]);
        }

        function formToSubscribeForNewsletterSent()
        {
            return isset($_POST[self::input_submit_name]);
        }

        function getRandomNumberFromConfirmationLinkFromEmail()
        {
            return sanitize_text_field($_GET[self::getParameter_randomNumberToVerifyAspirant_name]);
        }

        function confirmationLinkFromEmailClicked()
        {
            return isset($_GET[self::getParameter_randomNumberToVerifyAspirant_name]);
        }

        function getEmailAddressToUnsubscribe()
        {
            return sanitize_text_field($_GET[self::getParameter_emailToUnsubscribe_name]);
        }

        function getRandomNumberToVerifyUnsubscribe()
        {
            return sanitize_text_field($_GET[self::getParameter_randomNumberToVerifyUnsubscribe_name]);
        }

        function unsubscribeLinkFromEmailClicked()
        {
            return isset($_GET[self::getParameter_emailToUnsubscribe_name]) && isset($_GET[self::getParameter_randomNumberToVerifyUnsubscribe_name]);
        }

        function isCaptchaValid()
        {
            return $this->captcha->isCaptchaValid(self::input_captchaValue_name);
        }

        function generateUnsubscribeURLForEmail($recipientEmailAddress)
        {
            $randomNumberToVerifyUnsubscribe = $this->databaseManager->getRandomNumberToVerifyUnsubscribeForEmailAddressFromDatabase($recipientEmailAddress);
            return $this->unsubscribeSiteManager->getURLOfUnsubscribeSite().'&'.self::getParameter_emailToUnsubscribe_name.'='.$recipientEmailAddress.'&'.self::getParameter_randomNumberToVerifyUnsubscribe_name.'='.$randomNumberToVerifyUnsubscribe;

            //return $this->getURL().'&'.self::getParameter_emailToUnsubscribe_name.'='.$recipientEmailAddress.'&'.self::getParameter_randomNumberToVerifyUnsubscribe_name.'='.$randomNumberToVerifyUnsubscribe;
            //return plugin_dir_url(__FILE__).'gcms_pilzNewsletter_unsubscribe.php?'.self::getParameter_emailToUnsubscribe_name.'='.$recipientEmailAddress.'&'.self::getParameter_randomNumberToVerifyUnsubscribe_name.'='.$randomNumberToVerifyUnsubscribe;
        }

        function getURL()
        {
            return esc_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        }

        function generateURLWithRandomNumberParameteToVerifyAspirant($randomNumber)
        {
            //return $this->getURL().'&'.self::getParameter_randomNumberToVerifyAspirant_name.'='.$randomNumber;
            $returnURL = $this->getURL();
            if(strpos($returnURL, '?'))
            {
                $returnURL = $returnURL.'&';
            }
            else
            {
                $returnURL = $returnURL.'?';
            }
            $returnURL = $returnURL.self::getParameter_randomNumberToVerifyAspirant_name.'='.$randomNumber;
            return $returnURL;
        }
    }

?>