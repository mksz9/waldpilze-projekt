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
                    <p><?php _e('Enter your Email for PilzNewsletter-Registration', 'gcms_newsletter') ?></p>
                    <input type="text" name="<?php echo self::input_email_name ?>" maxlength="30">
                    <input type="submit" name="<?php echo self::input_submit_name ?>" value="<?php _e('Send', 'gcms_newsletter') ?>" />
                    <br><br>
                                       <?php
                        if($this->captcha->isCaptchaPluginActive())
                        {
                            $this->captcha->printCaptcha(self::input_captchaValue_name);
                        }
                    ?>
                </form>
            <?php
        }

        function printEmailToConfirmNewsletterRegistrationSentHTML()
        {
            ?>
                <p><?php echo __('Email to confirm newsletter registration was sent to the following Email-Address: ', 'gcms_newsletter').$this->getNewSubscribedMailAdressForNewsletter() ?></p>
            <?php
        }

        function printSuccessfullySubscribedForNewsletterHTML()
        {
            ?>
                <p><?php _e('successfully subscribed for newsletter!', 'gcms_newsletter') ?></p>
            <?php
        }

        function printUnsuccessfullNewsletterConfirmationHTML()
        {
            ?>
                <p><?php _e('Could not confirm newsletter registration. Maybe you clicked not the latest confirmation email.', 'gcms_newsletter') ?></p>
            <?php
        }

        function  printEmailAddressIsAlreadyRecipientHTML()
        {
            ?>
                <p><?php _e('This email address is already registered for newsletter', 'gcms_newsletter') ?></p>
            <?php
        }

        function printEmailIsAlreadyAspirantHTML()
        {
            ?>
                <p><?php echo __('Newsletter confirmation was already sent to the following email address: ', 'gcms_newsletter') . $this->getNewSubscribedMailAdressForNewsletter() ?> <br> <?php _e('New confirmation email was sent.', 'gcms_newsletter') ?></p>
            <?php
        }

        function printUnsuccessfullRegistrationHTML()
        {
            ?>
                <p><?php echo __('We couldnt send the email to complete your registration to the entered email address: ', 'gcms_newsletter') . $this->getNewSubscribedMailAdressForNewsletter() ?></p>
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
            $returnURL = $this->unsubscribeSiteManager->getURLOfUnsubscribeSite();
            if(!strpos($returnURL, '?'))
            {
                $returnURL = $returnURL.'?';
            }
            else
            {
                $returnURL = $returnURL.'&';
            }
            $returnURL = $returnURL.self::getParameter_emailToUnsubscribe_name.'='.$recipientEmailAddress.'&'.self::getParameter_randomNumberToVerifyUnsubscribe_name.'='.$randomNumberToVerifyUnsubscribe;
            return $returnURL;
        }

        function getURL()
        {
            return esc_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        }

        function generateURLWithRandomNumberParameteToVerifyAspirant($randomNumber)
        {
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