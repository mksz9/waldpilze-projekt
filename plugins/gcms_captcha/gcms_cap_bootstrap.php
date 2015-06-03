<?php

/**
 * Plugin Name: Captcha GCMS
 * Description: Stellt ein Captcha zur Verfügung
 * Version: 0.1
 * Author: Grundlagen CMS
 */

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_cap_bootstrap
{
    private $adminPage;

    function __construct()
    {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'pluginActivated'));
    }

    function init()
    {
        //init session, constants and localization
        session_start();
        include_once('gcms_cap_constant.php');
        load_plugin_textdomain(gcms_cap_constant::captcha_localization, FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');

        //init the captcha
        include_once('gcms_cap_captcha.php');

        //init admin setting page
        include_once('gcms_cap_adminPage.php');
        if (is_admin() && is_null($this->adminPage))
            $this->adminPage = new gcms_cap_adminPage(plugin_basename(__FILE__));
    }

    function pluginActivated()
    {
        include_once('gcms_cap_captcha.php');
        gcms_cap_captcha::getInstance()->createCaptchaUploadDir();

        include_once('gcms_cap_constant.php');
        include_once('gcms_cap_adminPage.php');
        if (is_admin()) {
            $this->adminPage = new gcms_cap_adminPage(plugin_basename(__FILE__));
            $this->adminPage->setDefaultSettings();
        }
    }
}

$gcms_cap_bootstrap = new gcms_cap_bootstrap();
