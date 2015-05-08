<?php

class gcms_pf_formManager
{
    private $formPrinterAndReader;
    private $pilzPostTypeCreater;


    function __construct()
    {
        $this->formPrinterAndReader = new gcms_pf_formPrinterAndReader();
        $this->pilzPostTypeCreater = new gcms_pf_pilzPostTypeCreater();

        add_shortcode('pilzformular', array($this, 'managePilzFormShortcode'));
    }

    function managePilzFormShortcode()
    {
        ob_start();

        if ($this->formPrinterAndReader->hastPostContent()) {
            $data = $this->formPrinterAndReader->getFromData();
            $this->pilzPostTypeCreater->createNewPilz($data);
        } else {
            $this->formPrinterAndReader->printHtmlForm();
        }

        return ob_get_clean();
    }
}