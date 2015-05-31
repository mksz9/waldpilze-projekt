<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_securityField
{
    const input_nonce_filed = 'pf_nonce_field';

    function __construct()
    {
        add_filter('pilzformular_addFormField', array($this, 'printHtml'), 5);
        add_filter('pilzformular_validateInput',array($this, 'validate'), 5);
    }

    function printHtml($data)
    {
        wp_nonce_field(gcms_pf_formManagerHelper::input_submit_name, self::input_nonce_filed);
        return $data;
    }

    function validate($validationResult)
    {
        if (!(isset($_POST[self::input_nonce_filed]) && isset($_POST[gcms_pf_formManagerHelper::input_submit_name]) &&
            wp_verify_nonce($_POST[self::input_nonce_filed], gcms_pf_formManagerHelper::input_submit_name) === 1)
        ) {
            $validationResult->appendErrorMessage('<li>' . __('The server encountered an internal error', 'gcms_pilzformular') . '</li>');
            $validationResult->setError();
        }

        return $validationResult;
    }
}