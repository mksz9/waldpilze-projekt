<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_formManager
{
    private $helper;

    function __construct()
    {
        $this->helper = new gcms_pf_formManagerHelper();
        add_shortcode('pilzformular', array($this, 'managePilzFormShortcode'));
    }

    function managePilzFormShortcode()
    {
        ob_start();

        $this->helper->readFieldData();
        if ($this->helper->hasFormSubmitted() === true) {
            $validationResult = $this->helper->validateFieldData();
            if ($validationResult->hasError() == true) {
                $this->helper->printHtmlFormWithValidationError($validationResult->getMessage());
            } else {
                $this->helper->saveData();
            }
        } else {
            $this->helper->printHtmlForm();
        }

        return ob_get_clean();
    }
}