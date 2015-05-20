<?php

/**
 * Plugin Name: Captcha_GrCMS
 * Description:
 * Version: 0.1
 * Author: Grundlagen CMS
 */

if (!class_exists('gcms_cap_bootstrap')) {
    class gcms_cap_bootstrap
    {
        function __construct()
        {
            if (class_exists('gcms_cap_captcha') ||
                class_exists('gcms_cap_adminPage') ||
                class_exists('gcms_cap_constant')
            ) {
                echo 'Plugin konnte nicht gestartet werden. Eine PHP Klasse ist schon vorhanden. ERROR';
                return;
            }

            include_once('gcms_cap_captcha.php');
            include_once('gcms_cap_adminPage.php');
            include_once('gcms_cap_constant.php');

            gcms_cap_captcha::getInstance()->initRedirection();
            if (is_admin())
                new gcms_cap_adminPage();

            register_activation_hook(__FILE__, array($this, 'plugin_activated'));
        }

        function plugin_activated()
        {

        }
    }

    $gcms_cap_bootstrap = new gcms_cap_bootstrap();
}