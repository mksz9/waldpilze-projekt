<?php

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

class gcms_pf_pilzPostTypeCreater
{
    private $error_Message = '<h2>Es ist ein interner Fehler aufgetreten.</h2>';

    public function createNewPilz($data)
    {
        $postId = $this->createPost($data);

        if(is_wp_error($postId))
        {
            echo $this->error_Message;
            return;
        }

        $thumbnailId = $this->createImage();

        if(is_wp_error($thumbnailId))
        {
            echo $this->error_Message;
            return;
        }

        if(set_post_thumbnail($postId, $thumbnailId) === false)
        {
            echo $this->error_Message;
            return;
        }

        echo '<h2>Ihr Pilz wurde erfolgreich an die Pilzredaktion versendet.</h2>';
    }

    private function createPost($data)
    {
        $my_post = array(
            'post_title' => $data->getTitle(),
            'post_content' => $data->getContent(),
            'post_status' => 'draft',
            //todo change post author
            'post_author' => 1,
            'post_type' => pilzDb::_POST_TYPE_NAME
        );

        // Insert the post into the database
        return wp_insert_post($my_post);
    }

    private function createImage()
    {
        // These files need to be included as dependencies when on the front end.
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Let WordPress handle the upload.
        return media_handle_upload(gcms_pf_formPrinterAndReader::input_thumbnail, 0);
    }
}