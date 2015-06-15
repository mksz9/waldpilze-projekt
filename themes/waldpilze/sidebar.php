        <div class="col-sm-3 sidebar">
        	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
				<div class="box">
					<?php dynamic_sidebar( 'sidebar-1' ); ?>
				</div>
			<?php endif; ?>
        </div>