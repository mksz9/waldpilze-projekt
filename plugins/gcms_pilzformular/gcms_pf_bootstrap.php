<?php

/**
 * Plugin Name: Import-Pilz-Formular
 * Description:
 * Version: 0.2
 * Author: Grundlagen CMS
 */

if (!class_exists('gcms_pf_bootstrap')) {
    class gcms_pf_bootstrap
    {
        private $formManager;

        function __construct()
        {
            if (class_exists('gcms_pf_pilzPostTypeCreater') ||
                class_exists('gcms_pf_formManager') ||
                class_exists('gcms_pf_formPrinterAndReader')
            ) {
                echo 'Plugin konnte nicht gestartet werden. Eine PHP Klasse ist schon vorhanden. ERROR';
                return;
            }

            include_once('gcms_pf_pilzPostTypeCreater.php');
            include_once('gcms_pf_formManager.php');
            include_once('gcms_pf_formPrinterAndReader.php');
            include_once('gcms_pf_formData.php');

            $this->formManager = new gcms_pf_formManager();
        }
    }

    $gcms_pf_boostrap = new gcms_pf_bootstrap();
}