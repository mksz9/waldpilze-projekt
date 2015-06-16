<?php

/**
 * Template Name: Lexikon
 **/

get_header();
get_sidebar();

?>
    <div class="col-sm-9 maincontent">
        <div class="box">
            <div class="row">
                <?php
                $args = array('post_type' => pilzDb::_POST_TYPE_NAME,
                    'orderby' => 'title',
                    'order' => 'ASC');

                $letter = '';

                if(isset($_GET['letter']))
                {
                    $letter = strtoupper($_GET['letter']);
                }

                if(strlen($letter) === 1)
                {
                    $args['letter'] =  $letter;
                    echo '<div class="col-sm-12"><h2> Pilze mit dem Anfangsbuchstaben '.$letter.'</h2></div>';
                }
                else
                {
                    echo '<div class="col-sm-12"><h2>Alle Pilze</h2></div>';
                }

                // The Query
                query_posts($args);

                // The Loop
                while (have_posts()) : the_post();
                    ?>
                    <div class="col-sm-4">
                        <?php the_title(); ?>
                        <?php
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('medium', array(
                                'class' => "img-responsive thumbnail",
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