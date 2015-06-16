<?php
get_header();
get_sidebar();

function printAddInfo($additionalInformation, $key, $label)
{
    $value = esc_html($additionalInformation[$key]);
    if (strlen($value) > 0)
        echo '<p><strong >' . $label . ': </strong > ' . $value . '</p>';

}

?>
    <div <?php post_class('col-sm-9 maincontent'); ?>>
        <div class="box">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : ?>
                    <?php the_post(); ?>
                    <h2><?php the_title(); ?></h2>
                    <div class="row">
                        <div class="col-sm-6">
                            <?php
                            if (has_post_thumbnail()) {
                                the_post_thumbnail('large', array(
                                    'class' => "img-responsive thumbnail",
                                ));
                            }
                            ?>
                        </div>

                        <?php $additionalInformation = get_post_meta($post->ID, pilzDb::POST_META_ADD_INFO, true); ?>

                        <div class="col-sm-6">
                            <?php
                            printAddInfo($additionalInformation, pilzDb::POST_META_ADD_INFO_TOXIC, 'Giftigkeit');
                            printAddInfo($additionalInformation, pilzDb::POST_META_ADD_INFO_TOXIC, 'Giftigkeit');
                            printAddInfo($additionalInformation, pilzDb::POST_META_ADD_INFO_LOCATIONS, 'Location');
                            printAddInfo($additionalInformation, pilzDb::POST_META_ADD_INFO_SKINCOLOR, 'Hautfarbe');
                            printAddInfo($additionalInformation, pilzDb::POST_META_ADD_INFO_FEATURES, 'Besonderheiten');
                            ?>
                        </div>
                    </div>
                    <?php the_content(); ?>
                <?php endwhile; // end of one post ?>
            <?php else : ?>
                <h2>Fehler - 404</h2>
                <p>Die angeforderte Seite konnte nicht gefunden werden. Bitte versuchen Sie es erneut.</p>
            <?php endif; // do not delete
            ?>
        </div>
    </div>
<?php
get_footer();
?>