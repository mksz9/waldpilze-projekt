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
        add_filter('pilzformular_addFormField', array($this, 'printTitleHtml'));
        add_filter('pilzformular_validateInput', array($this, 'validateTitle'), 10, 2);
        add_filter('pilzformular_getFieldData', array($this, 'getTitleData'));
        add_filter('pilzformular_editPost', array($this, 'addTitleToPost'), 10, 2);


        add_filter('pilzformular_addFormField', array($this, 'printContentHtml'));
        add_filter('pilzformular_validateInput', array($this, 'validateContent'), 10, 2);
        add_filter('pilzformular_getFieldData', array($this, 'getContentData'));
        add_filter('pilzformular_editPost', array($this, 'addContentToPost'), 10, 2);
    }

    function printTitleHtml($data)
    {
        echo '<p>';
        echo 'Name: <br />';
        echo '<input type="text" name="' . self::input_title_name . '" pattern="[a-zA-Z0-9 öäüÖÜÄ]+" value="' . esc_attr($data[self::input_title_name]) . '" size="40" />';
        echo '</p>';

        return $data;
    }

    function validateTitle($validationResult, $data)
    {
        if (strlen($data[self::input_title_name]) < 6) {
            $validationResult->appendErrorMessage('<li>Der Title muss mindestens 6 Zeichen lang sein.</li>');
        }
        return $validationResult;
    }

    function validateContent($validationResult, $data)
    {
        if (strlen($data[self::input_title_content]) < 50) {
            $validationResult->appendErrorMessage('<li>Der Content muss mindestens 50 Zeichen lang sein.</li>');
            $validationResult->setError();
        }
        return $validationResult;
    }

    function  printContentHtml($data)
    {
        echo '<p>';
        echo 'Beschreibung: <br />';
        echo '<textarea type="text" name="' . self::input_title_content . '" pattern="[a-zA-Z0-9 ]+" size="200" >' . esc_attr($data[self::input_title_content]) . '</textarea>';
        echo '</p>';

        return $data;
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