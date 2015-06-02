<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_formManagerHelper
{
    const input_submit_name = 'pf_submit';

    private $formDataArray = array();

    public function printHtmlFormWithValidationError($errorMessages)
    {
        echo $errorMessages;
        $this->printHtmlForm();
    }

    public function printHtmlForm()
    {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" enctype="multipart/form-data">';

        apply_filters('pilzformular_addFormField', $this->formDataArray);

        echo '<p><input type="submit" name="' . self::input_submit_name . '" value="' . __('Submit mushroom', 'gcms_pilzformular') . '"/></p>';
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
        $this->formDataArray = apply_filters('pilzformular_getFieldData', $this->formDataArray);
    }

    public function validate()
    {
        $validationResult = new gcms_pf_validationResult();

        $validationResult->appendErrorMessage('<ul>');

        $validationResult = apply_filters('pilzformular_validateInput', $validationResult, $this->formDataArray);

        $validationResult->appendErrorMessage('</ul>');

        return $validationResult;
    }

    public function saveData()
    {
        $post = array(
            'post_status' => 'draft',
            'post_type' => pilzDb::_POST_TYPE_NAME
        );

        $post = apply_filters('pilzformular_editPost', $post, $this->formDataArray);

        // Insert the post into the database
        $postId = wp_insert_post($post);

        if (!is_wp_error($postId)) {
            $postId = apply_filters('pilzformular_postInserted', $postId, $this->formDataArray);
        }

        if (is_wp_error($postId)) {
            echo '<h2>' . __('The server encountered an internal error', 'gcms_pilzformular') . '</h2 >';
        } else {
            echo '<h2>' . __('The mushroom was successfully sent', 'gcms_pilzformular') . '</h2>';
        }
    }
}

