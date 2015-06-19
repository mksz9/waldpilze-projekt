<?php
	get_header();
	get_sidebar();
?>
        <div class="col-sm-9 maincontent">
          <div class="box brown">
			<?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : // The Loop ?>
                    <?php the_post(); ?>
                    	<h2><?php the_title(); ?></h2>
                        <?php the_content(); ?>
                    <?php endwhile; // end of one post ?>
                    <?php else : ?>
                        <h2>Fehler - 404</h2>
                        <p>Die angeforderte Seite konnte nicht gefunden werden. Bitte versuchen Sie es erneut.</p>
            <?php endif; // do not delete ?>
              <?php comments_template(); ?>
          </div>
        </div>       
<?php

	get_footer();
?>