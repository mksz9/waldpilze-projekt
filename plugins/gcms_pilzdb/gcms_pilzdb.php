<?php

/**
 * Plugin Name: Pilzdatenbank
 * Description: Hauptplugin, welches Pilzdatenbank Funktionalitäten zur Verfügung stellt
 * Version: 0.1
 * Author: Grundlagen CMS
 */
class pilzDb
{

    const _POST_TYPE_NAME = "pilze";

    public function __construct()
    {
        add_action('init', array($this, 'createCustomPostType'), 1);
        add_action('add_meta_boxes', array($this, 'add_pilz_metaboxes'));
        add_action('save_post', array($this, 'savePilz'), 1, 2);

    }

    public function createCustomPostType()
    {
        register_post_type(
            self::_POST_TYPE_NAME,
            array(
                'labels' => array(
                    'name' => ucfirst(self::_POST_TYPE_NAME),
                ),
                'public' => true,
                'show_ui' => true,
                'query_var' => strtolower(self::_POST_TYPE_NAME),
                'supports' => array('title', 'editor', 'thumbnail', 'custom-fields')
            )
        );

        //Custom Hook after registering Posttype
        do_action('pilzdatenbank_posttype_created');
    }


    function add_pilz_metaboxes()
    {
        add_meta_box('wpt_events_location', 'Zusatz Information', array($this, 'pilzform'), 'pilze', 'side', 'default');
    }

    function pilzform()
    {
        global $post;

        wp_nonce_field(plugin_basename(__FILE__), 'pilzmeta_noncename');

        // Get the location data if its already been entered
        $toxic = get_post_meta($post->ID, '_toxic', true);

        ?>
        <label for="_toxic">Giftig oder ungiftig:</label>
        <br/>
        <input type="radio" name="_toxic" value="toxic" <?php checked($toxic, 'toxic'); ?> >toxic<br>
        <input type="radio" name="_toxic" value="atoxic" <?php checked($toxic, 'atoxic'); ?> >atoxic<br>
    <?php
    }

    function savePilz($post_id, $post)
    {
        if (!isset($_POST['pilzmeta_noncename'])) {
            return $post->ID;
        }

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        if (!wp_verify_nonce($_POST['pilzmeta_noncename'], plugin_basename(__FILE__))) {
            return $post->ID;
        }

        // Is the user allowed to edit the post or page?
        if (!current_user_can('edit_post', $post->ID))
            return $post->ID;

        // OK, we're authenticated: we need to find and save the data
        // We'll put it into an array to make it easier to loop though.

        $toxic_meta['_toxic'] = $_POST['_toxic'];

        // Add values of $events_meta as custom fields

        foreach ($toxic_meta as $key => $value) { // Cycle through the $events_meta array!
            if ($post->post_type == 'revision') return; // Don't store custom data twice
            $value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
            if (get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
                update_post_meta($post->ID, $key, $value);
            } else { // If the custom field doesn't have a value
                add_post_meta($post->ID, $key, $value);
            }
            if (!$value) delete_post_meta($post->ID, $key); // Delete if blank
        }
    }
}

$pilzDb = new pilzDb();