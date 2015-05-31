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
        add_filter('pilzformular_addFormField', array($this, 'printHtml'), 60);
        add_filter('pilzformular_validateInput', array($this, 'validate'));

        add_action('wp_enqueue_scripts', array($this, 'captchaScripts'));
    }

    function captchaScripts()
    {
        wp_enqueue_style('captchaStyle', plugins_url('captchaStyle.css', __FILE__), array(), filemtime(plugin_dir_path( __FILE__ ).'captchaStyle.css'));
        wp_enqueue_script('captchaScript', plugins_url('captchaScript.js', __FILE__), array(), filemtime(plugin_dir_path( __FILE__ ).'captchaScript.js'), true);
    }

    function printHtml($data)
    {
        echo '<img id="captchaImage" src="' . esc_url(gcms_cap_captcha::getInstance()->getCaptachaImageUrl()) . '" alt="" />';
        echo '<p>' . __('Captcha') . ': <br /><input type="text" name="captcha" id="captcha" autocomplete="off" /></p>';

        return $data;
    }

    function validate($validationResult)
    {
        if (!(gcms_cap_captcha::getInstance()->isValidCaptchaText(trim($_POST['captcha'])))) {
            $validationResult->appendErrorMessage('<li>' . __('The captcha-content does not match the captcha-image', 'gcms_pilzformular') . '</li>');
            $validationResult->setError();
        }

        return $validationResult;
    }
}