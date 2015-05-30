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
        ?>
            <script language="javascript">alert("newsletter successfully sent")</script>'
        <?php
    }

    function initPilzNewsletterAdminPage()
    {
        ?>
            <h1>Pilz-Newsletter-Administration</h1>
            <p>sending the newsletter sends the last 3 posts to all recipients</p>
            <form method="post" action="<?php esc_url($_SERVER['REQUEST_URI']) ?>">
            <button type="submit" name="<?php echo self::postParameter_sendNewsletter ?>">Newsletter versenden</button>
            </form>
            </br>
            </br>
            <h2>current newsletter recipients:</h2>
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