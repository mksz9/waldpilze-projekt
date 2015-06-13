<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_imageField
{
    const input_thumbnail = 'pf_thumbnail';


    function __construct()
    {
        add_filter('pilzformular_addFormField', array($this, 'printHtml'), 20);
        add_filter('pilzformular_addFormField', array($this, 'changeFormTag'), 4);
        add_filter('pilzformular_validateInput', array($this, 'validate'));
        add_filter('pilzformular_postInserted', array($this, 'insertImage'));
    }

    function changeFormTag($htmlForm)
    {
        return substr_replace($htmlForm, ' onclick="return checkFileSize()"', strlen($htmlForm) - 1, 0);
    }

    function insertImage($postId)
    {
        $thumbnailId = $this->createImage();

        if (is_wp_error($thumbnailId)) {
            return $thumbnailId;
        }

        if (set_post_thumbnail($postId, $thumbnailId) === false) {
            return new WP_Error('broke', "set post thumbnail Error");
        }
        return $postId;
    }

    function printHtml($htmlForm)
    {
        $htmlForm .= '<p>';
        $htmlForm .= '' . __('Picture', 'gcms_pilzformular') . ': <br />';
        $htmlForm .= '<input id="imageImput" type="file" name="' . self::input_thumbnail . '" multiple="false" />';
        $htmlForm .= '</p>';

        $htmlForm .= '
        <script type="text/javascript">
        function checkFileSize()
        {
            var maxSize = 2000000;
            var fileInput = document.getElementById("imageImput");
            if(fileInput.files.length > 0 && fileInput.files[0].size > maxSize)
            {
                alert("The image ist to large!");
                fileInput.value = null;
                return false
            }
            return true;
        }
        </script>';

        return $htmlForm;
    }

    function validate($validationResult)
    {
        if (!isset($_FILES[self::input_thumbnail]['error']) ||
            is_array($_FILES[self::input_thumbnail]['error']) ||
            $_FILES[self::input_thumbnail]['error'] != UPLOAD_ERR_OK
        ) {
            $validationResult->appendErrorMessage('<li>' . __('You must specify an image', 'gcms_pilzformular') . '</li>');
            $validationResult->setError();

        } else {
            $fileType = strtolower($_FILES[self::input_thumbnail]['type']);
            if (!($fileType == 'image/jpeg' || $fileType == 'image/png')) {
                $validationResult->appendErrorMessage('<li>' . __('The image must be a jpg, jpeg or png file', 'gcms_pilzformular') . '</li>');
                $validationResult->setError();
            }
        }

        return $validationResult;
    }

    private function createImage()
    {
        // These files need to be included as dependencies when on the front end.
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Let WordPress handle the upload.
        return media_handle_upload(self::input_thumbnail, 0);
    }

}