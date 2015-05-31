<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_captchaField
{
    function __construct()
    {
        add_filter('pilzformular_addFormField', array($this, 'printHtml'), 30);
        add_filter('pilzformular_validateInput',array($this, 'validate'));
    }

    function printHtml($data)
    {
        echo '<img src="' . esc_url(gcms_cap_captcha::getInstance()->getCaptachaImageUrl()) . ' alt="" />';
        echo '<p>Captcha: <br /><input type="text" name="captcha" id="captcha" autocomplete="off" /></p>';

        return $data;

    }

    function validate($validationResult)
    {
        if (!(gcms_cap_captcha::getInstance()->isValidCaptchaText(trim($_POST['captcha'])))) {
            $validationResult->appendErrorMessage('<li>Der Captcha-Inhalt stimmt nicht mit dem Bild überein.</li>');
            $validationResult->setError();
        }

        return $validationResult;
    }



}