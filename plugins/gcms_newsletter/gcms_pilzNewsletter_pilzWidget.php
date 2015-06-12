<?php

    class gcms_pilzNewsletter_pilzWidget
    {
        private $pilzWidget;

        function __construct()
        {
            //$this->pilzWidget = new Pilzwidget_Widget();
        }

        function getPostsForNewsletter($count)
        {
            $args = array(
                'post_type' => 'pilze',
                'orderby' => 'rand',
                'posts_per_page' => $count,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'seasons',
                        'field' => 'name',
                        'terms' => __(date('F')),
                    ),
                ),
            );

            $args = apply_filters('initQueryArgumentsForPilzWidget', $args);
            return new WP_Query($args);
        }
    }

?>