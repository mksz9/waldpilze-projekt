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

    const POST_META_ADD_INFO_TOXIC = '_toxic';

    const POST_META_ADD_INFO_FEATURES = '_features';

    const POST_META_ADD_INFO_LOCATIONS = '_locations';

    const POST_META_ADD_INFO_SKINCOLOR = '_skinColor';

    const POST_META_ADD_INFO = '_additionalInformation';

    public function __construct()
    {
        add_action('init', array($this, 'loadLanguage'));
        add_action('init', array($this, 'createCustomPostType'), 1);
        add_action('add_meta_boxes', array($this, 'add_pilz_metaboxes'));
        add_action('save_post', array($this, 'savePilz'), 1, 2);
    }

    public function loadLanguage()
    {
        load_plugin_textdomain('gcms_pilzdb', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
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
        add_meta_box('additionalInformation', 'Additional Information', array($this, 'pilzform'), 'pilze', 'normal', 'high');
    }

    function pilzform()
    {
        global $post;

        wp_nonce_field(plugin_basename(__FILE__), 'pilzmeta_noncename');

        // Get the data if its already been entered
        $additionalInformation = get_post_meta($post->ID, self::POST_META_ADD_INFO, true);

        ?>
        <label for="_toxic"><?php _e('Toxic or Atoxic', 'gcms_pilzdb'); ?>: </label>
        <input type="radio" name="_toxic"
               value="toxic" <?php checked(isset($additionalInformation[self::POST_META_ADD_INFO_TOXIC]) ? esc_attr($additionalInformation[self::POST_META_ADD_INFO_TOXIC]) : '', 'toxic'); ?> >toxic
        <input type="radio" name="_toxic"
               value="atoxic" <?php checked(isset($additionalInformation[self::POST_META_ADD_INFO_TOXIC]) ? esc_attr($additionalInformation[self::POST_META_ADD_INFO_TOXIC]) : '', 'atoxic'); ?> >atoxic
        <br>
        <label for="_features"><?php _e('Features', 'gcms_pilzdb'); ?>: </label>
        <input type="text" name="_features"
               value="<?php echo isset($additionalInformation[self::POST_META_ADD_INFO_FEATURES]) ? esc_attr($additionalInformation[self::POST_META_ADD_INFO_FEATURES]) : '' ?>">
        <br>
        <label for="_locations"><?php _e('Locations', 'gcms_pilzdb'); ?>: </label>
        <input type="text" name="_locations"
               value="<?php echo isset($additionalInformation[self::POST_META_ADD_INFO_LOCATIONS]) ? esc_attr($additionalInformation[self::POST_META_ADD_INFO_LOCATIONS]) : '' ?>">
        <br>
        <label for="_skinColor"><?php _e('Skin Color', 'gcms_pilzdb'); ?>: </label>
        <input type="text" name="_skinColor"
               value="<?php echo isset($additionalInformation[self::POST_META_ADD_INFO_SKINCOLOR]) ? esc_attr($additionalInformation[self::POST_META_ADD_INFO_SKINCOLOR]) : '' ?>">
    <?php
    }

    function savePilz($post_id, $post)
    {
        if (!isset($_POST['pilzmeta_noncename'])) {
            return $post->ID;
        }

        // verify this came from the our screen and with proper authorization,
        // because savePilz can be triggered at other times
        if (!wp_verify_nonce($_POST['pilzmeta_noncename'], plugin_basename(__FILE__))) {
            return $post->ID;
        }

        // Is the user allowed to edit the pilz?
        if (!current_user_can('edit_post', $post->ID))
            return $post->ID;

        $additionalInformation[self::POST_META_ADD_INFO_TOXIC] = $_POST[self::POST_META_ADD_INFO_TOXIC];
        $additionalInformation[self::POST_META_ADD_INFO_FEATURES] = sanitize_text_field($_POST[self::POST_META_ADD_INFO_FEATURES]);
        $additionalInformation[self::POST_META_ADD_INFO_LOCATIONS] = sanitize_text_field($_POST[self::POST_META_ADD_INFO_LOCATIONS]);
        $additionalInformation[self::POST_META_ADD_INFO_SKINCOLOR] = sanitize_text_field($_POST[self::POST_META_ADD_INFO_SKINCOLOR]);

        if ($post->post_type == 'revision')
            return;
        if (get_post_meta($post->ID, self::POST_META_ADD_INFO, FALSE)) {
            update_post_meta($post->ID, self::POST_META_ADD_INFO, $additionalInformation);
        } else {
            add_post_meta($post->ID, self::POST_META_ADD_INFO, $additionalInformation);
        }
    }
}

$pilzDb = new pilzDb();