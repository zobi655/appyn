<?php get_header(); ?>
	<div class="container">
		<div class="app-s">
			<?php 
			if( have_posts() ) : while ( have_posts() ) : the_post(); 	
				do_action( 'box_single_app' );
			endwhile; endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
<?php get_footer(); ?>