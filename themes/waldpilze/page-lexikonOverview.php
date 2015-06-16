<?php

/**
 * Template Name: LexikonOverview
 **/

get_header();
get_sidebar();

?>
    <div class="col-sm-9 maincontent">
        <div class="box">
            <h2>Pilzauswahl nach Buchstaben</h2>
            <?php
            $args = array('post_type' => pilzDb::_POST_TYPE_NAME,
                'orderby' => 'title',
                'order' => 'ASC');

            // The Query
            query_posts($args);

            $letters = array();

            // The Loop
            while (have_posts()) : the_post();

                $title = get_the_title($post->ID);

                if (!is_null($title) && strlen($title) > 0) {
                    $firstLetter = substr($title, 0, 1);

                    if(!array_key_exists($firstLetter, $letters))
                    {
                        $letters[$firstLetter] = $firstLetter;
                    }
                }
            endwhile;
            wp_reset_query();

            foreach ($letters as $letter)
                     echo $letter. ' ';
            ?>
        </div>
    </div>
<?php
get_footer();
?>