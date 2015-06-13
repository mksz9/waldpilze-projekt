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
        add_filter('pilzformular_addFormField', array($this, 'printHtml'), 25, 2);
        add_filter('pilzformular_postInserted', array($this, 'insertMetaInfo'), 10, 2);
        add_filter('pilzformular_getFieldData', array($this, 'getToxicValue'));
        add_filter('pilzformular_validateInput', array($this, 'validateToxic'), 10, 2);
    }

    function printHtml($htmlForm, $data)
    {
        $htmlForm .= '<p>';
        $htmlForm .= __('Toxic or Nontoxic', 'gcms_pilzformular') . '<br />';
        $htmlForm .= '<input type="radio" name="' . self::input_toxic_name . '" value="toxic" ' . checked($data[self::input_toxic_name], 'toxic') . ' ><label for="toxic"> ' . __('Toxic', 'gcms_pilzformular') . '</label><br> ';
        $htmlForm .= '<input type="radio" name="' . self::input_toxic_name . '" value="atoxic" ' . checked($data[self::input_toxic_name], 'atoxic') . ' ><label for="atoxic">  ' . __('Nontoxic', 'gcms_pilzformular') . '</label><br> ';
        $htmlForm .= ' </p>';

        return $htmlForm;
    }

    function insertMetaInfo($postId, $data)
    {
        add_post_meta($postId, '_toxic', $data[self::input_toxic_name]);
    }

    function getToxicValue($data)
    {
        if (isset($_POST[self::input_toxic_name])) {
            $data[self::input_toxic_name] = sanitize_text_field($_POST[self::input_toxic_name]);
        } else {
            $data[self::input_toxic_name] = '';
        }

        return $data;
    }

    function validateToxic($validationResult, $data)
    {
        if($data[self::input_toxic_name] === 'toxic' || $data[self::input_toxic_name] === 'atoxic')
        {
            return $validationResult;
        }
        else
        {
            $validationResult->appendErrorMessage('<li>' . __('You must specify the toxicity', 'gcms_pilzformular') . '</li>');
            return $validationResult;
        }
    }
}