<?php get_header(); ?>
	<div class="container">
		<div class="app-s" style="width:100%;">
			<?php 
			if ( have_posts() ) : while ( have_posts() ) : the_post(); 	
				do_action( 'box_single_app' );
			endwhile; endif; ?>
		</div>
	</div>
<?php get_footer(); ?>