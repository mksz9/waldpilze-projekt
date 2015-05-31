<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_toxicField
{
    const input_toxic_name = 'pf_toxic';

    function __construct()
    {
        add_filter('pilzformular_addFormField', array($this, 'printHtml'), 25);
    }

    function printHtml($data)
    {
        echo '<p>';
        echo __('Toxic or Nontoxic', 'gcms_pilzformular') . '<br />';
        echo '<input type="radio" id="toxic" name="' . self::input_toxic_name . '" value="giftig"><label for="toxic"> ' . __('Toxic', 'gcms_pilzformular') . '</label><br> ';
        echo '<input type="radio" id="atoxic" name="' . self::input_toxic_name . '" value="ungiftig"><label for="atoxic">  ' . __('Nontoxic', 'gcms_pilzformular') . '</label><br> ';
        echo ' </p>';

        return $data;
    }
}