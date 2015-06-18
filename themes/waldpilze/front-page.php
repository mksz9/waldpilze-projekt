<?php
get_header();
get_sidebar();
$ids = array(get_option('fristPage'), get_option('secondPage'), get_option('thirdPage'));
$the_query = new WP_Query(array('post_type' => 'page', 'post__in' => $ids));

$boxStyle = array('yellow', 'red', 'green');
$pageCounter = 0;

if ($the_query->have_posts()) {
    while ($the_query->have_posts()) {
        $the_query->the_post();
        ?>
        <div class="col-sm-3 maincontent ">
            <div class="box <?php echo $boxStyle[$pageCounter] ?>">
                <?php
                if (has_post_thumbnail()) {
                    the_post_thumbnail('large', array(
                        'class' => "img-responsive center-block",
                    ));
                }
                ?>
                <h2 class="text-center"><?php the_title(); ?></h2>
                <?php the_content(); ?>
            </div>
        </div>

        <?php
        $pageCounter++;
    }
    wp_reset_query();
}

$mainPage = get_post(get_option('mainPage'));

if (!is_null($mainPage)) : ?>

    <div class="col-sm-9 maincontent">
        <div class="box brown">
            <h2 class="text-center"><?php the_title(); ?></h2>
            <?php the_content(); ?>
        </div>
    </div>
<?php
endif;
get_footer();