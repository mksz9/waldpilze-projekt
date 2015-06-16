<?php
    /**
     * Plugin Name: Pilz-Newsletter
     * Description: Shortcode zum Einfügen der Newsletter-Funktion in eine Seite: [newsletterHTMLPrint]
     * Version: 0.1
     * Author: Patrick Sippl
     */


if (!class_exists('gcms_pilzNewsletter'))
{
    class gcms_pilzNewsletter
    {
        private $newsletterManager;

        function __construct()
        {
            $this->doIncludes();

            $this->newsletterManager = new gcms_pilzNewsletter_manager();

            register_activation_hook( __FILE__, array($this, 'initializePlugin'));
            register_deactivation_hook( __FILE__, array($this, 'finalizePlugin'));
        }

        // include all neccessary files
        function doIncludes()
        {
            include_once('gcms_pilzNewsletter_manager.php');
            include_once('gcms_pilzNewsletter_databaseManager.php');
            include_once('gcms_pilzNewsletter_emailSender.php');
            include_once('gcms_pilzNewsletter_formPrinterAndReader.php');
            include_once('gcms_pilzNewsletter_adminPage.php');
            include_once('gcms_pilzNewsletter_captcha.php');
            include_once('gcms_pilzNewsletter_unsubscribeSiteManager.php');

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            require_once( ABSPATH . 'wp-includes/pluggable.php' );
            require_once( ABSPATH . 'wp-includes/capabilities.php' );
        }

        function initializePlugin()
        {
            $this->newsletterManager->initializePlugin();
        }

        function finalizePlugin()
        {
            $this->newsletterManager->finalizePlugin();
        }
    }

    $gcms_pilzNewsletter = new gcms_pilzNewsletter();
}

 
?>
