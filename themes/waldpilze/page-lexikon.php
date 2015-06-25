<?php

/**
 * Template Name: Lexikon
 **/

get_header();
get_sidebar(); ?>
    <div class="col-sm-9 maincontent lexikon">
        <div class="box">
            <div class="row">

                <div class="col-sm-12">
                    <?php if (have_posts()) : ?>
                        <?php while (have_posts()) : // The Loop ?>
                            <?php the_post(); ?>
                            <h2><?php the_title(); ?></h2>
                            <?php the_content(); ?>
                        <?php endwhile; // end of one post ?>
                    <?php endif; // do not delete
                    wp_reset_query(); ?>
                

                <?php
                //Print all Pize
                $args = array('post_type' => pilzDb::_POST_TYPE_NAME,
                    'orderby' => 'title',
                    'order' => 'ASC');

                // The Query
                query_posts($args);

                // The Loop
                $counter = 0;
                while (have_posts()) : the_post();
                if ($counter == 0):
                    ?>
                <div class="row row-lxk">
                <?php endif; ?>
                    <div class="col-sm-4">
                        <a class="lexikon_link" href="<?php the_permalink() ?>"><?php the_title(); ?></a>
                        <?php
                        if (has_post_thumbnail()) { ?>
                            <a href="<?php the_permalink() ?>">
                                <?php the_post_thumbnail('medium', array(
                                    'class' => "img-responsive ",
                                )); ?>
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                <?php
                $counter++;
                if ($counter == 3):
                    $counter = 0;
                ?>
                </div>
                <?php
                endif;
                endwhile;
                wp_reset_query();
                ?>
                </div>
            </div>
        </div>
    </div>
<?php
get_footer();
