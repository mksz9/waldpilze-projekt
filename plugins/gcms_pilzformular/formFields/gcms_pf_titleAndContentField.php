<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_titleAndContentField
{
    const input_title_name = 'pf_name';
    const input_title_content = 'pf_content';

    function __construct()
    {
        add_filter('pilzformular_addFormField', array($this, 'printTitleHtml'), 9,2);
        add_filter('pilzformular_validateInput', array($this, 'validateTitle'), 10, 2);
        add_filter('pilzformular_getFieldData', array($this, 'getTitleData'));
        add_filter('pilzformular_editPost', array($this, 'addTitleToPost'), 10, 2);

        add_filter('pilzformular_addFormField', array($this, 'printContentHtml'),10,2);
        add_filter('pilzformular_getFieldData', array($this, 'getContentData'));
        add_filter('pilzformular_editPost', array($this, 'addContentToPost'), 10, 2);
    }

    function printTitleHtml($htmlForm, $data)
    {
        $htmlForm .= '<p>';
        $htmlForm .=   __('Name', 'gcms_pilzformular') . '*: <br />';
        $htmlForm .=  '<input type="text" name="' . self::input_title_name . '" pattern="[a-zA-Z0-9 öäüÖÜÄ]+" value="' . esc_attr($data[self::input_title_name]) . '" size="40" />';
        $htmlForm .=  '</p>';

        return $htmlForm;
    }

    function validateTitle($validationResult, $data)
    {
        if (strlen($data[self::input_title_name]) < 3) {
            $validationResult->appendErrorMessage('<li>' . __('The Title must be at least 3 characters long', 'gcms_pilzformular') . '</li>');
        }
        return $validationResult;
    }

    function  printContentHtml($htmlForm, $data)
    {
        $htmlForm .=  '<p>';
        $htmlForm .=  __('Description', 'gcms_pilzformular') . ': <br />';
        $htmlForm .=  '<textarea type="text" name="' . self::input_title_content . '" pattern="[a-zA-Z0-9 ]+" size="200" rows="8" >' . esc_attr($data[self::input_title_content]) . '</textarea>';
        $htmlForm .=  '</p>';

        return $htmlForm;
    }

    function getTitleData($data)
    {
        if (isset($_POST[self::input_title_content])) {
            $data[self::input_title_content] = sanitize_text_field(trim($_POST[self::input_title_content]));
        } else {
            $data[self::input_title_content] = '';
        }

        return $data;
    }

    function getContentData($data)
    {
        if (isset($_POST[self::input_title_name])) {
            $data[self::input_title_name] = sanitize_text_field(trim($_POST[self::input_title_name]));
        } else {
            $data[self::input_title_name] = '';

        }
        return $data;
    }

    function addContentToPost($post, $data)
    {
        $post['post_content'] = $data[self::input_title_content];
        return $post;
    }

    function addTitleToPost($post, $data)
    {
        $post['post_title'] = $data[self::input_title_name];
        return $post;
    }
}