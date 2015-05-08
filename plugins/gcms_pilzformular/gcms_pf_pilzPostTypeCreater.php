<?php


class gcms_pf_pilzPostTypeCreater
{
    public function createNewPilz($data)
    {
        if ($data instanceof gcms_pf_formData) {
            $my_post = array(
                'post_title' => $data->getTitle(),
                'post_content' => $data->getContent(),
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => pilzDb::_POST_TYPE_NAME
            );

            // Insert the post into the database
            wp_insert_post($my_post);

            echo '<h2>Ihr Pilz wurde erfolgreich an die Pilzredaktion versendet.</h2>';
        }
    }
}