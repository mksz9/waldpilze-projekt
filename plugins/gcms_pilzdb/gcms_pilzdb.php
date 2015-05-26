<?php
    /**
     * Plugin Name: Pilzdatenbank
     * Description: Hauptplugin, welches Pilzdatenbank Funktionalitäten zur Verfügung stellt
     * Version: 0.1
     * Author: Grundlagen CMS
     */

    class pilzDb {

        const _POST_TYPE_NAME = "pilze";

        public function __construct() {
            add_action('init', array($this, 'createCustomPostType'), 1);
        }

        public function createCustomPostType() {
            register_post_type(
                self::_POST_TYPE_NAME,
                array(
                    'labels' => array(
                        'name' => ucfirst(self::_POST_TYPE_NAME),
                    ),
                    'public' => true,
                    'show_ui' => true,
                    'query_var' => strtolower(self::_POST_TYPE_NAME),
                    'supports' => array('title', 'editor', 'thumbnail')
                )
            );

            //Custom Hook after registering Posttype
            do_action('pilzdatenbank_posttype_created');

        }

    }

    $pilzDb = new pilzDb();