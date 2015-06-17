<?php

/**
 * Template Name: Lexikon
 **/

get_header();
get_sidebar();

?>
    <div class="col-sm-9 maincontent lexikon">
        <div class="box">
            <div class="row">
                <div class="col-sm-12"><h2>Alle Pilze</h2></div>
                <?php
                $args = array('post_type' => pilzDb::_POST_TYPE_NAME,
                    'orderby' => 'title',
                    'order' => 'ASC');

                // The Query
                query_posts($args);

                // The Loop
                while (have_posts()) : the_post();
                    ?>
                    <div class="col-sm-4">
                        <a class="lexikon_link" href="<?php the_permalink() ?>"><?php the_title(); ?></a>
                        <?php
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('medium', array(
                                'class' => "img-responsive ",
                            ));
                        }
                        ?>
                    </div>
                <?php
                endwhile;
                wp_reset_query();
                ?>
            </div>
        </div>
    </div>
<?php
get_footer();
?>