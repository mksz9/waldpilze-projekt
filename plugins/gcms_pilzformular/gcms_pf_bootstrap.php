<?php

/**
 * Plugin Name: Import-Pilz-Formular
 * Description:
 * Version: 0.2
 * Author: Grundlagen CMS
 */

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (class_exists('pilzDb')) {
    class gcms_pf_bootstrap
    {
        private $formManager;

        function __construct()
        {
            include_once('gcms_pf_formManagerHelper.php');
            include_once('gcms_pf_validationResult.php');
            include_once('gcms_pf_formManager.php');
            $this->formManager = new gcms_pf_formManager();

            add_action('init', array($this, 'init'));
        }

        function init()
        {
            if(class_exists('gcms_cap_captcha'))
            {
                include_once('formFields/gcms_pf_captchaField.php');
                new gcms_pf_captchaField();
            }

            include_once('formFields/gcms_pf_securityField.php');
            include_once('formFields/gcms_pf_titleAndContentField.php');
            include_once('formFields/gcms_pf_imageField.php');
            new gcms_pf_securityField();
            new gcms_pf_titleAndContentField();
            new gcms_pf_imageField();
        }

    }

    $gcms_pf_boostrap = new gcms_pf_bootstrap();
}