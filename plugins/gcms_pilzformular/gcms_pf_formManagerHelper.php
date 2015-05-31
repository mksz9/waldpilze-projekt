<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_formManagerHelper
{
    const input_submit_name = 'pf_submit';

    const input_toxic_name = 'pf_toxic';
    private $data = array();


    public function printHtmlFormWithValidationError($errorMessages)
    {
        echo $errorMessages;
        $this->printHtmlForm();
    }

    public function printHtmlForm()
    {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" enctype="multipart/form-data">';

        apply_filters('pilzformular_addFormField', $this->data);

        echo '<p><input type="submit" name="' . self::input_submit_name . '" value="Pilz absenden"/></p>';
        echo '</form>';
    }

    public function hasSubmited()
    {
        if (isset($_POST[self::input_submit_name])) {
            return true;
        }

        return false;
    }


    public function readFieldData()
    {
        $this->data = apply_filters('pilzformular_getFieldData', $this->data);
    }

    public function validate()
    {
        $validationResult = new gcms_pf_validationResult();

        $validationResult->appendErrorMessage('<ul>');

        $validationResult = apply_filters('pilzformular_validateInput', $validationResult, $this->data);

        $validationResult->appendErrorMessage('</ul>');

        return $validationResult;

    }

    public function saveData()
    {
        $post = array(
            'post_status' => 'draft',
            //todo change post author
            'post_author' => 1,
            'post_type' => pilzDb::_POST_TYPE_NAME
        );

        $post = apply_filters('pilzformular_editPost', $post, $this->data);

        // Insert the post into the database
        $postId = wp_insert_post($post);

        if (!is_wp_error($postId)) {
            $postId = apply_filters('pilzformular_postInserted', $postId, $this->data);
        }

        if (is_wp_error($postId)) {
            echo '<h2 > Es ist ein interner Fehler aufgetreten .</h2 >';
        } else {
            echo '<h2>Ihr Pilz wurde erfolgreich an die Pilzredaktion versendet.</h2>';
        }

    }
}

