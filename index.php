<?php get_header(); ?>
	<div class="container">
		<div class="sections">
			<?php do_action( 'do_home' ); ?>
		</div>
        <?php 
        if( appyn_options( 'og_sidebar' ) ) {
            get_sidebar( 'general' ); 
        } ?>
	</div>
<?php get_footer(); ?>