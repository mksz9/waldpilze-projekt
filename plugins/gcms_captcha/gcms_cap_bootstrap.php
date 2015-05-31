<?php

/**
 * Plugin Name: Captcha
 * Description:
 * Version: 0.1
 * Author: Grundlagen CMS
 */

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!class_exists('gcms_cap_bootstrap')) {
    class gcms_cap_bootstrap
    {
        private $adminPage;

        function __construct()
        {
            if (class_exists('gcms_cap_captcha') ||
                class_exists('gcms_cap_adminPage') ||
                class_exists('gcms_cap_constant')
            ) {
                trigger_error(__('Plugin konnte nicht gestartet werden. Eine PHP Klasse ist schon vorhanden.', 'captcha_localization'), E_USER_ERROR);
                return;
            }

            include_once('gcms_cap_captcha.php');
            include_once('gcms_cap_adminPage.php');
            include_once('gcms_cap_constant.php');

            gcms_cap_captcha::getInstance()->initRedirection();
            if (is_admin())
                $this->adminPage = new gcms_cap_adminPage();

            add_action('init', array($this, 'init'));

            register_activation_hook(__FILE__, array($this, 'plugin_activated'));

        }

        function init()
        {
            load_plugin_textdomain(gcms_cap_constant::captcha_localization, FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
        }


        function plugin_activated()
        {
            $this->adminPage->setDefaultSettings();
        }
    }

    $gcms_cap_bootstrap = new gcms_cap_bootstrap();
}