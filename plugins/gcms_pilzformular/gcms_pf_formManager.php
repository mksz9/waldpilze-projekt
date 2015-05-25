<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_formManager
{
    private $formPrinterAndReader;
    private $pilzPostTypeCreater;


    function __construct()
    {
        session_start();

        $this->formPrinterAndReader = new gcms_pf_formPrinterAndReader();
        $this->pilzPostTypeCreater = new gcms_pf_pilzPostTypeCreater();

        add_shortcode('pilzformular', array($this, 'managePilzFormShortcode'));

    }


    function managePilzFormShortcode()
    {
        ob_start();

        if (!class_exists('gcms_cap_captcha')) {
            echo '<p>Das Forumlar kann nicht angezeigt werden, da das Captcha-Plugin nicht gefunden wurde!</p>';
            return ob_get_clean();
        }


        if ($this->formPrinterAndReader->hasSubmited() === true) {
            if ($this->formPrinterAndReader->hasPostValidData() === true) {
                $formData = $this->formPrinterAndReader->getFromData();
                $this->pilzPostTypeCreater->createNewPilz($formData);
            } else {
                $errorMessages = $this->formPrinterAndReader->getInvalidDataMessage();
                $this->formPrinterAndReader->printHtmlFormWithValidationError($errorMessages);
            }
        } else {
            $this->formPrinterAndReader->printHtmlForm();
        }

        return ob_get_clean();
    }
}