<?php

class gcms_pilzNewsletter_adminPage
{
    const postParameter_sendNewsletter = 'sendNewsletter';

    private $emailSender;
    private $databaseManager;

    function __construct($emailSender, $databaseManager)
    {
        $this->emailSender = $emailSender;
        $this->databaseManager = $databaseManager;

        if ($this->isNewsletterTriggered())
        {
            $this->emailSender->sendReminder();
            $this->printSuccessfullNewsletterAlertBox();
        }

        add_menu_page('Pilz-Newsletter Plugin Page', __('mushroom-reminder', 'gcms_newsletter'), 'manage_options', 'pilz-newsletter-plugin', array($this, 'initPilzNewsletterAdminPage'));
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
        ?>
            <script language="javascript">alert("<?php _e('Reminder successfully sent', 'gcms_newsletter') ?>")</script>'
        <?php
    }

    function initPilzNewsletterAdminPage()
    {
        ?>
            <h1><?php _e('mushroom-reminder-administration', 'gcms_newsletter') ?></h1>
            <p><?php _e('sending the reminder reminds the user to visit the pilz-site again', 'gcms_newsletter') ?></p>
            <form method="post" action="<?php esc_url($_SERVER['REQUEST_URI']) ?>">
            <input type="submit" name="<?php echo self::postParameter_sendNewsletter ?>" value="<?php _e('send Reminder', 'gcms_newsletter') ?>" />
            </form>
            </br>
            </br>
            <h2><?php _e('current newsletter recipients:', 'gcms_newsletter') ?></h2>
        <?php

        foreach($this->databaseManager->getAllNewsletterRecipients() as $recipient)
        {
            $recipientEmailAddress = $recipient->email;
            ?>
                <p><?php echo $recipientEmailAddress; ?></p>
            <?php
        }
    }
}


?>