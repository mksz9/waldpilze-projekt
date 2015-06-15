<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_additionalInformationField
{
    const input_toxic_name = 'pf_toxic';

    function __construct()
    {
        add_filter('pilzformular_addFormField', array($this, 'printHtml'), 25, 2);
        add_filter('pilzformular_postInserted', array($this, 'insertMetaInfo'), 10, 2);
        add_filter('pilzformular_getFieldData', array($this, 'getValue'));
    }

    function printHtml($htmlForm, $data)
    {
        $htmlForm .= '<p>';
        $htmlForm .= __('Toxic or Nontoxic', 'gcms_pilzformular') . ':<br />';
        $htmlForm .= '<input type="radio" name="' . pilzDb::POST_META_ADD_INFO_TOXIC . '" value="toxic" ' . checked($data[pilzDb::POST_META_ADD_INFO_TOXIC], 'toxic') . ' ><label for="toxic"> ' . __('Toxic', 'gcms_pilzformular') . '</label><br> ';
        $htmlForm .= '<input type="radio" name="' . pilzDb::POST_META_ADD_INFO_TOXIC . '" value="atoxic" ' . checked($data[pilzDb::POST_META_ADD_INFO_TOXIC], 'atoxic') . ' ><label for="atoxic">  ' . __('Nontoxic', 'gcms_pilzformular') . '</label><br> ';
        $htmlForm .= ' </p>';

        $htmlForm .= '<p>';
        $htmlForm .= __('Features', 'gcms_pilzformular') . ':<br />';
        $htmlForm .= '<input type="text" name="' . pilzDb::POST_META_ADD_INFO_FEATURES . '" value="' . $data[pilzDb::POST_META_ADD_INFO_FEATURES] . '" ><br>';
        $htmlForm .= ' </p>';

        $htmlForm .= '<p>';
        $htmlForm .= __('Locations', 'gcms_pilzformular') . ':<br />';
        $htmlForm .= '<input type="text" name="' . pilzDb::POST_META_ADD_INFO_LOCATIONS . '" value="' . $data[pilzDb::POST_META_ADD_INFO_LOCATIONS] . '" ><br>';
        $htmlForm .= ' </p>';

        $htmlForm .= '<p>';
        $htmlForm .= __('Skin Color', 'gcms_pilzformular') . ':<br />';
        $htmlForm .= '<input type="text" name="' . pilzDb::POST_META_ADD_INFO_SKINCOLOR . '" value="' . $data[pilzDb::POST_META_ADD_INFO_SKINCOLOR] . '" ><br>';
        $htmlForm .= ' </p>';

        return $htmlForm;
    }

    function insertMetaInfo($postId, $data)
    {
        $additionalInformation = array();

        $additionalInformation[pilzDb::POST_META_ADD_INFO_TOXIC] = $data[pilzDb::POST_META_ADD_INFO_TOXIC];
        $additionalInformation[pilzDb::POST_META_ADD_INFO_FEATURES] = $data[pilzDb::POST_META_ADD_INFO_FEATURES];
        $additionalInformation[pilzDb::POST_META_ADD_INFO_LOCATIONS] = $data[pilzDb::POST_META_ADD_INFO_LOCATIONS];
        $additionalInformation[pilzDb::POST_META_ADD_INFO_SKINCOLOR] = $data[pilzDb::POST_META_ADD_INFO_SKINCOLOR];


        add_post_meta($postId, pilzDb::POST_META_ADD_INFO, $additionalInformation);
    }

    function getValue($data)
    {

        if (isset($_POST[pilzDb::POST_META_ADD_INFO_TOXIC]) &&
            ($_POST[pilzDb::POST_META_ADD_INFO_TOXIC] === 'toxic' || $_POST[pilzDb::POST_META_ADD_INFO_TOXIC] === 'atoxic')) {
            $data[pilzDb::POST_META_ADD_INFO_TOXIC] = $_POST[pilzDb::POST_META_ADD_INFO_TOXIC];
        }
        else
        {
            $data[pilzDb::POST_META_ADD_INFO_TOXIC] = '';
        }

        $data = $this->getValueHelper($data, pilzDb::POST_META_ADD_INFO_FEATURES);
        $data = $this->getValueHelper($data, pilzDb::POST_META_ADD_INFO_LOCATIONS);
        $data = $this->getValueHelper($data, pilzDb::POST_META_ADD_INFO_SKINCOLOR);

        return $data;
    }

    private function getValueHelper($data, $key)
    {
        if (isset($_POST[$key])) {
            $data[$key] = sanitize_text_field($_POST[$key]);
        } else {
            $data[$key] = '';
        }

        return $data;
    }
}